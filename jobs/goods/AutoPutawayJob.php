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

namespace shopstar\jobs\goods;

use shopstar\constants\goods\GoodsStatusConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\goods\GoodsModel;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @author 青岛开店星信息技术有限公司
 */
class AutoPutawayJob extends BaseObject implements JobInterface
{
    public $goodsId;

    /**
     * 商品自动上架
     * @inheritDoc
     */
    public function execute($queue)
    {
        $goods = GoodsModel::findOne([
            'id' => $this->goodsId
        ]);

        if (empty($goods)) {
            echo "自动上架失败,id:" . $this->goodsId . ' 失败原因:商品未找到';
        }

        $extField = $goods->getExtField();
        if ($extField['auto_putaway'] == 1 && $extField['putaway_time'] <= DateTimeHelper::now()) {
            $goods->status = GoodsStatusConstant::GOODS_STATUS_PUTAWAY;
            $extField['auto_putaway'] = 0;
            $goods->ext_field = $extField;
            $result = $goods->save();
            if (!$result) {
                echo '自动上架失败,id:' . $goods->id . ' 失败原因：' . $goods->getErrorMessage();
            }
        }

        echo '自动上架完成,id:' . $goods->id;
    }
}
