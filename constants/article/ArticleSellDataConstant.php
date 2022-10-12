<?php

namespace shopstar\constants\article;

use shopstar\bases\constant\BaseConstant;

/**
 * 引流销售常量
 * Class ArticleSellDataConstant
 * @package shopstar\constants\article
 * @author yuning
 */
class ArticleSellDataConstant extends BaseConstant
{
    /**
     * @Text("商品")
     */
    const TYPE_GOODS = 1;

    /**
     * @Text("优惠券")
     */
    const TYPE_COUPON = 2;
}