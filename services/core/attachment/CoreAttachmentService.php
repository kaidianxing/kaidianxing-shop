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

namespace shopstar\services\core\attachment;

use shopstar\components\storage\bases\StorageDriverConstant;
use shopstar\components\storage\bases\StorageDriverInterface;
use shopstar\components\storage\StorageComponent;
use shopstar\constants\core\CoreAttachmentSceneConstant;
use shopstar\constants\core\CoreAttachmentTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\FileHelper;
use shopstar\helpers\HttpHelper;
use shopstar\helpers\ImageHelper;
use shopstar\helpers\MP3Helper;
use shopstar\helpers\StringHelper;
use shopstar\models\core\attachment\CoreAttachmentGroupModel;
use shopstar\models\core\attachment\CoreAttachmentModel;
use shopstar\models\core\CoreSettings;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * 系统附件服务层
 * Class CreditController
 * @package shopstar\services\core
 * @author likexin
 */
class CoreAttachmentService
{

    /**
     * @var array 文件mime映射
     */
    private static $mimeMap = [
        'png' => [
            'image/png',
        ],
        'jpg' => [
            'image/jpg',
            'image/jpeg',
        ],
        'jpeg' => [
            'image/jpg',
            'image/jpeg',
        ],
        'gif' => [
            'image/gif',
        ],
        'mp3' => [
            'audio/x-mpeg',
        ],
        'mp4' => [
            'video/mp4',
        ],
    ];

    /**
     * 获取URL(全路径)
     * @param string $path
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public static function getUrl(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // 如果http、https开头
        if (StringHelper::exists($path, ['http://', 'https://'], StringHelper::SEL_OR)) {
            return $path;
        }

        // 取第一位
        $firstChar = substr($path, 0, 1);

        // 第一位是/则返回
        if ($firstChar == '/') {
            return Url::base(true) . $path;
        }

        return self::getRoot() . $path;
    }

    /**
     * 获取存储跟路径
     * @param array $options
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public static function getRoot(array $options = []): string
    {
        $options = array_merge([
            'static' => true
        ], $options);

        // 处理连接数据
        $settings = static::getSettings();

        $storageType = 'local';
        static $instance;
        if ($options['static'] === false || is_null($instance)) {
            $instance = StorageComponent::getInstance($storageType, $settings[$storageType]);
        }
        if (is_error($instance)) {

            return '';
        }

        if ($storageType == StorageDriverConstant::DRIVE_LOCAL) {
            return $instance->url;
        } elseif ($storageType == StorageDriverConstant::DRIVE_OSS && empty($instance->url)) {
            $buckets = explode('@', $settings[StorageDriverConstant::DRIVE_OSS]['bucket']);
            $instance->url = "{$buckets[0]}.{$buckets[1]}.aliyuncs.com";
        } else if ($storageType == StorageDriverConstant::DRIVE_COS && empty($instance->url)) {
            $cos = $settings[StorageDriverConstant::DRIVE_COS];
            $instance->url = "{$cos['bucket']}.cos.{$cos['region']}.myqcloud.com";
        }

        return $instance->scheme . rtrim(preg_replace('/\/+/', '/', StringHelper::replaceAll(['http://', 'https://'], '', $instance->url) . '/'), "/") . '/';
    }

    /**
     * 上传文件
     * @param array $params 上传参数
     * @param int $scene 上传场景
     * @return array|mixed
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public static function upload(array $params, int $scene)
    {
        $params = array_merge([
            'type' => 10,  // 附件类型
            'group_id' => 0,   // 分组ID
            'account_id' => 0,   // 上传者ID
            'postField' => 'file',  // 上传字段
            'remote' => false,  // 获取远端文件
            'local' => false,  // 本地文件(传入相对路径直接读取)
            'save_databases' => true, //是否入库
        ], $params);

        // 判断是否在支持的类型里
        $supportType = CoreAttachmentTypeConstant::getAll();
        if (!isset($supportType[(int)$params['type']])) {
            return error('不支持的附件类型');
        }

        if (empty($params['account_id'])) {
            return error('参数错误account_id不能为空');
        }

        // 附件类型
        $attachmentType = $supportType[(int)$params['type']]['identify'];

        // 获取已经上传完成的文件
        $file = self::getUploadedFile($params);
        if (is_error($file)) {
            return $file;
        }

        // 空文件验证
        if (empty($file->size)) {
            return error('不能上传空文件');
        }

        $year = date('Y');
        $month = date('m');
        $md5 = md5_file($file->tempName);
        $ext = $file->getExtension();
        // 处理扩展字段
        $extend = [];

        // 附件设置
        $attachmentSettings = self::getAttachmentSettings();
        $attachmentSettings = $attachmentSettings[$attachmentType];

        // 验证文件大小
        if (isset($attachmentSettings) && $attachmentSettings['max_size'] < ($file->size / 1024)) {
            return error('最大支持上传' . $attachmentSettings['max_size'] . 'kb的文件');
        }

        // 适配淘宝首图视频
        if ($params['type'] == CoreAttachmentTypeConstant::TYPE_VIDEO) {
            $ext = substr($ext, 0, 3);
        }

        if (!in_array($ext, (array)$attachmentSettings['extensions'])) {
            return error('禁止上传此类型文件');
        }

        // 此处更严格的验证下mime
        $mime = self::$mimeMap[$ext] ?? [];
        if (!in_array($file->type, $mime)) {
            return error('禁止上传此类型文件mime: ' . $file->type);
        }

        // 如果是图片、手机端上传的、非gif图片，则进行压缩
        if ($params['type'] == CoreAttachmentTypeConstant::TYPE_IMAGE) {
            if ($scene == CoreAttachmentSceneConstant::SCENE_MOBILE && $file->type != 'image/gif') {
                $file = ImageHelper::compress($file, [
                    'width' => (int)$attachmentSettings['image']['compress_width']
                ]);
            }
        }

        // 根据附件类型获取附加参数
        if ($params['type'] == CoreAttachmentTypeConstant::TYPE_IMAGE) {
            $imageSize = getimagesize($file->tempName);
            if (!$imageSize) {
                return error('图片尺寸错误');
            }
            $extend['width'] = (int)$imageSize[0];
            $extend['height'] = (int)$imageSize[1];
        } elseif ($params['type'] == CoreAttachmentTypeConstant::TYPE_AUDIO) {
            $mp3 = new MP3Helper($file->tempName);
            $extend['duration'] = (string)$mp3->getDuration();
        }

        $targetPath = $attachmentType . '/' . $year . '/' . $month . '/' . md5($md5 . time()) . '.' . $ext;

        // 获取存储设置
        $settings = static::getSettings();

        // 获取储存方式
        $storageType = 'local';

        /**
         * 获取存储组件实例
         * @var StorageDriverInterface $storage
         */
        $storage = StorageComponent::getInstance($storageType, $settings[$storageType] ?? '');
        if (is_error($storage)) {
            return $storage;
        }
        // 开始上传
        $result = $storage->upload($file->tempName, $targetPath);
        if (is_error($result)) {
            return $result;
        }

        //判断是否需要入库
        if ($params['save_databases']) {
            // 写入数据库
            $attachment = new CoreAttachmentModel();
            $attachment->setAttributes([
                'type' => (int)$params['type'],
                'group_id' => (int)$params['group_id'],
                'account_id' => (int)$params['account_id'],
                'scene' => $scene,
                'name' => $file->name,
                'path' => $targetPath,
                'ext' => $ext,
                'size' => $file->size,
                'md5' => $md5,
                'year' => $year,
                'month' => $month,
                'extend' => Json::encode($extend),
                'created_at' => DateTimeHelper::now()
            ]);
            if (!$attachment->save()) {
                return error('上传文件失败: ' . $attachment->getErrorMessage());
            }

            // 如果分组不为空时 数量++
            if (!empty($params['group_id'])) {
                CoreAttachmentGroupModel::updateAllCounters(['total' => 1], ['id' => (int)$params['group_id']]);
            }
        }

        // 删除临时文件
        FileHelper::unlink($file->tempName);

        return [
            'id' => $attachment->id ?? 0,
            'path' => $attachment->path ?? $targetPath,
        ];
    }

    /**
     * 删除文件
     * @param string $remotePath
     * @return StorageDriverInterface|mixed
     * @throws \yii\base\InvalidConfigException
     * @author likexin
     */
    public static function remove(string $remotePath)
    {
        // 获取存储设置
        static $settings;
        if (empty($settings)) {
            $settings = static::getSettings();
        }

        // 获取储存方式
        $storageType = 'local';

        /**
         * 获取存储组件实例
         * @var StorageDriverInterface $storage
         */
        $storage = StorageComponent::getInstance($storageType, $settings[$storageType] ?? '');
        if (is_error($storage)) {
            return $storage;
        }

        // 执行删除
        return $storage->remove($remotePath);
    }

    /**
     * 读取设置
     * @return array
     * @author likexin
     */
    public static function getSettings(): array
    {
        return CoreSettings::get('storage');
    }

    /**
     * 获取附件设置
     * @return array
     * @author likexin
     */
    public static function getAttachmentSettings(): array
    {
        return CoreSettings::get('attachment');
    }

    /**
     * 获取已经上传文件(remote\local\formPost)
     * @param array $params
     * @return UploadedFile|array
     * @throws \yii\base\Exception
     * @author likexin
     */
    private static function getUploadedFile(array $params)
    {
        if (!empty($params['remote'])) {
            // 获取远端的文件
            if (!StringHelper::exists($params['remote'], ['http://', 'https://'], StringHelper::SEL_OR)) {
                return error('请传入正确的图片地址');
            }

            // 获取远端文件
            // $remoteFile = file_get_contents($params['remote']);
            $remoteFile = HttpHelper::get($params['remote']);

            // 文件后缀
            $ext = FileHelper::getExtension($params['remote']);

            // 转存本地临时文件
            $filePath = SHOP_STAR_TMP_PATH . '/remote/' . md5($params['remote']) . '.' . $ext;
            FileHelper::createDirectory(dirname($filePath));

            // 写入本地文件
            FileHelper::write($filePath, $remoteFile);

            // 组成Yii文件类
            $file = new UploadedFile([
                'name' => basename($params['remote']),
                'size' => filesize($filePath),
                'tempName' => $filePath,
            ]);
        } elseif (!empty($params['local'])) {
            if (!is_file($params['local'])) {
                return error('local文件没找到');
            }

            // 组成Yii文件类
            $file = new UploadedFile([
                'name' => basename($params['local']),
                'size' => filesize($params['local']),
                'tempName' => $params['local'],
                'type' => mime_content_type($params['local']),
            ]);
        } else {
            // 本地文件直接获取
            $file = UploadedFile::getInstanceByName($params['postField']);
        }

        if (is_null($file)) {
            return error('上传文件失败，请检查格式');
        }

        // 获取文件真实内容类型
        if (function_exists('mime_content_type') && $file->tempName) {
            $file->type = mime_content_type($file->tempName);
        } else {
            $file->type = '';
        }

        return $file;
    }

}