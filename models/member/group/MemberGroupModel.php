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

namespace shopstar\models\member\group;

/**
 * This is the model class for table "{{%member_group}}".
 *
 * @property int $id
 * @property string $group_name 标签名称
 * @property string $description 标签描述
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 */
class MemberGroupModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['group_name'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 255],
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'id' => 'ID',
            'group_name' => '标签名称',
            'description' => '标签描述',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_name' => '标签名称',
            'description' => '标签描述',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * 删除标签组
     * @param array $id
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteGroups(array $id)
    {
        $groups = self::find()
            ->where(['id' => $id])
            ->all();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $group->delete();
                MemberGroupMapModel::deleteAll(['group_id' => $group->id]);
            }
        }
    }

    /**
     * 获取会员标签映射
     * @return array
     */
    public static function getMemberGroupMap()
    {
        $groupList = self::find()
            ->select('id, group_name')
            ->asArray()
            ->all();

        return $groupList;
    }

}