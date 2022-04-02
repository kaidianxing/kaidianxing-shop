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
use shopstar\exceptions\UserException;
use shopstar\helpers\DateTimeHelper;
use shopstar\traits\CacheTrait;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%manager_role}}".
 *
 * @property int $id
 * @property string $name 角色名称
 * @property string $desc 角色简介
 * @property int $is_default 是否官方默认
 * @property string $created_at 创建时间
 * @property string|null $perms 权限集合\
 * @property int|null $status 是否启用
 * @property string $updated_at 更新时间
 * @property int $is_deleted 是否删除
 */
class ManagerRoleModel extends BaseActiveRecord
{
    use CacheTrait;

    /**
     * 启用
     */
    const STATUS_ENABLE = 1;

    /**
     * 不启用
     */
    const STATUS_DISABLE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_role}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_default', 'status', 'is_deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['perms'], 'string'],
            [['desc'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '角色名称',
            'desc' => '角色简介',
            'is_default' => '是否官方默认',
            'created_at' => '创建时间',
            'perms' => '权限集合',
            'status' => '是否启用',
            'updated_at' => '更新时间',
            'is_deleted' => '是否删除 0否 1是'
        ];
    }

    public function getManage()
    {
        return $this->hasMany(ManagerModel::class, ['role_id' => 'id']);
    }

    public static function createRole(array $post)
    {
        $model = new self();
        $model->name = $post['name'];
        $model->created_at = DateTimeHelper::now();
        $model->perms = $post['perms'];
        $model->status = $post['status'];
        isset($post['is_default']) && $model->is_default = $post['is_default'];
        if ($model->save() === false) {
            throw new UserException(UserException::CREATE_FAILED);
        }
        return true;
    }

    /**
     * 获取角色信息
     * @param $id
     * @return array|null
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInfo($id): ?array
    {
        $info = self::find()
            ->where(compact('id'))
            ->select('id, name, desc, is_default, status, perms')
            ->first();
        if ($info == null) {
            throw new UserException(UserException::RECORD_NOT_FOUND);
        }
        if ($info['is_default'] == 1) {
            throw new UserException(UserException::DEFAULT_IS_CANT_EDIT);
        }

        $info['has_perm'] = explode('|', $info['perms']);
        $info['all_perm'] = Permission::getPermTreeForRole();
        unset($info['perms']);

        return $info;
    }

    /**
     * 保存角色
     * @param $roleId
     * @param $data
     * @return bool
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveByPost($roleId, $data)
    {
        /** @var ManagerRoleModel $info */
        $info = self::find()->where(['id' => $roleId])->one();

        $info->name = $data['name'];
        $info->perms = $data['perms'];
        $info->status = $data['status'];
        if ($info->save() === false) {
            throw new UserException(UserException::SAVE_FAILED);
        }

        // 删除缓存
        self::deleteCache(CacheTypeConstant::ROLE_PERMS, [$roleId]);

        return true;
    }

    /**
     * 删除角色
     * @param $id
     * @return bool
     * @throws UserException
     */
    public static function deleteById($id)
    {
        try {
            self::updateAll(['is_deleted' => 1], ['id' => $id]);

            // 删除缓存
            self::deleteCache(CacheTypeConstant::ROLE_PERMS, [$id]);

        } catch (\Throwable $exception) {
            throw  new UserException(UserException::DELETE_FAILED);
        }

        return true;
    }

    /**
     * 获取所有角色
     * @return array|int|string|\yii\db\ActiveRecord[]
     */
    public static function allRoles()
    {
        $params = [
            'where' => ['status' => 1],
        ];
        $list = self::getColl(
            $params, [
                'pager' => false,
                'onlyList' => true
            ]

        );
        return $list;
    }

    /**
     * 禁用
     * @param $id
     * @return bool
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function forbidden($id)
    {
        try {
            $attributes = [
                'status' => self::STATUS_DISABLE
            ];
            $condition = [
                'id' => $id,
            ];
            self::updateAll($attributes, $condition);

            // 删除缓存
            self::deleteCache(CacheTypeConstant::ROLE_PERMS, [$id]);

        } catch (\Throwable $exception) {
            throw  new UserException(UserException::FORBIDDEN_FAILED);
        }

        return true;
    }

    /**
     * 启用
     * @param $id
     * @return bool
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function active($id)
    {
        try {
            $attributes = [
                'status' => self::STATUS_ENABLE
            ];
            $condition = [
                'id' => $id,
            ];
            self::updateAll($attributes, $condition);

            // 删除缓存
            self::deleteCache(CacheTypeConstant::ROLE_PERMS, [$id]);

        } catch (\Throwable $exception) {
            throw  new UserException(UserException::ACTIVE_FAILED);
        }

        return true;
    }

    /**
     * 获取角色权限
     * @param $roleId
     * @return array
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getRolePerms($roleId): array
    {
        $perms = self::getStringCache(CacheTypeConstant::ROLE_PERMS, [$roleId]);

        if ($perms === false || $perms === null) {
            $perms = self::find()
                ->where(['id' => $roleId, 'status' => self::STATUS_ENABLE])
                ->select('perms')
                ->asArray()
                ->column();

            // 设置缓存
            $permsJson = !empty($perms) ? Json::encode($perms) : $perms;
            self::stringCache(CacheTypeConstant::ROLE_PERMS, $permsJson, [$roleId]);
        } else {
            $perms = Json::decode($perms);
        }

        if (empty($perms)) {
            return [];
        }

        return explode('|', $perms[0]);
    }
}
