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

namespace shopstar\mobile\product;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\exceptions\form\FormException;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\RequestHelper;
use shopstar\models\form\FormModel;
use shopstar\models\form\FormTempModel;
use shopstar\models\goods\GoodsActivityModel;
use shopstar\models\goods\GoodsCartModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\member\MemberFavoriteModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\goods\GoodsActivityService;
use shopstar\services\goods\GoodsCartService;
use shopstar\services\goods\GoodsService;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class CartController extends BaseMobileApiController
{
    public $configActions = [
        'needBindMobileActions' => [
            'change-total' => 'add_cart'
        ],
        'allowNotLoginActions' => [
            'get-count',
        ]
    ];

    /**
     * 购物车列表
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetList(): array
    {
        // 有效列表
        $list = GoodsCartService::getGoods($this->memberId);
        $totalPrice = 0;
        $selectedNum = 0;
        $selectedGoods = 0;
        if (!empty($list)) {

            //获取商品当前参与的活动
            $goodsIds = array_column($list, 'goods_id');
            $goodsActivity = GoodsActivityService::getJoinActivityByGoodsIdGroup($goodsIds, $this->clientType);

            // 获取活动预热
            $preheatActivity = GoodsActivityModel::getPreheatActivityExist($goodsIds, $this->clientType);
            if (!empty($preheatActivity)) {
                $preheatActivity = array_column($preheatActivity, null, 'goods_id');
            }
        }
        foreach ($list as $listIndex => &$listItem) {

            $listItem['is_soldout'] = $listItem['is_soldout'] ? true : false;
            $listItem['is_selected'] = $listItem['is_selected'] ? true : false;

            unset($listItem['has_option']);

            if ($listItem['is_selected']) {
                $selectedGoods++;
                $selectedNum += $listItem['total'];
                $totalPrice += (round2($listItem['price'] * $listItem['total']));
            }

            //赋值商品参与的活动
            if (isset($goodsActivity[$listItem['goods_id']])) {
                $listItem['activity_type'] = $goodsActivity[$listItem['goods_id']];
            }

            // 赋值活动预热
            if (isset($preheatActivity[$listItem['goods_id']])) {
                $listItem['preheat_activity_type'] = $preheatActivity[$listItem['goods_id']]['activity_type'];
            }
        }

        // 处理价格面议商品
        $list = GoodsActivityModel::doBuyButtonFilter($list);

        // 失效列表
        $loseList = GoodsCartModel::find()
            ->select(['cart.id', 'cart.goods_id', 'goods.thumb', 'goods.title', 'cart.is_lose_efficacy', 'goods.ext_field'])
            ->alias('cart')
            ->leftJoin(GoodsModel::tableName() . ' goods', 'goods.id=cart.goods_id')
            ->where([
                'cart.member_id' => $this->memberId,
                'cart.is_lose_efficacy' => 1
            ])
            ->orderBy(['cart.created_at' => SORT_DESC])
            ->get();

        // 填充价格面议buy_button_status数据
        array_walk($loseList, function (&$goods) {
            $goods['ext_field'] = Json::decode($goods['ext_field']) ?? [];
            $goods['buy_button_status'] = GoodsService::getBuyButtonStatus($goods['ext_field']['buy_button_type'], $goods['ext_field']['buy_button_settings']);
            unset($goods['ext_field']);
        });

        return $this->result([
            'list' => $list,
            'lose_list' => $loseList,
            'selected_num' => $selectedNum,
            'selected_goods' => $selectedGoods,
            'all_selected' => GoodsCartService::$allSelected,
            'total_price' => $totalPrice,
            'express_enable' => ShopSettings::get('dispatch.express.enable'),
            'intracity_enable' => ShopSettings::get('dispatch.intracity.enable')
        ]);
    }

    /**
     * 修改购物车数量
     * @return array|\yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeTotal()
    {
        $post = RequestHelper::post();

        if (empty($post) || $post['total'] < 0) {
            throw new GoodsException(GoodsException::CLIENT_GOODS_CART_CHANGE_TOTAL_PARAMS_ERROR);
        }

        if ($post['is_reelect'] == 1 && !empty($post['id'])) {
            // 重选的需要删除旧的
            GoodsCartModel::deleteAll(['id' => $post['id']]);
        }


        if (!$post['id']) {
            // 根据商品ID 判断购物车里是否有商品
            $existsReelect = GoodsCartModel::find()
                ->select([
                    'id',
                ])
                ->where([
                    'member_id' => $this->memberId,
                    'goods_id' => $post['goods_id'],
                    'option_id' => $post['option_id'],
                    'is_reelect' => 1,
                ])
                ->first();

            //如果有，删除之前的
            if ($existsReelect) {
                GoodsCartModel::deleteAll(['id' => $existsReelect['id']]);
            }
        }

        $goods = GoodsModel::find()
            ->where([
                'id' => $post['goods_id'],
                'status' => [1, 2],
            ])
            ->select(['id', 'title', 'thumb', 'price', 'has_option', 'form_id', 'form_status', 'ext_field',])
            ->asArray()
            ->one();

        if (empty($goods)) {
            throw new GoodsException(GoodsException::CLIENT_GOODS_CART_CHANGE_TOTAL_GOODS_NOT_FOUND_ERROR);
        }

        // 没有活动及活动预热时, 走价格面议拦截
        $is_activity_goods = RequestHelper::postInt('is_activity_goods');
        if (!$is_activity_goods) {
            GoodsService::buyButtonGoodsBuyBlock($goods['ext_field']);
        }

        if ($goods['has_option'] === '1' || $post['option_id'] > 0) {
            $option = GoodsOptionModel::find()
                ->where([
                    'id' => $post['option_id'],
                    'goods_id' => $post['goods_id'],
                ])
                ->select(['price', 'title'])
                ->asArray()->one();

            if (empty($option)) {
                throw new GoodsException(GoodsException::CLIENT_GOODS_CART_CHANGE_TOTAL_OPTION_NOT_FOUND_ERROR);
            }
            $goods['price'] = $option['price'];
        }

        unset($goods['has_option']);

        $cart = GoodsCartModel::find()->where([
            'member_id' => $this->memberId,
            'goods_id' => $post['goods_id'],
            'option_id' => $post['option_id']
        ])->one();


        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // 加购时 如果商品已失效 则删除(能加购说明商品可以加购了
            if ($cart->is_lose_efficacy == 1) {
                $cart->delete();
                unset($cart);
            }

            if (empty($cart)) {
                //购物车限制数量99
                $cartCount = GoodsCartModel::find()->where(['member_id' => $this->memberId])->count();
                if ($cartCount >= 99 && $post['mode'] == 1) {
                    throw new GoodsException(GoodsException::CLIENT_GOODS_CART_CHANGE_TOTAL_FULL_ERROR);
                }
                $cart = new GoodsCartModel();
                $cart->price = $goods['price'];
                $cart->goods_id = $post['goods_id'];
                $cart->option_id = (int)$post['option_id'];
                $cart->member_id = $this->memberId;
                $cart->is_selected = 0;

            } else {
                // 增加数量时，不改添加购物车时价格
                unset($goods['price']);
            }

            if ($post['mode'] == 1) {
                $total = intval($cart->total) + $post['total'];
                if ($total <= 0) {
                    throw new GoodsException(GoodsException::CLIENT_GOODS_CART_CHANGE_TOTAL_CANNOT_SUBTRACT_ERROR);
                }
            } elseif ($post['mode'] == 0) {
                $total = intval($cart->total) - $post['total'];
                if ($total <= 0) {
                    throw new GoodsException(GoodsException::CLIENT_GOODS_CART_CHANGE_TOTAL_NUMBER_LITTLE_ERROR);
                }
            } else { //如果mode = 2 则是固定数量
                $total = $post['total'];
            }

            $cart->total = $total;
            $cart->save();

            $formStatus = FormModel::getStatus($goods['form_id']);

            if ($goods['form_id'] && $goods['form_status'] == 1 && $formStatus) {

                //验证是否已经添加过
                $existsFormData = FormTempModel::find()
                    ->where([
                        'goods_id' => $goods['id'],
                        'cart_id' => $cart->id,
                    ])
                    ->select('id')
                    ->first();

                if (empty($existsFormData) || !empty($post['form_data']['content'])) {

                    if (empty($post['form_data']['form_id'])) {
                        throw new FormException(FormException::FORM_PAGE_SUBMIT_ID_NOT_EMPTY);
                    }

                    if (empty($post['form_data']['content'])) {
                        throw new FormException(FormException::FORM_PAGE_SUBMIT_CONTENT_NOT_EMPTY);
                    }

                    $params = [
                        'cart_id' => $cart->id,
                        'form_id' => $post['form_data']['form_id'],
                        'content' => $post['form_data']['content'],
                        'goods_id' => $post['goods_id'],
                    ];

                    $result = FormTempModel::submitTempData($this->memberId, $params);

                    if (is_error($result)) {
                        throw new FormException(FormException::FORM_PAGE_SUBMIT_INVALID, $result['message']);
                    }
                }

            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }

        return $this->success();
    }

    /**
     * 删除购物车
     * @return \yii\web\Response
     * @throws GoodsException|\yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete(): \yii\web\Response
    {

        $post = RequestHelper::post();

        if ($post['clear_all'] == 0) {
            if (empty($post['id'])) {
                throw new GoodsException(GoodsException::CLIENT_GOODS_CART_DELETE_ID_NOT_EMPTY);
            }
            $where['id'] = explode(',', $post['id']);
        } else {
            $where = ['member_id' => $this->memberId, 'is_selected' => 1];
        }

        $goods = GoodsCartModel::findAll($where);
        if (empty($goods)) {
            throw new GoodsException(GoodsException::CLIENT_GOODS_CART_DELETE_NOT_OPT_FOR_ERROR);
        }

        //如果移入收藏夹
        if ($post['favorite'] == 1) {
            $goodsId = array_column($goods, 'goods_id');
            MemberFavoriteModel::changeFavorite(true, $goodsId, $this->memberId);
        }

        GoodsCartModel::deleteAll($where);

        return $this->success();
    }

    /**
     * 选中购物车
     * @return \yii\web\Response
     * @throws GoodsException
     */
    public function actionSelect(): \yii\web\Response
    {
        $post = RequestHelper::post();

        //组装初始条件
        $where = ['member_id' => $this->memberId, 'is_lose_efficacy' => 0, 'is_reelect' => 0];
        if ($post['select_all'] == 0) {
            $where['id'] = $post['id'];
        }

        $limit = 50;

        $cartGoods = GoodsCartModel::find()
            ->where($where)
            ->select(['is_selected'])
            ->orderBy('created_at desc')
            ->limit($limit)
            ->column();

        if (in_array('0', $cartGoods)) {
            $value = '1';

            //购物车选择商品最多50件
            if ($post['select_all'] == 0) {
                $cartCount = GoodsCartModel::find()->where([
                    'member_id' => $this->memberId,
                    'is_selected' => 1
                ])->count();

                if ($cartCount >= $limit) {
                    throw new GoodsException(GoodsException::CLIENT_GOODS_CART_SELECT_MAXIMUM_BUY_ERROR);
                }
            } else {
                //先都改成未选中
                GoodsCartModel::updateAll(['is_selected' => 0], ['member_id' => $this->memberId]);

                $ids = GoodsCartModel::find()
                    ->where(['member_id' => $this->memberId, 'is_lose_efficacy' => 0])
                    ->select('id')
                    ->asArray()
                    ->orderBy('created_at desc')
                    ->limit($limit)
                    ->column();

                $where['id'] = $ids;
            }
        } else {
            $value = '0';
        }

        if (empty($cartGoods)) {
            $errorMsg = $post['select_all'] == 1 ? GoodsException::CLIENT_GOODS_CART_SELECT_CART_EMPTY_ERROR : GoodsException::CLIENT_GOODS_CART_SELECT_GOODS_NOT_IN_CART_ERROR;
            throw new GoodsException($errorMsg);
        }

        GoodsCartModel::updateAll(['is_selected' => $value], $where);

        return $this->success();
    }

    /**
     * 验证选中商品是否可同时下单
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCheckSelect()
    {
        $isPop = RequestHelper::post('is_pop'); // 是否从弹窗调用
        $goodsId = RequestHelper::post('goods_id'); // 从弹窗调用才传 否则用选中的商品
        $type = RequestHelper::post('type'); // 选择的结算方式

        if ($isPop == 1 && (empty($goodsId) || empty($type))) {
            // 参数错误
            throw new GoodsException(GoodsException::CLIENT_GOODS_CART_GOODS_PARAMS_ERROR);
        }


        // 获取商品信息
        if ($isPop) {
            $goods = GoodsCartService::getGoods($this->memberId, ['goods_id' => $goodsId]);
        } else {
            // 购物车选中的
            $goods = GoodsCartService::getGoods($this->memberId, ['is_selected' => 1]);
        }

        if (empty($goods)) {
            // 没有选择商品
            throw new GoodsException(GoodsException::CLIENT_GOODS_CART_GOODS_EMPTY);
        }

        //获取商品当前参与的活动
        $goodsActivity = GoodsActivityService::getJoinActivityByGoodsIdGroup(array_column($goods, 'goods_id'), $this->clientType);

        // 判断商品配送方式是否全部一致
        $dispatchGoods = []; // 配送方式下的商品
        $dispatchType = []; // 如果可以直接下单 支持的配送方式
        $isSuccess = 1; // 是否去下单

        foreach ($goods as $item) {

            //赋值商品参与的活动
            if (isset($goodsActivity[$item['goods_id']])) {
                $item['activity_type'] = $goodsActivity[$item['goods_id']];
            }

            $goodsType = [];
            // 快递
            if ($item['dispatch_express'] == 1) {
                $dispatchGoods['express'][] = $item;
                $goodsType[] = 'express';
            }
            // 同城
            if ($item['dispatch_intracity'] == 1) {
                $dispatchGoods['intracity'][] = $item;
                $goodsType[] = 'intracity';
            }

            // 第一次为空 赋值
            if ($isPop) {
                if (!empty($dispatchType)) {
                    // 比较差集
                    $goodsTypeTemp = array_intersect($dispatchType, $goodsType);
                    // 有交集 不能下单
                    if (!in_array($type, $goodsTypeTemp)) {
                        $isSuccess = 0;
                    }
                }
                // 保存上一次的类型
                $dispatchType = $goodsType;
            } else {
                if (!empty($dispatchType)) {
                    // 比较差集
                    $goodsTypeTemp = array_diff($dispatchType, $goodsType);
                    if (empty($goodsTypeTemp)) {
                        $goodsTypeTemp = array_diff($goodsType, $dispatchType);
                    }
                    // 有差集 不能下单
                    if (!empty($goodsTypeTemp)) {
                        $isSuccess = 0;
                    }
                }
                // 保存上一次的类型
                $dispatchType = $goodsType;

            }
        }

        if (!empty($type) && empty($dispatchGoods[$type])) {
            // 配送方式不支持
            throw new GoodsException(GoodsException::CLIENT_GOODS_CART_GOODS_DISPATCH_TYPE_ERROR);
        }

        // 弹窗 并且通过
        if ($isPop && $isSuccess) {
            // 其他商品置为未选中
            GoodsCartModel::updateAll(['is_selected' => 0], ['and', ['member_id' => $this->memberId], ['not in', 'goods_id', $goodsId]]);
        }

        // 如果不能去下单 遍历统计分组信息
        $countInfo = [];
        if (!$isSuccess) {

            foreach ($dispatchGoods as $key => &$value) {
                $totalPrice = 0;
                $total = 0;
                foreach ($value as &$item) {
                    $total += $item['total'];
                    $totalPrice += (round2($item['price'] * $item['total']));
                }

                // 统计
                $countInfo[$key]['total'] = $total;
                $countInfo[$key]['total_price'] = round2($totalPrice);

            }
        }

        return $this->result(['dispatch_type' => $dispatchType, 'is_success' => $isSuccess, 'goods_data' => $dispatchGoods, 'count_info' => $countInfo]);
    }

    /**
     * 获取购物车数量
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetCount()
    {
        if (empty($this->memberId)) {
            return $this->result(['count' => 0]);
        }

        $result = GoodsCartService::goodsCount($this->memberId);

        return $this->result(['count' => $result]);
    }

    /**
     * 一键清理失效商品
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCleanLoseGoods()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new GoodsException(GoodsException::CLIENT_GOODS_CART_CLEAN_ID_EMPTY);
        }
        GoodsCartModel::deleteAll(['id' => $id, 'member_id' => $this->memberId, 'is_lose_efficacy' => 1]);

        return $this->success();
    }

}
