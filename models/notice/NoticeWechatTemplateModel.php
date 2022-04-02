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
use shopstar\exceptions\sysset\NoticeException;
use shopstar\helpers\RequestHelper;


/**
 * This is the model class for table "{{%notice_customer_template}}".
 *
 * @property int $id
 * @property string $title 模板名称
 * @property string $scene_code 模板对应消息接口
 * @property string $template_code 模板消息code
 * @property string $template_id 模板消息id
 * @property string $created_at 创建时间
 * @property string $content 内容
 */
class NoticeWechatTemplateModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%notice_wechat_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['content'], 'string'],
            [['title', 'template_code', 'template_id'], 'string', 'max' => 191],
            [['scene_code'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '模板名称',
            'scene_code' => '模板对应消息接口',
            'template_code' => '模板消息code',
            'template_id' => '模板消息id',
            'created_at' => '创建时间',
            'content' => '内容',
        ];
    }

    /**
     * 保存自定义消息
     * @param int $id
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveCustomerNotice(int $id = 0)
    {
        $post = RequestHelper::post();

        if (empty($id)) {
            $detail = new self();
        } else {
            $detail = self::findOne(['id' => $id]);
            if (empty($detail)) {
                return error('消息模板不存在');
            }
        }

        $detail->title = $post['title'];
        $detail->type_group = $post['type_group'];
        $detail->type_code = $post['type_code'];
        $detail->push_type = $post['push_type'];
        $detail->customer_text = $post['customer_text'];
        $detail->template_id = $post['template_id'];
        $detail->message_title = $post['message_title'];
        $detail->message_title_color = $post['message_title_color'];
        $detail->message_remark = $post['message_remark'];
        $detail->message_remark_color = $post['message_remark_color'];
        $detail->data = serialize($post['data']);

        if ($detail->save() === false) {
            return error($detail->getErrorMessage());
        }
        return true;
    }

    /**
     * 获取模板消息列表
     * @param string $typeCode
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getTemplateList(string $typeCode)
    {
        $list = self::find()
            ->select('id, title, type_code')
            ->where(['type_group' => $typeCode])
            ->get();

        $templateGroup = [];
        // 组装模板消息
        foreach ($list as $item) {
            $templateGroup[$item['type_code']][] = $item;
        }

        return $templateGroup;
    }
}
