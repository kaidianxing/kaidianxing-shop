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

namespace shopstar\models\role;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\permission\Permission;
use shopstar\constants\CacheTypeConstant;
use shopstar\constants\manage\ManageRootConstant;
use shopstar\exceptions\UserException;
use shopstar\helpers\CacheHelper;
use shopstar\models\user\UserModel;
use shopstar\traits\CacheTrait;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%manager}}".
 *
 * @property int $id
 * @property int $uid 用户id
 * @property int $is_root 是否是店铺超管
 * @property int $role_id 店铺操作员角色id
 * @property string $name 用户名称
 * @property string $contact 联系方式
 * @property int $create_uid 创建人
 * @property int $status 状态 0: 禁用 1: 启用
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $is_deleted 是否删除 0否 1是
 * @property int $member_id 核销绑定会员id
 * @property int $source 来源 1核销邀请码
 */
class ManagerModel extends BaseActiveRecord
{
    use CacheTrait;

    /**
     * 禁用
     */
    const STATUS_DISABLE = 0;

    /**
     * 启用
     */
    const STATUS_ENABLE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid', 'is_root', 'role_id', 'create_uid', 'status', 'is_deleted', 'member_id', 'source'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 128],
            [['contact'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '用户id',
            'is_root' => '是否是店铺超管',
            'role_id' => '店铺操作员角色id',
            'name' => '用户名称',
            'contact' => '联系方式',
            'create_uid' => '创建人',
            'status' => '状态 0: 禁用 1: 启用',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'is_deleted' => '是否删除 0否 1是',
            'member_id' => '核销绑定会员id',
            'source' => '来源 1核销邀请码',
        ];
    }

    /**
     * 获取角色名称
     * @func getRoleName
     * @return \yii\db\ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getRoleName()
    {
        return $this->hasOne(ManagerRoleModel::class, ['id' => 'role_id']);
    }

    /**
     * 前端获取详情
     * @func getInfo
     * @param $where
     * @param $fields
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInfo($where, $fields)
    {
        $info = self::find()
            ->where($where)
            ->select($fields)
            ->with(['role' => function ($query) {
                $query->select('id, name');
            }])
            ->with(['user' => function ($query) {
                $query->select('id, username, password');
            }])
            ->first();
        return $info;
    }

    public function getRole()
    {
        return $this->hasOne(ManagerRoleModel::class, ['id' => 'role_id']);
    }

    public function getUser()
    {
        return $this->hasOne(UserModel::class, ['id' => 'uid']);
    }


    /**
     * 禁用
     * @param $ids
     * @return bool
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function forbidden($ids)
    {
        foreach ($ids as $id) {
            try {
                $attributes = [
                    'status' => self::STATUS_DISABLE
                ];
                $condition = [
                    'id' => $id,
                ];
                self::updateAll($attributes, $condition);
                self::deleteSingleCache($id);
            } catch (\Throwable $exception) {
                throw  new UserException(UserException::MANAGE_USER_INDEX_FORBIDDEN_FAILED);
            }
        }

        return true;
    }

    /**
     * 启用
     * @param $ids
     * @return bool
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function active($ids)
    {
        foreach ($ids as $id) {
            try {
                $attributes = [
                    'status' => self::STATUS_ENABLE
                ];
                $condition = [
                    'id' => $id,
                ];
                self::updateAll($attributes, $condition);
                self::deleteSingleCache($id);
            } catch (\Throwable $exception) {
                throw  new UserException(UserException::MANAGE_USER_INDEX_ACTIVE_FAILED);
            }
        }

        return true;
    }

    /**
     * 删除
     * @param $ids
     * @return bool
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteByIds($ids)
    {
        foreach ($ids as $id) {
            try {
                $where = ['id' => $id];
                self::updateAll(['is_deleted' => 1, 'member_id' => 0], $where);
                self::deleteSingleCache($id);

            } catch (\Throwable $exception) {
                throw  new UserException(UserException::MANAGE_USER_INDEX_DELETE_FAILED);
            }
        }

        return true;
    }

    /**
     * 删除单个缓存
     * @param int $manageId
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteSingleCache(int $manageId)
    {
        $manage = self::findOne(['id' => $manageId]);
        self::deleteCache(CacheTypeConstant::MANAGE_PROFILE, [$manage['uid']]);
    }

    /**
     * 获取用户权限列表
     * @param $memberId
     * @param bool $isRoot
     * @return array
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPerms($memberId, bool $isRoot = false): array
    {
        // 超级管理员返回全部权限
        $manage = self::getStringCache(CacheTypeConstant::MANAGE_PROFILE, [$memberId]);
        if (!empty($manage)) {
            $manage = Json::decode($manage);
        } else {
            $manage = self::find()->where(['uid' => $memberId])->first();
        }

        /**
         * is_root 0普通用户 1公众号创建者 2超级管理员
         */
        if ($isRoot || (int)$manage['is_root'] > ManageRootConstant::GENERAL_OPERATOR) {
            $perms = Permission::getAllPermKey();
        } else {
            $perms = ManagerRoleModel::getRolePerms($manage['role_id']);

        }

        if (empty($perms)) {
            return [];
        }

        return $perms;
    }

    /**
     * 删除系统缓存key
     * @param bool $isAll
     * @param string $key
     * @return bool|mixed
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function clearCache(bool $isAll = true, string $key = '')
    {
        if ($isAll) {
            // 删除所有manage
            $cacheManageKey = CacheHelper::getKey(CacheTypeConstant::MANAGE_PROFILE);
            $cacheManageKeyList = \Yii::$app->redis->keys("*{$cacheManageKey}*");
            if (!empty($cacheManageKeyList)) {
                \Yii::$app->redis->del(...$cacheManageKeyList);
            }

            // 删除所有角色权限
            $cacheRolePermsKey = CacheHelper::getKey(CacheTypeConstant::MANAGE_PROFILE);
            $cacheRolePermsKeyList = \Yii::$app->redis->keys("*{$cacheRolePermsKey}*");
            if (!empty($cacheRolePermsKeyList)) {
                \Yii::$app->redis->del(...$cacheRolePermsKeyList);
            }

            // 删除所有w7用户
            $cacheUserKey = CacheHelper::getKey(CacheTypeConstant::USER_PROFILE);
            $cacheUserKeyList = \Yii::$app->redis->keys("*{$cacheUserKey}*");
            if (!empty($cacheUserKeyList)) {
                \Yii::$app->redis->del(...$cacheUserKeyList);
            }
        }

        if (!$isAll && !empty($key)) {
            return \Yii::$app->redis->del($key);
        }

        return true;
    }

}