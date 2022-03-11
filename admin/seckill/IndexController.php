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

namespace shopstar\admin\seckill;

use shopstar\constants\activity\ActivityConstant;

use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\RequestHelper;
use shopstar\jobs\activity\AutoStopActivityJob;
use shopstar\models\activity\ShopMarketingGoodsMapModel;
use shopstar\models\activity\ShopMarketingModel;
use shopstar\models\goods\GoodsActivityModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\order\OrderActivityModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;
use shopstar\constants\seckill\SeckillLogConstant;
use shopstar\exceptions\seckill\SeckillException;
use shopstar\bases\KdxAdminApiController;
use yii\helpers\Json;

/**
 * 秒杀
 * Class IndexController
 * @package apps\seckill\manage
 */
class IndexController extends KdxAdminApiController
{
    /**
     * 活动列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $data = RequestHelper::get();
        
        $list = ShopMarketingModel::getActivityList($data, 'seckill');
        
        return $this->result($list);
    }
    
    /**
     * 详情
     * @throws SeckillException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new SeckillException(SeckillException::SECKILL_DETAIL_PARAMS_ERROR);
        }
        $detail = ShopMarketingModel::getActivityDetail($id);
        if (empty($detail)) {
            throw new SeckillException(SeckillException::SECKILL_DETAIL_ACTIVITY_NOT_EXISTS);
        }
    
        return $this->result(['data' => $detail]);
    }
    
    /**
     * 添加活动
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        // 停止活动时间
        $closeDelay = strtotime(RequestHelper::post('end_time')) - time();
        // 商品信息
        $goodsInfo = RequestHelper::post('goods_info');
        if (empty($goodsInfo)) {
            return error('商品不能为空');
        }
        $goodsInfo = Json::decode($goodsInfo);
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $res = ShopMarketingModel::easyAdd([
                'attributes' => [
                    'type' => 'seckill',
                ],
                'filterAttributes' => [
                    'goods_info',
                ],
                'beforeSave' => function ($data) use ($goodsInfo) {
                    // 限购
                    if ($data['rules']['limit_type'] != ActivityConstant::LIMIT_TYPE_NOT_LIMIT && empty($data['rules']['limit_num'])) {
                        return error('限购次数不能为空');
                    }
                    // 预热
                    if ($data['is_preheat'] == ActivityConstant::IS_PREHEAT && empty($data['preheat_time'])) {
                        return error('预热时间不能为空');
                    }
                    $data->rules = Json::encode($data->rules);
                    
                    // 校验商品
                    $res = ShopMarketingGoodsMapModel::checkGoodsInfo($goodsInfo, $data->start_time, $data->end_time, [
                        'is_seckill' => true
                    ]);

                    if (is_error($res)) {
                        return $res;
                    }
                },
                'afterSave' => function ($data) use ($closeDelay, $goodsInfo) {

                    // 保存商品
                    ShopMarketingGoodsMapModel::saveGoodsMap($goodsInfo, $data,['type' => 'seckill']);
                
                    // 添加定时关闭任务
                    QueueHelper::push(new AutoStopActivityJob([
                        'id' => $data->id,
                    ]), $closeDelay);

                    // 日志
                    LogModel::write(
                        $this->userId,
                        SeckillLogConstant::ADD,
                        SeckillLogConstant::getText(SeckillLogConstant::ADD),
                        $data->id,
                        [
                            'log_data' => $data->attributes,
                            'log_primary' => [
                                '活动id' => $data->id,
                                '活动标题' => $data->title,
                                '是否预热' => $data->is_preheat ? '是' : '否',
                                '预热时间' => $data->is_preheat ? $data->preheat_time : '-',
                            ],
                            'dirty_identity_code' => [
                                SeckillLogConstant::EDIT,
                                SeckillLogConstant::ADD,
                            ]
                        ]
                    );
                }
            ]);
            if (is_error($res)) {
                throw new SeckillException(SeckillException::SECKILL_ADD_FAIL, $res['message']);
            }
            
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }
        
        return $this->success();
    }
    
    /**
     * 数据
     * @throws SeckillException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionStatistics()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new SeckillException(SeckillException::STATISTICS_PARAMS_ERROR);
        }
        
        $select = [
            'activity_goods.goods_id',
            'activity_goods.option_id',
            'count(if(order.pay_type<>0, order.id, null)) as order_count',
            'sum(if(order.pay_type<>0, order.pay_price, 0)) as pay_price',
            'COALESCE(sum(order.refund_price), 0) as refund_price',
            'sum(if(order.pay_type<>0, order_goods.total , 0)) as total',
            'count(distinct(if(order.pay_type<>0, order.member_id, null))) member_count',
        ];
        $params = [
            'select' => $select,
            'alias' => 'activity_goods',
            'leftJoins' => [
                [OrderActivityModel::tableName(). 'order_activity', 'order_activity.activity_id=activity_goods.activity_id and activity_type=\'seckill\''],
                [OrderGoodsModel::tableName().' order_goods', 'order_goods.order_id=order_activity.order_id and order_goods.goods_id=activity_goods.goods_id and order_goods.option_id=activity_goods.option_id'],
                [OrderModel::tableName().' order', 'order.id=order_goods.order_id']
            ],
            'where' => [
                'and',
                ['activity_goods.activity_id' => $id],
                ['activity_goods.is_join' => 1]
            ],
            'groupBy' => [
                'activity_goods.goods_id', 'activity_goods.option_id'
            ]
        ];
        
        $list = ShopMarketingGoodsMapModel::getColl($params);
        
        // 商品
        $goodsIds = array_unique(array_column($list['list'], 'goods_id'));
        // 商品列表
        $goodsList = GoodsModel::find()->with('options')->where(['id' => $goodsIds])->indexBy('id')->get();

        foreach ($list['list'] as &$item) {
            $item['title'] = $goodsList[$item['goods_id']]['title'];
            $item['thumb'] = $goodsList[$item['goods_id']]['thumb'];
            $item['type'] = $goodsList[$item['goods_id']]['type'];
            if (!empty($item['option_id'])) {
                // 遍历规格
                foreach ($goodsList[$item['goods_id']]['options'] as $option) {
                    if ($option['id'] == $item['option_id']) {
                        $item['option_title'] = $option['title'];
                        $item['price'] = $option['price'];
                    }
                }
            } else {
                $item['price'] = $goodsList[$item['goods_id']]['price'];
            }

        }
        unset($item);
        
        return $this->result($list);
    }
    
    /**
     * 编辑
     * @throws SeckillException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::post('id');
        $isPreheat = RequestHelper::post('is_preheat');
        $preheatTime = RequestHelper::post('preheat_time', DateTimeHelper::DEFAULT_DATE_TIME);
        // 停止活动时间
        $endTime = RequestHelper::post('end_time');
        $closeDelay = strtotime($endTime) - time();
        if (empty($id)) {
            throw new SeckillException(SeckillException::SECKILL_EDIT_PARAMS_ERROR);
        }
        $activity = ShopMarketingModel::find()->where(['id' => $id, 'type' => 'seckill'])->one();
        if (empty($activity)) {
            throw new SeckillException(SeckillException::SECKILL_EDIT_ACTIVITY_NOT_EXISTS);
        }
        // 结束时间不能为空 || 结束时间不能小于起始时间
        if(empty($endTime) || (strtotime($endTime) < strtotime($activity->start_time))){
            throw new SeckillException(SeckillException::SECKILL_DELETE_PARAMS_ERROR);
        }

        // 新结束时间不能小于旧结束时间
        if(strtotime($endTime) < strtotime($activity->end_time)){
            throw new SeckillException(SeckillException::NEW_EDIT_EMD_TIME_NOT_LESS_THAN_OLD_END_TIME_ERROR);
        }

        $activity->is_preheat = $isPreheat;
        if ($isPreheat) {
            $activity->preheat_time = $preheatTime;
        }
        $activity->end_time = $endTime;
        
        if (!$activity->save()) {
            throw new SeckillException(SeckillException::SECKILL_EDIT_FAIL);
        }
        GoodsActivityModel::changePreheat($id, 'seckill', $isPreheat, $preheatTime);
        GoodsActivityModel::changeEndTime($id, 'seckill', $endTime);

        // 添加定时关闭任务
        QueueHelper::push(new AutoStopActivityJob([
            'id' => $id,
        ]), $closeDelay);
        // 日志
        LogModel::write(
            $this->userId,
            SeckillLogConstant::EDIT,
            SeckillLogConstant::getText(SeckillLogConstant::EDIT),
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
                    SeckillLogConstant::EDIT,
                    SeckillLogConstant::ADD,
                ]
            ]
        );
        
        return $this->success();
    }
    
    /**
     * 手动停止
     * @throws SeckillException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManualStop()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new SeckillException(SeckillException::SECKILL_MANUAL_STOP_PARAMS_ERROR);
        }
        
        $res = ShopMarketingModel::manualStop($id, 'seckill');
        if (is_error($res)) {
            throw new SeckillException(SeckillException::SECKILL_MANUAL_STOP_FAIL, $res['message']);
        }
        // 活动商品停止
        GoodsActivityModel::changeEndTime($id, 'seckill', DateTimeHelper::now());
        // 日志
        LogModel::write(
            $this->userId,
            SeckillLogConstant::STOP,
            SeckillLogConstant::getText(SeckillLogConstant::STOP),
            $res['id'],
            [
                'log_data' => $res,
                'log_primary' => [
                    '活动id' => $res['id'],
                    '活动标题' => $res['title'],
                    '停止时间' => DateTimeHelper::now(),
                ],
                'dirty_identity_code' => [
                    SeckillLogConstant::STOP,
                ]
            ]
        );
        
        return $this->success();
    }
    
    /**
     * 删除活动
     * @throws SeckillException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new SeckillException(SeckillException::SECKILL_DELETE_PARAMS_ERROR);
        }
        $res = ShopMarketingModel::deleteActivity($id, 'seckill');
        if (is_error($res)) {
            throw new SeckillException(SeckillException::SECKILL_DELETE_FAIL, $res['message']);
        }
        // 删除活动商品表
        GoodsActivityModel::deleteActivity($id, 'seckill');
        // 日志
        LogModel::write(
            $this->userId,
            SeckillLogConstant::DELETE,
            SeckillLogConstant::getText(SeckillLogConstant::DELETE),
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
                    SeckillLogConstant::DELETE,
                ]
            ]
        );
        
        return $this->success();
    }
    
    /**
     * 获取设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetSetting()
    {
        $setting = ShopSettings::get('activity.seckill');
        return $this->result(['data' => $setting]);
    }
    
    /**
     * 修改设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetSetting()
    {
        $setting = [
            'close_type' => RequestHelper::post('close_type', 0),
            'close_time' => RequestHelper::post('close_time', 15),
        ];
        ShopSettings::set('activity.seckill', $setting);
    
        LogModel::write(
            $this->userId,
            SeckillLogConstant::CHANGE_SETTING,
            SeckillLogConstant::getText(SeckillLogConstant::CHANGE_SETTING),
            0,
            [
                'log_data' => $setting,
                'log_primary' => [
                    '未付款订单' => $setting['close_type'] == 0 ? '永不关闭' : '自定义关闭时间',
                    '关闭时间' => $setting['close_type'] == 0 ? '-' :'拍下未付款订单'.$setting['close_time'].'分钟内未付款，自动关闭订单'
                ],
                'dirty_identity_code' => [
                    SeckillLogConstant::CHANGE_SETTING,
                ]
            ]
        );
        return $this->result(['data' => $setting]);
    }


}