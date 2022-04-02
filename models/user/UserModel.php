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

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\user\UserIsDeleteConstant;
use shopstar\exceptions\UserException;
use shopstar\helpers\ClientHelper;
use shopstar\helpers\CryptHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\StringHelper;
use shopstar\helpers\ValueHelper;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property string $username 账号
 * @property string $password 密码
 * @property string $salt 签名
 * @property int $is_root 是否是超级管理员0否1是
 * @property int $status 用户状态
 * @property string $created_at 创建时间
 * @property string $last_login 最后登录时间
 * @property string $last_ip 最后登录ip
 * @property int $group_id 分组id
 * @property int $is_deleted 是否已删除
 * @property string $delete_time 删除时间
 */
class UserModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_root', 'status', 'group_id', 'is_deleted'], 'integer'],
            [['created_at', 'last_login', 'delete_time'], 'safe'],
            [['username'], 'string', 'max' => 50],
            [['password', 'last_ip'], 'string', 'max' => 255],
            [['salt'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => '账号',
            'password' => '密码',
            'salt' => '签名',
            'is_root' => '是否是超级管理员0否1是',
            'status' => '用户状态',
            'created_at' => '创建时间',
            'last_login' => '最后登录时间',
            'last_ip' => '最后登录ip',
            'group_id' => '分组id',
            'is_deleted' => '是否已删除',
            'delete_time' => '删除时间',
        ];
    }

    /**
     * 标签组映射关系
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getProfile(): ActiveQuery
    {
        return $this->hasOne(UserProfileModel::class, ['user_id' => 'id']);
    }

    /**
     * 获取单个
     * @param int $userId
     * @param array $options
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getOne(int $userId, array $options = []): array
    {
        $options = array_merge([
            'select' => [],
            'profile' => true,
            'profileSelect' => '*',
        ], $options);

        $userModel = self::where(['id' => $userId])->select($options['select']);

        if ($options['profile']) {
            $userModel->with(['profile' => function ($query) use ($options) {
                $query->select($options['profileSelect']);
            }]);
        }

        return $userModel->first() ?: [];
    }

    /**
     * 检测超管密码
     * @param string $password
     * @return bool
     * @author 青岛开店星信息技术有限公司.
     */
    public static function checkRootPassword(string $password): bool
    {
        //查找超管
        $userModel = UserModel::where(['is_root' => 1])->one();

        //检测超管密码
        return UserModel::checkUserPassword($userModel->id, $password);
    }

    /**
     * 检测用户密码是否正确
     * @param int $userId 用户ID
     * @param string $password 用户密码
     * @return bool
     */
    public static function checkUserPassword(int $userId, string $password)
    {
        // 查询用户
        $user = UserModel::find()
            ->where([
                'id' => $userId,
            ])
            ->select(['salt', 'password'])
            ->first();
        if (empty($user)) {
            return false;
        }

        return CryptHelper::passwordVerify($password . $user['salt'], $user['password']);
    }


    /**
     * 校验用户是否存在
     * @param $username
     * @return array|UserModel|null|\yii\db\ActiveRecord
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkUsername($username)
    {
        $result = self::find()
            ->where([
                'username' => $username,
                'is_deleted' => [
                    UserIsDeleteConstant::NOT_IS_DELETE,
                    UserIsDeleteConstant::IS_DELETE,
                ]
            ])
            ->one();

        if (!empty($result)) {
            return $result;
        }

        return new self();
    }

    /**
     * 创建默认系统用户
     * @param $data
     * @return array|UserModel|null
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function createDefaultUser(array $data)
    {
        if (empty($data['uid'])) {
            throw new UserException(UserException::CREATE_DEFAULT_USER_UID_NOT_EMPTY);
        }

        if (empty($data['username'])) {
            throw new UserException(UserException::CREATE_DEFAULT_USER_USERNAME_NOT_EMPTY);
        }

        $isRoot = $data['is_root'] ?? 0;

        $result = new self();
        $result->username = $data['username'];
        $result->password = $data['username'];
        $result->salt = StringHelper::random(16);
        $result->is_root = $isRoot;
        $result->created_at = DateTimeHelper::now();
        $result->last_login = DateTimeHelper::now();
        $result->last_ip = ClientHelper::getIp();
        $result->status = 1;

        if ($result->save() === false) {
            throw new UserException(UserException::CREATE_DEFAULT_USER_FAILED);
        }

        return $result;
    }

    /**
     * 检测用户是否可用
     * @param string $userName
     * @return array type 类型  0正常进行添加,需要密码  1 不需填写密码  -1 不能使用该用户名
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkUserStatus(string $userName)
    {
        $res = ['type' => 0];
        // 处理用户是否可用 是否需要创建密码
        // 单店铺的 平台操作 1 检测微擎有没有  有的话 不需要填写密码,带着微擎uid   没有的话 检查本地有没有 本地也没有 需要填写密码
        // 以上 如果本地有的话, 判断本地是不是商户操作员 如果是的话  不允许添加  否则 可以添加 不需要填写密码

        // 多商户操作员,只检查本地有没有  本地有的话, 判断密码是否为空  空的话 填写密码  不为空 不填写
        // 本地没有 正常添加

        // 检测微擎是否存在该账户

        // 本地
        $user = UserModel::find()->where(['username' => $userName, 'is_deleted' => [
            UserIsDeleteConstant::NOT_IS_DELETE,
            UserIsDeleteConstant::IS_DELETE,
        ]])->first();
        if (!empty($user) && !empty($user['password'])) {
            $res['uid'] = $user['id'];
            $res['type'] = 1;
        }

        return $res;
    }

    /**
     * 更新用户密码
     * @param array $data
     * @param int $uid
     * @return array|bool
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateUser(array $data, int $uid)
    {
        $password = $data['password'];

        //检测用户密码是否符合要求
        $result = ValueHelper::checkPassword($password);
        if (is_error($result)) {
            return $result;
        }

        // 密码加盐
        $salt = StringHelper::random(16);
        $passwordSalt = CryptHelper::passwordHash($password . $salt);


        // 校验用户
        $usermodel = self::findOne(['id' => $uid]);
        if ($usermodel->id) {
            $usermodel->password = $passwordSalt;
            $usermodel->salt = $salt;
            if ($usermodel->save() === false) {
                throw new UserException(UserException::SAVE_FAILED);
            }
        }

        return true;
    }

    /**
     * 验证账号是否存在
     * @param $username
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function check($username)
    {
        $count = self::find()
            ->where(['username' => $username])
            ->count();

        return $count > 0 ? true : false;
    }

}
