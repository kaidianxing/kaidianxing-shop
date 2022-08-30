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

namespace shopstar\services\creditShop;

use shopstar\constants\coupon\CouponConstant;
use shopstar\constants\creditShop\CreditShopConstant;
use shopstar\constants\creditShop\CreditShopGoodsTypeConstant;
use shopstar\constants\creditShop\CreditShopLogConstant;
use shopstar\constants\goods\GoodsDispatchTypeConstant;
use shopstar\exceptions\creditShop\CreditShopException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ExcelHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\StringHelper;
use shopstar\helpers\ValueHelper;
use shopstar\helpers\VideoHelper;
use shopstar\models\creditShop\CreditShopGoodsModel;
use shopstar\models\creditShop\CreditShopGoodsOptionModel;
use shopstar\models\form\FormModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\goods\spec\GoodsSpecModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\DispatchModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\core\attachment\CoreAttachmentService;
use shopstar\services\material\IndexService;
use shopstar\services\shop\ShopSettingIntracityLogic;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * 积分商城商品服务类
 * Class CreditShopGoodsService.
 * @package shopstar\services\creditShop
 */
class CreditShopGoodsService
{
    /**
     * @var array 导出字段
     */
    public static array $column = [
        ['title' => '商品标题', 'field' => 'title', 'width' => 24],
        ['title' => '商品类型', 'field' => 'type_text', 'width' => 24],
        ['title' => '积分价', 'field' => 'credit', 'width' => 24],
        ['title' => '剩余库存', 'field' => 'credit_shop_stock', 'width' => 24],
        ['title' => '销量', 'field' => 'sale', 'width' => 24],
        ['title' => '销售积分', 'field' => 'pay_credit', 'width' => 24],
        ['title' => '销售金额（元）', 'field' => 'pay_price', 'width' => 24],
        ['title' => '创建时间', 'field' => 'created_at', 'width' => 24],
        ['title' => '积分商品状态', 'field' => 'status_text', 'width' => 24],
    ];

    /**
     * 是否已添加过
     * @param int $type
     * @param int $goodsId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isExists(int $type, int $goodsId): bool
    {
        return CreditShopGoodsModel::find()->where(['type' => $type, 'is_delete' => 0, 'goods_id' => $goodsId])->exists();
    }

    /**
     * 检查保存数据
     * @param array $data
     * @param bool $isEdit
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public function checkSaveData(array $data, bool $isEdit = false)
    {
        // 取选择的商品id
        $goodsId = array_column($data['goods'], 'goods_id');

        // 商品
        if ($data['type'] == CreditShopGoodsTypeConstant::GOODS) {
            // 获取商品
            $shopGoods = GoodsModel::find()->select(['id', 'title', 'status', 'stock', 'reduction_type', 'is_deleted', 'has_option'])->where(['id' => $goodsId])->indexBy('id')->get();
            foreach ($data['goods'] as $item) {
                // 判断是否已添加过
                if (!$isEdit && self::isExists($data['type'], $item['goods_id'])) {
                    return error('商品[' . $shopGoods[$item['goods_id']]['title'] . ']已存在');
                }
                // 商品不存在 或 商品规格不一致
                if (empty($shopGoods[$item['goods_id']]) || $shopGoods[$item['goods_id']]['has_option'] != $item['has_option']) {
                    return error('商品信息错误');
                }

                // 判断上下架状态
                if ($data['status'] == 1 && ($shopGoods[$item['goods_id']]['is_deleted'] != 0 || $shopGoods[$item['goods_id']]['status'] == 0)) {
                    return error('商品[' . $shopGoods[$item['goods_id']]['title'] . ']已下架/已删除');
                }
                // 不是多规格
                if ($item['has_option'] == 0) {
                    if (empty($item['credit_shop_credit'])) {
                        return error('商品[' . $shopGoods[$item['goods_id']]['title'] . ']积分价格不能为空');
                    }
                    if (empty($item['credit_shop_stock'])) {
                        return error('商品[' . $shopGoods[$item['goods_id']]['title'] . ']库存不能为空');
                    }
                    // 不是永不减库存
                    if ($shopGoods['reduction_type'] != 2 && $item['stock'] > $shopGoods[$item['goods_id']]['stock']) {
                        return error('商品[' . $shopGoods[$item['goods_id']]['title'] . ']库存不能大于原商品库存');
                    }
                } else {
                    // 多规格
                    if (empty($item['rules'])) {
                        return error('商品[' . $shopGoods[$item['goods_id']]['title'] . ']至少选择一个规格参与积分商城');
                    }
                    // 获取规格
                    $shopGoodsOption = GoodsOptionModel::find()->select(['id', 'stock'])->where(['goods_id' => $item['goods_id']])->indexBy('id')->get();
                    foreach ($item['rules'] as $rule) {
                        if ($rule['is_join'] == 0) {
                            continue;
                        }
                        // 商品信息错误
                        if (empty($shopGoodsOption[$rule['option_id']])) {
                            return error('规格不存在');
                        }
                        if (empty($rule['credit_shop_credit'])) {
                            return error('商品[' . $shopGoods[$item['goods_id']]['title'] . ']积分价格不能为空');
                        }
                        if (empty($rule['credit_shop_stock'])) {
                            return error('商品[' . $shopGoods[$item['goods_id']]['title'] . ']库存不能为空');
                        }
                        // 不是永不减库存
                        if ($shopGoods['reduction_type'] != 2 && $rule['stock'] > $shopGoodsOption[$rule['option_id']]['stock']) {
                            return error('商品[' . $shopGoods[$item['goods_id']]['title'] . ']库存不能大于原商品库存');
                        }
                    }
                }
            }

        } else { // 优惠券
            // 获取优惠券
            $shopCoupons = CouponModel::find()->select(['id', 'coupon_name', 'stock', 'get_total', 'state', 'stock_type', 'time_limit', 'end_time'])->where(['id' => $goodsId])->indexBy('id')->get();
            foreach ($data['goods'] as $item) {

                // 优惠券不存在
                if (empty($shopCoupons[$item['goods_id']])) {
                    return error('商品信息错误');
                }

                // 判断是否已添加过
                if (!$isEdit && self::isExists($data['type'], $item['goods_id'])) {
                    return error('商品[' . $shopCoupons[$item['goods_id']]['coupon_name'] . ']已存在');
                }

                // 判断上下架状态
                if ($data['status'] == 1 && $shopCoupons[$item['goods_id']]['state'] == 0) {
                    return error('商品[' . $shopCoupons[$item['goods_id']]['coupon_name'] . ']已停止发放');
                }

                // 判断过期
                if ($shopCoupons[$item['goods_id']]['time_limit'] == 0 && $shopCoupons[$item['goods_id']]['end_time'] < DateTimeHelper::now()) {
                    return error('商品[' . $shopCoupons[$item['goods_id']]['coupon_name'] . ']已过期');
                }

                if (empty($item['credit_shop_credit'])) {
                    return error('商品[' . $shopCoupons[$item['goods_id']]['coupon_name'] . ']积分价格不能为空');
                }
                if (empty($item['credit_shop_stock'])) {
                    return error('商品[' . $shopCoupons[$item['goods_id']]['coupon_name'] . ']库存不能为空');
                }
                // 如果不是不限制库存
                if ($shopCoupons['stock_type'] != 0 && $item['stock'] > ($shopCoupons['stock'] - $shopCoupons['get_total'])) {
                    return error('商品[' . $shopCoupons[$item['goods_id']]['coupon_name'] . ']库存不能大于原商品库存');
                }
            }
        }

        // 会员等级限制
        if ($data['member_level_limit_type'] != CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_NOT_LIMIT && empty($data['member_level_id'])) {
            return error('会员等级不能为空');
        }
        // 会员标签限制
        if ($data['member_group_limit_type'] != CreditShopConstant::MEMBER_GROUP_LIMIT_TYPE_NOT_LIMIT && empty($data['member_group_id'])) {
            return error('会员标签不能为空');
        }
        // 不是不限购
        if ($data['goods_limit_type'] != CreditShopConstant::GOODS_LIMIT_TYPE_NOT_LIMIT && empty($data['goods_limit_num'])) {
            return error('限购数量不能为空');
        }
        if ($data['goods_limit_type'] == CreditShopConstant::GOODS_LIMIT_TYPE_LIMIT_DAY && empty($data['goods_limit_day'])) {
            return error('限购天数不能为空');
        }

        return true;
    }

    /**
     * 添加积分商品
     * @param array $data
     * @param int $userId
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public function add(array $data, int $userId)
    {
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            // 遍历商品 插入
            foreach ($data['goods'] as $item) {
                $creditShopGoods = new CreditShopGoodsModel();
                $creditShopGoods->setAttributes([
                    'goods_id' => $item['goods_id'],
                    'type' => $data['type'],
                    'has_option' => $item['has_option'],
                    'dispatch_type' => $data['dispatch_type'],
                    'member_level_limit_type' => $data['member_level_limit_type'],
                    'member_level_id' => $data['member_level_id'] ?: '',
                    'member_group_limit_type' => $data['member_group_limit_type'],
                    'member_group_id' => $data['member_group_id'] ?: '',
                    'goods_limit_type' => $data['goods_limit_type'],
                    'goods_limit_num' => $data['goods_limit_num'] ?: 0,
                    'goods_limit_day' => $data['goods_limit_day'] ?: 0,
                    'status' => $data['status'],
                ]);

                if ($item['has_option'] == 0) {
                    $creditShopGoods->setAttributes([
                        'credit_shop_credit' => $item['credit_shop_credit'],
                        'credit_shop_price' => $item['credit_shop_price'],
                        'min_price_credit' => $item['credit_shop_credit'],
                        'min_price' => $item['credit_shop_price'],
                        'credit_shop_stock' => $item['credit_shop_stock'],
                        'original_stock' => $item['credit_shop_stock'],
                    ]);
                }

                if (!$creditShopGoods->save()) {
                    $transaction->rollBack();
                    throw new CreditShopException(CreditShopException::ADD_GOODS_SAVE_FAIL, $creditShopGoods->getErrorMessage());
                }

                //如果是多规格
                if ($item['has_option'] == 1) {
                    // 保存多规格 计算最小价格 赋值积分商品
                    $optionFields = ['goods_id', 'credit_shop_goods_id', 'option_id', 'credit_shop_credit', 'credit_shop_price', 'credit_shop_stock', 'original_stock', 'is_join'];
                    $optionData = [];
                    // 先赋值最大值
                    $creditShopGoods->min_price = 99999999.99;
                    $creditShopGoods->min_price_credit = 999999999;
                    $creditShopGoods->credit_shop_stock = 0;
                    $creditShopGoods->original_stock = 0;
                    $shopGoodsOption = GoodsOptionModel::find()->select(['id', 'title'])->where(['goods_id' => $item['goods_id']])->indexBy('id')->get();

                    foreach ($item['rules'] as $rule) {
                        // 库存
                        if ($rule['is_join']) {
                            $creditShopGoods->credit_shop_stock += $rule['credit_shop_stock'];
                            $creditShopGoods->original_stock += $rule['credit_shop_stock'];

                            // 计算最小金额
                            if ($creditShopGoods->min_price > $rule['credit_shop_price']) {
                                $creditShopGoods->min_price = $rule['credit_shop_price'];
                                if ($creditShopGoods->min_price_credit > $rule['credit_shop_credit']) {
                                    $creditShopGoods->min_price_credit = $rule['credit_shop_credit'];
                                }
                            }
                        }

                        $optionData[] = [
                            $item['goods_id'],
                            $creditShopGoods->id,
                            $rule['option_id'],
                            $rule['credit_shop_credit'] ?: 0,
                            $rule['credit_shop_price'] ?: 0,
                            $rule['credit_shop_stock'] ?: 0,
                            $rule['credit_shop_stock'] ?: 0,
                            $rule['is_join'],
                        ];

                        $logOption[] = [
                            'title' => $shopGoodsOption[$rule['option_id']]['title'],
                            'is_join' => $rule['is_join'] ? '参与' : '不参与',
                            'credit_shop_credit' => $rule['credit_shop_credit'] ?: 0,
                            'credit_shop_price' => $rule['credit_shop_price'] ?: 0,
                            'credit_shop_stock' => $rule['credit_shop_stock'] ?: 0,
                        ];
                    }
                    CreditShopGoodsOptionModel::batchInsert($optionFields, $optionData);
                }

                if (!$creditShopGoods->save()) {
                    $transaction->rollBack();
                    throw new CreditShopException(CreditShopException::ADD_GOODS_SAVE_FAIL, $creditShopGoods->getErrorMessage());
                }

                // 操作日志
                // 日志
                $logPrimaryData = [
                    'id' => $creditShopGoods->id,
                    'type' => $creditShopGoods->type ? '优惠券' : '商品',
                    'credit_shop_credit' => $creditShopGoods->credit_shop_credit,
                    'credit_shop_price' => $creditShopGoods->credit_shop_price,
                    'credit_shop_stock' => $creditShopGoods->credit_shop_stock,
                    'dispatch_type' => $creditShopGoods->dispatch_type ? '包邮' : '读取系统',
                    'member_level_limit_type' => $creditShopGoods->member_level_limit_type == 0 ? '不限制' : ($creditShopGoods->member_level_limit_type == 1 ? '指定可购买' : '指定不可购买'),
                    'member_group_limit_type' => $creditShopGoods->member_group_limit_type == 0 ? '不限制' : ($creditShopGoods->member_group_limit_type == 1 ? '指定可购买' : '指定不可购买'),
                    'goods_limit_type' => $creditShopGoods->goods_limit_type == 0 ? '不限制' : ($creditShopGoods->member_group_limit_type == 1 ? '每人限购' : '每人每n天限购'),
                    'goods_limit_num' => $creditShopGoods->goods_limit_type != 0 ? $creditShopGoods->goods_limit_num : '-',
                    'goods_limit_day' => $creditShopGoods->goods_limit_type == 2 ? $creditShopGoods->goods_limit_day : '-',
                    'status' => $creditShopGoods->status == 1 ? '上架' : '下架',
                ];

                if ($creditShopGoods->type == CreditShopGoodsTypeConstant::COUPON) {
                    $shopCoupons = CouponModel::find()->select(['id', 'coupon_name', 'stock', 'get_total', 'state', 'stock_type', 'time_limit', 'end_time'])->where(['id' => $creditShopGoods->goods_id])->first();
                    $logPrimaryData['title'] = $shopCoupons['coupon_name'];
                } else {
                    $shopGoods = GoodsModel::find()->select(['id', 'title', 'status', 'stock', 'reduction_type', 'is_deleted', 'has_option'])->where(['id' => $creditShopGoods->goods_id])->first();
                    $logPrimaryData['title'] = $shopGoods['title'];
                }

                if (!empty($logOption)) {
                    $logPrimaryData['option'] = $logOption;
                }

                // 记录日志
                LogModel::write(
                    $userId,
                    CreditShopLogConstant::ADD,
                    CreditShopLogConstant::getText(CreditShopLogConstant::ADD),
                    $creditShopGoods->id,
                    [
                        'log_data' => $creditShopGoods->attributes,
                        'log_primary' => $creditShopGoods->getLogAttributeRemark($logPrimaryData),
                        'dirty_identify_code' => [
                            CreditShopLogConstant::ADD,
                            CreditShopLogConstant::EDIT,
                        ]
                    ]
                );
            }

            $transaction->commit();

            return true;
        } catch (Throwable $exception) {
            $transaction->rollBack();
            return error($exception->getMessage());
        }
    }

    /**
     * 编辑积分商品
     * @param array $data
     * @param int $userId
     * @return void
     * @throws CreditShopException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function edit(array $data, int $userId)
    {
        // 查找商品
        $creditShopGoods = CreditShopGoodsModel::findOne(['id' => $data['id'], 'is_delete' => 0]);

        if (empty($creditShopGoods)) {
            throw new CreditShopException(CreditShopException::EDIT_GOODS_NOT_EXISTS);
        }

        $goods = $data['goods'][0];
        $creditShopGoods->setAttributes([
            'has_option' => $goods['has_option'],
            'dispatch_type' => $data['dispatch_type'],
            'member_level_limit_type' => $data['member_level_limit_type'],
            'member_level_id' => $data['member_level_id'] ?: '',
            'member_group_limit_type' => $data['member_group_limit_type'],
            'member_group_id' => $data['member_group_id'] ?: '',
            'goods_limit_type' => $data['goods_limit_type'],
            'goods_limit_num' => $data['goods_limit_num'] ?: 0,
            'goods_limit_day' => $data['goods_limit_day'] ?: 0,
            'status' => $data['status'],
        ]);

        // 单规格
        if ($creditShopGoods->has_option == 0) {
            $creditShopGoods->setAttributes([
                'credit_shop_credit' => $goods['credit_shop_credit'],
                'credit_shop_price' => $goods['credit_shop_price'],
                'min_price_credit' => $goods['credit_shop_credit'],
                'min_price' => $goods['credit_shop_price'],
                'credit_shop_stock' => $goods['credit_shop_stock'],
                'original_stock' => $goods['credit_shop_stock'],
            ]);
        } else {
            // 多规格
            // 先删除旧的
            CreditShopGoodsOptionModel::deleteAll(['credit_shop_goods_id' => $creditShopGoods->id]);

            // 重新保存
            // 保存多规格 计算最小价格 赋值积分商品
            $optionFields = ['goods_id', 'credit_shop_goods_id', 'option_id', 'credit_shop_credit', 'credit_shop_price', 'credit_shop_stock', 'original_stock', 'is_join'];
            $optionData = [];
            // 先赋值最大值
            $creditShopGoods->min_price = 99999999.99;
            $creditShopGoods->min_price_credit = 999999999;
            $creditShopGoods->credit_shop_stock = 0;
            $creditShopGoods->original_stock = 0;

            $shopGoodsOption = GoodsOptionModel::find()->select(['id', 'title'])->where(['goods_id' => $creditShopGoods->goods_id])->indexBy('id')->get();

            foreach ($goods['rules'] as $rule) {
                // 库存
                if ($rule['is_join']) {
                    $creditShopGoods->credit_shop_stock += $rule['credit_shop_stock'];
                    $creditShopGoods->original_stock += $rule['credit_shop_stock'];

                    // 计算最小金额
                    if ($creditShopGoods->min_price > $rule['credit_shop_price']) {
                        $creditShopGoods->min_price = $rule['credit_shop_price'];
                        if ($creditShopGoods->min_price_credit > $rule['credit_shop_credit']) {
                            $creditShopGoods->min_price_credit = $rule['credit_shop_credit'];
                        }
                    }
                }

                $optionData[] = [
                    $goods['goods_id'],
                    $creditShopGoods->id,
                    $rule['option_id'],
                    $rule['credit_shop_credit'],
                    $rule['credit_shop_price'],
                    $rule['credit_shop_stock'],
                    $rule['credit_shop_stock'],
                    $rule['is_join']
                ];

                $logOption[] = [
                    'title' => $shopGoodsOption[$rule['option_id']]['title'],
                    'is_join' => $rule['is_join'] ? '参与' : '不参与',
                    'credit_shop_credit' => $rule['credit_shop_credit'] ?: 0,
                    'credit_shop_price' => $rule['credit_shop_price'] ?: 0,
                    'credit_shop_stock' => $rule['credit_shop_stock'] ?: 0,
                ];
            }
            CreditShopGoodsOptionModel::batchInsert($optionFields, $optionData);
        }

        if (!$creditShopGoods->save()) {
            throw new CreditShopException(CreditShopException::EDIT_GOODS_SAVE_FAIL);
        }

        $logPrimaryData = [
            'id' => $creditShopGoods->id,
            'type' => $creditShopGoods->type ? '优惠券' : '商品',
            'credit_shop_credit' => $creditShopGoods->credit_shop_credit,
            'credit_shop_price' => $creditShopGoods->credit_shop_price,
            'credit_shop_stock' => $creditShopGoods->credit_shop_stock,
            'dispatch_type' => $creditShopGoods->dispatch_type ? '包邮' : '读取系统',
            'member_level_limit_type' => $creditShopGoods->member_level_limit_type == 0 ? '不限制' : ($creditShopGoods->member_level_limit_type == 1 ? '指定可购买' : '指定不可购买'),
            'member_group_limit_type' => $creditShopGoods->member_group_limit_type == 0 ? '不限制' : ($creditShopGoods->member_group_limit_type == 1 ? '指定可购买' : '指定不可购买'),
            'goods_limit_type' => $creditShopGoods->goods_limit_type == 0 ? '不限制' : ($creditShopGoods->member_group_limit_type == 1 ? '每人限购' : '每人每n天限购'),
            'goods_limit_num' => $creditShopGoods->goods_limit_type != 0 ? $creditShopGoods->goods_limit_num : '-',
            'goods_limit_day' => $creditShopGoods->goods_limit_type == 2 ? $creditShopGoods->goods_limit_day : '-',
            'status' => $creditShopGoods->status == 1 ? '上架' : '下架',
        ];

        if ($creditShopGoods->type == CreditShopGoodsTypeConstant::COUPON) {
            $shopCoupons = CouponModel::find()->select(['id', 'coupon_name', 'stock', 'get_total', 'state', 'stock_type', 'time_limit', 'end_time'])->where(['id' => $creditShopGoods->goods_id])->first();
            $logPrimaryData['title'] = $shopCoupons['coupon_name'];
        } else {
            $shopGoods = GoodsModel::find()->select(['id', 'title', 'status', 'stock', 'reduction_type', 'is_deleted', 'has_option'])->where(['id' => $creditShopGoods->goods_id])->first();
            $logPrimaryData['title'] = $shopGoods['title'];
        }

        if (!empty($logOption)) {
            $logPrimaryData['option'] = $logOption;
        }
        // 记录日志
        LogModel::write(
            $userId,
            CreditShopLogConstant::EDIT,
            CreditShopLogConstant::getText(CreditShopLogConstant::EDIT),
            $creditShopGoods->id,
            [
                'log_data' => $creditShopGoods->attributes,
                'log_primary' => $creditShopGoods->getLogAttributeRemark($logPrimaryData),
                'dirty_identify_code' => [
                    CreditShopLogConstant::ADD,
                    CreditShopLogConstant::EDIT,
                ]
            ]
        );
    }

    /**
     * 删除积分商品
     * @param int $id
     * @param int $userId
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function delete(int $id, int $userId)
    {
        // 删除
        CreditShopGoodsModel::updateAll(['is_delete' => 1], ['id' => $id]);

        // 操作日志
        // 日志
        LogModel::write(
            $userId,
            CreditShopLogConstant::DELETE,
            CreditShopLogConstant::getText(CreditShopLogConstant::DELETE),
            $id,
            [
                'log_data' => [],
                'log_primary' => [
                    'id' => $id,
                ],
                'dirty_identify_code' => [
                    CreditShopLogConstant::DELETE,
                ],
            ]
        );
    }

    /**
     * 修改状态
     * @param int $id
     * @param int $status
     * @param int $userId
     * @return void
     * @throws CreditShopException
     * @author 青岛开店星信息技术有限公司
     */
    public function changeStatus(int $id, int $status, int $userId)
    {
        $creditShopGoods = CreditShopGoodsModel::findOne(['id' => $id, 'is_delete' => 0]);
        // 修改商品信息导致下架的 不允许上下架
        if ($creditShopGoods->status == -1) {
            throw new CreditShopException(CreditShopException::CHANGE_STATUS_STATUS_ERROR);
        }
        // 切换上架
        if ($status == 1) {
            // 验证原商品信息
            if ($creditShopGoods->type == CreditShopGoodsTypeConstant::GOODS) {
                // 查找商品
                $shopGoods = GoodsModel::find()->select(['id', 'title', 'status', 'stock', 'reduction_type', 'is_deleted', 'has_option'])->where(['id' => $creditShopGoods->goods_id])->first();
                // 是否删除 判断上下架状态
                if ($shopGoods['is_deleted'] != 0 || $shopGoods['status'] == 0) {
                    throw new CreditShopException(CreditShopException::CHANGE_STATUS_STATUS_ERROR);
                }
            } else {
                // 获取优惠券
                $shopCoupon = CouponModel::find()->select(['id', 'coupon_name', 'stock', 'get_total', 'state', 'stock_type', 'time_limit', 'end_time'])->where(['id' => $creditShopGoods->goods_id])->first();
                // 优惠券不存在
                if (empty($shopCoupon)) {
                    throw new CreditShopException(CreditShopException::CHANGE_STATUS_STATUS_ERROR);
                }
                // 判断上下架状态
                if ($shopCoupon['state'] == 0) {
                    throw new CreditShopException(CreditShopException::CHANGE_STATUS_STATUS_ERROR);
                }
                // 判断过期
                if ($shopCoupon['time_limit'] == 0 && $shopCoupon['end_time'] < DateTimeHelper::now()) {
                    throw new CreditShopException(CreditShopException::CHANGE_STATUS_STATUS_ERROR);
                }
            }
        }

        $creditShopGoods->status = $status;

        $creditShopGoods->save();

        // 操作日志
        LogModel::write(
            $userId,
            CreditShopLogConstant::OP,
            CreditShopLogConstant::getText(CreditShopLogConstant::OP),
            '0',
            [
                'log_data' => [],
                'log_primary' => [
                    'id' => $id,
                    '操作' => $status ? '上架' : '下架',
                ],
                'dirty_identify_code' => [
                    CreditShopLogConstant::OP,
                ],
            ]
        );
    }

    /**
     * 获取商品详情
     * @param int $id
     * @return array|null
     * @throws CreditShopException
     * @author 青岛开店星信息技术有限公司
     */
    public function detail(int $id): ?array
    {
        $detail = CreditShopGoodsModel::find()->where(['id' => $id, 'is_delete' => 0])->first();

        if (empty($detail)) {
            throw new CreditShopException(CreditShopException::DETAIL_GOODS_NOT_EXISTS);
        }

        // 获取商品
        if ($detail['type'] == CreditShopGoodsTypeConstant::GOODS) {
            $detail['goods'] = GoodsModel::find()->select(['id', 'title', 'type', 'thumb', 'stock', 'price', 'has_option', 'min_price', 'max_price', 'sales'])->where(['id' => $detail['goods_id']])->first();

            // 查找积分商品多规格
            if ($detail['has_option']) {
                $detail['goods']['rules'] = CreditShopGoodsOptionModel::find()
                    ->select([
                        'shop_option.stock option_stock', // 规格库存
                        'shop_option.price option_price', // 规格价格
                        'shop_option.title option_title', // 规格标题
                        'shop_option.weight option_weight', // 规格重量
                        'option.id',
                        'option.option_id',
                        'option.credit_shop_credit',
                        'option.credit_shop_price',
                        'option.credit_shop_stock',
                        'option.is_join',
                    ])
                    ->alias('option')
                    ->where(['option.credit_shop_goods_id' => $id])
                    ->leftJoin(GoodsOptionModel::tableName() . ' shop_option', 'shop_option.id=option.option_id')
                    ->get();
            }
        } else {
            $detail['goods'] = CouponModel::find()
                ->select(['id', 'coupon_name', 'coupon_sale_type', 'stock', 'get_total', 'balance', 'credit', 'stock_type', 'discount_price', 'enough'])
                ->where(['id' => $detail['goods_id']])
                ->first();
            // 如果是立减类型
            if ($detail['goods']['coupon_sale_type'] == CouponConstant::COUPON_SALE_TYPE_SUB) {
                $detail['goods']['content'] = '满' . ValueHelper::delZero($detail['goods']['enough']) . '减' . ValueHelper::delZero($detail['goods']['discount_price']);
            } else {
                // 打折类型
                $detail['goods']['content'] = '满' . ValueHelper::delZero($detail['goods']['enough']) . '享' . ValueHelper::delZero($detail['goods']['discount_price']) . '折';
            }
        }

        $detail['goods']['credit_shop_credit'] = $detail['credit_shop_credit'];
        $detail['goods']['credit_shop_price'] = $detail['credit_shop_price'];
        $detail['goods']['credit_shop_stock'] = $detail['credit_shop_stock'];

        unset($detail['credit_shop_credit']);
        unset($detail['credit_shop_price']);
        unset($detail['credit_shop_stock']);

        // 获取等级
        $detail['member_level'] = MemberLevelModel::find()->orderBy(['level' => SORT_ASC])->get();
        $detail['member_group'] = MemberGroupModel::find()->get();

        return $detail;
    }

    /**
     * 手机端商品详情
     * @param int $id
     * @param int $memberId
     * @return array
     * @throws CreditShopException
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function mobileDetail(int $id, int $memberId = 0): array
    {
        // 返回信息
        $data = [];

        $detail = CreditShopGoodsModel::find()->where(['id' => $id, 'is_delete' => 0, 'status' => 1])->first();
        if (empty($detail)) {
            // 积分商品不存在
            throw new CreditShopException(CreditShopException::MOBILE_DETAIL_GOODS_NOT_EXISTS);
        }

        $data['credit_shop'] = $detail;

        // 检测购买权限
        $data['perm']['buy'] = true;
        if (!empty($memberId)) {
            // 判断购买权限 会员等级和标签的限制 读自己设置的

            // 会员等级限制
            if ($detail['member_level_limit_type'] != CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_NOT_LIMIT) {
                $limitLevelId = explode(',', $detail['member_level_id']);
                $memberLevelId = MemberModel::find()->select('level_id')->where(['id' => $memberId])->first();

                // 无权限
                if (($detail['member_level_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_ALLOW && !in_array($memberLevelId['level_id'], $limitLevelId))
                    || ($detail['member_level_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_DENY && in_array($memberLevelId['level_id'], $limitLevelId))) {
                    $data['perm']['buy'] = false;
                }
            }

            // 标签限制
            if ($data['perm']['buy'] && $detail['member_group_limit_type'] != CreditShopConstant::MEMBER_GROUP_LIMIT_TYPE_NOT_LIMIT) {
                $limitGroupId = explode(',', $detail['member_group_id']);
                // 获取会员标签
                $memberGroupId = MemberGroupMapModel::getGroupIdByMemberId($memberId);
                // 判断有没有交集
                $isIntersect = array_intersect($limitGroupId, $memberGroupId);
                // 无权限
                if (($detail['member_group_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_ALLOW && !$isIntersect)
                    || ($detail['member_group_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_DENY && $isIntersect)) {
                    $data['perm']['buy'] = false;
                }
            }
        }


        // 获取商品
        if ($detail['type'] == CreditShopGoodsTypeConstant::GOODS) {
            $detail['goods'] = $this->getShopGoodsInfo($detail['goods_id'], $id);

            // 一键发圈
            $detail['goods']['material'] = IndexService::showMaterial($detail['id']);

            // 商城 0元包邮文案
            $detail['goods']['trade'] = ShopSettings::get('sysset.trade');

            $data['activity'] = $detail['goods']['activity'];
            $data['intracity'] = $detail['goods']['intracity'];
            unset($detail['goods']['activity']);
            unset($detail['goods']['intracity']);
            $data['data']['goods'] = $detail['goods'];

            //运费模板运费
            if ($detail['dispatch_type'] == 0) {
                if ($detail['goods']['dispatch_type'] == GoodsDispatchTypeConstant::GOODS_DISPATCH_TYPE_TEMPLATE) {
                    // 获取配送的设置
                    $dispatchInfo = DispatchModel::getNotDispatchArea($detail['goods']['dispatch_id']);
                    $data['data']['dispatch_template'] = [
                        'dispatch_price' => DispatchModel::getStartPrice($detail['goods']['dispatch_id']),
                        'delivery_type' => $dispatchInfo['dispatch_area_type'] ?? '',
                        'not_dispatch' => $dispatchInfo['dispatch_limit_area'] ?? '',
                    ];
                } else {
                    // 获取配送类型
                    $deliveryType = ShopSettings::get('sysset.express.address.delivery_type');

                    $deliveryType == 0 ? $denyArea = ShopSettings::get('sysset.express.address.deny_area') : $deliveryArea = ShopSettings::get('sysset.express.address.delivery_area');

                    $areaInfo = isset($denyArea) ? Json::decode($denyArea) : Json::decode($deliveryArea);
                    $data['data']['dispatch_template'] = [
                        'not_dispatch' => $areaInfo['text'] ?? '',
                        'delivery_type' => $deliveryType, // 配送类型
                    ];
                }
            }
        } else {
            $detail['goods'] = CouponModel::find()
                ->select(['id', 'coupon_name', 'coupon_sale_type', 'stock', 'get_total', 'balance', 'credit', 'stock_type', 'discount_price', 'enough'])
                ->where(['id' => $detail['goods_id']])
                ->first();

            // 如果是立减类型
            if ($detail['goods']['coupon_sale_type'] == CouponConstant::COUPON_SALE_TYPE_SUB) {
                $detail['goods']['content'] = '满' . ValueHelper::delZero($detail['goods']['enough']) . '减' . ValueHelper::delZero($detail['goods']['discount_price']);
            } else {
                // 打折类型
                $detail['goods']['content'] = '满' . ValueHelper::delZero($detail['goods']['enough']) . '享' . ValueHelper::delZero($detail['goods']['discount_price']) . '折';
            }
            $data['data']['goods'] = $detail['goods'];
        }

        //海报url
        $data['poster_url'] = ShopUrlHelper::wap('/pagesCreditShop/detail', [
            'id' => $id
        ], true);

        // 已购买数量 // 限制购买数量才查
        $data['data']['buy_num'] = 0;
        if ($detail['goods_limit_type'] != CreditShopConstant::GOODS_LIMIT_TYPE_NOT_LIMIT) {
            $limitDay = $detail['goods_limit_type'] == CreditShopConstant::GOODS_LIMIT_TYPE_LIMIT_DAY ? $detail['goods_limit_day'] : 0;
            $data['data']['buy_num'] = CreditShopOrderService::getBuyTotal($id, $memberId, $limitDay);
        }

        $data['plugin_account']['virtualAccount'] = true;

        // 插入访问记录
        CreditShopViewLogService::insertViewLog($memberId, $id);

        return $data;
    }

    /**
     * 获取商城商品详情
     * @param int $shopGoodsId
     * @param int $creditShopGoodsId
     * @return array|null
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    private function getShopGoodsInfo(int $shopGoodsId, int $creditShopGoodsId): ?array
    {
        $data = GoodsModel::find()
            ->select(['id', 'title', 'type', 'thumb', 'stock', 'price', 'has_option', 'min_price', 'max_price', 'sales',
                'form_id', 'form_status', 'dispatch_type', 'dispatch_id', 'thumb_all', 'content', 'ext_field', 'video', 'video_thumb', 'video_type',
                'is_all_verify', 'dispatch_verify', 'deduction_balance_type', 'deduction_balance', 'dispatch_type',
                'dispatch_price', 'dispatch_id', 'dispatch_express', 'dispatch_intracity', 'params_switch', 'sub_name', 'params'])
            ->where(['id' => $shopGoodsId])
            ->first();

        //转移扩展字段
        !empty($data['ext_field']) && $data['ext_field'] = Json::decode($data['ext_field']);
        !empty($data['thumb_all']) && $data['thumb_all'] = Json::decode($data['thumb_all']);
        //转移商品参数
        !empty($data['params']) && $data['params'] = Json::decode($data['params']);
        // 获取储存拼接路径
        $attachmentUrl = CoreAttachmentService::getRoot();
        !empty($data['content']) && $data['content'] = StringHelper::htmlToImages($data['content'], $attachmentUrl);
        // 商品单位
        $data['goods_unit'] = '件';

        // 处理主图视频
        if (!empty($data['content'])) {
            $data['content'] = VideoHelper::parseRichTextTententVideo($data['content']);
        }
        // 处理封面视频
        if (!empty($data['video']) && preg_match("/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is", $data['video'])) {
            $data['video'] = VideoHelper::getTententVideo($data['video']);
        }

        $expressEnable = ShopSettings::get('dispatch.express.enable');
        //获取同城配送设置
        $intracity = [
            'dispatch_price' => ShopSettingIntracityLogic::getDispatchPrice(),
            'dispatch_area' => ShopSettingIntracityLogic::getDispatchArea(),
            'shop_address' => ShopSettings::get('contact'),
            'express_enable' => $expressEnable,
            'intracity_enable' => ShopSettings::get('dispatch.intracity.enable'),
        ];

        $data['intracity'] = $intracity;
        $data['activity'] = [];

        // 余额抵扣
        if ($data['deduction_balance_type'] != 0) {
            // 获取积分余额抵扣设置
            $settings = ShopSettings::get('sale.basic.deduct');
            // 如果系统设置关闭 返回false
            if ($settings['balance_state'] != 0) {
                $data['activity']['balance'] = [
                    'deduction_balance' => $data['deduction_balance'], //可抵扣金额
                    'deduction_balance_type' => $data['deduction_balance_type'],//抵扣类型0是关闭 1不限制 2自定义抵扣最多
                ];
            }
        }

        //获取表单信息
        if (!empty($data['form_id']) && $data['form_status'] == 1) {
            $formName = FormModel::find()
                ->where([
                    'id' => $data['form_id'],
                    'status' => 1,
                    'is_deleted' => 0,
                ])
                ->select(['id', 'content', 'name'])
                ->asArray()->one();

            $data['form_name'] = $formName['name'];

            $data['form_data'] = $formName;
        }

        return $data;
    }

    /**
     * 获取商品规格规格
     * @param int $goodsId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getOption(int $goodsId): array
    {
        $data = [];
        $data['credit_shop'] = CreditShopGoodsModel::find()->where(['id' => $goodsId, 'is_delete' => 0])->first();

        $data['spec'] = GoodsSpecModel::getSpaceById($data['credit_shop']['goods_id']);

        $data['options'] = GoodsOptionModel::find()
            ->where(['goods_id' => $data['credit_shop']['goods_id']])
            ->asArray()->indexBy('specs')->all();

        // 查找积分商品多规格
        $creditShopOptions = CreditShopGoodsOptionModel::find()
            ->select([
                'option.id',
                'option.option_id',
                'option.credit_shop_credit',
                'option.credit_shop_price',
                'option.credit_shop_stock',
                'option.is_join',
            ])
            ->alias('option')
            ->where(['option.credit_shop_goods_id' => $goodsId, 'option.is_join' => 1])
            ->indexBy('option_id')
            ->get();

        foreach ($data['options'] as &$row) {
            $row['activity']['credit_shop'] = $creditShopOptions[$row['id']];
        }

        return $data;
    }

    /**
     * 导出exc
     * @param array $data
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function export(array $data)
    {
        // 处理导出数据
        foreach ($data as &$item) {
            if ($item['type'] == CreditShopGoodsTypeConstant::COUPON) {
                $item['title'] = $item['coupon_name'];

            }

            $item['type_text'] = CreditShopGoodsTypeConstant::getText($item['type']);
            $item['status_text'] = $item['status'] == 1 ? '上架' : '下架';

            if ($item['has_option']) {
                $item['credit'] = $item['rules']['min']['credit_shop_credit'] . '积分';
                if ($item['rules']['min']['credit_shop_price'] != 0) {
                    $item['credit'] .= '+￥' . $item['rules']['min']['credit_shop_price'];
                }
                $item['credit'] .= ' ~ ' . $item['rules']['max']['credit_shop_credit'];
                if ($item['rules']['max']['credit_shop_price'] != 0) {
                    $item['credit'] .= '+￥' . $item['rules']['max']['credit_shop_price'];
                }

            } else {
                $item['credit'] = $item['credit_shop_credit'] . '积分';
                if ($item['credit_shop_price'] != 0) {
                    $item['credit'] .= '+￥' . $item['credit_shop_price'];
                }
            }
        }

        ExcelHelper::export($data, self::$column, '积分商品导出');
        die;
    }
}
