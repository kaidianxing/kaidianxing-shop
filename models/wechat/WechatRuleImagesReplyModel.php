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

use ReflectionException;
use shopstar\bases\model\BaseActiveRecord;
use shopstar\exceptions\wechat\WechatException;
use shopstar\helpers\DateTimeHelper;
use shopstar\constants\wechat\WechatMediaTypeConstant;
use yii\base\InvalidConfigException;
use yii\db\Exception;

/**
 * This is the model class for table "{{%wechat_rule_images_reply}}".
 *
 * @property int $id
 * @property int $rule_id 规则id
 * @property string $path 路径
 * @property string $media_id 图片id
 * @property string $created_at 创建时间
 */
class WechatRuleImagesReplyModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%wechat_rule_images_reply}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['rule_id'], 'integer'],
            [['created_at'], 'safe'],
            [['path', 'media_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'rule_id' => '规则id',
            'path' => '路径',
            'media_id' => '图片id',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 保存数据
     * @param $params
     * @param $ruleId
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     * @throws ReflectionException
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function addData($params, $ruleId): bool
    {
        foreach ($params as $value) {
            if ($value['containtype'] == 'text') {
                continue;
            }

            $result = self::getMediaId($value['attachment_id']);
            $data[] = [
                'rule_id' => $ruleId,
                'path' => $value['content'],
                'media_id' => $result['media_id'],
                'created_at' => DateTimeHelper::now(),
            ];
        }

        $result = self::batchInsert(array_keys($data[0]), $data);

        if (!$result) {
            throw new WechatException(WechatException::SAVE_FAILURE_ERROR);
        }

        return true;
    }

    /**
     * 查询数量
     * @param $ruleId
     * @return int|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCount($ruleId)
    {
        return self::find()->where(['rule_id' => $ruleId])->count('id');
    }

    /**
     * 更新回复内容的图片
     * @param array $params
     * @param int $ruleId
     * @param string $flag
     * @param int $attachmentId
     * @return bool
     * @throws InvalidConfigException
     * @throws ReflectionException
     * @throws WechatException
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateData(array $params, int $ruleId, string $flag = '', int $attachmentId = 0): bool
    {
        $modelIds = [];

        foreach ($params as $value) {
            if ($value['containtype'] == 'text') {
                continue;
            }

            $info = self::findOne(['id' => $value['id'], 'rule_id' => $ruleId]);
            if (!$info) {
                $info = new self();
            }
            $result = self::getMediaId($attachmentId);

            $info->setAttributes([
                'path' => $value['path'] ?? $value['content'],
                'media_id' => $result['media_id'],
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
     * 获取微信端素材id
     * @param int $attachmentId
     * @return array
     * @throws WechatException
     * @throws ReflectionException
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMediaId(int $attachmentId): array
    {
        $result = WechatMaterialModel::upload($attachmentId, WechatMediaTypeConstant::WECHAT_MEDIA_TYPE_IMAGE);

        if (is_error($result)) {
            throw new WechatException(WechatException::IMAGES_UPLOAD_ERROR, $result['message']);
        }

        return $result ?? [];
    }

    /**
     * 获取图片
     * @param int $ruleId
     * @param string $type
     * @param string $count
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getinfoByRuleId(int $ruleId, string $type, string $count = ''): ?array
    {
        $query = self::find()->where(['rule_id' => $ruleId])->select(['media_id', 'path']);

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
