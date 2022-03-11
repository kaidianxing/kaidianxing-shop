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

namespace shopstar\admin\printer;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\printer\PrinterLogConstant;
use shopstar\constants\printer\PrinterSceneConstant;
use shopstar\exceptions\printer\PrinterTaskException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\printer\PrinterManualLogModel;
use shopstar\models\printer\PrinterModel;
use shopstar\models\printer\PrinterTaskModel;
use shopstar\models\printer\PrinterTemplateModel;
use yii\helpers\Json;

/**
 * 打印任务
 * Class TaskController
 * @package apps\printer\manage
 */
class TaskController extends KdxAdminApiController
{

    /**
     * 列表
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $params = [
            'searchs' => [
                [['task.name', 't.name'], 'like', 'keyword']
            ],
            'where' => ['task.is_deleted' => 0],
            'alias' => 'task',
            'leftJoins' => [
                [PrinterTemplateModel::tableName() . ' t', 't.id = task.template_id'],
            ],
            'select' => [
                'task.id',
                'task.name as task_name',
                'task.printer_id',
                'task.template_id',
                't.name as template_name',
                'task.scene',
                'task.created_at',
                't.is_deleted as template_deleted_status'
            ],
            'orderBy' => [
                'task.created_at' => SORT_DESC
            ]

        ];

        if (!empty(RequestHelper::get('scene'))) {
            $params['andWhere'] = [
                'find_in_set("' . RequestHelper::get('scene') . '",scene)'
            ];
        }

        // 打印机
        $printers = PrinterModel::find()->where(['is_deleted' => 0])->select('id, status, name, location, type, brand')->get();
        $printersMap = array_column($printers, NULL, 'id');
        $list = PrinterTaskModel::getColl($params, [
            'callable' => function (&$row) use ($printersMap) {
                $row['printer_id'] = PrinterModel::getPrinterNameMapById($row['printer_id'], $printersMap);
                $row['scene'] = PrinterTaskModel::getSceneById(explode(',', $row['scene']));
                $row['template_name'] = $row['template_deleted_status'] ? '' : $row['template_name'];
            }
        ]);

        return $this->result(['data' => $list]);
    }

    /**
     * 添加
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $params = $this->checkParams();

        if (is_error($params)) {
            return $this->result($params['message'], PrinterTaskException::PRINTER_TASK_ADD_PARAMS_INVALID);
        }

        $result = PrinterTaskModel::easyAdd([
            'attributes' => $params,
            'beforeSave' => function (&$result) {
                $result->delivery = Json::encode($result->delivery);
                if (empty($result->verify_point)) {
                    $result->verify_point = 0;
                } else {
                    $result->verify_point = Json::encode($result->verify_point);
                }
            },
            'afterSave' => function (PrinterTaskModel $model) {

                // 日志
                $logPrimary = [
                    'id' => $model->id,
                    '名称' => $model->name,
                    '打印次数' => $model->times,
                    '打印模板ID' => $model->template_id,
                    '打印机ID' => $model->printer_id,
                    '打印场景' => empty($model->scene) ? '' : implode(',', array_map(function ($val) {

                        return PrinterSceneConstant::getText($val);

                    }, explode(',', $model->scene)))
                ];

                LogModel::write(
                    $this->userId,
                    PrinterLogConstant::PRINTER_TASK_ADD,
                    PrinterLogConstant::getText(PrinterLogConstant::PRINTER_TASK_ADD),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $logPrimary,
                        'dirty_identify_code' => [
                            PrinterLogConstant::PRINTER_TASK_ADD,
                            PrinterLogConstant::PRINTER_TASK_SAVE,
                        ]
                    ]
                );

            },
        ]);

        return $this->result($result);
    }

    /**
     * 编辑
     * @return array|\yii\web\Response
     * @throws PrinterTaskException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $taskId = RequestHelper::getInt('id');

        $task = PrinterTaskModel::findOne(['id' => $taskId, 'is_deleted' => 0]);

        if (empty($task)) {
            throw new PrinterTaskException(PrinterTaskException::PRINTER_TASK_EDIT_RECORD_INVALID);
        }

        $task->scene = explode(',', $task->scene);
        !empty($task->scene) && $task->scene = array_map(function ($row) {
            return intval($row);
        }, $task->scene);

        $task->delivery = Json::decode($task->delivery);

        $verifyPoint = [];
        $task->verify_point = Json::decode($task->verify_point);

        $task->printer_id = explode(',', $task->printer_id);
        !empty($task->printer_id) && $task->printer_id = PrinterModel::getPrinterNameById($task->printer_id);
        $task->toArray();

        return $this->result(['data' => $task, 'verify_point' => $verifyPoint]);
    }

    /**
     * 保存
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSave()
    {
        $params = $this->checkParams(false);

        if (is_error($params)) {
            return $this->result($params['message'], PrinterTaskException::PRINTER_TASK_SAVE_PARAMS_INVALID);
        }

        $result = PrinterTaskModel::easyEdit(array(
            'attributes' => $params,
            'beforeSave' => function (&$result) {
                $result->delivery = Json::encode($result->delivery);
                if (empty($result->verify_point)) {
                    $result->verify_point = 0;
                } else {
                    $result->verify_point = Json::encode($result->verify_point);
                }
            },
            'afterSave' => function (PrinterTaskModel $model) {
                // 日志
                $logPrimary = [
                    'id' => $model->id,
                    '名称' => $model->name,
                    '打印次数' => $model->times,
                    '打印模板ID' => $model->template_id,
                    '打印机ID' => $model->printer_id,
                    '打印场景' => empty($model->scene) ? '' : implode(',', array_map(function ($val) {

                        return PrinterSceneConstant::getText($val);

                    }, explode(',', $model->scene)))
                ];

                LogModel::write(
                    $this->userId,
                    PrinterLogConstant::PRINTER_TASK_SAVE,
                    PrinterLogConstant::getText(PrinterLogConstant::PRINTER_TASK_SAVE),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $logPrimary,
                        'dirty_identify_code' => [
                            PrinterLogConstant::PRINTER_TASK_ADD,
                            PrinterLogConstant::PRINTER_TASK_SAVE,
                        ]
                    ]
                );

            },
        ));

        return $this->result($result);
    }

    /**
     * 删除打印任务
     * @return array|\yii\web\Response
     * @throws PrinterTaskException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $taskId = RequestHelper::getInt('id');

        $task = PrinterTaskModel::findOne(['id' => $taskId, 'is_deleted' => 0]);

        if (empty($task)) {
            throw new PrinterTaskException(PrinterTaskException::PRINTER_TASK_DELETE_RECORD_INVALID);
        }

        $task->is_deleted = 1;

        if (!$task->save()) {
            return $this->error($task->getErrorMessage());
        }

        // 日志
        $logPrimary = [
            'id' => $task->id,
            '名称' => $task->name,
            '打印次数' => $task->times,
            '打印模板ID' => $task->template_id,
            '打印机ID' => $task->printer_id,
            '打印场景' => empty($task->scene) ? '' : implode(',', array_map(function ($val) {

                return PrinterSceneConstant::getText($val);

            }, explode(',', $task->scene)))
        ];

        LogModel::write(
            $this->userId,
            PrinterLogConstant::PRINTER_TASK_DELETE,
            PrinterLogConstant::getText(PrinterLogConstant::PRINTER_TASK_DELETE),
            $task->id,
            [
                'log_data' => $task->attributes,
                'log_primary' => $logPrimary,
            ]
        );

        return $this->result();
    }

    /**
     * 手动触发打印任务
     * @return array|\yii\web\Response
     * @throws PrinterTaskException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionExecute()
    {
        $orderId = RequestHelper::postInt('order_id');

        // 判断打印次数
        $total = PrinterManualLogModel::find()
            ->where([
                'order_id' => $orderId,
            ])
            ->count();
        if (RequestHelper::postInt('is_check', 0)) {
            if ($total > 1) {
                return $this->result($total);
            } else {
                return $this->result(0);
            }
        }

        $printerId = RequestHelper::postInt('printer_id');
        $templateId = RequestHelper::postInt('template_id');

        if (empty($printerId) || empty($templateId) || empty($orderId)) {
            throw new PrinterTaskException(PrinterTaskException::PRINTER_TASK_EXECUTE_PARAMS_INVALID);
        }

        $result = PrinterTaskModel::execute($printerId, $templateId, $orderId);

        return $this->result($result);
    }

    private function checkParams($isAdd = true)
    {
        $postData = RequestHelper::post();

        $params = [];

        // 任务名称
        if (empty($postData['name'])) {
            return error('打印任务名称不能为空');
        } else {
            $andWhere = [];
            if (!$isAdd) {
                $andWhere = [
                    'and',
                    ['<>', 'id', $postData['id']]
                ];
            }

            $query = PrinterTaskModel::find()->where(
                [
                    'name' => $postData['name'],
                    'is_deleted' => 0
                ]
            );

            if (!empty($andWhere)) {
                $query->andWhere($andWhere);
            }

            $exist = $query->exists();

            if ($exist) {
                return error('打印任务名称已存在');
            }
        }
        if (mb_strlen($postData['name']) > 30) {
            return error('最大支持30个字符');
        }
        $params['name'] = $postData['name'];

        // 打印场景
        if (empty($postData['scene'])) {
            return error('打印场景不能为空');
        }
        $params['scene'] = $postData['scene'];

        // 打印模板
        if (empty($postData['template_id'])) {
            return error('打印模板不能为空');
        }
        $params['template_id'] = $postData['template_id'];

        // 打印联数
        if (empty($postData['times'])) {
            return error('打印联数不能为空');
        }
        $params['times'] = $postData['times'];

        // 打印机
        if (empty($postData['printer_id'])) {
            return error('打印机不能为空');
        }
        $params['printer_id'] = $postData['printer_id'];

        return $params;
    }

}