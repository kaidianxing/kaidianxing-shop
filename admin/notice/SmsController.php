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

use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use shopstar\bases\KdxAdminApiController;
use shopstar\components\notice\config\SmsConfig;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\notice\NoticeLogConstant;
use shopstar\exceptions\notice\NoticeSmsException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\core\CoreSettings;
use shopstar\models\log\LogModel;
use shopstar\models\notice\NoticeSmsTemplateModel;
use shopstar\models\shop\ShopSettings;
use yii\db\Exception;
use yii\helpers\Json;
use yii\web\Response;

/**
 * 短信设置
 * Class SmsController
 * @package shopstar\admin\notice
 */
class SmsController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'add',
            'edit',
            'change-states',
            'delete',
            'test-send',
            'edit-set',
            'edit-code',
        ]
    ];

    /**
     * 脱敏key
     * @var string[]
     */
    private $secretKeys = ['access_key_secret', 'access_key_id'];

    /**
     * 自定义短信库列表
     * @return array|int[]|Response
     * @throws \ReflectionException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $data = ShopSettings::get('sysset.mall.basic');
        $shop_name = $data['name'];
        $list = NoticeSmsTemplateModel::getColl([
            'alias' => 'sms',
            'searchs' => [
                ['sms.name', 'like', 'name']
            ],
            'select' => [
                'sms.id',
                'sms.name',
                'sms.sms_tpl_id',
                'sms.scene_code',
                'sms.content',
                'sms.state',
                'sms.audit',
                'sms.created_at'
            ],
            'orderBy' => ['sms.created_at' => SORT_DESC]
        ], [
            'pager' => true,
            'callable' => function (&$row) use ($shop_name) {
                // 如果有扩展字段解码
                //if (is_null($row['shop_name'])) {
                $row['shop_name'] = $shop_name;
                //}
            }
        ]);

        if (!empty($list['list'])) {
            //场景值列表
            $sceneCode = NoticeComponent::getTypeCodeByClient();
            foreach ($list['list'] as &$item) {
                foreach ($sceneCode as $sceneCodeItem) {
                    if ($sceneCodeItem['scene_code'] == $item['scene_code']) {
                        $item['scene'] = $sceneCodeItem['title'];
                    }
                }
            }
        }
        return $this->success($list);
    }

    /**
     * 获取短信场景列表
     * @return array|int[]|\yii\web\Response
     * @throws \ReflectionException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetScene()
    {
        $list = NoticeComponent::getTypeCodeByClient();
        return $this->result(['list' => $list]);
    }


    /**
     * 详情
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            return $this->error('缺少参数');
        }

        $template = NoticeSmsTemplateModel::findOne($id);
        if (empty($template)) {
            return $this->error('未找到模板');
        }

        $template = $template->toArray();
        $template['data'] = $template['data'] ? Json::decode($template['data']) : '';

        return $this->success($template);
    }

    /**
     * 编辑
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::postInt('id');
        $res = NoticeSmsTemplateModel::saveSms($id);

        // 日志
        $data = Json::decode($res->data);
        $temp_code = [];
        $dataData = [];
        if (is_array($data['temp_code']) && !empty($data['temp_code'])) {
            foreach ($data['temp_code'] as $item) {
                if (is_array($item)) {
                    $temp_code[] = implode('、', $item);
                }
            }
        }
        if (is_array($data['data'])) {
            $dataData = implode('、', array_keys($data['data']));
        }

        LogModel::write(
            $this->userId,
            NoticeLogConstant::NOTICE_TEMPLATE_SMS_ADD,
            NoticeLogConstant::getText(NoticeLogConstant::NOTICE_TEMPLATE_SMS_ADD),
            $res->id,
            [
                'log_data' => $res->attributes,
                'log_primary' => [
                    '模板名称' => $res->name,
                    '发送条件' => NoticeTypeConstant::getText($res->scene_code),
                    '模板内容' => $res->content,
                    '数据值' => '短信模板变量：' . $dataData . '，商城变量：' . implode('，', $temp_code),
                ]
            ]
        );

        return $this->result($res);

        //return $this->success();
    }

    /**
     * 新增
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        return $this->actionEdit();
    }

    /**
     * 批量/启用禁用短信模板
     * @throws NoticeSmsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeState()
    {
        $id = RequestHelper::post('id');
        $state = RequestHelper::post('state');

        if (empty($id) || $state == '') {
            throw new NoticeSmsException(NoticeSmsException::SMS_CHANGE_STATE_PARAMS_ERROR);
        }

        try {
            NoticeSmsTemplateModel::updateAll(['state' => $state], ['id' => $id]);
        } catch (\Throwable $exception) {
            throw new NoticeSmsException(NoticeSmsException::SMS_CHANGE_STATE_FAIL);
        }

        return $this->success();
    }

    /**
     * 删除短信模板
     * @throws NoticeSmsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::post('id');

        if (empty($id)) {
            throw new NoticeSmsException(NoticeSmsException::SMS_DELETE_PARAMS_ERROR);
        }

        $sms = NoticeSmsTemplateModel::findOne(['id' => $id]);
        if (empty($sms)) {
            return error('模板不存在');
        }

        // 删除
        try {
            NoticeSmsTemplateModel::deleteAll(['id' => $id]);

            // 日志
            $data = Json::decode($sms->data);
            $temp_code = [];
            foreach ($data['temp_code'] as $item) {
                $temp_code[] = implode('、', $item);
            }

            LogModel::write(
                $this->userId,
                NoticeLogConstant::NOTICE_TEMPLATE_SMS_DEL,
                NoticeLogConstant::getText(NoticeLogConstant::NOTICE_TEMPLATE_SMS_DEL),
                $sms->id,
                [
                    'log_data' => $sms->attributes,
                    'log_primary' => [
                        '模板名称' => $sms->name,
                        '发送条件' => NoticeTypeConstant::getText($sms->scene_code),
                        '模板内容' => $sms->content,
                        '数据值' => '短信模板变量：' . implode('、', array_keys($data['data'])) . '，商城变量：' . implode('，', $temp_code),
                    ]
                ]
            );
        } catch (\Throwable $exception) {

        }

        return $this->success();
    }


    /**
     * 获取测试发送短信数据
     * @return array|int[]|Response
     * @throws NoticeSmsException
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSendData()
    {
        $post = RequestHelper::post();
        if (!ValueHelper::isMobile((string)$post['mobile'])) {
            return $this->error('请输入正确手机号');
        }
        if (empty($post['data'])) {
            return $this->error('输入发送内容');
        }

        // 初始化配置
        $config = SmsConfig::getConfig([
            'type' => $post['type'],
            'sms_sign' => $post['sms_sign']
        ]);

        if (is_error($config)) {
            return $this->error($config['message']);
        }

        $easySms = new EasySms($config);

        try {
            $easySms->send($post['mobile'], [
                'template' => $post['sms_tpl_id'],
                'data' => $post['data']
            ]);
        } catch (NoGatewayAvailableException $exception) {

            if ($exception->getExceptions()[$post['type']]->raw['Code'] == 'isv.INVALID_PARAMETERS') {

                throw new NoticeSmsException(NoticeSmsException::SMS_THE_SEND_IS_ERROR);

            } else {
                $exp = $exception->getExceptions()[$post['type']];
                if ($exp->getCode() == 404) {
                    return $this->error("请设置正确的短信Access Key 和 Secret");
                } else {
                    return $this->error($exp->getMessage());
                }
            }
        }

        return $this->success();
    }

    /**
     * 获取短信AccessKey信息
     * @return array|int[]|Response
     * @author: Terry
     */
    public function actionGetAccessKey()
    {
        $settings = CoreSettings::get('sms');
        return $this->result([
            'settings' => [
                'type' => (string)$settings['type'],
                // 对阿里云数据进行去敏
                'aliyun' => (array)StringHelper::doSecret($settings['aliyun'], $this->secretKeys),
                'juhe' => (array)$settings['juhe'],
            ],
        ]);
    }

    /**
     * 保存AccessKey信息
     * @return array|int[]|Response
     * @throws Exception
     * @author: Terry
     */
    public function actionSetAccessKey()
    {
        // 获取设置
        $settings = CoreSettings::get('sms');

        $postData = [
            'type' => RequestHelper::post('type'),
            'aliyun' => [
                'access_key_secret' => RequestHelper::post('aliyun.access_key_secret'),
                'access_key_id' => RequestHelper::post('aliyun.access_key_id'),
            ],
            'juhe' => [
                'app_key' => RequestHelper::post('juhe.app_key'),
            ],
        ];

        // 对比脱敏数据, 有变动时用新数据, 否则用旧数据
        $postData['aliyun'] = StringHelper::compareSecret($settings['aliyun'], $postData['aliyun'], $this->secretKeys);
        if (is_error($postData['aliyun'])) {
            return $this->error('阿里云短信参数错误');
        }

        // 接收前端传入参数
        $settings = array_merge($settings, $postData);

        // 保存设置
        CoreSettings::set('sms', $settings);

        return $this->result();
    }


    /**
     * 修改配置
     * @return Response
     * @throws NoticeSmsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEditSet(): Response
    {
        $data = [
            'aliyun' => RequestHelper::post('aliyun', '0'), // 阿里云设置
            'aliyun_keyid' => RequestHelper::post('aliyun_keyid', ''), // 阿里云keyid
            'aliyun_keysecret' => RequestHelper::post('aliyun_keysecret', ''), // 阿里云aliyun_keysecret
            'juhe' => RequestHelper::post('juhe', '0'), // 聚合
            'juhe_key' => RequestHelper::post('juhe_key', ''), // 聚合
        ];
        // 阿里云
        if ($data['aliyun'] == 1 && (empty($data['aliyun_keyid']) || empty($data['aliyun_keysecret']))) {
            // 阿里云设置不能为空
            throw new NoticeSmsException(NoticeSmsException::ALIYUN_SET_NOT_EMPTY);
        }
        // 聚合
        if ($data['juhe'] == 1 && empty($data['juhe_key'])) {
            // 聚合设置不能为空
            throw new NoticeSmsException(NoticeSmsException::JUHE_SET_NOT_EMPTY);
        }

        try {
            ShopSettings::set('sysset.smsset', $data);
        } catch (Exception $exception) {
            throw new NoticeSmsException(NoticeSmsException::SMS_SET_SAVE_FAIL);
        }

        return $this->success();
    }


    /**
     * 验证码设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCode()
    {
        $data = ShopSettings::get('sysset.smscode');
        $smsSet = ShopSettings::get('sysset.smsset');
        // 短信模板
        if (!empty($smsSet)) {
            $data['template_sms'] = NoticeSmsTemplateModel::getAllTemplate($smsSet);
        }

        return $this->success($data);
    }

    /**
     * 修改验证码设置
     * @return Response
     * @throws NoticeSmsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEditCode(): Response
    {
        $data = [
            'sms_code' => RequestHelper::post('sms_code', '0'), // 短信验证码
            'user_reg' => RequestHelper::post('user_reg', ''), // 用户注册短信模板
            'retrieve_pwd' => RequestHelper::post('retrieve_pwd', ''), // 找回密码模板
            'change_bind' => RequestHelper::post('change_bind', ''), // 修改密码模板
            'bind' => RequestHelper::post('bind', ''), // 绑定手机模板
            'login_code' => RequestHelper::post('login_code', ''), // 用户登录
        ];
        try {
            ShopSettings::set('sysset.smscode', $data);
        } catch (Exception $exception) {
            throw new NoticeSmsException(NoticeSmsException::CODE_SAVE_FAIL);
        }

        return $this->success();
    }


    /**
     * 获取配置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSet()
    {
        $item = ShopSettings::get('sysset.smsset');
        return $this->success(['smsset' => $item]);
    }


}
