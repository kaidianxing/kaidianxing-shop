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
use shopstar\constants\virtualAccount\VirtualAccountLogConstant;
use shopstar\exceptions\virtualAccount\VirtualAccountException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\virtualAccount\VirtualAccountModel;

/**
 * 卡密库
 * Class IndexController
 * @package apps\virtualAccount\manage
 */
class VirtualAccountRecycleBinController extends KdxAdminApiController
{
    /**
     * index
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $params = [
            'where' => [
                'is_delete' => 1,
            ],
            'select' => [
                'id',
                'name',
                'total_count',
                'stock',
                'sell_count',
                'mailer',
                'created_at',
                'updated_at',
                'is_delete',
            ],
            'searchs' => [
                ['name', 'like', 'keyword']
            ],
            'orderBy' => [
                'updated_at' => SORT_DESC
            ]
        ];

        $data = VirtualAccountModel::getColl($params, [
            'callable' => function (&$row) {
                // 剩余数量
                $row['remaining_count'] = (int)$row['stock'] - (int)$row['sell_count'];
            },
        ]);
        return $this->success($data);
    }

    /**
     * 彻底删除
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::post('id');
        if (!$id) {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }
        // 彻底删除
        VirtualAccountModel::deleteData($id, 2);
        // 日志
        LogModel::write(
            $this->userId,
            VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_DELETE_COMPLETE,
            VirtualAccountLogConstant::getText(VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_DELETE_COMPLETE),
            $id,
            [
                'log_data' => [],
                'log_primary' => [
                    'id' => $id,
                    '操作' => '彻底删除',
                ],
            ]
        );
        return $this->result();

    }

    /**
     * 恢复
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRestore()
    {
        $id = RequestHelper::post('id');
        if (!$id) {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }
        // 恢复
        VirtualAccountModel::deleteData($id, 0);
        // 日志
        LogModel::write(
            $this->userId,
            VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_RESTORE,
            VirtualAccountLogConstant::getText(VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_RESTORE),
            $id,
            [
                'log_data' => [],
                'log_primary' => [
                    'id' => $id,
                    '操作' => '恢复',
                ]
            ]
        );
        return $this->result();

    }

}