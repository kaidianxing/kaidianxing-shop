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

namespace shopstar\services\expressHelper;

use shopstar\constants\order\OrderStatusConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\expressHelper\ExpressHelperConsignerTemplateModel;
use shopstar\models\expressHelper\ExpressHelperExpressTemplateModel;
use shopstar\models\expressHelper\ExpressHelperRequestRecordModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use yii\helpers\Json;

/**
 * 打印助手
 * Class PrintHandler
 * @package shopstar\models\expressHelper
 * @author 青岛开店星信息技术有限公司
 */
class PrintHandler
{
    /**
     * 获取需要打印的列表（重组打印订单列表）
     * @param int $orderId
     * @param array $orderGoodsId
     * @return array
     */
    public function getPrintList(int $orderId, array $orderGoodsId): array
    {
        //获取需要打印的所有订单和订单商品
        $orderInfo = $this->getOrder($orderId, $orderGoodsId);

        //打印记录
        $printRecord = $this->getPrintRecord($orderId);
        if (is_error($printRecord)) {
            return error($printRecord['message']);
        }

        //等待打印的列表
        $awaitPrintList = [];

        //分离
        //如果打印记录不存在则当做第一次打印选中的商品全部打印
        if (empty($printRecord)) {
            return [$orderInfo];
        }

        //分离订单和订单商品信息
        $orderGoodsInfo = $orderInfo['order_goods'];

        //订单商品为空，则直接返回
        if (empty($orderGoodsInfo)) {
            return error('不存在打印信息');
        }

        //是否订单商品
        unset($orderInfo['order_goods']);

        //分离所有已打印过的订单
        foreach ((array)$printRecord as $printRecordIndex => $printRecordItem) {
            //分离的订单商品
            $separateOrderGoods = [];

            //已打印的订单商品id数组
            $printRecordOrderGoodsId = StringHelper::explode($printRecordItem['order_goods_id']);

            //分离订单商品
            foreach ((array)$orderGoodsInfo as $orderGoodsInfoIndex => $orderGoodsItem) {

                //获取判断商品是否已打印
                if (in_array($orderGoodsItem['id'], $printRecordOrderGoodsId)) {
                    $separateOrderGoods[] = $orderGoodsItem;

                    //释放掉已经分离出去的订单商品
                    unset($orderGoodsInfo[$orderGoodsInfoIndex]);
                }
            }

            //如果不存在说明没打印过则直接跳过
            if (empty($separateOrderGoods)) {
                continue;
            }

            //合并分离的订单和订单商品
            $awaitPrintList[] = array_merge($orderInfo, [
                'order_goods' => $separateOrderGoods
            ]);
        }

        unset($printRecordIndex, $printRecordItem);

        //如果分离完成后的订单商品 还存在未打印的那么直接剩下的全部订单商品组成一个包
        if (!empty($orderGoodsInfo)) {
            //追加为打印过的
            $awaitPrintList[] = array_merge($orderInfo, [
                'order_goods' => array_values($orderGoodsInfo)
            ]);
        }

        unset($orderGoodsId);
        //判断拆分完成的订单商品是否和已打印的订单商品吻合
        foreach ((array)$awaitPrintList as $awaitPrintListIndex => $awaitPrintListItem) {
            foreach ((array)$printRecord as $printRecordIndex => $printRecordItem) {
                //已打印的订单商品id数组
                $printRecordOrderGoodsId = StringHelper::explode($printRecordItem['order_goods_id']);

                //升序排序用于全等对比
                asort($printRecordOrderGoodsId);

                //分离完成的订单商品id
                $orderGoodsId = array_column($awaitPrintListItem['order_goods'], 'id');

                //升序排序用于对比
                asort($orderGoodsId);

                //如果订单相等，并且订单商品id存在在打印记录里
                if ($printRecordItem['order_id'] == $awaitPrintListItem['id'] && array_intersect($orderGoodsId, $printRecordOrderGoodsId)) {

                    //订单商品不全等于打印记录里的则报错
                    if ($printRecordOrderGoodsId !== $orderGoodsId) {

                        return error($awaitPrintListItem['order_no'] . '：存在交叉打印');
                    }
                }
            }
        }

        return $awaitPrintList;
    }

    /**
     * 获取打印记录
     * @param int $orderId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private function getPrintRecord(int $orderId): array
    {
        //获取订单商品的打印记录
        $allPrintRecord = ExpressHelperRequestRecordModel::find()
            ->where(['order_id' => $orderId])
            ->select([
                'order_goods_id',
                'order_id',
                'order_no',
                'order_code',
            ])
            ->groupBy([
                'order_id',
                'order_goods_id'
            ])
            ->asArray()
            ->all();

        //暂存商品id的拼接值，判断打印是否存在重复
        $orderGoodsId = [];
        foreach ($allPrintRecord as $index => $item) {
            //转化成数组
            $orderGoodsIdArray = StringHelper::explode($item['order_goods_id'], ',');

            //判断打印记录里是否有重复打印的订单商品
            if (array_intersect($orderGoodsIdArray, $orderGoodsId)) {
                return error('重复打印订单商品(1)');
            }

            //合并到暂存数组
            $orderGoodsId = array_merge($orderGoodsId, $orderGoodsIdArray);
        }

        return $allPrintRecord;
    }

    /**
     * 获取需要打印的订单
     * @param int $orderId
     * @param array $orderGoodsId
     * @return array|int|string|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    private function getOrder(int $orderId, array $orderGoodsId)
    {
        $orderInfo = OrderModel::find()->where([
            'and',
            ['id' => $orderId],
            ['>=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_SEND],
        ])->select([
            'id',
            'order_no',
            'order_type',
            'pay_price',
            'member_nickname',
            'buyer_name',
            'buyer_mobile',
            'address_state',
            'address_city',
            'address_area',
            'address_detail',
            'address_info',
            'created_at',
        ])->asArray()->indexBy('id')->one();

        //订单商品查询
        $orderInfo['order_goods'] = OrderGoodsModel::find()->where([
            'id' => $orderGoodsId,
            'order_id' => $orderId,
        ])->select([
            'id',
            'order_id',
            'goods_id',
            'title',
            'option_title',
            'price',
            'total',
            'weight',
        ])->asArray()->all();

        return $orderInfo;
    }

    /**
     * 获取依托在商城订单号上的，唯一订单号
     * 规则：获取商城订单号的前14位，拼接订单和订单商品id的md5中16位
     * @param $orderNo
     * @param array $orderGoodsId
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public function getOrderCode($orderNo, array $orderGoodsId): string
    {
        asort($orderGoodsId);
        return substr($orderNo, 0, 14) . StringHelper::shortMd5($orderNo . Json::encode($orderGoodsId));
    }

    /**
     * 获取打印参数
     * @param $orderInfo
     * @param array $expressTemplate
     * @param array $consignerTemplate
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getKdnParams($orderInfo, array $expressTemplate, array $consignerTemplate): array
    {
        //获取快递面单模板
        if (empty($expressTemplate)) {
            return error('快递面单不存在');
        }

        if (empty($consignerTemplate)) {
            return error('发件人模板不存在');
        }

        //解析地址信息
        $addressInfo = Json::decode($orderInfo['address_info']);

        //计算重量
        $weight = 0;

        //商品信息
        $goodsInfo = [];
        foreach ((array)$orderInfo['order_goods'] as $orderGoodsIndex => $orderGoodsItem) {

            $weight = round2(($weight + $orderGoodsItem['weight'] * $orderGoodsItem['total']) / 1000);

            $goodsName = $orderGoodsItem['title'] . '-' . $orderGoodsItem['option_title'];
            $goodsInfo[] = [
                'GoodsName' => mb_strlen($goodsName) > 97 ? mb_substr($goodsName, 0, 97) . '...' : $goodsName . ';',
                'Goodsquantity' => $orderGoodsItem['total'],
                'GoodsWeight' => $weight,
            ];
        }

        //参数
        return [
            'CustomerName' => $expressTemplate['template_account'],//电子面单账号
            'CustomerPwd' => $expressTemplate['template_password'],//电子面单密码

            'MonthCode' => $expressTemplate['monthly_code'],//月结编码
            'SendSite' => $expressTemplate['branch_name'],//所属网点
            'SendStaff' => $expressTemplate['branch_code'],//网点编码
            'ShipperCode' => $expressTemplate['express_company'],//快递公司名称
            'OrderCode' => $this->getOrderCode($orderInfo['order_no'], array_column($orderInfo['order_goods'], 'id')),//订单号
            'PayType' => $expressTemplate['pay_type'],
            'ExpType' => '1',//快递类型，默认标准快递 后期可以根据需求更改

            //收货人信息
            'Receiver' => [
                'Name' => $orderInfo['buyer_name'],//收货人姓名
                'Tel' => '',//电话
                'Mobile' => $orderInfo['buyer_mobile'],//手机
                'PostCode' => !isset($addressInfo['zip_code']) ? 0 : $addressInfo['zip_code'],//邮编
                'ProvinceName' => $orderInfo['address_state'],//省
                'CityName' => $orderInfo['address_city'],//市
                'ExpAreaName' => $orderInfo['address_area'],//区/县
                'Address' => $orderInfo['address_detail'],//区/县
            ],

            //发件人信息
            'Sender' => [
                'Company' => $consignerTemplate['consigner_company'],
                'Name' => $consignerTemplate['consigner_name'],
                'Tel' => '',//电话
                'Mobile' => $consignerTemplate['consigner_mobile'],//手机
                'PostCode' => $consignerTemplate['postcode'],//邮编
                'ProvinceName' => $consignerTemplate['consigner_province'],//省
                'CityName' => $consignerTemplate['consigner_city'],//市
                'ExpAreaName' => $consignerTemplate['consigner_area'],//区/县
                'Address' => $consignerTemplate['consigner_address'],//区/县
            ],

            'IsNotice' => $expressTemplate['is_notice'],//快递员上门通知
            'Weight' => $weight,//重量
            'Quantity' => (int)($expressTemplate['quantity']) ?: 1,//包裹数量 默认1个  发多个数量会走子母件 订单首次打印生效
            // 'Quantity' => 1,//包裹数量 默认1个  发多个数量会走子母件

            //货物
            'Commodity' => $goodsInfo,

            'IsReturnPrintTemplate' => 1, //是否返回电子面单模板
            'TemplateSize' => $expressTemplate['template_style'], //尺寸
            'CurrencyCode' => 'CNY', //货币类型 写死人民币
        ];
    }

    /**
     * 获取模板
     * @param int $consignerTemplateId
     * @param int $expressTemplateId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getTemplateData(int $consignerTemplateId, int $expressTemplateId): array
    {
        //获取面单模板

        $expressTemplate = ExpressHelperExpressTemplateModel::find()
            ->where([
                'id' => $expressTemplateId,
            ])
            ->asArray()
            ->one();
        //获取发货人模板
        $consignerTemplate = ExpressHelperConsignerTemplateModel::find()
            ->where([
                'id' => $consignerTemplateId
            ])
            ->asArray()
            ->one();

        //返回数据
        return [$consignerTemplate, $expressTemplate];
    }

    /**
     * 请求成功记录
     * @param array $printData
     * @return bool|int
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function requestRecord(array $printData)
    {
        //入库数据
        $insetData = [];

        //批量入库时间
        $insertTime = DateTimeHelper::now();

        foreach ($printData as $printDataIndex => $printDataItem) {

            //订单商品id排序
            asort($printDataItem['shop_order_goods_id']);

            $insertData[] = [
                'order_id' => $printDataItem['shop_order_id'],//商城订单id
                'order_no' => $printDataItem['shop_order_no'], //商城订单号
                'order_goods_id' => implode(',', $printDataItem['shop_order_goods_id']),//订单商品id
                'order_code' => $printDataItem['Order']['OrderCode'], //快递鸟订单编号
                'result_message' => $printDataItem['Reason'], //返回成功或失败理由
                'logistic_code' => $printDataItem['Order']['LogisticCode'], //运单号
                'express_type' => $printDataItem['Order']['ShipperCode'], //快递公司
                'request_response' => Json::encode($printDataItem), //打印内容
                'created_at' => $insertTime, //返回理由
                'user_id' => $printDataItem['EBusinessID'], //返回理由
            ];
        }

        //如果数据为空，则直接返回false
        if (empty($insertData)) {
            return false;
        }

        //批量入库
        return ExpressHelperRequestRecordModel::batchInsert(array_keys($insertData[0]), $insertData);
    }


}