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

use shopstar\helpers\CryptHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\StringHelper;
use yii\db\ActiveRecord;

/**
 * 用户表模型类
 * Class UserModel
 * @package install\models
 * @author likexin
 * @property int $id
 * @property string|null $username
 * @property string $password
 * @property int $is_root
 * @property int $status
 * @property string|null $created_at
 */
class UserModel extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['is_root', 'status',], 'integer'],
            [['created_at', 'last_login'], 'safe'],
            [['username'], 'string', 'max' => 50],
            [['password', 'last_ip'], 'string', 'max' => 255],
            [['salt'], 'string', 'max' => 20],
        ];
    }

    /**
     * 创建超管
     * @param string $username 用户名
     * @param string $password 密码
     * @return bool|array
     */
    public static function createSuperAdmin(string $username, string $password): bool
    {
        // 删除之前的用户名和超管
        self::deleteAll([
            'or',
            ['username' => $username],
            ['is_root' => 1],
        ]);

        // 生成新用户
        $model = new self();

        // 生成新用户的盐值
        $salt = StringHelper::random(16);

        // 计算密码
        $passwordSalt = CryptHelper::passwordHash($password . $salt);

        $model->setAttributes([
            'username' => $username,
            'password' => $passwordSalt,
            'salt' => $salt,
            'is_root' => 1,
            'status' => 1,
            'create_at' => DateTimeHelper::now(),
        ]);

        // 执行保存
        if (!$model->save()) {
            return error(current($model->getErrors()));
        }

        return true;
    }

}