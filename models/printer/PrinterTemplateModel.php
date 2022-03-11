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

namespace shopstar\models\printer;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\payment\base\PayTypeConstant;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;


/**
 * This is the model class for table "{{%app_printer_template}}".
 *
 * @property int $id auto increment
 * @property string $name 模板名称
 * @property int $type 模板类型
 * @property int $width 模板宽度
 * @property string $content 内容
 * @property int $qrcode 二维码
 * @property string $footer 底部信息
 * @property string $logo_image 店铺图片
 * @property int $is_deleted 是否删除 0否1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class PrinterTemplateModel extends BaseActiveRecord
{

    /**
     * 模板
     */
    const TEMPLATE_MAP = [
        'header_info' => [
            'shop_name' => 1,
            'shop_logo' => 0,
        ],
        'goods_info' => [
            'goods_name' => 1,
            'goods_num' => 0,
            'goods_price' => 0,
            'goods_sku' => 0, // 商品编码
        ],
        'calculate_info' => [
            'order_original_price' => 0,
            'order_discounts_price' => 0,
            'order_pay_price' => 0,
            'dispatch_price' => 0, // 运费
        ],
        'order_info' => [
            'order_no' => 1,
            'pay_type' => 0,
            'pay_channel' => 0,
            'created_at' => 0,
            'pay_time' => 0,
            'finish_time' => 0,
            'gift_card_title', // 礼品卡名称
            'gift_card_no', // 礼品卡卡号
        ],
        'member_info' => [
            'nickname' => 1,
            'mobile' => 0,
            'level' => 0,
            'commission_level' => 0
        ],
        'mark_info' => [
            'customer_mark' => 1,
            'saler_mark' => 0
        ],
        'customer_info' => [
            'name' => 1,
            'mobile' => 0,
            'address' => 0
        ],
        'shop_info' => [
            'address' => 1,
            'mobile' => 0,
            'qrcode' => 0
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_printer_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'width', 'is_deleted'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 64],
            [['qrcode', 'footer', 'logo_image'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment',
            'name' => '模板名称',
            'type' => '模板类型 1商品模板',
            'width' => '模板宽度 1.58mm',
            'content' => '内容',
            'qrcode' => '二维码',
            'footer' => '底部信息',
            'logo_image' => '店铺图片',
            'is_deleted' => '是否删除 0否1是',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }


    /**
     * 添加
     * @param $params
     * @return array|PrinterTemplateModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function addResult($params)
    {
        try {
            $printer = new self();
            $printer->setAttributes($params);
            if (!$printer->save()) {
                return error($printer->getErrorMessage());
            }
        } catch (\Throwable $e) {

            return error($e->getMessage());
        }

        return $printer;
    }

    /**
     * 保存
     * @param $params
     * @return array|null|static
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveResult($params)
    {
        try {
            $printer = PrinterTemplateModel::findOne(['id' => $params['id'], 'is_deleted' => 0]);
            if (empty($printer)) {

                return error('打印模板不存在');
            }
            $printer->setAttributes($params);

            if (!$printer->save()) {
                return error($printer->getErrorMessage());
            }

        } catch (\Throwable $e) {

            return error($e->getMessage());
        }

        return $printer;
    }

    /**
     * 根据模板返回打印内容
     * @param PrinterTemplateModel $template
     * @param int $orderId
     * @param int $times
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getTemplatePrintContent($template, int $orderId, $times = 1)
    {
        $templateContent = Json::decode($template->content);

        $order = OrderModel::getOrderGoodsInfo($orderId);

        $orderGoods = array_column($order['orderGoods'], NULL, 'goods_id');

        $goodsIds = array_column($orderGoods, 'goods_id');

        $goodsInfo = GoodsModel::find()
            ->where([
                'id' => $goodsIds
            ])
            ->select('id, short_name, title, goods_sku')
            ->get();

        $memberInfo = MemberModel::find()
            ->where([
                'id' => $order['member_id'],
                'is_deleted' => 0
            ])
            ->first();

        $content = '';
        $content .= "<MN>$times</MN>";
        $content .= "\r\n";
        foreach ($templateContent as $item) {
            switch ($item['type']) {
                case 'header_info':
                    foreach ($item['children'] as $child) {

                        if ($child['type'] == 'shop_name' && $child['status'] == 1) {
                            // 头部信息-商城名称
                            $headerInfo = ShopSettings::get('sysset.mall.basic.name');
                            $content .= "<FS2><center>$headerInfo</center></FS2>";
                        }

                        $content .= str_repeat('.', 32);
                    }
                    break;


                case 'goods_info':
                    $tableHead = [];
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'goods_name' && $child['status'] == 1) {
                            // 商品信息-名称
                            $tableHead[] = 'goods_name';
                        }

                        if ($child['type'] == 'goods_num' && $child['status'] == 1) {
                            // 商品信息-数量
                            $tableHead[] = 'goods_num';
                        }

                        if ($child['type'] == 'goods_price' && $child['status'] == 1) {
                            // 商品信息-价格
                            $tableHead[] = 'goods_price';
                        }
                    }

                    // 只有一列
                    if (count($tableHead) == 1) {
                        // 判断是什么列
                        if ($tableHead[0] == 'goods_name') {
                            $content .= '商品名称';
                            $content .= "\r\n";
                            $content .= str_repeat('.', 32);

                            $content .= "<table>";
                            foreach ($goodsInfo as $goods) {
                                // 优先获取短标题 没有获取商品标题取12个汉字
                                $title = self::getTitle($goods);
                                $content .= "<tr><td>$title</td></tr>";
                            }
                            $content .= "</table>";
                        } elseif ($tableHead[0] == 'goods_num') {
                            $content .= '数量';
                            $content .= "\r\n";
                            $content .= str_repeat('.', 32);

                            $content .= "<table>";
                            foreach ($goodsInfo as $goods) {
                                $num = self::getNum($orderGoods, $goods);
                                $content .= "<tr><td>$num</td></tr>";
                            }
                            $content .= "</table>";
                        } elseif ($tableHead[0] == 'goods_price') {
                            $content .= '金额';
                            $content .= "\r\n";
                            $content .= str_repeat('.', 32);

                            $content .= "<table>";
                            foreach ($goodsInfo as $goods) {
                                $price = self::getPrice($orderGoods, $goods);
                                $content .= "<tr><td>$price</td></tr>";
                            }
                            $content .= "</table>";
                        }
                    } // 只有两列
                    elseif (count($tableHead) == 2) {

                        if ($tableHead[0] == 'goods_name' && $tableHead[1] == 'goods_num') {
                            $content .= "<LR>商品名称,数量</LR>";
                            $content .= str_repeat('.', 32);

                            $content .= "<table>";
                            foreach ($goodsInfo as $goods) {
                                // 优先获取短标题 没有获取商品标题取12个汉字
                                $title = self::getTitle($goods);
                                $num = self::getNum($orderGoods, $goods);
                                $content .= "<tr><td>$title</td><td></td><td>$num</td></tr>";
                            }
                            $content .= "</table>";
                        } elseif ($tableHead[0] == 'goods_name' && $tableHead[1] == 'goods_price') {
                            $content .= "<LR>商品名称,金额</LR>";
                            $content .= str_repeat('.', 32);

                            $content .= "<table>";
                            foreach ($goodsInfo as $goods) {
                                // 优先获取短标题 没有获取商品标题取12个汉字
                                $title = self::getTitle($goods);
                                $price = self::getPrice($orderGoods, $goods);
                                $content .= "<tr><td>$title</td><td></td><td>$price</td></tr>";
                            }
                            $content .= "</table>";
                        } elseif ($tableHead[0] == 'goods_num' && $tableHead[1] == 'goods_price') {
                            $content .= "<LR>数量,金额</LR>";
                            $content .= str_repeat('.', 32);

                            $content .= "<table>";
                            foreach ($goodsInfo as $goods) {
                                $num = self::getNum($orderGoods, $goods);
                                $price = self::getPrice($orderGoods, $goods);
                                $content .= "<tr><td>$num</td><td></td><td>$price</td></tr>";
                            }
                            $content .= "</table>";
                        }
                    } // 三列
                    elseif (count($tableHead) == 3) {
                        $content .= '商品名称';
                        $content .= str_repeat(' ', 7);
                        $content .= '数量';
                        $content .= str_repeat(' ', 9);
                        $content .= '金额';
                        $content .= str_repeat('.', 32);
                        $content .= "\r\n";

                        $content .= "<table>";
                        foreach ($goodsInfo as $goods) {
                            // 优先获取短标题 没有获取商品标题取12个汉字
                            $title = self::getTitle($goods);
                            $num = self::getNum($orderGoods, $goods);
                            $price = self::getPrice($orderGoods, $goods);
                            $content .= "<tr><td>$title</td><td>$num</td><td>$price</td></tr>";
                        }
                        $content .= "</table>";
                    }

                    $content .= str_repeat('.', 32);


                    break;
                case 'calculate_info':
                    // 合计 运费 优惠金额 实付金额
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'order_original_price' && $child['status'] == 1) {
                            // 合计
                            $originGoodsPrice = $order['original_goods_price'] ?? '';
                            $content .= "<LR>合计,$originGoodsPrice</LR>";
                        }

                        if ($child['type'] == 'dispatch_price' && $child['status'] == 1) {
                            // 运费
                            $dispatchPrice = $order['dispatch_price'] ?? '';
                            $content .= "<LR>运费,$dispatchPrice</LR>";
                        }

                        if ($child['type'] == 'pay_price' && $child['status'] == 1) {
                            // 优惠金额
                            $payPrice = $order['pay_price'] ?? '';
                            $content .= "<LR>实付金额,$payPrice</LR>";
                        }

                        if ($child['type'] == 'pay_price' && $child['status'] == 1) {
                            // 实付金额
                            $payPrice = $order['pay_price'] ?? '';
                            $content .= "<LR>实付金额,$payPrice</LR>";
                        }
                    }
                    $content .= str_repeat('.', 32);
                    break;
                case 'order_info':
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'order_no' && $child['status'] == 1) {
                            // 订单编号
                            $orderNo = $order['order_no'] ?? '';
                            $content .= "订单编号：$orderNo";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'pay_type' && $child['status'] == 1) {
                            // 支付方式
                            $payType = $order['pay_type'] ?? '';
                            !empty($payType) && $payType = PayTypeConstant::getIdentify($payType);
                            $content .= "支付方式：$payType";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'created_at' && $child['status'] == 1) {
                            // 下单时间
                            $createTime = $order['created_at'] == 0 ? '' : $order['created_at'];
                            $content .= "下单时间：$createTime";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'pay_time' && $child['status'] == 1) {
                            // 付款时间
                            $payTime = $order['pay_time'] == 0 ? '' : $order['pay_time'];
                            $content .= "收货时间：$payTime";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'finish_time' && $child['status'] == 1) {
                            // 收货时间
                            $finishTime = $order['finish_time'] == 0 ? '' : $order['finish_time'];
                            $content .= "收货时间：$finishTime";
                            $content .= "\r\n";
                        }

                    }
                    $content .= str_repeat('.', 32);
                    break;
                case 'member_info':
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'nickname' && $child['status'] == 1) {
                            // 会员昵称
                            $nickname = $memberInfo['nickname'] ?? '';
                            $content .= "会员昵称：$nickname";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'mobile' && $child['status'] == 1) {
                            // 联系方式
                            $mobile = $memberInfo['mobile'] ?? '';
                            $content .= "联系方式：$mobile";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'level' && $child['status'] == 1) {
                            // 会员等级
                            $level = $memberInfo['level'] ?? '';
                            $content .= "会员等级：$level";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'commission_level' && $child['status'] == 1) {
                            // 分销商等级
                            $agentInfo = CommissionAgentModel::find()->where(['member_id' =>
                                $memberInfo['id']])->select('level_id')->with('level')->first();
                            $level = $agentInfo['level']['name'] ?? '';
                            $content .= "分销商等级：$level";
                            $content .= "\r\n";
                        }
                    }
                    $content .= str_repeat('.', 32);
                    break;
                case 'mark_info': //备注信息
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'customer_mark' && $child['status'] == 1) {
                            // 买家留言
                            $buyerRemark = $order['buyer_remark'] ?? '';
                            $content .= "买家留言：$buyerRemark";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'saler_mark' && $child['status'] == 1) {
                            // 卖家留言
                            $remark = $order['remark'] ?? '';
                            $content .= "卖家留言：$remark";
                            $content .= "\r\n";
                        }
                    }
                    $content .= str_repeat('.', 32);
                    break;
                case 'customer_info': //买家信息
                    $addressInfo = Json::decode($order['address_info']);
                    foreach ($item['children'] as $child) {
                        if ($child['type'] == 'name' && $child['status'] == 1) {
                            // 买家姓名
                            $buyerName = $order['buyer_name'] ?? '';
                            $content .= "买家姓名：$buyerName";
                            $content .= "\r\n";
                        }
                        if ($child['type'] == 'mobile' && $child['status'] == 1) {
                            // 联系方式
                            $buyerMobile = $order['buyer_mobile'] ?? '';
                            $content .= "联系方式：$buyerMobile";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'address' && $child['status'] == 1) {
                            // 联系地址
                            $buyerAddress = $addressInfo['province'] . $addressInfo['city'] . $addressInfo['area'] .
                                $addressInfo['address_detail'];
                            $content .= "联系地址：$buyerAddress";
                            $content .= "\r\n";
                        }
                    }
                    $content .= str_repeat('.', 32);
                    break;
                case 'shop_info': //商城信息
                    foreach ($item['children'] as $child) {
                        $shopInfo = ShopSettings::get('contact');
                        if ($child['type'] == 'qrcode' && $child['status'] == 1) {
                            // 商城二维码
                            $content .= "<QR>$template->qrcode</QR>";
                        }

                        if ($child['type'] == 'mobile' && $child['status'] == 1) {
                            // 联系方式
                            $tel = $shopInfo['tel1'] ?? '';
                            $content .= "<center>$tel</center>";
                            $content .= "\r\n";
                        }

                        if ($child['type'] == 'address' && $child['status'] == 1) {
                            //地址
                            $address = $shopInfo['address']['province'] . $shopInfo['address']['city'] . $shopInfo['address']['area'] . $shopInfo['address']['detail'];
                            $content .= "<center>$address</center>";
                            $content .= "\r\n";
                        }
                    }
                    $content .= str_repeat('.', 32);
                    break;
            }
        }

        if (!empty($template->footer)) {
            $content .= "<center>$template->footer</center>";
        }

        return $content;
    }

    private static function getTitle($goods)
    {
        // 优先获取短标题 没有获取商品标题取12个汉字
        if (!empty($goods['short_name'])) {
            $title = mb_substr($goods['short_name'], 0, 12);
        } else {
            $title = mb_substr($goods['title'], 0, 12);
        }
        if (mb_strlen($title) > 6) {
            $title = mb_substr($title, 0, 6) . "\r\n" . mb_substr($title, 6);
        }
        if (!empty($goods['goods_sku'])) {
            $title .= "\r\n" . $goods['goods_sku'];
        }

        return $title;
    }

    private static function getNum($orderGoods, $goods)
    {
        return 'x' . $orderGoods[$goods['id']]['total'];
    }

    private static function getPrice($orderGoods, $goods)
    {
        return '￥' . $orderGoods[$goods['id']]['price_unit'];
    }
}