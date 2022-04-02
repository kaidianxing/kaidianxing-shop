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
use shopstar\components\electronicSheet\bases\ElectronicSheetApiConstant;
use shopstar\components\electronicSheet\ElectronicSheetComponents;
use shopstar\constants\expressHelper\ExpressHelperLogConstant;
use shopstar\constants\expressHelper\ExpressTemplateTypeConstant;
use shopstar\exceptions\expressHelper\ExpressHelperException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\core\CoreExpressModel;
use shopstar\models\expressHelper\ExpressHelperExpressTemplateModel;
use shopstar\models\log\LogModel;
use shopstar\services\expressHelper\PrintHandler;

/**
 * 面单模板
 * Class ExpressTemplateController
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\admin\expressHelper
 */
class ExpressTemplateController extends KdxAdminApiController
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
        $list = ExpressHelperExpressTemplateModel::getColl([
            'where' => [],
            'select' => [
                'id',
                'name',
                'is_default',
                'express_company',
                'is_sub'
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
     * 列表
     * @return array|int[]|\yii\web\Response
     */
    public function actionList()
    {
        $list = ExpressHelperExpressTemplateModel::getColl([
            'where' => [],
            'select' => [
                'id',
                'name',
                'type',
                'express_company',
                'is_default'
            ],
            'searchs' => [
                ['name', 'like']
            ],
            'orderBy' => [
                'created_at' => SORT_DESC
            ]
        ], [
            'callable' => function (&$result) {
                $result['express_company_text'] = CoreExpressModel::getNameByCode($result['express_company']);
            }
        ]);

        return $this->result($list);
    }

    /**
     * 添加
     * @return array|int[]|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionAdd()
    {
        $usableExpress = [];
        if (RequestHelper::isGet()) {
            //获取全部快递公司
            $allExpress = CoreExpressModel::getAll();

            //可用的快递公司
            $usableExpress = array_intersect_key(array_column($allExpress, null, 'key'), ExpressHelperExpressTemplateModel::$expressTemplateFormatMap);
        }

        $result = ExpressHelperExpressTemplateModel::easyAdd([
            'attributes' => [
                'created_at' => DateTimeHelper::now(),
                'is_default' => 0
            ],
            'beforeSave' => function ($result) {
                $exist = ExpressHelperExpressTemplateModel::findOne(['name' => $result->name]);
                if (!empty($exist)) {
                    return error('模板名称重复，请重新输入');
                }
            },
            'loadParams' => [
                'template_format' => ExpressHelperExpressTemplateModel::$expressTemplateFormatMap,
                'express' => $usableExpress
            ],
            'afterSave' => function ($result) {

                /**
                 * @var ExpressHelperExpressTemplateModel $result
                 */
                LogModel::write(
                    $this->userId,
                    ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_ADD,
                    ExpressHelperLogConstant::getText(ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_ADD),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => $result->getLogAttributeRemark([
                            'name' => $result->name,
                            'type' => ExpressTemplateTypeConstant::getText($result->type),
                            'express_company' => CoreExpressModel::getNameByCode($result->express_company),
                            'template_account' => $result->template_account,
                            'template_password' => $result->template_password,
                            'monthly_code' => $result->monthly_code,
                            'branch_name' => $result->branch_name,
                            'branch_code' => $result->branch_code,
                            'template_style' => $result->template_style,
                            'is_notice' => $result->is_notice == 0 ? '是' : '否',
                            'auto_send' => $result->auto_send == 1 ? '是' : '否',
                        ]),
                        'dirty_identify_code' => [
                            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_ADD,
                            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_EDIT
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
        $usableExpress = [];
        if (RequestHelper::isGet()) {
            //获取全部快递公司
            $allExpress = CoreExpressModel::getAll();

            //可用的快递公司
            $usableExpress = array_intersect_key(array_column($allExpress, null, 'key'), ExpressHelperExpressTemplateModel::$expressTemplateFormatMap);
        }

        $result = ExpressHelperExpressTemplateModel::easyEdit([
            'loadParams' => [
                'template_format' => ExpressHelperExpressTemplateModel::$expressTemplateFormatMap,
                'express' => $usableExpress
            ],
            'beforeSave' => function ($result) {
                $exist = ExpressHelperExpressTemplateModel::findOne(['name' => $result->name]);
                if (!empty($exist) && $exist->id != $result->id) {
                    return error('模板名称重复，请重新输入');
                }
            },
            'afterSave' => function ($result) {

                /**
                 * @var ExpressHelperExpressTemplateModel $result
                 */
                LogModel::write(
                    $this->userId,
                    ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_EDIT,
                    ExpressHelperLogConstant::getText(ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_EDIT),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => $result->getLogAttributeRemark([
                            'name' => $result->name,
                            'type' => ExpressTemplateTypeConstant::getText($result->type),
                            'express_company' => CoreExpressModel::getNameByCode($result->express_company),
                            'template_account' => $result->template_account,
                            'template_password' => $result->template_password,
                            'monthly_code' => $result->monthly_code,
                            'branch_name' => $result->branch_name,
                            'branch_code' => $result->branch_code,
                            'template_style' => $result->template_style,
                            'is_notice' => $result->is_notice == 0 ? '是' : '否',
                            'auto_send' => $result->auto_send == 1 ? '是' : '否',
                        ]),
                        'dirty_identify_code' => [
                            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_ADD,
                            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_EDIT
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

        //暂存
        $data = [];
        $result = ExpressHelperExpressTemplateModel::easyDelete([
            'andWhere' => [],
            'beforeDelete' => function ($result) use (&$data) {
                /**
                 * @var ExpressHelperExpressTemplateModel $result
                 */
                $data = $result->attributes;
            },
            'afterDelete' => function ($result) use ($data) {

                LogModel::write(
                    $this->userId,
                    ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_DELETE,
                    ExpressHelperLogConstant::getText(ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_DELETE),
                    $result->id,
                    [
                        'log_data' => $data,
                        'log_primary' => $result->getLogAttributeRemark([
                            'name' => $data['name'],
                            'type' => ExpressTemplateTypeConstant::getText($data['type']),
                            'express_company' => CoreExpressModel::getNameByCode($data['express_company']),
                            'template_account' => $data['template_account'],
                            'template_password' => $data['template_password'],
                            'monthly_code' => $data['monthly_code'],
                            'branch_name' => $data['branch_name'],
                            'branch_code' => $data['branch_code'],
                            'template_style' => $data['template_style'],
                            'is_notice' => $data['is_notice'] == 0 ? '是' : '否',
                            'auto_send' => $data['auto_send'] == 1 ? '是' : '否',
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
            ExpressHelperExpressTemplateModel::updateAll(['is_default' => 0], []);
        }

        ExpressHelperExpressTemplateModel::updateAll(['is_default' => $isDefault], ['id' => $id]);

        $model = new ExpressHelperExpressTemplateModel();

        LogModel::write(
            $this->userId,
            ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_SWITCH,
            ExpressHelperLogConstant::getText(ExpressHelperLogConstant::EXPRESS_HELPER_LOG_EXPRESS_TEMPLATE_SWITCH),
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

    /**
     * 测试打印
     * @throws ExpressHelperException
     * @throws \yii\base\InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionTestPrint()
    {

        $post = RequestHelper::post();

        //获取api实例  如果后期添加的菜鸟裹裹实体的话，直接添加实体并调用
        $instance = ElectronicSheetComponents::getInstance(ElectronicSheetApiConstant::API_KDN);
        if (is_error($instance)) {
            throw new ExpressHelperException(ExpressHelperException::MANAGE_EXPRESS_TEMPLATE_TEST_PRINT_PARAMS_ERROR, $instance['message']);
        }

        //注入配置参数
        $result = $instance->init();
        if (is_error($result)) {
            throw new ExpressHelperException(ExpressHelperException::MANAGE_EXPRESS_TEMPLATE_TEST_PRINT_LACK_PARAMS_ERROR);
        }

        //获取助手类
        $printHandler = new PrintHandler();

        $result = $instance->submitEOrder([
            'CustomerName' => $post['template_account'],//电子面单账号
            'CustomerPwd' => $post['template_password'],//电子面单密码

//            'CustomerName' => 'testzto',//电子面单账号
//            'CustomerPwd' => 'testztopwd', //电子面单密码
            'MonthCode' => $post['monthly_code'],//月结编码
            'SendSite' => $post['branch_name'],//所属网点
            'SendStaff' => $post['branch_code'],//网点编码
            'ShipperCode' => $post['express_company'],//快递公司名称
            'OrderCode' => $printHandler->getOrderCode(time() . StringHelper::random(5), [time(), time(), time(), time()]),//订单号
            'PayType' => $post['pay_type'],
            'ExpType' => '1',//快递类型，默认标准快递 后期可以根据需求更改

            //收货人信息
            'Receiver' => [
                'Name' => '测试收货人',//收货人姓名
                'Tel' => '',//电话
                'Mobile' => '15000000000',//手机
                'PostCode' => '100000',//邮编
                'ProvinceName' => '北京',//省
                'CityName' => '北京',//市
                'ExpAreaName' => '朝阳',//区/县
                'Address' => '天坛公园',
            ],

            //发件人信息
            'Sender' => [
                'Company' => '测试发货人公司',
                'Name' => '测试收货人',
                'Tel' => '',//电话
                'Mobile' => '15000000001',//手机
                'PostCode' => '100000',//邮编
                'ProvinceName' => '北京',//省
                'CityName' => '北京',//市
                'ExpAreaName' => '朝阳',//区/县
                'Address' => '东单公园',
            ],

            'IsNotice' => 1,//快递员上门通知
            'Weight' => 0.01,//重量
            'Quantity' => 1,//包裹数量 默认1个

            //货物
            'Commodity' => [
                [
                    'GoodsName' => '测试商品测试测试'
                ]
            ],

            'IsReturnPrintTemplate' => 1, //是否返回电子面单模板
            'TemplateSize' => '', //尺寸
            'CurrencyCode' => 'CNY', //货币类型 写死人民币
        ]);

        if (is_error($result)) {
            return $this->error($result['message']);
        }

        if ($result['Success'] == false) {
            return $this->error($result['Reason']);
        }

        return $this->success();
    }
}
