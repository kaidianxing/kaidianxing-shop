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

namespace shopstar\models\printer;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\printer\PrinterComponent;
use shopstar\constants\printer\PrinterSceneConstant;
use shopstar\constants\printer\PrinterTypeConstant;
use shopstar\models\order\OrderModel;
use shopstar\models\printer\handle\TemplateContentHandle;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%app_printer_task}}".
 *
 * @property int $id auto increment
 * @property string $name 任务名称
 * @property string $scene 打印场景 1下单2付款3确认收货
 * @property int $template_id 打印模板
 * @property int $times 打印次数
 * @property string $printer_id 打印机
 * @property int $is_deleted 是否删除 0否1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $delivery 快递方式
 * @property string $verify_point 核销点 0是所有核销点
 * @property string $not_select_verify_point 未选择核销点是否打印1是0否
 * @property string $is_verify_all 是否核销全部打印 1是0否
 * @property string $is_verify_point_all 是否核销打印全部核销点
 */
class PrinterTaskModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_printer_task}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['template_id', 'times', 'is_deleted', 'is_verify_point_all', 'is_verify_all', 'not_select_verify_point'], 'integer'],
            [['created_at', 'updated_at', 'verify_point'], 'safe'],
            [['name', 'scene', 'printer_id'], 'string', 'max' => 64],
            [['delivery'], 'string', 'max' => 191],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment',
            'name' => '任务名称',
            'scene' => '打印场景 1下单2付款3确认收货',
            'template_id' => '打印模板',
            'times' => '打印次数',
            'printer_id' => '打印机',
            'is_deleted' => '是否删除 0否1是',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'delivery' => '快递方式',
            'verify_point' => '核销点 0是所有核销点',
            'not_select_verify_point' => '未选择核销点是否打印1是0否',
            'is_verify_all' => '是否核销全部打印 1是0否',
            'is_verify_point_all' => '是否核销打印全部核销点 1是0否'
        ];
    }

    /**
     * 查看是否有打印机在任务中
     * @param $printerId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkPrinter($printerId)
    {
        $task = PrinterTaskModel::find()
            ->where([
                'printer_id' => $printerId,
                'is_deleted' => 0
            ])
            ->select('name')
            ->column();

        return $task;
    }

    /**
     * @param $templateId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkTemplate($templateId)
    {
        $task = PrinterTaskModel::find()
            ->where([
                'template_id' => $templateId,
                'is_deleted' => 0
            ])
            ->select('name')
            ->column();

        return $task;
    }

    /**
     * 获取场景名称
     * @param array $scenes
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getSceneById(array $scenes)
    {
        $returnData = [];
        foreach ($scenes as $scene) {
            $returnData[] = PrinterSceneConstant::getOneByCode($scene)['text'] ?? '';
        }

        return $returnData;
    }

    /**
     * 手动打印
     * @param int $printerId
     * @param int $templateId
     * @param int $orderId
     * @return array|bool|mixed|PrinterManualLogModel|\Psr\Http\Message\ResponseInterface
     * @author 青岛开店星信息技术有限公司
     */
    public static function execute(int $printerId, int $templateId, int $orderId)
    {

        // 验证打印机状态
        $printer = PrinterModel::findOne(['id' => $printerId, 'status' => 1, 'is_deleted' => 0]);

        if (empty($printer)) {
            return error('打印机状态不正确');
        }

        if ($printer->type == PrinterTypeConstant::PRINTER_YLY_AUTH && empty($printer->access_token)) {
            return error('打印机未授权，请先配置打印机');
        }

        $printerConfig = Json::decode($printer->config);
        if ($printer->type == PrinterTypeConstant::PRINTER_YLY_AUTH) {
            $printerConfig['access_token'] = $printer->access_token;
        }

        // 验证模板状态
        $template = PrinterTemplateModel::findOne(['id' => $templateId, 'is_deleted' => 0]);

        if (empty($template)) {
            return error('打印机模板状态不正确');
        }

        // 获取打印内容
        $templateHandle = new TemplateContentHandle($templateId, $orderId, $printer->type);
        $content = $templateHandle->getTemplatePrintContent();

        // 打印机驱动
        $driver = PrinterComponent::getInstance(PrinterTypeConstant::getIdentify($printer->type), $printerConfig);
        $printerResult = $driver->printIndex($content);

        if (is_error($printerResult)) {
            return $printerResult;
        }

        // 记录日志
        $logResult = PrinterManualLogModel::addLog($orderId, $printerId, $templateId);

        if (is_error($logResult)) {
            return $logResult;
        }

        return true;
    }

    /**
     * 执行打印场景任务
     * @param $scene
     * @param $orderId
     * @return array|bool|mixed|PrinterLogModel|\Psr\Http\Message\ResponseInterface
     * @author 青岛开店星信息技术有限公司
     */
    public static function executeTask($scene, $orderId)
    {

        $order = OrderModel::Where([
            'id' => $orderId,
        ])->first();

        $where = [
            'and',
            ['task.is_deleted' => 0],
            ['template.is_deleted' => 0],
            'find_in_set("' . $scene . '",scene)'
        ];

        // 获取符合要求的task
        $tasks = PrinterTaskModel::find()
            ->where($where)
            ->select([
                'task.id',
                'task.name',
                'task.scene',
                'task.times',
                'task.printer_id',
                'task.template_id',
                'task.delivery',
                'is_verify_all',
                'is_verify_point_all',
                'not_select_verify_point',
                'verify_point',
            ])
            ->alias('task')
            ->leftJoin(PrinterTemplateModel::tableName() . ' template', 'template.id = task.template_id')
            ->get();

        if (empty($tasks)) {
            return true;
        }

        // 获取所有打印机
        $printers = PrinterModel::find()
            ->where(['status' => 1, 'is_deleted' => 0])
            ->select('id, type, config, access_token')
            ->get();

        $printerMap = array_column($printers, NULL, 'id');
        foreach ($tasks as $key => $task) {
            // 判断scene
            $taskScene = $task['scene'];
            $taskSceneArr = explode(',', $taskScene);
            if (!in_array($scene, $taskSceneArr)) {
                unset($tasks[$key]);
                continue;
            }

            //判断快递方式
            $delivery = Json::decode($task['delivery']);
            if (!is_array($delivery) || !in_array($order['dispatch_type'], $delivery)) {
                unset($tasks[$key]);
                continue;
            }

            $printerIds = $task['printer_id'];
            if ($printerIds) {
                $printerArr = explode(',', $printerIds);
                foreach ($printerArr as $printerId) {
                    if (isset($printerMap[$printerId])) {
                        $tasks[$key]['printer_config'][] = $printerMap[$printerId];
                    }
                }
                if (empty($tasks[$key]['printer_config'])) {
                    unset($tasks[$key]);
                }

            } else {
                unset($tasks[$key]);
            }
        }

        if (empty($tasks)) {
            return true;
        }

        // 执行打印任务
        foreach ($tasks as $task) {
            $taskId = $task['id'];
            $times = $task['times']; //打印次数
            $templateId = $task['template_id'];
            $printerConfigs = $task['printer_config']; // 打印机配置

            foreach ($printerConfigs as $item) {

                // 获取打印机配置
                $printerConfig = Json::decode($item['config']);
                $item['type'] == PrinterTypeConstant::PRINTER_YLY_AUTH && $printerConfig['access_token'] = $item['access_token'];

                $templateHandle = new TemplateContentHandle($templateId, $orderId, $item['type'], $times);
                $content = $templateHandle->getTemplatePrintContent();

                $driver = PrinterComponent::getInstance(PrinterTypeConstant::getIdentify($item['type']), $printerConfig);

                $printerResult = $driver->printIndex($content, $times);

                if (is_error($printerResult)) {
                    return $printerResult;
                }

                // 记录日志
                $logResult = PrinterLogModel::addLog($orderId, $taskId, $scene);

                if (is_error($logResult)) {
                    return $logResult;
                }
            }
        }

        return true;
    }
}