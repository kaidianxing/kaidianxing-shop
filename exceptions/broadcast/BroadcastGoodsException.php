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



namespace shopstar\exceptions\broadcast;


use shopstar\bases\exception\BaseException;

/**
 * 4400
 * Class BroadcastGoodsException
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\exceptions\broadcast
 */
class BroadcastGoodsException extends BaseException
{
    /**
     * @Message("参数错误")
     */
    const BROADCAST_MANAGE_GOODS_ADD_AUDIT_PARAMS_ERROR = 440000;

    /**
     * @Message("商品不存在或已下架")
     */
    const BROADCAST_MANAGE_GOODS_ADD_AUDIT_GOODS_NOT_EXIST_ERROR = 440001;

    /**
     * @Message("商品图片上传失败")
     */
    const BROADCAST_MANAGE_GOODS_ADD_AUDIT_GOODS_THUMB_UPLOAD_ERROR = 440002;

    /**
     * @Message("添加审核失败")
     */
    const BROADCAST_MANAGE_GOODS_ADD_AUDIT_ERROR = 440003;

    /**
     * @Message("参数错误")
     */
    const BROADCAST_MANAGE_GOODS_RESET_AUDIT_PARAMS_ERROR = 440010;

    /**
     * @Message("撤销审核失败")
     */
    const BROADCAST_MANAGE_GOODS_RESET_AUDIT_ERROR = 440011;

    /**
     * @Message("参数错误")
     */
    const BROADCAST_MANAGE_GOODS_REPEAT_AUDIT_PARAMS_ERROR = 440016;

    /**
     * @Message("重新审核失败")
     */
    const BROADCAST_MANAGE_GOODS_REPEAT_AUDIT_ERROR = 440017;

    /**
     * @Message("参数错误")
     */
    const BROADCAST_MANAGE_GOODS_DELETE_PARAMS_ERROR = 440020;

    /**
     * @Message("删除商品失败")
     */
    const BROADCAST_MANAGE_GOODS_DELETE_ERROR = 440021;
}
