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

namespace shopstar\admin\broadcast;

use shopstar\bases\KdxAdminApiController;
use shopstar\components\wechat\helpers\MiniProgramBroadcastRoomHelper;
use shopstar\constants\broadcast\BroadcastLogConstant;
use shopstar\constants\broadcast\BroadcastRoomIsDeletedConstant;
use shopstar\constants\broadcast\BroadcastRoomStatusConstant;
use shopstar\constants\order\OrderSceneConstant;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\exceptions\broadcast\BroadcastRoomException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\broadcast\BroadcastGoodsModel;
use shopstar\models\broadcast\BroadcastRoomGoodsMapModel;
use shopstar\models\broadcast\BroadcastRoomModel;
use shopstar\models\broadcast\BroadcastStatisticsModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\order\OrderModel;
use shopstar\models\wxapp\WxappUploadLogModel;
use yii\db\Expression;

/**
 * 直播间
 * Class RoomController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\broadcast
 */
class RoomController extends KdxAdminApiController
{

    /**
     * 直播间列表 装修调用
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRoomAndGoodsList()
    {
        $roomId = RequestHelper::getArray('room_id');

        $where = [
            'status' => [
                BroadcastRoomStatusConstant::BROADCAST_ROOM_STATUS_UNDERWAY,
                BroadcastRoomStatusConstant::BROADCAST_ROOM_STATUS_NOTSTARTED,
                BroadcastRoomStatusConstant::BROADCAST_ROOM_STATUS_END,
                BroadcastRoomStatusConstant::BROADCAST_ROOM_STATUS_SUSPEND,
            ]
        ];

        if (!empty($roomId)) {
            $where['id'] = $roomId;
        }

        $list = BroadcastRoomModel::getColl([
            'where' => $where,
            'andWhere' => [
                ['is_deleted' => BroadcastRoomIsDeletedConstant::BROADCAST_ROOM_IS_DELETED_NO]
            ],
            'searchs' => [
                [['title', 'anchor_name'], 'like', 'keywords'],
            ],
            'orderBy' => ['status' => SORT_ASC, 'start_time' => SORT_DESC],
            'indexBy' => 'id',
            'select' => [
                'id',
                'broadcast_room_id',
                'title',
                'anchor_name',
                'start_time',
                'end_time',
                'status',
                'cover_img',
                'share_img',
                'broadcast_type',
                'is_deleted',
                'is_hide',
            ]
        ]);

        if (!empty($list['list'])) {
            $roomId = array_keys($list['list']);
            $goodsCount = BroadcastRoomGoodsMapModel::find()->where([
                'room_id' => $roomId,
            ])->groupBy('room_id')->select(['room_id', 'count(*) as total'])->indexBy('room_id')->asArray()->all();

            foreach ($list['list'] as &$item) {
                $item['goods_count'] = $goodsCount[$item['id']]['total'] ?: 0;
            }

            $goods = GoodsModel::find()->alias('goods')
                ->leftJoin(BroadcastRoomGoodsMapModel::tableName() . ' goods_map', 'goods_map.goods_id = goods.id')
                ->where(['goods_map.room_id' => $roomId])
                ->select([
                    'goods_map.room_id',
                    'goods.id',
                    'goods.title',
                    'goods.thumb',
                    'goods.price',
                ])
                ->groupBy([
                    'goods_map.room_id',
                    'goods_map.goods_id',
                ])
                ->having('count(`goods_map`.`room_id`) < 2')
                ->orderBy([
                    'goods.created_at' => SORT_DESC
                ])
                ->asArray()
                ->all();


            if (!empty($goods)) {
                foreach ($goods as $goodsIndex => $goodsItem) {
                    if (!empty($goodsItem['room_id'])) {
                        $list['list'][$goodsItem['room_id']]['goods_list'][] = $goodsItem;
                    }
                }
            }

        }

        $list['list'] = array_values($list['list']);

        return $this->result($list);
    }


    /**
     * 获取直播间列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $list = BroadcastRoomModel::getColl([
            'where' => [
                'is_deleted' => BroadcastRoomIsDeletedConstant::BROADCAST_ROOM_IS_DELETED_NO
            ],
            'orderBy' => ['status' => SORT_ASC, 'start_time' => SORT_DESC],
            'searchs' => [
                [['title', 'anchor_name'], 'like', 'keywords'],
                ['start_time', 'between'],
                ['end_time', 'between'],
                ['status', 'int'],
            ],
            'select' => [
                'id',
                'broadcast_room_id',
                'title',
                'broadcast_type', //直播类型
                'cover_img',
                'share_img',
                'anchor_name',
                'start_time',
                'end_time',
                'status',
                'is_hide',
            ],
            'indexBy' => 'id'
        ]);

        if (!empty($list['list'])) {
            $roomId = array_keys($list['list']);
            $goodsCount = BroadcastRoomGoodsMapModel::find()->where([
                'room_id' => $roomId,
            ])->groupBy('room_id')->select(['room_id', 'count(*) as total'])->indexBy('room_id')->asArray()->all();

            foreach ($list['list'] as &$item) {
                $item['goods_count'] = $goodsCount[$item['id']]['total'] ?: 0;
            }
        }

        $list['list'] = array_values($list['list']);

        return $this->result($list);
    }

    /**
     * 获取二维码
     * @return array|int[]|\yii\web\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetQrcode()
    {
        $get = RequestHelper::get();
        $result = WxappUploadLogModel::getWxappQRcode('pages/index/index', [
            'room_id' => $get['room_id'],
            'broadcast_room_id' => $get['broadcast_room_id']
        ]);

        return $this->result(['url' => $result]);
    }

    /**
     * 添加直播间
     * @throws BroadcastRoomException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $tr = \Yii::$app->db->beginTransaction();
        try {
            $result = BroadcastRoomModel::easyAdd([
                'attributes' => [
                    'created_at' => DateTimeHelper::now()
                ],
                'afterSave' => function ($result) {
                    //创建微信小程序直播间
                    $roomResult = MiniProgramBroadcastRoomHelper::create([
                        'name' => mb_substr($result->title, 0, 31) . (mb_strlen($result->title) > 31 ? '...' : ''), //直播间名称
                        'coverImg' => $result->cover_img_media_id, //背景图
                        'startTime' => strtotime($result->start_time),
                        'endTime' => strtotime($result->end_time),
                        'feedsImg' => $result->cover_img_media_id,//封面图
                        'anchorName' => $result->anchor_name,//主播昵称
                        'anchorWechat' => $result->anchor_wechat,//主播微信号
                        'shareImg' => $result->share_img_media_id,//分享图片
                        'type' => (int)$result->broadcast_type,//直播类型
                        'screenType' => (int)$result->screen_type,//
                        'closeLike' => (int)$result->close_like,//
                        'closeGoods' => (int)$result->close_goods,//
                        'closeComment' => (int)$result->close_comment,//
                    ]);

                    if (is_error($roomResult)) {

                        switch ($roomResult['error']) {
                            case 300002:
                                throw new \Exception('直播间名称过长最长17个汉字');
                            case 300035:
                                throw new \Exception('主播微信号不存在');
                                break;
                            case 300036:
                                throw new \Exception('主播微信号未实名认证');
                                break;
                            case 300034:
                                throw new \Exception('主播微信昵称长度不符合要求');
                                break;
                        }

                        throw new \Exception($roomResult['message']);
                    }

                    $result->broadcast_room_id = $roomResult['roomId'];
                    $result->save();

                    //  日志
                    LogModel::write(
                        $this->userId,
                        BroadcastLogConstant::BROADCAST_ROOM_ADD,
                        BroadcastLogConstant::getText(BroadcastLogConstant::BROADCAST_ROOM_ADD),
                        $result->id,
                        [
                            'log_data' => $result->attributes,
                            'log_primary' => [
                                '直播类型' => $result->broadcast_type == 0 ? '手机直播' : ('推流设备直播(' . $result->screen_type == 0 ? '竖屏' : '横屏' . ')'),
                                '直播间标题' => $result->title,
                                '开播时间' => $result->start_time . '-' . $result->end_time,
                                '主播昵称' => $result->anchor_name,
                                '主播微信账号' => $result->anchor_wechat
                            ]
                        ]
                    );
                },
                'onResult' => function ($data, $insert, &$result) {
                    $result['broadcast_room_id'] = $data['broadcast_room_id'];
                }
            ]);

            $tr->commit();
        } catch (\Exception $exception) {
            $tr->rollBack();
            throw new BroadcastRoomException(BroadcastRoomException::BROADCAST_MANAGE_ROOM_ADD_ERROR, $exception->getMessage());
        }

        return $this->result($result);
    }

    /**
     * 同步直播间
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSync()
    {
        BroadcastRoomModel::syncRoom($this->userId);
        return $this->success();
    }

    /**
     * 直播间详情
     * @throws BroadcastRoomException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $roomId = RequestHelper::getInt('room_id');
        $room = BroadcastRoomModel::findOne(['id' => $roomId]);
        if (empty($room)) {
            throw new BroadcastRoomException(BroadcastRoomException::BROADCAST_MANAGE_ROOM_DETAILS_ROOM_NOT_FOUND_ERROR);
        }

        //获取统计
        $statistic = BroadcastStatisticsModel::getStatistics($roomId);

        //全部付款的
        $broadcastOrder = OrderModel::find()
            ->where([
                'and',
                ['scene' => OrderSceneConstant::ORDER_SCENE_MINIPROGRAM_BROADCAST],
                ['scene_value' => $roomId],
                ['>=', 'status', OrderStatusConstant::ORDER_STATUS_WAIT_SEND]
            ])->asArray()->count();

        //所有的直播间订单
        $allBroadcastOrder = OrderModel::find()
            ->where([
                'and',
                ['scene' => OrderSceneConstant::ORDER_SCENE_MINIPROGRAM_BROADCAST],
                ['scene_value' => $roomId],
            ])->asArray()->count();


        //算已支付的直播间订单比例
        if (!empty($broadcastOrder) && !empty($allBroadcastOrder)) {
            $statistic['pay_percent'] = round2((($broadcastOrder / $allBroadcastOrder) * 100), 2);
        } else {
            $statistic['pay_percent'] = 0;
        }

        return $this->result([
            'room' => [
                'title' => $room['title'],
                'anchor_name' => $room['anchor_name'],
                'start_time' => $room['start_time'],
                'end_time' => $room['end_time'],
                'anchor_wechat' => $room['anchor_wechat'],
                'share_img' => $room['share_img'],
            ],
            'statistic' => $statistic
        ]);
    }

    /**
     * @return array|int[]|\yii\web\Response
     * @throws BroadcastRoomException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetailGoods()
    {
        $get = RequestHelper::get();
        $roomId = RequestHelper::getInt('room_id');
        $room = BroadcastRoomModel::findOne(['id' => $roomId]);
        if (empty($room)) {
            throw new BroadcastRoomException(BroadcastRoomException::BROADCAST_MANAGE_ROOM_DETAILS_GOODS_ROOM_NOT_FOUND_ERROR);
        }

        $orderBy = [];
        if ($get['sort'] && $get['by']) {
            $orderBy[$get['sort']] = $get['by'] == 'asc' ? SORT_ASC : SORT_DESC;
        }

        $orderBy['goods.id'] = SORT_DESC;


        //子查询sql
        $sql = OrderGoodsModel::find()
            ->alias('order_goods')
            ->leftJoin(OrderModel::tableName() . ' order', 'order_goods.order_id = order.id')
            ->where([
                'and',
                ['order.scene' => OrderSceneConstant::ORDER_SCENE_MINIPROGRAM_BROADCAST],
                ['order.scene_value' => $roomId],
                ['order_goods.goods_id' => new Expression("`room_goods`.`goods_id`")],
                ['>=', 'order.status', OrderStatusConstant::ORDER_STATUS_WAIT_SEND],
                ['order_goods.shop_goods_id' => 0]
            ])
            ->select('count(distinct order_goods.member_id)')
            ->createCommand()->getRawSql();

        //商品库列表
        $goodsList = BroadcastGoodsModel::getColl([
            'alias' => 'broadcast_goods',
            'leftJoins' => [
                [GoodsModel::tableName() . ' goods', 'goods.id = broadcast_goods.goods_id'],
                [BroadcastRoomGoodsMapModel::tableName() . ' room_goods', 'room_goods.goods_id = goods.id']
            ],
            'groupBy' => 'broadcast_goods.goods_id',
            'where' => [
                'room_goods.room_id' => $roomId
            ],
            'select' => [
                'goods.title',
                'goods.price',
                'goods.type',
                'goods.has_option',
                'room_goods.goods_id',
                'broadcast_goods.cover_img_url as thumb',
                'count(room_goods.room_id) as room_quantity',
                'sum(room_goods.pv_count) as pv_count',
                'sum(room_goods.sales) as sales',
                '(' . $sql . ') as member_count'
            ],
            'orderBy' => $orderBy
        ]);

        return $this->result($goodsList);
    }

    /**
     * 是否隐藏
     * @throws BroadcastRoomException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionHide()
    {
        $roomId = RequestHelper::postInt('room_id');
        $isHide = RequestHelper::postInt('is_hide');
        if (empty($roomId)) {
            throw new BroadcastRoomException(BroadcastRoomException::BROADCAST_MANAGE_ROOM_HIDE_PARAMS_ERROR);
        }

        BroadcastRoomModel::updateAll(['is_hide' => $isHide], ['id' => $roomId]);

        // 日志
        LogModel::write(
            $this->userId,
            BroadcastLogConstant::BROADCAST_ROOM_OPERATION,
            BroadcastLogConstant::getText(BroadcastLogConstant::BROADCAST_ROOM_OPERATION),
            $roomId,
            [
                'log_data' => [
                    'id' => $roomId,
                    'is_hide' => $isHide
                ],
                'log_primary' => [
                    '直播间操作' => $isHide == 1 ? '隐藏' : '显示'
                ]
            ]
        );

        return $this->success();
    }

}
