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

use shopstar\constants\log\member\MemberLogConstant;
use shopstar\constants\member\MemberLevelConstant;
use shopstar\exceptions\member\LevelException;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;
use shopstar\bases\KdxAdminApiController;
use Yii;
use yii\db\Exception;
use yii\helpers\Json;
use yii\web\Response;

/**
 * 会员等级类
 * Class LevelController
 * @package app\controllers\manage\member
 */
class LevelController extends KdxAdminApiController
{
    public $configActions = [
        'postActions' => [
            'add',
            'edit',
        ],
        'allowPermActions' => [
            'index'
        ]
    ];
    
    /**
     * 等级列表
     * @return string
     */
    public function actionIndex()
    {
        // 是否获取全部等级
        $isAll = RequestHelper::get('is_all', 0);
        $params = [
            'searchs' => [
                ['state', 'int', 'state'],
                ['level_name', 'like', 'keyword']
            ],
            'where' => [],
            'select' => [
                'id',
                'level',
                'level_name',
                'discount',
                'update_condition',
                'state',
                'is_default',
                'is_discount',
                'order_count',
                'order_money'
            ],
            'orderBy' => [
                'is_default' => SORT_DESC,
                'level' => SORT_ASC
            ]
        ];
        
        // 获取所有等级的会员数
        $memberCount = MemberLevelModel::getMemberCount();
        
        // 获取列表
        $levels = MemberLevelModel::getColl($params, [
            'pager' => !($isAll == 1),
            'onlyList' => $isAll == 1,
            'callable' => function(&$row) use ($memberCount) {
                if ($row['update_condition'] == MemberLevelConstant::LEVEL_UPGRADE_ORDER_COUNT) {
                    $row['update_text'] = '已完成的订单数量满'. $row['order_count'] .'单';
                } else if ($row['update_condition'] == MemberLevelConstant::LEVEL_UPGRADE_ORDER_MONEY) {
                    $row['update_text'] = '已完成的订单金额'. $row['order_money'] .'元';
                } else if ($row['update_condition'] == MemberLevelConstant::LEVEL_UPGRADE_GOODS) {
                    $row['update_text'] = '购买指定商品';
                } else {
                    $row['update_text'] = '不自动升级';
                }
                $row['state_text'] = MemberLevelModel::$stateText[$row['state']];
                $row['member_count'] = $memberCount[$row['id']]['count'] ?? 0;
            }
        ]);
        
        if ($isAll) {
            $levels = ['list' => $levels];
        }
        return $this->result($levels);
    }
    
    /**
     * 简单会员等级列表
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSimpleList()
    {
        $levels = MemberLevelModel::getColl([
            'select' => ['id', 'level_name'],
            'where' => [],
            'orderBy' => [
                'is_default' => SORT_DESC,
                'level' => SORT_ASC,
            ]
        ], [
            'pager' => false,
            'onlyList' => true
        ]);
        
        return $this->result(['list' => $levels]);
    }
    
    /**
     * 添加会员等级
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $res = MemberLevelModel::saveLevel($this->userId);
            if (is_error($res)) {
                throw new LevelException(LevelException::ADD_LEVEL_FAIL, $res['message']);
            }
            
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }
        return $this->success();
    }
    
    /**
     * 修改会员等级
     * @return Response
     * @throws LevelException
     * @throws Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new LevelException(LevelException::EDIT_PARAM_ERROR);
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $res = MemberLevelModel::saveLevel($this->userId, $id);
            if (is_error($res)) {
                throw new LevelException(LevelException::EDIT_LEVEL_FAIL, $res['message']);
            }
            $transaction->commit();
        } catch (LevelException $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }
        return $this->success();
    }
    
    /**
     * 等级详情
     * @return string
     * @throws LevelException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail()
    {
        $id = RequestHelper::get('id');
        if (empty($id)) {
            throw new LevelException(LevelException::DETAIL_PARAM_ERROR);
        }
        
        $level = MemberLevelModel::find()->where(['id' => $id])->first();
        if (empty($level)) {
            throw new LevelException(LevelException::DETAIL_LEVEL_NOT_EXISTS);
        }
        
        if (!empty($level['goods_ids'])) {
            $level['goods_info'] = GoodsModel::find()
                ->select('id, title, thumb, type, has_option')
                ->where(['id' => Json::decode($level['goods_ids'])])
                ->get();
        }
        
        return $this->result($level);
    }
    
    /**
     * 检查等级排序id是否存在
     * @return Response
     * @throws LevelException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCheckLevel()
    {
        $level = RequestHelper::postInt('level');
        
        if (!MemberLevelModel::checkLevel($level)) {
            throw new LevelException(LevelException::LEVEL_IS_EXISTS);
        }
        return $this->success();
    }
    
    /**
     * 删除/批量删除会员等级
     * @return Response
     * @throws LevelException
     * @throws Exception|\Throwable
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::postArray('id');
        if (empty($id)) {
            throw new LevelException(LevelException::DELETE_LEVEL_FAIL);
        }
    
        // 查找默认等级
        $defaultLevelId = MemberLevelModel::getDefaultLevelId();
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        
        $res = MemberLevelModel::easyDelete([
            'andWhere' => ['<>', 'id', $defaultLevelId], // 默认等级不删
            'afterDelete' => function ($model) use ($defaultLevelId) {
                // 该等级下的会员恢复默认等级
                MemberModel::updateAll(['level_id' => $defaultLevelId], ['level_id' => $model->id]);
                // 保存日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'level' => $model->level,
                    'level_name' => $model->level_name,
                    'upgrade_condition' => MemberLevelConstant::getText($model->update_condition),
                    'discount' => $model->is_discount == 0 ? '无' : ValueHelper::delZero($model->discount).'折',
                    'state' => MemberLevelModel::$stateText[$model->state],
                ];
                LogModel::write(
                    $this->userId,
                    MemberLogConstant::MEMBER_LEVEL_DELETE,
                    MemberLogConstant::getText(MemberLogConstant::MEMBER_LEVEL_DELETE),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identify_code'=> [
                            MemberLogConstant::MEMBER_LEVEL_DELETE,
                        ]
                    ]
                );
            },
        ]);
        if (is_error($res)) {
            $transaction->rollBack();
            throw new LevelException(LevelException::DELETE_FAIL, $res['message']);
        }
        $transaction->commit();
        
        return $this->success();
    }
    
    /**
     * 修改等级状态
     * @return Response
     * @throws LevelException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeState()
    {
        $res = MemberLevelModel::easySwitch('state', [
            'andWhere' => [],
            'isPost' => false,
            'afterAction' => function ($model) {
                // 保存日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'level_name' => $model->level_name,
                    'state' => MemberLevelModel::$stateText[$model->state],
                ];
                LogModel::write(
                    $this->userId,
                    MemberLogConstant::MEMBER_LEVEL_CHANGE_STATE,
                    MemberLogConstant::getText(MemberLogConstant::MEMBER_LEVEL_CHANGE_STATE),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identify_code'=> [
                            MemberLogConstant::MEMBER_LEVEL_DELETE,
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new LevelException(LevelException::CHANGE_LEVEL_STATE_FAIL, $res['message']);
        }
        
        return $this->success();
    }
    
    /**
     * 修改等级升级方式
     * @return Response
     * @throws LevelException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetType()
    {
        $updateType = RequestHelper::get('update_type');
        try {
            ShopSettings::set('member.level.update_type', $updateType);
            //记录日志
            LogModel::write(
                $this->userId,
                MemberLogConstant::MEMBER_LEVEL_UPGRADE,
                MemberLogConstant::getText(MemberLogConstant::MEMBER_LEVEL_UPGRADE),
                '0',
                [
                    'log_data' => ['update_type' => $updateType],
                    'log_primary' => [
                        '升级方式' => $updateType == 1 ? '订单完成后' : '付款后'
                    ],
                    'dirty_identify_code'=> [
                        MemberLogConstant::MEMBER_LEVEL_UPGRADE,
                    ]
                ]
            );
            
        } catch (Exception $exception) {
            throw new LevelException(LevelException::CHANGE_UPDATE_TYPE_FAIL);
        }
        return $this->success();
    }
    
    /**
     * 获取会员升级方式
     * 1 订单完成后   2 付款后
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetType()
    {
        $type = ShopSettings::get('member.level.update_type');
        
        return $this->result(['type' => $type]);
    }
    
}