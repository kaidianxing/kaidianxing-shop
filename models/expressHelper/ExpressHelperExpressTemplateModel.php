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

namespace shopstar\models\expressHelper;


use shopstar\bases\model\BaseActiveRecord;

/**
 *
 * 快递鸟错误信息
 * 成功编码 描述 100 成功
 * 错误编码 描述 101 缺少必要参数
 * 错误编码 描述 102 校验问题
 * 错误编码 描述 103 格式问题
 * 错误编码 描述 104 用户问题
 * 错误编码 描述 105 其他错误
 * 错误编码 描述 201 填写的寄件方信息有误，请核实确认
 * 错误编码 描述 202 填写的到件方信息有误，请核实确认
 * 错误编码 描述 203 填写的快递公司电子面单账号密码有误，请核实 确认
 * 错误编码 描述 204 账户余额不足请联系快递网点充值
 * 错误编码 描述 205 订单编号重复，请勿重复下单
 * 错误编码 描述 206 订单编号不能为空
 * 错误编码 描述 207 单号不足请联系快递网点充值
 * 错误编码 描述 208 快递类型不能为空
 * 错误编码 描述 209 运费支付方式有误
 * 错误编码 描述 210 增值服务名称有误
 * 错误编码 描述 211 月结编号不合法
 * 错误编码 描述 212 代收货款信息有误
 *
 * 菜鸟错误信息
 *
 * 面单模板
 * Class ExpressHelperExpressTemplateModel
 * @package shopstar\models\expressHelper
 */

/**
 * This is the model class for table "{{%app_express_helper_express_template}}".
 *
 * @property int $id
 * @property string $name 电子面单模板名称
 * @property int $type 模板类型 0快递鸟 1菜鸟
 * @property int $express_company 快递公司
 * @property string $template_account 模板账号
 * @property string $template_password 模板密码
 * @property string $monthly_code 月结编码
 * @property string $branch_name 网点名称
 * @property string $branch_code 网点编码
 * @property string $template_style 模板样式
 * @property int $pay_type 支付类型 快递鸟:1-现付，2-到付，3-月结，4- 第三方付(仅 SF、KYSY 支持)
 * @property int $is_notice 0通知 1不通知
 * @property int $auto_send 是否自动修改订单状态 1是0否
 * @property string $created_at 创建时间
 * @property int $is_default 是否默认1是0否
 * @property int $is_sub 是否子母单模板 0否1是  暂时废弃
 */
class ExpressHelperExpressTemplateModel extends BaseActiveRecord
{
    /**
     * 面单格式映射
     * @var string[]
     */
    public static $expressTemplateFormatMap = [
        'ANE' => [
            [
                'label' => '二联 180 (宽100mm 高180mm 切点110/70)',
                'value' => ''
            ]
        ],
        'ANEKY' => [
            [
                'label' => '二联 180 (宽100mm 高180mm 切点110/70)',
                'value' => ''
            ]
        ],
        'CND' => [
            [
                'label' => '二联 180 (宽 100mm)',
                'value' => ''
            ]
        ],
        'DBL' => [
            [
                'label' => '二联 177 (宽 100mm 高 177mm 切点 107/70)',
                'value' => ''
            ],
            [
                'label' => '二联 177 新 (宽 100mm 高 177mm 切点 107/70)',
                'value' => '18001'
            ],
            [
                'label' => '三联 177 新 (宽 100mm 高 177mm 切点 107/30/40)',
                'value' => '18002'
            ],
            [
                'label' => '一联 130 (宽 76mm 高 130mm)',
                'value' => '130'
            ]
        ],
        'DBLKY' => [
            [
                'label' => '三联 180 (宽 100mm 高180mm 切点 110/30/40)',
                'value' => ''
            ]
        ],
        'DNWL' => [
            [
                'label' => '一联 150 (宽 100mm 高 150mm 切点 90/60)',
                'value' => ''
            ]
        ],
        'EST365' => [
            [
                'label' => '一联 120 (宽 100mm 高 120mm)',
                'value' => ''
            ]
        ],
        'EMS' => [
            [
                'label' => '二联 150 (宽 100mm 高 150mm 切点 90/60)',
                'value' => ''
            ],
            [
                'label' => '二联 180 (宽 100mm 高180mm 切点 110/70)',
                'value' => '180'
            ]
        ],
        'HTKY' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ],
            [
                'label' => '二联 180 新 (宽 100mm 高 180mm 切点 110/70)',
                'value' => '180'
            ],
            [
                'label' => '一联 130 (宽 76mm 高 130mm)',
                'value' => '130'
            ],
        ],
        'HTKYKY' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ]
        ],
        'CNEX' => [
            [
                'label' => '一联 100 (宽 90mm 高 100mm)',
                'value' => ''
            ]
        ],
        'JD' => [
            [
                'label' => '二联 110 (宽 100mm 高 110mm 切点 60/50)',
                'value' => ''
            ],
            [
                'label' => '二联 110 新 (宽 100mm 高 110mm 切点 60/50)',
                'value' => '110'
            ]
        ],
        'JDKY' => [
            [
                'label' => '二联 110 (宽 100mm 高 110mm 切点 60/50)',
                'value' => ''
            ],
        ],
        'JTSD' => [
            [
                'label' => '一联 130 (宽 76mm 高 130mm)',
                'value' => '139'
            ],
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ],
        ],
        'KYSY' => [
            [
                'label' => '二联 137 (宽 100mm 高 137mm 切点 101/36)',
                'value' => ''
            ],
            [
                'label' => '三联 210 (宽 100mm 高 210mm 切点 90/60/60)',
                'value' => '210'
            ],
        ],
        'LB' => [
            [
                'label' => '三联 104 (宽 75mm 高 104mm)',
                'value' => ''
            ],
        ],
        'LHT' => [
            [
                'label' => '二联 150 (宽 100mm 高 150mm 切点 90/60)',
                'value' => ''
            ],
        ],
        'PJ' => [
            [
                'label' => '一联 120 (宽 80mm 高 120mm)',
                'value' => ''
            ],
        ],
        'UAPEX' => [
            [
                'label' => '二联 150 (宽 100mm 高 150mm 切点 90/60)',
                'value' => ''
            ],
        ],
        'SF' => [
            [
                'label' => '二联 150 新 (宽 100mm 高 150mm 切点 90/60)',
                'value' => '15001'
            ],
            [
                'label' => '二联 180 新 (宽 100mm 高 180mm 切点 110/70)',
                'value' => '180'
            ],
            [
                'label' => '三联 210 新 (宽 100mm 高 210mm 切点 90/60/60)',
                'value' => '21001'
            ],
        ],
        'STO' => [
            [
                'label' => '二联 150 (宽 100mm 高 150mm 切点 90/60)',
                'value' => '150'
            ],
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ],
            [
                'label' => '二联 180 新 (宽 100mm 高 180mm 切点 110/70)',
                'value' => '180'
            ],
            [
                'label' => '三联 180 新 (宽 100mm 高 180mm 切点 110/30/40)',
                'value' => '18003'
            ],
            [
                'label' => '一联 130 (宽 76mm 高 130mm)',
                'value' => '130'
            ],
        ],
        'SURE' => [
            [
                'label' => '二联 150 (宽 100mm 高 150mm 切点 90/60)',
                'value' => ''
            ],
            [
                'label' => '二联 150 新 (宽 100mm 高 150mm 切点 90/60)',
                'value' => '150'
            ],
            [
                'label' => '二联 180 新 (宽 100mm 高 180mm 切点 110/70)',
                'value' => '180'
            ],
        ],
        'SX' => [
            [
                'label' => '一联 105 (宽 75mm 高 105mm)',
                'value' => ''
            ],
        ],
        'SNWL' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ],
        ],
        'TLWL' => [
            [
                'label' => '一联 70 (宽 100mm 高 70mm)',
                'value' => ''
            ],
        ],
        'HOAU' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ],
        ],
        'HHTT' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ],
        ],
        'XFEX' => [
            [
                'label' => '二联 150 (宽 100mm 高 150mm 切点 90/60)',
                'value' => ''
            ],
        ],
        'YD' => [
            [
                'label' => '二联 203 (宽 100mm 高 203mm 切点 152/51)',
                'value' => ''
            ],
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => '180'
            ],
            [
                'label' => '一联 130 (宽 76mm 高 130mm)',
                'value' => '130'
            ],
        ],
        'YDKY' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ],
        ],
        'YTO' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ], [
                'label' => '三联 180 (宽 100mm 高 180mm 切点 110/30/40)',
                'value' => '180'
            ], [
                'label' => '二联 180 新 (宽 100mm 高 180mm 切点 110/70)',
                'value' => '18001'
            ], [
                'label' => '一联 130 (宽 76mm 高 130mm)',
                'value' => '130'
            ],
        ],
        'YZBK' => [
            [
                'label' => '二联 150 (宽 100mm 高 150mm 切点 90/60)',
                'value' => ''
            ],
        ],
        'YZPY' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ], [
                'label' => '二联 180 新 (宽 100mm 高 180mm 切点 110/70)',
                'value' => '180'
            ],
        ],
        'UC' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ]
        ],
        'YCWL' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ]
        ],
        'YMDD' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ]
        ],
        'ZJS' => [
            [
                'label' => '二联 120 (宽 100mm 高 116mm 切点 98/18)',
                'value' => ''
            ],
            [
                'label' => '二联 180 (宽 100m 高 180mm 切点 110/70)',
                'value' => '180'
            ],
            [
                'label' => '二联 120 新 (宽 100mm 高 116mm 切点 98/18)',
                'value' => '120'
            ]
        ],
        'ZTO' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ], [
                'label' => '二联 180 新 (宽 100mm 高 180mm 切点 110/70)',
                'value' => '180'
            ], [
                'label' => '一联 130 (宽 76mm 高 130mm)',
                'value' => '130'
            ]
        ],
        'ZTOKY' => [
            [
                'label' => '二联 180 (宽 100mm 高 180mm 切点 110/70)',
                'value' => ''
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_express_helper_express_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'pay_type', 'is_notice', 'auto_send','is_default','is_sub'], 'integer'],
            [['created_at'], 'safe'],
            [['name', 'template_account', 'template_password', 'monthly_code', 'branch_name', 'branch_code', 'template_style'], 'string', 'max' => 120],
            [['express_company'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '电子面单模板名称',
            'type' => '模板类型 0快递鸟 1菜鸟 ',
            'express_company' => '快递公司',
            'template_account' => '模板账号',
            'template_password' => '模板密码',
            'monthly_code' => '月结编码',
            'branch_name' => '网点名称',
            'branch_code' => '网点编码',
            'template_style' => '模板样式',
            'pay_type' => '支付类型 快递鸟:1-现付，2-到付，3-月结，4- 第三方付(仅 SF、KYSY 支持)',
            'is_notice' => '0通知 1不通知',
            'auto_send' => '是否自动修改订单状态 1是0否',
            'created_at' => '创建时间',
            'is_default' => '是否默认1是0否',
            'is_sub' => '是否子母单模板 0否1是',
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'name' => '电子面单模板名称',
            'type' => '模板类型',
            'express_company' => '快递公司',
            'template_account' => '模板账号',
            'template_password' => '模板密码',
            'monthly_code' => '月结编码',
            'branch_name' => '网点名称',
            'branch_code' => '网点编码',
            'template_style' => '模板样式',
            'pay_type' => '支付类型',
            'is_notice' => '通知快递员上门取件',
            'auto_send' => '是否自动修改订单状态',
            'is_default' => '是否默认',
            'is_sub' => '是否子母单模板',
        ];
    }
}