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

namespace shopstar\models\printer;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\components\printer\PrinterComponent;
use shopstar\constants\CacheTypeConstant;
use shopstar\constants\printer\PrinterTypeConstant;
use shopstar\traits\CacheTrait;
use yii\helpers\Json;


/**
 * This is the model class for table "{{%printer}}".
 *
 * @property int $id auto increment
 * @property int $type 打印机类型
 * @property string $brand 品牌
 * @property string $name 名称
 * @property string $client_id 客户端唯一标识
 * @property string $config 配置
 * @property string $location 应用位置
 * @property string $access_token access token
 * @property int $status 状态 0禁用1启用
 * @property int $is_deleted 是否删除 0否1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class PrinterModel extends BaseActiveRecord
{
    use CacheTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%printer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'status', 'is_deleted'], 'integer'],
            [['config'], 'required'],
            [['config'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['brand', 'name', 'location', 'client_id', 'access_token'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment',
            'type' => '打印机类型',
            'brand' => '品牌',
            'name' => '名称',
            'client_id' => '客户端唯一标识',
            'config' => '配置',
            'location' => '应用位置',
            'access_token' => 'access token',
            'status' => '状态 0禁用1启用',
            'is_deleted' => '是否删除 0否1是',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 添加打印机
     * @param $params
     * @return array|PrinterModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function addResult($params)
    {
        try {
            // 判断是否之间添加过打印机
            if ($params['type'] == PrinterTypeConstant::PRINTER_YLY_AUTH) {
                $clientId = $params['config']['client_id'];
            } else {
                $clientId = $params['config']['ukey'];
            }

            $printerExits = PrinterModel::find()->where([
                'type' => $params['type'],
                'client_id' => $clientId,
                'is_deleted' => 0,
            ])->exists();

            if ($printerExits) {
                return error('该打印机平台账号下已存在打印机');
            }

            // access_token为空第一次添加授权
            try {
                $driver = PrinterComponent::getInstance(PrinterTypeConstant::getIdentify($params['type']), $params['config']);

                // 飞鹅添加前删除授权
                if ($params['type'] == PrinterTypeConstant::PRINTER_FEY) {
                    $driver->deletePrinter();
                }

                $addPrinterResult = $driver->addPrinter();
            } catch (\Throwable $e) {
                return error($e->getMessage());
            }

            if (is_error($addPrinterResult)) {
                return $addPrinterResult;
            }

            $accessToken = $driver->access_token ?? (string)time();

            $printer = new self();

            $params['client_id'] = $clientId;
            $params['config'] = Json::encode($params['config']);
            $params['access_token'] = $accessToken;

            $printer->setAttributes($params);

            if (!$printer->save()) {
                return error($printer->getErrorMessage());
            }

            // 清除redis access_token
            if ($printer->type == PrinterTypeConstant::PRINTER_YLY_AUTH) {
                self::deleteCache(CacheTypeConstant::PRINTER_ACCESS_TOKEN, $clientId);
            }

        } catch (\Throwable $e) {

            return error($e->getMessage());
        }

        return $printer;
    }

    /**
     * 测试打印
     * @param $params
     * @author 青岛开店星信息技术有限公司
     */
    public static function printIndex($params)
    {
        // 判断是否之间添加过打印机 获取access_token
        if ($params['type'] == PrinterTypeConstant::PRINTER_YLY_AUTH) {
            $clientId = $params['config']['client_id'];
        } else {
            $clientId = $params['config']['ukey'];
        }
        $accessToken = PrinterModel::find()->where(['type' => $params['type'], 'client_id' =>
            $clientId, 'is_deleted' => 0])->select('access_token')->first();

        $accessToken = $accessToken['access_token'] ?? '';

        // 数据库中没有access_token 从redis中获取
        if (empty($accessToken)) {
            $accessToken = self::getStringCache(CacheTypeConstant::PRINTER_ACCESS_TOKEN, $clientId);
        }

        // 易联云打印机添加access_token
        if ($params['type'] == PrinterTypeConstant::PRINTER_YLY_AUTH && !empty($accessToken)) {
            $params['config']['access_token'] = $accessToken;
        }

        if ($params['type'] == PrinterTypeConstant::PRINTER_YLY_AUTH) {
            $content = "<FS2><center>**#XX商城**</center></FS2>";
            $content .= str_repeat('.', 32);
            $content .= "<FS2><center>--在线支付--</center></FS2>";
            $content .= "<FS><center>张周兄弟烧烤</center></FS>";
            $content .= "订单时间:" . date("Y-m-d H:i") . "\n";
            $content .= "订单编号:40807050607030\n";
            $content .= str_repeat('*', 14) . "商品" . str_repeat("*", 14);
            $content .= "<table>";
            $content .= "<tr><td>烤土豆(超级辣)</td><td>x3</td><td>5.96</td></tr>";
            $content .= "<tr><td>烤豆干(超级辣)</td><td>x2</td><td>3.88</td></tr>";
            $content .= "<tr><td>烤鸡翅(超级辣)</td><td>x3</td><td>17.96</td></tr>";
            $content .= "<tr><td>烤排骨(香辣)</td><td>x3</td><td>12.44</td></tr>";
            $content .= "<tr><td>烤韭菜(超级辣)</td><td>x3</td><td>8.96</td></tr>";
            $content .= "</table>";
            $content .= str_repeat('.', 32);
            $content .= "<QR>这是二维码内容</QR>";
            $content .= "小计:￥82\n";
            $content .= "折扣:￥４ \n";
            $content .= str_repeat('*', 32);
            $content .= "订单总价:￥78 \n";
            $content .= "<FS2><center>**#1 完**</center></FS2>";
        } else {
            $content = '<CB>测试打印</CB><BR>';
            $content .= '名称　　　　　 单价  数量 金额<BR>';
            $content .= '--------------------------------<BR>';
            $content .= '饭　　　　　 　10.0   10  100.0<BR>';
            $content .= '炒饭　　　　　 10.0   10  100.0<BR>';
            $content .= '蛋炒饭　　　　 10.0   10  100.0<BR>';
            $content .= '鸡蛋炒饭　　　 10.0   10  100.0<BR>';
            $content .= '西红柿炒饭　　 10.0   10  100.0<BR>';
            $content .= '西红柿蛋炒饭　 10.0   10  100.0<BR>';
            $content .= '西红柿鸡蛋炒饭 10.0   10  100.0<BR>';
            $content .= '--------------------------------<BR>';
            $content .= '备注：加辣<BR>';
            $content .= '合计：xx.0元<BR>';
            $content .= '送货地点：广州市南沙区xx路xx号<BR>';
            $content .= '联系电话：13888888888888<BR>';
            $content .= '订餐时间：2014-08-08 08:08:08<BR>';
            $content .= '<QR>http://www.feieyun.com</QR>';//把二维码字符串用标签套上即可自动生成二维码
        }


        try {
            $driver = PrinterComponent::getInstance(PrinterTypeConstant::getIdentify($params['type']), $params['config']);

            // 易联云token为空添加授权
            if (empty($accessToken)) {
                // access_token为空第一次添加授权
                $addPrinterResult = $driver->addPrinter();
                if (is_error($addPrinterResult)) {
                    return $addPrinterResult;
                }
                // access_token为空 缓存access_token
                if ($params['type'] == PrinterTypeConstant::PRINTER_YLY_AUTH) {
                    self::stringCache(CacheTypeConstant::PRINTER_ACCESS_TOKEN, $driver->access_token, [$clientId]);
                }
                if ($params['type'] == PrinterTypeConstant::PRINTER_FEY) {
                    self::stringCache(CacheTypeConstant::PRINTER_ACCESS_TOKEN, (string)time(), [$clientId]);
                }
            }

            $printerResult = $driver->printIndex($content);
        } catch (\Throwable $e) {
            return error($e->getMessage());
        }

        if (is_error($printerResult)) {
            return $printerResult;
        }

        return true;
    }

    /**
     * 保存
     * @param $params
     * @return array|null|static
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveResult($params)
    {
        try {

            $printer = PrinterModel::findOne(['id' => $params['id'], 'is_deleted' => 0]);

            if (empty($printer)) {
                return error('打印机不存在');
            }

            $printer->setAttributes($params);

            if (!$printer->save()) {
                return error($printer->getErrorMessage());
            }

        } catch (\Throwable $e) {

            return error($e->getMessage());
        }

        return $printer;
    }

    /**
     * 根据ID获取打印机名称
     * @param $printerIds
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPrinterNameById($printerIds)
    {
        $result = self::find()
            ->where([
                'id' => $printerIds,
                'is_deleted' => 0
            ])
            ->select('id, status, name, location, type, brand')
            ->get();

        return (array)$result;
    }

    /**
     * 根据ID获取打印机名称(map)
     * @param string $printerIds 打印机ID
     * @param array $printerIds 打印机列表
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPrinterNameMapById($printerIds, $printers)
    {
        if (empty($printerIds)) {
            return [];
        }

        $returnData = [];
        foreach (explode(',', $printerIds) as $printer) {
            if (isset($printers[$printer])) {
                $returnData[] = $printers[$printer];
            }
        }

        return $returnData;
    }
}