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

namespace shopstar\admin\sysset;

use shopstar\constants\log\sysset\RefundLogConstant;

use shopstar\constants\SyssetTypeConstant;
use shopstar\exceptions\sysset\RefundException;
 
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use shopstar\bases\KdxAdminApiController;
use yii\db\Exception;

/**
 * 维权设置
 * Class RefundController
 * @package app\controllers\manage\sysset
 */
class RefundController extends KdxAdminApiController
{
    public $configActions = [
        'postActions' => [
            'update',
        ]
    ];
    /**
     * 获取维权配置信息
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetInfo()
    {
        $res = ShopSettings::get('sysset.refund');

        return $this->success($res);
    }


    /**
     * 更新维权配置信息
     * @return \yii\web\Response
     * @throws RefundException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate()
    {
        $post = [
            'apply_type' => RequestHelper::post('apply_type', '1'), // 售后维权申请
            'apply_days' => RequestHelper::post('apply_days', '0'), // 退款天数
            'refund_info' => RequestHelper::post('refund_info', ''), // 售后维权说明
            'single_refund_enable' => RequestHelper::post('single_refund_enable', '1'), // 是否开启单品维权
            'timeout_cancel_refund' => RequestHelper::post('timeout_cancel_refund', '0'), // 超时取消维权
            'timeout_cancel_refund_days' => RequestHelper::post('timeout_cancel_refund_days', '0'), // 超时取消维权天数
        ];

        if ($post['apply_type'] == SyssetTypeConstant::CUSTOMER_REFUND_TIME) {
            // 天数不能为空
            if (empty($post['apply_days'])) {
                throw new RefundException(RefundException::REFUND_DAYS_EMPTY);
            }
            // 天数必须为正整数
            if (!is_numeric($post['apply_days']) || !is_int((int)$post['apply_days']) || $post['apply_days'] < 0) {
                throw new RefundException(RefundException::REFUND_DAYS_ERROR);
            }

        }
        // 超时取消维权
        if ($post['timeout_cancel_refund'] == SyssetTypeConstant::CUSTOMER_TIMEOUT_CANCEL_REFUND_TIME) {
            if (empty($post['timeout_cancel_refund_days'])) {
                throw new RefundException(RefundException::REFUND_DAYS_EMPTY);
            }
            if (!is_numeric($post['timeout_cancel_refund_days']) || !is_int((int)$post['timeout_cancel_refund_days']) || $post['timeout_cancel_refund_days'] < 0) {
                throw new RefundException(RefundException::REFUND_DAYS_ERROR);
            }
        }
        try {
            ShopSettings::set('sysset.refund', $post);
            // 日志
            LogModel::write(
                $this->userId,
                RefundLogConstant::REFUND_SET_EDIT,
                RefundLogConstant::getText(RefundLogConstant::REFUND_SET_EDIT),
                '0',
                [
                    'log_data' => $post,
                    'log_primary' => [
                        '售后维权申请' => $post['apply_type'] == 1 ? '完成订单不允许售后' : '自定义时间', // 售后维权申请
                        '退款天数' => '订单完成天 ' . $post['apply_days'] . ' 内可发起售后维权', // 退款天数
                        '售后维权说明' => $post['refund_info'], // 售后维权说明
                        '单品退换货' => $post['single_refund_enable'] == 1 ? '开启' : '关闭', // 是否开启单品维权
                        '超时取消维权' => $post['timeout_cancel_refund'] == 0 ? '永不关闭' : '自定义关闭时间', // 超时取消维权
                        '超时取消维权天数' => '商家同意退货退款/换货申请后' . $post['timeout_cancel_refund_days'] . '天内买家未提交快递单号的，自动取消售后维权', // 超时取消维权天数
                    ],
                    'dirty_identify_code' => [
                        RefundLogConstant::REFUND_SET_EDIT
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new RefundException(RefundException::REFUND_SAVE_FAIL);
        }

        return $this->success();
    }
}