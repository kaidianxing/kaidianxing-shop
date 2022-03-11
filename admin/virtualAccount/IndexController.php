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
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use shopstar\models\virtualAccount\VirtualAccountModel;
use yii\helpers\Json;

/**
 * 卡密库
 * Class IndexController
 * @package apps\virtualAccount\manage
 */
class IndexController extends KdxAdminApiController
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
                'is_delete' => 0,
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
                'id' => SORT_DESC
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
     * 保存
     * @return \yii\web\Response
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $data = $this->getParams();
        $result = VirtualAccountModel::easyAdd([
            'attributes' => $data
        ]);
        // 记录日志
        $result && LogModel::write(
            $this->userId,
            VirtualAccountLogConstant::VIRTUAL_ACCOUNT_EDIT_ADDRESS,
            VirtualAccountLogConstant::getText(VirtualAccountLogConstant::VIRTUAL_ACCOUNT_EDIT_ADDRESS),
            $result['id'],
            [
                'log_data' => $data,
                'log_primary' => [
                    'id' => $result['id'],
                    '卡密库名称' => $data['name'],
                    '使用说明' => !$data['use_description'] ? '关闭' : '开启',
                    '使用地址' => !$data['use_address'] ? '关闭' : '开启',
                    '发卡顺序' => !$data['sequence'] ? '添加时间排序' : '权重排序',
                    '邮箱发送' => !$data['mailer'] ? '关闭' : '开启',
                    '卡密库数据排重' => !$data['repeat'] ? '关闭' : '开启',
                    '新增字段' => Json::decode($data['config']),
                ],
            ]
        );

        return $this->success($result);
    }

    /**
     * 编辑
     * @return array|\yii\web\Response
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::isPost() ? RequestHelper::postInt('id') : RequestHelper::getInt('id');
        $name = RequestHelper::isPost() ? RequestHelper::post('name') : '';
        // 名称不能为空
        if (RequestHelper::isPost() && isset($name) && empty($name)) {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }
        if (RequestHelper::isPost() && isset($name) && !empty($name)) {
            // 验证名称重复并排除自己
            if (VirtualAccountModel::checkName($name, $id)) {
                throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_PARAMS_NAME_ERROR);
            }
        }
        if (!$id) {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }

        $result = VirtualAccountModel::easyEdit([
            'onLoad' => function (&$data) {
                $data['data']['config'] = Json::decode($data['data']['config']);
            },
            'filterAttributes' => [
                'is_delete',
                'total_count',
                'sell_count',
                'created_at',
                'updated_at',
            ],
            'filterPostField' => [
                'repeat',
                'config',
            ],
            'afterSave' => function (VirtualAccountModel $model) {
                // 记录日志
                LogModel::write(
                    $this->userId,
                    VirtualAccountLogConstant::VIRTUAL_ACCOUNT_EDIT_EDIT,
                    VirtualAccountLogConstant::getText(VirtualAccountLogConstant::VIRTUAL_ACCOUNT_EDIT_EDIT),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => [
                            'id' => $model->id,
                            '卡密库名称' => $model->name,
                            '使用说明' => !$model->use_description ? '关闭' : '开启',
                            '使用地址' => !$model->use_address ? '关闭' : '开启',
                            '发卡顺序' => !$model->sequence ? '添加时间排序' : '权重排序',
                            '邮箱发送' => !$model->mailer ? '关闭' : '开启',
                        ],
                        'dirty_identify_code' => [
                            VirtualAccountLogConstant::VIRTUAL_ACCOUNT_EDIT_ADDRESS,
                            VirtualAccountLogConstant::VIRTUAL_ACCOUNT_EDIT_EDIT,
                        ],
                    ]
                );
            }
        ]);


        // 判断邮箱是否开启
//        $mailer = $this->checkMailer();

        return $this->result($result);
    }

    /**
     * 删除
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
        $info = VirtualAccountModel::getInfo($id);

        // 恢复或者删除卡密库
        VirtualAccountModel::deleteData($id, 1);

        // 处理关联的订单以及商品状态及库存等
        VirtualAccountModel::deleteVirtualAccount($id);
        // 日志
        LogModel::write(
            $this->userId,
            VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_DELETE,
            VirtualAccountLogConstant::getText(VirtualAccountLogConstant::VIRTUAL_ACCOUNT_DATA_EDIT_DELETE),
            $id,
            [
                'log_data' => [],
                'log_primary' => [
                    'id' => $id,
                    '操作' => '删除 ( ' . $info['name'] . ' ) 卡密库',
                ],
            ]
        );
        return $this->result();

    }

    /**
     * 校验参数
     * @return array
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function getParams()
    {
        $params = RequestHelper::post();
        // 名称不能为空
        if (empty($params['name'])) {
            throw new VirtualAccountException(VirtualAccountException::PARAMS_ERROR);
        }
        // 名称重复
        if (VirtualAccountModel::getInfoToName($params['name'])) {
            throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_PARAMS_NAME_ERROR);
        }
        // 消息通知应用权限，是否开启邮箱设置
        if ($params['mailer'] == 1) {
            // 如果没有开启邮箱设置
            $mailer = $this->checkMailer();
            if (!$mailer) {
                $params['mailer'] = 0;
            }
        }
        // 数据结构的数量限制 0 < $params['config'] < 10
        $config = Json::decode($params['config'], true);
        if (isset($config) && (count($config) > 10 || count($config) == 0)) {
            throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_PARAMS_CONFIG_ERROR);
        }
        $data = [
            'name' => $params['name'],
            'use_description' => $params['use_description'],
            'use_description_title' => $params['use_description_title'],
            'use_description_remark' => $params['use_description_remark'],
            'use_address' => $params['use_address'],
            'use_address_title' => $params['use_address_title'],
            'use_address_address' => $params['use_address_address'],
            'sequence' => $params['sequence'],
            'mailer' => $params['mailer'],
            'repeat' => $params['repeat'],
            'config' => $params['config'],
            'created_at' => DateTimeHelper::now(),
        ];
        return $data;
    }

    /**
     * 库名去重
     * @return array|\yii\web\Response
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCheckName()
    {
        $name = RequestHelper::post('name');
        if (VirtualAccountModel::getInfoToName($name)) {
            throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_PARAMS_NAME_ERROR);
        }
        return $this->result();
    }

    /**
     * 检测邮箱是否开启 之后移植到邮箱的model层
     * @return bool
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public function checkMailer()
    {
        $setting = ShopSettings::get('mailer');
        if ($setting['status'] == 1) {
            return true;
        }
        return false;
    }


}