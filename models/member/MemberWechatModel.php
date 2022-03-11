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

use shopstar\bases\model\BaseActiveRecord;
use shopstar\models\member\MemberModel;
use shopstar\services\member\MemberWechatService;
use shopstar\exceptions\member\MemberException;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\group\MemberGroupModel;
use shopstar\models\wechat\WechatFansModel;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%member_wechat}}".
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
class MemberWechatModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_wechat}}';
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
     * 获取用户关注状态
     * @param int $id
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMemberFollow(int $id)
    {
        $wechatMember = self::find()->select('openid')->where(['member_id' => $id, 'is_deleted' => 0])->first();
        $fans = [];
        $fans = WechatFansModel::find()->where([
            'open_id' => $wechatMember['openid'],
        ])->select([
            'is_follow as follow'
        ])->first();


        // 0未关注 1已关注 2已取消
        if ($fans['follow'] == 1) {
            return 1;
        } else if ($fans['follow'] == 0 && $fans['un_follow_time'] != 0) {
            return 2;
        } else {
            return 0;
        }
    }


    /**
     * @param array $wechatMember
     * @param int $clientType
     * @return array
     * @throws \common\exceptions\member\MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkMember(array $wechatMember, int $clientType): array
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            // 当前操作渠道会员
            $model = null;

            // 如过对接了开放平台，查询当前unionid是否注册过公众号渠道
            if (!empty($wechatMember['unionid'])) {
                $model = self::findOne([
                    'unionid' => $wechatMember['unionid'],
                    'is_deleted' => 0,
                ]);
            }

            // 如果unionid没有查到 || 没有unionid，使用openid查
            if (empty($model->id)) {
                $model = self::findOne([
                    'openid' => $wechatMember['openid'],
                    'is_deleted' => 0,
                ]);
            }

            // 如果查不到则是新会员
            if (empty($model)) {
                $model = new self();
            }

            // 处理使用哪个会员ID
            $memberId = $model->member_id;
            if (empty($model->member_id) && !empty($wechatMember['unionid'])) {
                $memberId = MemberWechatService::getMemberIdByUnionId($wechatMember['unionid'], [
                    MemberWechatService::CHANNEL_MINI_PROGRAM,
                    MemberWechatService::CHANNEL_PC,
                ]);
            }

            if (!empty($memberId)) {
                // 检测会员黑名单
                self::checkBlackList($memberId);
            }

            //保存会员主体
            $memberInfo = MemberModel::saveMember([
                'id' => $memberId,
                'avatar' => $wechatMember['headimgurl'],
                'nickname' => $wechatMember['nickname'],
                'province' => $wechatMember['province'],
                'city' => $wechatMember['city'],
                'source' => $clientType,
            ], true);

            if (is_error($memberInfo)) {
                throw new MemberException(MemberException::MEMBER_WECHAT_CREATE_MEMBER_ERROR, $memberInfo['message']);
            }


            //保存微信会员
            $model->setAttributes(array_merge($wechatMember, [
                'avatar' => $wechatMember['headimgurl'],
                'unionid' => $wechatMember['unionid'] ?: '',
                'type' => 1,
                'member_id' => $memberInfo['id']
            ]));

            if ($model->save() === false) {
                throw new MemberException(MemberException::MEMBER_WECHAT_CREATE_WECHAT_MEMBER_ERROR, $model->getErrorMessage());
            }

            $transaction->commit();

        } catch (\Throwable $e) {
            $transaction->rollBack();

            // 继续抛出异常
            throw new MemberException($e->getCode(), $e->getMessage());
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

    /**
     * 根据openID获取memberId
     * @param string $openId openID
     * @return int|mixed
     */
    public static function getIdByOpenId(string $openId)
    {
        $member = self::find()
            ->where([
                'openid' => $openId,
                'is_deleted' => 0
            ])
            ->select('member_id')
            ->first();

        if (empty($member)) {
            return 0;
        }

        return $member['member_id'];
    }


    /**
     * 标签组映射关系
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getMemberGroupsMap()
    {
        return $this->hasMany(MemberGroupMapModel::class, ['member_id' => 'member_id']);
    }

    /**
     * 标签组关系
     * @return ActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function getMemberGroups()
    {
        return $this->hasMany(MemberGroupModel::class, ['id' => 'group_id'])->via('memberGroupsMap');
    }
}
