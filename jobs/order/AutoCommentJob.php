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

namespace shopstar\jobs\order;

use shopstar\helpers\DateTimeHelper;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsCommentModel;
use shopstar\models\order\OrderGoodsModel;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * 订单自动评价
 * Class AutoCommentJob
 * @package shopstar\jobs\order
 * @author 青岛开店星信息技术有限公司
 */
class AutoCommentJob extends BaseObject implements JobInterface
{
    /**
     * @var int 订单id
     */
    public $orderId;

    /**
     * @var int 会员id
     */
    public $memberId;

    /**
     * @var string 自动评价内容
     */
    public $content;

    /**
     * @param Queue $queue
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function execute($queue)
    {
        // 查找是否已评价过
        $isExists = OrderGoodsCommentModel::find()->where(['order_id' => $this->orderId, 'member_id' => $this->memberId])->exists();
        if ($isExists) {
            echo '已评价过,订单id:' . $this->orderId . "\n";
            return;
        }

        // 查找订单商品
        $orderGoods = OrderGoodsModel::find()
            ->select(['id', 'member_id', 'goods_id', 'is_single_refund', 'refund_type', 'refund_status', 'option_title'])
            ->where(['order_id' => $this->orderId,])
            ->get();
        // 查找会员信息
        $member = MemberModel::find()->select(['nickname', 'avatar', 'level_id'])->where(['id' => $this->memberId])->first();
        $memberLevel = MemberLevelModel::find()->select('level_name')->where(['id' => $member['level_id']])->first();
        // 遍历商品
        foreach ($orderGoods as $item) {
            // 不评价维权的商品
            if ($item['is_single_refund'] == 1 && $item['refund_type'] != 3 && $item['refund_status'] >= 10) {
                continue;
            }
            // 自动评价
            $comment = new OrderGoodsCommentModel();
            $comment->setAttributes([
                'member_id' => $item['member_id'],
                'order_id' => $this->orderId,
                'order_goods_id' => $item['id'],
                'goods_id' => $item['goods_id'],
                'level' => 5,
                'status' => 1,
                'type' => 0,
                'content' => $this->content,
                'images' => Json::encode([]),
                'is_have_images' => 0, //是否有图
                'nickname' => $member['nickname'] ?: '',
                'avatar' => $member['avatar'] ?: '',
                'created_at' => DateTimeHelper::now(),
                'option_title' => $item['option_title'], // 2021-05-06 评价助手新增  之前没有该字段
                'member_level_name' => $memberLevel['level_name'], // 2021-05-06 评价助手新增  之前没有该字段
                'is_new' => 1, // 2021-05-06 评价助手新增  之前没有该字段 更新后 新增的评论 都是新的

            ]);
            if (!$comment->save()) {
                echo '自动评价失败,订单id:' . $this->orderId . ',失败原因:' . $comment->getErrorMessage() . "\n";
                return;
            }
            // 更新评价状态
            OrderGoodsModel::updateAll(['comment_status' => 1], ['id' => $item['id']]);
        }
    }
}