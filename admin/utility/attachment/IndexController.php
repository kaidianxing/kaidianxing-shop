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



namespace shopstar\admin\utility\attachment;

use shopstar\helpers\HttpHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\attachment\CoreAttachmentModel;
use shopstar\models\core\CoreSettings;
use shopstar\models\shop\ShopSettings;
use shopstar\bases\KdxAdminUtilityController;

/**
 * Class IndexController
 * @package modules\utility\manage\attachment
 * @author 青岛开店星信息技术有限公司.
 */
class IndexController extends KdxAdminUtilityController
{
    public $configActions = [
        'allowHeaderActions' => [
            '*'
        ]
    ];

    /**
     * 商城获取微信图片
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetWechatImage()
    {
        $url = RequestHelper::get('url');
        $content = HttpHelper::get($url, ['CURLOPT_REFERER' => 'http://www.qq.com']);
        header('Content-Type:image/jpg');
        echo $content;
        exit();
    }

    /**
     * 获取附件上传限制
     * @author 青岛开店星信息技术有限公司
     * @return \yii\web\Response
     */
    public function actionGetConfig()
    {
        $result = CoreSettings::get('attachment');
        $result['storage']['type'] = ShopSettings::get('service_storage')['type'];
        $result['storage']['storage_model'] = CoreAttachmentModel::$storageModelMap[CoreAttachmentModel::HOSTING];
        return $this->success(['data'=>$result]);
    }
}
