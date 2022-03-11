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

namespace shopstar\admin\finance;

use shopstar\constants\finance\RefundLogConstant;

use shopstar\helpers\ExcelHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\finance\RefundLogModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\bases\KdxAdminApiController;

/**
 * 退款记录
 * Class RefundLogController
 * @package shop\manage\finance
 */
class RefundLogController extends KdxAdminApiController
{

    public $configActions = [
        'allowHeaderActions' => [
            'list',
        ]
    ];
    
    /**
     * 列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $get = RequestHelper::get();
        $andWhere = [];
        if (!empty($get['start_time']) && !empty($get['end_time'])) {
            $andWhere[] = ['between', 'log.created_at', $get['start_time'], $get['end_time']];
        }
        
        $params = [
            'searchs' => [
                ['member.level_id', 'int', 'level_id'],
                ['log.status', 'int', 'status'],
                ['log.type', 'int', 'type'],
                [['member.nickname', 'member.realname', 'member.mobile', 'order_no'], 'like', 'keyword']
            ],
            'select' => [
                'log.id',
                'log.member_id',
                'log.type',
                'log.money',
                'log.status',
                'log.order_id',
                'log.order_no',
                'log.created_at',
                'member.nickname',
                'member.avatar',
                'level.level_name',
                'member.level_id',
                'member.source',
            ],
            'alias' => 'log',
            'where' => [],
            'andWhere' => $andWhere,
            'leftJoins' => [
                [MemberModel::tableName().' member', 'member.id = log.member_id'],
                [MemberLevelModel::tableName(). ' level', 'level.id = member.level_id'],
            ],
            'orderBy' => ['id' => SORT_DESC],
        ];
        
        // 获取默认等级
        $defaultLevelId = MemberLevelModel::getDefaultLevelId();
        
        $list = RefundLogModel::getColl($params, [
            'pager' => !$get['export'],
            'onlyList' => $get['export'],
            'callable' => function (&$row) use ($defaultLevelId) {
                $row['is_default_level'] = (int)($row['level_id'] == $defaultLevelId);
                $row['type_text'] = RefundLogConstant::getText($row['type']);
                $row['status_text'] = $row['status'] ? '成功' : '失败';
            }
        ]);
        
        if ($get['export']) {
            try {
                ExcelHelper::export($list, RefundLogModel::$logFields, '退款记录导出');
            } catch (\Throwable $exception) {
            
            }
            die;
        }
        
        return $this->result($list);
    }
    
    /**
     * 获取退款类型
     * @author 青岛开店星信息技术有限公司
     */
    public function actionTypeList()
    {
        $list = RefundLogConstant::getAll();;
        foreach ($list as $key => $item) {
            $list[$key]['key'] = $key;
        }
        return $this->result(['data' => array_values($list)]);
    }
}