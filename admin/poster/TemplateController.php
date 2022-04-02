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

namespace shopstar\admin\poster;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\poster\PosterTypeConstant;
use shopstar\helpers\RequestHelper;
use shopstar\models\poster\PosterTemplateModel;

/**
 * 模板
 * Class TemplateController
 * @package shopstar\admin\poster
 */
class TemplateController extends KdxAdminApiController
{

    /**
     * 系统模板
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSystemList()
    {
        return $this->list([
            ['>', 'system_id', 0],
        ]);
    }

    /**
     * 我的模板
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionMyList()
    {
        return $this->list([]);
    }

    /**
     * 获取列表
     * @param array $andWhere
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    private function list(array $andWhere = [])
    {
        $params = [
            'where' => [
                'and',
                ['status' => 1],
            ],
            'andWhere' => $andWhere,
            'searchs' => [
                ['name', 'like', 'keywords'],
                ['type', 'int'],
            ],
            'select' => ['id', 'name', 'type', 'thumb', 'system_id', 'created_at'],
            'orderBy' => [
                'system_id' => SORT_DESC,
                'created_at' => SORT_DESC,
            ],
        ];

        $options = [
            'callable' => function (&$row) {
                $row['type_text'] = PosterTypeConstant::getText($row['type']);
            }
        ];

        $result = PosterTemplateModel::getColl($params, $options);

        return $this->result($result);
    }

    /**
     * 获取模板
     * @return array|\yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGet()
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        // 查询模板
        $template = PosterTemplateModel::find()
            ->where([
                'and',
                ['id' => $id],
                ['>', 'system_id', 0],
                ['status' => 1],
            ])
            ->select(['id', 'name', 'type', 'thumb', 'content', 'system_id'])
            ->first();
        if (empty($template)) {
            return $this->error('模板不存在');
        }

        return $this->result([
            'data' => $template,
        ]);
    }

}