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

namespace shopstar\admin\sale;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\sale\CouponLogConstant;
use shopstar\exceptions\sale\CouponException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;

/**
 * 优惠券设置
 * Class CouponSetController
 * @package shopstar\admin\sale
 */
class CouponSetController extends KdxAdminApiController
{

    /**
     * 获取设置
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetInfo()
    {
        $set = ShopSettings::get('sale.coupon.set');

        return $this->result($set);
    }

    /**
     * 更新设置
     * @return \yii\web\Response
     * @throws CouponException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate(): \yii\web\Response
    {
        $post = [
            'use_content' => RequestHelper::post('use_content', ''), // 优惠券使用说明
            'template' => RequestHelper::post('use_content', ''), // 模板
            'template_id' => RequestHelper::post('use_content', ''), // 模板消息id
        ];
        try {
            ShopSettings::set('sale.coupon.set', $post);

            // 日志
            LogModel::write(
                $this->userId,
                CouponLogConstant::COUPON_SET,
                CouponLogConstant::getText(CouponLogConstant::COUPON_SET),
                '0',
                [
                    'log_data' => $post,
                    'log_primary' => [
                        '优惠券使用说明' => $post['use_content'],
                    ],
                    'dirty_identity_code' => [
                        CouponLogConstant::COUPON_SET,
                    ]
                ]
            );
        } catch (\Throwable $exception) {
            throw new CouponException(CouponException::COUPON_SET_SAVE_FAIL);
        }
        return $this->success();
    }

}