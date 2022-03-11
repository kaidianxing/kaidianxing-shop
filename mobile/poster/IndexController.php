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

namespace shopstar\mobile\poster;

use apps\poster\base\PosterClientApiController;
use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\poster\PosterTypeConstant;
use shopstar\helpers\ShopUrlHelper;
use shopstar\models\poster\PosterModel;
use shopstar\models\poster\PosterTemplateModel;

/**
 * Class IndexController
 * @package apps\commission\client
 */
class IndexController extends BaseMobileApiController
{

    /**
     * 获取商品海报
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGoods()
    {
        $poster = [];

        //判断权限
        $poster = PosterModel::find()
            ->where([
                'type' => PosterTypeConstant::POSTER_TYPE_GOODS,
                'status' => 1,
                'is_deleted' => 0
            ])
            ->select(['id', 'type', 'name', 'thumb', 'content', 'status', 'template_id', 'created_at', 'updated_at'])
            ->first();

        if (empty($poster)) {

            // 返回默认模板
            $poster = PosterTemplateModel::find()
                ->where([
                    'system_id' => 1,
                    'type' => PosterTypeConstant::POSTER_TYPE_GOODS,
                    'status' => 1,
                ])
                ->first();
        }

        return $this->result(['data' => $poster]);

    }


    /**
     * 获取分销海报
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCommission()
    {

        $poster = [];

        //判断权限
        $poster = PosterModel::find()
            ->where([
                'type' => PosterTypeConstant::POSTER_TYPE_COMMISSION,
                'status' => 1,
                'is_deleted' => 0
            ])
            ->select(['id', 'type', 'name', 'thumb', 'content', 'status', 'template_id', 'visit_page', 'created_at', 'updated_at'])
            ->first();

        if (empty($poster)) {
            // 返回默认模板
            $poster = PosterTemplateModel::find()
                ->where([
                    'system_id' => 2,
                    'type' => PosterTypeConstant::POSTER_TYPE_COMMISSION,
                    'status' => 1,
                ])
                ->first();
        }

        if ($poster['visit_page'] == 1) {//商城主页
            $url = '/';
        } elseif ($poster['visit_page'] == 2) {//分销主页
            $url = '/kdxCommission/index/index';
        } else {
            $url = '';
        }

        $poster['url'] = ShopUrlHelper::wap($url, [
            'inviter_id' => $this->memberId
        ], true);

        return $this->result(['data' => $poster]);
    }


}