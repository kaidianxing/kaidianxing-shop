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

namespace shopstar\models\goods\category;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\constants\log\goods\GoodsLogConstant;

use shopstar\helpers\DateTimeHelper;
use shopstar\models\log\LogModel;

/**
 * This is the model class for table "{{%goods_category}}".
 *
 * @property int $id
 * @property int $sort_by 排序
 * @property int $parent_id 上级分类ID,0为第一级
 * @property int $level 层级
 * @property string $name 分类名称
 * @property string $thumb 分类图片
 * @property int $status 是否开启
 * @property string $created_at 创建时间
 * @property int $is_recommand 是否推荐
 * @property string $advurl 点击图片跳转网址
 * @property string $advimg 分类广告
 */
class GoodsCategoryModel extends BaseActiveRecord
{

    /**
     * 全部
     * @var int
     */
    const STATUS_ALL = null;

    /**
     * 开启
     * @var int
     */
    const STATUS_OPEN = 1;
    /**
     * 关闭
     * @var int
     */
    const STATUS_OFF = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort_by', 'parent_id', 'level', 'status', 'is_recommand'], 'integer'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['thumb', 'advurl', 'advimg'], 'string', 'max' => 191],
        ];
    }

    /**
     * 日志
     * @return string[]
     * @author 青岛开店星信息技术有限公司
     */
    public function logAttributeLabels()
    {
        return [
            'id' => '商品分类id',
            'sort_by' => '权重',
            'name' => '商品分类名称',
            'status' => '商品分类状态',
            'level' => '商品分类等级',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sort_by' => '排序',
            'parent_id' => '上级分类ID,0为第一级',
            'level' => '层级',
            'name' => '分类名称',
            'thumb' => '分类图片',
            'status' => '是否开启',
            'created_at' => '创建时间',
            'is_recommand' => '是否推荐',
            'advurl' => '点击图片跳转网址',
            'advimg' => '分类广告',
        ];
    }

    /**
     * 获取单个分类
     * @param $id
     * @return GoodsCategoryModel|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getOne($id)
    {
        return self::findOne(['id' => $id]);
    }

    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
    }

    /**
     * 搜索分类
     * @param string $keywords
     * @param array $field
     * @param int|null $status
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function search(string $keywords = '', array $field = [], $status = self::STATUS_ALL): ?array
    {
        $field = $field ? $field : 'id,sort_by,parent_id,name,thumb,is_recommand,advurl,advimg,status,created_at';
        if (0 < mb_strlen($keywords)) {
            $param['andFilterWhere'] = [
                [
                    'like', 'name', $keywords
                ],
                [
                    'status' => $status
                ]
            ];
        } else {
            $param['andFilterWhere'] = [
                [
                    'parent_id' => 0
                ],
                [
                    'status' => $status
                ]
            ];
            $param['with'] = ['children' => function ($query) use ($field, $status) {
                $query->andFilterWhere(['status' => $status]);
                $query->select($field);
                $query->orderBy(['sort_by' => SORT_ASC]);
                $query->with([
                    'children' => function ($query) use ($field, $status) {
                        $query->andFilterWhere(['status' => $status]);
                        $query->select($field);
                        $query->orderBy(['sort_by' => SORT_ASC]);
                    }
                ]);
            }];
        }

        $param['select'] = $field;
        $param['orderBy'] = [
            'sort_by' => SORT_ASC
        ];

        return self::getColl($param, [
            'pager' => false,
        ]);
    }

    public static function getById($id, $filed = '')
    {
        $filed = $filed ? $filed : 'id,parent_id,name,thumb,is_recommand,advurl,advimg';
        $param = [];
        $param['id'] = $id;
        $param['status'] = 1;
        return (self::find()
            ->where($param)
            ->select($filed)
            ->first());
    }

    /**
     * 分类的添加\更新操作
     * @param $userId
     * @param $data
     * @param int $parentId
     * @param int $level
     * @return bool|int
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveData($userId, $data, $parentId = 0, $level = 1)
    {
        if (is_numeric($data['id'])) {
            $oneModel = self::findOne($data['id']);
        } else {
            $oneModel = new self;
            $oneModel->created_at = DateTimeHelper::now();
            unset($data['id']);
        }

        $oneModel->setAttributes($data);
        $oneModel->parent_id = $parentId;
        if (!$oneModel->save()) {
            return false;
        }

        $logPrimaryData = $oneModel->getLogAttributeRemark([
            'id' => $oneModel->id,
            'name' => $oneModel->name,
            'sort_by' => $oneModel->sort_by ?: 0,
            'status' => $oneModel->status == 1 ? '启用' : '禁用',
            'level' => $level == 1 ? '一级' : ($level == 2 ? '二级' : '三级')
        ]);

        //添加操作日志
        LogModel::write(
            $userId,
            GoodsLogConstant::GOODS_CATEGORY_SAVE,
            GoodsLogConstant::getText(GoodsLogConstant::GOODS_CATEGORY_SAVE),
            $oneModel->id,
            [
                'log_data' => $oneModel->attributes,
                'log_primary' => $logPrimaryData
            ]
        );

        return $oneModel->id;
    }


}
