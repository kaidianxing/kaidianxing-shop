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

namespace shopstar\admin\sale;

use shopstar\constants\coupon\CouponConstant;
use shopstar\exceptions\member\MemberException;
use shopstar\exceptions\sale\CouponException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\bases\KdxAdminApiController;

class CouponLogController extends KdxAdminApiController
{

    public $configActions = [
        'allowPermActions' => [
            'get-member-coupon'
        ]
    ];
    /**
     * 获取用户可用优惠券
     * @throws CouponException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetMemberCoupon()
    {
        $memberId = RequestHelper::get('member_id');
        if (empty($memberId)) {
            throw new CouponException(CouponException::MEMBER_COUPON_PARAMS_ERROR);
        }

        if (!MemberModel::checkDeleted($memberId)) {
            throw new MemberException(MemberException::MEMBER_DELETED_NO_GET_MEMBER_COUPON);
        }

        // 优惠券状态 1 可使用 2 已使用 3 已过期
        $type = RequestHelper::get('type',1);

        $where = [
            ['member_id' => $memberId],
        ];


        switch ($type)
        {
            case 1:
                $where[] = ['order_id' => 0];
                $where[] = ['status' => 0];
                $where[] = ['>', 'end_time', DateTimeHelper::now()];
                break;
            case 2:
                $where[] = [
                    'or',
                    ['<>','status',0],
                    ['<>','order_id',0]
                ];
                break;
            case 3:
                $where[] = ['order_id' => 0];
                $where[] = ['status' => 0];
                $where[] = ['<', 'end_time', DateTimeHelper::now()];
                break;
            default;
        }


        $list = CouponMemberModel::getColl([
            'select' => 'id, coupon_id, coupon_sale_type, title, enough, discount_price, end_time, goods_limit, created_at ',
            'andWhere' => $where,

        ], [
            'callable' => function (&$row) {
                if ($row['coupon_sale_type'] == 1) {
                    $row['content'] = '满' . ValueHelper::delZero($row['enough']) . '减' . ValueHelper::delZero($row['discount_price']);
                } else {
                    // 打折类型
                    $row['content'] = '满' . ValueHelper::delZero($row['enough']) . '享' . ValueHelper::delZero($row['discount_price']) . '折';
                }
            }
        ]);

        return $this->result($list);
    }
}