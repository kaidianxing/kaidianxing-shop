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

namespace shopstar\mobile\member;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\models\member\MemberBrowseFootprintModel;
use shopstar\services\goods\GoodsService;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class FootprintController extends BaseMobileApiController
{
    /**
     * 获取浏览足迹列表
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex(): \yii\web\Response
    {
        $params = [
            'where' => ['member_id' => $this->memberId],
            'with' => [
                'goods' => function ($query) {
                    $query->select('title, thumb, original_price, price,id,ext_field');
                },
                'favorite' => function ($fa) {
                    $fa->where(['member_id' => $this->memberId]);
                }
            ],
            'orderBy' => ['updated_at' => SORT_DESC],
        ];

        $list = MemberBrowseFootprintModel::getColl($params, [
            'callable' => function (&$row) {
                $row['date'] = date('Y-m-d', strtotime($row['updated_at']));
                $row['goods']['ext_field'] = Json::decode($row['goods']['ext_field']) ?? [];
                // 自定义购买按钮status, 影响加购按钮及价格文字显示
                $row['goods']['buy_button_status'] = GoodsService::getBuyButtonStatus($row['goods']['ext_field']['buy_button_type'], $row['goods']['ext_field']['buy_button_settings']);
            }
        ]);
        return $this->result(['data' => $list]);
    }
}
