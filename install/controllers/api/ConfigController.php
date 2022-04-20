<?php
/**
 * 开店星商城系统1.0
 * @author 青岛开店星信息技术有限公司
 * @copyright Copyright (c) 2015-2021 Qingdao ShopStar Information Technology Co., Ltd.
 * @link https://www.kaidianxing.com
 * @warning This is not a free software, please get the license before use.
 * @warning 这不是一个免费的软件，使用前请先获取正版授权。
 */

namespace install\controllers\api;

use install\bases\BaseController;
use install\models\UserModel;
use shopstar\helpers\FileHelper;
use shopstar\helpers\RequestHelper;
use Symfony\Component\Yaml\Yaml;
use yii\web\Response;

/**
 * 配置相关接口
 * Class ConfigController
 * @package install\controllers\api
 * @author likexin
 */
class ConfigController extends BaseController
{

    /**
     * @var array Controller配置
     */
    public array $config = [
        // 需要POST请求的Actions
        'postActions' => [
            'submit-db',
            'submit-site',
        ],
    ];

    /**
     * 提交设置项
     * @return Response
     * @author likexin
     */
    public function actionSubmitDb(): Response
    {

        // 组成配置参数
        $config = [
            // 附加参数
            'params' => [],
        ];

        // 接收mysql配置参数
        $config['mysql'] = [
            'host' => RequestHelper::post('mysql_host'),
            'port' => RequestHelper::postInt('mysql_port'),
            'database' => RequestHelper::post('mysql_database'),
            'username' => RequestHelper::post('mysql_username'),
            'password' => RequestHelper::post('mysql_password'),
            'prefix' => 'shopstar_',
            'slaves' => [],
        ];

        // 校验mysql参数必填
        if (empty($config['mysql']['host'])) {
            return $this->error('请填写Mysql数据库地址');
        } elseif (empty($config['mysql']['port'])) {
            return $this->error('请填写Mysql数据库端口');
        } elseif (empty($config['mysql']['database'])) {
            return $this->error('请填写Mysql数据库名称');
        } elseif (empty($config['mysql']['username'])) {
            return $this->error('请填写Mysql数据库用户名');
        } elseif (empty($config['mysql']['password'])) {
            return $this->error('请填写Mysql数据库密码');
        }

        // 验证mysql参数正确性
        $checkMysql = $this->checkMysql($config['mysql']);
        if (is_error($checkMysql)) {
            return $this->error('Mysql连接失败: ' . $checkMysql['message']);
        }

        // 接收redis配置参数
        $config['redis'] = [
            'host' => RequestHelper::post('redis_host'),
            'port' => RequestHelper::postInt('redis_port'),
            'password' => RequestHelper::post('redis_password'),
            'database' => 7,
            'permanentDatabase' => 8,
        ];
        if (empty($config['redis']['password'])) {
            $config['redis']['password'] = null;
        }

        // 校验redis参数必填
        if (empty($config['redis']['host'])) {
            return $this->error('请填写Redis数据库地址');
        } elseif (empty($config['redis']['port'])) {
            return $this->error('请填写Redis数据库端口');
        }

        // 验证redis参数正确性
        $checkRedis = $this->checkRedis($config['redis']);
        if (is_error($checkRedis)) {
            return $this->error('Redis连接失败: ' . $checkRedis['message']);
        }

        // 生成配置文件
        $this->buildConfigFile($config);

        return $this->result();
    }

    /**
     * 检测Mysql
     * @param array $config mysql配置
     * @return array|true
     * @author likexin
     */
    private function checkMysql(array $config)
    {
        try {
            /**
             * @var \yii\db\Connection $connection
             */
            $connection = \Yii::createObject([
                'class' => 'yii\db\Connection',
                'commandClass' => 'yii\db\Command',
                'dsn' => "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                'username' => $config['username'],
                'password' => $config['password'],
                'charset' => 'utf8mb4',
                'enableSchemaCache' => false,
            ]);

            // 执行连接
            $connection->open();
        } catch (\Exception $exception) {
            return error($exception->getMessage());
        }

        $connection->close();

        return true;
    }

    /**
     * 检测Redis
     * @param array $config redis配置
     * @return array|bool
     * @author likexin
     */
    private function checkRedis(array $config)
    {
        try {
            /**
             * @var \yii\redis\Connection $connection
             */
            $connection = \Yii::createObject([
                'class' => 'yii\redis\Connection',
                'hostname' => $config['host'],
                'port' => $config['port'],
                'database' => 13,
                'password' => $config['password'],
                'connectionTimeout' => 20,
            ]);

            // 执行连接
            $connection->open();

            // 清除redis
            $connection->flushdb();
        } catch (\Exception $exception) {
            return error($exception->getMessage());
        }

        $connection->close();

        return true;
    }

    /**
     * 提交站点用户信息
     * @return Response
     * @author likexin
     */
    public function actionSubmitSite(): Response
    {
        // 接收参数::超管账号
        $username = RequestHelper::post('username');
        if (empty($username)) {
            return $this->error('请填写系统超管账号');
        }
        // 接收参数::超管密码
        $password = RequestHelper::post('password');
        if (empty($password)) {
            return $this->error('请填写系统超管密码');
        }

        // 创建超管用户
        $create = UserModel::createSuperAdmin($username, $password);
        if (is_error($create)) {
            return $this->error('创建系统超管失败: ' . $create['message']);
        }

        // 写入安装文件
        FileHelper::write(SHOP_STAR_PATH . '/config/install.lock', '');

        return $this->result();
    }

    /**
     * 生成配置文件
     * @param array $config
     * @author likexin
     */
    private function buildConfigFile(array $config)
    {
        // arr2yaml
        $content = Yaml::dump($config);

        // 写入yaml
        FileHelper::write($this->getYamlPath(), $content);
    }

    /**
     * 获取配置文件路径
     * @return string
     */
    private function getYamlPath(): string
    {
        return SHOP_STAR_PATH . '/config/conf.yaml';
    }

}