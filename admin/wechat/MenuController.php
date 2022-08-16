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

namespace shopstar\admin\wechat;

use shopstar\bases\KdxAdminApiController;
use shopstar\components\wechat\helpers\OfficialAccountMenuHelper;
use shopstar\exceptions\wechat\WechatException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\wechat\WechatMenuModel;
use Throwable;
use yii\db\StaleObjectException;
use yii\helpers\Json;
use yii\web\Response;

/**
 * 自定义菜单控制器
 * Class MenuController.
 * @package shopstar\admin\wechat
 */
class MenuController extends KdxAdminApiController
{
    /**
     * 获取自定义菜单列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $list = WechatMenuModel::getColl([
            'where' => [],
            'orderBy' => [
                'created_at' => SORT_DESC
            ],
            'select' => [
                'id',
                'name',
                'status',
                'created_at',
            ],
        ]);

        return $this->result($list);
    }

    /**
     * 添加自定义菜单
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $result = WechatMenuModel::easyAdd([
            'attributes' => [
                'created_at' => DateTimeHelper::now()
            ],
            'beforeSave' => function (&$result) {
                $result['menu_json'] = Json::encode($result['menu_json']);
            },
        ]);

        return $this->result($result);
    }

    /**
     * 编辑自定义菜单
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $result = WechatMenuModel::easyEdit([
            'attributes' => [],
            'beforeSave' => function (&$result) {
                $result['menu_json'] = Json::encode($result['menu_json']);
            },
            'onLoad' => function (&$result) {
                $result['data']['menu_json'] = Json::decode($result['data']['menu_json']);
            },
        ]);

        return $this->result($result);
    }

    /**
     * 启用自定义菜单
     * @return array|int[]|Response
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEnable()
    {
        $status = RequestHelper::postInt('status', 1);
        $id = RequestHelper::postInt('menu_id');

        if (empty($id)) {
            throw new WechatException(WechatException::WECHAT_MENU_ENABLE_PARAMS_ERROR);
        }

        $menu = WechatMenuModel::find()->where([
            'id' => $id
        ])->first();

        if (empty($menu)) {
            throw new WechatException(WechatException::WECHAT_MENU_ENABLE_NOT_FOUND_ERROR);
        }

        $menuJson = Json::decode($menu['menu_json']);

        foreach ($menuJson['button'] as &$item) {

            /** @change likexin 判断不包含http与https时才拼接 */
            if (($item['type'] == 'view' || $item['type'] == 'miniprogram') && strstr($item['url'], 'pages') && !StringHelper::exists($item['url'], ['http://', 'https://'], StringHelper::SEL_OR)) {
                $item['url'] = ShopUrlHelper::wap($item['url'], [], true);
            }

            //如果有子级
            if (!empty($item['sub_button'])) {
                foreach ($item['sub_button'] as &$itemItem) {
                    /** @change likexin 判断不包含http与https时才拼接 */
                    if (($itemItem['type'] == 'view' || $itemItem['type'] == 'miniprogram') && strstr($itemItem['url'], 'pages') && !StringHelper::exists($itemItem['url'], ['http://', 'https://'], StringHelper::SEL_OR)) {
                        $itemItem['url'] = ShopUrlHelper::wap($itemItem['url'], [], true);
                    }
                }
            }
        }

        $result = $status == 0 ? OfficialAccountMenuHelper::delete() : OfficialAccountMenuHelper::create($menuJson['button']);
        if (!is_error($result)) {
            //全部改为未启用
            WechatMenuModel::updateAll(['status' => 0]);

            if ($status) {
                //单独改成启用
                WechatMenuModel::updateAll(['status' => 1], ['id' => $id]);
            }
        }

        return $this->result($result);
    }

    /**
     * 删除自定义菜单
     * @return array|int[]|Response
     * @throws Throwable
     * @throws StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $result = WechatMenuModel::easyDelete([
            'afterDelete' => function () {
                OfficialAccountMenuHelper::delete();
            },
        ]);

        return $this->result($result);
    }
}
