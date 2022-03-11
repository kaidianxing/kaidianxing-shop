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

namespace shopstar\models\finance;


/**
 * This is the model class for table "{{%refund_log}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property int $type 退款类型
 * @property string $money 退款金额
 * @property int $status 状态
 * @property int $order_id 订单id
 * @property string $order_no 订单编号
 * @property string $created_at 创建时间
 * @property string $remark 备注
 */
class RefundLogModel extends \shopstar\bases\model\BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%refund_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'type', 'status', 'order_id'], 'integer'],
            [['money'], 'number'],
            [['created_at'], 'safe'],
            [['order_no'], 'string', 'max' => 50],
            [['remark'], 'string', 'max' => 191],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'type' => '退款类型',
            'money' => '退款金额',
            'status' => '状态',
            'order_id' => '订单id',
            'order_no' => '订单编号',
            'created_at' => '创建时间',
            'remark' => '备注',
        ];
    }


    /**
     * 写入记录
     * @param array $data
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function writeLog(array $data)
    {
        $log = new self();
        $log->setAttributes($data);
        $log->save();

        return true;
    }

    /**
     * @var array[] 导出字段
     */
    public static $logFields = [
        ['title' => '会员ID', 'field' => 'member_id', 'width' => 12],
        ['title' => '会员昵称', 'field' => 'nickname', 'width' => 24],
        ['title' => '会员等级', 'field' => 'level_name', 'width' => 24],
        ['title' => '退款类型', 'field' => 'type_text', 'width' => 24],
        ['title' => '退款金额', 'field' => 'money', 'width' => 24],
        ['title' => '退款时间', 'field' => 'created_at', 'width' => 24],
        ['title' => '退款商品订单号', 'field' => 'order_no', 'width' => 24],
        ['title' => '状态', 'field' => 'status_text', 'width' => 24],
    ];
}