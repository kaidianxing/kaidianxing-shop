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

namespace shopstar\admin\order;

use shopstar\constants\log\order\DispatchLogConstant;

use shopstar\exceptions\order\DispatchException;
use shopstar\helpers\ArrayHelper;
 
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\order\DispatchModel;
use shopstar\models\shop\ShopSettings;
use shopstar\bases\KdxAdminApiController;
use yii\web\Response;

/**
 * 配送方式
 * Class DispatchController
 * @package app\controllers\manage\order
 */
class DispatchController extends KdxAdminApiController
{

    public $configActions = [
        'allowPermActions' => [
            'get-list',
            'get-default',
        ]
    ];
    
    /**
     * 配送方式列表
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $get = RequestHelper::get();
        $state = $get['state'];
        $keyword = $get['keyword'];

        $where = [];
        if ($state != '') {
            $where[] = ['state' => $state];
        }
        if (!empty($keyword)) {
            $where[] = ['like', 'dispatch_name', $keyword];
        }

        $select = 'id, sort, dispatch_name, calculate_type, start_num_price, start_weight_price, add_num_price, add_weight_price, state, is_default';
        $params = [
            'andWhere' => $where,
            'select' => $select,
            'orderBy' => [
                'is_default' => SORT_DESC,
                'sort' => SORT_DESC,
                'id' => SORT_DESC
            ]
        ];

        $list = DispatchModel::getColl($params);

        return $this->success($list);
    }

    /**
     * 配送方式简单列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetList()
    {
        $list = DispatchModel::getColl([
            'select' => ['id', 'dispatch_name', 'is_default'],
            'where' => [
                'state' => 1,
            ],
            'orderBy' => [
                'is_default' => SORT_DESC,
                'sort' => SORT_DESC,
                'id' => SORT_DESC
            ]
        ], [
            'pager' => false,
            'onlyList' => true
        ]);

        return $this->result(['list' => $list]);
    }

    /**
     * 配送方式详情
     * @return string
     * @throws DispatchException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::getInt('id');

        if (empty($id)) {
            throw new DispatchException(DispatchException::DETAIL_PARAMS_ERROR);
        }

        $detail = DispatchModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new DispatchException(DispatchException::DISPATCH_NOT_EXISTS);
        }
        $detail = $detail->toArray();

        $detail['default'] = [
            'start_num' => $detail['start_num'],
            'start_num_price' => $detail['start_num_price'],
            'add_num' => $detail['add_num'],
            'add_num_price' => $detail['add_num_price'],
            'start_weight' => $detail['start_weight'],
            'start_weight_price' => $detail['start_weight_price'],
            'add_weight' => $detail['add_weight'],
            'add_weight_price' => $detail['add_weight_price'],
        ];
        unset($detail['start_num']);
        unset($detail['start_num_price']);
        unset($detail['add_num']);
        unset($detail['add_num_price']);
        unset($detail['start_weight']);
        unset($detail['start_weight_price']);
        unset($detail['add_weight']);
        unset($detail['add_weight_price']);

        return $this->success($detail);
    }

    /**
     * 修改配送方式
     * @return Response
     * @throws DispatchException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::postInt('id');
        if (empty($id)) {
            throw new DispatchException(DispatchException::EDIT_PARAMS_ERROR);
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $res = DispatchModel::saveDispatch($id);
            if (is_error($res)) {
                throw new DispatchException(DispatchException::DISPATCH_EDIT_SAVE_FAIL, $res['message']);
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getMessage());
        }

        return $this->success();
    }

    /**
     * 新增配送方式
     * @return string|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $res = DispatchModel::saveDispatch();
            if (is_error($res)) {
                throw new DispatchException(DispatchException::DISPATCH_ADD_SAVE_FAIL, $res['message']);
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }

        return $this->success();
    }

    /**
     * 删除/批量删除
     * @return Response
     * @throws DispatchException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::post('ids');
        if (empty($id)) {
            throw new DispatchException(DispatchException::DELETE_PARAMS_ERROR);
        }

        try {
            DispatchModel::deleteAll(['id' => $id]);
        } catch (\Throwable $exception) {
            throw new DispatchException(DispatchException::DELETE_DISPATCH_FAIL);
        }
        return $this->success();
    }

    /**
     * 修改状态
     * @return Response
     * @throws DispatchException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeState()
    {
        $id = RequestHelper::post('ids');
        $state = RequestHelper::post('state');
        if (empty($id) || $state == '') {
            throw new DispatchException(DispatchException::CHANGE_STATE_PARAMS_ERROR);
        }
        try {
            DispatchModel::updateAll(['state' => $state], ['id' => $id]);
        } catch (\Throwable $exception) {
            throw new DispatchException(DispatchException::CHANGE_DISPATCH_STATE_FAIL);
        }
        return $this->success();
    }

    /**
     * 修改默认
     * @return Response
     * @throws DispatchException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeDefault()
    {
        $id = RequestHelper::getInt('id');
        $isDefault = RequestHelper::getInt('is_default');
        if (empty($id)) {
            throw new DispatchException(DispatchException::CHANGE_DEFAULT_PARAMS_ERROR);
        }
        try {
            // 修改为默认时需要修改其他的记录
            if ($isDefault) {
                DispatchModel::updateAll(['is_default' => 0], []);
                DispatchModel::updateAll(['is_default' => 1], ['id' => $id]);
            } else {
                DispatchModel::updateAll(['is_default' => 0], ['id' => $id]);
            }
        } catch (\Throwable $exception) {
            throw new DispatchException(DispatchException::CHANGE_DEFAULT_FAIL);
        }
        return $this->success();
    }

    /**
     * 获取默认模板
     * @return array|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetDefault()
    {
        $default = DispatchModel::find()
            ->select('id, dispatch_name')
            ->where(['is_default' => 1])
            ->first();
        return $this->result($default);
    }

    /**
     * 修改普通配送开启状态
     */
    public function actionEnable()
    {
        $enable = RequestHelper::postInt('enable');

        // 关闭前判断是否有开启配送方式
        if (empty($enable)) {
            $dispatchEnable = ShopSettings::get('dispatch.intracity.enable');
            if (empty($dispatchEnable)) {
                throw new DispatchException(DispatchException::SHOP_SETTINGS_DISPATCH_EXPRESS_ENABLE_INVALID);
            }
        }

        ShopSettings::set('dispatch.express.enable', $enable);
        // 配送方式排序处理
        DispatchModel::updateSort($this->shopType, $enable, 10);

        LogModel::write(
            $this->userId,
            DispatchLogConstant::EXPRESS_ENABLE_SETTING,
            DispatchLogConstant::getText(DispatchLogConstant::EXPRESS_ENABLE_SETTING),
            0,
            [
                'log_data' => ['enable' => $enable],
                'log_primary' => [
                    '状态' => $enable == 1 ? '开启' : '关闭'
                ],
                'dirty_identify_code'=> [
                    DispatchLogConstant::EXPRESS_ENABLE_SETTING,
                ]
            ]
        );
        return $this->success();
    }

}