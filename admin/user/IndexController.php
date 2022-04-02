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

namespace shopstar\admin\user;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\CacheTypeConstant;
use shopstar\constants\log\user\UserLogConstant;
use shopstar\constants\user\UserAuditStatusConstant;
use shopstar\exceptions\UserException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\MemberModel;
use shopstar\models\role\ManagerModel;
use shopstar\models\role\ManagerRoleModel;
use shopstar\models\role\ManagerVerifyMapModel;
use shopstar\models\user\UserModel;
use shopstar\services\role\ManagerService;

/**
 * 操作员
 * Class IndexController
 * @package shopstar\admin\sysset
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends KdxAdminApiController
{

    /**
     * 操作员列表
     * @action index
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $where = [
            'is_deleted' => 0,
            'is_root' => 0,
        ];

        $params = [
            'searchs' => [
                ['status', 'int', 'status'],
                ['role_id', 'int', 'role_id'],
                [['name', 'contact'], 'like', 'keyword']
            ],
            'where' => $where,
            'select' => [
                'id as manage_id',
                'uid',
                'role_id',
                'name',
                'contact',
                'status',
            ],
            'with' => ['roleName' => function ($query) {
                $query->select('id, name')->where(['status' => ManagerRoleModel::STATUS_ENABLE, 'is_deleted' => 0]);
            }],
            'orderBy' => [
                'updated_at' => SORT_DESC
            ]
        ];

        // 获取列表
        $managers = ManagerModel::getColl($params);

        return $this->result($managers);
    }

    /**
     * 编辑
     * @action edit
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::get('id');

        $where = ['id' => $id];
        $fields = 'id,uid,is_root,role_id,name,contact,status,member_id,source';
        $info = ManagerModel::getInfo($where, $fields);
        if ($info['member_id'] != 0) {
            $memberInfo = MemberModel::getColl([
                'where' => [
                    'm.is_deleted' => 0,
                    'm.id' => $info['member_id'],
                ],
                'select' => [
                    'm.id',
                    'm.avatar',
                    'm.nickname',
                    'm.mobile',
                    'm.source',
                    'm.created_at',
                ],
                'alias' => 'm',
                'leftJoins' => [
                    [MemberGroupMapModel::tableName() . ' gm', 'gm.member_id=m.id']
                ],
                'with' => [
                    'groups'
                ]
            ], [
                'onlyList' => true,
                'pager' => false,
                'callable' => function (&$row) {
                    $row['group_name'] = $row['groups'][0]['group_name'] ?? '';
                    unset($row['groups']);
                    unset($row['groupsMap']);
                }
            ]);
            $info['member'] = $memberInfo[0] ?? [];

        }

        return $this->result($info);
    }

    /**
     * 更改管理员保存
     * @return array|\yii\web\Response
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSave()
    {
        $post = RequestHelper::post();

        $params = [];
        // 参数校验
        if (empty($post['id'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_SAVE_ID_NOT_EMPTY);
        }
        $params['id'] = $post['id'];
        if (empty($post['role_id'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_SAVE_ROLE_NOT_EMPTY);
        }
        $params['role_id'] = $post['role_id'];
        if (empty($post['name'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_SAVE_NAME_NOT_EMPTY);
        }
        $params['name'] = $post['name'];
        if (empty($post['contact'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_SAVE_CONTACT_NOT_EMPTY);
        }
        if (!ValueHelper::isMobile($post['contact']) && !ValueHelper::isTelephone($post['contact'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_CONTACT_INVALID);
        }
        $params['contact'] = $post['contact'];
        if (!isset($post['status'])) {
            $post['status'] = ManagerModel::STATUS_ENABLE;
        }
        $params['status'] = $post['status'];
        $params['member_id'] = $post['member_id'] ?? '0';
        $params['verify_point_id'] = $post['verify_point_id'];
        $params['password'] = $post['password'] ?? '';
        if (!empty($params['password']) && is_error(ValueHelper::checkPassword($params['password']))) {
            throw new UserException(UserException::MANAGE_USER_INDEX_PASSWORD_TYPE_INVALID);
        }
        $res = ManagerService::saveByPost($params);
        $primary = [
            '操作员账号' => ManagerModel::getInfo(['id' => $params['id']], 'id,uid')['user']['username'] ?? '',
            '所属角色' => ManagerRoleModel::find()->where(['id' => $params['role_id']])->first()['name'],
            '绑定会员' => $params['member_id'],
            '状态' => $params['status'] ? '启用' : '关闭',
            '姓名' => $params['name'],
            '手机号' => $params['contact'],
        ];
        if ($params['member_id'] == '0') {
            unset($primary['绑定会员']);
        }
        $res && LogModel::write(
            $this->userId,
            UserLogConstant::USER_EDIT,
            UserLogConstant::getText(UserLogConstant::USER_EDIT),
            $post['id'],
            [
                'log_data' => $params,
                'log_primary' => $primary,
                'dirty_identify_code' => [
                    UserLogConstant::USER_EDIT,
                    UserLogConstant::USER_ADD,
                ]
            ]
        );
        if (is_error($res)) {
            return $this->error($res);
        }
        return $this->result('编辑成功');
    }

    /**
     * 添加操作员
     * @return array|\yii\web\Response
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCreate()
    {
        $post = RequestHelper::post();

        $params = [];
        // 参数校验
        if (empty($post['username'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_CREATE_USERNAME_NOT_EMPTY);
        }
        $params['username'] = $post['username'];
        if (empty($post['role_id'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_CREATE_ROLE_NOT_EMPTY);
        }
        $params['role_id'] = $post['role_id'];
        if (empty($post['name'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_CREATE_NAME_NOT_EMPTY);
        }
        $params['name'] = $post['name'];
        if (empty($post['contact'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_CREATE_CONTACT_NOT_EMPTY);
        }
        if (!ValueHelper::isMobile($post['contact']) && !ValueHelper::isTelephone($post['contact'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_CONTACT_INVALID);
        }
        $params['contact'] = $post['contact'];
        if (!isset($post['status'])) {
            $post['status'] = ManagerModel::STATUS_ENABLE;
        }
        $params['status'] = $post['status'];
        $params['create_uid'] = intval($this->userId);
        $params['password'] = $post['password'] ?? '';

        // 判断是否需要填写密码
        $res = UserModel::checkUserStatus($params['username']);
        if ($res['type'] != 1 && empty($params['password'])) {
            throw new UserException(UserException::MANAGE_USER_INDEX_CREATE_PASSWORD_NOT_EMPTY);
        }
        if (!empty($params['password']) && is_error(ValueHelper::checkPassword($params['password']))) {
            throw new UserException(UserException::MANAGE_USER_INDEX_PASSWORD_TYPE_INVALID);
        }
        // 如果是核销绑定会员 则添加参数
        $params['member_id'] = $post['member_id'] ?? '0';
        $params['verify_point_id'] = $post['verify_point_id'];

        $res = ManagerService::createByPost($params);
        $primary = [
            '操作员账号' => $post['username'],
            '所属角色' => ManagerRoleModel::find()->where(['id' => $params['role_id']])->first()['name'],
            '绑定会员' => $params['member_id'],
            '状态' => $params['status'] ? '启用' : '关闭',
            '姓名' => $params['name'],
            '手机号' => $params['contact'],
        ];
        if ($params['member_id'] == '0') {
            unset($primary['绑定会员']);
        }

        if (is_error($res)) {
            return $this->error($res);
        }

        LogModel::write(
            $this->userId,
            UserLogConstant::USER_ADD,
            UserLogConstant::getText(UserLogConstant::USER_ADD),
            $res->id ?? '',
            [
                'log_data' => $params,
                'log_primary' => $primary,
                'dirty_identify_code' => [
                    UserLogConstant::USER_ADD,
                ]
            ]
        );
        return $this->result('添加成功');
    }

    /**
     * 删除
     * @action delete
     * @return array|\yii\web\Response
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::postInt('id');
        if ($id < 1) {
            $ids = RequestHelper::post('ids');
            if (mb_strlen($ids)) {
                $id = explode(',', $ids);
            }
        }

        $uids = ManagerModel::find()->where(['id' => $id])->select(['uid'])->get();

        foreach ($uids as $uid) {
            self::deleteCache(CacheTypeConstant::MANAGE_PROFILE, [$uid['uid']]);
        }
        $managerIds = [];
        if (is_array($id)) {
            $managerIds = $id;
        } else {
            $managerIds = [$id];
        }
        ManagerModel::deleteByIds($managerIds);
        // 同步删除操作员关联的核销点
        ManagerVerifyMapModel::deleteAll(['manager_id' => $id]);
        LogModel::write(
            $this->userId,
            UserLogConstant::USER_DELETE,
            UserLogConstant::getText(UserLogConstant::USER_DELETE),
            $id,
            [
                'log_data' => $id,
                'log_primary' => [
                    '操作员账号' => ManagerModel::getInfo(['id' => $id], 'id,uid')['user']['username'] ?? '',
                    '状态' => '已删除',
                ]
            ]
        );
        return $this->result('操作成功');
    }

    /**
     * 禁用
     * @return array|\yii\web\Response
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionForbidden()
    {
        $id = RequestHelper::postInt('id');
        if ($id < 1) {
            $ids = RequestHelper::post('ids');
            if (mb_strlen($ids)) {
                $id = explode(',', $ids);
            }
        }
        if (!is_array($id)) {
            $id = [$id];
        }
        ManagerModel::forbidden($id);
        return $this->result('操作成功');
    }

    /**
     * 启用
     * @return array|\yii\web\Response
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionActive()
    {
        $id = RequestHelper::postInt('id');
        if ($id < 1) {
            $ids = RequestHelper::post('ids');
            if (mb_strlen($ids)) {
                $id = explode(',', $ids);
            }
        }
        if (!is_array($id)) {
            $id = [$id];
        }
        ManagerModel::active($id);
        return $this->result('操作成功');
    }

    /**
     * 判断是否存在用户
     * @return \yii\web\Response
     * author: 青岛开店星信息技术有限公司
     */
    public function actionCheckUser(): \yii\web\Response
    {
        $username = RequestHelper::get('username');

        $res = UserModel::checkUserStatus($username);

        return $this->success(['data' => $res]);
    }

}