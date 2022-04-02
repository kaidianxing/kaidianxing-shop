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

namespace shopstar\mobile\commission;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\exceptions\commission\CommissionAgentException;
use shopstar\exceptions\commission\CommissionSetException;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionSettings;

/**
 * 分销应用客户端接口基类
 * Class CommissionClientApiController
 * @package shopstar\mobile\commission
 * @author 青岛开店星信息技术有限公司
 */
class CommissionClientApiController extends BaseMobileApiController
{

    /**
     * @var array 分销商信息
     */
    public $agent = [];

    /**
     * @var array 允许访问的分销商Action
     */
    public $allowAgentActions = [];

    /**
     * @param $action
     * @return bool|void
     * @throws CommissionAgentException
     * @throws CommissionSetException
     * @throws \shopstar\bases\exception\BaseApiException
     * @throws \shopstar\exceptions\ChannelException
     * @throws \shopstar\exceptions\member\MemberException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @author likexin
     */
    public function beforeAction($action)
    {
        parent::beforeAction($action);

        // 是否开启分销
        $set = CommissionSettings::get('set.commission_level');
        if (empty($set)) {
            throw new CommissionSetException(CommissionSetException::COMMISSION_IS_CLOSE);
        }
        // 允许访问Action，跳过
        if (in_array('*', $this->allowAgentActions) || in_array($action->id, $this->allowAgentActions)) {
            return true;
        }

        // 查询分销商

        $this->agent = CommissionAgentModel::find()
            ->where([
                'member_id' => $this->memberId,
            ])
            ->select(['member_id', 'status', 'is_black', 'agent_id', 'level_id', 'commission_pay', 'commission_total', 'ladder_commission_total', 'become_time'])
            ->first();

        // 验证是否是分销商
        $isAgent = !empty($this->agent) && $this->agent['status'] > 0 && $this->agent['is_black'] == 0;
        if (!$isAgent) {
            throw new CommissionAgentException(CommissionAgentException::MEMBER_NOT_IS_AGENT);
        }

        return true;
    }

}