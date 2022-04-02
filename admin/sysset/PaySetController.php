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

/**
 * 打款设置
 * Class PaySetController
 * @package shopstar\admin\sysset
 * @author 青岛开店星信息技术有限公司
 */
class PaySetController extends KdxAdminApiController
{

    /**
     * 获取打款设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetInfo()
    {
        $set = ShopSettings::get('sysset.payment.payset');

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

        return $this->success($set);
    }

    /**
     * 修改打款设置
     * @return \yii\web\Response
     * @throws PaymentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate(): \yii\web\Response
    {
        $post = RequestHelper::post();
        $data = [
            'wechat' => [
                'enable' => $post['wechat']['enable'],
                'wechat' => [
                    'id' => $post['wechat']['wechat']['id'],
                ],
                'wxapp' => [
                    'id' => $post['wechat']['wxapp']['id'],
                ]
            ],
            'alipay' => [
                'enable' => $post['alipay']['enable'],
                'id' => $post['alipay']['id'],
            ],
            'pay_type_commission' => $post['pay_type_commission'],
            'pay_type_withdraw' => $post['pay_type_withdraw'],
            'pay_red_pack_money' => $post['pay_red_pack_money'],

        ];
        try {
            ShopSettings::set('sysset.payment.payset', $data);
            // 日志
            $redPack = [
                '1' => '188 元',
                '2' => '288 元',
                '3' => '388 元',
            ];
            LogModel::write(
                $this->userId,
                PaymentLogConstant::PAYMENT_PAY_SET_EDIT,
                PaymentLogConstant::getText(PaymentLogConstant::PAYMENT_PAY_SET_EDIT),
                '0',
                [
                    'log_data' => $data,
                    'log_primary' => [
                        '支付宝打款状态' => $data['alipay']['enable'] == 1 ? '开启' : '关闭',
                        '微信打款状态' => $data['wechat']['enable'] == 1 ? '开启' : '关闭',
                        '佣金打款' => $data['pay_type_commission'] == 1 ? '企业打款' : '红包付款',
                        '提现申请' => $data['pay_type_withdraw'] == 1 ? '企业打款' : '红包付款',
                        '红包金额' => $redPack[$data['pay_red_pack_money']],
                    ],
                    'dirty_identify_code' => [
                        PaymentLogConstant::PAYMENT_PAY_SET_EDIT
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new PaymentException(PaymentException::PAY_SET_SAVE_FAIL);
        }
        return $this->success();
    }

}