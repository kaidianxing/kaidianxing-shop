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

namespace shopstar\models\order\create\handler;

use shopstar\exceptions\order\OrderCreatorException;
use shopstar\models\goods\GoodsCartModel;

class GoodsCartHandler
{

    /**
     * 是否来自购物车
     * @author 青岛开店星信息技术有限公司
     * @var bool
     */
    private $isCart = false;
    /**
     * 商品映射
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $goodsMaps = [];

    /**
     * all商品id
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $goodsIds = [];

    /**
     * all规格id
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $optionIds = [];

    /**
     * all购物车id
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    private $cartIds = [];

    /**
     * 获取购物车商品
     * GoodsCartHandler constructor.
     * @param int $memberId
     * @param array $goodsInfo
     * @param $isCart
     * @throws OrderCreatorException
     */
    public function __construct(int $memberId, array $goodsInfo, $isCart)
    {
        $this->validate($memberId, $goodsInfo, $isCart);
    }

    /**
     * 获取购物车列表
     * @param int $memberId
     * @param array $goodsInfo
     * @param bool $isCart
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    protected function validate(int $memberId, array $goodsInfo = [], $isCart = false)
    {
        //是来自购物车
        $this->isCart = (bool)$isCart;

        //如果来自购物车，并且有商品信息，则认为是修改购物车的商品数量
        if ($isCart && !empty($goodsInfo)) {
            $this->updateTotal($memberId, $goodsInfo);
            unset($goodsInfo);
        }

        if (empty($goodsInfo)) {

            //如果商品为空则强制设置为购物车
            $this->isCart = true;

            //获取购物车商品
            $goodsInfo = GoodsCartModel::getColl([
                'where' => ['member_id' => $memberId, 'is_selected' => 1, 'is_lose_efficacy' => 0],
                'select' => [
                    'id as cart_id',
                    'goods_id',
                    'option_id',
                    'total'
                ]
            ], [
                'pager' => false,
                'onlyList' => true
            ]);

            $this->cartIds = array_column($goodsInfo, 'cart_id');
            $goodsInfo = array_diff_key($goodsInfo, ['cart_id' => '']);
        }

        foreach ($goodsInfo as $goodsInfoIndex => $goods) {
            //商品不合法
            if ($goods['total'] <= 0 || $goods['goods_id'] <= 0) {
                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_GOODS_INVALID_ERROR);
            }

            //拼装数组映射key
            $key = $goods['option_id'] > 0 ? 'option_' . $goods['option_id'] : 'goods_' . $goods['goods_id'];

            //组装映射商品
            $this->goodsMaps[$key] = $goods;

            //追加商品id
            $this->goodsIds[] = $goods['goods_id'];
            //如果有规格id 则追加

            $goods['option_id'] > 0 && $this->optionIds[] = $goods['option_id'];
        }

        return;
    }


    /**
     * 获取商品ID
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getGoodsIds(): array
    {
        return $this->goodsIds;
    }

    /**
     * 获取商品规格ID
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getOptionIds(): array
    {
        return $this->optionIds;
    }

    /**
     * 获取商品映射
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getGoodsMap(): array
    {
        return $this->goodsMaps;
    }

    /**
     * 是否来自购物车
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function getIsCart()
    {
        return $this->isCart;
    }

    /**
     * 获取购物车IDs
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getCartIds()
    {
        return $this->cartIds;
    }

    /**
     * 如果来源是购物车，则下单结束删除购物车
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function deleteCart()
    {
        return $this->isCart && GoodsCartModel::deleteAll(['id' => $this->cartIds]);
    }

    /**
     * 修改购物车数量
     * @param int $memberId
     * @param array $goodsInfo
     * @author 青岛开店星信息技术有限公司.
     */
    private function updateTotal(int $memberId, array $goodsInfo)
    {

        foreach ($goodsInfo as $item) {

            /**
             * @var $model GoodsCartModel
             */
            $model = GoodsCartModel::find()->where([
                'goods_id' => $item['goods_id'],
                'option_id' => $item['option_id'],
                'member_id' => $memberId
            ])->one();

            if (empty($model)) {
                continue;
            }

            //修改数量
            $model->total = $item['total'];

            //保存
            $model->save();
        }


    }

}