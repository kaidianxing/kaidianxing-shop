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

namespace shopstar\admin\wxapp;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\ClientTypeConstant;
use shopstar\exceptions\wxapp\WxappException;
use shopstar\helpers\RequestHelper;
use shopstar\models\shop\ShopSettings;

/**
 * wxapp
 * Class IndexController
 * @package apps\wxapp\manage
 */
class IndexController extends KdxAdminApiController
{

    /**
     * @return string
     * @author likexin
     */
    public function actionGetSetting()
    {
        $info = ShopSettings::get('channel_setting.wxapp');
        $info['status'] = (string)ShopSettings::get('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WXAPP));
        $notice = ShopSettings::get('plugin_notice.send');

        //获取订阅消息
        $wxappNotice = [
            'buyer_pay' => $notice['buyer_pay'][ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WXAPP)],
            'seller_send' => $notice['seller_send'][ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WXAPP)],
            'auto_send' => $notice['auto_send'][ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WXAPP)],
            'buyer_receive' => $notice['buyer_receive'][ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WXAPP)],
        ];
        return $this->success([
            'data' => $info,
            'notice' => $wxappNotice,
        ]);
    }

    /**
     * 设置
     * @return \yii\web\Response
     * @throws WxappException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetSetting()
    {
        $post = RequestHelper::post();
        if (empty($post)) {
            throw new WxappException(WxappException::CHANNEL_MANAGE_WXAPP_SET_PARAMS_ERROR);
        }


        // 处理跳转链接
        $navigateAppIdList = [];
        if (!empty($post['navigate_appid_list'])) {
            foreach ((array)$post['navigate_appid_list'] as $navigateAppId) {
                if (!isset($navigateAppId['name']) || empty($navigateAppId['name'])) {
                    return $this->error('请填写小程序名称');
                } elseif (!isset($navigateAppId['appid']) || empty($navigateAppId['appid'])) {
                    return $this->error('请填写小程序appid');
                }
                $navigateAppIdList[] = [
                    'name' => $navigateAppId['name'],
                    'appid' => $navigateAppId['appid'],
                ];
            }
        }

        ShopSettings::set('channel.' . ClientTypeConstant::getIdentify(ClientTypeConstant::CLIENT_WXAPP), 1);
        ShopSettings::set('channel_setting.wxapp', [
            'appid' => $post['appid'],
            'app_secret' => $post['app_secret'],
            'maintain' => $post['maintain'],
            'maintain_explain' => $post['maintain_explain'],
            'show_commission' => $post['show_commission'],
            'navigate_appid_list' => $navigateAppIdList,
        ]);

        return $this->success();
    }

}
