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

namespace shopstar\admin\commission\settings;

use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\models\commission\CommissionSettings;
use shopstar\bases\KdxAdminApiController;

/**
 * 排行设置
 * Class RankController
 * @package apps\commission\manage\settings
 */
class RankController extends KdxAdminApiController
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
     * 获取设置
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGet()
    {
        return $this->result([
            'data' => CommissionSettings::get('rank'),
        ]);
    }

    /**
     * 提交设置
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     * @author likexin
     */
    public function actionSet()
    {
        $data = [
            'open' => RequestHelper::postInt('open'),  // 排行榜开关 0:关闭 1:开启
            'commission_type' => RequestHelper::postInt('commission_type'),    // 佣金排行类型 0:累计佣金 1:已提现佣金
            'show_total' => RequestHelper::postInt('show_total'),  // 排行榜显示数量
        ];
        CommissionSettings::set('rank', $data);
        // 日志
        LogModel::write(
            $this->userId,
            CommissionLogConstant::RANK_EDIT,
            CommissionLogConstant::getText(CommissionLogConstant::RANK_EDIT),
            '0',
            [
                'log_data' => $data,
                'log_primary' => [
                    '排行榜状态' => $data['open'] ? '开启' : '关闭',
                    '排行榜类型' => $data['open'] ? ($data['commission_type'] ? '已提现佣金' : '累计佣金') : '-',
                    '显示数量' => $data['open'] ? $data['show_total'] : '-',
                ],
                'dirty_identity_code' => [
                    CommissionLogConstant::RANK_EDIT,
                ]
            ]
        );
        
        return $this->success();
    }

}