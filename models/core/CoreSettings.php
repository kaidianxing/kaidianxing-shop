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

namespace shopstar\models\core;

use shopstar\bases\model\BaseSettings;
use shopstar\components\storage\bases\StorageDriverConstant;

/**
 * This is the model class for table "es_core_settings".
 *
 * @property string $key 键
 * @property string $value 值
 */
class CoreSettings extends BaseSettings
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_settings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key', 'value'], 'required'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 50],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'key' => '键',
            'value' => '值',
        ];
    }

    /**
     * 复写获取设置
     * @param string $key
     * @param null $defaultValue
     * @param array $dbWhere
     * @return null
     * @author likexin
     */
    public static function get(string $key, $defaultValue = null, array $dbWhere = [])
    {
        // 赋值缓存前缀
        static::setCachePrefix('core_');

        return parent::baseGet($key, $defaultValue, $dbWhere);
    }

    /**
     * 复写保存设置
     * @param string $key
     * @param null $value
     * @param bool $mergeOriginalData
     * @param array $dbAttributes
     * @return bool
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function set(string $key, $value = null, bool $mergeOriginalData = true, array $dbAttributes = [])
    {
        // 赋值缓存前缀
        static::setCachePrefix('core_');

        return parent::baseSet($key, $value, $mergeOriginalData, $dbAttributes);
    }

    /**
     * 删除设置项
     * @param string $key
     * @param array $dbAttributes
     * @return bool|mixed
     * @author likexin
     */
    public static function remove(string $key = '', array $dbAttributes = [])
    {
        // 赋值缓存前缀
        static::setCachePrefix('core_');

        return parent::baseRemove($key, $dbAttributes);
    }

    /**
     * 默认设置
     * @return array
     * @author likexin
     */
    public static function defaultSettings(): array
    {
        return [

            /**
             * @var array 站点设置
             */
            'site' => [
                'name' => '', // 店铺助手的
                'logo' => '', // b端小程序用
                'status' => 1, // 站点状态 默认开启

                'pc_name' => '', // pc的 名称
                'pc_logo' => '', // pc logo
                'is_save_pc' => '0', // 是否保存过pc

                // 站点登录配图
                'login_logo_type' => 0,     //商家端登录logo 0 默认 1自定义
                'login_logo_img' => '',     //商家端登录logo 1自定义图片url

                // 后面看
                'copyright_info' => '',       //商家端版本信息
                'copyright_open' => '1',       //商家端版本信息开关
                'close_redirect_url' => '',  //商家端关闭后重定向url
            ],

            /**
             * @var array 基础设置
             */
            'basic' => [
                'mobile_loading' => [
                    'type' => 0, // 类型 0:骨架屏 1:系统loading
                    'style' => 0, // 系统loading样式
                    'color' => '#FF3C29', // loading颜色
                ],
                // 状态20为尊享会员专享
                'copyright' => [                          // 版权信息
                    'open' => '0',                        // 0关闭 1开启
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
                        'url_wxapp' => '', // 版权链接
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
                // 自定义字段 -- 商户端首页显示
                'custom_fields' => [
                    [
                        'id' => 0,// 前端排序用
                        'open' => 0, // 0关闭 1开启
                        'name' => '',
                        'jump_url' => '',// 调整链接
                        'scheme' => 'https://', // 链接前缀
                    ],
                    [
                        'id' => 1,
                        'open' => 0,
                        'name' => '',
                        'jump_url' => '',
                        'scheme' => 'https://', // 链接前缀
                    ],
                    [
                        'id' => 2,
                        'open' => 0,
                        'name' => '',
                        'jump_url' => '',
                        'scheme' => 'https://', // 链接前缀
                    ],
                ],
            ],

            'admin_basic' => [         //管理端基础设置
                'type' => 0,          //类型 0系统默认 1自定义
                'name' => '',          //管理端名称
                'logo' => '',           //管理端logo
                'login_logo_type' => 0, //管理端登录logo 0 默认 1自定义
                'login_logo_img' => '', //管理端登录logo 1自定义图片url
                'copyright_info' => '',   //管理端版本信息
            ],

            //管理端短信模板
            'admin_sms_template' => [
                'register' => [  //注册
                    'signature' => '',
                    'template_id' => '',
                    'content' => '',
                    'data' => [],
                ],
                'forget' => [ //找回
                    'signature' => '',
                    'template_id' => '',
                    'content' => '',
                    'data' => [],
                ]
            ],

            //用户设置
            'user_setting' => [
                //注册设置
                'register' => [
                    'way' => 0, //注册途径 0关闭 1开启
                    'audit' => 0, //注册审核 0关闭 1开启
                    'method' => 0, //注册方式 0手机号注册
                ],

                //注册资料
                'register_info' => [
                    [
                        'is_default' => 1,//是否是系统默认1是0否
                        'enabled' => 1,//是否启用 1是0否
                        'required' => 1,//是否必填 1是0否
                        'name' => '联系人姓名',//姓名
                        'desc' => '联系人姓名',//姓名
                        'key' => 'contact_name'
                    ],
                ],

                //注册协议
                'register_protocol' => [
                    'status' => 0,  //注册协议是否开启 0关闭 1开启
                    'title' => '',  //注册标题
                    'content' => '',//注册内容
                ],
            ],

            /**
             * @var array 安装临时信息
             */
            'install' => [],

            /**
             * @var array 授权信息
             */
            'auth' => [
                'site_id' => 0,
                'auth_code' => '',
            ],

            /**
             * @var array 店铺设置
             */
            'shop' => [
                // 注册设置
                'register' => [
                    // 注册协议
                    'agreement' => [
                        'enabled' => 0,
                        'title' => '',
                        'content' => '',
                    ],
                    'audit' => 1,//是否开启店铺审核 1是0否
                    'register_info' => [
//                        [
//                            'is_default' => 1,//是否是系统默认1是0否
//                            'enabled' => 1,//是否启用 1是0否
//                            'required' => 1,//是否必填 1是0否
//                            'name' => '详细地址',//名称
//                            'desc' => '详细地址',//简介
//                            'key' => 'address',//key
//                        ],
//                        [
//                            'is_default' => 1,//是否是系统默认1是0否
//                            'enabled' => 1,//是否启用 1是0否
//                            'required' => 1,//是否必填 1是0否
//                            'name' => '店铺名称',//名称
//                            'desc' => '店铺名称',//名称
//                            'key' => 'title',//key
//                        ],
//                        [
//                            'is_default' => 1,//是否是系统默认1是0否
//                            'enabled' => 1,//是否启用 1是0否
//                            'required' => 1,//是否必填 1是0否
//                            'name' => '店铺地址',//名称
//                            'desc' => '店铺地址',//名称
//                            'key' => 'area',//key
//                        ],
//                        [
//                            'is_default' => 1,//是否是系统默认1是0否
//                            'enabled' => 1,//是否启用 1是0否
//                            'required' => 1,//是否必填 1是0否
//                            'name' => '店铺行业',//名称
//                            'desc' => '店铺行业',//名称
//                            'key' => 'industry',//key
//                        ],
                    ],//店铺字段
                ],
            ],

            /**
             * @var array 附件设置
             */
            'attachment' => [
                'image' => [
                    'extensions' => ['gif', 'jpg', 'png'],
                    'max_size' => 10240,
                    'compress' => 0,
                    'compress_width' => 0,
                ],
                'video' => [
                    'extensions' => ['mp4'],
                    'max_size' => 20480,
                ],
                'audio' => [
                    'extensions' => ['mp3'],
                    'max_size' => 2048,
                ],
            ],

            /**
             * @var array 短信设置
             */
            'sms' => [
                'type' => 'aliyun', // 当前短信平台
                'aliyun' => [   // 阿里云短信配置
                    'access_key_secret' => '',
                    'access_key_id' => '',
                ],
                'juhe' => [ // 聚合短信配置
                    'app_key' => '',
                ],
                'plan' => [],   // 短信数量套餐设置
                'template_plan' => [],  // 短信模板套餐
            ],

            /**
             * @var array 支付设置
             */
            'payment' => [
                'alipay' => [
                    'enabled' => 0,
                    'app_id' => '',
                    'private_key' => '',
                    'alipay_cert_public_key_rsa2' => '',
                    'app_cert_public_key' => '',
                    'alipay_root_cert' => '',
                ],
                'wechat' => [
                    'enabled' => 0,
                    'app_id' => '',
                    'mch_id' => '',
                    'key' => '',
                ],
            ],

            /**
             * @var array 存储设置
             */
            'storage' => [
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
                ],
                // 阿里云OSS存储设置
                StorageDriverConstant::DRIVE_OSS => [
                    'url' => '', // 链接
                    'access_key' => '', // AccessKey
                    'secret_key' => '',  // SecretKey
                    'bucket' => '',     // bucket @形式存储  填写accesskey和secretkey之后会出现选择
                    'scheme' => 'http://',
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

            /**
             * @var array 计划任务
             */
            'crontab' => [
                'execute_type' => 0,    // 执行类型 0:被动 1:主动
                'api_key' => '',    // API_KEY 接口Key
                'params' => [   // 设置项
                    'statistics' => 30,   // 数据统计
                    'plugin_broadcast_room_sync_status' => 30,   // 小程序直播间状态
                    'plugin_broadcast_goods_sync_status' => 30,   // 小程序直播商品状态
                    'plugin_broadcast_statistics' => 30,   // 小程序直播数据统计
                    'plugin_presell_statistics' => 30,   // 商品预售数据统计
                    'plugin_seckill_statistics' => 30,   // 秒杀数据统计
                    'plugin_verify_statistics' => 30,   // 核销数据统计
                    'plugin_groups_statistics' => 30,   // 拼团数据统计
                    'plugin_groups_rebate_statistics' => 30,   // 拼团返利数据统计
                    'plugin_gift_card_statistics' => 30,   // 礼品卡数据统计
                    'plugin_full_reduce_statistics' => 30,   // 满减折数据统计
                    'plugin_credit_shop_statistics' => 30,   // 礼品卡数据统计
                ],
            ],

            /**
             * @var array 应用设置
             */
            'apps' => self::defaultAppSettings(),

            /**
             * @var array 码科改名
             */
            'dispatch' => [
                'make_diy' => [
                    'name' => '',              // 码科自定义名称
                    'logo' => '',              // 码科自定义logo
                ],
            ],
            'contacts' => [

            ]
        ];
    }

    /**
     * 默认应用设置
     * @return array
     * @author likexin
     */
    private static function defaultAppSettings(): array
    {
        return [
            /**
             * @var array 商品助手设置
             */
            'goods_helper' => [
                'api_key' => '',
                'plan' => [],
            ],

            /**
             * @var array 店铺助手设置
             */
            'assistant' => [
                'app_id' => '',
                'app_secret' => '',
            ],

        ];
    }

}
