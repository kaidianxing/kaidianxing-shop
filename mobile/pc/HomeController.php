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
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\pc\GoodsGroupConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\group\GoodsGroupMapModel;
use shopstar\models\pc\PcGoodsGroupModel;
use shopstar\models\pc\PcHomeAdvertiseModel;
use yii\helpers\Json;

class HomeController extends BaseMobileApiController
{
    public $configActions = [
        'allowSessionActions' => [
            '*',
        ],
        // 允许不登录访问的Actions
        'allowActions' => [
            '*',
        ]
    ];

    public function actionInfo()
    {
        // 广告首页大图
        $homeAdvertise = PcHomeAdvertiseModel::getColl([
            'select' => [
                'name','url','img','sort_order',
            ],
            'orderBy' => [
                'sort_order' => SORT_DESC
            ],
        ],['onlyList' => true]);

        // pc渠道的秒杀活动信息查询 , 取出来最近的秒杀活动
        $clientType = ClientTypeConstant::CLIENT_PC;
        $seckillActivityInfo = ShopMarketingModel::find()->where(['type' => 'seckill'])
            ->andWhere(['<=', 'start_time', DateTimeHelper::now()])
            ->andWhere(['>=', 'end_time', DateTimeHelper::now()])
            ->andWhere('status = 0 ')
            ->andWhere('find_in_set(' . $clientType . ',client_type)')
            ->orderBy(['id' => SORT_DESC])->one();

        // 商品组信息查询
        $pcGoodsGroups = PcGoodsGroupModel::getColl([
            'orderBy' => [
                'sort_order' => SORT_DESC
            ],
            'where' => [
                'status' => 1,
            ],
        ],[
            'onlyList' => true,
            'callable' => function (&$row) {
                $row['goods_info'] = Json::decode($row['goods_info']);
                $goods_type = $row['goods_type'];
                $goods_info = $row['goods_info'];
                $goods_ids = [];

                // 自选商品
                switch ($goods_type) {
                    case GoodsGroupConstant::PC_GOODS_GROUP_TYPE_CHOSE:
                        if (is_array($goods_info) && !empty($goods_info)) {
                            foreach ($goods_info as $goods_info_one) {
                                if ($goods_info_one['id']) {
                                    $goods_ids[] = $goods_info_one['id'];
                                }
                            }
                        }
                        break;
                    case GoodsGroupConstant::PC_GOODS_GROUP_TYPE_CATEGORY:
                        $cateId = $goods_info['params']['cateid'] ?? '';
                        // 通过分类id 获取商品
                        if ($cateId) {
                            $goods_ids = GoodsCategoryMapModel::getGoodsIdByCategoryId([$cateId]);
                        }
                        break;
                    case GoodsGroupConstant::PC_GOODS_GROUP_TYPE_GROUP:
                        $groupId = $goods_info['params']['groupid'] ?? '';
                        // 通过商品组id 获取商品
                        if ($groupId) {
                            $goods_ids = GoodsGroupMapModel::getGoodsIdByGroupId($groupId);
                        }
                        break;
                    default:
                        break;
                }

                if (!empty($goods_ids)) {
                    $goods_ids = array_slice($goods_ids, 0, 10);
                    $goods_ids = implode(',', $goods_ids);
                }
                $row['goods_ids'] = $goods_ids;
            }
        ]);

        return $this->success(['data' => [
            'homeAdvertise' => $homeAdvertise,
            'seckillActivityInfo' => $seckillActivityInfo,
            'pcGoodsGroups' => $pcGoodsGroups,
        ]]);
    }
}
