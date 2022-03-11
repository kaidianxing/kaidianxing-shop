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

namespace shopstar\models\notice;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\notice\NoticeComponent;
use shopstar\constants\SyssetTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;

use yii\helpers\Json;

/**
 * This is the model class for table "{{%sms}}".
 *
 * @property int $id
 * @property string $name 模板名称
 * @property string $type 类型
 * @property int $is_template 是否模版 0:否;1:是;
 * @property string $sms_tpl_id sms模板ID 服务商提供
 * @property string $content 短信内容
 * @property string $data 模板数据
 * @property int $state 状态 0:禁用;1:启用;
 * @property string $sms_sign 短信签名
 * @property string $scene_desc 场景描述
 * @property string $scene_code 场景值
 * @property string $audit 审核状态 0待审核 1通过 2拒绝
 * @property string $created_at 创建时间
 * @property string $remark 备注
 */
class NoticeSmsTemplateModel extends BaseActiveRecord
{
    /**
     * 类型名称映射
     * @var array
     */
    public static $typeName = [
        'aliyun' => '阿里云',
        'emay' => '亿美',
        'juhe' => '聚合'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_notice_sms_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_template', 'state', 'audit'], 'integer'],
            [['data'], 'string'],
            [['name', 'sms_tpl_id', 'content', 'sms_sign', 'scene_code', 'remark'], 'string', 'max' => 191],
            [['type'], 'string', 'max' => 191],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '模板名称',
            'type' => '类型',
            'is_template' => '是否模版 0:否;1:是;',
            'sms_tpl_id' => 'sms模板ID 服务商提供',
            'content' => '短信内容',
            'data' => '模板数据',
            'state' => '状态 0:禁用;1:启用;',
            'sms_sign' => '短信签名',
            'scene_code' => '场景值',
            'audit' => '审核状态 0待审核 1通过 2拒绝',
            'created_at' => '创建时间',
            'remark' => '备注',
        ];
    }

    /**
     * 保存短信消息模板
     * @param int $id
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveSms(int $id = 0)
    {
        $post = RequestHelper::post();

        if (empty($id)) {
            $sms = new self();
            $sms->created_at = DateTimeHelper::now();
        } else {
            $sms = self::findOne(['id' => $id]);
            if (empty($sms)) {
                return error('模板不存在');
            }
        }

        // 编辑不允许修改类型
        if (empty($id)) {
            $sms->type = $post['type'];
        }

        $sms->setAttributes([
            'name' => $post['name'],
            'sms_tpl_id' => $post['sms_tpl_id'] ?: '',
            'is_template' => $post['is_template'] ?: 0,
            'content' => $post['content'] ?: '',
            'state' => $post['state'] ?: 0,
            'audit' => 1,
            'sms_sign' => $post['sms_sign'] ?: '',
            'scene_code' => $post['scene_code'],
            'data' => Json::encode($post['data']),
        ]);

        if (!$sms->save()) {
            return error($sms->getErrorMessage());
        }
        return $sms;
    }

    /**
     * 短信模板
     * @param array $smsSet
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAllTemplate(array $smsSet)
    {
        // 只获取开启的模板消息列表
        $type = [];
        if (!empty($smsSet['juhe'])) {
            $type[] = 'juhe';
        }
        if (!empty($smsSet['emay'])) {
            $type[] = 'emay';
        }
        if (!empty($smsSet['aliyun'])) {
            $type[] = 'aliyun';
        }
        $smsTemplate = self::find()
            ->select('id, type, name')
            ->where(['and', ['state' => SyssetTypeConstant::SMS_STATE_OPEN], ['in', 'type', $type]])
            ->get();
        if (!empty($smsTemplate)) {
            foreach ($smsTemplate as $key => $value) {
                $smsTemplate[$key]['name'] = '[' . self::$typeName[$value['type']] . ']' . $value['name'];
            }
        }
        return $smsTemplate;
    }

    /**
     * 发送验证码
     * @param $type
     * @param $mobile
     * @return bool
     * @throws \Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendSms($type, $mobile)
    {
        $code = random_int(100000, 999999);

        $result = NoticeComponent::getInstance($type, ['code' => $code]);
        if (!is_error($result)) {
            $result->sendVerifyCode($mobile);
        }

        return true;
    }
}
