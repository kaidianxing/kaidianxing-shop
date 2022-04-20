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

namespace install\services;

use shopstar\helpers\KdxCloudHelper;
use shopstar\models\core\CoreSettings;
use yii\db\Exception;
use yii\helpers\Json;

/**
 * 安装服务
 * Class CheckEnvService
 * @package install\services
 * @author likexin
 */
class InstallService
{

    /**
     * 创建表结构
     * @return array
     * @author likexin
     */
    public static function createTableStruct(): array
    {
        // 定义数据库表结构json路径
        $jsonPath = SHOP_STAR_PATH . '/install/data/db_struct.json';
        if (!is_file($jsonPath)) {
            return error('db_struct.json不存在无法继续安装');
        }

        // 检测文件是否为空
        $dbStruct = file_get_contents($jsonPath);
        if (empty($dbStruct)) {
            return error('db_struct.json数据为空无法继续安装');
        }
        // 转为array
        $array = Json::decode($dbStruct);

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            foreach ($array as $item) {
                \Yii::$app->db->createCommand($item)->execute();
            }

            // 提交事务
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();
            return error('安装数据表错误:' . $exception->getMessage());
        }

        return [];
    }

    /**
     * 创建默认数据
     * @return array
     * @author likexin
     */
    public static function createDefaultData(): array
    {
        // 定义数据库表结构json路径
        $jsonPath = SHOP_STAR_PATH . '/install/data/db_default.json';
        if (!is_file($jsonPath)) {
            return error('db_default.json不存在无法继续安装');
        }

        // 检测文件是否为空
        $dbDefault = file_get_contents($jsonPath);
        if (empty($dbDefault)) {
            return error('db_default.json数据为空无法继续安装');
        }
        // 转为array
        $array = Json::decode($dbDefault);

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            foreach ($array as $item) {
                \Yii::$app->db->createCommand($item)->execute();
            }

            // 提交事务
            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();
            return error('创建默认数据错误:' . $exception->getMessage());
        }

        return [];
    }

    /**
     * 注册站点
     * @return array
     * @author likexin
     */
    public static function registerSite(): array
    {
        // 调用kdx-cloud自动注册站点
        $response = KdxCloudHelper::post('/install/site/register', [
            'site_domain' => \Yii::$app->request->hostName,
        ]);
        if (is_error($response)) {
            return $response;
        }

        // 存储授权码、站点ID
        try {
            CoreSettings::set('licence', [
                'site_id' => isset($response['site']['site_id']) ? (string)$response['site']['site_id'] : '0',
                'licence_code' => $response['site']['auth_code'] ?? '',
            ]);
        } catch (Exception $e) {
            return error($e->getMessage());
        }

        return [];
    }

}