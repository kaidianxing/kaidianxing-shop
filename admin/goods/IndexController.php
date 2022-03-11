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


namespace shopstar\admin\goods;

use shopstar\bases\KdxAdminApiController;
use shopstar\models\diypage\DiypageModel;
use shopstar\models\virtualAccount\VirtualAccountModel;
use shopstar\models\wxapp\WxappUploadLogModel;
use shopstar\constants\goods\GoodsBuyButtonConstant;
use shopstar\constants\goods\GoodsDispatchTypeConstant;
use shopstar\constants\goods\GoodsReductionTypeConstant;
use shopstar\constants\goods\GoodsStatusConstant;
use shopstar\constants\goods\GoodsTypeConstant;
use shopstar\constants\log\goods\GoodsLogConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ExcelHelper;
 
use shopstar\helpers\RequestHelper;
use shopstar\helpers\VideoHelper;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\goods\GoodsActivityModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsPermMapModel;
use shopstar\services\goods\GoodsCreator;
use shopstar\services\goods\GoodsAdminQueryService;
use shopstar\services\goods\GoodsService;

class IndexController extends KdxAdminApiController
{

    public $configActions = [
        'postActions' => [
            'create',
            'update',
            'delete',
            'forever-delete',
            'property'
        ],
        'allowHeaderActions' => [
            'list',
        ],
        'allowPermActions' => [
            'list',
            'rand-one-id',
            'get-activity-goods',
            'get-diy-page-buy-button-text',
        ]
    ];

    public function actionIndex()
    {

    }

    /**
     * 随机获取一个上架的商品ID(装修调用)
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionRandOneId()
    {
        $goods = GoodsModel::find()
            ->where([
                'status' => GoodsStatusConstant::GOODS_STATUS_PUTAWAY,
                'is_deleted' => 0,
            ])
            ->select(['id'])
            ->orderBy('RAND()')->first();

        return $this->result([
            'goods_id' => $goods['id'],
        ]);
    }

    /**
     * 商品列表
     * @return \yii\web\Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $pager = RequestHelper::getInt('pager', 0);
        $export = RequestHelper::getInt('export', 0); //是否是导出
        $isPager = $pager != 0 || $export == 0;
        $goodsService = new GoodsAdminQueryService;
        $goodsService->initParams([
            'select' => RequestHelper::get('select', []),
            'labelField' => RequestHelper::get('label_field', ''),
            'status' => RequestHelper::getInt('status', -1),
            'get' => RequestHelper::get(),
            'flag' => RequestHelper::get('flag'),
            'type' => RequestHelper::get('type', 'all'),
            'clientType' => $this->clientType,
        ]);
        $list = $goodsService->getGoodsList([
            'pager' => $isPager,
            'activityType' => RequestHelper::get('activity_type', ''), // 活动类型
            'activityId' => RequestHelper::get('activity_id', 0), // 活动id
            'showActivity' => RequestHelper::get('show_activity', 0), // 是否展示正在参与的活动
            'flag' => RequestHelper::get('flag'), // 是否积分商城商品选择器
            'isShoppingReward' => RequestHelper::get('is_shopping_reward'), // 是否是购物奖励商品选择器
            'export' => $export,
        ]);

        //如果是导出
        if ($export == 1) {
            $this->export($list['list']);
        }

        return $this->success($list);
    }

    /**
     * 获取单个商品
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $goodsId = RequestHelper::getInt('id', 0);
        $flag = RequestHelper::get('flag', '');
        //空参返回添加前
        if (empty($goodsId)) {
            $data = [];

            return $this->success(['data' => $data]);
        }
        $data = GoodsAdminQueryService::getOne($goodsId, $flag);

        return $this->success(['data' => $data]);
    }

    /**
     * 添加商品
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        return $this->save();
    }

    /**
     * 保存商品
     * @param bool $isEdit
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    protected function save($isEdit = false)
    {
        $data = RequestHelper::post();
        (new GoodsCreator($this->userId ?: 0, $this->shopType, $data, $isEdit))->init($data);
        return $this->success();
    }

    /**
     * 修改商品
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        return $this->save(true);
    }

    /**
     * 修改属性
     * @return array|\yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionProperty()
    {
        $id = RequestHelper::post('id');
        $field = RequestHelper::post('field');
        $value = RequestHelper::post('value');
        $result = GoodsModel::changeProperty($this->userId, $id, $field, $value);
        return $this->result($result);
    }

    /**
     * 商品放入回收站
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::post('id');
        $result = GoodsService::deleteGoods($this->userId, $id, GoodsLogConstant::GOODS_DELETE);
        return $this->result($result);
    }

    /**
     * 永久删除商品
     * @return \yii\web\Response
     * @throws \Throwable
     * @author 青岛开店星信息技术有限公司
     */
    public function actionForeverDelete()
    {
        $id = RequestHelper::postInt('id');
        $result = GoodsService::foreverRemove($this->userId, $id, GoodsLogConstant::GOODS_REAL_DELETE);
        return $this->result($result);
    }

    /**
     * 商品获取推广二维码
     * @return array|\yii\web\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetGoodsQrcode()
    {
        $goodsId = RequestHelper::get('id');
        if (empty($goodsId)) {
            throw new GoodsException(GoodsException::CATEGORY_GET_ONE_PARAMS_ERROR);
        }
        $data = [];
        $data = [
            'qrcode' => WxappUploadLogModel::getWxappQRcode('/kdxGoods/detail/index', ['goods_id' => $goodsId]),//二维码
            'url' => '/kdxGoods/detail/index?goods_id=' . $goodsId,
        ];
        return $this->result(['data' => $data]);
    }

    /**
     * 获取所有卡密库
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetVirtualAccount()
    {
        $params = [
            'where' => [
                'is_delete' => 0,
            ],
            'select' => [
                'id',
                'name',
                'total_count',
            ]
        ];

        $data = VirtualAccountModel::getColl($params, [
            'pager' => false,
            'onlyList' => true,
        ]);
        return $this->success(['data' => $data]);
    }

    /**
     * 获取所有活动商品
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetActivityGoods()
    {
        // 活动类型
        $type = RequestHelper::get('activity_type');
        $list = GoodsAdminQueryService::getActivityGoods($type);

        return $this->result($list);
    }

    /**
     * 获取默认商品装修页面的购买按钮文字
     * @return array|int[]|\yii\web\Response
     * @author nizengchao
     */
    public function actionGetDiyPageBuyButtonText()
    {
        $data = DiypageModel::getDiyPageBuyButtonText();
        return $this->result(['data' => $data]);
    }


    /**
     * 导出
     * @param $list
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    protected function export($list)
    {
        // 重组数据
        foreach ($list as &$item) {
            // 商品分类
            if (!empty($item['category'])) {
                $category = array_column($item['category'], 'name');
                $item['category'] = implode('、', $category);
            }
            // 规格
            if ($item['has_option'] == 1) {
                $item['option'] = '多规格';
            } else {
                $item['option'] = '单规格';
            }
            // 商品状态
            $item['status'] = GoodsStatusConstant::getText($item['status']);
            // 减库存方式
            $item['reduction_type'] = GoodsReductionTypeConstant::getText($item['reduction_type']);

            $extField = $item['ext_field'];
            // 营销标签
            $tags = [];
            if ($item['is_recommand'] == 1) {
                $tags[] = '推荐';
            }
            if ($item['is_hot'] == 1) {
                $tags[] = '热卖';
            }
            if ($item['is_new'] == 1) {
                $tags[] = '新品';
            }
            $item['tags'] = implode('、', $tags);
            // 物流支持
            $express = [];
            if ($item['dispatch_express'] == 1) {
                $express[] = '快递';
            }
            if ($item['dispatch_intracity'] == 1) {
                $express[] = '同城';
            }
            $item['express'] = implode('、', $express);
            // 货到付款
            $item['is_delivery_pay'] = $extField['is_delivery_pay'] ? '是' : '否';
            // 运费设置
            $item['dispatch_type'] = GoodsDispatchTypeConstant::getText($item['dispatch_type']);
            // 是否参与分销
            $item['is_commission'] = $item['is_commission'] ? '是' : '否';

            // 商品类型转义
            $item['type'] = GoodsTypeConstant::getText($item['type']);

        }
        unset($item);

        ExcelHelper::export($list, [
            [
                'field' => 'sort_by',
                'title' => '排序',
            ],
            [
                'field' => 'title',
                'title' => '标题',
            ],
            [
                'field' => 'type',
                'title' => '商品类型',
            ],
            [
                'field' => 'price',
                'title' => '价格',
            ],
            [
                'field' => 'option',
                'title' => '商品规格',
            ],
            [
                'field' => 'stock',
                'title' => '库存',
            ],
            [
                'field' => 'real_sales',
                'title' => '真实销量',
            ],
            [
                'field' => 'goods_sku',
                'title' => '商品编码',
            ],
            [
                'field' => 'bar_code',
                'title' => '商品条码',
            ],
            [
                'field' => 'weight',
                'title' => '商品重量',
            ],
            [
                'field' => 'tags',
                'title' => '营销标签',
            ],
            [
                'field' => 'category',
                'title' => '商品分类',
            ],
            [
                'field' => 'status',
                'title' => '商品状态',
            ],
            [
                'field' => 'reduction_type',
                'title' => '减库存方式',
            ],
            [
                'field' => 'express',
                'title' => '物流支持',
            ],
            [
                'field' => 'dispatch_type',
                'title' => '运费设置',
            ],
            [
                'field' => 'is_delivery_pay',
                'title' => '货到付款',
            ],
            [
                'field' => 'is_commission',
                'title' => '是否参与分销',
            ],
            [
                'field' => 'created_at',
                'title' => '创建时间',
            ],

        ], '商品列表数据导出');
        die;
    }


}
