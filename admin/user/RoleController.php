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


use shopstar\components\permission\Permission;
use shopstar\exceptions\UserException;
use shopstar\helpers\RequestHelper;
use shopstar\models\role\ManagerRoleModel;
use shopstar\bases\KdxAdminApiController;

class RoleController extends KdxAdminApiController
{
    /**
     * 角色列表
     * @action index
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $params = [
            'searchs' => [
                ['status', 'int', 'status'],
                ['name', 'like', 'keyword']
            ],
            'where'   => [
                'is_deleted' => 0
            ],
            'select'  => [
                'id',
                'name',
                'status',
                'perms',
                'is_default',
            ],
            'with'    => [
                // 删除未开启和已删除的操作员
                'manage' => function ($query) {
                    $query->select('role_id, name')->where(['status' => 1, 'is_deleted' => 0]);
                }
            ],
            'orderBy' => [
                'is_default' => SORT_DESC,
                'created_at' => SORT_DESC
            ],
            'groupBy' => 'id'
        ];


        // 获取列表
        $roles = ManagerRoleModel::getColl($params, [
            'callable' => function (&$row) {
                $row['operator_num'] = count($row['manage']);
                unset($row['manage']);
                // 判断此角色是否含有订单核销权限
                $row['perms'] = explode('|',$row['perms']);
                $perms = [];
                foreach ($row['perms'] as $permKey => $permValue) {
                    if ($permValue == 'verify.verification.manage') {
                        $perms[] = $permValue;
                    }
                }
                $row['perms'] = $perms;
            }
        ]);

        return $this->result($roles);
    }

    /**
     * 创建角色
     * @action create
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCreate()
    {
        $post = RequestHelper::post();
        if (empty($post['name'])) {
            throw  new UserException(UserException::PARAMS_ERROR);
        }
        $post['status'] = RequestHelper::post('status', ManagerRoleModel::STATUS_ENABLE);
        $post['perms'] = RequestHelper::post('perms', '');
        
        ManagerRoleModel::createRole($post);
        return $this->result('创建角色成功');
    }

    /**
     * 编辑角色
     * @action edit
     * @return array|\yii\web\Response
     * @throws UserException|\Matrix\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::get('id');
        $info = ManagerRoleModel::getInfo($id, $this->shopType);
        return $this->result(['data' => $info]);
    }

    /**
     * 更改角色保存
     * @action save
     * @return array|\yii\web\Response
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSave()
    {
        $post = RequestHelper::post();

        // 参数校验
        if (empty($post['name'])) {
            throw  new UserException(UserException::PARAMS_ERROR);
        }
        if (!isset($post['status'])) {
            throw  new UserException(UserException::PARAMS_ERROR);
        }

        $roleId = $post['id'];
        ManagerRoleModel::saveByPost($roleId, $post);


        return $this->result('操作成功');
    }

    /**
     * 删除角色
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
        
        ManagerRoleModel::deleteById($id);
        return $this->result('操作成功');
    }

    /**
     * 获取所有权限
     * @action get-all-perms
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetAllPerms()
    {
        $allPerm = Permission::getPermTreeForRole($this->shopType);;

        return $this->result(['data' => $allPerm]);
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
        
        ManagerRoleModel::forbidden($id);

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
        
        ManagerRoleModel::active($id);

        return $this->result('操作成功');
    }

    /**
     * 获取所有角色
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAllRole()
    {
        
        $allRole =  ManagerRoleModel::allRoles();

        return $this->result(['data' => $allRole]);
    }
}
