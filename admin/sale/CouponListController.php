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

namespace shopstar\admin\sale;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\coupon\CouponConstant;
use shopstar\constants\log\sale\CouponLogConstant;
use shopstar\exceptions\sale\CouponException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\helpers\ShopUrlHelper;
use shopstar\helpers\ValueHelper;
use shopstar\models\goods\category\GoodsCategoryModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\sale\CouponMapModel;
use shopstar\models\sale\CouponMemberModel;
use shopstar\models\sale\CouponModel;
use shopstar\models\sale\CouponRuleModel;
use shopstar\services\sale\CouponService;
use yii\helpers\StringHelper;

/**
 * 优惠券列表
 * Class CouponListController
 * @package shopstar\admin\sale
 */
class CouponListController extends KdxAdminApiController
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
     * 优惠券列表
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex(): \yii\web\Response
    {
        $get = RequestHelper::get();
        $by = RequestHelper::get('by', 'desc');
        $sort = RequestHelper::get('sort', 'sort');
        $pager = RequestHelper::getInt('pager', 1);
        $isValidity = RequestHelper::get('is_validity');

        $where = [];

        // 领取方式
        switch ($get['pick_way']) {
            case 1: // 免费
                $where[] = ["coupon.is_free" => CouponConstant::IS_FREE];
                break;
            case 2: // 付费
                $where[] = ["coupon.is_free" => CouponConstant::IS_NOT_FREE, "coupon.pick_type" => CouponConstant::COUPON_PICK_TYPE_CENTER];
                break;
            case 3: // 链接
                $where[] = ["coupon.pick_type" => CouponConstant::COUPON_PICK_TYPE_LINK];
                break;
            case 4: // 活动领取
                $where[] = ["coupon.pick_type" => CouponConstant::COUPON_PICK_TYPE_ACTIVITY];
                break;
        }

        // 创建时间
        if (!empty($get['start_time']) && !empty($get['end_time'])) {
            $where[] = ['between', 'coupon.created_at', $get['start_time'], $get['end_time']];
        }

        if (!empty($get['id'])) {
            $where[] = ['coupon.id' => StringHelper::explode($get['id'], ',')];
        }

        // 有效期 仅获取未过期的
        if ($isValidity == 1) {
            $where[] = [
                'or',
                ['coupon.time_limit' => 1],
                [
                    'and',
                    ['coupon.time_limit' => 0],
                    ['>', 'coupon.end_time', DateTimeHelper::now()]
                ]
            ];
        }

        // 查询 发放状态
        if (!empty($get['is_state']) && $get['is_state'] == 1) {
            $where[] = ["coupon.state" => 1];
        }

        //排序
        if ($sort == 'use_num') {
            $orderBy[$sort] = $by == 'asc' ? SORT_ASC : SORT_DESC;
        } else {
            $orderBy['coupon.' . $sort] = $by == 'asc' ? SORT_ASC : SORT_DESC;
        }
        $orderBy['coupon.id'] = SORT_DESC;
        $params = [
            'searchs' => [
                ['coupon.coupon_name', 'like', 'keyword'],
                ['coupon.coupon_sale_type', 'int', 'coupon_sale_type'],
            ],
            'andWhere' => $where,
            'alias' => 'coupon',
            'leftJoin' => [CouponMemberModel::tableName() . ' member_coupon', 'member_coupon.coupon_id=coupon.id and member_coupon.order_id>0'],
            'orderBy' => $orderBy,
            'groupBy' => 'coupon.id',
            'select' => [
                'coupon.*',
                '(count(member_coupon.id)/coupon.get_total) AS use_num',
            ],
        ];

        $list = CouponModel::getColl($params, [
            'pager' => (bool)$pager,
            'callable' => function (&$row) {
                // 优惠类型
                // 如果是立减类型
                if ($row['coupon_sale_type'] == CouponConstant::COUPON_SALE_TYPE_SUB) {
                    $row['content'] = '满' . ValueHelper::delZero($row['enough']) . '减' . ValueHelper::delZero($row['discount_price']);
                } else {
                    // 打折类型
                    $row['content'] = '满' . ValueHelper::delZero($row['enough']) . '享' . ValueHelper::delZero($row['discount_price']) . '折';
                }
                // 剩余数量
                if ($row['stock_type'] == CouponConstant::COUPON_STOCK_TYPE_LIMIT) {
                    $row['surplus'] = $row['stock'] - $row['get_total'];
                }

                // 领取方式
                if ($row['pick_type'] == CouponConstant::COUPON_PICK_TYPE_CENTER && $row['is_free'] == CouponConstant::IS_FREE) {
                    $row['pick_way'] = 1; // 免费
                } else if ($row['pick_type'] == CouponConstant::COUPON_PICK_TYPE_CENTER && $row['is_free'] == CouponConstant::IS_NOT_FREE) {
                    $row['pick_way'] = 2; // 付费
                } else if ($row['pick_type'] == CouponConstant::COUPON_PICK_TYPE_LINK) {
                    $row['pick_way'] = 3; // 链接
                } else if ($row['pick_type'] == CouponConstant::COUPON_PICK_TYPE_ACTIVITY) {
                    $row['pick_way'] = 4; // 活动
                }

                $row['wap_url'] = ShopUrlHelper::wap('/kdxMember/coupon/detail/index', [
                    'id' => $row['id']
                ], true);
            }
        ]);


        return $this->result($list);
    }

    /**
     * 优惠券详情
     * @return \yii\web\Response
     * @throws CouponException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDetail(): \yii\web\Response
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            throw new CouponException(CouponException::DETAIL_PARAMS_ERROR);
        }
        // 优惠券
        $data = CouponModel::find()->with(['rules', 'map'])
            ->where(['id' => $id])
            ->asArray()->one();

        $memberLevel = [];
        $memberLevel = MemberLevelModel::find()->get();

        // 等级限制
        $rules = [];
        if (!empty($data['rules'])) {
            foreach ($data['rules'] as $rule) {
                if (!empty($rule['commission_level'])) {
                    $rules['commission_level'][] = $rule['commission_level'];
                }
                if (!empty($rule['member_level'])) {
                    $rules['member_level'][] = $rule['member_level'];
                }
            }
        }
        unset($data['rules']);

        // 商品列表
        $goodsList = [];
        // 分类列表
        $cateList = [];
        // 限制商品
        if ($data['goods_limit'] == CouponConstant::COUPON_GOODS_LIMIT_ALLOW_GOODS || $data['goods_limit'] == CouponConstant::COUPON_GOODS_LIMIT_NOT_ALLOW_GOODS) {
            // 取商品id
            $goodsIds = array_column($data['map'], 'goods_cate_id');
            $goodsList = GoodsModel::find()->select('id, title, thumb, price, stock, type')->where(['id' => $goodsIds])->get();
        } else if ($data['goods_limit'] == CouponConstant::COUPON_GOODS_LIMIT_ALLOW_GOODS_CATE) {
            // 限制商品分类
            $cateIds = array_column($data['map'], 'goods_cate_id');
            $cateList = GoodsCategoryModel::find()->select('id, name')->where(['id' => $cateIds])->get();
        }
        return $this->success([
            'item' => $data,
            'rules' => $rules,
            'member_level' => $memberLevel,
            'goods_list' => $goodsList,
            'cate_list' => $cateList
        ]);
    }

    /**
     * 新增优惠券
     * @return \yii\web\Response
     * @throws CouponException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd(): \yii\web\Response
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();
        // 保存
        $coupon = new CouponService();
        $res = $coupon->saveCoupon($this->userId);
        if (is_error($res)) {
            $transaction->rollBack();
            throw new CouponException(CouponException::ADD_COUPON_SAVE_FAIL, $res['message']);
        }
        $transaction->commit();

        return $this->success();
    }

    /**
     * 编辑优惠券
     * @return \yii\web\Response
     * @throws CouponException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit(): \yii\web\Response
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new CouponException(CouponException::EDIT_PARAMS_ERROR);
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $coupon = new CouponService();
            $res = $coupon->saveCoupon($this->userId, $id);
            if (is_error($res)) {
                throw new CouponException(CouponException::EDIT_COUPON_SAVE_FAIL, $res['message']);
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }
        return $this->success();
    }

    /**
     * 删除优惠券 无批量删除
     * @return \yii\web\Response
     * @throws CouponException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete(): \yii\web\Response
    {
        $id = RequestHelper::get('id');
        // 删除类型  1仅删除  2删除用户已领取的
        $type = RequestHelper::get('type');
        if (empty($id)) {
            throw new CouponException(CouponException::DELETE_PARAMS_ERROR);
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {

            $res = CouponModel::easyDelete([
                'isPost' => false,
                'afterDelete' => function ($model) use ($type) {
                    // 删除规则
                    CouponRuleModel::deleteAll(['coupon_id' => $model->id]);
                    CouponMapModel::deleteAll(['coupon_id' => $model->id]);
                    // 删除用户已领取的 未使用的
                    if ($type == 2) {
                        CouponMemberModel::deleteAll(['coupon_id' => $model->id, 'order_id' => 0]);
                    }
                    if ($model->coupon_sale_type == CouponConstant::COUPON_SALE_TYPE_SUB) {
                        $content = '满' . ValueHelper::delZero($model->enough) . '减' . ValueHelper::delZero($model->discount_price);
                    } else {
                        // 打折类型
                        $content = '满' . ValueHelper::delZero($model->enough) . '享' . ValueHelper::delZero($model->discount_price) . '折';
                    }

                    // 日志
                    $logPrimaryData = [
                        'id' => $model->id,
                        'coupon_name' => $model->coupon_name,
                        'content' => $content,
                        'delete_type' => $type == 1 ? '会员已领取的优惠券可正常使用' : '一并删除',
                    ];
                    LogModel::write(
                        $this->userId,
                        CouponLogConstant::COUPON_DELETE,
                        CouponLogConstant::getText(CouponLogConstant::COUPON_DELETE),
                        $model->id,
                        [
                            'log_data' => $model->attributes,
                            'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                            'dirty_identity_code' => [
                                CouponLogConstant::COUPON_DELETE
                            ]
                        ]
                    );
                }
            ]);

            if (is_error($res)) {
                throw new CouponException(CouponException::COUPON_DELETE_FAIL);
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            return $this->result($exception->getMessage(), $exception->getCode());
        }
        return $this->success();
    }

    /**
     * 修改优惠券发放状态
     * @throws CouponException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionChangeState()
    {
        $res = CouponModel::easySwitch('state', [
            'isPost' => false,
            'afterAction' => function ($model) {

                // 日志
                $logPrimaryData = [
                    'id' => $model->id,
                    'coupon_name' => $model->coupon_name,
                    'state' => $model->state ? '开启' : '关闭',
                ];
                LogModel::write(
                    $this->userId,
                    CouponLogConstant::COUPON_CHANGE_STATE,
                    CouponLogConstant::getText(CouponLogConstant::COUPON_CHANGE_STATE),
                    $model->id,
                    [
                        'log_data' => $model->attributes,
                        'log_primary' => $model->getLogAttributeRemark($logPrimaryData),
                        'dirty_identity_code' => [
                            CouponLogConstant::COUPON_CHANGE_STATE,
                        ],
                    ]
                );
            }
        ]);

        if (is_error($res)) {
            throw new CouponException(CouponException::CHANGE_COUPON_STATE_FAIL, $res['message']);
        }
        return $this->success();
    }

}