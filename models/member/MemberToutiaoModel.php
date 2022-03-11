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

use shopstar\exceptions\member\MemberException;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%member_toutiao}}".
 *
 * @property string $id
 * @property int $member_id 会员id
 * @property string $openid openid
 * @property string $nickname 用户昵称
 * @property string $avatar 头像
 * @property int $is_deleted 是否删除
 */
class MemberToutiaoModel extends \shopstar\bases\model\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member_toutiao}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'is_deleted'], 'integer'],
            [['member_id'], 'required'],
            [['openid'], 'string', 'max' => 50],
            [['nickname', 'avatar'], 'string', 'max' => 191],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'openid' => 'openid',
            'nickname' => '用户昵称',
            'avatar' => '头像',
            'is_deleted' => '是否删除',
        ];
    }

    /**
     * 检查用户是否存在
     * 不存在则新建
     * @param array $byteDanceMember
     * @param int $clientType
     * @return array|int
     * @throws MemberException
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkMember(array $byteDanceMember, int $clientType)
    {
        $model = self::findOne(['openid' => $byteDanceMember['openId'], 'is_deleted' => 0]);
        // 注册过
        if (!empty($model)) {
            $member = MemberModel::findOne(['id' => $model->member_id]);
            if (!empty($model) && $member->is_black) {
                throw new MemberException(MemberException::MEMBER_BYTE_DANCE_LOGIN_IS_BLACK_ERROR);
            }
        } else {
            $model = new self();
        }

        // 保存会员主体信息
        $memberInfo = MemberModel::saveMember([
            'id' => $member->id ?? 0,
            'avatar' => $byteDanceMember['avatarUrl'],
            'nickname' => $byteDanceMember['nickName'],
            'province' => $byteDanceMember['province'],
            'city' => $byteDanceMember['city'],
            'source' => $clientType,
        ], true);
        if (is_error($memberInfo)) {
            throw new MemberException(MemberException::MEMBER_BYTE_DANCE_SUBJECT_ACCOUNT_CREATE_ERROR, $memberInfo['message']);
        }

        //保存小程序会员
        $model->setAttributes([
            'member_id' => $memberInfo['id'],
            'openid' => $byteDanceMember['openId'],
            'avatar' => $byteDanceMember['avatarUrl'],
            'nickname' => $byteDanceMember['nickName'],
        ]);
        if ($model->save() === false) {
            throw new MemberException(MemberException::MEMBER_BYTE_DANCE_ACCOUNT_CREATE_ERROR);
        }
        return $memberInfo;
    }

    /**
     * 获取抖音用户详细信息
     * @param string $encryptedData
     * @param string $session_key
     * @param string $iv
     * @param string $rawData
     * @param string $signature
     * @return bool|mixed
     * @author 青岛开店星信息技术有限公司
     * @func getUserInfo
     */
    public static function getUserInfo(string $encryptedData, string $session_key, string $iv, string $rawData, string $signature)
    {
        $key = base64_decode($session_key);
        $iv = base64_decode($iv);
        $plaintext = openssl_decrypt(base64_decode($encryptedData), 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        // trim pkcs#7 padding
        $pad = ord(substr($plaintext, -1));
        $pad = $pad < 1 || $pad > 32 ? 0 : $pad;
        $plaintext = substr($plaintext, 0, strlen($plaintext) - $pad);
        if (self::checkSignature($rawData, $session_key, $signature)) {
            return Json::decode($plaintext);
        }
        return false;
    }

    /**
     * 抖音验证签名
     * @param $rawData
     * @param $session_key
     * @param $signature
     * @return bool
     * @author 青岛开店星信息技术有限公司
     * @func checkSignature
     */
    private static function checkSignature($rawData, $session_key, $signature)
    {
        return hash_equals(sha1($rawData . $session_key), $signature);
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