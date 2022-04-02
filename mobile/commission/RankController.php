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

namespace shopstar\mobile\commission;

use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionSettings;
use shopstar\models\member\MemberModel;

/**
 * 佣金排名
 * Class RankController
 * @package shopstar\mobile\commission
 * @author 青岛开店星信息技术有限公司
 */
class RankController extends CommissionClientApiController
{

    /**
     * 获取数据
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGet()
    {
        $rankSettings = CommissionSettings::get('rank');
        // 未开启返回 0
        if ($rankSettings['open'] == 0) {
            return $this->success(['open' => 0]);
        }
        // 分销金额字段
        if ((int)$rankSettings['commission_type'] == 0) {
            $commissionField = 'agent.commission_total';
            $andWhere[] = ['<>', 'agent.commission_total', 0];
        } else {
            $commissionField = 'agent.commission_pay';
            $andWhere[] = ['<>', 'agent.commission_pay', 0];
        }


        // 显示数量
        $limit = max(10, (int)$rankSettings['show_total']);

        $params = [
            'alias' => 'agent',
            'where' => [],
            'andWhere' => $andWhere,
            'leftJoin' => [MemberModel::tableName() . ' as member', 'member.id = agent.member_id'],
            'select' => [
                'member.id as member_id',
                'member.nickname',
                'member.avatar',
                $commissionField . ' as commission_total',
            ],
            'orderBy' => [
                $commissionField => SORT_DESC,
                'agent.become_time' => SORT_ASC,
            ],
            'limit' => $limit,
        ];

        $result = CommissionAgentModel::getColl($params, [
            'pager' => false,
        ]);

        // 我的累计提现佣金
        $result['my'] = [
            'avatar' => (string)$this->member['avatar'],
            'commission_pay' => (float)$this->agent['commission_pay'],
            'commission_total' => (float)$this->agent['commission_total'],
            'show_total' => $limit,
            'top' => -1
        ];

        if (!empty($result['list'])) {
            foreach ($result['list'] as $index => &$row) {
                $row['top'] = $index + 1;
                if ($row['member_id'] == $this->memberId) {
                    $result['my']['top'] = $row['top'];
                }
            }
        }

        return $this->result($result);
    }

}