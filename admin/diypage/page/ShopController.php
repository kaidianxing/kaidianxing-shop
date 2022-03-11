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

namespace shopstar\admin\diypage\page;

use shopstar\helpers\ArrayHelper;
use shopstar\constants\diypage\DiypageTypeConstant;
use shopstar\models\diypage\DiypageModel;
use shopstar\bases\KdxAdminApiController;

/**
 * 商城页面
 * Class ShopController
 * @package apps\diypage\manage\page
 */
class ShopController extends KdxAdminApiController
{

    /**
     * 列表概览
     * @return array|\yii\web\Response
     * @throws \yii\db\Exception
     * @author likexin
     */
    public function actionListView()
    {
        $result = [];

        // 查询应用中页面
        $result['used_page'] = DiypageModel::getListResult(DiypageTypeConstant::$pageShopMap, [
            'andWhere' => [
                ['status' => 1],
            ],
            'pager' => false,
            'onlyList' => true,
        ]);

        // 返回页面类型，并且查询对应的页面数量
        $result['page_type'] = array_map(function ($type) {
            $total = DiypageModel::find()
                ->where([
                    'type' => $type,
                ])
                ->count();
            return [
                'type' => $type,
                'name' => DiypageTypeConstant::getMessage($type),
                'total' => $total,
            ];
        }, DiypageTypeConstant::$pageShopMap);

        // 计算全部页面数量
        array_unshift($result['page_type'], [
            'type' => null,
            'name' => '全部页面',
            'total' => ArrayHelper::columnSum($result['page_type'], 'total'),
        ]);

        return $this->result($result);
    }

}