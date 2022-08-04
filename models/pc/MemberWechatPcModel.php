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

namespace shopstar\models\pc;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\ClientTypeConstant;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\member\MemberModel;
use shopstar\services\member\MemberWechatService;

/**
 * This is the model class for table "{{%member_wechat_pc}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property string $openid openid
 * @property string $unionid unionid
 * @property string $avatar 微信头像
 * @property string $nickname 微信昵称
 * @property string $access_token
 * @property string $refresh_token
 * @property int $is_deleted 是否删除 1是0否
 * @property string $created_at 创建时间
 */
class MemberWechatPcModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%member_wechat_pc}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['member_id', 'is_deleted'], 'integer'],
            [['created_at'], 'safe'],
            [['openid', 'unionid'], 'string', 'max' => 50],
            [['avatar', 'nickname'], 'string', 'max' => 191],
            [['access_token', 'refresh_token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'openid' => 'openid',
            'unionid' => 'unionid',
            'avatar' => '微信头像',
            'nickname' => '微信昵称',
            'access_token' => 'Access Token',
            'refresh_token' => 'Refresh Token',
            'is_deleted' => '是否删除 1是0否',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 检测并注册会员
     * @param array $userInfo
     * @return array
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkMember(array $userInfo): array
    {
        if (!isset($userInfo['original']) || !isset($userInfo['access_token'])) {
            throw new MemberException(MemberException::MEMBER_WECHAT_PC_CHECK_PARAMS_INVALID);
        }

        // 原始数据
        $original = $userInfo['original'];

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // 当前操作渠道会员
            $model = null;

            // 如过对接了开放平台，查询当前unionid是否注册过APP渠道
            if (!empty($original['unionid'])) {
                $model = self::findOne([
                    'unionid' => $original['unionid'],
                    'is_deleted' => 0,
                ]);
            }

            // 如果unionid没有查到 || 没有unionid，使用openid查
            if (empty($model->id)) {
                $model = self::findOne([
                    'openid' => $original['openid'],
                    'is_deleted' => 0,
                ]);
            }

            // 如果查不到则是新会员
            if (empty($model)) {
                $model = new self();
                $model->setAttribute('created_at', DateTimeHelper::now());
            }

            // 处理使用哪个会员ID
            $memberId = $model->member_id;

            // 如果当前渠道没有会员ID，并且对接了开放平台，查询其他渠道
            if (empty($model->member_id) && !empty($original['unionid'])) {
                $memberId = MemberWechatService::getMemberIdByUnionId($original['unionid'], [
                    MemberWechatService::CHANNEL_OFFICE_ACCOUNT,
                    MemberWechatService::CHANNEL_MINI_PROGRAM,
                ]);
            }

            if (!empty($memberId)) {
                // 检测会员黑名单
                self::checkBlackList($memberId);
            }

            // 保存会员主体信息
            $memberInfo = MemberModel::saveMember([
                'id' => $memberId,
                'avatar' => $original['headimgurl'],
                'nickname' => $original['nickname'],
                'province' => $original['province'],
                'city' => $original['city'],
                'source' => ClientTypeConstant::CLIENT_PC,
            ], true);
            if (is_error($memberInfo)) {
                throw new MemberException(MemberException::MEMBER_WECHAT_PC_CHECK_CREATE_FAIL, $memberInfo['message']);
            }

            // 保存会员渠道信息
            $model->setAttributes([
                'member_id' => $memberInfo['id'],
                'openid' => $original['openid'],
                'unionid' => $original['unionid'],
                'avatar' => $original['headimgurl'],
                'nickname' => $original['nickname'],
                'access_token' => $userInfo['access_token'],
                'refresh_token' => $userInfo['refresh_token'],
            ]);
            if (!$model->save()) {
                throw new MemberException(MemberException::MEMBER_WECHAT_PC_CHECK_CREATE_CHANNEL_FAIL, $model->getErrorMessage());
            }

            $transaction->commit();

        } catch (\Exception $exception) {
            $transaction->rollBack();

            // 继续抛出异常
            throw new MemberException($exception->getCode(), $exception->getMessage());
        }

        return $memberInfo;
    }

    /**
     * 检测会员黑名单
     * @param int $memberId
     * @return MemberModel
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    private static function checkBlackList(int $memberId)
    {
        /**
         * @var MemberModel $member
         */
        $member = MemberModel::find()
            ->where([
                'id' => $memberId,
            ])
            ->select(['id', 'is_black'])
            ->one();

        if (!empty($member) && $member->is_black == 1) {
            throw new MemberException(MemberException::MEMBER_WECHAT_PC_CHECK_IN_BLACK_LIST);
        }

        return $member;
    }
}
