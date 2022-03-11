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


namespace shopstar\services\goods;

use shopstar\bases\service\BaseService;
 
use shopstar\models\goods\GoodsCartModel;
use shopstar\models\goods\GoodsMemberLevelDiscountModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\goods\GoodsPermMapModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;

class GoodsCartService extends BaseService
{
    /**
     * 购物车需要查询的字段
     * @var array
     * @author 青岛开店星信息技术有限公司
     */
    private static $_cartFields = [
        'id',
        'goods_id',
        'option_id',
        'total',
        'price as cart_price',
        'is_selected',
        'is_lose_efficacy',
        'is_reelect',
    ];
    /**
     * 是否全选
     * @author 青岛开店星信息技术有限公司
     * @var bool
     */
    public static $allSelected = true;


    /**
     * 商品需要查询的字段
     * @var array
     * @author 青岛开店星信息技术有限公司
     */
    public static $_goodsFields = [
        'id as goods_id',
        'title',
        'thumb',
        'price',
        'status',//status==[1,2] 才允许购买
        '(stock=0) as is_soldout', //库存等于0应该显示库存不足
        'stock',//商品库存
        'has_option', // 是否有规格
        'ext_field',//扩展字段
        'dispatch_express', //是否支持快递 0否1是
        'dispatch_intracity', //是否支持同城配送 0否1是
        'dispatch_verify',
        'is_deleted',
        'status',
        'member_level_discount_type',
    ];

    /**
     * 规格需要查询的字段
     * @var array
     * @author 青岛开店星信息技术有限公司
     */
    public static $_optionFields = [
        'goods_option.id as option_id',
        'goods_option.goods_id',
        'goods_option.title as option_title',
        'goods_option.thumb',
        'goods_option.stock',
        'goods.thumb master_thumb',
        'goods_option.price',
        '(goods_option.stock=0) as is_soldout'
    ];




    /**
     * 获取购物车商品
     * @param int $memberId
     * @param array $options
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getGoods(int $memberId, array $options = [])
    {
        //检测是否失效
        list($list, $optionList) = self::checkFailure($memberId, $options);

        // 会员信息
        $member = MemberModel::find()->select('level_id')->where(['id' => $memberId])->first();
        $memberLevel = MemberLevelModel::find()->where(['id' => $member['level_id'], 'state' => 1])->first();

        foreach ($list as &$item) {
            if (isset($optionList[$item['option_id']])) {
                $item = array_merge($item, $optionList[$item['option_id']]);
                $item['thumb'] = !empty($item['thumb']) ? $item['thumb'] : $item['master_thumb'];
            }
            unset($item['master_thumb']);
            // 会员价相关  如果没购买权限 跳过不执行
            if ($item['buy_perm'] == 0) {
                continue;
            }
            if ($item['member_level_discount_type'] == 1 && !empty($memberLevel['is_discount']) && !empty($memberLevel['discount'])) {
                // 默认
                $item['price'] = round2($item['price'] * ($memberLevel['discount'] / 10));

            } else if ($item['member_level_discount_type'] == 2) {

                //  指定会员价
                $goodsLevelDiscountMap = GoodsMemberLevelDiscountModel::find()->where([
                    'goods_id' => $item['goods_id'], //会员折扣类型是 2 = 指定会员等级
                    'level_id' => $memberLevel['id'],
                    'option_id' => 0
                ])->first();

                if ($goodsLevelDiscountMap['type'] == 1) {
                    $item['price'] = round2($item['price'] * ($goodsLevelDiscountMap['discount'] / 10));
                } else {
                    $item['price'] = round2($goodsLevelDiscountMap['discount']);
                }

            } else if ($item['member_level_discount_type'] == 3) {

                // 按规格折扣
                $goodsLevelDiscountMap = GoodsMemberLevelDiscountModel::find()->where([
                    'goods_id' => $item['goods_id'], //会员折扣类型是 2 = 指定会员等级
                    'level_id' => $memberLevel['id'],
                    'option_id' => $item['option_id'],
                ])->first();

                if ($goodsLevelDiscountMap['type'] == 1) {
                    $item['price'] = round2($item['price'] * ($goodsLevelDiscountMap['discount'] / 10));
                } else {
                    $item['price'] = round2($goodsLevelDiscountMap['discount']);
                }
            }
        }

        return $list;
    }

    /**
     * 获取购物车商品数量
     * @param int $memberId
     * @return int|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function goodsCount(int $memberId, $options = []): int
    {
        //检测购物车失效
        self::checkFailure($memberId, $options);

        $count = GoodsCartModel::find()->select('sum(total) as total')->where([
            'member_id' => $memberId,
            'is_reelect' => 0,
            'is_lose_efficacy' => 0
        ])->one();

        return $count->total ?? 0;
    }


    /**
     * 检测失效
     * @param int $memberId
     * @param array $options
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司.
     */
    public static function checkFailure(int $memberId, array $options = [])
    {
        // 购物车列表
        $query = GoodsCartModel::find()
            ->where([
                'member_id' => $memberId,
                'is_lose_efficacy' => 0
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->select(self::$_cartFields);
        if (!empty($options['goods_id'])) {
            $query->andWhere(['goods_id' => $options['goods_id']]);
        }
        if (!empty($options['is_selected'])) {
            $query->andWhere(['is_selected' => 1]);
        }

        $list = $query->get();

        $goodsIds = [];
        $optionIds = [];

        //如果为空则不是全选
        !empty($list) ?: self::$allSelected = false;

        foreach ($list as &$item) {
            $goodsIds[] = $item['goods_id'];
            if ($item['option_id'] > 0) {
                $optionIds[] = $item['option_id'];
            }
            $item['option_title'] = '';
            // 只要存在未选中的商品，那么就不是全选
            if ($item['is_selected'] === '0') {
                self::$allSelected = false;
            }
        }
        unset($item);


        // 商品列表
        $goodsList = GoodsModel::find()->where([
            'id' => $goodsIds
        ])->select(self::$_goodsFields)->indexBy('goods_id')->asArray()->all();

        array_walk($goodsList, function (&$result) {
            $extField = (new GoodsModel())->getExtField($result['ext_field']);
            unset($result['ext_field']);
            if (!empty($extField)) {
                $result = array_merge($result, $extField);
            }
            // 价格面议状态
            $result['buy_button_status'] = GoodsService::getBuyButtonStatus($extField['buy_button_type'], $extField['buy_button_settings']);
        });

        // 规格列表
        $optionList = GoodsOptionModel::find()
            ->alias('goods_option')
            ->leftJoin(GoodsModel::tableName() . ' goods', 'goods.id=goods_option.goods_id')
            ->where(['goods_option.id' => $optionIds])
            ->indexBy('option_id')
            ->select(self::$_optionFields)
            ->asArray()
            ->all();

        //获取有权限购买的商品
        $havePermBuyGoodsIds = GoodsPermMapModel::getNotHasPermGoodsId($goodsIds, $memberId, GoodsPermMapModel::PERM_BUY);
        // 有权限浏览的商品
        $havePermViewGoodsIds = GoodsPermMapModel::getNotHasPermGoodsId($goodsIds, $memberId, GoodsPermMapModel::PERM_VIEW);

        // 配送方式设置
        $dispatchSet = [];
        if (empty($options['shop_type'])) {
            $dispatchSet = ShopSettings::get('dispatch');
        }

        // 清除选中状态的商品
        $cleanSelectIds = [];
        $loseGoodsIds = [];
        foreach ($list as $key => &$item) {
            //权限购买
            if (in_array($item['goods_id'], $havePermBuyGoodsIds)) {
                $item['buy_perm'] = 0;
                // 去除选中状态
                $cleanSelectIds[] = $item['goods_id'];
            } else {
                $item['buy_perm'] = 1;
            }

            //权限查看
            if (in_array($item['goods_id'], $havePermViewGoodsIds)) {
                // 去除选中状态
                $cleanSelectIds[] = $item['goods_id'];
                $loseGoodsIds[] = $item['goods_id'];
                unset($list[$key]);
            } else {
                // 有查看权限
                $item['view_perm'] = 0;
            }
            if (isset($goodsList[$item['goods_id']]) && isset($list[$key])) {
                $item = array_merge($item, $goodsList[$item['goods_id']]);
            }
            // 检查商品是否有配送方式
            $dispatchResult = self::checkDispatchSet($options['shop_type'], $item, $dispatchSet);

            if (!$dispatchResult) {
                $cleanSelectIds[] = $item['goods_id'];
                $loseGoodsIds[] = $item['goods_id'];
                unset($list[$key]);
            }

        }

        unset($item);
        $list = array_values($list);

        // 清除的id
        if (!empty($cleanSelectIds)) {
            GoodsCartModel::updateAll(['is_selected' => 0], ['goods_id' => array_unique($cleanSelectIds)]);
        }
        // 失效的
        if (!empty($loseGoodsIds)) {
            GoodsCartModel::updateAll(['is_lose_efficacy' => 1], ['goods_id' => array_unique($loseGoodsIds)]);
        }

        return [$list, $optionList];
    }


    /**
     * 检查商品有没有配送类型
     * @param int $shopType
     * @param array $goods
     * @param array $dispatchSet 配送方式设置
     * @return bool true 有配送方式  false 无配送方式
     * @author 青岛开店星信息技术有限公司
     */
    private static function checkDispatchSet(int $shopType, array &$goods, $dispatchSet = [])
    {
        if (empty($shopType)) {
            // 校验配送方式 如果都没有配送方式 则失效
            if ($dispatchSet['express']['enable'] == 0) {
                $goods['dispatch_express'] = 0;
            }
            if ($dispatchSet['intracity']['enable'] == 0) {
                $goods['dispatch_intracity'] = 0;
            }
            if (isset($dispatchSet['verify_set']) && $dispatchSet['verify_set']['verify_is_open'] == 0) {
                $goods['dispatch_verify'] = 0;
            }
            $goods['dispatch_verify'] = 0;
            // 都没有配送方式  删除
            if ($goods['dispatch_express'] == 0 && $goods['dispatch_intracity'] == 0 && $goods['dispatch_verify'] == 0) {
                return false;
            }
        }
        return true;
    }
}