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

namespace shopstar\admin\newGifts;

use shopstar\constants\ClientTypeConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\newGifts\NewGiftsActivityModel;
use shopstar\models\newGifts\NewGiftsLogModel;
use shopstar\bases\KdxAdminApiController;
use yii\helpers\Json;

/**
 * 发送记录
 * Class LogController
 * @package apps\newGifts\manage
 */
class LogController extends KdxAdminApiController
{
    /**
     * 列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $startTime = RequestHelper::get('start_time');
        $endTime = RequestHelper::get('end_time');
        $andWhere = [];
        if (!empty($startTime) && !empty($endTime)) {
            $andWhere[] = ['between', 'log.created_at', $startTime, $endTime];
        }
        
        $params = [
            'searchs' => [
                ['activity.title', 'like', 'keyword'],
                ['log.client_type', 'int', 'client_type'],
                ['log.pick_type', 'int', 'pick_type'],
                ['activity.id', 'int', 'activity_id'],
            ],
            'select' => [
                'log.id',
                'log.member_id',
                'member.nickname',
                'member.avatar',
                'activity.title',
                'log.created_at',
                'log.client_type',
                'log.gifts',
                'log.pick_type',
            ],
            'alias' => 'log',
            'where' => [],
            'andWhere' => $andWhere,
            'leftJoins' => [
                [NewGiftsActivityModel::tableName().' activity', 'log.activity_id = activity.id'],
                [MemberModel::tableName().' member', 'member.id=log.member_id'],
            ],
            'orderBy' => [
                'id' => SORT_DESC,
            ]
        ];
        
        $list = NewGiftsLogModel::getColl($params, [
            'callable' => function (&$row) {
                $row['client_type_text'] = ClientTypeConstant::getText($row['client_type']);
                $row['pick_type_text'] = NewGiftsActivityModel::$pickTypeText[$row['pick_type']];
                $row['gifts'] = Json::decode($row['gifts']);
                $row['gifts']['coupon_title'] = [];
                // 优惠券名称
                if (!empty($row['gifts']['coupon_ids'])) {
                    $couponIds = explode(',', $row['gifts']['coupon_ids']);
                    $coupons = CouponModel::getCouponInfo($couponIds);
                    $row['gifts']['coupon_title'] = array_column($coupons, 'coupon_name');
                    unset($row['gifts']['coupon_ids']);
                }
            }
        ]);
        
        return $this->result($list);
    }
    
}