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
use shopstar\constants\pc\GoodsGroupConstant;
use shopstar\constants\pc\MenusConstant;
use shopstar\exceptions\pc\PcException;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\GoodsCartModel;
use shopstar\models\goods\group\GoodsGroupMapModel;
use shopstar\models\member\MemberSession;
use shopstar\models\pc\PcGoodsGroupModel;
use shopstar\models\pc\PcMenusModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;
use yii\web\Response;

class BasicController extends BaseMobileApiController
{
    public $configActions = [
        // 允许不登录访问的Actions
        'allowActions' => [
            '*',
        ]
    ];

    /**
     * 初始数据
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionInfo()
    {
        $menuLists = PcMenusModel::getColl([
            'where' => [
                'status' => 1,
            ],
            'select' => [
                'name', 'url', 'img', 'type',
            ],
            'orderBy' => [
                'sort_order' => SORT_DESC
            ],
        ]);

        $topMenus = []; // 顶部菜单
        $bottomMenus = []; // 底部菜单

        if (is_array($menuLists['list']) && !empty($menuLists['list'])) {
            foreach ($menuLists['list'] as $menuList) {
                if ($menuList['type'] == MenusConstant::PC_MENU_TYPE_TOP) {
                    $topMenus[] = $menuList;
                }
                if ($menuList['type'] == MenusConstant::PC_MENU_TYPE_BOTTOM) {
                    $bottomMenus[] = $menuList;
                }
            }
        }

        $copyRight = ShopSettings::get('pc.sysset.copyright');
        $serviceInfo = ShopSettings::get('pc.sysset.service');

        // 用户是否登录
        $member = [];
        if (!empty($this->sessionId)) {
            $member = MemberSession::get($this->sessionId, 'member');
        }
        $member_id = $member['id'] ?? 0;

        // 购物车个数
        $cartCount = 0;
        if ($member_id) {
            $cartList = GoodsCartModel::find()->asArray()->select(['total'])->where(['member_id' => $member_id])->all();
            if (!empty($cartList) && is_array($cartList)) {
                foreach ($cartList as $cartOne) {
                    $cartCount += (int)$cartOne['total'];
                }
            }
        }

        // 首页logo
        $marketInfo = ShopSettings::get('pc.sysset.market');
        //$site_logo = $basic['site_logo'] ?? '';

        $wxpc = ShopSettings::get('channel_setting.wxpc');
        $logo = $wxpc['logo'] ?? '';
        $qrcode_login_status = $wxpc['qrcode_login_status'] ?? '';

        return $this->success(['data' => [
            'topMenus' => $topMenus,
            'bottomMenus' => $bottomMenus,
            'logo' => $logo,
            'qrcode_login_status' => $qrcode_login_status,
            'copyRight' => $copyRight,
            'serviceInfo' => $serviceInfo,
            'member_id' => $member_id,
            'cart_count' => $cartCount,
            'markget_info' => $marketInfo,
        ]]);
    }

    /**
     * 获取商品组，商品id
     * @return array|int[]|Response
     * @throws PcException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetGoodsGroupGoodsIds()
    {
        $goodsGroupId = RequestHelper::get('goods_group_id');
        $pageNum = RequestHelper::get('pageNum', 1);
        $pageSize = RequestHelper::get('pageSize', 20);

        if (!$goodsGroupId) {
            throw new PcException(PcException::GOODS_GROUP_ID_EMPTY);
        }

        $goodsGroupM = PcGoodsGroupModel::find()->asArray()->where(['id' => $goodsGroupId])->one();

        if (empty($goodsGroupM)) {
            throw new PcException(PcException::GOODS_GROUP_ID_ERROR);
        }

        $goods_info = Json::decode($goodsGroupM['goods_info']);
        $goods_type = $goodsGroupM['goods_type'];
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
            default :
                break;
        }

        unset($goodsGroupM['goods_info']);

        $count = 0;
        $pageCount = 0;

        if (!empty($goods_ids)) {
            $count = count($goods_ids);
            if ($pageSize > 0) {
                $pageCount = ceil($count / $pageSize);
            }
            $goods_ids = array_slice($goods_ids, $pageSize * ($pageNum - 1), $pageSize);
            // $goods_ids = implode(',', $goods_ids);
        }

        $goodsGroupM['goods_ids'] = $goods_ids;

        return $this->success([
            'data' => [
                'goodsGroup' => $goodsGroupM,
                'count' => $count,
                'pageCount' => $pageCount,
            ]
        ]);
    }
}
