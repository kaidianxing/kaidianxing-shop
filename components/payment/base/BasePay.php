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

namespace shopstar\components\payment\base;

use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\exceptions\member\MemberException;
use shopstar\exceptions\sysset\PaymentException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\FileHelper;
use shopstar\helpers\OrderNoHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\member\MemberCreditRecordModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderChangePriceLogModel;
use shopstar\models\order\PayOrderModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\sysset\PaymentModel;
use shopstar\structs\order\OrderPaySuccessStruct;
use Yansongda\Pay\Pay;
use yii\base\Component;
use yii\helpers\Json;

class BasePay extends Component
{
    /**
     * 注意：由于直接Yii::createObject注入，直接读取设置中字段所有使用下划线分隔单词
     */

    const SERVICE_MODEL = 11;

    /**
     * @var int 会员ID
     */
    public $member_id;

    /**
     * @var int 客户端类型
     */
    public $client_type;

    /**
     * @var int 订单表主键ID
     */
    public $order_id;

    /**
     * @var string 订单编号
     */
    public $order_no;

    /**
     * 订单数据
     * @var array
     * @author 青岛开店星信息技术有限公司.
     */
    public $orderData;

    /**
     * @var int 订单类型
     */
    public $order_type;

    /**
     * @var string 支付价格
     */
    public $pay_price;

    /**
     * @var int 支付类型
     */
    public $pay_type;

    /**
     * @var string 微信证书
     */
    public $cert_key;

    /**
     * @var string 微信证书
     */
    public $cert_client;

    /**
     * @var string 支付宝证书
     */
    public $cert_public_key_rsa2;

    /**
     * @var string 支付宝证书
     */
    public $app_cert_public_key;

    /**
     * @var string 支付宝证书
     */
    public $alipay_root_cert;

    /**
     * @var int 退款金额
     */
    public $refund_fee;

    /**
     * @var string 退款原因
     */
    public $refund_desc;

    /**
     * @var int 转账方式
     */
    public $transfer_type;

    /**
     * @var int 转账金额
     */
    public $transfer_fee;

    /**
     * @var string 转账原因
     */
    public $transfer_desc;

    /**
     * @var int 提现方式 佣金提现 余额提现
     */
    public $withdraw_order_type;

    /**
     * @var string alipay 账号
     */
    public $alipay;

    /**
     * @var string 真实姓名
     */
    public $real_name;

    /**
     * @var 支付订单
     */
    public $order;

    /**
     * 支付配置 手动set config 才会有，直接get则为空
     * @author 青岛开店星信息技术有限公司
     * @var PaymentModel
     */
    public $config;

    /**
     * @var int 改价次数
     */
    public $change_price_count;

    /**
     * 支付宝当面付扩展参数 因为当面付支付宝没有提供可扩展参数 所以放在异步回调上
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $extendParams;

    /**
     * 跳转URL，支付宝
     * @var string|null
     */
    public $return_url;

    /**
     * 商城名称
     * @var
     * @author 青岛开店星信息技术有限公司.
     */
    public $shopName;

    /**
     * 交易简介
     * @var
     * @author 青岛开店星信息技术有限公司.
     */
    public $body;

    /**
     * 初始化
     * @author 青岛开店星信息技术有限公司
     */
    public function init()
    {
        // 获取改价次数
        if (!empty($this->order_id)) {
            $this->change_price_count = OrderChangePriceLogModel::getChangePriceCount($this->order_id);
        }

        //获取商城名称
        $this->shopName = ShopSettings::get('sysset.mall.basic.name');

        //交易简介
        $this->body = $this->shopName . '商城的商品-消费';
    }


    /**
     * 校验支付类型
     * @param $payType
     * @return bool
     * @throws PaymentException
     * @author 青岛开店星信息技术有限公司
     */
    public function checkPayType($payType)
    {
        if (!is_string($payType)) {
            throw new PaymentException(PaymentException::PAYMENT_TYPE_INVALID);
        }

        return true;
    }

    /**
     * 设置支付参数
     * @param PaymentModel $config
     * @return PaymentModel
     * @author 青岛开店星信息技术有限公司
     */
    public function setConfig(PaymentModel $config)
    {
        return $this->config = $config;
    }

    /**
     * 获取交易单号
     * @param string $prefix
     * @param array $orderNo
     * @return string
     * @author 青岛开店星信息技术有限公司.
     */
    public function getOutTradeNo($prefix = 'JY')
    {
        return OrderNoHelper::getOrderOutTradeNo($prefix, $this->member_id, $this->order_type, (array)$this->order_id, (array)$this->order_no, [
            'change_price_count' => $this->change_price_count
        ]);
    }

    /**
     *
     * 获取支付配置项
     * @param $clientType
     * @param $payType
     * @return PaymentModel
     * @throws PaymentException
     * @author 青岛开店星信息技术有限公司
     */
    public function getConfig($clientType = '', $payType = '')
    {
        //如果$this->config不存在的话则重新获取配置
        if (!$this->config) {
            $settings = ShopSettings::get('sysset.payment.typeset')[$clientType];
            if (!isset($settings[$payType]) || $settings[$payType]['enable'] != 1) {
                throw new PaymentException(PaymentException::PAY_TYPE_IS_CLOSED);
            }
            $id = $settings[$payType]['id'];
            /** @var PaymentModel $config */
            $this->config = PaymentModel::find()->where(['id' => $id])->one();
            if ($this->config === null) {
                throw new PaymentException(PaymentException::PAY_TYPE_IS_NOT_ALLOWED);
            }
        }

        return $this->config;
    }

    /**
     * 获取提现配置项
     * @param $withdrawType
     * @author 青岛开店星信息技术有限公司
     */
    public static function getWithdrawConfig($clientType, $withdrawType)
    {
        // 获取提现配置
        $settings = ShopSettings::get('sysset.payment.payset');

        // h5 只能获取支付宝模板
        $withdrawTem = '';
        if ($clientType == 'h5' && $withdrawType == 'alipay') {
            $withdrawTem = 'alipay';
        }
        if ($clientType == 'wxapp' && $withdrawType == 'wechat') {
            $withdrawTem = 'wechat.wxapp';
        }
        if ($clientType == 'wechat' && $withdrawType == 'wechat') {
            $withdrawTem = 'wechat.wechat';
        }
        if ($clientType == 'wechat' && $withdrawType == 'alipay') {
            $withdrawTem = 'alipay';
        }
        if ($clientType == 'wxapp' && $withdrawType == 'alipay') {
            $withdrawTem = 'alipay';
        }

        if (empty(ArrayHelper::arrayGet($settings, $withdrawTem))) {
            throw new PaymentException(PaymentException::PAYSET_IS_CLOSED);
        }
        $id = ArrayHelper::arrayGet($settings, $withdrawTem)['id'];
        /** @var PaymentModel $config */
        $config = PaymentModel::find()->where(['id' => $id])->one();
        if ($config === null) {
            throw new PaymentException(PaymentException::PAYSET_IS_NOT_ALLOWED);
        }
        return $config;
    }

    /**
     * 获取提现方式配置项
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getWithdrawMethod()
    {
        // 获取提现配置
        $settings = ShopSettings::get('sysset.payment.payset');

        return [
            'pay_type_commission' => $settings['pay_type_commission'],
            'pay_type_withdraw' => $settings['pay_type_withdraw'],
            'pay_red_pack_money' => $settings['pay_red_pack_money'],
        ];
    }

    /**
     * 构建证书
     * @param string $name
     * @param string $content
     * @return string
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function buildPerm(string $name, string $content)
    {
        $path = SHOP_STAR_TMP_PATH . '/cert/' .  '/';
        if (!is_dir(($path))) {
            FileHelper::createDirectory($path);
        }
        $file = $path . StringHelper::random(16) . $name;

        file_put_contents($file, $content);

        return $file;
    }

    /**
     * 回调地址
     * @author 青岛开店星信息技术有限公司.
     */
    private function getNotifyUrl(): string
    {
        $notifyUrl = ShopUrlHelper::wap('/api/notify/index/index', [], true);

        return $notifyUrl;
    }

    /**
     * 获取支付宝支付实例
     * @param PaymentModel $payConf
     * @param bool $isRefund
     * @return \Yansongda\Pay\Gateways\Alipay
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function setAlipayConfig(PaymentModel $payConf, bool $isRefund = false)
    {
        /**
         * @var $payConf PaymentModel
         */
        $config = [
            'app_id' => $payConf->appid,
            'notify_url' => $isRefund ? '' : $this->getNotifyUrl(),
            'log' => [ // optional
                'file' => SHOP_STAR_TMP_PATH . '/logs/alipay.log',
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
            ],
        ];

        // 处理跳转URL
        if (!empty($this->return_url) && !$isRefund) {
            $config['return_url'] = $this->return_url;
        }

        //如果当面付扩展不为空
        if ($this->extendParams) {
            $config['notify_url'] = $config['notify_url'] . '?extend_params=' . urlencode($this->extendParams);
        }

        $config['private_key'] = $payConf->ali_private_key;

        $this->cert_public_key_rsa2 = self::buildPerm('alipayCertPublicKey_RSA2.crt', trim($payConf->alipay_cert_public_key_rsa2));
        $config['ali_public_key'] = $this->cert_public_key_rsa2;

        $this->app_cert_public_key = self::buildPerm('appCertPublicKey.crt', trim($payConf->app_cert_public_key));

        $config['app_cert_public_key'] = $this->app_cert_public_key;

        $this->alipay_root_cert = self::buildPerm('alipayRootCert.crt', trim($payConf->alipay_root_cert));
        $config['alipay_root_cert'] = $this->alipay_root_cert;
        if ($payConf->pay_type == self::SERVICE_MODEL) {
            $config['mode'] = 'service';
            $config['pid'] = $payConf->appid; //这里填写支付服务商的pid信息
        }

        // 此处清除yansongda.pay的$instance
        \Yansongda\Pay\Gateways\Alipay\Support::clear();

        // var_dump($config);die();

        return Pay::alipay($config);
    }

    /**
     * 获取微信公众号设置实例
     * @param PaymentModel $payConf
     * @param bool $needCert
     * @param bool $isRefund
     * @return \Yansongda\Pay\Gateways\Wechat
     * @author 青岛开店星信息技术有限公司
     */
    public function setWechatConfig(PaymentModel $payConf, bool $needCert = false, bool $isRefund = false)
    {
        /**
         * @var $payConf PaymentModel
         */
        $config = [
            'app_id' => $payConf->sub_appid,
            'mch_id' => $payConf->sub_mch_id,
            'key' => $payConf->api_key,
            'notify_url' => $isRefund ? '' : $this->getNotifyUrl(),
            'log' => [ // optional
                'file' => SHOP_STAR_TMP_PATH . '/logs/wechat.log',
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
            ],
        ];

        /** 退款需要证书 */
        if ($needCert === true) {
            if(!$payConf->wechat_key || !$payConf->wechat_cert) {
                throw new PaymentException(PaymentException::PAY_CONFIG_NEED_CERT);
            }
            $this->cert_key = self::buildPerm('wechatcertKey.pem', trim($payConf->wechat_key));
            $config['cert_key'] = $this->cert_key;

            $this->cert_client = self::buildPerm('wechatcertClient.cert', trim($payConf->wechat_cert));
            $config['cert_client'] = $this->cert_client;
        }
        if ($payConf->pay_type == self::SERVICE_MODEL) {
            $config['app_id'] = $payConf->appid;
            $config['mch_id'] = $payConf->mch_id;
            $config['sub_app_id'] = $payConf->sub_appid;
            $config['sub_mch_id'] = $payConf->sub_mch_id;
            $config['mode'] = 'service';
        }

        // 此处清除yansongda.pay的$instance
        \Yansongda\Pay\Gateways\Wechat\Support::clear();

        $result = Pay::wechat($config);


        return $result;
    }

    /**
     * 获取微信小程序设置实例
     * @param PaymentModel $payConf
     * @param bool $needCert
     * @param bool $isRefund
     * @return \Yansongda\Pay\Gateways\Wechat
     * @author 青岛开店星信息技术有限公司
     */
    public function setWxappConfig(PaymentModel $payConf, bool $needCert = false, bool $isRefund = false)
    {
        /**
         * @var $payConf PaymentModel
         */
        $config = [
            'miniapp_id' => $payConf->sub_appid,
            'mch_id' => $payConf->sub_mch_id,
            'key' => $payConf->api_key,
            'notify_url' => $isRefund ? '' : $this->getNotifyUrl(),
            'log' => [ // optional
                'file' => SHOP_STAR_TMP_PATH . '/logs/wxapp.log',
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
            ],
        ];

        /** 退款需要证书 */
        if ($needCert === true) {
            $this->cert_key = self::buildPerm('wxappcertKey.pem', trim($payConf->wechat_key));
            $config['cert_key'] = $this->cert_key;

            $this->cert_client = self::buildPerm('wxappcertClient.cert', trim($payConf->wechat_cert));
            $config['cert_client'] = $this->cert_client;
        }
        if ($payConf->pay_type == self::SERVICE_MODEL) {
            $config['miniapp_id'] = $payConf->appid;
            $config['mch_id'] = $payConf->mch_id;
            $config['sub_miniapp_id'] = $payConf->sub_appid;
            $config['sub_mch_id'] = $payConf->sub_mch_id;
            $config['mode'] = 'service';
        }

        // 此处清除yansongda.pay的$instance
        \Yansongda\Pay\Gateways\Wechat\Support::clear();

        $result = Pay::wechat($config);


        return $result;
    }

    /**
     * 获取支付订单
     * @throws PaymentException
     * @throws \shopstar\exceptions\order\OrderException
     */
    public function write()
    {

        //如果是商城订单更换处理方式
        if ($this->order_type == PayOrderTypeConstant::ORDER_TYPE_ORDER && $this->order_no == '') {
            $this->order = PayOrderModel::write($this->orderData, $this->pay_type, $this->client_type, $this->order_type);

            //获取支付金额
            $this->pay_price = array_sum(array_column($this->order, 'pay_price'));
            return;
        }

        $this->order = PayOrderModel::write2(
            0,
            $this->member_id,
            $this->order_id,
            $this->order_no,
            $this->order_type,
            $this->pay_type,
            $this->client_type,
            $this->pay_price
        );
    }

    /**
     * 余额支付
     * @return array|bool|MemberModel
     * @throws MemberException|\yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function balance()
    {
        // 写入订单记录
        $this->write();

        // 比较用户余额
        $member = MemberModel::findOne(['id' => $this->member_id]);
        if ($this->pay_price > $member->balance) {
            throw new MemberException(MemberException::MEMBER_BALANCE_IS_NOT_ENOUGH);
        }

        $trans = \Yii::$app->db->beginTransaction();

        $ret = MemberModel::updateCredit($this->member_id, $this->pay_price, 0, 'balance', 2, '余额支付', MemberCreditRecordStatusConstant::BALANCE_STATUS_PAY, [
            'order_id' => $this->order_id[0]
        ]);

        if (is_error($ret)) {
            $trans->rollBack();
            throw new MemberException(MemberException::MEMBER_DEDUCTION_FAILED, $ret['message']);
        }

        // 修改订单状态
        foreach ((array)$this->order as $orderIndex => $orderItem) {
            $orderItem->status = 10;
            $orderItem->pay_time = DateTimeHelper::now();
            $orderItem->trade_no = OrderNoHelper::getOrderNo('CA', $this->client_type);
            if ($orderItem->save() === false) {
                $trans->rollBack();
                throw new MemberException(MemberException::CHANGE_ORDER_STATUS_FAILED);
            }

            // 构建回调函数
            $callback = $merge = [
                'order_no' => $orderItem->order_no,
                'pay_price' => $orderItem->pay_price,
                'order_id' => $orderItem->order_id,
                'trans_id' => $orderItem->trade_no,
                'code' => $this->order_type,
            ];

            // 订单支付
            $model = PayOrderTypeConstant::getModel($this->order_type);

            //paysuccess结构体
            $orderPaySuccessStruct = \Yii::createObject([
                'class' => 'shopstar\structs\order\OrderPaySuccessStruct',
                'orderId' => $orderItem->order_id,
                'accountId' => $this->member_id,
                'payType' => $this->pay_type,
                'callBack' => $callback
            ]);

            /**
             * @var OrderPaySuccessStruct $orderPaySuccessStruct
             */
            $ret = ($model::paySuccess($orderPaySuccessStruct));

            if (is_error($ret)) {
                $trans->rollBack();
                $orderItem->status = 0;
                $orderItem->error_info = Json::encode($ret['message']);
                $orderItem->save();
                return $ret;
            }
        }

        $trans->commit();
        return true;
    }

    /**
     * 根据提现红包设置获取分片红包
     * @param $withdrawConf
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getRedpack($withdrawConf)
    {

        $redpack = [];
        // 金额大于 188 288 388 ？？？？
        if ($withdrawConf['pay_red_pack_money'] == 1) {
            while ($this->transfer_fee > 188) {
                $redpack[] = 188;

                $this->transfer_fee -= 188;
            }

            $redpack[] = $this->transfer_fee;
        }

        if ($withdrawConf['pay_red_pack_money'] == 2) {
            while ($this->transfer_fee > 288) {
                $redpack[] = 288;

                $this->transfer_fee -= 288;
            }

            $redpack[] = $this->transfer_fee;
        }

        if ($withdrawConf['pay_red_pack_money'] == 3) {
            while ($this->transfer_fee > 388) {
                $redpack[] = 388;

                $this->transfer_fee -= 388;
            }

            $redpack[] = $this->transfer_fee;
        }

        return $redpack;
    }

    /**
     * 删除临时证书
     */
    public function clearCert()
    {
        is_file($this->cert_public_key_rsa2) && unlink($this->cert_public_key_rsa2);
        is_file($this->app_cert_public_key) && unlink($this->app_cert_public_key);
        is_file($this->alipay_root_cert) && unlink($this->alipay_root_cert);

        is_file($this->cert_key) && unlink($this->cert_key);
        is_file($this->cert_client) && unlink($this->cert_client);
    }
}
