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

namespace shopstar\admin\pc;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\pc\GoodsGroupConstant;
use shopstar\exceptions\pc\PcException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\pc\PcGoodsGroupModel;
use Throwable;
use yii\db\StaleObjectException;
use yii\helpers\Json;
use yii\web\Response;

class GoodsGroupController extends KdxAdminApiController
{
    /**
     * 获取商品类型
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionTypes()
    {
        $types = [
            [
                'key' => GoodsGroupConstant::PC_GOODS_GROUP_TYPE_CHOSE,
                'val' => GoodsGroupConstant::getText(GoodsGroupConstant::PC_GOODS_GROUP_TYPE_CHOSE),
            ],
            [
                'key' => GoodsGroupConstant::PC_GOODS_GROUP_TYPE_CATEGORY,
                'val' => GoodsGroupConstant::getText(GoodsGroupConstant::PC_GOODS_GROUP_TYPE_CATEGORY),
            ],
            [
                'key' => GoodsGroupConstant::PC_GOODS_GROUP_TYPE_GROUP,
                'val' => GoodsGroupConstant::getText(GoodsGroupConstant::PC_GOODS_GROUP_TYPE_GROUP),
            ],
        ];

        return $this->result(['data' => $types]);
    }

    /**
     * 获取商品组列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        // 搜索
        $searchs = [
            ['name', 'like', 'name'],
            ['goods_type', 'int', 'goods_type'],
        ];

        $andWhere = [];
        // 创建时间搜索
        $start_time = RequestHelper::get('start_time', '');
        $end_time = RequestHelper::get('end_time', '');

        if (!empty($start_time) && !empty($end_time)) {
            $andWhere[] = ['between', 'created_at', $start_time, $end_time];
        }

        $list = PcGoodsGroupModel::getColl([
            'searchs' => $searchs,
            'orderBy' => [
                'sort_order' => SORT_DESC
            ],
            'andWhere' => $andWhere,
            'select' => [
                'id',
                'name',
                'status',
                'created_at',
                'sort_order',
                'goods_type'
            ]
        ]);

        return $this->result($list);
    }

    /**
     * 获取商品组详情
     * @return array|int[]|Response
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::getInt('id');
        if (!$id) {
            throw new PcException(PcException::GOODS_GROUP_ID_EMPTY);
        }

        $one = PcGoodsGroupModel::findOne($id);

        return $this->result(['data' => $one]);
    }

    /**
     * 添加商品组
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $result = PcGoodsGroupModel::easyAdd([
            'attributes' => [
                'created_at' => DateTimeHelper::now()
            ],
            'beforeSave' => function (&$result) {
                $result['goods_info'] = Json::encode($result['goods_info']);
            }
        ]);

        return $this->result($result);
    }

    /**
     * 修改商品组
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $result = PcGoodsGroupModel::easyEdit([
            'attributes' => [],
            'beforeSave' => function (&$result) {
                $result['goods_info'] = Json::encode($result['goods_info']);
            },
            'onLoad' => function (&$result) {
                $result['data']['goods_info'] = Json::decode($result['data']['goods_info']);
            }
        ]);

        return $this->result($result);
    }

    /**
     * 删除商品组
     * @return array|int[]|Response
     * @throws Throwable
     * @throws StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $result = PcGoodsGroupModel::easyDelete([]);

        return $this->result($result);
    }
}
