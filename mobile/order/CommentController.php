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

namespace shopstar\mobile\order;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\order\OrderStatusConstant;
use shopstar\constants\RefundConstant;
use shopstar\exceptions\order\CommentException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderGoodsCommentModel;
use shopstar\models\order\OrderGoodsModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * 评价
 * Class CommentController
 * @author 青岛开店星信息技术有限公司
 * @package shop\client\order
 */
class CommentController extends BaseMobileApiController
{

    /**
     * @var \string[][]
     */
    public $configActions = [
        'allowNotLoginActions' => [
            'list'
        ]
    ];

    /**
     * 商品详情评价列表
     * @return \yii\web\Response
     * @throws CommentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList(): \yii\web\Response
    {
        $goodsId = RequestHelper::getInt('goods_id');
        if (empty($goodsId)) {
            throw new CommentException(CommentException::ORDER_GOODS_COMMENT_LIST_PARAMS_FOUND_ERROR);
        }

        $showComment = ShopSettings::get('sysset.trade.show_comment');
        if ($showComment == 0) {
            return $this->success(['list' => []]);
        }

        $defaultLevel = MemberLevelModel::getDefaultLevel();
        // 获取所有等级
        $levelsId = MemberLevelModel::find()->select('id')->column();

        // 兼容旧的数据  2021-05-06 做评价助手新增规格标题和会员等级名称字段 不需要重新取了
        $oldComment = [];
        $list = OrderGoodsCommentModel::getColl([
            'where' => [
                'status' => 1,
                'goods_id' => $goodsId,
                'is_delete' => 0
            ],
            'orderBy' => [
                'is_choice' => SORT_DESC,
                'sort_by' => SORT_DESC,
                'created_at' => SORT_DESC,
            ],
            'select' => [
                'id',
                'member_id',
                'nickname',
                'avatar',
                'level',
                'order_goods_id',
                'content',
                'images',
                'reply_content',
                'reply_time',
                'created_at',
                'member_level_name',
                'level_id',
                'option_title',
                'is_new',
            ]
        ], [
            'callable' => function (&$result) use (&$oldComment, $defaultLevel, $levelsId) {
                $result['images'] = Json::decode($result['images']);
                // 如果是旧的评论 需要去订单商品查找标题
                if ($result['is_new'] == 0) {
                    $oldComment['order_goods_id'][] = $result['order_goods_id'];
                    $oldComment['member_id'][] = $result['member_id'];
                } else {
                    // 新数据 比较等级id
                    if (!in_array($result['level_id'], $levelsId)) {
                        $result['level_id'] = $defaultLevel['id'];
                        $result['member_level_name'] = $defaultLevel['level_name'];
                    }
                }
            }
        ]);

        // 旧数据兼容
        if (!empty($oldComment)) {
            // 获取评价商品
            $orderGoods = OrderGoodsModel::find()
                ->where(['id' => $oldComment['order_goods_id']])->indexBy('id')
                ->select(['id', 'option_title'])->asArray()->all();
            //获取会员等级
            $member = MemberModel::find()
                ->alias('member')
                ->leftJoin(MemberLevelModel::tableName() . 'member_level', 'member_level.id=member.level_id')
                ->where(['member.id' => $oldComment['member_id']])
                ->indexBy('id')
                ->select(['member.id', 'member_level.level_name'])
                ->asArray()
                ->all();
            foreach ((array)$list['list'] as $key => $item) {
                // 如果是旧的评论
                if ($item['is_new'] == 0) {
                    $list['list'][$key]['option_title'] = isset($orderGoods[$item['order_goods_id']]) ? $orderGoods[$item['order_goods_id']]['option_title'] : '';
                    $list['list'][$key]['member_level_name'] = !empty($member[$item['member_id']]['level_name']) ? $member[$item['member_id']]['level_name'] : '';
                }
            }
        }

        // 获取系统设置
        $isCommentDesensitization = ShopSettings::get('sysset.trade.comment_desensitization');
        // 昵称脱敏
        if ($isCommentDesensitization == 1) {
            foreach ((array)$list['list'] as $key => $item) {
                $list['list'][$key]['nickname'] = mb_substr($list['list'][$key]['nickname'], 0, 1) . '***';
            }
        }

        $list['list'] = array_values($list['list']);
        return $this->success($list);
    }

    /**
     * 待评价列表
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionWaitList(): \yii\web\Response
    {
        $orderId = RequestHelper::getInt('order_id');
        $params = [
            'where' => [
                'member_id' => $this->memberId,
                'status' => OrderStatusConstant::ORDER_STATUS_SUCCESS,
                'comment_status' => 0,
                'refund_status' => [0, RefundConstant::REFUND_STATUS_REJECT, RefundConstant::REFUND_STATUS_CANCEL],
            ],
            'orderBy' => [
                'created_at' => SORT_DESC
            ],
            'select' => [
                'id',
                'order_id',
                'goods_id',
                'option_id',
                'member_id',
                'title',
                'option_title',
                'thumb',
                'comment_status',
                'price',
                'price_unit',
                'total'
            ]
        ];

        if (!empty($orderId)) {
            $params['where']['order_id'] = $orderId;
        }

        $list = OrderGoodsModel::getColl($params);

        return $this->success($list);
    }

    /**
     * 写评价
     * @throws CommentException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionWriteComment()
    {
        $orderComment = ShopSettings::get('sysset.trade.order_comment');
        $commentAudit = ShopSettings::get('sysset.trade.comment_audit');
        if ($orderComment == 0) {
            throw new CommentException(CommentException::ORDER_GOODS_COMMENT_WRITE_COMMENT_SWITCH_CLOSE_ERROR);
        }

        if (RequestHelper::isGet()) {
            $get = RequestHelper::get();
            $goods = [];
            if (!empty($get['order_goods_id'])) {
                $goods = OrderGoodsModel::find()->where(['id' => $get['order_goods_id']])->select([
                    'title',
                    'option_title',
                    'thumb',
                    'price',
                    'price_unit',
                    'total'
                ])->asArray()->one();
            }

            return $this->success(['goods' => $goods]);
        }
        $post = RequestHelper::post();
        if (empty($post['order_goods_id']) || $post['level'] < 0 || empty($post['content']) || mb_strlen($post['content']) > 500) {
            throw new CommentException(CommentException::ORDER_GOODS_COMMENT_WRITE_COMMENT_PARAMS_ERROR);
        }

        // 查找是否已评价过
        $isExists = OrderGoodsCommentModel::find()->where(['order_goods_id' => $post['order_goods_id'], 'member_id' => $this->memberId])->exists();
        if ($isExists) {
            throw new CommentException(CommentException::ORDER_CLIENT_IS_EXISTS);
        }

        $orderGoods = OrderGoodsModel::findOne(['id' => $post['order_goods_id']]);
        if (empty($orderGoods)) {
            throw new CommentException(CommentException::ORDER_GOODS_COMMENT_WRITE_COMMENT_ORDER_GOODS_NOT_FOUND_ERROR);
        }
        // 缓存没存名称 现查
        $member = MemberModel::find()->select('nickname, level_id')->where(['id' => $this->memberId])->first();
        // 查找等级名称
        $memberLevel = MemberLevelModel::find()->select('level_name')->where(['id' => $member['level_id']])->first();
        $data = [
            'order_id' => $orderGoods->order_id,
            'order_goods_id' => $post['order_goods_id'],
            'goods_id' => $orderGoods->goods_id,
            'level' => $post['level'],
            'status' => $commentAudit == 0 ? 1 : 0,
            'content' => $post['content'],
            'images' => Json::encode($post['images'] ?: []),
            'is_have_image' => $post['images'] ? 1 : 0, //是否有图
            'member_id' => $this->memberId,
            'nickname' => $member['nickname'] ?: '',
            'avatar' => $this->member['avatar'] ?: '',
            'created_at' => DateTimeHelper::now(),
            'client_type' => $this->clientType,
            'option_title' => $orderGoods->option_title, // 2021-05-06 评价助手新增  之前没有该字段
            'member_level_name' => $memberLevel['level_name'], // 2021-05-06 评价助手新增  之前没有该字段
            'is_new' => 1, // 2021-05-06 评价助手新增  之前没有该字段 更新后 新增的评论 都是新的
            'level_id' => $member['level_id'],
        ];

        $model = new OrderGoodsCommentModel();
        $model->setAttributes($data);
        if (!$model->save()) {
            throw new CommentException(CommentException::ORDER_GOODS_COMMENT_WRITE_COMMENT_ERROR);
        }

        //保存评论状态
        $orderGoods->comment_status = 1;
        $orderGoods->save();

        return $this->success();
    }
}
