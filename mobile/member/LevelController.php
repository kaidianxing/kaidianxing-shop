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
use shopstar\models\member\MemberLevelModel;
use yii\helpers\Json;

/**
 * @author 青岛开店星信息技术有限公司
 */
class LevelController extends BaseMobileApiController
{
    /**
     * 等级
     * @return array|int[]|\yii\web\Response
     */
    public function actionIndex()
    {
        $list = MemberLevelModel::find()
            ->select('id,level,level_name,discount,is_discount,is_default,discount,update_condition,order_count,order_money,goods_ids,state')
            ->orderBy(['is_default' => SORT_DESC, 'level' => SORT_ASC])
            ->get();

        foreach ($list as $index => &$item) {
            if ($item['state'] == 0 && $item['id'] != $this->member['level_id']) {
                unset($list[$index]);
                continue;
            }
            $text = '';

            if ($item['update_condition'] == 1) {
                $text .= '累计订单数达到' . $item['order_count'] . '个';
            } elseif ($item['update_condition'] == 2) {
                $text .= '订单金额满' . $item['order_money'] . '元';
            } elseif ($item['update_condition'] == 3) {
                $text .= '购买指定商品';
                $item['goods_ids'] = Json::decode($item['goods_ids']);
            }

            $item['update_text'] = $text;
        }
        unset($item);

        return $this->result(['data' => array_values($list)]);
    }
}
