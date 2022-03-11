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


use shopstar\bases\KdxAdminAccountApiController;
use shopstar\constants\user\UserAuditStatusConstant;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\CoreSettings;
use shopstar\models\user\UserModel;
use shopstar\models\user\UserProfileModel;
use shopstar\exceptions\adminAccount\UserRegisterException;
use yii\helpers\Json;

/**
 * 审核
 * Class LoginController
 * @package modules\account\manage
 */
class AuditController extends KdxAdminAccountApiController
{
    /**
     * 表单信息
     * @throws UserRegisterException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        if (RequestHelper::isGet()) {
            $result['setting'] = CoreSettings::get('user_setting');
            $result['user_info'] = UserModel::getOne($this->userId, [
                'select' => [
                    'id',
                    'username',
                    'audit_status',
                ]
            ]);

            //解析
            if (!empty($result['user_info']['profile']['form_info'])) {
                $result['user_info']['profile']['form_info'] = Json::decode($result['user_info']['profile']['form_info']);
            }

            return $this->result($result);
        }

        $post = RequestHelper::post();

        //开启事务
        $tr = \Yii::$app->db->beginTransaction();
        try {

            //修改用户审核状态
            $userModel = UserModel::findOne($this->userId);
            if (empty($userModel)) {
                throw new \Exception('用户不存在');
            }

            if($userModel->audit_status != UserAuditStatusConstant::AUDIT_STATUS_CHECK_PASS){
                //赋值审核状态
                $userModel->audit_status = UserAuditStatusConstant::AUDIT_STATUS_CHECK_PADDING;
            }

            if (!$userModel->save()) throw new \Exception('修改失败');

            //添加表单信息
            $userProfileModel = UserProfileModel::findOne(['user_id' => $this->userId]);
            if (empty($userProfileModel)) {
                $userProfileModel = new UserProfileModel();
                $userProfileModel->user_id = $this->userId;

            }

            $userProfileModel->setAttributes([
                'form_info' => $post['form_info'] ? Json::encode($post['form_info']) : '',
                'avatar' => $post['avatar'] ?? ''
            ]);

            if (!$userProfileModel->save()) throw new \Exception('修改失败(1)');

            $tr->commit();
        } catch (\Exception $exception) {
            $tr->rollBack();
            throw new UserRegisterException(UserRegisterException::AUDIT_FORM_ERROR);
        }

        return $this->result([
            'audit_status' => $userModel->audit_status
        ]);
    }

    /**
     * 获取审核信息
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionGetAuditInfo()
    {
        $user = UserModel::getOne($this->userId, [
            'select' => [
                'id',
                'status',
                'audit_status',
            ]
        ]);

        if (empty($user)) {
            throw new UserRegisterException(UserRegisterException::AUDIT_USER_NOT_EXIST_ERROR);
        }

        //解析表单数据
        $user['profile']['form_info'] = Json::decode($user['profile']['form_info']);

        //缓存key
        $key = 'user_audit_pass_' . $user['id'];
        $cache = CacheHelper::get($key);
        if ($user['audit_status'] == UserAuditStatusConstant::AUDIT_STATUS_CHECK_PASS && $cache) {
            CacheHelper::delete($key);
        }

        return $this->result([
            'user' => $user
        ]);
    }
}