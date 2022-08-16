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

use Qcloud\Cos\Client;
use shopstar\components\storage\bases\BaseStorageDriver;
use shopstar\components\storage\bases\StorageDriverInterface;
use shopstar\helpers\StringHelper;
use Yii;
use yii\base\InvalidConfigException;

class CosDriver extends BaseStorageDriver implements StorageDriverInterface
{
    /**
     * 注意：access_key、secret_key由于直接Yii::createObject注入，直接读取设置中字段所有使用下划线分隔单词
     */

    /**
     * @var string 访问URL
     */
    public $url;

    /**
     * @var string AppId
     */
    public string $app_id;

    /**
     * @var string SecretId
     */
    public string $secret_id;

    /**
     * @var string SecretKey
     */
    public string $secret_key;

    /**
     * @var string Bucket
     */
    public string $bucket;

    /**
     * @var string Region
     */
    public string $region = 'ap-beijing';

    /**
     * @var Client 腾讯云COS客户端
     */
    private Client $client;

    /**
     * 连接服务
     * @return void
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function connect()
    {
        $this->client = Yii::createObject(Client::class, [
            [
                'region' => $this->region,
                'credentials' => [
                    'appId' => $this->app_id,
                    'secretId' => $this->secret_id,
                    'secretKey' => $this->secret_key
                ],
            ],
        ]);
    }

    /**
     * 获取存储桶列表
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getBucketList(): array
    {
        try {
            $result = $this->client->listBuckets()['Buckets'][0]['Bucket'];
        } catch (\Exception $e) {
            $result = error($e->getMessage());
        }

        if (is_error($result)) {
            return $result;
        }

        // 如果只有单个，需要包一层
        if (!isset($result[0])) {
            $result = [$result];
        }

        // 处理下
        return array_map(function ($val) {
            return [
                'bucket' => $val['Name'],
                'region' => $val['Location'],
            ];
        }, $result);
    }

    /**
     * 上传文件
     * @param string $localPath
     * @param string $targetPath
     * @param array $params
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public function upload(string $localPath, string $targetPath, array $params = [])
    {
        try {
            $this->client->putObject([
                'Bucket' => $this->getBucketName(),
                'Key' => $targetPath,
                'Body' => fopen($localPath, 'rb')
            ]);
        } catch (\Exception $e) {
            return error('COS上传失败: ' . $e->getMessage());
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
        try {
            $this->client->deleteObject([
                'Bucket' => $this->getBucketName(),
                'Key' => $targetPath,
            ]);
        } catch (\Exception $exception) {
            return error($exception->getMessage());
        }

        return true;
    }

    /**
     * 获取存储桶名称
     * @return array|string|string[]
     * @author 青岛开店星信息技术有限公司
     */
    private function getBucketName()
    {
        if (empty($this->bucket)) {
            return '';
        }

        $bucket = $this->bucket;

        // 判断如果bucket中含有appid时去掉
        if (StringHelper::exists($bucket, $this->app_id)) {
            $bucket = str_replace('-' . $this->app_id, '', $bucket);
        }

        return $bucket;
    }
}
