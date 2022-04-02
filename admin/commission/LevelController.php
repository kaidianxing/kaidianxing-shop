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

namespace shopstar\admin\commission;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\commission\CommissionLogConstant;
use shopstar\exceptions\commission\CommissionLevelException;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionLevelModel;
use shopstar\models\log\LogModel;

/**
 * 分销等级
 * Class LevelController
 * @package shopstar\admin\commission
 */
class LevelController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'add',
            'edit',
        ],
        'allowPermActions' => [
            'get-list',
            'get-used',
            'list'
        ]
    ];

    /**
     * 等级列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $status = RequestHelper::get('status', null);
        $andWhere = [];
        if (isset($status)) {
            $andWhere[] = ['status' => $status];
        }
        $params = [
            'where' => [],
            'andWhere' => $andWhere,
            'orderBy' => [
                'is_default' => SORT_DESC,
                'level' => SORT_ASC
            ]
        ];

        $list = CommissionLevelModel::getColl($params, [
            'callable' => function (&$row) {
                // 判断升级条件 拼接文字
                if ($row['upgrade_type'] == 0) {
                    $type = ' 或 ';
                } else {
                    $type = ' 并且 ';
                }
                // 取条件
                $condition = array_intersect_key($row, CommissionLevelModel::$upgradeCondition);
                // 去除空条件
                $condition = ArrayHelper::arrayFilterEmpty($condition);
                // 获取注释
                $comment = (new CommissionLevelModel)->attributeLabels();
                // 交集 值取注释
                $condition = array_intersect_key($comment, $condition);
                // 转字符串
                $row['condition_text'] = implode($type, $condition);
            }
        ]);

        return $this->result($list);
    }

    /**
     * 分销等级简单列表
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetList()
    {
        $status = RequestHelper::get('status', null);
        $andWhere = [];
        if (isset($status)) {
            $andWhere[] = ['status' => $status];
        }
        $list = CommissionLevelModel::getColl([
            'select' => ['id', 'name'],
            'andWhere' => $andWhere,
            'orderBy' => [
                'is_default' => SORT_DESC,
                'level' => SORT_ASC,
            ],

        ], [
            'pager' => false,
            'onlyList' => true
        ]);

        return $this->result(['list' => $list]);
    }

    /**
     * 等级详情
     * @throws CommissionLevelException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new CommissionLevelException(CommissionLevelException::LEVEL_DETAIL_PARAMS_ERROR);
        }
        $detail = CommissionLevelModel::findOne(['id' => $id]);

        return $this->result($detail);
    }

    /**
     * 获取已使用的等级
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetUsed()
    {
        $setLevel = CommissionLevelModel::find()
            ->select('level')
            ->indexBy('level')
            ->get();
        $level = array_keys($setLevel);
        $used = array_values($level);
        return $this->result(['level' => $used]);
    }

    /**
     * 新增等级
     * @throws CommissionLevelException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();
        $add = CommissionLevelModel::saveLevel($this->userId);
        if (is_error($add)) {
            $transaction->rollBack();
            throw new CommissionLevelException(CommissionLevelException::LEVEL_ADD_FAIL, $add['message']);
        }
        $transaction->commit();

        return $this->success();
    }

    /**
     * 编辑等级
     * @throws CommissionLevelException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new CommissionLevelException(CommissionLevelException::LEVEL_EDIT_PARAMS_ERROR);
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        $edit = CommissionLevelModel::saveLevel($this->userId, $id);
        if (is_error($edit)) {
            $transaction->rollBack();
            throw new CommissionLevelException(CommissionLevelException::LEVEL_EDIT_FAIL, $edit['message']);
        }
        $transaction->commit();

        return $this->success();
    }

    /**
     * 修改等级状态
     * @throws CommissionLevelException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeStatus()
    {
        $id = RequestHelper::get('id');
        $status = RequestHelper::get('status');
        if (empty($id) || $status == '') {
            throw new CommissionLevelException(CommissionLevelException::LEVEL_CHANGE_STATUS_PARAMS_ERROR);
        }

        $res = CommissionLevelModel::easySwitch('status', [
            'isPost' => false,
            'afterAction' => function ($model) {
                // 日志
                LogModel::write(
                    $this->userId,
                    CommissionLogConstant::LEVEL_CHANGE_STATUS,
                    CommissionLogConstant::getText(CommissionLogConstant::LEVEL_CHANGE_STATUS),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => [
                            'id' => $model->id,
                            '等级名称' => $model->name,
                            '状态' => $model->status ? '启用' : '禁用'
                        ],
                        'dirty_identity_code' => [
                            CommissionLogConstant::LEVEL_CHANGE_STATUS,
                        ]
                    ]
                );
            }
        ]);

        if (is_error($res)) {
            throw new CommissionLevelException(CommissionLevelException::LEVEL_CHANGE_STATUS_FAIL, $res['message']);
        }

        return $this->success();
    }

    /**
     * 删除等级
     * 木有批量删除
     * @throws CommissionLevelException
     * @throws \yii\db\Exception|\Throwable
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::get('id');
        $type = RequestHelper::get('type');
        $levelId = RequestHelper::get('level_id');
        if (empty($id) || empty($type)) {
            throw new CommissionLevelException(CommissionLevelException::LEVEL_DELETE_PARAMS_ERROR);
        }
        // 不能移入该等级
        if ($id == $levelId) {
            throw new CommissionLevelException(CommissionLevelException::LEVEL_DELETE_NOT_REPEAT);
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        $defaultLevelId = CommissionLevelModel::getDefaultId();

        // 删除
        $res = CommissionLevelModel::easyDelete([
            'andWhere' => ['<>', 'id', $defaultLevelId], // 默认等级不删
            'isPost' => false,
            'afterDelete' => function ($model) use ($type, $levelId) {
                if ($type == 1) {
                    // 移入其他等级下
                    CommissionAgentModel::updateAll(['level_id' => $levelId], ['level_id' => $model->id]);
                } else {
                    $defaultLevel = CommissionLevelModel::getDefaultId();
                    // 更改该等级下分销商等级为默认
                    CommissionAgentModel::updateAll(['level_id' => $defaultLevel], ['level_id' => $model->id]);
                }

                // 日志
                LogModel::write(
                    $this->userId,
                    CommissionLogConstant::LEVEL_DELETE,
                    CommissionLogConstant::getText(CommissionLogConstant::LEVEL_DELETE),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => [
                            'id' => $model->id,
                            '等级名称' => $model->name,
                            '一级佣金' => $model->commission_1,
                            '二级佣金' => $model->commission_2,
                            '三级佣金' => $model->commission_2,
                            '删除方式' => $type == 1 ? '移入其他等级' : '修改为默认等级',
                            '移入等级id' => $type == 1 ? $levelId : '-',
                        ],
                        'dirty_identity_code' => [
                            CommissionLogConstant::LEVEL_DELETE,
                        ]
                    ]
                );
            }
        ]);

        if (is_error($res)) {
            $transaction->rollBack();
            throw new CommissionLevelException(CommissionLevelException::LEVEL_DELETE_FAIL, $res['message']);
        }

        $transaction->commit();

        return $this->success();
    }

}