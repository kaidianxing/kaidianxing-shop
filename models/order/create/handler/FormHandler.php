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

use shopstar\models\form\FormTempModel;
use shopstar\models\order\create\OrderCreatorKernel;

/**
 * 挂载商品中表单信息
 * Class FormHandler
 * @package shopstar\models\order\create\handler
 * @author 青岛开店星信息技术有限公司
 */
class FormHandler
{

    public $orderCreatorKernel;

    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {

        $this->orderCreatorKernel = $orderCreatorKernel;
    }


    /**
     * 挂载每个商品的表单数据
     * @author 青岛开店星信息技术有限公司
     */
    public function processor()
    {

        $formData = FormTempModel::find()
            ->where([
                'goods_id' => $this->orderCreatorKernel->goodsIds,
                'member_id' => $this->orderCreatorKernel->memberId
            ])
            ->asArray()
            ->all();


        if ($formData) {
            foreach ($this->orderCreatorKernel->orderGoodsData as $k => &$v) {
                foreach ($formData as $kk => $vv) {
                    if ($v['goods_id'] == $vv['goods_id'] && $v['form_id'] == $vv['form_id']) {
                        $this->orderCreatorKernel->orderGoodsData[$k]['form_data'] = $vv;
                    }
                }
            }
            unset($v);
        }

        return;

    }

}