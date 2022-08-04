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

namespace shopstar\mobile\pc;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\order\OrderActivityTypeConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberFavoriteModel;
use shopstar\models\order\OrderActivityModel;
use shopstar\models\order\OrderGoodsCommentModel;
use yii\db\ActiveRecord;
use yii\web\Response;

class MemberController extends BaseMobileApiController
{
    /**
     * 会员获取信息
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetFavoriteAndCommentGoodsCount()
    {
        $favoriteCount = $this->getFavoriteGoodsCount();
        $commentCount = $this->getCommentGoodsCount();

        return $this->result([
            'data' => [
                'favoriteCount' => $favoriteCount ?? 0,
                'commentCount' => $commentCount ?? 0,
            ],
        ]);
    }

    /**
     * 获取收藏信息
     * @return array|int|string|ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    private function getFavoriteGoodsCount()
    {
        return MemberFavoriteModel::getColl(
            [
                'where' => [
                    'member_id' => $this->memberId
                ],
                'select' => 'id'
            ],
            ['onlyCount' => true,]
        );
    }

    /**
     * 获取分销信息
     * @return array|int|string|ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    private function getCommentGoodsCount()
    {
        $get = RequestHelper::get();
        $where = [
            ['comment.reply_content' => ''],
            ['comment.is_delete' => 0],
            ['in', 'order_activity.activity_id', [
                OrderActivityTypeConstant::ACTIVITY_TYPE_SECKILL,
                OrderActivityTypeConstant::ACTIVITY_TYPE_NORMAL
            ]],
        ];

        $select = 'comment.id';

        $leftJoin = [
            [OrderActivityModel::tableName() . ' order_activity', 'order_activity.order_id=comment.order_id'],
        ];

        $params = [
            'select' => $select,
            'andWhere' => $where,
            'alias' => 'comment',
            'leftJoins' => $leftJoin
        ];

        return OrderGoodsCommentModel::getColl($params, [
            'onlyCount' => true,
        ]);
    }

}
