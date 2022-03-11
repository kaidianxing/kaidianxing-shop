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

namespace shopstar\models\order\create\handler;

use shopstar\exceptions\order\OrderCreatorException;
use shopstar\models\member\MemberModel;
use shopstar\models\order\create\interfaces\HandlerInterface;
use shopstar\models\order\create\OrderCreatorKernel;

class MemberHandler implements HandlerInterface
{

    /**
     * 订单实体类
     * @author 青岛开店星信息技术有限公司
     * @var OrderCreatorKernel 当前订单类的实体，里面包含了关于当前你所需要的所有内容
     */
    public $orderCreatorKernel;

    public function __construct(OrderCreatorKernel &$orderCreatorKernel)
    {
        $this->orderCreatorKernel = $orderCreatorKernel;
    }

    /**
     * 订单业务核心处理器标准
     *
     * 请注意：请不要忘记处理完成之后需要挂载到订单实体类下，请不要随意删除在当前挂载属性以外的属性
     * @return mixed
     * @throws OrderCreatorException
     * @author 青岛开店星信息技术有限公司
     */
    public function processor()
    {
        $this->orderCreatorKernel->member = MemberModel::find()->where([
            'id' => $this->orderCreatorKernel->memberId,
        ])->asArray()->select([
            'id',
            'mobile',
            'avatar',
            'nickname',
            'realname',
            'balance',
            'level_id',
            'credit'
        ])->one();

        if (empty($this->orderCreatorKernel->member)) {
            throw new OrderCreatorException(OrderCreatorException::ORDER_CREATOR_KERNEL_MEMBER_HANDLER_MEMBER_NOT_FOUND_ERROR);
        }

        // 将获取的会员信息赋值到订单数据
        $this->orderCreatorKernel->orderData['member_id'] = $this->orderCreatorKernel->member['id'];
        $this->orderCreatorKernel->orderData['member_nickname'] = $this->orderCreatorKernel->member['nickname'];
        $this->orderCreatorKernel->orderData['member_realname'] = $this->orderCreatorKernel->member['realname'];
        $this->orderCreatorKernel->orderData['member_mobile'] = $this->orderCreatorKernel->member['mobile'];
        $this->orderCreatorKernel->orderData['member_avatar'] = $this->orderCreatorKernel->member['avatar'];
        return;
    }
}
