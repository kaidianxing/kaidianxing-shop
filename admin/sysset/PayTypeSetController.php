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
use shopstar\models\shop\ShopSettings;
use shopstar\models\sysset\PaymentModel;
use yii\db\Exception;
use yii\helpers\Url;

/**
 * 支付方式设置
 * Class PayTypeSetController
 * @package shopstar\admin\sysset
 * @author 青岛开店星信息技术有限公司
 */
class PayTypeSetController extends KdxAdminApiController
{

    /**
     * 获取支付设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetInfo()
    {
        $set = ShopSettings::get('sysset.payment.typeset');
        // 微信支付模板
        $set['wechat_template'] = PaymentModel::find()
            ->select('id, title')
            ->where([
                'and',
                ['between', 'pay_type', 10, 19],
                ['is_deleted' => 0]
            ])->get();
        // 支付宝支付模板
        $set['alipay_template'] = PaymentModel::find()
            ->select('id, title')
            ->where([
                'and',
                ['between', 'pay_type', 20, 29],
                ['is_deleted' => 0]
            ])->get();

        $set['white_host'] = str_replace('web', 'app', Url::base(true) . '/');

        return $this->success($set);
    }

    /**
     * 修改支付设置
     * @throws PaymentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate()
    {
        $post = RequestHelper::post();
        $data = [
            'wechat' => [
                'wechat' => [
                    'enable' => $post['wechat']['wechat']['enable'],
                    'id' => $post['wechat']['wechat']['id'],
                ],
                'alipay' => [
                    'enable' => $post['wechat']['alipay']['enable'],
                    'id' => $post['wechat']['alipay']['id'],
                ],
                'balance' => [
                    'enable' => $post['wechat']['balance']['enable'],
                ],
                'delivery' => [
                    'enable' => $post['wechat']['delivery']['enable'],
                ],
            ],
            'wxapp' => [
                'wechat' => [
                    'enable' => $post['wxapp']['wechat']['enable'],
                    'id' => $post['wxapp']['wechat']['id'],
                ],
                'balance' => [
                    'enable' => $post['wxapp']['balance']['enable'],
                ],
                'delivery' => [
                    'enable' => $post['wxapp']['delivery']['enable'],
                ],
            ],

            'pc' => [
                'wechat' => [
                    'enable' => $post['pc']['wechat']['enable'],
                    'id' => $post['pc']['wechat']['id'],
                ],
                'alipay' => [
                    'enable' => $post['pc']['alipay']['enable'],
                    'id' => $post['pc']['alipay']['id'],
                ],
                'balance' => [
                    'enable' => $post['pc']['balance']['enable'],
                ],
                'delivery' => [
                    'enable' => $post['pc']['delivery']['enable'],
                ],
            ],

            'h5' => [
                'alipay' => [
                    'enable' => $post['h5']['alipay']['enable'],
                    'id' => $post['h5']['alipay']['id'],
                ],
                'balance' => [
                    'enable' => $post['h5']['balance']['enable'],
                ],
                'delivery' => [
                    'enable' => $post['h5']['delivery']['enable'],
                ],
            ],
            'byte_dance' => [
                'byte_dance' => [
                    'enable' => $post['byte_dance']['byte_dance']['enable'],
                    'merchant_id' => $post['byte_dance']['byte_dance']['merchant_id'],
                    'token' => $post['byte_dance']['byte_dance']['token'],
                    'salt' => $post['byte_dance']['byte_dance']['salt'],
                ],
            ]
        ];
        // 公众号
        if ($data['wechat']['wechat']['enable'] == 1 && empty($data['wechat']['wechat']['id'])) {
            throw new PaymentException(PaymentException::TYPE_SET_WECHAT_TEMPLATE_PARAMS_ERROR);
        }
        if ($data['wechat']['alipay']['enable'] == 1 && empty($data['wechat']['alipay']['id'])) {
            throw new PaymentException(PaymentException::TYPE_SET_ALIPAY_TEMPLATE_PARAMS_ERROR);
        }
        // 小程序
        if ($data['wxapp']['wechat']['enable'] == 1 && empty($data['wxapp']['wechat']['id'])) {
            throw new PaymentException(PaymentException::TYPE_SET_WECHAT_TEMPLATE_PARAMS_ERROR);
        }
        // H5
        if ($data['h5']['alipay']['enable'] == 1 && empty($data['h5']['alipay']['id'])) {
            throw new PaymentException(PaymentException::TYPE_SET_ALIPAY_TEMPLATE_PARAMS_ERROR);
        }
        // pc
        if ($data['pc']['wechat']['enable'] == 1 && empty($data['pc']['wechat']['id'])) {
            throw new PaymentException(PaymentException::TYPE_SET_WECHAT_TEMPLATE_PARAMS_ERROR);
        }
        if ($data['pc']['alipay']['enable'] == 1 && empty($data['pc']['alipay']['id'])) {
            throw new PaymentException(PaymentException::TYPE_SET_ALIPAY_TEMPLATE_PARAMS_ERROR);
        }
        // 字节跳动
        if ($data['byte_dance']['alipay']['enable'] == 1 && empty($data['byte_dance']['alipay']['id'])) {
            throw new PaymentException(PaymentException::TYPE_SET_ALIPAY_TEMPLATE_PARAMS_ERROR);
        }
        try {
            ShopSettings::set('sysset.payment.typeset', $data);
            // 日志
            LogModel::write(
                $this->userId,
                PaymentLogConstant::PAYMENT_TYPE_SET_EDIT,
                PaymentLogConstant::getText(PaymentLogConstant::PAYMENT_TYPE_SET_EDIT),
                '0',
                [
                    'log_data' => $data,
                    'log_primary' => [
                        '微信公众号' => [
                            '微信支付状态' => $post['wechat']['wechat']['enable'] == 1 ? '开启' : '关闭',
                            '支付宝支付状态' => $post['wechat']['alipay']['enable'] == 1 ? '开启' : '关闭',
                            '余额支付状态' => $post['wechat']['balance']['enable'] == 1 ? '开启' : '关闭',
                            '货到付款状态' => $post['wechat']['delivery']['enable'] == 1 ? '开启' : '关闭',
                        ],
                        '微信小程序' => [
                            '微信支付状态' => $post['wxapp']['wechat']['enable'] == 1 ? '开启' : '关闭',
                            '余额支付状态' => $post['wxapp']['balance']['enable'] == 1 ? '开启' : '关闭',
                            '货到付款状态' => $post['wxapp']['delivery']['enable'] == 1 ? '开启' : '关闭',
                        ],
                        '浏览器H5支付' => [
                            '支付宝支付状态' => $post['h5']['alipay']['enable'] == 1 ? '开启' : '关闭',
                            '余额支付状态' => $post['h5']['balance']['enable'] == 1 ? '开启' : '关闭',
                            '货到付款状态' => $post['h5']['delivery']['enable'] == 1 ? '开启' : '关闭',
                        ],
                        '浏览器PC支付' => [
                            '微信支付状态' => $post['pc']['wechat']['enable'] == 1 ? '开启' : '关闭',
                            '支付宝支付状态' => $post['pc']['alipay']['enable'] == 1 ? '开启' : '关闭',
                            '余额支付状态' => $post['pc']['balance']['enable'] == 1 ? '开启' : '关闭',
                            '货到付款状态' => $post['pc']['delivery']['enable'] == 1 ? '开启' : '关闭',
                        ],
                        '字节跳动小程序' => [
                            '微信支付状态' => $post['byte_dance']['wechat']['enable'] == 1 ? '开启' : '关闭',
                            '支付宝支付状态' => $post['byte_dance']['alipay']['enable'] == 1 ? '开启' : '关闭',
                            '余额支付状态' => $post['byte_dance']['balance']['enable'] == 1 ? '开启' : '关闭',
                            '货到付款状态' => $post['byte_dance']['delivery']['enable'] == 1 ? '开启' : '关闭',
                        ]
                    ],
                    'dirty_identify_code' => [
                        PaymentLogConstant::PAYMENT_TYPE_SET_EDIT,
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new PaymentException(PaymentException::TYPE_SET_SAVE_FAIL);
        }

        return $this->success();
    }

}