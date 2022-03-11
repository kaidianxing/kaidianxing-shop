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
use shopstar\helpers\FileHelper;
use shopstar\helpers\StringHelper;
use shopstar\services\core\CoreAppService;
use shopstar\models\role\ManagerModel;
use shopstar\models\shop\ShopAppModel;
use shopstar\bases\KdxAdminApiController;
use yii\helpers\Json;

/**
 *
 * Class Permission
 * @package shopstar\components\permission
 */
class Permission
{
    // Client-Type 区分各端   50:单店铺/平台   52:商户 53: B端
    // shop-type   区分单店铺/平台
    // shop-id     取应用权限


    /**
     * 检测权限
     * @param KdxAdminApiController $controller
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function check(KdxAdminApiController $controller): bool
    {
        // 获取当前控制器权限
        $controllerPerm = self::getControllerPerm(str_replace("kdx/api","manage",$controller->uniqueId), $controller->shopType);

        // 没有配置 返回false
        if (empty($controllerPerm)) {
            return false;
        }
        // 判断店铺应用权限 TODO 青岛开店星信息技术有限公司 暂时不用判断
        if ($controllerPerm['is_plugin']) {
            // 根据店铺类型 判断
        }
        
        // 获取角色权限
        $roles = ManagerModel::getPerms($controller->userId, $controller->shopType);
        // 获取当前控制器的perm key
        $permKey = $controllerPerm['actions'][$controller->action->id];
        // 兼容数组格式
        if (is_array($permKey)) {
            $permIntersect = array_intersect($permKey, $roles);
            if (empty($permIntersect)) {
                return false;
            }
        } else {
            // 用户无权限
            if (!in_array($permKey, $roles)) {
                return false;
            }
        }


        // return
        return true;
    }

    /**
     * 获取所有权限key
     * @param int $shopType
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAllPermKey(int $shopType): array
    {
        // 获取所有
        $permKey = CacheHelper::get('all_perm_key_' . $shopType);
        if (empty($permKey)) {
            $controllerTree = self::getAllPermTree($shopType);
            // 权限key
            $permKey = [];
            foreach ($controllerTree as $item) {
                $perm = Json::decode($item);
                // 处理actions包含depends的情况
                $permKeyItem = [];
                foreach ($perm['actions'] as $action) {
                    if (is_array($action)) {
                        $permKeyItem = array_merge($permKeyItem, array_values($action));
                    } else {
                        $permKeyItem[] = $action;
                    }
                }
                // 取值 合并 去重
                $permKey[$perm['identity']] = array_unique(array_merge($permKeyItem, $permKey[$perm['identity']] ?? []));
            }
            // 缓存 不判断是否有权限
            CacheHelper::set('all_perm_key_' . $shopType, Json::encode($permKey));
        } else {
            $permKey = Json::decode($permKey);
        }

        // 二维转一维
        $permKey = array_reduce($permKey, function ($result, $value) {
            return array_merge($result, ($value));
        }, array());

        // 去重
        return array_values(array_unique($permKey));
    }

    /**
     * 创建权限树
     * @param int $shopType
     * @author 青岛开店星信息技术有限公司
     */
    private static function createAllPermTree(int $shopType)
    {
        // 创建个key来判断是否创建完成 如果没有完成  等待完成再继续
        // is_created_perm_cache 空 未创建  1创建中  2创建完成
        $redis = \Yii::$app->redis;
        $isCreatedCache = $redis->setnx('kdx_shop_is_created_perm_cache_' . $shopType, 0);
        if ($isCreatedCache == 1) {
            // 创建中
            CacheHelper::set('is_created_perm_cache_' . $shopType, 1);
        } else if ($isCreatedCache == 0) {
            // 如果正在创建 则等待创建完成
            while (true) {
                // 睡眠 0.01秒
                usleep(10000);
                $iFinish = CacheHelper::get('is_created_perm_cache_' . $shopType);
                if ($iFinish == 2) {
                    break;
                }
            }
        }

        // 如果不存在key 重新创建
        if (!\Yii::$app->redis->exists(BasePermissionConfig::getCacheKey($shopType))) {
            // shop 下配置
            $configClass = FileHelper::fileGlob(SHOP_STAR_PATH . '/config/modules/permission/', ['recursive' => false]);
            foreach ($configClass as $item) {
                if (!StringHelper::exists($item, 'PermissionConfig')) {
                    continue;
                }
                // 拼接class
                $fileName = basename($item, '.php');
                $className = 'shopstar\config\modules\permission\\' . $fileName;
                /** @var $config BasePermissionConfig */
                $config = new $className($shopType);
                $config->createPermTree($shopType);
            }

            // plugins 下配置
            $plugins = FileHelper::fileGlob(SHOP_STAR_PATH . '/apps/', ['onlyDir' => true, 'recursive' => false]);
            foreach ($plugins as $plugin) {
                $pluginName = basename($plugin);
                $className = 'apps\\' . $pluginName . '\config\PermissionConfig';
                if (class_exists($className)) {
                    /** @var $config BasePermissionConfig */
                    $config = new $className($shopType);
                    $config->createPermTree($shopType);
                }
            }
            // 创建完成
            CacheHelper::set('is_created_perm_cache_' . $shopType, 2);
        }
    }

    /**
     * 获取所有权限
     * @param int $shopType
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    private static function getAllPermTree(int $shopType)
    {
        // 先判断是否需要重新创建
        self::createAllPermTree($shopType);

        return \Yii::$app->redis->hvals(BasePermissionConfig::getCacheKey($shopType));
    }

    /**
     * 获取控制器权限
     * @param string $controllerName
     * @param int $shopType
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private static function getControllerPerm(string $controllerName, int $shopType): array
    {
        // 先判断是否需要重新创建
        self::createAllPermTree($shopType);

        // 读取缓存  (缓存不判断权限, 只要有, 就缓存
        $perms = \Yii::$app->redis->hget(BasePermissionConfig::getCacheKey($shopType), $controllerName);

        // 缓存没有 配置错误 返回空
        if (empty($perms)) {
            return [];
        } else {
            $perms = Json::decode($perms);
        }

        return $perms;
    }

    /**
     * 获取角色用的权限树结构
     * @param int $shopType
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPermTreeForRole(int $shopType): array
    {
        // 重新读取config 每次生成 用自己的缓存
        $perm = [];
        // shop 下配置
        $configClass = FileHelper::fileGlob(SHOP_STAR_PATH . '/config/modules/permission/', ['recursive' => false]);
        foreach ($configClass as $item) {
            if (!StringHelper::exists($item, 'PermissionConfig')) {
                continue;
            }
            // 拼接class
            $fileName = basename($item, '.php');
            $className = 'shopstar\config\modules\permission\\' . $fileName;
            /** @var $config BasePermissionConfig */
            $config = new $className($shopType);
            $config->getPermForRole($perm, $shopType);
        }

        // plugins 下配置
        $plugins = FileHelper::fileGlob(SHOP_STAR_PATH . '/apps/', ['onlyDir' => true, 'recursive' => false]);
        foreach ($plugins as $plugin) {
            $pluginName = basename($plugin);
            $className = 'apps\\' . $pluginName . '\config\PermissionConfig';
            if (class_exists($className)) {
                /** @var $config BasePermissionConfig */
                $config = new $className($shopType);
                $config->getPermForRole($perm, $shopType);
            }
        }

        $allPerm = array_filter(array_merge(self::$rolePermSort, $perm));

        // 处理应用权限
        $authApp = CoreAppService::getManageShowApps();

        foreach ($allPerm as $index => $item) {
            if (isset($authApp[$index]) && !$authApp[$index]) {
                unset($allPerm[$index]);
            }
        }

        // 处理显示顺序 去空 并返回
        return array_values($allPerm);
    }

    /**
     * @var array 角色权限显示顺序
     */
    private static $rolePermSort = [
        'shop' => [],
        'goods' => [],
        'order' => [],
        'member' => [],
        'sale' => [],
        'finance' => [],
        'statistics' => [],
        'channel' => [],
        'apps' => [],
        'sysset' => [],
        'system' => [],
        'commission' => [],
        'notice' => [],
        'goodsHelper' => [],
        'newGifts' => [],
        'rechargeReward' => [],
        'consumeReward' => [],
        'poster' => [],
        'diyform' => [],
        'form' => [],
        'printer' => [],
        'shoppingReward' => [],
        'broadcast' => [],
        'expressHelper' => [],
        'persell' => [],
        'seckill' => [],
        'verify' => [],
        'groups' => [],
        'virtualAccount' => [],
        'commentHelper' => [],
    ];

}