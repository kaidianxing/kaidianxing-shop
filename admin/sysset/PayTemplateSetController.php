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

namespace shopstar\admin\sysset;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\sysset\PaymentLogConstant;
use shopstar\exceptions\sysset\PaymentException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\sysset\PaymentModel;

/**
 * 支付模版
 * Class TemplateSetController
 * @package shopstar\admin\sysset
 * @author 青岛开店星信息技术有限公司
 */
class PayTemplateSetController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'add',
            'update',
            'delete',
        ]
    ];

    /**
     * 支付模板列表
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $params = [
            'searchs' => [
                ['title', 'like', 'keyword']
            ],
            'select' => 'id, title, pay_type',
            'orderBy' => ['id' => SORT_DESC]
        ];

        $list = PaymentModel::getColl($params);

        return $this->result($list);
    }

    /**
     * 详情
     * @return array|int[]|\yii\web\Response
     * @throws PaymentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new PaymentException(PaymentException::TEMPLATE_SET_DETAIL_PARAMS_ERROR);
        }
        $detail = PaymentModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new PaymentException(PaymentException::TEMPLATE_SET_DETAIL_TEMPLATE_NOT_EXISTS);
        }

        return $this->success($detail);
    }

    /**
     * 新增
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $data = RequestHelper::post();
        $payment = new PaymentModel();
        $res = $payment->savePayment($data, $this->userId);
        if (is_error($res)) {
            throw new PaymentException(PaymentException::TEMPLATE_ADD_SAVE_FAIL, $res['message']);
        }

        return $this->success();
    }

    /**
     * 更新
     * @throws PaymentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate()
    {
        $id = RequestHelper::postInt('id');
        if (empty($id)) {
            throw new PaymentException(PaymentException::TEMPLATE_SET_UPDATE_PARAMS_ERROR);
        }
        $payment = PaymentModel::findOne(['id' => $id]);
        $res = $payment->savePayment(RequestHelper::post(), $this->userId);
        if (is_error($res)) {
            throw new PaymentException(PaymentException::TEMPLATE_UPDATE_SAVE_FAIL, $res['message']);
        }
        return $this->success();
    }

    /**
     * 删除/批量删除模板
     * @return array|int[]|\yii\web\Response
     * @throws PaymentException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $res = PaymentModel::easyDelete([
            'afterDelete' => function ($model) {
                // 记录日志
                $logPrimaryData = [
                    'ID' => $model->id,
                    '支付名称' => $model->title,
                    '支付类型' => PaymentModel::PAT_TYPE[$model->pay_type],
                    '支付方式' => $model->type == 1 ? '微信支付' : '支付宝支付',
                ];
                if ($model->type == 1) {
                    $logPrimaryData['微信支付APPID'] = $model->appid ?: '-';
                    $logPrimaryData['微信支付mchid'] = $model->mch_id ?: '-';
                    $logPrimaryData['微信apikey'] = $model->api_key ?: '-';
                    $logPrimaryData['子商户APPID'] = $model->sub_appid ?: '-';
                    $logPrimaryData['子商户APP Secret'] = $model->sub_appsecret ?: '-';
                    $logPrimaryData['子商户mchid'] = $model->sub_mch_id ?: '-';
                } else {
                    $logPrimaryData['验签方式'] = $model->sub_mch_id ?: '-';
                    $logPrimaryData['支付宝公钥'] = $model->sub_mch_id ?: '-';
                    $logPrimaryData['支付宝秘钥'] = $model->sub_mch_id ?: '-';
                }
                LogModel::write(
                    $this->userId,
                    PaymentLogConstant::PAYMENT_TEMPLATE_DELETE,
                    PaymentLogConstant::getText(PaymentLogConstant::PAYMENT_TEMPLATE_DELETE),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $logPrimaryData,
                        'dirty_identity_code' => [
                            PaymentLogConstant::PAYMENT_TEMPLATE_DELETE,
                        ]
                    ]
                );
            }
        ]);

        if (is_error($res)) {
            throw new PaymentException(PaymentException::PAYMENT_DELETE_FAIL);
        }
        return $this->success();
    }

}