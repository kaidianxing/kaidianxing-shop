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

namespace shopstar\models\shoppingReward;

use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\goods\category\GoodsCategoryModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\sale\CouponModel;
use shopstar\constants\shoppingReward\ShoppingRewardActivityConstant;
use shopstar\constants\shoppingReward\ShoppingRewardActivityLogConstant;
use shopstar\jobs\shoppingReward\AutoStopShoppingRewardJob;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%app_shopping_reward_activity}}".
 *
 * @property string $id
 * @property string $title  标题
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property string $client_type 发放渠道
 * @property int $goods_type 商品类型 0 不限制 1指定商品参与  2指定商品不参与 3 指定商品分类
 * @property int $member_type 参与资格 0全部会员  1 会员等级 2会员标签
 * @property int $send_type 发送结点 0 下单支付成功  1订单完成
 * @property string $reward 奖励内容 1 优惠券  2积分 3余额
 * @property string $coupon_ids 优惠券id
 * @property int $credit 积分
 * @property string $balance 余额
 * @property int $pick_times_type 领取次数限制  0 不限制  1每人活动期间最多领取  2每人每天做多领取
 * @property int $pick_times_limit 最多领取
 * @property string $created_at 创建时间
 * @property int $job_id 任务id
 * @property int $status 活动状态  0 未开始或进行中 -1 停止 -2手动停止
 * @property int $send_count 发送数量
 * @property string $stop_time 停止时间
 * @property int $is_deleted 是否删除
 * @property int $redPackage 红包
 * @property int $popup_type 弹窗样式 0样式一（默认样式）1样式二
 */
class ShoppingRewardActivityModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * 客户端类型
     * @var string[]
     */
    public static $clientType = [
        '10' => 'H5',
        '20' => '微信公众号',
        '21' => '微信小程序'
    ];

    /**
     * 奖励类型
     * @var string[]
     */
    public static $rewardText = [
        '1' => '优惠券',
        '2' => '积分',
        '3' => '余额'
    ];

    /**
     * 商品限制
     * @var string[]
     */
    public static $goodsType = [
        '0' => '不限制',
        '1' => '指定以下商品参与',
        '2' => '指定以下商品不参与',
        '3' => '指定以下商品分类参与'
    ];

    /**
     * 会员限制
     * @var string[]
     */
    public static $memberType = [
        '0' => '全部会员',
        '1' => '会员等级',
        '2' => '会员标签',
    ];

    /**
     * 弹框样式
     * @var string[]
     */
    public static $popupType = [
        '0' => '样式一',
        '1' => '样式二'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_shopping_reward_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_type', 'member_type', 'send_type', 'credit', 'pick_times_type', 'pick_times_limit', 'job_id', 'status', 'send_count', 'is_deleted', 'popup_type'], 'integer'],
            [['start_time', 'end_time', 'created_at', 'stop_time'], 'safe'],
            [['balance'], 'number'],
            [['title', 'client_type', 'coupon_ids'], 'string', 'max' => 50],
            [['red_package'], 'string', 'max' => 255],
            [['reward'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => ' 标题',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'client_type' => '发放渠道',
            'goods_type' => '商品类型 0 不限制 1指定商品参与  2指定商品不参与 3 指定商品分类',
            'member_type' => '参与资格 0全部会员  1 会员等级 2会员标签',
            'send_type' => '发送结点 0 下单支付成功  1订单完成',
            'reward' => '奖励内容 1 优惠券  2积分 3余额',
            'coupon_ids' => '优惠券id',
            'credit' => '积分',
            'balance' => '余额',
            'pick_times_type' => '领取次数限制  0 不限制  1每人活动期间最多领取  2每人每天做多领取',
            'pick_times_limit' => '最多领取',
            'created_at' => '创建时间',
            'job_id' => '任务id',
            'status' => '活动状态  0 未开始或进行中 -1 停止 -2手动停止',
            'send_count' => '发送数量',
            'stop_time' => '停止时间',
            'is_deleted' => '是否删除',
            'red_package' => '红包',
            'popup_type' => '弹窗样式 0样式一（默认样式）1样式二'
        ];
    }

    /**
     * 保存商品限制
     * @param int $activityId
     * @param array $rule
     * @return bool
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveGoodsRule(int $activityId, array $rule)
    {
        $insertDetail = [];
        $fields = ['activity_id', 'goods_or_cate_id'];
        foreach ($rule as $item) {
            $insertDetail[] = [
                $activityId,
                $item
            ];
        }
        if (!empty($insertDetail)) {
            ShoppingRewardActivityGoodsRuleModel::batchInsert($fields, $insertDetail);
        }
        return true;
    }

    /**
     * 保存会员限制
     * @param int $activityId
     * @param array $rule
     * @return bool
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveMemberRule(int $activityId, array $rule)
    {
        $insertDetail = [];
        $fields = ['activity_id', 'level_or_group_id'];
        foreach ($rule as $item) {
            $insertDetail[] = [
                $activityId,
                $item
            ];
        }
        if (!empty($insertDetail)) {
            ShoppingRewardActivityMemberRuleModel::batchInsert($fields, $insertDetail);
        }
        return true;
    }

    /**
     * 检查时间段内是否有活动
     * @param string $startTime
     * @param string $endTime
     * @param int $exceptId
     * @param array $goodsId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkExistsByTime(string $startTime, string $endTime, int $exceptId = 0, array $goodsId = [])
    {
        $where = [
            'and',
            ['is_deleted' => 0],
            ['stop_time' => 0],
            [
                'and',
                [
                    'status' => 0
                ],
                [
                    'or',
                    [ // 开始时间不能在时间段内
                        'and',
                        ['<=', 'start_time', $startTime],
                        ['>=', 'end_time', $startTime],
                    ],
                    [ // 结束时间不能在时间段内
                        'and',
                        ['<=', 'start_time', $endTime],
                        ['>=', 'end_time', $endTime],
                    ],
                ]
            ]
        ];

        if (!empty($exceptId)) {
            $where[] = ['<>', 'id', $exceptId];
        }

        $activity = self::find()->where($where)->column();

        if (empty($activity)) {
            return false;
        }

        //查询是否有交集商品

        $goodsExist = ShoppingRewardActivityGoodsRuleModel::where([
            'goods_or_cate_id' => $goodsId,
            'activity_id' => $activity
        ])->exists();

        if ($goodsExist) {
            return true;
        }

        return false;
    }

    /**
     * 获取活动
     * @param int $clientType
     * @param array $goodsId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOpenActivity(int $clientType, $goodsId = [])
    {

        $activityId = ShoppingRewardActivityGoodsRuleModel::where([
            'goods_or_cate_id' => $goodsId
        ])->select(['activity_id'])->column();

        $activity = self::find()
            ->where(['status' => 0, 'is_deleted' => 0, 'id' => $activityId])
            ->andWhere(['<', 'start_time', DateTimeHelper::now()])
            ->andWhere(['>', 'end_time', DateTimeHelper::now()])
            ->first();
        if (empty($activity)) {
            return error('无活动');
        }
        $activityClientType = explode(',', $activity['client_type']);
        if (!in_array($clientType, $activityClientType)) {
            return error('当前渠道不支持');
        }

        return $activity;
    }

}