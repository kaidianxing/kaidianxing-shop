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

namespace shopstar\admin\virtualAccount;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\virtualAccount\VirtualAccountDataConstant;
use shopstar\constants\virtualAccount\VirtualAccountLogConstant;
use shopstar\exceptions\sysset\MallException;
use shopstar\exceptions\virtualAccount\VirtualAccountException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ExcelHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\order\OrderModel;
use shopstar\models\virtualAccount\VirtualAccountDataModel;
use shopstar\models\virtualAccount\VirtualAccountModel;
use shopstar\models\virtualAccount\VirtualAccountOrderMapModel;
use yii\helpers\Json;

/**
 * 卡密库-数据
 * Class IndexController
 * @package shopstar\admin\virtualAccount
 * @author 青岛开店星信息技术有限公司
 */
class VirtualAccountDataController extends KdxAdminApiController
{

    /**
     * @var array 允许GET携带Header参数Actions
     */
    public $configActions = [
        'allowHeaderActions' => [
            'download',
            'index'
        ]
    ];

    /**
     * 下载模板
     * @var array
     */
    public static $virtualAccountTemplace = [
        ['title' => '权重', 'field' => '', 'width' => 12],
    ];

    public static $defaultFilePath = 'tmp/virtualAccount/';

    /**
     * 查看卡密库数据
     * @return \yii\web\Response
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $status = RequestHelper::getInt('status');
        $virtualAccountId = RequestHelper::getInt('virtual_account_id');
        $startTime = RequestHelper::get('start_time');
        $endTime = RequestHelper::get('end_time');
        $export = RequestHelper::get('export');
        if (!empty($startTime) && !empty($endTime)) {
            $andWhere[] = ['between', 'virtual.created_at', $startTime, $endTime];
        }
        if (!$export) {
            if (!empty($status) && $status != 0) {
                $andWhere[] = ['!=', 'virtual.status', 0];
            } else {
                $andWhere[] = ['virtual.status' => $status];
            }
        }

        $params = [
            'alias' => 'virtual',
            'where' => [
                'virtual.virtual_account_id' => $virtualAccountId,
                'virtual.is_delete' => 0,
            ],
            'andWhere' => $andWhere ?? [],
            'leftJoins' => [
                [VirtualAccountOrderMapModel::tableName() . ' order_virtual', 'order_virtual.virtual_account_data_id = virtual.id'],
                [OrderModel::tableName() . ' order', 'order.id = order_virtual.order_id'],
            ],
            'select' => [
                'virtual.id',
                'virtual.sort',
                'virtual.data',
                'virtual.status',
                'virtual.created_at',
            ],
            'searchs' => [
                ['virtual.key', 'like', 'keyword']
            ],
            'orderBy' => [
                'virtual.sort' => SORT_DESC,
                'virtual.id' => SORT_DESC
            ]
        ];

        // 如果是已出售列表 则按订单时间倒序
        if ($status) {
            $params['orderBy'] = ['order.created_at' => SORT_DESC];
            $params['select'] = [
                'virtual.id',
                'virtual.sort',
                'virtual.data',
                'virtual.status',
                'virtual.created_at',
                'order.order_no',
                'order.created_at order_created_at',
                'order.id order_id',
                'order.status order_status',
            ];
            $params['where']['order_virtual.is_deleted'] = 0;
        }

        if ($export) {
            // 如果是导出 则按卡密库数据状态正序 未出售的在前
            $params['orderBy'] = ['virtual.status' => SORT_ASC];
            // 导出时增加字段
            $params['select'][] = 'order.order_no';
            $params['select'][] = 'order.created_at order_created_at';
        }

        $data = VirtualAccountDataModel::getColl($params, [
            'callable' => function (&$row) {
                $row['status_text'] = VirtualAccountDataModel::$statusField[$row['status']];
                $row['order_no'] = !is_null($row['order_no']) ? $row['order_no'] : '-';
                $row['order_created_at'] = !is_null($row['order_created_at']) ? $row['order_created_at'] : '-';
            },
            'pager' => !$export,
            'onlyList' => (bool)$export,
        ]);

        // 导出
        if ($export) {
            try {
                $config = VirtualAccountModel::getInfoToId($virtualAccountId);
                $config = $config['config'];
                // 处理json串拼接导出数据
                $newColumns = VirtualAccountDataModel::exportField($data, $config);
                $columns = VirtualAccountDataModel::$exportColumns;
                ExcelHelper::export($data, array_merge($newColumns, $columns), '卡密库数据导出');
            } catch (\Throwable $exception) {
                throw new VirtualAccountException(VirtualAccountException::MEMBER_EXPORT_FAIL);
            }
            die;
        }

        return $this->success($data);
    }

    /**
     * add
     * @return void
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $result = $this->getParams();
        $this->result(['data' => $result]);
    }

    /**
     * 快速更新接口
     * @return \yii\web\Response
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate(): \yii\web\Response
    {
        $id = RequestHelper::postInt('id');
        $virtualAccountId = RequestHelper::postInt('virtual_account_id');
        if ($id == 0 || $virtualAccountId == 0) {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }
        $data = RequestHelper::post('data');
        $model = VirtualAccountDataModel::findOne(['id' => $id]);
        if ($model) {
            // 验证重复
            if (VirtualAccountDataModel::DeDuplication($virtualAccountId, md5($data))) {
                throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_DATA_ERROR);
            }
            $model->data = $data;
            $model->data_md5 = md5($data);
            $model->updated_at = DateTimeHelper::now();
            $model->save();
        } else {
            // 卡密库数据不存在
            throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_DATA_NOT_NULL);

        }
        return $this->success();
    }

    /**
     * 处理参数
     * @return array
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function getParams(): array
    {
        $params = RequestHelper::post();
        $errorArray = [];
        if (0 < count($params['data']) && count($params['data']) <= 1000) {
            $i = 0;
            foreach ($params['data'] as $value) {
                $dataMd5 = md5($value['data']);

                $virtualAccountInfo = VirtualAccountModel::findOne(['id' => $params['virtual_account_id']]);
                // 判断是否开启了排重
                if ($virtualAccountInfo->repeat) {
                    if (VirtualAccountDataModel::DeDuplication($params['virtual_account_id'], $dataMd5)) {
                        $errorArray[] = $value;
                        continue;
                    }
                }
                VirtualAccountDataModel::easyAdd([
                    'attributes' => [
                        'virtual_account_id' => $params['virtual_account_id'],
                        'sort' => $value['sort'] ?: 1,
                        'data' => $value['data'],
                        'data_md5' => $dataMd5,
                        'key' => Json::decode($value['data'])['value1'],
                        'created_at' => DateTimeHelper::now(),
                        'create_way' => VirtualAccountDataConstant::CREATE_WAY_ADD,
                    ]
                ]);
                $i++;
            }

            // 增加库存
            $this->updateCount($params['virtual_account_id'], $i);

            // 日志
            LogModel::write(
                $this->userId,
                VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_ADD_DATA,
                VirtualAccountLogConstant::getText(VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_ADD_DATA),
                $params['virtual_account_id'],
                [
                    'log_data' => [],
                    'log_primary' => [
                        'id' => $params['virtual_account_id'],
                        '添加卡密数据' => '新增数据',
                    ],
                ]
            );
        }

        return $errorArray;
    }

    /**
     * 下载卡密模板
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDownload()
    {
        $virtualAccountId = RequestHelper::getInt('virtual_account_id');
        if ($virtualAccountId == 0) {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }
        $columns = VirtualAccountModel::download($virtualAccountId);
        if (empty($columns)) {
            throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_NOT_NULL);
        }

        // 日志
        LogModel::write(
            $this->userId,
            VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_ADD_DATA,
            VirtualAccountLogConstant::getText(VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_ADD_DATA),
            $virtualAccountId,
            [
                'log_data' => [],
                'log_primary' => [
                    'id' => $virtualAccountId,
                    '添加卡密数据' => '下载模板',
                ],
            ]
        );

        ExcelHelper::export([], array_merge(self::$virtualAccountTemplace, $columns), '卡密库模板');
        die;
    }

    /**
     * excel导入
     * @return array|\yii\web\Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionImport()
    {
        $file = RequestHelper::post('file', 'file');
        $virtualAccountId = RequestHelper::postInt('virtual_account_id');
        if ($virtualAccountId == 0 || $file != 'file') {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }
        $data = ExcelHelper::import($file, 2, self::$defaultFilePath);

        // 导入文件是否解析成功
        if (!$data) {
            throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_EXCEL_PARSING_ERROR);
        }

        // 导入数量限制
        if (count($data) > 1000) {
            throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_EXCEL_IMPORT_COUNT_MAX);
        }

        $result = $this->splicingData($virtualAccountId, $data);
        if ($result['error_count'] > 0) {
            // 存到tmp中 以供下载
            $columns = VirtualAccountModel::download($virtualAccountId);
            $columns = $this->processField(array_merge(self::$virtualAccountTemplace, $columns));
            $file = ExcelHelper::export($result['error_data'], $columns, '卡密库导入失败数据', self::$defaultFilePath);
            $result['file_path'] = $file['filepath'];
        }
        unset($result['error_data']);

        // 日志
        LogModel::write(
            $this->userId,
            VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_ADD_DATA,
            VirtualAccountLogConstant::getText(VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_ADD_DATA),
            $virtualAccountId,
            [
                'log_data' => [],
                'log_primary' => [
                    'id' => $virtualAccountId,
                    '添加卡密数据' => 'Excel导入',
                ],
            ]
        );

        return $this->result(['data' => $result]);
    }

    /**
     * 拼接数据
     * @param int $id
     * @param array $data
     * @return array
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function splicingData(int $id, array $data): array
    {
        $errorArray = [];
        $i = 0;
        $data = array_filter($data);
        $virtualAccountInfo = VirtualAccountModel::findOne(['id' => $id]);
        foreach ($data as $dataKey => $dataValue) {
            $newArray = [];
            $sort = $dataValue[0];
            unset($dataValue[0]);
            // 去掉权重，做减一处理
            foreach ($dataValue as $kk => $vv) {
                $newArray['value' . ($kk)] = !is_null($vv) ? $vv : '';
            }
            $newData = Json::encode($newArray);
            // 判断是否开启了排重
            if ($virtualAccountInfo->repeat) {
                $result = VirtualAccountDataModel::DeDuplication($id, md5($newData));
                if ($result) {
                    // 重复值
                    $dataValue[0] = $sort;
                    $errorArray[] = $dataValue;
                    continue;
                }
            }
            VirtualAccountDataModel::easyAdd([
                'attributes' => [
                    'virtual_account_id' => $id,
                    'sort' => $sort ? ((int)$sort >= 99999 ? 99999 : (int)$sort) : 1,
                    'data' => $newData,
                    'data_md5' => md5($newData),
                    'key' => (string)Json::decode($newData)['value1'],
                    'created_at' => DateTimeHelper::now(),
                    'create_way' => VirtualAccountDataConstant::CREATE_WAY_IMPORT,
                ]
            ]);
            $i++;
        }

        // 增加库存
        $this->updateCount($id, $i);

        return ['success_count' => $i, 'error_count' => count($errorArray), 'error_data' => $errorArray];
    }

    /**
     * 查询卡密库结构
     * @return array|\yii\web\Response
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetData()
    {
        $virtualAccountId = RequestHelper::getInt('virtual_account_id');
        if ($virtualAccountId == 0) {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }
        $result = VirtualAccountModel::getInfoToId($virtualAccountId);
        return $this->result(['data' => $result]);
    }

    /**
     * 更新卡密数据权重值及删除
     * @return array|\yii\web\Response
     * @throws VirtualAccountException
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdateData()
    {
        $id = RequestHelper::post('id');
        $sort = RequestHelper::post('sort');
        $isDelete = RequestHelper::postInt('is_delete');
        $flag = 'is_delete';
        if ($sort != '') {
            $flag = 'sort';
        }

        if (!$id) {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }
        if (isset($flag)) {
            VirtualAccountDataModel::updateData($id, $flag, $sort);
        }
        if ($flag == 'is_delete') {
            // 根据删除卡密库数据id查询卡密库id
            VirtualAccountDataModel::updateReduceStock($id);
            // 日志
            LogModel::write(
                $this->userId,
                VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_DELETE_DATA,
                VirtualAccountLogConstant::getText(VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_DELETE_DATA),
                $id,
                [
                    'log_data' => [],
                    'log_primary' => [
                        'id' => $id,
                        '添加卡密数据' => '删除数据',
                    ]
                ]
            );
        }

        return $this->result();
    }

    /**
     * 增加库存
     * @param $virtualAccountId
     * @param $count
     * @return void
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function updateCount($virtualAccountId, $count)
    {
        // 增加库存
        VirtualAccountDataModel::updateAddStock($virtualAccountId, $count);
    }

    /**
     * 处理导出的拼接字段标识
     * @param $data
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function processField($data)
    {
        if ($data) {
            foreach ($data as $key => &$value) {
                $value['field'] = $key;
            }
        }
        return $data ?? [];
    }

}