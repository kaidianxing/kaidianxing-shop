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

namespace shopstar\admin\utility\attachment;

use shopstar\bases\KdxAdminUtilityController;
use shopstar\helpers\ArrayHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\core\attachment\CoreAttachmentGroupModel;
use shopstar\models\core\attachment\CoreAttachmentModel;

/**
 * 附件分组
 * Class GroupController
 * @package shopstar\admin\utility\attachment
 * @author 青岛开店星信息技术有限公司
 */
class GroupController extends KdxAdminUtilityController
{

    /**
     * @var array 需要POST请求的Action
     */
    public $configActions = [
        'postActions' => [
            'add',
            'edit',
        ]
    ];

    /**
     * 获取默认分组信息
     * @param int $type
     * @return array
     * @author likexin
     */
    private function getDefaultGroup(int $type): array
    {
        $andWhere = [
            'scene' => $this->attachmentScene,
            'type' => $type,
        ];

        // 查询所有的附件
        $total = CoreAttachmentModel::find()
            ->andWhere($andWhere)
            ->count();

        // 查询未分组的附件数量
        $noGroup = CoreAttachmentModel::find()
            ->where([
                'group_id' => 0
            ])
            ->andWhere($andWhere)
            ->count();

        // 查询商品助手的附件数量
        $goodsHelperGroup = CoreAttachmentModel::find()
            ->where([
                'group_id' => -1
            ])
            ->andWhere($andWhere)
            ->count();

        // 查询评价助手的附件数量
        $commentHelperGroup = CoreAttachmentModel::find()
            ->where([
                'group_id' => -2
            ])
            ->andWhere($andWhere)
            ->count();

        return [
            [
                'id' => '',
                'name' => '全部',
                'total' => $total,
            ],
            [
                'id' => '0',
                'name' => '默认分组',
                'total' => $noGroup,
            ],
            [
                'id' => '-1',
                'name' => '商品助手',
                'total' => $goodsHelperGroup,
            ],
            [
                'id' => '-2',
                'name' => '评价助手',
                'total' => $commentHelperGroup,
            ],
        ];
    }

    /**
     * 获取列表
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGetList()
    {
        $type = RequestHelper::getInt('type');
        if (empty($type)) {
            return $this->error('附件类型错误');
        }

        $params = [
            'where' => [
                'and',
                [
                    'type' => $type,
                    'scene' => $this->attachmentScene,
                ],
            ],
            'select' => ['id', 'name', 'total'],
            'orderBy' => [
                'created_at' => SORT_DESC,
            ],
        ];

        // 读取列表
        $result = CoreAttachmentGroupModel::getColl($params, [
            'pager' => false,
        ]);

        $result['list'] = ArrayHelper::merge($this->getDefaultGroup($type), $result['list']);

        return $this->result($result);
    }

    /**
     * 添加
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionAdd()
    {
        $type = RequestHelper::postInt('type');
        if (empty($type)) {
            return $this->error('附件类型错误');
        }

        $attributes = [
            'type' => $type,
            'scene' => $this->attachmentScene[0],
            'created_at' => DateTimeHelper::now(),
        ];

        // 执行添加
        $result = CoreAttachmentGroupModel::easyAdd([
            'attributes' => $attributes,
        ]);

        return $this->result($result);
    }

    /**
     * 编辑
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionEdit()
    {
        // 过滤上传场景
        $andWhere = [
            'scene' => $this->attachmentScene,
        ];

        $result = CoreAttachmentGroupModel::easyEdit([
            'andWhere' => $andWhere,
        ]);

        return $this->result($result);
    }

    /**
     * 删除
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author likexin
     */
    public function actionDelete()
    {
        // 过滤上传场景
        $andWhere = [
            'scene' => $this->attachmentScene,
        ];

        // 执行删除
        $result = CoreAttachmentGroupModel::easyDelete([
            'andWhere' => $andWhere,
            'afterDelete' => function (CoreAttachmentGroupModel $model) {
                // 将当前分组文件置为未分组
                CoreAttachmentModel::updateAll([
                    'group_id' => 0,
                ], [
                    'group_id' => $model->id,
                ]);
            }
        ]);

        return $this->result($result);
    }

}