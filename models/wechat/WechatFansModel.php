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

namespace shopstar\models\wechat;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\wechat\helpers\OfficialAccountFansHelper;
use shopstar\helpers\DateTimeHelper;
use yii\db\Exception;

/**
 * This is the model class for table "{{%wechat_fans}}".
 *
 * @property int $id
 * @property int $is_follow 是否关注 0否 1是
 * @property string $follow_time 关注时间
 * @property string $unfollow_time 取关时间
 * @property int $is_black 是否黑名单 1是0否
 * @property string $open_id openid
 * @property string $union_id 联合id
 * @property string $nickname 昵称
 * @property string $avatar 头像
 * @property int $sex 性别 1男2女
 * @property string $language 语言
 * @property string $country 国家
 * @property string $province 省
 * @property string $city 市
 * @property string $remark 备注
 * @property string $created_at 创建时间
 * @property string $black_time 拉黑时间
 */
class WechatFansModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%wechat_fans}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['is_follow', 'is_black', 'sex'], 'integer'],
            [['follow_time', 'unfollow_time', 'created_at', 'black_time'], 'safe'],
            [['open_id', 'union_id', 'avatar'], 'string', 'max' => 255],
            [['nickname', 'language', 'country', 'province', 'city', 'remark'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'is_follow' => '是否关注 0否 1是',
            'follow_time' => '关注时间',
            'unfollow_time' => '取关时间',
            'is_black' => '是否黑名单 1是0否',
            'open_id' => 'openid',
            'union_id' => '联合id',
            'nickname' => '昵称',
            'avatar' => '头像',
            'sex' => '性别 1男2女',
            'language' => '语言',
            'country' => '国家',
            'province' => '省',
            'city' => '市',
            'remark' => '备注',
            'created_at' => '创建时间',
            'black_time' => '拉黑时间',
        ];
    }

    /**
     * 更改关注事件 如果是新用户则直接创建粉丝
     * @param string $openId
     * @param bool $subscribe
     * @return array|bool
     * @author 青岛开店星信息技术有限公司.
     */
    public static function changeFollowStatus(string $openId, bool $subscribe = true)
    {
        $fansModel = WechatFansModel::find()->where([
            'open_id' => $openId,
        ])->one();

        if ($subscribe) {
            //获取粉丝信息
            $userInfo = OfficialAccountFansHelper::getInfo($openId);

            if (empty($userInfo['openid'])) {
                return error('获取粉丝失败');
            }

            if (empty($fansModel['id'])) {
                $fansModel = new WechatFansModel();
            }

            $fansModel->setAttributes([
                'open_id' => (string)$userInfo['openid'],
                'union_id' => (string)$userInfo['unionid'],
                'nickname' => (string)$userInfo['nickname'],
                'avatar' => (string)$userInfo['headimgurl'],
                'sex' => (int)$userInfo['sex'],
                'language' => (string)$userInfo['language'],
                'is_follow' => (int)$userInfo['subscribe'],
                'follow_time' => date('Y-m-d H:i:s', $userInfo['subscribe_time']),
                'country' => (string)$userInfo['country'],
                'province' => (string)$userInfo['province'],
                'city' => (string)$userInfo['city'],
                'remark' => (string)$userInfo['remark'],
                'created_at' => DateTimeHelper::now(),
            ]);
        } else {

            if (empty($fansModel)) {
                return error('粉丝不存在');
            }

            $fansModel->setAttributes([
                'is_follow' => (int)$subscribe,
                'unfollow_time' => DateTimeHelper::now(),
            ]);
        }


        if (!$fansModel->save()) {
            return error($fansModel->getErrorMessage());
        }

        //保存
        return true;
    }

    /**
     * 获取公众号粉丝
     * @param array $options
     * @return array|bool|int
     * @throws Exception
     * @author 青岛开店星信息技术有限公司.
     */
    public static function sync(array $options = [])
    {
        $options = array_merge([
        ], $options);

        //删除以前的粉丝
        WechatFansModel::deleteAll();

        //删除标签
        WechatFansTagMapModel::deleteAll();

        // 当前时间
        $time = DateTimeHelper::now();

        // 获取数据
        $openId = self::recursiveGetWechatUser();

        //黑名单
        $blackOpenId = self::recursiveGetWechatUser(null, true);

        if (is_error($openId)) {
            return error($openId['message']);
        }

        if (empty($openId)) {
            return true;
        }

        $userInfoList = [];

        //分割数组
        $openId = array_chunk($openId, 100);

        foreach ($openId as $openIdItem) {

            //获取粉丝信息
            $userInfoListTemp = OfficialAccountFansHelper::getInfo($openIdItem);
            if (is_error($userInfoList)) {
                return error($userInfoList['message']);
            }

            $userInfoList = array_merge($userInfoList, $userInfoListTemp['user_info_list']);
        }

        if (empty($userInfoList)) {
            return true;
        }

        $tagInsertData = [];

        foreach ($userInfoList as $userInfo) {
            $attributes = [
                'open_id' => (string)$userInfo['openid'],
                'union_id' => (string)$userInfo['unionid'],
                'nickname' => (string)$userInfo['nickname'],
                'avatar' => (string)$userInfo['headimgurl'],
                'sex' => (int)$userInfo['sex'],
                'language' => (string)$userInfo['language'],
                'is_follow' => (int)$userInfo['subscribe'],
                'follow_time' => date('Y-m-d H:i:s', $userInfo['subscribe_time']),
                'country' => (string)$userInfo['country'],
                'province' => (string)$userInfo['province'],
                'city' => (string)$userInfo['city'],
                'remark' => (string)$userInfo['remark'],
                'created_at' => $time,
            ];

            /**
             * 如果是黑名单用户
             */
            if (in_array($userInfo['openid'], $blackOpenId)) {
                $attributes['is_black'] = 1;
                $attributes['black_time'] = $time;
            }

            $fansModel = new self();
            $fansModel->setAttributes($attributes);

            //保存标签
            $fansModel->save();

            //获取用户下的标签
            $userTag = OfficialAccountFansHelper::getUserTags($userInfo['openid']);

            foreach ((array)$userTag['tagid_list'] as $wechatTagId) {
                $tagInsertData[] = [
                    'wechat_tag_id' => $wechatTagId,
                    'fans_id' => $fansModel->id,
                    'created_at' => DateTimeHelper::now()
                ];
            }

        }

        //如果标签不为空 则直接返回true
        if (empty($tagInsertData)) {
            return true;
        }

        //添加标签
        return WechatFansTagMapModel::batchInsert(array_keys(current($tagInsertData)), $tagInsertData);
    }

    /**
     * 递归获取用户
     * @param null $nextOpenId
     * @param bool $isBlack
     * @return array
     * @author 青岛开店星信息技术有限公司.
     */
    private static function recursiveGetWechatUser($nextOpenId = null, bool $isBlack = false): array
    {
        if ($isBlack) {
            $data = OfficialAccountFansHelper::getBlackList($nextOpenId);
        } else {
            $data = OfficialAccountFansHelper::getList($nextOpenId);
        }

        if (is_error($data)) {
            return error($data['message']);
        }

        //获取openid
        $openId = $data['data']['openid'];
        if ($data['total'] >= 1000 && $data['total'] == $data['count']) {
            $recursiveOpenId = self::recursiveGetWechatUser($data['next_openid'], $isBlack);
            $openId = array_merge($openId, $recursiveOpenId);
        }

        return $openId ? array_filter($openId) : [];
    }
}
