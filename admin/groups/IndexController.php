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

use shopstar\exceptions\groups\GroupsException;
use shopstar\bases\KdxAdminApiController;
use shopstar\constants\activity\ActivityConstant;
use shopstar\constants\groups\GroupsLogConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\jobs\activity\AutoStopActivityJob;
use shopstar\models\activity\ShopMarketingGoodsMapModel;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\goods\GoodsActivityModel;
use shopstar\models\groups\GroupsGoodsModel;
use shopstar\models\log\LogModel;
use shopstar\services\groups\GroupsTeamService;
use yii\helpers\Json;
use yii\web\Response;

/**
 * 拼团活动管理接口类
 * Class IndexController
 * @package shopstar\admin\groups
 * @author likexin
 */
class IndexController extends KdxAdminApiController
{

    /**
     * 活动列表
     * @author likexin
     */
    public function actionList()
    {
        $data = RequestHelper::get();

        $list = ShopMarketingModel::getActivityList($data, 'groups');

        return $this->result($list);
    }

    /**
     * 详情
     * @throws GroupsException
     * @author 李可鑫
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new GroupsException(GroupsException::GROUPS_DETAIL_PARAMS_ERROR);
        }
        $copy = RequestHelper::getInt('copy');

        $detail = ShopMarketingModel::getActivityDetail($id);
        if (empty($detail)) {
            throw new GroupsException(GroupsException::GROUPS_DETAIL_ACTIVITY_NOT_EXISTS);
        }

        // 复制时, 删除一些数据
        if ($copy) {
            unset($detail['is_deleted'], $detail['create_time'], $detail['stop_time'], $detail['update_time'], $detail['status'], $detail['start_time'], $detail['end_time'], $detail['preheat_time']);
        }
        //获取拼团价格信息
        $groupsPriceInfo = GroupsGoodsModel::getGroupsGoodsPriceInfoByActivityId($id);

        foreach ($detail['goods_info'] as &$item) {
            if ($item['has_option']) {
                foreach ($item['rules'] as &$rulesItem) {
                    $rulesItem['ladder_price'] = $groupsPriceInfo[$item['id'] . '_' . $rulesItem['id']]['ladder_price'] ?? [];
                    $rulesItem['activity_price'] = $groupsPriceInfo[$item['id'] . '_' . $rulesItem['id']]['price'] ?? 0;
                    $rulesItem['leader_price'] = $groupsPriceInfo[$item['id'] . '_' . $rulesItem['id']]['leader_price'] ?? 0;
                }

                continue;
            }
            $item['ladder_price'] = $groupsPriceInfo[$item['id'] . '_0']['ladder_price'] ?? [];
            $item['activity_price'] = $groupsPriceInfo[$item['id'] . '_0']['price'] ?? 0;
            $item['leader_price'] = $groupsPriceInfo[$item['id'] . '_0']['leader_price'] ?? 0;
        }


        return $this->result([
            'data' => $detail,
        ]);
    }

    /***
     * 活动的商品统计数据
     * @return Response
     * @author likexin
     */
    public function actionStatistics(): Response
    {
        // 获取活动ID
        $activityId = RequestHelper::getInt('id');
        if (empty($activityId)) {
            return $this->error('活动ID不正确');
        }

        $list = ShopMarketingGoodsMapModel::statisticsGoods('groups', [$activityId]);

        return $this->result($list);
    }

    /**
     * 添加活动
     * @return Response
     * @author likexin
     */
    public function actionAdd(): Response
    {
        // 停止活动时间
        $closeDelay = strtotime(RequestHelper::post('end_time')) - time();

        // 商品信息
        $goodsInfo = RequestHelper::post('goods_info');
        if (empty($goodsInfo)) {
            return $this->error('商品不能为空');
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();

        try {
            $res = ShopMarketingModel::easyAdd([
                'attributes' => [
                    'type' => 'groups',
                ],
                'filterAttributes' => [
                    'goods_info',
                ],
                'beforeSave' => function ($data) use ($goodsInfo) {

                    //赋值规则
                    $rules = $data['rules'];

                    //解析规则
                    if (!is_array($rules)) $rules = Json::decode($rules);

                    //取key的交际
                    $rules = array_intersect_key($rules, [
                        'limit_time' => 0,  //限时
                        'success_num' => 0, //拼团成团人数 普通拼团整形 阶梯团 数组 阶梯 => 人数
                        'virtual_success' => 0, //是否开启虚拟拼团
                        'virtual_success_num' => 0, //虚拟几人
                        'is_commission' => 0, //是否参与分销
                        'use_coupon' => 1, //是否使用优惠券
                        'limit_type' => 0, //限购类型
                        'limit_num' => 0, //限购数量
                        'single_buy' => 0, //是否单购
                    ]);

                    // 限购
                    if ($rules['limit_type'] != ActivityConstant::LIMIT_TYPE_NOT_LIMIT && empty($rules['limit_num'])) {
                        return error('限购次数不能为空');
                    }

                    // 预热
                    if ($data['is_preheat'] == ActivityConstant::IS_PREHEAT && empty($data['preheat_time'])) {
                        return error('预热时间不能为空');
                    }

                    $data->rules = Json::encode($rules);

                    // 校验商品
                    $res = ShopMarketingGoodsMapModel::checkGoodsInfo($goodsInfo, $data->start_time, $data->end_time, [
                        'is_groups' => true
                    ]);
                    if (is_error($res)) {
                        return $res;
                    }
                },
                'afterSave' => function ($data) use ($closeDelay, $goodsInfo) {

                    // 保存商品
                    ShopMarketingGoodsMapModel::saveGoodsMap($goodsInfo, $data, [
                        'type' => 'groups',
                    ]);

                    // 入库
                    GroupsGoodsModel::saveData($data['id'], $data->inner_type, $goodsInfo);

                    // 添加定时关闭任务
                    QueueHelper::push(new AutoStopActivityJob([
                        'id' => $data->id,
                    ]), $closeDelay);

                    // 日志
                    LogModel::write(
                        $this->userId,
                        GroupsLogConstant::ADD,
                        GroupsLogConstant::getText(GroupsLogConstant::ADD),
                        $data->id,
                        [
                            'log_data' => $data->attributes,
                            'log_primary' => [
                                '活动id' => $data->id,
                                '活动标题' => $data->title,
                                '是否预热' => $data->is_preheat ? '是' : '否',
                                '预热时间' => $data->preheat_time ? $data->preheat_time : '-',
                            ],
                            'dirty_identity_code' => [
                                GroupsLogConstant::EDIT,
                                GroupsLogConstant::ADD,
                            ],
                        ]
                    );
                }
            ]);

            if (is_error($res)) {
                throw new GroupsException(GroupsException::GROUPS_ADD_FAIL, $res['message']);
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }

        return $this->success();
    }

    /**
     * 编辑
     * @return Response
     * @throws GroupsException
     * @author likexin
     */
    public function actionEdit(): Response
    {
        $id = RequestHelper::post('id');
        $isPreheat = RequestHelper::post('is_preheat');
        $preheatTime = RequestHelper::post('preheat_time');

        // 停止活动时间
        $endTime = RequestHelper::post('end_time');
        $closeDelay = strtotime($endTime) - time();
        if (empty($id)) {
            throw new GroupsException(GroupsException::GROUPS_EDIT_PARAMS_ERROR);
        }

        /**
         * @var ShopMarketingModel $activity
         */
        $activity = ShopMarketingModel::find()->where([
            'id' => $id,
            'type' => 'groups',
        ])->one();
        if (empty($activity)) {
            throw new GroupsException(GroupsException::GROUPS_EDIT_ACTIVITY_NOT_EXIST_ERROR);
        }

        // 结束时间不能为空 || 结束时间不能小于起始时间
        if (empty($endTime) || (strtotime($endTime) < strtotime($activity->start_time))) {
            throw new GroupsException(GroupsException::GROUPS_MANUAL_STOP_PARAMS_ERROR);
        }

        // 新结束时间不能小于旧结束时间
        if (strtotime($endTime) < strtotime($activity->end_time)) {
            throw new GroupsException(GroupsException::NEW_EDIT_EMD_TIME_NOT_LESS_THAN_OLD_END_TIME_ERROR);
        }

        $activity->is_preheat = $isPreheat;
        if ($isPreheat) {
            $activity->preheat_time = $preheatTime;
        }
        $activity->end_time = $endTime;
        if (!$activity->save()) {
            throw new GroupsException(GroupsException::GROUPS_EDIT_FAIL);
        }

        GoodsActivityModel::changePreheat($id, 'groups', $isPreheat, $preheatTime);

        // 添加定时关闭任务
        QueueHelper::push(new AutoStopActivityJob([
            'id' => $id,
        ]), $closeDelay);

        // 日志
        LogModel::write(
            $this->userId,
            GroupsLogConstant::EDIT,
            GroupsLogConstant::getText(GroupsLogConstant::EDIT),
            $id,
            [
                'log_data' => $activity->attributes,
                'log_primary' => [
                    '活动id' => $activity['id'],
                    '活动标题' => $activity['title'],
                    '是否预热' => $isPreheat ? '是' : '否',
                    '预热时间' => $isPreheat ? $preheatTime : '-',
                ],
                'dirty_identity_code' => [
                    GroupsLogConstant::EDIT,
                    GroupsLogConstant::ADD,
                ],
            ]
        );

        return $this->success();
    }

    /**
     * 手动停止
     * @throws GroupsException
     * @author 青椒
     */
    public function actionManualStop()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new GroupsException(GroupsException::GROUPS_MANUAL_STOP_PARAMS_ERROR);
        }

        // 调用手动停止
        $res = ShopMarketingModel::manualStop($id, 'groups');
        if (is_error($res)) {
            throw new GroupsException(GroupsException::GROUPS_MANUAL_STOP_FAIL, $res['message']);
        }

        // 活动商品停止
        GoodsActivityModel::changeEndTime($id, 'groups', DateTimeHelper::now());

        // 活动删除 关闭所有未成团的订单
        GroupsTeamService::failureActivityPushQueue($id);

        // 日志
        LogModel::write(
            $this->userId,
            GroupsLogConstant::STOP,
            GroupsLogConstant::getText(GroupsLogConstant::STOP),
            $res['id'],
            [
                'log_data' => $res,
                'log_primary' => [
                    '活动id' => $res['id'],
                    '活动标题' => $res['title'],
                    '停止时间' => DateTimeHelper::now(),
                ],
                'dirty_identity_code' => [
                    GroupsLogConstant::STOP,
                ],
            ]
        );

        return $this->success();
    }

    /**
     * 删除活动
     * @return Response
     * @throws GroupsException
     * @author likexin
     */
    public function actionDelete(): Response
    {
        $id = RequestHelper::postInt('id');
        if (empty($id)) {
            throw new GroupsException(GroupsException::GROUPS_DELETE_PARAMS_ERROR);
        }

        // 调用删除活动
        $res = ShopMarketingModel::deleteActivity($id, 'groups');
        if (is_error($res)) {
            throw new GroupsException(GroupsException::GROUPS_DELETE_ERROR, $res['message']);
        }

        // 删除活动商品表
        GoodsActivityModel::deleteActivity($id, 'groups');

        //活动删除 关闭所有未成团的订单
        GroupsTeamService::failureActivityPushQueue($id);

        // 日志
        LogModel::write(
            $this->userId,
            GroupsLogConstant::DELETE,
            GroupsLogConstant::getText(GroupsLogConstant::DELETE),
            $res['id'],
            [
                'log_data' => $res,
                'log_primary' => [
                    '活动id' => $res['id'],
                    '活动标题' => $res['title'],
                    '开始时间' => $res['start_time'],
                    '结束时间' => $res['end_time'],
                    '删除时间' => DateTimeHelper::now()
                ],
                'dirty_identity_code' => [
                    GroupsLogConstant::DELETE,
                ],
            ]
        );

        return $this->success();
    }

}