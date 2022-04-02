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

namespace shopstar\admin\sysset;

use shopstar\constants\log\sysset\ExpressLogConstant;
use shopstar\exceptions\sysset\ExpressException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\log\LogModel;
use shopstar\models\sysset\RefundAddressModel;
use shopstar\bases\KdxAdminApiController;
use yii\web\Response;

/**
 * 退货地址
 * Class RefundAddressController
 * @package shopstar\admin\order
 * @author 青岛开店星信息技术有限公司
 */
class RefundAddressController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'add',
            'edit',
            'delete'
        ]
    ];

    /**
     * 退货地址列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $keyword = RequestHelper::get('keyword');
        $where = [];

        if (!empty($keyword)) {
            $where[] = ['like', 'title', $keyword];
        }
        $select = 'id, title, name, mobile, address, is_default';
        $params = [
            'where' => [],
            'andWhere' => $where,
            'select' => $select,
            'orderBy' => [
                'is_default' => SORT_DESC,
                'id' => SORT_DESC
            ]
        ];

        $list = RefundAddressModel::getColl($params);

        return $this->result($list);
    }

    /**
     * 退货地址
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAllRefundAddress()
    {
        $list = RefundAddressModel::getColl([
            'select' => ['id', 'title'],
            'where' => [],
            'orderBy' => [
                'is_default' => SORT_DESC,
                'id' => SORT_DESC
            ]
        ], [
            'pager' => false,
            'onlyList' => true
        ]);

        return $this->result(['list' => $list]);
    }

    /**
     * 新增
     * @return array|int[]|Response
     * @throws ExpressException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $res = RefundAddressModel::easyAdd([
            'attributes' => [],
            'beforeSave' => function ($data) {

                if (StringHelper::length($data['title']) > 30) {
                    return error('标题过长');
                }

                // 验证
                if (!ValueHelper::isMobile($data->mobile)) {
                    return error('手机号格式不正确');
                }

                if (empty($data->province) || empty($data->city) || empty($data->area) || empty($data->address)) {
                    return error('请填写地址');
                }
                // 如果是默认 则把其他置为非默认
                if ($data->is_default) {
                    RefundAddressModel::updateAll(['is_default' => 0], []);
                }
            },
            'afterSave' => function ($model) {
                // 日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'title' => $model->title,
                    'name' => $model->name,
                    'mobile' => $model->mobile,
                    'tel' => $model->tel,
                    'province' => $model->province,
                    'city' => $model->city,
                    'area' => $model->area,
                    'address' => $model->address,
                    'is_default' => $model->is_default ? '是' : '否',
                    'zip_code' => $model->zip_code,
                ];
                LogModel::write(
                    $this->userId,
                    ExpressLogConstant::REFUND_ADDRESS_ADD,
                    ExpressLogConstant::getText(ExpressLogConstant::REFUND_ADDRESS_ADD),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identity_code' => [
                            ExpressLogConstant::REFUND_ADDRESS_ADD,
                            ExpressLogConstant::REFUND_ADDRESS_EDIT,
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new ExpressException(ExpressException::REFUND_ADDRESS_ADD_SAVE_FAIL, $res['message']);
        }

        return $this->success();
    }

    /**
     * 修改
     * @return Response
     * @throws ExpressException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit(): Response
    {
        $res = RefundAddressModel::easyEdit([
            'beforeSave' => function ($data) {

                if (StringHelper::length($data['title']) > 30) {
                    return error('标题过长');
                }

                // 验证
                if (!ValueHelper::isMobile($data->mobile)) {
                    return error('手机号格式不正确');
                }

                if (empty($data->province) || empty($data->city) || empty($data->area) || empty($data->address)) {
                    return error('请填写地址');
                }
                // 如果是默认 则把其他置为非默认
                if ($data->is_default) {
                    RefundAddressModel::updateAll(['is_default' => 0], []);
                }
            },
            'afterSave' => function ($model) {
                // 日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'title' => $model->title,
                    'name' => $model->name,
                    'mobile' => $model->mobile,
                    'tel' => $model->tel,
                    'province' => $model->province,
                    'city' => $model->city,
                    'area' => $model->area,
                    'address' => $model->address,
                    'is_default' => $model->is_default ? '是' : '否',
                    'zip_code' => $model->zip_code,
                ];
                LogModel::write(
                    $this->userId,
                    ExpressLogConstant::REFUND_ADDRESS_EDIT,
                    ExpressLogConstant::getText(ExpressLogConstant::REFUND_ADDRESS_EDIT),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identity_code' => [
                            ExpressLogConstant::REFUND_ADDRESS_ADD,
                            ExpressLogConstant::REFUND_ADDRESS_EDIT,
                        ],
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new ExpressException(ExpressException::REFUND_ADDRESS_EDIT_SAVE_FAIL, $res['message']);
        }

        return $this->success();
    }

    /**
     * 详情
     * @return array|int[]|Response
     * @throws ExpressException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            throw new ExpressException(ExpressException::REFUND_ADDRESS_DETAIL_PARAMS_ERROR);
        }
        $refundAddress = RefundAddressModel::findOne(['id' => $id]);
        if (empty($refundAddress)) {
            throw new ExpressException(ExpressException::REFUND_ADDRESS_DETAIL_ADDRESS_NOT_EXISTS);
        }

        return $this->success($refundAddress);
    }

    /**
     * 删除/批量删除
     * @return Response
     * @throws ExpressException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete(): Response
    {
        $res = RefundAddressModel::easyDelete([
            'afterDelete' => function ($model) {
                // 记录日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'title' => $model->title,
                    'name' => $model->name,
                    'mobile' => $model->mobile,
                    'tel' => $model->tel,
                    'province' => $model->province,
                    'city' => $model->city,
                    'area' => $model->area,
                    'address' => $model->address,
                    'is_default' => $model->is_default ? '是' : '否',
                    'zip_code' => $model->zip_code,
                ];
                LogModel::write(
                    $this->userId,
                    ExpressLogConstant::REFUND_ADDRESS_DELETE,
                    ExpressLogConstant::getText(ExpressLogConstant::REFUND_ADDRESS_DELETE),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identity_code' => [
                            ExpressLogConstant::REFUND_ADDRESS_DELETE,
                        ],
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new ExpressException(ExpressException::REFUND_ADDRESS_DELETE_FAIL, $res['message']);
        }

        return $this->success();
    }

    /**
     * 修改默认状态
     * @throws ExpressException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeDefault()
    {
        $isDefault = RequestHelper::get('is_default', '0');
        // 修改为默认时需要修改其他的记录
        if ($isDefault == 1) {
            RefundAddressModel::updateAll(['is_default' => 0], []);
        }

        $res = RefundAddressModel::easySwitch('is_default', [
            'isPost' => false,
            'afterAction' => function ($model) {
                // 记录日志
                $logPrimaryData = [
                    'id' => 'ID',
                    'title' => $model->title,
                    'is_default' => $model->is_default ? '是' : '否',
                ];
                LogModel::write(
                    $this->userId,
                    ExpressLogConstant::REFUND_ADDRESS_CHANGE_DEFAULT,
                    ExpressLogConstant::getText(ExpressLogConstant::REFUND_ADDRESS_CHANGE_DEFAULT),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identity_code' => [
                            ExpressLogConstant::REFUND_ADDRESS_CHANGE_DEFAULT,
                        ],
                    ]
                );
            }
        ]);

        if (is_error($res)) {
            throw new ExpressException(ExpressException::REFUND_ADDRESS_CHANGE_DEFAULT_FAIL, $res['message']);
        }

        return $this->success();
    }

}