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

namespace shopstar\components\storage\drivers;

use Exception;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Http\Error;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use shopstar\components\storage\bases\BaseStorageDriver;
use shopstar\components\storage\bases\StorageDriverInterface;

/**
 * 七牛云存储驱动类
 * Class QiniuDriver.
 * @package shopstar\components\storage\drivers
 */
class QiniuDriver extends BaseStorageDriver implements StorageDriverInterface
{
    /**
     * 注意：access_key、secret_key由于直接Yii::createObject注入，直接读取设置中字段所有使用下划线分隔单词
     */

    /**
     * @var string AccessKey
     */
    public string $access_key;

    /**
     * @var string SecretKey
     */
    public string $secret_key;

    /**
     * @var string 存储桶
     */
    public string $bucket;

    /**
     * @var string 令牌
     */
    private string $token;

    /**
     * @var Auth 七牛授权对象
     */
    private Auth $auth;

    /**
     * @var UploadManager 上传管理器
     */
    private UploadManager $uploadManager;

    /**
     * 执行连接
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function connect()
    {
        $this->auth = new Auth($this->access_key, $this->secret_key);
        $this->uploadManager = new UploadManager();
    }

    /**
     * 获取域名列表
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getDomainList(): array
    {
        $config = new Config();

        $bucketManager = new BucketManager($this->auth, $config);

        /**
         * @var array $domain
         */
        [$domain, $error] = $bucketManager->domains($this->bucket);
        if (!is_null($error)) {
            return error($error->getResponse()->error);
        }

        return array_filter($domain);
    }

    /**
     * 上传文件
     * @param string $localPath
     * @param string $targetPath
     * @param array $params
     * @return array|bool|string[]
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function upload(string $localPath, string $targetPath, array $params = [])
    {
        // 每次都覆盖文件
        $this->token = $this->auth->uploadToken($this->bucket, $targetPath);

        // 推送文件
        /**
         * @var Error $error
         */
        [$result, $error] = $this->uploadManager->putFile($this->token, $targetPath, $localPath);
        if ($error !== null) {
            if ($error->getResponse()->statusCode == 614) {
                return ['url' => $this->url . '/' . $targetPath];
            }

            $message = !empty($error->getResponse()->error) ? $error->getResponse()->error : '配置参数错误';

            return error('七牛上传失败: ' . $message, $error->getResponse()->statusCode);
        }

        return true;
    }

    /**
     * 移除文件
     * @param string $targetPath
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public function remove(string $targetPath)
    {
        $config = new Config();
        $bucketManager = new BucketManager($this->auth, $config);

        /**
         * @var Error $error
         */
        [$result, $error] = $bucketManager->delete($this->bucket, $targetPath);
        if (!is_null($error)) {
            return error($error->getResponse()->error, $error->getResponse()->statusCode);
        }

        return true;
    }
}
