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

namespace shopstar\mobile\order;

use GuzzleHttp\Exception\GuzzleException;
use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\form\FormTypeConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\form\FormModel;
use shopstar\models\order\create\OrderCreator;
use yii\web\Response;

/**
 * @author 青岛开店星信息技术有限公司
 */
class CreateController extends BaseMobileApiController
{
    /**
     * @var \string[][]
     */
    public $configActions = [
        'needBindMobileActions' => [
            'index' => 'buy',
            'confirm' => 'buy'
        ],
    ];

    /**
     * 创建订单
     * @return Response
     * @throws GuzzleException
     * @throws \shopstar\exceptions\order\OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex(): Response
    {
        return $this->create($this->getArgs(), false);
    }

    /**
     * 确认订单
     * @return Response
     * @throws GuzzleException
     * @throws \shopstar\exceptions\order\OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionConfirm(): Response
    {
        return $this->create($this->getArgs(), true);
    }

    /**
     * 执行创建订单
     * @param array $args
     * @param bool $isConfirm
     * @return Response
     * @throws GuzzleException
     * @throws \shopstar\exceptions\order\OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    private function create(array $args, bool $isConfirm = true): Response
    {
        $goodsData = $args['goods_info'];
        unset($args['goods_info']);
        $inputData = $args;

        // 实例订单创建器
        $creator = new OrderCreator($goodsData, $inputData);

        // 执行预览|提交订单
        $order = $isConfirm ? $creator->confirm() : $creator->submit();

        // 返回数据
        $result = [
            'order' => $order,
        ];

        // 表单信息
        if ($isConfirm) {
            $result['form'] = FormModel::get(FormTypeConstant::FORM_TYPE_ORDER, $this->memberId, true);
        }

        return $this->result($result);
    }

    /**
     * 获取请求参数
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private function getArgs(): array
    {
        // 接收前端传入订单信息
        return [

            //应用名称
            'app_name' => RequestHelper::post('app_name'),

            //是否来自购物车
            'is_cart' => RequestHelper::postInt('is_cart'),

            // 商品信息
            'goods_info' => RequestHelper::postArray('goods_info'),

            /**
             * 以下是订单优惠信息
             */
            // 商品优惠券ID -1: 不使用优惠券 0: 自动计算最优 > 0: 优惠券id
            'select_coupon_id' => RequestHelper::postInt('select_coupon_id'),

            // 使用积分抵扣
            'deduct_credit' => RequestHelper::postInt('deduct_credit'),
            // 使用余额抵扣
            'deduct_balance' => RequestHelper::postInt('deduct_balance'),
            // 选择礼品卡id
            'select_gift_card_id' => RequestHelper::postInt('select_gift_card_id'),

            /**
             * 以下是会员信息
             */
            // 收货地址id OR 自提点id
            'address_id' => RequestHelper::postInt('address_id', 0),

            // 自提人 OR 收件人姓名
            'buyer_name' => RequestHelper::post('buyer_name'),
            // 自提人 OR 收件人手机
            'buyer_mobile' => RequestHelper::post('buyer_mobile'),
            // 买家备注
            'buyer_remark' => RequestHelper::post('buyer_remark'),

            /**
             * 以下是发票相关
             */
            // 发票抬头
            'invoice_title' => RequestHelper::post('invoice_title'),
            // 是否电子发票
            'invoice_is_electronic' => RequestHelper::postInt('invoice_is_electronic', 0),
            // 是否企业发票
            'invoice_is_company' => RequestHelper::postInt('invoice_is_company', 0),
            // 纳税人识别号
            'invoice_number' => RequestHelper::post('invoice_number'),
            //发票邮寄地址或者邮箱地址
            'invoice_address' => RequestHelper::post('invoice_address'),

            //发票合集
            'invoice_info' => RequestHelper::post('invoice_info'),


            /**
             * 以下是订单基础信息
             */
            // 扩展参数(非必填)
            'extend_params' => RequestHelper::post('extend_params'),

            //配送时间
            'delivery_time' => RequestHelper::post('delivery_time'),

            // 配送方式 10: 快递 20: 自提 30: 同城
            'dispatch_type' => RequestHelper::postInt('dispatch_type', 0),

            // 订单渠道
            'client_type' => $this->clientType,
            // 是否原价购买
            'is_original_buy' => RequestHelper::post('is_original_buy'),

            // 订单来源
            'source' => RequestHelper::post('source', null),
            //订单场景
            'scene' => RequestHelper::postInt('scene'),

            //场景值
            'scene_value' => RequestHelper::postInt('scene_value'),

            // 付款方式
            'pay_type' => RequestHelper::post('pay_type', 'online'),

            'member_id' => $this->memberId,
            // 经纬度
            'lat' => RequestHelper::post('lat', '0'),
            'lng' => RequestHelper::post('lng', '0'),

            // 虚拟卡密的接收邮箱
            'virtual_email' => RequestHelper::post('virtual_email'),

            // 店铺笔记-文章id
            'article_id' => RequestHelper::postInt('article_id'),
        ];
    }
}
