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

namespace shopstar\models\wechat;

use shopstar\wechat\constants\WechatMediaTypeConstant;
use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\wechat\helpers\OfficialAccountMediaHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\FileHelper;
use shopstar\helpers\LogHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\core\attachment\CoreAttachmentModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%app_wechat_material}}".
 *
 * @property int $id
 * @property int $media_id 素材id
 * @property int $attachment_id 附件id
 * @property string $url url
 * @property string $type 素材类型
 * @property string $title 素材标题
 * @property string $description 素材描述
 * @property string $created_at 创建时间
 */
class WechatMaterialModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_wechat_material}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['attachment_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 10],
            [['description', 'url'], 'string'],
            [['media_id'], 'string', 'max' => 191],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'media_id' => '素材id',
            'attachment_id' => '附件id',
            'url' => 'url',
            'type' => '素材类型',
            'title' => '素材标题',
            'description' => '素材描述',
            'created_at' => '创建时间',
        ];
    }

    /**
     *
     * @param array $options
     * @return mixed
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司.
     */
    public static function sync(array $options = []): bool
    {
        $options = array_merge([
            'type' => WechatMediaTypeConstant::WECHAT_MEDIA_TYPE_IMAGE,
        ], $options);

        //获取素材数量
        $stats = OfficialAccountMediaHelper::stats();
        if (is_error($stats)) {
            return error($stats);
        }

        //获取单一素材数量
        $mediaCount = $stats[$options['type'] . '_count'] ?? 0;

        if ($mediaCount == 0) {
            return true;
        }

        $tr = \Yii::$app->db->beginTransaction();

        try {
            //删除之前素材
            self::deleteAll(['type' => $options['type']]);

            if ($options['type'] == WechatMediaTypeConstant::WECHAT_MEDIA_TYPE_NEWS) {
                WechatMaterialNewsEntity::deleteAll();
            }

            $page = 0;

            //循环同步素材
            do {
                $data = OfficialAccountMediaHelper::getColl($options['type'], $page * 20);
                if (!empty($data['item'])) {
                    foreach ($data['item'] as $item) {

                        //如果是视频的话获取视频地址
                        if ($options['type'] == WechatMediaTypeConstant::WECHAT_MEDIA_TYPE_VIDEO) {

                            $detail = OfficialAccountMediaHelper::get($item['media_id']);

                            //赋值视频url
                            $item['url'] = $detail['down_url'];
                        }

                        $model = new self();
                        $model->setAttributes([
                            'media_id' => $item['media_id'],
                            'title' => $item['name'] ?? '',
                            'created_at' => $item['updated_at'] ? date('Y-m-d H:i:s', $item['updated_at']) : DateTimeHelper::now(),
                            'url' => $item['url'] ?? '',
                            'description' => $item['description'] ?? '',
                            'type' => $options['type'] ?? '',
                        ]);

                        $model->save();

                        //批量入库数据
                        $insetData = [];

                        //如果是图文需要处理图文数据
                        if ($options['type'] == WechatMediaTypeConstant::WECHAT_MEDIA_TYPE_NEWS) {
                            foreach ((array)$item['content']['news_item'] as $index => $newsItem) {
                                $insetData[] = [
                                    'material_id' => $model->id,
                                    'index' => $index,
                                    'title' => $newsItem['title'],
                                    'author' => $newsItem['author'],
                                    'description' => $newsItem['digest'],
                                    'content' => $newsItem['content'],
                                    'content_source_url' => $newsItem['content_source_url'],
                                    'thumb_media_id' => $newsItem['thumb_media_id'],
                                    'show_cover_pic' => $newsItem['show_cover_pic'],
                                    'url' => $newsItem['url'],
                                    'thumb_url' => $newsItem['thumb_url'],
                                    'need_open_comment' => $newsItem['need_open_comment'],
                                    'only_fans_can_comment' => $newsItem['only_fans_can_comment'],
                                ];
                            }

                            if (empty($insetData)) continue;

                            //批量入库
                            WechatMaterialNewsEntity::batchInsert(array_keys(current($insetData)), $insetData);
                        }
                    }
                }

                $page++;
            } while ($data['item_count'] == 20);

            $tr->commit();
        } catch (\Throwable $throwable) {
            LogHelper::error('[WECHAT MATERIAL SYNC ERROR]:', [
                'message' => $throwable->getMessage()
            ]);
            $tr->rollBack();
            return false;
        }

        return true;
    }

    /**
     * 上传素材
     * @param int $attachmentId
     * @param string $type
     * @param array $options
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \ReflectionException
     * @author 青岛开店星信息技术有限公司.
     */
    public static function upload(int $attachmentId, string $type, array $options = [])
    {
        $options = array_merge([
            'video_title' => '',
            'video_description' => '',
        ], $options);

        if (!empty($attachmentId)) {
            //判断是否上传过
            $wechatMediaResult = self::find()->where([
                'attachment_id' => $attachmentId,
            ])->first();

            //如果存在直接返回
            if (!empty($wechatMediaResult)) {
                return [
                    'id' => $wechatMediaResult['id'],
                    'media_id' => $wechatMediaResult['media_id'],
                ];
            }
        }

        //获取全部类型
        $allType = WechatMediaTypeConstant::getAll();

        if (!in_array($type, array_keys($allType))) {
            return error('文件类型不合法');
        }

        //视频文件判断标题和简介
        if ($allType == WechatMediaTypeConstant::WECHAT_MEDIA_TYPE_VIDEO && (empty($options['video_title']) || empty($options['video_description']))) {
            return error('请填写视频标题和描述');
        }

        //判断素材id是否存在  如果存在则使用素材库地址 如果不在则说明是上传的文件
        if ($attachmentId) {
            //获取素材
            $attachment = CoreAttachmentModel::find()->where([
                'id' => $attachmentId,
            ])->first();

            if (empty($attachment)) {
                return error('素材为空');
            }

            //获取文件url
            $fileUrl = CoreAttachmentService::getUrl($attachment['path']);

            //判断远程文件是否存在
            if (!FileHelper::checkRemoteFileExists($fileUrl)) {
                return error('文件不存在');
            }

            //获取本地路径
            $localPath = self::getWriteFilePath($fileUrl);

            //写入本地
            $file = file_get_contents($fileUrl);
            if (empty($file)) {
                return error('获取文件失败，请检查网络');
            }

            //写入本地
            $fileResult = FileHelper::write($localPath, $file);

        } else {
            //上传文件
            $file = UploadedFile::getInstanceByName('file');

            if (empty($file->name)) {
                return error('请选择需要上传文件');
            }

            //获取本地路径
            $localPath = self::getWriteFilePath($file->name);
            $dir = dirname($localPath);
            if (!is_dir($dir)) {

                FileHelper::createDirectory($dir);
            }

            //保存文件
            $fileResult = $file->saveAs($localPath);

        }

        if (is_error($fileResult)) {
            return error($fileResult);
        }

        //检测类型
        $method = 'upload' . strtolower($type);
        $result = OfficialAccountMediaHelper::$method($localPath, $options['video_title'], $options['video_description']);

        //删除本地临时文件
        FileHelper::unlink($localPath);

        //如果是错误直接返回
        if (is_error($result)) return $result;

        //如果是视频的话获取视频地址
        if ($type == WechatMediaTypeConstant::WECHAT_MEDIA_TYPE_VIDEO) {
            $detail = OfficialAccountMediaHelper::get($result['media_id']);

            //赋值视频url
            $result['url'] = $detail['down_url'];
        }

        $model = new self();
        $model->setAttributes([
            'media_id' => $result['media_id'],
            'attachment_id' => $attachmentId,
            'url' => $result['url'] ?? '',
            'type' => $type,
            'title' => trim($file->name, dirname($file->name)),
            'description' => trim($file->name, dirname($file->name)),
            'created_at' => DateTimeHelper::now()
        ]);

        $model->save();
        return [
            'media_id' => $result['media_id']
        ];
    }

    /**
     * 获取写入链接
     * @param string $fileName
     * @return string
     * @author 青岛开店星信息技术有限公司.
     */
    public static function getWriteFilePath(string $fileName): string
    {
        return SHOP_STAR_PUBLIC_TMP_PATH . '/wechat_tmp_media_file/' . '/' . md5(time() . StringHelper::random(5)) . '.' . FileHelper::getExtension($fileName);
    }

}