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

use shopstar\constants\order\OrderDispatchExpressConstant;
use shopstar\exceptions\order\OrderCreatorException;
use shopstar\models\member\MemberAddressModel;
use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\create\OrderCreatorKernel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

class AddressHandler implements HandlerInterface
{
    private $orderCreatorKernel;

    /**
     * HandlerInterface constructor.
     * @param OrderCreatorKernel $orderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 订单业务核心处理器标准
     *
     * 请注意：请不要忘记处理完成之后需要挂载到订单实体类下，请不要随意删除在当前挂载属性以外的属性
     * @return mixed|void
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function processor()
    {
        // 无需发货时不处理地址
        // 虚拟商品不需要发货

        if ($this->orderCreatorKernel->orderData['dispatch_type'] == 0) {
            return;
        }


        // 10 快递 30 同城配送
        if ($this->orderCreatorKernel->orderData['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_EXPRESS ||
            $this->orderCreatorKernel->orderData['dispatch_type'] == OrderDispatchExpressConstant::ORDER_DISPATCH_INTRACITY) {

            $addressWhere = [
                'member_id' => $this->orderCreatorKernel->memberId,
                'is_delete' => 0,
            ];

            $orderBy = [];
            if ($this->orderCreatorKernel->inputData['address_id'] > 0) {
                $addressWhere['id'] = $this->orderCreatorKernel->inputData['address_id'];
            } else {
                $orderBy = [
                    'is_default' => SORT_DESC,
                    'id' => SORT_DESC,
                ];
            }

            //获取会员地址
            $addressInfo = MemberAddressModel::find()->asArray()->where($addressWhere)->orderBy($orderBy)->asArray()->one();

            // 提交订单时验证不能为空
            if (!$this->orderCreatorKernel->isConfirm && empty($addressInfo) && !$this->orderCreatorKernel->isVirtual) {
                throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_ADDRESS_HANDLER_ADDRESS_EMPTY_ERROR);
            }

            // 不配送区域
            $denyArea = ShopSettings::get('sysset.express.address.deny_area');
            // 配送类型
            $deliveryType = ShopSettings::get('sysset.express.address.delivery_type');
            // 只配送区域
            $deliveryArea = ShopSettings::get('sysset.express.address.delivery_area');
            //提交订单判断地址是否正确

            if ($deliveryType == 0 && !$this->orderCreatorKernel->isConfirm && !empty(Json::decode($denyArea)) && !$this->orderCreatorKernel->isVirtual) {
                $denyArea = Json::decode($denyArea);
                // 在不配送区域里
                if (!empty($denyArea['areas']) && in_array($addressInfo['address_code'], $denyArea['areas']) && !$this->orderCreatorKernel->isConfirm) {
                    throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_ADDRESS_HANDLER_DENY_ADDRESS_ERROR);
                }
            }
            if ($deliveryType == 1 && !$this->orderCreatorKernel->isConfirm && !empty(Json::decode($deliveryArea)) && !$this->orderCreatorKernel->isVirtual) {
                $deliveryArea = Json::decode($deliveryArea);
                //不在只配送区域内
                if (!empty($deliveryArea['areas']) && !in_array($addressInfo['address_code'], $deliveryArea['areas']) && !$this->orderCreatorKernel->isConfirm) {
                    throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_ADDRESS_HANDLER_DENY_ADDRESS_ERROR);
                }
            }

            // 挂载用户地址信息
            $this->orderCreatorKernel->orderData['buyer_mobile'] = $addressInfo['mobile'] ?? '';
            $this->orderCreatorKernel->orderData['buyer_name'] = $addressInfo['name'] ?? '';
        }

        // 地址信息塞入
        $this->orderCreatorKernel->orderData['address_id'] = $addressInfo['id'] ?? 0;
        $this->orderCreatorKernel->orderData['address_state'] = $addressInfo['province'] ?? '';
        $this->orderCreatorKernel->orderData['address_city'] = $addressInfo['city'] ?? '';
        $this->orderCreatorKernel->orderData['address_area'] = $addressInfo['area'] ?? '';
        $this->orderCreatorKernel->orderData['address_detail'] = $addressInfo['address'] ?? '';
        $this->orderCreatorKernel->orderData['address_name'] = $addressInfo['name'] ?? '';
        $this->orderCreatorKernel->orderData['address_code'] = $addressInfo['address_code'] ?? '';

        $this->orderCreatorKernel->confirmData['opening_type'] = $addressInfo['opening_type'] ?? null;
        $this->orderCreatorKernel->confirmData['opening_rule'] = (!isset($addressInfo['opening_rule']) || $addressInfo['opening_type'] == 0) ? null : Json::decode($addressInfo['opening_rule']);

        // 收货地址挂载到订单
        if (!empty($addressInfo)) {
            $this->orderCreatorKernel->address = [
                'dispatch_type' => $this->orderCreatorKernel->orderData['dispatch_type'],
                'address_id' => $addressInfo['id'],
                'province' => $addressInfo['province'] ?: '',
                'city' => $addressInfo['city'] ?: '',
                'area' => $addressInfo['area'] ?: '',
                'area_code' => $addressInfo['address_code'] ?: 0,
                'address_detail' => $addressInfo['address'] ?: '',
                'lng' => $addressInfo['lng'] ?: '',
                'lat' => $addressInfo['lat'] ?: '',
                'name' => $addressInfo['name'] ?: '',
                'postcode' => $addressInfo['zip_code'] ?: 0,
            ];
        }

        // 提交订单时生成address_info字段
        if (!$this->orderCreatorKernel->isConfirm) {
            $this->orderCreatorKernel->orderData['address_info'] = $this->orderCreatorKernel->address;
            $this->orderCreatorKernel->orderData['address_json_md5'] = md5(Json::encode($this->orderCreatorKernel->orderData['address_info']));
            $this->orderCreatorKernel->orderData['address_json_md5'] = md5(Json::encode($this->orderCreatorKernel->orderData['address_info']));
        }

        return;
    }
}
