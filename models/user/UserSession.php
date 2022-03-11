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

namespace shopstar\models\user;

use shopstar\bases\model\BaseSession;
use shopstar\helpers\SessionHelper;

/**
 * 用户会话实体类
 * This is the model class for table "{{%user_session}}".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $session_id 会话ID
 * @property int $client_type 访问客户端类型
 * @property string $data 记录的数据
 * @property string $created_at 创建时间
 * @property string $expire_time 失效时间
 */
class UserSession extends BaseSession
{

    /**
     * @var string 缓存前缀
     */
    protected static $cachePrefix = 'user_session';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_session}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'client_type'], 'integer'],
            [['data'], 'required'],
            [['data'], 'string'],
            [['created_at', 'expire_time'], 'safe'],
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
            'user_id' => '用户ID',
            'session_id' => '会话ID',
            'client_type' => '访问客户端类型',
            'data' => '记录的数据',
            'created_at' => '创建时间',
            'expire_time' => '失效时间',
        ];
    }

    /**
     * 获取Session
     * @param string $sessionId
     * @param string $key
     * @param string $defaultValue
     * @return false|mixed|string
     * @author likexin
     */
    public static function get(string $sessionId, string $key, $defaultValue = '')
    {
        return parent::baseGet($sessionId, $key, $defaultValue, ['>', 'user_id', 0]);
    }

    /**
     * 设置Session
     * @param string $sessionId
     * @param int $userId
     * @param string $key
     * @param $value
     * @param int $expireTime
     * @param int $clientType
     * @param array $dbAttributes
     * @return bool|mixed
     * @author likexin
     */
    public static function set(string $sessionId, int $userId, string $key, $value, int $expireTime = 0, int $clientType = 0, array $dbAttributes = [])
    {
        if (!empty($clientType)) {
            $dbAttributes['client_type'] = $clientType;
        }
        $dbAttributes['user_id'] = $userId;

        return parent::baseSet($sessionId, $key, $value, $expireTime, [
            'user_id' => $userId,
        ], $dbAttributes);
    }

    /**
     * 移除Session
     * @param string $sessionId
     * @param string $key
     * @return bool
     * @author likexin
     */
    public static function remove(string $sessionId, string $key)
    {
        self::setCachePrefix('user_session');
        return parent::baseRemove($sessionId, $key);
    }

    /**
     * 根据用户创建Session-Id
     * @param UserModel $user
     * @return string
     * @author likexin
     */
    public static function createSessionIdByUser(UserModel $user)
    {
        /**
         * @var self $session
         */
        $session = self::find()->where(['user_id' => $user->id])->one();
        if ($session) {
            return $session->session_id;
        }

        $sessionId = self::createSessionId($user->id);
        self::set($sessionId, $user->id, 'user', [
            'id' => $user->id,
            'username' => $user->username,
            'is_root' => $user->is_root,
        ]);

        return $sessionId;
    }

}