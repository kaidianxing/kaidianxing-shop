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


namespace shopstar\admin\goods;

use shopstar\constants\log\goods\GoodsLogConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\label\GoodsLabelGroupModel;
use shopstar\models\goods\label\GoodsLabelMapModel;
use shopstar\models\goods\label\GoodsLabelModel;
use shopstar\models\log\LogModel;
use shopstar\bases\KdxAdminApiController;

class LabelGroupController extends KdxAdminApiController
{
    public $configActions = [
        'postActions' => [
            'create',
            'update',
            'delete',
            'forever-delete'
        ],
        'allowPermActions' => [
            'get-list',
            'get-list-and-label'
        ]
    ];


    /**
     * 获取标签分组列表
     * @param bool $pager 是否需要返回page规则
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetList($pager = true)
    {
        $list = GoodsLabelGroupModel::getColl([
            'where' => [],
            'searchs' => [
                ['name', 'like'],
                ['status'],
            ],
            'orderBy' => [
                'sort_by' => SORT_DESC,
                'created_at' => SORT_DESC
            ]
        ], [
            'pager' => $pager
        ]);

        return $this->success($list);
    }

    /**
     * 获取分组标签
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetListAndLabel()
    {
        $list = $this->actionComponent(['is_default' => 0]);

        return $this->success(['data' => $list, 'recommend' => $this->actionComponent(['is_default' => 1])[0]['label']]);
    }

    /**
     * 分组标签查询组件
     * @param $option
     * @author 青岛开店星信息技术有限公司
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionComponent($option)
    {
        $list = GoodsLabelGroupModel::find()
            ->where([
                'status' => '1',
                'is_default' => $option['is_default']
            ])
            ->with('label')
            ->orderBy([
                'sort_by' => SORT_DESC,
                'created_at' => SORT_DESC
            ])
            ->asArray()
            ->all();
        return $list;
    }

    /**
     * 获取单个商品分组
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetOne()
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            throw new GoodsException(GoodsException::LABEL_GROUP_GET_ONE_PARAMS_ERROR);
        }

        $label = GoodsLabelGroupModel::find()
            ->where([
                'id' => $id,
            ])
            ->with(['label'])
            ->asArray()
            ->one();

        if (empty($label)) {
            throw new GoodsException(GoodsException::LABEL_GROUP_GET_ONE_NOT_FOUND_ERROR);
        }

        return $this->success(['data' => $label]);
    }

    /**
     * 创建标签 | 权限
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCreate()
    {
        return $this->actionUpdate();
    }

    /**
     * 编辑更新标签分组
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate()
    {

        $id = RequestHelper::postInt("id");

        if ($id) {
            $labelGroup = GoodsLabelGroupModel::findOne(['id' => $id]);
            if (empty($labelGroup)) {
                throw new GoodsException(GoodsException::LABEL_GROUP_SAVE_NOT_FOUND_ERROR);
            }
        }

        if (empty($labelGroup)) {
            $labelGroup = new GoodsLabelGroupModel();
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $post = RequestHelper::post();
            unset($post['id']);
            //保存标签信息
            if (empty($labelGroup->created_at)) {
                $labelGroup->created_at = DateTimeHelper::now();
            }

            $labelData = $post['label'];
            $labelGroup->setAttributes($post);
            if (!$labelGroup->save()) {
                throw new GoodsException(GoodsException::LABEL_GROUP_SAVE_ERROR, $labelGroup->getErrorMessage());
            }

            $labelGroupId = $labelGroup->id;

            $logPrimaryData = [
                'id' => $labelGroup->id,
                'sort_by' => $labelGroup->sort_by,
                'name' => $labelGroup->name,
                'status' => $labelGroup->status == 0 ? '禁用' : '启用'
            ];
            // 删除标签
            $ids = array_column($labelData, 'id');
            $labelIds = GoodsLabelModel::getColl([
                'where' => [
                    'group_id' => $id,
                ],
                'select' => [
                    'id'
                ]
            ], [
                'pager' => false,
                'onlyList' => true,
            ]);
            if ($labelIds) {
                $labelIds = array_column($labelIds, 'id');
                $diffIds = array_diff($labelIds, $ids);
                // 删除标签
                GoodsLabelModel::deleteAll([
                    'and',
                    ['group_id' => $id],
                    ['not in', 'id', $ids]
                ]);
                // 删除商品标签映射
                GoodsLabelMapModel::deleteAll([
                    'and',
                    ['in', 'label_id', $diffIds]
                ]);
            }

            if (!empty($labelData)) {
                foreach ((array)$labelData as $labelDataIndex => $labelDataItem) {
                    $labelDataItem['group_id'] = $labelGroupId;
                    $logPrimaryData['label_name'] .= $labelDataItem['name'] . ', ';
                    GoodsLabelModel::saveLabel($labelDataItem);
                }
            }

            //添加操作日志
            $code = empty($id) ? GoodsLogConstant::GOODS_LABEL_GROUP_ADD : GoodsLogConstant::GOODS_LABEL_GROUP_EDIT;
            LogModel::write(
                $this->userId,
                $code,
                GoodsLogConstant::getText($code),
                $id,
                [
                    'log_data' => $labelGroup->attributes,
                    'log_primary' => $labelGroup->getLogAttributeRemark($logPrimaryData),
                    'dirty_identify_code' => [
                        GoodsLogConstant::GOODS_LABEL_GROUP_ADD,
                        GoodsLogConstant::GOODS_LABEL_GROUP_EDIT
                    ]
                ]
            );

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new GoodsException($exception->getCode());

        }

        return $this->success();
    }


    /**
     * 永久删除商品标签分组
     * @param int|array $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @author 青岛开店星信息技术有限公司
     */
    public function actionForeverDelete()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new GoodsException(GoodsException::LABEL_GROUP_DELETE_PARAMS_ERROR);
        }

        $model = GoodsLabelGroupModel::find()
            ->where(['id' => $id])
            ->all();

        if (empty($model)) {
            throw new GoodsException(GoodsException::LABEL_GROUP_DELETE_NOT_FOUND_ERROR);
        }

        foreach ($model as $item) {
            // 筛选出默认标签组，禁止删除
            if($item->is_default == '1'){
                throw new GoodsException(GoodsException::LABEL_GROUP_DEFAULT_GROUP_BAN_DELETE);
            }
            $item->delete();
            GoodsLabelModel::deleteAll(['group_id' => $item->id]);

            //添加操作日志
            LogModel::write(
                $this->userId,
                GoodsLogConstant::GOODS_LABEL_GROUP_DELETE,
                GoodsLogConstant::getText(GoodsLogConstant::GOODS_LABEL_GROUP_DELETE),
                $item->id,
                [
                    'log_data' => $item->attributes,
                    'log_primary' => $item->getLogAttributeRemark([
                        'id' => $item->id,
                        'name' => $item->name
                    ])
                ]
            );
        }

        return $this->success();
    }

    /**
     * 分组开关
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSwitch()
    {
        $id = RequestHelper::post('id');
        $status = RequestHelper::postInt('status', 0);
        if (empty($id)) {
            throw new GoodsException(GoodsException::LABEL_GROUP_SWITCH_PARAMS_ERROR);
        }

        $tr = \Yii::$app->db->beginTransaction();
        try {
            foreach ((array)$id as $idIndex => $idItem) {
                $group = GoodsLabelGroupModel::findOne($idItem);
                if (empty($group)) {
                    throw new GoodsException(GoodsException::GROUP_SWITCH_GROUP_NOT_FOUND_ERROR);
                }

                $group->status = $status;

                if (!$group->save()) {
                    throw new GoodsException(GoodsException::LABEL_GROUP_SWITCH_GROUP_SAVE_ERROR);
                }

                //添加操作日志
                LogModel::write(
                    $this->userId,
                    GoodsLogConstant::GOODS_LABEL_GROUP_SWITCH,
                    GoodsLogConstant::getText(GoodsLogConstant::GOODS_LABEL_GROUP_SWITCH),
                    $group->id,
                    [
                        'log_data' => $group->attributes,
                        'log_primary' => $group->getLogAttributeRemark([
                            'id' => $group->id,
                            'name' => $group->name,
                            'status' => $group->status == 0 ? '禁用' : '启用'
                        ])
                    ]
                );
            }

            $tr->commit();
        } catch (\Throwable $throwable) {
            $tr->rollBack();
            throw new GoodsException($throwable->getCode());
        }

        return $this->success();
    }

}
