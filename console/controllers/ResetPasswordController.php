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

namespace shopstar\console\controllers;

use shopstar\helpers\CryptHelper;
use shopstar\models\user\UserModel;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * 重置操作密码
 * Class ResetPasswordController
 * @package modules\console\controllers
 * @author likexin
 */
class ResetPasswordController extends Controller
{

    /**
     * @var string 用户名
     */
    public $username;

    /**
     * @var string 密码
     */
    public $password;

    /**
     * 参数
     * @param string $actionID
     * @return string[]
     * @author likexin
     */
    public function options($actionID): array
    {
        return ['username', 'password'];
    }

    /**
     * @return string[]
     * @author likexin
     */
    public function optionAliases(): array
    {
        return [
            'u' => 'username',
            'p' => 'password',
        ];
    }

    /**
     * 执行修改
     * @author likexin
     */
    public function actionIndex()
    {
        if (empty($this->username)) {
            return $this->stdout("Params Invalid:: username can not be empty.\n", Console::BOLD, Console::FG_RED);
        } elseif (empty($this->password)) {
            return $this->stdout("Params Invalid:: password can not be empty.\n", Console::BOLD, Console::FG_RED);
        }

        // 查询用户
        /**
         * @var UserModel $user
         */
        $user = UserModel::find()->where([
            'username' => $this->username,
        ])->one();
        if (empty($user)) {
            return $this->stdout("User not found.\n", Console::BOLD, Console::FG_RED);
        }

        // 重置密码
        $user->password = CryptHelper::passwordHash($this->password . $user->salt);
        if (!$user->save()) {
            return $this->stdout("Reset Fail :: {$user->getErrorMessage()}.\n", Console::BOLD, Console::FG_RED);
        }

        return $this->stdout("Reset success.\n", Console::BOLD, Console::FG_GREEN);
    }

}