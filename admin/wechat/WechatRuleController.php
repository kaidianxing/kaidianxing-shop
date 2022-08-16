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

namespace shopstar\admin\wechat;

use ReflectionException;
use shopstar\bases\KdxAdminApiController;
use shopstar\exceptions\wechat\WechatException;
use shopstar\helpers\RequestHelper;
use shopstar\models\wechat\WechatRuleImagesReplyModel;
use shopstar\models\wechat\WechatRuleKeywordModel;
use shopstar\models\wechat\WechatRuleModel;
use shopstar\models\wechat\WechatRuleTextReplyModel;
use shopstar\constants\wechat\WechatRuleKeywordConstant;
use shopstar\constants\wechat\WechatRuleModuleConstant;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * 自动回复控制器
 * Class WechatRuleController.
 * @package shopstar\admin\wechat
 */
class WechatRuleController extends KdxAdminApiController
{
    /**
     * 保存关注回复数据
     * @return array|int[]|Response
     * @throws WechatException
     * @throws ReflectionException
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAddAttention()
    {
        $containType = RequestHelper::post('containtype');
        $content = RequestHelper::post('content');

        if (empty($containType) || empty($content)) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }

        $data = [
            'name' => '关注自动回复',
            'containtype' => [$containType],
            'reply_setting' => 1,
            'module' => 'wechat',
            'event' => 'subscribe',
            'event_key' => '',
            'ruleKeywordData' => [
                [
                    'keyword' => '',
                    'type' => WechatRuleKeywordConstant::WECHAT_RULE_KEYWORD_TYPE_ALL,
                ]
            ],
            'ruleContent' => [
                [
                    'containtype' => $containType,
                    'content' => $content,
                    'attachment_id' => RequestHelper::post('attachment_id')
                ]
            ]
        ];

        $exists = WechatRuleModel::findOne(['event' => $data['event'], 'status' => 1]);

        // 如果走更新 赋值id
        if ($exists) {
            $data['id'] = $exists->id;
            $data['ruleKeywordData'][0]['id'] = WechatRuleKeywordModel::findOne(['rule_id' => $exists->id])->id;

            if ($containType == 'text') {
                $data['ruleContent'][0]['id'] = WechatRuleTextReplyModel::findOne(['rule_id' => $exists->id])->id;
            }
            if ($containType == 'images') {
                $data['ruleContent'][0]['id'] = WechatRuleImagesReplyModel::findOne(['rule_id' => $exists->id])->id;
            }

            $result = WechatRuleModel::updateAllData($exists->id, $data, 'subscribe');
        } else {
            $result = WechatRuleModel::addAllData($data);
        }

        if (is_error($result)) {
            throw new WechatException(WechatException::SAVE_FAILURE_ERROR);
        }

        return $this->result();
    }

    /**
     * 获取设置的关注内容
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEditAttention()
    {
        $result = WechatRuleModel::getEvent('subscribe');
        // 特殊处理适配格式
        if (isset($result[0][0]['media_id'])) {
            unset($result[0][0]['media_id']);
        }

        return $this->result(['data' => $result[0][0]]);
    }

    /**
     * 关键词列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $result = WechatRuleModel::getColl([
            'alias' => 'rule',
            'leftJoin' => [WechatRuleKeywordModel::tableName() . ' rule_keyword', 'rule_keyword.rule_id=rule.id'],
            'where' => [
                'rule.status' => 1,
                'rule.module' => WechatRuleModuleConstant::WECHAT_MODULE,
                'rule.event' => WechatRuleModuleConstant::WECHAT_MODULE_EVENT_KEYWORD,
            ],
            'select' => [
                'rule.id',
                'rule.name',
                'rule_keyword.keyword',
                'rule.name',
                'rule.containtype',
            ]
        ], [
            'callable' => function (&$row) {
                $row['count'] = 0;
                $containtype = array_column(explode(',', $row['containtype']), null);
                foreach ($containtype as $value){
                    if ($value == 'text') {
                        $textCount = WechatRuleTextReplyModel::getCount($row['id']);
                        $row['count'] += $textCount;
                    }
                    if ($value == 'images') {
                        $imagesCount = WechatRuleImagesReplyModel::getCount($row['id']);
                        $row['count'] += $imagesCount;
                    }
                }
            },
        ]);

        return $this->result(['data' => $result]);
    }

    /**
     * 删除
     * @return array|int[]|Response
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }

        $result = WechatRuleModel::deleteData($id);

        if (is_error($result)) {
            throw new WechatException(WechatException::DELETE_FAILURE_ERROR);
        }

        return $this->success();
    }

    /**
     * 关键词保存
     * @return array|int[]|Response
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $params = $this->process();

        $result = WechatRuleModel::addAllData($params);

        if (!$result) {
            throw new WechatException(WechatException::SAVE_FAILURE_ERROR);
        }

        return $this->result();
    }

    /**
     * 编辑信息
     * @return array|int[]|Response
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::getInt('id');

        if (empty($id)) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }

        $ruleInfo = WechatRuleModel::getInfo($id);

        return $this->result(['data' => $ruleInfo]);
    }

    /**
     * 更新
     * @return array|int[]|Response
     * @throws InvalidConfigException
     * @throws ReflectionException
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate()
    {
        $params = $this->process('update');

        $id = RequestHelper::post('id');

        $result = WechatRuleModel::updateAllData($id, $params);

        if (!$result) {
            throw new WechatException(WechatException::SAVE_FAILURE_ERROR);
        }

        return $this->result();
    }

    /**
     * 验证数据
     * @param string $actionType
     * @return array
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function process(string $actionType = 'add'): array
    {
        $ruleData = [
            'name' => RequestHelper::post('name'),
            'containtype' => RequestHelper::post('containtype'),
            'reply_setting' => RequestHelper::post('reply_setting'),
            'module' => RequestHelper::post('module'),
        ]; // 规则配置
        $ruleKeywordData = RequestHelper::post('rule_keyword_data'); // 关键词数据
        $ruleContent = RequestHelper::post('rule_content'); // 回复内容

        if ($actionType != 'add') {
            $ruleData['id'] = RequestHelper::post('id');
            if (empty($ruleData['id'])) {
                throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
            }
        }

        // 规则名称等不能为空
        if (empty($ruleData['name']) || empty($ruleData['containtype']) || empty($ruleData['reply_setting'])) {
            throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }

        // 关键词默认事件 keyword
        $ruleData['event'] = 'keyword';
        // 关键词不能为空
        foreach ($ruleKeywordData as &$value) {
            if (empty($value['keyword']) || empty($value['type'])) {
                throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
            }
        }

        // 回复内容不能为空
        foreach ($ruleContent as $item) {
            if ($item['containtype'] != 'images' && empty($item['content'])) {
                throw new WechatException(WechatException::CHANNEL_MANAGE_WECHAT_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
            }
        }

        $keywordArray = array_column($ruleKeywordData, 'keyword');
        // 查询关键词是否重复
        if ($actionType == 'add') {
            $result = WechatRuleKeywordModel::find()->andWhere(['in', 'keyword', $keywordArray])->exists();
            if ($result) {
                throw new WechatException(WechatException::KEYWORD_REPEAT);
            }
        } else {
            // 更新方法排除自身关键词重复
            $keywordIdArray = array_column($ruleKeywordData, 'keyword', 'id');
            foreach ($keywordIdArray as $idKey => $idValue) {
                $result = WechatRuleKeywordModel::find()
                    ->where(['keyword' => $idValue])
                    ->andWhere(['<>', 'id', $idKey])->exists();
                if ($result) {
                    throw new WechatException(WechatException::KEYWORD_REPEAT);
                }
            }
        }

        return [
            'name'=>$ruleData['name'],
            'containtype'=>$ruleData['containtype'],
            'reply_setting'=>$ruleData['reply_setting'],
            'event'=>$ruleData['event'],
            'module'=>$ruleData['module'],
            'id'=>$ruleData['id'] ?? '',
            'ruleKeywordData'=>$ruleKeywordData,
            'ruleContent'=>$ruleContent,
        ];
    }
}
