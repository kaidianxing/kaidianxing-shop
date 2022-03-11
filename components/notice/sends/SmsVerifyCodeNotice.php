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



namespace shopstar\components\notice\sends;


use shopstar\components\notice\bases\SendBase;
use shopstar\components\notice\config\SmsConfig;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\components\noticeComponents\SmsMessageJob;
use shopstar\models\core\CoreSettings;
use shopstar\models\shop\ShopSettings;
use shopstar\models\notice\NoticeSmsTemplateModel;
use yii\helpers\Json;

/**
 * 验证码发送
 * Class SmsVerifyCodeNotice
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\notice\sends
 */
class SmsVerifyCodeNotice extends SendBase implements SendNoticeInterface
{
    /**
     * 发送数据
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $messageData;

    /**
     * 原始发送数据
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $originalMessageData;

    /**
     * 短信配置
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $sms;

    /**
     * 标识
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $sceneCode;

    /**
     * 模板id
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $templateId;

    /**
     * 附加参数
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $options = [];

    /**
     * 配置项
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $config;

    /**
     * 手机号
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $mobile;

    /**
     * 签名
     * @var
     * @author 青岛开店星信息技术有限公司.
     */
    public $signature;

    /**
     * 发送前处理（包括参数验证，模板验证）
     * @return bool
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function sendBefore(): bool
    {
        if (!$this->sms) {

            //获取短信配置项
            $this->sms = NoticeSmsTemplateModel::where(['id' => (int)$this->templateId])->first();
            if (empty($this->sms)) {
                throw new \Exception('缺少模板');
            }
        }
        //$this->signature = $this->sms->sms_sign;

        //获取默认短信类型
        if (!$this->sms['type']) {
            $this->sms['type'] = CoreSettings::get('sms.type', 'aliyun');
        }

        //获取配置
        $this->config = SmsConfig::getConfig([
            'type' => $this->sms['type'],
            'sms_sign' =>  $this->sms['sms_sign'] //适配前端
        ]);

        //判断配置是否为空
        if (is_error($this->config)) {
            throw new \Exception('config is empty');
        }

        return true;
    }

    /**
     * 转化
     * @return mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public function makeTemplateField()
    {
        if (empty($this->sms['data'])) {
            return;
        }

        $content = is_array($this->sms['data']) ? $this->sms['data'] : Json::decode($this->sms['data']);

        if (isset($content['data'])) {
            $content = [$content['data']];
        }

        $this->messageData = $this->makeTpl(Json::encode($content), $this->messageData);
    }

    /**
     * 消息发送（主要处理发送，最好不要有别的操作）
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function sendMessage()
    {
        $key = '_' . $this->sceneCode . '_' . $this->mobile;
        $ret = CacheHelper::get($key);
        if ($ret) {
            return false;
        }

        QueueHelper::push(new SmsMessageJob([
            'job' => [
                'mobile' => $this->mobile,
                'config' => $this->config,
                'template_id' => $this->sms['sms_tpl_id'],
                'data' => $this->messageData,
            ]
        ]));

        CacheHelper::set($key, $this->originalMessageData['code'], 121);
        return true;
    }

    /**
     * 组成发送会员
     * @param array $toUser
     * @param bool $appointTemplateMember
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function makeToUser(array $toUser, bool $appointTemplateMember)
    {
        // TODO: Implement makeToUser() method.
    }
}
