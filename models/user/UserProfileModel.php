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

namespace shopstar\models\user;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%user_profile}}".
 *
 * @property int $user_id 用户id
 * @property int $shop_num 已使用店铺数量
 * @property string $nickname 昵称
 * @property string $refuse_reason 拒绝理由
 * @property string $form_info 表单信息
 * @property string $avatar 头像
 * @property string $audit_time 审核时间
 */
class UserProfileModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_profile}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'shop_num'], 'integer'],
            [['form_info'], 'string'],
            [['nickname'], 'string', 'max' => 125],
            [['refuse_reason', 'avatar'], 'string', 'max' => 255],
            [['user_id'], 'unique'],
            [['audit_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => '用户id',
            'shop_num' => '已使用店铺数量',
            'nickname' => '昵称',
            'refuse_reason' => '拒绝理由',
            'form_info' => '表单信息',
            'avatar' => '头像',
            'audit_time' => '审核时间',
        ];
    }
}