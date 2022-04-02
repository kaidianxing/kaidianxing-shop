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

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\sysset\TradeLogConstant;
use shopstar\constants\SyssetTypeConstant;
use shopstar\exceptions\sysset\TradeException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\db\Exception;

/**
 * 交易设置
 * Class TradeController
 * @package shopstar\admin\sysset
 * @author 青岛开店星信息技术有限公司
 */
class TradeController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'get-info'
        ]
    ];

    /**
     * 获取交易设置信息
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetInfo(): \yii\web\Response
    {
        $res = ShopSettings::get('sysset.trade');
        return $this->success($res);
    }

    /**
     * 更新交易设置信息
     * @return \yii\web\Response
     * @throws TradeException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate(): \yii\web\Response
    {
        $post = [
            'close_type' => RequestHelper::post('close_type', '1'), // 未付款订单关闭类型
            'close_time' => RequestHelper::post('close_time', 60), // 未付款订单关闭时间
            'close_notice_type' => RequestHelper::post('close_notice_type', '1'), // 未付款订单关闭通知
            'close_notice_time' => RequestHelper::post('close_notice_time', '0'), // 订单关闭前 x 分钟发送通知
            'auto_receive' => RequestHelper::post('auto_receive', '1'), // 自动收货
            'auto_receive_days' => RequestHelper::post('auto_receive_days', 7), // 自定义自动收货天数
            'stock_warning_state' => RequestHelper::post('stock_warning_state', '0'), // 库存预警通知
            'stock_warning_num' => RequestHelper::post('stock_warning_num', 5), // 库存预警通知少于 x 件
            'invoice' => RequestHelper::post('invoice', ''), // 发票设置
            'order_comment' => RequestHelper::post('order_comment', '0'), // 订单评价
            'show_comment' => RequestHelper::post('show_comment', '0'), // 显示评价
            'comment_audit' => RequestHelper::post('comment_audit', '0'), // 评价审核
            'strengthen_state' => '0',
            'comment_desensitization' => RequestHelper::post('comment_desensitization', '1'), // 评价脱敏
            'auto_comment' => RequestHelper::post('auto_comment', '0'), // 是否开启自动评价
            'auto_comment_day' => RequestHelper::post('auto_comment_day'), // 开启自动评价时间
            'auto_comment_content' => RequestHelper::post('auto_comment_content'), // 自动评价内容
        ];

        // 未付款订单 自定义关闭时间
        if ($post['close_type'] == SyssetTypeConstant::CUSTOMER_CLOSE_ORDER_TIME) {
            // 关闭时间不能为空
            if (empty($post['close_time'])) {
                throw new TradeException(TradeException::CLOSE_TIME_EMPTY);
            }
            // 关闭时间范围不对
            if ($post['close_time'] < 20 || $post['close_time'] > 1440) {
                throw new TradeException(TradeException::CLOSE_TIME_ERROR);
            }
            // 如果发送通知
            if ($post['close_notice_type'] == SyssetTypeConstant::CLOSE_ORDER_NOTICE_SEND) {
                // 订单关闭通知时间不能为空
                if (empty($post['close_notice_time'])) {
                    throw new TradeException(TradeException::CLOSE_NOTICE_TIME_EMPTY);
                }
                // 订单关闭通知时间不能大于自动关闭时间
                if ($post['close_notice_time'] > $post['close_time']) {
                    throw new TradeException(TradeException::CLOSE_NOTICE_TIME_ERROR);
                }
            }
        }

        // 自动收货
        if ($post['auto_receive'] == SyssetTypeConstant::CUSTOMER_AUTO_RECEIVE_TIME) {
            // 自动收货天数不能为空
            if (empty($post['auto_receive_days'])) {
                throw new TradeException(TradeException::AUTO_RECEIVE_TIME_EMPTY);
            }
            // 不能大于30天 或小于0天
            if ($post['auto_receive_days'] > 30 || $post['auto_receive_days'] < 0) {
                throw new TradeException(TradeException::AUTO_RECEIVE_TIME_ERROR);
            }
        }

        // 库存预警通知
        if ($post['stock_warning_state'] == SyssetTypeConstant::STOCK_WARNING_NOTICE_OPEN) {
            // 库存件数不能为空
            if (empty($post['stock_warning_num'])) {
                throw new TradeException(TradeException::STOCK_WANING_DAY_EMPTY);
            }
            // 必须为正整数
            if (!is_numeric($post['stock_warning_num']) || !is_int((int)$post['stock_warning_num']) || $post['stock_warning_num'] < 0) {
                throw new TradeException(TradeException::STOCK_WANING_DAY_ERROR);
            }
        }

        // 开启自动评价
        if ($post['auto_comment'] == 1) {
            if (empty($post['auto_comment']) || $post['auto_comment_day'] < 7 || $post['auto_comment_day'] > 180) {
                throw new TradeException(TradeException::TRADE_AUTO_COMMENT_TIME_ERROR);
            }
            if (empty($post['auto_comment_content'])) {
                throw new TradeException(TradeException::TRADE_AUTO_COMMENT_CONTENT_ERROR);
            }
        }

        try {
            ShopSettings::set('sysset.trade', $post);
            // 发票设置文字
            if ($post['invoice'] == 0) {
                $invoiceText = '';
            } else if ($post['invoice'] == 1) {
                $invoiceText = '纸质发票';
            } else if ($post['invoice'] == 2) {
                $invoiceText = '电子发票';
            } else {
                $invoiceText = '纸质发票;电子发票';
            }

            // 记录日志
            LogModel::write(
                $this->userId,
                TradeLogConstant::TRADE_SET_EDIT,
                TradeLogConstant::getText(TradeLogConstant::TRADE_SET_EDIT),
                '0',
                [
                    'log_data' => $post,
                    'log_primary' => [
                        '未付款订单' => $post['close_type'] == 1 ? '永不关闭' : '自定义关闭时间', // 未付款订单关闭类型
                        '未付款订单关闭时间' => $post['close_time'], // 未付款订单关闭时间
                        '订单关闭消息通知类型' => $post['close_notice_type'] == 1 ? '不发送消息通知' : '自定义通知时间', // 未付款订单关闭通知
                        '订单关闭消息通知时间' => '订单关闭前 ' . $post['close_notice_time'] . ' 分钟发送通知', // 订单关闭前 x 分钟发送通知
                        '自动收货' => $post['auto_receive'] == 1 ? '不自动收货' : '自定义收货时间', // 自动收货
                        '自定义自动收货天数' => $post['auto_receive_days'] . '天', // 自定义自动收货天数
                        '库存预警通知' => $post['stock_warning_state'] == 1 ? '开启' : '关闭', // 库存预警通知
                        '库存预警件数' => '库存预警通知少于 ' . $post['stock_warning_num'] . ' 件', // 库存预警通知少于 x 件
                        '交易增强' => $post['strengthen_state'] == 1 ? '开启' : '关闭', // 交易增强
                        '发票设置' => $invoiceText, // 发票设置
                        '订单评价' => $post['order_comment'] == 1 ? '开启' : '关闭', // 订单评价
                        '显示评价' => $post['show_comment'] == 1 ? '开启' : '关闭', // 显示评价
                        '评价审核' => $post['comment_audit'] == 1 ? '开启' : '关闭', // 评价审核
                        '评价者昵称脱敏' => $post['comment_desensitization'] == 1 ? '开启' : '关闭',
                        '自动默认评价' => $post['auto_comment'] == 1 ? '开启' : '关闭', // 评价审核
                        '自动评价时间' => $post['auto_comment'] == 1 ? '订单完成' . $post['auto_comment_day'] . '天买家未评价的，自动生成默认五星评价' : '-', // 评价审核
                        '默认评价内容' => $post['auto_comment'] == 1 ? $post['auto_comment_content'] : '-', // 评价审核
                    ],
                    'dirty_identify_code' => [
                        TradeLogConstant::TRADE_SET_EDIT
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new TradeException(TradeException::TRADE_SAVE_FAIL);
        }

        return $this->success();
    }

}
