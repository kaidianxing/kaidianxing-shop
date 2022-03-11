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

namespace shopstar\mobile\member;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\RequestHelper;
use shopstar\models\member\MemberAddressModel;
use yii\web\Response;

class AddressController extends BaseMobileApiController
{
    /**
     * 获取地址列表
     * @return Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionIndex()
    {
        $params = [
            'andWhere' => [
                ['member_id' => $this->memberId],
                ['is_delete' => 0]
            ],
            'select' => 'id,name,mobile,province,city,area,address,is_default,address_code',
            'orderBy'=>['is_default' => SORT_DESC,'id' => SORT_DESC]
        ];

        $list = MemberAddressModel::getColl($params);
        return $this->result(['list' => $list]);
    }

    /**
     * 用户新增地址
     * @return Response
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionCreate()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $res = MemberAddressModel::saveAddress($this->memberId,0,$this->shopType);
            if (is_error($res)) {
                throw new MemberException(MemberException::MEMBER_ADDRESS_CREATE_FAIL, $res['message']);
            }
            $transaction->commit();
        } catch (MemberException $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }

        return $this->result([
            'id' => $res['id'],
        ]);
    }

    /**
     * 修改地址
     * @return Response
     * @throws MemberException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSave()
    {
        $id = RequestHelper::postInt('id');
        if (empty($id)) {
            throw new MemberException(MemberException::MEMBER_ADDRESS_SAVE_PARAM_ERROR);
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $res = MemberAddressModel::saveAddress($this->memberId, $id,$this->shopType);
            if (is_error($res)) {
                throw new MemberException(MemberException::MEMBER_ADDRESS_SAVE_FAIL, $res['message']);
            }
            $transaction->commit();
        } catch (MemberException $exception) {
            $transaction->rollBack();
            return $this->error($exception->getMessage(), $exception->getCode());
        }

        return $this->result([
            'id' => $res['id'],
        ]);
    }

    /**
     * 删除地址
     * @return Response
     * @throws MemberException
     * @throws MemberException|\yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            throw new MemberException(MemberException::MEMBER_ADDRESS_DELETE_PARAM_ERROR);
        }
        $transaction = \Yii::$app->getDb()->beginTransaction();
        $res = MemberAddressModel::deleteAddress($id, $this->memberId);
        if (is_error($res)) {
            $transaction->rollBack();
            throw new MemberException(MemberException::MEMBER_ADDRESS_DELETE_FAIL, $res['message']);
        }
        $transaction->commit();
        
        return $this->success();
    }

    /**
     * 设置默认地址
     * @return Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetDefault()
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            throw new MemberException(MemberException::MEMBER_ADDRESS_SET_DEFAULT_PARAM_ERROR);
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            MemberAddressModel::updateAll(['is_default' => 0], ['is_default' => 1, 'member_id' => $this->memberId]);
            MemberAddressModel::updateAll(['is_default' => 1], ['id' => $id, 'member_id' => $this->memberId]);
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new MemberException(MemberException::MEMBER_ADDRESS_SET_DEFAULT_FAIL);
        }
        return $this->success();
    }
    
    /**
     * 获取地址详情
     * @return array|Response
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit(): Response
    {
        $id = RequestHelper::get('id', '');
        if (empty($id)) {
            throw new MemberException(MemberException::MEMBER_ADDRESS_DETAIL_PARAMS_ERROR);
        }
        $info = MemberAddressModel::find()
            ->where(['id' => $id, 'member_id' => $this->memberId, 'is_delete' => 0])
            ->select('id, name, mobile, province, city, area, address, is_default, address_code')
            ->first();
        if (empty($info)) {
            throw new MemberException(MemberException::MEMBER_ADDRESS_DETAIL_NOT_EXISTS);
        }
        return $this->result(['data' => $info]);
    }

}
