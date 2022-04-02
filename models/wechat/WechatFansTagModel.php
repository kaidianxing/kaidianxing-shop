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


/**
 * This is the model class for table "{{%wechat_fans_tag}}".
 *
 * @property int $id
 * @property int $wechat_tag_id 微信粉丝id
 * @property string $tag_name 标题
 * @property string $created_at 创建时间
 */
class WechatFansTagModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_fans_tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['wechat_tag_id'], 'required'],
            [['wechat_tag_id'], 'integer'],
            [['created_at'], 'safe'],
            [['tag_name'], 'string', 'max' => 255],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'wechat_tag_id' => '微信粉丝标签id',
            'tag_name' => '标题',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 同步
     * @param array $options
     * @return bool
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司.
     */
    public static function sync(array $options = []): bool
    {
        $options = array_merge([
        ], $options);

        //删除以前的粉丝标签
        self::deleteAll();

        // 当前时间
        $time = DateTimeHelper::now();


        //获取粉丝信息
        $tagList = OfficialAccountFansHelper::getTagList();

        if (empty($tagList)) {
            return true;
        }

        $insertData = [];

        foreach ($tagList['tags'] as $tagInfo) {
            $insertData[] = [
                'wechat_tag_id' => (string)$tagInfo['id'],
                'tag_name' => (string)$tagInfo['name'],
                'created_at' => $time,
            ];
        }

        //批量插入
        return self::batchInsert(array_keys(current($insertData)), $insertData);
    }
}