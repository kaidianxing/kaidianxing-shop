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

namespace shopstar\models\member;
use shopstar\models\member\MemberModel;
use shopstar\services\member\MemberWechatService;
use shopstar\exceptions\member\MemberException;

/**
 * This is the model class for table "{{%member_wxapp}}".
 *
 * @property int $id
 * @property string $openid
 * @property int $member_id 用户id
 * @property string $avatar 微信头像
 * @property string $nickname 微信昵称
 * @property string $unionid 绑定开放平台的
 * @property int $type 0=微信, 1=微信小程序
 * @property string $wechat_id 微信号
 * @property string $is_deleted 是否删除 1是0否
 */
class MemberWxappModel extends \shopstar\bases\model\BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_wxapp}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['openid'], 'required'],
            [['member_id', 'type', 'is_deleted'], 'integer'],
            [['openid', 'unionid'], 'string', 'max' => 50],
            [['avatar', 'nickname'], 'string', 'max' => 191],
            [['wechat_id'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'openid' => 'Openid',
            'member_id' => '用户id',
            'avatar' => '微信头像',
            'nickname' => '微信昵称',
            'unionid' => '绑定开放平台的',
            'type' => '0=微信, 1=微信小程序',
            'wechat_id' => '微信号',
            'is_deleted' => '是否删除 1是0否',
        ];
    }

    /**
     * 检测并注册会员
     * @param array $wxappMember 小程序会员信息
     * @param int $clientType 客户端类型
     * @return array
     * @throws \common\exceptions\member\MemberException
     */
    public static function checkMember(array $wxappMember, int $clientType): array
    {

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // 当前操作渠道会员
            $model = null;

            // 如过对接了开放平台，查询当前unionid是否注册过APP渠道
            if (!empty($wxappMember['unionId'])) {
                $model = self::findOne([
                    'unionid' => $wxappMember['unionId'],
                    'is_deleted' => 0,
                ]);
            }

            // 如果unionid没有查到 || 没有unionid，使用openid查
            if (empty($model->id)) {
                $model = self::findOne([
                    'openid' => $wxappMember['openId'],
                    'is_deleted' => 0,
                ]);
            }

            // 如果查不到则是新会员
            if (empty($model)) {
                $model = new self();
            }

            // 处理使用哪个会员ID
            $memberId = $model->member_id;

            // 如果当前渠道没有会员ID，并且对接了开放平台，查询其他渠道
            if (empty($model->member_id) && !empty($wxappMember['unionId'])) {
                $memberId = MemberWechatService::getMemberIdByUnionId($wxappMember['unionId'], [
                    MemberWechatService::CHANNEL_OFFICE_ACCOUNT,
                    MemberWechatService::CHANNEL_PC,
                ]);
            }

            if (!empty($memberId)) {
                // 检测会员黑名单
                self::checkBlackList($memberId);
            }

            //保存会员主体信息
            $memberInfo = MemberModel::saveMember([
                'id' => $memberId,
                'avatar' => $wxappMember['avatarUrl'],
                'nickname' => $wxappMember['nickName'],
                'province' => $wxappMember['province'],
                'city' => $wxappMember['city'],
                'source' => $clientType,
            ], true);
            if (is_error($memberInfo)) {
                throw new MemberException(MemberException::MEMBER_WXAPP_SUBJECT_ACCOUNT_CREATE_ERROR, $memberInfo['message']);
            }

            //保存小程序会员
            $model->setAttributes([
                'member_id' => $memberInfo['id'],
                'openid' => $wxappMember['openId'],
                'avatar' => $wxappMember['avatarUrl'],
                'nickname' => $wxappMember['nickName'],
                'type' => 3,
                'unionid' => $wxappMember['unionId'] ?: ''
            ]);
            if ($model->save() === false) {
                throw new MemberException(MemberException::MEMBER_WXAPP_ACCOUNT_CREATE_ERROR);
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
     * @param int $memberId 会员ID
     * @throws MemberException
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


    /**
     * 获取openId
     * @param $memberId
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOpenId($memberId)
    {
        $member = self::find()
            ->where(['member_id' => $memberId, 'is_deleted' => 0])
            ->select('openid')
            ->first();

        if (!empty($member)) {
            return $member['openid'];
        }

        return $member;
    }
}
