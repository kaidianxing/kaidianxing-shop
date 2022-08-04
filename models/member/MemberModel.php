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

namespace shopstar\models\member;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\MemberTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\pc\MemberWechatPcModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\shop\ShopSettings;
use yii\db\ActiveQuery;

class MemberModel extends BaseActiveRecord
{
    /**
     * 黑名单
     * @var array
     */
    public static $isBlack = ['否', '是'];

    /**
     * 关注状态
     * @var array
     */
    public static $isFollow = ['未关注', '已关注', '已取消'];

    /**
     * 导出会员字段
     * @var array
     */
    public static $memberColumns = [
        ['title' => '会员ID', 'field' => 'id', 'width' => 12],
        ['title' => '会员昵称', 'field' => 'nickname', 'width' => 24],
        ['title' => '真实姓名', 'field' => 'realname', 'width' => 18],
        ['title' => '手机号', 'field' => 'mobile', 'width' => 18],
        ['title' => '会员等级', 'field' => 'level_name', 'width' => 24],
        ['title' => '标签组', 'field' => 'group_name', 'width' => 24],
        ['title' => '积分', 'field' => 'credit', 'width' => 24],
        ['title' => '余额', 'field' => 'balance', 'width' => 24],
        ['title' => '成交订单数', 'field' => 'order_count', 'width' => 18],
        ['title' => '成交金额', 'field' => 'money_count', 'width' => 24],
        ['title' => '黑名单', 'field' => 'is_black_name', 'width' => 12],
        ['title' => '注册时间', 'field' => 'created_at', 'width' => 24]
    ];

    /**
     * 查找字段
     * @var string[]
     */
    private static $select = [
        'm.id',
        'm.avatar',
        'm.nickname',
        'm.realname',
        'm.level_id',
        'm.mobile',
        'm.password',
        'm.credit',
        'm.balance',
        'm.source',
        'm.last_time',
        'm.is_deleted',
        'm.last_time',
        'm.created_at',
        'm.is_black',
        'm.remark',
        'm.is_bind_mobile',
        'm.birth_year',
        'm.birth_month',
        'm.birth_day',
        'm.inviter',
        'm.province',
        'm.city',
        'level.level_name',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['credit', 'is_black', 'level_id', 'source', 'is_bind_mobile', 'inviter', 'is_deleted'], 'integer'],
            [['balance', 'credit_limit'], 'number'],
            [['remark'], 'string'],
            [['last_time', 'invite_time', 'created_at', 'updated_at'], 'safe'],
            [['avatar', 'nickname', 'password'], 'string', 'max' => 191],
            [['realname', 'mobile', 'salt'], 'string', 'max' => 20],
            [['birth_year'], 'string', 'max' => 4],
            [['birth_month', 'birth_day'], 'string', 'max' => 2],
            [['province', 'city'], 'string', 'max' => 120],
        ];
    }


    public function logAttributeLabels()
    {
        return [
            'id' => 'id',
            'avatar' => '头像',
            'nickname' => '用户昵称',
            'realname' => '用户真实名',
            'mobile' => '手机号',
            'credit' => '积分',
            'balance' => '余额',
            'is_black' => '黑名单',
            'level_name' => '等级名称',
            'group_name' => '分组名称',
            'source' => '渠道',
            'commission_info' => [
                'title' => '分销信息',
                'item' => [
                    'level_name' => '分销等级名称',
                    'commission_total' => '累计佣金',
                    'commission_pay' => '已提现佣金',
                    'become_time' => '成为分销商时间',
                    'status' => '分销商状态',
                    'agent_name' => '上级',
                    'agent_id' => '上级id',
                    'child_id' => '下级id',
                ],
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'avatar' => '头像',
            'nickname' => '用户昵称',
            'realname' => '用户真实名',
            'mobile' => '手机号',
            'password' => '密码',
            'salt' => '盐',
            'credit' => '积分',
            'balance' => '余额',
            'is_black' => '是否黑名单 0:否;1:是',
            'level_id' => '等级id',
            'birth_year' => '出生年',
            'birth_month' => '出生月',
            'birth_day' => '出生日',
            'remark' => '备注',
            'province' => '所在省',
            'city' => '所在市',
            'credit_limit' => '积分上限  0:读取系统设置;其他:自定义;',
            'source' => '用户来源 0: 未知;10:H5;20: 微信公众号;21: 微信小程序;4:抖音小程序;5:支付宝小程序;',
            'is_bind_mobile' => '是否绑定手机号 0:否;1:是;',
            'last_time' => '最后一次登录时间',
            'inviter' => '邀请人id',
            'invite_time' => '邀请时间',
            'is_deleted' => '是否废弃 0否 1是',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 标签组映射关系
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getGroupsMap()
    {
        return $this->hasMany(MemberGroupMapModel::class, ['member_id' => 'id']);
    }

    /**
     * 公众号会员
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getWechatMember()
    {
        return $this->hasOne(MemberWechatModel::class, ['member_id' => 'id']);
    }

    /**
     * 小程序会员
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getWxappMember()
    {
        return $this->hasOne(MemberWxappModel::class, ['member_id' => 'id']);
    }

    /**
     * 抖音小程序会员
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getDouyinMember()
    {
        return $this->hasOne(MemberDouyinModel::class, ['member_id' => 'id']);
    }

    /**
     * 头条小程序会员
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getToutiaoMember()
    {
        return $this->hasOne(MemberToutiaoModel::class, ['member_id' => 'id']);
    }

    /**
     * 标签组关系
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getGroups()
    {
        return $this->hasMany(MemberGroupModel::class, ['id' => 'group_id'])->via('groupsMap');
    }

    /**
     * 等级关系
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getLevel()
    {
        return $this->hasOne(MemberLevelModel::class, ['id' => 'level_id']);
    }

    /**
     * 优惠券关系
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getMemberCoupons()
    {
        return $this->hasMany(CouponMemberModel::class, ['member_id' => 'id']);
    }

    /**
     * 订单关系
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getOrders()
    {
        return $this->hasMany(CouponMemberModel::class, ['member_id' => 'id']);
    }

    /**
     * 获取用户信息
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberDetail(int $memberId)
    {
        $member = self::find()
            ->alias('m')
            ->select(self::$select)
            ->with(['groups'])
            ->leftJoin(MemberGroupMapModel::tableName() . ' gm', 'gm.member_id=m.id')
            ->leftJoin(MemberLevelModel::tableName() . ' level', 'level.id=m.level_id')
            ->where(['m.id' => $memberId])
            ->first();
        if (empty($member)) {
            return error('用户不存在');
        }
        // 获取所有渠道
        $member['all_source'] = self::getMemberAllSource($memberId, $member['source'], $member['mobile']);

        // 判断密码是否设置
        $member['password_set'] = $member['password'] != '' ? 1 : 0;
        unset($member['password']);

        // 获取用户关注状态
        if ($member['source'] == ClientTypeConstant::CLIENT_WECHAT) {
            $member['is_follow'] = MemberWechatModel::getMemberFollow($memberId);
        } else {
            $member['is_follow'] = MemberTypeConstant::MEMBER_NOT_FOLLOW;
        }
        // 获取默认等级
        $defaultLevelId = MemberLevelModel::getDefaultLevelId();
        if ($member['level_id'] == $defaultLevelId) {
            $member['is_default_level'] = 1;
        }
        $member['group_name'] = implode(',', array_column($member['groups'], 'group_name'));
        $member['is_black_name'] = self::$isBlack[$member['is_black']];
        $member['is_follow_name'] = self::$isFollow[$member['is_follow']];

        return $member;
    }

    /**
     * 获取可用的会员信息(未删除, 未加入黑名单)
     * @param int $memberId
     * @return array|null
     * @author nizengchao
     */
    public static function getCanUserMember(int $memberId = 0): ?array
    {
        return self::find()
            ->where([
                'id' => $memberId,
                'is_black' => 0,
                'is_deleted' => 0,
            ])
            ->select([
                'id',
                'avatar',
                'nickname',
                'realname',
                'level_id',
                'mobile',
                'password',
                'credit',
                'balance',
                'source',
                'last_time',
                'is_deleted',
                'last_time',
                'created_at',
                'is_black',
                'remark',
                'is_bind_mobile',
                'birth_year',
                'birth_month',
                'birth_day',
                'inviter',
            ])
            ->first();
    }

    /**
     * 检查手机号是否可用
     * @param int $memberId
     * @param string $mobile
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkMobile(int $memberId, string $mobile)
    {
        if (!ValueHelper::isMobile($mobile)) {
            return error('手机号格式错误');
        }
        $isExists = MemberModel::find()
            ->where(['mobile' => $mobile])
            ->andWhere(['is_deleted' => 0])
            ->first();
        if ($isExists && $isExists['id'] != $memberId) {
            return error('手机号已存在');
        }
        return true;
    }

    /**
     * 获取用户当前余额
     * @param int $id
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getBalance(int $id)
    {
        $member = self::find()
            ->select('balance')
            ->where(['id' => $id, 'is_deleted' => 0])
            ->first();
        return $member['balance'] ?? 0;
    }

    /**
     * 获取用户当前余额
     * @param int $id
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCredit(int $id)
    {
        $member = self::find()
            ->select('credit')
            ->where(['id' => $id, 'is_deleted' => 0])
            ->first();
        return $member['credit'] ?? 0;
    }

    /**
     * 最近浏览时间
     * 商城首页、会员中心、分类、商品详情
     * 30分钟记录一次
     * @param int $id
     * @param string $sessionId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateLastTime(int $id, string $sessionId)
    {
        $redis = \Yii::$app->redis;
        $key = 'kdx_shop_' . '_' . $sessionId . '_last_time';
        // 缓存是否存在 不存在则更新
        $isExists = $redis->get($key);
        if (!$isExists) {
            // 重新设置缓存时间 30分钟
            $expireTime = 1800;
            $redis->setex($key, $expireTime, DateTimeHelper::now());
            try {
                MemberModel::updateAll(['last_time' => DateTimeHelper::now()], ['id' => $id]);
            } catch (\Throwable $exception) {
                // 不作处理
            }
        }
        return true;
    }

    /**
     * 保存会员
     * @param array $member 需要保存的数据
     * @param bool $getInfo 是否返回会员数据
     * @return array|int
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveMember(array $member, bool $getInfo = false)
    {
        if (!empty($member['id'])) {
            $model = self::findOne(['id' => $member['id'], 'is_deleted' => 0]);
            if (empty($model)) {
                return error('会员不存在');
            }

            //如果会员已经注册，则释放来源
            unset($member['source']);
        }

        $isAdd = false;
        if (empty($model)) {
            $model = new self();
            $isAdd = true;
        }

        $model->setAttributes($member);
        if ($model->save() === false) {
            return error('保存失败');
        }

        //注册会员后处理
        self::afterSaveMember($model, $isAdd);

        if ($getInfo) {
            return $model->getAttributes();
        }

        return $model->id;
    }

    /**
     * 保存会员后处理，
     * @param MemberModel $member
     * @param bool $isAdd 是否是新增
     * @author 青岛开店星信息技术有限公司
     */
    private static function afterSaveMember(self $member, bool $isAdd)
    {
        //设置会员默认等级
        if ($isAdd) {
            $memberDefaultLevel = MemberLevelModel::getDefaultLevel();
            if (!empty($memberDefaultLevel)) {
                $member->level_id = $memberDefaultLevel['id'];
            }

            $member->save();
        }

    }


    /**
     * 校验手机号是否绑定
     * @param $mobile
     * @return bool
     */
    public static function checkMobileIsBind($mobile)
    {
        $result = self::find()
            ->where(['mobile' => $mobile, 'is_deleted' => 0])
            ->one();
        return $result === null;
    }


    /**
     * 获取积分排名
     * @param $memberId
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCreditRanking($memberId)
    {
        // 获取当前积分
        $memberCredit = self::getCredit($memberId);
        // 比我高的
        return self::find()
                ->where(['is_black' => 0, 'is_deleted' => 0])
                ->andWhere(['>', 'credit', $memberCredit])
                ->count() + 1;
    }

    /**
     * 获取用户所有来源渠道
     * @param int $memberId
     * @param int $firstSource 会员表上的来源 表示第一来源
     * @param string $mobile 手机号 判断h5 来源
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberAllSource(int $memberId, int $firstSource, string $mobile = '')
    {
        $all = [
            ClientTypeConstant::CLIENT_H5 => [ // H5
                'source' => ClientTypeConstant::CLIENT_H5,
                'is_register' => 0,
            ],
            ClientTypeConstant::CLIENT_WECHAT => [ // 微信公众号
                'source' => ClientTypeConstant::CLIENT_WECHAT,
                'is_register' => 0,
            ],
            ClientTypeConstant::CLIENT_WXAPP => [ // 微信小程序
                'source' => ClientTypeConstant::CLIENT_WXAPP,
                'is_register' => 0,
            ],
            ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO => [ // 头条小程序
                'source' => ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO,
                'is_register' => 0,
            ],
            ClientTypeConstant::CLIENT_BYTE_DANCE_DOUYIN => [ // 抖音小程序
                'source' => ClientTypeConstant::CLIENT_BYTE_DANCE_DOUYIN,
                'is_register' => 0,
            ],
            ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE => [ // 头条极速版小程序
                'source' => ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE,
                'is_register' => 0,
            ],
            ClientTypeConstant::CLIENT_PC => [ // pc
                'source' => ClientTypeConstant::CLIENT_PC,
                'is_register' => 0,
            ],
        ];

        // H5 只要绑定手机号  就算
        if (!empty($mobile)) {
            $all[ClientTypeConstant::CLIENT_H5]['is_register'] = 1;
        }
        // 公众号
        $isWechat = MemberWechatModel::findOne(['member_id' => $memberId, 'is_deleted' => 0]);
        if (!empty($isWechat)) {
            $all[ClientTypeConstant::CLIENT_WECHAT]['is_register'] = 1;
        }
        // 微信小程序
        $isWxapp = MemberWxappModel::findOne(['member_id' => $memberId, 'is_deleted' => 0]);
        if (!empty($isWxapp)) {
            $all[ClientTypeConstant::CLIENT_WXAPP]['is_register'] = 1;
        }
        // 头条小程序
        $isToutiao = MemberToutiaoModel::findOne(['member_id' => $memberId, 'is_deleted' => 0]);
        if (!empty($isToutiao)) {
            $all[ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO]['is_register'] = 1;
        }
        // 头条极速版小程序
        $isToutiaoLite = MemberToutiaoLiteModel::findOne(['member_id' => $memberId, 'is_deleted' => 0]);
        if (!empty($isToutiaoLite)) {
            $all[ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE]['is_register'] = 1;
        }
        // 抖音小程序
        $isDouyin = MemberDouyinModel::findOne(['member_id' => $memberId, 'is_deleted' => 0]);
        if (!empty($isDouyin)) {
            $all[ClientTypeConstant::CLIENT_BYTE_DANCE_DOUYIN]['is_register'] = 1;
        }
        // PC
        $isPc = MemberWechatPcModel::findOne(['member_id' => $memberId,  'is_deleted' => 0]);
        if (!empty($isPc)) {
            $all[ClientTypeConstant::CLIENT_PC]['is_register'] = 1;
        }


        return array_values($all);
    }

    /**
     * 获取会员下所有账号
     * @param int $memberId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberAllInfo(int $memberId)
    {
        return MemberModel::find()
            ->with([
                'wechatMember' => function ($query) {
                    $query->where(['is_deleted' => 0])->select([
                        'member_id',
                        'openid'
                    ]);
                },
                'wxappMember' => function ($query) {
                    $query->where(['is_deleted' => 0])->select([
                        'member_id',
                        'openid'
                    ]);
                },
                'douyinMember' => function ($query) {
                    $query->where(['is_deleted' => 0])->select([
                        'member_id',
                        'openid'
                    ]);
                },
                'toutiaoMember' => function ($query) {
                    $query->where(['is_deleted' => 0])->select([
                        'member_id',
                        'openid'
                    ]);
                },
            ])
            ->where(['id' => $memberId, 'is_deleted' => 0])
            ->select([
                'id',
                'nickname',
                'mobile'
            ])
            ->asArray()
            ->all();
    }

    /**
     * 判断用户是被删除
     * @param int|array $memberId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkDeleted($memberId)
    {
        // 存在删除的用户
        return !self::find()->where(['id' => $memberId, 'is_deleted' => 1])->exists();
    }

    /**
     * 积分/余额变动
     * @param int $memberId
     * @param float $num 变动金额
     * @param int $operator 操作人  >0后台管理员  0本人
     * @param string $type
     * @param int $changeType 0固定 1充值 2扣除
     * @param string $remark 说明
     * @param int $recordType 记录类型
     * @param array $options 扩展字段
     * @return MemberModel|array
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateCredit(int $memberId, float $num, int $operator = 0, string $type = 'credit', int $changeType = 1, string $remark = '', int $recordType = 10, array $options = [])
    {
        $options = array_merge([
            'order_id' => 0,
            'get_record' => false,
        ], $options);


        $sum = 0; // 合计金额

        if (($changeType != MemberTypeConstant::RECHARGE_CHANGE_TYPE_FIXED && empty($num)) || $num < 0) {
            return error('充值金额不能为空或负数');
        }

        $member = self::findOne(['id' => $memberId]);
        if (empty($member)) {
            return error('用户不存在');
        }

        // 积分
        if ($type == 'credit') {
            // 积分限额 获取系统配置 默认数据库可存最大值
            $creditLimit = 9999999;
            $creditSet = ShopSettings::get('sysset.credit');
            if (!empty($creditSet) && $creditSet['credit_limit_type'] == 2) {
                $creditLimit = $creditSet['credit_limit'] ?? 0;
            }

            // 0固定 1充值 2扣除
            if ($changeType == MemberTypeConstant::RECHARGE_CHANGE_TYPE_ADD) {
                $sum = bcadd($member->credit, $num, 2);
            } else if ($changeType == MemberTypeConstant::RECHARGE_CHANGE_TYPE_SUB) {
                $sum = bcsub($member->credit, $num, 2);
            } else {
                $sum = bcadd($num, 0, 2);
            }

            // 后台操作
            if ($operator > 0) {
                if ($sum < 0) {
                    $sum = 0;
                } else if (bccomp($sum, '9999999', 2) > 0) {
                    // 积分超过上限 置为最大
                    $sum = 9999999;
                    // 变动数量
                    $num = bcsub('9999999', $member->credit, 2);
                }
            } else {
                // 用户操作        如果是充值的话   (扣除不需要
                if ($sum > 0 && bccomp($sum, $creditLimit, 2) > 0 && $changeType == 1) {
                    // 超过限额 （不抛异常）
                    // 如果原来积分就大于限额，积分不变
                    if (bccomp($member->credit, $creditLimit, 2) >= 0) {
                        $sum = $member->credit;
                        $num = 0;
                    } else {
                        // 可充值最大积分
                        $sum = $creditLimit;
                        $num = bcsub($creditLimit, $member->credit, 2);
                    }
                } else if ($sum < 0) {
                    return error('积分不足');
                }
            }


            // 更新
            $result = MemberModel::updateAll([
                'credit' => (int)$sum
            ], [
                'id' => $memberId,
                'credit' => $member->credit
            ]);

            if (!$result) {
                return error('积分发生变动，请重试');
            }
            $member->credit = (int)$sum;


        } else {
            // 余额
            // 0固定 1充值 2扣除
            if ($changeType == MemberTypeConstant::RECHARGE_CHANGE_TYPE_ADD) {
                $sum = bcadd($member->balance, $num, 2);
            } else if ($changeType == MemberTypeConstant::RECHARGE_CHANGE_TYPE_SUB) {
                $sum = bcsub($member->balance, $num, 2);
            } else {
                $sum = bcadd($num, 0, 2);
            }

            // 后台操作
            if ($operator > 0 && $sum < 0) {
                $sum = 0;
            } else if ($sum < 0) {
                return error('余额不足');
            }
            if (bccomp($sum, '9999999.99', 2) > 0) {
                return error('余额超过限额');
            }
            $result = MemberModel::updateAll([
                'balance' => $sum
            ], [
                'id' => $memberId,
                'balance' => $member->balance
            ]);


            if (!$result) {
                return error('余额发生变动，请重试');
            }
            $member->balance = (int)$sum;

        }

        $num = $changeType == 2 ? -$num : $num;
        // 日志信息
        $recordData = [
            'member_id' => $memberId,
            'type' => $type == 'credit' ? 1 : 2,
            'num' => $num,
            'operator' => $operator,
            'present_credit' => $sum,
            'remark' => $remark,
            'status' => $recordType,
            'order_id' => $options['order_id'],
        ];

        // 记录日志
        $record = new MemberCreditRecordModel();
        $record->setAttributes($recordData);
        if ($record->save() === false) {
            return error('日志保存失败');
        }

        //获取记录
        if ($options['get_record']) {
            return ['record' => $record->toArray(), 'member' => $member];
        }

        return $member;
    }

    /**
     * 获取随机会员
     * @param int $number
     * @param array $options
     * @return array
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getRandMember(int $number, array $options = []): array
    {
        $options = array_merge([
            'andWhere' => [],
            'select' => [
                'id',
            ]
        ], $options);

        $member = self::find()
            ->where($options['andWhere'])
            ->select($options['select'])
            ->limit($number)
            ->orderBy('RAND()')
            ->get();

        //判断是否满足需要的用户数量 如果不满足，则在当前的数据基础上随机再获取条数补充
        $memberCount = count($member);

        //判断是否获取人数足够 不过不够则补充
        if ($memberCount < $number) {

            //取差
            $diff = $number - $memberCount;
            if ($diff < $memberCount) {

                //随机获取
                $copyMember = array_rand($member, $diff);

                //合并数据
                $member = array_merge($member, $copyMember);
            } else {

                //需要的数量-当前所有的会员数/当前所有的会员数 并向上取整得到合并次数
                $divisor = ceil(round2(($number - $memberCount) / $memberCount));

                $newMember = $member;

                for ($i = 1; $i <= $divisor; $i++) {

                    //追加数组
                    $newMember = array_merge($newMember, $member);
                }

                $member = $newMember;
            }
        }

        return (array)array_slice((array)$member, 0, $number);
    }


    /**
     * 获取第三方客服需要的个人详细信息
     * @param int $memberId
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberInfo(int $memberId)
    {
        $memberInfo = MemberModel::find()
            ->alias('m')
            ->leftJoin(MemberLevelModel::tableName() . ' ml', 'ml.id=m.level_id')
            ->leftJoin(MemberGroupMapModel::tableName() . ' mgm', 'mgm.member_id=m.id')
            ->leftJoin(MemberGroupModel::tableName() . ' mg', 'mg.id=mgm.group_id')
            ->leftJoin(CommissionAgentModel::tableName() . ' ca', 'ca.member_id=m.id')
            ->leftJoin(CommissionLevelModel::tableName() . ' cl', 'cl.id=ca.level_id')
            ->where(['m.id' => $memberId])
            ->select([
                'm.id',
                'm.nickname',
                'm.created_at',
                'm.realname',
                'm.mobile',
                'ml.level_name',
                'mg.group_name',
                'cl.name commission_level_name',
                'm.credit',
                'm.balance',
            ])
            ->first();
        foreach ($memberInfo as $key => &$value) {
            if ($key == 'commission_level_name' && empty($value)) {
                $value = '非分销商';
            }
        }
        return $memberInfo;
    }

    /**
     * 通过手机号查询
     * @param int $mobile
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberInfoToMobile(int $mobile): array
    {
        $info = MemberModel::find()
            ->where(['mobile' => trim($mobile), 'is_deleted' => 0])
            ->select(['id', 'avatar', 'nickname', 'realname', 'mobile', 'credit', 'balance', 'is_black', 'level_id', 'source', 'created_at', 'inviter'])
            ->first();
        if ($info) {
            $info['source_name'] = ClientTypeConstant::getText($info['source']);
            $info['group_name'] = MemberGroupModel::find()
                    ->alias('group')
                    ->leftJoin(MemberGroupMapModel::tableName() . ' group_map', 'group_map.group_id = group.id')
                    ->where(['group_map.member_id' => $info['id']])
                    ->select('group.group_name')
                    ->first()['group_name'] ?? '';
            $levelList = MemberLevelModel::find()
                ->select('id, level_name')
                ->indexBy('id')
                ->get();
            $info['level_name'] = $levelList[$info['level_id']]['level_name'] ?? '';
        }
        return $info ?? [];
    }

}
