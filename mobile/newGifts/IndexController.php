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

namespace shopstar\mobile\newGifts;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\exceptions\newGifts\NewGiftsException;
use shopstar\models\member\MemberModel;
use shopstar\models\newGifts\NewGiftsActivityModel;
use shopstar\models\newGifts\NewGiftsLogModel;
use shopstar\models\order\OrderModel;

/**
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends BaseMobileApiController
{
    public $configActions = [
        'allowSessionActions' => [
            'un-login'
        ],
        'allowNotLoginActions' => [
            'un-login'
        ]
    ];

    /**
     * 未登录用户
     * 检查有无新人送礼活动
     * @throws NewGiftsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUnLogin()
    {
        $activity = NewGiftsActivityModel::getOpenActivity($this->clientType);
        if (is_error($activity)) {
            throw new NewGiftsException(NewGiftsException::UN_LOGIN_ACTIVITY_NO_EXISTS);
        }
        return $this->result($activity);

    }

    /**
     * 检测用户是否可领新人礼
     * @throws NewGiftsException|\yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSendNew()
    {
        // 查找当前执行的活动 过滤后的 (比如 无效优惠券)
        $activity = NewGiftsActivityModel::getOpenActivity($this->clientType);
        if (is_error($activity)) {
            throw new NewGiftsException(NewGiftsException::CHECK_NEW_ACTIVITY_NO_EXISTS);
        }
        // 检查当前用户是否是新用户
        // 无消费记录
        if ($activity['pick_type'] == 0) {
            $isExpend = OrderModel::find()->where(['member_id' => $this->memberId])->andWhere(['<>', 'pay_type', '0'])->exists();
            if ($isExpend) {
                throw new NewGiftsException(NewGiftsException::SEND_NEW_MEMBER_IS_EXPEND);
            }
        } else {
            // 新注册用户
            $member = MemberModel::findOne(['id' => $this->memberId]);
            if ($member->created_at < $activity['start_time']) {
                throw new NewGiftsException(NewGiftsException::SEND_NEW_MEMBER_IS_NOT_NEW);
            }
        }

        // 检查用户是否参与过活动
        $isExists = NewGiftsLogModel::find()
            ->where(['member_id' => $this->memberId])
            ->exists();
        if ($isExists) {
            throw new NewGiftsException(NewGiftsException::SEND_NEW_MEMBER_IS_JOIN);
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        // 可以发
        $res = NewGiftsLogModel::sendGifts($this->memberId, $activity, $this->clientType);
        if (is_error($res)) {
            $transaction->rollBack();
            throw new NewGiftsException(NewGiftsException::SEND_NEW_MEMBER_FAIL);
        }
        $transaction->commit();

        return $this->result($activity);

    }

}