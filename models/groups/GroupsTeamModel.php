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

namespace shopstar\models\groups;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "{{%shopstar_groups_team}}".
 *
 * @property int $id
 * @property string $team_no 团编号
 * @property int $activity_id 活动id
 * @property int $leader_id 团长会员id
 * @property string $created_at 创建时间
 * @property int $is_ladder 是否是阶梯团 1是 0否
 * @property int $ladder 阶梯
 * @property int $limit_time 限时
 * @property string $end_time 结束时间
 * @property int $count 参团人数
 * @property int $success 是否成团0未成团1成团2过期
 * @property int $success_num 成团人数
 * @property int $is_valid 是否有效 1是0否
 * @property int $is_fictitious 是否虚拟成团0否1是
 * @property string $success_time 成功时间
 */
class GroupsTeamModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%groups_team}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['activity_id', 'leader_id', 'is_ladder', 'ladder', 'limit_time', 'count', 'success', 'success_num', 'is_valid', 'is_fictitious'], 'integer'],
            [['created_at', 'end_time', 'success_time'], 'safe'],
            [['team_no'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'team_no' => '团编号',
            'activity_id' => '活动id',
            'leader_id' => '团长会员id',
            'created_at' => '创建时间',
            'is_ladder' => '是否是阶梯团 1是 0否',
            'ladder' => '阶梯',
            'limit_time' => '限时',
            'end_time' => '结束时间',
            'count' => '参团人数',
            'success' => '是否成团0未成团1成团2过期',
            'success_num' => '成团人数',
            'is_valid' => '是否有效 1是0否',
            'is_fictitious' => '是否虚拟成团0否1是',
            'success_time' => '成功时间',
        ];
    }

    /**
     * 有效
     * @var int
     * @author likexin
     */
    public const IS_VAlID = 1;

    /**
     * 无效
     * @var int
     * @author likexin
     */
    public const IS_NOT_VALID = 0;

    /**
     * 获取单个
     * @param int $id
     * @param array $options
     * @return array|\yii\db\ActiveRecord|null
     * @author likexin
     */
    public static function getOne(int $id, array $options = [])
    {
        $options = array_merge([
            'select' => '*',
            'asArray' => true,
        ], $options);

        return self::find()
            ->where([
                'id' => $id,
            ])
            ->select($options['select'])
            ->asArray($options['asArray'])
            ->one();
    }

    /**
     * 查看团是否过期
     * @param int $teamId
     * @return array|null
     * @author likexin
     */
    public static function getTeamStatus(int $teamId): ?array
    {
        $nowDate = DateTimeHelper::now();

        return self::find()
            ->select([
                'id',
                'activity_id',
            ])
            ->where([
                'id' => $teamId,
                'success' => 0,
            ])
            ->andWhere([
                'and',
                ['>=', 'end_time', $nowDate],
            ])
            ->first();
    }

}