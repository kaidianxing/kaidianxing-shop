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

namespace shopstar\models\poster;


use shopstar\bases\model\BaseActiveRecord;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\ImageHelper;
use shopstar\models\core\CoreRuleModel;
use shopstar\models\sale\CouponModel;
use shopstar\constants\poster\PosterTypeConstant;

use shopstar\wechat\constants\WechatRuleKeywordConstant;
use shopstar\models\wechat\WechatRuleKeywordModel;
use shopstar\models\wechat\WechatRuleModel;

/**
 * This is the model class for table "{{%poster_profile}}".
 *
 * @property int $id auto increment id
 * @property int $type 海报类型：0自定义页面，10商品海报，20分销海报，30关注海报
 * @property string $name 海报名称
 * @property string $keyword 关键词
 * @property int $scans 扫码数
 * @property int $follows 关注数
 * @property string $thumb 封面图
 * @property string $content 页面内容
 * @property int $visit_page 访问页面 0默认 1商城主页 2分销首页
 * @property int $status 状态：0禁用1启用
 * @property int $is_deleted 是否删除：0否1是
 * @property string $expire_start_time 海报生效开始时间
 * @property string $expire_end_time 海报生效结束时间
 * @property int $expire_time 海报有效期（s）
 * @property int $access_type 获取对象 0不限制 1分销商
 * @property int $template_id 模板ID(通过此模板创建)
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class PosterModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}w
     */
    public static function tableName()
    {
        return '{{%poster}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'scans', 'follows', 'visit_page', 'status', 'is_deleted', 'expire_time', 'access_type', 'template_id'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string'],
            [['expire_start_time', 'expire_end_time', 'created_at', 'updated_at'], 'safe'],
            [['name', 'keyword'], 'string', 'max' => 64],
            [['thumb'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment id',
            'type' => '海报类型：0自定义页面，10商品海报，20分销海报，30关注海报',
            'name' => '海报名称',
            'keyword' => '关键词',
            'scans' => '扫码数',
            'follows' => '关注数',
            'thumb' => '封面图',
            'content' => '页面内容',
            'visit_page' => '访问页面 0默认 1商城主页 2分销首页',
            'status' => '状态：0禁用1启用',
            'is_deleted' => '是否删除：0否1是',
            'expire_start_time' => '海报生效开始时间',
            'expire_end_time' => '海报生效结束时间',
            'expire_time' => '海报有效期（s）',
            'access_type' => '获取对象 0不限制 1分销商 ',
            'template_id' => '模板ID(通过此模板创建)',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 新增海报
     * @param array $params
     * @return array|null|PosterModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function getAddResult(array $params)
    {
        switch ($params['type']) {
            case PosterTypeConstant::POSTER_TYPE_GOODS:
                $result = self::createOrUpdateGoodsPoster($params);
                break;
            case PosterTypeConstant::POSTER_TYPE_COMMISSION:
                $result = self::createOrUpdateCommissionPoster($params);
                break;
            case PosterTypeConstant::POSTER_TYPE_ATTENTION:
                $result = self::createOrUpdateAttentionPoster($params);
                break;
            default:
                return error('海报类型不合法');
        }

        if (is_error($result)) {
            return $result;
        }

        // 如果启用，处理其他页面的关闭
        if (!empty($params['status'])) {
            self::updateStatus($result->id, $params['type']);
        }

        return $result;
    }

    /**
     * 编辑海报
     * @param int $posterId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getEditResult(int $posterId)
    {
        // 获取海报详情
        $poster = PosterModel::findOne(['id' => $posterId, 'is_deleted' => 0]);

        if (empty($poster)) {
            return error('海报不存在');
        }

        $poster = $poster->toArray();

        $returnData = [];
        switch ($poster['type']) {
            case PosterTypeConstant::POSTER_TYPE_GOODS:
                $select = ['id', 'type', 'name', 'thumb', 'content', 'status', 'template_id', 'created_at', 'updated_at'];
                $profile = ArrayHelper::only($poster, $select);
                $returnData['profile'] = $profile;
                break;
            case PosterTypeConstant::POSTER_TYPE_COMMISSION:
                $select = ['id', 'type', 'name', 'thumb', 'content', 'status', 'template_id', 'visit_page', 'created_at', 'updated_at'];
                $profile = ArrayHelper::only($poster, $select);
                $returnData['profile'] = $profile;
                break;
            case PosterTypeConstant::POSTER_TYPE_ATTENTION:
                $select = ['id', 'type', 'name', 'thumb', 'keyword', 'content', 'status', 'template_id', 'expire_start_time', 'expire_end_time', 'expire_time', 'access_type', 'created_at', 'updated_at'];
                $profile = ArrayHelper::only($poster, $select);
                $attentionProfile = PosterAttentionModel::findOne(['poster_id' => $posterId]);
                if (empty($attentionProfile)) {
                    $returnData = [
                        'profile' => $profile,
                        'push' => [],
                        'award' => []
                    ];
                    break;
                }
                $attentionProfile = $attentionProfile->toArray();
                $pushSelect = ['poster_id', 'type', 'title', 'thumb', 'description', 'url', 'url_name'];
                $push = ArrayHelper::only($attentionProfile, $pushSelect);
                $awardSelect = ['status', 'rec_credit_enable', 'rec_cash_enable', 'rec_coupon_enable', 'rec_credit', 'rec_credit_limit', 'rec_cash', 'rec_cash_limit', 'rec_cash_type', 'rec_coupon', 'rec_coupon_limit', 'sub_credit_enable', 'sub_cash_enable', 'sub_coupon_enable', 'sub_credit', 'sub_cash', 'sub_cash_type', 'sub_coupon'];
                $award = ArrayHelper::only($attentionProfile, $awardSelect);
                // 获取优惠券信息
                $couponInfo = CouponModel::getCouponInfo([$award['rec_coupon'], $award['sub_coupon']]);
                if (!empty($couponInfo)) {
                    $couponInfo = array_column($couponInfo, NULL, 'id');
                    $award['rec_coupon'] = [
                        'id' => $couponInfo[$award['rec_coupon']]['id'],
                        'coupon_name' => $couponInfo[$award['rec_coupon']]['coupon_name'],
                        'coupon_sale_type' => $couponInfo[$award['rec_coupon']]['coupon_sale_type'],
                        'content' => $couponInfo[$award['rec_coupon']]['content'],
                        'stock' => $couponInfo[$award['rec_coupon']]['stock'],
                        'stock_type' => $couponInfo[$award['rec_coupon']]['stock_type'],
                    ];
                    $award['sub_coupon'] = [
                        'id' => $couponInfo[$award['sub_coupon']]['id'],
                        'coupon_name' => $couponInfo[$award['sub_coupon']]['coupon_name'],
                        'coupon_sale_type' => $couponInfo[$award['sub_coupon']]['coupon_sale_type'],
                        'content' => $couponInfo[$award['sub_coupon']]['content'],
                        'stock' => $couponInfo[$award['sub_coupon']]['stock'],
                        'stock_type' => $couponInfo[$award['sub_coupon']]['stock_type'],

                    ];
                }
                $returnData = [
                    'profile' => $profile,
                    'push' => $push,
                    'award' => $award
                ];
                break;
            default:
                return error('海报类型不合法');
        }

        return $returnData;
    }

    /**
     * 保存海报
     * @param array $params
     * @return array|null|PosterModel
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function getSaveResult(array $params)
    {
        switch ($params['type']) {
            case PosterTypeConstant::POSTER_TYPE_GOODS:
                $result = self::createOrUpdateGoodsPoster($params);
                break;
            case PosterTypeConstant::POSTER_TYPE_COMMISSION:
                $result = self::createOrUpdateCommissionPoster($params);
                break;
            case PosterTypeConstant::POSTER_TYPE_ATTENTION:
                $result = self::createOrUpdateAttentionPoster($params);

                break;
            default:
                return error('海报类型不合法');
        }

        if (is_error($result)) {
            return $result;
        }

        // 如果启用，处理其他页面的关闭
        if (!empty($params['status'])) {
            self::updateStatus($result->id, $params['type']);
        }

        return $result;
    }

    /**
     * 更新相同类型海报其他海报的启用状态
     * @param int $id
     * @param int $type
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateStatus(int $id, int $type)
    {
        self::updateAll(['status' => 0], [
            'and',
            [
                'type' => $type,
                'status' => 1,
            ],
            ['<>', 'id', $id],
        ]);
        return true;
    }

    /**
     * 删除海报
     * @param PosterModel $poster
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function deletePoster($poster)
    {
        $trans = \Yii::$app->db->beginTransaction();

        try {
            // 修改海报状态
            $poster->is_deleted = 1;

            $poster->save();

            if ($poster->type == PosterTypeConstant::POSTER_TYPE_ATTENTION) {
                // 删除关键词
                CoreRuleModel::deleteAll([
                    'rule_id' => $poster->id,
                    'type' => 'poster'
                ]);

            }

            $trans->commit();

        } catch (\Throwable $e) {

            $trans->rollBack();

            return error($e->getMessage());
        }

        return true;
    }

    /**
     * 创建或更新商品海报
     * @param array $params
     * @return array|null|PosterModel|static
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    private static function createOrUpdateGoodsPoster(array $params)
    {
        $attr = [
            'type' => $params['type'],
            'name' => $params['name'],
            'thumb' => self::saveThumb($params['thumb']),
            'content' => $params['content'],
            'status' => $params['status'],
            'template_id' => $params['template_id']
        ];

        // 获取海报实体
        if (isset($params['id'])) {
            $poster = PosterModel::findOne(['id' => $params['id']]);
        } else {
            $poster = new self();
        }

        try {

            $poster->setAttributes($attr);

            $poster->save();

        } catch (\Throwable $e) {
            return error('商品海报保存失败:' . $e->getMessage());
        }

        return $poster;
    }

    /**
     * 创建或更新分销海报
     * @param array $params
     * @return array|null|PosterModel|static
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    private static function createOrUpdateCommissionPoster(array $params)
    {
        $attr = [
            'type' => $params['type'],
            'name' => $params['name'],
            'thumb' => self::saveThumb($params['thumb']),
            'content' => $params['content'],
            'status' => $params['status'],
            'visit_page' => $params['visit_page'],
            'template_id' => $params['template_id']
        ];

        // 获取海报实体
        if (isset($params['id'])) {
            $poster = PosterModel::findOne(['id' => $params['id']]);
        } else {
            $poster = new self();
        }

        try {

            $poster->setAttributes($attr);

            $poster->save();

        } catch (\Throwable $e) {
            return error('分销海报保存失败:' . $e->getMessage());
        }

        return $poster;
    }

    /**
     * 创建或更新关注海报
     * @param array $params
     * @return array|null|PosterModel|static
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    private static function createOrUpdateAttentionPoster(array $params)
    {
        $trans = \Yii::$app->db->beginTransaction();
        try {
            /***** 插入poster表 *****/
            $attr = [
                'type' => $params['type'],
                'name' => $params['name'],
                'thumb' => self::saveThumb($params['thumb']),
                'content' => $params['content'],
                'status' => $params['status'],
                'template_id' => $params['template_id'],
                'keyword' => $params['keyword'],
                'expire_start_time' => $params['expire_start_time'],
                'expire_end_time' => $params['expire_end_time'],
                'expire_time' => $params['expire_time'],
                'access_type' => $params['access_type']
            ];

            // 获取海报实体
            if (isset($params['id'])) {
                $poster = PosterModel::findOne(['id' => $params['id']]);
            } else {
                $poster = new self();
            }
            $poster->setAttributes($attr);
            // 保存主表
            $poster->save();
            // 获取主键ID
            $posterId = $poster->id;
            /***** 插入poster_attention表 *****/
            $attentionAttr = [
                'poster_id' => $posterId
            ];
            // 推送封面转图片
            $params['push']['thumb'] = self::saveThumb($params['push']['thumb']);
            $attentionAttr = array_merge($attentionAttr, $params['push'], $params['award']);
            // 获取海报实体
            if (isset($params['id'])) {
                $posterAtten = PosterAttentionModel::findOne(['poster_id' => $params['id']]);
            } else {
                $posterAtten = new PosterAttentionModel();
            }
            $posterAtten->setAttributes($attentionAttr);
            if (!$posterAtten->save()) {
                throw new \Exception($posterAtten->getErrorMessage());
            }
            // 独立版添加关键词
            self::insertKeywordRuleIndependent($posterId, $params['keyword']);
            // 修改qrcode关键词
            if (!empty($params['id'])) {
                self::updateQrCodeKeyword($params['id'], $params['keyword']);
            }
        } catch (\Throwable $e) {
            $trans->rollBack();
            return error('关注海报保存失败:' . $e->getMessage());
        }

        $trans->commit();

        return $poster;

    }

    /**
     * base64ToImage
     * @param string $base64
     * @return string
     * @throws \yii\base\Exception
     * @author 青岛开店星信息技术有限公司
     */
    private static function saveThumb(string $base64)
    {
        if (empty($base64)) {
            return '';
        }

        if (!preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            return $base64;
        }

        // 文件存储路径
        $path = SHOP_STAR_PUBLIC_DATA_PATH . '/poster/page/thumb_' . md5($base64) . '.jpg';

        // 转存图片
        ImageHelper::createFromBase64($base64, $path);

        return '/data/poster/page/thumb_' . md5($base64) . '.jpg';
    }

    /**
     * 插入关键词
     * @param $posterId
     * @param $keyword
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    private static function insertKeywordRule($posterId, $keyword)
    {

        /*****  *****/
        // 海报
        CoreRuleModel::createOrUpdateRule($posterId, $keyword, 'poster');
        return true;
    }

    /**
     * 更新qr关键词
     * @param $posterId
     * @param $keyword
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    private static function updateQrCodeKeyword($posterId, $keyword)
    {
        // 检查qr表是否创建二维码,没有创建则不需修改关键词
        $posterQr = PosterQrModel::find()->where(['poster_id' => $posterId])->first();

        if (empty($posterQr)) {
            return true;
        }

        return true;
    }

    /**
     * 保存关键词
     * @param $posterId
     * @param $keyword
     * @throws \shopstar\exceptions\wechat\WechatException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     * @return bool
     */
    private static function insertKeywordRuleIndependent($posterId, $keyword)
    {
        $name = 'kdx_shop:poster:' . $posterId;
        $model = WechatRuleModel::findOne(['name' => $name, 'module' => 'poster']);
        if (!$model) {
            $model = new WechatRuleModel();
            $model->setAttributes([
                'unionid' => 0,
                'name' => $name,
                'module' => 'poster',
                'event' => 'SCAN',
                'event_key' => (string)$posterId,
                'containtype' => '',
            ]);
            $model->save();
        }

        WechatRuleKeywordModel::createOrUpdateRuleKeyword($keyword, $model->id);

        // 海报
        CoreRuleModel::createOrUpdateRule($posterId, $keyword, 'poster');

        return true;
    }


}