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

namespace shopstar\bases\controller;

use shopstar\bases\exception\BaseApiException;
use shopstar\exceptions\ChannelException;
use shopstar\exceptions\creditShop\CreditShopException;
use shopstar\exceptions\member\MemberException;
use shopstar\models\shop\ShopSettings;
use yii\db\Exception;
use yii\web\BadRequestHttpException;

/**
 * 积分商城移动端基类
 * Class BaseCreditShopMobileApiController.
 * @package shopstar\bases\controller
 */
class BaseCreditShopMobileApiController extends BaseMobileApiController
{
    /**
     * @param $action
     * @return bool
     * @throws CreditShopException
     * @throws BaseApiException
     * @throws ChannelException
     * @throws MemberException
     * @throws Exception
     * @throws BadRequestHttpException
     * @author 青岛开店星信息技术有限公司
     */
    public function beforeAction($action): bool
    {
        parent::beforeAction($action);

        // 判断积分商城状态
        $set = ShopSettings::get('credit_shop');
        if ($set['status'] == 0) {
            throw new CreditShopException(CreditShopException::CREDIT_SHOP_STATUS_ERROR);
        }

        return true;
    }
}
