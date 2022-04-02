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

use shopstar\bases\constant\BaseConstant;

/**
 * 存储驱动常量
 * Class StorageDriveConstant
 * @method getMessage($code) string
 * @package shopstar\components\storage
 * @author 青岛开店星信息技术有限公司
 */
class StorageDriverConstant extends BaseConstant
{

    /**
     * 注意：如果需要新增远程存储类型时
     * 1. 首先在此文件中定义相关类型的常量、映射
     * 2. 在drives目录中创建对应的类并且继承BaseStorageDriver、引用StorageDriveInterface
     */

    /**
     * @message("本地存储")
     */
    public const DRIVE_LOCAL = 'local';

    /**
     * @message("FTP存储")
     */
    public const DRIVE_FTP = 'ftp';

    /**
     * @message("七牛存储")
     */
    public const DRIVE_QINIU = 'qiniu';

    /**
     * @message("阿里OSS存储")
     */
    public const DRIVE_OSS = 'oss';

    /**
     * @message("腾讯COS存储")
     */
    public const DRIVE_COS = 'cos';

    /**
     * @var array 映射Map
     */
    private static $map = [
        self::DRIVE_LOCAL => [
            'name' => '本地存储',
            'class' => 'shopstar\components\storage\drivers\LocalDriver'
        ],
        self::DRIVE_FTP => [
            'name' => 'ftp存储',
            'url' => '',
            'class' => 'shopstar\components\storage\drivers\FtpDriver'
        ],
        self::DRIVE_QINIU => [
            'name' => '七牛存储',
            'url' => 'https://www.qiniu.com',
            'class' => 'shopstar\components\storage\drivers\QiniuDriver'
        ],
        self::DRIVE_OSS => [
            'name' => '阿里云 OSS 存储',
            'url' => 'https://www.aliyun.com/product/oss',
            'class' => 'shopstar\components\storage\drivers\OssDriver'
        ],
        self::DRIVE_COS => [
            'name' => '腾讯 COS 存储',
            'url' => 'https://cloud.tencent.com/product/cos',
            'class' => 'shopstar\components\storage\drivers\CosDriver'
        ],
    ];

    /**
     * 获取驱动
     * @param string $type 存储类型
     * @return bool|mixed
     * @author likexin
     */
    public static function getDriver(string $type)
    {
        return self::$map[$type] ?? false;
    }

}