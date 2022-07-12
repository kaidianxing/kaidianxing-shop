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

namespace shopstar\services\wxTransactionComponent;

use shopstar\components\wechat\helpers\MiniProgramWxTransactionComponentHelper;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\wxTransactionComponent\WxAuditCategoryConstant;
use shopstar\constants\wxTransactionComponent\WxTransactionComponentConstant;
use shopstar\constants\wxTransactionComponent\WxTransactionComponentLogConstant;
use shopstar\exceptions\wxTransactionComponent\WxAuditCategoryException;
use shopstar\exceptions\wxTransactionComponent\WxTransactionComponentException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\wxTransactionComponent\WxAuditCategoryModel;
use shopstar\models\wxTransactionComponent\WxTransactionComponentModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\helpers\Json;

class WxTransactionComponentService
{
    /**
     * 小程序跳转路径
     * 商品详情页面路径
     * @var string
     */
    public static string $path = 'kdxGoods/detail/index?goods_id=';

    /**
     * 售后详情小程序路径
     * @var string
     */
    public static string $refundPath = 'kdxOrder/refund/detail?order_id=';

    /**
     * 订单详情小程序路径
     * @var string
     */
    public static string $orderPath = 'kdxOrder/detail?order_id=';

    /**
     * 中台状态映射 上下架等
     * 状态不完全匹配 后期可完善
     * @var array
     */
    public static array $remoteStatus = [
        '0' => WxTransactionComponentConstant::REMOTE_STATUS_UP,
        '5' => WxTransactionComponentConstant::REMOTE_STATUS_UP,
        '9' => WxTransactionComponentConstant::REMOTE_STATUS_DOWN,
        '11' => WxTransactionComponentConstant::REMOTE_STATUS_DOWN,
        '12' => WxTransactionComponentConstant::REMOTE_STATUS_DOWN,
        '13' => WxTransactionComponentConstant::REMOTE_STATUS_DOWN,
    ];

    /**
     * 状态映射  审核中等
     * 状态不完全匹配 后期可完善
     * @var array
     */
    public static array $statusMap = [
        '0' => WxTransactionComponentConstant::STATUS_IN,
        '1' => WxTransactionComponentConstant::STATUS_IN,
        '2' => WxTransactionComponentConstant::STATUS_IN,
        '3' => WxTransactionComponentConstant::STATUS_ERROR,
        '4' => WxTransactionComponentConstant::STATUS_SUCCESS,
    ];

    /**
     * 配送状态映射  审核中等
     * 状态不完全匹配 后期可完善
     * @var array
     */
    public static array $dispatchTypeMap = [
        OrderDispatchExpressConstant::ORDER_DISPATCH_NOT_DELIVERY => '2',
        OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS => '1',
        OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH => '4',
        OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY => '3',
    ];

    /**
     * 添加商品
     * @param $goodsList
     * @param $categoryId
     * @param $categoryName
     * @param $goodsQualification
     * @return array|bool|mixed
     * @throws Exception
     * @throws InvalidConfigException
     * @throws WxTransactionComponentException
     * @throws WxAuditCategoryException
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function AddData($goodsList, $categoryId, $categoryName, $goodsQualification)
    {
        $oldGoodsQualification = $goodsQualification;

        if ($goodsQualification) {
            // 处理全路径
            foreach ($goodsQualification as &$value) {
                $value = CoreAttachmentService::getUrl($value);
            }
            unset($value);
        }

        foreach ($goodsList as $value) {
            $exists = WxTransactionComponentModel::findOne(['goods_id' => $value['id']]);

            $transaction = Yii::$app->db->beginTransaction();
            if (!$exists) {
                $insertData = [
                    'goods_id' => $value['id'],
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'status' => WxTransactionComponentConstant::STATUS_IN,
                    'remote_status' => WxTransactionComponentConstant::REMOTE_STATUS_DOWN,
                    'create_time' => DateTimeHelper::now(),
                ];
                $model = new WxTransactionComponentModel();
                $model->setAttributes($insertData);

                if ($model->save()) {
                    if ($oldGoodsQualification) {
                        // 进行资质图片的储存
                        WxAuditCategoryImagesService::addDataByWxId($model->id, $oldGoodsQualification);
                    }

                    $data = self::process($value, $categoryId, $goodsQualification);

                    $res = MiniProgramWxTransactionComponentHelper::add($data);

                    if (isset($res['errcode']) ? $res['errcode'] == 0 : $res['error'] == 0) {
                        LogModel::write(
                            0,
                            WxTransactionComponentLogConstant::WX_TRANSACTION_COMPONENT_ADD_GOODS,
                            WxTransactionComponentLogConstant::getText(WxTransactionComponentLogConstant::WX_TRANSACTION_COMPONENT_ADD_GOODS),
                            0,
                            [
                                'log_data' => [],
                                'log_primary' => [
                                    'id' => $model->id,
                                    '自定义交易组件' => '新增数据',
                                    '上传商品' => '',
                                ],
                            ]
                        );

                        $transaction->commit();
                    } else {
                        // 单独判断商品资质
                        if (isset($res['errcode']) ? $res['errcode'] == 1000008 : $res['error'] == 1000008) {
                            LogHelper::error('[WX_APP_ADD_GOODS_ERROR]', $res);

                            $transaction->rollBack();
                            throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_COMMODITY_QUALIFICATION_ERROR);
                        }

                        LogHelper::error('[WX_APP_ADD_GOODS_ERROR]', $res);
                        $transaction->rollBack();
                    }
                }
            } else {
                if ($exists->status == WxTransactionComponentConstant::STATUS_IN) {
                    throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_GOODS_UPLOAD_STATUS_IN_ERROR);
                }

                if ($oldGoodsQualification) {
                    // 进行资质图片的储存
                    WxAuditCategoryImagesService::addDataByWxId($exists->id, $oldGoodsQualification);
                }

                // 商品存在走更新
                $res = self::updateGoods($exists, $value, $categoryId, $categoryName, $goodsQualification);
                if (isset($res['errcode']) ? $res['errcode'] == 0 : $res['error'] == 0) {
                    LogModel::write(
                        0,
                        WxTransactionComponentLogConstant::WX_TRANSACTION_COMPONENT_UPDATE_GOODS,
                        WxTransactionComponentLogConstant::getText(WxTransactionComponentLogConstant::WX_TRANSACTION_COMPONENT_UPDATE_GOODS),
                        0,
                        [
                            'log_data' => [],
                            'log_primary' => [
                                'id' => $exists->id,
                                '商品名称' => $value['title'],
                                '商品状态' => '审核中',
                            ],
                        ]
                    );
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                }
            }
        }

        return $res;
    }

    /**
     * 统一处理参数
     * @param array $goodsInfo
     * @param int $categoryId
     * @param array $goodsQualification
     * @return array
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function process(array $goodsInfo, int $categoryId, array $goodsQualification): array
    {
        $temporaryImgRes = MiniProgramWxTransactionComponentHelper::uploadImg([
            'resp_type' => 1, // 0:此参数返回media_id，目前只用于品牌申请品牌和类目，推荐使用1：返回临时链接
            'upload_type' => 1, // 0:图片流，1:图片url
            'img_url' => CoreAttachmentService::getRoot() . $goodsInfo['thumb'],
        ]);

        $temporaryImg = $temporaryImgRes['img_info']['temp_img_url'];
        $data = [
            'out_product_id' => $goodsInfo['id'],
            'title' => $goodsInfo['title'],
            'path' => self::$path . $goodsInfo['id'],// 小程序商品路径
            'head_img' => [$temporaryImg],
            'third_cat_id' => $categoryId,
            'brand_id' => '2100000000', // 品牌id 无品牌2100000000
        ];

        // 商品资质图片
        if ($goodsQualification) {
            $data['qualification_pics'] = self::changeMapImages($goodsQualification);
        }

        if ($goodsInfo['has_option']) {
            $goodsOptionList = GoodsOptionModel::getListByGoodsId($goodsInfo['id']);
            foreach ($goodsOptionList as $value) {
                $data['skus'][] = [
                    'out_product_id' => $goodsInfo['id'],
                    'out_sku_id' => $value['id'],  // 如果是多规格商品,id需要取多规格的id
                    'thumb_img' => $temporaryImg,
                    'sale_price' => $value['price'] * 100,
                    'market_price' => $value['original_price'] * 100,
                    'stock_num' => $value['stock'],
                    'sku_attrs' => [
                        [
                            'attr_key' => $value['title'],// 销售属性key
                            'attr_value' => $value['title'],// 销售属性value
                        ]
                    ]
                ];
            }
        } else {
            $data['skus'][] = [
                'out_product_id' => $goodsInfo['id'],
                'out_sku_id' => $goodsInfo['id'], // 如果是单规格商品,id需要取商品的id
                'thumb_img' => $temporaryImg,
                'sale_price' => $goodsInfo['price'] * 100,
                'market_price' => $goodsInfo['original_price'] * 100,
                'stock_num' => $goodsInfo['stock'],
                'sku_attrs' => [
                    [
                        'attr_key' => '0',// 销售属性key
                        'attr_value' => '0',// 销售属性value
                    ]
                ]
            ];
        }

        return $data;
    }

    /**
     * 处理图片
     * @param array $goodsQualification
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function changeMapImages(array $goodsQualification): array
    {
        $images = [];
        foreach ($goodsQualification as $value) {
            $temporaryImgRes = MiniProgramWxTransactionComponentHelper::uploadImg([
                'resp_type' => 1, // 0:此参数返回media_id，目前只用于品牌申请品牌和类目，推荐使用1：返回临时链接
                'upload_type' => 1, // 0:图片流，1:图片url
                'img_url' => $value,
            ]);

            $images[] = $temporaryImgRes['img_info']['temp_img_url'];
        }

        return $images;
    }

    /**
     * 提交审核(更新商品)
     * @param $model
     * @param array $goodsInfo
     * @param int $categoryId
     * @param string $categoryName
     * @param array $goodsQualification
     * @param array $data
     * @return array|bool|mixed
     * @throws InvalidConfigException
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateGoods($model, array $goodsInfo, int $categoryId, string $categoryName, array $goodsQualification, array $data = [])
    {
        $result = [];
        if ($data) {
            return MiniProgramWxTransactionComponentHelper::update($data);
        }

        // 先更新状态
        WxTransactionComponentModel::updateAll(['status' => WxTransactionComponentConstant::STATUS_IN], ['id' => $model->id]);

        // 分类走事务
        $insertData = [
            'category_id' => $categoryId,
            'category_name' => $categoryName,
        ];

        $model->setAttributes($insertData);

        $transaction = Yii::$app->db->beginTransaction();
        if ($model->save()) {
            $data = self::process($goodsInfo, $categoryId, $goodsQualification);

            $result = MiniProgramWxTransactionComponentHelper::update($data);
            if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
                LogModel::write(
                    0,
                    WxTransactionComponentLogConstant::WX_TRANSACTION_COMPONENT_UPDATE_GOODS,
                    WxTransactionComponentLogConstant::getText(WxTransactionComponentLogConstant::WX_TRANSACTION_COMPONENT_UPDATE_GOODS),
                    0,
                    [
                        'log_data' => [],
                        'log_primary' => [
                            'id' => $model->id,
                            '商品名称' => $data['title'],
                            '商品状态' => '审核中',
                        ],
                    ]
                );

                $transaction->commit();
            } else {
                $transaction->rollBack();
                WxTransactionComponentModel::updateAll(['status' => WxTransactionComponentConstant::STATUS_ERROR], ['id' => $model->id]);
            }
        }

        return $result;
    }

    /**
     * 上下架
     * @param int $id
     * @param int $status
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateStatus(int $id, int $status)
    {
        $model = WxTransactionComponentModel::findOne(['id' => $id]);
        $data = [
            'out_product_id' => $model->goods_id,
        ];

        $result = MiniProgramWxTransactionComponentHelper::updateStatus($data, $status);
        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            WxTransactionComponentModel::updateAll(['remote_status' => $status], ['id' => $id]);

            $status == 1 && LogModel::write(
                0,
                WxTransactionComponentLogConstant::WX_TRANSACTION_COMPONENT_UPDATE_STATUS_DOWN_GOODS,
                WxTransactionComponentLogConstant::getText(WxTransactionComponentLogConstant::WX_TRANSACTION_COMPONENT_UPDATE_STATUS_DOWN_GOODS),
                0,
                [
                    'log_data' => [],
                    'log_primary' => [
                        'id' => $model->id,
                        '商品名称' => $data['title'],
                        '操作' => '已下架',
                    ],
                ]
            );
        }

        return $result;
    }

    /**
     * 删除
     * @param int $id
     * @return array|bool|mixed
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteGoods(int $id)
    {
        $model = WxTransactionComponentModel::findOne(['id' => $id]);
        // 审核中禁止删除
        if ($model->status == WxTransactionComponentConstant::STATUS_IN) {
            throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_STATUS_IN_NOT_DELETE_ERROR);
        }

        $data = [
            'out_product_id' => $model->goods_id
        ];

        $result = MiniProgramWxTransactionComponentHelper::delete($data);
        if (isset($result['errcode']) ? $result['errcode'] == 0 : ($result['error'] == 0 || $result['error'] == 1000011)) {// 1000011 微信端商品id不存在, 也直接删除
            WxTransactionComponentModel::deleteAll(['id' => $id]);

            LogModel::write(
                0,
                WxTransactionComponentLogConstant::WX_TRANSACTION_COMPONENT_DELETE_GOODS,
                WxTransactionComponentLogConstant::getText(WxTransactionComponentLogConstant::WX_TRANSACTION_COMPONENT_DELETE_GOODS),
                0,
                [
                    'log_data' => [],
                    'log_primary' => [
                        'id' => $model->id,
                        '商品名称' => $data['title'],
                        '操作' => '已删除',
                    ],
                ]
            );
        }

        return $result;
    }

    /**
     * 同步查询商品状态并更新到库中
     * @param $page
     * @param $pageSize
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getStatus($page, $pageSize)
    {
        $data = [
            'page' => $page,
            'page_size' => $pageSize,
            'need_edit_spu' => 0, // 0获取线上数据 1获取草稿数据
        ];

        $result = MiniProgramWxTransactionComponentHelper::getApproved($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            foreach ($result['spus'] as $value) {
                $updateData = [
                    'status' => self::$statusMap[$value['edit_status']],
                    'remote_status' => self::$remoteStatus[$value['status']],
                ];

                WxTransactionComponentModel::updateAll($updateData, ['goods_id' => $value['out_product_id']]);
            }
        }

        return $result;
    }

    /**
     * 免审更新
     * @param int $id
     * @return array|bool|mixed
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    public static function exemptionUpdate(int $id)
    {
        $model = WxTransactionComponentModel::findOne(['id' => $id]);
        if ($model) {
            // 仅支持审核成功的商品进行免审更新
            if ($model->status != WxTransactionComponentConstant::STATUS_SUCCESS) {
                throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_STATUS_IN_EXEMPTION_UPDATE_STATUS_ERROR);
            }

            $goodsInfo = GoodsModel::findOne(['id' => $model->goods_id, 'is_deleted' => 0]);
            if ($goodsInfo) {
                // 默认处理单规格
                $data = [
                    'out_product_id' => $model->goods_id,
                    'skus' => [
                        [
                            'out_sku_id' => $model->goods_id,
                            'sale_price' => $goodsInfo->price * 100,
                            'stock_num' => $goodsInfo->stock,
                        ]
                    ]
                ];

                if ($goodsInfo->has_option) {
                    // 处理多规格
                    $goodsOptionList = GoodsOptionModel::getListByGoodsId($model->goods_id);
                    if ($goodsOptionList) {
                        unset($data['skus']);
                        foreach ($goodsOptionList as $value) {
                            $data['skus'][] =
                                [
                                    'out_sku_id' => $value['id'],
                                    'sale_price' => $value['price'] * 100,
                                    'stock_num' => $value['stock'],
                                ];
                        }

                    }
                }

                $result = MiniProgramWxTransactionComponentHelper::exemptionUpdate($data);
            }
        }

        return $result ?? [];
    }

    /**
     * 撤回审核
     * @param $id
     * @return array|bool|mixed
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    public static function resetAudit($id)
    {
        $model = WxTransactionComponentModel::findOne(['id' => $id]);

        // 商品状态不是审核中 禁止撤回审核
        if ($model->status != WxTransactionComponentConstant::STATUS_IN) {
            throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_NOT_STATUS_IN_RESET_AUDIT_ERROR);
        }

        $data = [
            'out_product_id' => $model->goods_id
        ];
        $result = MiniProgramWxTransactionComponentHelper::resetAudit($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            WxTransactionComponentModel::updateAll(['status' => WxTransactionComponentConstant::NOT_STATUS], ['id' => $id]);
        }

        return $result;
    }

    /**
     * 获取类目
     * @param bool $sign
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCache(bool $sign = false): array
    {
        // 查询已经用过审核的类目
        $wxAuditCategoryList = WxAuditCategoryModel::find()->where(['status' => WxAuditCategoryConstant::STATUS_SUCCESS])->select(['category_id'])->asArray()->all();

        $result = MiniProgramWxTransactionComponentHelper::getCategory();
        if (is_error($result)) {
            return [];
        }

        $newResult = [];

        if ($result['errcode'] == 0) {
            // 处理为前端展示格式
            foreach ($result['third_cat_list'] as $key => $value) {
                if ($sign) {
                    if ($value['qualification_type'] == 0 || $value['product_qualification_type'] == 0) {
                        continue;
                    }

                    // 进行审核成功的排除
                    if (in_array($value['third_cat_id'], array_column($wxAuditCategoryList, 'category_id', null))) {
                        continue;
                    }
                } else {
                    if ($value['qualification_type'] != 0 || $value['product_qualification_type'] != 0) {
                        // 验证分类是否已经通过审核
                        $wxAuditCategoryInfo = WxAuditCategoryService::getExists($value['third_cat_id']);
                        if (!$wxAuditCategoryInfo) {
                            continue;
                        }
                    }
                }

                $newResult[$value['first_cat_id']]['value'] = $value['first_cat_id'];
                $newResult[$value['first_cat_id']]['label'] = $value['first_cat_name'];
                if ($sign) {
                    // 资质说明
                    $newResult[$value['first_cat_id']]['qualification'] = $value['qualification'];
                }

                $newResult[$value['first_cat_id']]['children'][$value['second_cat_id']]['value'] = $value['second_cat_id'];
                $newResult[$value['first_cat_id']]['children'][$value['second_cat_id']]['label'] = $value['second_cat_name'];
                $newResult[$value['first_cat_id']]['children'][$value['second_cat_id']]['children'][$value['third_cat_id']]['value'] = $value['third_cat_id'];
                $newResult[$value['first_cat_id']]['children'][$value['second_cat_id']]['children'][$value['third_cat_id']]['label'] = $value['third_cat_name'];
            }

            // 处理索引
            foreach ($newResult as &$item) {
                foreach ($item['children'] as &$childItem) {
                    $childItem['children'] = array_values($childItem['children']);
                }
                $item['children'] = array_values($item['children']);
            }

            $newResult = array_values($newResult);
        }

        return $newResult ?? [];
    }

    /**
     * 检验当前场景值是否需要校验
     * @param $scene
     * @param $orderId
     * @param $payInfo
     * @return array|bool|mixed
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkScene($scene, $orderId, $payInfo)
    {
        $data = [
            'scene' => $scene
        ];

        $result = MiniProgramWxTransactionComponentHelper::checkScene($data);

        if (isset($result['errcode']) ? $result['errcode'] == 0 : $result['error'] == 0) {
            if ($result['is_matched'] == 1) {
                // 适配下单的格式
                $res = self::uploadOrderProcess($orderId, $payInfo);
                if (!$res) return error('订单条件不符合');

                $result['order_info'] = $res;
            }

            return $result;
        }

        return error($result['errmsg']);
    }

    /**
     * 上传订单的参数
     * @param $orderId
     * @param $ret
     * @return array|false
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadOrderProcess($orderId, $ret)
    {
        $order = OrderModel::find()->where(['id' => $orderId])->first();
        $order['goods_info'] = Json::decode($order['goods_info']);
        $order['extra_price_package'] = Json::decode($order['extra_price_package']);
        $order['goodsData'] = OrderGoodsModel::find()->where(['order_id' => $orderId])->asArray()->all();

        // check order valid
        $result = self::checkGoodsValid(array_column($order['goods_info'], 'goods_id'));
        if (!$result) {
            return false;
        }

        $time = time();
        $data = [
            'create_time' => (strtotime($order['create_time']) - $time) < 5 ? date('Y-m-d H:i:s', $time) : $order['create_time'],// 与微信服务器相差不能大于5秒, 大于拿当前时间
            'out_order_id' => $order['id'],
            'openid' => self::getOpenId($order['member_id']),
            'path' => self::$orderPath . $order['id'],
            'order_detail' => [
                'pay_info' => [
                    'pay_method' => '微信支付',
                    'prepay_id' => substr($ret['package'], 10),
                    'prepay_time' => date("Y-m-d H:i:s", $ret['timeStamp']),
                ],
                'price_info' => [
                    'order_price' => (int)bcmul($order['pay_price'], 100),
                    'freight' => (int)bcmul($order['dispatch_price'], 100),
                    'discounted_price' => (int)bcmul(array_sum($order['extra_price_package'] ?: []), 100),
                ]
            ],
            'delivery_detail' => [
                'delivery_type' => self::$dispatchTypeMap[$order['dispatch_type']],
            ],
            'address_info' => [
                'receiver_name' => Json::decode($order['address_info'])['name'] ?? $order['buyer_name'],
                'detailed_address' => $order['address_province'] . $order['address_city'] . $order['address_area'] . $order['address_detail'],
                'tel_number' => $order['buyer_mobile'],
            ],
            'fund_type' => 1,// 视频号暂时写死
            'expire_time' => $order['auto_close_time'] != DateTimeHelper::DEFAULT_DATE_TIME ? strtotime($order['auto_close_time']) : $time + 60 * 30,// 关闭订单的时间, 默认30分钟
        ];

        // 最新版接口，如果是自提，需要传自提点信息(此处获取不到自提点，产品说写死)
        if ($order['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_SELFFETCH) {
            $data['address_info']['detailed_address'] = '请联系卖家前往自提点提货';
        }

        if ($order['goodsData']) {
            foreach ($order['goodsData'] as $value) {
                $data['order_detail']['product_infos'][] = [
                    'out_product_id' => $value['goods_id'],
                    'out_sku_id' => $value['option_id'] == 0 ? $value['goods_id'] : $value['option_id'],
                    'product_cnt' => (int)$value['total'],
                    'sale_price' => (int)bcmul($value['price_unit'], 100),
                    'sku_real_price' => (int)bcmul($value['price'], 100),
                    'head_img' => CoreAttachmentService::getRoot() . $value['thumb'],
                    'title' => $value['title'],
                    'path' => self::$path . $value['goods_id'],
                ];
            }
        }

        return $data;
    }

    /**
     * @param $goodsId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkGoodsValid($goodsId): bool
    {
        return WxTransactionComponentModel::find()->where([
            'goods_id' => $goodsId,
            'status' => WxTransactionComponentConstant::STATUS_SUCCESS,
//            'remote_status' => WxTransactionComponentConstant::REMOTE_STATUS_UP,
        ])->exists();
    }

    /**
     * 获取openId
     * @param int $memberId
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOpenId(int $memberId)
    {
        return MemberWxappModel::getOpenId($memberId);
    }
}
