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

use shopstar\components\notice\bases\SendBase;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\components\noticeComponents\WxappMessageJob;
use shopstar\models\notice\NoticeWxappTemplateModel;

/**
 * 小程序信息发送
 * Class SendWxappNotice
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\notice\sends
 */
class SendWxappNotice extends SendBase implements SendNoticeInterface
{

    /**
     * 发送数据
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $messageData;

    /**
     * 模板id
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $templateId;

    /**
     * 附加参数
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $options = [];

    /**
     * 模板
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $template;

    /**
     * 要发送的用户
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $toUser;

    /**
     * 发送前处理（包括参数验证，模板验证）
     * @return bool
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function sendBefore(): bool
    {
        if (empty($this->templateId) || empty($this->messageData)) {
            throw new \Exception('Wxapp Notice Lack Params');
        }

        $this->template = NoticeWxappTemplateModel::findOne(['id' => $this->templateId]);
        if (empty($this->template)) {
            throw new \Exception('Wxapp Notice Template Not Found');
        }

        return true;
    }

    /**
     * 组成模板所需字段
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function makeTemplateField()
    {
        $this->messageData = $this->makeWechatTpl($this->template->content, $this->messageData);

        foreach ($this->messageData as $messageDataIndex => $messageDataItem) {
            $messageData[$messageDataItem['key']] = $messageDataItem['value'];
        }

        // 校验数据  订阅消息限制  只校验必要字段
        foreach ($messageData as $key => $value) {
            $subKey = mb_substr($key, 0, -1);
            switch ($subKey) {
                case 'thing': // 20个以内字符
                    if (mb_strlen($value) > 19) {
                        $messageData[$key] = mb_substr($value, 0, 17) . '...';
                    }
                    break;
                case 'character_string': // 32位以内数字、字母或符号
                    if (mb_strlen($value) > 31) {
                        $messageData[$key] = mb_substr($value, 0, 29) . '...';
                    }
                    break;
                case 'phrase': // 5个以内汉字
                    if (mb_strlen($value) > 4) {
                        $messageData[$key] = mb_substr($value, 0, 5);
                    }
                    break;
                case 'name': // 10个以内汉字
                    if (mb_strlen($value) > 9) {
                        $messageData[$key] = mb_substr($value, 0, 10);
                    }
                    break;
                case 'number': //整形
                    $messageData[$key] = intval($value);
                    break;
            }
        }

        //重新赋值 messagedata
        $this->messageData = $messageData;
    }

    /**
     * 组成发送会员
     * @param array $toUser
     * @param bool $appointTemplateMember
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function makeToUser(array $toUser, bool $appointTemplateMember)
    {
        $this->toUser = array_column(array_column($toUser, 'wxappMember'), 'openid');
        if (empty($this->toUser)) {
            return false;
        }

        return true;
    }

    /**
     * 消息发送（主要处理发送，最好不要有别的操作）
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function sendMessage()
    {
        foreach ((array)$this->toUser as $item) {
            if (empty($item)) {
                continue;
            }

            QueueHelper::push(new WxappMessageJob([
                'job' => [
                    'touser' => $item,
                    'template_id' => $this->template->pri_tmpl_id,
                    'data' => $this->messageData,
                    'page' => $this->options['pagepath'] ?: '',
                    'form_id' => $this->options['form_id'],
                ]
            ]));
        }

        return true;
    }

}
