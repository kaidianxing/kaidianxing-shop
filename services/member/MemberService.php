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


namespace shopstar\services\member;

use shopstar\bases\service\BaseService;
use shopstar\constants\ClientTypeConstant;
use shopstar\constants\log\member\MemberLogConstant;
 
use shopstar\models\log\LogModel;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\member\MemberDouyinModel;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberSession;
use shopstar\models\member\MemberToutiaoLiteModel;
use shopstar\models\member\MemberToutiaoModel;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\member\MemberWxappModel;
use shopstar\services\commission\CommissionAgentService;

class MemberService extends BaseService
{

    /**
     * 删除会员
     * @param int $id
     * @param int $uid
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function delete(int $id, int $uid)
    {
        try {
            $member = MemberModel::findOne(['id' => $id, 'is_deleted' => 0]);
            if (empty($member)) {
                return error('会员不存在');
            }
            // 等级名称
            $levelName = $member->level->level_name;
            // 获取分组
            $groupIds = MemberGroupMapModel::find()->select('group_id')->where(['member_id' => $id])->get();
            $groupIds = array_column($groupIds, 'group_id');
            $groups = MemberGroupModel::find()->select('group_name')->where(['id' => $groupIds])->get();
            $groupName = implode(',', array_column($groups, 'group_name'));
            // 获取所有渠道信息
            $sourceInfo = MemberModel::getMemberAllSource($id, $member->source, $member->mobile);

            // 删除
            $member->is_deleted = 1;
            $member->level_id = 0; // 删除等级
            $member->save();

            // 公众号
            MemberWechatModel::updateAll(['is_deleted' => 1], ['member_id' => $id]);
            // 小程序
            MemberWxappModel::updateAll(['is_deleted' => 1], ['member_id' => $id]);
            // 字节跳动小程序
            MemberDouyinModel::updateAll(['is_deleted' => 1], ['member_id' => $id]);
            // 字节跳动小程序
            MemberToutiaoModel::updateAll(['is_deleted' => 1], ['member_id' => $id]);
            // 字节跳动小程序
            MemberToutiaoLiteModel::updateAll(['is_deleted' => 1], ['member_id' => $id]);
            // 删除标签组信息
            MemberGroupMapModel::deleteAll(['member_id' => $id]);

            // 处理分销关系
            $returnData = CommissionAgentService::deleteAgent($id);

            // 删除用户session
            MemberSession::deleteMemberSession($id);

            // 记录日志
            $sourceName = [];
            foreach ($sourceInfo as $item) {
                if ($item['is_register'] == 0) {
                    continue;
                }
                switch ($item['source']) {
                    case '10':
                        $sourceName[] = 'H5';
                        break;
                    case '20':
                        $sourceName[] = '微信公众号';
                        break;
                    case '21':
                        $sourceName[] = '微信小程序';
                        break;
                    case '30':
                        $sourceName[] = '头条小程序';
                        break;
                    case '31':
                        $sourceName[] = '抖音小程序';
                        break;
                    default:
                        $sourceName[] = '未知';
                        break;
                }
            }

            $logPrimaryData = [
                'id' => $id,
                'avatar' => $member->avatar ?: '-',
                'nickname' => $member->nickname,
                'realname' => $member->realname ?: '-',
                'mobile' => $member->mobile ?: '-',
                'credit' => $member->credit,
                'balance' => $member->balance,
                'level_name' => $levelName,
                'group_name' => $groupName ?: '-',
                'source' => implode(',', $sourceName),
            ];
            if (!empty($returnData)) {
                $logPrimaryData['commission_info'] = $returnData;
            }

            LogModel::write(
                $uid,
                MemberLogConstant::MEMBER_DELETE,
                MemberLogConstant::getText(MemberLogConstant::MEMBER_DELETE),
                $id,
                [
                    'log_data' => $member->attributes,
                    'log_primary' => $member->getLogAttributeRemark($logPrimaryData),
                    [
                        MemberLogConstant::MEMBER_DELETE,
                    ]
                ]
            );

        } catch (\Throwable $e) {
            return error($e->getMessage());
        }
        return true;
    }


    /**
     * @param int $type
     * @param int $member_id
     * @return array|MemberModel|MemberWxappModel|\yii\db\ActiveRecord|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getModelByType(int $type, int $member_id)
    {
        switch ($type) {
            case ClientTypeConstant::CLIENT_H5:
                $model = MemberModel::findOne(['id' => $member_id,  'is_deleted' => 0]);
                break;
            case ClientTypeConstant::CLIENT_WECHAT:
                $model = MemberWechatModel::find()
                    ->where(['member_id' => $member_id,  'is_deleted' => 0])
                    ->one();
                break;
            case ClientTypeConstant::CLIENT_WXAPP:
                $model = MemberWxappModel::findOne(['member_id' => $member_id, 'is_deleted' => 0]);
                break;
            case ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO:
                $model = MemberToutiaoModel::findOne(['member_id' => $member_id, 'is_deleted' => 0]);
                break;
            case ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE:
                $model = MemberToutiaoLiteModel::findOne(['member_id' => $member_id,  'is_deleted' => 0]);
                break;
            case ClientTypeConstant::CLIENT_BYTE_DANCE_DOUYIN:
                $model = MemberDouyinModel::findOne(['member_id' => $member_id, 'is_deleted' => 0]);
                break;
            default:
                $model = null;
        }

        if ($model === null) {
            return error('用户不存在');
        }
        return $model;
    }


    /**
     * 转移账号主体
     * @param int $memberId
     * @param int $toTransferredMemberId
     * @param int $beTransferredMemberId
     * @param string $clientType
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public static function changeAccountSubject(int $memberId, int $toTransferredMemberId, int $beTransferredMemberId, string $clientType)
    {
        $clientObjectArrays = [
            ClientTypeConstant::CLIENT_WECHAT => MemberWechatModel::find(),
            ClientTypeConstant::CLIENT_WXAPP => MemberWxappModel::find(),
            ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO => MemberToutiaoModel::find(),
            ClientTypeConstant::CLIENT_BYTE_DANCE_TOUTIAO_LITE => MemberToutiaoLiteModel::find(),
            ClientTypeConstant::CLIENT_BYTE_DANCE_DOUYIN => MemberDouyinModel::find(),
        ];

        foreach ($clientObjectArrays as $clientObjectArraysIndex => $clientObjectArray) {
            //如果等于当前的渠道
            if ($clientType == $clientObjectArraysIndex) {
                //废弃渠道的member id
                if ($memberId == $toTransferredMemberId) { //如果当前登录的会员id 和选择转移的会员id相同的话，那么则选择的是选择老账号  如果是老账号则需要把老账号的当前的渠道信息置为失效
                    $discardId = $beTransferredMemberId;
                } else {                                  //如果当前登录的会员id 不等于 选择转移的会员id相同的话，那么则选择的是选择新账号  如果是新账号则需要把老账号的当前的渠道信息置为失效
                    $discardId = $toTransferredMemberId;
                }

                //查找要转移的当前渠道的会员
                $beTransferredClientMember = $clientObjectArray->where(['member_id' => $discardId, 'is_deleted' => 0])->one();
                if (!empty($beTransferredClientMember)) {
                    //把要转移的当前渠道的会员置为失效
                    $beTransferredClientMember->is_deleted = 1;
                    $beTransferredClientMember->save();
                }
            }

            //查找需要转移的会员
            $clientObject = $clientObjectArray->where(['member_id' => $beTransferredMemberId, 'is_deleted' => 0])->one();

            //如果为空则
            if (empty($clientObject)) continue;

            $clientObject->member_id = $toTransferredMemberId;
            if (!$clientObject->save()) {
                return error('附属会员修改主体失败');
            }
        }

        //废弃被转移渠道会员
        MemberModel::updateAll(['is_deleted' => 1], ['id' => $beTransferredMemberId]);
        MemberSession::deleteMemberSession([
            $beTransferredMemberId,
            $toTransferredMemberId
        ]);
        return true;
    }



}