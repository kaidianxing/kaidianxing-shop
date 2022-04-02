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

namespace shopstar\services\sale;

use shopstar\bases\service\BaseService;
use shopstar\constants\coupon\CouponConstant;
use shopstar\constants\coupon\CouponTimeLimitConstant;
use shopstar\constants\log\sale\CouponLogConstant;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ValueHelper;
use shopstar\jobs\sale\CouponExpireJob;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\goods\category\GoodsCategoryModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\sale\CouponMapModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\sale\CouponRuleModel;

/**
 * @author 青岛开店星信息技术有限公司
 */
class CouponService extends BaseService
{

    /**
     * 商品使用限制
     * @var string[]
     */
    public $goodsLimit = [
        '不限制',
        '允许以下商品使用',
        '限制以下商品使用',
        '允许以下商品分类使用',
    ];


    /**
     * 保存优惠券
     * @param int $uid
     * @param int $id
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public function saveCoupon(int $uid, int $id = 0)
    {
        $post = RequestHelper::post();
        // 优惠券名称
        if (empty($post['coupon_name'])) {
            return error('优惠券的名字不能为空');
        }
        // 名称长度
        if (mb_strlen($post['coupon_name']) > 10) {
            return error('优惠券名称超长');
        }
        // 使用时间限制
        if ($post['time_limit'] == CouponConstant::COUPON_TIME_LIMIT_AREA && (empty($post['start_time']) || empty($post['end_time']))) {
            return error('时间限制不能为空');
        }
        // 满减金额
        if ($post['enough'] == '') {
            return error('满减金额不能为空');
        }
        // 折扣金额   比例或金额
        if (empty($post['discount_price'])) {
            return error('折扣金额不能为空');
        }
        // 满减类型
        if ($post['coupon_sale_type'] == CouponConstant::COUPON_SALE_TYPE_SUB) {
            if (bccomp($post['discount_price'], $post['enough'], 2) > 0 || bccomp($post['discount_price'], 9999999.99) > 0) {
                return error('折扣金额不能大于满减金额或超过最大限额');
            }
        } else {
            // 折扣类型
            if (bccomp($post['discount_price'], 0.01, 2) < 0 || bccomp($post['discount_price'], 9.99) > 0) {
                return error('折扣比例范围错误');
            }
        }
        // 数量
        if ($post['stock_type'] == CouponConstant::COUPON_STOCK_TYPE_LIMIT && $post['stock'] <= 0) {
            return error('优惠券发放数量不能为空');
        }

        // 商品使用限制
        if ($post['goods_limit'] != 0 && empty($post['goods_ids'])) {
            return error('商品使用限制不能为空');
        }
        // 活动送券默认开启
        if ($post['pick_type'] == 2) {
            $post['state'] = 1;
        }

        if (empty($id)) {
            $coupon = new CouponModel();
        } else {
            $coupon = CouponModel::findOne(['id' => $id]);
            if (empty($coupon)) {
                return error('优惠券不存在');
            }
        }

        // 判断是否需要创建优惠券过期任务
        $isCreateJob = false;
        // 有使用期限的   并且   (新创建 或者 编辑了使用期限)
        if ($post['time_limit'] == 0 && $post['end_time'] != $coupon->end_time) {
            $isCreateJob = true;
        }


        $coupon->setAttributes($post);
        if (!$coupon->save()) {
            return error('优惠券保存失败');
        }

        // 创建任务
        if ($isCreateJob) {
            // 计算时间
            $delay = strtotime($post['end_time']) - time();
            QueueHelper::push(new CouponExpireJob([
                'couponId' => $coupon->id,
            ]), $delay);
        }

        // 保存优惠券限制
        // 商品使用限制
        $mapRes = CouponMapModel::updateMap([
            'is_update' => $id ? true : false, // 修改 则更新
            'goods_ids' => $post['goods_ids'], // 商品ids
            'goods_limit' => $post['goods_limit'], // 限制类型
            'coupon_id' => $coupon->id,
        ]);
        if (is_error($mapRes)) {
            return $mapRes;
        }

        // 会员等级限制 分销等级限制
        $ruleRes = CouponRuleModel::updateRule([
            'is_update' => $id ? true : false, // 修改 则更新
            'coupon_id' => $coupon->id, // 优惠券id
            'member_level' => $post['member_level'], // 等级限制
            'commission_level' => $post['commission_level'], // 分销等级限制
        ]);
        if (is_error($ruleRes)) {
            return $ruleRes;
        }


        if ($coupon->coupon_sale_type == CouponConstant::COUPON_SALE_TYPE_SUB) {
            $content = '满' . ValueHelper::delZero($coupon->enough) . '减' . ValueHelper::delZero($coupon->discount_price);
        } else {
            // 打折类型
            $content = '满' . ValueHelper::delZero($coupon->enough) . '享' . ValueHelper::delZero($coupon->discount_price) . '折';
        }
        if ($coupon->pick_type == CouponConstant::COUPON_PICK_TYPE_CENTER && $coupon->is_free == CouponConstant::IS_FREE) {
            $pickWay = '免费'; // 免费
        } else if ($coupon->pick_type == CouponConstant::COUPON_PICK_TYPE_CENTER && $coupon->is_free == CouponConstant::IS_NOT_FREE) {
            $pickWay = '付费'; // 付费
        } else if ($coupon->pick_type == CouponConstant::COUPON_PICK_TYPE_LINK) {
            $pickWay = '链接'; // 链接
        } else if ($coupon->pick_type == CouponConstant::COUPON_PICK_TYPE_ACTIVITY) {
            $pickWay = '活动'; // 链接
        }
        // 记录日志
        $logPrimaryData = [
            'id' => $coupon->id,
            'coupon_name' => $coupon->coupon_name,
            'coupon_sale_type' => $coupon->coupon_sale_type == 1 ? '满减券' : '折扣券',
            'content' => $content,
            'stock_type' => $coupon->stock_type ? '限制' : '不限制',
            'stock' => $coupon->stock ?: '-',
            'time_limit' => $coupon->time_limit ? '获得后' : '日期内',
            'limit_day' => $coupon->time_limit == 1 ? $coupon->limit_day . ' 天内有效' : '-',
            'start_time' => $coupon->time_limit == 0 ? $coupon->start_time : '-',
            'end_time' => $coupon->time_limit == 0 ? $coupon->end_time : '-',
            'sort' => $coupon->sort ?: '-',
            'limit_member' => $coupon->limit_member ? '指定会员身份' : '全部会员',
        ];
        // 会员等级
        if (!empty($post['member_level'])) {
            $memberLevel = MemberLevelModel::find()->select('level_name')->where(['id' => explode(',', $post['member_level'])])->get();
            $logPrimaryData['limit_member_level'] = $post['member_level'];
            $logPrimaryData['member_level_text'] = implode(',', array_column($memberLevel, 'level_name'));
        }
        // 分销等级
        if (!empty($post['commission_level'])) {
            $commissionLevel = CommissionLevelModel::find()->select('name')->where(['id' => explode(',', $post['commission_level'])])->get();
            $logPrimaryData['limit_commission_level'] = $post['commission_level'];
            $logPrimaryData['commission_level_text'] = implode(',', array_column($commissionLevel, 'name'));
        }
        $logPrimaryData = array_merge($logPrimaryData, [
            'get_max_type' => $coupon->get_max_type ? '自定义' : '不限制',
            'get_max' => $coupon->get_max ? '每人限领 ' . $coupon->get_max . ' 张' : '-',
            'pick_type' => $pickWay,
            'goods_limit' => $this->goodsLimit[$coupon->goods_limit],
            'goods_ids' => $post['goods_ids'] ?: '-',
        ]);
        // 商品限制
        if ($coupon->goods_limit == 1 || $coupon->goods_limit == 2) {
            $goods = GoodsModel::find()->select('title')->where(['id' => explode(',', $post['goods_ids'])])->get();
            $logPrimaryData['goods_text'] = implode(',', array_column($goods, 'title'));
        } else if ($coupon->goods_limit == 3) {
            $goods = GoodsCategoryModel::find()->select('name')->where(['id' => explode(',', $post['goods_ids'])])->get();
            $logPrimaryData['goods_cate_text'] = implode(',', array_column($goods, 'name'));
        }

        $logPrimaryData = array_merge($logPrimaryData, [
            'coupon_sale_limit' => $coupon->coupon_sale_limit ? '不可与会员折扣同时使用' : '不限制',
            'default_description' => $coupon->default_description == 2 ? '单独设置' : '统一说明',
            'description' => $coupon->description ?? '-',
        ]);

        $code = empty($id) ? CouponLogConstant::COUPON_ADD : CouponLogConstant::COUPON_EDIT;
        LogModel::write(
            $uid,
            $code,
            CouponLogConstant::getText($code),
            $coupon->id,
            [
                'log_data' => $coupon->attributes,
                'log_primary' => $coupon->getLogAttributeRemark($logPrimaryData),
                'dirty_identity_code' => [
                    CouponLogConstant::COUPON_ADD,
                    CouponLogConstant::COUPON_EDIT,
                ]
            ]
        );


        return true;
    }


    /**
     * 检查是否可以领取优惠券
     * @param int $memberId
     * @param int $couponId
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkReceive(int $memberId, int $couponId): ?array
    {
        $coupon = CouponModel::find()->where(['id' => $couponId])->first();
        if ($coupon['state'] == 0) {
            return error('优惠券已失效');
        }
        // 校验库存
        if ($coupon['stock'] - $coupon['get_total'] < 1 && $coupon['stock_type'] == CouponConstant::COUPON_STOCK_TYPE_LIMIT) {
            return error('优惠券已被领取完');
        }

        // 校验领取限制
        if ($coupon['get_max_type'] != CouponConstant::COUPON_GET_MAX_TYPE_NOT_LIMIT && $coupon['pick_type'] != CouponConstant::COUPON_PICK_TYPE_ACTIVITY) {
            // 后台会员详情中赠送的优惠券 不计入张数统计
            $couponCount = CouponMemberModel::find()
                ->where(['member_id' => $memberId, 'coupon_id' => $couponId, 'send_type' => 0])
                ->count();
            if ($couponCount >= $coupon['get_max']) {
                return error('您的领取张数已达到上限');
            }
        }
        // 校验等级  领取人限制
        if ($coupon['limit_member'] == CouponConstant::COUPON_LIMIT_MEMBER) {
            // 获取规则
            $rule = CouponRuleModel::find()
                ->where(['coupon_id' => $couponId])
                ->select('member_level, commission_level')
                ->get();
            // 会员等级
            $memberLevels = array_filter(array_column($rule, 'member_level'));
            if (!empty($memberLevels)) {
                $member = MemberModel::find()
                    ->where(['id' => $memberId])
                    ->select('level_id')
                    ->asArray()
                    ->first();
                if (!in_array(ArrayHelper::arrayGet((array)$member, 'level_id', 0), $memberLevels)) {
                    return error('等级限制,领取失败');
                }
            }

            // 分销等级
            $commissionLevels = array_filter(array_column($rule, 'commission_level'));
            if (!empty($commissionLevels)) {
                $commission = CommissionAgentModel::find()
                    ->where(['member_id' => $memberId, 'status' => 1, 'is_black' => 0])
                    ->select('level_id')
                    ->asArray()
                    ->first();
                // 校验分销商等级限制
                if (!in_array(ArrayHelper::arrayGet((array)$commission, 'level_id', 0), $commissionLevels)) {
                    return error('分销等级限制,领取失败');
                }
            }
        }

        return $coupon;
    }


    /**
     * 获取商品优惠券
     * @param int $memberId
     * @param int $goodsId
     * @param int $goodsCategoryId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getGoodsCoupon(int $memberId, int $goodsId, int $goodsCategoryId)
    {
        $params = [
            'where' => [
                'and',
                ['in', 'goods_limit', [0, 1, 2, 3]],//商品使用限制 0无限制 1允许以下产品使用 2不允许一下产品使用 3允许以下分类使用
                ['state' => 1],
                ['is_free' => 1],
                ['pick_type' => 0],
            ],
            'orderBy' => 'sort desc, created_at desc',
            'select' => 'id, get_max, get_max_type, coupon_name, coupon_sale_type, enough, discount_price, start_time, end_time, goods_limit, time_limit, limit_day, stock, get_total, stock_type',
            'with' => [
                'memberCoupon' => function ($query) use ($memberId) {
                    $query->where(['member_id' => $memberId]);
                }
            ]
        ];

        $coupons = CouponModel::getColl($params, ['onlyList' => true]);

        $couponNoLimit = [];
        $couponGoods = [];
        $couponLimit = [];
        $couponCategory = [];
        // 优惠券分类
        foreach ($coupons as $coupon) {
            if ($coupon['goods_limit'] == 0) {
                $couponNoLimit[] = $coupon['id'];
            }

            if ($coupon['goods_limit'] == 1) {
                $couponGoods[] = $coupon['id'];
            }

            if ($coupon['goods_limit'] == 2) {
                $couponLimit[] = $coupon['id'];
            }

            if ($coupon['goods_limit'] == 3) {
                $couponCategory[] = $coupon['id'];
            }
        }

        // 按照商品分类限制筛选
        $couponGoodsExists = CouponMapModel::find()
            ->where([
                'and',
                ['in', 'coupon_id', $couponGoods],
                ['goods_cate_id' => $goodsId],
                ['type' => 1] // 1产品 2分类
            ])->select('coupon_id')->get();
        $couponGoodsExistsId = array_column($couponGoodsExists, 'coupon_id');

        $couponGoodsLimitExists = CouponMapModel::find()
            ->where([
                'and',
                ['in', 'coupon_id', $couponLimit],
                ['goods_cate_id' => $goodsId],
                ['type' => 1] // 1产品 2分类
            ])->select('coupon_id')->get();
        $couponGoodsLimitExistsId = array_column($couponGoodsLimitExists, 'coupon_id');

        $couponCategoryExists = CouponMapModel::find()
            ->where([
                'and',
                ['in', 'coupon_id', $couponCategory],
                ['goods_cate_id' => $goodsCategoryId],
                ['type' => 2] // 1产品 2分类
            ])->select('coupon_id')->get();
        $couponCategoryExistsId = array_column($couponCategoryExists, 'coupon_id');

        // 筛选
        $returnData = [];
        foreach ($coupons as $coupon) {

            // 校验库存
            if ($coupon['stock'] - $coupon['get_total'] < 1 && $coupon['stock_type'] == CouponConstant::COUPON_STOCK_TYPE_LIMIT) {
                continue;
            }

            // 校验时间
            if ($coupon['time_limit'] == CouponConstant::COUPON_TIME_LIMIT_AREA && $coupon['end_time'] < DateTimeHelper::now()) {
                continue;
            }

            // 不限制
            if ($coupon['goods_limit'] == 0) {
                $returnData[$coupon['id']] = $coupon;
            }

            // 不允许以下商品使用
            if ($coupon['goods_limit'] == 2) {
                if (!empty($couponGoodsLimitExistsId) && in_array($coupon['id'], $couponGoodsLimitExistsId)) {
                    continue;
                }
                $returnData[$coupon['id']] = $coupon;
            }

            // 商品限制
            if ($coupon['goods_limit'] == 1 && in_array($coupon['id'], $couponGoodsExistsId)) {
                $returnData[$coupon['id']] = $coupon;
            }

            // 分类限制
            if ($coupon['goods_limit'] == 3 && in_array($coupon['id'], $couponCategoryExistsId)) {
                $returnData[$coupon['id']] = $coupon;
            }
        }

        if (!empty($returnData)) {
            foreach ($returnData as $key => $value) {
                if ($value['coupon_sale_type'] == CouponConstant::COUPON_SALE_TYPE_SUB) {
                    $returnData[$key]['content'] = '满' . ValueHelper::delZero($value['enough']) . '减' . ValueHelper::delZero($value['discount_price']);
                } else {
                    // 打折类型
                    $returnData[$key]['content'] = '满' . ValueHelper::delZero($value['enough']) . '享' . ValueHelper::delZero($value['discount_price']) . '折';
                }

                // 时间展示
                if ($value['time_limit'] == CouponTimeLimitConstant::COUPON_TIME_LIMIT_TIME) {
                    $returnData[$key]['time_content'] = date('Y-m-d', strtotime($value['start_time'])) . '~' . date('Y-m-d',
                            strtotime($value['end_time']));
                }
                if ($value['time_limit'] == CouponTimeLimitConstant::COUPON_TIME_LIMIT_DAY) {
                    $returnData[$key]['time_content'] = '领取日内' . $value['limit_day'] . '天内有效';
                }

                $memberHasCount = 0;
                // 判断是否领取
                if (!empty($value['memberCoupon'])) {
                    $returnData[$key]['is_has'] = 1;
                    $memberHasCount = count($value['memberCoupon']);
                } else {
                    $returnData[$key]['is_has'] = 0;
                }
                $returnData[$key]['has_receive_count'] = $memberHasCount;
                if ($returnData[$key]['stock_type'] == 0) {
                    if ($returnData[$key]['get_max_type'] == 0) {  // 无限制
                        $returnData[$key]['can_receive_count'] = -1;  // -1代表每个人可以无线领取
                    } else if ($returnData[$key]['get_max_type'] == 1) {  // 自定义
                        $memberLimitCount = $returnData[$key]['get_max'];  // 每个人领取的最大个数。
                        $memberCanReveiveCount = $memberLimitCount - $memberHasCount;
                        // 用户可以领取的个数
                        $returnData[$key]['can_receive_count'] = $memberCanReveiveCount > 0 ? $memberCanReveiveCount : 0;
                    }

                } else if ($returnData[$key]['stock_type'] == 1) {
                    // 总量
                    $totalCount = $returnData[$key]['stock'];
                    // 已经领取个数
                    $getCount = $returnData[$key]['get_total'];
                    // 剩余总个数
                    $laveCount = $totalCount - $getCount;
                    $laveCount = $laveCount >= 0 ? $laveCount : 0;

                    if ($returnData[$key]['get_max_type'] == 0) {  // 无限制
                        // 用户可以领取的个数
                        $returnData[$key]['can_receive_count'] = $laveCount;
                    } else if ($returnData[$key]['get_max_type'] == 1) {  // 自定义
                        $memberLimitCount = $returnData[$key]['get_max'];
                        $memberCanReveiveCount = $memberLimitCount - $memberHasCount;
                        if ($memberCanReveiveCount > $laveCount) {
                            $memberCanReveiveCount = $laveCount;
                        }
                        // 用户可以领取的个数
                        $returnData[$key]['can_receive_count'] = $memberCanReveiveCount > 0 ? $memberCanReveiveCount : 0;
                    }

                }

                unset($returnData[$key]['goods_limit']);
                unset($returnData[$key]['memberCoupon']);
            }
        }

        return array_values($returnData);
    }


}