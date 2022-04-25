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

namespace shopstar\config\modules;

use shopstar\constants\diypage\DiypageMenuTypeConstant;
use shopstar\constants\diypage\DiypageTypeConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\diypage\DiypageMenuModel;
use shopstar\models\diypage\DiypageModel;
use shopstar\models\diypage\DiypageTemplateModel;
use shopstar\models\goods\label\GoodsLabelGroupModel;
use shopstar\models\goods\label\GoodsLabelModel;
use shopstar\models\member\MemberLevelModel;

/**
 * 店铺初始化
 * Class KdxInitialize
 * @package config\modules
 */
class KdxInitialize
{

    /**
     * 初始化入口
     * @return void
     * @author likexin
     */
    public static function init(): void
    {
        // 插入会员默认等级
        self::intiMemberDefaultLevel();

        // 初始化分销商默认等级
        self::initCommissionDefaultLevel();

        // 初始化系统默认标签组
        self::initDefaultLabelGroup();

        // 初始化装修默认页面
        self::initDiyPage();

        // 初始化自定义菜单
        self::initDiyMenu();
    }

    /**
     * 初始化会员默认等级
     * @return void
     * @author likexin
     */
    private static function intiMemberDefaultLevel(): void
    {
        $now = DateTimeHelper::now();

        // 判断是否已经有默认等级
        $count = MemberLevelModel::find()->where([
            'is_default' => 1
        ])->count();

        if ($count > 0) {
            return;
        }

        $model = new MemberLevelModel();
        $model->setAttributes([
            'level' => 0,
            'level_name' => '默认等级',
            'state' => 1,
            'create_time' => $now,
            'update_time' => $now,
            'is_default' => 1,
        ]);
        $model->save();
    }

    /**
     * 初始化分销数据
     * @author likexin
     */
    private static function initCommissionDefaultLevel(): void
    {
        // 检查是否存在
        $isExists = CommissionLevelModel::find()
            ->where([
                'is_default' => 1,
            ])
            ->exists();
        if ($isExists) {
            return;
        }

        // 插入
        $level = new CommissionLevelModel();
        $level->setAttributes([
            'is_default' => 1,
            'name' => '默认等级',
            'level' => 0,
            'status' => 1,
        ]);
        $level->save();
    }

    /**
     * 初始化系统默认标签组
     * @return void
     * @author xukaixuan
     */
    private static function initDefaultLabelGroup(): void
    {
        // 判断是否已经添加过
        $exists = GoodsLabelGroupModel::find()
            ->where([
                'is_default' => '1',
                'name' => '默认标签组',
            ])
            ->count('id');
        if ($exists > 0) {
            return;
        }
        $model = new GoodsLabelGroupModel();
        $model->setAttributes(array_merge([
            'create_time' => DateTimeHelper::now(),
        ], GoodsLabelGroupModel::defaultLabelGroup()));
        $model->save();

        $groupId = $model->attributes['id'];

        // 自动生成系统内置三个标签
        if ($groupId) {
            try {
                foreach (GoodsLabelGroupModel::RECOMMEND as $key => $value) {
                    $data = [
                        'group_id' => $groupId,
                        'create_time' => DateTimeHelper::now(),
                        'name' => $value['name'],
                        'desc' => $value['desc'],
                    ];
                    GoodsLabelModel::saveLabel($data);
                }
            } catch (GoodsException $exception) {
            }
        }
    }

    /**
     * 初始化装修默认页面
     * @return void
     */
    private static function initDiyPage(): void
    {
        // 默认首页
        $count = DiypageModel::find()->where([
            'type' => DiypageTypeConstant::TYPE_HOME,
        ])->count();
        if (empty($count)) {
            self::savePageFromTemplate(1);
        }

        // 默认商品详情
        $count = DiypageModel::find()->where([
            'type' => DiypageTypeConstant::TYPE_GOODS_DETAIL,
        ])->count();
        if (empty($count)) {
            self::savePageFromTemplate(2);
        }

        // 默认会员中心
        $count = DiypageModel::find()->where([
            'type' => DiypageTypeConstant::TYPE_MEMBER,
        ])->count();
        if (empty($count)) {
            self::savePageFromTemplate(3);
        }
    }

    /**
     * 通过系统模板id创建
     * @param int $systemId
     * @author likexin
     */
    private static function savePageFromTemplate(int $systemId)
    {
        /**
         * @var DiypageTemplateModel $template
         */
        $template = DiypageTemplateModel::find()->where([
            'system_id' => $systemId,
        ])->one();
        if (empty($template)) {
            return;
        }

        $now = DateTimeHelper::now();

        $page = new DiypageModel();
        $page->setAttributes([
            'type' => $template->type,
            'name' => $template->name,
            'status' => 1,
            'thumb' => $template->thumb,
            'common' => $template->common,
            'content' => $template->content,
            'create_time' => $now,
            'update_time' => $now,
            'template_id' => $template->id,
        ]);
        $page->save();
    }

    /**
     * 初始化菜单
     * @author likexin
     */
    private static function initDiyMenu()
    {
        // 查询是否存在
        $count = DiypageMenuModel::find()->where([
            'type' => DiypageMenuTypeConstant::TYPE_SHOP,
            'is_default' => 1,
        ])->count();
        if (!empty($count)) {
            return;
        }

        $now = DateTimeHelper::now();

        $menu = new DiypageMenuModel();
        $menu->setAttributes([
            'type' => DiypageMenuTypeConstant::TYPE_SHOP,
            'name' => '默认菜单',
            'thumb' => '/static/images/diypage/menu_1/thumb_20eefa3a14a7a2a569cba71eb16eefef.jpg',
            'content' => '{"id":"diymenu","type":"diymenu","isbottom":3,"app_type":"edit","cart_bgcolor":"#ff3c29","cart_number":"1","cart_num":0,"icon_position":"top","icon_type":"0","style":{"bgcolor":"#ffffff","bgcoloron":"#ffffff","iconcolor":"#565656","iconcoloron":"#ff3c29","textcolor":"#565656","textcoloron":"#212121","childtextcolor":"#212121","childbgcolor":"#ffffff","childactivebgcolor":"#e6e7eb","bordercolor":"#ffffff","bordercoloron":"#ffffff","childbordercolor":"#eeeeee"},"items":[{"icon_url":"iconfont-m- icon-m-dibudaohang-home","icon_url_0":"iconfont-m- icon-m-dibudaohang-home","icon_url_1":"","icon_cache":"","target_url":"/pages/index/index","target_url_name":"商城首页","iconclass":"iconfont-m- icon-m-iconfontshop","text":"首页","active":false,"showsubmenu":false,"badge":"","child":[],"_sortId":"1587704700088_0.6977493779760124"},{"icon_url":"iconfont-m- icon-m-dibudaohang-fenlei","icon_url_0":"iconfont-m- icon-m-dibudaohang-fenlei","icon_url_1":"","icon_cache":"","target_url":"/kdxGoods/categoryList/index","target_url_name":"分类导航","iconclass":"iconfont-m- icon-m-iconfontshop","text":"分类","active":false,"showsubmenu":false,"badge":"","child":[],"_sortId":"1587704700088_0.6682435169802112"},{"icon_url":"iconfont-m- icon-m-dibudaohang-gouwuche","icon_url_0":"iconfont-m- icon-m-dibudaohang-gouwuche","icon_url_1":"","icon_cache":"","target_url":"/kdxCart/index","target_url_name":"购物车","iconclass":"iconfont-m- icon-m-iconfontshop","text":"购物车","active":false,"showsubmenu":false,"badge":4,"child":[],"_sortId":"1587704700088_0.3497304022500296"},{"icon_url":"iconfont-m- icon-m-dibudaohang-me","icon_url_0":"iconfont-m- icon-m-dibudaohang-me","icon_url_1":"","target_url":"/kdxMember/index/index","target_url_name":"会员中心","iconclass":"iconfont-m- icon-m-iconfontshop","text":"我的","active":false,"showsubmenu":false,"badge":"","child":[],"_sortId":"1587704700088_0.7749125012000802"}],"_comIndex_":"diymenu_0_1589883882804","icon":"ivu-icon ivu-icon-md-arrow-dropup","groupName":"全局组件","groupType":"global","yIndex":5,"color":"#2D8CF0"}',
            'status' => 1,
            'is_default' => 1,
            'create_time' => $now,
            'update_time' => $now,
        ]);
        $menu->save();
    }

}
