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

use shopstar\components\storage\bases\BaseStorageDriver;
use shopstar\components\storage\bases\StorageDriverInterface;
use shopstar\helpers\FileHelper;
use yii\helpers\Url;

/**
 * 本地存储驱动类
 * Class LocalDriver
 * @package shopstar\components\storage\drivers
 */
class LocalDriver extends BaseStorageDriver implements StorageDriverInterface
{

    /**
     * @var string 根路径
     */
    private $rootPath = 'data/attachment';

    /**
     * 初始化
     * @author likexin
     */
    public function init()
    {
        $this->url = Url::to($this->rootPath . DIRECTORY_SEPARATOR, true);
    }

    /**
     * 上传文件
     * @param string $localPath 本地路径
     * @param string $targetPath 目标路径
     * @param array $params 附加参数
     * @return mixed|void
     * @throws \yii\base\Exception
     * @author likexin
     */
    public function upload(string $localPath, string $targetPath, array $params = [])
    {
        // 定义目标的路径
        $uploadPath = $this->getPath($targetPath);
        $result = FileHelper::createDirectory(dirname($uploadPath));
        if (!$result) {
            return error('目录创建失败');
        }

        // 拷贝到指定目录
        return @copy($localPath, $uploadPath);
    }

    /**
     * 获取文件路径
     * @param string $targetPath 目标路径
     * @return string
     * @author likexin
     */
    private function getPath(string $targetPath)
    {
        return SHOP_STAR_PUBLIC_PATH . DIRECTORY_SEPARATOR . $this->rootPath . DIRECTORY_SEPARATOR . $targetPath;
    }

    /**
     * 移除文件
     * @param string $targetPath 目标路径
     * @return bool|mixed
     * @author likexin
     */
    public function remove(string $targetPath)
    {
        $filePath = $this->getPath($targetPath);

        return FileHelper::unlink($filePath);
    }

}