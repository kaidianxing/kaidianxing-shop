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

namespace shopstar\mobile\broadcast;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\broadcast\BroadcastRoomIsDeletedConstant;
use shopstar\constants\broadcast\BroadcastRoomStatusConstant;
use shopstar\exceptions\broadcast\BroadcastRoomException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\broadcast\BroadcastRoomGoodsMapModel;
use shopstar\models\broadcast\BroadcastRoomModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\wxapp\WxappUploadLogModel;

/**
 * 直播
 * Class RoomController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\mobile\broadcast
 */
class RoomController extends BaseMobileApiController
{
    /**
     * @author 青岛开店星信息技术有限公司
     * @var string
     */
    public $allowNotLoginController = '*';

    /**
     * 获取商品直播间
     * @return array|int[]|\yii\web\Response
     * @throws BroadcastRoomException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetGoodsBroadcast()
    {
        $goodsId = RequestHelper::getInt('goods_id');
        if (empty($goodsId)) {
            throw new BroadcastRoomException(BroadcastRoomException::BROADCAST_CLIENT_ROOM_GET_GOODS_ROOM_PARAMS_ERROR);
        }

        $broadcastRoom = BroadcastRoomModel::getGoodsRoom($goodsId);
        //如果是error 则返回0 代表没有正在直播的直播间
        if (is_error($broadcastRoom)) {
            throw new BroadcastRoomException(BroadcastRoomException::BROADCAST_CLIENT_ROOM_GET_GOODS_ROOM_ERROR, $broadcastRoom['message']);

        }

        return $this->result([
            'broadcast_room_id' => $broadcastRoom['broadcast_room_id'],
            'room_id' => $broadcastRoom['id'],
        ]);
    }

    /**
     * 直播间列表
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRoomList()
    {
        $roomId = RequestHelper::get('room_id');

        $where = [
            'status' => [
                BroadcastRoomStatusConstant::BROADCAST_ROOM_STATUS_UNDERWAY,
                BroadcastRoomStatusConstant::BROADCAST_ROOM_STATUS_NOTSTARTED,
                BroadcastRoomStatusConstant::BROADCAST_ROOM_STATUS_END,
                BroadcastRoomStatusConstant::BROADCAST_ROOM_STATUS_SUSPEND,
            ]
        ];

        $orderBy = [];
        if (!empty($roomId)) {
            $where['id'] = ArrayHelper::explode(',', $roomId);
            $orderBy[] = new \yii\db\Expression('FIELD (id, ' . $roomId . ')');
        }

        $orderBy = array_merge($orderBy, [
            'status' => SORT_ASC,
            'start_time' => SORT_DESC
        ]);

        $list = BroadcastRoomModel::getColl([
            'where' => $where,
            'andWhere' => [
                ['is_hide' => 0, 'is_deleted' => BroadcastRoomIsDeletedConstant::BROADCAST_ROOM_IS_DELETED_NO]
            ],
            'orderBy' => $orderBy,
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
     * 获取小程序二维码
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
            'member_id' => $this->memberId,
            'broadcast_room_id' => $get['broadcast_room_id']
        ]);

        return $this->result(['url' => $result]);
    }
}
