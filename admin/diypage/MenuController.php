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

namespace shopstar\admin\diypage;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\diypage\DiypageMenuTypeConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ImageHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\diypage\DiypageMenuModel;

/**
 * 自定义菜单
 * Class MenuController
 * @package shopstar\admin\diypage
 */
class MenuController extends KdxAdminApiController
{

    /**
     * @var array 需要POST请求的Actions
     */
    public $configActions = [
        'postActions' => [
            'delete',
            'change-status',
        ]
    ];

    /**
     * 获取菜单内容
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionGetContent()
    {
        $id = RequestHelper::getInt('id');

        $andWhere = [];
        if (!empty($id)) {
            $andWhere['id'] = $id;
        } else {
            $andWhere['type'] = RequestHelper::getInt('type', DiypageMenuTypeConstant::TYPE_SHOP);
            $andWhere['status'] = 1;
        }

        // 查询菜单内容
        $menu = DiypageMenuModel::find()
            ->where($andWhere)
            ->select([
                'id',
                'content',
                'type',
            ])
            ->asArray()
            ->limit(1)
            ->one();

        if (empty($menu)) {
            return $this->error('底部菜单不存在');
        }

        return $this->result($menu);
    }

    /**
     * 获取菜单列表
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @author likexin
     */
    public function actionList()
    {
        $params = [
            'where' => [],
            'searchs' => [
                ['name', 'like', 'keywords'],
            ],
            'select' => ['id', 'name', 'type', 'status', 'thumb', 'updated_at'],
            'orderBy' => [
                'status' => SORT_DESC,
                'updated_at' => SORT_DESC,
            ],
        ];

        $options = [
            'callable' => function (&$row) {
                $row['type_text'] = DiypageMenuTypeConstant::getTextWithSuffix($row['type'], '菜单');
            }
        ];

        // 查询列表
        $result = DiypageMenuModel::getColl($params, $options);

        // 类型列表
        $result['type_list'] = DiypageMenuTypeConstant::getList('type');

        return $this->result($result);
    }

    /**
     * 添加菜单
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionAdd()
    {
        $now = DateTimeHelper::now();

        $result = DiypageMenuModel::easyAdd([
            'attributes' => [
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'beforeSave' => function (DiypageMenuModel &$model) {
                // 处理图片
                if (!empty($model->thumb)) {
                    $model->thumb = $this->saveThumb($model->thumb);
                }
            },
            'afterSave' => function (DiypageMenuModel $model) {
                // 如果启用，处理其他菜单的关闭
                if (!empty($model->status)) {
                    $this->updateStatus($model->id, $model->type);
                }

                // 清除缓存
                DiypageMenuModel::clearCacheMenu($model->id, $model->type, $model->status == 1);
            },
        ]);

        return $this->result($result);
    }

    /**
     * 编辑菜单
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionEdit()
    {
        $result = DiypageMenuModel::easyEdit([
            'attributes' => [
                'updated_at' => DateTimeHelper::now(),
            ],
            'beforeSave' => function (DiypageMenuModel &$model) {
                // 处理图片
                if (!empty($model->thumb)) {
                    $model->thumb = $this->saveThumb($model->thumb);
                }
            },
            'afterSave' => function (DiypageMenuModel $model) {
                // 如果启用，处理其他菜单的关闭
                if (!empty($model->status)) {
                    $this->updateStatus($model->id, $model->type);
                }

                // 清除缓存
                DiypageMenuModel::clearCacheMenu($model->id, $model->type, $model->status == 1);
            },
        ]);

        return $this->result($result);
    }

    /**
     * 删除菜单
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author likexin
     */
    public function actionDelete()
    {
        $result = DiypageMenuModel::easyDelete([
            'beforeDelete' => function (DiypageMenuModel $model) {
                if (!empty($model->status)) {
                    return error('应用中菜单不可删除');
                }
            },
            'afterDelete' => function (DiypageMenuModel $model) {
                // 清除缓存
                DiypageMenuModel::clearCacheMenu($model->id, $model->type, $model->status == 1);
            }
        ]);

        return $this->result($result);
    }

    /**
     * 保存缩略图
     * @param string $base64
     * @return string
     * @throws \yii\base\Exception
     * @author likexin
     */
    private function saveThumb(string $base64): string
    {
        if (empty($base64)) {
            return '';
        }

        // 文件存储路径
        $path = SHOP_STAR_PUBLIC_DATA_PATH . '/diypage/menu/thumb_' . md5($base64) . '.jpg';

        // 转存图片
        ImageHelper::createFromBase64($base64, $path);

        return '/data/diypage/menu/thumb_' . md5($base64) . '.jpg';
    }

    /**
     * 将同类型其他启用菜单更新为未启用
     * @param int $id
     * @param int $type
     * @author likexin
     */
    private function updateStatus(int $id, int $type)
    {
        // 将同一类型其他的启用中菜单改为未启用
        DiypageMenuModel::updateAll([
            'status' => 0,
        ], [
            'and',
            [
                'type' => $type,
                'status' => 1,
            ],
            ['<>', 'id', $id],
        ]);
    }

    /**
     * 修改启用状态
     * @return array|int[]|\yii\web\Response
     * @author likexin
     */
    public function actionChangeStatus()
    {
        $result = DiypageMenuModel::easySwitch('status', [
            'value' => 1,
            'afterAction' => function (DiypageMenuModel $model) {
                // 将同一类型其他的启用中菜单改为未启用
                DiypageMenuModel::updateAll([
                    'status' => 0,
                ], [
                    'and',
                    [
                        'type' => $model->type,
                        'status' => 1,
                    ],
                    ['<>', 'id', $model->id],
                ]);

                // 清除缓存
                DiypageMenuModel::clearCacheMenu($model->id, $model->type, $model->status == 1);
            }
        ]);

        return $this->result($result);
    }

}