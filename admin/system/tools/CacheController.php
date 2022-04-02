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
use shopstar\helpers\CacheHelper;

/**
 * 积分余额设置
 * Class CreditController
 * @package shopstar\admin\sysset
 * @author 青岛开店星信息技术有限公司
 */
class CacheController extends KdxAdminApiController
{

    /**
     * @var string[] 允许post请求的Actions
     */
    public $configActions = [
        'postActions' => [
            'flush',
        ]
    ];

    /**
     * 获取信息
     * @return array|int[]|\yii\web\Response
     */
    public function actionInfo()
    {
        $redisMemory = \Yii::$app->redis->info('memory');

        // 匹配
        preg_match("/used_memory_human\:(.*?)\\r\\n/", $redisMemory, $usedMemoryMatches);

        return $this->result([
            'redis' => [
                'used_memory_human' => $usedMemoryMatches[1] ?? '0M',
            ],
        ]);
    }

    /**
     * 清除缓存
     * @return array|int[]|\yii\web\Response
     */
    public function actionFlush()
    {
        // 清除缓存 redis 13库
        CacheHelper::flush();

        return $this->success();
    }

}