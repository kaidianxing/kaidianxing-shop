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

namespace shopstar\mobile\notify;


use shopstar\bases\controller\BaseMobileApiController;
use shopstar\components\response\bases\ResponseChannelConstant;
use shopstar\components\response\ResponseComponent;
use shopstar\components\wechat\bases\WechatChannelConstant;
use shopstar\components\wechat\WechatComponent;
use shopstar\exceptions\ResponseException;
use shopstar\helpers\LogHelper;
 
use shopstar\helpers\RequestHelper;

/**
 * 消息回复
 * Class ResponseController
 * @package shop\client\notify
 */
class ResponseController extends BaseMobileApiController
{
    public $configActions = [
        'allowSessionActions' => [
            'index',
            'independent'
        ],
        'allowClientActions' => [
            'index',
            'independent'
        ],
        'allowNotLoginActions' => [
            'index',
            'independent'
        ],
        'allowHeaderActions' => [
            'independent'
        ]
    ];

    /**
     * @var array 回复消息体
     */
    public $message;


    public function actionIndex()
    {
        // 接受消息体
        $this->message = RequestHelper::post();
        // 兼容新版（旧版本二维数组带不过来）
        if (!empty($this->message['message_json'])) {
            $this->message = json_decode($this->message['message_json'], true);
        }

        if ($this->message['msgtype'] == 'event') {
            $type = $this->message['event'];
        } elseif ($this->message['msgtype'] == 'text') {
            $type = $this->message['type'];
        } else {
            return $this->result(ResponseException::getMessages(ResponseException::RESPONSE_MSG_TYPE_INVALID),
                ResponseException::RESPONSE_MSG_TYPE_INVALID);
        }

        // 获取智能应答实例
        try {

            $instance = ResponseComponent::getInstance(ResponseChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [
                'type' => $type,
                'message' => [
                    'content' => $this->message['content'], // 关键词
                    'tousername' => $this->message['tousername'],
                    'fromusername' => $this->message['fromusername'], // 扫码关注人
                    'ticket' => $this->message['ticket']  // ticket
                ],

            ]);
        } catch (\Throwable $e) {
            $instance = [];
            LogHelper::error('[Process Response Exception]' . $e->getMessage(), []);
        }

        if (is_error($instance)) {
            LogHelper::error('[Process Response Error]' . $instance['message'], []);
        }


        return $this->result();
    }

    /**
     * 独立版
     * @return array|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndependent()
    {
        // 实例化
        $instance = WechatComponent::getInstance(WechatChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [])->factory;
        // 验证
        if (RequestHelper::isGet()) {
            $instance->server->serve()->send();
            return $this->result();
        }
        if (RequestHelper::isPost()) {

            $instance->server->push(function ($message) use (&$type) {
                $this->message = $message;
                if ($this->message['MsgType'] == 'event') {
                    $type = $this->message['Event'];
                } elseif ($this->message['MsgType'] == 'text') {
                    $type = $this->message['MsgType'];
                } else {
                    $type = false;
                }
                return '';
            });

            $instance->server->serve()->send();

            if (!$type) {
                // 暂时不支持其余格式
                return $this->result(ResponseException::getMessages(ResponseException::RESPONSE_MSG_TYPE_INVALID), ResponseException::RESPONSE_MSG_TYPE_INVALID);
            }

            // 获取智能应答实例
            try {
                $instance = ResponseComponent::getInstance(ResponseChannelConstant::CHANNEL_OFFICIAL_ACCOUNT, [
                    'type' => $type,
                    'message' => [
                        'content' => $this->message['content'] ?? $this->message['Content'], // 关键词
                        'tousername' => $this->message['ToUserName'],
                        'fromusername' => $this->message['FromUserName'],
                        'ticket' => $this->message['ticket'] ?? $this->message['Ticket'],  // ticket
                        'message' => $this->message
                    ],

                ]);
            } catch (\Throwable $e) {
                $instance = [];
                LogHelper::error('[Process Response Exception]' . $e->getMessage(), []);
            }

            if (is_error($instance)) {
                LogHelper::error('[Process Response Error]' . $instance['message'], []);
            }
        }

        return $this->result();
    }
}