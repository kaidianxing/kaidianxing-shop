<?php

namespace shopstar\jobs\creditSign;

use shopstar\services\creditSign\CreditSignSendNoticeService;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class AutoCreditSignSendNoticeJob extends BaseObject implements JobInterface
{

    /**
     * @var int 会员ID
     */
    public int $memberId;

    /**
     * @var int 活动ID
     */
    public int $activityId;

    /**
     * @param Queue $queue
     * @return void
     * @author yuning
     */
    public function execute($queue)
    {
        try {
            // 执行发送
            $result = CreditSignSendNoticeService::sendNotice($this->memberId, $this->activityId);

            // 发送失败
            if (is_error($result)) {
                echo $result['message'] . "\n";
                die;
            }
        } catch (\Throwable $exception) {
            echo $exception->getMessage() . "\n";
            die;
        }

        echo "发送完毕\n";
        die;
    }
}