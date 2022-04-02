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

namespace shopstar\components\storage\bases;

/**
 * 存储驱动接口
 * Interface StorageDriveInterface
 * @package shopstar\components\storage
 * @author 青岛开店星信息技术有限公司
 */
interface StorageDriverInterface
{

    /**
     * 连接服务
     * @return mixed
     * @author likexin
     */
    public function connect();

    /**
     * 上传文件
     * @param string $localPath 本地路径
     * @param string $targetPath 目标路径
     * @param array $params 附加参数
     * @return mixed
     * @author likexin
     */
    public function upload(string $localPath, string $targetPath, array $params = []);

    /**
     * 移除文件
     * @param string $targetPath 目标路径
     * @return mixed
     * @author likexin
     */
    public function remove(string $targetPath);

}