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

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\ClientTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ExcelHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberRedPackageModel;

/**
 * Class RedPackageController
 * @package shopstar\admin\finance
 * @author 青岛开店星信息技术有限公司.
 */
class RedPackageController extends KdxAdminApiController
{

    /**
     * @var string[]
     */
    public $allowHeaderActions = [
        'list',
    ];

    /**
     * @var array
     */
    public $configActions = [
        'allowHeaderActions' => [
            'list',
        ]
    ];

    /**
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionList()
    {
        $get = RequestHelper::get();
        $where = [];
        if (isset($get['status']) && $get['status'] == 0) {
            $where = ['and'];
            $where[] = ['red_package.status' => 0];
            $where[] = ['>', 'red_package.expire_time', DateTimeHelper::now()];
        } elseif ($get['status'] == 2 && !empty($get['status'])) {
            $where = [
                'or',
                [
                    'and',
                    ['red_package.status' => 0],
                    ['<', 'red_package.expire_time', DateTimeHelper::now()]
                ],
                ['red_package.status' => -1],
            ];
        } else if ($get['status']) {

            $where = ['red_package.status' => $get['status']];
        }

        $list = MemberRedPackageModel::getColl([

            'alias' => 'red_package',
            'leftJoins' => [
                [MemberModel::tableName() . ' member', 'member.id = red_package.member_id'],
                [MemberLevelModel::tableName() . ' level', 'member.level_id = level.id'],
            ],
            'searchs' => [
                [['member.nickname', 'member.mobile', 'member.realname'], 'like', 'keywords'],
                ['member.level_id', 'int', 'level_id'],
                ['red_package.created_at', 'between', 'created_at'],
                ['red_package.updated_at', 'between', 'updated_at'],
            ],
            'where' => $where,
            'select' => [
                'member.id as member_id',
                'member.nickname',
                'member.avatar',
                'member.source as member_source',
                'level.level_name',
                'red_package.money',
                'red_package.created_at',
                'red_package.updated_at',
                'red_package.scene',
                'red_package.status',
                'red_package.expire_time',
            ],
            'orderBy' => ['created_at' => SORT_DESC]
        ], [
            'callable' => function (&$result) {
                $result['scene_text'] = MemberRedPackageModel::$sceneMap[$result['scene']];
                $result['member_source_name'] = ClientTypeConstant::getText($result['member_source']);
                if ($result['status'] == 1) {
                    $result['status_text'] = '已领取';
                } else if ($result['status'] == 0) {

                    if ($result['expire_time'] < DateTimeHelper::now()) {

                        $result['status_text'] = '已失效';
                    } else {

                        $result['status_text'] = '未领取';
                    }
                } else {
                    $result['status_text'] = '已失效';
                }
            },
            'pager' => $get['export'] ? false : true,
            'onlyList' => $get['export'] ? true : false,
        ]);

        if ($get['export']) {
            ExcelHelper::export($list, [
                [
                    'field' => 'member_id',
                    'title' => '会员id',
                    'width' => 18,
                ],
                [
                    'field' => 'nickname',
                    'title' => '会员昵称',
                    'width' => 18,
                ],
                [
                    'field' => 'member_source_name',
                    'title' => '会员来源',
                    'width' => 18,
                ],
                [
                    'field' => 'scene_text',
                    'title' => '红包来源',
                    'width' => 18,
                ],
                [
                    'field' => 'level_name',
                    'title' => '会员等级',
                    'width' => 18,
                ],
                [
                    'field' => 'money',
                    'title' => '金额',
                    'width' => 18,
                ],
                [
                    'field' => 'created_at',
                    'title' => '发放时间',
                    'width' => 18,
                ],
                [
                    'field' => 'updated_at',
                    'title' => '领取时间',
                    'width' => 18,
                ],
                [
                    'field' => 'status_text',
                    'title' => '领取状态',
                    'width' => 18,
                ],
            ], '红包记录导出');
        }

        return $this->result($list);
    }

}