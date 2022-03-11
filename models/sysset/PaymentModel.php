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

namespace shopstar\models\sysset;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\log\sysset\PaymentLogConstant;

use shopstar\helpers\DateTimeHelper;
use shopstar\models\log\LogModel;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%payment}}".
 *
 * @property int $id
 * @property string $ver
 * @property int $is_deleted 是否删除0=未删除, 1=已删除
 * @property int $pay_type 支付呢类型, 10-19微信,20-29支付宝,30+其他支付
 * @property string $wechat_cert 微信证书
 * @property string $wechat_key 微信证书key
 * @property string $wechat_root_cert 微信根本证书, 大部分用户可以不填写
 * @property string $title 支付名称
 * @property string $appid 微信支付APPID
 * @property string $mch_id 微信支付mchid
 * @property string $api_key 微信apikey
 * @property string $sub_appid 子商户APPID
 * @property string $sub_appsecret 子商户APP Secret
 * @property string $sub_mch_id 子商户mchid
 * @property int $sign_type 验签方式, 1=MD5/RSA, 2=RSA2
 * @property int $is_raw 是否原生
 * @property int $type 支付方式  1微信支付  2支付宝
 * @property string $ali_public_key 支付宝公钥
 * @property string $ali_private_key 支付宝秘钥
 * @property string $app_cert_public_key 商户证书
 * @property string $alipay_cert_public_key_rsa2 支付宝公钥证书
 * @property string $alipay_root_cert 支付宝根证书
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class PaymentModel extends BaseActiveRecord
{
    const WECHAT_PAY = 10;
    const WECHAT_PAY_SUB = 11;
    const BORROW_WECHAT_PAY = 12;
    const BORROW_WECHAT_PAY_SUB = 13;
    const AliPay = 20;
    const WFTPay = 30;

    const PAT_TYPE = [
        10 => '微信支付',
        11 => '微信支付子商户',
        12 => '借用支付',
        13 => '借用微信支付子商户',
        20 => '支付宝',
        30 => '威富通(兼容全付通)'
    ];

    /**
     * 日志数据
     * @var array
     */
    private $logPrimaryData = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ver', 'is_deleted', 'pay_type', 'sign_type', 'is_raw', 'type'], 'integer'],
            [['wechat_cert', 'wechat_key', 'wechat_root_cert', 'ali_public_key', 'ali_private_key', 'app_cert_public_key', 'alipay_cert_public_key_rsa2', 'alipay_root_cert'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['updated_at'], 'required'],
            [['title', 'merchant_name'], 'string', 'max' => 64],
            [['appid', 'mch_id', 'sub_appid', 'sub_mch_id'], 'string', 'max' => 32],
            [['api_key', 'sub_appsecret'], 'string', 'max' => 50],
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'id' => 'ID',
            'pay_type' => '支付类型',
            'title' => '支付名称',
            'appid' => '微信支付APPID',
            'alipay_appid' => '支付宝APPID',
            'mch_id' => '微信支付mchid',
            'api_key' => '微信apikey',
            'sub_appid' => '子商户APPID',
            'sub_appsecret' => '子商户APP Secret',
            'sub_mch_id' => '子商户mchid',
            'sign_type' => '验签方式',
            'type' => '支付方式',
            'ali_public_key' => '支付宝公钥',
            'ali_private_key' => '支付宝秘钥',
            'app_cert_public_key' => '商户证书',
            'alipay_cert_public_key_rsa2' => '支付宝公钥证书',
            'alipay_root_cert' => '支付宝根证书',
            'wechat_cert' => 'CERT文件证书',
            'wechat_key' => 'KEY密钥文件',
            'wechat_root_cert' => '微信根本证书',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ver' => 'Ver',
            'is_deleted' => '是否删除0=未删除, 1=已删除',
            'pay_type' => '支付呢类型, 10-19微信,20-29支付宝,30+其他支付',
            'wechat_cert' => '微信证书',
            'wechat_key' => '微信证书key',
            'wechat_root_cert' => '微信根本证书, 大部分用户可以不填写',
            'title' => '支付名称',
            'appid' => '微信支付APPID',
            'mch_id' => '微信支付mchid',
            'api_key' => '微信apikey',
            'sub_appid' => '子商户APPID',
            'sub_appsecret' => '子商户APP Secret',
            'sub_mch_id' => '子商户mchid',
            'sign_type' => '验签方式, 1=MD5/RSA, 2=RSA2',
            'is_raw' => '是否原生',
            'type' => '支付方式  1微信支付  2支付宝',
            'ali_public_key' => '支付宝公钥',
            'ali_private_key' => '支付宝秘钥',
            'app_cert_public_key' => '商户证书',
            'alipay_cert_public_key_rsa2' => '支付宝公钥证书',
            'alipay_root_cert' => '支付宝根证书',
            'merchant_name' => '商户名称',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 保存支付方式
     * @param array $data
     * @param $uid
     * @return int|array
     * @author 青岛开店星信息技术有限公司
     */
    public function savePayment(array $data, $uid)
    {
        $data['pay_type'] = (int)$data['pay_type'];

        $saveData = [
            'type' => $data['type'],
            'pay_type' => $data['pay_type'],
            'title' => $data['title'],
            'merchant_name' => $data['merchant_name'] ?? '',
            'updated_at' => DateTimeHelper::now()
        ];
        $this->logPrimaryData = [
            'title' => $data['title'],
            'pay_type' => self::PAT_TYPE[$data['pay_type']],
            'type' => $data['type'] == 1 ? '微信支付' : '支付宝支付',
        ];

        $this->setAttributes($saveData);

        switch ($data['pay_type']) {
            case self::WECHAT_PAY:
                $res = $this->wechatPay($data);
                break;
            case self::WECHAT_PAY_SUB:
                $res = $this->wechatPaySub($data);
                break;
//            case self::BORROW_WECHAT_PAY:
//                $res = $this->borrowWechatPay($data);
//                break;
//            case self::BORROW_WECHAT_PAY_SUB:
//                $res = $this->borrowWechatPaySub($data);
//                break;
            case self::AliPay:
                $res = $this->aliPay($data);
                break;
//            case self::WFTPay:
//                $res = $this->WFTPay($data);
//                break;
            default:
                return error('类型错误');
        }

        // 返回错误
        if (is_error($res)) {
            return $res;
        }

        if ($this->save() === false) {
            return error('保存失败');
        }
        $code = !empty($data['id']) ? PaymentLogConstant::PAYMENT_TEMPLATE_EDIT : PaymentLogConstant::PAYMENT_TEMPLATE_ADD;
        // 记录日志
        LogModel::write(
            $uid,
            $code,
            PaymentLogConstant::getText($code),
            $this->id,
            [
                'log_data' => $this->attributes,
                'log_primary' => $this->getLogAttributeRemark($this->logPrimaryData),
                'dirty_identity_code' => [
                    PaymentLogConstant::PAYMENT_TEMPLATE_ADD,
                    PaymentLogConstant::PAYMENT_TEMPLATE_EDIT,
                ]
            ]
        );

        return $this->id;
    }

    /**
     * 上传微信证书
     * @param $data
     * @author 青岛开店星信息技术有限公司
     */
    public function uploadCert(&$data)
    {
        $wechatCert = UploadedFile::getInstanceByName('wechat_cert');
        $wechatKey = UploadedFile::getInstanceByName('wechat_key');
        $wechatRootCert = UploadedFile::getInstanceByName('wechat_root_cert');

        if (!empty($wechatCert)) {
            $data['wechat_cert'] = file_get_contents($wechatCert->tempName);
        }

        if (!empty($wechatKey)) {
            $data['wechat_key'] = file_get_contents($wechatKey->tempName);
        }

        if (!empty($wechatRootCert)) {
            $data['wechat_root_cert'] = file_get_contents($wechatRootCert->tempName);
        }
    }

    /**
     * 上传支付宝证书
     * @param $data
     * @author 青岛开店星信息技术有限公司
     */
    public function uploadAlipayCert(&$data)
    {
        $appCertPublicKey = UploadedFile::getInstanceByName('app_cert_public_key');
        $alipayCertPublicKeyRsa2 = UploadedFile::getInstanceByName('alipay_cert_public_key_rsa2');
        $alipayRootCert = UploadedFile::getInstanceByName('alipay_root_cert');

        if (!empty($appCertPublicKey)) {
            $data['app_cert_public_key'] = file_get_contents($appCertPublicKey->tempName);
        }

        if (!empty($alipayCertPublicKeyRsa2)) {
            $data['alipay_cert_public_key_rsa2'] = file_get_contents($alipayCertPublicKeyRsa2->tempName);
        }

        if (!empty($alipayRootCert)) {
            $data['alipay_root_cert'] = file_get_contents($alipayRootCert->tempName);
        }
    }

    /**
     * 微信支付公共参数
     * @param $data
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    protected function publicWechatParams(&$data)
    {
        //上传证书
        $this->uploadCert($data);
        if (!empty($data['wechat_cert'])) {
            $this->setAttribute('wechat_cert', $data['wechat_cert']);
        }
        if (!empty($data['wechat_key'])) {
            $this->setAttribute('wechat_key', $data['wechat_key']);
        }
        //可有可无的字段
        if (!empty($data['wechat_root_cert'])) {
            $this->setAttribute('wechat_root_cert', $data['wechat_root_cert']);
        }
        // sub_appid
        if (!empty($data['sub_appid'])) {
            $this->setAttribute('sub_appid', $data['sub_appid']);
            $this->logPrimaryData['sub_appid'] = $data['sub_appid'];
        } else {
            return error('SUB_APPID不能为空');
        }

        // sub_mch_id
        if (!empty($data['sub_mch_id'])) {
            $this->setAttribute('sub_mch_id', $data['sub_mch_id']);
            $this->logPrimaryData['sub_mch_id'] = $data['sub_mch_id'];
        } else {
            return error('SUB_MCH_ID不能为空');
        }

        // api_key
        if (!empty($data['api_key'])) {
            $this->setAttribute('api_key', $data['api_key']);
            $this->logPrimaryData['api_key'] = $data['api_key'];
        } else {
            return error('APIKEY不能为空');
        }

        // 证书
        if (!empty($this->wechat_cert)) {
            $this->logPrimaryData['wechat_cert'] = '已上传';
        }
        // key
        if (!empty($this->wechat_key)) {
            $this->logPrimaryData['wechat_key'] = '已上传';
        }
        // root
        if (!empty($this->wechat_root_cert)) {
            $this->logPrimaryData['wechat_root_cert'] = '已上传';
        }

    }

    /**
     * 微信支付子商户公共参数
     * @param $data
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    protected function publicWechatSubParams(&$data)
    {

        // appid
        if (!empty($data['appid'])) {
            $this->setAttribute('appid', $data['appid']);
            $this->logPrimaryData['appid'] = $data['appid'];
        } else {
            return error('appid不能为空');
        }

        // mch_id
        if (!empty($data['mch_id'])) {
            $this->setAttribute('mch_id', $data['mch_id']);
            $this->logPrimaryData['mch_id'] = $data['mch_id'];
        } else {
            return error('mch_id不能为空');
        }

        return $this->publicWechatParams($data);
    }

    /**
     * 微信支付参数
     * @param $data
     * @author 青岛开店星信息技术有限公司
     */
    public function wechatPay(&$data)
    {
        return $this->publicWechatParams($data);
    }


    /**
     * 微信支付子商户参数
     * @param $data
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function wechatPaySub(&$data)
    {
        return $this->publicWechatSubParams($data);
    }


    /**
     * 借用微信支付参数配置
     * @param $data
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function borrowWechatPaySub(&$data)
    {
        if (empty($data['sub_appid'])) {
            return error('借用公众号APPID 不能为空');
        }

        if (empty($data['sub_appsecret'])) {
            return error('借用公众号APPSECRET 不能为空');
        }

        $this->setAttribute('sub_appid', $data['sub_appid']); //当前借用公众号
        $this->setAttribute('sub_appsecret', $data['sub_appsecret']); //当前借用公众号appsecret

        $this->publicWechatParams($data);
    }


    /**
     * 借用微信支付子商户参数配置
     * @param $data
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public function borrowWechatPay(&$data)
    {
        if (empty($data['sub_appid'])) {
            return error('借用公众号APPID 不能为空');
        }

        if (empty($data['sub_appsecret'])) {
            return error('借用公众号APPSECRET 不能为空');
        }

        $this->setAttribute('sub_appid', $data['sub_appid']); //当前借用公众号
        $this->setAttribute('sub_appsecret', $data['sub_appsecret']); //当前借用公众号appsecret

        $this->publicWechaSubtParams($data);

        return true;
    }

    /**
     * 支付宝支付配置信息
     * @param $data
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public function aliPay(&$data)
    {
        //上传证书
        $this->uploadAlipayCert($data);

        if (!empty($data['appid'])) {
            $this->setAttribute('appid', $data['appid']); //支付宝共有key
            $this->logPrimaryData['alipay_appid'] = $data['appid'];
        } else {
            return error('支付宝应用appid 不能为空');
        }

        //支付宝私有key
        if (!empty($data['ali_private_key'])) {
            $this->setAttribute('ali_private_key', $data['ali_private_key']);
            $this->logPrimaryData['ali_private_key'] = $data['ali_private_key'];
        } else {
            return error('支付宝私有key不能为空');
        }

        //支付宝商户证书
        if (!empty($data['app_cert_public_key'])) {
            $this->setAttribute('app_cert_public_key', $data['app_cert_public_key']);
        }

        //支付宝公钥证书
        if (!empty($data['alipay_cert_public_key_rsa2'])) {
            $this->setAttribute('alipay_cert_public_key_rsa2', $data['alipay_cert_public_key_rsa2']);
        }

        //支付宝根证书
        if (!empty($data['alipay_root_cert'])) {
            $this->setAttribute('alipay_root_cert', $data['alipay_root_cert']);
        }

        //签名类型1=RSA, 2=RSA2
        $this->setAttribute('sign_type', $data['sign_type']);
        $this->logPrimaryData['sign_type'] = $this->sign_type == 1 ? 'MD5/RSA' : 'RSA2';

        // 商户证书
        if (!empty($this->app_cert_public_key)) {
            $this->logPrimaryData['app_cert_public_key'] = '已上传';
        }
        // 支付宝公钥证书
        if (!empty($this->alipay_cert_public_key_rsa2)) {
            $this->logPrimaryData['alipay_cert_public_key_rsa2'] = '已上传';
        }
        // 支付宝根证书
        if (!empty($this->alipay_root_cert)) {
            $this->logPrimaryData['alipay_root_cert'] = '已上传';
        }

        return true;
    }

    /**
     * 支付宝支付配置信息
     * @param $data
     * @return bool|array
     * @author 青岛开店星信息技术有限公司
     */
    public function WFTPay(&$data)
    {
        if (empty($data['sub_mch_id'])) {
            return error('支付商户号 不能为空');
        }

        if (empty($data['api_key'])) {
            return error('支付秘钥 不能为空');
        }

        $this->setAttribute('api_key', $data['apikey']); //支付密钥(APIKEY)
        $this->setAttribute('sub_mch_id', $data['sub_mch_id']); //支付商户号(Mch_Id)
        $this->setAttribute('sign_type', $data['sign_type']); //签名类型0=MD5, 1=RSA2

        return true;
    }

}
