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

namespace shopstar\models\shop;

use shopstar\bases\model\BaseSettings;
use shopstar\components\storage\bases\StorageDriverConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\notice\MailerConstant;
use shopstar\models\core\attachment\CoreAttachmentModel;
use shopstar\models\core\CoreSettings;

/**
 * This is the model class for table "{{%shop_settings}}".
 *
 * @property string $key 设置名
 * @property string|null $value 设置值
 */
class ShopSettings extends BaseSettings
{
    /**
     * 公众号的类型 前端要求格式如此
     * @var array
     */
    public static $wechatTypeMap = [
        [
            'key' => 10,
            'value' => '未认证订阅号'
        ],
        [
            'key' => 20,
            'value' => '认证订阅号'
        ],
        [
            'key' => 30,
            'value' => '未认证服务号'
        ],
        [
            'key' => 40,
            'value' => '认证服务号'
        ],
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 50],
            [['key'], 'unique', 'targetAttribute' => ['key']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'key' => '设置名',
            'value' => '设置值',
        ];
    }

    /**
     * 读取设置
     * @param string $key
     * @param string $defaultValue
     * @return array|mixed|string
     * @author likexin
     */
    public static function get($key = '', $defaultValue = '')
    {
        // 设置缓存前缀
        static::setCachePrefix('shop_');
        return parent::baseGet($key, $defaultValue, []);
    }

    /**
     * 保存设置
     * @param string $key
     * @param string $value
     * @param bool $mergeOriginalData
     * @return bool
     * @author likexin
     */
    public static function set($key = '', $value = '', $mergeOriginalData = true)
    {
        // 设置缓存前缀
        static::setCachePrefix('shop_');
        return parent::baseSet($key, $value, $mergeOriginalData, []);
    }

    /**
     * 删除设置项
     * @param string $key
     * @return array|mixed
     * @author likexin
     */
    public static function remove($key = '')
    {
        static::setCachePrefix('shop_');
        // 设置缓存前缀
        return parent::baseRemove($key, []);
    }

    /**
     * 获取店铺支付方式
     * @param $clientType
     * @return array|mixed
     */
    public static function getOpenPayType($clientType)
    {
        if (empty($clientType)) {
            return error('客户端类型不能为空');
        }
        $settings = ShopSettings::get('sysset.payment.typeset')[$clientType];
        if (!empty($settings)) {
            foreach ($settings as &$setting) {
                if (!(isset($setting['enabled']) && $setting['enabled'] == 1)) {
                    unset($setting);
                }
            }
            unset($setting);
        }
        return $settings;
    }


    /**
     * 获取压缩规则和储存方式
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function getImageCompressionRule()
    {
        $storage = self::get('service_storage');

        // TODO likexin
        $result = [
            'storage_model' => $storage['storage_model'],
            'type' => CoreSettings::get('storage.type'),
        ];

        return $result;
    }

    /**
     * 默认设置
     * @return array
     * @author likexin
     */
    public static function defaultSettings()
    {
        return [
            /**
             * @var array 联系方式
             */
            'contact' => [
                'amap_key' => '', // 前端sdk调用高德key
                'contact' => '', //联系人
                'tel1' => '', //联系电话1
                'tel2' => '', //联系电话2
                'address' => [
                    'province' => '',    // 省份
                    'province_code' => '',    // 省份
                    'city' => '',             // 城市
                    'city_code' => '',             // 城市
                    'area' => '',           // 区域
                    'area_code' => '',           // 区域
                    'detail' => '',         // 详细地址 全部文字 例如: 山东省青岛市市北区龙城路31号
                    'lng' => '',             // 经度
                    'lat' => '',              // 纬度
                ],
            ],
            'goods_category' => [ //商品分类设置
                'level' => 0,
                'style' => 0,
                'adv_url' => '',
                'template_type' => 0, //模板类型 0默认模板 1快速购买模板
                'title' => '商品分类', // 分类页面title
            ],
            'member' => [
                'rank' => [ // 排行榜设置
                    'credit_state' => 1,
                    'credit_num' => 50,
                    'money_state' => 1,
                    'money_num' => 50,
                ],
                'level' => [ // 升级方式  1 订单完成后 2 付款后
                    'update_type' => "1"
                ]
            ],
            'channel' => [ //渠道开启状态
                'h5' => '0',
                'wechat' => '0',
                'wxapp' => '0',
                'byte_dance' => '0',
            ],
            'channel_setting' => [
                'wxapp' => [
                    'appid' => '',
                    'app_secret' => '',
                    'maintain' => '0',//是否维护
                    'maintain_explain' => '',//维护说明
                    'show_commission' => '1',//显示分销
                    'navigate_appid_list' => [],    // 跳转小程序列表
                ],
                'wechat' => [
                    'name' => '',       // 公众号名称
                    'type' => '',       // 公众号类型
                    'app_id' => '',
                    'secret' => '',
                    'logo' => '',
                    'qr_code' => '',    // 二维码
                    'bases' => [
                        'url' => '',              // url  必须以http://或https://开头，分别支持80端口和443端口
                        'token' => '',            // token 必须为英文或数字，长度为3-32字符
                        'encoding_aes_key' => '', // 消息加密密钥 由43位字符组成，可随机修改，字符范围为A-Z，a-z，0-9
                        'encryption_type' => 3    // 加密方式 1明文模式 2兼容模式 3安全模式
                    ]
                ],
                'registry_settings' => [
                    'coerce_auth' => 0,// 强制授权 0关闭 1开启
                    'coerce_auth_channel' => [ //强制授权渠道
                        'h5' => 0,
                        'wechat' => 0,
                        'byte_dance' => 0,
                    ],// 强制授权 0关闭 1开启
                    'h5_login_method' => 0,// h5默认登录方式 0账号密码 1短信验证码
                    'bind_method' => 1, // 1 手动绑定 2强制绑定
                    'bind_scene' => [ //触发绑定场景
                        'add_cart' => 0, //添加购物车
                        'buy' => 0, //立即购买
                        'share' => 0, //分享
                        'submit_form' => 0, //提交表单
                        'get_coupon' => 0, //领取优惠券
                    ] //绑定场景
                ],
                'byte_dance' => [ // 字节跳动小程序
                    'appid' => '',
                    'app_secret' => '',
                    'maintain' => '0', // 是否维护
                    'maintain_explain' => '', // 维护说明
                    'show_commission' => '1', // 显示分销
                ]
            ],
            'sale' => [ // 营销设置
                'basic' => [ // 基础设置
                    'enough' => [ // 满额立减设置
                        'state' => "0",
                        'set' => [],
                    ],
                    'enough_free' => [ // 满额包邮
                        'state' => "0",
                        'is_participate' => "0" // 以下商品是否参与包邮,默认为'0'，'2'不限制 0不参与，1只参与
                    ],
                    'deduct' => [ // 抵扣设置
                        'credit_state' => "0",
                        'basic_credit_num' => "10",
                        'credit_num' => "1",
                        'balance_state' => "0",
                    ]
                ],
                'coupon' => [ // 优惠券设置
                    'set' => [
                        'use_content' => '',
                    ],
                    'send' => [ // 手动发券配置
                        'service' => [
                            'title' => '',
                            'image' => '',
                            'description' => '',
                            'link' => ''
                        ],
                        'template' => [
                            'id' => '',
                            'title' => '',
                            'title_color' => '',
                            'keyword1' => '',
                            'keyword1_color' => '',
                            'keyword2' => '',
                            'keyword2_color' => '',
                            'footer' => '',
                            'footer_color' => '',
                            'link' => '',

                        ]
                    ]
                ]
            ],
            'sysset' => [ // 系统设置
                'mall' => [ // 商城设置
                    'basic' => [ // 基础设置
                        'mall_status' => '0',
                        'name' => '商城名称',
                        'agreement_name' => '',
                        'agreement_content' => '',
                        'icp_code' => '', // ICP备案号
                    ],
                    'share' => [ // 分享设置
                        'title_type' => '1',
                        'logo_type' => '1',
                        'link_type' => '1',
                        'share_description_type' => '1'
                    ],
                ],
                'payment' => [ // 支付设置
                    'payset' => [ // 打款设置
                        'alipay' => [
                            'enable' => '0',
                            'id' => ''
                        ],
                        'wechat' => [
                            'enable' => '0',
                            'wechat' => [
                                'id' => ''
                            ],
                            'wxapp' => [
                                'id' => ''
                            ]

                        ],

                        'pay_type_commission' => '1',
                        'pay_type_withdraw' => '1',
                        'pay_red_pack_money' => '1',
                    ],
                    'typeset' => [ // 支付方式设置
                        'wechat' => [ // 公众号支付设置
                            'wechat' => [ // 微信支付设置
                                'enable' => '0',
                                'id' => ''
                            ],
                            'alipay' => [ // 支付宝支付设置
                                'enable' => '0',
                                'id' => ''
                            ],
                            'balance' => [ // 余额支付设置
                                'enable' => '0'
                            ],
                            'delivery' => [ // 货到付款设置
                                'enable' => '0'
                            ],
                        ],
                        'wxapp' => [ // 小程序支付设置
                            'wechat' => [ // 微信支付设置
                                'enable' => '0',
                                'id' => ''
                            ],
                            'balance' => [ // 余额支付设置
                                'enable' => '0'
                            ],
                            'delivery' => [ // 货到付款设置
                                'enable' => '0'
                            ],
                        ],
                        'h5' => [
                            'alipay' => [ // 支付宝支付设置
                                'enable' => '0',
                                'id' => ''
                            ],
                            'balance' => [ // 余额支付设置
                                'enable' => '0'
                            ],
                            'delivery' => [ // 货到付款设置
                                'enable' => '0'
                            ],
                        ],
                        'byte_dance' => [
                            'byte_dance' => [ // 为了格式统一 特加一层
                                'enable' => '0',
                                'merchant_id' => '',
                                'token' => '',
                                'salt' => '',
                            ],
                        ],
                    ]
                ],
                'express' => [ // 地址物流设置
                    'set' => [ // 物流设置
                        'express_type' => '0' // 类型
                    ],
                    'address' => [
                        'deny_area' => '',
                        'wechat_address' => '0',
                        'delivery_type' => '0', // 配送区域类型 0为不配送区域 1为只配送区域
                        'delivery_area' => '',// 只配送区域
                    ]
                ],

                'credit' => [ // 积分余额设置
                    'credit_text' => '积分',
                    'give_credit_status' => "0",
                    'balance_text' => '余额',
                    'credit_limit_type' => '1',
                    'recharge_state' => '0',
                    'withdraw_state' => '0',
                    'recharge_money_low' => '0.1',
                    'withdraw_limit_type' => '1',
                    'withdraw_fee_type' => '1',
                    'free_fee_type' => '1'
                ],
                'trade' => [ // 交易设置
                    'close_type' => '2',
                    'close_time' => '60',
                    'close_notice_type' => '1',
                    'auto_receive' => '2',
                    'auto_receive_days' => '7',
                    'strengthen_state' => '0',
                    'invoice' => '0',
                    'order_comment' => '0',
                    'show_comment' => '0',
                    'comment_audit' => '0',
                    'comment_desensitization' => '1',
                    'auto_comment' => '0',
                    'auto_comment_day' => '',
                    'auto_comment_content' => '此用户没有填写评价',
                ],
                'refund' => [
                    'apply_type' => '2',
                    'apply_days' => '7',
                    'single_refund_enable' => '1',
                    'platform_join' => '0', //多商戶 是否允許平台介入
                    'timeout_cancel_refund' => '0',//是否开启超时取消维权 1开启
                    'timeout_cancel_refund_days' => '1'//取消售后维权时间,
                ]
            ],
            'plugin_express_helper' => [//快递助手
                'express' => [ //面单
                    'kdn' => [
                        'appid' => '',
                        'key' => '',
                    ], //快递鸟
                    'cainiao' => [ //菜鸟

                    ]
                ]
            ],
            //插件设置
            'plugin_notice' => [// 通知设置
                'send' => [
                    NoticeTypeConstant::VERIFY_CODE_USER_REG => [ //用户注册
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::VERIFY_CODE_RETRIEVE_PWD => [ //用户注册
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::VERIFY_CODE_CHANGE_BIND => [ //修改绑定手机号
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::VERIFY_CODE_LOGIN_CODE => [ //用户注册
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::VERIFY_CODE_BIND => [ //用户注册
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_ORDER_PAY => [ //买家支付通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [    // 公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]

                    ],
                    NoticeTypeConstant::BUYER_ORDER_SEND => [ //卖家发货通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'member_id' => []
                        ]
                    ],
                    NoticeTypeConstant::SELLER_ORDER_PAY => [ //卖家订单付款通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'member_id' => []
                        ]
                    ],
                    NoticeTypeConstant::SELLER_ORDER_RECEIVE => [ //卖家订单收货通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'member_id' => []
                        ]
                    ],
                    NoticeTypeConstant::SELLER_STOCK_WARNING => [ //卖家库存预警通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'member_id' => []
                        ]
                    ],
                    NoticeTypeConstant::SELLER_GOODS_PAY => [ //卖家商品付款通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'member_id' => []
                        ]
                    ],
                    NoticeTypeConstant::SELLER_ORDER_REFUND => [ //卖家订单维权通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'member_id' => []
                        ]
                    ],
                    NoticeTypeConstant::BUYER_COUPON_SEND => [ //买家优惠券发放通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_ORDER_CANCEL => [ //买家订单取消通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_ORDER_CANCEL_AND_REFUND => [ //卖家订单手动退款通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'member_id' => []
                        ]
                    ],
                    NoticeTypeConstant::BUYER_ORDER_RECEIVE => [ //买家订单收货通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_ORDER_STATUS => [ //买家订单状态更新通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_REFUND_MONEY => [ //买家退款成功通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_REFUND_EXCHANGE => [ //买家换货成功通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_REFUND_SEND => [ //买家退款发货通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_REFUND_REJECT => [ //买家退款申请拒绝通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_PAY_RECHARGE => [ //买家充值成功通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_PAY_WITHDRAW => [ //买家提现成功通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_PAY_RECHARGE_ADMIN => [ //买家后台充值通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::BUYER_PAY_CREDIT => [ //买家积分变动
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ],
                    ],
                    NoticeTypeConstant::BUYER_MEMBER_UPDATE => [ //买家退款发货通知
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_AGENT_BECOME => [ // 买家成为分销商通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::COMMISSION_SELLER_APPLY => [ // 买家成为分销商通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::COMMISSION_SELLER_ADD_COMMISSION_ORDER => [ // 卖家新增分销订单通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'member_id' => []
                        ]
                    ],
                    NoticeTypeConstant::COMMISSION_SELLER_WITHDRAW => [ // 卖家申请提现通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'member_id' => []
                        ]
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD => [ // 买家新增下级通知
                        'wechat' => [
                            'status' => '0',
                            'commission_level' => '1',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'commission_level' => '1',
                        ]
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_CHILD_PAY => [ // 买家下级支付通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'commission_level' => '1',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'commission_level' => '1',
                        ]
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_WITHDRAW_APPLY => [ // 买家申请提现失败通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_WITHDRAW_APPLY_FAIL => [ // 买家申请提现失败通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_COMMISSION_PAY => [ // 买家佣金打款通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::COMMISSION_BUYER_COMMISSION_UPGRADE => [ // 买家分销等级升级通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    'commission_buyer_agent_add_child_line' => [ // 买家新增下线通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'commission_level' => '1',
                            'is_default' => 1,
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'commission_level' => '1',
                        ]
                    ],
                    NoticeTypeConstant::PRESELL_BUYER_PAY_FINAL => [
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []

                        ],

                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::DIYFORM_SUBMIT_SEND => [ //表单受理通知
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'subscribe' => [//公众号一次性订阅消息
                            'status' => '0',
                            'template_id' => '',
                        ],
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0,
                            'member_id' => []
                        ]
                    ],
                    NoticeTypeConstant::GROUPS_SUCCESS => [ //拼团成功通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []

                        ],
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::GROUPS_JOIN => [ //参与拼团通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []

                        ],
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::GROUPS_DEFEATED => [ //拼团失败通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []

                        ],
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::GROUPS_REBATE_SUCCESS => [ //拼团成功通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []

                        ],
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::GROUPS_REBATE_JOIN => [ //参与拼团通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []

                        ],
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::GROUPS_REBATE_DEFEATED => [ //拼团失败通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []

                        ],
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::VERIFY_SUCCESS => [ // 买家核销成功通知
                        'wechat' => [
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1,
                            'member_id' => []

                        ],
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'sms' => [ //短信
                            'status' => 0,
                            'template_id' => 0
                        ]
                    ],
                    NoticeTypeConstant::VERIFY_QRCODE_BIND_SUCCESS => [ // 扫码核销员成功短信通知
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => []
                        ],
                        'wxapp' => [
                            'status' => '0', //是否开启1是0否
                            'template_id' => ''
                        ],
                        'sms' => [ //短信
                            'status' => '0',
                            'template_id' => 0
                        ]
                    ],

                    // 人信云消息通知
                    NoticeTypeConstant::RXY_ADVISORY_REMINDER => [ // 人信云咨询提醒通知
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                            'member_id' => [],
                            'is_send_message' => 0, // 是否开启 未读消息数
                            'message_num' => 0,
                            'line_num' => 0,
                            'is_send_line' => 0, // 是否开启 人信云消息通知
                        ],
                    ],
                    NoticeTypeConstant::RXY_ADVISORY_REMINDER_REPLY => [ // 人信云咨询回复提醒通知
                        'wechat' => [ //公众号
                            'status' => '0',
                            'template_id' => '',
                            'is_default' => 1, //是否默认
                        ],
                    ],
                ],
                'sms_signature_id' => 0, //启用签名id
            ],
            'activity' => [ // 活动设置
                'seckill' => [ //秒杀设置
                    'close_type' => '1',
                    'close_time' => '15',
                ],
                'groups' => [ //拼团
                    'team_list' => 0,//团列表
                    'auto_close' => [ //自动关闭
                        'open' => 0,//是否自动关闭
                        'close_time' => 0 //关闭分钟
                    ],
                ],

            ],
            /**
             * @var array 配送方式
             */
            'dispatch' => [
                'sort' => '', // 配送方式排序 默认为空
                'express' => [                      // 快递
                    'enable' => 0,
                ],
                'intracity' => [                      // 同城配送
                    'enable' => 0,
                    'merchant' => 0,                  // 商家(商户)配送
                    'delivery_time' => 0, // 送达时间
                    'delivery_time_settings' => [
                        'time_span' => '60', // 时间段划分单位(分), 默认60 范围 10-1440
                        'start' => '00:00', // 开始时间 默认00:00
                        'end' => '23:59', // 结束时间 默认00:00
                        //具体划分的时间
                        'span_detail' => '00:00-01:00,01:00-02:00,02:00-03:00,03:00-04:00,04:00-05:00,05:00-06:00,06:00-07:00,07:00-08:00,08:00-09:00,09:00-10:00,10:00-11:00,11:00-12:00,12:00-13:00,13:00-14:00,14:00-15:00,15:00-16:00,16:00-17:00,17:00-18:00,18:00-19:00,19:00-20:00,20:00-21:00,21:00-22:00,22:00-23:00',
                    ],
                    'dada' => [           // 达达配送
                        'enable' => 0,          // 启用
                        'app_key' => '',
                        'app_secret' => '',
                        'source_id' => '',         // 商户编号
                        'shop_no' => '',         // 门店编号
                        'city_code' => '',         // 城市代码
                    ],
                    'amap_key' => '',                   // 高德接口key（获取行政区域）
                    'delivery_area' => 0,               // 配送区域 0: 按不同区域 1: 按不同距离 2: 按行政区域
                    'division_way' => 0,                // 划分方式 0: 半径 1: 自定义  delivery_area=0or1时生效
                    'dispatch_area' => [                // 配送区域
                        [
                            'initial_price' => 0,                // 起送金额
                            'dispatch_price' => 10,              // 配送费
                            'center_lng' => '',                  // 地图中心点经度
                            'center_lat' => '',                  // 地图中心点纬度
                            'radius' => 2000,                    // 配送半径(m)
                            'is_free' => 1,                      // 是否免配送费 0否1是
                            'free_price' => 10,                  // 免配送费满足金额
                            'location' => [],                    // 坐标信息 自定义配送区域生效 顺时针
                        ]
                    ],
                    'dispatch_rule' => [                       // 配送规则
                        'initial_distance' => 0,               // 初始配送距离(km)
                        'initial_dispatch_price' => 0,         // 初始配送费用
                        'increase_distance' => 1,              // 每增加配送距离(km)
                        'increase_distance_price' => 0,        // 每增加距离增加的金额(km)
                        'over_distance' => 0,                  // 超出距离(km)
                        'over_distance_fix_price' => 0,        // 超出距离(km)固定价格

                        'initial_weight' => 0,                 // 初始配送重量不收费(kg)
                        'increase_weight' => 0,                // 每增加配送重量(kg)
                        'increase_weight_price' => 0,          // 每增加重量增加的金额(kg)
                    ],
                    'dispatch_barrio' => [],                   // 可配送的行政区域，根据店铺地址获取行政区域 delivery_area=2时生效
                    'barrio_rule' => [                         // 行政区域配送规则 delivery_area=2时生效
                        'initial_price' => 10,                 // 起送费（低于配送价时，买家无法下单）
                        'dispatch_price' => 10,                // 配送费
                    ],
                    'over_scope' => 0                     // 超出范围 0不使用快递1使用快递

//                    'shop_address' => [],                 // 店铺地址
                ],
            ],
            'diypage' => [
                'login_auth' => [ //前端热区缓存 存什么取什么无需默认
                    'type' => 0, //热区类型
                    'style' => 1, //默认弹窗图片
                    'diy_data' => [],
                    'thumb' => '',//自定义图
                ],
                'theme_color' => 'default', // 默认 现有 black green blue  pink purple yellow
            ],
            // 核销
            'verify' => [
                'base_setting' => [
                    'verify_is_open' => '0',                       // 商品核销是否开启 1开启
                    'verify_buyers_independent' => '0',            // 买家自主核销 1开启
                    'verify_compulsive_buyers' => '0',             // 强制买家选择核销点 1开启
                    'verify_order_remind' => '0',                  // 核销订单提醒 1开启
                    'verify_order_remind_content' => '',         // 核销订单提醒内容
                    'delivery_time' => 0, // 送达时间
                    'auto_close_type' => 2, //核销自动关闭类型 1到期自动关闭订单并退款 2到期东单自动完成不退款 3永不关闭订单且不退款
                    'auto_close_day' => 30, //核销自动关闭天数
                    'verify_perm' => 1, //核销权限 0仅核销员自己的记录 1全部核销记录
                ]
            ],
            // 客服
            'customer_service' => [
                'base_setting' => [
                    'h5' => '0',                                 // h5客服 1开启
                    'h5_url' => '',                              // h5url地址,h5客服开启时，需要必填此参数
                    'wxapp' => '0',                            // 小程序 1开启
                    'wxapp_sign' => ''                             // 小程序签名地址
                ],
                'params' => [                                     // 参数
                    'h5' => [
                        'c1' => 'id',
                        'c2' => 'nickname',
                        'c3' => 'level_name',
                        'c4' => 'commission_level_name',
                        'c5' => 'success_price',
                    ],
                    'wxapp' => [
                        'c1' => 'id',
                        'c2' => 'nickname',
                        'c3' => 'level_name',
                        'c4' => 'commission_level_name',
                        'c5' => 'success_price',
                    ]
                ]
            ],

            // 插件-视频号设置
            'plugin_video_account' => [
                'enabled' => 0, // 是否启用 0:否 1:是
                'template_style' => 1,  // 模板 0/1/2/3

                'custom_config' => 0,   // 自定义公众号配置参数 0:否 1:是
                'wechat_app_id' => '',   // 自定义公众号配置参数 0:否 1:是
                'wechat_app_secret' => '',   // 自定义公众号配置参数 0:否 1:是

                'broadcast_member_type' => 0,   // 群发会员类型 0:会员ID 1: openid
                'broadcast_member_id' => [],   // 群发会员ID
                'share_title' => '',   // 分享标题

                'member_level_limit' => 0,   // 会员限制 0:会员等级 1:分销等级
                'member_level_id' => [],   // 会员等级限制ID
                'goods_limit' => 0,   // 商品限制 0:全部 1:自定义
                'goods_limit_id' => [],   // 商品限制ID goods_limit==1时生效
            ],

            // 存储设置
            'service_storage' => [
                // 存储方式  10为托管存储 20为独立存储
                'storage_model' => CoreAttachmentModel::HOSTING,
                // 当前启用类型
                'type' => StorageDriverConstant::DRIVE_LOCAL,
                // 本地存储配置
                StorageDriverConstant::DRIVE_LOCAL => [],
                // FTP存储设置
                StorageDriverConstant::DRIVE_FTP => [
                    'url' => '', // 链接
                    'host' => '', // 端口
                    'port' => '', // 端口
                    'username' => '',  // 用户名
                    'password' => '',     // 密码,
                    'ssl' => 0, // 开启ssl
                    'passive_mode' => 0, // 开启被动模式
                    'path' => '', // ftp相对路径
                    'timeout' => 0,// 上传超时
                    'scheme' => 'http://'
                ],
                // 七牛存储设置
                StorageDriverConstant::DRIVE_QINIU => [
                    'url' => '', // 链接
                    'access_key' => '', //AccessKey
                    'secret_key' => '',  // SecretKey
                    'bucket' => '',     // bucket,
                    'scheme' => 'http://',
                    'image_compression' => 0, // 图片压缩是否开启 0不开启
                    'image_compression_rule' => [  //默认图片压缩规则
                        'is_default' => 1,        //是否默认规则 1默认 0自定义
                        'rule' => 'imageMogr2/auto-orient/thumbnail/750x/format/jpg/blur/1x0/quality/75|imageslim'
                    ],
                ],
                // 阿里云OSS存储设置
                StorageDriverConstant::DRIVE_OSS => [
                    'url' => '', // 链接
                    'access_key' => '', // AccessKey
                    'secret_key' => '',  // SecretKey
                    'bucket' => '',     // bucket @形式存储  填写accesskey和secretkey之后会出现选择
                    'scheme' => 'http://',
                    'image_compression' => 0, // 图片压缩是否开启 0不开启
                    'image_compression_rule' => [  //默认图片压缩规则
                        'is_default' => 1,        //是否默认规则 1默认 0自定义
                        'rule' => 'x-oss-process=image/auto-orient,1/resize,m_lfit,w_750/quality,q_60'
                    ],
                ],
                // 腾讯云COS存储
                StorageDriverConstant::DRIVE_COS => [
                    'url' => '', // 链接
                    'app_id' => '', // AppId
                    'secret_id' => '', // AccessKey
                    'secret_key' => '',  // SecretKey
                    'bucket' => '',     // bucket 填写secretId和secretKey之后会出现选择
                    'region' => '',     // region 填写secretId和secretKey之后会出现选择
                    'scheme' => 'http://'
                ]
            ],
            // 虚拟卡密
            'virtual_setting' => [
                // 关闭类型 0默认系统 1自定义
                'close_type' => 0,
                // 未付款订单关闭时间
                'close_time' => '',
            ],
            // 邮箱设置
            'mailer' => [
                // 0关闭 1开启
                'status' => 0,
                // 默认qq邮箱
                'type' => MailerConstant::MAILER_TYPE_QQ,
                MailerConstant::MAILER_TYPE_QQ => [     // qq邮箱
                    'host' => 'smtp.qq.com',              // 服务器
                    'port' => '465',                      // 端口
                    'username' => '',                     // 发件人邮件地址
                    'shop_name' => '',                    // 发件平台名称
                    'password' => '',                     // smtp验证码
                    'mailer_title' => '',
                    'ssl' => 1,                          // 是否开启安全链接 0关闭 1开启
                    'test_address' => '',                // 测试接收邮件地址
                ],
                MailerConstant::MAILER_TYPE_163 => [
                    'host' => 'smtp.163.com',             // 服务器
                    'port' => '465',                      // 端口
                    'username' => '',                     // 发件人邮件地址
                    'shop_name' => '',                    // 发件平台名称
                    'password' => '',                     // smtp验证码
                    'mailer_title' => '',
                    'ssl' => 1,                          // 是否开启安全链接 0关闭 1开启
                    'test_address' => '',                // 测试接收邮件地址
                ],
//                MailerConstant::MAILER_TYPE_ALIYUN => [
//                    'host'=>'smtpdm.aliyun.com',        // 服务器
//                    'port'=>'465',                      // 端口
//                    'username'=>'',                      // 发件人邮件地址
//                    'shop_name'=>'',                    // 发件平台名称
//                    'password'=>'',                     // smtp验证码
//                    'mailer_title'=>'',
//                    'ssl'=> 0,                          // 是否开启安全链接 0关闭 1开启
//                    'test_address'=> '',                // 测试接收邮件地址
//                ],
                MailerConstant::MAILER_TYPE_CUSTOMIZE => [  // 自定义邮箱
                    'host' => '',                         // 服务器
                    'port' => '',                         // 端口
                    'username' => '',                     // 发件人邮件地址
                    'shop_name' => '',                    // 发件平台名称
                    'password' => '',                     // smtp验证码
                    'mailer_title' => '',
                    'ssl' => 1,                          // 是否开启安全链接 0关闭 1开启
                    'test_address' => '',                // 测试接收邮件地址
                ],
            ],

            'commentHelper' => [ // 评价助手设置
                'choice_status' => 1, // 精选
                'comment_reward_status' => 1, // 评价奖励
            ],
            'plugin_gift_card' => [ // 礼品卡设置
                'card_center_title' => '礼品卡中心', // 礼品卡中心页面标题
                'pay_type' => '2,20,30', // 支付方式
                'card_title' => '礼品卡', // 礼品卡名称自定义
                'card_center_banner' => '', // banner 设置

                'active_center_title' => '激活中心', // 激活中心标题
                'active_center_banner' => '', // 激活中心标题
                'active_prevent_status' => '0', // 防刷是否开启
                'active_prevent_minute' => '0', // 几分钟内
                'active_prevent_times' => '0', // 连续输错
                'active_prevent_freeze_hour' => '0', // 冻结小时
                'active_limit_status' => '0', // 每天限制激活
                'active_limit_num' => '0', // 每天激活数量
                'active_note' => '', // 激活说明
            ],
            // 店铺笔记设置
            'plugin_article' => [
                'title' => '专题文章',// 专题页面自定义title
                'template_type' => '1',// 专题页面模板 1:小图模式 2: 瀑布流
                'banner' => [],// 专题页面banner, 数组格式, 0-5个图片
                'reward_time_limit' => '15',// 阅读n秒后, 进行奖励发放 10-60
            ],
            // 状态20为尊享会员专享
            'copyright' => [                          // 版权信息
                'mobile' => [
                    'open' => '2',                        // 0关闭 1开启 2 系统设置
                    'style_model' => '1',                 // 选择的LOGO版权信息模块 1 2 3
                    'style1' => [
                        'message_switch' => '10',         // 10纯文字 20富文本
                        'message' => '',                  // 纯文字
                        'message_vip' => '',              // 富文本
                    ],
                    'style2' => [
                        'logo_switch' => '10',            // 10普通logo 20自定义
                        'logo' => '',                     // logo
                        'message_switch' => '10',         // 10纯文字 20富文本
                        'message' => '',                  // 纯文字
                        'message_vip' => '',              // 富文本
                    ],
                    'style3' => [
                        'logo_switch' => '10',            // 10普通logo 20自定义
                        'logo' => '',                     // logo
                        'message_switch' => '10',         // 10纯文字 20富文本
                        'message' => '',                  // 纯文字
                        'message_vip' => '',              // 富文本
                    ],
                    'h5' => [
                        'url_type' => 0, // 跳转方式 0不支持  1 自定义 2 默认联系人页面
                        'url' => '',                      // 版权链接
                        'url_default' => [
                            [
                                'id' => 0,// 前端排序用
                                'open' => 0, // 0关闭 1开启
                                'name' => '',
                                'content' => '',// 内容
                            ],
                            [
                                'id' => 0,// 前端排序用
                                'open' => 0, // 0关闭 1开启
                                'name' => '',
                                'content' => '',// 内容
                            ],
                            [
                                'id' => 0,// 前端排序用
                                'open' => 0, // 0关闭 1开启
                                'name' => '',
                                'content' => '',// 内容
                            ],
                        ],
                    ],
                    'wxapp' => [
                        'url_type' => 0, // 小程序跳转方式 0不支持  1 自定义 2 默认联系人页面
                        'url' => '', // 版权链接
                        'url_default' => [
                            [
                                'id' => 0,// 前端排序用
                                'open' => 0, // 0关闭 1开启
                                'name' => '',
                                'content' => '',// 内容
                            ],
                            [
                                'id' => 0,// 前端排序用
                                'open' => 0, // 0关闭 1开启
                                'name' => '',
                                'content' => '',// 内容
                            ],
                            [
                                'id' => 0,// 前端排序用
                                'open' => 0, // 0关闭 1开启
                                'name' => '',
                                'content' => '',// 内容
                            ],
                        ],
                    ]
                ],
                'pc' => [
                    'copyright_info' => '',       //商家端版本信息
                    'copyright_open' => '2',       //商家端版本信息开关 2 系统设置  1开启  0关闭
                ], // 独立版有
            ],
            'plugin_performance_award' => [ // 个人业绩奖
                'auto_send' => '0',
                'auto_send_limit' => '0.01',// 默认值最小为0.01
            ],

            // 自定义交易组件
            'wxTransactionComponent' => self::defaultWxTransactionComponentSettings(),

            // 一键发圈
            'material' => self::defaultMaterialSettings(),

            // 积分商城
            'credit_shop' => self::defaultCreditShopSettings(),
        ];
    }

    /**
     * 视频号交易组件默认数据
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private static function defaultWxTransactionComponentSettings(): array
    {
        return [
            'live' => [
                'video_number_id' => '', // 视频号id
            ],
            'dynamic' => [
                'video_id' => '', // 视频id
                'video_number_id' => '', // 视频号id
            ],
            'bases' => [ // 配置参数
                'url' => '',
                'token' => '',
                'encoding_aes_key' => '',
            ],
            'development' => [ // 定向设置
                'member_id' => 0,
            ],
        ];
    }

    /**
     * 一键发圈默认值
     * @return string[]
     * @author 青岛开店星信息技术有限公司
     */
    private static function defaultMaterialSettings(): array
    {
        return [
            'status' => '0', // 0 关闭 1 开启
        ];
    }

    /**
     * 积分商城默认值
     * @return string[]
     * @author 青岛开店星信息技术有限公司
     */
    private static function defaultCreditShopSettings(): array
    {
        return [
            'status' => '0',
            'refund_type' => '0',
            'finish_order_refund_type' => '0',
            'finish_order_refund_days' => '0',
            'refund_rule' => '0'
        ];
    }
}
