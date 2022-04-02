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

namespace shopstar\admin\member;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\log\member\MemberLogConstant;
use shopstar\exceptions\member\GroupException;
use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\group\MemberGroupModel;
use yii\web\Response;

/**
 * 会员标签组类
 * Class GroupController
 * @package shopstar\admin\member
 */
class GroupController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowPermActions' => [
            'index'
        ]
    ];

    /**
     * 会员标签组列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $isAll = RequestHelper::get('is_all', 0);
        $params = [
            'searchs' => [
                ['state', 'int', 'state'],
                ['group_name', 'like', 'keyword']
            ],
            'where' => [],
            'select' => ['id', 'group_name', 'description'],
            'orderBy' => [
                'id' => SORT_DESC
            ]
        ];

        // 获取标签组下的会员数
        $countGroup = MemberGroupMapModel::getMemberCount();

        // 标签组列表
        $groups = MemberGroupModel::getColl($params, [
            'pager' => !($isAll == 1),
            'onlyList' => $isAll == 1,
            'callable' => function (&$row) use ($countGroup) {
                $row['member_count'] = $countGroup[$row['id']]['count'] ?: 0;
            }
        ]);

        if ($isAll) {
            $groups = ['list' => $groups];
        }

        return $this->result($groups);
    }

    /**
     * 会员分组简单列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSimpleList()
    {
        $levels = MemberGroupModel::getColl([
            'select' => ['id', 'group_name'],
            'where' => [],
            'orderBy' => [
                'id' => SORT_DESC,
            ]
        ], [
            'pager' => false,
            'onlyList' => true
        ]);

        return $this->result(['list' => $levels]);
    }

    /**
     * 新增标签组
     * @return array|int[]|Response
     * @throws GroupException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $res = MemberGroupModel::easyAdd([
            'attributes' => [],
            'afterSave' => function ($model) {
                // 记录日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'group_name' => $model->group_name,
                    'description' => $model->description,
                ];
                LogModel::write(
                    $this->userId,
                    MemberLogConstant::MEMBER_GROUP_ADD,
                    MemberLogConstant::getText(MemberLogConstant::MEMBER_GROUP_ADD),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identify_code' => [
                            MemberLogConstant::MEMBER_GROUP_ADD,
                            MemberLogConstant::MEMBER_GROUP_EDIT,
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new GroupException(GroupException::ADD_GROUP_SAVE_FAIL, $res['message']);
        }

        return $this->success();
    }

    /**
     * 修改标签组
     * @return Response
     * @throws GroupException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit(): Response
    {
        $res = MemberGroupModel::easyEdit([
            'andWhere' => [],
            'afterSave' => function ($model) {
                // 记录日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'group_name' => $model->group_name,
                    'description' => $model->description,
                ];

                LogModel::write(
                    $this->userId,
                    MemberLogConstant::MEMBER_GROUP_EDIT,
                    MemberLogConstant::getText(MemberLogConstant::MEMBER_GROUP_EDIT),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identify_code' => [
                            MemberLogConstant::MEMBER_GROUP_ADD,
                            MemberLogConstant::MEMBER_GROUP_EDIT,
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new GroupException(GroupException::EDIT_GROUP_SAVE_FAIL, $res['message']);
        }

        return $this->success($res);
    }

    /**
     * 删除/批量删除标签组
     * @return Response
     * @throws GroupException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete(): Response
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();
        // 删除标签组
        $res = MemberGroupModel::easyDelete([
            'andWhere' => [],
            'afterDelete' => function ($model) {
                // 删除map
                MemberGroupMapModel::deleteAll(['group_id' => $model->id]);
                // 记录日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'group_name' => $model->group_name,
                    'description' => $model->description,
                ];
                
                LogModel::write(
                    $this->userId,
                    MemberLogConstant::MEMBER_GROUP_DELETE,
                    MemberLogConstant::getText(MemberLogConstant::MEMBER_GROUP_DELETE),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identify_code' => [
                            MemberLogConstant::MEMBER_GROUP_DELETE,
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            $transaction->rollBack();
            throw new GroupException(GroupException::DELETE_FAIL, $res['message']);
        }
        $transaction->commit();

        return $this->success();
    }

}