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

namespace shopstar\admin\expressHelper;

use shopstar\bases\KdxAdminApiController;
use shopstar\exceptions\expressHelper\ExpressHelperException;
use shopstar\helpers\RequestHelper;
use shopstar\models\expressHelper\ExpressHelperSendBillLogModel;
use shopstar\models\expressHelper\ExpressHelperSendBillTemplateModel;

/**
 * 发货单打印
 * Class SendBillPrintController
 * @package shopstar\admin\expressHelper
 * @author 青岛开店星信息技术有限公司
 */
class SendBillPrintController extends KdxAdminApiController
{

    /**
     * 获取发货单数据
     * @return array|int[]|\yii\web\Response
     * @throws ExpressHelperException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $orderId = RequestHelper::postInt('order_id');
        $orderGoodsId = RequestHelper::postArray('order_goods_id');

        if (empty($orderId) || empty($orderGoodsId)) {
            throw new ExpressHelperException(ExpressHelperException::SEND_BILL_PRINT_INDEX_PARAMS_ERROR);
        }
        //获取订单数据
        $orderList = ExpressHelperSendBillTemplateModel::getSendBillPrintList($orderId, $orderGoodsId);

        if (is_error($orderList)) {
            return $this->error($orderList);
        }
        //增加打印log
        $res = ExpressHelperSendBillLogModel::addLog($orderList['id']);
        // 打印记录的id
        $orderList['express_log_id'] = $res->id;

        return $this->result($orderList);
    }

    /**
     * 根据模板ID获取模板内容
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetTemplateData()
    {
        $sendBillTemplateId = RequestHelper::getInt('send_bill_template_id');
        $sendBillTemplate = ExpressHelperSendBillTemplateModel::getSendBillTemplate($sendBillTemplateId);

        return $this->result($sendBillTemplate);
    }

}