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

namespace shopstar\mobile\member;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberCreditRecordModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;

/**
 * 积分记录
 * Class CreditController
 * @package shop\client\member
 */
class CreditController extends BaseMobileApiController
{
    /**
     * 记录
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        // 列表类型
        $type = RequestHelper::get('type');
        
        $andWhere = [];
        
        switch ($type) {
            case 1: // 获得
                $andWhere[] = ['record.status' => MemberCreditRecordModel::$creditGet];
                break;
            case 2: // 抵扣
                $andWhere[] = ['record.status' => MemberCreditRecordModel::$creditDeduct];
                break;
            case 3: // 支付
                $andWhere[] = ['record.status' => MemberCreditRecordModel::$creditPay];
                break;
        }
    
        $params = [
            'select' => 'record.id, record.num, record.created_at, record.remark, record.status,record.order_id,order.order_no,order.user_delete',
            'where' => [
                'record.member_id' => $this->memberId,
                'record.type' => 1,
            ],
            'andWhere' => $andWhere,
            'orderBy' => 'record.created_at desc',
            'alias' => 'record',
            'leftJoins' => [
                [OrderModel::tableName().' order','order.id = record.order_id']
            ]
        ];
    
        // 积分设置
        $creditSet = ShopSettings::get('sysset.credit');
    
        $list = MemberCreditRecordModel::getColl($params, [
            'callable' => function (&$row) use($creditSet) {
                $row['status_text'] = str_replace('积分', $creditSet['credit_text'], MemberCreditRecordStatusConstant::getMessage($row['status']));
            }
        ]);
    
        return $this->result(['data' => $list]);
    }
}