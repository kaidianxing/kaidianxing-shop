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
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%shopstar_groups_crew}}".
 *
 * @property int $id
 * @property int $team_id 团id
 * @property int $member_id 会员id
 * @property int $order_id 订单id
 * @property int $goods_id 活动商品ID
 * @property int $is_leader (冗余字段)是否是团长 1是 0否
 * @property string $created_at 参团时间
 * @property int $is_valid 是否有效1是0否
 */
class GroupsCrewModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%groups_crew}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['team_id', 'member_id', 'order_id', 'goods_id', 'is_leader', 'is_valid'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'team_id' => '团id',
            'member_id' => '会员id',
            'order_id' => '订单id',
            'goods_id' => '活动商品ID',
            'is_leader' => '(冗余字段)是否是团长 1是 0否',
            'created_at' => '参团时间',
            'is_valid' => '是否有效1是0否',
        ];
    }

    /**
     * 获取团信息
     * @return ActiveQuery
     * @author likexin
     */
    public function getTeam(): ActiveQuery
    {
        return $this->hasOne(GroupsTeamModel::class, [
            'id' => 'team_id',
        ]);
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
     * 获取参团人数，包括团长
     * @param int $teamId
     * @return int
     * @author likexin
     */
    public static function getCrewNumIncludeLeader(int $teamId): int
    {
        return self::find()
            ->where([
                'team_id' => $teamId,
                'is_valid' => 1,
            ])
            ->count() ?: 0;
    }

    /**
     * 获取会员在当前队伍下是否参与过
     * @param int $teamId
     * @param int $memberId
     * @return array|null
     * @author likexin
     */
    public static function getOne(int $teamId, int $memberId): ?array
    {
        return self::find()
            ->where([
                'team_id' => $teamId,
                'member_id' => $memberId,
            ])
            ->select([
                'id'
            ])
            ->first();
    }

}