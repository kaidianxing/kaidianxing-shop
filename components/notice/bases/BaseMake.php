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

namespace shopstar\components\notice\bases;

use shopstar\components\notice\interfaces\MakeInterface;
use yii\base\Component;

/**
 * Class BaseMake
 * @package shopstar\components\notice\bases
 * @author 青岛开店星信息技术有限公司
 */
class BaseMake extends Component implements MakeInterface
{
    /**
     * 预留字段 ，用于字段名转化
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $reserveField = [];

    /**
     * 组成数据
     * @param array $messageData
     * @return mixed|void
     * @author 青岛开店星信息技术有限公司
     */
    final function makeMessageData(array $messageData)
    {
        $makeMessageData = [];
        foreach ($messageData as $key => $item) {
            if (isset($this->reserveField[$key])) {
                $makeMessageData[$this->reserveField[$key]] = $item;
            }
        }

        return $makeMessageData;
    }

}
