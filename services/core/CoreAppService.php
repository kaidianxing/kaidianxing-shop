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

namespace shopstar\services\core;

use shopstar\constants\core\CoreAppCategoryConstant;

/**
 * 系统附件服务层
 * Class CreditController
 * @package shopstar\services\core
 * @author likexin
 */
class CoreAppService
{
    public static $_appList;

    public static function getAppListNew($type = 'all')
    {
        if (!isset(static::$_appList[$type]) || empty(static::$_appList[$type])) {

            $appConfigs = [];
            foreach (glob(SHOP_STAR_PATH . '/config/apps/AppConfig.php') as $filename) {
                $appConfig = require($filename);
                if ($type != 'all') {
                    foreach ($appConfig as $k => $v) {
                        if ($v['type'] == $type) {
                            $appConfigs[$k] = $v;
                        }
                    }
                }

            }
            static::$_appList[$type] = $appConfigs;
        }

        return static::$_appList[$type];
    }

    /**
     * @throws \ReflectionException
     */
    public static function getAppListCacheNew($type, $options = []): array
    {
        $list = self::getAppListNew($type);
        if ($searchName = $options['search_name']) {
            foreach ($list as $k => $v) {
                if (!strstr($v['name'], $searchName)) {
                    unset($list[$k]);
                }
            }
        }
        if (!$options['category']) {
            return ['list' => $list];
        }

        // 根据分类生成新的列表
        $newList = self::listClassIfyByCategory($list);

        return [
            'category_list' => array_values($newList),
        ];
    }

    /**
     * 根据分类分组
     * @param array $list
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function listClassIfyByCategory(array $list): array
    {
        $newList = CoreAppCategoryConstant::getAll();

        foreach ($list as $app) {
            $appCategory = $app['category'];

            if (!isset($newList[$appCategory]['list'])) {
                $newList[$appCategory]['list'] = [];
                $newList[$appCategory]['category'] = $appCategory;
            }

            $newList[$appCategory]['list'][] = $app;
        }

        return $newList;
    }

    /**
     * 获取应用名称
     * @param string $identity 应用标识
     * @return mixed|string
     */
    public static function getAppName(string $identity)
    {
        $coreAppList = self::getAppListNew();

        return $coreAppList[$identity]['name'] ?? '';
    }

    /**
     * 获取本地应用的标识
     * @return array
     * @author likexin
     */
    public static function getAppIdentity(): array
    {
        $coreAppList = self::getAppListNew();

        return array_keys($coreAppList);
    }

    /**
     * 获取可用的应用列表
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAppEnableList(): array
    {
        $coreAppList = self::getAppListNew();
        $hasArray = [];
        foreach ($coreAppList as $identity => $coreApp) {
            $hasArray[$identity] = true;
        }
        return $hasArray;

    }

    /**
     * 获取管理端可用应用
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getManageShowApps(): array
    {
        return self::getAppEnableList();
    }
}