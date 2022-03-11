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

namespace shopstar\admin\poster;

use shopstar\helpers\RequestHelper;
use shopstar\models\log\LogModel;
use shopstar\constants\poster\PosterAccessTypeConstant;
use shopstar\constants\poster\PosterCashTypeConstant;
use shopstar\constants\poster\PosterLogConstant;
use shopstar\constants\poster\PosterPushTypeConstant;
use shopstar\constants\poster\PosterTypeConstant;
use shopstar\constants\poster\PosterVisitPageConstant;
use shopstar\exceptions\poster\PosterException;
use shopstar\models\poster\PosterModel;
use shopstar\bases\KdxAdminApiController;

/**
 * Class ListController
 * @package apps\poster\manage
 */
class ListController extends KdxAdminApiController
{
    /**
     * 海报列表
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $params = [
            'searchs' => [
                ['type', 'int', 'type'],
                [['name'], 'like', 'keyword']
            ],
            'where' => [
                'is_deleted' => 0
            ],
            'select' => [
                'id',
                'name',
                'type',
                'thumb',
                'keyword',
                'scans',
                'follows',
                'status'
            ],
            'orderBy' => [
                'created_at' => SORT_DESC
            ]

        ];

        $list = PosterModel::getColl($params, [
            'callable' => function (&$row) {
                $row['type_text'] = PosterTypeConstant::getText($row['type']);
            }
        ]);

        return $this->result(['data' => $list]);
    }

    /**
     * 应用中的海报列表
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUsing()
    {
        $params = [
            'where' => [
                'status' => 1,
                'is_deleted' => 0
            ],
            'select' => [
                'id',
                'name',
                'type',
                'thumb',
                'keyword',
                'updated_at'
            ],
            'orderBy' => [
                'type' => SORT_ASC,
                'created_at' => SORT_DESC
            ]
        ];

        $list = PosterModel::getColl($params, [
            'callable' => function (&$row) {
                $row['type_text'] = PosterTypeConstant::getText($row['type']);
            }
        ]);

        return $this->result(['data' => $list]);
    }


    /**
     * 禁用海报
     * @return array|\yii\web\Response
     * @throws PosterException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionForbidden()
    {
        // 获取海报ID
        $posterId = RequestHelper::postInt('id');

        $poster = PosterModel::findOne(['id' => $posterId]);

        if (empty($poster)) {
            throw new PosterException(PosterException::POSTER_LIST_FORBIDDEN_RECORD_EMPTY);
        }

        // 修改海报状态
        $poster->status = 0;

        $poster->save();

        //  日志
        $logPrimary = [
            'id' => $poster->id,
            '海报类型' => PosterTypeConstant::getText($poster->type),
            '海报名称' => $poster->name,
            '模板' => $poster->template_id,
            '访问页面' => PosterVisitPageConstant::getText($poster->visit_page)
        ];

        LogModel::write(
            $this->userId,
            PosterLogConstant::POSTER_FORBIDDEN,
            PosterLogConstant::getText(PosterLogConstant::POSTER_FORBIDDEN),
            $poster->id,
            [
                'log_data' => $poster->attributes,
                'log_primary' => $logPrimary
            ]
        );

        return $this->result('禁用成功');
    }

    /**
     * 激活海报
     * @return array|\yii\web\Response
     * @throws PosterException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionActive()
    {
        // 获取海报ID
        $posterId = RequestHelper::postInt('id');

        $poster = PosterModel::findOne(['id' => $posterId]);

        if (empty($poster)) {
            throw new PosterException(PosterException::POSTER_LIST_ACTIVE_RECORD_EMPTY);
        }

        // 修改海报状态
        $poster->status = 1;

        // 如果启用，处理其他页面的关闭
        PosterModel::updateStatus($posterId, $poster->type);

        $poster->save();

        //  日志
        $logPrimary = [
            'id' => $poster->id,
            '海报类型' => PosterTypeConstant::getText($poster->type),
            '海报名称' => $poster->name,
            '模板' => $poster->template_id,
            '访问页面' => PosterVisitPageConstant::getText($poster->visit_page)
        ];

        LogModel::write(
            $this->userId,
            PosterLogConstant::POSTER_ACTIVE,
            PosterLogConstant::getText(PosterLogConstant::POSTER_ACTIVE),
            $poster->id,
            [
                'log_data' => $poster->attributes,
                'log_primary' => $logPrimary
            ]
        );

        return $this->result('启用成功');
    }

    /**
     * 删除海报
     * @return array|\yii\web\Response
     * @throws PosterException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        // 获取海报ID
        $posterId = RequestHelper::postInt('id');

        $poster = PosterModel::findOne(['id' => $posterId]);

        if (empty($poster)) {
            throw new PosterException(PosterException::POSTER_LIST_DELETE_RECORD_EMPTY);
        }

        $result = PosterModel::deletePoster($poster);

        if (is_error($result)) {
            return $this->result($result);
        }

        //  日志
        $logPrimary = [
            'id' => $poster->id,
            '海报类型' => PosterTypeConstant::getText($poster->type),
            '海报名称' => $poster->name,
            '模板' => $poster->template_id,
            '访问页面' => PosterVisitPageConstant::getText($poster->visit_page)
        ];

        LogModel::write(
            $this->userId,
            PosterLogConstant::POSTER_DELETE,
            PosterLogConstant::getText(PosterLogConstant::POSTER_DELETE),
            $poster->id,
            [
                'log_data' => $poster->attributes,
                'log_primary' => $logPrimary
            ]
        );

        return $this->result('删除成功');
    }

    /**
     * 新增海报
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $params = [];

        // 通用参数校验
        $check = $this->commonParamsCheck($params);

        if (is_error($check)) {
            return $this->result($check);
        }

        $result = PosterModel::getAddResult($params);

        if (is_error($result)) {
            return $this->result($result);
        }

        // 日志
        $logPrimary = [
            'id' => $result->id,
            '海报类型' => PosterTypeConstant::getText($result->type),
            '海报名称' => $result->name,
            '模板' => $result->template_id,
            '访问页面' => PosterVisitPageConstant::getText($result->visit_page)
        ];
        if ($result->type == PosterTypeConstant::POSTER_TYPE_ATTENTION) {
            $logPrimary['海报生效时间'] = $result->expire_start_time . '~' . $result->expire_end_time;
            $logPrimary['海报有效时长'] = $result->expire_time;
            $logPrimary['获取对象'] = PosterAccessTypeConstant::getText($result->access_type);
            $logPrimary['推送方式'] = PosterPushTypeConstant::getText($params['push']['type']);
            $logPrimary['推送标题'] = $params['push']['title'];
            $logPrimary['推送描述'] = $params['push']['description'];
            $logPrimary['推送链接'] = $params['push']['url'];
            $logPrimary['关注奖励是否开启'] = $params['award']['status'] ? '开启' : '关闭';
            $logPrimary['推荐人积分'] = $params['award']['rec_credit_enable'] ? '开启' : '关闭';
            $logPrimary['推荐人现金'] = $params['award']['rec_cash_enable'] ? '开启' : '关闭';
            $logPrimary['推荐人优惠券'] = $params['award']['rec_coupon_enable'] ? '开启' : '关闭';
            $logPrimary['推荐人获得积分'] = $params['award']['rec_credit'];
            $logPrimary['推荐人积分每月积分奖励上限'] = $params['award']['rec_credit_limit'];
            $logPrimary['推荐人获得现金'] = $params['award']['rec_cash'];
            $logPrimary['推荐人现金每月奖励上限'] = $params['award']['rec_cash_limit'];
            $logPrimary['推荐人获得现金类型'] = PosterCashTypeConstant::getText($params['award']['rec_cash_type']);
            $logPrimary['推荐人获得优惠券'] = $params['award']['rec_coupon'];
            $logPrimary['推荐人优惠券每月最多发放数量'] = $params['award']['rec_coupon_limit'];
            $logPrimary['关注者积分'] = $params['award']['sub_credit_enable'] ? '开启' : '关闭';
            $logPrimary['关注者现金'] = $params['award']['sub_cash_enable'] ? '开启' : '关闭';
            $logPrimary['关注者优惠券'] = $params['award']['sub_coupon_enable'] ? '开启' : '关闭';
            $logPrimary['关注者获得积分'] = $params['award']['sub_credit'];
            $logPrimary['关注者获得现金'] = $params['award']['sub_cash'];
            $logPrimary['关注者获得现金类型'] = PosterCashTypeConstant::getText($params['award']['sub_cash_type']);
            $logPrimary['关注者获得优惠券'] = $params['award']['sub_coupon'];
        }
        LogModel::write(
            $this->userId,
            PosterLogConstant::POSTER_ADD,
            PosterLogConstant::getText(PosterLogConstant::POSTER_ADD),
            $result->id,
            [
                'log_data' => $result->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    PosterLogConstant::POSTER_ADD,
                    PosterLogConstant::POSTER_SAVE,
                ]
            ]
        );

        return $this->result('保存成功');
    }

    /**
     * 编辑海报
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        // 获取海报ID
        $posterId = RequestHelper::getInt('id');

        $result = PosterModel::getEditResult($posterId);

        if (is_error($result)) {
            return $this->result($result);
        }

        return $this->result(['data' => $result]);
    }

    /**
     * 保存海报
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSave()
    {
        $params = [];

        // 添加海报ID
        $posterId = RequestHelper::postInt('id');
        $params['id'] = $posterId;

        // 通用参数校验
        $check = $this->commonParamsCheck($params);

        if (is_error($check)) {
            return $this->result($check);
        }

        $result = PosterModel::getSaveResult($params);

        if (is_error($result)) {
            return $this->result($result);
        }

        // 日志
        $logPrimary = [
            'id' => $result->id,
            '海报类型' => PosterTypeConstant::getText($result->type),
            '海报名称' => $result->name,
            '模板' => $result->template_id,
            '访问页面' => PosterVisitPageConstant::getText($result->visit_page)
        ];
        if ($result->type == PosterTypeConstant::POSTER_TYPE_ATTENTION) {
            $logPrimary['海报生效时间'] = $result->expire_start_time . '~' . $result->expire_end_time;
            $logPrimary['海报有效时长'] = $result->expire_time;
            $logPrimary['获取对象'] = PosterAccessTypeConstant::getText($result->access_type);
            $logPrimary['推送方式'] = PosterPushTypeConstant::getText($params['push']['type']);
            $logPrimary['推送标题'] = $params['push']['title'];
            $logPrimary['推送描述'] = $params['push']['description'];
            $logPrimary['推送链接'] = $params['push']['url'];
            $logPrimary['关注奖励是否开启'] = $params['award']['status'] ? '开启' : '关闭';
            $logPrimary['推荐人积分'] = $params['award']['rec_credit_enable'] ? '开启' : '关闭';
            $logPrimary['推荐人现金'] = $params['award']['rec_cash_enable'] ? '开启' : '关闭';
            $logPrimary['推荐人优惠券'] = $params['award']['rec_coupon_enable'] ? '开启' : '关闭';
            $logPrimary['推荐人获得积分'] = $params['award']['rec_credit'];
            $logPrimary['推荐人积分每月积分奖励上限'] = $params['award']['rec_credit_limit'];
            $logPrimary['推荐人获得现金'] = $params['award']['rec_cash'];
            $logPrimary['推荐人现金每月奖励上限'] = $params['award']['rec_cash_limit'];
            $logPrimary['推荐人获得现金类型'] = PosterCashTypeConstant::getText($params['award']['rec_cash_type']);
            $logPrimary['推荐人获得优惠券'] = $params['award']['rec_coupon'];
            $logPrimary['推荐人优惠券每月最多发放数量'] = $params['award']['rec_coupon_limit'];
            $logPrimary['关注者积分'] = $params['award']['sub_credit_enable'] ? '开启' : '关闭';
            $logPrimary['关注者现金'] = $params['award']['sub_cash_enable'] ? '开启' : '关闭';
            $logPrimary['关注者优惠券'] = $params['award']['sub_coupon_enable'] ? '开启' : '关闭';
            $logPrimary['关注者获得积分'] = $params['award']['sub_credit'];
            $logPrimary['关注者获得现金'] = $params['award']['sub_cash'];
            $logPrimary['关注者获得现金类型'] = PosterCashTypeConstant::getText($params['award']['sub_cash_type']);
            $logPrimary['关注者获得优惠券'] = $params['award']['sub_coupon'];
        }
        LogModel::write(
            $this->userId,
            PosterLogConstant::POSTER_SAVE,
            PosterLogConstant::getText(PosterLogConstant::POSTER_SAVE),
            $result->id,
            [
                'log_data' => $result->attributes,
                'log_primary' => $logPrimary,
                'dirty_identify_code' => [
                    PosterLogConstant::POSTER_ADD,
                    PosterLogConstant::POSTER_SAVE,
                ]
            ]
        );

        return $this->result('修改成功');
    }

    private function commonParamsCheck(&$params)
    {
        $type = RequestHelper::post('type');
        if (empty($type)) {
            return error('海报类型错误');
        }

        // 检测类型是否合法
        $checkType = PosterTypeConstant::getOneByCode($type);
        if (is_null($checkType)) {
            return error('不支持的页面类型');
        }
        $params['type'] = $type;

        // 检测海报名称
        $name = RequestHelper::post('name');
        if (empty($name)) {
            return error('海报名称不能为空');
        }
        $params['name'] = $name;

        // 检测海报缩略图
        $thumb = RequestHelper::post('thumb');
        if (empty($thumb)) {
            return error('海报缩略图不能为空');
        }
        $params['thumb'] = $thumb;

        // 检测海报内容
        $content = RequestHelper::post('content');
        if (empty($content)) {
            return error('海报内容不能为空');
        }
        $params['content'] = $content;

        // 检测海报状态
        $status = RequestHelper::post('status');
        if (empty($status)) {
            $status = 0;
        }
        $params['status'] = $status;

        // 检测模板ID
        $templateId = RequestHelper::post('template_id', NULL);
        if (is_null($templateId)) {
            return error('不支持的模板');
        }
        $params['template_id'] = $templateId;

        // 分销海报
        if ($type == PosterTypeConstant::POSTER_TYPE_COMMISSION) {
            // 访问页面
            $visitPage = RequestHelper::post('visit_page');
            if (empty($visitPage)) {
                return error('分销海报访问页面不能为空');
            }
            $params['visit_page'] = $visitPage;
        }

        // 关注海报
        if ($type == PosterTypeConstant::POSTER_TYPE_ATTENTION) {
            $checkRes = $this->attentionPosterParamsCheck($params);
            return $checkRes;
        }

        return true;
    }

    private function attentionPosterParamsCheck(&$params)
    {
        // 检测关键词
        $keyword = RequestHelper::post('keyword');
        if (empty($keyword)) {
            return error('关注海报关键词不能为空');
        } else {
            // 添加判断关键词
            $andWhere = [];
            if (!empty($params['id'])) {
                $andWhere = [
                    'and',
                    ['<>', 'id', $params['id']]
                ];
            }

            $query = PosterModel::find()->where(
                [
                    'keyword' => $keyword,
                    'is_deleted' => 0
                ]
            );

            if (!empty($andWhere)) {
                $query->andWhere($andWhere);
            }

            $exist = $query->exists();
            if ($exist) {
                return error('关注海报关键词重复');
            }

        }

        $params['keyword'] = $keyword;

        // 检测获取有效期
        $expireStartTime = RequestHelper::post('expire_start_time');
        $expireEndTime = RequestHelper::post('expire_end_time');
        if (empty($expireStartTime) || empty($expireEndTime)) {
            return error('关注海报获取有效期不能为空');
        }
        if (strtotime($expireEndTime) < strtotime($expireStartTime)) {
            return error('关注海报获取有效期不合法');
        }
        $params['expire_start_time'] = $expireStartTime;
        $params['expire_end_time'] = $expireEndTime;

        // 检测海报有效期
        $expireTime = RequestHelper::post('expire_time');
        if (empty($expireTime)) {
            return error('关注海报有效期不能为空');
        }
        $params['expire_time'] = $expireTime * 86400; // 转化为天

        // 检测获取对象
        $accessType = RequestHelper::post('access_type', NULL);
        if (is_null($accessType)) {
            return error('关注海报获取对象不合法');
        }
        $params['access_type'] = $accessType;

        /***** 推送设置 *****/
        $push = [];

        // 推送方式
        $pushType = RequestHelper::post('push_type');
        if (empty($pushType)) {
            return error('关注海报推送方式不能为空');
        }
        $push['type'] = $pushType;

        // 推送标题
        $pushTitle = RequestHelper::post('push_title');
        if (empty($pushTitle)) {
            return error('关注海报推送标题不能为空');
        }
        $push['title'] = $pushTitle;

        // 推送封面
        $pushThumb = RequestHelper::post('push_thumb');
        if (empty($pushThumb) && $pushType == PosterPushTypeConstant::POSTER_PUSH_TYPE_IMAGE) {
            return error('关注海报推送封面不能为空');
        }
        $push['thumb'] = $pushThumb;

        // 推送描述
        $pushDesc = RequestHelper::post('push_desc');
        if (empty($pushDesc)) {
            return error('关注海报推送描述不能为空');
        }
        $push['description'] = $pushDesc;

        // 推送链接
        $pushUrl = RequestHelper::post('push_url');
        if (empty($pushUrl) && $pushType == 1) {
            return error('关注海报推送链接不能为空');
        }
        $push['url'] = $pushUrl;

        // 推送链接名称
        $pushUrlName = RequestHelper::post('push_url_name');
        if (empty($pushUrl) && $pushType == 1) {
            return error('关注海报推送链接名称不能为空');
        }
        $push['url_name'] = $pushUrlName;

        $params['push'] = $push;

        /***** 奖励设置 *****/
        $award = [];

        // 关注奖励开启状态
        $awardStatus = RequestHelper::post('award_status', 0);
        $award['status'] = $awardStatus;

        // 未开启奖励 直接退出
        if (!$awardStatus) {
            $params['award'] = $award;
            return true;
        }

        // 推荐人
        $recCreditEnable = RequestHelper::post('rec_credit_enable', NULL);
        if (is_null($recCreditEnable)) {
            return error('推荐人积分奖励开启异常');
        }
        $award['rec_credit_enable'] = $recCreditEnable;
        if ($recCreditEnable) {
            $recCredit = RequestHelper::post('rec_credit', NULL);
            if (is_null($recCredit)) {
                return error('推荐人获得积分异常');
            }
            $award['rec_credit'] = $recCredit;

            $recCreditLimit = RequestHelper::post('rec_credit_limit', NULL);
            if (is_null($recCreditLimit)) {
                return error('推荐人积分每月积分奖励上限异常');
            }
            $award['rec_credit_limit'] = $recCreditLimit;
        }

        $recCashEnable = RequestHelper::post('rec_cash_enable', NULL);
        if (is_null($recCashEnable)) {
            return error('推荐人现金奖励开启异常');
        }
        $award['rec_cash_enable'] = $recCashEnable;
        if ($recCashEnable) {
            $recCash = RequestHelper::post('rec_cash', NULL);
            if (is_null($recCash)) {
                return error('推荐人获得现金异常');
            }
            $award['rec_cash'] = $recCash;

            $recCashLimit = RequestHelper::post('rec_cash_limit', NULL);
            if (is_null($recCashLimit)) {
                return error('推荐人现金每月奖励上限异常');
            }
            $award['rec_cash_limit'] = $recCashLimit;

            $recCashType = RequestHelper::post('rec_cash_type', NULL);
            if (is_null($recCashType)) {
                return error('推荐人获得现金类型异常');
            }
            $award['rec_cash_type'] = $recCashType;
        }

        $recCouponEnable = RequestHelper::post('rec_coupon_enable', NULL);
        if (is_null($recCouponEnable)) {
            return error('推荐人优惠券奖励开启异常');
        }
        $award['rec_coupon_enable'] = $recCouponEnable;
        if ($recCouponEnable) {
            $recCoupon = RequestHelper::post('rec_coupon', NULL);
            if (is_null($recCoupon)) {
                return error('推荐人获得优惠券异常');
            }
            $award['rec_coupon'] = $recCoupon;

            $recCouponLimit = RequestHelper::post('rec_coupon_limit', NULL);
            if (is_null($recCouponLimit)) {
                return error('推荐人优惠券每月最多发放数量异常');
            }
            $award['rec_coupon_limit'] = $recCouponLimit;
        }


        // 关注者

        $subCreditEnable = RequestHelper::post('sub_credit_enable', NULL);
        if (is_null($subCreditEnable)) {
            return error('关注者积分奖励开启异常');
        }
        $award['sub_credit_enable'] = $subCreditEnable;
        if ($subCreditEnable) {
            $subCredit = RequestHelper::post('sub_credit', NULL);
            if (is_null($subCredit)) {
                return error('关注者获得积分异常');
            }
            $award['sub_credit'] = $subCredit;
        }

        $subCashEnable = RequestHelper::post('sub_cash_enable', NULL);
        if (is_null($subCashEnable)) {
            return error('关注者现金奖励开启异常');
        }
        $award['sub_cash_enable'] = $subCashEnable;
        if ($subCashEnable) {
            $subCash = RequestHelper::post('sub_cash', NULL);
            if (is_null($subCash)) {
                return error('关注者获得现金异常');
            }
            $award['sub_cash'] = $subCash;

            $subCashType = RequestHelper::post('sub_cash_type', NULL);
            if (is_null($subCashType)) {
                return error('关注者获得现金类型异常');
            }
            $award['sub_cash_type'] = $subCashType;
        }

        $subCouponEnable = RequestHelper::post('sub_coupon_enable', NULL);
        if (is_null($subCouponEnable)) {
            return error('关注者优惠券奖励开启异常');
        }
        $award['sub_coupon_enable'] = $subCouponEnable;
        if ($subCouponEnable) {
            $subCoupon = RequestHelper::post('sub_coupon', NULL);
            if (is_null($subCoupon)) {
                return error('关注者获得优惠券异常');
            }
            $award['sub_coupon'] = $subCoupon;
        }

        $params['award'] = $award;

        return true;
    }

}