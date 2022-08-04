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

namespace shopstar\mobile\member;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\bases\exception\BaseApiException;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\CacheTypeConstant;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\coupon\CouponConstant;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\CaptchaHelper;
use shopstar\helpers\CryptHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberLoginModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberSession;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\notice\NoticeSmsTemplateModel;
use shopstar\models\order\OrderModel;
use shopstar\models\role\ManagerModel;
use shopstar\models\role\ManagerRoleModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\member\MemberService;
use shopstar\services\role\ManagerService;

/**
 * Class IndexController
 * @package shop\client
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends BaseMobileApiController
{

    public $configActions = [
        'allowSessionActions' => [
            'get-session-id'
        ],
        'allowClientActions' => [
            'get-capture'
        ],
        'postActions' => [
            'register'
        ],
        'allowNotLoginActions' => [
            'auth',
            'login',
            'login-by-code',
            'forget-password',
            'register',
            'send-sms',
            'get-capture',
            'get-session-id',
            'merge-member',
            'get-login-status',
            'check-sms-code',
            'invalid'
        ],
        'allowHeaderActions' => [
            'get-capture'
        ],
        'allowShopCloseActions' => [
            'get-capture',
            'get-session-id'
        ],
    ];

    /**
     * @return \yii\web\Response
     * @author likexin
     */
    public function actionIndex()
    {
        return $this->result([]);
    }

    /**
     * 登录分发
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAuth()
    {
        $type = RequestHelper::post('type') ? RequestHelper::post('type') : RequestHelper::get('type');

        $data = RequestHelper::post() ? RequestHelper::post() : RequestHelper::get();
        $namespace = "shopstar\components\authorization\\";
        $class = $namespace . ucfirst(trim($type)) . 'Handler';
        if (!class_exists($class) || !method_exists($class, 'auth')) {
            throw new MemberException(MemberException::AUTHORIZATION_AUTH_COMPONENTS_NOT_FOUND);
        }

        $info = (new $class())->auth($data);

        if (is_error($info)) {
            throw new MemberException(MemberException::AUTHORIZATION_AUTH_IS_DEFEATED, $info['message']);
        }
        $this->result($info);
    }

    /**
     * 获取用户信息分发
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     * @action login
     */
    public function actionLogin()
    {
        $type = RequestHelper::post('type') ? RequestHelper::post('type') : RequestHelper::get('type');

        $namespace = "shopstar\components\authorization\\";
        $class = $namespace . ucfirst(trim($type)) . 'Handler';
        if (!class_exists($class) || !method_exists($class, 'login')) {
            throw new MemberException(MemberException::AUTHORIZATION_LOGIN_COMPONENTS_NOT_FOUND);
        }

        $result = (new $class())->login($this->sessionId);

        if (is_error($result)) {
            throw new MemberException(MemberException::AUTHORIZATION_LOGIN_IS_DEFEATED, $result['message']);
        }

        $this->result($result);
    }

    /**
     * 通过验证码登录
     * @action login-by-code
     * @return array|\yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionLoginByCode()
    {
        $mobile = RequestHelper::post('mobile');
        if (!ValueHelper::isMobile($mobile)) {
            throw new MemberException(MemberException::LOGIN_BY_CODE_MOBILE_ERROR);
        }

        $verifyCode = RequestHelper::post('verify_code');

        if (!CaptchaHelper::check($verifyCode, $this->sessionId)) {
            throw new MemberException(MemberException::LOGIN_BY_CODE_VERIFY_CODE_ERROR);
        }

        //验证手机号码
        $code = RequestHelper::post('code');

        //验证短信
        if (!NoticeComponent::checkVerifyCode('login_code', $mobile, $code)) {
            throw new MemberException(MemberException::LOGIN_BY_CODE_SMSCODE_ERROR);
        }

        //直接登录成功
        //直接获取用户
        $user = MemberModel::find()
            ->where(['mobile' => $mobile, 'is_deleted' => 0])
            ->asArray()
            ->one();
        if ($user === null) {
            throw new MemberException(MemberException::LOGIN_BY_CODE_MEMBER_NOT_EXISTS);
        }

        $result = MemberLoginModel::login($user['id'], $this->sessionId, $user, $this->clientType);
        return $this->result($result);
    }

    /**
     * 找回密码
     * @action forget-password
     * @return array|\yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionForgetPassword()
    {
        $mobile = RequestHelper::post('mobile');
        $code = RequestHelper::post('code');
        $verifyCode = RequestHelper::post('verify_code');

        if (!CaptchaHelper::check($verifyCode, $this->sessionId)) {
            throw new MemberException(MemberException::FORGET_PASSWORD_VERIFY_CODE_ERROR);
        }

        //验证短信验证码
        if (!NoticeComponent::checkVerifyCode('retrieve_pwd', $mobile, $code)) {
            throw new MemberException(MemberException::FORGET_PASSWORD_SMSCODE_ERROR);
        }

        $newpass = RequestHelper::post('newpass');
        $reply_password = RequestHelper::post('reply_password');
        if ($newpass != $reply_password) {
            throw new MemberException(MemberException::PASSWORD_CHECK_ERROR);
        }

        $user = MemberModel::find()
            ->where(['mobile' => $mobile, 'is_deleted' => 0])
            ->one();

        if (empty($user)) {
            throw new MemberException(MemberException::MEMBER_INDEX_FORGET_PASSWORD_MOBILE_EXIST_ERROR);
        }
        $salt = StringHelper::random(16);
        $user->password = md5($newpass . $salt);
        $user->salt = $salt;
        if ($user->save() === false) {
            throw new MemberException(MemberException::FORGET_PASSWORD_FAIL);
        }
        return $this->result('找回密码成功');
    }

    /**
     * @return \yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangePassword(): \yii\web\Response
    {
        $post = RequestHelper::post();

        $user = MemberModel::find()
            ->where(['id' => $this->memberId, 'is_deleted' => 0])
            ->one();

        if (empty($post['password']) || empty($post['confirm_password'])) {
            throw new MemberException(MemberException::MEMBER_CHANGE_PASSWORD_PARAMS_ERROR);
        }

        if ($user['password']) {
            if (empty($post['old_password'])) {
                throw new MemberException(MemberException::MEMBER_CHANGE_PASSWORD_PARAMS_ERROR);
            }

            if ($user['password'] != md5($post['old_password'] . $user['salt'])) {
                throw new MemberException(MemberException::MEMBER_CHANGE_PASSWORD_OLD_PASSWORD_ERROR);
            }
        }

        $salt = StringHelper::random(16);
        $user->password = md5($post['password'] . $salt);
        $user->salt = $salt;
        if ($user->save() === false) {
            throw new MemberException(MemberException::MEMBER_CHANGE_PASSWORD_ERROR);
        }

        return $this->success();
    }

    /**
     * 用户绑定手机号
     * @action bind-mobile
     * @return array|\yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionBindMobile()
    {
        $mobile = RequestHelper::post('mobile');
        $code = RequestHelper::post('code');
        //是否是微信授权 如果是微信授权则不检测手机验证码
        $isAuth = RequestHelper::postInt('is_auth', 0);
        $verifyCode = RequestHelper::post('verify_code', 0);

        if ($isAuth == 0 && !CaptchaHelper::check($verifyCode, $this->sessionId)) {
            throw new MemberException(MemberException::BIND_MOBILE_VERIFY_CODE_ERROR);
        }

        if ($isAuth == 0 && !NoticeComponent::checkVerifyCode('bind', $mobile, $code)) {
            throw new MemberException(MemberException::BIND_MOBILE_SMSCODE_ERROR);
        }

        if (!MemberModel::checkMobileIsBind($mobile)) {
            //如果已经存在的话就要直接返回两个用户
            $bind_user = MemberModel::find()
                ->where(['mobile' => $mobile, 'is_deleted' => 0])
                ->select('id,nickname,mobile,credit,balance,level_id,created_at,source,avatar')
                ->asArray()
                ->one();

            //等级名称
            $memberLevel = MemberLevelModel::find()->where(['id' => $bind_user['level']])->select('level_name')->one();
            $bind_user['level_name'] = $memberLevel['level_name'];

            //订单数
            $bind_user['order_num'] = OrderModel::find()->where(['member_id' => $bind_user['id']])->count();
            //优惠券数
            $bind_user['coupon_num'] = CouponMemberModel::find()->where(['member_id' => $bind_user['id']])->count();

            if ($bind_user['id'] == $this->memberId) {
                throw new MemberException(MemberException::MEMBER_BIND_MOBILE_MEMBER_ERROR);
            }

            $user = MemberModel::find()
                ->where(['id' => $this->memberId])
                ->select('id,nickname,mobile,credit,balance,level_id,created_at,source,avatar')
                ->asArray()
                ->one();
            //订单数
            $user['order_num'] = OrderModel::find()->where(['member_id' => $user['id']])->count();
            //优惠券数
            $user['coupon_num'] = CouponMemberModel::find()->where(['member_id' => $user['id']])->count();

            return $this->result([
                'bind_user' => $bind_user,
                'user' => $user,
                'bind_mobile' => $mobile
            ], MemberException::BIND_MOBILE_MOBILE_EXISTS_ERROR);
        }

        $member = MemberModel::findOne(['id' => $this->memberId]);
        if (empty($member)) {
            throw new MemberException(MemberException::BIND_MOBILE_MEMBER_NOT_EXISTS);
        }
        $member->mobile = $mobile;
        if ($member->save() === false) {
            throw new MemberException(MemberException::BIND_MOBILE_ERROR);
        }
        return $this->result('绑定成功');
    }

    /**
     * 用户合并
     * @return array|\yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionMergeMember()
    {
        $post = RequestHelper::post();
        //废弃的会员id
        $discardMemberId = $post['discard_member_id'];
        //选择的会员id
        $selectMemberId = $post['select_member_id'];
        //需要绑定的手机号
        $mobile = $post['mobile'];

        $tr = \Yii::$app->db->beginTransaction();
        try {
            if (empty($discardMemberId) || empty($selectMemberId) || empty($mobile)) {
                throw new MemberException(MemberException::MEMBER_MERGE_PARAMS_ERROR);
            }

            //先废弃会员主表的手机号和密码
            $discardMemberModel = MemberService::getModelByType(ClientTypeConstant::CLIENT_H5, (int)$discardMemberId);
            if (is_error($discardMemberModel)) {
                throw new MemberException(MemberException::MERGE_MEMBER_NOT_EXISTS);
            }
            $discardMemberModel->setAttributes(['mobile' => '', 'password' => '', 'salt' => '']);
            if (!$discardMemberModel->save()) {
                //废弃会员的手机号去除失败
                throw new MemberException(MemberException::MEMBER_MERGE_DISCARD_SAVE_ERROR);
            }

            //修改选择会员的手机号
            $selectMemberModel = MemberService::getModelByType(ClientTypeConstant::CLIENT_H5, (int)$selectMemberId);
            if (is_error($selectMemberModel)) {
                throw new MemberException(MemberException::MERGE_MEMBER_SELECT_NOT_EXISTS);
            }
            $selectMemberModel->mobile = $mobile;
            if ($selectMemberModel->save() == false) {
                throw new MemberException(MemberException::MERGE_MEMBER_ERROR);
            }

            //修改废弃会员的主体信息到选择的账号的主体下
            $result = MemberService::changeAccountSubject($this->memberId, $selectMemberId, (int)$discardMemberId, $this->clientType);
            if (is_error($result)) {
                throw new MemberException(MemberException::MEMBER_MERGE_CHANGE_SUBJECT_ERROR);
            }

            //重新写入session
            MemberSession::set($this->sessionId, $selectMemberId, $this->clientType, 'member', $selectMemberModel->getAttributes());
            $tr->commit();
        } catch (\Throwable $throwable) {
            $tr->rollBack();
            throw new MemberException($throwable->getCode(), $throwable->getMessage());
        }

        return $this->result('操作成功');
    }

    /**
     * 更换手机号
     * @action change-bind-mobile
     * @return array|\yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeBindMobile()
    {
        $mobile = RequestHelper::post('mobile');
        $oldMobile = RequestHelper::post('old_mobile');
        $verifyCode = RequestHelper::post('verify_code');

        //验证验证码
        if (!CaptchaHelper::check($verifyCode, $this->sessionId)) {
            throw new MemberException(MemberException::CHANGE_BIND_MOBILE_VERIFY_CODE_ERROR);
        }

        //验证老手机号的验证码
        if (!NoticeComponent::checkVerifyCode('bind', $oldMobile, RequestHelper::post('old_code'))) {
            throw new MemberException(MemberException::CHANGE_BIND_MOBILE_SMSCODE_ERROR);
        }

        //验证当前手机号的验证码
        if (!NoticeComponent::checkVerifyCode('bind', $mobile, RequestHelper::post('code'))) {
            throw new MemberException(MemberException::CHANGE_BIND_MOBILE_NOW_SMSCODE_ERROR);
        }

        //验证新手机号是否重复
        if (!MemberModel::checkMobileIsBind($mobile)) {
            throw new MemberException(MemberException::CHANGE_BIND_MOBILE_MOBILE_EXISTS_ERROR);
        }

        $member = MemberModel::findOne(['id' => $this->memberId]);
        if (empty($member)) {
            throw new MemberException(MemberException::CHANGE_BIND_MOBILE_MEMBER_NOT_EXISTS);
        }
        $member->mobile = $mobile;
        $member->is_bind_mobile = 1;
        if ($member->save() === false) {
            throw new MemberException(MemberException::CHANGE_MOBILE_FAIL);
        }
        return $this->result('绑定成功');
    }

    /**
     * 用户注册
     * @action register
     * @return array|\yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRegister()
    {
        $mobile = RequestHelper::post('mobile');
        $password = RequestHelper::post('password');
        $sms_code = RequestHelper::post('code');
        $verify_code = RequestHelper::post('verify_code');

        //验证图形验证码
        if (!CaptchaHelper::check($verify_code, $this->sessionId)) {
            throw new MemberException(MemberException::REGISTER_VERIFY_CODE_ERROR);
        }

        //验证短信
        if (!NoticeComponent::checkVerifyCode('user_reg', $mobile, $sms_code)) {
            throw new MemberException(MemberException::REGISTER_SMSCODE_ERROR);
        }

        //判断是否在用户表存在本手机号码
        if (!MemberModel::checkMobileIsBind($mobile)) {
            throw new MemberException(MemberException::REGISTER_MOBILE_EXISTS_ERROR);
        }
        $salt = StringHelper::random(16);
        $pass = md5($password . $salt);

        //保存会员
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $result = MemberModel::saveMember([
                'mobile' => $mobile,
                'salt' => $salt,
                'password' => $pass,
                'source' => $this->clientType,
                'is_bind_mobile' => 1,
                'nickname' => $mobile,
            ]);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return $this->result($e->getMessage(), $e->getCode());
        }

        if (is_error($result)) {
            throw new MemberException(MemberException::CHANGE_REGISTER_FAIL, $result['message']);
        }

        return $this->result();
    }

    /**
     * 发送短信
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     * @action send-sms
     */
    public function actionSendSms()
    {
        $mobile = RequestHelper::post('mobile');
        $type = RequestHelper::post('type');

        $verifyCode = RequestHelper::post('verify_code');
        //验证图形验证码
        if (!CaptchaHelper::check($verifyCode, $this->sessionId)) {
            throw new MemberException(MemberException::SEND_SMS_VERIFY_CODE_ERROR);
        }
        if (!ValueHelper::isMobile($mobile)) {
            throw new MemberException(MemberException::SEND_SMS_MOBILE_ERROR);
        }
        NoticeSmsTemplateModel::sendSms($type, $mobile);

        return $this->result('发送成功');
    }

    /**
     * 验证短信
     * @author 青岛开店星信息技术有限公司
     * @action send-sms
     */
    public function actionCheckSmsCode()
    {
        $mobile = RequestHelper::post('mobile');
        $type = RequestHelper::post('type');
        $code = RequestHelper::post('code');
        $result = NoticeComponent::checkVerifyCode($type, $mobile, $code);
        if (!$result) {
            throw new MemberException(MemberException::MEMBER_CHECK_SMS_CODE_ERROR);
        }

        if ($type == 'user_reg') {
            $member = MemberModel::findOne(['mobile' => $mobile, 'is_deleted' => 0]);
            if (!empty($member)) {
                throw new MemberException(MemberException::MEMBER_INDEX_CHECK_SMS_CODE_MOBILE_EXIST_ERROR);
            }
        }

        return $this->result('通过');
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
     * 获取sessionid
     * @action get-session-id
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetSessionId()
    {
        if (mb_strlen($this->sessionId) < 1) {
            $sessionid = MemberSession::createSessionId();
            MemberSession::baseSet($sessionid, '', '', 0, [], []);
            $this->sessionId = $sessionid;
        }

        return $this->result(['code' => 0, 'data' => ['session-id' => $this->sessionId]]);
    }

    /**
     * 会员中心
     * @return array|\yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUserInfo()
    {
        if (empty($this->memberId)) {
            return $this->result(['data' => []]);
        }

        $info = MemberModel::findOne(['id' => $this->memberId]);
        if ($info === null) {
            throw new MemberException(MemberException::USER_INFO_MEMBER_NOT_EXISTS);
        }
        $info = $info->toArray();

        $info['has_password'] = (int)!empty($info['password']);

        //释放关键数据
        unset($info['password'], $info['salt']);

        $where = [];
        $levelId = $info['level_id'];
        if ($levelId != 0) {
            $where = ['id' => $info['level_id']];
        } else { //默认等级
            $where = ['is_default' => 1];
        }

        $level = MemberLevelModel::findOne($where);
        $info['level_name'] = $level['level_name'];

        //获取优惠券数量
        $info['coupon_total'] = CouponMemberModel::getTotal($this->memberId, CouponConstant::COUPON_LIST_TYPE_NORMAL);

        $result = [
            'data' => $info,
        ];

        //加密会员id
        $result['hash_member_id'] = CryptHelper::encrypt($this->memberId . '-hash_member_id');

        return $this->result($result);
    }

    /**
     * 修改用户资料
     * @return array|\yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeUserInfo()
    {
        $post = RequestHelper::post();
        $info = MemberModel::findOne(['id' => $this->memberId]);
        if (empty($info)) {
            throw new MemberException(MemberException::MEMBER_INDEX_CHANGE_USER_INFO_USER_NOT_EXIST_ERROR);
        }

        $data = array_filter([
            'avatar' => $post['avatar'],
            'nickname' => $post['nickname'],
            'birth_year' => $post['birth_year'],
            'birth_month' => $post['birth_month'],
            'birth_day' => $post['birth_day'],
            'province' => $post['province'],
            'city' => $post['city'],
        ]);
        if ($post['province'] == '国外' && empty($post['city'])) {
            $data['city'] = '';
        }
        $info->setAttributes($data);

        if (!$info->save()) {
            throw new MemberException(MemberException::MEMBER_INDEX_CHANGE_USER_INFO_ERROR);
        }

        return $this->result();
    }

    /**
     * 获取登录状态
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetLoginStatus()
    {
        $member = [];
        if (!empty($this->sessionId)) {
            $member = MemberSession::get($this->sessionId, 'member');
        }
        $bindMethod = ShopSettings::get('channel_setting.registry_settings.bind_method');
        return $this->result([
            'success' => empty($member) ? 0 : 1,
            'bind_method' => $bindMethod,
            'member_id' => isset($member['id']) ? $member['id'] : 0
        ]);
    }

    /**
     * 获取用户是否关注
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetMemberFollowStatus()
    {
        return $this->result([
            'follow' => MemberWechatModel::getMemberFollow($this->memberId)
        ]);
    }

    /**
     * 登录之后回调绑定核销员
     * @return \yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionLoginAfter(): \yii\web\Response
    {
        $memberId = RequestHelper::post('member_id');
        $uid = RequestHelper::post('uid');
        // 参数错误
        if (empty($memberId) || $memberId == 0 || $memberId == 'undefined') {
            throw new MemberException(MemberException::CHANGE_LEVEL_PARAM_ERROR);
        }
        $exists = ManagerModel::find()->where(['member_id' => $memberId])->count();
        // 已是核销员
        if ($exists) {
            throw new MemberException(MemberException::ALREADY_VALIDATOR_ERROR);
        }
        $memberInfo = MemberModel::getMemberDetail($memberId);
        // 未绑定手机号
        if (empty($memberInfo['mobile'])) {
            throw new MemberException(MemberException::MEMBER_MOBILE_NOT_EXIST);
        }

        // 已绑定手机号 不是操作员
        if (!$exists && !empty($memberInfo['mobile'])) {
            throw new MemberException(MemberException::BIND_MOBILE_NOT_INVALI_ERROR);
        }
        return $this->success();
    }

    /**
     * 参数处理
     * @param string $memberId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function process(string $memberId)
    {
        $data = MemberModel::find()->where(['id' => $memberId])->first();

        $params = [
            'username' => $data['mobile'], // 核销邀请的人员要用手机号创建w7账号
            'role_id' => ManagerRoleModel::find()->where(['is_default' => '1', 'name' => '默认-核销员'])->select(['id'])->first()['id'],
            'name' => '',
            'contact' => $data['mobile'],
            'status' => '1',
            'create_uid' => '',
            'password' => StringHelper::random(4, true) . StringHelper::random(4, false, true),// 默认8位随机密码
            'member_id' => $data['id'],
            'verify_point_id' => '',
            'source' => 1,  // 来源 1是核销邀请码
        ];

        return $params;
    }

    /**
     * 判断二维码是否失效
     * @return \yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionInvalid(): \yii\web\Response
    {
        $clientType = RequestHelper::header('Client-Type');
        $time = RequestHelper::postInt('time');
        $result = CacheHelper::get(CacheTypeConstant::CACHEKEY . $clientType . $time);
        // 二维码或链接已失效
        if (isset($result) && empty($result)) {
            throw new MemberException(MemberException::QRCODE_URL_INVALI_ERROR);
        }
        return $this->success();
    }

    /**
     * 绑定核销员
     * @return \yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionBindVerifier(): \yii\web\Response
    {
        $uid = RequestHelper::post('uid');
        $time = RequestHelper::postInt('time');
        $memberId = RequestHelper::post('member_id');
        // 参数错误
        if (empty($memberId) || $memberId == 0 || $memberId == 'undefined' || empty($time)) {
            throw new MemberException(MemberException::CHANGE_LEVEL_PARAM_ERROR);
        }
        $exists = ManagerModel::find()->where(['member_id' => $memberId])->count();
        // 已是核销员
        if ($exists > 0) {
            throw new MemberException(MemberException::ALREADY_VALIDATOR_ERROR);
        }
        $memberInfo = MemberModel::getMemberDetail($memberId);

        $params = $this->process($memberId);
        $clientType = RequestHelper::header('Client-Type');
        // 添加前判断二维码是否已失效
        $cache = CacheHelper::get(CacheTypeConstant::CACHEKEY . $clientType . $time);
        if (!isset($cache) || empty($cache)) {
            throw new MemberException(MemberException::QRCODE_URL_INVALI_ERROR);
        }
        $params['create_uid'] = $uid;
        // 创建操作员等
        $res = ManagerService::createByPost($params);
        if (is_error($res)) {
            throw new MemberException(MemberException::BIND_USER_ERROR, $res['message']);
        }

        // 保存成功 清除二维码 保证二维码唯一性
        CacheHelper::delete(CacheTypeConstant::CACHEKEY . $clientType . $time);

        // 发送短信 通知密码
        $messageData = [
            'password' => $params['password'],
            'role' => '角色-默认核销员',
            'account' => $params['username'],
            'created_at' => DateTimeHelper::now(),
        ];
        // 发送消息
        $notice = NoticeComponent::getInstance(NoticeTypeConstant::VERIFY_QRCODE_BIND_SUCCESS, $messageData, 'verify');
        if (!is_error($notice)) {
            $notice->sendMessage($memberId);
        }

        return $this->success();
    }

    /**
     * 获取积分设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetCreditSet()
    {
        $res = ShopSettings::get('sysset.credit');
        // 原抵扣设置
        $deductSet = ShopSettings::get('sale.basic.deduct');

        $data = [
            'credit_text' => $res['credit_text'],
            'credit_limit_type' => $res['credit_limit_type'],
            'credit_limit' => $res['credit_limit'],
            'credit_state' => $deductSet['credit_state'],
            'credit_num' => $deductSet['credit_num'],
            'basic_credit_num' => $deductSet['basic_credit_num'],
        ];

        return $this->result(['data' => $data]);
    }

}
