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

namespace shopstar\admin\groups;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\groups\GroupsLogConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\web\Response;

/**
 * 设置接口类
 * Class SettingsController
 * @package shopstar\admin\groups
 * @author likexin
 */
class SettingsController extends KdxAdminApiController
{

    /**
     * 获取设置
     * @return Response
     * @author likexin
     */
    public function actionGet(): Response
    {
        $setting = ShopSettings::get('activity.groups');

        return $this->result([
            'data' => $setting,
        ]);
    }

    /**
     * 提交设置
     * @return Response
     * @author likexin
     */
    public function actionSet(): Response
    {
        $setting = [
            'team_list' => RequestHelper::post('team_list', 0),
            'auto_close' => [
                'open' => RequestHelper::post('auto_close_open', 0),
                'close_time' => RequestHelper::post('auto_close_time', 0),
            ]
        ];

        // 保存设置
        ShopSettings::set('activity.groups', $setting);

        // 记录日志
        LogModel::write(
            $this->userId,
            GroupsLogConstant::CHANGE_SETTING,
            GroupsLogConstant::getText(GroupsLogConstant::CHANGE_SETTING),
            0,
            [
                'log_data' => $setting,
                'log_primary' => [
                    '团列表' => $setting['team_list'] == 0 ? '不显示' : '显示',
                    '自动关闭订单' => $setting['auto_close']['open'] == 0 ? '关闭' : '开启',
                    '自动关闭时间' => $setting['auto_close']['close_tiome'] == 0 ? '-' : '拍下未付款订单' . $setting['auto_close']['close_time'] . '分钟内未付款，自动关闭订单'
                ],
                'dirty_identity_code' => [
                    GroupsLogConstant::CHANGE_SETTING,
                ],
            ]
        );

        return $this->result();
    }

}