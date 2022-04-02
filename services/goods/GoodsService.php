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
use shopstar\constants\goods\GoodsBuyButtonConstant;
use shopstar\constants\goods\GoodsReductionTypeConstant;
use shopstar\constants\goods\GoodsTypeConstant;
use shopstar\constants\log\goods\GoodsLogConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\broadcast\BroadcastGoodsModel;
use shopstar\models\commission\CommissionGoodsModel;
use shopstar\models\goods\GoodsActivityModel;
use shopstar\models\goods\GoodsCartModel;
use shopstar\models\goods\GoodsMemberLevelDiscountModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\goods\GoodsStockLogModel;
use shopstar\models\goods\label\GoodsLabelGroupModel;
use shopstar\models\goods\label\GoodsLabelModel;
use shopstar\models\goods\spec\GoodsSpecModel;
use shopstar\models\log\LogModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\virtualAccount\VirtualAccountDataModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class GoodsService extends BaseService
{
    private static $goodsField = [
        'id',
        'status',
        'is_deleted',
        'title',
        'sub_name',
        'short_name',
        'type',
        'thumb',
        'thumb_all',
        'video',
        'video_thumb',
        'video_type',
        'unit',
        'goods_sku',
        'bar_code',
        'sort_by',
        'stock',
        'reduction_type',
        'sales',
        'real_sales',
        'price',
        'min_price',
        'max_price',
        'cost_price',
        'original_price',
        'has_option',
        'content',
        'dispatch_type',
        'dispatch_price',
        'dispatch_id',
        'weight',
        'ext_field',
        'is_recommand',
        'is_hot',
        'is_new',
        'params_switch',
        'params',
        'deduction_credit_type',
        'deduction_credit',
        'deduction_credit_repeat',
        'deduction_balance_type',
        'deduction_balance',
        'deduction_balance_repeat',
        'pv_count',
        'single_full_unit_switch',
        'single_full_unit',
        'single_full_quota_switch',
        'single_full_quota',
        'browse_level_perm',
        'browse_tag_perm',
        'buy_level_perm',
        'buy_tag_perm',
        'member_level_discount_type',
        'is_commission',
        'auto_deliver',
        'auto_deliver_content',
        'dispatch_express',
        'dispatch_intracity',
        'form_status',
        'form_id',
        'is_checked',
        'dispatch_verify',
        'is_all_verify',
        'virtual_account_id',
        'give_credit_status',
        'give_credit_num',
    ];


    /**
     * 获取商品详情
     * @param int $goodsId
     * @param array $options
     * @return array
     * @throws GoodsException
     * @throws \yii\base\InvalidConfigException
     * @author terry
     */
    public static function getGoods(int $goodsId, array $options = []): array
    {
        $options = array_merge([
            'where' => [],
            'get_option' => 0, //是否获取规格
            'select' => []
        ], $options);

        $data = GoodsModel::find()
            ->where(['id' => $goodsId])
            ->andFilterWhere($options['where'])
            ->with([
                'category' => function ($query) {
                },
                'group', 'label'])
            ->select($options['select'] ?: self::$goodsField)
            ->asArray()->one();

        if (empty($data)) {
            throw new GoodsException(GoodsException::GOODS_GET_NOT_FOUND_ERROR);
        }

        //获取规格
        $data['has_option'] == 1 && $data['spec'] = GoodsSpecModel::getSpaceById($goodsId);

        $data['has_option'] == 1 && $options['get_option'] == 1 && $data['options'] = GoodsOptionModel::getListByGoodsId($goodsId);

        //转移扩展字段
        !empty($data['ext_field']) && $data['ext_field'] = Json::decode($data['ext_field']);

        //转移扩展字段
        !empty($data['thumb_all']) && $data['thumb_all'] = Json::decode($data['thumb_all']);

        //转移商品参数
        !empty($data['params']) && $data['params'] = Json::decode($data['params']);

        //获取商品分类id
        !empty($data['category']) && $data['category_id'] = array_column($data['category'], 'category_id');

        !empty($data['subShopCategory']) && $data['sub_shop_category_id'] = array_column($data['subShopCategory'], 'category_id');

        //获取商品分组id
        !empty($data['group']) && $data['group_id'] = array_column($data['group'], 'group_id');

        // 处理被禁用的标签组下的标签
        if (!empty($data['label'])) {
            foreach ($data['label'] as $labelKey => $labelValue) {
                GoodsLabelModel::getColl([
                    'select' => ['group_id', 'id'],
                    'where' => [
                        'id' => $labelValue['label_id']
                    ],
                ], [
                    'callable' => function (&$row) use (&$data, &$labelKey) {
                        $labelGroupStatus = GoodsLabelGroupModel::find()
                            ->where([
                                'id' => $row['group_id'],
                            ])
                            ->select(['status'])
                            ->first();
                        if ($labelGroupStatus && $labelGroupStatus['status'] == '0') {
                            unset($data['label'][$labelKey]);
                        }
                    }
                ]);
            }
        }

        //获取商品标签id
        !empty($data['label']) && $data['label_id'] = array_column($data['label'], 'label_id');

        // 获取储存拼接路径
        $attachmentUrl = CoreAttachmentService::getRoot();
        //修改商品详情
        !empty($data['content']) && $data['content'] = StringHelper::htmlToImages($data['content'], $attachmentUrl);

        // 获取会员等级折扣
        $discount = GoodsMemberLevelDiscountModel::getDiscount($goodsId);
        foreach ($discount as $value) {
            // 指定会员
            if (empty($value['option_id'])) {
                $data['member_level_discount'][$value->level_id] = [
                    'id' => $value->id,
                    'type' => $value->type,
                    'discount' => $value->discount
                ];
            } else {
                // 多规格
                $data['member_level_discount'][$value->option_id][$value->level_id] = [
                    'id' => $value->id,
                    'type' => $value->type,
                    'discount' => $value->discount
                ];
            }
        }

        // 获取分销佣金设置
        $data['commission'] = CommissionGoodsModel::getCommission($goodsId);

        // 获取是否活动商品 (只要有结束的活动  就算)
        $activity = GoodsActivityModel::find()
            ->select(['id', 'activity_type'])
            ->where(['goods_id' => $goodsId, 'is_delete_activity' => 0])
            ->andWhere(['>', 'end_time', DateTimeHelper::now()])
            ->get();
        if (!empty($activity)) {
            $data['is_activity_goods'] = 1;
            // 如果是秒杀
            $activityType = array_column($activity, 'activity_type');
            if (in_array('seckill', $activityType)) {
                $data['is_seckill'] = 1;
            }
        }

        return $data;
    }

    /**
     * 删除商品
     * @param $userId
     * @param $goodsId
     * @param $type
     * @return bool
     * @throws GoodsException
     * @author terry
     */
    public static function deleteGoods($userId, $goodsId, $type): bool
    {
        $model = GoodsModel::find()
            ->where(['id' => $goodsId])
            ->all();

        if (empty($model)) {
            throw new GoodsException(GoodsException::GOODS_DELETE_NOT_FOUND_ERROR);
        }

        foreach ($model as $key => $item) {
            /**
             * @var $item self
             */
            $item->is_deleted = 1;
            $result = $item->save();

            $logPrimary = $item->getLogAttributeRemark([
                'goods' => [
                    [
                        'title' => $item['title'],
                        'is_deleted' => '已删除'
                    ]
                ]
            ]);

            $result && LogModel::write(
                $userId,
                $type,
                GoodsLogConstant::getText($type),
                $goodsId,
                [
                    'log_data' => $item->attributes,
                    'log_primary' => $logPrimary,
                ]
            );

        }

        // 更新购物车
        GoodsCartModel::updateAll(['is_lose_efficacy' => 1, 'is_selected' => 0], ['goods_id' => $goodsId]);

        //删除小程序商品库
        BroadcastGoodsModel::deleteGoodsAndGoodsMapByGoodsId($goodsId);

        return true;
    }

    /**
     * 恢复商品
     * @param int $userId
     * @param $goodsId
     * @param $type
     * @return int
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public static function recover(int $userId, $goodsId, string $type)
    {
        $model = GoodsModel::find()
            ->where(['id' => $goodsId])
            ->all();

        if (empty($model)) {
            throw new GoodsException(GoodsException::GOODS_DELETE_NOT_FOUND_ERROR);
        }

        foreach ($model as $key => $item) {
            /**
             * @var $item self
             */
            $item->is_deleted = 0;
            $result = $item->save();

            $logPrimary = $item->getLogAttributeRemark([
                'goods' => [
                    'title' => $item['title'],
                    'is_deleted' => '已恢复'
                ]
            ]);


            $result && LogModel::write(
                $userId,
                $type,
                GoodsLogConstant::getText($type),
                $goodsId,
                [
                    'log_data' => $item->attributes,
                    'log_primary' => $logPrimary,
                ]
            );
        }

        return true;
    }


    /**
     * 永久删除商品
     * @param int $userId
     * @param $goodsId
     * @param $type
     * @return bool
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public static function foreverRemove(int $userId, $goodsId, $type): bool
    {
        $model = GoodsModel::find()
            ->where(['id' => $goodsId])
            ->all();

        if (empty($model)) {
            throw new GoodsException(GoodsException::GOODS_FOREVER_DELETE_DELETE_NOT_FOUND_ERROR);
        }

        $tr = \Yii::$app->db->beginTransaction();
        try {
            foreach ($model as $item) {
                /**
                 * @var $item self
                 */

                $item->is_deleted = 2;
                $result = $item->save();

                $logPrimary = $item->getLogAttributeRemark([
                    'goods' => [
                        'id' => $item->id,
                        'title' => $item->title,
                    ]
                ]);

                //添加日志
                $result && LogModel::write(
                    $userId,
                    $type,
                    GoodsLogConstant::getText($type),
                    $goodsId,
                    [
                        'log_data' => $item->attributes,
                        'log_primary' => $logPrimary,
                    ]
                );
            }

            $tr->commit();
        } catch (\Throwable $throwable) {
            $tr->rollBack();
            throw new GoodsException(GoodsException::GOODS_FOREVER_DELETE_DELETE_ERROR, $throwable->getMessage());
        }

        return true;
    }


    /**
     * 修改商品库存
     * @param bool $reduce 减少 true  增加 false
     * @param int $orderId
     * @param int|array $orderGoodsId 订单商品id
     * @param bool|string $reductionType 减库存方式 如果是false 则不判断减库存方式
     * @param array $options
     * @return bool|array
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateQty(bool $reduce, int $orderId, $orderGoodsId = [], $reductionType = false, array $options = [])
    {
        $options = array_merge([
            'transaction' => true,
            'reason' => ''
        ], $options);

        // 获取订单类型  分发减库存方法
        $order = OrderModel::find()->select(['id', 'activity_type'])->where(['id' => $orderId])->first();

        $options['transaction'] && $tr = \Yii::$app->db->beginTransaction();

        //获取下单商品
        $model = OrderGoodsModel::find()
            ->alias('order_goods')
            ->leftJoin(GoodsModel::tableName() . 'goods', 'goods.id=order_goods.goods_id')
            ->where(['order_goods.order_id' => $orderId,]);

        if (!empty($orderGoodsId)) {
            $model->andWhere([
                'order_goods.id' => $orderGoodsId
            ]);
        }

        // 获取可以减库存的商品
        if ($reductionType !== false) {
            $reductionTypeWhere[] = $reductionType;
            //如果不等于付款减库存 处理永不减库存
            if ($reductionType != GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_PAYMENT) {
                $reductionTypeWhere[] = GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_NOT_REDUCE;
            }

            $model->andWhere([
                'or',
                ['goods.reduction_type' => $reductionTypeWhere]
            ]);
        }

        $goodsList = $model->select([
            'goods.reduction_type',//减库存方式
            'order_goods.goods_id',
            'order_goods.title',
            'order_goods.option_id',
            'order_goods.option_title',
            'order_goods.total',
            'goods.type',
            'order_goods.order_id',
        ])
            ->asArray()
            ->all();

        foreach ($goodsList as $goodsListIndex => $goodsListItem) {

            //库存表达式
            $stockReduce = 'stock ' . ($reduce ? '- ' : '+ ') . $goodsListItem['total'];

            //销量表达式
            $realReduce = 'real_sales ' . ($reduce ? '+ ' : '- ') . $goodsListItem['total'];

            $data = [
                'real_sales' => new Expression($realReduce),
            ];

            //如果不等于永不减库存
            if ($goodsListItem['reduction_type'] != GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_NOT_REDUCE) {
                $data['stock'] = new Expression($stockReduce);

            }

            GoodsStockLogModel::saveData([
                'order_id' => $orderId,
                'goods_id' => $goodsListItem['goods_id'],
                'method' => $reduce ? 0 : 1,
                'stock' => $data['stock'] ? $goodsListItem['total'] : 0,
                'sales' => $goodsListItem['total'],
                'reason' => $options['reason']
            ]);

            $result = GoodsModel::updateAll($data, [
                'id' => $goodsListItem['goods_id'],
            ]);

            if (!$result) {
                //事务回滚
                $options['transaction'] && $tr->rollBack();
                return error('商品信息变更失败');
            }

            //如果是多规格 and 不是永不减库存 and 不是预约商品
            if ($goodsListItem['option_id'] > 0
                && $goodsListItem['reduction_type'] != GoodsReductionTypeConstant::GOODS_REDUCTION_TYPE_NOT_REDUCE
            ) {
                $optionResult = GoodsOptionModel::updateAll([
                    'stock' => new Expression($stockReduce)
                ], [
                    'id' => $goodsListItem['option_id'],
                    'goods_id' => $goodsListItem['goods_id'],
                ]);

                if (!$optionResult) {
                    //事务回滚
                    $options['transaction'] && $tr->rollBack();
                    return error('规格信息变更失败');
                }
            }
            // 如果是虚拟卡密订单 需要额外处理卡密库的库存数量
            if ($goodsListItem['type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
                VirtualAccountDataModel::updateVirtualAccountReduceStock($reduce, $goodsListItem['order_id'], $goodsListItem['total'], $goodsListItem['goods_id']);
            }
        }

        $options['transaction'] && $tr->commit();
        return true;
    }

    /**
     * 根据卡密库id查询绑定的商品列表
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function getGoodsByVirtualAccountId($id)
    {
        // 处理单规格商品的库存和售罄
        GoodsModel::updateAll(['stock' => 0], ['virtual_account_id' => $id]);

        // 处理多规格商品的库存和售罄并连带处理主商品表
        $goodsOptionsList = GoodsOptionModel::find()->where(['virtual_account_id' => $id])->select(['id', 'goods_id', 'stock'])->asArray()->all();

        if ($goodsOptionsList) {
            // 处理多规格
            $updateGoodsId = 0;
            foreach ($goodsOptionsList as $value) {
                GoodsOptionModel::updateAllCounters(['stock' => -$value['stock']], ['id' => $value['id']]);
                // 只更新一遍商品表
                if ($updateGoodsId == $value['goods_id']) {
                    continue;
                }
                GoodsModel::updateAllCounters(['stock' => -$value['stock']], ['id' => $value['goods_id']]);
                $updateGoodsId = $value['goods_id'];
            }
        }
    }

    /**
     *价格面议, 电话走商城配置时的获取电话
     * @param int $buyButtonType
     * @param array $buyButtonSettings
     * @return array|mixed|string
     * @throws GoodsException
     * @author nizengchao
     */
    public static function getBuyButtonTelephone(int $buyButtonType = 0, array $buyButtonSettings = [])
    {
        $telephone = '';
        if (!$buyButtonType || !$buyButtonSettings) {
            return $telephone;
        }
        if ($buyButtonType == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_TYPE_CUSTOM && $buyButtonSettings['click_type'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TYPE_CUSTOM && $buyButtonSettings['click_style'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_STYLE_PHONE && $buyButtonSettings['click_telephone_type'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TELEPHONE_TYPE_DEFAULT) {
            $telephone = self::getShopDefaultTel();
        }
        return $telephone;
    }

    /**
     * 获取商城配置的默认电话
     * @param false $error
     * @return array|mixed|string
     * @throws GoodsException
     * @author nizengchao
     */
    public static function getShopDefaultTel(bool $error = false)
    {
        // 获取商城配置
        $tel1 = ShopSettings::get('contact.tel1');
        if (!$tel1 && $error) {
            throw new GoodsException(GoodsException::GOODS_SAVE_BUY_BUTTON_GET_SHOP_TELEPHONE_ERROR);
        }
        return $tel1;
    }


    /**
     * 自定义购买按钮(价格面议)商品阻止加入购物车/购买/下单
     * @param $extField
     * @return bool
     * @throws GoodsException
     * @author nizengchao
     */
    public static function buyButtonGoodsBuyBlock($extField = []): bool
    {
        // 格式化
        if (!is_array($extField)) {
            $extField = Json::decode($extField) ?? [];
        }
        // 判断是否拦截
        if ($extField['buy_button_type'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_TYPE_CUSTOM && $extField['buy_button_settings']['click_type'] == GoodsBuyButtonConstant::GOODS_BUY_BUTTON_CLICK_TYPE_CUSTOM) {
            throw new GoodsException(GoodsException::CLIENT_BUY_BUTTON_GOODS_BUY_ERROR);
        }

        return true;
    }


    /**
     * 自定义购买按钮状态, 影响客户端商品列表的购买按钮价格文字显示与否和加入购物车按钮显示与否  0: 不显示价格文字,显示加购  1: 显示价格文字, 不显示加购
     * $buy_button_type == 1 && $buy_button_settings['click_type'] == 2 显示价格文字, 不显示加购
     * @param $buttonType
     * @param $buttonSettings
     * @return int
     * @author nizengchao
     */
    public static function getBuyButtonStatus($buttonType = 0, $buttonSettings = []): int
    {
        return 0;
    }

    /**
     * 判断订单商品是否为虚拟商品或虚拟卡密或预约
     * @param $orderInfo
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkOrderGoodsVirtualType($orderInfo)
    {
        $goodsInfo = $orderInfo['goods_info'];
        StringHelper::isJson($goodsInfo) && $goodsInfo = Json::decode($goodsInfo, true);
        $goodsInfoType = array_column($goodsInfo, 'type');
        if (in_array(GoodsTypeConstant::GOODS_TYPE_VIRTUAL, $goodsInfoType)
            || in_array(GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT, $goodsInfoType)
            || $orderInfo['order_type'] == OrderTypeConstant::ORDER_TYPE_CREDIT_SHOP_COUPON) {
            // 积分商城优惠券也当作虚拟商品
            return true;
        } else {
            return false;
        }
    }

}