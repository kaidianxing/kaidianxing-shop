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


namespace shopstar\admin\expressHelper;


use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\expressHelper\ExpressHelperSendBillTemplateModel;
use shopstar\bases\KdxAdminApiController;


/**
 * 发货单模板
 * Class SendBillTemplateController
 * @package apps\expressHelper\manage
 */
class SendBillTemplateController extends KdxAdminApiController
{
    public $configActions = [
        'allowPermActions' => [
            'simple-list',
            'list',
        ]
    ];

    /**
     * 获取无排序的模板列表
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSimpleList()
    {
        $list = ExpressHelperSendBillTemplateModel::getColl([
            'where' => [],
            'select' => [
                'id',
                'name',
                'is_default'
            ],
            'searchs' => [
                ['name', 'like']
            ],
            'orderBy' => [
                'created_at' => SORT_DESC
            ]
        ], [
            'pager' => false,
        ]);

        return $this->result($list);
    }


    /**
     * 模板列表
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $list = ExpressHelperSendBillTemplateModel::getColl([
            'where' => [],
            'searchs' => [
                ['name', 'like'],
                ['express_code', 'express_code']
            ],
            'select' => ['id', 'name', 'express_code', 'is_default'],
            'orderBy' => [
                'created_at' => SORT_DESC
            ]
        ]);

        if (!empty($list['list'])) {
            //获取快递公司列表
            $express = CoreExpressModel::getAll(false);

            $expressIndex = ArrayHelper::index($express, 'code');
            //查找快递公司匹配
            foreach ($list['list'] as &$row) {
                $code = $row['express_code'];

                $row['express_name'] = $expressIndex[$code]['name'] ?? '未找到';
            }
            unset($row);
        }

        return $this->result($list);
    }

    /**
     * 详情map
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetContentList()
    {
        $list = ExpressHelperSendBillTemplateModel::contentList();

        return $this->result($list);
    }

    /**
     * 设置默认模板
     * @return array|int[]|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSwitch()
    {
        $isDefault = RequestHelper::postInt('is_default');
        $id = RequestHelper::postInt('id');

        //所有模板更改为0
        if ($isDefault) {
            ExpressHelperSendBillTemplateModel::updateAll(['is_default' => 0], []);
        }
        //默认设置为1
        ExpressHelperSendBillTemplateModel::updateAll(['is_default' => $isDefault], ['id' => $id]);

        return $this->result();
    }

    /**
     * 快递列表
     * @return array|int[]|\yii\web\Response
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionExpress()
    {
        $result = [
            'express' => CoreExpressModel::getAll(false)
        ];
        return $this->result($result);
    }


    /**
     * 添加模板
     * @return array|int[]|\yii\web\Response
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $result = ExpressHelperSendBillTemplateModel::easyAdd([
            'attributes' => [
                'created_at' => DateTimeHelper::now(),
                'is_default' => 0,
            ],
            'beforeSave' => function ($data) {

                //验证名称重复
                $exit = ExpressHelperSendBillTemplateModel::checkName($data->name);

                if (!$exit) {
                    return error('发货单模板名称重复，请重新填写');
                }
            },
        ]);
        return $this->result($result);
    }

    /**
     * 模板数据修改
     * @return array|int[]|\yii\web\Response
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $result = ExpressHelperSendBillTemplateModel::easyEdit([
            'andWhere' => [],
            'beforeSave' => function ($result) {
                //验证名称重复
                $exist = ExpressHelperSendBillTemplateModel::checkName($result->name, $result->id);
                if (!$exist) {
                    return error('发货单模板重复 请重新填写');
                }
            }
        ]);

        return $this->result($result);
    }

    /**
     * 删除模板
     * @return array|int[]|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $result = ExpressHelperSendBillTemplateModel::easyDelete([
            'andWhere' => []
        ]);
        return $this->result($result);
    }

}