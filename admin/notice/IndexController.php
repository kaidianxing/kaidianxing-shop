<?php
/**
 * 开店星新零售管理系统
 * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开
 * @author 青岛开店星信息技术有限公司
 * @link https://www.kaidianxing.com
 * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.
 * @copyright 版权归青岛开店星信息技术有限公司所有
 * @warning Unauthorized deletion of copyright information is prohibited.
 * @warning 未经许可禁止私自删除版权信息
 */

namespace shopstar\admin\notice;

use GuzzleHttp\Exception\GuzzleException;
use shopstar\bases\KdxAdminApiController;
use shopstar\components\wechat\helpers\MiniProgramSubscriptionNoticeHelper;
use shopstar\components\wechat\helpers\OfficialAccountMessageHelper;
use shopstar\constants\ClientTypeConstant;
use shopstar\exceptions\notice\NoticeException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\notice\NoticeWechatDefaultTemplateModel;
use shopstar\models\notice\NoticeWechatTemplateModel;
use shopstar\models\notice\NoticeWxappDefaultTemplateModel;
use shopstar\models\notice\NoticeWxappTemplateModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * 消息通知
 * Class IndexController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\notice
 */
class IndexController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'edit',
        ]
    ];

    /**
     * 初始化页
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionInit()
    {
        $setting = ShopSettings::get('plugin_notice.send');
        //插件名称
        $pluginName = RequestHelper::get('plugin_name');
        $groupName = RequestHelper::get('group_name');
        $groupItemName = RequestHelper::get('group_item_name');

        $typeMap = [];
        if ($pluginName) {

            // 配置文件路径
            $filePath = \Yii::getAlias('@shopstar') . '/config/apps/' . $pluginName . '/NoticeSceneGroup.php';
            if (!is_file($filePath)) {
                return $this->error('配置文件不存在');
            }

            $className = "\\shopstar\\config\\apps\\{$pluginName}\\NoticeSceneGroup";

            $typeMap = (new $className)::getSceneGroupMap();
        } else {
            $typeMap = \shopstar\config\apps\notice\NoticeSceneGroup::getSceneGroupMap();
        }

        //默认
        $typeMap = $typeMap[$groupName][$groupItemName];
        if (empty($typeMap)) {
            return $this->success();
        }

        $type = [];
        //处理各个消息类型状态，和每个分组类型下的消息类型
        foreach ($typeMap as $index => $item) {
            $tempArr = [
                'title' => $item['title'],
                'scene_code' => $index
            ];

            foreach ($item['item'] as $itemIndex => $itemItem) {
                $tempArr['item'][$itemItem] = ['status' => isset($setting[$index][$itemItem]) ? $setting[$index][$itemItem]['status'] : 0];
            }

            $type[] = $tempArr;
        }

        return $this->success(['data' => $type]);
    }

    /**
     * 返回公众号消息详情
     * @return array|int[]|\yii\web\Response
     * @throws NoticeException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetWechatNotice()
    {
        $get = RequestHelper::get();
        if (empty($get['type_code'])) {
            throw new NoticeException(NoticeException::MANAGE_INDEX_GET_WECHAT_NOTICE_PARAMS_ERROR);
        }
        // 获取配置
        $setting = $this->getAllSetting($get, 'wechat');
        //获取模板
        $template = NoticeWechatTemplateModel::findOne([
            'scene_code' => $get['type_code'],
        ]);

        if ($template) {
            $template = $template->toArray();
            $template['content'] = Json::decode($template['content']);
        }

        $member = [];
        if ($setting['member_id']) {
            $member = self::getMemberInfoToId($setting['member_id']);
        }

        $data = [
            'status' => (int)$setting['status'] ?: 0,
            'is_default' => $setting['is_default'] ?: 0,
            'member' => $member,
            'template' => $template
        ];

        // 分销通知 特殊处理
        $commissionNotice = [
            'commission_buyer_child_pay',
            'commission_buyer_agent_add_child',
            'commission_buyer_agent_add_child_line',
        ];
        if (in_array($get['type_code'], $commissionNotice)) {
            $data['commission_level'] = $setting['commission_level'];
        }


        // 人信云消息 特色处理
        $rxyNotice = [
            'rxy_advisory_reminder',
        ];

        if (in_array($get['type_code'], $rxyNotice)) {
            $data['is_send_message'] = $setting['is_send_message'];
            $data['message_num'] = $setting['message_num'];
            $data['line_num'] = $setting['line_num'];
            $data['is_send_line'] = $setting['is_send_line'];
        }


        return $this->success($data);
    }

    /**
     * 公众号消息
     * @return array|int[]|\yii\web\Response
     * @throws NoticeException
     * @throws GuzzleException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionWechatNotice()
    {
        $post = RequestHelper::post();
        if (empty($post['type_code'])) {
            throw new NoticeException(NoticeException::MANAGE_INDEX_WECHAT_NOTICE_PARAMS_ERROR);
        }

        //状态 是否开启消息通知
        $setting['status'] = $post['status'] ?: 0;
//        if (!empty($post['member_id']) && count($post['member_id']) > 3) {
//            throw new NoticeException(NoticeException::MANAGE_INDEX_WECHAT_NOTICE_PEOPLE_NUMBER_ERROR);
//        }

        //是否使用默认模板
        $setting['is_default'] = $post['is_default'] == 1 ? 1 : 0;

        $setting['member_id'] = $post['member_id'];

        // 分销通知 特殊处理
        $commissionNotice = [
            'commission_buyer_child_pay',
            'commission_buyer_agent_add_child',
            'commission_buyer_agent_add_child_line',
        ];
        if (in_array($post['type_code'], $commissionNotice)) {
            $setting['commission_level'] = $post['commission_level'];
        }


        // 人信云通知 特殊处理
        $rxyNotice = [
            'rxy_advisory_reminder',
        ];
        if (in_array($post['type_code'], $rxyNotice)) {
            $setting['is_send_message'] = $post['is_send_message'];
            $setting['message_num'] = $post['message_num'];
            $setting['line_num'] = $post['line_num'];
            $setting['is_send_line'] = $post['is_send_line'];

        }


        $model = NoticeWechatTemplateModel::findOne([
            'scene_code' => $post['type_code'],
        ]);

        if (empty($model)) {
            $model = new NoticeWechatTemplateModel();
            $model->created_at = DateTimeHelper::now();
            $model->scene_code = $post['type_code'];
        }

        $model->title = $post['title'] ?: '';
        $model->content = Json::encode($post['content'] ?: []);

        //开启
        if ($setting['status'] == 1) {
            //如果是默认模板
            if ($setting['is_default'] == 1) {
                $defaultTemplate = NoticeWechatDefaultTemplateModel::findOne([
                    'scene_code' => $post['type_code']
                ]);

                if (empty($defaultTemplate)) {
                    throw new NoticeException(NoticeException::MANAGE_INDEX_WECHAT_NOTICE_LACK_DEFAULT_TEMPLATE_ERROR);
                }

                $defaultTemplateCode = $model->template_code;
                $model->template_code = $defaultTemplate->template_code;
                $model->content = $defaultTemplate->content;
                $model->title = $defaultTemplate->name;

                //如果是默认模板 && template_code 不等于原来的 || template_id为空的话 重新提交微信消息模板
                if (($setting['is_default'] == 1 && $defaultTemplateCode != $defaultTemplate->template_code) || empty($model->template_id)) {
                    //添加到微信模板库
                    if (empty($model->template_code)) {
                        throw new NoticeException(NoticeException::MANAGE_INDEX_WECHAT_NOTICE_WECHAT_TEMPLATE_CODE_ERROR);
                    }
                    $result = OfficialAccountMessageHelper::addTemplate($model->template_code);
                    if (is_error($result)) {
                        throw new NoticeException(NoticeException::MANAGE_INDEX_WECHAT_NOTICE_ADD_WECHAT_TEMPLATE_ERROR, $result['message']);
                    }

                    $model->template_id = $result['template_id'];
                }
            } else {
                //如果不是默认 而且没有选择微信消息模板
                if (empty($post['template_id'])) {
                    throw new NoticeException(NoticeException::MANAGE_INDEX_WECHAT_NOTICE_LACK_TEMPLATE_ERROR);
                }

                $model->template_id = $post['template_id'];
                //自定义模板没有template_code
                $model->template_code = '';
            }


            if (!$model->save()) {
                throw new NoticeException(NoticeException::MANAGE_INDEX_WECHAT_NOTICE_ERROR);
            }

            //赋值模板id
            $setting['template_id'] = $model->id;
        } else {
            //删除模板
            if (!empty($model->id)) {
                if (!$model->delete()) {
                    throw new NoticeException(NoticeException::MANAGE_INDEX_WECHAT_NOTICE_DELETE_ERROR);
                }

                //删除公众号模板
                OfficialAccountMessageHelper::deletePrivateTemplate($model->template_id);
            }

            //重置模板id
            $setting['template_id'] = 0;
        }

        // 保存
        $this->setAllSetting($post, $setting, 'wechat');

        return $this->success();
    }

    /**
     * 获取小程序消息通知
     * @return array|int[]|\yii\web\Response
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetWxappNotice()
    {
        $get = RequestHelper::get();
        if (empty($get['type_code'])) {
            throw new NoticeException(NoticeException::MANAGE_INDEX_GET_WXAPP_NOTICE_PARAMS_ERROR);
        }

        //获取缓存
        $result = ShopSettings::get('plugin_notice.send.' . $get['type_code'])['wxapp'] ?: [];
        $result['status'] = (int)$result['status'];
        return $this->success($result);
    }

    /**
     * 小程序消息通知
     * @return array|int[]|\yii\web\Response
     * @throws GuzzleException
     * @throws NoticeException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionWxappNotice()
    {
        $post = RequestHelper::post();
        if (empty($post['type_code'])) {
            throw new NoticeException(NoticeException::MANAGE_INDEX_WXAPP_NOTICE_PARAMS_ERROR);
        }

        $noticeModel = NoticeWxappTemplateModel::findOne([
            'scene_code' => $post['type_code'],
        ]);

        //缓存设置
        $setting = [
            'status' => $post['status'] ?: 0,
        ];

        //如果是开启
        if ($setting['status'] == 1) {
            $defaultNotice = NoticeWxappDefaultTemplateModel::findOne([
                'scene_code' => $post['type_code']
            ]);

            if (empty($defaultNotice)) {
                throw new NoticeException(NoticeException::MANAGE_INDEX_WXAPP_NOTICE_LACK_DEFAULT_TEMPLATE_ERROR);
            }

            $noticeModel = NoticeWxappTemplateModel::findOne(['scene_code' => $post['type_code']]);
            //如果为空 则上传小程序模板库，并添加到本地模板库
            if (empty($noticeModel)) {
                $noticeModel = new NoticeWxappTemplateModel();
                $result = MiniProgramSubscriptionNoticeHelper::addTemplate($defaultNotice->template_id, Json::decode($defaultNotice->kid_list), $defaultNotice->scene_desc);
                if (is_error($result)) {
                    throw new NoticeException(NoticeException::MANAGE_INDEX_WXAPP_NOTICE_ADD_TEMPLATE_ERROR, $result['message']);
                }

                $noticeModel->setAttributes([
                    'scene_code' => $post['type_code'],
                    'title' => $defaultNotice->name,
                    'template_id' => $defaultNotice->template_id,
                    'created_at' => DateTimeHelper::now(),
                    'kid_list' => $defaultNotice->kid_list,
                    'scene_desc' => $defaultNotice->scene_desc,
                    'pri_tmpl_id' => $result['priTmplId'],
                    'content' => $defaultNotice['content'],
                ]);

                if (!$noticeModel->save()) {
                    throw new NoticeException(NoticeException::MANAGE_INDEX_WXAPP_NOTICE_ERROR);
                }
            }

            $setting['template_id'] = $noticeModel->id;
        } else {

            //删除模板
            if (!empty($noticeModel->id)) {
                if (!$noticeModel->delete()) {
                    throw new NoticeException(NoticeException::MANAGE_INDEX_WXAPP_NOTICE_DELETE_ERROR);
                }

                //删除个人小程序模板
                MiniProgramSubscriptionNoticeHelper::deleteTemplate($noticeModel->pri_tmpl_id);
            }


            $setting['template_id'] = 0;
        }


        //设置状态和模板id
        ShopSettings::set('plugin_notice.send.' . $post['type_code'] . '.wxapp', $setting);

        return $this->success();
    }

    /**
     * 获取短信设置
     * @return array|int[]|\yii\web\Response
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetSmsNotice()
    {
        $get = RequestHelper::get();
        if (empty($get['type_code'])) {
            throw new NoticeException(NoticeException::MANAGE_INDEX_GET_SMS_NOTICE_PARAMS_ERROR);
        }

        // 获取配置
        $result = $this->getAllSetting($get, 'sms');

        $result['status'] = (int)$result['status'];

        if ($result['member_id']) {
            $result['member'] = self::getMemberInfoToId($result['member_id']);
        }

        return $this->success($result);
    }

    /**
     * 设置短信
     * @throws NoticeException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSmsNotice()
    {
        $post = RequestHelper::post();
        if (empty($post['type_code'])) {
            throw new NoticeException(NoticeException::MANAGE_INDEX_SMS_NOTICE_PARAMS_ERROR);
        }

        //状态 是否开启消息通知
        $setting['status'] = $post['status'] ?: 0;
        //模板id
        $setting['template_id'] = $post['template_id'];
        // 用户id
        $setting['member_id'] = $post['member_id'];
        // 分销通知 特殊处理
        // 分销通知 特殊处理
        $commissionNotice = [
            'commission_buyer_child_pay',
            'commission_buyer_agent_add_child',
            'commission_buyer_agent_add_child_line',
        ];

        if (in_array($post['type_code'], $commissionNotice)) {
            $setting['commission_level'] = $post['commission_level'];
        }

        // 保存
        $this->setAllSetting($post, $setting, 'sms');

        return $this->success();
    }

    /**
     * 获取拼接好的消息通知配置
     * @param array $get
     * @param string $flag
     * @return array|mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    public function getAllSetting(array $get, string $flag)
    {
        // 单店铺与平台端
        return ShopSettings::get('plugin_notice.send.' . $get['type_code'])[$flag] ?: [];
    }

    /**
     * 设置消息通知的配置
     * @param array $post
     * @param array $setting
     * @param string $flag
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function setAllSetting(array $post, array $setting, string $flag)
    {
        //单店铺于平台
        ShopSettings::set('plugin_notice.send.' . $post['type_code'] . '.' . $flag, $setting);
    }

    /**
     * 查询会员选择器需要的多个或单个会员的信息
     * @param $memberId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberInfoToId($memberId): array
    {
        return MemberModel::getColl([
            'where' => [
                'id' => $memberId,
            ],
            'select' => [
                'id',
                'nickname',
                'avatar',
                'source',
                'created_at',
                'level_id'
            ]
        ], [
            'pager' => false,
            'onlyList' => true,
            'callable' => function (&$row) {
                $row['source_name'] = ClientTypeConstant::getText($row['source']);
                $row['group_name'] = MemberGroupModel::find()
                        ->alias('group')
                        ->leftJoin(MemberGroupMapModel::tableName() . ' group_map', 'group_map.group_id = group.id')
                        ->where(['group_map.member_id' => $row['id']])
                        ->select('group.group_name')
                        ->first()['group_name'] ?? '';
                $levelList = MemberLevelModel::find()
                    ->select('id, level_name')
                    ->indexBy('id')
                    ->get();
                $row['level_name'] = $levelList[$row['level_id']]['level_name'] ?? '';
            }
        ]);
    }

}
