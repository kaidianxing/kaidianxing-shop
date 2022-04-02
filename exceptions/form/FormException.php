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

namespace shopstar\exceptions\form;

use shopstar\bases\exception\BaseException;

/**
 * 海报列表异常
 * Class FormException
 * @package shopstar\exceptions\form
 * @author 青岛开店星信息技术有限公司
 */
class FormException extends BaseException
{
    /************** 管理端 **************/

    /**
     * @Message("表单名字不能为空")
     */
    const FORM_LIST_NAME_NOT_EMPTY = 420100;

    /**
     * @Message("表单内容不能为空")
     */
    const FORM_LIST_CONTENT_NOT_EMPTY = 420101;

    /**
     * @Message("表单类型不合法")
     */
    const FORM_LIST_TYPE_INVALID = 420102;

    /**
     * @Message("表单添加失败")
     */
    const FORM_LIST_ADD_INVALID = 420103;

    /**
     * @Message("表单ID不能为空")
     */
    const FORM_LIST_ID_NOT_EMPTY = 420104;

    /**
     * @Message("表单保存失败")
     */
    const FORM_LIST_SAVE_INVALID = 420105;

    /**
     * @Message("编辑表单ID不能为空")
     */
    const FORM_LIST_EDIT_ID_NOT_EMPTY = 420106;

    /**
     * @Message("禁用表单ID不能为空")
     */
    const FORM_LIST_FORBIDDEN_ID_NOT_EMPTY = 420107;

    /**
     * @Message("启用表单ID不能为空")
     */
    const FORM_LIST_ACTIVE_ID_NOT_EMPTY = 420108;

    /**
     * @Message("表单禁用失败")
     */
    const FORM_LIST_FORBIDDEN_INVALID = 420109;

    /**
     * @Message("表单启用失败")
     */
    const FORM_LIST_ACTIVE_INVALID = 420110;

    /**
     * @Message("表单删除失败")
     */
    const FORM_LIST_DELETED_INVALID = 420111;

    /************** 移动端 **************/

    /**
     * @Message("表单提交ID不合法")
     */
    const FORM_PAGE_SUBMIT_ID_NOT_EMPTY = 421001;

    /**
     * @Message("表单提交内容不合法")
     */
    const FORM_PAGE_SUBMIT_CONTENT_NOT_EMPTY = 421002;

    /**
     * @Message("表单提交失败")
     */
    const FORM_PAGE_SUBMIT_INVALID = 421003;

    /**
     * @Message("表单记录不存在")
     */
    const FORM_PAGE_RECORD_NOT_EXISTS = 421004;

    /**
     * @Message("表单提交订单ID不合法")
     */
    const FORM_PAGE_SUBMIT_ORDER_ID_NOT_EMPTY = 421005;

    /**
     * @Message("表单提交商品ID不合法")
     */
    const FORM_PAGE_SUBMIT_GOODS_ID_NOT_EMPTY = 421006;

    /**
     * @Message("表单数据提交失败")
     */
    const FORM_PAGE_SUBMIT_FORM_DATA_NOT_EMPTY = 421007;

    /**
     * @Message("表单名称重复")
     */
    const FORM_PAGE_SUBMIT_FORM_DATA_NAME_EXIT = 421008;

    /**
     * @Message("临时表转入正式表失败")
     */
    const FORM_TEMP_TO_LOG_EMPTY = 421009;

    /**
     * @Message("数据格式不正确")
     */
    const FORM_TEMP_DETAIL_DATA_EMPTY = 421010;

}