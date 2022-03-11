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

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\log\member\MemberLogConstant;

use shopstar\models\log\LogModel;
use shopstar\models\member\MemberModel;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "{{%member_group_map}}".
 *
 * @property int $member_id 用户id
 * @property int $group_id 标签组id
 */
class MemberGroupMapModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_group_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'group_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'member_id' => '用户id',
            'group_id' => '标签组id',
        ];
    }

    /**
     * 获取每个标签组下的会员个数
     * @return array|ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberCount()
    {
        return self::find()
            ->select('group_id, count(member_id) count')
            ->groupBy('group_id')
            ->indexBy('group_id')
            ->get();
    }

    /**
     * 更新会员标签组 map
     * @param array|string $memberIds 会员id
     * @param array $groups 标签组数组
     * @param int $uid
     * @return bool
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateMap(array $memberIds, array $groups, int $uid)
    {
        $mapValue = [];
        // 删除所有
        self::deleteAll(['member_id' => $memberIds]);
        // 获取所有会员名称
        $members = MemberModel::find()
            ->select('id, nickname')
            ->where(['id' => $memberIds])
            ->indexBy('id')
            ->get();
        // 获取所有标签组
        $groupList = MemberGroupModel::find()
            ->select('id, group_name')
            ->where(['id' => $groups])
            ->indexBy('id')
            ->get();

        $groupName = implode(',', array_column($groupList, 'group_name'));
        // 遍历每个会员进行插入
        foreach ($memberIds as $memberId) {
            foreach ($groups as $group) {
                if (empty($group)) {
                    continue;
                }
                $mapValue[] = [$memberId, $group];
            }
            // 记录日志
            LogModel::write(
                $uid,
                MemberLogConstant::MEMBER_CHANGE_GROUP,
                MemberLogConstant::getText(MemberLogConstant::MEMBER_CHANGE_GROUP),
                $memberId,
                [
                    'log_data' => [
                        'id' => $memberId,
                        'nickname' => $members[$memberId]['nickname'],
                        'group_name' => $groupName
                    ],
                    'log_primary' => [
                        'id' => $memberId,
                        '昵称' => $members[$memberId]['nickname'],
                        '标签组' => $groupName ?: '-',
                    ],
                    'dirty_identity_code' => [
                        MemberLogConstant::MEMBER_CHANGE_GROUP,
                    ]
                ]
            );
        }
        // 批量插入
        if (!empty($mapValue)) {
            self::batchInsert(['member_id', 'group_id'], $mapValue);
        }

        return true;
    }

    /**
     * 根据会员id获取标签id
     * @param int $memberId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getGroupIdByMemberId(int $memberId)
    {
        return self::find()->where(['member_id' => $memberId])->asArray()->select('group_id')->column();
    }

}
