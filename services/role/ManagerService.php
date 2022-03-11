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


namespace shopstar\services\role;

use shopstar\bases\service\BaseService;
use shopstar\constants\CacheTypeConstant;
use shopstar\exceptions\UserException;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\role\ManagerModel;
use shopstar\models\role\ManagerVerifyMapModel;
use shopstar\models\user\UserModel;
use shopstar\services\user\UserService;

class ManagerService extends BaseService
{


    /**
     * 创建操作员
     * @param $post
     * @return ManagerModel|array|bool
     * @author  青岛开店星信息技术有限公司
     */
    public static function createByPost($post)
    {
        $trans = \Yii::$app->db->beginTransaction();

        try {
            // 创建用户
            $kdxUid = UserService::createUser($post);

            $andWere['is_deleted'] = 0;
            // 创建操作员
            $result = ManagerModel::find()
                ->where(['uid' => $kdxUid])
                ->andWhere($andWere)
                ->one();

            if ($result === null) {

                // 创建管理员记录
                $result = new ManagerModel();
                $result->uid = $kdxUid;
                $result->role_id = $post['role_id'];
                $result->name = $post['name'] ?? '';
                $result->contact = $post['contact'];
                $result->create_uid = $post['create_uid'];
                $result->status = $post['status'];
                $result->created_at = DateTimeHelper::now();
                $result->source = $post['source'] ?? 0;
                $post['member_id'] && $result->member_id = $post['member_id'];
                if (!$result->save()) {
                    throw new UserException(UserException::MANAGE_USER_INDEX_CREATE_MANAGE_FAILED);
                }
            } else {
                // 该操作员已经存在
                throw new UserException(UserException::MANAGE_USER_INDEX_CREATE_MANAGE_EXISTS);
            }
            // 操作员绑定核销员
            if (($post['member_id'] != '0' || !empty($post['verify_point_id'])) && !is_null($result->id)) {
                $post['manager_id'] = $result->id;
                $post['uid'] = $result->uid;
                ManagerVerifyMapModel::saveData($post);
            }

            $trans->commit();
        } catch (\Throwable $e) {
            $trans->rollBack();
            return error($e->getMessage(), $e->getCode());
        }

        return $result ?? true;
    }

    /**
     * 更新操作员
     * @param $data
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveByPost($data)
    {
        $trans = \Yii::$app->db->beginTransaction();

        try {
            // 获取操作员
            $where = ['id' => $data['id']];
            $manager = ManagerModel::find()->where($where)->one();
            if (empty($manager)) {
                throw new UserException(UserException::MANAGE_USER_INDEX_MANAGE_NOT_EXISTS);
            }
            $manager->role_id = $data['role_id'];
            $manager->name = $data['name'];
            $manager->contact = $data['contact'];
            $manager->status = $data['status'];
            $manager->member_id = $data['member_id'];

            $manager->save();
            // 修改密码
            if (isset($data['password']) && !empty($data['password'])) {
                UserModel::updateUser($data, $manager->uid);
            }

            // 清除缓存
            ManagerModel::deleteCache(CacheTypeConstant::MANAGE_PROFILE, [$manager['uid']]);

            // 操作员绑定核销员，核销点 TODO 青岛开店星信息技术有限公司
            if (!is_null($manager->id)) {
                $data['manager_id'] = $manager->id;
                $data['uid'] = $manager->uid;
                ManagerVerifyMapModel::updateData($data);
            }

            $trans->commit();
        } catch (\Throwable $e) {
            $trans->rollBack();
            return error($e->getMessage(), $e->getCode());
        }
        return true;
    }


}