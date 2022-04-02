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

namespace shopstar\jobs\newGifts;

use shopstar\helpers\DateTimeHelper;
use shopstar\models\newGifts\NewGiftsActivityModel;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * @author 青岛开店星信息技术有限公司
 */
class AutoStopActivityJob extends BaseObject implements JobInterface
{
    /**
     * @var
     */
    public $id;

    /**
     * @param Queue $queue
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function execute($queue)
    {
        $detail = NewGiftsActivityModel::findOne(['id' => $this->id]);
        if (empty($detail)) {
            echo '新人送礼活动自动停止失败,id:' . $this->id . ",原因:活动未找到\n";
            die;
        }
        $detail->stop_time = DateTimeHelper::now();
        $detail->status = -1;
        if (!$detail->save()) {
            echo '新人送礼活动自动停止失败,id:' . $this->id . '原因:' . $detail->getErrorMessage() . "\n";
        }

        echo '自动停止新人送礼活动成功,id:' . $this->id . "\n";
    }
}