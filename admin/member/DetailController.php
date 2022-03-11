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
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\StringHelper;
use shopstar\models\commission\CommissionRelationLogModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\models\order\OrderModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\user\UserModel;
use shopstar\services\commission\CommissionAgentService;
use shopstar\services\member\MemberService;
use yii\web\Response;

/**
 * 会员详情类
 * Class DetailController
 * @package app\controllers\manage\member
 */
class DetailController extends KdxAdminApiController
{
    public $configActions = [
        'allowActions' => [
            'get-commission-relation-log',
        ]
    ];

    /**
     * 会员详情
     * @return Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            throw new MemberException(MemberException::DETAIL_PARAM_ERROR);
        }

        $member = MemberModel::getMemberDetail($id);
        if (is_error($member)) {
            throw new MemberException(MemberException::DETAIL_MEMBER_NOT_EXISTS);
        }

        // 订单信息
        $orderInfo = OrderModel::getMemberOrder($id);

        //优惠券条件
        $couponWhere = [
            'and',
            ['member_id' => $id],
            ['order_id' => 0],
            ['status' => 0],
            ['>', 'end_time', DateTimeHelper::now()],
        ];

        // 优惠券数量
        $member['coupon_count'] = CouponMemberModel::find()
            ->where($couponWhere)->count();

        $data = [
            'member' => $member,
            'order_info' => $orderInfo,
        ];
        // 分销信息
        $data['commission_info'] = CommissionAgentService::getCommissionInfo($id, $member['inviter']);

        // 新增open_id返回
        $data['member']['open_id'] = MemberWechatModel::getOpenId($id) ?? MemberWxappModel::getOpenId($id);

        return $this->result($data);
    }

    /**
     * 修改密码
     * @return Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangePassword()
    {
        $memberId = RequestHelper::postInt('id');
        $password = RequestHelper::post('password');
        $salt = '';
        if (empty($memberId)) {
            throw new MemberException(MemberException::DETAIL_CHANGE_PASSWORD_PARAM_ERROR);
        }
        // 判断用户是否删除
        if (!MemberModel::checkDeleted($memberId)) {
            throw new MemberException(MemberException::MEMBER_DELETED_NO_CHANGE_PASSWORD);
        }

        // 如果密码不为空 加密密码
        if (!empty(trim($password))) {
            $salt = StringHelper::random(16);
            $password = md5($password . $salt);
        }
        try {
            $member = MemberModel::findOne(['id' => $memberId]);
            $member->salt = $salt;
            $member->password = $password;
            $member->save();
            // 记录日志
            LogModel::write(
                $this->userId,
                MemberLogConstant::MEMBER_CHANGE_PASSWORD,
                MemberLogConstant::getText(MemberLogConstant::MEMBER_CHANGE_PASSWORD),
                $memberId,
                [
                    'log_data' => [
                        'id' => $memberId,
                        'nickname' => $member->nickname
                    ],
                    'log_primary' => [
                        'id' => $memberId,
                        '用户昵称' => $member->nickname,
                    ]
                ]
            );
        } catch (\Throwable $exception) {
            throw new MemberException(MemberException::DETAIL_CHANGE_PASSWORD_FAIL);
        }
        return $this->success();
    }

    /**
     * 修改备注
     * @return array|int[]|Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司.
     */
    public function actionChangeRemark()
    {
        $memberId = RequestHelper::postInt('id');
        $remark = RequestHelper::post('remark');

        $res = MemberModel::easyProperty('remark', [
            'beforeAllAction' => function () use ($memberId) {
                // 检验用户是否被删除
                if (!MemberModel::checkDeleted($memberId)) {
                    return error('用户已被删除，无法操作');
                }
            },
            'afterAction' => function ($model) {
                // 日志
                LogModel::write(
                    $this->userId,
                    MemberLogConstant::MEMBER_CHANGE_REMARK,
                    MemberLogConstant::getText(MemberLogConstant::MEMBER_CHANGE_REMARK),
                    $model->id,
                    [
                        'log_data' => [
                            'id' => $model->id,
                            'nickname' => $model->nickname,
                            'remark' => $model->remark,
                        ],
                        'log_primary' => [
                            'id' => $model->id,
                            '用户昵称' => $model->nickname,
                            '备注' => $model->remark,
                        ],
                        'dirty_identify_code' => [
                            MemberLogConstant::MEMBER_CHANGE_REMARK
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new MemberException(MemberException::DETAIL_CHANGE_MOBILE_REMARK, $res['message']);
        }

        return $this->result();
    }

    /**
     * 修改手机号
     * @return Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeMobile()
    {
        $memberId = RequestHelper::postInt('id');
        $mobile = RequestHelper::post('mobile');

        $res = MemberModel::easyProperty('mobile', [
            'beforeAllAction' => function () use ($memberId, $mobile) {
                // 检验用户是否被删除
                if (!MemberModel::checkDeleted($memberId)) {
                    return error('用户已被删除，无法操作');
                }
                $res = MemberModel::checkMobile($memberId, $mobile);
                if (is_error($res)) {
                    return $res;
                }
            },
            'afterAction' => function ($model) {
                // 日志
                LogModel::write(
                    $this->userId,
                    MemberLogConstant::MEMBER_CHANGE_MOBILE,
                    MemberLogConstant::getText(MemberLogConstant::MEMBER_CHANGE_MOBILE),
                    $model->id,
                    [
                        'log_data' => [
                            'id' => $model->id,
                            'nickname' => $model->nickname,
                            'mobile' => $model->mobile,
                        ],
                        'log_primary' => [
                            'id' => $model->id,
                            '用户昵称' => $model->nickname,
                            '手机号' => $model->mobile,
                        ],
                        'dirty_identify_code' => [
                            MemberLogConstant::MEMBER_CHANGE_MOBILE
                        ]
                    ]
                );
            }
        ]);
        if (is_error($res)) {
            throw new MemberException(MemberException::DETAIL_CHANGE_MOBILE_FAIL, $res['message']);
        }

        return $this->success();
    }

    /**
     * 删除会员
     * @return Response
     * @throws MemberException
     * @throws \Throwable
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::post('id');
        $password = RequestHelper::post('manage_password');
        if (empty($id) || empty($password)) {
            throw new MemberException(MemberException::DETAIL_DELETE_MEMBER_PARAM_ERROR);
        }

        //检测超管密码
        $result = UserModel::checkUserPassword($this->userId, $password);

        //判断是否错误
        if (!$result) {
            return $this->error('服务商密码错误');
        }


        $transaction = \Yii::$app->db->beginTransaction();
        $res = MemberService::delete($id, $this->userId);
        if (is_error($res)) {
            $transaction->rollBack();
            throw new MemberException(MemberException::DETAIL_DELETE_MEMBER_ERROR, $res['message']);
        }
        $transaction->commit();

        return $this->success();
    }

    /**
     * @desc 会员分销商关系日志
     * @author nizengchao
     */
    public function actionGetCommissionRelationLog()
    {

        $list = CommissionRelationLogModel::getMemberLog();
        return $this->result($list);
    }
}
