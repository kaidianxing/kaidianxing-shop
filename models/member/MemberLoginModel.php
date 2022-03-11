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
use shopstar\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%member}}".
 *
 * @property int $id
 * @property string $avatar 头像
 * @property string $nickname 用户昵称
 * @property string $realname 用户真实名
 * @property string $mobile 手机号
 * @property string $password 密码
 * @property string $salt 盐
 * @property int $credit 积分
 * @property string $balance 余额
 * @property int $is_black 是否黑名单 0:否;1:是
 * @property int $level_id 等级id
 * @property string $birth_year 出生年
 * @property string $birth_month 出生月
 * @property string $birth_day 出生日
 * @property string $remark 备注
 * @property string $province 所在省
 * @property string $city 所在市
 * @property string $credit_limit 积分上限  0:读取系统设置;其他:自定义;
 * @property int $source 用户来源 0: 未知;10:H5;20: 微信公众号;21: 微信小程序;4:抖音小程序;5:支付宝小程序;
 * @property int $is_bind_mobile 是否绑定手机号 0:否;1:是;
 * @property string $last_time 最后一次登录时间
 * @property int $inviter 邀请人id
 * @property string $invite_time 邀请时间
 * @property int $is_deleted 是否废弃 0否 1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class MemberLoginModel extends BaseActiveRecord
{
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

    /***
     * 登录生成sessionid
     * @param $memberId
     * @param string $sessionId
     * @param array $userInfo
     * @param int $clientType
     * @return string
     * @author 青岛开店星信息技术有限公司
     * @func login
     */
    public static function login(int $memberId, string $sessionId, array $userInfo, int $clientType)
    {
        if (empty($sessionId)) {
            return false;
        }

        //获取会员中的某几个字段
        $sessionData = ArrayHelper::Intercept($userInfo, [
            'id',
            'avatar',
            'realname',
            'mobile',
            'level_id',
            'is_deleted',
            'is_black'
        ]);

        MemberSession::set($sessionId, $memberId, $clientType, 'member', $sessionData);
        return $sessionId;
    }
}
