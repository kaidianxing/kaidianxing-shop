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

namespace shopstar\mobile\member;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\constants\article\ArticleSellDataConstant;
use shopstar\constants\base\PayTypeConstant;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\coupon\CouponConstant;
use shopstar\constants\coupon\CouponTimeLimitConstant;
use shopstar\constants\creditShop\CreditShopConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\tradeOrder\TradeOrderTypeConstant;
use shopstar\exceptions\sale\CouponException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\creditShop\CreditShopGoodsModel;
use shopstar\models\goods\category\GoodsCategoryModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberDouyinModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberToutiaoLiteModel;
use shopstar\models\member\MemberToutiaoModel;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\models\sale\CouponLogModel;
use shopstar\models\sale\CouponMapModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\sale\CouponRuleModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\article\ArticleSellDataService;
use shopstar\services\sale\CouponMemberService;
use shopstar\services\sale\CouponService;
use shopstar\services\tradeOrder\TradeOrderService;

/**
 * @author 青岛开店星信息技术有限公司
 */
class CouponController extends BaseMobileApiController
{
    public $configActions = [
        'allowNotLoginActions' => [
            'detail'
        ]
    ];

    /**
     * @var string[]
     * @author 青岛开店星信息技术有限公司.
     */
    public $needBindMobileActions = [
        'get-coupon' => 'get_coupon',
    ];

    /**
     * 获取列表
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $leftJoin = [];
        $where = [
            ['state' => 1],
            ['pick_type' => CouponConstant::COUPON_PICK_TYPE_CENTER],
            [ // 库存
                'or',
                ['stock_type' => CouponConstant::COUPON_STOCK_TYPE_NOT_LIMIT],
                [
                    'and',
                    ['stock_type' => CouponConstant::COUPON_STOCK_TYPE_LIMIT],
                    ['>', '`stock` - `get_total`', 0]
                ]
            ],
            [ // 领取时间
                'or',
                ['time_limit' => CouponConstant::COUPON_TIME_LIMIT_DAYS],
                [
                    'and',
                    ['time_limit' => CouponConstant::COUPON_TIME_LIMIT_AREA],
                    ['>', 'end_time', DateTimeHelper::now()]
                ]
            ]
        ];

        $params = [
            'select' => [
                'id',
                'get_max',
                'get_max_type',
                'get_total',
                'stock',
                'stock_type',
                'coupon_name',
                'coupon_type',
                'time_limit',
                'limit_day',
                'start_time',
                'end_time',
                'enough',
                'discount_price',
                'coupon_sale_type',
            ],
            'andWhere' => $where,
            'leftJoin' => $leftJoin,
            'orderBy' => [
                'sort' => SORT_DESC,
                'id' => SORT_DESC,
            ],
        ];

        //获取是否翻页
        $pager = RequestHelper::getInt('pager', 1);
        $list = CouponModel::getColl($params, [
            'pager' => (bool)$pager,
        ]);

        return $this->result($list);
    }

    /**
     * 优惠券详情
     * @return array|int[]|\yii\web\Response
     * @throws CouponException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new CouponException(CouponException::COUPON_DETAIL_PARAMS_ERROR);
        }
        $detail = CouponModel::find()->where(['id' => $id])->first();
        if (empty($detail)) {
            throw new CouponException(CouponException::SEND_COUPON_NOT_EXISTS);
        }
//        // 未开始发放 未开始发放可以查看详情 so 注释
//        if ($detail['state'] == 0) {
//            throw new CouponException(CouponException::COUPON_DETAIL_STATE_ERROR);
//        }
        // 优惠文字
        if ($detail['coupon_sale_type'] == CouponConstant::COUPON_SALE_TYPE_SUB) {
            $detail['content'] = '满' . ValueHelper::delZero($detail['enough']) . '减' . ValueHelper::delZero($detail['discount_price']);
        } else {
            // 打折类型
            $detail['content'] = '满' . ValueHelper::delZero($detail['enough']) . '享' . ValueHelper::delZero($detail['discount_price']) . '折';
        }

        // 使用说明 系统默认
        if ($detail['default_description'] == 1) {
            $detail['default_description'] = ShopSettings::get('sale.coupon.set')['use_content'];
        }
        // 会员限制
        if ($detail['limit_member'] == CouponConstant::COUPON_LIMIT_MEMBER) {
            $members = CouponRuleModel::find()
                ->alias('rule')
                ->select('level.level_name')
                ->where(['coupon_id' => $detail['id']])
                ->leftJoin(MemberLevelModel::tableName() . ' as level', 'rule.member_level = level.id')
                ->get();
            $detail['limit_member'] = array_column($members, 'level_name');
        }

        // 商品限制
        if ($detail['goods_limit'] != CouponConstant::COUPON_GOODS_NOT_LIMIT) {
            // 获取限制
            $godsLimit = CouponMapModel::find()
                ->where(['coupon_id' => $id])
                ->select('goods_cate_id')
                ->get();
            $limitIds = array_column($godsLimit, 'goods_cate_id');

            $goods = [];
            switch ($detail['goods_limit']) {
                case CouponConstant::COUPON_GOODS_LIMIT_ALLOW_GOODS: // 允许商品
                case CouponConstant::COUPON_GOODS_LIMIT_NOT_ALLOW_GOODS: // 不允许商品
                    $goods = GoodsModel::findAll(['id' => $limitIds]);
                    break;
                case CouponConstant::COUPON_GOODS_LIMIT_ALLOW_GOODS_CATE: // 允许分类
                    $goods = GoodsCategoryModel::findAll(['id' => $limitIds]);
                    break;
            }
            $detail['goods'] = $goods;
        }

        if (RequestHelper::get('is_credit_shop')) {
            // 查找商品
            $detail['credit_shop'] = CreditShopGoodsModel::find()->where(['goods_id' => $id, 'type' => 1])->first();
            if (empty($detail['credit_shop'])) {
                return $this->error('积分商品不存在');
            }

            // 会员等级限制
            if ($detail['credit_shop']['member_level_limit_type'] != 0) {
                $memberLevelId = explode(',', $detail['credit_shop']['member_level_id']);
                $detail['credit_shop']['member_level_name'] = MemberLevelModel::find()->select('level_name')->where(['id' => $memberLevelId])->column();
            }

            // 会员等级限制
            if ($detail['credit_shop']['member_group_limit_type'] != 0) {
                $memberGroupId = explode(',', $detail['credit_shop']['member_group_id']);
                $detail['credit_shop']['member_group_name'] = MemberGroupModel::find()->select('group_name')->where(['id' => $memberGroupId])->column();
            }

            // 检测购买权限
            $detail['credit_shop']['perm']['buy'] = true;

            if (!empty($this->memberId)) {
                // 判断购买权限 会员等级和标签的限制 读自己设置的
                // 会员等级限制
                if ($detail['credit_shop']['member_level_limit_type'] != CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_NOT_LIMIT) {
                    $limitLevelId = explode(',', $detail['credit_shop']['member_level_id']);
                    $memberLevelId = MemberModel::find()->select('level_id')->where(['id' => $this->memberId])->first();
                    // 无权限
                    if (($detail['credit_shop']['member_level_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_ALLOW && !in_array($memberLevelId['level_id'], $limitLevelId))
                        || ($detail['credit_shop']['member_level_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_DENY && in_array($memberLevelId['level_id'], $limitLevelId))) {
                        $detail['credit_shop']['perm']['buy'] = false;
                    }
                }

                // 标签限制
                if ($detail['credit_shop']['perm']['buy'] && $detail['credit_shop']['member_group_limit_type'] != CreditShopConstant::MEMBER_GROUP_LIMIT_TYPE_NOT_LIMIT) {
                    $limitGroupId = explode(',', $detail['credit_shop']['member_group_id']);
                    // 获取会员标签
                    $memberGroupId = MemberGroupMapModel::getGroupIdByMemberId($this->memberId);
                    // 判断有没有交集
                    $isIntersect = array_intersect($limitGroupId, $memberGroupId);
                    // 无权限
                    if (($detail['credit_shop']['member_group_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_ALLOW && $isIntersect)
                        || ($detail['credit_shop']['member_group_limit_type'] == CreditShopConstant::MEMBER_LEVEL_LIMIT_TYPE_DENY && !$isIntersect)) {
                        $detail['credit_shop']['perm']['buy'] = false;
                    }
                }
            }
        }

        return $this->result(['data' => $detail]);
    }

    /**
     * 领取免费优惠券
     * 领券中心/链接领取
     * @return array|int[]|\yii\web\Response
     * @throws CouponException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetCoupon()
    {
        $id = RequestHelper::get('id');
        $articleId = RequestHelper::getInt('article_id');
        if (empty($id)) {
            throw new CouponException(CouponException::COUPON_GET_PARAMS_ERROR);
        }

        // 检查优惠券
        $coupon = CouponService::checkReceive($this->memberId, $id);
        if (is_error($coupon)) {
            throw new CouponException(CouponException::COUPON_GET_CHECK_ERROR, $coupon['message']);
        }

        // 如果不免费
        if ($coupon['is_free'] == CouponConstant::IS_NOT_FREE) {
            throw new CouponException(CouponException::COUPON_IS_NOT_FREE);
        }

        // 如果是领券中心  则是免费领取
        if ($coupon['pick_type'] == CouponConstant::COUPON_PICK_TYPE_CENTER) {
            $source = 11;
        } else if ($coupon['pick_type'] == CouponConstant::COUPON_PICK_TYPE_LINK) {
            // 如果是链接  则是链接领取
            $source = 13;
        } else {
            throw new CouponException(CouponException::FAIL_TO_RECEIVE);
        }

        // 发送优惠券
        $res = CouponMemberService::sendCoupon($this->memberId, $coupon, $source, ['get_id' => true]);
        if (is_error($res)) {
            throw new CouponException(CouponException::FAIL_TO_RECEIVE, $res['message']);
        }

        if ($articleId) {
            ArticleSellDataService::saveSellData($this->memberId, ArticleSellDataConstant::TYPE_COUPON, $articleId, $res, $id);
        }

        return $this->success();
    }

    /**
     * 购买优惠券
     * @return array|\yii\web\Response
     * @throws CouponException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionPayCoupon()
    {
        $id = RequestHelper::postInt('id');
        $articleId = RequestHelper::postInt('article_id');

        // 是否需要支付
        $needPay = 0;
        $payList = [];
        // 检查优惠券
        $coupon = CouponService::checkReceive($this->memberId, $id);
        if (is_error($coupon)) {
            throw new CouponException(CouponException::COUPON_GET_CHECK_ERROR, $coupon['message']);
        }
        // 如果是免费券
        if ($coupon['is_free'] == CouponConstant::IS_FREE) {
            throw new CouponException(CouponException::COUPON_ONT_NEED_PAY);
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 积分抵扣
            if ($coupon['credit'] != 0) {
                // 获取用户积分 校验积分
                $memberCredit = MemberModel::getCredit($this->memberId);
                if ($coupon['credit'] > $memberCredit) {
                    throw new CouponException(CouponException::COUPON_PAY_CREDIT_NOT_ENOUGH);
                }
                // 扣除积分
                $res = MemberModel::updateCredit($this->memberId, $coupon['credit'], 0, 'credit', 2, '优惠券购买', MemberCreditRecordStatusConstant::CREDIT_STATUS_DEDUCTION);
                if (is_error($res)) {
                    throw new CouponException(CouponException::COUPON_PAY_CREDIT_FAIL, $res['message']);
                }
            }

            // 余额抵扣
            if ($coupon['balance'] != 0) {
                $needPay = 1;
                // 获取支付方式
                $payList = ShopSettings::getOpenPayType(ClientTypeConstant::getIdentify(RequestHelper::header('Client-Type')));
                // 删除货到付款
                unset($payList['delivery']);
            }
            //创建一条购买记录 (不能删除未成功日志  查找错误)
            $orderId = CouponLogModel::createLog($this->memberId, $coupon, $this->clientType, $articleId);
            if (is_error($orderId)) {
                throw new CouponException(CouponException::PAY_COUPON_BUILD_LOG_ERROR);
            }

            // 不需要支付 直接发送
            if ($needPay == 0) {
                $res = CouponMemberService::sendCoupon($this->memberId, $coupon, 12, ['get_id' => true]);
                if (is_error($res)) {
                    throw new CouponException(CouponException::FAIL_TO_RECEIVE, $res['message']);
                }

                if ($articleId) {
                    ArticleSellDataService::saveSellData($this->memberId, ArticleSellDataConstant::TYPE_COUPON, $articleId, $res, $id);
                }

            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }

        return $this->result(['need_pay' => $needPay, 'pay_list' => $payList, 'order_id' => $orderId]);
    }

    /**
     * 支付优惠券
     * @return array|\yii\web\Response
     * @throws CouponException
     * @throws \shopstar\exceptions\tradeOrder\TradeOrderPayException
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionPay()
    {
        // 购买记录ID
        $orderId = RequestHelper::post('order_id');
        if (empty($orderId)) {
            return $this->error('参数错误 order_id不能为空');
        }

        // 支付类型
        $payType = RequestHelper::post('pay_type');
        if (empty($payType)) {
            return $this->error('参数错误 pay_type不能为空');
        }

        // 转为code，为空时说明传入pay_type无效
        $payTypeCode = PayTypeConstant::getPayTypeCodeByIdentity($payType);
        if (empty($payTypeCode)) {
            return $this->error('参数错误 不支持的pay_type');
        }

        /**
         * 查询购买记录订单
         * @var CouponLogModel $order
         */
        $order = CouponLogModel::find()
            ->where([
                'id' => $orderId,
            ])
            ->select(['id', 'order_no', 'pay_price'])
            ->one();
        if (empty($order)) {
            throw new CouponException(CouponException::PARAMS_OR_PAY_TYPE_ERROR);
        } elseif ($order->status) {
            throw new CouponException(CouponException::PARAMS_OR_PAY_TYPE_ERROR);
        }

        // 根据渠道获取会员openid
        $openid = '';
        if ($this->clientType == ClientTypeConstant::CLIENT_WECHAT) {
            $openid = MemberWechatModel::getOpenId($this->memberId);
        } else if ($this->clientType == ClientTypeConstant::CLIENT_WXAPP) {
            $openid = MemberWxappModel::getOpenId($this->memberId);
        } else if ($this->clientType == ClientTypeConstant::CLIENT_BYTE_DANCE_DOUYIN) {
            $openid = MemberDouyinModel::getOpenId($this->memberId);
        } else if ($this->clientType == ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO) {
            $openid = MemberToutiaoModel::getOpenId($this->memberId);
        } else if ($this->clientType == ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE) {
            $openid = MemberToutiaoLiteModel::getOpenId($this->memberId);
        }

        /** @change likexin 调用交易订单服务获取支付参数 * */
        $result = TradeOrderService::pay([
            'type' => TradeOrderTypeConstant::TYPE_MEMBER_COUPON_ORDER,     // 交易订单类型(交易类型)
            'payType' => $payTypeCode,              // 支付类型code
            'payTypeIdentity' => $payType,          // 支付类型string

            'clientType' => $this->clientType,        // 客户端类型
            'accountId' => $this->memberId,       // 充值账号ID(会员ID)
            'openid' => $openid,                           // 会员OPENID

            'orderId' => $order->id,                     // 订单ID(充值记录ID)
            'orderNo' => $order->order_no,        // 订单编号(充值单号)
            'orderPrice' => $order->pay_price,     // 订单金额(充值金额)

            'callbackUrl' => RequestHelper::post('return_url'),     // 回调URL
        ])->unify();

        return $this->result(['data' => $result['pay_params']['pay_url'] ?? $result['pay_params']]);
    }

    /**
     * 我的优惠券的列表
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionMy()
    {
        $state = RequestHelper::get('status', 1);
        $andWhere = [];
        $orderBy = [];
        // 未使用
        if ($state == CouponConstant::COUPON_LIST_TYPE_NORMAL) {
            $andWhere[] = [
                'and',
                ['order_id' => 0],
                ['status' => 0],
                ['>', 'end_time', DateTimeHelper::now()],
            ];
            $orderBy['created_at'] = SORT_DESC;
        } else if ($state == CouponConstant::COUPON_LIST_TYPE_USED) {
            // 已使用
            $andWhere[] =
                [
                    'or',
                    ['>', 'order_id', 0],
                    ['status' => 1],
                ];
            $orderBy['used_time'] = SORT_DESC;
        } else if ($state == CouponConstant::COUPON_LIST_TYPE_EXPIRE) {
            // 已过期
            $andWhere[] = [
                'and',
                ['<', 'end_time', DateTimeHelper::now()],
                [
                    'or',
                    ['order_id' => 0],
                    ['status' => 0],
                ],
            ];
            $orderBy['end_time'] = SORT_DESC;
        }

        $params = [
            'where' => [
                'member_id' => $this->memberId,
            ],
            'andWhere' => $andWhere,
            'with' => 'coupon',
            'orderBy' => $orderBy,
        ];

        $list = CouponMemberModel::getColl($params, [
            'callable' => function (&$row) {
                // 时间展示
                if ($row['coupon']['time_limit'] == CouponTimeLimitConstant::COUPON_TIME_LIMIT_TIME) {
                    $row['time_content'] = date('Y-m-d', strtotime($row['start_time'])) . '~' . date('Y-m-d',
                            strtotime($row['end_time']));
                }
                if ($row['coupon']['time_limit'] == CouponTimeLimitConstant::COUPON_TIME_LIMIT_DAY) {
                    $row['time_content'] = '领取日内' . $row['coupon']['limit_day'] . '天内有效';
                }
                unset($row['coupon']);
            }
        ]);

        return $this->result($list);
    }

    /**
     * 获取总数
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionTotal()
    {
        $status = [
            'normal' => CouponConstant::COUPON_LIST_TYPE_NORMAL,
            'used' => CouponConstant::COUPON_LIST_TYPE_USED,
            'expired' => CouponConstant::COUPON_LIST_TYPE_EXPIRE
        ];
        $result = [];
        foreach ($status as $key => $v) {
            $result[$key] = CouponMemberModel::getTotal($this->memberId, $v);
        }
        return $this->result(['data' => $result]);
    }

    /**
     * 我的优惠券的详情
     * @param int $id
     * @param int $coupon_id
     * @return array|\yii\web\Response
     * @throws CouponException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCouponDetail(int $id, int $coupon_id)
    {
        $log = CouponMemberModel::findOne(['id' => $id, 'coupon_id' => $coupon_id])->toArray();
        if (empty($log)) {
            throw new CouponException(CouponException::COUPON_NOT_EXISTS);
        }
        $coupon = CouponModel::findOne(['id' => $log['coupon_id']])->toArray();
        if ($coupon['coupon_sale_type'] == 1) {
            $coupon['content'] = '满' . $coupon['enough'] . '满' . $coupon['discount_price'];
        } else {
            // 打折类型
            $coupon['content'] = '满' . $coupon['enough'] . '享' . $coupon['discount_price'] . '折';
        }

        if ($coupon['stock_type'] == 1) {
            $coupon['surplus'] = $coupon['stock'] - $coupon['get_total'];
        }

        // 领取方式
        if ($coupon['is_free'] == 1) {
            $row['pick_way'] = 1;
        } else if ($coupon['pick_type'] == 0 && $coupon['is_free'] == 1) {
            $row['pick_way'] = 2;
        } else if ($coupon['pick_type'] == 1) {
            $row['pick_way'] = 3;
        }
        if ($coupon['default_description'] == 1) {
            $coupon['$coupon'] = ShopSettings::get('sale.coupon.set');
        }
        $merge = array_merge($log, $coupon);
        return $this->result(['data' => $merge]);
    }

    /**
     * 检查支付状态
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCheck()
    {
        $id = RequestHelper::post('id');
        $result = CouponLogModel::find()
            ->where(['id' => $id])
            ->andWhere(['>', 'status', 0])
            ->one();
        if ($result === null) {
            return $this->error('支付还未成功', -1);
        }
        return $this->success();
    }

}
