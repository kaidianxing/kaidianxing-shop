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

namespace shopstar\mobile\creditShop;

use shopstar\bases\controller\BaseCreditShopMobileApiController;
use shopstar\components\wechat\helpers\MiniProgramACodeHelper;
use shopstar\constants\coupon\CouponConstant;
use shopstar\constants\creditShop\CreditShopGoodsTypeConstant;
use shopstar\constants\goods\GoodsTypeConstant;
use shopstar\exceptions\creditShop\CreditShopException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\creditShop\CreditShopGoodsModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\creditShop\CreditShopGoodsService;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * 积分商城移动端控制器
 * Class IndexController.
 * @package shopstar\mobile\creditShop
 */
class IndexController extends BaseCreditShopMobileApiController
{
    /**
     * 需要控制的Actions
     * @var array
     */
    public $configActions = [
        'allowNotLoginActions' => [
            'list',
            'detail',
            'get-option',
            'get-set',
        ],
    ];

    /**
     * 获取商品列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $id = RequestHelper::getArray('id');
        $saleSort = RequestHelper::get('sale_sort');

        $orderBy = [];
        $andWhere = [];

        if (!empty($id)) {
            $andWhere[] = ['goods.id' => $id];
        }

        // 销量排序
        if (!empty($saleSort)) {
            $orderBy['goods.sale'] = $saleSort == 'asc' ? SORT_ASC : SORT_DESC;
        }

        $orderBy['goods.created_at'] = SORT_DESC;

        $params = [
            'select' => [
                'goods.id',
                'goods.goods_id',
                'goods.type',
                'shop_goods.title',
                'shop_goods.sub_name',
                'shop_goods.thumb',
                'shop_goods.price',
                'shop_goods.type as goods_type',
                'shop_coupon.coupon_name',
                'shop_coupon.coupon_sale_type',
                'shop_coupon.credit',
                'shop_coupon.balance',
                'shop_coupon.enough',
                'shop_coupon.discount_price',
                'goods.has_option',
                'goods.credit_shop_credit',
                'goods.credit_shop_price',
                'goods.credit_shop_stock',
                'goods.min_price',
                'goods.min_price_credit',
                'goods.sale',
                'goods.created_at',
                'shop_goods.ext_field',
            ],
            'searchs' => [
                [['shop_goods.title', 'shop_coupon.coupon_name'], 'like', 'keyword']
            ],
            'alias' => 'goods',
            'where' => [
                'goods.is_delete' => 0,
                'goods.status' => 1,
            ],
            'andWhere' => $andWhere,
            'leftJoins' => [
                [GoodsModel::tableName() . ' shop_goods', 'shop_goods.id = goods.goods_id and goods.type = 0'],
                [CouponModel::tableName() . ' shop_coupon', 'shop_coupon.id = goods.goods_id and goods.type = 1']
            ],
            'orderBy' => $orderBy,
        ];

        $list = CreditShopGoodsModel::getColl($params, [
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

                // 商品信息归类
                $row['shop_goods'] = [
                    'title' => $row['title'],
                    'sub_name' => $row['sub_name'],
                    'thumb' => $row['thumb'],
                    'price' => $row['price'],
                    'min_price' => $row['min_price'],
                    'max_price' => $row['max_price'],
                    'goods_type' => $row['goods_type'],
                    'has_option' => $row['has_option'],
                ];

                if ($row['type'] == CreditShopGoodsTypeConstant::GOODS) {
                    $row['shop_goods']['goods_unit'] = '件';
                }

                // 优惠券信息归类
                $row['shop_coupon'] = [
                    'coupon_name' => $row['coupon_name'],
                    'coupon_sale_type' => $row['coupon_sale_type'],
                    'credit' => $row['credit'],
                    'balance' => $row['balance'],
                    'content' => $row['content'],
                ];

                // 保留字段
                $row = ArrayHelper::filter($row, ['id', 'type', 'goods_id', 'shop_goods', 'shop_coupon', 'has_option', 'credit_shop_credit', 'credit_shop_price', 'credit_shop_stock', 'sale', 'created_at', 'min_price', 'min_price_credit', 'goods_type']);
                if ($row['goods_type'] == GoodsTypeConstant::GOODS_TYPE_VIRTUAL_ACCOUNT) {
                    $row['plugin_account']['virtualAccount'] = true;
                }
            }
        ]);

        return $this->result($list);
    }

    /**
     * 商品详情
     * @return array|int[]|Response
     * @throws CreditShopException
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        // 查找详情
        $service = new CreditShopGoodsService();
        $detail = $service->mobileDetail($id, $this->memberId);

        return $this->result($detail);
    }

    /**
     * 获取规格
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetOption()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        $service = new CreditShopGoodsService();
        $detail = $service->getOption($id);

        return $this->result($detail);
    }

    /**
     * 获取设置
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetSet()
    {
        $set = ShopSettings::get('credit_shop');

        // 如果维权读取系统设置
        if ($set['refund_type'] == 0) {
            // 获取系统设置
            $shopSetting = ShopSettings::get('sysset.refund');
            $set['finish_order_refund_type'] = $shopSetting['apply_type'] == 2 ? 1 : 0;
            $set['finish_order_refund_days'] = $shopSetting['apply_days'];
        }

        return $this->result(['data' => $set]);
    }

    /**
     * 获取小程序二维码
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetWxApp()
    {
        $goodsId = RequestHelper::getInt('id');
        //文件名
        $fileName = md5('credit_shop_' . $goodsId . '_' . $this->memberId) . '.jpg';
        //保存地址文件夹
        $savePatchDir = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/';
        //保存地址
        $savePatch = SHOP_STAR_PUBLIC_TMP_PATH . '/wxapp_qrcode/' . $fileName;
        //访问地址
        $accessPatch = ShopUrlHelper::build('tmp/wxapp_qrcode/' . $fileName, [], true);

        //如果不是文件  ||  生成时间大于一天
        if (!is_file($savePatch) || (filemtime($savePatch) && (time() - filemtime($savePatch)) > 86400)) {
            $result = MiniProgramACodeHelper::getUnlimited(http_build_query([
                'id' => $goodsId
            ]), [
                'page' => 'pagesCreditShop/detail',
                'directory' => $savePatchDir,
                'fileName' => $fileName
            ]);

            if (is_error($result)) {
                return $this->result($result);
            }
        }

        return $this->success(['patch' => $accessPatch]);
    }
}
