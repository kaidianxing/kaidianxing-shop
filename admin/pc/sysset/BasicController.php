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

namespace shopstar\admin\pc\sysset;

use Exception;
use shopstar\bases\KdxAdminApiController;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\pc\PcLogConstant;
use shopstar\exceptions\pc\PcException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Url;
use yii\web\Response;

class BasicController extends KdxAdminApiController
{
    /**
     * 获取设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $data = [];

        // 推广信息
        $basic = ShopSettings::get('pc.sysset.market');

        $data['site_title'] = $basic['site_title'] ?? '';
        $data['site_logo'] = $basic['site_logo'] ?? '';
        $data['site_description'] = $basic['site_description'] ?? '';
        $data['site_keywords'] = $basic['site_keywords'] ?? '';
        $data['site_analysis_code'] = $basic['site_analysis_code'] ?? '';

        // 默认访问地址
        $data['default_url'] = Url::toRoute('/pc', true);

        // pc渠道状态
        $pcChannelStatus = (string)ShopSettings::get('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_PC));
        $data['channel_status'] = $pcChannelStatus;

        // 微信开放平台
        $wxPc = ShopSettings::get('channel_setting.wxpc');
        $data['logo'] = $wxPc['logo'] ?? '';
        $data['app_id'] = $wxPc['app_id'] ?? '';
        $data['secret'] = $wxPc['secret'] ?? '';
        $data['qrcode_login_status'] = $wxPc['qrcode_login_status'] ?? '';

        return $this->success(['data' => $data]);
    }

    /**
     * 保存pc设置
     * @return array|int[]|Response
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSet()
    {
        // 微信开放平台
        $openPlatformData = [
            'logo' => RequestHelper::post('logo', ''), // 登录页LOGO
            'qrcode_login_status' => RequestHelper::post('qrcode_login_status', ''), // 微信AppId
            'app_id' => RequestHelper::post('app_id', ''), // 微信AppId
            'secret' => RequestHelper::post('secret', ''), // 微信AppSecret
        ];

        // 推广信息
        $marketData = [
            'site_title' => RequestHelper::post('site_title', ''), // 网站标题
            'site_logo' => RequestHelper::post('site_logo', ''), // 网站LOGO
            'site_description' => RequestHelper::post('site_description', ''), // 网站描述
            'site_keywords' => RequestHelper::post('site_keywords', ''), // 网站关键字
            'site_analysis_code' => RequestHelper::post('site_analysis_code', ''), // 工具代码
        ];
        try {
            // pc渠道状态
            $channel_status = RequestHelper::postInt('channel_status', 1);

            ShopSettings::set('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_PC), $channel_status); // 渠道状态
            // 推广信息
            ShopSettings::set('pc.sysset.market', $marketData);
            // 微信开放平台
            ShopSettings::set('channel_setting.wxpc', $openPlatformData);

            // 记录日志
            $logData = array_merge($openPlatformData, $marketData);
            $logData['channel_status'] = $channel_status;

            LogModel::write(
                $this->userId,
                PcLogConstant::PC_SYSSET_BASIC,
                PcLogConstant::getText(PcLogConstant::PC_SYSSET_BASIC),
                '0',
                [
                    'log_data' => $logData,
                    'log_primary' => [
                        '渠道状态' => $logData['channel_status'] == 1 ? '开启' : '关闭',
                        '登录页LOGO' => $logData['logo'],
                        '微信扫码登录' => $logData['qrcode_login_status'] == 1 ? '开启' : '关闭',
                        '微信AppId' => $logData['app_id'],
                        '微信AppSecret' => $logData['secret'],
                        '网站标题' => $logData['site_title'],
                        '网站LOGO' => $logData['site_logo'],
                        '网站描述' => $logData['site_description'],
                        '网站关键字' => $logData['site_keywords'],
                        '工具代码' => $logData['site_analysis_code'],
                    ],
                    'dirty_identify_code' => [
                        PcLogConstant::PC_SYSSET_BASIC
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new PcException(PcException::BASIC_SAVE_FAIL);
        }

        return $this->success();
    }
}
