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

namespace shopstar\models\commission;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\commission\CommissionRelationLogConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\services\commission\CommissionLevelService;

/**
 * This is the model class for table "{{%commission_relation}}".
 *
 * @property int $member_id 会员ID
 * @property int $parent_id 上级ID
 * @property int $level 层级
 * @property string $child_time 成为下线时间
 */
class CommissionRelationModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%commission_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'parent_id'], 'required'],
            [['member_id', 'parent_id', 'level'], 'integer'],
            [['child_time'], 'safe'],
            [['member_id', 'parent_id'], 'unique', 'targetAttribute' => ['member_id', 'parent_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'member_id' => '会员ID',
            'parent_id' => '上级ID',
            'level' => '层级',
            'child_time' => '成为下线时间',
        ];
    }


    /**
     * 确立上下线关系
     * @param int $memberId
     * @param int $parentId
     * @param int $bindType 绑定的类型 10 正常绑定关系
     * @return array|bool
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function handle(int $memberId, int $parentId = 0, int $bindType = 0)
    {
        $member = MemberModel::find()->where(['id' => $memberId])->asArray()->one();
        if (empty($member)) {
            return error('参数错误');
        }
        // 读取分销设置
        $set = CommissionSettings::get('set');
        if (empty($set['commission_level'])) {
            return error('未开启分销');
        }

        // 处理上级ID (可能自己扫自己的海报
        if (!empty($parentId) && $memberId == $parentId) {
            $parentId = $member['inviter'];
        }

        // 处理邀请人ID
        if (empty($parentId) && $member['inviter'] > 0) {
            $parentId = $member['inviter'];
        }
        // 不为空判断 新的上级 是否是分销商
        if (!empty($parentId)) {
            if (!CommissionAgentModel::isAgent($parentId)) {
                return error('上级不是分销商');
            }
        } else {
            return error('上级为空');
        }

        // 判断上下线条件
        // 首次分享链接
        // @change 倪增超 兼容竞争分销, 代码块提前到这里, 记录邀请人
        if ($set['child_condition'] == 1) {
            if (empty($member['inviter'])) {
                MemberModel::updateAll(['inviter' => $parentId, 'invite_time' => DateTimeHelper::now()], ['id' => $memberId]);
            }
        } else if ($set['child_condition'] == 2) { // 首次付款
            $orderCount = 0;
            // 获取有邀请人后的订单数
            if ($member['invite_time'] != 0) {
                $orderCount = OrderModel::find()->where(['member_id' => $memberId])->andWhere(['>', 'pay_time', $member['invite_time']])->count();
            }
            //@change 倪增超 付款应先拿header里的inviter 的值
            $inviterId = (int)RequestHelper::header('inviter-id');
            if (!empty($inviterId) && $inviterId > 0) {
                $parentId = $inviterId;
            }
            //@change 倪增超 兼容竞争分销的逻辑, 每次都需要更新邀请人
            if ($member['inviter'] != $parentId) {
                // 更新邀请人
                MemberModel::updateAll(['inviter' => $parentId, 'invite_time' => DateTimeHelper::now()], ['id' => $memberId]);
            } else {
                // 有上级时,更新邀请时间, 给竞争抢客用
                if (self::getParentId($memberId)) {
                    MemberModel::updateAll(['invite_time' => DateTimeHelper::now()], ['id' => $memberId]);
                }
            }

            if ($orderCount == 0) {
                return error('未满足首次付款条件');
            }
        }

        // 获取分销商
        $commissionAgent = CommissionAgentModel::find()
            ->where([
                'and',
                ['member_id' => $memberId],
                ['>=', 'status', 0]
            ])->one();

        // 已是分销商或待审核跳过
        if (!empty($commissionAgent)) {
            return true;
        }
        // 先检查当前是否有上级 有上级跳出
        $check = self::getParentId($memberId);
        if (!empty($check)) {
            return true;
        }

        // 新增下线

        // 走到这说明可以绑定关系
        return self::build($memberId, $parentId, 1, $bindType);
    }

    /**
     * 处理层级关系
     * @param int $memberId
     * @param int $parentId
     * @param int $level
     * @param int $bindType 绑定类型 10: 正常绑定上级
     * @return bool
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function build(int $memberId, int $parentId, int $level = 1, int $bindType = 0): bool
    {
        $level = max($level, 1);
        // 查询一级上线关系
        $model = self::findOne(['member_id' => $memberId, 'level' => 1]);

        if ($level == 1) {
            // 如果有关系
            if (!empty($model)) {
                $now = DateTimeHelper::now();
                $model->parent_id = $parentId;
                $model->child_time = DateTimeHelper::now();
                if ($saveRes = $model->save()) {
                    // 如果用户是分销商
                    if (CommissionAgentModel::isAgent($memberId)) {
                        // 修改分销商表上级ID
                        CommissionAgentModel::updateAll(['agent_id' => $parentId, 'child_time' => $now], ['member_id' => $memberId]);
                    }
                }

            } else { // 新建关系
                $model = new self();
                $model->member_id = $memberId;
                $model->parent_id = $parentId;
                $model->level = $level;
                $model->child_time = DateTimeHelper::now();
                $saveRes = $model->save();

                // @change 倪增超 build为公用的方法, 只有是正常绑定上级时, 才记录分销关系日志, 其他的日志在单独的业务逻辑处理
                if ($saveRes && $bindType == CommissionRelationLogConstant::TYPE_BIND) {
                    // 记录分销关系log
                    $logData[] = [
                        'member_id' => $memberId,
                        'parent_id' => $parentId,
                        'type' => CommissionRelationLogConstant::TYPE_BIND,
                    ];
                    CommissionRelationLogModel::saveLog($logData);
                }
            }
        }
        // 查询上级用户
        $parents = self::find()->where(['member_id' => $parentId])->asArray()->all();
        if (!empty($parents)) {
            // 上级层级加一  对于当前用户
            foreach ($parents as $parent) {
                if (empty($parent['parent_id'])) {
                    continue;
                }
                $model = new self();
                $model->member_id = $memberId;
                $model->parent_id = $parent['parent_id'];
                $model->child_time = DateTimeHelper::now();
                $model->level = (int)($parent['level'] + $level);
                $model->save();
            }
        }
        // 新增下线通知
        $member = MemberModel::findOne(['id' => $memberId]);
        $agent = MemberModel::findOne(['id' => $parentId]);
        $parentIds = CommissionRelationModel::getAllParentId($memberId);
        if (!empty($parentIds)) {
            // 同一会员五秒钟之内只执行一次
            $key = 'send_child_notice_' . '_' . $memberId;
            $exists = CacheHelper::get($key);
            if (!$exists) {
                CacheHelper::set($key, '1', 5);
                foreach ($parentIds as $key => $value) {
                    $result = NoticeComponent::getInstance(NoticeTypeConstant::COMMISSION_BUYER_AGENT_ADD_CHILD_LINE, [
                        'member_nickname' => $agent->nickname,
                        'change_time' => DateTimeHelper::now(),
                        'down_line_nickname' => $member->nickname,
                    ], 'commission');
                    if (!is_error($result)) {
                        $result->sendMessage([], ['commission_level' => $key, 'member_id' => $value]);
                    }
                }
            }

        }

        //分销上级等级升级检测
        CommissionLevelService::agentUpgrade($memberId);

        return true;
    }

    /**
     * 获取上级ID
     * @param int $memberId
     * @param int $level
     * @return array|int|null|\yii\db\ActiveRecord
     * @author likexin
     */
    public static function getParentId(int $memberId, int $level = 1)
    {
        $parent = self::find()->where(['member_id' => $memberId, 'level' => $level])->select(['parent_id'])->asArray()->one();
        if (empty($parent)) {
            return 0;
        }
        return (int)$parent['parent_id'];
    }

    /**
     * 修改用户上级
     * @param int $memberId 要修改的ID
     * @param int $parentId 上级ID 可为空
     * @author 青岛开店星信息技术有限公司
     */
    public static function modify(int $memberId, int $parentId = 0)
    {
        // 删除当前会员关系
        self::deleteAll(['member_id' => $memberId]);
        // 指定上级
        if (!empty($parentId)) {
            self::build($memberId, $parentId);
        }

        // 找出所有跟当前ID 有关系的下级
        $child = self::find()->where(['parent_id' => $memberId])->asArray()->all();

        foreach ($child as $item) {
            // 删除超过当前等级之上的记录
            self::deleteAll(['and', ['member_id' => $item['member_id']], ['>', 'level', $item['level']]]);
            // 重建等级树
            self::build($item['member_id'], $item['parent_id'], $item['level']);
        }
    }

    /**
     * 获取所有上级id
     * @param int $memberId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAllParentId(int $memberId)
    {
        $parentIds = [];
        $parent1Id = self::getParentId($memberId, 1);
        if ($parent1Id) {
            $parentIds[1] = $parent1Id;
            $parent2Id = self::getParentId($memberId, 2);
            if ($parent2Id) {
                $parentIds[2] = $parent2Id;
                $parent3Id = self::getParentId($memberId, 3);
                if ($parent3Id) {
                    $parentIds[3] = $parent3Id;
                }
            }
        }
        return $parentIds;
    }

    /**
     * 获取绑定关系总数
     * @param array $where
     * @return int|string
     * @author nizengchao
     */
    public static function getRelationMemberCount(array $where = [])
    {
        return MemberModel::find()
            ->alias('member')
            ->where([
                'member.is_black' => 0,
                'member.is_deleted' => 0,
            ])
            ->andWhere($where)
            ->leftJoin(self::tableName() . ' relation', 'member.id = relation.member_id')
            ->count(1);
    }

}