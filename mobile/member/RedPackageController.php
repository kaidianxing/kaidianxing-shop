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
use shopstar\components\payment\base\PayTypeConstant;
use shopstar\components\payment\base\WithdrawOrderTypeConstant;
use shopstar\components\payment\PayComponent;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\OrderNoHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberRedPackageModel;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * 红包
 * Class RedPackageController
 * @package shop\client\member
 * @author 青岛开店星信息技术有限公司.
 */
class RedPackageController extends BaseMobileApiController
{
    /**
     * 红包列表
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionList()
    {
        $list = MemberRedPackageModel::getColl([
            'where' => [
                'and',
                [
                    'member_id' => $this->memberId,
                    'status' => 0
                ],
                ['>', 'expire_time', DateTimeHelper::now()]
            ],
            'searchs' => [
                ['scene', 'int'],
                ['scene_id', 'int'],
            ],
            'orderBy' => [
                'created_at' => SORT_ASC
            ]
        ], [
            'pager' => false,
            'callable' => function (&$result) {
                $result['extend'] = Json::decode($result['extend']);
            }
        ]);

        return $this->result($list);
    }

    /**
     * 领取红包
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionGet()
    {
        $post = RequestHelper::post();
        if (empty($post['id'])) {
            return $this->error('缺少参数');
        }

        $tr = \Yii::$app->db->beginTransaction();

        try {

            $redPackage = MemberRedPackageModel::where([
                'and',
                [
                    'id' => $post['id'],
                    'status' => 0
                ],
                ['>', 'expire_time', DateTimeHelper::now()]
            ])->one();

            if (empty($redPackage)) {
                throw new \Exception('红包信息变更领取失败');
            }

            $orderNo = OrderNoHelper::getTransferNo($this->memberId, $redPackage['id'], WithdrawOrderTypeConstant::MEMBER_WITHDRAW);

            $extend = Json::decode($redPackage['extend']);

            // 微信或支付宝退款
            $config = [
                'member_id' => $this->memberId,
                'transfer_fee' => $redPackage['money'],
                'transfer_desc' => $extend['blessing'] ?: '恭喜您获得商城红包',
                'transfer_type' => PayTypeConstant::PAY_TYPE_WECHAT, // 20微信 30支付宝
                'client_type' => $this->clientType,
                'withdraw_order_type' => WithdrawOrderTypeConstant::MEMBER_WITHDRAW,
                'order_no' => $orderNo
            ];

            // 根据设置获取openid
            // 打款方式是红包  都获取公众号的openid  如果是转账 则根据提现账户打款
            // 获取设置
            $settings = ShopSettings::get('sysset.payment.payset');

            if ($settings['pay_type_withdraw'] != 1) {
                throw new \Exception('未开启企业打款，请联系管理员');
            }

            //先获取小程序openid
            $config['openid'] = MemberWxappModel::getOpenId($this->memberId);

            //如果小程序openid为空则获取公众号openid
            if (empty($config['openid'])) {
                $config['openid'] = MemberWechatModel::getOpenId($this->memberId);
            }

            if (empty($config['openid'])) {
                throw new \Exception('请确定已关联微信帐号');
            }

            /**
             * @var MemberRedPackageModel $redPackage
             */
            $redPackage->updated_at = DateTimeHelper::now();
            $redPackage->status = 1;
            if (!$redPackage->save()) {
                throw new \Exception($redPackage->getErrorMessage());
            }

            // 转账
            $payInstance = PayComponent::getInstance($config);
            $refundResult = $payInstance->transfer();
            if (is_error($refundResult)) {
                if ($refundResult['error'] == 135114) {
                    throw new \Exception('红包领取异常，请联系管理员');
                }
                throw new \Exception($refundResult['message']);
            }

            $tr->commit();

        } catch (\Exception $exception) {

            $tr->rollBack();
            return $this->error($exception->getMessage());
        }


        return $this->result();
    }

    /**
     * 我的红包列表
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionMyRedPackage()
    {
        $where = [
            'member_id' => $this->memberId,
            'status' => 1
        ];

        $list = MemberRedPackageModel::getColl([
            'where' => $where
        ], [
            'callable' => function (&$result) {
                $result['scene_text'] = MemberRedPackageModel::$sceneMap[$result['scene']];
            }
        ]);

        $list['total_money'] = MemberRedPackageModel::where($where)->sum('money');

        return $this->result($list);
    }
}