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

namespace shopstar\admin\sysset\mall;

use shopstar\constants\log\sysset\MallLogConstant;
use shopstar\exceptions\sysset\MallException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\sysset\NoticeModel;
use shopstar\bases\KdxAdminApiController;

/**
 * 公告
 * Class NoticeController
 * @package shopstar\admin\sysset\mall
 */
class NoticeController extends KdxAdminApiController
{
    public $configActions = [
        'postActions' => [
            'add',
            'change-status',
            'delete',
        ]
    ];
    /**
     * 通知列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $params = [
            'searchs' => [
                ['status', 'int', 'status'],
                ['title', 'like', 'keyword'],
            ],
            'select' => [
                'id',
                'sort_by',
                'title',
                'link',
                'status',
            ],
            'orderBy' => [
                'sort_by' => SORT_DESC,
                'id' => SORT_DESC,
            ]
        ];
        
        $list = NoticeModel::getColl($params);
        return $this->result($list);
    }
    
    /**
     * 通知详情
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id', '');
        if (empty($id)) {
            throw new MallException(MallException::NOTICE_DETAIL_PARAMS_ERROR);
        }
        $detail = NoticeModel::findOne(['id' => $id]);
        if (empty($detail)) {
            throw new MallException(MallException::NOTICE_DETAIL_NOT_EXISTS);
        }
        
        return $this->result($detail);
    }
    
    /**
     * 新增公告
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $res = NoticeModel::easyAdd([
            'attributes' => [
            ],
            'beforeSave' => function ($data) {
                // 显示类型 1链接
                if ($data->show_type == 1 && empty($data->link)) {
                    return error('链接不能为空');
                } else if ($data->show_type == 0 && empty($data->detail)) {
                    // 内容
                    return error('内容不能为空');
                }
            },
            'afterSave' => function ($model) {
                // 日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'sort_by' => $model->sort_by,
                    'title' => $model->title,
                    'status' => $model->status == 1 ? '显示' : '隐藏',
                    'show_type' => $model->show_type == 0 ? '显示内容' : '链接跳转',
                    'link' => $model->link,
                    'detail' => $model->detail,
                ];
                LogModel::write(
                    $this->userId,
                    MallLogConstant::MALL_NOTICE_ADD,
                    MallLogConstant::getText(MallLogConstant::MALL_NOTICE_ADD),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identity_code' => [
                            MallLogConstant::MALL_NOTICE_ADD,
                            MallLogConstant::MALL_NOTICE_EDIT,
                        ]
                    ]
                );
            }
        ]);
        
        if (is_error($res)) {
            throw new MallException(MallException::NOTICE_ADD_FAIL, $res['message']);
        }
        return $this->success();
    }
    
    /**
     * 编辑公告
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $res = NoticeModel::easyEdit([
            'beforeSave' => function ($data) {
                // 显示类型 1链接
                if ($data->show_type == 1 && empty($data->link)) {
                    return error('链接不能为空');
                } else if ($data->show_type == 0 && empty($data->detail)) {
                    // 内容
                    return error('内容不能为空');
                }
            },
            'afterSave' => function ($model) {
                // 日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'sort_by' => $model->sort_by,
                    'title' => $model->title,
                    'status' => $model->status == 1 ? '显示' : '隐藏',
                    'show_type' => $model->show_type == 0 ? '显示内容' : '链接跳转',
                    'link' => $model->link,
                    'detail' => $model->detail,
                ];
                LogModel::write(
                    $this->userId,
                    MallLogConstant::MALL_NOTICE_EDIT,
                    MallLogConstant::getText(MallLogConstant::MALL_NOTICE_EDIT),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identity_code' => [
                            MallLogConstant::MALL_NOTICE_ADD,
                            MallLogConstant::MALL_NOTICE_EDIT,
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new MallException(MallException::NOTICE_EDIT_FAIL, $res['message']);
        }
        return $this->success();
    }
    
    /**
     * 修改状态
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeStatus()
    {
        $res = NoticeModel::easySwitch('status', [
            'afterAction' => function ($model) {
                // 记录日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'title' => $model->title,
                    'status' => $model->status == 1 ? '显示' : '隐藏',
                ];
                LogModel::write(
                    $this->userId,
                    MallLogConstant::MALL_NOTICE_CHANGE_STATUS,
                    MallLogConstant::getText(MallLogConstant::MALL_NOTICE_CHANGE_STATUS),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identity_code' => [
                            MallLogConstant::MALL_NOTICE_CHANGE_STATUS,
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new MallException(MallException::NOTICE_CHANGE_STATUS_FAIL, $res['message']);
        }
        return $this->success();
    }
    
    /**
     * 删除
     * @return array|int[]|\yii\web\Response
     * @throws MallException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $res = NoticeModel::easyDelete([
            'afterDelete' => function ($model) {
                // 日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'sort_by' => $model->sort_by,
                    'title' => $model->title,
                    'status' => $model->status == 1 ? '显示' : '隐藏',
                    'show_type' => $model->show_type == 0 ? '显示内容' : '链接跳转',
                    'link' => $model->link,
                    'detail' => $model->detail,
                ];
                LogModel::write(
                    $this->userId,
                    MallLogConstant::MALL_NOTICE_DELETE,
                    MallLogConstant::getText(MallLogConstant::MALL_NOTICE_DELETE),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identity_code' => [
                            MallLogConstant::MALL_NOTICE_DELETE,
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new MallException(MallException::NOTICE_DELETE_FAIL, $res['message']);
        }
       
        return $this->success();
    }
    
}