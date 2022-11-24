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

namespace shopstar\admin\creditShop;

use shopstar\bases\KdxAdminApiController;
use shopstar\components\wechat\helpers\MiniProgramACodeHelper;
use shopstar\constants\coupon\CouponConstant;
use shopstar\constants\creditShop\CreditShopGoodsTypeConstant;
use shopstar\exceptions\creditShop\CreditShopException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\creditShop\CreditShopGoodsModel;
use shopstar\models\creditShop\CreditShopGoodsOptionModel;
use shopstar\models\creditShop\CreditShopOrderModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\sale\CouponModel;
use shopstar\services\creditShop\CreditShopGoodsService;
use Throwable;
use yii\db\Exception;
use yii\helpers\Json;
use yii\web\Response;

/**
 * 积分商城商品控制器
 * Class ListController.
 * @package shopstar\admin\creditShop
 */
class ListController extends KdxAdminApiController
{
    /**
     * 需要控制的Actions
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'index',
        ],
        'allowHeaderActions ' => [
            'index',
        ],
    ];

    /**
     * 获取商品列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $startTime = RequestHelper::get('start_time');
        $endTime = RequestHelper::get('end_time');
        $id = RequestHelper::getArray('id');
        $export = RequestHelper::get('export');

        $andWhere = [];
        // 创建时间搜索
        if (!empty($startTime) && !empty($endTime)) {
            $andWhere[] = ['between', 'goods.created_at', $startTime, $endTime];
        }

        if (!empty($id)) {
            $andWhere[] = ['goods.id' => $id];
        }

        $params = [
            'select' => [
                'goods.id',
                'goods.goods_id',
                'goods.type',
                'shop_goods.title',
                'shop_goods.short_name',
                'shop_goods.sub_name',
                'shop_goods.thumb',
                'shop_goods.type as goods_type',
                'shop_goods.price',
                'shop_coupon.coupon_name',
                'shop_coupon.coupon_sale_type',
                'shop_coupon.enough',
                'shop_coupon.discount_price',
                'shop_coupon.credit shop_coupon_credit',
                'shop_coupon.balance shop_coupon_balance',
                'goods.has_option',
                'goods.credit_shop_credit',
                'goods.credit_shop_price',
                'goods.credit_shop_stock',
                'goods.sale',
                'goods.created_at',
                'goods.status',
                'shop_goods.is_deleted shop_goods_is_delete', // 商品删除不允许编辑
                'sum(if(order.status>0, order.pay_credit, 0)) as pay_credit',
                'sum(if(order.status>0, order.pay_price, 0)) as pay_price',
            ],
            'alias' => 'goods',
            'searchs' => [
                [['shop_coupon.coupon_name', 'shop_goods.title'], 'like', 'keyword'],
                ['goods.status', 'int', 'status']
            ],
            'where' => [
                'goods.is_delete' => 0,
            ],
            'andWhere' => $andWhere,
            'leftJoins' => [
                [GoodsModel::tableName() . ' shop_goods', 'shop_goods.id = goods.goods_id and goods.type = 0'],
                [CouponModel::tableName() . ' shop_coupon', 'shop_coupon.id = goods.goods_id and goods.type = 1'],
                [CreditShopOrderModel::tableName() . ' order', 'order.goods_id = goods.id'],
            ],
            'orderBy' => [
                'goods.created_at' => SORT_DESC,
                'goods.id' => SORT_DESC,
            ],
            'groupBy' => [
                'goods.id'
            ],
        ];

        $list = CreditShopGoodsModel::getColl($params, [
            'pager' => !$export,
            'callable' => function (&$row) {

                if ($row['type'] == CreditShopGoodsTypeConstant::COUPON) {
                    // 如果是立减类型
                    if ($row['coupon_sale_type'] == CouponConstant::COUPON_SALE_TYPE_SUB) {
                        $row['content'] = '满' . ValueHelper::delZero($row['enough']) . '减' . ValueHelper::delZero($row['discount_price']);
                    } else {
                        // 打折类型
                        $row['content'] = '满' . ValueHelper::delZero($row['enough']) . '享' . ValueHelper::delZero($row['discount_price']) . '折';
                    }
                }

                if ($row['has_option']) {
                    // 查找多规格
                    $optionData = CreditShopGoodsOptionModel::find()->where(['credit_shop_goods_id' => $row['id'], 'is_join' => 1])->orderBy(['credit_shop_price' => SORT_ASC, 'credit_shop_credit' => SORT_ASC])->get();
                    $end = end($optionData);
                    // 上面已经排好序 直接取第一个和最后一个元素即可
                    $row['rules'] = [
                        'min' => [
                            'credit_shop_credit' => $optionData[0]['credit_shop_credit'],
                            'credit_shop_price' => $optionData[0]['credit_shop_price']
                        ],
                        'max' => [
                            'credit_shop_credit' => $end['credit_shop_credit'],
                            'credit_shop_price' => $end['credit_shop_price']
                        ]
                    ];
                }
            }
        ]);

        // 导出
        if ($export) {
            try {
                $service = new CreditShopGoodsService();
                $service->export($list['list']);
            } catch (Throwable $exception) {
                return $this->error('导出失败');
            }
        }

        return $this->result($list);
    }

    /**
     * 获取商品详情
     * @return array|int[]|Response
     * @throws CreditShopException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        // 获取详情
        $service = new CreditShopGoodsService();
        $detail = $service->detail($id);

        return $this->result(['data' => $detail]);
    }

    /**
     * 添加积分商品
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $data = RequestHelper::post();

        // 校验数据
        $data['goods'] = Json::decode($data['goods'] ?? '[]');
        if (empty($data['goods'])) {
            return $this->error('商品信息错误');
        }

        // 添加
        $service = new CreditShopGoodsService();
        // 校验数据
        $res = $service->checkSaveData($data);

        if (is_error($res)) {
            return $this->error($res['message']);
        }

        // 添加
        $service->add($data, $this->userId);

        return $this->success();
    }

    /**
     * 编辑积分商品
     * @return array|int[]|Response
     * @throws CreditShopException
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $data = RequestHelper::post();

        if (empty($data['id'])) {
            return $this->error('参数错误');
        }
        // 校验数据
        $data['goods'] = Json::decode($data['goods'] ?? '[]');
        if (empty($data['goods'])) {
            return $this->error('商品信息错误');
        }

        $service = new CreditShopGoodsService();
        // 校验数据
        $res = $service->checkSaveData($data, true);

        if (is_error($res)) {
            return $this->error($res['message']);
        }
        $service->edit($data, $this->userId);

        return $this->success();
    }

    /**
     * 删除积分商品
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        // 删除
        $service = new CreditShopGoodsService();
        $service->delete($id, $this->userId);

        return $this->success();
    }

    /**
     * 上架/下架
     * @return array|int[]|Response
     * @throws CreditShopException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeStatus()
    {
        $status = RequestHelper::post('status');
        $id = RequestHelper::post('id');

        if (empty($id) || $status == '') {
            return $this->error('参数错误');
        }

        // 修改状态
        $service = new CreditShopGoodsService();
        $service->changeStatus($id, $status, $this->userId);

        return $this->success();
    }

    /**
     * 积分商品获取推广二维码
     * @return array|int[]|Response
     * @throws CreditShopException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetGoodsQrcode()
    {
        $goodsId = RequestHelper::get('id');
        if (empty($goodsId)) {
            throw new CreditShopException(CreditShopException::GET_CODE_PARAMS_ERROR);
        }

        $md5Str = md5('creditShopGoods_' . $goodsId);
        //文件名
        $fileName = $md5Str . '.jpg';
        //保存地址文件夹
        $savePatchDir = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/';
        //保存地址
        $savePatch = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/' . $fileName;
        //访问地址
        $accessPatch = ShopUrlHelper::build('tmp/wxapp_qrcode/' . $md5Str . '.jpg', [], true);

        //如果不是文件  ||  生成时间大于一天
        if (!is_file($savePatch) || (filemtime($savePatch) && (time() - filemtime($savePatch)) > 86400)) {
            $result = MiniProgramACodeHelper::getUnlimited(http_build_query([
                'goods_id' => $goodsId
            ]), [
                'directory' => $savePatchDir,
                'fileName' => $fileName,
                'page' => 'pagesCreditShop/detail' // TODO 青岛开店星信息技术有限公司
            ]);

            if (is_error($result)) {
                return $this->result($result);
            }
        }

        $data = [
            'qrcode' => $accessPatch,//二维码
            'url' => '/pagesCreditShop/detail?goods_id=' . $goodsId,
        ];

        return $this->result(['data' => $data]);
    }
}
