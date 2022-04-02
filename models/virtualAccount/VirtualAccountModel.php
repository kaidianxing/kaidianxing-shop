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
use shopstar\constants\OrderConstant;
use shopstar\helpers\QueueHelper;
use shopstar\models\order\OrderModel;
use shopstar\models\shop\ShopSettings;
use shopstar\exceptions\virtualAccount\VirtualAccountException;
use shopstar\jobs\virtualAccount\AutoCloseOrderVirtualAccountJob;
use shopstar\jobs\virtualAccount\AutoDeleteGoodsStock;
use yii\helpers\Json;


/**
 * This is the model class for table "{{%virtual_account}}".
 *
 * @property int $id
 * @property string $name 卡密库名称
 * @property int $is_delete 删除 1已删除
 * @property int $use_description 使用说明 1开启
 * @property string $use_description_title 使用说明-文字标题
 * @property string $use_description_remark 使用说明-备注
 * @property int $use_address 使用地址 1开启
 * @property string $use_address_title 使用地址-文字标题
 * @property string $use_address_address 使用说明-链接地址
 * @property int $sequence 发卡顺序 0时间倒序 1权重值倒序
 * @property int $mailer 邮箱发送 0关闭 1开启
 * @property int $repeat 卡密库数据排重 0关闭 1开启
 * @property string $config key值字段
 * @property int $stock 不变总库存
 * @property int $total_count 总库存
 * @property int $sell_count 已售数量
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class VirtualAccountModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%virtual_account}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_delete', 'use_description', 'use_address', 'sequence', 'mailer', 'repeat', 'total_count', 'sell_count', 'stock'], 'integer'],
            [['config'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'use_description_title', 'use_address_title'], 'string', 'max' => 100],
            [['use_description_remark', 'use_address_address'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '卡密库名称',
            'is_delete' => '删除 1已删除',
            'use_description' => '使用说明 1开启',
            'use_description_title' => '使用说明-文字标题',
            'use_description_remark' => '使用说明-备注',
            'use_address' => '使用地址 1开启',
            'use_address_title' => '使用地址-文字标题',
            'use_address_address' => '使用说明-链接地址',
            'sequence' => '发卡顺序 0时间倒序 1权重值倒序',
            'mailer' => '邮箱发送 0关闭 1开启',
            'repeat' => '卡密库数据排重 0关闭 1开启',
            'config' => 'key值字段',
            'stock' => '不变总库存',
            'total_count' => '总库存',
            'sell_count' => '已售数量',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 库名查询
     * @param string $name
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInfoToName(string $name): bool
    {
        return self::find()->where(['name' => $name])->exists();
    }

    /**
     * 获取数据
     * @param int $id
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInfoToId(int $id): array
    {
        $info = self::find()->where(['id' => $id])->select(['config', 'repeat', 'name'])->first();
        if ($info) {
            $result['config'] = Json::decode($info['config'], true);
            $result['repeat'] = $info['repeat'];
            $result['name'] = $info['name'];
        }
        return $result;
    }

    /**
     * 处理下载模板
     * @param int $id
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function download(int $id)
    {
        $data = self::getInfoToId($id)['config'] ?? [];
        if (!$data) {
            return [];
        }
        $key = '（key）';
        foreach ($data as &$value) {
            $value = array_column($value, null);
            // 拼接表头信息
            $value = ['title' => $value[0] . $key];
            $value['field'] = '';
            $value['width'] = 12;
            unset($key);
        }
        return $data;
    }

    /**
     * 删除和恢复
     * @param $id
     * @param $isDelete
     * @return void
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws VirtualAccountException
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteData($id, $isDelete)
    {
        $model = self::find()
            ->where(['id' => $id])
            ->all();
        if (empty($model)) {
            throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_NOT_NULL);
        }
        foreach ($model as $key => $item) {
            if ($item && $isDelete != 2) {
                $item->is_delete = $isDelete;
                if (!$item->save()) {
                    throw new VirtualAccountException(VirtualAccountException::VIRTUAL_ACCOUNT_UPDATE_ERROR);
                }
            } else {
                $item->delete();
            }
        }
    }

    /**
     * 查询数据
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInfo($id)
    {
        $params = [
            'id' => $id
        ];

        return self::find()
            ->where($params)
            ->select([
                'id',
                'name',
                'sequence',
                'total_count',
                'use_description',
                'use_description_title',
                'use_description_remark',
                'use_address',
                'use_address_title',
                'use_address_address',
                'config',
            ])
            ->first();
    }

    /**
     * 减库存
     * @param $id
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateReduceCount($id, int $count)
    {
        self::updateAllCounters(['total_count' => -$count, 'stock' => -$count], ['id' => $id]);
    }

    /**
     * 增加库存
     * @param $id
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateAddCount($id, $count)
    {
        self::updateAllCounters(['stock' => $count, 'total_count' => $count], ['id' => $id]);
    }

    /**
     * 验证是否有邮箱权限
     * @param $virtualAccountId
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkMailer($virtualAccountId)
    {

        if (ShopSettings::get('mailer.status') == 0) {
            return false;
        }
        $result = self::findOne(['id' => $virtualAccountId]);
        if ($result['mailer'] == 0) {
            return false;
        }
        return true;
    }

    /**
     * 关闭全部待支付订单,返还库存,单规格商品并售罄,多规格商品特殊处理减少库存
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteVirtualAccount($id)
    {
        if ($id) {
            $orderVirtualAccountDataMap = VirtualAccountOrderMapModel::find()
                ->alias('order_virtual_account_data_map')
                ->leftJoin(OrderModel::tableName() . ' order', 'order.id = order_virtual_account_data_map.order_id')
                ->where([
                    'order_virtual_account_data_map.virtual_account_id' => $id,
                    'order.status' => OrderConstant::ORDER_STATUS_WAIT_PAY,
                ])
                ->select(['order.id'])
                ->asArray()
                ->all();

            // 循环投递关闭卡密库关联的待支付订单 如果存在订单 先关闭订单 再减少商品库存 避免库存错误
            if ($orderVirtualAccountDataMap) {
                foreach ($orderVirtualAccountDataMap as $value) {
                    QueueHelper::push(new AutoCloseOrderVirtualAccountJob([
                        'orderId' => $value['id'],
                    ]));
                }
                // 投递商品库存减少
                QueueHelper::push(new AutoDeleteGoodsStock([
                    'virtualAccountId' => $id,
                ]));
            } else {
                // 投递商品库存减少
                QueueHelper::push(new AutoDeleteGoodsStock([
                    'virtualAccountId' => $id,
                ]));
            }
        }

    }

    /**
     * 查询是否存在
     * @param $id
     * @return VirtualAccountModel|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function existsInfo($id)
    {
        return self::findOne(['id' => $id, 'is_delete' => 0]);
    }

    /**
     * 编辑时去重自己的名称
     * @param string $name
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function checkName(string $name, int $id): bool
    {
        $result = self::findOne(['name' => $name]);
        // 如果是自己
        if ($result && $result->id == $id) {
            return false;
        }
        if ($result) {
            return true;
        }
        return false;
    }
}