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

namespace shopstar\admin\commentHelper;

use shopstar\bases\exception\BaseApiException;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\helpers\DateTimeHelper;
 
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberRedPackageModel;
use shopstar\models\order\OrderGoodsCommentModel;
use shopstar\models\order\OrderModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\shop\ShopSettings;
use shopstar\constants\commentHelper\CommentHelperConstant;
use shopstar\constants\commentHelper\CommentHelperLogConstant;
use shopstar\exceptions\commentHelper\CommentHelperException;
use shopstar\services\commentHelper\CommentGrabService;
use shopstar\services\commentHelper\CommentService;
use shopstar\bases\KdxAdminApiController;
use yii\helpers\Json;

/**
 * 商品助手
 * Class IndexController
 * @package apps\commentHelper\manage
 */
class IndexController extends KdxAdminApiController
{
    /**
     * 商品列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGoodsList()
    {
        $list = GoodsModel::getColl([
            'select' => [
                'goods.id',
                'goods.title',
                'goods.thumb',
                'goods.status',
                'goods.stock',
                'goods.is_deleted',
                'goods.type',
                'goods.has_option',
                'count(comment.id) as comment_total', // 总数量
                'count(if(comment.type=2, comment.id, null)) as manual_comment_total', // 手动创建评论数量
                'count(if(comment.type=3, comment.id, null)) as grab_comment_total', // 抓取评论数量
            ],
            'searchs' => [
                ['goods.title', 'like', 'keyword']
            ],
            'alias' => 'goods',
            'where' => [],
            'andWhere' => [
                ['<>', 'is_deleted', 2]
            ],
            'leftJoin' => [OrderGoodsCommentModel::tableName() . ' comment', 'comment.goods_id=goods.id and comment.is_delete=0'],
            'orderBy' => [
                'comment_total' => SORT_DESC,
                'goods.sort_by' => SORT_DESC,
                'goods.created_at' => SORT_DESC,
            ],
            'groupBy' => [
                'goods.id',
            ]
        ], [
            'callable' => function (&$row) {
                if (($row['status'] == 1 || $row['status'] == 2) && $row['stock'] > 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 1;
                } else if ($row['status'] == 1 && $row['stock'] == 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 2;
                } else if ($row['status'] == 0 && $row['is_deleted'] == 0) {
                    $row['status'] = 3;
                } else if ($row['is_deleted'] == 1) {
                    $row['status'] = 4;
                }
            }
        ]);

        return $this->result($list);
    }

    /**
     * 获取商品信息
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGoodsInfo()
    {
        $goodsId = RequestHelper::get('goods_id');
        if (empty($goodsId)) {
            return $this->error('参数错误');
        }

        // 查找商品
        $goods = GoodsModel::find()
            ->select(['id', 'title', 'thumb', 'type', 'has_option'])
            ->where(['id' => $goodsId])
            ->first();
        if (empty($goods)) {
            return $this->error('商品不存在');
        }

        return $this->result(['data' => $goods]);
    }

    /**
     * 商品评价
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGoodsCommentList()
    {
        $goodsId = RequestHelper::get('goods_id');
        if (empty($goodsId)) {
            return $this->error('参数错误');
        }

        $andWhere = [];
        $status = RequestHelper::get('status');
        if ($status != '') {
            if ($status == 1) {
                $andWhere[] = ['status' => 1];
            } else {
                $andWhere[] = ['<>', 'status', 1];
            }
        }

        $list = OrderGoodsCommentModel::getColl([
            'select' => [
                'id',
                'member_id',
                'nickname',
                'avatar',
                'level',
                'content',
                'status',
                'type',
                'sort_by',
                'created_at',
                'is_reward',
                'is_choice',
                'reward_content',
                'images',
            ],
            'searchs' => [
                ['type', 'int'],
                ['nickname', 'like', 'keyword']
            ],
            'where' => [
                'goods_id' => $goodsId,
                'is_delete' => 0,
            ],
            'andWhere' => $andWhere,
            'orderBy' => [
                'is_choice' => SORT_DESC,
                'sort_by' => SORT_DESC,
                'created_at' => SORT_DESC
            ]
        ], ['callable' => function (&$row) {
            $row['images'] = Json::decode($row['images']);
            $row['reward_content'] = Json::decode($row['reward_content']);

            if (!empty($row['reward_content']['coupon_id'])) {
                $row['reward_content']['coupon_info'] = CouponModel::getCouponInfo($row['reward_content']['coupon_id']);
            }

        }]);

        return $this->result($list);
    }

    /**
     * 奖励
     * @throws BaseApiException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionReward()
    {

        $id = RequestHelper::post('id');
        $memberId = RequestHelper::post('member_id');
        $couponId = RequestHelper::post('coupon_id');
        $credit = RequestHelper::post('credit');
        $balance = RequestHelper::post('balance');
        $redPackage = RequestHelper::post('red_package');
        $rewards = RequestHelper::post('rewards');

        if (empty($id) || empty($memberId)) {
            return $this->error('参数错误');
        }

        // 判断开启状态
        $commentRewardStatus = ShopSettings::get('commentHelper.comment_reward_status');
        if ($commentRewardStatus == 0) {
            return $this->error('评价奖励未开启');
        }

        // 查找评论
        $comment = OrderGoodsCommentModel::findOne(['id' => $id, 'type' => [0, 1]]);
        if (empty($comment)) {
            return $this->error('评价不存在');
        }

        // 已发奖励
        if ($comment->is_reward == 1) {
            return $this->error('奖励已发放');
        }

        $logData = [];
        // 发放奖励
        $rewardContent = [
            'rewards' => $rewards,
        ];

        // 发优惠券
        if (!empty($couponId[0])) {
            CouponModel::activitySendCoupon($memberId, $couponId);
            $rewardContent['coupon_id'] = $couponId;
            // 获取优惠券名称
            $coupons = CouponModel::find()->select('coupon_name')->where(['id' => $couponId])->column();
            $logData[] = '优惠券: ' . implode(',', $coupons);
        }
        // 发积分
        if (!empty($credit) && $credit > 0) {
            $member = MemberModel::updateCredit($memberId, $credit, 0, 'credit', 1, '评价奖励', MemberCreditRecordStatusConstant::COMMENT_REWARD_SEND_CREDIT);
            $rewardContent['credit'] = $credit;
            $logData[] = '积分: ' . $credit;

            $result = NoticeComponent::getInstance(NoticeTypeConstant::BUYER_PAY_CREDIT, [
                'member_nickname' => $member['nickname'],
                'nickname' => $member['nickname'],
                'recharge_price' => $credit,
                'recharge_method' => '',
                'recharge_time' => DateTimeHelper::now(),
                'recharge_pay_method' => '评价奖励',
                'member_credit' => $member['credit'],
                'change_time' => DateTimeHelper::now(),// 变动时间
                'change_reason' => '评价奖励',
            ]);
            if (!is_error($result)) {
                $result->sendMessage($memberId);
            }
        }
        // 发余额
        if (!empty($balance) && $balance > 0) {
            $member = MemberModel::updateCredit($memberId, $balance, 0, 'balance', 1, '评价奖励', MemberCreditRecordStatusConstant::COMMENT_REWARD_SEND_BALANCE);
            $rewardContent['balance'] = $balance;
            $logData[] = '余额: ' . $balance;

            $result = NoticeComponent::getInstance(NoticeTypeConstant::BUYER_PAY_RECHARGE, [
                'member_nickname' => $member['nickname'],
                'nickname' => $member['nickname'],
                'recharge_price' => $balance,
                'recharge_method' => '评价奖励',
                'balance_change_reason' => '评价奖励',
                'recharge_time' => DateTimeHelper::now(),
                'recharge_pay_method' => '评价奖励',
                'member_balance' => $member['balance'],
                'change_time' => DateTimeHelper::now(),// 变动时间
                'change_reason' => '评价奖励',
            ]);
            if (!is_error($result)) {
                $result->sendMessage($memberId);
            }
        }

        //发红包
        if (!empty($redPackage) && !empty($redPackage['money'])) {

            $rewardContent['red_package'] = $redPackage;
            $logData[] = '红包: ' . $redPackage['money'];

            MemberRedPackageModel::createLog( [
                'member_id' => $comment->member_id,
                'scene' => MemberRedPackageModel::SCENE_GOODS_COMMENT,
                'created_at' => DateTimeHelper::now(),
                'money' => $redPackage['money'],
                'extend' => Json::encode($redPackage),
                'expire_time' => date('Y-m-d H:i:s', time() + $redPackage['expiry'] * 86400)
            ]);
        }

        // 更新发放状态
        $comment->is_reward = 1;
        $comment->reward_content = Json::encode($rewardContent);
        $comment->save();

        $orderInfo = OrderModel::find()->select('order_no')->where(['id' => $comment['order_id']])->asArray()->one();

        if (empty($member)) {
            $member = MemberModel::findOne(['id' => $memberId]);
        }

        // 操作日志
        LogModel::write(
            $this->userId,
            CommentHelperLogConstant::COMMENT_HELPER_REWARD,
            CommentHelperLogConstant::getText(CommentHelperLogConstant::COMMENT_HELPER_REWARD),
            $id,
            [
                'log_data' => $comment->attributes,
                'log_primary' => [
                    'id' => $comment->id,
                    '会员' => $member['nickname'],
                    '订单编号' => $orderInfo['order_no'],
                    '奖励内容' => implode(',', $logData),
                    '奖励时间' => DateTimeHelper::now(),
                ],
                'dirty_identity_code' => [
                    CommentHelperLogConstant::COMMENT_HELPER_REWARD,
                ]
            ]
        );

        return $this->success();
    }

    /**
     * 精选
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChoice()
    {
        $id = RequestHelper::get('id');
        $displayOrder = RequestHelper::get('sort_by', 0);
        $isChoice = RequestHelper::get('is_choice');

        if (empty($id)) {
            return $this->error('参数错误');
        }

        // 获取设置
        $choiceStatus = ShopSettings::get('commentHelper.choice_status');
        if ($choiceStatus == 0) {
            return $this->error('精选评价未开启');
        }
        // 更新排序
        OrderGoodsCommentModel::updateAll(['sort_by' => $displayOrder, 'is_choice' => $isChoice], ['id' => $id]);

        return $this->success();
    }

    /**
     * 手动创建
     * @return array|int[]|\yii\web\Response
     * @throws CommentHelperException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionManualCreate()
    {
        $post = RequestHelper::post();

        // 评价不能为空
        if (empty($post['comment_data']) || !is_array($post['comment_data'])) {
            throw new CommentHelperException(CommentHelperException::MANUAL_CONTENT_EMPTY);
        }
        // 判空
        foreach ($post['comment_data'] as $item) {
            if (empty($item['content'])) {
                throw new CommentHelperException(CommentHelperException::MANUAL_CONTENT_EMPTY);
            }
        }

        // 评价时间不能为空
        if (empty($post['start_time']) || empty($post['end_time'])) {
            throw new CommentHelperException(CommentHelperException::MANUAL_TIME_EMPTY);
        }
        if ($post['start_time'] > $post['end_time']) {
            throw new CommentHelperException(CommentHelperException::MANUAL_TIME_ERROR);
        }
        // 如果是自定义创建
        if ($post['member_type'] == 0) {
            // 校验用户昵称头像
            if (empty($post['nickname']) || empty($post['avatar'])) {
                throw new CommentHelperException(CommentHelperException::MANUAL_CUSTOMER_MEMBER_INFO_ERROR);
            }
        } else {
            // 选择商城会员
            if (empty($post['member_id'])) {
                throw new CommentHelperException(CommentHelperException::MANUAL_MEMBER_EMPTY);
            }
        }

        // 创建
        $service = new CommentService();
        $service->manualCreateComment($post);

        // 日志
        $logPrimary = [
            '创建方式' => '手动创建',
            '评分等级' => $post['level'] . '星',
            '评价内容' => '',
            '评价会员' => $post['member_type'] == 0 ? $post['nickname'] : '',
            '评价时段' => $post['start_time'] . '-' . $post['end_time'],
            '显示状态' => $post['status'] == 1 ? '显示' : '隐藏'
        ];
        $commentStr = '';
        foreach ($post['comment_data'] as $value) {
            $commentStr .= '文字：' . $value['content'] . '，';
        }
        $logPrimary['评价内容'] = $commentStr;
        if ($post['member_type'] == 1 && !empty($post['member_id'])) {
            $member = MemberModel::find()->select(['nickname'])->where(['id' => explode(',', $post['member_id'])])->get();
            $logPrimary['评价会员'] = implode(array_column($member, 'nickname'), '、');
        }

        LogModel::write(
            $this->userId,
            CommentHelperLogConstant::COMMENT_HELPER_ADD,
            CommentHelperLogConstant::getText(CommentHelperLogConstant::COMMENT_HELPER_ADD),
            0,
            [
                'log_data' => $post,
                'log_primary' => $logPrimary
            ]
        );

        return $this->success();
    }

    /**
     * 编辑评价
     * @throws CommentHelperException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $post = RequestHelper::post();
        // 评价内容不能为空
        if (empty($post['content'])) {
            throw new CommentHelperException(CommentHelperException::EDIT_CONTENT_EMPTY);
        }
        // 评价时间
        if (empty($post['time'])) {
            throw new CommentHelperException(CommentHelperException::EDIT_TIME_EMPTY);
        }
        // 头像 等级 昵称
        if (empty($post['nickname']) || empty($post['member_level_name']) || empty($post['avatar'])) {
            throw new CommentHelperException(CommentHelperException::EDIT_MEMBER_INFO_ERROR);
        }

        // 修改
        $service = new CommentService();
        $service->editComment($this->userId, $post);

        return $this->success();
    }

    /**
     * api抓取
     * @return array|int[]|\yii\web\Response
     * @throws CommentHelperException|\yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGrab()
    {
        $post = RequestHelper::post();

        // 校验参数
        // 抓取地址
        if (empty($post['url'])) {
            throw new CommentHelperException(CommentHelperException::GRAB_URL_EMPTY);
        }
        // 抓取数量
        if (empty($post['num']) || !is_numeric($post['num'])) {
            throw new CommentHelperException(CommentHelperException::GRAB_NUM_EMPTY);
        }
        // 评价时间
        if (empty($post['start_time']) || empty($post['end_time']) || $post['start_time'] > $post['end_time']) {
            throw new CommentHelperException(CommentHelperException::GRAB_TIME_ERROR);
        }

        // 抓取
        $service = new CommentGrabService($this->userId, $post);
        $result = $service->grab();
        if (is_error($result)) {
            throw new CommentHelperException(CommentHelperException::GRAB_API_ERROR, $result['message']);
        }

        // 日志
        $logPrimary = [
            '创建方式' => 'API抓取',
            '渠道选择' => $post['type'] == CommentHelperConstant::TYPE_TAOBAO ? '淘宝' : ($post['type'] == CommentHelperConstant::TYPE_TMALL ? '天猫' : ($post['type'] == CommentHelperConstant::TYPE_SUNING ? '苏宁易购' : ($post['type'] == CommentHelperConstant::TYPE_JD ? '京东' : ''))),
            '商品链接' => $post['url'],
            '抓取数量' => $post['num'],
            '抓取内容' => $post['content_type'] == CommentHelperConstant::CONTENT_TYPE_DEFAULT ? '逐条抓取评价' : ($post['content_type'] == CommentHelperConstant::CONTENT_TYPE_GOODS ? '仅抓取好评' : ($post['content_type'] == CommentHelperConstant::CONTENT_TYPE_IMAGES ? '仅抓取带图评价' : ($post['content_type'] == CommentHelperConstant::CONTENT_TYPE_TEXT ? '仅抓取评价文字' : ''))),
            '评分等级' => $post['level'] . '星',
            '评价会员等级' => '随机等级',
            '评价时段' => $post['start_time'] . '-' . $post['end_time'],
            '显示状态' => $post['status'] == 1 ? '显示' : '隐藏'
        ];
        if ($post['level_id'] > 0) {
            $memberLevel = MemberLevelModel::find()->select(['level_name'])->where(['id' => $post['level_id']])->first();
            $logPrimary['评价会员等级'] = $memberLevel['level_name'];
        }

        LogModel::write(
            $this->userId,
            CommentHelperLogConstant::COMMENT_HELPER_ADD,
            CommentHelperLogConstant::getText(CommentHelperLogConstant::COMMENT_HELPER_ADD),
            0,
            [
                'log_data' => $post,
                'log_primary' => $logPrimary
            ]
        );

        return $this->result(['data' => $result]);
    }

    /**
     * 审核操作
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeStatus()
    {
        $get = RequestHelper::get();
        if ($get['id'] == '') {
            return $this->error('参数错误');
        }

        OrderGoodsCommentModel::updateAll(['status' => $get['status']], ['id' => $get['id']]);

        return $this->success();
    }

}