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

namespace shopstar\models\role;


use shopstar\exceptions\UserException;
use shopstar\helpers\ExcelHelper;
use shopstar\traits\CacheTrait;

/**
 * This is the model class for table "{{%manager_verify_map}}".
 *
 * @property int $id
 * @property int $member_id 会员id
 * @property int $is_role 是否是操作员 0不是 1是
 * @property int $manager_id 操作员id
 * @property int $verify_point_id 核销点id
 * @property int $status 状态 0禁用 1启用
 * @property string $created_at 创建时间
 * @property int $is_deleted 是否删除 0否 1是
 */
class ManagerVerifyMapModel extends \shopstar\bases\model\BaseActiveRecord
{
    use CacheTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%manager_verify_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'is_role', 'manager_id', 'verify_point_id', 'status', 'is_deleted'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => '会员id',
            'is_role' => '是否是操作员 0不是 1是',
            'manager_id' => '操作员id',
            'verify_point_id' => '核销点id',
            'status' => '状态 0禁用 1启用',
            'created_at' => '创建时间',
            'is_deleted' => '是否删除 0否 1是',
        ];
    }

    /**
     * 入库
     * @param array $params
     * @return void
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveData(array $params)
    {
        if (!empty($params['verify_point_id'])) {
            $params['verify_point_id'] = explode(',', $params['verify_point_id']);
            foreach ($params['verify_point_id'] as $k => $v) {
                $data[] = [
                    'uid' => $params['uid'],
                    'manager_id' => $params['manager_id'],
                    'verify_point_id' => $v,
                ];
            }
            $field = ['uid', 'manager_id', 'verify_point_id'];
            self::batchInsert($field, $data);
        }
    }


    /**
     * 更新数据
     * @param array $params
     * @return void
     * @throws UserException
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateData(array $params)
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            // 清空数据
            self::deleteAll(['manager_id' => $params['manager_id']]);
            if ($params['member_id'] != '0' || !empty($params['verify_point_id'])) {
                self::saveData($params);
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new UserException(UserException::SAVE_FAILED);
        }
    }

    /**
     * 导出
     * @param array $list
     * @param array $field
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\base\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function export(array $list, array $field = [])
    {
        $diffFields = [
            'nickname',
            'mobile',
            'export_point',
            'status_text',
            'username',
            'name',
        ];
        $list = ExcelHelper::exportFilter($list, $diffFields);
        if ($list) {
            foreach ($list as $k => &$v) {
                if ($v['export_point'] && is_array($v['export_point'])) {
                    $v['export_point'] = implode('; ', $v['export_point']);
                }
            }
        }
        if (empty($field)) {
            $field = self::$exportField;
        }
        ExcelHelper::export($list, $field, '核销员导出');
        die;
    }

    /**
     * 默认导出字段
     * @var array
     */
    public static $exportField = [
        [
            'field' => 'username',
            'title' => '登录账号',
            'width' => 24
        ],
        [
            'field' => 'nickname',
            'title' => '绑定会员',
            'width' => 18
        ],
        [
            'field' => 'export_point',
            'title' => '绑定核销点',
            'width' => 40
        ],
        [
            'field' => 'name',
            'title' => '所属角色',
            'width' => 28
        ],
        [
            'field' => 'status_text',
            'title' => '状态',
            'width' => 18
        ],
    ];

    /**
     * 过滤核销点
     * @param $point
     * @param $managerPoint
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function filterPoint($point, $managerPoint)
    {
        $point = array_column($point, 'id');
        $managerPoint = array_column($managerPoint, 'id');
        $exists = array_intersect($point, $managerPoint);
        return count($exists) > 0 ? true : false;
    }

}
