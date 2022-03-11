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

namespace shopstar\admin\system;

use shopstar\bases\KdxAdminApiController;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\CoreSettings;

/**
 * 积分余额设置
 * Class CreditController
 * @package app\controllers\manage\sysset
 */
class AttachmentController extends KdxAdminApiController
{

    public $configActions = [
        'postActions' => [
            'set',
        ]
    ];
    /**
     * @var array 可用的类型
     */
    private $usableType = [
        'image' => [
            'jpeg',
            'jpg',
            'png',
            'gif'
        ],
        'audio' => [
            'mp3'
        ],
        'video' => [
            'mp4'
        ]
    ];

    /**
     * 获取设置
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGet()
    {
        $settings = CoreSettings::get('attachment');

        return $this->result([
            'settings' => $settings,
            'usable_type' => $this->usableType,
        ]);
    }

    /**
     * 提交保存
     * @return array|\yii\web\Response
     * @throws \yii\db\Exception
     * @author likexin
     */
    public function actionSet()
    {
        if (empty(RequestHelper::post())) {
            return $this->error('数据不能为空');
        }

        // 图片压缩
        $imageCompress = RequestHelper::postInt('image.compress');
        // 图片压缩宽度
        $imageCompressWidth = RequestHelper::postInt('image.compress_width');

        // 验证图片压缩最小宽度
        if (!empty($imageCompress) && $imageCompressWidth < 2) {
            return $this->error('手机端图片压缩宽度值不能小于2像素');
        }

        // 判断设置的大小是否超出了php.ini中设置的上限
        $typeArray = [
            'video'=>RequestHelper::post('video.max_size'),
            'image'=>RequestHelper::post('image.max_size'),
            'audio'=>RequestHelper::post('audio.max_size'),
        ];
        $ini = ini_get('upload_max_filesize');
        foreach ($typeArray as $value){
            if($value > ($ini * 1024)){
                return $this->error('上传限制超出了php配置中的上限');
            }
        }

        // 保存设置
        CoreSettings::set('attachment', [
            'image' => [
                'extensions' => $this->getUsableExtensions('image'),
                'max_size' => RequestHelper::postInt('image.max_size'),
                'compress' => $imageCompress,
                'compress_width' => $imageCompressWidth,
            ],
            'video' => [
                'extensions' => $this->getUsableExtensions('video'),
                'max_size' => RequestHelper::post('video.max_size'),
            ],
            'audio' => [
                'extensions' => $this->getUsableExtensions('audio'),
                'max_size' => RequestHelper::postInt('audio.max_size'),
            ],
        ]);

        return $this->success();
    }

    /**
     * 获取可用类型
     * @param string $type 类型
     * @return array
     * @author likexin
     */
    private function getUsableExtensions(string $type)
    {
        $postField = $type . '.extensions';
        $postType = RequestHelper::postArray($postField);
        if (empty($postType)) {
            return [];
        }

        // 读取系统允许的后缀
        $usableType = $this->usableType[$type] ?? [];
        if (empty($usableType)) {
            return [];
        }

        // 扩展
        $extensions = [];

        // 遍历过滤不合法的后缀
        foreach ($postType as $extension) {
            if (!in_array($extension, $usableType)) {
                continue;
            }
            $extensions[] = $extension;
        }

        return $extensions;
    }

}