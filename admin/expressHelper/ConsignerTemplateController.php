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

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\expressHelper\ExpressHelperLogConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\expressHelper\ExpressHelperConsignerTemplateModel;
use shopstar\models\log\LogModel;

/**
 * 发货人模板
 * Class ConsignerTemplateController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\expressHelper
 */
class ConsignerTemplateController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'simple-list'
        ]
    ];

    /**
     * @return array|int[]|\yii\web\Response
     */
    public function actionSimpleList()
    {
        $list = ExpressHelperConsignerTemplateModel::getColl([
            'where' => [],
            'searchs' => [
                ['name', 'like']
            ]
        ], [
            'pager' => false,
        ]);

        return $this->result($list);
    }

    /**
     * 列表
     * @return array|int[]|\yii\web\Response
     */
    public function actionList()
    {
        $list = ExpressHelperConsignerTemplateModel::getColl([
            'where' => [],
            'searchs' => [
                ['name', 'like']
            ],
            'orderBy' => [
                'created_at' => SORT_DESC
            ]
        ]);

        return $this->result($list);
    }

    /**
     * 添加
     * @return array|int[]|\yii\web\Response
     */
    public function actionAdd()
    {
        $result = ExpressHelperConsignerTemplateModel::easyAdd([
            'attributes' => [
                'created_at' => DateTimeHelper::now(),
                'is_default' => 0,
            ],
            'beforeSave' => function ($result) {
                $exist = ExpressHelperConsignerTemplateModel::findOne(['name' => $result->name]);
                if (!empty($exist)) {
                    return error('模板名称重复，请重新输入');
                }
            },
            'afterSave' => function ($result) {

                /**
                 * @var ExpressHelperConsignerTemplateModel $result
                 */
                LogModel::write(
                    $this->userId,
                    ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_ADD,
                    ExpressHelperLogConstant::getText(ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_ADD),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => $result->getLogAttributeRemark([
                            'name' => $result->name,
                            'consigner_company' => $result->consigner_company,
                            'consigner_name' => $result->consigner_name,
                            'consigner_mobile' => $result->consigner_mobile,
                            'consigner_province' => $result->consigner_province,
                            'consigner_city' => $result->consigner_city,
                            'consigner_area' => $result->consigner_area,
                            'consigner_address' => $result->consigner_address,
                            'postcode' => $result->postcode,
                        ]),
                        'dirty_identify_code' => [
                            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_ADD,
                            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_EDIT
                        ]
                    ]
                );
            }
        ]);

        return $this->result($result);
    }

    /**
     * 修改
     * @return array|int[]|\yii\web\Response
     */
    public function actionEdit()
    {
        $result = ExpressHelperConsignerTemplateModel::easyEdit([
            'beforeSave' => function ($result) {
                $exist = ExpressHelperConsignerTemplateModel::findOne(['name' => $result->name]);
                if (!empty($exist) && $exist->id != $result->id) {
                    return error('模板名称重复，请重新输入');
                }
            },
            'afterSave' => function ($result) {

                /**
                 * @var ExpressHelperConsignerTemplateModel $result
                 */
                LogModel::write(
                    $this->userId,
                    ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_EDIT,
                    ExpressHelperLogConstant::getText(ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_EDIT),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => $result->getLogAttributeRemark([
                            'name' => $result->name,
                            'consigner_company' => $result->consigner_company,
                            'consigner_name' => $result->consigner_name,
                            'consigner_mobile' => $result->consigner_mobile,
                            'consigner_province' => $result->consigner_province,
                            'consigner_city' => $result->consigner_city,
                            'consigner_area' => $result->consigner_area,
                            'consigner_address' => $result->consigner_address,
                            'postcode' => $result->postcode,
                        ]),
                        'dirty_identify_code' => [
                            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_ADD,
                            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_EDIT
                        ]
                    ]
                );
            }
        ]);
        return $this->result($result);
    }

    /**
     * 删除
     * @return array|int[]|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        $data = [];
        $result = ExpressHelperConsignerTemplateModel::easyDelete([
            'andWhere' => [],
            'beforeDelete' => function ($result) use (&$data) {
                /**
                 * @var ExpressHelperConsignerTemplateModel $result
                 */
                $data = $result->attributes;
            },
            'afterDelete' => function ($result) use ($data) {
                /**
                 * @var ExpressHelperConsignerTemplateModel $result
                 */
                LogModel::write(
                    $this->userId,
                    ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_DELETE,
                    ExpressHelperLogConstant::getText(ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_DELETE),
                    $result->id,
                    [
                        'log_data' => $data,
                        'log_primary' => $result->getLogAttributeRemark([
                            'name' => $data['name'],
                            'consigner_company' => $data['consigner_company'],
                            'consigner_name' => $data['consigner_name'],
                            'consigner_mobile' => $data['consigner_mobile'],
                            'consigner_province' => $data['consigner_province'],
                            'consigner_city' => $data['consigner_city'],
                            'consigner_area' => $data['consigner_area'],
                            'consigner_address' => $data['consigner_address'],
                            'postcode' => $data['postcode'],
                        ])
                    ]
                );
            }
        ]);

        return $this->result($result);
    }

    /**
     * 设置默认
     * @return array|int[]|\yii\web\Response
     * @throws \Throwable
     */
    public function actionSwitch()
    {
        $isDefault = RequestHelper::postInt('is_default');
        $id = RequestHelper::postInt('id');

        if ($isDefault) {
            ExpressHelperConsignerTemplateModel::updateAll(['is_default' => 0], []);
        }

        ExpressHelperConsignerTemplateModel::updateAll(['is_default' => $isDefault], ['id' => $id]);

        $model = new ExpressHelperConsignerTemplateModel();

        LogModel::write(
            $this->userId,
            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_SWITCH,
            ExpressHelperLogConstant::getText(ExpressHelperLogConstant::EXPRESS_HELPER_LOG_CONSIGNER_TEMPLATE_SWITCH),
            $id,
            [
                'log_data' => ['is_default' => $isDefault],
                'log_primary' => $model->getLogAttributeRemark([
                    'is_default' => $isDefault == 1 ? '是' : '否',
                ])
            ]
        );

        return $this->result();
    }

}
