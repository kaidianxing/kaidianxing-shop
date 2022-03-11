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

namespace shopstar\models\goods;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\log\goods\GoodsLogConstant;
use shopstar\constants\order\OrderTypeConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\group\GoodsGroupMapModel;
use shopstar\models\goods\label\GoodsLabelMapModel;
use shopstar\models\log\LogModel;
use yii\helpers\Json;


/**
 * This is the model class for table "{{%goods}}".
 *
 * @property int $id
 * @property int $status 状态, 0下架, 1上架, 2上架不显示
 * @property string $created_at
 * @property string $ver 锁
 * @property int $is_deleted 是否删除0=未删除, 1=已删除 2 永久删除
 * @property string $title 商品标题
 * @property string $sub_name 商品副标题
 * @property string $short_name 短标题, 用于打印机
 * @property int $type 商品类型 0=实体商品, 1=虚拟商品
 * @property string $thumb 商品封面图第一张
 * @property string $thumb_all 所有商品封面图
 * @property string $video 首图视频
 * @property string $video_thumb 视频首图
 * @property string $video_type 视频首图
 * @property string $unit 商品单位, 默认件
 * @property string $goods_sku 商品编码
 * @property string $bar_code 商品条码
 * @property int $sort_by 商品排序,默认0, 越大越靠前
 * @property int $stock 商品库存库存
 * @property int $reduction_type 减库存方式: 0 拍下减库存 1 付款减库存 2 永久不减
 * @property int $sales 销售量
 * @property int $real_sales 实际销售量
 * @property string $price 商品价格
 * @property string $min_price 最低销售价
 * @property string $max_price 最高销售价
 * @property string $cost_price 成本价
 * @property string $original_price 商品原始价格
 * @property int $has_option 是否启动多规格, 0不启用 ,1启用
 * @property string $content 商品详情
 * @property int $dispatch_type 运费类型 0包邮 1运费模板, 2统一运费
 * @property string $dispatch_price type为1时必填
 * @property int $dispatch_id 运费模板
 * @property string $weight 重量
 * @property string $ext_field 扩展字段 show_sales:展示销量, 0不显示, 1显示 show_stock:展示库存, 0不显示, 1显示 is_delivery_pay: 货到付款, 0不支持, 1支持 invoice: 发票, 0不支持, 1支持 refund: 退款 0不支持, 1支持 return: 退款退货 0不支持, 1支持 exchange: 换货, 0不支持, 1支持 is_not_discount: 不参与会员折扣, 0参与, 1不参与 putaway_time:自动上架时间 auto_putaway:是否是自动上架1是0否 single_max_buy:单次最大购买 single_min_buy:单次最小购买 max_buy:最大购买 buy_limit:是否开启限购1是0否 auto_deliver: 自动发货, 0否 1是 auto_deliver_content: 自动发货内容  auto_receive: 自动收货: 0否 1是'
 * @property int $is_recommand 是否推荐, 0不推荐, 1推荐
 * @property int $is_hot 是否热卖, 0不是, 1是
 * @property int $is_new 是否新品, 0不是, 1是
 * @property int $params_switch 参数开关1开0关
 * @property string $params 商品参数, json: {key,value}
 * @property int $deduction_credit_type 0是关闭 1不限制 2自定义抵扣最多
 * @property string $deduction_credit 积分抵扣金额
 * @property int $deduction_credit_repeat 积分抵扣是否重复叠加0=不重复,1=重复
 * @property int $deduction_balance_type 0是关闭 1不限制 2自定义抵扣最多
 * @property string $deduction_balance 余额抵扣
 * @property int $deduction_balance_repeat 余额抵扣是否重复叠加0=不重复,1=重复
 * @property int $pv_count 浏览量
 * @property int $single_full_unit_switch 单品满件开关
 * @property int $single_full_unit 单品满件包邮
 * @property int $single_full_quota_switch 单品满额包邮开关
 * @property int $single_full_quota 单品满额包邮
 * @property int $browse_level_perm 会员等级查看权限,0关闭, 1开启
 * @property int $browse_tag_perm 会员标签查看权限,0关闭, 1开启
 * @property int $buy_level_perm 会员等级购买权限,0关闭, 1开启
 * @property int $buy_tag_perm 会员标签购买权限,0关闭, 1开启
 * @property int $member_level_discount_type 会员折扣类型 0 不支持  1 系统默认  2 指定会员等级  3多规格会员等级
 * @property int $is_commission 是否参与分销 0否 1 是
 * @property int $auto_deliver 自动发货 0否 1是
 * @property int $form_status 表单状态 0关闭 1开启
 * @property int $form_id 表单ID
 * @property string $auto_deliver_content 自动发货内容
 * @property string $dispatch_express 是否支持快递
 * @property string $dispatch_intracity 是否支持同城配送
 * @property int $in_checked 是否审核通过(多商户) 0未提交审核 1未审核 10通过 20拒绝
 * @property int $dispatch_verify 是否支持核销 0否1是
 * @property int $is_all_verify 是否所有核销点 0否1是
 * @property int $virtual_account_id 卡密库id
 * @property int $give_credit_status 赠送积分开关
 * @property int $give_credit_num 赠送积分数量
 */
class GoodsModel extends BaseActiveRecord
{
    /**
     * @event self an event that is triggered before a record is save.
     */
    const EVENT_BEFORE_SAVE = 'beforeSave';

    /**
     * @event self an event that is triggered after a record is save.
     */
    const EVENT_AFTER_SAVE = 'afterSave';


    /**
     * @var array
     * @author 青岛开店星信息技术有限公司
     */
    public $goods = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['virtual_account_id', 'give_credit_num', 'give_credit_status', 'is_all_verify', 'dispatch_verify', 'form_status', 'form_id', 'status', 'ver', 'is_deleted', 'type', 'sort_by', 'stock', 'reduction_type', 'sales', 'real_sales', 'has_option', 'dispatch_type', 'dispatch_id', 'is_recommand', 'is_hot', 'is_new', 'params_switch', 'deduction_credit_type', 'deduction_credit_repeat', 'deduction_balance_type', 'deduction_balance_repeat', 'pv_count', 'single_full_unit_switch', 'single_full_unit', 'single_full_quota_switch', 'single_full_quota', 'browse_level_perm', 'browse_tag_perm', 'buy_level_perm', 'buy_tag_perm', 'member_level_discount_type', 'is_commission', 'auto_deliver', 'dispatch_express', 'dispatch_intracity', 'is_checked', 'video_type'
            ], 'integer'],
            [['created_at'], 'safe'],
            [['thumb_all', 'content', 'ext_field', 'params', 'auto_deliver_content',], 'string'],
            [['price', 'min_price', 'max_price', 'cost_price', 'original_price', 'dispatch_price', 'weight', 'deduction_credit', 'deduction_balance'], 'number'],
            [['title'], 'string', 'max' => 100],
            [['sub_name', 'short_name', 'thumb', 'video', 'video_thumb'], 'string', 'max' => 191],
            [['unit'], 'string', 'max' => 12],
            [['goods_sku', 'bar_code'], 'string', 'max' => 50],
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'goods' => [
                'title' => '基本设置',
                'item' => [
                    'id' => '商品id',
                    'type' => '商品类型',
                    'title' => '商品标题',
                    'price' => '商品价格',
                    'max_price' => '商品最大',
                    'min_price' => '商品最小',
                    'original_price' => '划线价',
                    'stock' => '库存',
                    'reduction_type' => '库存设置',
                    'dispatch_type' => '运费设置',
                    'category' => '分类',
                    'is_new' => '新品',
                    'is_hot' => '新品',
                    'is_recommand' => '推荐',
                    'sort_by' => '权重',
                    'dispatch_template_name' => '模板名称',
                    'status' => '商品状态',
                    'is_delivery_pay' => '货到付款',
                    'return' => '退款退货',
                    'options' => [
                        'title' => '商品规格',
                        'item' => [
                            'title' => '规格标题',
                            'price' => '规格售价',
                            'stock' => '规格库存'
                        ]
                    ],
                    'is_deleted' => '删除'
                ],
            ],
            'buy_button' => [
                'title' => '价格面议',
                'item' => [
                    'buy_button_type' => '购买按钮类型',
                    'name' => '按钮名称',
                    'click_type' => '按钮交互',
                    'click_style' => '点击交互样式',
                    'price_text' => '价格文字',
                ]
            ],
            'buy_perm' => [
                'title' => '商品购买权限',
                'item' => [
                    'member_level' => [
                        'title' => '会员等级',
                        'item' => [
                            'name' => '会员等级名称'
                        ]
                    ],
                    'member_label' => [
                        'title' => '会员标签',
                        'item' => [
                            'name' => '会员标签名称'
                        ]
                    ]
                ]
            ],
            'browse_perm' => [
                'title' => '商品浏览权限',
                'item' => [
                    'member_level' => [
                        'title' => '会员等级',
                        'item' => [
                            'name' => '会员等级名称'
                        ]
                    ],
                    'member_label' => [
                        'title' => '会员标签',
                        'item' => [
                            'name' => '会员标签名称'
                        ]
                    ]
                ]
            ],
            'buy_limit' => [
                'title' => '购买设置',
                'item' => [
                    'single_max_buy' => '单次最大购买',
                    'single_min_buy' => '单次最小购买',
                    'max_buy' => '总共可购买'
                ]
            ],
            'sales' => [
                'title' => '营销',
                'item' => [
                    'deduction_credit_type' => '积分抵扣',
                    'deduction_balance_type' => '余额抵扣',
                    'single_full_unit_switch' => '单品满件包邮',
                    'single_full_unit' => '单品满X件包邮',
                    'single_full_quota_switch' => '单品满额包邮',
                    'single_full_quota' => '单品满X额包邮',
                    'give_credit_status' => '消费得积分',
                    'give_credit_num' => '消费得积分赠送数量',
                ]
            ],
            'member_discount' => [
                'title' => '会员折扣',
                'item' => [
                    'type' => '折扣类型',
                    'rules' => [
                        'title' => '规则',
                        'item' => [
                            'level_name' => '等级名称',
                            'option_name' => '规格标题',
                            'type' => '折扣类型',
                            'discount' => '会员价'
                        ]
                    ]
                ]
            ],
            'commission' => [
                'title' => '分销',
                'item' => [
                    'join' => '是否参与',
                    'commission_set' => '佣金设置',
                    'goods_commission' => [
                        'title' => '分销规则',
                        'item' => [
                            'commission_level_name' => '分销等级',
                            'one_type' => '一级佣金类型',
                            'one_commission' => '一级佣金',
                            'two_type' => '二级佣金类型',
                            'two_commission' => '二级佣金',
                            'three_type' => '三级佣金类型',
                            'three_commission' => '二级佣金',
                        ]
                    ]
                ]
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => '状态, 0下架, 1上架, 2上架不显示',
            'created_at' => 'Create Time',
            'ver' => '锁',
            'is_deleted' => '是否删除0=未删除, 1=已删除 2 永久删除',
            'title' => '商品标题',
            'sub_name' => '商品副标题',
            'short_name' => '短标题, 用于打印机',
            'type' => '商品类型 0=实体商品, 1=虚拟商品',
            'thumb' => '商品封面图第一张',
            'thumb_all' => '所有商品封面图',
            'video' => '首图视频',
            'video_thumb' => '视频首图',
            'video_type' => '视频上传类型 1本地 2网络提取 3商品助手抓取',
            'unit' => '商品单位, 默认件',
            'goods_sku' => '商品编码',
            'bar_code' => '商品条码',
            'sort_by' => '商品排序,默认0, 越大越靠前',
            'stock' => '商品库存库存',
            'reduction_type' => '减库存方式: 0 拍下减库存 1 付款减库存 2 永久不减',
            'sales' => '销售量',
            'real_sales' => '实际销售量',
            'price' => '商品价格',
            'min_price' => '最低销售价',
            'max_price' => '最高销售价',
            'cost_price' => '成本价',
            'original_price' => '商品原始价格',
            'has_option' => '是否启动多规格, 0不启用 ,1启用',
            'content' => '商品详情',
            'dispatch_type' => '运费类型 0包邮 1运费模板, 2统一运费',
            'dispatch_price' => 'type为1时必填',
            'dispatch_id' => '运费模板',
            'weight' => '重量',
            'ext_field' => '扩展字段
                show_sales:展示销量, 0不显示, 1显示
                show_stock:展示库存, 0不显示, 1显示
                is_delivery_pay: 货到付款, 0不支持, 1支持
                invoice: 发票, 0不支持, 1支持
                refund: 退款 0不支持, 1支持
                return: 退款退货 0不支持, 1支持
                exchange: 换货, 0不支持, 1支持
                is_not_discount: 不参与会员折扣, 0参与, 1不参与
                putaway_time:自动上架时间
                auto_putaway:是否是自动上架1是0否
                single_max_buy:单次最大购买
                single_min_buy:单次最小购买
                max_buy:最大购买
                buy_limit:是否开启限购1是0否
                auto_deliver: 自动发货, 0否 1是
                auto_deliver_content: 自动发货内容
                auto_receive: 自动收货: 0否 1是
                buy_button_type: 详情页购买按钮样式 0:走店铺装修 1:自定义样式(价格面议)
                buy_button_settings:按钮样式设置
                name: 按钮名称
                price_text: 商品价格文字
                click_type: 点击按钮交互 1: 默认, 立即下单 2:价格面议, 无法下单和加购
                click_style: 点击按钮交互样式: 1: 弹窗 2:跳转链接 3:打电话
                click_pop_content: 弹窗内容
                click_pop_image: 弹窗图片
                click_pop_button_text: 弹窗按钮文字
                click_jump_url: 跳转链接地址
                click_telephone_type: 电话类型 1:走商城默认联系方式 2:自定义电话
                click_telephone: 电话 支持手机;/座机/400
                note: 商品备注',
            'is_recommand' => '是否推荐, 0不推荐, 1推荐',
            'is_hot' => '是否热卖, 0不是, 1是',
            'is_new' => '是否新品, 0不是, 1是',
            'params_switch' => '参数开关1开0关',
            'params' => '商品参数, json: {key,value}',
            'deduction_credit_type' => '0是关闭 1不限制 2自定义抵扣最多',
            'deduction_credit' => '积分抵扣金额',
            'deduction_credit_repeat' => '积分抵扣是否重复叠加0=不重复,1=重复',
            'deduction_balance_type' => '0是关闭 1不限制 2自定义抵扣最多',
            'deduction_balance' => '余额抵扣',
            'deduction_balance_repeat' => '余额抵扣是否重复叠加0=不重复,1=重复',
            'pv_count' => '浏览量',
            'single_full_unit_switch' => '单品满件开关',
            'single_full_unit' => '单品满件包邮',
            'single_full_quota_switch' => '单品满额包邮开关',
            'single_full_quota' => '单品满额包邮',
            'browse_level_perm' => '会员等级查看权限,0关闭, 1开启',
            'browse_tag_perm' => '会员标签查看权限,0关闭, 1开启',
            'buy_level_perm' => '会员等级购买权限,0关闭, 1开启',
            'buy_tag_perm' => '会员标签购买权限,0关闭, 1开启',
            'member_level_discount_type' => '会员折扣类型 0 不支持  1 系统默认  2 指定会员等级  3多规格会员等级',
            'is_commission' => '是否参与分销 0否 1 是',
            'auto_deliver' => '自动发货 0否 1是',
            'auto_deliver_content' => '自动发货内容',
            'dispatch_express' => '是否支持快递 0否1是',
            'dispatch_intracity' => '是否支持同城配送 0否1是',
            'form_status' => '是否支持同城配送 0否1是',
            'is_checked' => '是否审核通过(多商户) 0未提交审核 1未审核 10通过 20拒绝',
            'dispatch_verify' => '是否支持核销 0否1是',
            'is_all_verify' => '是否所有核销点 0否1是',
            'virtual_account_id' => '卡密库id',
            'give_credit_status' => '赠送积分开关',
            'give_credit_num' => '赠送积分数量',
        ];
    }

    /**
     * 获取转义字符串
     * @param string $extField
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function getExtField($extField = '')
    {
        return empty($extField) ? Json::decode($this->ext_field) : Json::decode($extField);
    }

    /**
     * 验证前的修改
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function beforeValidate()
    {
        //需要转义的字符串
        $this->ext_field = is_string($this->ext_field) ? $this->ext_field : Json::encode($this->ext_field);
        $this->params = is_string($this->params) ? $this->params : Json::encode($this->params);
        $this->thumb_all = is_string($this->thumb_all) ? $this->thumb_all : Json::encode($this->thumb_all);

        return parent::beforeValidate();
    }

    /**
     * 获取商品对应分类
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getCategory()
    {
        return $this->hasMany(GoodsCategoryMapModel::class, ['goods_id' => 'id']);
    }

    /**
     * 获取商品-规格关系
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getOptions()
    {
        return $this->hasMany(GoodsOptionModel::class, ['goods_id' => 'id']);
    }

    /**
     * 获取商品对应子店铺分类
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getSubShopCategory()
    {
        return $this->hasMany(GoodsCategoryMapModel::class, ['goods_id' => 'id']);
    }

    /**
     * 获取分组
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getGroup()
    {
        return $this->hasMany(GoodsGroupMapModel::class, ['goods_id' => 'id']);
    }

    /**
     * 获取标签
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getLabel()
    {
        return $this->hasMany(GoodsLabelMapModel::class, ['goods_id' => 'id']);
    }

    /**
     * 修改商品属性
     * @param int $uid
     * @param $goodsId 商品id
     * @param string $field 字段
     * @param $value 值
     * @return bool
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public static function changeProperty(int $uid, $goodsId, string $field, $value)
    {
        $allowField = ['is_new', 'is_hot', 'is_recommand', 'ext_field', 'sort_by', 'title'];

        if (!in_array($field, $allowField)) {
            throw new GoodsException(GoodsException::GOODS_PROPERTY_NOT_ALLOW_FIELD_ERROR);
        }

        $tr = \Yii::$app->db->beginTransaction();
        try {
            $goods = self::find()->where(['id' => $goodsId])->all();
            foreach ($goods as $goodsItem) {
                if ($field == 'ext_field') {
                    $ext_field = $goodsItem->getExtField();
                    $ext_field['is_not_discount'] = $value;
                    $goodsItem->ext_field = $ext_field;

                } else {
                    $goodsItem->$field = $value;
                }

                $goodsItem->save();

                if (in_array($field, ['is_new', 'is_hot', 'is_recommand'])) {
                    $value = $value == 0 ? '否' : '是';
                }


                //添加操作日志
                LogModel::write(
                    $uid,
                    GoodsLogConstant::GOODS_CHANGE_PROPERTY,
                    GoodsLogConstant::getText(GoodsLogConstant::GOODS_CHANGE_PROPERTY),
                    $goodsId,
                    [
                        'log_data' => $goodsItem->attributes,
                        'log_primary' => $goodsItem->getLogAttributeRemark([
                            'goods' => [
                                'id' => $goodsItem['id'],
                                'title' => $goodsItem['title'],
                                $field => $value,
                            ]
                        ]),
                    ]
                );
            }


            $tr->commit();
        } catch (\Throwable $throwable) {
            $tr->rollBack();
            throw new GoodsException(GoodsException::GOODS_PROPERTY_SAVE_ERROR, $throwable->getMessage());
        }

        return true;
    }


}
