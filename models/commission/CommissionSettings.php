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

namespace shopstar\models\commission;


use shopstar\bases\model\BaseSettings;

/**
 * This is the model class for table "{{%commission_settings}}".
 *
 * @property string $key 设置名
 * @property string $value 设置值
 */
class CommissionSettings extends BaseSettings
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%commission_settings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value'], 'string'],
            [['key'], 'string', 'max' => 50],
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
        static::setCachePrefix('commission_');

        return parent::baseGet($key, $defaultValue, [
        ]);
    }

    /**
     * 保存设置
     * @param string $key
     * @param string $value
     * @param bool $mergeOriginalData
     * @return bool
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function set($key = '', $value = '', $mergeOriginalData = true)
    {
        // 设置缓存前缀
        static::setCachePrefix('commission_');

        return parent::baseSet($key, $value, $mergeOriginalData, []);
    }

    /**
     * 删除设置项
     * @param string $key
     * @return array|mixed|void
     * @author likexin
     */
    public static function remove($key = '')
    {
        // 设置缓存前缀
        static::setCachePrefix('');

        return parent::baseRemove($key, []);
    }

    /**
     * 默认设置
     * @return array
     * @author likexin
     */
    public static function defaultSettings()
    {
        return [
            'set' => [ // 分销设置
                'commission_level' => 3, // 分销层级
                'self_buy' => 0, // 内购
                'banner' => '', // 申请页面顶部图片
                'become_condition' => 0, // 成为条件 0无条件  1商品  2金额  3数量  4申请
                'become_goods_ids' => '', // 购买商品
                'become_order_money' => 0, // 消费金额
                'become_order_count' => 0, // 订单数量
                'show_agreement' => 0, // 是否显示协议
                'become_order_status' => 0, // 统计方式  1 付款  2完成
                'is_audit' => 1, // 是否需要审核

                'write_info' => 0, // 完善资料
                'child_condition' => 1, // 成为下线条件 1 分享  2 付款
                'show_commission' => 1, // 显示佣金
                'show_commission_level_type' => 1, // 显示佣金层级
                'show_commission_level' => 1, // 显示分销商等级
            ],
            'settlement' => [ // 结算设置
                'calculate_type' => '2', // 计算方式 1折扣价  2实际
                'withdraw_limit' => 1, // 最低提现额度
                'withdraw_fee_type' => '1', // 提现手续费类型 1不扣除 2 自定义
                'withdraw_fee' => '', // 提现手续费百分比
                'free_fee_type' => '1', // 免手续费区间类型 1不免手续费 2 自定义区间
                'free_fee_start' => '', // 免手续费区间开始金额
                'free_fee_end' => '', // 免手续费区间结束金额
                'settlement_day_type' => '1', // 自定义结算天数 1订单完成后既可提现 2自定义结算天数
                'settlement_days' => '0', // 自定义结算天数

                'withdraw_audit' => '1', // 审核方式  1手动  2自动
                'auto_check_level' => '1', // 自动通过审核等级
                'auto_check_price' => '', // 自动通过审核金额

                'auto_pay' => '0', // 自动打款(暂时不用)
                'withdraw_type' => ["10"], // 提现方式  10商城余额  20微信  30支付宝
            ],

            /**
             * @var array 排行设置
             */
            'rank' => [
                'open' => 0,    // 排行榜开关 0:关闭 1:开启
                'commission_type' => 0, // 佣金排行类型 0:累计佣金 1:已提现佣金
                'show_total' => 10,     // 排行榜显示数量
            ],

            'other' => [ // 其他设置
                'become_agent' => '成为分销商',
                'agent_name' => '分销商',
                'head_agent' => '总店',

                'agent_center' => '分销中心',
                'agent_commission' => '分销佣金',
                'commission_order' => '分销订单',
                'withdraw_detail' => '提现明细',
                'my_down_line' => '我的下线',
                'level_description' => '等级说明',
                'commission_rank' => '佣金排名',
                'commission' => '佣金',
                'withdraw' => '提现',
                'commission_assessment' => '业绩考核',

                'can_withdraw_commission' => '可提现佣金',
                'count_commission' => '累计佣金',
                'wait_audit_commission' => '待审核佣金',
                'wait_pay_commission' => '待打款佣金',
                'wait_account_commission' => '待入账佣金',

                'level_name_1' => '一级',
                'level_name_2' => '二级',
                'level_name_3' => '三级',

                'agreement_title' => '协议名称',
                'agreement_content' => '协议内容',
            ],
            'notice' => [ // 通知设置
                'seller' => [ // 卖家通知
                    'apply' => [ // 申请分销商通知
                        'template' => [ // 模板消息设置 为了以后可能添加其他类型通知 这里区分开
                            'state' => '0', // 状态
                            'id' => '', // 模板id
                            'member_id' => [1], // 通知人id
                        ]
                    ],
                    'withdraw' => [ // 提现通知
                        'template' => [ // 模板消息设置
                            'state' => '0', // 状态
                            'id' => '', // 模板id
                            'member_id' => [], // 通知人id
                        ]
                    ],
                ],
                'buyer' => [ // 买家通知
                    'agent' => [ // 分销商通知
                        'become' => [ // 成为分销商通知
                            'template' => [ // 模板消息
                                'id' => '',
                                'state' => '0',
                            ]
                        ],
                        'add_child' => [ // 新增下级
                            'template' => [ // 模板消息
                                'id' => '',
                                'state' => '0',
                                'level' => '0', // 通知等级  1 一级 2 3
                            ]
                        ]

                    ],
                    'child' => [ // 下级通知
                        'pay' => [ // 下级付款通知
                            'template' => [ // 模板消息
                                'id' => '',
                                'state' => '0',
                                'level' => '0', // 通知等级  1 一级 2 3
                            ]
                        ],
                        'receive' => [ // 下级收货通知
                            'template' => [ // 模板消息
                                'id' => '',
                                'state' => '0',
                                'level' => '0', // 通知等级  1 一级 2 3
                            ]
                        ]
                    ],
                    'withdraw' => [ // 提现通知
                        'apply' => [ // 提现申请通知
                            'template' => [ // 模板消息
                                'id' => '',
                                'state' => '0',
                            ]
                        ],
                        'finish' => [ // 提现完成通知
                            'template' => [ // 模板消息
                                'id' => '',
                                'state' => '0',
                            ]
                        ]
                    ],
                    'commission' => [ // 佣金通知
                        'pay' => [ // 打款通知
                            'template' => [ // 模板消息
                                'id' => '',
                                'state' => '0',
                            ]
                        ],
                        'upgrade' => [ // 升级通知
                            'template' => [ // 模板消息
                                'id' => '',
                                'state' => '0',
                            ]
                        ]
                    ]
                ],
            ],

        ];
    }

}
