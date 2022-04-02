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

namespace shopstar\admin\wxapp;

use shopstar\bases\KdxAdminApiController;
use shopstar\components\wechat\helpers\MiniProgramMediaHelper;
use shopstar\exceptions\wxapp\WxappException;
use shopstar\helpers\FileHelper;
use shopstar\helpers\RequestHelper;
use shopstar\services\core\attachment\CoreAttachmentService;

/**
 * 微信附件
 * Class MediaController
 * @package shopstar\admin\wxapp
 * @author 青岛开店星信息技术有限公司
 */
class MediaController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'upload-image'
        ]
    ];

    /**
     * 上传临时图片
     * @throws WxappException
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUploadImage()
    {
        $post = RequestHelper::post();
        if (empty($post['url'])) {
            throw new WxappException(WxappException::CHANNEL_MANAGE_WXAPP_MEDIA_UPLOAD_IMAGE_PARAMS_ERROR);
        }

        //获取完整url
        $thumbUrl = CoreAttachmentService::getUrl($post['url']);
        //组成系统目录
        $filePatch = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_media/' . md5($thumbUrl) . '.png';
        //获取文件内容
        $fileContent = file_get_contents($thumbUrl);
        //写入文件
        FileHelper::write($filePatch, $fileContent);

        //上传小程序图
        $result = MiniProgramMediaHelper::uploadImage($filePatch);
        if (is_error($result)) {
            throw new WxappException(WxappException::CHANNEL_MANAGE_WXAPP_MEDIA_UPLOAD_IMAGE_ERROR, $result['message']);
        }

        //删除文件
        @unlink($filePatch);
        return $this->result($result);
    }

}
