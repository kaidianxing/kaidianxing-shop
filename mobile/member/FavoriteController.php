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
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\GoodsActivityModel;
use shopstar\models\member\MemberFavoriteModel;
use shopstar\services\goods\GoodsService;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class FavoriteController extends BaseMobileApiController
{
    /**
     * 获取收藏列表
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex(): \yii\web\Response
    {
        $now = DateTimeHelper::now();
        $params = [
            'select' => [
                'favorite.*',
                'if(isnull(activity.id), 0, 1) as has_activity'
            ],
            'alias' => 'favorite',
            'where' => ['favorite.member_id' => $this->memberId],
            'with' => [
                'goods' => function ($query) {
                    $query->select('title, thumb, original_price, price, id,ext_field');
                }
            ],
            'leftJoin' => [GoodsActivityModel::tableName() . ' activity', "activity.goods_id=favorite.goods_id and activity.start_time<'$now' and activity.end_time>'$now' and activity.is_delete_activity=0"],
            'orderBy' => ['favorite.id' => SORT_DESC]
        ];
        $list = MemberFavoriteModel::getColl($params, [
            'callable' => function (&$row) {
                // 购买按钮
                $row['goods']['ext_field'] = Json::decode($row['goods']['ext_field']) ?? [];
                // 自定义购买按钮status, 影响加购按钮及价格文字显示
                $row['goods']['buy_button_status'] = GoodsService::getBuyButtonStatus($row['goods']['ext_field']['buy_button_type'], $row['goods']['ext_field']['buy_button_settings']);
            }
        ]);
        return $this->result($list);
    }

    /**
     * 删除收藏
     * @return \yii\web\Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete(): \yii\web\Response
    {
        $ids = RequestHelper::postArray('ids');
        if (empty($ids)) {
            throw new MemberException(MemberException::FAVORITE_DELETE_PARAM_ERROR);
        }
        MemberFavoriteModel::deleteAll(['id' => $ids, 'member_id' => $this->memberId]);

        return $this->success();
    }

    /**
     * 收藏/取消收藏动作
     * @return array|\yii\web\Response
     * @throws MemberException|\yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChange()
    {
        $goodsId = RequestHelper::postArray('goods_id');
        $isAdd = RequestHelper::post('is_add');
        $res = MemberFavoriteModel::changeFavorite($isAdd == 1, $goodsId, $this->memberId);
        if ($res == false) {
            throw new MemberException(MemberException::MEMBER_FAVORITE_CHANGE_FAIL);
        }
        return $this->success();
    }
}
