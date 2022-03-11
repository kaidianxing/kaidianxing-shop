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


namespace shopstar\exceptions\goods;

use shopstar\bases\exception\BaseException;

class GoodsException extends BaseException
{
    /******************************分类100******************************/

    /**
     * @Message("参数错误")
     */
    const CATEGORY_GET_ONE_PARAMS_ERROR = 200101;

    /**
     * @Message("分类不存在")
     */
    const CATEGORY_GET_ONE_NOT_FOUND_ERROR = 200102;

    /**
     * @Message("分类不存在")
     */
    const CATEGORY_SAVE_NOT_FOUND_ERROR = 200103;

    /**
     * @Message("保存失败");
     */
    const CATEGORY_SAVE_ERROR = 200104;

    /**
     * @Message("参数错误");
     */
    const CATEGORY_DELETE_PARAMS_ERROR = 200105;

    /**
     * @Message("参数错误");
     */
    const CATEGORY_SET_SETTING_PARAMS_ERROR = 200106;


    /**
     * @Message("参数错误");
     */
    const CATEGORY_SWITCH_PARAMS_ERROR = 200107;

    /**
     * @Message("参数错误");
     */
    const CATEGORY_SWITCH_CATEGORY_NOT_FOUND_ERROR = 200108;

    /******************************分组200******************************/

    /**
     * @Message("参数错误");
     */
    const GROUP_GET_ONE_PARAMS_ERROR = 200201;

    /**
     * @Message("分组不存在");
     */
    const GROUP_GET_ONE_NOT_FOUND_ERROR = 200202;

    /**
     * @Message("分组不存在")
     */
    const GROUP_SAVE_NOT_FOUND_ERROR = 200203;

    /**
     * @Message("分组保存失败")
     */
    const GROUP_SAVE_ERROR = 200204;

    /**
     * @Message("参数错误")
     */
    const GROUP_DELETE_PARAMS_ERROR = 200205;

    /**
     * @Message("分组不存在")
     */
    const GROUP_DELETE_NOT_FOUND_ERROR = 200206;

    /**
     * @Message("参数错误")
     */
    const GROUP_SWITCH_NOT_FOUND_ERROR = 200207;

    /**
     * @Message("分组未找到")
     */
    const GROUP_SWITCH_GROUP_NOT_FOUND_ERROR = 200208;
    

    /******************************商品400******************************/
    /**
     * @Message("参数错误")
     */
    const GOODS_SAVE_GOODS_EMPTY_PARAMS_ERROR = 200401;

    /**
     * @Message("缺少商品标题")
     */
    const GOODS_SAVE_TITLE_EMPTY_PARAMS_ERROR = 200402;

    /**
     * @Message("缺少商品主图")
     */
    const GOODS_SAVE_THUMB_EMPTY_PARAMS_ERROR = 200403;

    /**
     * @Message("缺少商品价格")
     */
    const GOODS_SAVE_PRICE_EMPTY_PARAMS_ERROR = 200404;

    /**
     * @Message("缺少规格")
     */
    const GOODS_SAVE_LACK_SPEC_ERROR = 200405;

    /**
     * @Message("缺少规格项")
     */
    const GOODS_SAVE_LACK_SPEC_ITEM_ERROR = 200406;

    /**
     * @Message("缺少规格商品")
     */
    const GOODS_SAVE_LACK_OPTIONS_ERROR = 200407;

    /**
     * @Message("缺少规格商品标题")
     */
    const GOODS_SAVE_EMPTY_OPTIONS_TITLE_ERROR = 200408;

    /**
     * @Message("缺少规格商品价格")
     */
    const GOODS_SAVE_EMPTY_OPTIONS_PRICE_ERROR = 200409;

    /**
     * @Message("商品不存在")
     */
    const GOODS_SAVE_GOODS_NOT_FOUND_ERROR = 200410;

    /**
     * @Message("商品保存失败")
     */
    const GOODS_SAVE_GOODS_SAVE_ERROR = 200411;

    /**
     * @Message("规格保存失败")
     */
    const GOODS_SAVE_GOODS_OPTIONS_SAVE_ERROR = 200412;

    /**
     * @Message("规格保存失败")
     */
    const GOODS_SAVE_GOODS_SPEC_SAVE_ERROR = 200413;

    /**
     * @Message("规格项保存失败")
     */
    const GOODS_SAVE_GOODS_SPEC_ITEM_SAVE_ERROR = 200414;

    /**
     * @Message("商品未找到")
     */
    const GOODS_DELETE_NOT_FOUND_ERROR = 200415;

    /**
     * @Message("商品未找到")
     */
    const GOODS_FOREVER_DELETE_DELETE_NOT_FOUND_ERROR = 200416;

    /**
     * @Message("删除失败")
     */
    const GOODS_FOREVER_DELETE_DELETE_ERROR = 200417;

    /**
     * @Message("字段不合法")
     */
    const GOODS_PROPERTY_NOT_ALLOW_FIELD_ERROR = 200418;

    /**
     * @Message("修改失败")
     */
    const GOODS_PROPERTY_SAVE_ERROR = 200419;

    /**
     * @Message("商品不存在")
     */
    const GOODS_GET_NOT_FOUND_ERROR = 200420;

    /**
     * @Message("商品会员等级折扣错误")
     */
    const MEMBER_LEVEL_DISCOUNT_NUM_ERROR = 200422;

    /**
     * @Message("商品会员等级折扣类型不存在多规格")
     */
    const MEMBER_LEVEL_DISCOUNT_TYPE_NOT_EXISTS_OPTION = 200424;

    /**
     * @Message("商品会员等级折扣类保存失败")
     */
    const MEMBER_LEVEL_DISCOUNT_SAVE_FAIL = 200425;

    /**
     * @Message("请先设置会员等级")
     */
    const MEMBER_LEVEL_EMPTY = 200426;

    /**
     * @Message("会员等级折扣参数错误")
     */
    const MEMBER_LEVEL_DISCOUNT_PARAMS_ERROR = 200427;

    /**
     * @Message("商品分销佣金设置保存失败")
     */
    const GOODS_COMMISSION_SAVE_FAIL = 200431;
    
    /**
     * @Message("会员等级折扣不能为空")
     */
    const MEMBER_LEVEL_DISCOUNT_NOT_NULL = 200432;

    /**
     * @Message("配送方式不能为空")
     */
    const DISPATCH_MODE_NOT_NULL = 200433;

    /**
     * @Message("同城配送仅支持实体商品")
     */
    const DISPATCH_INTRACITY_ONLY_ENTITY_GOODS = 200434;

    /**
     * @Message("快递发货未开启")
     */
    const DISPATCH_EXPRESS_NOT_ENABLE = 200435;

    /**
     * @Message("同城配送未开启")
     */
    const DISPATCH_INTRACITY_NOT_ENABLE = 200436;

    /**
     * @Message("配送方式必须开启一种")
     */
    const DISPATCH_ALL_NOT_ENABLE = 200437;
    /**
     * @Message("缺少购买按钮参数")
     */
    const GOODS_SAVE_BUY_BUTTON_EMPTY_PARAMS_EMPTY = 200438;
    /**
     * @Message("缺少购买按钮设置参数")
     */
    const GOODS_SAVE_BUY_BUTTON_SETTINGS_EMPTY_PARAMS_EMPTY = 200439;

    /**
     * @Message("购买按钮设置参数错误")
     */
    const GOODS_SAVE_BUY_BUTTON_SETTINGS_PARAMS_ERROR = 200440;

    /**
     * @Message("缺少购买按钮弹窗参数")
     */
    const GOODS_SAVE_BUY_BUTTON_SETTINGS_POP_PARAMS_EMPTY = 200441;

    /**
     * @Message("缺少购买按钮跳转链接参数")
     */
    const GOODS_SAVE_BUY_BUTTON_SETTINGS_JUMP_URL_PARAMS_EMPTY = 200442;

    /**
     * @Message("请输入电话号码")
     */
    const GOODS_SAVE_BUY_BUTTON_SETTINGS_TELEPHONE_PARAMS_EMPTY = 200443;

    /**
     * @Message("购买按钮电话格式错误")
     */
    const GOODS_SAVE_BUY_BUTTON_SETTINGS_TELEPHONE_PARAMS_ERROR = 200444;

    /**
     * @Message("当前商品类型不支持开启购买按钮自定义功能")
     */
    const GOODS_SAVE_BUY_BUTTON_OPEN_GOODS_TYPE_ERROR = 200445;

    /**
     * @Message("商城联系电话未设置，请先去设置或使用自定义电话")
     */
    const GOODS_SAVE_BUY_BUTTON_GET_SHOP_TELEPHONE_ERROR = 200445;

    /**
     * @Message("购买按钮点击电话号码类型错误")
     */
    const GOODS_SAVE_BUY_BUTTON_CLICK_TELEPHONE_TYPE_ERROR = 200446;
    
    /**
     * @Message("配送方式错误")
     */
    const DISPATCH_MODE_VERIFY_NOT_PERM = 200447;
    
    
    
    /******************************操作200******************************/

    /**
     * @Message("商品不存在")
     */
    const GOODS_OPERATION_GET_PRICE_AND_STOCK_GOODS_NOT_FOUND_ERROR = 200500;

    /**
     * @Message("参数错误")
     */
    const GOODS_OPERATION_GET_PRICE_AND_STOCK_GOODS_PARAMS_ERROR = 200501;

    /**
     * @Message("参数错误")
     */
    const GOODS_OPERATION_SET_PRICE_AND_STOCK_GOODS_PARAMS_ERROR = 200502;

    /**
     * @Message("保存失败")
     */
    const GOODS_OPERATION_SET_PRICE_AND_STOCK_GOODS_SAVE_ERROR = 200503;

    /**
     * @Message("商品不存在")
     */
    const GOODS_OPERATION_SET_PRICE_AND_STOCK_GOODS_NOT_FOUND_ERROR = 200504;

    /**
     * @Message("参数错误")
     */
    const GOODS_OPERATION_SET_CATEGORY_PARAMS_ERROR = 200405;

    /**
     * @Message("参数错误")
     */
    const GOODS_OPERATION_DELETE_PARAMS_ERROR = 200406;

    /******************************标签600******************************/
    /**
     * @Message("参数错误")
     */
    const LABEL_GROUP_GET_ONE_PARAMS_ERROR = 200600;
    /**
     * @Message("未找到标签分组")
     */
    const LABEL_GROUP_GET_ONE_NOT_FOUND_ERROR = 200601;
    /**
     * @Message("未找到标签分组")
     */
    const LABEL_GROUP_SAVE_NOT_FOUND_ERROR = 200602;
    /**
     * @Message("保存失败")
     */
    const LABEL_GROUP_SAVE_ERROR = 200603;
    /**
     * @Message("参数错误")
     */
    const LABEL_GROUP_DELETE_PARAMS_ERROR = 200604;
    /**
     * @Message("分组未找到")
     */
    const LABEL_GROUP_DELETE_NOT_FOUND_ERROR = 200605;


    /**
     * @Message("参数错误")
     */
    const LABEL_GROUP_SWITCH_PARAMS_ERROR = 200606;

    /**
     * @Message("分组未找到")
     */
    const LABEL_GROUP_SWITCH_GROUP_NOT_FOUND_ERROR = 200607;

    /**
     * @Message("保存失败")
     */
    const LABEL_GROUP_SWITCH_GROUP_SAVE_ERROR = 200608;

    /**
     * @Message("默认标签组禁止删除")
     */
    const LABEL_GROUP_DEFAULT_GROUP_BAN_DELETE = 200609;


    /******************************手机端商品700******************************/
    /**
     * @Message("参数错误")
     */
    const CLIENT_DETAIL_GET_DETAIL_PARAMS_ERROR = 200700;

    /**
     * @Message("参数错误")
     */
    const CLIENT_DETAIL_GET_OPTION_PARAMS_ERROR = 200701;

    /**
     * @Message("商品已删除或已下架")
     */
    const CLIENT_DETAIL_GET_DETAIL_GOODS_NOT_FOUND_ERROR = 200702;

    /**
     * @Message("缺少参数")
     */
    const CLIENT_DETAIL_CHANGE_FAVORITE_PARAMS_ERROR = 200703;

    /**
     * @Message("没有浏览权限")
     */
    const CLIENT_DETAIL_GET_DETAIL_PERM_ERROR = 200704;
    
    /**
     * @Message("商品列表为空")
     */
    const CLIENT_GOODS_LIST_IS_EMPTY = 200705;
    
    /**
     * @Message("参数错误")
     */
    const CLIENT_GOODS_LIST_ACTIVITY_PARAMS_ERROR = 200706;

    /**
     * @Message("活动不存在")
     */
    const CLIENT_GOODS_LIST_ACTIVITY_NOT_EXISTS = 200707;

    /**
     * @Message("商品规格不存在")
     */
    const CLIENT_DETAIL_GET_DETAIL_OPTION_EMPTY_ERROR = 200708;

    /**
     * @Message("价格面议商品无法进行购买操作")
     */
    const CLIENT_BUY_BUTTON_GOODS_BUY_ERROR = 200709;

    /**
     * @Message("缺少价格面议相关参数")
     */
    const CLIENT_BUY_BUTTON_GOODS_BUY_PARAMS_EMPTY = 200710;


    /******************************手机端购物车800******************************/

    /**
     * @Message("参数错误")
     */
    const CLIENT_GOODS_CART_CHANGE_TOTAL_PARAMS_ERROR = 200800;

    /**
     * @Message("商品不存在")
     */
    const CLIENT_GOODS_CART_CHANGE_TOTAL_GOODS_NOT_FOUND_ERROR = 200801;

    /**
     * @Message("规格不存在")
     */
    const CLIENT_GOODS_CART_CHANGE_TOTAL_OPTION_NOT_FOUND_ERROR = 200802;

    /**
     * @Message("商品数量不能大于99")
     */
    const CLIENT_GOODS_CART_CHANGE_TOTAL_FULL_ERROR = 200803;

    /**
     * @Message("商品数量不能大于99")
     */
    const CLIENT_GOODS_CART_CHANGE_TOTAL_CANNOT_SUBTRACT_ERROR = 200803;

    /**
     * @Message("购物车商品未找到")
     */
    const CLIENT_GOODS_CART_DELETE_NOT_OPT_FOR_ERROR = 200804;

    /**
     * @Message("超出最大选中个数")
     */
    const CLIENT_GOODS_CART_SELECT_MAXIMUM_BUY_ERROR = 200805;

    /**
     * @Message("购物车是空的")
     */
    const CLIENT_GOODS_CART_SELECT_CART_EMPTY_ERROR = 200806;

    /**
     * @Message("选中商品未找到")
     */
    const CLIENT_GOODS_CART_SELECT_GOODS_NOT_IN_CART_ERROR = 200807;

    /**
     * @Message("不能再减了")
     */
    const CLIENT_GOODS_CART_CHANGE_TOTAL_NUMBER_LITTLE_ERROR = 200808;

    /**
     * @Message("购物车删除商品ID不能为空")
     */
    const CLIENT_GOODS_CART_DELETE_ID_NOT_EMPTY = 200809;
    
    /**
     * @Message("商品信息错误")
     */
    const CLIENT_GOODS_CART_CLEAN_ID_EMPTY = 200810;

    /**
     * @Message("请选择商品")
     */
    const CLIENT_GOODS_CART_GOODS_EMPTY = 200811;

    /**
     * @Message("参数错误")
     */
    const CLIENT_GOODS_CART_GOODS_PARAMS_ERROR = 200812;

    /**
     * @Message("配送方式错误")
     */
    const CLIENT_GOODS_CART_GOODS_DISPATCH_TYPE_ERROR = 200813;


    /**
     * @Message("应用商品为空")
     */
    const CLIENT_ACTIVITY_GOODS_LIST_IS_EMPTY = 200814;

    /**
     * @Message("需要先调用initParams(),进行初始化")
     */
    const SERVICE_GET_MANAGER_LIST_MUST_INIT_PARAM = 200815;

    /**
     * @Message("需要先调用initParams(),进行初始化")
     */
    const SERVICE_GET_MOBILE_LIST_MUST_INIT_PARAM = 200816;

    /**
     * @Message("Label保存失败")
     */
    const LABEL_SAVE_ERROR = 200816;



}
