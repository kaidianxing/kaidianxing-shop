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
 * Class CustomerServiceController
 * @package shopstar\admin\wechatCustomerService
 * @author yuning
 */
class CustomerServiceController extends KdxAdminApiController
{
    /**
     * 客服列表
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionList()
    {

        $params = [
            'alias' => 's',
            'where' => [
                's.is_deleted' => 0
            ],
            'select' => [
                's.id',
                's.name',
                's.company_id',
                'c.name as company_name',
                'c.corp_id',
                's.link',
                's.is_deleted',
                's.created_at',
            ],
            'searchs' => [
                ['s.name', 'like', 'name']
            ],
            'leftJoins' => [
                [WechatCustomerServiceCompanyModel::tableName() . ' c', 'c.id = s.company_id'],
            ],
            'orderBy' => [
                's.id' => SORT_DESC,
            ],
        ];
        $list = WechatCustomerServiceServicerModel::getColl($params, [
            'pager' => !(bool)RequestHelper::getInt('only_list'),
            'callable' => function (&$result) {
                $result['link'] = $result['link'] . '?corp_id=' . $result['corp_id'];
            }

        ]);

        return $this->result(['data' => $list]);
    }


    /**
     * 添加客服
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionAdd()
    {
        $result = WechatCustomerServiceServicerModel::easyAdd([
            'attributes' => [
                'created_at' => DateTimeHelper::now(),
                'is_default' => 0,
            ],
            'beforeSave' => function ($result) {
                $exist = WechatCustomerServiceServicerModel::findOne(['name' => $result->name, 'company_id' => $result->company_id, 'is_deleted' => 0]);
                if (!empty($exist)) {
                    return error('客服名称重复，请重新输入');
                }

                $link = WechatCustomerServiceServicerModel::findOne(['link' => $result->link, 'is_deleted' => 0]);
                if (!empty($link)) {
                    return error('客服链接重复，请重新输入');
                }
            },
            'afterSave' => function ($result) {

                /**
                 * @var WechatCustomerServiceServicerModel $result
                 */

                $company = WechatCustomerServiceCompanyModel::findOne(['id' => $result->company_id]);
                LogModel::write(
                    $this->userId,
                    WechatCustomerServiceLogConstant::CUSTOMER_SERVICE_ADD,
                    WechatCustomerServiceLogConstant::getText(WechatCustomerServiceLogConstant::CUSTOMER_SERVICE_ADD),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => $result->getLogAttributeRemark([
                            'company_name' => $company->name,
                            'name' => $result->name,
                            'link' => $result->link,
                        ]),
                        'dirty_identify_code' => [
                            WechatCustomerServiceLogConstant::CUSTOMER_SERVICE_ADD,
                        ],
                    ]
                );
            }

        ]);

        return $this->result($result);
    }


    /**
     * 详情
     * @return mixed
     * @author yuning
     */
    public function actionInfo()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        $customerService = WechatCustomerServiceServicerModel::findOne(['id' => $id]);

        $company = WechatCustomerServiceCompanyModel::findOne(['id' => $customerService['company_id']]);

        $data = [
            'company' => $company,
            'customer_service' => $customerService,
        ];
        return $this->result($data);
    }


    /**
     * 修改
     * @return array|int[]|Response
     */
    public function actionEdit()
    {
        $result = WechatCustomerServiceServicerModel::easyEdit([
            'beforeSave' => function ($result) {
                $exist = WechatCustomerServiceServicerModel::findOne(['name' => $result->name, 'company_id' => $result->company_id, 'is_deleted' => 0]);
                if (!empty($exist) && $exist->id != $result->id) {
                    return error('客服名称重复，请重新输入');
                }

                $link = WechatCustomerServiceServicerModel::findOne(['link' => $result->link, 'is_deleted' => 0]);
                if (!empty($link) && $link->id != $result->id) {
                    return error('客服链接重复，请重新输入');
                }
            },
            'afterSave' => function ($result) {

                /**
                 * @var WechatCustomerServiceServicerModel $result
                 */

                $company = WechatCustomerServiceCompanyModel::findOne(['id' => $result->company_id]);
                LogModel::write(
                    $this->userId,
                    WechatCustomerServiceLogConstant::CUSTOMER_SERVICE_EDIT,
                    WechatCustomerServiceLogConstant::getText(WechatCustomerServiceLogConstant::CUSTOMER_SERVICE_EDIT),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => $result->getLogAttributeRemark([
                            'company_name' => $company->name,
                            'name' => $result->name,
                            'link' => $result->link,
                        ]),
                        'dirty_identify_code' => [
                            WechatCustomerServiceLogConstant::CUSTOMER_SERVICE_ADD,
                            WechatCustomerServiceLogConstant::CUSTOMER_SERVICE_EDIT,
                        ],
                    ]
                );
            }
        ]);
        return $this->result($result);
    }


    /**
     * 删除客服
     * @return array|int[]|Response
     * @author yuning
     */
    public function actionDelete()
    {
        $res = WechatCustomerServiceServicerModel::easyRecycle([
            'andWhere' => [
                'is_deleted' => 0,
            ],
            'afterSave' => function ($model) {
                // 日志
                LogModel::write(
                    $this->userId,
                    WechatCustomerServiceLogConstant::CUSTOMER_SERVICE_DELETE,
                    WechatCustomerServiceLogConstant::getText(WechatCustomerServiceLogConstant::CUSTOMER_SERVICE_DELETE),
                    $model->id,
                    [
                        'log_data' => ['id' => $model->id, 'is_deleted' => 1],
                        'log_primary' => [
                            '客服ID' => $model->id,
                            '客服名称' => $model->name,
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