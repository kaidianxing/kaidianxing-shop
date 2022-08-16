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

namespace shopstar\admin\creditShop;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\creditShop\CreditShopLogConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\web\Response;

/**
 * 积分商城基础设置控制器
 * Class SettingController.
 * @package shopstar\admin\creditShop
 */
class SettingController extends KdxAdminApiController
{
    /**
     * 需要控制的Actions
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'get'
        ],
    ];

    /**
     * 获取设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $set = ShopSettings::get('credit_shop');

        return $this->result(['data' => $set]);
    }

    /**
     * 保存设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSet()
    {
        $data = [
            'status' => RequestHelper::post('status'),
            'refund_type' => RequestHelper::post('refund_type'),
            'finish_order_refund_type' => RequestHelper::post('finish_order_refund_type'),
            'finish_order_refund_days' => RequestHelper::post('finish_order_refund_days'),
            'refund_rule' => RequestHelper::post('refund_rule')
        ];

        ShopSettings::set('credit_shop', $data);

        // 日志
        LogModel::write(
            $this->userId,
            CreditShopLogConstant::SETTING,
            CreditShopLogConstant::getText(CreditShopLogConstant::SETTING),
            '0',
            [
                'log_data' => $data,
                'log_primary' => [
                    '积分商城' => $data['status'] ? '开启' : '关闭',
                    '售后维权' => $data['refund_type'] == 0 ? '读取系统设置' : '自定义',
                    '已完成订单' => $data['refund_type'] ? ($data['finish_order_refund_type'] ? '允许售后' : '不允许售后') : '-',
                    '订单完成后' => ($data['refund_type'] && $data['finish_order_refund_type']) ? ($data['finish_order_refund_days'].'天') : '-',
                    '退款规则' => $data['refund_rule'] == 0 ? '全部退款' : '不退积分',
                ],
                'dirty_identify_code' => [
                    CreditShopLogConstant::SETTING,
                ],
            ]
        );

        return $this->success();
    }
}
