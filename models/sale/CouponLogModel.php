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

namespace shopstar\models\sale;

use shopstar\bases\model\BaseActiveRecord;

use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\OrderNoHelper;

/**
 * This is the model class for table "{{%coupon_log}}".
 *
 * @property int $id
 * @property string $order_no 订单号
 * @property int $member_id 用户id
 * @property int $coupon_id 卡券id
 * @property string $pay_price 支付金额
 * @property string $pay_credit 支付积分
 * @property int $status 状态 -1未完成 1完成
 * @property int $pay_status 支付状态 -1未支付 1支付
 * @property int $credit_status 积分支付状态   -1未支付 1支付
 * @property int $pay_type 支付类型
 * @property int $article_id 文章id
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CouponLogModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'coupon_id', 'status', 'pay_status', 'credit_status', 'pay_type', 'article_id'], 'integer'],
            [['pay_price', 'pay_credit'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['order_no'], 'string', 'max' => 50],
            [['order_no'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => '订单号',
            'member_id' => '用户id',
            'coupon_id' => '卡券id',
            'pay_price' => '支付金额',
            'pay_credit' => '支付积分',
            'status' => '状态 -1未完成 1完成',
            'pay_status' => '支付状态 -1未支付 1支付',
            'credit_status' => '积分支付状态   -1未支付 1支付',
            'pay_type' => '支付类型',
            'article_id' => '支付类型',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 添加优惠券购买记录
     * @param int $memberId
     * @param array $coupon
     * @param int $clientType
     * @param int $articleId
     * @return array|int
     */
    public static function createLog(int $memberId, array $coupon, int $clientType, int $articleId = 0)
    {
        $model = new self();
        $model->order_no = OrderNoHelper::getOrderNo('BC', $clientType);
        $model->member_id = $memberId;
        $model->coupon_id = $coupon['id'];
        $model->pay_price = $coupon['balance'];
        $model->pay_credit = $coupon['credit'];
        $model->status = -1;
        $model->pay_status = -1;
        $model->article_id = $articleId ?? 0;
        $model->credit_status = 1;
        $model->created_at = DateTimeHelper::now();
        if ($model->save() === false) {
            return error($model->getErrorMessage());
        }
        return $model->id;
    }

}