<?php

namespace shopstar\admin\wechatCustomerService;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\wechatCustomerService\WechatCustomerServiceLogConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\wechatCustomerService\WechatCustomerServiceCompanyModel;
use shopstar\models\wechatCustomerService\WechatCustomerServiceServicerModel;
use yii\web\Response;

/**
 * Class CompanyController
 * @package shopstar\admin\wechatCustomerService
 * @author yuning
 */
class CompanyController extends KdxAdminApiController
{

    /**
     * 企业列表
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionList()
    {

        $params = [
            'alias' => 'c',
            'where' => [
                'c.is_deleted' => 0
            ],
            'select' => [
                'c.id',
                'c.corp_id',
                'c.name',
                'c.is_deleted',
                'c.created_at',
            ],
            'searchs' => [
                ['c.name', 'like', 'name']
            ],
            'orderBy' => [
                'c.id' => SORT_DESC,
            ],
        ];
        $list = WechatCustomerServiceCompanyModel::getColl($params, [
            'pager' => !(bool)RequestHelper::getInt('only_list'),
            'callable' => function (&$result) {
                $result['count'] = WechatCustomerServiceServicerModel::getCustomerServiceCount($result['id']);
            }
        ]);

        return $this->result(['data' => $list]);
    }


    /**
     * 添加企业
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionAdd()
    {
        $result = WechatCustomerServiceCompanyModel::easyAdd([
            'attributes' => [
                'created_at' => DateTimeHelper::now(),
                'is_default' => 0,
            ],
            'afterSave' => function ($result) {

                /**
                 * @var WechatCustomerServiceCompanyModel $result
                 */

                LogModel::write(
                    $this->userId,
                    WechatCustomerServiceLogConstant::COMPANY_ADD,
                    WechatCustomerServiceLogConstant::getText(WechatCustomerServiceLogConstant::COMPANY_ADD),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => $result->getLogAttributeRemark([
                            'corp_id' => $result->corp_id,
                            'name' => $result->name,
                        ]),
                        'dirty_identify_code' => [
                            WechatCustomerServiceLogConstant::COMPANY_ADD,
                        ],
                    ]
                );
            }

        ]);

        return $this->result($result);
    }


    /**
     * 修改
     * @return array|int[]|Response
     */
    public function actionEdit()
    {
        $result = WechatCustomerServiceCompanyModel::easyEdit([
            'beforeSave' => function ($result) {

            },
            'afterSave' => function ($result) {

                /**
                 * @var WechatCustomerServiceCompanyModel $result
                 */
                LogModel::write(
                    $this->userId,
                    WechatCustomerServiceLogConstant::COMPANY_EDIT,
                    WechatCustomerServiceLogConstant::getText(WechatCustomerServiceLogConstant::COMPANY_EDIT),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => $result->getLogAttributeRemark([
                            'corp_id' => $result->corp_id,
                            'name' => $result->name,
                        ]),
                        'dirty_identify_code' => [
                            WechatCustomerServiceLogConstant::COMPANY_ADD,
                            WechatCustomerServiceLogConstant::COMPANY_EDIT,
                        ],
                    ]
                );
            }
        ]);
        return $this->result($result);
    }

    /**
     * 删除企业
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionDelete()
    {
        $res = WechatCustomerServiceCompanyModel::easyRecycle([
            'andWhere' => [
                'is_deleted' => 0,
            ],
            'afterSave' => function ($model) {
                // 日志
                LogModel::write(
                    $this->userId,
                    WechatCustomerServiceLogConstant::COMPANY_DELETE,
                    WechatCustomerServiceLogConstant::getText(WechatCustomerServiceLogConstant::COMPANY_DELETE),
                    $model->id,
                    [
                        'log_data' => ['id' => $model->id, 'is_deleted' => 1],
                        'log_primary' => [
                            '企业ID' => $model->corp_id,
                            '企业名称' => $model->name,
                            '操作时间' => DateTimeHelper::now(),
                        ],
                    ]
                );


            }
        ]);
        if (is_error($res)) {
            return $this->error($res);
        }
        return $this->success();
    }
}