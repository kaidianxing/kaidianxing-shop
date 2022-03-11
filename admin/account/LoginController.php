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


use shopstar\models\assistant\AssistantUploadLogModel;
use shopstar\bases\KdxAdminAccountApiController;
use shopstar\exceptions\adminAccount\UserLoginException;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\user\UserAuditStatusConstant;
use shopstar\constants\user\UserIsDeleteConstant;
use shopstar\exceptions\UserException;
use shopstar\helpers\CryptHelper;
use shopstar\helpers\DateTimeHelper;
 
use shopstar\helpers\RequestHelper;
use shopstar\models\core\CoreSettings;
use shopstar\models\member\MemberModel;
use shopstar\models\role\ManagerModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\user\UserModel;
use shopstar\models\user\UserSession;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\helpers\Json;

/**
 * 登录相关
 * Class LoginController
 * @package modules\account\manage
 */
class LoginController extends KdxAdminAccountApiController
{
    public $configActions = [
        'allowActions' => ['*'],  // 允许不登录访问的Actions
        'postActions' => ['submit'],   // 需要POST请求访问的Actions
    ];

    /**
     * 店铺助手获取后台设置的logoname
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManageInit()
    {
        $result['site_settings'] = CoreSettings::get('site');
        // name 和 logo 从 shop setting中读取
        $siteSettings = ShopSettings::get('mobile_basic.site');
        $result['site_settings']['name'] = $siteSettings['name'];
        $result['site_settings']['logo'] = $siteSettings['logo'];

        return $this->result($result);
    }

    /**
     * 登录初始化
     * @return array|int[]|\yii\web\Response
     * @throws UserLoginException
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public function actionInit()
    {
        if (!in_array($this->clientType, [
            ClientTypeConstant::MANAGE_SHOP_ASSISTANT,
            ClientTypeConstant::ADMIN_PC,
            ClientTypeConstant::MANAGE_PC
        ])) {
            throw new UserLoginException(UserLoginException::LOGIN_INIT_CLIENT_TYPE_INVALID);
        }

        if (in_array($this->clientType, [ClientTypeConstant::ADMIN_PC, ClientTypeConstant::MANAGE_PC])) {

            //管理端登录
            if ($this->clientType == ClientTypeConstant::ADMIN_PC) {
                $result['setting'] = CoreSettings::get('admin_basic');
            } else {

                $result['setting'] = CoreSettings::get('site');
            }

            $shopData = ShopSettings::get('sysset.mall.basic');

            $result['setting']['pc_name'] = $shopData['name'] ?? '';
            $result['setting']['pc_logo'] = $shopData['logo'] ?? '';
            $result['setting']['login_show_img'] = $shopData['login_show_img'] ?? '';
            return $this->result($result);
        }

        $result = [];

        // 查询商户
        $data = ShopSettings::get('sysset.mall.basic');
        $result['shop'] = [
            'name' => $data['name'],
            'logo' => $data['logo'],
        ];

        // 解析token
        $token = RequestHelper::get('token');
        if (!empty($token)) {
            $tokenDecode = $this->decodeToken($token);
            // 查询会员
            if (!empty($tokenDecode) && !empty($tokenDecode['member_id'])) {
                $result['member'] = MemberModel::find()
                    ->where([
                        'id' => $tokenDecode['member_id'],
                    ])
                    ->select(['nickname', 'avatar'])
                    ->first();
                $result['user'] = [
                    'id' => $tokenDecode['user_id'],
                ];
            }
        }

        $result['shop_attachment_url'] = CoreAttachmentService::getRoot();
        $result['storage'] = ShopSettings::getImageCompressionRule();

        $result['site_settings'] = CoreSettings::get('site');

        return $this->result($result);
    }

    /**
     * 提交登录
     * @return array|int[]|\yii\web\Response
     * @throws UserLoginException
     * @throws UserException
     * @author likexin
     */
    public function actionSubmit()
    {
        $username = RequestHelper::post('username');
        $username = trim($username, '');
        if (empty($username)) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_PARAM_USERNAME_EMPTY);
        }

        $password = RequestHelper::post('password');
        $password = trim($password, '');
        if (empty($password)) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_PARAM_PASSWORD_EMPTY);
        }

        // 判断已经登录
        $session = UserSession::get($this->sessionId, 'user');
        if (!empty($session)) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_USER_ALREADY_LOGIN);
        }

        // 查询用户
        $userModel = UserModel::find()
            ->where([
                'username' => $username,
                'is_deleted' => UserIsDeleteConstant::NOT_IS_DELETE
            ])
            ->select(['id', 'username', 'is_root', 'password', 'status', 'salt', 'audit_status'])
            ->one();

        if (empty($userModel)) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_USER_NOT_EXIST);
        }

        //转数组
        $user = $userModel->toArray();

        //判断状态
        if (empty($user['status'])) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_USER_STATUS_INVALID);
        }

        //判断是否是超管
        if ($this->clientType == ClientTypeConstant::ADMIN_PC && $user['is_root'] == 0) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_USER_PERMISSION_DENIED);
        }

        // 验证密码
        $verify = CryptHelper::passwordVerify($password . $user['salt'], $user['password']);
        if (!$verify) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_USER_PASSWORD_INVALID);
        }

        //最后登录时间
        $userModel->last_login = DateTimeHelper::now();
        $userModel->save();

        // 卸载敏感字段
        unset($user['password'], $user['salt']);

        // 记录Session
        UserSession::set($this->sessionId, $user['id'], 'user', $user);


        return $this->result([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'need_set_password' => empty($user['password']),
                'audit_page' => $this->getAuditStatusPage($user)
            ],
        ]);
    }

    /**
     * 获取用户状态页
     * @param array $user
     * @return int
     * @author 青岛开店星信息技术有限公司.
     */
    private function getAuditStatusPage(array $user): int
    {

        //0 = 不跳转
        $page = 0;

        if ($user['is_root'] == 1) {
            return $page;
        }

        //缓存key
        $key = 'user_audit_pass_' . $user['id'];
        $cache = CoreSettings::get($key);

        $auditOpen = CoreSettings::get('user_setting.register.audit');

        //填写资料页
        if (!empty($auditOpen) && $user['audit_status'] == UserAuditStatusConstant::AUDIT_STATUS_NOT_SUBMIT) {
            $page = 1;
        }

        //提示审核成功页
        if (!empty($cache) && !empty($auditOpen) && $user['audit_status'] == UserAuditStatusConstant::AUDIT_STATUS_CHECK_PASS) {
            $page = 2;
        }

        //审核资料页
        if (!empty($auditOpen) && in_array($user['audit_status'], [UserAuditStatusConstant::AUDIT_STATUS_CHECK_PADDING, UserAuditStatusConstant::AUDIT_STATUS_CHECK_REFUSE])) {
            $page = 3;
        }

        return $page;
    }

    /**
     * 通过Token进行登录
     * @return array|int[]|\yii\web\Response
     * @throws UserLoginException
     * @author likexin
     */
    public function actionSubmitByToken()
    {
        $token = RequestHelper::post('token');
        if (empty($token)) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_BY_TOKEN_PARAM_TOKEN_EMPTY);
        }

        // 判断已经登录
        $session = UserSession::get($this->sessionId, 'user');
        if (!empty($session)) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_BY_TOKEN_USER_ALREADY_LOGIN);
        }

        // 解析Token
        $tokenDecode = $this->decodeToken($token);
        if (empty($tokenDecode)) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_BY_TOKEN_PARAM_TOKEN_INVALID);
        }

        if (empty($tokenDecode['user_id'])) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_BY_TOKEN_PARAM_INVALID);
        }

        // 查询用户
        $user = UserModel::find()
            ->where([
                'id' => $tokenDecode['user_id'],
            ])
            ->select(['id', 'username', 'is_root', 'status', 'password'])
            ->first();
        if (empty($user)) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_BY_TOKEN_USER_NOT_EXIST);
        } elseif (empty($user['status'])) {
            throw new UserLoginException(UserLoginException::LOGIN_SUBMIT_BY_TOKEN_USER_STATUS_INVALID);
        }

        // 记录Session
        UserSession::set($this->sessionId, $user['id'], 'user', $user);

        return $this->result([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'need_set_password' => empty($user['password'])
            ],
        ]);
    }

    /**
     * 解析Token
     * @param string $token
     * @return mixed|null
     * @author likexin
     */
    private function decodeToken(string $token)
    {
        // $token = str_replace(' ', '+', $token);
        $token = base64_decode($token);

        $key = md5(md5('_kdx_shop') . 'kaidianxing' . base64_encode('kaidianxing'));
        $tokenDecode = CryptHelper::encrypt($token, 'DECODE', $key);
        return Json::decode($tokenDecode);
    }

    /**
     * 获取是否登录
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionGetLoginStatus()
    {
        $session = UserSession::get($this->sessionId, 'user');
        return $this->result([
            'login_status' => !empty($session),
        ]);
    }

    /**
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionGetManageInfo()
    {
        $uid = RequestHelper::getInt('user_id');
        //店铺助手追加参数
        if ($this->clientType == ClientTypeConstant::MANAGE_SHOP_ASSISTANT) {
            $manage = ManagerModel::find()->where(['uid' => $uid])->select(['name', 'contact'])->first();
        }

        return $this->result(['user' => $manage ?? []]);
    }

}