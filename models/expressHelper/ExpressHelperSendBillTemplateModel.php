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

namespace shopstar\models\expressHelper;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\payment\base\PayTypeConstant;
use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\exceptions\expressHelper\ExpressHelperException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%express_helper_send_bill_template}}".
 *
 * @property int $id
 * @property string $name 模板名称
 * @property string $express_code 快递公司编码
 * @property int $type 发货单样式1横2竖
 * @property string $title 发货单标题
 * @property string $logo logo图
 * @property string $data 内容数据
 * @property string $footer 底部信息
 * @property string $qr_code 二维码地址
 * @property int $is_default 是否默认
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 */
class ExpressHelperSendBillTemplateModel extends BaseActiveRecord
{
    /**
     * 隐藏配送信息的订单类型: 虚拟卡密, 虚拟商品
     * @var array
     */
    private static $hiddenDispatch = [
        OrderTypeConstant::ORDER_TYPE_VIRTUAL,
        OrderTypeConstant::ORDER_TYPE_VIRTUAL_ACCOUNT,
    ];

    /**
     * 隐藏的配送信息字段
     * @var array
     */
    private static $hiddenDispatchField = [
        'order_message' => ['dispatch_type'],
        'consignee_message' => '*',
        'price_message' => ['dispatch_price'],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%express_helper_send_bill_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['type', 'is_default'], 'integer'],
            [['data'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'express_code', 'send_title', 'logo', 'footer', 'qr_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '模板名称',
            'express_code' => '快递公司编码',
            'type' => '发货单样式1横2竖',
            'title' => '发货单标题',
            'logo' => 'logo图',
            'data' => '内容数据',
            'footer' => '底部信息',
            'qr_code' => '二维码地址',
            'is_default' => '是否默认',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * 验证模板名称
     * @param string $name
     * @param int $id
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkName(string $name, $id = 0)
    {
        $exist = self::findOne(['name' => $name]);

        if (!$exist) {
            return true;
        }
        if (!empty($exist) && $exist->id = $id) {
            return true;
        }
        return false;

    }

    /**
     * 模板详情map
     * @return \array[][]
     * @author 青岛开店星信息技术有限公司
     */
    public static function contentList()
    {
        return [
            'list' =>
                [
                    ['key' => 'send_message',
                        'name' => '发货单信息',
                        'checked' => true,
                        'children' => [
                            ['key' => 'send_title',
                                'name' => '发货单标题',
                                'checked' => true
                            ],
                            ['key' => 'send_code',
                                'name' => '发货单编号',
                                'checked' => true
                            ],
                            ['key' => 'print_time',
                                'name' => '打印时间',
                                'checked' => true
                            ]
                        ]
                    ],
                    ['key' => 'order_message',
                        'name' => '订单信息',
                        'checked' => true,
                        'children' => [
                            ['key' => 'order_no',
                                'name' => '订单编号',
                                'checked' => true
                            ],
                            ['key' => 'member_nickname',
                                'name' => '会员昵称',
                                'checked' => true
                            ],
                            ['key' => 'member_level',
                                'name' => '会员等级',
                                'checked' => true
                            ],
                            ['key' => 'pay_type',
                                'name' => '支付方式',
                                'checked' => true
                            ],
                            ['key' => 'created_at',
                                'name' => '下单时间',
                                'checked' => true
                            ],
                            ['key' => 'pay_time',
                                'name' => '付款时间',
                                'checked' => true
                            ],
                            ['key' => 'dispatch_type',
                                'name' => '配送方式',
                                'checked' => true
                            ],
                        ]
                    ],
                    ['key' => 'consignee_message',
                        'name' => '收货人信息',
                        'checked' => true,
                        'children' => [
                            ['key' => 'buyer_name',
                                'name' => '收货人姓名',
                                'checked' => true
                            ],
                            ['key' => 'buyer_mobile',
                                'name' => '联系方式',
                                'checked' => true
                            ],
                            ['key' => 'address_code',
                                'name' => '收货人地址',
                                'checked' => false
                            ],
                            ['key' => 'buyer_remark',
                                'name' => '买家留言',
                                'checked' => true
                            ],
                        ]
                    ],
                    ['key' => 'goods_message',
                        'name' => '商品信息',
                        'checked' => true,
                        'children' => [
                            ['key' => 'number',
                                'name' => '序号',
                                'checked' => true
                            ],
                            ['key' => 'title',
                                'name' => '商品名称',
                                'checked' => true
                            ],
                            ['key' => 'short_name',
                                'name' => '商品简称',
                                'checked' => false
                            ],
                            ['key' => 'goods_sku',
                                'name' => '商品编码',
                                'checked' => true
                            ],
                            ['key' => 'option_title',
                                'name' => '规格',
                                'checked' => true
                            ],
                            ['key' => 'price_unit',
                                'name' => '单价',
                                'checked' => true
                            ],
                            ['key' => 'total',
                                'name' => '数量',
                                'checked' => true
                            ],
                            ['key' => 'price',
                                'name' => '总价',
                                'checked' => false
                            ]
                        ]
                    ],
                    ['key' => 'price_message',
                        'name' => '费用信息',
                        'checked' => true,
                        'children' => [
                            ['key' => 'goods_price',
                                'name' => '商品小计',
                                'checked' => true
                            ],
                            ['key' => 'dispatch_price',
                                'name' => '订单运费',
                                'checked' => true
                            ],
                            ['key' => 'discount_price',
                                'name' => '订单优惠',
                                'checked' => true
                            ],
                            ['key' => 'pay_price',
                                'name' => '实付金额',
                                'checked' => true
                            ],
                            ['key' => 'goods_count',
                                'name' => '商品件数',
                                'checked' => true
                            ]
                        ]
                    ],

                ]
        ];

    }

    /**
     * 获取发货单数据
     * @param int $orderId
     * @param array $orderGoodsId
     * @author 青岛开店星信息技术有限公司
     */
    public static function getSendBillPrintList(int $orderId, array $orderGoodsId)
    {
        $orderInfo = OrderModel::find()->where([
            'and',
            ['id' => $orderId],
            ['>=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_SEND],
        ])->select([
            'id',
            'order_no',
            'order_type',
            'member_id',
            'member_nickname',
            'pay_type',
            'created_at',
            'pay_time',
            'dispatch_type',
            'buyer_name',
            'buyer_mobile',
            'buyer_remark',
            'dispatch_price',
            'extra_price_package',
            'address_state',
            'address_city',
            'address_area',
            'address_detail'
        ])->asArray()->indexBy('id')->one();


        if (is_null($orderInfo)) {
            return error('获取订单数据错误');
        }

        foreach ($orderInfo as $k => &$v) {
            if ($k == 'pay_type') {
                $v = PayTypeConstant::getMessage($v);
            }
            if ($k == 'dispatch_type') {
                $v = OrderDispatchExpressConstant::getText($v);
            }
        }
        unset($v);

        //获取等级名称
        $orderInfo['member_level'] = MemberLevelModel::getMemberLevelNameByMemberId($orderInfo['member_id']);


        $orderInfo['order_goods'] = OrderGoodsModel::find()->where([
            'id' => $orderGoodsId,
            'order_id' => $orderId,
        ])->select([
            'title',
            'short_name',
            'option_title',
            'total',
            'price_unit',
            'price',
            'price_original',
            'goods_sku'
        ])->asArray()->all();

        if (is_null($orderInfo['order_goods'])) {
            return error('获取订单商品数据错误');
        }


        //计算商品小计跟实付金额
        $orderInfo['goods_price'] = 0;
        $orderInfo['pay_price'] = 0;

        foreach ($orderInfo['order_goods'] as $k => $v) {

            $orderInfo['goods_price'] += $v['price_unit'] * $v['total'];

            $orderInfo['pay_price'] += $v['price'];

        }
        $orderInfo['goods_price'] = round2($orderInfo['goods_price']);
        $orderInfo['pay_price'] = round2($orderInfo['pay_price']);


        $orderInfo['goods_count'] = count($orderInfo['order_goods']);

        $orderInfo['discount_price'] = round2(array_sum(array_values(Json::decode($orderInfo['extra_price_package']))), 2);

        $today = DateTimeHelper::now(false);

        $count = ExpressHelperSendBillLogModel::find()->where(['>', 'created_at', $today])->count();

        $count = StringHelper::replenishZero($count, 5);

        $orderInfo['send_code'] = date('Ymd') . $count;

        $orderInfo['print_time'] = date('Y-m-d H:i:s', time());

        $orderInfo['address_code'] = $orderInfo['address_state'] . $orderInfo['address_city'] . $orderInfo['address_area'] . $orderInfo['address_detail'];


        return $orderInfo;
    }

    /**
     * 获取发货单模板
     * @param int $sendBillTemplateId
     * @return array|\yii\db\ActiveRecord|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getSendBillTemplate(int $sendBillTemplateId)
    {
        //获取面单模板
        return self::find()
            ->where([
                'id' => $sendBillTemplateId,
            ])
            ->asArray()
            ->one();
    }

    /**
     * 打印模板数据过滤
     * @param string $templateData
     * @return array|false|string
     * @author nizengchao
     */
    public static function templateDataFilter(string $templateData = '')
    {
        if (!$templateData || !StringHelper::isJson($templateData)) {
            return $templateData;
        }
        $templateData = Json::decode($templateData);

        // 获取参数
        $orderId = RequestHelper::getInt('order_id');
        if (!$orderId) {
            return error('缺少订单id', ExpressHelperException::MANAGE_PRINT_INDEX_CALLBACK_PARAMS_ERROR);
        }

        // 获取订单信息
        $order = OrderModel::find()->where([
            'and',
            ['id' => $orderId],
            ['>=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_SEND],
        ])->select([
            'id',
            'order_type',
        ])->first();
        if (!$order) {
            return error('获取订单信息失败', ExpressHelperException::SEND_BILL_PRINT_NO_FOUND_ORDER);
        }

        // 虚拟商品/虚拟卡密商品, 不打印配送方式,收货人信息,订单运费
        if (in_array($order['order_type'], self::$hiddenDispatch)) {
            $templateData = self::doFilter($templateData, self::$hiddenDispatchField);
        }
        return json_encode($templateData, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 处理过滤
     * @param array $templateData
     * @param array $filter
     * @return array
     * @author nizengchao
     */
    private static function doFilter(array $templateData = [], array $filter = []): array
    {
        if (empty($templateData) || empty($filter)) {
            return $templateData;
        }
        // 循环进行过滤
        foreach ($templateData as $index => $item) {
            if (!isset($filter[$item['key']]) || empty($item['children']) || !is_array($item['children'])) {
                continue;
            }

            // 整体过滤, 直接删除
            if (!is_array($filter[$item['key']]) && $filter[$item['key']] === '*') {
                unset($templateData[$index]);
                continue;
            }

            // 单个的过滤,强制设为未选中
            foreach ($item['children'] as $key => $child) {
                if (in_array($child['key'], $filter[$item['key']])) {
                    $templateData[$index]['children'][$key]['checked'] = false;
                }
            }
        }

        return array_values($templateData);
    }

}