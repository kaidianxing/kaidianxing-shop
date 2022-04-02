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
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\components\notice\NoticeTypeConstant;
use shopstar\constants\log\member\MemberLogConstant;
use shopstar\constants\member\MemberCreditRecordStatusConstant;
use shopstar\constants\member\MemberLogPayTypeConstant;
use shopstar\constants\member\MemberLogStatusConstant;
use shopstar\constants\member\MemberLogTypeConstant;
use shopstar\constants\MemberTypeConstant;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ExcelHelper;
use shopstar\helpers\OrderNoHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\commission\CommissionAgentModel;
use shopstar\models\commission\CommissionRelationModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberLogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberSession;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\order\OrderModel;
use shopstar\models\role\ManagerModel;
use shopstar\models\user\UserModel;
use Yii;
use yii\web\Response;

/**
 * 会员列表类
 * Class ListController
 * @package shopstar\admin\member
 */
class ListController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'allowHeaderActions' => ['index'],
        'allowPermActions' => [
            'index',
            'get-bind-setter',
            'accurate-query'
        ]
    ];

    /**
     * 会员列表
     * @return Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $export = RequestHelper::get('export', 0);
        $startTime = RequestHelper::get('start_time');
        $endTime = RequestHelper::get('end_time');
        $keyword = RequestHelper::get('keyword');
        $bindSetter = RequestHelper::get('bind_setter');
        // 判断是否是筛选操作员绑定会员的访问入口
        if ($bindSetter) {
            return $this->actionGetBindSetter();
        }
        $andWhere = [];
        // 时间
        if (!empty($startTime) && !empty($endTime)) {
            $andWhere[] = ['between', 'm.created_at', $startTime, $endTime];
        }

        // 关键词
        if (!empty($keyword)) {
            // id 搜索
            if ($keyword[0] == '`') {
                $id = substr($keyword, 1);
                $andWhere[] = ['m.id' => $id];
            } else {
                $andWhere[] = [
                    'or',
                    ['like', 'm.realname', $keyword],
                    ['like', 'm.nickname', $keyword],
                    ['like', 'm.mobile', $keyword],
                    ['like', 'm.id', $keyword],
                ];
            }
        }

        $params = [
            'searchs' => [
                ['m.level_id', 'int', 'level_id'],
                ['gm.group_id', 'int', 'group_id'],
                ['m.is_black', 'int', 'is_black'],
                ['m.source', 'int', 'source'],
            ],
            'where' => [
                'm.is_deleted' => 0
            ],
            'andWhere' => $andWhere,
            'select' => [
                'm.id',
                'm.avatar',
                'm.nickname',
                'm.realname',
                'm.mobile',
                'm.credit',
                'm.balance',
                'm.is_black',
                'm.level_id',
                'm.source',
                'm.created_at',
                'm.inviter',
                'count(o.id) order_count',
                'sum(o.pay_price) money_count'
            ],
            'alias' => 'm',
            'leftJoins' => [
                [OrderModel::tableName() . ' o', 'o.member_id=m.id and o.status > 0'],
                [MemberGroupMapModel::tableName() . ' gm', 'gm.member_id=m.id']
            ],
            'with' => [
                'groups',
                'memberCoupons' => function ($query) {
                    $query->andWhere(['order_id' => 0])->andWhere(['>', 'end_time', DateTimeHelper::now()]);
                }
            ],
            'orderBy' => [
                'm.id' => SORT_DESC
            ],
            'groupBy' => 'm.id'
        ];

        // 所有等级
        $levelList = MemberLevelModel::find()
            ->select('id, level_name')
            ->orderBy(['is_default' => SORT_DESC, 'level' => SORT_ASC])
            ->indexBy('id')
            ->get();
        // 所有标签组
        $groupList = MemberGroupModel::find()
            ->select('id, group_name')
            ->orderBy(['id' => SORT_DESC])
            ->indexBy('id')
            ->get();

        // 获取默认等级id
        $defaultLevelId = MemberLevelModel::getDefaultLevelId();

        // 获取列表
        $members = MemberModel::getColl($params, [
            'pager' => $export ? false : true,
            'callable' => function (&$row) use ($defaultLevelId, $export, $levelList) {
                // 累计金额
                if ($row['money_count'] == null) {
                    $row['money_count'] = 0;
                } else {
                    $row['money_count'] = bcadd($row['money_count'], 0, 2);
                }
                // 是否默认等级
                if ($defaultLevelId == $row['level_id']) {
                    $row['is_default_level'] = 1;
                }
                if ($row['source'] == ClientTypeConstant::CLIENT_WECHAT) {
                    $row['is_follow'] = MemberWechatModel::getMemberFollow($row['id']);
                } else {
                    $row['is_follow'] = MemberTypeConstant::MEMBER_NOT_FOLLOW;
                }
                // 获取上级 并且有分销插件
                if ($export != 1) {
                    // 获取上级
                    $parentId = CommissionRelationModel::getParentId($row['id']);
                    if (!empty($parentId)) {
                        $agent = MemberModel::find()->select('avatar, nickname')->where(['id' => $parentId])->first();
                        $row['inviter_name'] = $agent['nickname'];
                        $row['inviter_avatar'] = $agent['avatar'];
                        $row['inviter_id'] = $parentId;
                    } else {
                        // 如果没有上级  但是分销商 则是总店
                        if (CommissionAgentModel::isAgent($row['id'])) {
                            $row['inviter_name'] = '总店';
                        }
                    }
                }
                $row['is_black'] = (int)$row['is_black'];
                $row['is_black_name'] = MemberModel::$isBlack[$row['is_black']];
                $row['level_name'] = $levelList[$row['level_id']]['level_name'];
                $row['group_name'] = implode(',', array_column($row['groups'], 'group_name'));
                $row['source_name'] = ClientTypeConstant::getText($row['source']);
                $row['coupon_count'] = count($row['memberCoupons']);
                $row['is_follow_name'] = MemberModel::$isFollow[$row['is_follow']];
                unset($row['memberCoupons']);
            }
        ]);

        // 导出
        if ($export) {
            try {

                $columns = MemberModel::$memberColumns;

                ExcelHelper::export($members['list'], $columns, '会员数据导出');
            } catch (\Throwable $exception) {
                throw new MemberException(MemberException::MEMBER_EXPORT_FAIL);
            }
            die;
        }

        $data = $members;
        $data['levels'] = array_values($levelList);
        $data['groups'] = array_values($groupList);

        return $this->result($data);
    }

    /**
     * 修改/批量修改等级
     * @return Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeLevel(): Response
    {
        $id = RequestHelper::postArray('id');
        // 获取等级名称
        $level = MemberLevelModel::findOne(['id' => RequestHelper::postInt('level_id')]);

        $res = MemberModel::easyProperty('level_id', [
            'andWhere' => [],
            'beforeAllAction' => function () use ($id) {
                // 检验用户是否被删除
                if (!MemberModel::checkDeleted($id)) {
                    throw new MemberException(MemberException::MEMBER_DELETED_NO_CHANGE_LEVEL);
                }
            },
            'afterAction' => function ($model) use ($level) {

                // 记录日志
                LogModel::write(
                    $this->userId,
                    MemberLogConstant::MEMBER_CHANGE_LEVEL,
                    MemberLogConstant::getText(MemberLogConstant::MEMBER_CHANGE_LEVEL),
                    $model->id,
                    [
                        'log_data' => [
                            'id' => $model->id,
                            'nickname' => $model->nickname,
                            'level_id' => $model->level_id,
                        ],
                        'log_primary' => [
                            'id' => $model->id,
                            '昵称' => $model->nickname,
                            '等级' => $level->level_name,
                        ],
                        'dirty_identify_code' => [
                            MemberLogConstant::MEMBER_CHANGE_LEVEL
                        ]
                    ]
                );
            }
        ]);

        if (is_error($res)) {
            throw new MemberException(MemberException::CHANGE_LEVEL_FAIL, $res['message']);
        }
        return $this->success();
    }

    /**
     * 修改/批量修改标签组
     * @return Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeGroup(): Response
    {
        $id = RequestHelper::postArray('id');

        $groupIds = RequestHelper::postArray('group_ids');
        if (empty($id)) {
            throw new MemberException(MemberException::CHANGE_GROUP_PARAM_ERROR);
        }
        // 检验用户是否删除
        if (!MemberModel::checkDeleted($id)) {
            throw new MemberException(MemberException::MEMBER_DELETED_NO_CHANGE_GROUP);
        }
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            MemberGroupMapModel::updateMap($id, $groupIds, $this->userId);
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new  MemberException(MemberException::CHANGE_GROUP_FAIL);
        }
        return $this->success();
    }

    /**
     * 批量设置/取消黑名单
     * @return Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetBlack(): Response
    {
        $id = RequestHelper::postArray('id');

        $res = MemberModel::easyProperty('is_black', [
            'andWhere' => [],
            'beforeAllAction' => function () use ($id) {
                // 检验用户是否被删除
                if (!MemberModel::checkDeleted($id)) {
                    return error('用户已被删除，无法操作');
                }
            },
            'afterAction' => function ($model) {
                // 删除用户session
                MemberSession::deleteMemberSession($model->id);
                // 记录日志
                LogModel::write(
                    $this->userId,
                    MemberLogConstant::MEMBER_SET_BLACK,
                    MemberLogConstant::getText(MemberLogConstant::MEMBER_SET_BLACK),
                    $model->id,
                    [
                        'log_data' => [
                            'id' => $model->id,
                            'nickname' => $model->nickname,
                            'is_black' => $model->is_black,
                        ],
                        'log_primary' => [
                            'id' => $model->id,
                            '昵称' => $model->nickname,
                            '黑名单' => $model->is_black == 1 ? '加入黑名单' : '移除黑名单',
                        ],
                        'dirty_identify_code' => [
                            MemberLogConstant::MEMBER_SET_BLACK
                        ]
                    ]
                );
            },
        ]);

        if (is_error($res)) {
            throw new MemberException(MemberException::CHANGE_BLACK_FAIL, $res['message']);
        }

        return $this->success();
    }

    /**
     * 修改积分/余额
     * @return Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRecharge(): Response
    {
        $post = RequestHelper::post();
        $id = $post['id'];
        $type = $post['type'];
        $changeType = $post['change_type'];
        $num = (float)$post['num'];
        $remark = $post['remark'];
        $password = $post['password'];

        // 充值变化类型
        if ($changeType === '') {
            throw new MemberException(MemberException::RECHARGE_CHANGE_TYPE_NOT_EMPTY);
        }
        // 充值类型
        if (empty($type)) {
            throw new MemberException(MemberException::RECHARGE_TYPE_NOT_EMPTY);
        }
        // 充值金额
        if ($num == 0 && $changeType != MemberTypeConstant::RECHARGE_CHANGE_TYPE_FIXED) {
            throw new MemberException(MemberException::RECHARGE_NUM_NOT_EMPTY);
        }
        // 备注
        if (empty($remark)) {
            throw new MemberException(MemberException::RECHARGE_REMARK_NOT_EMPTY);
        }
        // 密码
        if (empty($password)) {
            throw new MemberException(MemberException::MEMBER_MANAGE_PASSWORD_NOT_EMPTY);
        }

        //检测超管密码
        $result = UserModel::checkUserPassword($this->userId, $password);

        //判断是否错误
        if (!$result) {
            return $this->error('密码错误');
        }

        // 校验用户是否删除
        if (!MemberModel::checkDeleted($id)) {
            throw new MemberException(MemberException::MEMBER_DELETED_NO_RECHARGE);
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $recordType = $type == 'credit' ? MemberCreditRecordStatusConstant::CREDIT_STATUS_BACKGROUND : MemberCreditRecordStatusConstant::BALANCE_STATUS_BACKGROUND;
            $member = MemberModel::updateCredit($id, $num, (int)$this->userId, $type, $changeType,
                $remark, $recordType);
            if (is_error($member)) {
                throw new MemberException(MemberException::UPDATE_CREDIT_FAIL, $member['message']);
            }

            // 余额变动
            if ($type == 'balance') {
                // 生成用户余额操作日志
                $logSn = OrderNoHelper::getOrderNo('BG', $this->clientType);
                MemberLogModel::insertLog($changeType == 2 ? -$num : $num, MemberLogPayTypeConstant::ORDER_PAY_TYPE_BACKGROUND,
                    $id, $logSn, MemberLogTypeConstant::ORDER_TYPE_RECHARGE, $remark,
                    MemberLogStatusConstant::ORDER_STATUS_SUCCESS, ClientTypeConstant::MANAGE_PC);

            }

            // 发送通知
            if ($type == 'balance') {
                $result = NoticeComponent::getInstance(NoticeTypeConstant::BUYER_PAY_RECHARGE, [
                    'member_nickname' => $member['nickname'],
                    'nickname' => $member['nickname'],
                    'recharge_price' => $changeType == 2 ? -$num : $num,
                    'recharge_method' => '后台充值',
                    'balance_change_reason' => '余额充值',
                    'recharge_time' => DateTimeHelper::now(),
                    'recharge_pay_method' => '后台支付',
                    'member_balance' => $member['balance'],
                    'change_time' => DateTimeHelper::now(),// 变动时间
                    'change_reason' => '后台充值',
                ]);
            } else {
                // 积分变动
                $result = NoticeComponent::getInstance(NoticeTypeConstant::BUYER_PAY_CREDIT, [
                    'member_nickname' => $member['nickname'],
                    'nickname' => $member['nickname'],
                    'recharge_price' => $changeType == 2 ? -$num : $num,
                    'recharge_method' => '',
                    'recharge_time' => DateTimeHelper::now(),
                    'recharge_pay_method' => '后台支付',
                    'member_credit' => $member['credit'],
                    'change_time' => DateTimeHelper::now(),// 变动时间
                    'change_reason' => '后台充值',
                ]);
            }


            if (!is_error($result)) {
                $result->sendMessage($id);
            }


            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage());
        }

        return $this->success();
    }

    /**
     * 筛选操作员绑定会员
     * @return array|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetBindSetter()
    {
        $keyword = RequestHelper::get('keyword');
        $bindSetter = RequestHelper::get('bind_setter');
        $andWhere = [];

        // 关键词
        if (!empty($keyword)) {
            // id 搜索
            if ($keyword[0] == '`') {
                $id = substr($keyword, 1);
                $andWhere[] = ['m.id' => $id];
            } else {
                $andWhere[] = [
                    'or',
                    ['like', 'm.realname', $keyword],
                    ['like', 'm.nickname', $keyword],
                    ['like', 'm.mobile', $keyword],
                    ['like', 'm.id', $keyword],
                ];
            }
        }

        $params = [
            'searchs' => [
                ['m.level_id', 'int', 'level_id'],
                ['gm.group_id', 'int', 'group_id'],
                ['m.is_black', 'int', 'is_black'],
                ['m.source', 'int', 'source'],
            ],
            'where' => [
                'm.is_deleted' => 0
            ],
            'andWhere' => $andWhere,
            'select' => [
                'm.id',
                'm.avatar',
                'm.nickname',
                'm.realname',
                'm.mobile',
                'm.credit',
                'm.balance',
                'm.is_black',
                'm.level_id',
                'm.source',
                'm.created_at',
                'm.inviter',
                'count(o.id) order_count',
                'sum(o.pay_price) money_count',
                'ma.id man_id',
            ],
            'alias' => 'm',
            'leftJoins' => [
                [OrderModel::tableName() . ' o', 'o.member_id=m.id and o.status > 0'],
                [MemberGroupMapModel::tableName() . ' gm', 'gm.member_id=m.id'],
                [ManagerModel::tableName() . ' ma', 'ma.member_id=m.id']
            ],
            'with' => [
                'groups',
                'memberCoupons' => function ($query) {
                    $query->andWhere(['order_id' => 0])->andWhere(['>', 'end_time', DateTimeHelper::now()]);
                }
            ],
            'orderBy' => [
                'm.id' => SORT_DESC
            ],
            'groupBy' => 'm.id'
        ];

        // 所有等级
        $levelList = MemberLevelModel::find()
            ->select('id, level_name')
            ->orderBy(['is_default' => SORT_DESC, 'level' => SORT_ASC])
            ->indexBy('id')
            ->get();
        // 所有标签组
        $groupList = MemberGroupModel::find()
            ->select('id, group_name')
            ->orderBy(['id' => SORT_DESC])
            ->indexBy('id')
            ->get();

        // 获取默认等级id
        $defaultLevelId = MemberLevelModel::getDefaultLevelId();

        // 获取列表
        $members = MemberModel::getColl($params, [
            'callable' => function (&$row) use ($defaultLevelId, $levelList, $bindSetter) {
                // 累计金额
                if ($row['money_count'] == null) {
                    $row['money_count'] = 0;
                } else {
                    $row['money_count'] = bcadd($row['money_count'], 0, 2);
                }
                // 是否默认等级
                if ($defaultLevelId == $row['level_id']) {
                    $row['is_default_level'] = 1;
                }
                if ($row['source'] == ClientTypeConstant::CLIENT_WECHAT) {
                    $row['is_follow'] = MemberWechatModel::getMemberFollow($row['id']);
                } else {
                    $row['is_follow'] = MemberTypeConstant::MEMBER_NOT_FOLLOW;
                }
                $row['is_black'] = (int)$row['is_black'];
                $row['is_black_name'] = MemberModel::$isBlack[$row['is_black']];
                $row['level_name'] = $levelList[$row['level_id']]['level_name'];
                $row['group_name'] = implode(',', array_column($row['groups'], 'group_name'));
                $row['source_name'] = ClientTypeConstant::getText($row['source']);
                $row['coupon_count'] = count($row['memberCoupons']);
                $row['is_follow_name'] = MemberModel::$isFollow[$row['is_follow']];
                unset($row['memberCoupons']);
                $row['is_use_' . $bindSetter] = $row['man_id'] ? 1 : 0;
            },
        ]);
        $data = $members;
        $data['levels'] = array_values($levelList);
        $data['groups'] = array_values($groupList);

        return $this->result($data);
    }

    /**
     * 查询会员
     * @return array|Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAccurateQuery()
    {
        $mobile = RequestHelper::getInt('mobile');
        if (empty($mobile)) {
            throw new MemberException(MemberException::MEMBER_CHANGE_MOBILE_ERROR);
        }
        $info = MemberModel::getMemberInfoToMobile($mobile);

        return $this->result(count($info) ? ['data' => $info] : []);
    }

}
