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

namespace shopstar\mobile\form;

use shopstar\jobs\printer\AutoPrinterOrder;
use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\form\FormLogConstant;
use shopstar\constants\form\FormTypeConstant;
use shopstar\constants\printer\PrinterSceneConstant;
use shopstar\exceptions\form\FormException;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\form\FormLogModel;
use shopstar\models\form\FormModel;
use shopstar\models\order\OrderModel;

/**
 * 页面设置
 * Class PageController
 * @package apps\form\client
 */
class PageController extends BaseMobileApiController
{
    /**
     * 提交表单
     * @return array|\yii\web\Response
     * @throws FormException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSubmit()
    {
        $params = RequestHelper::post();
        if (empty($params['form_id'])) {
            throw new FormException(FormException::FORM_PAGE_SUBMIT_ID_NOT_EMPTY);
        }

        if (empty($params['content'])) {
            throw new FormException(FormException::FORM_PAGE_SUBMIT_CONTENT_NOT_EMPTY);
        }
        $where = [
            'id' => $params['form_id'],
            'is_deleted' => 0,
            'status' => 1
        ];
        $formExists = FormModel::find()
            ->select('type')
            ->where($where)->first();
        if (empty($formExists)) {
            throw new FormException(FormException::FORM_PAGE_RECORD_NOT_EXISTS);
        }

        // 订单类型表单校验订单ID
        if ($formExists['type'] == FormTypeConstant::FORM_TYPE_ORDER && empty($params['order_id'])) {
            throw new FormException(FormException::FORM_PAGE_SUBMIT_ORDER_ID_NOT_EMPTY);
        }

        // 商品类表单校验商品ID
        if ($formExists['type'] == FormTypeConstant::FORM_TYPE_GOODS && empty($params['goods_id'])) {
            throw new FormException(FormException::FORM_PAGE_SUBMIT_GOODS_ID_NOT_EMPTY);
        }

        if (!isset($params['order_id']) || empty($params['order_id'])) {
            $params['order_id'] = 0;
        }

        if (!isset($params['goods_id']) || empty($params['goods_id'])) {
            $params['goods_id'] = 0;
        }

        $params['md5'] = md5($params['old_content']);

        // 来源默认值 1: 下单提交 2: 价格面议提交
        if (empty($params['source']) && empty($params['order_id'])) {
            $params['source'] = FormLogConstant::FORM_SOURCE_BUY_BUTTON_GOODS;
        }

        $result = FormLogModel::submit($this->memberId, $params);

        if (is_error($result)) {
            throw new FormException(FormException::FORM_PAGE_SUBMIT_INVALID, $result['message']);
        }

        // 不打印价格面议提交的表单
        if ($params['source'] != FormLogConstant::FORM_SOURCE_BUY_BUTTON_GOODS && $formExists['type'] == FormTypeConstant::FORM_TYPE_ORDER) {
            // 打印小票
            $order = OrderModel::getOrderGoodsInfo($params['order_id']);
            QueueHelper::push(new AutoPrinterOrder([
                'job' => [
                    'scene' => PrinterSceneConstant::PRINTER_ORDER,
                    'order_id' => $params['order_id']
                ]
            ]));
        }

        return $this->result();
    }
}