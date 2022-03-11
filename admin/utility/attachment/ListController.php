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
use shopstar\helpers\RequestHelper;
use shopstar\helpers\VideoHelper;
use shopstar\models\core\attachment\CoreAttachmentGroupModel;
use shopstar\models\core\attachment\CoreAttachmentModel;
use shopstar\services\core\attachment\CoreAttachmentService;
use yii\helpers\Json;

/**
 * 附件管理
 * Class ListController
 * @package modules\utility\manage
 */
class ListController extends KdxAdminUtilityController
{

    /**
     * @var array 需要POST的Action
     */
    public $configActions = [
       'postActions' => [
            'upload',
            'delete',
        ]
    ];

    /**
     * 获取设置
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGetSettings()
    {
        return $this->result([
            'settings' => CoreAttachmentService::getAttachmentSettings(),
        ]);
    }

    /**
     * 获取列表
     * @param int $type
     * @return array|\yii\web\Response
     * @author likexin
     */
    public function actionGetList()
    {
        // 附件类型
        $type = RequestHelper::getInt('type');
        if (empty($type)) {
            return $this->error('附件类型错误');
        }

        $params = [
            'searchs' => [
                [['name'], 'like', 'keywords'],
                ['created_at', 'between'],
                ['group_id', 'int'],
            ],
            'where' => [
                'and', [
                    'type' => $type,
                    'scene' => $this->attachmentScene,
                ],
            ],
            'select' => ['id', 'name', 'ext', 'path', 'size', 'extend', 'created_at'],
            'orderBy' => [
                'created_at' => SORT_DESC,
                'id' => SORT_DESC,
            ],
        ];

        $options = [
            'callable' => function (&$row) {
                $row['extend'] = empty($row['extend']) ? null : Json::decode($row['extend']);
            }
        ];

        // 获取列表
        $result = CoreAttachmentModel::getColl($params, $options);

        return $this->result($result);
    }

    /**
     * 上传附件
     * @return array|\yii\web\Response
     * @throws \ReflectionException
     * @throws \yii\base\Exception
     * @author likexin
     */
    public function actionUpload()
    {
        // 附件类型
        $type = RequestHelper::postInt('type');
        if (empty($type)) {
            return $this->error('附件类型错误');
        }

        // 上传参数
        $params = [
            'type' => $type,
            'group_id' => RequestHelper::postInt('group_id'),
            'account_id' => $this->userId,
            'remote' => RequestHelper::post('remote'),
            'save_databases' => (bool)RequestHelper::postInt('save_databases', 1),
        ];
        // 当前店铺ID
        $params['isShop'] = true;
        // 执行上传 为了适配只能写死0
        $result = CoreAttachmentService::upload($params, $this->attachmentScene[0]);

        return $this->result($result);
    }

    /**
     * 删除附件
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author likexin
     */
    public function actionDelete()
    {
        $andWhere = [
            'and',
            ['scene' => $this->attachmentScene],
        ];

        // 执行删除
        $result = CoreAttachmentModel::easyDelete([
            'andWhere' => $andWhere,
            'afterDelete' => function (CoreAttachmentModel $model) {
                // 删除文件时对应分组数量-1
                if (!empty($model->group_id)) {
                    CoreAttachmentGroupModel::updateAllCounters(['total' => -1], [
                        'and',
                        ['id' => $model->group_id],
                        ['>', 'total', 0],
                    ]);
                }

                // 删除文件
                CoreAttachmentService::remove($model->path);
            },
        ]);

        return $this->result($result);
    }

    /**
     * 提取tx视频
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetTencentVideo()
    {
        $url = RequestHelper::post('url');
        $url = trim($url);
        // 提取tx视频
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            // 判断是否有path路径
            $filter = parse_url($url);
            if (!strpos($url, '.html')) {
                return $this->error('地址不正确');
            }
            if ($filter['path'] == '/' || !isset($filter['path']) || empty($filter['path'])) {
                return $this->error('地址不正确');
            }
            $result = VideoHelper::getTententVideo($url);
        } else {
            // 判断是否含有html标签
            if ($url == strip_tags($url)) {
                return $this->error('地址不正确');
            }
            $result = VideoHelper::parseRichTextTententVideo($url);
        }
        if (!$result) {
            return $this->error();
        }
        return $this->success(['data' => $result]);
    }

}