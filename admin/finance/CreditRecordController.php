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
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\member\MemberCreditRecordTypeConstant;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\ExcelHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberCreditRecordModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;

/**
 * 会员积分、余额类
 * Class CreditRecordController
 * @package shopstar\admin\member
 */
class CreditRecordController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowHeaderActions' => [
            'integral',
            'balance',
        ],
        'allowPermActions' => [
            'label'
        ]
    ];

    /**
     * 积分明细
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIntegral()
    {
        //是否是导出
        $export = RequestHelper::getInt('export', 0);

        $records = $this->commonCredit(MemberCreditRecordTypeConstant::RECORD_TYPE_INTEGRAL, $export);

        //如果是导出
        if ($export == 1) {
            $this->exportIntegral($records);
        }

        return $this->result($records);
    }

    /**
     * 导出积分明细
     * @param $records
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function exportIntegral($records): bool
    {
        ExcelHelper::export($records, [

            [
                'field' => 'member_id',
                'title' => '会员id',
            ],
            [
                'field' => 'nickname',
                'title' => '昵称',
            ],
            [
                'field' => 'num',
                'title' => '积分变化',
            ],
            [
                'field' => 'present_credit',
                'title' => '当前积分',
            ],
            [
                'field' => 'remark',
                'title' => '备注',
            ],
            [
                'field' => 'operator_text',
                'title' => '操作人',
            ],
            [
                'field' => 'created_at',
                'title' => '操作时间',
            ],


        ], '积分明细导出');

        return true;
    }

    /**
     * 余额明细
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionBalance()
    {
        //是否是导出
        $export = RequestHelper::getInt('export', 0);

        $records = $this->commonCredit(MemberCreditRecordTypeConstant::RECORD_TYPE__BALANCE, $export);

        //如果是导出
        if ($export == 1) {
            $this->exportBalance($records);
        }

        return $this->result($records);
    }

    /**
     * 导出余额明细
     * @param $records
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    private function exportBalance($records)
    {
        ExcelHelper::export($records, [
            [
                'field' => 'member_id',
                'title' => '会员id',
            ],
            [
                'field' => 'nickname',
                'title' => '昵称',
            ],
            [
                'field' => 'num',
                'title' => '余额变化',
            ],
            [
                'field' => 'present_credit',
                'title' => '当前余额',
            ],
            [
                'field' => 'remark',
                'title' => '备注',
            ],
            [
                'field' => 'operator_text',
                'title' => '操作人',
            ],
            [
                'field' => 'created_at',
                'title' => '操作时间',
            ],


        ], '余额明细导出');

        return true;
    }

    /**
     * @param $type
     * @param $export
     * @return array|int|string|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    private function commonCredit($type, $export)
    {
        $params = [
            'searchs' => [
                ['record.created_at', 'between', 'created_at'],
                ['m.level_id', 'int', 'level_id'],
                [['m.nickname'], 'like', 'keyword'],
                ['m.id', 'int', 'member_id']
            ],
            'where' => [
                'record.type' => $type,
                'm.is_deleted' => 0
            ],
            'select' => [
                'm.id as member_id',
                'm.avatar',
                'm.nickname',
                'm.source',
                'm.level_id',
                'level.level_name',
                'record.num',
                'record.present_credit',
                'record.operator',
                'record.remark',
                'record.created_at',
                'record.status',
                'level.is_default',

            ],
            'alias' => 'record',
            'leftJoins' => [
                [MemberModel::tableName() . ' m', 'record.member_id = m.id'],
                [MemberLevelModel::tableName() . ' level', 'm.level_id = level.id'],
            ],
            'orderBy' => [
                'record.created_at' => SORT_DESC,
                'record.id' => SORT_DESC,
            ],
        ];

        // 获取列表
        return MemberCreditRecordModel::getColl($params, [
            'callable' => function (&$row) {
                $row['source_name'] = ClientTypeConstant::getText($row['source']);
                $row['operator_text'] = $row['operator'] == 0 ? "本人" : "管理员";
                $row['status_text'] = MemberCreditRecordStatusConstant::getMessage($row['status']);
            },
            'pager' => $export == 0,
            'onlyList' => $export == 1
        ]);
    }

    /**
     * 获取积分余额筛选标签
     * @return array|\yii\web\Response
     */
    public function actionLabel()
    {
        $label = [];

        $label['levels'] = ArrayHelper::map(MemberLevelModel::find()->select('id, level_name')->get(), 'id', 'level_name');

        return $this->result($label);
    }

}
