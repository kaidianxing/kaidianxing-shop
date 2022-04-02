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
use shopstar\exceptions\wechat\WechatException;

/**
 * This is the model class for table "{{%wechat_rule_text_reply}}".
 *
 * @property int $id
 * @property int $rule_id 规则id
 * @property string $content 回复内容
 */
class WechatRuleTextReplyModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_rule_text_reply}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rule_id'], 'integer'],
            [['content'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rule_id' => '规则id',
            'content' => '回复内容',
        ];
    }

    /**
     * 保存数据
     * @param array $params
     * @param int $ruleId
     * @return bool
     * @throws \yii\db\Exception
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function addData(array $params, int $ruleId)
    {
        foreach ($params as $value) {
            if ($value['containtype'] == 'images') {
                continue;
            }
            $data[] = [
                'rule_id' => $ruleId,
                'content' => $value['content'],
            ];
        }
        $result = self::batchInsert(array_keys($data[0]), $data);
        if (!$result) {
            throw new WechatException(WechatException::SAVE_FAILURE_ERROR);
        }
        return true;
    }

    /**
     * 获取数量
     * @param int $ruleId
     * @return int|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCount(int $ruleId)
    {
        return self::find()->where(['rule_id' => $ruleId])->count('id');
    }

    /**
     * 更新回复文本数据
     * @param array $params
     * @return bool
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateData(array $params, int $ruleId, $flag = '')
    {
        foreach ($params as $value) {
            if ($value['containtype'] == 'images') {
                continue;
            }
            $info = self::findOne(['id' => $value['id'], 'rule_id' => $ruleId]);
            if (!$info) {
                $info = new self();
            }
            $info->setAttributes([
                'content' => $value['content'],
                'rule_id' => $ruleId,
            ]);
            if (!$info->save()) {
                throw new WechatException(WechatException::UPDATE_FAILURE_ERROR);
            }
            $modelIds[] = $info->id;
        }
        self::deleteAll([
            'and',
            ['rule_id' => $ruleId],
            ['not in', 'id', $modelIds]
        ]);

        return true;
    }

    /**
     * 根据rule_id查询下面的content
     * @param int $ruleId
     * @param string $count
     * @return array|\yii\db\ActiveRecord[]|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getinfoByRuleId($ruleId, $type, $count = '')
    {
        $query = self::find()->where(['rule_id' => $ruleId])->select(['content']);
        if ($count == 'all') {
            $result = $query->asArray()->all();
            foreach ($result as &$value) {
                $value['type'] = $type;
            }
        } else {
            $result = $query->first();
            $result['type'] = $type;
        }
        return $result;
    }
}