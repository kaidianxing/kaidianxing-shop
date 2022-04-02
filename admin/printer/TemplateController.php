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

use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\log\LogModel;
use shopstar\constants\printer\PrinterLogConstant;
use shopstar\constants\printer\PrinterTemplateTypeConstant;
use shopstar\constants\printer\PrinterTemplateWidthConstant;
use shopstar\exceptions\printer\PrinterTemplateException;
use shopstar\models\printer\PrinterModel;
use shopstar\models\printer\PrinterTaskModel;
use shopstar\models\printer\PrinterTemplateModel;
use shopstar\bases\KdxAdminApiController;
use yii\helpers\Json;

/**
 * 小票打印模板
 * Class TemplateController
 * @package apps\printer\manage
 */
class TemplateController extends KdxAdminApiController
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
                [['temp.name'], 'like', 'keyword']
            ],
            'where' => [
                'temp.is_deleted' => 0,
            ],
            'alias' => 'temp',
            'leftJoin' => [PrinterTaskModel::tableName() . ' task', 'temp.id = task.template_id'],
            'select' => [
                'temp.id as template_id',
                'temp.name',
                'temp.type',
                'IF (task.template_id AND task.is_deleted = 0, 1, 0) AS status ',
                'temp.content',
                'temp.qrcode',
                'temp.footer',
                'task.is_deleted as task_deleted',
                'task.printer_id',
                'temp.created_at'
            ],
            'orderBy' => [
                'temp.created_at' => SORT_DESC
            ],
            //'groupBy' => 'temp.id'
        ];

        $returnData = [];

        // 打印机
        $printers = PrinterModel::find()->where(['is_deleted' => 0])->select('id, status, name, location, type, brand')->get();
        $printersMap = array_column($printers, NULL, 'id');

        PrinterTemplateModel::getColl($params, [
            'callable' => function (&$row) use (&$returnData, $printersMap) {
                if (isset($returnData[$row['template_id']]) && !empty($row['printer_id'])) {
                    $printersItem = PrinterModel::getPrinterNameMapById($row['printer_id'], $printersMap);
                    if (!empty($printersItem)) {
                        foreach ($printersItem as $printer) {
                            if (array_search($printer['id'], array_column($returnData[$row['template_id']]['printer_id'], 'id')) === false) {
                                $returnData[$row['template_id']]['printer_id'] = array_merge($returnData[$row['template_id']]['printer_id'], [$printer]);
                            }
                        }
                    }
                    if ($row['status'] == 1) {
                        $returnData[$row['template_id']]['status'] = 1;
                    }

                } else {
                    $returnData[$row['template_id']] = [
                        'id' => $row['template_id'],
                        'name' => $row['name'],
                        'type_text' => PrinterTemplateTypeConstant::getText($row['type']),
                        'status' => $row['status'],
                        'content' => $row['content'],
                        'footer' => $row['footer'],
                        'printer_id' => $row['task_deleted'] ? [] : PrinterModel::getPrinterNameMapById($row['printer_id'], $printersMap),
                        'created_at' => $row['created_at'],
                        'type' => $row['type']
                    ];
                }
            },
        ]);

        if (RequestHelper::get('only_list', 0)) {
            $list = array_values($returnData);
        } else {
            $list = [
                'total' => count($returnData),
                'list' => array_values($returnData),
                'page' => RequestHelper::get('page', 1),
                'page_size' => RequestHelper::get('pagesize', 20)
            ];
        }

        return $this->result(['data' => $list]);
    }

    /**
     * 创建
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $params = $this->checkParams();

        if (is_error($params)) {
            return $this->result($params['message'], PrinterTemplateException::PRINTER_TEMPLATE_ADD_PARAMS_INVALID);
        }

        $result = PrinterTemplateModel::addResult($params);

        if (is_error($result)) {
            return $this->result($result['message'], PrinterTemplateException::PRINTER_TEMPLATE_ADD_RESULT_FAILED);
        }

        // 日志
        $logPrimary = [
            'id' => $result->id,
            '类型' => PrinterTemplateTypeConstant::getText($result->type),
            '打印宽度' => PrinterTemplateWidthConstant::getText($result->width),
            '名称' => $result->name,
            '内容' => $result->content,
            '二维码' => $result->qrcode,
            '底部信息' => $result->footer
        ];

        LogModel::write(
            $this->userId,
            PrinterLogConstant::PRINTER_TEMPLATE_ADD,
            PrinterLogConstant::getText(PrinterLogConstant::PRINTER_TEMPLATE_ADD),
            $result->id,
            [
                'log_data' => $result->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    PrinterLogConstant::PRINTER_TEMPLATE_ADD,
                    PrinterLogConstant::PRINTER_TEMPLATE_SAVE,
                ]
            ]
        );

        return $this->result();
    }

    /**
     * 编辑
     * @return array|\yii\web\Response
     * @throws PrinterTemplateException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $templateId = RequestHelper::getInt('id');

        $template = PrinterTemplateModel::findOne(['id' => $templateId, 'is_deleted' => 0]);

        if (empty($template)) {
            throw new PrinterTemplateException(PrinterTemplateException::PRINTER_TEMPLATE_EDIT_RECORD_INVALID);
        }

        $template->content = Json::decode($template->content);

        $template->toArray();

        return $this->result(['data' => $template]);
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
            return $this->result($params['message'], PrinterTemplateException::PRINTER_TEMPLATE_SAVE_PARAMS_INVALID);
        }

        $result = PrinterTemplateModel::saveResult($params);

        if (is_error($result)) {
            return $this->result($result['message'], PrinterTemplateException::PRINTER_TEMPLATE_SAVE_RESULT_FAILED);
        }

        // 日志
        $logPrimary = [
            'id' => $result->id,
            '类型' => PrinterTemplateTypeConstant::getText($result->type),
            '打印宽度' => PrinterTemplateWidthConstant::getText($result->width),
            '名称' => $result->name,
            '内容' => $result->content,
            '二维码' => $result->qrcode,
            '底部信息' => $result->footer
        ];
        LogModel::write(
            $this->userId,
            PrinterLogConstant::PRINTER_TEMPLATE_SAVE,
            PrinterLogConstant::getText(PrinterLogConstant::PRINTER_TEMPLATE_SAVE),
            $result->id,
            [
                'log_data' => $result->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    PrinterLogConstant::PRINTER_TEMPLATE_ADD,
                    PrinterLogConstant::PRINTER_TEMPLATE_SAVE,
                ]
            ]
        );

        return $this->result();
    }

    /**
     * 删除
     * @return array|\yii\web\Response
     * @throws PrinterTemplateException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $templateId = RequestHelper::getInt('id');

        $printer = PrinterTemplateModel::findOne(['id' => $templateId, 'is_deleted' => 0]);

        if (empty($printer)) {
            throw new PrinterTemplateException(PrinterTemplateException::PRINTER_TEMPLATE_DELETE_RECORD_INVALID);
        }

        // 检测是否有打印任务
        if (RequestHelper::get('is_check', 0)) {
            $task = PrinterTaskModel::checkTemplate($templateId);
            if (!empty($task)) {
                $taskStr = '该模版正在执行【' . implode(',', $task) . '】打印任务，删除后该打印机则不能执行打印任务，是否要删除';
                return $this->result($taskStr);
            }
            return $this->result();
        }

        $printer->is_deleted = 1;

        if (!$printer->save()) {
            return $this->error($printer->getErrorMessage());
        }

        // 日志
        $logPrimary = [
            'id' => $printer->id,
            '类型' => PrinterTemplateTypeConstant::getText($printer->type),
            '打印宽度' => PrinterTemplateWidthConstant::getText($printer->width),
            '名称' => $printer->name,
            '内容' => $printer->content,
            '二维码' => $printer->qrcode,
            '底部信息' => $printer->footer
        ];
        LogModel::write(
            $this->userId,
            PrinterLogConstant::PRINTER_TEMPLATE_DELETE,
            PrinterLogConstant::getText(PrinterLogConstant::PRINTER_TEMPLATE_DELETE),
            $printer->id,
            [
                'log_data' => $printer->attributes,
                'log_primary' => $logPrimary
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

        $params = [];

        // id
        if (!$isAdd) {
            if (empty($postData['id'])) {
                return error('打印模板ID不能为空');
            }
            $params['id'] = $postData['id'];
        }

        // 验证类型
        $checkType = PrinterTemplateTypeConstant::getOneByCode($postData['type']);
        if (is_null($checkType)) {
            return error('模板类型错误');
        }
        $params['type'] = $postData['type'];

        // 验证打印纸
        $checkWidth = PrinterTemplateWidthConstant::getOneByCode($postData['width']);
        if (is_null($checkWidth)) {
            return error('模板宽度错误');
        }
        $params['width'] = $postData['width'];

        // 验证名称
        if (empty($postData['name'])) {
            return error('模板名称不能为空');
        } else {
            $andWhere = [];
            if (!$isAdd) {
                $andWhere = [
                    'and',
                    ['<>', 'id', $postData['id']]
                ];
            }

            $query = PrinterTemplateModel::find()->where(
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
                return error('打印模板名称已存在');
            }
        }
        if (mb_strlen($postData['name']) > 20) {
            return error('最大支持20个字符');
        }
        $params['name'] = $postData['name'];

        // 验证内容
//        $templateMap = PrinterTemplateModel::TEMPLATE_MAP;
//        foreach ($templateMap as $templateInfo => $item) {
//            foreach ($item as $key => $value) {
//                if (!isset($postData[$templateInfo][$key])) {
//                    $content[$templateInfo][$key] = intval($value);
//                } else {
//                    $content[$templateInfo][$key] = intval($postData[$templateInfo][$key]);
//
//                }
//            }
//        }
        $content = Json::decode($postData['content']);
        $params['content'] = $postData['content'];

        // 判断是否需要二维码
        $isShopQrcode = false;
        foreach ($content as $item) {
            if ($item['type'] == 'shop_info') {
                foreach ($item['children'] as $child) {
                    if ($child['type'] == 'qrcode') {
                        $isShopQrcode = $child['status'] == 0 ? false : true;
                    }
                }
            }
        }

        // 验证二维码
        if ($isShopQrcode) {
            if (empty($postData['qrcode'])) {
                return error('二维码链接不能为空');
            }
            if (!StringHelper::exists($postData['qrcode'], ['http://', 'https://'], StringHelper::SEL_OR)) {
                return error('请传入正确的二维码链接');
            }
            $params['qrcode'] = $postData['qrcode'];
        } else {
            $params['qrcode'] = '';
        }

        // 验证底部信息
        if (empty($postData['footer'])) {
            return error('底部信息不能为空');
        }
        $params['footer'] = $postData['footer'];

        return $params;
    }

}