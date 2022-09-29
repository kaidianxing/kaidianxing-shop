<?php

namespace shopstar\jobs\creditSign;

use shopstar\constants\creditSign\CreditSignActivityConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\creditSign\CreditSignModel;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * 积分签到自动关闭队列
 * Class AutoCreditSignStopActivityJob
 * @package shopstar\jobs\creditSign
 * @author yuning
 */
class AutoCreditSignStopActivityJob extends BaseObject implements JobInterface
{

    /**
     * @var int 活动id
     */
    public int $id;

    /**
     * 自动停止活动
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
            echo "积分签到活动自动停止失败,id: " . $this->id . ",原因:活动未找到\n";
            die;
        }

        // 判断活动是否停止
        if (in_array($activity->status, [CreditSignActivityConstant::STATUS_MANUAL_END, CreditSignActivityConstant::STATUS_MANUAL_STOP])) {
            echo "积分签到活动已经停止,id: " . $this->id . " \n";
            die;
        }

        $activity->stop_time = DateTimeHelper::now();
        $activity->status = CreditSignActivityConstant::STATUS_MANUAL_END;

        // 执行停止任务
        if (!$activity->save()) {
            echo "积分签到活动自动停止失败,id:" . $this->id . ",原因:" . $activity->getErrorMessage() . "\n";
            die;
        }

        echo "积分签到活动开始自动停止成功\n";
    }
}