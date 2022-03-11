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

namespace shopstar\models\sysset;

use shopstar\bases\model\BaseActiveRecord;



/**
 * This is the model class for table "{{%notice}}".
 *
 * @property int $id 主键
 * @property int $sort_by 排序
 * @property string $title 名称
 * @property string $link 链接
 * @property string $detail 详细
 * @property int $status 状态
 * @property int $show_type 展示方式 0 内容  1跳转链接
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class NoticeModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%notice}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort_by', 'status', 'show_type'], 'integer'],
            [['detail'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'link'], 'string', 'max' => 255],
        ];
    }

    public function logAttributeLabels()
    {
        return [
            'id' => '主键',
            'sort_by' => '排序',
            'title' => '名称',
            'link' => '链接',
            'detail' => '详细',
            'status' => '状态',
            'show_type' => '展示方式',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'sort_by' => '排序',
            'title' => '名称',
            'link' => '链接',
            'detail' => '详细',
            'status' => '状态',
            'show_type' => '展示方式 0 内容  1跳转链接',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 保存公告
     * @param array $post
     * @return array|bool
     * @author 青岛开店星信息技术有限公司
     */
    public function saveNotice(array $post)
    {
        // 标题不能为空
        if (empty($post['title'])) {
            return error('标题不能为空');
        }
        // 显示类型 1链接
        if ($post['show_type'] == 1 && empty($post['link'])) {
            return error('链接不能为空');
        } else if ($post['show_type'] == 0 && empty($post['detail'])) {
            // 内容
            return error('内容不能为空');
        }
        if (empty($post['id'])) {
            $notice = new self();
        } else {
            $notice = self::findOne(['id' => $post['id']]);
            if (empty($notice)) {
                return error('公告不存在');
            }
        }
        $notice->setAttributes($post);
        if ($notice->save() === false) {
            return error('保存失败,' . $notice->getErrorMessage());
        }

        return true;
    }
}