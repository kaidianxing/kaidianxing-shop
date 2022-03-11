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

namespace shopstar\mobile\form;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\helpers\RequestHelper;
use shopstar\constants\form\FormLogConstant;
use shopstar\constants\form\FormTypeConstant;
use shopstar\exceptions\form\FormException;
use shopstar\models\form\FormModel;
use shopstar\models\form\FormTempModel;


/**
 * 临时表单
 * Class SubmitTempController
 * @package apps\form\client
 */
class SubmitTempController extends BaseMobileApiController
{

    /**
     * 提交到临时表单
     * @return array|int[]|\yii\web\Response
     * @throws FormException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSubmit()
    {

        $params = RequestHelper::post();

        if (empty($params['form_id'])) {
            throw new FormException(FormException::FORM_PAGE_SUBMIT_ID_NOT_EMPTY);
        }

        if (empty($params['content'])) {
            throw new FormException(FormException::FORM_PAGE_SUBMIT_CONTENT_NOT_EMPTY);
        }

        $formExists = FormModel::find()->where([
            'id'      => $params['form_id'],
            'is_deleted' => 0,
            'status'  => 1
        ])->first();

        //查询是否有这个表单并启用
        if (empty($formExists)) {
            throw new FormException(FormException::FORM_PAGE_RECORD_NOT_EXISTS);
        }

        // 商品类表单校验商品ID
        if (empty($params['goods_id'])) {
            throw new FormException(FormException::FORM_PAGE_SUBMIT_GOODS_ID_NOT_EMPTY);
        }

        //参数校验+赋值
        if (!isset($params['order_id']) || empty($params['order_id'])) {
            $params['order_id'] = 0;
        }
        if (!isset($params['goods_id']) || empty($params['goods_id'])) {
            $params['goods_id'] = 0;
        }

        if (!isset($params['cart_id']) || empty($params['cart_id'])) {
            $params['cart_id'] = 0;
        }

        $params['md5'] = md5($params['old_content']);

        // 商品表单, 填充source数据
        if($formExists['type'] == 4) {
            $params['source'] = FormLogConstant::FORM_SOURCE_ORDER;
        }

        //执行添加
        $result = FormTempModel::submitTempData($this->memberId,$params);


        if (is_error($result)) {
            throw new FormException(FormException::FORM_PAGE_SUBMIT_INVALID, $result['message']);
        }
        return $this->result();
    }

}