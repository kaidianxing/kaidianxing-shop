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

namespace shopstar\components\permission;

use shopstar\helpers\CacheHelper;
use yii\helpers\Json;

/**
 * 权限控制配置
 * Interface PermissionConfigInterface
 * @package shopstar\components\permission
 * @author 青岛开店星信息技术有限公司
 */
class BasePermissionConfig
{
    /**
     * @var string 权限模块标识
     */
    protected $identity = '';

    /**
     * @var string 路由前缀
     */
    protected $prefix = '';

    /**
     * @var string 分组名称
     */
    protected $groupName = '';

    /**
     * @var bool 是否插件
     */
    protected $isPlugin = false;

    /**
     * @var string 缓存key
     */
    protected static $cacheKey = '';

    /**
     * @var array 权限配置
     */
    protected $config = [];

    /**
     * BasePermissionConfig constructor.
     */
    public function __construct()
    {
        // 缓存key
        self::getCacheKey();
    }

    /**
     * 获取权限
     * @author 青岛开店星信息技术有限公司
     */
    public function createPermTree()
    {
        // 遍历配置
        foreach ($this->config as $key => $item) {
            // 权限
            $perm = [
                'identity' => $this->identity, // 标识
                'is_plugin' => $this->isPlugin, // 是否插件
                'group_name' => $this->groupName, // 分组名称 TODO 替换应用名称
            ];

            // 如果是多个
            if (isset($item['multi'])) {
                $perm['actions'] = [];
                foreach ($item['multi'] as $multiItem) {
                    // 获取action perm
                    $permAction = $this->getActionPerm($multiItem);
                    $perm['actions'] = array_merge($perm['actions'], $permAction);
                }
            } else {
                // 权限名称
                $perm['title'] = $item['title'];
                // 获取action perm
                $perm['actions'] = $this->getActionPerm($item);
            }
            // 如果为空 则跳过
            if (empty($perm['actions'])) {
                continue;
            }

            // 缓存
            \Yii::$app->redis->hset(self::$cacheKey, $this->prefix . $key, Json::encode($perm));
        }
    }

    /**
     * 获取actions perm
     * @param array $permMap
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    protected function getActionPerm(array $permMap): array
    {
        $actionPerm = [];
        foreach ($permMap['perm'] as $permKey => $permValue) {
            // 如果有 depends 用这个
            if (!empty($permValue['depends'])) {
                $key = $permValue['depends'];
            } else {
                $key = $permMap['alias'] . '.' . $permKey;
            }
            foreach ($permValue['actions'] as $action) {
                // 组成缓存结构
                $actionPerm[$action] = $key;
            }
        }
        return $actionPerm;
    }

    /**
     * 获取角色用的权限
     * @param array $perm
     * @author 青岛开店星信息技术有限公司
     */
    public function getPermForRole(array &$perm)
    {
        // 第一层数组
        if (!isset($perm[$this->identity])) {
            $perm[$this->identity] = [
                'title' => $this->groupName,
                'module' => $this->identity,
            ];
        }
        // 遍历配置
        foreach ($this->config as $item) {
            if (isset($item['multi'])) {
                foreach ($item['multi'] as $multiItem) {
                    // 权限
                    $childPerm = $this->getChildPerm($multiItem);
                    // 空的可以跳过
                    if (!empty($childPerm)) {
                        // 拼装
                        $perm[$this->identity]['children'][] = [
                            'title' => $multiItem['title'],
                            'perm' => $childPerm
                        ];
                    }
                }
            } else {
                // 权限
                $childPerm = $this->getChildPerm($item);
                // 空的可以跳过
                if (!empty($childPerm)) {
                    // 拼装
                    $perm[$this->identity]['children'][] = [
                        'title' => $item['title'],
                        'perm' => $childPerm
                    ];
                }
            }
        }
        // 如果一个子权限也没有
        if (!isset($perm[$this->identity]['children'])) {
            unset($perm[$this->identity]);
        }
    }

    /**
     * 获取xxxx
     * @param array $perm
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    protected function getChildPerm(array $perm): array
    {
        $childPerm = [];
        foreach ($perm['perm'] as $permKey => $permValue) {
            // 该权限支持当前店铺类型 并且不包含depends
            if (!isset($permValue['depends'])) {
                // 组装需要的格式
                $childPerm[] = [
                    'title' => $permValue['title'],
                    'perm_key' => $perm['alias'] . '.' . $permKey
                ];
            }
        }
        return $childPerm;
    }

    /**
     * 获取缓存key
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCacheKey(): string
    {
        self::$cacheKey = 'kdx_shop_controller_perm_hash';
        return self::$cacheKey;
    }

    /**
     * 删除权限缓存
     * 所有的关于权限的
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteConfigCache()
    {
        CacheHelper::delete('is_created_perm_cache_0');
        CacheHelper::delete('is_created_perm_cache_20');
        CacheHelper::delete('is_created_perm_cache_21');

        \Yii::$app->redis->del('kdx_shop_is_created_perm_cache_0');
        \Yii::$app->redis->del('kdx_shop_is_created_perm_cache_20');
        \Yii::$app->redis->del('kdx_shop_is_created_perm_cache_21');

        CacheHelper::delete('all_perm_key_0');
        CacheHelper::delete('all_perm_key_20');
        CacheHelper::delete('all_perm_key_21');

        \Yii::$app->redis->del('kdx_shop_controller_perm_hash_0');
        \Yii::$app->redis->del('kdx_shop_controller_perm_hash_20');
        \Yii::$app->redis->del('kdx_shop_controller_perm_hash_21');
    }

}