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

namespace shopstar\bases\model;

use shopstar\helpers\ArrayHelper;
use shopstar\helpers\ClientHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\StringHelper;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * 会话基类，所有会话继承此类(MemberSession\UserSession等)
 * Class BaseSession
 * @package shopstar\bases\model
 */
class BaseSession extends ActiveRecord
{

    /**
     * 生效时间 默认15天过期
     * @author likexin
     * @var float|int
     */
    private static $expireTime = 15 * 86400;

    /**
     * @var string 缓存前缀
     */
    protected static $cachePrefix = '';

    /**
     * 读取缓存Key
     * @param string $sessionId
     * @return string
     * @author likexin
     */
    public static function getCacheKey(string $sessionId)
    {
        return 'kdx_shop_' . (string)self::$cachePrefix . '_' . $sessionId;
    }

    /**
     * 设置缓存前缀
     * @param string $cachePrefix
     * @author likexin
     */
    protected static function setCachePrefix(string $cachePrefix)
    {
        self::$cachePrefix = $cachePrefix;
    }

    /**
     * 获取session数据
     * @param string $sessionId 会话ID
     * @param string $key 数据Key
     * @param string $defaultValue 默认数据
     * @param array $dbWhere 查询过滤条件
     * @return mixed
     * @author likexin
     */
    public static function baseGet(string $sessionId, string $key, $defaultValue = '', array $dbWhere = [])
    {
        $redis = \Yii::$app->redis;

        // 先读取redis缓存
        $data = $redis->get(self::getCacheKey($sessionId));

        // redis读取不到时读取mysql
        if (empty($data)) {
            $data = self::find()->where(['session_id' => $sessionId])->andWhere($dbWhere)->limit(1)->asArray()->one();
            if (!empty($data)) {

                // 判断数据库中的过期
                if ($data['expire_time'] != '0000-00-00 00:00:00' && $data['expire_time'] < DateTimeHelper::now()) {
                    self::deleteAll(['id' => $data['id']]);
                    $redis->del(self::getCacheKey($sessionId));
                    return false;
                }

                $redis->set(self::getCacheKey($sessionId), $data['data']);
            }
        }

        if (empty($data)) {
            return $defaultValue;
        }

        if (is_array($data)) {
            $newData = Json::decode($data['data']);
        } else {
            $newData = !empty($data) ? Json::decode($data) : '';
        }

        if (empty($newData)) {
            return $defaultValue;
        } elseif ($key == '') {
            return $newData;
        }

        return $key ? ($newData[$key] ?: $defaultValue) : $defaultValue;
    }

    /**
     * 设置session数据
     * @param string $sessionId 会话ID
     * @param string $key 数据Key
     * @param string $value 数据值
     * @param int $expireTime 过期时间
     * @param array $dbWhere
     * @param array $dbAttributes 数据字段
     * @return bool|mixed
     * @author likexin
     */
    public static function baseSet(string $sessionId, string $key = '', $value = '', int $expireTime = 0, array $dbWhere = [], array $dbAttributes = [])
    {
        $model = self::find()->where(['session_id' => $sessionId])->andWhere($dbWhere)->limit(1)->one();
        if (empty($model)) {
            $model = new static();
            $data = [
                'session_id' => $sessionId,
                'created_at' => DateTimeHelper::now(),
                'data' => Json::encode([$key => $value]),
            ];
            $model->setAttributes(array_merge($dbAttributes, $data));
        } else {
            if (!empty($key)) {
                $tempData = ArrayHelper::merge(Json::decode($model->data) ?: [], [$key => $value]);
                $model->setAttributes($dbAttributes);
                $model->data = Json::encode($tempData);
            }
        }

        // 过期时间
        if ($expireTime > 0) {
            $model->expire_time = date('Y-m-d H:i:s', time() + $expireTime);
        } elseif ($expireTime == 0) {
            $model->expire_time = date('Y-m-d H:i:s', time() + self::$expireTime);
        }

        if (!$model->save()) {
            return false;
        }

        $attributes = $model->getAttributes();
        unset($attributes['id']);

        $redis = \Yii::$app->redis;

        // 先读取redis缓存
        $set = $redis->set(self::getCacheKey($sessionId), $attributes['data']);

        // 添加时限
        $redis->expire(self::getCacheKey($sessionId), $expireTime > 0 ? $expireTime : self::$expireTime);

        return $set;
    }

    /**
     * 移除某个Key
     * @param string $sessionId 缓存ID
     * @param string $key 数据Key
     * @param array $dbWhere 查询过滤条件
     * @return bool
     * @author likexin
     */
    public static function baseRemove(string $sessionId, string $key = '', array $dbWhere = [])
    {
        $model = self::find()->where(['session_id' => $sessionId])->andWhere($dbWhere)->limit(1)->one();
        if (empty($model)) {
            return false;
        }

        $tempData = Json::decode($model->data);
        unset($tempData[$key]);
        $model->data = Json::encode($tempData);

        $redis = \Yii::$app->redis;

        $attributes = $model->getAttributes();
        unset($attributes['id']);

        $redis->set(self::getCacheKey($sessionId), $model->data);

        return $model->save();
    }

    /**
     * 清除session
     * @param string $sessionId
     * @param array $dbWhere
     * @author likexin
     */
    public static function clear(string $sessionId, array $dbWhere = [])
    {
        // 删除数据库
        $count = self::deleteAll([
            'and',
            [
                'session_id' => $sessionId,
            ],
            $dbWhere,
        ]);

        if ($count) {
            // 删除缓存
            $cacheKey = self::getCacheKey($sessionId);
            \Yii::$app->redis->del($cacheKey);
        }
    }

    /**
     * 创建SessionId
     * @param int $uid 用户ID User::$id 或 Member::$id
     * @param array $andWhere 查询过滤条件
     * @return string
     * @author likexin
     */
    public static function createSessionId(int $uid = 0, array $andWhere = [])
    {
        while (true) {
            $sessionId = md5(ClientHelper::getIp() . StringHelper::random(18) . time() . $uid);
            $sessionObject = self::find()->where(['session_id' => $sessionId])->andWhere($andWhere)->count();
            if (empty($sessionObject)) {
                return $sessionId;
            }
        }
    }

}
