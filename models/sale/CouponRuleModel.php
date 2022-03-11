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
use shopstar\models\member\MemberLevelModel;

/**
 * This is the model class for table "{{%coupon_rule}}".
 *
 * @property int $coupon_id
 * @property string $member_level 会员等级
 * @property string $commission_level 分销商等级
 */
class CouponRuleModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_rule}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coupon_id'], 'required'],
            [['coupon_id', 'member_level', 'commission_level'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'coupon_id' => 'Coupon ID',
            'member_level' => '会员等级',
            'commission_level' => '分销商等级',
        ];
    }

    public static function primaryKey()
    {
        return ["coupon_id"];
    }

    /**
     * 更新规则
     * @param array $data
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateRule(array $data)
    {
        if ($data['is_update']) {
            CouponRuleModel::deleteAll(['coupon_id' => $data['coupon_id']]);
        }
        //会员领取限制
        $insertMemberDetail = [];
        $fieldsMember = ['coupon_id', 'member_level', 'commission_level'];
        if (!empty($data['member_level'])) {
            $data['member_level'] = explode(',', $data['member_level']);
            foreach ($data['member_level'] as $item) {
                $insertMemberDetail[] = [
                    $data['coupon_id'],
                    $item, // 会员等级限制
                    0,
                ];
            }
        }
        //分销商领取限制
        if (!empty($data['commission_level'])) {
            $data['commission_level'] = explode(',', $data['commission_level']);
            foreach ($data['commission_level'] as $item) {
                $insertMemberDetail[] = [
                    $data['coupon_id'],
                    0,
                    $item, // 分销等级限制
                ];
            }
        }

        try {
            if (!empty($insertMemberDetail)) {
                CouponRuleModel::batchInsert($fieldsMember, $insertMemberDetail);
            }
        } catch (\Throwable $exception) {
            return error('限制保存失败');
        }
        return true;
    }
}