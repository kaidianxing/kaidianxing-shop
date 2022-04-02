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
use shopstar\constants\diypage\DiypageTypeConstant;
use shopstar\exceptions\diypage\DiypageException;
use shopstar\exceptions\member\MemberException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\ImageHelper;
use shopstar\helpers\VideoHelper;
use shopstar\models\member\group\MemberGroupMapModel;
use shopstar\models\member\MemberWechatModel;
use yii\helpers\Json;

/**
 * 应用-店铺装修-页面实体类
 * This is the model class for table "{{%diypage}}".
 *
 * @property int $id
 * @property int $type 页面类型 0:自定义页面 10:商城首页 11:商品详情 12:会员中心 20:分销首页
 * @property string $name 页面名称
 * @property string $thumb 封面图
 * @property string $common 页面配置
 * @property string $content 页面内容
 * @property int $status 状态 0:未启用 1:启用
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $template_id 模板ID(通过此模板创建)
 */
class DiypageModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%diypage}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'thumb', 'content', 'template_id'], 'required'],
            [['type', 'status', 'template_id'], 'integer'],
            [['common', 'content'], 'string'],
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
            'type' => '页面类型 0:自定义页面 10:商城首页 11:商品详情 12:会员中心 20:分销首页',
            'name' => '页面名称',
            'thumb' => '封面图',
            'common' => '页面配置',
            'content' => '页面内容',
            'status' => '状态 0:未启用 1:启用',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'template_id' => '模板ID(通过此模板创建)',
        ];
    }

    /**
     * 获取业务端列表回调
     * @param int|array $type 页面类型
     * @param array $options 附加选项
     * @return array
     * @author likexin
     */
    public static function getListResult($type, array $options = [])
    {
        $options = array_merge([
            'andWhere' => [],
            'select' => ['id', 'type', 'name', 'thumb', 'status', 'created_at', 'updated_at'],
            'orderBy' => [
                'status' => SORT_DESC,
                'type' => SORT_ASC,
                'updated_at' => SORT_DESC,
            ],
            'pager' => true,
            'onlyList' => false,
        ], $options);

        $params = [
            'where' => [
                'type' => $type,
            ],
            'andWhere' => $options['andWhere'],
            'searchs' => [
                ['name', 'like', 'keywords'],
                ['status', 'int'],
            ],
            'select' => $options['select'],
            'orderBy' => $options['orderBy'],
        ];

        $options = [
            'callable' => function (&$row) {
                $row['status_text'] = !empty($row['status']) ? '应用中' : '未使用';
                // 自定义页面的状态特殊处理
                $row['type'] == 0 ? $row['status_text'] = !empty($row['status']) ? '应用中' : '-' : $row['status_text'];
                $row['type_text'] = DiypageTypeConstant::getMessage($row['type']);
            },
            'pager' => $options['pager'],
            'onlyList' => $options['onlyList'],
        ];

        return DiypageModel::getColl($params, $options);
    }

    /**
     * 获取添加回调
     * @param int $type 页面类型
     * @return array
     * @author likexin
     */
    public static function getAddResult(int $type)
    {
        $now = DateTimeHelper::now();
        return DiypageModel::easyAdd(array(
            'attributes' => array(
                'type' => $type,
                'created_at' => $now,
                'updated_at' => $now,
            ),
            'beforeSave' => function (DiypageModel &$model) {
                // 保存缩略图
                $model->thumb = self::saveThumb($model->thumb);

            },
            'afterSave' => function (DiypageModel $model) {
                // 如果启用，处理其他页面的关闭
                if (!empty($model->status)) {
                    self::updateStatus($model->id, $model->type);
                }

                // 清除缓存
                self::clearCachePage($model->id, $model->type, $model->status == 1);
            },
        ));
    }

    /**
     * 获取编辑回调
     * @param int $type 页面类型
     * @return array
     * @author likexin
     */
    public static function getEditResult(int $type)
    {
        return DiypageModel::easyEdit(array(
            'attributes' => array(
                'updated_at' => DateTimeHelper::now(),
            ),
            'andWhere' => array(
                'type' => $type,
            ),
            'filterPostField' => array('created_at', 'template_id', 'type'),
            'beforeSave' => function (DiypageModel &$model) {
                // 保存缩略图
                $model->thumb = self::saveThumb($model->thumb);

                $content = Json::decode($model->content);
                foreach ($content as &$value) {
                    if ($value['type'] == 'richtext') {
                        $value['params']['content'] = VideoHelper::parseRichTextTententVideo($value['params']['content']);
                    }
                }
                unset($value);
                $model->content = Json::encode($content);
            },
            'afterSave' => function (DiypageModel $model) {
                // 如果启用，处理其他页面的关闭
                if (!empty($model->status)) {
                    self::updateStatus($model->id, $model->type);
                }

                // 清除缓存
                self::clearCachePage($model->id, $model->type, $model->status == 1);
            },
        ));
    }

    /**
     * @param string $base64
     * @return string
     * @throws \yii\base\Exception
     * @author likexin
     */
    private static function saveThumb(string $base64)
    {
        if (empty($base64)) {
            return '';
        }

        // 文件存储路径
        $path = SHOP_STAR_PUBLIC_DATA_PATH . '/diypage/page/thumb_' . md5($base64) . '.jpg';

        // 转存图片
        ImageHelper::createFromBase64($base64, $path);

        return '/data/diypage/page/thumb_' . md5($base64) . '.jpg';
    }

    /**
     * 更新相同页面其他页面的启用状态
     * @param int $id
     * @param int $type
     * @return bool
     * @author likexin
     */
    private static function updateStatus(int $id, int $type)
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
     * 获取修改状态回调
     * @param int $type 页面类型
     * @return array
     * @author likexin
     */
    public static function getChangeStatusResult(int $type)
    {
        return DiypageModel::easySwitch('status', [
            'andWhere' => [
                'type' => $type,
            ],
            'value' => 1,
            'afterAction' => function (DiypageModel $model) {
                // 将同类型的页面取消
                DiypageModel::updateAll([
                    'status' => 0
                ], [
                    'and',
                    [
                        'type' => $model->type,
                        'status' => 1,
                    ],
                    ['<>', 'id', $model->id],
                ]);

                // 清除缓存
                self::clearCachePage($model->id, $model->type, $model->status == 1);
            },
        ]);
    }

    /**
     * 获取删除回调
     * @param int $type 页面类型
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author likexin
     */
    public static function getDeleteResult(int $type)
    {
        return DiypageModel::easyDelete([
            'andWhere' => [
                'type' => $type,
            ],
            'beforeDelete' => function (DiypageModel $model) {
                if (!empty($model->status)) {
                    return error('页面应用中无法删除');
                }
            },
            'afterDelete' => function (DiypageModel $model) {
                // 清除缓存
                self::clearCachePage($model->id, $model->type, $model->status == 1);
            }
        ]);
    }

    /**
     * 获取默认页面
     * @param int $id 页面ID
     * @param int $type 页面类型
     * @param array $options 附加参数
     * @return array
     * @author likexin
     */
    public static function getDefaultPage(int $id, int $type = DiypageTypeConstant::TYPE_HOME, array $options = [])
    {
        $options = array_merge([
            'select' => ['id', 'type', 'common', 'content'],
        ], $options);

        $page = self::find()
            ->where(!empty($id) ? ['id' => $id] : ['type' => $type, 'status' => 1])
            ->select($options['select'])
            ->first();
        if (empty($page)) {
            return null;
        }

        if (isset($page['common'])) {
            $page['common'] = Json::decode($page['common']);
        }
        if (isset($page['content'])) {
            $page['content'] = Json::decode($page['content']);
        }

        return $page;
    }

    /**
     * 获取默认商品装修页面的购买按钮文字
     * @return array
     * @author nizengchao
     */
    public static function getDiyPageBuyButtonText()
    {
        $page = DiypageModel::getDefaultPage(0, DiypageTypeConstant::TYPE_GOODS_DETAIL);

        $data['id'] = 0;//页面id
        $data['text'] = '';//文字
        if ($page) {
            $data['id'] = $page['id'];
            $id = 'detail_navbar';
            foreach ($page['content'] as $item) {
                if ($item['id'] != $id) {
                    continue;
                }
                $data['text'] = $item['params']['textbuy'] ?: '';
                break;
            }
        }

        return $data;
    }

    /**
     * 获取装修页面(客户端)
     * @param array $options
     * @return array
     * @throws MemberException
     * @throws DiypageException
     * @author likexin
     */
    public static function getCachePage(array $options)
    {
        $options = array_merge([
            'id' => 0,  // 页面ID
            'type' => DiypageTypeConstant::TYPE_HOME,   // 页面类型
            'member_id' => 0,   // 会员ID
            'member_level_id' => 0,   // 会员等级ID
        ], $options);

        // 先读取缓存
        $cacheKey = self::getCachePageKey($options);
        $page = \Yii::$app->redis->get($cacheKey);
        if (empty($page)) {
            // 读取mysql获取默认页面
            $page = self::getDefaultPage($options['id'], $options['type']);

            // 写入缓存
            if (!empty($page)) {
                \Yii::$app->redis->setex($cacheKey, 60 * 2, Json::encode($page));
            }
        }

        // 如果页面不为空
        if (!empty($page)) {
            $page = !is_array($page) ? Json::decode($page) : $page;

            // 检测页面访问权限
            self::checkPageLimitAccess($page, $options);

            $page['content'] = (array)$page['content'];

            foreach ($page['content'] as $pageIndex => &$pageItem) {
                //判断是否是关注条 并且是关注后关闭
                if (!empty($options['member_id']) && $pageItem['type'] == 'followbar' && $pageItem['params']['showtype'] == 1) {
                    //获取用户是否关注，如果已关注则直接释放
                    $isFollow = MemberWechatModel::getMemberFollow($options['member_id']);
                    if (!$isFollow) {
                        continue;
                    }

                    unset($page['content'][$pageIndex]);
                }

            }
            unset($pageItem);
        }

        // 返回值
        $result = [
            'page' => $page,
            'menu' => null,
        ];

        // 还是没有页面
        if (empty($page)) {
            return $result;
        }

        // 获取菜单
        if (!empty($page['common']) && isset($page['common']['menu_id'])) {
            $result['menu'] = DiypageMenuModel::getCacheMenu([
                'id' => (int)$page['common']['menu_id'],
                'type' => $options['type'],
            ]);
        }

        //适配网络提取腾讯视频
        if (isset($result['page']['content']) && !empty($result['page']['content'])) {
            foreach ($result['page']['content'] as &$value) {
                if (isset($value['id']) && $value['id'] == 'video' && isset($value['params']['videourl']['type']) && $value['params']['videourl']['type'] == 'network') {
                    $value['params']['videourl']['path'] = VideoHelper::getTententVideo($value['params']['videourl']['video_url']);
                }
            }
        }

        return $result;
    }

    /**
     * 检测页面访问权限
     * @param array $page 页面
     * @param array $options 选项
     * @return bool
     * @throws DiypageException
     * @throws MemberException
     * @author likexin
     */
    private static function checkPageLimitAccess(array $page, array $options)
    {
        // 页面配置参数
        $commonParams = isset($page['common']) && isset($page['common']['params']) ? (array)$page['common']['params'] : [];

        // 判断页面访问权限，默认设置为有权限
        $limitAccess = true;

        // 如果限制等级或者标签组，判断是否登录
        if (!empty($commonParams['limitlevel']) || !empty($commonParams['limitlabel'])) {
            if (empty($options['member_id'])) {
                throw new MemberException(DiypageException::CLIENT_PAGE_ACCESS_NOT_LOGIN);
            }
        }

        // 开启限制访问等级、有设置限制访问等级ID
        if (!empty($commonParams['limitlevel']) && !empty($commonParams['browsepermlevels'])) {
            $limitLevelIds = array_column($commonParams['browsepermlevels'], 'id');
            if (!empty($limitLevelIds) && !in_array($options['member_level_id'], $limitLevelIds)) {
                // 如果当前会员等级不在允许会员等级列表中，访问权限设置false
                $limitAccess = false;
            }
        }


        // 如果当前无权访问、开启限制访问标签、有设置限制访问标签ID
        if ($limitAccess && !empty($commonParams['limitlabel']) && !empty($commonParams['browsepermlabels'])) {
            $limitLabelIds = array_column($commonParams['browsepermlabels'], 'id');
            if (!empty($limitLabelIds)) {
                // 查询会员标签组
                $memberLabelIds = MemberGroupMapModel::getGroupIdByMemberId($options['member_id']);
                if (!empty($memberLabelIds) && array_intersect($memberLabelIds, $limitLabelIds)) {
                    // 判断如果有交集 则 允许访问
                    $limitAccess = true;
                } else {
                    $limitAccess = false;
                }
            }
        }

        if (!$limitAccess) {
            throw new DiypageException(DiypageException::CLIENT_PAGE_ACCESS_LIMIT);
        }
    }

    /**
     * 获取缓存页面Key值
     * @param array $options
     * @return string
     * @author likexin
     */
    private static function getCachePageKey(array $options)
    {

        return 'kdx_shop_diypage_page_' . '_' . (int)$options['id'] . '_' . (int)$options['type'] . '_' . (isset($options['member_id']) ? (int)$options['member_id'] : '*');
    }

    /**
     * 清除页面缓存
     * @param int $id
     * @param int $type
     * @param bool $isDefault
     * @return void
     * @author likexin
     */
    public static function clearCachePage(int $id, int $type, bool $isDefault = false)
    {
        // 删除当前页面的缓存
        $cacheKey = self::getCachePageKey([
            'id' => $id,
            'type' => $type,
        ]);
        $keys = \Yii::$app->redis->keys($cacheKey);
        if (!empty($keys)) {
            \Yii::$app->redis->del(...$keys);
        }

        // 如果是默认删除页面id为0的缓存
        if ($isDefault) {
            $cacheKey = self::getCachePageKey([
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