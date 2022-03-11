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

namespace shopstar\admin\commentHelper;

use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use shopstar\constants\commentHelper\CommentHelperLogConstant;
use shopstar\bases\KdxAdminApiController;

/**
 * 评价助手设置
 * Class SettingController
 * @package apps\commentHelper\manage
 */
class SettingController extends KdxAdminApiController
{
    public $configActions = [
        'allowPermActions' => [
            'get'
        ]
    ];
    /**
     * 获取设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $set = ShopSettings::get('commentHelper');

        return $this->result(['data' => $set]);
    }
    
    /**
     * 设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSet()
    {
        $set = [
            'choice_status' => RequestHelper::post('choice_status'), // 精选
            'comment_reward_status' => RequestHelper::post('comment_reward_status'), // 评价奖励
            'api_key' => RequestHelper::post('api_key'), // 精选
        ];
        
        ShopSettings::set('commentHelper', $set);

        // 日志
        LogModel::write(
            $this->userId,
            CommentHelperLogConstant::COMMENT_HELPER_SET,
            CommentHelperLogConstant::getText(CommentHelperLogConstant::COMMENT_HELPER_SET),
            0,
            [
                'log_data' => $set,
                'log_primary' => [
                    '精选评价' => $set['choice_status'] == 1 ? '开启' : '关闭',
                    '评价奖励' => $set['comment_reward_status'] == 1 ? '开启' : '关闭',
                    '设置ApiKey' => $set['api_key'] ? '已设置' : '未设置'
                ]
            ]
        );
        
        return $this->success();
    }

}