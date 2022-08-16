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

namespace shopstar\admin\system;

use Exception;
use shopstar\bases\KdxAdminApiController;
use shopstar\components\storage\bases\StorageDriverConstant;
use shopstar\components\storage\drivers\CosDriver;
use shopstar\components\storage\drivers\OssDriver;
use shopstar\components\storage\drivers\QiniuDriver;
use shopstar\components\storage\StorageComponent;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\core\CoreSettings;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * 远程存储控制器
 * Class StorageController.
 * @package shopstar\admin\system
 */
class StorageController extends KdxAdminApiController
{
    /**
     * 需要POST的Action
     * @var string[][]
     */
    public $configActions = [
        'postActions' => [
            'set',
            'get-qiniu-domain',
            'get-oss-bucket',
            'get-cos-bucket',
        ],
    ];

    /**
     * 脱敏key
     * @var array|string[][]
     */
    private array $secretKeys = [
        StorageDriverConstant::DRIVE_FTP => ['username', 'password'],
        StorageDriverConstant::DRIVE_QINIU => ['access_key', 'secret_key'],
        StorageDriverConstant::DRIVE_OSS => ['access_key', 'secret_key'],
        StorageDriverConstant::DRIVE_COS => ['secret_id', 'secret_key'],
    ];

    /**
     * 获取设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $settings = CoreSettings::get('storage', []);

        // 数据脱敏
        $settings = StringHelper::doSecretArray($settings, $this->secretKeys);

        $data = [
            'settings' => $settings,
        ];

        return $this->result($data);
    }

    /**
     * 提交保存
     * @return array|int[]|Response
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSet()
    {
        // 当前启用类型
        $type = RequestHelper::post('type');

        // 如果为空
        if (empty($type)) {
            return $this->error('文件储存类型错误');
        }

        // 获取全部类型，验证当前传入是否合法
        $allowType = StorageDriverConstant::getAll();
        if (!isset($allowType[$type])) {
            return $this->error('文件储存类型错误');
        }

        $settings = [
            // 当前设置类型
            'type' => $type,
            // 本地存储
            StorageDriverConstant::DRIVE_LOCAL => [],
            // FTP存储
            StorageDriverConstant::DRIVE_FTP => [
                'url' => RequestHelper::post('ftp.url'), // 链接
                'host' => RequestHelper::post('ftp.host'), // 服务器
                'port' => RequestHelper::post('ftp.port'), // 端口
                'username' => RequestHelper::post('ftp.username'),  // 用户名
                'password' => RequestHelper::post('ftp.password'),     // 密码
                'passive_mode' => RequestHelper::postInt('ftp.passive_mode'),     // 被动模式
                'ssl' => RequestHelper::postInt('ftp.ssl'),     // ssl
                'timeout' => RequestHelper::postInt('ftp.timeout'),     // 超时时间
                'path' => RequestHelper::post('ftp.path'),     // 相对路径
                'scheme' => RequestHelper::post('ftp.scheme'),
            ],
            // 七牛存储
            StorageDriverConstant::DRIVE_QINIU => [
                'url' => RequestHelper::post('qiniu.url'), // 链接
                'access_key' => RequestHelper::post('qiniu.access_key'), // AccessKey
                'secret_key' => RequestHelper::post('qiniu.secret_key'),  // SecretKey
                'bucket' => RequestHelper::post('qiniu.bucket'),     // bucket
                'scheme' => RequestHelper::post('qiniu.scheme'),
            ],
            // 阿里云OSS
            StorageDriverConstant::DRIVE_OSS => [
                'url' => RequestHelper::post('oss.url'), // 链接
                'access_key' => RequestHelper::post('oss.access_key'), // AccessKey
                'secret_key' => RequestHelper::post('oss.secret_key'),  // SecretKey
                'bucket' => RequestHelper::post('oss.bucket'),     // bucket @形式存储  填写accesskey和secretkey之后会出现选择
                'scheme' => RequestHelper::post('oss.scheme'),
            ],
            // 腾讯云COS
            StorageDriverConstant::DRIVE_COS => [
                'url' => RequestHelper::post('cos.url'), // 链接
                'app_id' => RequestHelper::post('cos.app_id'), // AppId
                'secret_id' => RequestHelper::post('cos.secret_id'), // SecretId
                'secret_key' => RequestHelper::post('cos.secret_key'),  // SecretKey
                'bucket' => RequestHelper::post('cos.bucket'),     // bucket 填写secretId和secretKey之后会出现选择
                'region' => RequestHelper::post('cos.region'),     // region 填写secretId和secretKey之后会出现选择
                'scheme' => RequestHelper::post('cos.scheme'),
            ],
        ];

        $originalSettings = CoreSettings::get('storage');

        // 脱敏数据与原数据进行对比, 一致用原数据,不一致用新上传的数据
        $settings = StringHelper::compareSecretArray($originalSettings, $settings, $this->secretKeys);

        // 启用类型是阿里云OSS时 验证是都选择Bucket
        if ($type == StorageDriverConstant::DRIVE_OSS && empty($settings[StorageDriverConstant::DRIVE_OSS]['bucket'])) {
            return $this->error('请选择 Bucket');
        }

        // 启用存储类型不为本地时，执行上传测试
        if ($type != StorageDriverConstant::DRIVE_LOCAL) {
            try {
                // 获取存储组件
                $storage = StorageComponent::getInstance($type, $settings[$type]);
                $upload = $storage->upload(SHOP_STAR_PUBLIC_PATH . '/static/images/storage_upload_test.png', 'storage_upload_test.png');
                if (is_error($upload)) {
                    return $this->error('测试上传失败：' . $upload['message']);
                }
            } catch (Exception $e) {
                return $this->error('测试上传异常: ' . $e->getMessage());
            }
        }

        // 获取原来的设置，只保存当前启用的设置参数
        $originalSettings['type'] = $type;
        $originalSettings[$type] = $settings[$type];
        CoreSettings::set('storage', $originalSettings);

        return $this->success();
    }

    /**
     * 获取七牛域名列表
     * @return array|int[]|Response
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetQiniuDomain()
    {
        $settings = [
            'access_key' => RequestHelper::post('access_key'),
            'secret_key' => RequestHelper::post('secret_key'),
            'bucket' => RequestHelper::post('bucket'),
        ];

        // 验证必要参数
        if (empty($settings['access_key'])) {
            return $this->error('参数access_key不能为空');
        } elseif (empty($settings['secret_key'])) {
            return $this->error('参数secret_key不能为空');
        } elseif (empty($settings['bucket'])) {
            return $this->error('参数bucket不能为空');
        }

        // 获取原始数据
        $settings = $this->getOriginData($settings, StorageDriverConstant::DRIVE_QINIU, $this->secretKeys[StorageDriverConstant::DRIVE_QINIU]);

        /**
         * @var QiniuDriver 获取七牛存储组件实例
         */
        $storage = StorageComponent::getInstance(StorageDriverConstant::DRIVE_QINIU, $settings);
        if (is_error($storage)) {
            return $this->error($storage['message']);
        }

        // 获取域名列表
        $domain = $storage->getDomainList();
        if (is_error($domain)) {
            return $this->error('获取域名列表失败: ' . $domain['message']);
        }

        return $this->result([
            'domain_list' => $domain,
        ]);
    }

    /**
     * 获取阿里云OSS的Bucket列表
     * @return array|int[]|Response
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetOssBucket()
    {
        $settings = [
            'access_key' => RequestHelper::post('access_key'), // AccessKey
            'secret_key' => RequestHelper::post('secret_key'),  // SecretKey
        ];

        // 验证必要参数
        if (empty($settings['access_key'])) {
            return $this->error('参数access_key不能为空');
        } elseif (empty($settings['secret_key'])) {
            return $this->error('参数secret_key不能为空');
        }

        // 获取原始数据
        $settings = $this->getOriginData($settings, StorageDriverConstant::DRIVE_OSS, $this->secretKeys[StorageDriverConstant::DRIVE_OSS]);

        /**
         * @var OssDriver 获取OSS存储组件实例
         */
        $storage = StorageComponent::getInstance(StorageDriverConstant::DRIVE_OSS, $settings);
        if (is_error($storage)) {
            return $this->error($storage['message']);
        }

        // 获取存储桶
        $bucket = $storage->getBucketList();
        if (is_error($bucket)) {
            return $this->result($bucket);
        }

        return $this->result([
            'bucket_list' => $bucket,
        ]);
    }

    /**
     * 获取腾讯云COS的Bucket列表
     * @return array|int[]|Response
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetCosBucket()
    {
        $settings = [
            'app_id' => RequestHelper::post('app_id'), // AppId
            'secret_id' => RequestHelper::post('secret_id'),  // SecretId
            'secret_key' => RequestHelper::post('secret_key'),  // SecretKey
        ];

        // 验证必要参数
        if (empty($settings['app_id'])) {
            return $this->error('参数app_id不能为空');
        } elseif (empty($settings['secret_id'])) {
            return $this->error('参数secret_id不能为空');
        } elseif (empty($settings['secret_key'])) {
            return $this->error('参数secret_key不能为空');
        }

        // 获取原始数据
        $settings = $this->getOriginData($settings, StorageDriverConstant::DRIVE_COS, $this->secretKeys[StorageDriverConstant::DRIVE_COS]);

        /**
         * @var CosDriver 获取腾讯云COS存储组件实例
         */
        $storage = StorageComponent::getInstance(StorageDriverConstant::DRIVE_COS, $settings);
        if (is_error($storage)) {
            return $this->error($storage['message']);
        }

        // 获取存储桶
        $bucket = $storage->getBucketList();
        if (is_error($bucket)) {
            return $this->result($bucket);
        }

        return $this->result([
            'bucket_list' => $bucket,
        ]);
    }

    /**
     * 获取原始数据
     * @param array $data
     * @param string $type
     * @param array $keys
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private function getOriginData(array $data = [], string $type = '', array $keys = []): array
    {
        if (empty($data) || empty($keys) || empty($type)) {
            return $data;
        }

        $originData = CoreSettings::get('storage.' . $type);

        if (empty($originData)) {
            return $data;
        }

        // 数据带*, 用原数据
        foreach ($data as $k => $v) {
            if (!in_array($k, $keys) || !mb_substr_count($v, '*')) {
                continue;
            }

            if (empty($originData[$k])) {
                continue;
            }

            $data[$k] = $originData[$k];
        }

        return $data;
    }
}
