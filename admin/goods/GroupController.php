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

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\goods\GoodsLogConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\group\GoodsGroupMapModel;
use shopstar\models\goods\group\GoodsGroupModel;
use shopstar\models\log\LogModel;

/**
 * 商品分组
 * Class GroupController
 * @package shopstar\admin\goods
 */
class GroupController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'create',
            'update',
            'forever-delete'
        ],
        'allowPermActions' => [
            'get-list',
        ]
    ];

    /**
     * 商品分组列表
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetList(): \yii\web\Response
    {
        $get = RequestHelper::get();

        $list = GoodsGroupModel::getColl([
            'where' => [],
            'searchs' => [
                ['name', 'like'],
                ['status', 'int'],
            ],
            'orderBy' => [
                'id' => SORT_DESC
            ]
        ], [
            'pager' => $get['pager']
        ]);

        return $this->success($list);
    }


    /**
     * 获取单个商品分组
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetOne(): \yii\web\Response
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            throw new GoodsException(GoodsException::GROUP_GET_ONE_PARAMS_ERROR);
        }

        $group = GoodsGroupModel::getOne($id);
        if (empty($group)) {
            throw new GoodsException(GoodsException::GROUP_GET_ONE_NOT_FOUND_ERROR);
        }

        $group = $group->toArray();
        $group['status'] = (int)$group['status'];
        $goodsId = GoodsGroupMapModel::find()->where(['group_id' => $group['id']])->select(['goods_id'])->column();
        $group['goods'] = GoodsModel::find()->where(['id' => $goodsId])->asArray()->select([
            'id',
            'title',
            'type',
            'price',
            'thumb',
            'stock',
        ])->all();

        return $this->success(['data' => $group]);
    }

    /**
     * 创建分组 | 权限
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCreate(): \yii\web\Response
    {
        return $this->actionUpdate();
    }

    /**
     * 更新分组 | 权限
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate(): \yii\web\Response
    {
        $post = RequestHelper::post();

        if (!empty($post['id'])) {
            $group = GoodsGroupModel::getOne($post['id']);
            if (empty($group)) {
                throw new GoodsException(GoodsException::GROUP_SAVE_NOT_FOUND_ERROR);
            }
        }

        $group = !empty($group) ? $group : new GoodsGroupModel();

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            if (empty($group->created_at)) {
                $group->created_at = DateTimeHelper::now();
            }
            $group->updated_at = DateTimeHelper::now();

            $group->setAttributes($post);

            if (!$group->save()) {
                throw new GoodsException(GoodsException::GROUP_SAVE_ERROR, $group->getErrorMessage());
            }

            GoodsGroupMapModel::deleteAll(['group_id' => $group->id]);
            //重新构建商品分组映射关系
            $data = [];
            foreach ((array)$post['goods_id'] as $goodsId) {
                $data[] = [$goodsId, $group->id];
            }

            GoodsGroupMapModel::batchInsert(
                ['goods_id', 'group_id'], $data);

            //商品
            $goods = GoodsModel::find()->where(['id' => $post['goods_id']])->select(['title'])->column();

            $logPrimaryData = [
                'id' => $group->id,
                'name' => $post['name'],
                'status' => $post['status'] == 0 ? '禁用' : '启用',
                'goods_title' => $goods ? implode(',', $goods) : ''
            ];

            $code = empty($post['id']) ? GoodsLogConstant::GOODS_GROUP_ADD : GoodsLogConstant::GOODS_GROUP_EDIT;

            //添加操作日志
            LogModel::write(
                $this->userId,
                $code,
                GoodsLogConstant::getText($code),
                $group->id,
                [
                    'log_data' => $group->attributes,
                    'log_primary' => $group->getLogAttributeRemark($logPrimaryData),
                    'dirty_identify_code' => [
                        GoodsLogConstant::GOODS_GROUP_ADD,
                        GoodsLogConstant::GOODS_GROUP_EDIT
                    ]
                ]
            );
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new GoodsException($exception->getCode(), $exception->getMessage());
        }

        return $this->success();
    }


    /**
     * 永久删除商品分组
     * @return \yii\web\Response
     * @throws GoodsException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionForeverDelete(): \yii\web\Response
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new GoodsException(GoodsException::GROUP_DELETE_PARAMS_ERROR);
        }

        $model = GoodsGroupModel::find()
            ->where(['id' => $id])
            ->all();

        if (empty($model)) {
            throw new GoodsException(GoodsException::GROUP_DELETE_NOT_FOUND_ERROR);
        }

        foreach ($model as $item) {
            $item->delete();

            //添加操作日志
            LogModel::write(
                $this->userId,
                GoodsLogConstant::GOODS_GROUP_DELETE,
                GoodsLogConstant::getText(GoodsLogConstant::GOODS_GROUP_DELETE),
                $item->id,
                [
                    'log_data' => $item->attributes,
                    'log_primary' => $item->getLogAttributeRemark([
                        'id' => $item->id,
                        'name' => $item->name,
                    ]),
                ]
            );
        }

        GoodsGroupMapModel::deleteAll(['group_id' => $id]);

        return $this->success();
    }

    /**
     * 分组开关
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSwitch(): \yii\web\Response
    {
        $id = RequestHelper::post('id');
        $status = RequestHelper::postInt('status', 0);
        if (empty($id)) {
            throw new GoodsException(GoodsException::GROUP_SWITCH_NOT_FOUND_ERROR);
        }

        $groupList = GoodsGroupModel::findAll($id);
        if (empty($groupList)) {
            throw new GoodsException(GoodsException::GROUP_SWITCH_GROUP_NOT_FOUND_ERROR);
        }
        foreach ($groupList as $groupListIndex => $groupListItem) {
            $groupListItem->status = $status;
            $groupListItem->save();

            $logPrimaryData = [
                'id' => $groupListItem['id'],
                'name' => $groupListItem['name'],
                'status' => $status == 0 ? '禁用' : '启用'
            ];

            //添加操作日志
            LogModel::write(
                $this->userId,
                GoodsLogConstant::GOODS_GROUP_SWITCH,
                GoodsLogConstant::getText(GoodsLogConstant::GOODS_GROUP_SWITCH),
                $groupListItem->id,
                [
                    'log_data' => $groupListItem->attributes,
                    'log_primary' => $groupListItem->getLogAttributeRemark($logPrimaryData)
                ]
            );
        }

        return $this->success();
    }
}
