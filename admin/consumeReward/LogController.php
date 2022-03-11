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

namespace shopstar\admin\consumeReward;

use shopstar\constants\ClientTypeConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\consumeReward\ConsumeRewardActivityModel;
use shopstar\models\consumeReward\ConsumeRewardLogModel;
use shopstar\bases\KdxAdminApiController;
use yii\helpers\Json;

/**
 * 领取记录
 * Class LogController
 * @package apps\consumeReward\manage
 */
class LogController extends KdxAdminApiController
{
    /**
     * 列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $startTime = RequestHelper::get('start_time');
        $endTime = RequestHelper::get('end_time');
        $andWhere = [];
        if (!empty($startTime) && !empty($endTime)) {
            $andWhere[] = ['between', 'log.created_at', $startTime, $endTime];
        }
    
        $params = [
            'searchs' => [
                ['activity.id', 'int', 'activity_id'],
                ['activity.title', 'like', 'keyword'],
                ['log.client_type', 'int', 'client_type'],
                ['log.pick_type', 'int', 'pick_type'],
            ],
            'select' => [
                'log.id',
                'log.member_id',
                'member.nickname',
                'member.avatar',
                'activity.title',
                'log.created_at',
                'log.client_type',
                'log.type',
                'log.reward',
                'log.order_no'
            ],
            'alias' => 'log',

            'where' => ['is_finish' => 1],
            'andWhere' => $andWhere,
            'leftJoins' => [
                [ConsumeRewardActivityModel::tableName().' activity', 'log.activity_id = activity.id'],
                [MemberModel::tableName().' member', 'member.id=log.member_id'],
            ],
            'orderBy' => [
                'id' => SORT_DESC,
            ]
        ];
        
        $list = ConsumeRewardLogModel::getColl($params, [
            'callable' => function (&$row) {
                $row['client_type_text'] = ClientTypeConstant::getText($row['client_type']);
                $row['reward'] = Json::decode($row['reward']);
                // 优惠券名称
                if (!empty($row['reward']['coupon_ids'])) {
                    $couponIds = explode(',', $row['reward']['coupon_ids']);
                    $coupons = CouponModel::getCouponInfo($couponIds);
                    $row['reward']['coupon_title'] = array_column($coupons, 'coupon_name');
                    unset($row['gifts']['coupon_ids']);
                }
                if ($row['type'] == 1) {
                    $row['type_text'] = '单笔订单';
                } else {
                    $row['type_text'] = '累计订单';
                }
            }
        ]);
        
        return $this->result($list);
    }
    
}