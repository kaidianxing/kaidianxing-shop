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


namespace shopstar\components\paymentNew\bases;

use shopstar\constants\base\PayTypeConstant;
use shopstar\constants\ClientTypeConstant;
use shopstar\helpers\FileHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\core\CoreSettings;
use shopstar\models\shop\ShopSettings;
use shopstar\models\sysset\PaymentModel;
use yii\base\Component;
use yii\helpers\Url;

/**
 * 支付组件驱动基类
 * Class BasePaymentNewDriver
 * @author likexin
 */
class BasePaymentNewDriver extends Component {
    
    /**
     * @var int|null 支付类型
     */
    public $payType;
    
    /**
     * @var string|null 支付类型标识
     */
    public $payTypeIdentity;
    
    /**
     * @var int|null 客户端类型
     */
    public $clientType;
    
    /**
     * @var int|null 客户端类型
     */
    public $clientTypeIdentify;

    /**
     * @var int|null 支付模板ID
     */
    public $paymentId;
    
    /**
     * @var int|null 支付账号ID
     */
    public $accountId;
    
    /**
     * @var string|null 交易订单号
     */
    public $tradeNo;
    
    /**
     * @var int|null 订单金额
     */
    public $orderPrice;
    
    /**
     * @var string|null 支付主题
     */
    public $subject;
    
    /**
     * @var string|null 关闭时间
     */
    public $closeTime;
    
    /**
     * @var string|null 状态通知地址
     */
    public $notifyUrl;
    
    /**
     * @var string|null 回调地址
     */
    public $callbackUrl;
    
    /**
     * @var PaymentModel 支付模板
     */
    protected $paymentModel;
    
    /**
     * @var bool 服务商模式
     */
    protected $isServiceMode = false;
    
    /**
     * @var bool 字节跳动支付appid
     */
    protected $bytedanceAppid = '';
    
    /**
     * @var bool 字节跳动支付salt
     */
    protected $bytedanceSalt = '';
    
    /**
     * @var array 临时证书
     */
    private $tmpCertPaths = [];
    
    /**
     * @throws \Exception
     * @author likexin
     */
    public function init() {
        // 检测参数
        $this->checkParams();

        // 加载设置
        $this->loadSettings();

        parent::init();
    }

    /**
     * 检测必要参数
     * @throws \Exception
     * @author likexin
     */
    protected function checkParams() {
        if (empty($this->clientType)) {
            throw new \Exception('参数错误 clientType');
        } elseif (empty($this->tradeNo)) {
            throw new \Exception('参数错误 tradeNo');
        }
        
        // 支付类型标识
        if (empty($this->clientTypeIdentify)) {
            $this->clientTypeIdentify = ClientTypeConstant::getIdentify($this->clientType);
        }
        
        // 关闭时间，默认退后7天
        if (is_null($this->closeTime)) {
            $this->closeTime = date('Y-m-d H:i:s', strtotime('+ 7days'));
        }
        
        // 支付主题
        if (is_null($this->subject)) {
            $this->subject = '商城订单支付';
        }
        
        if (is_null($this->notifyUrl)) {
            $this->notifyUrl = Url::base(true) . '/notify/pay.php';
        }
    }
    
    /**
     * 加载设置
     * @throws \Exception
     * @author likexin
     */
    public function loadSettings() {
        // 判断条件 商家端/平台端购买应用取管理端设置
        if ($this->clientType != ClientTypeConstant::MANAGE_PC) {
            // 非余额支付时，查询设置
            if ($this->payType != PayTypeConstant::TYPE_BALANCE && $this->payType != PayTypeConstant::PAY_TYPE_BYTEDANCE) {
                $this->paymentId = $this->getPaymentId();

                $this->paymentModel = PaymentModel::find()->where(['id' => $this->paymentId])->one();
                if (empty($this->paymentModel)) {
                    throw new \Exception('店铺支付设置支付模板未找到');
                }

                // 服务商模式
                $this->isServiceMode = $this->paymentModel->pay_type == 11;
            } else if ($this->payType == PayTypeConstant::PAY_TYPE_BYTEDANCE) {
                // 是字节跳动支付 获取配置
                // appid
                $this->bytedanceAppid = ShopSettings::get('channel_setting.byte_dance.appid');
            }

        } else {
            // 没传入店铺ID读取系统支付设置
            $coreSettings = CoreSettings::get('payment');
            if (empty($coreSettings)) {
                throw new \Exception('店铺支付设置不支持的支付类型');
            } else if (empty($coreSettings[$this->payTypeIdentity])) {
                throw new \Exception('店铺支付设置不支持的支付类型');
            } else if (empty($coreSettings[$this->payTypeIdentity]['enabled'])) {
                throw new \Exception('店铺支付设置未开启当前支付类型');
            }

            // 组装微信参数
            if ($this->payTypeIdentity == 'wechat') {
                $data = [
                    'sub_appid' => $coreSettings[$this->payTypeIdentity]['app_id'],
                    'sub_mch_id' => $coreSettings[$this->payTypeIdentity]['mch_id'],
                    'api_key' => $coreSettings[$this->payTypeIdentity]['key'],
                ];
            } else {
                // 支付宝参数
                $data = [
                    'appid' => $coreSettings[$this->payTypeIdentity]['app_id'],
                    'ali_private_key' => $coreSettings[$this->payTypeIdentity]['private_key'],
                    'alipay_cert_public_key_rsa2' => $coreSettings[$this->payTypeIdentity]['alipay_cert_public_key_rsa2'],
                    'app_cert_public_key' => $coreSettings[$this->payTypeIdentity]['app_cert_public_key'],
                    'alipay_root_cert' => $coreSettings[$this->payTypeIdentity]['alipay_root_cert'],
                ];
            }
            
            $this->paymentModel = new PaymentModel();
            $this->paymentModel->setAttributes($data);
        }
        
    }
    
    /**
     * 生成临时证书
     * @param string $content
     * @param string $ext
     * @return string
     * @author likexin
     */
    protected function buildTmpCert(string $content, string $ext) {
        try {
            $path = SHOP_STAR_TMP_PATH . '/cert/';
            if (!is_dir(($path))) {
                FileHelper::createDirectory($path);
            }
            
            // 写入文件
            $certPath = $path . StringHelper::random(32) . ".{$ext}";
            file_put_contents($certPath, trim($content));
            
            // 塞入类缓存
            $this->tmpCertPaths[] = $certPath;
            
            return $certPath;
        } catch (\Exception $exception) {
            return '';
        }
    }
    
    /**
     * 清除临时证书
     * @author likexin
     */
    public function clearTmpCert() {
        foreach ($this->tmpCertPaths as $path) {
            is_file($path) && unlink($path);
        }
    }
    
    /**
     * 获取支付模板id
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function getPaymentId()
    {
        $paymentId = 0;

        $settings = ShopSettings::get('sysset.payment.typeset');
        if (empty($settings)) {
            throw new \Exception('店铺支付设置错误');
        }
        // 获取支付类型
        $payTypeSettings = $settings[$this->clientTypeIdentify] ?? [];
        if (empty($payTypeSettings)) {
            throw new \Exception('店铺支付设置不支持的支付类型');
        } elseif (empty($payTypeSettings[$this->payTypeIdentity])) {
            throw new \Exception('店铺支付设置不支持的支付类型');
        } elseif (empty($payTypeSettings[$this->payTypeIdentity]['enable'])) {
            throw new \Exception('店铺支付设置未开启当前支付类型');
        }
        $paymentId = $payTypeSettings[$this->payTypeIdentity]['id'] ?? 0;

        
        return $paymentId;
    }

}