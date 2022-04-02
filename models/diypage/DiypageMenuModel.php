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

namespace shopstar\models\diypage;


use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\diypage\DiypageMenuTypeConstant;
use yii\helpers\Json;

/**
 * 应用-店铺装修-底部导航实体类
 * This is the model class for table "{{%diypage_menu}}".
 *
 * @property int $id
 * @property int $type 类型 0:自定义 10:商城 20:分销
 * @property string $name 导航名称
 * @property string $thumb 缩略图
 * @property string $content 导航内容
 * @property int $status 是否使用 0:否 1:是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class DiypageMenuModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%diypage_menu}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'content', 'thumb'], 'required'],
            [['type', 'status'], 'integer'],
            [['content'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['thumb'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '应用类型 0:商城',
            'name' => '导航名称',
            'thumb' => '缩略图',
            'content' => '导航内容',
            'status' => '是否使用 0:否 1:是',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 获取默认菜单
     * @param int $id
     * @param int $type
     * @param array $options
     * @return array|null
     * @author likexin
     */
    public static function getDefaultMenu(int $id, int $type = DiypageMenuTypeConstant::TYPE_SHOP, array $options = [])
    {
        $options = array_merge([
            'select' => ['id', 'content'],
        ], $options);

        $menu = self::find()
            ->where(!empty($id) ? ['id' => $id] : ['type' => $type, 'status' => 1])
            ->select($options['select'])
            ->first();
        if (empty($menu)) {
            return null;
        }

        if (isset($menu['content'])) {
            $menu['content'] = Json::decode($menu['content']);
        }

        return $menu;
    }

    /**
     * 从缓存中获取菜单
     * @param array $options
     * @return array
     * @author likexin
     */
    public static function getCacheMenu(array $options)
    {
        $options = array_merge([
            'id' => 0,  // 菜单ID
            'type' => DiypageMenuTypeConstant::TYPE_SHOP,   // 菜单类型
        ], $options);

        $cacheKey = self::getCacheMenuKey($options);
        $menu = \Yii::$app->redis->get($cacheKey);
        if (empty($menu)) {
            // 读取mysql获取默认菜单
            $menu = self::getDefaultMenu($options['id'], $options['type']);

            // 写入缓存
            if (!empty($menu)) {
                \Yii::$app->redis->setex($cacheKey, 60 * 2, Json::encode($menu));
            }
        }

        return !is_array($menu) ? Json::decode($menu) : $menu;
    }

    /**
     * 获取缓存菜单Key值
     * @param array $options
     * @return string
     * @author likexin
     */
    private static function getCacheMenuKey(array $options)
    {
        return 'kdx_shop_diypage_menu_' . '_' . (int)$options['id'] . '_' . (int)$options['type'];
    }

    /**
     * 清除缓存菜单
     * @param int $id
     * @param int $type
     * @param bool $isDefault
     * @return void
     * @author likexin
     */
    public static function clearCacheMenu(int $id, int $type, bool $isDefault = false)
    {
        $cacheKey = self::getCacheMenuKey([
            'id' => $id,
            'type' => $type,
        ]);
        $keys = \Yii::$app->redis->keys($cacheKey);
        if (!empty($keys)) {
            \Yii::$app->redis->del(...$keys);
        }

        // 如果是默认删除页面id为0的缓存
        if ($isDefault) {
            $cacheKey = self::getCacheMenuKey([
                'id' => 0,
                'type' => $type,
            ]);

            $keys = \Yii::$app->redis->keys($cacheKey);
            if (!empty($keys)) {
                \Yii::$app->redis->del(...$keys);
            }
        }
    }

}