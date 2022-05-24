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

namespace shopstar\admin\wxTransactionComponent;

use shopstar\bases\KdxAdminApiController;
use shopstar\helpers\RequestHelper;
use shopstar\models\shop\ShopSettings;
use yii\web\Response;

/**
 * 视频号设置（装修页面用）
 * Class LinkSettingController.
 * @package shopstar\admin\wxTransactionComponent
 */
class LinkSettingController extends KdxAdminApiController
{
    /**
     * 获取视频号直播设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionLiveGet()
    {
        $liveSettings = ShopSettings::get('wxTransactionComponent.live');

        return $this->result(['data' => $liveSettings]);
    }

    /**
     * 设置视频号直播
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionLiveSet()
    {
        $data = [
            'video_number_id' => RequestHelper::post('video_number_id'),
        ];

        ShopSettings::set('wxTransactionComponent.live', $data);

        return $this->success();
    }

    /**
     * 获取视频号动态设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDynamicGet()
    {
        $liveSettings = ShopSettings::get('wxTransactionComponent.dynamic');

        return $this->result(['data' => $liveSettings]);
    }

    /**
     * 设置视频号动态
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDynamicSet()
    {
        $data = [
            'video_id' => RequestHelper::post('video_id'),
            'video_number_id' => RequestHelper::post('video_number_id'),
        ];

        ShopSettings::set('wxTransactionComponent.dynamic', $data);

        return $this->success();
    }
}
