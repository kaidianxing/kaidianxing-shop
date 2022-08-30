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

namespace shopstar\jobs\groups;

use shopstar\services\groups\GroupsTeamService;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * 自动关闭团定时任务
 * Class AutoCloseTeamJob
 * @package shopstar\jobs\groups
 * @author likexin
 */
class AutoCloseTeamJob extends BaseObject implements JobInterface
{

    /**
     * 数据
     * @var array
     * @author likexin
     */
    public $data;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     * @throws \shopstar\exceptions\order\OrderException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        echo "<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<拼团关闭:{$this->data['team_id']}>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";
        $result = GroupsTeamService::autoCloseTeam($this->data['team_id'], (bool)$this->data['delete_activity']);
        if (is_error($result)) {
            echo "<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<拼团关闭失败:{$this->data['team_id']}----{$result['message']}>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";
            exit;
        }

        echo "<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<拼团执行完毕>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";
        return true;
    }
}
