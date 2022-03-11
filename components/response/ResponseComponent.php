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

namespace shopstar\components\response;

use shopstar\components\response\bases\ResponseChannelConstant;
use shopstar\components\response\bases\ResponseChannelInterface;
use shopstar\helpers\LogHelper;
use shopstar\models\core\CoreRuleModel;
use shopstar\models\wechat\WechatRuleKeywordModel;
use shopstar\models\wechat\WechatRuleModel;
use Yii;
use yii\base\Component;

/**
 * 智能应答组件
 * Class ResponseComponent
 * @package shopstar\components
 */
class ResponseComponent extends Component
{

    /**
     * @var ResponseChannelInterface 存储驱动接口
     */
    private static $instance = null;

    /**
     * @var string 实例应答渠道类型
     */
    private static $channel = null;

    /**
     * 事件对应模块
     * @var array
     */
    private static $channerMap = [
        ResponseChannelConstant::CHANNEL_OFFICIAL_ACCOUNT => 'wechat'
    ];

    /**
     * 获取实例
     * @param string $channel
     * @param array $config
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInstance(string $channel, array $config = [])
    {

        if (is_null(self::$instance) || self::$channel != $channel) {
            $channel = strtolower($channel);

            // 获取实现类
            $class = ResponseChannelConstant::getClass($channel);

            if (!$class) {
                return error("`{$channel}` Response Channel not Found.");
            }

            // 独立版插件转发
            if ($config['type'] == 'subscribe') {
                // 海报关注事件
                $rule = WechatRuleModel::getEvent($config['type'], 'poster', $config['message']['message']['EventKey']);

                $rule[]['type'] = self::$channerMap[ResponseChannelConstant::CHANNEL_OFFICIAL_ACCOUNT];
            } elseif ($config['type'] == 'unsubscribe') {

                $rule[]['type'] = self::$channerMap[ResponseChannelConstant::CHANNEL_OFFICIAL_ACCOUNT];
            } elseif ($config['type'] == 'SCAN') {

                // 海报关注事件
                $rule = WechatRuleModel::getEvent($config['type'], 'poster', $config['message']['message']['EventKey']);
                if (empty($rule)) {
                    return error("Response Rule not Found.");
                }
            } else if ($config['type'] == 'CLICK') { //点击事件

                $rule = WechatRuleKeywordModel::getKeywordList($config['message']['message']['EventKey']) ?? [];
            } else {
                $rule = WechatRuleKeywordModel::getKeywordList($config['message']['content']) ?? [];
            }

            if (empty($rule)) {
                return error("Response Rule not Found.");
            }

            // 去除相同rule_id 和 containtype 的数据, 避免respond时循环造成多次调用
            $processRule = [];
            foreach ($rule as $item) {
                $index = $item['rule_id'] . '_' . $item['containtype'];
                if (isset($processRule[$index])) {
                    continue;
                }
                $processRule[$index] = $item;
            }
            $rule = array_values($processRule);


            if (is_array($rule)) {
                foreach ($rule as $value) {
                    // 注入固定参数
                    $config = array_merge($config, [
                        'class' => $class,
                        'plugin_type' => $value['type'],
                        'rule_id' => $value['rule_id']
                    ]);

                    // 注入实现类
                    self::$instance = Yii::createObject($config);
                    self::$channel = $channel;
                    unset($config['class']);
                    self::$instance->respond();
                }
            }
        }

        return true;
    }

}