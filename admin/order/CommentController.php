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

namespace shopstar\admin\order;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\order\CommentLogConstant;
use shopstar\exceptions\order\CommentException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\order\OrderGoodsCommentModel;
use shopstar\models\order\OrderModel;
use shopstar\models\sale\CouponModel;
use yii\helpers\Json;

/**
 * 订单评价
 * Class CommentController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\order
 */
class CommentController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'get-type'
        ]
    ];

    /**
     * 评价列表
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $get = RequestHelper::get();
        $where = [];
        // 评论时间
        if (!empty($get['start_time']) && !empty($get['end_time'])) {
            $where[] = ['between', 'comment.created_at', $get['start_time'], $get['end_time']];
        }
        // 状态 0 需首次回复  1 需追加回复
        if ($get['reply_status'] != '') {
            if ($get['reply_status'] == 0) {
                $where[] = ['comment.reply_content' => ''];
            } else {
                $where[] = [
                    'and',
                    ['not', ['comment.reply_content' => '']],
                    ['comment.append_reply_content' => '']
                ];
            }
        }
        // 类型
        if ($get['type'] != '') {
            $where[] = ['comment.type' => $get['type']];
        }
        // 审核状态
        if ($get['status'] != '') {
            $where[] = ['comment.status' => $get['status']];
        }

        if (!empty($get['keyword'])) {
            $where[] = [
                'or',
                ['like', 'order.order_no', $get['keyword']],
                ['like', 'goods.title', $get['keyword']]
            ];
        }

        $where[] = ['comment.is_delete' => 0];

        $select = 'comment.*, goods.title, goods.thumb,order.order_no';

        $leftJoin = [
            [GoodsModel::tableName() . ' goods', 'goods.id=comment.goods_id'],
            [OrderModel::tableName() . ' order', 'order.id=comment.order_id'],
        ];

        $params = [
            'select' => $select,
            'andWhere' => $where,
            'alias' => 'comment',
            'leftJoins' => $leftJoin,
            'orderBy' => ['comment.id' => SORT_DESC]
        ];

        $list = OrderGoodsCommentModel::getColl($params, [
            'callable' => function (&$row) {

                if (!empty($row['reward_content'])) {
                    $row['reward_content'] = Json::decode($row['reward_content']);
                }

                if (!empty($row['reward_content']['coupon_id'])) {
                    $row['reward_content']['coupon_info'] = CouponModel::getCouponInfo($row['reward_content']['coupon_id']);
                }

                if (empty($row['reply_content'])) {
                    $row['comment_status'] = 1;
                } else {
                    $row['comment_status'] = 2;
                }
            }
        ]);

        return $this->result($list);
    }

    /**
     * 删除/批量删除
     * @throws CommentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::postArray('id');
        if (empty($id)) {
            throw new CommentException(CommentException::DELETE_PARAMS_ERROR);
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            OrderGoodsCommentModel::updateAll(['is_delete' => 1], ['id' => $id]);
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new CommentException(CommentException::COMMENT_DELETE_FAIL);
        }
        return $this->success();
    }

    /**
     * 详情
     * @throws CommentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $get = RequestHelper::get();
        if (empty($get['id'])) {
            throw new CommentException(CommentException::DETAIL_PARAMS_ERROR);
        }
        $comment = OrderGoodsCommentModel::find()->where(['id' => $get['id']])->asArray()->one();
        if (empty($comment)) {
            // 评价不存在
            throw new CommentException(CommentException::COMMENT_NOT_EXISTS);
        }
        $comment['images'] = Json::decode($comment['images']);
        $comment['goods_info'] = GoodsModel::find()->select('title, thumb,has_option,type,status, is_deleted')->where(['id' => $comment['goods_id']])->asArray()->one();
        if ($comment['type'] == 1 || $comment['type'] == 0) {
            $comment['order_info'] = OrderModel::find()->select('order_no,status')->where(['id' => $comment['order_id']])->asArray()->one();
        }

        if ($comment['reward_content']) {

            $comment['reward_content'] = Json::decode($comment['reward_content']);

            if (!empty($comment['reward_content']['coupon_id'])) {
                $comment['reward_content']['coupon_info'] = CouponModel::getCouponInfo($comment['reward_content']['coupon_id']);
            }
        }

        return $this->result(['item' => $comment]);
    }

    /**
     * 审核操作
     * @throws CommentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAudit()
    {
        $get = RequestHelper::get();
        if ($get['id'] == '') {
            throw new CommentException(CommentException::AUDIT_PARAMS_ERROR);
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            OrderGoodsCommentModel::updateAll(['status' => $get['status']], ['id' => $get['id']]);
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new CommentException(CommentException::COMMENT_AUDIT_FAIL);
        }


        LogModel::write(
            $this->userId,
            CommentLogConstant::COMMENT_SUCCESS,
            CommentLogConstant::getText(CommentLogConstant::COMMENT_SUCCESS),
            $get['id'],
            [
                'log_data' => [
                    'id' => $get['id'],
                    'status' => $get['status'],
                ],
                'log_primary' => [
                    'ID' => $get['id'],
                    '状态' => $get['status'] == 1 ? '通过' : '不通过'
                ],
                'dirty_identify_code' => [
                    CommentLogConstant::COMMENT_SUCCESS,
                ]
            ]
        );
        return $this->success();
    }

    /**
     * 回复操作
     * @throws CommentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionReply()
    {
        $post = RequestHelper::post();
        if (empty($post['id'])) {
            throw new CommentException(CommentException::REPLY_PARAMS_ERROR);
        }

        try {
            OrderGoodsCommentModel::updateAll(
                ['reply_content' => $post['reply_content'], 'reply_time' => DateTimeHelper::now()],
                ['id' => $post['id']]
            );
        } catch (\Throwable $exception) {
            throw new CommentException(CommentException::COMMENT_REPLY_FAIL);
        }
        return $this->success();
    }

    /**
     * 获取评论方式
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetType()
    {
        $type = [
            ['key' => '0', 'value' => '默认评价'],
            ['key' => '1', 'value' => '客户评价'],
        ];

        $type[] = [
            'key' => '2', 'value' => '手动创建'
        ];
        $type[] = [
            'key' => '3', 'value' => 'API抓取'
        ];

        return $this->result(['data' => $type]);
    }

}