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

use shopstar\bases\model\BaseSession;


/**
 *
 * @property int $id
 * @property int $member_id 会员ID
 * @property string $session_id 会话ID
 * @property int $client_type 访问客户端类型
 * @property string $data 记录的数据
 * @property string $created_at 创建时间
 * @property string $expire_time 过期时间
 */
class MemberSession extends BaseSession
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_session}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'client_type'], 'integer'],
            [['data'], 'string'],
            [['created_at'], 'safe'],
            [['session_id'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员ID',
            'session_id' => '会话ID',
            'client_type' => '访问客户端类型',
            'data' => '记录的数据',
            'created_at' => '创建时间',
            'expire_time' => '过期时间',
        ];
    }

    /**
     * 获取
     * @param string $sessionId 会话ID
     * @param string $key 数据Key
     * @param string $defaultValue 默认值
     * @return mixed
     * @author likexin
     */
    public static function get(string $sessionId, string $key, $defaultValue = '')
    {
        self::setCachePrefix('member_session_');
        return parent::baseGet($sessionId, $key, $defaultValue, ['>', 'client_type', 0]);
    }

    /**
     * 设置
     * @param string $sessionId 会话ID
     * @param int $memberId 会员ID
     * @param int $clientType 客户端类型
     * @param string $key 数据Key
     * @param string $value 数据值
     * @param int $expireTime 过期时间
     * @return bool|mixed
     * @author likexin
     */
    public static function set(string $sessionId, int $memberId, int $clientType = 0, string $key = '', $value = '', int $expireTime = 0)
    {
        self::setCachePrefix('member_session_');

        return parent::baseSet($sessionId, $key, $value, $expireTime, [
            'member_id' => $memberId,
        ], [
            'member_id' => $memberId,
            'client_type' => $clientType,
        ]);
    }

    /**
     * 移除
     * @param string $sessionId 会话ID
     * @param string $key 数据Key
     * @return bool
     * @author likexin
     */
    public static function remove(string $sessionId, string $key = '')
    {
        self::setCachePrefix('member_session_');

        return parent::baseRemove($sessionId, $key, []);
    }

    /**
     * 删除会员session
     * @param int|array $memberId
     * @param int $clientType
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteMemberSession($memberId, int $clientType = 0)
    {
        $where = [
            'member_id' => $memberId,
        ];

        //删除单一渠道
        if (!empty($clientType)) {
            $where['client_type'] = $clientType;
        }

        $memberSession = MemberSession::find()->where($where)->all();

        $redis = \Yii::$app->redis;
        self::setCachePrefix('member_session_');
        foreach ($memberSession as $memberSessionIndex => $memberSessionItem) {
            $memberSessionItem->delete();
            //删除redis
            $redis->del(parent::getCacheKey($memberSessionItem['session_id']));
        }

        return true;
    }


}
