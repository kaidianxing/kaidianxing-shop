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
use shopstar\constants\commission\CommissionAgentConstant;
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;

/**
 * This is the model class for table "{{%commission_agent}}".
 *
 * @property int $member_id 用户ID
 * @property int $agent_id 上级ID
 * @property int $level_id 分销商等级
 * @property int $status 销商状态 -2 取消分销商资格  -1审核不通过 0待审核,1启用 默认0
 * @property int $is_black 分销商黑名单 0非黑名单,1黑名单 默认0
 * @property string $apply_time 申请成为分销商时间
 * @property string $become_time 成为分销商时间
 * @property string $child_time 成为下线的时间
 * @property string $commission_total 累计佣金
 * @property string $ladder_commission_total 包含的累计阶梯佣金
 * @property string $commission_pay 已提现佣金
 * @property int $is_auto_upgrade 分销商自动升级  0否 1是 默认1
 * @property string $apply_data 手动审核申请资料
 * @property int $is_deleted 是否删除
 */
class CommissionAgentModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%commission_agent}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id'], 'required'],
            [['member_id', 'agent_id', 'level_id', 'status', 'is_black', 'is_auto_upgrade', 'is_deleted'], 'integer'],
            [['apply_time', 'become_time', 'child_time'], 'safe'],
            [['commission_total', 'commission_pay', 'ladder_commission_total'], 'number'],
            [['apply_data'], 'string'],
            [['member_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'member_id' => '用户ID',
            'agent_id' => '上级ID',
            'level_id' => '分销商等级',
            'status' => '销商状态 -2 取消分销商资格  -1审核不通过 0待审核,1启用 默认0',
            'is_black' => '分销商黑名单 0非黑名单,1黑名单 默认0',
            'apply_time' => '申请成为分销商时间',
            'become_time' => '成为分销商时间',
            'child_time' => '成为下线的时间',
            'commission_total' => '累计佣金',
            'commission_pay' => '已提现佣金',
            'is_auto_upgrade' => '分销商自动升级  0否 1是 默认1',
            'apply_data' => '手动审核申请资料',
            'is_deleted' => '是否删除',
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'member_id' => '用户ID',
            'agent_id' => '上级ID',
            'level_id' => '分销商等级',
            'status' => '分销商状态',
            'is_black' => '分销商黑名单',
            'apply_time' => '申请成为分销商时间',
            'become_time' => '成为分销商时间',
            'child_time' => '成为下线的时间',
            'commission_total' => '累计佣金',
            'ladder_commission_total' => '包含的累计阶梯佣金',
            'commission_pay' => '已提现佣金',
            'is_auto_upgrade' => '分销商自动升级',
            'is_deleted' => '是否删除',
        ];
    }

    /**
     * 分销-会员 关系
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getMember()
    {
        return $this->hasOne(MemberModel::class, ['id' => 'member_id']);
    }

    /**
     * 分销商-会员 关系表
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getAgentMember()
    {
        return $this->hasOne(MemberModel::class, ['id' => 'agent_id']);
    }

    /**
     * 分销商-分销等级关系
     * @author 青岛开店星信息技术有限公司
     */
    public function getLevel()
    {
        return $this->hasOne(CommissionLevelModel::class, ['id' => 'level_id']);
    }


    /**
     * 统计下级数量信息
     * @param int $id
     * @param int $setLevel 需要统计几级下级
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getChildCountInfo(int $id, int $setLevel)
    {
        // 下级数量统计
        $childCount = [
            'un_agent' => 0, // 非分销商下线人数
            'level1_all' => 0, // 一级下线
            'level1_agent' => 0, // 一级分销商下线
            'level2_all' => 0, // 二级下线
            'level2_agent' => 0, // 二级分销商下线
            'level3_all' => 0, // 三级下线
            'level3_agent' => 0, // 三级分销商下线
            'all' => 0, // 所有下线
            'all_agent' => 0, // 所有分销商下线
        ];

        // 下级会员非分销商
        $childCount['un_agent'] = CommissionAgentModel::getChildTotal($id, 0, false, $setLevel);
        // 获取三级分分销
        if ($setLevel > 0) {
            $childCount['level1_all'] = CommissionAgentModel::getChildTotal($id, 1, null, $setLevel); // 所有一级下线人数
            $childCount['level1_agent'] = CommissionAgentModel::getChildTotal($id, 1, true, $setLevel); // 所有一级下线分销商人数
            $childCount['all'] += $childCount['level1_all']; // 统计所有下线
            $childCount['all_agent'] += $childCount['level1_agent']; // 统计所有下线分销商
        }
        // 开启二级
        if ($setLevel > 1) {
            $childCount['level2_all'] = CommissionAgentModel::getChildTotal($id, 2, null, $setLevel); // 所有二级下线人数
            $childCount['level2_agent'] = CommissionAgentModel::getChildTotal($id, 2, true, $setLevel); // 所有下线分销商人数
            $childCount['all'] += $childCount['level2_all']; // 统计所有下线
            $childCount['all_agent'] += $childCount['level2_agent']; // 统计所有下线分销商
        }
        // 开启三级
        if ($setLevel > 2) {
            $childCount['level3_all'] = CommissionAgentModel::getChildTotal($id, 3, null, $setLevel); // 所有三级下线人数
            $childCount['level3_agent'] = CommissionAgentModel::getChildTotal($id, 3, true, $setLevel); // 所有下线分销商人数
            $childCount['all'] += $childCount['level3_all']; // 统计所有下线
            $childCount['all_agent'] += $childCount['level3_agent']; // 统计所有下线分销商
        }

        return $childCount;
    }

    /**
     * 判断用户是否是分销商
     * @param int $memberId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isAgent(int $memberId)
    {
        $agent = self::find()->where(['member_id' => $memberId])->first();
        return !is_null($agent) && $agent['status'] > 0;
    }

    /**
     * 获取分销商信息
     * @param int $memberId
     * @return array|\yii\db\ActiveRecord|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAgentInfo(int $memberId)
    {
        // 分销上信息
        $agent = self::find()
            ->where(['member_id' => $memberId, 'status' => 1])
            ->asArray()
            ->one();
        if (empty($agent)) {
            return error('分销商不存在');
        }

        // 分销等级
        if (empty($agent['level_id'])) {
            return error('等级错误');
        }

        $agent['commission_level'] = CommissionLevelModel::find()
            ->select('id, commission_1, commission_2, is_default')
            ->where(['id' => $agent['level_id'], 'status' => 1])
            ->first();

        return $agent;
    }

    /**
     * 获取下级数量
     * @param int $memberId 会员ID
     * @param int $level 层级数 0 全部  1 一级
     * @param null $isAgent 是否是分销商   null 全部    true 仅查询分销商     false 仅查询非分销商
     * @param null $setLevel 商城设置的分销层级
     * @param string $degradeTime 降级时间
     * @return int|string
     * @author likexin
     */
    public static function getChildTotal(int $memberId, int $level = 0, $isAgent = null, $setLevel = null, string $degradeTime = '')
    {
        // 下级
        $relation = CommissionRelationModel::find()->where(['parent_id' => $memberId]);
        if ($level) {
            $relation->andWhere(['level' => $level]);
        } else {
            if (!is_null($setLevel)) {
                $relation->andWhere(['<=', 'level', $setLevel]);
            } else {
                $relation->andWhere(['<=', 'level', 3]);
            }
        }
        if (!is_null($isAgent)) {
            $relation->alias('relation')->leftJoin(self::tableName() . 'as agent', 'agent.member_id = relation.member_id');
            if ($isAgent) {
                $relation->andWhere('agent.status is not null');
                $relation->andWhere(['agent.status' => 1]);
            } else {
                $relation->andWhere(['or', ['agent.status' => null], ['!=', 'agent.status', CommissionAgentConstant::AGENT_STATUS_SUCCESS]]);
            }
        }

        // 降级时间
        if (!empty($degradeTime)) {
            $relation->andWhere(['>=', 'child_time', $degradeTime]);
        }
        return $relation->count();
    }


    /**
     * 拒绝成为分销商
     * @param array $post
     * @param int $userId
     * @author 青岛开店星信息技术有限公司
     */
    public static function changeStatsReject(array $post, int $userId)
    {
        CommissionAgentModel::updateAll(
            ['status' => CommissionAgentConstant::AGENT_STATUS_REJECT],
            ['and', ['in', 'member_id', $post['member_id']]]
        );
        foreach ($post['member_id'] as $id) {
            // 拒绝分销商设置缓存
            $key = 'show_reject_' . '_' . $id;
            \Yii::$app->redis->set($key, DateTimeHelper::now());
            // 日志
            LogModel::write(
                $userId,
                CommissionLogConstant::AGENT_AUDIT,
                CommissionLogConstant::getText(CommissionLogConstant::AGENT_AUDIT),
                $id,
                [
                    'log_data' => ['member_id' => $id, 'status' => -1],
                    'log_primary' => [
                        '会员ID' => $id,
                        '分销商状态' => '拒绝',
                    ],
                    'dirty_identity_code' => [
                        CommissionLogConstant::AGENT_AUDIT,
                    ]
                ]
            );
        }
    }

    /**
     * 检查购买商品是否可成为
     * 用户已购买过的商品 未维权
     * @param int $memberId
     * @param array $set
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkBuyGoods(int $memberId, array $set)
    {
        $becomeGoodsIds = explode(',', $set['become_goods_ids']);
        if (empty($becomeGoodsIds)) {
            return error('未设置商品');
        }

        // 获取用户已购买的商品
        $query = OrderGoodsModel::find()->select('goods_id')->where(['member_id' => $memberId, 'is_count' => 1, 'shop_goods_id' => 0]);
        // 统计方式 1 订单付款后  2 订单完成后
        if ($set['become_order_status'] == 1) {
            $query->andWhere(['>', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_PAY]);
        } else if ($set['become_order_status'] == 2) {
            $query->andWhere(['>=', 'status', OrderStatusConstant::ORDER_STATUS_SUCCESS]);
        }
        $orderGoods = $query->get();

        // 取id
        $goodsIds = array_column($orderGoods, 'goods_id');
        // 交集
        $intersect = array_intersect($goodsIds, $becomeGoodsIds);
        // 如果无重合数据
        if (empty($intersect)) {
            return error('不符合成为分销商条件');
        }

        return true;
    }

    /**
     * 检查消费金额是否满足成为
     * @param int $memberId
     * @param array $set
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkMoneyCount(int $memberId, array $set)
    {
        if (empty($set['become_order_money'])) {
            return error('未设置消费金额');
        }
        // 统计方式  1付款完成  2 订单完成
        if ($set['become_order_status'] == 1) {
            $where = ['>=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_PAY];
        } else {
            $where = ['>=', 'status', OrderStatusConstant::ORDER_STATUS_SUCCESS];
        }
        // 计算实际支付金额
        $list = OrderModel::find()
            ->select(['id', 'pay_price', 'refund_price', 'extra_price_package', 'extra_discount_rules_package'])
            ->where(['member_id' => $memberId])
            ->andWhere($where)
            ->get();
        // 合计金额
        $sum = OrderModel::calculateOrderPrice($list);
        if (bccomp($sum, $set['become_order_money'], 2) < 0) {
            return error('不满足消费金额');
        }

        return true;
    }

    /**
     * 检查支付订单数量是否满足可成为
     * @param int $memberId
     * @param array $set
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkPayOrderCount(int $memberId, array $set)
    {
        if (empty($set['become_order_count'])) {
            return error('订单数量设置错误');
        }
        $where = [
            'and',
            ['member_id' => $memberId],
            ['refund_price' => 0]
        ];
        // 统计方式  1付款完成  2 订单完成
        if ($set['become_order_status'] == 1) {
            $where[] = ['>', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_PAY];
        } else {
            $where[] = ['>=', 'status', OrderStatusConstant::ORDER_STATUS_SUCCESS];
        }
        // 查询所有订单
        $count = OrderModel::find()->andWhere($where)->count();
        // 比较
        if ($count < $set['become_order_count']) {
            return error('不满足消费次数');
        }
        return true;
    }

    /**
     * 是否自动升级
     * @param int $memberId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function isAutoUpgrade(int $memberId)
    {
        $agent = CommissionAgentModel::find()
            ->select(['status', 'is_auto_upgrade'])
            ->where(['member_id' => $memberId])
            ->first();
        if (empty($agent) || $agent['status'] != 1 || $agent['is_auto_upgrade'] == 0) {
            return false;
        }
        return true;
    }


    /**
     * 导出字段
     * 分销商
     * @var array
     */
    public static $exportField = [
        [
            'field' => 'nickname',
            'title' => '昵称',
            'width' => 18,
        ],
        [
            'field' => 'mobile',
            'title' => '手机号',
            'width' => 18,
        ],
        [
            'field' => 'realname',
            'title' => '真实姓名',
            'width' => 18,
        ],
        [
            'field' => 'commission_level_name',
            'title' => '分销等级名称',
            'width' => 18,
        ],
        [
            'field' => 'commission_total',
            'title' => '累计佣金',
            'width' => 18,
        ],
        [
            'field' => 'commission_pay',
            'title' => '已提现佣金',
            'width' => 18,
        ],
        [
            'field' => 'become_time',
            'title' => '成为分销商时间',
            'width' => 18,
        ],
        [
            'field' => 'agent_name',
            'title' => '上级',
            'width' => 18,
        ],

        [
            'field' => 'commission_child',
            'title' => '下线总数',
            'width' => 18,
        ],
    ];

    /**
     * 导出字段
     * 待审核
     * @var array
     */
    public static $exportFieldWait = [
        [
            'field' => 'nickname',
            'title' => '昵称',
            'width' => 18,
        ],
        [
            'field' => 'mobile',
            'title' => '手机号',
            'width' => 18,
        ],
        [
            'field' => 'realname',
            'title' => '真实姓名',
            'width' => 18,
        ],
        [
            'field' => 'level_name',
            'title' => '等级名称',
            'width' => 18,
        ],
        [
            'field' => 'order_count',
            'title' => '订单',
            'width' => 18,
        ],
        [
            'field' => 'balance',
            'title' => '余额',
            'width' => 18,
        ],
        [
            'field' => 'created_at',
            'title' => '注册时间',
            'width' => 18,
        ],
        [
            'field' => 'apply_time',
            'title' => '申请时间',
            'width' => 18,
        ],

        [
            'field' => 'status_text',
            'title' => '审核状态',
            'width' => 18,
        ],
    ];

    public static $statusText = [
        '-2' => '已取消',
        '-1' => '已拒绝',
        '0' => '待审核',
        '1' => '通过'
    ];

    /**
     * 获取分销商总数
     * @param array $andWhere
     * @return int|string
     * @author nizengchao
     */
    public static function getAgentCount(array $andWhere = [])
    {
        return self::find()
            ->where([
                'is_deleted' => 0,
            ])
            ->andWhere($andWhere)
            ->count('1');
    }

}
