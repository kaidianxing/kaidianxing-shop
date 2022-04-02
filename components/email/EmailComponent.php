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

namespace shopstar\components\email;

use shopstar\constants\notice\MailerConstant;
use shopstar\models\shop\ShopSettings;
use shopstar\models\virtualAccount\VirtualAccountDataModel;
use shopstar\models\virtualAccount\VirtualAccountModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use yii\base\Component;
use yii\helpers\Json;

/**
 * Email组件
 * Class EmailComponent
 * @package shopstar\components\email
 * @author 青岛开店星信息技术有限公司
 */
class EmailComponent extends Component
{

    public static $body = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Title</title></head><body><div class="email-template" style="background-color:#fff"><div class="email-shop" style="clear:both;padding-top:16px;padding-bottom:16px;padding-left:20px;height:48px;line-height:80px;background-color:#f4f6f8"><img src="{%shopLogo%}" alt="" style="float:left;width:46px;height:46px;border-radius:50%;border:1px solid #e9edef"> <span style="float:left;margin-left:10px;line-height:48px;color:#262b30;font-size:20px;font-weight:700">{%shopName%}</span></div><div class="email-content" style="padding:20px"><div style="padding-left:20px"><div style="margin-bottom:20px;color:#262b30;font-size:12px;font-weight:700">尊敬的用户：</div><div style="color:#262b30;font-size:12px">感谢您使用【<span style="color:#262b30;font-size:12px;font-weight:700">{%shopName%}</span>】</div><div style="color:#262b30;font-size:12px">以下是您购买虚拟卡密商品的商品信息：</div></div>{%messageBody%}</div><div class="email-footer"></div></div></body></html>';

    public static $messageBody = '<div class="email-info"><div class="item"><div style="padding:0 20px 30px"><div style="margin-bottom:10px;color:#262b30;font-size:12px;font-weight:700;margin-top:15px">{%virtualAccount%}</div>{%messageKeyBody%}<div style="color:#262b30;font-size:12px;font-weight:700">{%useAddressTitle%}：<a style="text-decoration:none" href="{%useAddressAddress%}">{%useAddressAddress%}</a></div><div style="color:#262b30;font-size:12px"><span style="font-weight:700">{%useDescriptionTitle%}：</span><span>{%useDescriptionRemark%}</span></div></div><div class="line" style="margin-bottom: 30px;width:100%;height:1px;border-bottom:1px dashed #b8b9bd"></div></div></div>';

    public static $messageKeyBody = '<div><span style="line-height:20px;color:#262b30;font-size:12px">{%virtualAccountkey%}：</span><span style="line-height:20px;color:#262b30;font-size:12px">{%virtualAccountDataValue%}</span></div>';

//    public static $line = '<div class="line" style="width: 100%; height: 1px; border-bottom: 1px dashed #b8b9bd;"></div>';

    /**
     * 获取设置
     * @return array|mixed|string
     */
    private static function getSettings()
    {
        return ShopSettings::get('mailer');
    }

    /**
     * 发送消息
     * @param string $to 发送人
     * @param string $body 内容
     * @return array|bool
     */
    public static function sendMessage(string $to, string $body, $options = [])
    {
        if (count($options) <= 0) {
            $settings = self::getSettings();
        } else {
            $settings = $options;
        }
        // 获取当前开启的配置
        $type = $settings['type'];
        $settings = $settings[$settings['type']];

        if (empty($settings) || empty($settings['host']) || empty($settings['port']) || empty($settings['username']) || empty($settings['password'])) {
            return error('设置参数错误');
        }

        // 替换ssl是否开启的端口
        switch ($type) {
            case MailerConstant::MAILER_TYPE_QQ:
                if (!$settings['ssl']) {
                    $port = 587;
                }
                break;
            case MailerConstant::MAILER_TYPE_163:
                if (!$settings['ssl']) {
                    $port = 25;
                }
                break;
        }

        // Create the Transport
        $transport = (new Swift_SmtpTransport($settings['host'], $port ?? $settings['port'], $settings['ssl'] ? 'ssl' : 'tls'))
            ->setUsername($settings['username'])
            ->setPassword($settings['password']);

        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);

        // Create a message
        $message = (new Swift_Message($settings['mailer_title']) ?: $settings['shop_name'])
            ->setFrom([$settings['username'] => $settings['shop_name']])
            ->setTo($to)
            ->setBody($body, 'text/html');
        try {
            // 执行发送
            $result = $mailer->send($message, $failedRecipients);
            if (!$result) {
                return current($failedRecipients);
            }
        } catch (\Exception $exception) {
            return error($exception->getMessage());
        }

        return true;
    }

    /**
     * 获取卡密替换模板
     * @param int $id
     * @return mixed|string
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getTemplate($id = 0)
    {
        if ($id == 0) {
            return '';
        }

        $shopInfo = ShopSettings::get('sysset.mall.basic');
        $virtualAccountData = VirtualAccountDataModel::find()->where(['id' => $id])->asArray()->all();
        $virtualAccount = VirtualAccountModel::findOne(['id' => $virtualAccountData[0]['virtual_account_id']]);
        $config = Json::decode($virtualAccount->config);
        $body = self::$body;

        $imageUrl = CoreAttachmentService::getRoot();

        // 商家logo
        $body = str_replace('{%shopLogo%}', $imageUrl . $shopInfo['logo'] ?: '', $body);

        // 商家名称
        $body = str_replace('{%shopName%}', $shopInfo['name'] ?: '', $body);

        foreach ($virtualAccountData as $key => $value) {
            $jsonValue = Json::decode($value['data']);
            $messageBody = self::$messageBody;
            // 卡密信息
            $messageBody = str_replace('{%virtualAccount%}', '卡密信息(' . ($key + 1) . ')', $messageBody);

            foreach ($config as $configIndex => $configItem) {
                $messageKeyBody = self::$messageKeyBody;
                // key
                $messageKeyBody = str_replace('{%virtualAccountkey%}', $configItem['key'], $messageKeyBody);
                // value
                $messageKeyBody = str_replace('{%virtualAccountDataValue%}', $jsonValue['value' . ($configIndex + 1)], $messageKeyBody);
                $newMessageKeyBody .= $messageKeyBody;
            }

            // 合并key:value的值
            $messageBody = str_replace('{%messageKeyBody%}', $newMessageKeyBody, $messageBody);
            $newMessageKeyBody = '';

            // 使用说明title
            $messageBody = str_replace('{%useDescriptionTitle%}', $virtualAccount->use_description ? $virtualAccount->use_description_title : '使用说明', $messageBody);
            // 使用说明内容
            $messageBody = str_replace('{%useDescriptionRemark%}', $virtualAccount->use_description ? $virtualAccount->use_description_remark : '', $messageBody);
            // 使用地址title
            $messageBody = str_replace('{%useAddressTitle%}', $virtualAccount->use_address ? $virtualAccount->use_address_title : '使用地址', $messageBody);
            // 使用地址内容
            $messageBody = str_replace('{%useAddressAddress%}', $virtualAccount->use_address ? $virtualAccount->use_address_address : '', $messageBody);
            // 分割线
//            $messageBody = str_replace('{%line%}', self::$line, $messageBody);

            $newMessageBody .= $messageBody;
        }

        // 替换循环卡密信息进模板
        return str_replace('{%messageBody%}', $newMessageBody, $body);
    }


}