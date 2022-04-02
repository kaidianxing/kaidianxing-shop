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

namespace shopstar\components\notice\sends;

/**
 * 发送消息通知接口
 * Interface SendMessageInterface
 * @author 青岛开店星信息技术有限公司
 */
interface SendNoticeInterface
{

    /**
     * 发送前处理（包括参数验证，模板验证）
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function sendBefore(): bool;

    /**
     * 组成模板所需字段
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function makeTemplateField();

    /**
     * 组成发送会员
     * @param array $toUser
     * @param bool $appointTemplateMember
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function makeToUser(array $toUser, bool $appointTemplateMember);

    /**
     * 消息发送（主要处理发送，最好不要有别的操作）
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function sendMessage();
}
