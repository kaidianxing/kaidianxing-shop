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

namespace shopstar\components\response\channels;

use shopstar\components\response\bases\BaseResponseChannel;
use shopstar\components\response\bases\ResponseChannelInterface;
use shopstar\components\response\bases\ResponsePluginTypeMapConstant;
use shopstar\components\wechat\helpers\OfficialAccountMessageTextHelper;
use shopstar\helpers\LogHelper;
use shopstar\constants\poster\PosterPushTypeConstant;
use Yii;

/**
 * 公众号渠道
 * Class OfficialAccountChannel
 * @package shopstar\components\response\channels
 */
class OfficialAccountChannel extends BaseResponseChannel implements ResponseChannelInterface
{

    /**
     * 发送
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function respond()
    {
        // 获取实现类
        $class = ResponsePluginTypeMapConstant::getClass($this->plugin_type);
        if (!$class) {
            return error("`{$this->plugin_type}` Response Plugin Type not Found.");
        }

        // 注入固定参数
        $config = [
            'class' => $class,
            'type' => $this->type,
            'rule_id' => $this->rule_id,
            'message' => $this->message,
        ];
        // 注入实现类
        $obj = Yii::createObject($config);

        return $obj->respond();
    }
}