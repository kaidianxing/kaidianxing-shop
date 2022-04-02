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

namespace shopstar\admin\account;

use shopstar\constants\ClientTypeConstant;
use shopstar\helpers\CryptHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\user\UserModel;
use shopstar\models\user\UserSession;
use shopstar\bases\KdxAdminAccountApiController;

/**
 * Class ChangePasswordController
 * @package modules\account\manage
 */
class ChangePasswordController extends KdxAdminAccountApiController
{

    /**
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionIndex()
    {
        // 新密码
        $password = RequestHelper::post('password');
        if (empty($password)) {
            return $this->error('参数错误 password不能为空');
        }

        // 原密码
        if ($this->clientType == ClientTypeConstant::MANAGE_PC || $this->clientType == ClientTypeConstant::ADMIN_PC) {
            $originalPassword = RequestHelper::post('original_password');
        }

        // 查询当前登录用户
        /**
         * @var UserModel $user
         */
        $user = UserModel::find()
            ->where([
                'id' => $this->userId,
            ])
            ->one();
        if (empty($user)) {
            return $this->error('用户不存在');
        }

        // 判断原密码不能为空
        if (!empty($user->password) && ($this->clientType == ClientTypeConstant::MANAGE_PC || $this->clientType == ClientTypeConstant::ADMIN_PC)) {
            if (empty($originalPassword)) {
                return $this->error('参数错误 original_password不能为空');
            }

            // 验证原密码
            if (!CryptHelper::passwordVerify($originalPassword . $user->salt, $user->password)) {
                return $this->error('原密码错误');
            }
        }

        // 重置密码
        $user->salt = StringHelper::random(16);// 密码加盐
        $user->password = CryptHelper::passwordHash($password . $user->salt);

        if (!$user->save()) {
            return $this->error('修改失败: ' . $user->getErrorMessage());
        }

        // 登出
        UserSession::clear($this->sessionId);

        return $this->success();
    }

}