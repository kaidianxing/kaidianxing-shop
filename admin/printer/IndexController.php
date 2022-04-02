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
use shopstar\components\printer\PrinterComponent;
use shopstar\constants\CacheTypeConstant;
use shopstar\constants\printer\PrinterLogConstant;
use shopstar\constants\printer\PrinterTypeConstant;
use shopstar\exceptions\printer\PrinterException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\log\LogModel;
use shopstar\models\printer\PrinterModel;
use shopstar\models\printer\PrinterTaskModel;
use shopstar\traits\CacheTrait;
use yii\helpers\Json;

/**
 * 小票打印管理列表
 * Class IndexController
 * @package shopstar\admin\printer
 */
class IndexController extends KdxAdminApiController
{

    use CacheTrait;

    /**
     * 列表
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $params = [
            'searchs' => [
                ['status', 'int', 'status'],
                [['name'], 'like', 'keyword']
            ],
            'where' => [
                'is_deleted' => 0
            ],
            'select' => [
                'id',
                'name',
                'type',
                'brand',
                'location',
                'status'
            ],
            'orderBy' => [
                'created_at' => SORT_DESC
            ]

        ];

        $list = PrinterModel::getColl($params, [
            'onlyList' => RequestHelper::get('only_list', 0) ? true : false
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
        // 参数校验
        $params = $this->checkParams();

        if (is_error($params)) {
            return $this->result($params['message'], PrinterException::PRINTER_INDEX_ADD_PARAMS_INVALID);
        }

        $result = PrinterModel::addResult($params);

        if (is_error($result)) {
            return $this->result($result['message'], PrinterException::PRINTER_INDEX_ADD_RESULT_FAILED);
        }

        // 日志
        $logPrimary = [
            'id' => $result->id,
            '名称' => $result->name,
            '品牌' => $result->brand,
            '配置' => $result->config,
        ];

        LogModel::write(
            $this->userId,
            PrinterLogConstant::PRINTER_ADD,
            PrinterLogConstant::getText(PrinterLogConstant::PRINTER_ADD),
            $result->id,
            [
                'log_data' => $result->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    PrinterLogConstant::PRINTER_ADD,
                    PrinterLogConstant::PRINTER_SAVE,
                ]
            ]
        );

        return $this->result();
    }

    /**
     * 测试打印
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionTest()
    {
        // 参数校验
        $params = $this->checkConfig();

        if (is_error($params)) {
            return $this->result($params['message'], PrinterException::PRINTER_INDEX_TEST_PARAMS_INVALID);
        }

        // 类型
        $params['type'] = RequestHelper::post('type');

        $result = PrinterModel::printIndex($params);

        return $this->result($result);
    }

    /**
     * 启用
     * @return array|\yii\web\Response
     * @throws PrinterException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionActive()
    {
        $printerId = RequestHelper::getInt('id');

        $printer = PrinterModel::findOne(['id' => $printerId, 'is_deleted' => 0]);

        if (empty($printer)) {
            throw new PrinterException(PrinterException::PRINTER_INDEX_ACTIVE_RECORD_NOT_EXISTS);
        }

        $printer->status = 1;

        if (!$printer->save()) {
            return $this->error($printer->getErrorMessage());
        }

        // 日志
        LogModel::write(
            $this->userId,
            PrinterLogConstant::PRINTER_ACTIVE,
            PrinterLogConstant::getText(PrinterLogConstant::PRINTER_ACTIVE),
            $printer->id,
            [
                'log_data' => $printer->attributes,
                'log_primary' => [
                    '状态' => '启用'
                ],
                'dirty_identify_code' => [
                    PrinterLogConstant::PRINTER_FORBIDDEN,
                    PrinterLogConstant::PRINTER_ACTIVE
                ]
            ]
        );

        return $this->result();
    }

    /**
     * 禁用
     * @return array|\yii\web\Response
     * @throws PrinterException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionForbidden()
    {
        $printerId = RequestHelper::getInt('id');

        $printer = PrinterModel::findOne(['id' => $printerId, 'is_deleted' => 0]);

        if (empty($printer)) {
            throw new PrinterException(PrinterException::PRINTER_INDEX_FORBIDDEN_RECORD_NOT_EXISTS);
        }

        // 检测是否有打印任务
        if (RequestHelper::get('is_check', 0)) {

            $task = PrinterTaskModel::checkPrinter($printerId);
            if (!empty($task)) {
                $taskStr = '该打印机正在执行【' . implode(',', $task) . '】打印任务，禁用后该打印机则不能执行打印任务，是否要禁用';
                return $this->result($taskStr);
            }
        }

        $printer->status = 0;

        if (!$printer->save()) {
            return $this->error($printer->getErrorMessage());
        }

        // 检测打印任务时不记录日志
        if (!RequestHelper::get('is_check', 0)) {
            // 日志
            LogModel::write(
                $this->userId,
                PrinterLogConstant::PRINTER_FORBIDDEN,
                PrinterLogConstant::getText(PrinterLogConstant::PRINTER_FORBIDDEN),
                $printer->id,
                [
                    'log_data' => $printer->attributes,
                    'log_primary' => [
                        '状态' => '禁用'
                    ],
                    'dirty_identify_code' => [
                        PrinterLogConstant::PRINTER_FORBIDDEN,
                        PrinterLogConstant::PRINTER_ACTIVE
                    ]
                ]
            );
        }

        return $this->result();
    }

    /**
     * 删除
     * @return array|\yii\web\Response
     * @throws PrinterException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $printerId = RequestHelper::getInt('id');

        $printer = PrinterModel::findOne(['id' => $printerId, 'is_deleted' => 0]);

        if (empty($printer)) {
            throw new PrinterException(PrinterException::PRINTER_INDEX_DELETE_RECORD_NOT_EXISTS);
        }

        // 检测是否有打印任务
        if (RequestHelper::get('is_check', 0)) {
            $task = PrinterTaskModel::checkPrinter($printerId);
            if (!empty($task)) {
                $taskStr = '该打印机正在执行【' . implode(',', $task) . '】打印任务，删除后该打印机则不能执行打印任务，是否要删除';
                return $this->result($taskStr);
            }
            return $this->result();
        }

        // 删除打印机授权
        try {
            $config = Json::decode($printer->config);
            // 删除之前必须刷新access_token, 避免因access_token过期无法删除
//            if ($printer->type == PrinterTypeConstant::PRINTER_YLY_AUTH) {
//                $config['access_token'] = $printer->access_token;
//            }

            $driver = PrinterComponent::getInstance(PrinterTypeConstant::getIdentify($printer->type), $config);
            $deletePrinterResult = $driver->deletePrinter();

            // 清除redis access_token
            if ($printer->type == PrinterTypeConstant::PRINTER_YLY_AUTH) {
                self::deleteCache(CacheTypeConstant::PRINTER_ACCESS_TOKEN, $config['client_id']);
            }

        } catch (\Throwable $e) {
            return $this->result($e->getMessage());
        }

        // 如果调用接口报错, 飞鹅是因为用户UID不匹配, 易融云因为未绑定在db数据的账号下,  直接往下走即可
        if (is_error($deletePrinterResult)) {
            if (($printer->type == PrinterTypeConstant::PRINTER_YLY_AUTH && !StringHelper::exists($deletePrinterResult['message'], '打印机信息错误'))
                || ($printer->type == PrinterTypeConstant::PRINTER_FEY && !StringHelper::exists(strtoupper($deletePrinterResult['message']), 'UID不匹配'))) {
                return $this->result($deletePrinterResult);
            }
        }

        // 删除数据库中打印机
        $printer->is_deleted = 1;

        if (!$printer->save()) {
            return $this->error($printer->getErrorMessage());
        }

        // 日志
        $logPrimary = [
            'id' => $printer->id,
            '名称' => $printer->name,
            '品牌' => $printer->brand,
            '配置' => $printer->config,
        ];

        LogModel::write(
            $this->userId,
            PrinterLogConstant::PRINTER_DELETE,
            PrinterLogConstant::getText(PrinterLogConstant::PRINTER_DELETE),
            $printer->id,
            [
                'log_data' => $printer->attributes,
                'log_primary' => $logPrimary,
            ]
        );

        return $this->result();
    }

    /**
     * 编辑
     * @return array|\yii\web\Response
     * @throws PrinterException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $printerId = RequestHelper::getInt('id');

        $printer = PrinterModel::findOne(['id' => $printerId, 'is_deleted' => 0]);

        if (empty($printer)) {
            throw new PrinterException(PrinterException::PRINTER_INDEX_EDIT_RECORD_NOT_EXISTS);
        }

        $printer->config = Json::decode($printer->config);

        $printer->toArray();

        return $this->result(['data' => $printer]);
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
            return $this->result($params['message'], PrinterException::PRINTER_INDEX_SAVE_PARAMS_INVALID);
        }

        $result = PrinterModel::saveResult($params);

        if (is_error($result)) {
            return $this->result($result['message'], PrinterException::PRINTER_INDEX_SAVE_RESULT_FAILED);
        }

        // 日志
        $logPrimary = [
            'id' => $result->id,
            '名称' => $result->name,
            '品牌' => $result->brand,
            '配置' => $result->config,
        ];

        LogModel::write(
            $this->userId,
            PrinterLogConstant::PRINTER_SAVE,
            PrinterLogConstant::getText(PrinterLogConstant::PRINTER_SAVE),
            $result->id,
            [
                'log_data' => $result->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    PrinterLogConstant::PRINTER_ADD,
                    PrinterLogConstant::PRINTER_SAVE,
                ]
            ]
        );

        return $this->result();
    }

    /**
     * 取消所有未打印订单
     * @return array|\yii\web\Response
     * @throws PrinterException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCancel()
    {
        $printerId = RequestHelper::postInt('id');

        if (empty($printerId)) {
            throw new PrinterException(PrinterException::PRINTER_INDEX_CANCEL_PARAMS_INVALID);
        }

        $printer = PrinterModel::findOne(['id' => $printerId, 'is_deleted' => 0]);

        if (empty($printer)) {
            throw new PrinterException(PrinterException::PRINTER_INDEX_CANCEL_RECORD_NOT_EXISTS);
        }

        $printerConfig = Json::decode($printer->config);
        if ($printer->type == PrinterTypeConstant::PRINTER_YLY_AUTH) {
            $printerConfig['access_token'] = $printer->access_token;
        }

        // 添加打印机授权
        $driver = PrinterComponent::getInstance(PrinterTypeConstant::getIdentify($printer->type), $printerConfig);

        $cancelPrinterResult = $driver->cancelAll();

        if (is_error($cancelPrinterResult)) {
            return $this->result($cancelPrinterResult);
        }

        // 日志
        $logPrimary = [
            'id' => $printer->id,
            '名称' => $printer->name,
            '品牌' => $printer->brand,
            '配置' => $printer->config,
        ];

        LogModel::write(
            $this->userId,
            PrinterLogConstant::PRINTER_CANCEL,
            PrinterLogConstant::getText(PrinterLogConstant::PRINTER_CANCEL),
            $printer->id,
            [
                'log_data' => $printer->attributes,
                'log_primary' => $logPrimary,
            ]
        );

        return $this->result();
    }

    /**
     * @param bool $isAdd
     * @return array
     */
    private function checkParams(bool $isAdd = true): array
    {
        $postData = RequestHelper::post();
        array_walk($postData, function (&$v) {
            $v = is_string($v) ? trim($v) : $v;
        });

        $params = [];
        // id
        if (!$isAdd) {
            if (empty($postData['id'])) {
                return error('打印机ID不能为空');
            }
            $params['id'] = $postData['id'];
        }

        // check name
        if (empty($postData['name'])) {
            return error('打印机名称不能为空');
        } else {
            $andWhere = [];
            if (!$isAdd) {
                $andWhere = [
                    'and',
                    ['<>', 'id', $postData['id']]
                ];
            }

            $query = PrinterModel::find()->where(
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
                return error('打印机名称已存在');
            }
        }

        $params['name'] = $postData['name'];

        // 应用位置
        $params['location'] = $postData['location'] ?? '';

        // 配置 (编辑不可修改配置)
        if ($isAdd) {
            $config = $this->checkConfig();
            if (is_error($config)) {
                return $config;
            }
            $params = array_merge($params, $config);
        }

        // 品牌
        $params['brand'] = PrinterTypeConstant::getText($postData['type']);
        // 类型
        $params['type'] = $postData['type'];

        return $params;
    }

    /**
     * @return array
     */
    private function checkConfig(): array
    {
        $postData = RequestHelper::post();
        array_walk($postData, function (&$v) {
            $v = is_string($v) ? trim($v) : $v;
        });

        $params = [];

        switch ($postData['type']) {
            case PrinterTypeConstant::PRINTER_YLY_AUTH;

                // 终端号
                if (empty($postData['machine_code'])) {
                    return error('终端号不能为空');
                }
                $params['config']['machine_code'] = $postData['machine_code'];

                // 打印机秘钥
                if (empty($postData['msign'])) {
                    return error('打印机密钥不能为空');
                }
                $params['config']['msign'] = $postData['msign'];

                // 用户ID
                if (empty($postData['client_id'])) {
                    return error('用户ID不能为空');
                }
                $params['config']['client_id'] = $postData['client_id'];

                // 应用秘钥
                if (empty($postData['client_secret'])) {
                    return error('应用秘钥不能为空');
                }
                $params['config']['client_secret'] = $postData['client_secret'];

                break;
            case PrinterTypeConstant::PRINTER_FEY;

                // user
                if (empty($postData['user'])) {
                    return error('user不能为空');
                }
                $params['config']['user'] = $postData['user'];

                // ukey
                if (empty($postData['ukey'])) {
                    return error('ukey不能为空');
                }
                $params['config']['ukey'] = $postData['ukey'];

                // sn
                if (empty($postData['sn'])) {
                    return error('编码不能为空');
                }
                $params['config']['sn'] = $postData['sn'];

                // key 秘钥
                if (empty($postData['key'])) {
                    return error('秘钥不能为空');
                }
                $params['config']['key'] = $postData['key'];
                break;

            default:
                return error('打印机品牌异常');
        }

        return $params;
    }

}