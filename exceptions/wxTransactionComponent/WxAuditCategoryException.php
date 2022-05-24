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

namespace shopstar\exceptions\wxTransactionComponent;

use shopstar\bases\exception\BaseException;

class WxAuditCategoryException extends BaseException
{
    /**
     * @Message("类目不存在或类目id错误")
     */
    public const CATEGORY_NOT_EXIST_ERROR = 470000;

    /**
     * @Message("营业执照上传数量错误")
     */
    public const CATEGORY_LICENSE_NUMBER_ERROR = 470001;

    /**
     * @Message("该分类已存在")
     */
    public const CATEGORY_ALREADY_EXISTS_ERROR = 470002;

    /**
     * @Message("营业执照上传数量错误")
     */
    public const CATEGORY_ALREADY_SUCCESS_NOT_EDIT_ERROR = 470003;

    /**
     * @Message("添加失败")
     */
    public const CATEGORY_ADD_ERROR = 470004;

    /**
     * @Message("已审核成功的类目禁止重复添加")
     */
    public const CATEGORY_ALREADY_SUCCESS_NOT_ADD_ERROR = 470005;

    /**
     * @Message("参数错误")
     */
    public const CATEGORY_PARAMS_ERROR = 470006;

    /**
     * @Message("已审核成功的类目或审核中的类目禁止删除")
     */
    public const CATEGORY_ALREADY_SUCCESS_NOT_DELETE_ERROR = 470007;

    /**
     * @Message("同步失败")
     */
    public const CATEGORY_SYNCHRONIZE_STATUS_ERROR = 470008;
}
