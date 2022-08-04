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

namespace shopstar\models\virtualAccount;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\email\EmailComponent;
use shopstar\constants\virtualAccount\VirtualAccountDataConstant;
use shopstar\exceptions\sysset\MallException;
use shopstar\exceptions\virtualAccount\VirtualAccountException;
use shopstar\helpers\CacheHelper;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\components\MailerMessageJob;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use apps\notice\manage\MailerController;
use yii\db\Expression;
use yii\helpers\Json;


/**
 * This is the model class for table "{{%virtual_account_data}}".
 *
 * @property int $id
 * @property int $virtual_account_id 卡密库id
 * @property int $sort 权重
 * @property int $status 状态 0未出售 1已售 2待付款
 * @property string $key 数据key 搜索用
 * @property string $data 数据
 * @property string $data_md5 加密数据串
 * @property string $created_at 添加时间
 * @property string $updated_at 添加时间
 * @property string $use_time 使用时间
 * @property int $create_way 添加方式 1后台新增数据 2excel导入
 * @property int $is_delete 删除 1删除
 */
class VirtualAccountDataModel extends BaseActiveRecord
{
    /**
     * 导出字段
     * @var array
     */
    public static $exportColumns = [
        ['title' => '状态', 'field' => 'status_text', 'width' => 18],
        ['title' => '导入时间', 'field' => 'created_at', 'width' => 18],
        ['title' => '订单编号', 'field' => 'order_no', 'width' => 18],
        ['title' => '下单时间', 'field' => 'order_created_at', 'width' => 18],
    ];

    public static $statusField = [
        '未出售',
        '已出售',
        '待付款',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%virtual_account_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['virtual_account_id', 'sort', 'status', 'create_way', 'is_delete'], 'integer'],
            [['data'], 'string'],
            [['created_at', 'use_time', 'updated_at'], 'safe'],
            [['key'], 'string', 'max' => 255],
            [['data_md5'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'virtual_account_id' => '卡密库id',
            'sort' => '权重',
            'status' => '状态 0未出售 1已售 2待付款',
            'key' => '数据key 搜索用',
            'data' => '数据',
            'data_md5' => '加密数据串',
            'created_at' => '添加时间',
            'updated_at' => '更新时间',
            'use_time' => '使用时间',
            'create_way' => '添加方式 1后台新增数据 2excel导入',
            'is_delete' => '删除 1删除',
        ];
    }

    /**
     * 数据去重
     * @param $virtualAccountId
     * @param $dataMd5
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function DeDuplication(int $virtualAccountId, string $dataMd5): bool
    {

        return self::find()->where(['virtual_account_id' => $virtualAccountId, 'data_md5' => $dataMd5])->exists();
    }

    /**
     * 卡密库数据更新权重
     * @param $id
     * @param $sort
     * @return void
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateData($id, $flag, $sort = 0)
    {
        $model = self::find()
            ->where(['id' => $id, 'is_delete' => 0])
            ->all();
        if (empty($model)) {
            throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_DATA_NOT_NULL);
        }
        foreach ($model as $key => $item) {
            // 判断是删除还是更新权重
            $item->$flag = $flag == 'sort' ? $sort : 1;
            if (!$item->save()) {
                throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_UPDATE_ERROR);
            }
        }
    }

    /**
     * 解析json串，拼接导出数据
     * @param $data
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function exportField(&$data, $config)
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                $res = Json::decode($value['data']);
                for ($i = 0; $i < count($res); $i++) {
                    $va = 'value' . ($i + 1);
                    $newColumns[$i]['title'] = $config[$i]['key'];
                    $newColumns[$i]['field'] = $va;
                    $newColumns[$i]['width'] = '18';
                    $value[$va] = $res[$va];
                }
            }
        }
        return $newColumns;
    }

    /**
     * 根据库id查询数据id
     * @param $data
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getId($data)
    {
        $order = ['created_at' => SORT_DESC];
        if ($data) {
            if ($data['sequence'] == 0) {
                $order = [
                    'created_at' => SORT_DESC,
                    'id' => SORT_DESC
                ];
            } else {
                $order = [
                    'sort' => SORT_DESC,
                    'id' => SORT_DESC
                ];
            }
        }
        $params = [
            'virtual_account_id' => $data['id'],
            'status' => 0,
            'is_delete' => 0
        ];
        // 根据保存的发卡顺序设置 筛选卡密数据
        for ($i = 0; $i < 3; $i++) {
            //　先查询数据库
            $result = self::find()->where($params)->select(['id', 'data'])->orderBy($order)->first();
            if (empty($result)) {
                continue;
            }

            // 生成redis的key
            $cacheKey = 'kdx_shop_plugin_virtual_' . $result['virtual_account_id'] . '_' . $result['id'];

            // 查询redis是否存在
            if (CacheHelper::get($cacheKey)) {
                // 如果存在 continue
                continue;
            }

            // 不存在，记录，并且返回
            CacheHelper::set($cacheKey, 1, 10);

            return $result;
        }

        // 查三次还没有
        return [];
    }

    /**
     * 减少库存
     * @param $id
     * @return void
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateReduceStock($id)
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            $virtualAccountData = VirtualAccountDataModel::findOne(['id' => $id]);
            if ($virtualAccountData) {
                $virtualAccountId = $virtualAccountData->virtual_account_id;

                $ids = is_array($id) ? $id : (array)$id;
                $count = count($ids);

                // 减少卡密库库存
                VirtualAccountModel::updateReduceCount($virtualAccountId, $count);
                // 处理单规格商品库存
                GoodsModel::updateAllCounters(['stock' => -$count], ['virtual_account_id' => $virtualAccountId]);
                // 处理多规格库存
                $result = GoodsOptionModel::updateAllCounters(['stock' => -$count], ['virtual_account_id' => $virtualAccountId]);
                if (!is_error($result)) {
                    // 同步处理多规格下的商品表总数量
                    $goodsOptionList = GoodsOptionModel::getListByVirtualAccountId($virtualAccountId);
                    $goodsStock = array_column($goodsOptionList, 'stock', 'goods_id');
                    foreach ($goodsStock as $gsKey => $gsValue) {
                        GoodsModel::updateAllCounters(['stock' => -$count], ['id' => $gsKey]);
                    }
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new MallException(MallException::NOTICE_CHANGE_STATUS_FAIL);
        }
    }

    /**
     * 增加库存
     * @param $virtualAccountId
     * @param $count
     * @return void
     * @throws MallException
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateAddStock($virtualAccountId, $count)
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 增加卡密库库存
            VirtualAccountModel::updateAddCount($virtualAccountId, $count);
            // 处理单规格商品库存
            GoodsModel::updateAllCounters(['stock' => $count], ['virtual_account_id' => $virtualAccountId]);
            // 处理多规格库存
            $result = GoodsOptionModel::updateAllCounters(['stock' => $count], ['virtual_account_id' => $virtualAccountId]);
            if (!is_error($result)) {
                // 同步处理多规格下的商品表总数量
                $goodsOptionList = GoodsOptionModel::getListByVirtualAccountId($virtualAccountId);
                $goodsStock = array_column($goodsOptionList, 'stock', 'goods_id');
                foreach ($goodsStock as $gsKey => $gsValue) {
                    GoodsModel::updateAllCounters(['stock' => $count], ['id' => $gsKey]);
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new MallException(MallException::NOTICE_CHANGE_STATUS_FAIL);
        }
    }

    /**
     * 下单修改状态
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateStatus($id, int $status)
    {
        self::updateAll(['status' => $status], ['id' => $id]);
    }

    /**
     * 适配下单用,增减库存
     * @param $orderId
     * @param $count
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateVirtualAccountReduceStock($reduce, $orderId, $count, $goodsId)
    {
        $orderVirtualAccountData = VirtualAccountOrderMapModel::getInfo($orderId);
        $virtualAccountData = VirtualAccountDataModel::findOne(['id' => $orderVirtualAccountData->virtual_account_data_id]);
        if ($virtualAccountData) {
            $setting = $reduce ? '-' : '+';
            // 反向处理已销售数量
            $sellSetting = $reduce ? '+' : '-';
            $virtualAccountId = $virtualAccountData->virtual_account_id;
            // 卡密库库存和销量
            VirtualAccountModel::updateAllCounters(['total_count' => $setting . $count, 'sell_count' => $sellSetting . $count], ['id' => $virtualAccountId]);
            // 单规格商品表的库存
            $goodsList = GoodsModel::find()
                ->where([
                    'virtual_account_id' => $virtualAccountId,
                    'is_deleted' => 0
                ])
                ->select(['id'])
                ->asArray()
                ->all();

            if ($goodsList) {
                foreach ($goodsList as $value) {
                    if ($value['id'] == $goodsId) {
                        continue;
                    }
                    GoodsModel::updateAllCounters(['stock' => $setting . $count], ['id' => $value['id']]);
                }
            }
            // 处理多规格的库存以及关联到的商品表
            $goodsOptionList = GoodsOptionModel::find()
                ->where([
                    'virtual_account_id' => $virtualAccountId,
                ])
                ->select([
                    'id',
                    'goods_id',
                ])
                ->asArray()
                ->all();

            if ($goodsOptionList) {
                $updateGoodsId = '';
                foreach ($goodsOptionList as $value) {
                    // 跳过商品逻辑已经减掉的当前商品的库存
                    if ($value['goods_id'] == $goodsId) {
                        continue;
                    }
                    // 更新卡密库id关联的别的商品的库存
                    GoodsOptionModel::updateAllCounters(['stock' => $setting . $count], ['id' => $value['id']]);
                    // 只更新一遍商品表
                    if ($updateGoodsId == $value['goods_id']) {
                        continue;
                    }
                    GoodsModel::updateAllCounters(['stock' => $setting . $count], ['id' => $value['goods_id']]);
                    $updateGoodsId = $value['goods_id'];
                }
            }
            if (!$reduce) {
                $orderVirtualAccountData = VirtualAccountOrderMapModel::getMapList($orderId);
                if ($orderVirtualAccountData) {
                    VirtualAccountDataModel::updateStatus($orderVirtualAccountData, VirtualAccountDataConstant::ORDER_VIRTUAL_ACCOUNT_DATA_NOT);
                }
                VirtualAccountOrderMapModel::deleteOrderVirtualAccountDataMap($orderId);
            }
        }
    }

    /**
     * 发送邮件
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function sendMailer(int $orderId)
    {
        $orderVirtualAccountDataMap = VirtualAccountOrderMapModel::getMapList($orderId);

        $toMailer = VirtualAccountOrderMapModel::findOne(['order_id' => $orderId]);

        // 查询模板
        $body = EmailComponent::getTemplate($orderVirtualAccountDataMap);
        //推送队列
        QueueHelper::push(new MailerMessageJob([
            'orderId' => $orderId,
            'toMailer' => $toMailer->to_mailer,
            'body' => $body,
        ]));

    }

}
