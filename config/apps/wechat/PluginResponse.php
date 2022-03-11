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

namespace shopstar\config\apps\wechat;

use shopstar\bases\module\BasePluginProcessor;
use shopstar\constants\poster\PosterPushTypeConstant;
use shopstar\helpers\LogHelper;
use shopstar\helpers\QueueHelper;
use shopstar\interfaces\PluginProcessorInterface;
use shopstar\jobs\components\responseComponents\WechatMessageJob;
use shopstar\models\wechat\WechatFansModel;
use shopstar\models\wechat\WechatRuleModel;

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
     * @var array 配置项
     */
    public $messageData;

    /**
     * @var string 关注者openid
     */
    public $from_openid;

    /**
     * 类型的映射
     * @var array
     */
    private static $typeMap = [
        'text' => PosterPushTypeConstant::POSTER_PUSH_TYPE_TEXT,
        'images' => PosterPushTypeConstant::POSTER_PUSH_TYPE_IMAGES,
    ];

    /**
     * 根据类型的消息取值映射
     * @var array
     */
    private static $messageMap = [
        PosterPushTypeConstant::POSTER_PUSH_TYPE_TEXT => 'content',
        PosterPushTypeConstant::POSTER_PUSH_TYPE_IMAGES => 'media_id',
    ];

    /**
     * 开始处理
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function respond()
    {
        // 初始化
        $this->init();
        $result = [];

        // 处理事件
        if ($this->messageData['MsgType'] == 'event') {
            switch ($this->messageData['Event']) {
                case 'subscribe':

                    try {
                        //触发用户事件
                        $result = WechatFansModel::changeFollowStatus($this->from_openid);

                        if (is_error($result)) {
                            throw new \Exception($result['message']);
                        }
                    } catch (\Exception $exception) {
                        LogHelper::error('[WECHAT SUBSCRIBE FANS ERROR]', [
                            'message' => $exception->getMessage()
                        ]);
                    }

                    // 查询关注公众号的事件
                    $result = WechatRuleModel::getEvent($this->type);
                    break;
                // 之后可以增添的事件
                case 'unsubscribe': //取消关注

                    try {
                        //触发用户事件
                        $result = WechatFansModel::changeFollowStatus($this->from_openid, false);

                        if (is_error($result)) {
                            throw new \Exception($result['message']);
                        }
                    } catch (\Exception $exception) {
                        LogHelper::error('[WECHAT UNSUBSCRIBE FANS ERROR]', [
                            'message' => $exception->getMessage()
                        ]);
                    }
                    break;
                case 'CLICK'://点击事件
                    $result = WechatRuleModel::getKeywordContent($this->messageData['EventKey']);
                    break;
            }
        }

        // 文本查询关键字
        if ($this->messageData['MsgType'] == 'text') {
            $result = WechatRuleModel::getKeywordContent($this->messageData['Content']);
        }

        // 拦截未知
        if (!$result) {
            return false;
        }

        if (is_array($result)) {
            // 处理单个关键词模糊匹配到多个规则中的情景
            foreach ($result as $value) {
                foreach ($value as $item) {

                    $type = self::$typeMap[$item['type']];
                    $sendMessage = $item[self::$messageMap[$type]];

                    QueueHelper::push(new WechatMessageJob([
                        'job' => [
                            'type' => $type,
                            'openid' => $this->from_openid,
                            'message' => $sendMessage
                        ]
                    ]));
                }
            }
        }

        return true;
    }

    /**
     * 初始化
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    private function init()
    {
        // 赋值openid
        $this->from_openid = $this->message['fromusername'];// 关注者

        // 拆分配置
        $this->messageData = $this->message['message'];

    }
}