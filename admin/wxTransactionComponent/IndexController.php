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
use shopstar\constants\wxTransactionComponent\WxTransactionComponentConstant;
use shopstar\exceptions\wxTransactionComponent\WxAuditCategoryException;
use shopstar\exceptions\wxTransactionComponent\WxTransactionComponentException;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\wxTransactionComponent\WxTransactionComponentModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use shopstar\services\wxTransactionComponent\WxAuditCategoryImagesService;
use shopstar\services\wxTransactionComponent\WxAuditCategoryService;
use shopstar\services\wxTransactionComponent\WxTransactionComponentService;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\web\Response;

/**
 * 微信自定义交易组件
 * Class IndexController.
 * @package shopstar\admin\wxTransactionComponent
 */
class IndexController extends KdxAdminApiController
{
    /**
     * 商品列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $params = [];
        $this->process($params);

        $wxTransactionComponentInfo = WxTransactionComponentModel::getColl($params, [
            'callable' => function (&$row) {
                if ($row['goods_status'] == 1 && $row['stock'] > 0 && $row['is_deleted'] == 0) {
                    $row['goods_status'] = 1; // 上架
                } else if ($row['goods_status'] == 1 && $row['stock'] == 0 && $row['is_deleted'] == 0) {
                    $row['goods_status'] = 2; // 售罄
                } else if ($row['goods_status'] == 0 && $row['is_deleted'] == 0) {
                    $row['goods_status'] = 3; // 下架
                } else if ($row['is_deleted'] == 1) {
                    $row['goods_status'] = 4; // 回收站
                }
            }
        ]);

        return $this->success(['data' => $wxTransactionComponentInfo]);
    }

    /**
     * 处理参数
     * @param $params
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    private function process(&$params)
    {
        $get = RequestHelper::get();

        $params = [
            'alias' => 'wx',
            'leftJoin' => [GoodsModel::tableName() . ' goods', 'goods.id = wx.goods_id'],
            'where' => [],
            'select' => [
                'wx.id',
                'goods.title',
                'wx.category_id',
                'wx.category_name',
                'goods.price',
                'goods.stock',
                'goods.status as goods_status',
                'wx.create_time',
                'wx.status as wx_status',
                'wx.remote_status as wx_remote_status',
                'goods.thumb',
                'goods.has_option',
                'goods.type',
                'goods.min_price',
                'goods.max_price',
                'wx.goods_id',
                'goods.is_deleted',
            ],
            'orderBy' => [
                'wx.id' => SORT_DESC   // 等同于添加时间倒序 因id在索引中 所以更快
            ]
        ];

        if (!empty($get['keywords'])) {
            $params['searchs'][] = ['goods.title', 'like', 'keywords'];
        }

        if (!empty($get['goods_status'])) {
            switch ($get['goods_status']) {
                case 1://上架
                    $params['where']['goods.status'] = [1, 2];
                    $params['andWhere'][] = ['>', 'goods.stock', 0];
                    $params['where']['goods.is_deleted'] = 0;
                    break;
                case 2: //售罄
                    $params['where']['goods.status'] = 1;
                    $params['where']['goods.stock'] = 0;
                    $params['where']['goods.is_deleted'] = 0;
                    break;
                case 5: //上架和售罄
                    $params['where']['goods.status'] = [1, 2];
                    $params['where']['goods.is_deleted'] = 0;
                    break;
                case 3: //下架
                    $params['where']['goods.status'] = 0;
                    $params['where']['goods.is_deleted'] = 0;
                    break;
                case 4: //删除
                    $params['where']['goods.is_deleted'] = 1;
                    break;
                default: //上架下架和售罄
                    $params['where']['goods.status'] = [0, 1, 2];
                    $params['where']['goods.is_deleted'] = 0;
                    break;
            }
        }

        if (!empty($get['wx_status'])) {
            $params['searchs'][] = ['wx.status', 'int', 'wx_status'];
        }

        if (!empty($get['start_time']) && !empty($get['end_time'])) {
            $params['andWhere'][] = ['between', 'wx.create_time', $get['start_time'], $get['end_time']];
        }
    }

    /**
     * 查看
     * @return array|int[]|Response
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::postInt('id');

        if (!$id) {
            throw new WxTransactionComponentException(WxTransactionComponentException::PARAMS_ERROR);
        }

        $info = WxTransactionComponentModel::find()
            ->alias('wx')
            ->leftJoin(GoodsModel::tableName() . ' goods', 'goods.id=wx.goods_id')
            ->where([
                'wx.id' => $id,
            ])
            ->select([
                'wx.id',
                'wx.category_id',
                'wx.goods_id',
                'goods.title',
                'goods.thumb',
                'goods.type',
            ])
            ->first();

        $cateInfo = WxAuditCategoryService::getCatByLastId($info['category_id']);

        // 处理格式
        $result['category_name'] = $cateInfo['first_cat_name'] . '/' . $cateInfo['second_cat_name'] . '/' . $cateInfo['third_cat_name'];
        $result['goods_qualification'] = WxAuditCategoryImagesService::getListByWxId($info['id']);
        $result['goods_info'] = [
            'title' => $info['title'],
            'thumb' => $info['thumb'],
            'type' => $info['type'],
        ];

        return $this->result(['data' => $result]);
    }

    /**
     * 添加商品
     * @return array|int[]|Response
     * @throws WxTransactionComponentException
     * @throws WxAuditCategoryException
     * @throws \yii\base\Exception
     * @throws InvalidConfigException
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $ids = RequestHelper::post('goods_id');
        $categoryId = RequestHelper::postInt('category_id');
        $categoryName = RequestHelper::post('category_name');
        // 商品资质图片
        $goodsQualification = RequestHelper::postArray('goods_qualification');

        if (count($goodsQualification) > 50) {
            throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_NUMBER_ERROR);
        }
        if ($categoryId == 0 || !$ids || empty($categoryName)) {
            throw new WxTransactionComponentException(WxTransactionComponentException::PARAMS_ERROR);
        }
        if (count($ids) > 10) {
            throw new WxTransactionComponentException(WxTransactionComponentException::SHOP_GOODS_NUMBER_BEYOND_ERROR);

        }

        $params = [
            'where' => [
                'id' => $ids,
                'is_deleted' => 0,
            ],
            'select' => [
                'title',
                'thumb',
                'id',
                'price',
                'original_price',
                'stock',
                'goods_sku',
                'bar_code',
                'has_option',
            ]
        ];

        $goodsList = GoodsModel::getColl($params, ['onlyList' => true, 'pager' => false]);

        if ($goodsList) {
            $result = WxTransactionComponentService::AddData($goodsList, $categoryId, $categoryName, $goodsQualification);

            if ($result['errcode'] != 0) {
                throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_ADD_ERROR, $result['errmsg']);
            }
        }

        return $this->result($result);
    }

    /**
     * 提交审核 (更新商品)
     * @return array|int[]|Response
     * @throws Exception
     * @throws InvalidConfigException
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdateGoods()
    {
        $id = RequestHelper::postInt('id');
        $categoryId = RequestHelper::postInt('category_id');
        $categoryName = RequestHelper::post('category_name');
        $goodsQualification = RequestHelper::postArray('goods_qualification');

        if (count($goodsQualification) > 50) {
            throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_NUMBER_ERROR);
        }
        if ($categoryId == 0 || !$id || empty($categoryName)) {
            throw new WxTransactionComponentException(WxTransactionComponentException::PARAMS_ERROR);
        }

        $wxTransactionComponentInfo = WxTransactionComponentModel::findOne(['id' => $id]);

        // 如果交易组件表中不存在记录信息
        if (!$wxTransactionComponentInfo) {
            throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_GOODS_LOG_ERROR);
        }
        // 审核中禁止再次提交
        if ($wxTransactionComponentInfo->status == WxTransactionComponentConstant::STATUS_IN) {
            throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_GOODS_UPLOAD_STATUS_IN_ERROR);
        }

        $goodsInfo = GoodsModel::find()->where([
            'id' => $wxTransactionComponentInfo->goods_id,
            'is_deleted' => 0,
        ])->select([
            'title',
            'thumb',
            'id',
            'price',
            'original_price',
            'stock',
            'goods_sku',
            'bar_code',
        ])->first();

        if ($goodsInfo) {
            if ($goodsQualification) {
                // 处理全路径
                foreach ($goodsQualification as &$value) {
                    $value = CoreAttachmentService::getUrl($value);
                }
            }
            $result = WxTransactionComponentService::updateGoods($wxTransactionComponentInfo, $goodsInfo, $categoryId, $categoryName, $goodsQualification);
        }

        return $this->result(['data' => $result]);
    }

    /**
     * 上下架
     * @return array|int[]|Response
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdateStatus()
    {
        $id = RequestHelper::postInt('id');
        $status = RequestHelper::postInt('status');

        if ($id == 0 || $status == 0) {
            throw new WxTransactionComponentException(WxTransactionComponentException::PARAMS_ERROR);
        }

        $model = WxTransactionComponentModel::findOne(['id' => $id]);
        if ($model && $model->status == WxTransactionComponentConstant::STATUS_IN) {
            throw new WxTransactionComponentException(WxTransactionComponentException::WX_TRANSACTION_COMPONENT_STATUS_IN_NOT_UPDATE_STATUS_ERROR);
        }

        $result = WxTransactionComponentService::updateStatus($id, $status);

        return $this->success(['data' => $result]);
    }

    /**
     * 删除
     * @return array|int[]|Response
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::postInt('id');

        if (!$id) {
            throw new WxTransactionComponentException(WxTransactionComponentException::PARAMS_ERROR);
        }

        $result = WxTransactionComponentService::deleteGoods($id);

        return $this->success(['data' => $result]);
    }

    /**
     * 同步商品是否审核通过
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetStatus()
    {
        $page = RequestHelper::get('page', 1);
        $pageSize = RequestHelper::get('page_size', 10);

        $result = WxTransactionComponentService::getStatus($page, $pageSize);

        return $this->success(['data' => $result]);
    }

    /**
     * 免审更新
     * @return array|int[]|Response
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionExemptionUpdate()
    {
        $id = RequestHelper::postInt('id');

        if (!$id) {
            throw new WxTransactionComponentException(WxTransactionComponentException::PARAMS_ERROR);
        }

        $result = WxTransactionComponentService::exemptionUpdate($id);

        return $this->success($result);
    }

    /**
     * 撤销审核
     * @return array|int[]|Response
     * @throws WxTransactionComponentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionResetAudit()
    {
        $id = RequestHelper::postInt('id');

        if (!$id) {
            throw new WxTransactionComponentException(WxTransactionComponentException::PARAMS_ERROR);
        }

        $result = WxTransactionComponentService::resetAudit($id);

        return $this->success($result);
    }

    /**
     * 获取类目
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetCat()
    {
        $sign = RequestHelper::get('sign', false);

        $result = WxTransactionComponentService::getCache((bool)$sign);

        return $this->success(['data' => $result]);
    }
}
