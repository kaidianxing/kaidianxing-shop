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

namespace shopstar\jobs\virtualAccount;

use shopstar\services\goods\GoodsService;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * 自动关闭订单
 * Class AutoCloseOrderJob
 * @author 青岛开店星信息技术有限公司
 */
class AutoDeleteGoodsStock extends BaseObject implements JobInterface
{
    public $virtualAccountId;

    /**
     * 订单自动关闭
     * @inheritDoc
     */
    public function execute($queue)
    {
        echo "处理商品库存开始,virtual_account_id:'{$this->virtualAccountId} ";

        GoodsService::getGoodsByVirtualAccountId($this->virtualAccountId);

        echo "处理商品库存完成,virtual_account_id:'{$this->virtualAccountId}'\n";
        return;
    }
}
