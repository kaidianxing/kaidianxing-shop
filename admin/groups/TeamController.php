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

namespace shopstar\admin\groups;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\groups\GroupsTeamStatusConstant;
use shopstar\exceptions\groups\GroupsException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\groups\GroupsTeamModel;
use shopstar\models\member\MemberModel;
use shopstar\services\groups\GroupsCrewService;
use shopstar\services\groups\GroupsTeamService;
use yii\web\Response;

/**
 * 团订单列表控制器
 * Class TeamController
 * @package shopstar\admin\groups
 * @author likexin
 */
class TeamController extends KdxAdminApiController
{

    /**
     * 团订单列表
     * @return Response
     * @author likexin
     */
    public function actionIndex(): Response
    {
        //查询参数
        $get = RequestHelper::get();

        $select = [
            'a.title',
            'team.id',
            'team.team_no',
            'team.is_ladder',
            'team.created_at',
            'team.is_ladder',
            'team.count',
            'team.success',
            'team.success_num',
            'team.success_num',
            'm.nickname',
            'm.avatar',
        ];

        $where = [];

        //开团时间
        if (!empty($get['start_time']) && !empty($get['end_time'])) {
            $where[] = ['between', 'team.created_at', $get['start_time'], $get['end_time']];
        }

        //拼团类型
        if ($get['type'] != '') {
            $where[] = ['team.is_ladder' => (int)$get['type']];
        }

        //拼团状态
        if ($get['status'] != '' && in_array($get['status'], [
                GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_WAIT,
                GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_SUCCESS,
                GroupsTeamStatusConstant::GROUPS_TEAM_STATUS_TO_DEFEATED,
            ])) {
            $where[] = ['team.success' => (int)$get['status']];
        }

        //关键字搜索
        $searchs = [
            ['a.title', 'like', 'keyword'],
            ['team.team_no', 'like', 'team_no']
        ];

        $leftJoins = [
            [ShopMarketingModel::tableName() . ' as a', 'a.id = team.activity_id'],
            [MemberModel::tableName() . ' as m', 'm.id = team.leader_id']
        ];

        //必要参数
        $where[] = [
            'team.is_valid' => 1,
        ];

        $params = [
            'alias' => 'team',
            'select' => $select,
            'searchs' => $searchs,
            'andWhere' => $where,
            'leftJoins' => $leftJoins,
            'orderBy' => [
                'team.created_at' => SORT_DESC,
            ],
        ];

        // 获取列表
        $list = GroupsTeamModel::getColl($params);

        return $this->result($list);
    }

    /**
     * 获取团订单详情
     * @return Response
     * @throws GroupsException
     * @author likexin
     */
    public function actionGetTeamDetail(): Response
    {
        $teamId = RequestHelper::getInt('id');
        if (empty($teamId)) {
            return $this->error('团队参数错误');
        }

        //先查出来拼团的基本信息
        $groupsDetail = GroupsTeamModel::find()
            ->select([
                'activity_id',
                'team_no',
                'end_time',
                'count',
                'success',
                'success_num',
                'created_at',
                'is_fictitious',
                'is_ladder',
                'end_time',
                'success_time',
            ])
            ->where([
                'id' => $teamId,
            ])
            ->first();


        if (empty($groupsDetail)) {
            return $this->error('未找到团信息');
        }

        //查成员跟订单信息
        $teamDetail = GroupsCrewService::getTeamMemberAndOrderByTeamId($teamId);
        if (is_error($teamDetail)) {
            throw new GroupsException(GroupsException::GROUPS_MEMBER_OR_ORDER_IS_EMPTY);
        }

        foreach ($teamDetail as &$team) {
            // 处理实付款, 增加运费
            $team['price'] = sprintf('%.2f', bcadd($team['price'], $team['dispatch_price'], 2));
        }

        return $this->result([
            'groups_detail' => $groupsDetail,
            'team_detail' => $teamDetail,
        ]);
    }

    /**
     * 确认成团
     * @return Response
     * @throws \shopstar\exceptions\order\OrderException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @author likexin
     */
    public function actionEditGroupsStatus(): Response
    {
        $teamId = RequestHelper::postInt('id');
        if (empty($teamId)) {
            return $this->error('团队ID未找到');
        }

        /**
         * @var GroupsTeamModel $team
         */
        $team = GroupsTeamModel::find()->where([
            'id' => $teamId,
        ])->select([
            'id',
        ])->one();

        $team->success_time = DateTimeHelper::now();
        $team->success = 1;
        $team->save();

        // 手动确认成团
        $result = GroupsTeamService::finishAfter($teamId);

        return $this->result($result);
    }


}