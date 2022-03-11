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


use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\user\UserAuditStatusConstant;
use shopstar\constants\user\UserIsDeleteConstant;
use shopstar\helpers\CaptchaHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\CoreSettings;
use shopstar\models\user\UserModel;
use shopstar\models\user\UserSession;
use shopstar\bases\KdxAdminAccountApiController;
use shopstar\exceptions\adminAccount\UserRegisterException;
use shopstar\services\user\UserService;

/**
 * 注册
 * Class LoginController
 * @package modules\account\manage
 */
class RegisterController extends KdxAdminAccountApiController
{
    public $configActions = [
        'allowActions' => ['*'],  // 允许不登录访问的Actions
        'postActions' => ['submit'],  // 需要POST请求访问的Actions
        'allowHeaderActions' => ['get-capture'],  // 允许GET传入Header参数的Actions
    ];

    /**
     * 初始化
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionInit()
    {
        //获取设置
        $settings = CoreSettings::get('user_setting');

        return $this->result([
            'settings' => $settings,
        ]);
    }

    /**
     * 获取验证码
     * @action get-capture
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetCapture()
    {
        CaptchaHelper::create(['sessionId' => $this->sessionId], 1);
    }

    /**
     * @return array|int[]|\yii\web\Response
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionSendSmsCode()
    {
        $code = RequestHelper::post('code');
        $mobile = RequestHelper::post('mobile');
        $type = RequestHelper::post('type');

        if (empty($mobile)) {
            throw new UserRegisterException(UserRegisterException::REGISTER_SEND_SMS_CODE_EMPTY_MOBILE_ERROR);
        }

        $result = CaptchaHelper::check($code, $this->sessionId);
        if (!$result) {
            throw new UserRegisterException(UserRegisterException::REGISTER_SEND_SMS_CODE_IMAGE_CODE_ERROR);
        }

        if (empty($type)) {
            throw new UserRegisterException(UserRegisterException::REGISTER_SEND_SMS_CODE_TYPE_EMPTY_ERROR);
        }
        
        // 获取系统设置
        $smsSetting = CoreSettings::get('sms.aliyun');
        if (empty($smsSetting['access_key_secret']) || empty($smsSetting['access_key_id'])) {
            throw new UserRegisterException(UserRegisterException::CORE_SETTING_SMS_SETTING_ERROR);
        }

        //获取配置
        $setting = CoreSettings::get('admin_sms_template.' . $type);
        if (empty($setting)) {
            throw new UserRegisterException(UserRegisterException::REGISTER_SEND_SMS_CODE_SMS_SETTING_ERROR);
        }

        $result = NoticeComponent::getInstance($type == 'register' ? NoticeTypeConstant::SHOP_VERIFY_CODE_USER_REG : NoticeTypeConstant::SHOP_VERIFY_CODE_RETRIEVE_PWD, [
            'code' => random_int(1000, 9999)
        ]);

        if (!is_error($result)) {

            //发送验证码
            $result->sendVerifyCode($mobile, [
                'sms' => [
                    'data' => [$setting['data']],
                    'sms_tpl_id' => $setting['template_id']
                ],
                'signature' => [
                    'content' => $setting['signature']
                ]
            ]);
        }

        return $this->result();
    }

    /**
     * 提交注册
     * @return array|int[]|\yii\web\Response
     * @throws UserRegisterException
     * @throws \shopstar\exceptions\UserException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSubmit()
    {
        $username = RequestHelper::post('username');
        $username = trim($username,'');
        if (empty($username)) {
            throw new UserRegisterException(UserRegisterException::REGISTER_SUBMIT_EMPTY_USERNAME_ERROR);
        }

        $password = RequestHelper::post('password');
        $password = trim($password,'');
        if (empty($password)) {
            throw new UserRegisterException(UserRegisterException::REGISTER_SUBMIT_EMPTY_PASSWORD_ERROR);
        }

        $confirmPassword = RequestHelper::post('confirm_password');
        $confirmPassword = trim($confirmPassword,'');
        if ($password != $confirmPassword) {
            throw new UserRegisterException(UserRegisterException::REGISTER_SUBMIT_EMPTY_CONFIRM_PASSWORD_ERROR);
        }

        $smsCode = RequestHelper::post('sms_code');
        $result = NoticeComponent::checkVerifyCode(0, NoticeTypeConstant::SHOP_VERIFY_CODE_USER_REG, $username, $smsCode);
        if (!$result) {
            throw new UserRegisterException(UserRegisterException::REGISTER_SUBMIT_SMS_CODE_ERROR);
        }

        $setting = CoreSettings::get('user_setting');

        //创建用户
        $result = UserService::createAccount([
            'username' => $username,
            'password' => $password,
            'audit_status' => $setting['register']['audit'] == 0 ? UserAuditStatusConstant::AUDIT_STATUS_CHECK_PASS : UserAuditStatusConstant::AUDIT_STATUS_NOT_SUBMIT
        ], 1);

        if (is_error($result)) {
            throw new UserRegisterException(UserRegisterException::REGISTER_SUBMIT_ERROR, $result['message']);
        }

        //设置缓存
        UserSession::set($this->sessionId, $result, 'user', [
            'id' => $result,
            'username' => $username,
            'status' => 1
        ]);

        //修改登录时间
        UserModel::updateAll(['last_login' => DateTimeHelper::now()], ['id' => $result]);

        return $this->result($result);
    }

    /**
     * 忘记密码
     * @throws UserRegisterException
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionForgetPassword()
    {
        $username = RequestHelper::post('username');
        if (empty($username)) {
            throw new UserRegisterException(UserRegisterException::REGISTER_FORGET_EMPTY_USERNAME_ERROR);
        }

        $password = RequestHelper::post('password');
        if (empty($password)) {
            throw new UserRegisterException(UserRegisterException::REGISTER_FORGET_EMPTY_PASSWORD_ERROR);
        }

        $confirmPassword = RequestHelper::post('confirm_password');
        if ($password != $confirmPassword) {
            throw new UserRegisterException(UserRegisterException::REGISTER_FORGET_EMPTY_CONFIRM_PASSWORD_ERROR);
        }

        $smsCode = RequestHelper::post('sms_code');
        $result = NoticeComponent::checkVerifyCode(0, NoticeTypeConstant::SHOP_VERIFY_CODE_RETRIEVE_PWD, $username, $smsCode);
        if (!$result) {
            throw new UserRegisterException(UserRegisterException::REGISTER_FORGET_SMS_CODE_ERROR);
        }

        $user = UserModel::where([
            'username' => $username,
            'is_deleted' => [
                UserIsDeleteConstant::NOT_IS_DELETE,
                UserIsDeleteConstant::IS_DELETE
            ]
        ])->one();

        if (empty($user)) {
            throw new UserRegisterException(UserRegisterException::REGISTER_FORGET_USER_EMPTY);
        }

        $result = UserModel::updateUser([
            'password' => $password,
        ], $user->id);

        return $this->result($result);
    }


}