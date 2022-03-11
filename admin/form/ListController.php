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

namespace shopstar\admin\form;

use shopstar\constants\ClientTypeConstant;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\ExcelHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\goods\GoodsCartModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\order\OrderModel;
use shopstar\constants\form\FormLogConstant;
use shopstar\constants\form\FormTypeConstant;
use shopstar\exceptions\form\FormException;
use shopstar\models\form\FormLogModel;
use shopstar\models\form\FormModel;
use shopstar\bases\KdxAdminApiController;
use yii\helpers\Json;

/**
 * 系统表单
 * Class ListController
 * @package apps\form\manage
 */
class ListController extends KdxAdminApiController
{
    /**
     * 允许get参数
     * @var string[]
     * @author 青岛开店星信息技术有限公司.
     */
    public $configActions = [
        'allowHeaderActions' => [
            'download'
        ]
    ];


    /**
     * 列表搜索
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $params = [
            'searchs' => [
                ['type', 'int', 'type'],
                ['status', 'int', 'status'],
                [['name'], 'like', 'keyword']
            ],
            'where' => [
                'is_deleted' => 0,
                'type' => [
                    FormTypeConstant::FORM_TYPE_ORDER,
                    FormTypeConstant::FORM_TYPE_COMMISSION,
                    FormTypeConstant::FORM_TYPE_MEMBER,
                    FormTypeConstant::FORM_TYPE_GOODS,
                ]
            ],
            'select' => [
                'id',
                'name',
                'type',
                'status',
                'content',
                'created_at',
                'count'
            ],
            'orderBy' => [
                'created_at' => SORT_DESC
            ]
        ];


        $list = FormModel::getColl($params, [
            'callable' => function (&$row) {
                $row['type_text'] = FormTypeConstant::getText($row['type']);
            },
            'pager' => $this->clientType == ClientTypeConstant::MANAGE_SHOP_ASSISTANT ? false : true,
        ]);

        return $this->result(['data' => $list]);
    }

    /**
     * 创建
     * @return array|\yii\web\Response
     * @throws FormException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $params = RequestHelper::post();

        // 参数校验
        $this->checkParams($params);

        $result = FormModel::addResult($params);

        if (is_error($result)) {
            throw new FormException(FormException::FORM_LIST_ADD_INVALID, $result['message']);
        }
        // 日志
        $logPrimary = [
            'id' => $result->id,
            '表单类型' => FormTypeConstant::getText($result->type),
            '表单名称' => $result->name,
            '表单内容' => $this->formatDownloadContent($result->content),
        ];

        LogModel::write(
            $this->userId,
            FormLogConstant::FORM_ADD,
            FormLogConstant::getText(FormLogConstant::FORM_ADD),
            $result->id,
            [
                'log_data' => $result->attributes,
                'log_primary' => $logPrimary,
            ]
        );

        return $this->result('添加表单成功');
    }

    /**
     * 模板修改
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        //脏数据
        $dirtyData = [];
    
        $result = FormModel::easyEdit([
            'andWhere' => [],
            'beforeSave' => function (FormModel $result, &$dirtyData) {
            
                $dirtyData = $result->getDirtyAttributes2(false, true);
            
                //验证名称重复
                $exist = FormModel::checkName($result->type, $result->name, $result->id);
                if (!$exist) {
                    return error('表单名称重复，请重新填写');
                }
            },
            'afterSave' => function ($data) use ($dirtyData) {

                // 如果表单有修改 重选购物车
                if ($data['content'] != $dirtyData['content']) {
                    // 修改表单 全部重选
                    // 查找当前表单应用的商品
                    $goods = GoodsModel::find()->select('id')->where(['form_id' => $data['id']])->get();
                    GoodsCartModel::updateAll(
                        ['is_selected' => 0, 'is_reelect' => 1],
                        ['goods_id' => array_column($goods, 'id')]
                    );

                }

                // 日志
                LogModel::write(
                    $this->userId,
                    FormLogConstant::FORM_EDIT,
                    FormLogConstant::getText(FormLogConstant::FORM_EDIT),
                    $data->id,
                    [
                        'log_data' => $data->attributes,
                        'log_primary' => [
                            'id' => $data->id,
                            '表单类型' => FormTypeConstant::getText($data->type),
                            '表单名称' => $data->name,
                            '表单内容' => $this->formatDownloadContent($data->content),
                        ],
                    ]
                );
            },
            'attributes' => [
                'updated_at' => date('Y-m-d H:i:s', time())
            ]
        ]);
        return $this->result($result);
    }


    /**
     * 统计条数信息更新
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate()
    {
        $formId = RequestHelper::getInt('id');

        //判断ID存在
        if (empty($formId)) {
            return error('未获取到表单ID');
        }

        $update = FormModel::updateData($formId);

        return $this->result($update);
    }

    /**
     * 详情查看列表
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $formId = RequestHelper::getInt('id');
        if (empty($formId)) {
            return error('未获取到表单ID');
        }
        $formType = RequestHelper::getInt('type');

        $orderId = RequestHelper::get('order_no');

        //链表数据
        $leftJoins = [
            [MemberModel::tableName() . ' member', 'member.id = log.member_id'],
            [FormModel::tableName() . ' form', 'form.id = log.form_id'],
        ];
        //查看数据
        $select = [
            'log.id',
            'form.type',
            'form.name',
            'member.nickname',
            'member.id as member_id',
            'log.content',
            'log.created_at',
            'log.source',
        ];
        //如果是商品表单，连两个表
        if ($formType == 4) {
            $leftJoins[] = [GoodsModel::tableName() . ' goods', 'goods.id = log.goods_id'];
            $leftJoins[] = [OrderModel::tableName() . ' order', 'order.id = log.order_id'];

            $select = array_merge($select, [
                'goods.thumb',
                'goods.title',
                'goods.id as goods_id',
                'order.id as order_id',
                'order.order_no',
                'order.status',
                'order.order_type'
            ]);
        }
        //如果是下单类型，一个表
        if ($formType == 1) {
            $leftJoins[] = [OrderModel::tableName() . ' order', 'order.id = log.order_id'];
            $select[] = 'order.id as order_id';
            $select[] = 'order.order_no';
            $select[] = 'order.status';
            $select[] = 'order.order_type';
        }

        $searchs = [];
        $searchs[] = [['nickname', 'member.id'], 'like', 'keyword'];
        $searchs[] = ['log.source', 'int', 'source'];

        if ($orderId && ($formType == 4 || $formType == 1)) {

            $searchs[] = ['order.order_no', 'like', 'order_no'];
        }

        $params = [
            'searchs' => $searchs,
            'where' => [
                'log.form_id' => $formId,
                'log.is_deleted' => FormLogConstant::FORM_IS_NO_DELETE,
            ],
            'select' => $select,
            'alias' => 'log',
            'leftJoins' => $leftJoins,
            'orderBy' => [
                'log.created_at' => SORT_DESC
            ]
        ];

        $list = FormLogModel::getColl($params);

        return $this->result(['data' => $list]);
    }


    /**
     * 禁用
     * @return array|\yii\web\Response
     * @throws FormException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionForbidden()
    {
        $id = RequestHelper::get('id');

        if (empty($id)) {
            throw new FormException(FormException::FORM_LIST_FORBIDDEN_ID_NOT_EMPTY);
        }

        $result = FormModel::changeStatus($id, 0);

        if (is_error($result)) {
            throw new FormException(FormException::FORM_LIST_FORBIDDEN_INVALID, $result['message']);
        }

        // 查找当前表单应用的商品
        $goods = GoodsModel::find()->select('id')->where(['form_id' => $id])->get();
        GoodsCartModel::updateAll(
            ['is_selected' => 0, 'is_reelect' => 1],
            ['goods_id' => array_column($goods, 'id')]
        );

        // 日志
        $logPrimary = [
            'id' => $result->id,
            '表单类型' => FormTypeConstant::getText($result->type),
            '表单名称' => $result->name,
            '操作' => '禁用',
        ];

        LogModel::write(
            $this->userId,
            FormLogConstant::FORM_FORBIDDEN,
            FormLogConstant::getText(FormLogConstant::FORM_FORBIDDEN),
            $result->id,
            [
                'log_data' => $result->attributes,
                'log_primary' => $logPrimary,
            ]
        );

        return $this->result('禁用成功');
    }

    /**
     * 启用
     * @return array|\yii\web\Response
     * @throws FormException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionActive()
    {
        $id = RequestHelper::get('id');

        if (empty($id)) {
            throw new FormException(FormException::FORM_LIST_ACTIVE_ID_NOT_EMPTY);
        }

        $result = FormModel::changeStatus($id, 1);

        if (is_error($result)) {
            throw new FormException(FormException::FORM_LIST_ACTIVE_INVALID, $result['message']);
        }

        // 查找当前表单应用的商品
        $goods = GoodsModel::find()->select('id')->where(['form_id' => $id])->get();
        GoodsCartModel::updateAll(
            ['is_selected' => 0, 'is_reelect' => 1],
            ['goods_id' => array_column($goods, 'id')]
        );

        // 日志
        $logPrimary = [
            'id' => $result->id,
            '表单类型' => FormTypeConstant::getText($result->type),
            '表单名称' => $result->name,
            '操作' => '启用',
        ];

        LogModel::write(
            $this->userId,
            FormLogConstant::FORM_ACTIVE,
            FormLogConstant::getText(FormLogConstant::FORM_ACTIVE),
            $result->id,
            [
                'log_data' => $result->attributes,
                'log_primary' => $logPrimary,
            ]
        );

        return $this->result('启用成功');
    }

    /**
     * 删除
     * @return array|\yii\web\Response
     * @throws FormException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            $id = RequestHelper::getArray('ids', ',');
        }

        if (empty($id)) {
            throw new FormException(FormException::FORM_LIST_ACTIVE_ID_NOT_EMPTY);
        }
        // TODO 事务
        $result = FormModel::deleteResult($id);

        if (is_error($result)) {
            throw new FormException(FormException::FORM_LIST_DELETED_INVALID, $result['message']);
        }

        // 查找当前表单应用的商品
        $goods = GoodsModel::find()->select('id')->where(['form_id' => $id])->get();
        $goodsIds = array_column($goods, 'id');
        GoodsCartModel::updateAll(
            ['is_selected' => 0, 'is_reelect' => 1],['goods_id' => $goodsIds],
            []
        );

        // 当前表单的商品状态置为关闭
        GoodsModel::updateAll(['form_id' => 0, 'form_status' => 0], ['id' => $goodsIds]);


        // 日志
        $logPrimary = [
            'id' => $result->id,
            '表单类型' => FormTypeConstant::getText($result->type),
            '表单名称' => $result->name,
            '操作' => '删除',
        ];

        LogModel::write(
            $this->userId,
            FormLogConstant::FORM_DELETE,
            FormLogConstant::getText(FormLogConstant::FORM_DELETE),
            $result->id,
            [
                'log_data' => $result->attributes,
                'log_primary' => $logPrimary,
            ]
        );

        return $this->result('删除成功');
    }

    /**
     * 导出
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDownload()
    {
        // 获取表单ID
        $formId = RequestHelper::getInt('id');
        if (empty($formId)) {
            return $this->error('参数错误 id不能为空');
        }

        // 获取表单类型
        $formType = RequestHelper::getInt('type');


        // 查询出表单
        $formData = FormModel::find()
            ->where([
                'id' => $formId,
                'type' => $formType,
                'is_deleted' => 0,
            ])
            ->select([
                'id',
                'name',
                'last_update_time',
                'type',
            ])
            ->first();

        if (empty($formData)) {
            return $this->error('表单不存在');
        }

        // FormLog链表会员查询表单提交日志

        $leftJoins = [
            [MemberModel::tableName() . ' member', 'member.id = log.member_id']
        ];

        $select = [
            'log.id as form_id',
            'member.nickname',
            'member.id as member_id',
            'log.content',
            'log.created_at'
        ];


        if ($formType == FormTypeConstant::FORM_TYPE_ORDER) {
            //如果是订单ID查订单编号
            $leftJoins[] = [OrderModel::tableName() . ' order', 'order.id = log.order_id'];
            $select[] = 'order.order_no';
        } elseif ($formType == FormTypeConstant::FORM_TYPE_GOODS) {
            //如果是商品ID查商品名称
            $leftJoins[] = [GoodsModel::tableName() . ' goods', 'goods.id = log.goods_id'];
            $leftJoins[] = [OrderModel::tableName() . ' order', 'order.id = log.order_id'];
            $select[] = 'goods.title';
            $select[] = 'order.order_no';
        }

        $params = [
            'alias' => 'log',
            'leftJoins' => $leftJoins,
            'where' => [
                'and',
                [
                    'log.form_id' => $formId,
                    'log.is_deleted' => FormLogConstant::FORM_IS_NO_DELETE,
                ],
                /*['>', 'log.created_at', $formData['last_updated_at']], // 过滤表单最后修改时间之后的提交记录*/
            ],
            'select' => $select,
            'orderBy' => [
                'log.created_at' => SORT_DESC,
            ],
        ];

        // 查询出所有的提交记录
        $records = FormLogModel::getColl($params, [
            'callable' => function (&$row) use ($formData) {
                // 处理字段文字
                $row['location'] = FormTypeConstant::getText($formData['type']);

                // 将表单信息塞入提交记录
                //$row['form_id'] = $formData['id'];
                $row['name'] = $formData['name'];

                $row['nickname'] = trim($row['nickname'],'=');
            },
            'pager' => false,
            'onlyList' => true
        ]);


        //临时处理方法
        //$records = $this->tempDownLodaData($records, $formType);


        $records = $this->downLodaData($records, $formType);
        if (!$records) {
            $records = [
                'field' => [
                    [
                        'field' => 'form_id',
                        'title' => '表单数据ID',
                    ],
                    [
                        'field' => 'name',
                        'title' => '表单名称',
                    ],
                    [
                        'field' => 'nickname',
                        'title' => '提交会员',
                    ],
                    [
                        'field' => 'created_at',
                        'title' => '提交时间',
                    ]
                ],
                'data' => []
            ];
        }

        $location = '未知';
        if (!empty($records)) {
            $location = isset($formData['name']) ? $formData['name'] : '未知';
        }

        // 日志
        LogModel::write(
            $this->userId,
            FormLogConstant::FORM_EXPORT,
            FormLogConstant::getText(FormLogConstant::FORM_EXPORT),
            $formData['id'],
            [
                'log_data' => $formData,
                'log_primary' => [
                    '表单名称' => $location
                ]
            ]
        );

        ExcelHelper::export($records['data'], $records['field'], $location);

        return true;
    }


    /**
     * 临时处理导出数据
     * @param array $content
     * @param int $type
     * @return array|mixed|string|null
     * @author 青岛开店星信息技术有限公司
     */
    public function tempDownLodaData(array $content, int $type)
    {
        if (empty($content)) {
            return '';
        }

        //必须的字段
        $allField = [
            [
                'field' => 'form_id',
                'title' => '表单数据ID',
            ],
            [
                'field' => 'name',
                'title' => '表单名称',
            ],
            [
                'field' => 'location',
                'title' => '表单类型',
            ],
            [
                'field' => 'nickname',
                'title' => '提交会员',
            ],
            [
                'field' => 'member_id',
                'title' => '提交会员',
            ],
            [
                'field' => 'created_at',
                'title' => '提交时间',
            ]
        ];

        if ($type == FormTypeConstant::FORM_TYPE_GOODS) {
            $allField[] = ['field' => 'title', 'title' => '应用商品'];
            $allField[] = ['field' => 'order_no', 'title' => '订单编号'];
        }

        if ($type == FormTypeConstant::FORM_TYPE_ORDER) {
            $allField[] = ['field' => 'order_no', 'title' => '订单编号'];
        }

        foreach ($content as &$item) {
            StringHelper::isJson($item['content']) && $item['content'] = Json::decode($item['content']);

            if (!is_array($item['content'])) {
                return $item['content'];
            }

            //遍历表单数据
            foreach ($item['content'] as $k => $v) {

                $field = $v['type'] . '_' . $k;

                $title = $v['params']['title'];

                $newField['field'] = $field;
                $newField['title'] = $title;

                $allField[] = $newField;
                $item[$field] = $this->handleFieldData($v);
                unset($item['content']);

            }

        }
        unset($item);

        //去除重复的字段
        $allField = array_column($allField, null, 'field');

        $data = [
            'field' => array_values($allField),
            'data' => $content
        ];

        return $data;

    }


    private function checkParams(&$params, $isAdd = true)
    {
        if (!$isAdd && empty($params['id'])) {
            throw new FormException(FormException::FORM_LIST_ID_NOT_EMPTY);
        }

        if (empty($params['name'])) {
            throw new FormException(FormException::FORM_LIST_NAME_NOT_EMPTY);
        }

        if (empty($params['content'])) {
            throw new FormException(FormException::FORM_LIST_CONTENT_NOT_EMPTY);
        }

        $checkType = FormTypeConstant::getOneByCode($params['type']);
        if (is_null($checkType)) {
            throw new FormException(FormException::FORM_LIST_TYPE_INVALID);
        }

        $checkName = FormModel::checkName($params['type'], $params['name']);

        if (!$checkName) {
            throw new FormException(FormException::FORM_PAGE_SUBMIT_FORM_DATA_NAME_EXIT);
        }

        if (empty($params['status'])) {
            $params['status'] = 0;
        }

        return true;
    }

    private function formatDownloadContent($content, $isDownload = false)
    {
        if (empty($content)) {
            return '';
        }

        StringHelper::isJson($content) && $content = Json::decode($content);

        if (!is_array($content)) {
            return $content;
        }

        $returnData = '';
        foreach ($content as $item) {
            switch ($item['type']) {
                case 'timerange':
                    $tmpStr = ArrayHelper::arrayGet($item, 'params.title');
                    if ($isDownload) {
                        $tmpStr .= ':' . ArrayHelper::arrayGet($item, 'params.start.value') . '~' . ArrayHelper::arrayGet($item, 'params.end.value');
                    }
                    break;
                case 'pictures':
                    $tmpStr = '图片';
                    if ($isDownload) {
                        $tmpStr .= ':' . '略';
                    }
                    break;
                case  'checkboxes':
                    $tmpStr = ArrayHelper::arrayGet($item, 'params.title');
                    if ($isDownload) {
                        $tmpStr .= ':' . implode(',', ArrayHelper::arrayGet($item, 'params.value'));
                    }
                    break;
                case 'city':
                    $tmpStr = ArrayHelper::arrayGet($item, 'params.title');
                    if ($isDownload) {
                        $tmpStr .= ':' . ArrayHelper::arrayGet($item, 'params.province') . ArrayHelper::arrayGet($item, 'params.city') . ArrayHelper::arrayGet($item, 'params.area');
                    }
                    break;
                case 'daterange':
                    $tmpStr = ArrayHelper::arrayGet($item, 'params.title');
                    if ($isDownload) {
                        $tmpStr .= ':' . ArrayHelper::arrayGet($item, 'params.start.value') . '~' . ArrayHelper::arrayGet($item, 'params.end.value');
                    }
                    break;
                default:
                    $tmpStr = ArrayHelper::arrayGet($item, 'params.title');
                    if ($isDownload) {
                        $tmpStr .= ':' . ArrayHelper::arrayGet($item, 'params.value');
                    }
            }

            $returnData .= $tmpStr . ',';
        }

        $returnData = substr($returnData, 0, -1);

        return $returnData;
    }

    /**
     * 处理导出表头以及内容
     * @param $content
     * @return array|mixed|string|null
     * @author 青岛开店星信息技术有限公司
     */
    private function downLodaData($content, $type)
    {
        if (empty($content)) {
            return '';
        }

        //必须的字段
        $allField = [
            [
                'field' => 'form_id',
                'title' => '表单数据ID',
            ],
            [
                'field' => 'name',
                'title' => '表单名称',
            ],
            [
                'field' => 'location',
                'title' => '应用位置',
            ],
            [
                'field' => 'nickname',
                'title' => '提交会员',
            ],
            [
                'field' => 'member_id',
                'title' => '会员ID',
            ],
            [
                'field' => 'created_at',
                'title' => '提交时间',
            ]
        ];

        if ($type == FormTypeConstant::FORM_TYPE_GOODS) {
            $allField[] = ['field' => 'title', 'title' => '应用商品'];
            $allField[] = ['field' => 'order_no', 'title' => '订单编号'];
        }

        if ($type == FormTypeConstant::FORM_TYPE_ORDER) {
            $allField[] = ['field' => 'order_no', 'title' => '订单编号'];
        }

        $content = array_reverse($content);

        foreach ($content as &$item) {

            StringHelper::isJson($item['content']) && $item['content'] = Json::decode($item['content']);

            if (!is_array($item['content'])) {
                return $item['content'];
            }

            $countType = []; //统计重复字段
            $contentData = []; //存导出表头信息

            //遍历表单数据
            foreach ($item['content'] as $k => $v) {

                //查询 如果存在就是重复字段，+1
                if (isset($countType[$v['type']])) {

                    $countType[$v['type']] += 1;
                    //因为重复字段，下标+1导出
                    $field = $v['type'] . '_' . $countType[$v['type']];

                    $contentData[$k]['field'] = $field;
                    //处理导出内容
                    $item[$field] = $this->handleFieldData($v);
                    //表头名称
                    $contentData[$k]['title'] = $v['params']['title'];

                } else {
                    //如果不存在 创建该key
                    $countType[$v['type']] = 0;

                    $contentData[$k]['field'] = $v['type'];

                    $item[$v['type']] = $this->handleFieldData($v);

                    $contentData[$k]['title'] = $v['params']['title'];
                }
            }

            unset($item['content']);
            //将必须的字段跟表单字段合并
            $allField = array_merge($allField, $contentData);

            unset($contentData);
        }
        unset($item);

        //去除重复的字段
        $allField = array_column($allField, null, 'field');

        $data = [
            'field' => array_values($allField),
            'data' => $content
        ];

        return $data;
    }


    /**
     * 处理表单不同信息的值
     * @param $contentData
     * @return array|mixed|string
     * @author 青岛开店星信息技术有限公司
     */
    private function handleFieldData($contentData)
    {
        switch ($contentData['type']) {
            case 'timerange':
                $tmpStr = ArrayHelper::arrayGet($contentData, 'params.start.value') . '~' . ArrayHelper::arrayGet($contentData, 'params.end.value');
                break;
            case 'pictures':
                $tmpStr = '略';
                break;
            case  'checkboxes':
                $tmpStr = implode(',', ArrayHelper::arrayGet($contentData, 'params.value'));
                break;
            case 'city':
                $tmpStr = ArrayHelper::arrayGet($contentData, 'params.province') . ArrayHelper::arrayGet($contentData, 'params.city') . ArrayHelper::arrayGet($contentData, 'params.area');
                break;
            case 'daterange':
                $tmpStr = ArrayHelper::arrayGet($contentData, 'params.start.value') . '~' . ArrayHelper::arrayGet($contentData, 'params.end.value');
                break;
            case 'identity': // 身份证号转字符串
                $tmpStr = '\''.ArrayHelper::arrayGet($contentData, 'params.value');
                break;
            default:
                $tmpStr = ArrayHelper::arrayGet($contentData, 'params.value');
        }
        return $tmpStr;
    }

    /**
     * 删除系统表单的提交记录
     * @throws FormException
     * @author 青岛开店星信息技术有限公司
     * @return array|\yii\web\Response
     */
    public function actionDeleteLog()
    {
        $id = RequestHelper::post('id');
        $formId = RequestHelper::post('form_id');
        if (empty($id)) {
            $id = RequestHelper::postArray('ids', ',');
        }

        if (empty($id)) {
            throw new FormException(FormException::FORM_LIST_ACTIVE_ID_NOT_EMPTY);
        }

        $transaction = \Yii::$app->db->beginTransaction();
        // 确保数据统一
        try {
            // 删除提交记录
            $result = FormLogModel::updateAll(['is_deleted' => FormLogConstant::FORM_IS_DELETE], ['id' => $id]);
            // 减少表单的提交记录数量
            FormModel::updateAllCounters(['count' => ($result *= -1)], ['id' => $formId]);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new FormException(FormException::FORM_LIST_DELETED_INVALID, $result['message']);
        }

        return $this->result('删除成功');
    }

    /**
     * 获取用户提交的表单详情
     * @return array|int[]|\yii\web\Response
     * @author nizengchao
     */
    public function actionGetFormLog()
    {
        $id = RequestHelper::getInt('form_log_id');
        $type = RequestHelper::getInt('type');
        $memberId = RequestHelper::getInt('member_id');
        $orderId = RequestHelper::getInt('order_id');
        if (!$id || !$type || !$memberId) {
            return $this->error('参数错误');
        }

        $data = FormLogModel::get($type, $memberId, $orderId);
        if(empty($data)) {
            return $this->error('获取数据失败');
        }

        // 格式化
        !empty($data['content']) && $data['content'] = Json::decode($data['content']);
        return $this->result($data);
    }
}