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

namespace shopstar\config\apps\poster;

use shopstar\bases\module\BasePluginProcessor;
use shopstar\components\payment\PayComponent;
use shopstar\components\wechat\helpers\OfficialAccountUserInfo;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\poster\PosterPushTypeConstant;
use shopstar\constants\poster\PosterTypeConstant;
use shopstar\exceptions\poster\PosterResponseException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;
use shopstar\helpers\QueueHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\StringHelper;
use shopstar\interfaces\PluginProcessorInterface;
use shopstar\jobs\components\responseComponents\WechatMessageJob;
use shopstar\models\commission\CommissionRelationModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\poster\PosterAttentionModel;
use shopstar\models\poster\PosterLogModel;
use shopstar\models\poster\PosterModel;
use shopstar\models\poster\PosterQrModel;
use shopstar\models\poster\PosterScanModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\core\attachment\CoreAttachmentService;
use shopstar\services\sale\CouponService;

/**
 * 插件处理器
 * Class PluginProcessor
 * @package apps\poster\config
 */
class PluginResponse extends BasePluginProcessor implements PluginProcessorInterface
{

    /**
     * @var string 事件类型 text scan subscribe
     */
    public $type;

    /**
     * @var array 消息体
     */
    public $message;

    /**
     * @var string 海报ID
     */
    public $poster_id;

    /**
     * @var string 关注者openid
     */
    public $sub_openid;

    /**
     * @var string 推荐者openid
     */
    public $rec_openid;

    /**
     * @var int 关注者
     */
    public $sub_member;

    /**
     * @var int 推荐者
     */
    public $rec_member;

    /**
     * @var object 关注设置
     */
    public $attentionOpt;


    /**
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function respond()
    {
        // 初始化
        $this->init();
        if ($this->type == 'text' || $this->type == 'CLICK') {
            // 判断海报是否在有效期内
            $poster = PosterModel::find()
                ->where([
                    'and',
                    ['id' => $this->poster_id],
                    ['type' => PosterTypeConstant::POSTER_TYPE_ATTENTION],
                    ['status' => 1],
                    ['is_deleted' => 0],
                    ['<', 'expire_start_time', DateTimeHelper::now()],
                    ['>', 'expire_end_time', DateTimeHelper::now()]
                ])
                ->first();

            if (empty($poster)) {
                // 没有符合要求的海报 直接返回
                return true;
            }

            // 判断用户是否是注册用户
            $loginUser = MemberWechatModel::find()->where(['openid' =>
                $this->message['fromusername'], 'is_deleted' => 0])->first();
            if (empty($loginUser)) {
                $sendMessage = '您还不是商城会员，请先注册会员，在获取海报 <a href=\'' . ShopUrlHelper::wap('/kdxOthers/followPoster', [], true) . "'>点击获取</a>";
            } else {
                $sendMessage = '感谢您的支持与陪伴，请点击获取商城海报<a href= \'' . ShopUrlHelper::wap('/kdxOthers/followPoster', [], true) . "'>点击获取</a>";
            }

            QueueHelper::push(new WechatMessageJob([
                'job' => [
                    'type' => PosterPushTypeConstant::POSTER_PUSH_TYPE_TEXT, //文字
                    'openid' => $this->sub_openid,
                    'message' => $sendMessage
                ]
            ]));

            return true;
        }

        // 获取奖励配置
        $attentionOpt = PosterAttentionModel::find()->where([
            'poster_id' => $this->poster_id
        ])->first();

        $this->attentionOpt = $attentionOpt;

        // 根据ticket获取sub_openid
        if (empty($this->message['ticket'])) {
            return error('Ticket Empty');
        }

        // 根据ticket获取推荐者openid
        $posterQr = PosterQrModel::find()->where([
            'ticket' => $this->message['ticket']
        ])->select('openid')->first();

        if (empty($posterQr['openid'])) {
            return error('Poster Qr Openid Empty');
        }
        // 推荐者
        $this->rec_openid = $posterQr['openid'];

        $tr = \Yii::$app->db->beginTransaction();

        try {
            // 初始化member
            $this->initMember();

            // 扫码
            if ($this->type == 'SCAN') {
                $scan = new PosterScanModel();
                // 插入扫码记录
                $scanAttr = [
                    'poster_id' => $this->poster_id,
                    'openid' => $this->rec_openid, //推荐者
                    'from_openid' => $this->sub_openid, //关注者
                ];
                $scan->setAttributes($scanAttr);
                $scan->save();
                // 扫码数+1
                PosterModel::updateAllCounters(['scans' => '1'], ['id' => $this->poster_id]);
            }

            // 关注
            if ($this->type == 'subscribe') {
                // 发送奖励
                $this->award();
                // 关注数+1
                PosterModel::updateAllCounters(['follows' => '1'],
                    ['id' => $this->poster_id]);
            }

            $tr->commit();
        } catch (\Throwable $e) {
            $tr->rollBack();
            return error($e->getMessage());
        }
        // 处理上下线
        $inviterId = $this->rec_member;
        // 有邀请人
        if (!empty($inviterId)) {
            CommissionRelationModel::handle($this->sub_member, $inviterId);
        }

        // 组装推送
        $sendMessage = $this->attentionOpt['type'] == PosterPushTypeConstant::POSTER_PUSH_TYPE_IMAGE ? [
            [
                'title' => $this->attentionOpt['title'],
                'description' => $this->attentionOpt['description'],
                'image' => CoreAttachmentService::getRoot() . $this->attentionOpt['thumb'],
                'url' => StringHelper::exists($this->attentionOpt['url'], ['http://', 'https://'], StringHelper::SEL_OR) ? $this->attentionOpt['url'] : ShopUrlHelper::wap($this->attentionOpt['url'], [], true),
            ]
        ] : $this->attentionOpt['description'];

        QueueHelper::push(new WechatMessageJob([
            'job' => [
                'type' => $this->attentionOpt['type'],
                'openid' => $this->sub_openid,
                'message' => $sendMessage
            ]
        ]));

        return true;
    }

    /**
     * 初始化参数
     * @author 青岛开店星信息技术有限公司
     */
    private function init()
    {
        // fromusername 关注者 sub
        // 推荐者 rec
        // 赋值openid
        $this->sub_openid = $this->message['fromusername'];// 关注者

        // 赋值海报ID
        $this->poster_id = $this->rule_id;
    }

    private function initMember()
    {
        // 校验sub_openid是否注册
        // 获取微信用户信息
        $subMember = MemberWechatModel::find()->where([
            'openid' => $this->sub_openid,
            'is_deleted' => 0
        ])->select('member_id')->column();
        $subMemberId = $subMember[0];
        if (!$subMemberId) {
            $original = OfficialAccountUserInfo::getUserInfo($this->sub_openid);
            $userInfo = MemberWechatModel::checkMember($original, ClientTypeConstant::CLIENT_WECHAT);

            $subMemberId = $userInfo['id'];
        }

        // 获取推荐member
        $recMember = MemberWechatModel::find()->where([
            'openid' => $this->rec_openid,
            'is_deleted' => 0
        ])->select('member_id')->column();

        // 获取用户memberId
        $this->sub_member = $subMemberId;
        $this->rec_member = $recMember[0];
    }

    /**
     * 发放奖励
     * @return bool
     * @throws PosterResponseException
     * @author 青岛开店星信息技术有限公司
     */
    private function award()
    {

        // 判断奖励开启状态
        if (!$this->attentionOpt['status']) {
            return true;
        }

        // 推荐人和关注人相同
        if ($this->rec_openid == $this->sub_openid) {
            return true;
        }

        // 判断奖励是否发放
        $awardExists = PosterLogModel::find()->where(
            [
                'and',
                ['poster_id' => $this->poster_id],
                [
                    'or',
                    [
                        'and',
                        ['openid' => $this->rec_openid],
                        ['from_openid' => $this->sub_openid]
                    ],

                    [
                        'and',
                        ['openid' => $this->sub_openid],
                        ['from_openid' => $this->rec_openid]
                    ]
                ]
            ]
        )->exists();

        if ($awardExists) {
            return true;
        }

        // 获取自然月开始结束时间
        $monthDate = DateTimeHelper::getMonthDate();

        // 获取奖励历史记录
        $recLog = PosterLogModel::find()
            ->where([
                'and',
                ['openid' => $this->rec_openid],
                ['>', 'created_at', $monthDate[0]],
                ['<', 'created_at', $monthDate[1]]
            ])
            ->select([
                "sum(rec_credit) as rec_credit",
                "sum(rec_cash) as rec_cash",
                "SUM(IF(rec_coupon > 0,1,0)) as rec_coupon"
            ])
            ->asArray()->first();

        // 初始化奖励
        $awardRecCredit = 0;
        $awardRecCash = 0;
        $awardRecCoupon = 0;
        $awardSubCredit = 0;
        $awardSubCash = 0;
        $awardSubCoupon = 0;


        /********** 推荐 **********/

        // 推荐积分
        if ($this->attentionOpt['rec_credit_enable']) {
            // 历史奖励积分与积分上限比较
            if (intval($recLog['rec_credit']) < $this->attentionOpt['rec_credit_limit']) {
                // 奖励积分与原有积分与积分上限比较
                $creditGap = intval($this->attentionOpt['rec_credit_limit']) - intval($recLog['rec_credit']) - intval($this->attentionOpt['rec_credit']);
                if ($creditGap >= 0) {
                    $awardRecCredit = $this->attentionOpt['rec_credit'];
                } else {
                    $awardRecCredit = intval($this->attentionOpt['rec_credit_limit']) - intval($recLog['rec_credit']);
                }
            }

            //修改积分
            if ($awardRecCredit > 0) {
                $awardRecCreditRes = MemberModel::updateCredit($this->rec_member, $awardRecCredit, 0,
                    'credit', 1, '海报赠送积分', MemberCreditRecordStatusConstant::CREDIT_STATUS_SEND_POSTER);
                if (is_error($awardRecCreditRes)) {
                    LogHelper::error('[Plugin Response Rec Credit Res]', $awardRecCreditRes);
                    $awardRecCredit = 0;
                }
            }
        }


        // 推荐现金
        if ($this->attentionOpt['rec_cash_enable']) {
            // 历史奖励现金与现金上限比较
            if (round2($recLog['rec_cash']) < round2($this->attentionOpt['rec_cash_limit'])) {
                // 奖励积分与原有积分与积分上限比较
                $cashGap = round2($this->attentionOpt['rec_cash_limit']) - round2($recLog['rec_cash']) - round2($this->attentionOpt['rec_cash']);
                if ($cashGap >= 0) {
                    $awardRecCash = $this->attentionOpt['rec_cash'];
                } else {
                    $awardRecCash = round2($this->attentionOpt['rec_cash_limit']) - round2($recLog['rec_cash']);
                }
            }
            // 修改现金
            if ($awardRecCash > 0) {
                // 余额
                if ($this->attentionOpt['rec_cash_type'] == 1) {
                    $awardRecCashRes = MemberModel::updateCredit($this->rec_member, $awardRecCash, 0,
                        'balance', 1, '海报赠送余额', MemberCreditRecordStatusConstant::BALANCE_STATUS_POSTER_SEND);
                    if (is_error($awardRecCashRes)) {
                        LogHelper::error('[Plugin Response Rec Cash Res]', $awardRecCashRes);
                        $awardRecCash = 0;
                    }
                    // 红包
                } else {
                    $awardRecCashRes = $this->transfer($awardRecCash, $this->rec_openid);
                    if (is_error($awardRecCashRes)) {
                        LogHelper::error('[Plugin Response Rec Cash Res]', $awardRecCashRes);
                        $awardRecCash = 0;
                    }
                }
            }
        }

        // 推荐优惠券
        if ($this->attentionOpt['rec_coupon_enable']) {
            // 历史奖励积分与积分上限比较
            if ($recLog['rec_coupon'] < $this->attentionOpt['rec_coupon_limit']) {
                $awardRecCoupon = $this->attentionOpt['rec_coupon'];

                // 发送优惠券
                $recCoupon = CouponService::checkReceive($this->rec_member, $awardRecCoupon);
                if (is_error($recCoupon)) {
                    LogHelper::error('[Plugin Response Rec Coupon Res]', $recCoupon);
                    $awardRecCoupon = 0;
                } else {
                    $awardRecCouponRes = CouponModel::activitySendCoupon($this->rec_member, $recCoupon);
                    if (is_error($awardRecCouponRes)) {
                        LogHelper::error('[Plugin Response Award Rec Coupon Res]', $awardRecCouponRes);
                        $awardRecCoupon = 0;
                    }
                }

            }
        }

        /********** 关注 **********/

        // 关注积分
        if ($this->attentionOpt['sub_credit_enable']) {
            $awardSubCredit = $this->attentionOpt['sub_credit'];
            if ($awardSubCredit > 0 && $this->sub_member) {
                $awardSubCreditRes = MemberModel::updateCredit($this->sub_member, $awardSubCredit, 0,
                    'credit', 1, '海报赠送积分', MemberCreditRecordStatusConstant::CREDIT_STATUS_SEND_POSTER);
                if (is_error($awardSubCreditRes)) {
                    LogHelper::error('[Plugin Response Sub Credit Res]', $awardSubCreditRes);
                    $awardSubCredit = 0;
                }
            }
        }

        // 关注现金
        if ($this->attentionOpt['sub_cash_enable']) {
            $awardSubCash = round2($this->attentionOpt['sub_cash']);
            // 修改现金余额红包
            if ($awardSubCash > 0 && $this->sub_member) {
                // 余额
                if ($this->attentionOpt['sub_cash_type'] == 1) {
                    $awardSubCashRes = MemberModel::updateCredit($this->sub_member, $awardSubCash, 0,
                        'balance', 1, '海报赠送余额', MemberCreditRecordStatusConstant::BALANCE_STATUS_POSTER_SEND);
                    if (is_error($awardSubCashRes)) {
                        LogHelper::error('[Plugin Response Sub Cash Res]', $awardSubCashRes);
                        $awardSubCash = 0;
                    }
                    // 红包
                } else {
                    $awardSubCashRes = $this->transfer($awardSubCash, $this->sub_openid);
                    if (is_error($awardSubCashRes)) {
                        LogHelper::error('[Plugin Response Sub Cash Res]', $awardSubCashRes);
                        $awardSubCash = 0;
                    }

                }
            }
        }

        // 关注优惠券
        if ($this->attentionOpt['sub_coupon_enable']) {
            // 发送优惠券
            if ($this->sub_member) {
                $awardSubCoupon = $this->attentionOpt['sub_coupon'];
                $subCoupon = CouponService::checkReceive($this->sub_member, $awardSubCoupon);
                if (is_error($subCoupon)) {
                    LogHelper::error('[Plugin Response Sub Coupon Res]', $subCoupon);
                    $awardSubCoupon = 0;
                } else {
                    $awardSubCouponRes = CouponModel::activitySendCoupon($this->sub_member, $subCoupon);
                    if (is_error($awardSubCouponRes)) {
                        LogHelper::error('[Plugin Response Award Sub Coupon Res]', $awardSubCouponRes);
                        $awardSubCoupon = 0;
                    }
                }
            }
        }

        /********** 记录日志 **********/

        $posterLog = new PosterLogModel();

        $posterLogAttr = [
            'poster_id' => $this->poster_id,
            'openid' => $this->rec_openid,//推荐
            'from_openid' => $this->sub_openid,//关注
            'sub_credit' => $awardSubCredit,
            'sub_cash' => $awardSubCash,
            'sub_coupon' => $awardSubCoupon,
            'rec_credit' => $awardRecCredit,
            'rec_cash' => $awardRecCash,
            'rec_coupon' => $awardRecCoupon
        ];

        $posterLog->setAttributes($posterLogAttr);

        $posterLog->save();

        return true;
    }

    private function transfer($fee, $openid)
    {

        $settings = ShopSettings::get('sysset.payment.payset');
        if ($settings['pay_type_withdraw'] != 2) {
            // 提现不是红包方式
            return error('未开启红包提现方式');
        }
        $config = [
            'transfer_fee' => $fee,
            'transfer_desc' => '海报奖励',
            'transfer_type' => 20,
            //'order_no' => $apply->apply_no,
            'client_type' => 20,
            'withdraw_order_type' => 30
        ];

        $config['openid'] = $openid;

        $payDriver = PayComponent::getInstance($config);

        try {
            $result = $payDriver->transfer();
            if (is_error($result)) {
                return $result;
            }

            return true;
        } catch (\Throwable $e) {
            return error($e->getMessage());
        }
    }

}