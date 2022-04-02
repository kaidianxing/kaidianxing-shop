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

namespace shopstar\admin\commission;

use shopstar\bases\KdxAdminApiController;
use shopstar\exceptions\commission\CommissionGoodsException;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionGoodsModel;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\group\GoodsGroupMapModel;
use yii\helpers\Json;

/**
 * 分销商品
 * Class GoodsController
 * @package shopstar\admin\commission
 */
class GoodsController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'cancel',
        ]
    ];

    /**
     * 分销商品列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $params = $this->getParams();
        $list = GoodsModel::getColl($params, [
            'callable' => function (&$row) {
                $row['ext_field'] = Json::decode($row['ext_field']);
                // 转换状态
                if (($row['status'] == 1 || $row['status'] == 2) && $row['stock'] > 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 1;
                } else if ($row['status'] == 1 && $row['stock'] == 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 2;
                } else if ($row['status'] == 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 3;
                } else if ($row['is_deleted'] == 1) {
                    $row['status'] = 4;
                }

                // 获取该商品所有佣金
                $allCommission = CommissionGoodsModel::getGoodsAllCommission($row['id']);
                if (!is_error($allCommission)) {
                    // 取最大值和最小值
                    // 如果有多规格
                    $data = [];
                    //多规格或预约商品
                    if ($row['has_option']) {
                        foreach ($allCommission as $level) {
                            foreach ($level as $option) {
                                if (is_array($option)) {
                                    foreach ($option as $value) {
                                        $data[] = $value;
                                    }
                                }
                            }
                        }
                    } else {
                        // 单规格
                        foreach ($allCommission as $level) {
                            foreach ($level as $value) {
                                $data[] = $value;
                            }
                        }
                    }
                    if (is_array($data) && !empty($data)) {
                        $minCommission = min($data);
                        $maxCommission = max($data);
                        $row['commission'] = '￥' . $minCommission . ' ~ ￥' . $maxCommission;
                    }
                }
            },
        ]);

        return $this->result($list);
    }

    /**
     * 获取参数
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    protected function getParams(): array
    {
        $status = RequestHelper::getInt('status', -1);
        $get = RequestHelper::get();

        $params = [
            'where' => ['is_commission' => 1, 'is_deleted' => 0],
            'select' => [
                'id',
                'title',
                'thumb',
                'has_option',
                'stock',
                'price',
                'real_sales',
                'status',
                'type'
            ],
            'searchs' => [
                [['title', 'goods_sku', 'bar_code', 'id'], 'like', 'keywords'],
            ],
        ];

        //如果有排序
        if (!empty($get['sort']) && !empty($get['by'])) {
            $params['orderBy'][$get['sort']] = $get['by'] == 'asc' ? SORT_ASC : SORT_DESC;
            $params['orderBy']['sort_by'] = SORT_DESC;
        } else {
            //追加排序
            $params['orderBy']['sort_by'] = SORT_DESC;
            $params['orderBy']['created_at'] = SORT_DESC;
        }

        //按照分类id查找
        if (!empty($get['category_id'])) {
            $goodsId = GoodsCategoryMapModel::getGoodsIdByCategoryId([$get['category_id']]) ?: [];
            empty($goodsId) ? $params['where']['id'] = 0 : $params['where']['id'] = $goodsId;
        }

        //如果有分组id则根据分组id查找
        if ($get['group_id']) {
            $goodsId = GoodsGroupMapModel::getGoodsIdByGroupId($get['group_id']) ?: [];
            empty($goodsId) ? $params['where']['id'] = 0 : $params['where']['id'] = $goodsId;
        }

        switch ($status) {
            case 1:// 上架
                $params['where']['status'] = [1, 2];
                $params['andWhere'][] = ['>', 'stock', 0];
                $params['where']['is_deleted'] = 0;
                break;
            case 2: // 售罄
                $params['where']['status'] = 1;
                $params['where']['stock'] = 0;
                $params['where']['is_deleted'] = 0;

                break;
            case 3: // 下架
                $params['where']['status'] = 0;
                $params['where']['is_deleted'] = 0;
                break;
            case 4: // 回收站
                $params['where']['is_deleted'] = 1;
                break;
        }

        // 根据商品类型筛选
        if (isset($get['type']) && $get['type'] != 'all') {
            $params['where']['type'] = $get['type'];
        }

        return $params;
    }

    /**
     * 取消商品分销状态
     * @throws CommissionGoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCancel()
    {
        $goodsId = RequestHelper::post('id');
        if (empty($goodsId)) {
            throw new CommissionGoodsException(CommissionGoodsException::GOODS_CANCEL_PARAMS_ERROR);
        }
        try {
            GoodsModel::updateAll(['is_commission' => 0], ['id' => $goodsId,]);
        } catch (\Throwable $exception) {
            throw new CommissionGoodsException(CommissionGoodsException::GOODS_CANCEL_FAIL, $exception->getMessage());
        }
        return $this->success();
    }

}
