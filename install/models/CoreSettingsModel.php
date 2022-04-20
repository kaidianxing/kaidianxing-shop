<?php
/**
 * 开店星商城系统1.0
 * @author 青岛开店星信息技术有限公司
 * @copyright Copyright (c) 2015-2021 Qingdao ShopStar Information Technology Co., Ltd.
 * @link https://www.kaidianxing.com
 * @warning This is not a free software, please get the license before use.
 * @warning 这不是一个免费的软件，使用前请先获取正版授权。
 */

namespace install\models;

use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\Json;

/**
 * 系统设置表模型类
 * Class UserModel
 * @package install\models
 * @author likexin
 * @property string $key
 * @property string|null $value
 */
class CoreSettingsModel extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%core_settings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['key', 'value'], 'required'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 50],
            [['key'], 'unique'],
        ];
    }

    /**
     * 获取设置
     * @param string $key 设置key
     * @param array $defaultValue 默认值
     * @return array
     */
    public static function get(string $key, array $defaultValue = []): array
    {
        $dbValue = static::find()->select('value')->where(['key' => $key])->limit(1)->scalar();
        if (empty($dbValue)) {
            return $defaultValue;
        }

        return Json::decode($dbValue);
    }

    /**
     * 保存设置
     * @param string $key 设置key
     * @param array $value 设置值
     * @return array|bool
     */
    public static function set(string $key, array $value)
    {
        try {

            $keys = ['`key`', '`value`'];
            $values = [
                ':key' => $key,
                ':value' => Json::encode($value)
            ];

            $keys = implode(',', $keys);
            $params = implode(',', array_keys($values));

            // 更新数据库
            self::getDb()->createCommand("REPLACE INTO " . static::tableName() . " ({$keys}) VALUES ({$params})", $values)->execute();

        } catch (Exception $exception) {
            return error($exception->getMessage());
        }

        return true;
    }

}