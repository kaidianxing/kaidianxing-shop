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


namespace shopstar\services\user;

use shopstar\bases\service\BaseService;
use shopstar\exceptions\UserException;
use shopstar\helpers\CryptHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\StringHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\user\UserModel;
use shopstar\models\user\UserProfileModel;

class UserService extends BaseService
{
    /**
     * 创建用户
     * @param $data
     * @return int|mixed
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function createUser($data)
    {
        $username = $data['username'];
        $password = $data['password'];
        $now = DateTimeHelper::now();

        // 检查是否可添加
        $res = UserModel::checkUserStatus($username);
        if ($res['type'] == -1) {
            // 不允许添加
            throw new UserException(UserException::MANAGE_ACCOUNT_USER_ERROR);
        }

        // 密码加盐
        $passwordSalt = '';
        $salt = '';
        if (!empty($password)) {
            $salt = StringHelper::random(16);
            $passwordSalt = CryptHelper::passwordHash($password . $salt);
        }

        // 校验用户
        $usermodel = UserModel::checkUsername($username);
        if (!$usermodel->id) {
            $usermodel->username = $username;
            $usermodel->password = $passwordSalt;
            $usermodel->salt = $salt;
            $usermodel->created_at = $now;
            $usermodel->status = 1;
            $usermodel->audit_status = $data['audit_status'] ?: 0;
            if ($usermodel->save() === false) {
                throw new UserException(UserException::MANAGE_USER_INDEX_CREATE_USER_FAILED);
            }

            //添加用户资料表
            $userProfile = new UserProfileModel();
            $userProfile->setAttributes([
                'user_id' => $usermodel->id,
            ]);
            $userProfile->save();
        }
        $kdxUid = $usermodel->id;

        return $kdxUid;
    }

    /**
     * 管理端添加用户和注册用户
     * @param int $source 来源 0:后台添加，1:商家端注册
     * @param array $data
     * @return array|int|mixed|null
     * @throws UserException
     * @author 青岛开店星信息技术有限公司.
     */
    public static function createAccount(array $data, int $source = 0)
    {
        $username = $data['username'];
        $password = $data['password'];

        //判断用户密码是否合格
        $result = ValueHelper::checkPassword($password);
        if (is_error($result)) {
            return $result;
        }

        // 校验用户
        $userModel = UserModel::checkUsername($username);

        //如果用户存在，则直接返回错误
        if ($userModel->id) {
            return error('用户已存在');
        }

        $now = DateTimeHelper::now();

        //密码加盐
        $passwordSalt = '';
        $salt = '';
        if (!empty($password)) {
            $salt = StringHelper::random(16);
            $passwordSalt = CryptHelper::passwordHash($password . $salt);
        }

        $userModel->username = $username;
        $userModel->password = $passwordSalt;
        $userModel->salt = $salt;
        $userModel->created_at = $now;
        $userModel->status = 1;
        $userModel->audit_status = $data['audit_status'] ?? 0;

        if ($data['is_root']) {
            $userModel->is_root = 1;
        }
        $userModel->audit_status = $data['audit_status'] ?? 0;

        if (!$userModel->save()) {
            return error('用户创建失败');
        }

        //添加用户资料
        $userProfileModel = new UserProfileModel();
        $userProfileModel->setAttributes([
            'user_id' => $userModel->id,
            'shop_num' => 0,
            'nickname' => $data['nickname'] ?? '',
            'avatar' => $data['avatar'] ?? '',
        ]);
        $userProfileModel->save();

        return $userModel->id;
    }

}