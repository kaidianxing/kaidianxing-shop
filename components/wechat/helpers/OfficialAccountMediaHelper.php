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

namespace shopstar\components\wechat\helpers;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Messages\Article;
use EasyWeChat\Kernel\Messages\Image;
use shopstar\components\platform\Wechat;
use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\WechatComponent;
use shopstar\helpers\FileHelper;
use shopstar\helpers\StringHelper;
use shopstar\services\core\attachment\CoreAttachmentService;

/**
 * 公众号临时素材
 * Class OfficialMediaHelper
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\wechat\helpers
 */
class OfficialAccountMediaHelper
{
    /**
     * 获取实例
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司.
     */
    private static function getInstance()
    {
        return WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory;
    }

    /**
     * 上传图片
     * @param string $path
     * @return array|bool|mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function uploadImage(string $path)
    {
        try {
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory->material;
            $result = $instance->uploadImage($path);
        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

    /**
     * 发送图片
     * @param string $openId
     * @param string $message
     * @return array|bool|mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendImage(string $openId, string $message)
    {
        try {
            $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory;

            $image = new Image($message);

            $result = $instance->customer_service->message($image)->to($openId)->send();

        } catch (\Exception $exception) {
            $result = $exception;
        }

        return Wechat::apiError($result);
    }

    /**
     * 上传音频
     * @param string $path 音频路径
     * @return array|bool|void
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738729
     */
    public static function uploadVoice(string $path)
    {
        try {
            $result = self::getInstance()->material->uploadVoice($path);
        } catch (\Exception $exception) {
            return error($exception->getMessage());
        }
        return Wechat::apiError($result);
    }

    /**
     * 上传视频
     * @param string $path 视频路径
     * @param string $title
     * @param string $description
     * @return array|bool|void
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738729
     */
    public static function uploadVideo(string $path, string $title, string $description)
    {
        try {
            $result = self::getInstance()->material->uploadVideo($path, $title, $description);
        } catch (\Exception $exception) {
            return error($exception->getMessage());
        }
        return Wechat::apiError($result);
    }

    /**
     * 上传缩略图(用于视频封面或者音乐封面)
     * @param string $path 图片路径
     * @return array|bool|void
     * @throws \yii\base\InvalidConfigException
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738729
     */
    public static function uploadThumb(string $path)
    {
        try {
            $result = self::getInstance()->material->uploadThumb($path);
        } catch (InvalidArgumentException $exception) {
            return error($exception->getMessage());
        }
        return Wechat::apiError($result);
    }

    /**
     * 上传图文消息
     * @param array $article_list 文章列表
     * @param string $article_list [][title] 标题
     * @param string $article_list [][thumb_media_id] 图文消息的封面图片素材id(必须是永久mediaId)
     * @param string $article_list [][author] 作者 非必填
     * @param string $article_list [][digest] 图文消息的摘要, 仅有单图文消息才有摘要, 多图文此处为空. 如果本字段为没有填写, 则默认抓取正文前64个字 非必填
     * @param integer $article_list [][show_cover] 是否显示封面, 0不显示, 1即显示
     * @param string $article_list [][content] 图文消息的具体内容, 支持HTML标签, 必须少于2万字符, 小于1M, 且此处会去除JS, 涉及图片url必须来源"上传图文消息内的图片获取URL"接口获取. 外部图片url将被过滤.
     * @param string $article_list [][source_url] 图文消息的原文地址, 即点击“阅读原文”后的URL
     * @return array|bool|void
     * @throws \yii\base\InvalidConfigException
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738729
     */
    public static function uploadNews(array $article_list)
    {
        $articles = [];
        foreach ($article_list as $article_row) {
            //图片
            $imgs = StringHelper::getImages($article_row['content']);
            if (!empty($imgs)) {
                foreach ($imgs as $img) {
                    $img['new_src'] = CoreAttachmentService::getUrl($img['src']);
                    $extension = FileHelper::getExtension($img['src']);
                    $srcPath = '';

                    if (!empty($extension)) {
                        $body = file_get_contents($img['new_src']);
                        $localFilePath = (\Yii::$app->getBasePath() . '/web/data/tmp/' . md5(time() . StringHelper::random(5)) . '.' . $extension);
                        FileHelper::write($localFilePath, $body);
                        $upload = self::uploadThumb($localFilePath);

                        if (!is_error($upload)) {
                            $srcPath = $upload['url'];
                        }
                    } else {
                        $srcPath = $img['new_src'];
                    }

                    @unlink($localFilePath);
                    $article_row['content'] = str_replace("src=\"{$img['src']}\"", "wechat-src=\"" . $img['new_src'] . "\" src=\"" . $srcPath . "\"", $article_row['content']);
                }
            }

            $articles[] = new Article($article_row);
        }

        try {
            $result = self::getInstance()->material->uploadArticle($articles);
        } catch (InvalidArgumentException $exception) {
            return error($exception->getMessage());
        }

        return Wechat::apiError($result);
    }

    /**
     * 修改图文消息
     * @param string $mediaId 文章素材ID
     * @param array $article 文章内容
     * @param int $index 要更新的文章在图文消息中的位置(多图文消息时, 此字段才有意义, 单图片忽略此参数), 第一篇为 0
     * @return array|bool|void
     * @throws \yii\base\InvalidConfigException
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/material
     */
    public static function updateArticle(string $mediaId, array $article, int $index = 0)
    {
        $result = self::getInstance()->material->updateArticle($mediaId, $article, $index);
        return Wechat::apiError($result);
    }

    /**
     * 上传图文消息图片
     * @param string $path 图片路径
     * @return array|bool|void
     * @throws \yii\base\InvalidConfigException
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/material
     */
    public static function uploadArticleImage(string $path)
    {
        try {
            $result = self::getInstance()->material->uploadArticleImage($path);
        } catch (InvalidArgumentException $exception) {
            return error($exception->getMessage());
        }
        return Wechat::apiError($result);
    }

    /**
     * 获取永久素材
     * @param string $mediaId 素材ID
     * @return array|bool|void [save, saveAs]
     * @throws \yii\base\InvalidConfigException
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/material
     */
    public static function get(string $mediaId)
    {
        $result = self::getInstance()->material->get($mediaId);
        return Wechat::apiError($result);
    }

    /**
     * 获取永久素材列表
     * @param string $type 素材类型[image, video, voice, news]
     * @param int $offset
     * @param int $count
     * @return array|bool|void
     * @throws \yii\base\InvalidConfigException
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/material
     */
    public static function getList(string $type, int $offset = 0, int $count = 20)
    {
        if (!in_array($type, ['image', 'video', 'voice', 'news'])) {
            return error('type错误');
        }
        $result = self::getInstance()->material->list($type, $offset, $count);
        return Wechat::apiError($result);
    }

    /**
     * 获取素材计数
     * @return array|bool|void
     * @throws \yii\base\InvalidConfigException
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/material
     */
    public static function stats()
    {
        $result = self::getInstance()->material->stats();
        return Wechat::apiError($result);
    }

    /**
     * 删除永久素材
     * @param string $mediaId 素材ID
     * @return array|bool|void
     * @throws \yii\base\InvalidConfigException
     * @link https://www.easywechat.com/docs/master/zh-CN/official-account/material
     */
    public static function delete(string $mediaId)
    {
        $result = self::getInstance()->material->delete($mediaId);
        return Wechat::apiError($result);
    }
}
