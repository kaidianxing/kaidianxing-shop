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

use yii\base\Component;
use yii\helpers\Json;

class SendBase extends Component
{
    /**
     * 转化模板所需字段
     * @param $tpl
     * @param $data
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function makeTpl($tpl, $data)
    {
        $tpl = Json::decode($tpl);
        $tpl1 = [];
        foreach ($tpl as $index => $item) {
            $tpl1 = array_merge($tpl1, $item);
        }

        foreach ($tpl1 as $key => $value) {
            $tpl1[$key] = str_replace($value, $data[$value], $value);

        }

        return $tpl1;
    }

    /**
     * 转化公众号模板所需字段
     * @param $tpl
     * @param $data
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function makeWechatTpl($tpl, $data)
    {
        $tpl = Json::decode($tpl);
        foreach ($tpl as $key => $value) {
            $string = $value['value'];
            foreach ($data as $dataIndex => $dataItem) {
                $string = str_replace($dataIndex, $dataItem, $string);
            }

            $tpl[$key]['value'] = $string;
        }

        return $tpl;
    }
}
