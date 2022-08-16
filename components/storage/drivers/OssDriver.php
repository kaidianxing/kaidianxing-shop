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

use OSS\Core\OssException;
use OSS\OssClient;
use shopstar\components\storage\bases\BaseStorageDriver;
use shopstar\components\storage\bases\StorageDriverInterface;
use yii\base\InvalidConfigException;

class OssDriver extends BaseStorageDriver implements StorageDriverInterface
{
    /**
     * 注意：access_key、secret_key由于直接Yii::createObject注入，直接读取设置中字段所有使用下划线分隔单词
     */

    /**
     * @var array 数据中心映射表
     */
    private static array $dataCenterMap = [
        'oss-cn-qingdao' => '华北1',
        'oss-cn-beijing' => '华北2',
        'oss-cn-zhangjiakou' => '华北3',
        'oss-cn-hangzhou' => '华东1',
        'oss-cn-shanghai' => '华东2',
        'oss-cn-shenzhen' => '华南1',
        'oss-cn-hongkong' => '香港',
        'oss-ap-northeast-1' => '亚太东北 1 (东京)',
        'oss-ap-southeast-1' => '亚太东南 1 (新加坡)',
        'oss-ap-southeast-2' => '亚太东南 2 (悉尼)',
        'oss-us-east-1' => '美国东部 1 (弗吉尼亚)',
        'oss-us-west-1' => '美国西部 1 (硅谷)',
        'oss-me-east-1' => '中东东部 1 (迪拜)',
        'oss-eu-central-1' => '欧洲中部 1 (法兰克福)',
        'oss-ap-southeast-3' => '亚太东南3 (吉隆坡)',
        'oss-cn-huhehaote' => '华北 5',
        'oss-ap-south-1' => '亚太南部 1 (孟买)',
        'oss-ap-southeast-5' => '亚太东南 5 (雅加达)'
    ];

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
     * @var OssClient 阿里云OSS客户端
     */
    private OssClient $client;

    /**
     * 连接服务
     * @return void
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function connect()
    {
        $endPoint = 'https://oss-cn-beijing.aliyuncs.com';

        if (!empty($this->bucket)) {
            list(, $point) = explode('@', $this->bucket);
            $endPoint = 'https://' . $point . '.aliyuncs.com';
        }

        try {
            $this->client = \Yii::createObject(OssClient::class, [
                $this->access_key,
                $this->secret_key,
                $endPoint
            ]);
        } catch (OssException $e) {
            $this->setError($e);
        }
    }

    /**
     * 获取存储桶列表
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public function getBucketList(): ?array
    {
        // 判断错误
        $error = $this->getError();
        if (is_error($error)) {
            return $error;
        }

        try {
            $listBuckets = $this->client->listBuckets();
            $bucketList = $listBuckets->getBucketList();
        } catch (\Exception $exception) {
            return error($exception->getMessage());
        }

        $result = [];
        foreach ($bucketList as $bucket) {
            $result[] = [
                'name' => $bucket->getName(),
                'data_center' => static::$dataCenterMap[$bucket->getLocation()],
                'bucket' => $bucket->getName() . '@' . $bucket->getLocation()
            ];
        }

        return $result;
    }

    /**
     * 上传文件
     * @param string $localPath
     * @param string $targetPath
     * @param array $params
     * @return array|bool|null
     * @author 青岛开店星信息技术有限公司
     */
    public function upload(string $localPath, string $targetPath, array $params = [])
    {
        // 判断错误
        $error = $this->getError();
        if (is_error($error)) {
            return $error;
        }

        try {
            $this->client->uploadFile($this->getBucketName(), $targetPath, $localPath, [
                OssClient::OSS_PART_SIZE => 1024 * 1024 * 50,
            ]);
        } catch (OssException $e) {
            return error('OSS上传失败: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * 获取存储桶名称
     * @param string $targetPath
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public function remove(string $targetPath)
    {
        try {
            $this->client->deleteObject($this->getBucketName(), $targetPath);
        } catch (OssException $e) {
            return error($e->getMessage());
        }

        return true;
    }

    /**
     * 获取存储桶名称
     * @return mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    private function getBucketName()
    {
        if (empty($this->bucket)) {
            return '';
        }

        [$bucket,] = explode('@', $this->bucket);

        return $bucket;
    }
}
