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
use shopstar\exceptions\broadcast\BroadcastRoomOperationException;
use shopstar\helpers\RequestHelper;
use shopstar\models\broadcast\BroadcastRoomGoodsMapModel;
use shopstar\models\broadcast\BroadcastRoomModel;
use shopstar\models\goods\GoodsModel;

/**
 * 小程序直播间运营
 * Class RoomOperationController
 * @author 青岛开店星信息技术有限公司
 * @package apps\broadcast\manage
 */
class RoomOperationController extends KdxAdminApiController
{
    /**
     * 商品库列表
     * @throws BroadcastRoomOperationException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGoodsList()
    {
        $roomId = RequestHelper::getInt('room_id');
        if (empty($roomId)) {
            throw new BroadcastRoomOperationException(BroadcastRoomOperationException::BROADCAST_MANAGE_ROOM_OPERATION_GOODS_LIST_PARAMS_ERROR);
        }

        $list = BroadcastRoomGoodsMapModel::getColl([
            'alias' => 'room_goods_map',
            'leftJoin' => [GoodsModel::tableName() . ' goods', 'goods.id=room_goods_map.goods_id '],
            'where' => [
                'room_goods_map.room_id' => $roomId,
            ],
            'searchs' => [
                ['goods.title', 'like', 'title']
            ],
            'select' => [
                'room_goods_map.broadcast_goods_id',
                'room_goods_map.goods_id',
                'goods.title',
                'goods.thumb',
                'goods.min_price',
                'goods.max_price',
                'goods.price',
                'goods.type',
                'goods.has_option',
            ]
        ]);

        //获取直播间信息
        $list['room'] = BroadcastRoomModel::find()->where(['id' => $roomId])->select([
            'id',
            'title',
            'start_time',
            'end_time',
            'cover_img',
            'status',
            'share_img',
            'broadcast_room_id',
            'anchor_name',
            'anchor_wechat',
        ])->one();

        return $this->result($list);
    }

    /**
     * 添加直播间商品
     * @return array|int[]|\yii\web\Response
     * @throws BroadcastRoomOperationException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAddRoomGoods()
    {
        $post = RequestHelper::post();
        if (empty($post['goods']) || empty($post['room_id']) || empty($post['broadcast_room_id'])) {
            throw new BroadcastRoomOperationException(BroadcastRoomOperationException::BROADCAST_MANAGE_ROOM_OPERATION_ADD_ROOM_GOODS_PARAMS_ERROR);
        }

        $tr = \Yii::$app->db->beginTransaction();
        try {
            //判断是否大于200个直播间商品
            $count = BroadcastRoomGoodsMapModel::find()->where(['room_id' => $post['room_id']])->count();
            if ($count >= 200 || ($count + count($post['goods']) >= 200)) {
                throw new \Exception('直播间商品不可大于200件');
            }

            $exist = BroadcastRoomGoodsMapModel::find()->where(['room_id' => $post['room_id'], 'goods_id' => array_column($post['goods'], 'goods_id')])->one();
            if (!empty($exist)) {
                throw new \Exception('商品已存在，请勿重复添加');
            }


            $result = BroadcastRoomGoodsMapModel::saveMap($post['goods'], $post['room_id']);
            if (is_error($result) || !$result) {
                throw new \Exception('添加直播间映射失败');
            }

            $result = MiniProgramBroadcastRoomHelper::addGoods([
                'ids' => (array)array_column($post['goods'], 'broadcast_goods_id'),
                'roomId' => (int)$post['broadcast_room_id']
            ]);

            if (is_error($result)) {
                throw new \Exception($result['message']);
            }

            $tr->commit();
        } catch (\Exception $exception) {
            $tr->rollBack();
            throw new BroadcastRoomOperationException(BroadcastRoomOperationException::BROADCAST_MANAGE_ROOM_OPERATION_ADD_ROOM_GOODS_ERROR, $exception->getMessage());
        }

        return $this->result($result);
    }
}
