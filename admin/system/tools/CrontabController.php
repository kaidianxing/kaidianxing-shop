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

namespace shopstar\admin\system\tools;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\core\CoreCronTabConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\CoreSettings;

/**
 * 数据管理
 * Class CrontabController
 */
class CrontabController extends KdxAdminApiController
{

    /**
     * @var array 需要POST的Action
     */
    public $configActions = [
        'postActions' => [
            'set',
        ]
    ];
    /**
     * 读取设置
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @author likexin
     */
    public function actionGet()
    {
        // 全部设置项
        $params = CoreCronTabConstant::getAll();

        // 读取现有设置
        $settings = CoreSettings::get('crontab');

        $list = [];

        foreach ($params as $key => $item) {
            $list[] = [
                'key' => $key,
                'title' => $item['message'],
                'tips' => $item['tips'],
                'value' => (int)$settings['params'][$key],
            ];
        }

        // 获取统一设置
        $unifyValue = 0;
        $unique = array_unique(array_values($settings['params']));
        if (count($unique) == 1) {
            $unifyValue = $unique[0];
        }

        return $this->result([
            'execute_type' => (int)$settings['execute_type'],
            'unify_value' => $unifyValue,
            'params' => $list,
        ]);
    }

    /**
     * 保存设置
     * @return array|\yii\web\Response
     * @throws \yii\db\Exception
     * @throws \ReflectionException
     * @author likexin
     */
    public function actionSet()
    {
        $settings = [
            'execute_type' => RequestHelper::postInt('execute_type'),
            'params' => [],
        ];

        // 全部设置项
        $params = CoreCronTabConstant::getAll();
        if (!empty($params)) {
            foreach ($params as $key => $item) {
                $settings['params'][$key] = RequestHelper::postInt($key);
                if (!in_array($settings['params'][$key], [10, 30])) {
                    return $this->error($item['message'] . '(' . $key . ')值不合法');
                }
            }
        }

        // 保存设置
        CoreSettings::set('crontab', $settings);

        return $this->result();
    }
}