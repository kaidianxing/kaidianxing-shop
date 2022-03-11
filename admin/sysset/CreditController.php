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

use shopstar\constants\log\sysset\CreditLogConstant;
use shopstar\constants\SyssetTypeConstant;
use shopstar\exceptions\sysset\CreditException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberCreditRecordModel;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;
use shopstar\bases\KdxAdminApiController;
use yii\db\Exception;

/**
 * 积分余额设置
 * Class CreditController
 * @package app\controllers\manage\sysset
 */
class CreditController extends KdxAdminApiController
{
    public $configActions = [
        'postActions' => [
            'update',
        ],
        'allowPermActions' => [
            'get-info'
        ]
    ];
    /**
     * 获取积分余额配置信息
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetInfo()
    {
        // 原系统设置
        $res = ShopSettings::get('sysset.credit');
        // 过滤部分字段
        $res = ArrayHelper::filter($res, ['credit_text', 'credit_limit_type', 'credit_limit', 'give_credit_status', 'give_credit_type', 'give_credit_scale', 'give_credit_settle_day']);
        
        // 原抵扣设置
        $deductSet = ShopSettings::get('sale.basic.deduct');
        // 过滤设置
        $deductSet = ArrayHelper::filter($deductSet, ['credit_state', 'credit_num', 'basic_credit_num']);
        return $this->success(array_merge($res, $deductSet));
    }
    
    /**
     * 更新积分配置信息
     * @return \yii\web\Response
     * @throws CreditException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate()
    {
        $post = [
            'credit_text' => '积分', // 积分文字
            'credit_limit_type' => RequestHelper::post('credit_limit_type', '1'), // 积分上限设置
            'credit_limit' => RequestHelper::post('credit_limit', '0'), // 积分上限数量
            
            'give_credit_status' => RequestHelper::post('give_credit_status', '0'), // 消费得积分
            'give_credit_type' => RequestHelper::post('give_credit_type', '0'), // 消费得积分方式
            'give_credit_scale' => RequestHelper::post('give_credit_scale', '0'), // 消费得积分比例
            'give_credit_settle_day' => RequestHelper::post('give_credit_settle_day', '0'), // 消费得积分结算天数
        ];
        
        // 积分抵扣换位置 兼容老数据
        $deductData = [
            'credit_state' => RequestHelper::post('credit_state', 0), // 积分抵扣
            'credit_num' => RequestHelper::post('credit_num', 0), // 积分抵扣比例 (元)
            'basic_credit_num' => RequestHelper::post('basic_credit_num', 0), // 积分抵扣比例 (积分)
        ];
        
        // 积分定义文字不能为空
        if (empty($post['credit_text'])) {
            throw new CreditException(CreditException::CREDIT_TEXT_EMPTY);
        }
        // 积分上限不能为空
        if ($post['credit_limit_type'] == SyssetTypeConstant::CUSTOMER_CREDIT_LIMIT && empty($post['credit_limit'])) {
            throw new CreditException(CreditException::MOST_CREDIT_EMPTY);
        }
        // 积分上限只能输入正整数
        if ($post['credit_limit_type'] == SyssetTypeConstant::CUSTOMER_CREDIT_LIMIT
            && (!is_numeric($post['credit_limit']) || !is_int((int)$post['credit_limit']) || $post['credit_limit'] <= 0)) {
            
            throw new CreditException(CreditException::MOST_CREDIT_NOT_INT);
        }
        // 积分限额最大支持9999999
        if ($post['credit_limit'] > 9999999) {
            throw new CreditException(CreditException::MOST_CREDIT_BIG);
        }
        
        // 消费得积分
        if ($post['give_credit_status'] == 1) {
            if ($post['give_credit_type'] == 0 && empty($post['give_credit_scale'])) {
                return $this->error('消费得积分设置错误');
            }
        }
    
        // 抵扣设置
        $deductData['credit_num'] = bcadd($deductData['credit_num'], 0, 2);
        if (bccomp($deductData['credit_num'], 0.01, 2) < 0 || bccomp($deductData['basic_credit_num'], 0.01, 2) < 0) {
            return $this->error('积分抵扣比例错误');
        }


        try {
            // 原设置
            $res = ShopSettings::get('sysset.credit');
            $post = array_merge($res, $post);
            ShopSettings::set('sysset.credit', $post);
            
            // 兼容老数据
            $deductSet = ShopSettings::get('sale.basic.deduct');
            $deductData = array_merge($deductSet, $deductData);
            ShopSettings::set('sale.basic.deduct', $deductData);
            
            // 日志
            LogModel::write(
                $this->userId,
                CreditLogConstant::CREDIT_SET_EDIT,
                CreditLogConstant::getText(CreditLogConstant::CREDIT_SET_EDIT),
                '0',
                [
                    'log_data' => $post,
                    'log_primary' => [
                        '积分文字' => $post['credit_text'], // 积分文字
                        '积分上限设置' => $post['credit_limit_type'] == 1 ? '不限制' : '自定义', // 积分上限设置
                        '积分上限数量' => '最多可获得'.$post['credit_limit'].'积分', // 积分上限数量
                        
                        '消费得积分' => $post['give_credit_status'] == 1 ? '开启' : '关闭',
                        '消费得积分结算方式' => $post['give_credit_type'] == 0 ? '按订单实付金额' : '按商品',
                        '每笔订单实付金额' => $post['give_credit_type'] == 0 ? $post['give_credit_scale'].'%' : '-',
                        '结算时间' => '订单完成后'.$post['give_credit_settle_day'].'天',
    
                        '积分抵扣' => $deductData['credit_state'] == 1 ? '开启' : '关闭',
                        '积分抵扣比例' => $deductData['credit_state'] == 1 ? $deductData['basic_credit_num'].' 积分抵扣 ' . $deductData['credit_num'] . ' 元' : '-',
                    ],
                    'dirty_identify_code' => [
                        CreditLogConstant::CREDIT_SET_EDIT,
                    ],
                ]
            );
        } catch (Exception $exception) {
            throw new CreditException(CreditException::CREDIT_SAVE_FAIL);
        }
        
        return $this->success();
    }
    
    /**
     * 获取数据
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetStatistics()
    {
        // 累计发放
        $totalSend = MemberCreditRecordModel::getSumByType(1, 'creditSendType');
        $totalBack = MemberCreditRecordModel::getSumByType(1, 'creditBackType');
        // 减去返还的
        $totalSend = bcsub($totalSend, $totalBack);
    
        // 累计使用
        $totalUse = -MemberCreditRecordModel::getSumByType(1, 'creditUseType');
        $totalRefund = MemberCreditRecordModel::getSumByType(1, 'creditRefundType');
        // 退款返还
        $totalUse = bcsub($totalUse, $totalRefund);
        
        $data = [
            'total_send' => $totalSend,
            'total_member' => (int)MemberModel::find()->sum('credit') ?? 0, // 不过滤删除会员的
            'total_use' => $totalUse
        ];
        
        return $this->result(['data' => $data]);
    }
}