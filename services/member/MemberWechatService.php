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

namespace shopstar\services\member;

use shopstar\models\member\MemberWechatModel;
use shopstar\models\member\MemberWxappModel;

/**
 * 会员微信渠道服务
 */
class MemberWechatService
{

    // 渠道
    public const CHANNEL_OFFICE_ACCOUNT = 'office_account';

    // 小程序渠道
    public const CHANNEL_MINI_PROGRAM = 'mini_program';

    // PC渠道
    public const CHANNEL_PC = 'pc';

    /**
     * 通过unionid获取会员ID
     * @param string $unionId 开放平台的unionid
     * @param array $channels 传入渠道列表，按照数组顺序排序(找到前面的就停止)
     * @return int
     */
    public static function getMemberIdByUnionId(string $unionId, array $channels = []): int
    {
        // 遍历传入的渠道获取渠道的，member_id，根据数组的顺序开始查(数组的顺序就是优先级)，找到就停止
        foreach ($channels as $channel) {

            if ($channel == self::CHANNEL_OFFICE_ACCOUNT) {
                // 查询是否注册过公众号渠道
                $member = MemberWechatModel::find()
                    ->where([
                        'unionid' => $unionId,
                        'is_deleted' => 0,
                    ])
                    ->select('member_id')
                    ->one();

            } elseif ($channel == self::CHANNEL_MINI_PROGRAM) {
                // 查询是否注册过小程序渠道
                $member = MemberWxappModel::find()
                    ->where([
                        'unionid' => $unionId,
                        'is_deleted' => 0,
                    ])
                    ->select('member_id')
                    ->one();

            }
            // 如果渠道中有member_id，则直接返回
            if (!empty($member) && !empty($member->member_id)) {
                return $member->member_id;
            }
        }

        return 0;
    }

}