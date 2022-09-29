<?php

namespace shopstar\jobs\creditSign;

use shopstar\constants\creditSign\CreditSignActivityConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\creditSign\CreditSignModel;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * 积分签到开始活动队列
 * Class AutoCreditSignStartActivityJob
 * @package shopstar\jobs\creditSign
 * @author yuning
 */
class AutoCreditSignStartActivityJob extends BaseObject implements JobInterface
{

    /**
     * @var int 活动id
     */
    public int $id;

    /**
     * 活动自动开始
     * @param Queue $queue
     * @return void
     * @author yuning
     */
    public function execute($queue)
    {
        $activity = CreditSignModel::findOne([
            'id' => $this->id,
        ]);

        // 判断活动是否存在
        if (empty($activity)) {
            echo "积分签到活动自动开始失败,id:" . $this->id . ",原因:活动未找到\n";
            die;
        }

        // 判断活动时间(防止活动未开始编辑开始时间)
        if ($activity->start_time != DateTimeHelper::now(false)) {
            echo "积分签到活动开始时间未到或已开始\n";
            die;
        }

        // 判断活动状态
        if ($activity->status != CreditSignActivityConstant::STATUS_WAIT) {
            echo "积分签到活动自动开始失败,id:" . $this->id . ",原因:活动已开始或已结束\n";
            die;
        }

        $activity->status = CreditSignActivityConstant::STATUS_UNDERWAY;

        // 执行自动开始
        if (!$activity->save()) {
            echo "活动自动开始失败,id:" . $this->id . ",原因:" . $activity->getErrorMessage() . "\n";
            die;
        }

        echo "积分签到活动开始成功\n";
        die;
    }

}