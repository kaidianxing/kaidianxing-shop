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

use shopstar\components\email\EmailComponent;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use shopstar\constants\notice\MailerConstant;
use shopstar\constants\notice\NoticeLogConstant;
use shopstar\exceptions\notice\NoticeException;
use shopstar\bases\KdxAdminApiController;

/**
 * 邮箱设置
 * Class MailerController
 * @author
 * @package apps\notice\manage
 */
class MailerController extends KdxAdminApiController
{
    /**
     * 获取邮箱配置
     * @author 青岛开店星信息技术有限公司
     * @return array|mixed|string
     */
    public function actionGet()
    {
        $result = ShopSettings::get('mailer');
        return $this->result($result);
    }

    /**
     * 保存设置
     * @author 青岛开店星信息技术有限公司
     * @return array|\yii\web\Response
     */
    public function actionSet()
    {
        $params = RequestHelper::post();
        $oldSetting = ShopSettings::get('mailer');

        $oldSetting['status'] = $params['status'];
        $oldSetting['type'] = $params['type'];
        // 更改参数入库
        $oldSetting[$params['type']] = [
            'host' => $params['type'] == MailerConstant::MAILER_TYPE_QQ ? 'smtp.qq.com' : ($params['type'] == MailerConstant::MAILER_TYPE_163 ? 'smtp.163.com' : $params[$params['type']]['host']),
            'port' => $params['type'] == MailerConstant::MAILER_TYPE_QQ ? '465' : ($params['type'] == MailerConstant::MAILER_TYPE_163 ? '465' : $params[$params['type']]['port']),
            'username' => $params[$params['type']]['username'] ?? '',
            'shop_name' => $params[$params['type']]['shop_name'] ?? '',
            'password' => $params[$params['type']]['password'] ?? '',
            'mailer_title' => $params[$params['type']]['mailer_title'] ?? '',
            'ssl' => $params[$params['type']]['ssl'] ?: 0,
            'test_address' => $params[$params['type']]['test_address'] ?? '',
        ];

        ShopSettings::set('mailer', $oldSetting);

        // 日志
        $logPrimary = [
            '邮箱开启' => $params['status'] == 0 ? '关闭' : '开启'
        ];
        if ($params['status'] == 1) {
            $logPrimary['邮箱类型'] = $params['type'] == MailerConstant::MAILER_TYPE_QQ ? 'QQ邮箱' : ($params['type'] == MailerConstant::MAILER_TYPE_163 ? '网易邮箱' : '自定义');
            $logPrimary['发件人邮箱地址'] = $params[$params['type']]['username'];
            $logPrimary['发件人名称'] = $params[$params['type']]['shop_name'];
            $logPrimary['smtp身份验证码'] = $params[$params['type']]['password'];
            $logPrimary['是否使用安全链接'] = $params[$params['type']]['ssl'] == 0 ? '关闭' : '开启';
        }

        LogModel::write(
            $this->userId,
            NoticeLogConstant::NOTICE_MAILER,
            NoticeLogConstant::getText(NoticeLogConstant::NOTICE_MAILER),
            0,
            [
                'log_data' => $oldSetting,
                'log_primary' => $logPrimary
            ]
        );

        return $this->result(['data' => $oldSetting]);
    }

    /**
     * 测试发送邮件接口
     * @throws NoticeException
     * @author 青岛开店星信息技术有限公司
     * @return array|\yii\web\Response
     */
    public function actionTestSend()
    {
        $params = RequestHelper::post();
        if (empty($params) || empty($params[$params['type']]['test_address'])) {
            throw new NoticeException(NoticeException::MANAGE_INDEX_GET_WECHAT_NOTICE_PARAMS_ERROR);
        }
        $body = EmailComponent::getTemplate();
        EmailComponent::sendMessage($params[$params['type']]['test_address'], $body, $params);

        return $this->result();
    }

}
