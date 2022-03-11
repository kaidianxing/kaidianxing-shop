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



namespace shopstar\bases\model;

use shopstar\helpers\ArrayHelper;
use shopstar\helpers\RequestHelper;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @author 青岛开店星信息技术有限公司
 * Class BaseActiveRecord
 * @package shopstar\base\model
 * @method BaseActiveQuery where($condition, $params = []) static
 * @method BaseActiveQuery andWhere($condition, $params = []) static
 * @method BaseActiveQuery orWhere($condition, $params = []) static
 * @method BaseActiveQuery andFilterWhere($condition) static
 * @method BaseActiveQuery orFilterWhere($condition) static
 */
class BaseActiveRecord extends ActiveRecord
{
    /**
     * 隐私字段(用于查询后过滤)
     * @var
     */
    public static $privateFields = [];

    /**
     * Updates the whole table using the provided counter changes and conditions.
     *
     * For example, to increment all customers' age by 1,
     *
     * ```php
     * Customer::updateAllCounters(['age' => 1]);
     * ```
     *
     * Note that this method will not trigger any events.
     *
     * @param array $counters the counters to be updated (attribute name => increment value).
     * Use negative values if you want to decrement the counters.
     * @param string|array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
     * Please refer to [[Query::where()]] on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * Do not name the parameters as `:bp0`, `:bp1`, etc., because they are used internally by this method.
     * @return int the number of rows updated
     * @throws
     */
    public static function updateAllCounters($counters, $condition = '', $params = [])
    {
        $n = 0;
        foreach ($counters as $name => $value) {
            $counters[$name] = new Expression("[[$name]]" . ($value < 0 ? '-' : '+') . ":bp{$n}", [":bp{$n}" => abs($value)]);
            $n++;
        }
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), $counters, $condition, $params);

        return $command->execute();
    }

    /**
     * {@inheritdoc}
     * @return BaseActiveQuery|object the newly created [[ActiveQuery]] instance.
     * @throws null
     */
    public static function find()
    {
        return Yii::createObject(BaseActiveQuery::class, [get_called_class()]);
    }

    /**
     * 快速获取列表
     * @param array $params
     * @param array $options
     * @return array|int|string|ActiveRecord[]
     * @author likexin
     */
    public static function getColl(array $params, array $options = [])
    {
        $options = array_merge([
            'pager' => true,                // 是否分页
            'onlyList' => false,            // 仅返回列表
            'onlyCount' => false,       // 只获取数量
            'page' => null,                        // 当前页数
            'pageSize' => null,               // 每页数量
            'asArray' => true,              // Array返回

            'total' => false,                   // 获取总数
            'totalWhere' => [],             // 获取总数

            'callable' => null,                // 回调
            'sort' => null,                       // 排序字段
            'by' => null,                           // 排序顺序

            'disableSort' => false, // 禁用自动排序

            'get_sql' => false,                           // 是否获取sql
        ], $options);

        // 处理排序 层级顺序 get > option > orderBy
        if (!$options['disableSort']) {
            $options['sort'] = $options['sort'] ? $options['sort'] : RequestHelper::get('sort');
            $options['by'] = $options['by'] ? $options['by'] : RequestHelper::get('by');
            if (!empty($options['sort']) && !empty($options['by'])) {
                $params['orderBy'] = [(string)$options['sort'] => ($options['by'] == 'asc' ? SORT_ASC : SORT_DESC)];
            }
        }

        // 处理orderWhere
        if (isset($params['orderBy']) && is_array($params['orderBy'])) {
            if (!empty($params['orderWhere']) && is_array($params['orderWhere'])) {
                // 排序时的条件
                $params['orderBy'] = array_merge($params['orderBy'], $params['orderWhere']);
            }
        }
        unset($params['orderWhere']);

        $self = self::find();
        $resultArray = [];

        if (isset($params['where'])) {
            $self->where(ArrayHelper::remove($params, 'where'));
        }

        // 遍历加条件
        foreach ($params as $key => $param) {
            if (($key == 'orWhere' || $key == 'andWhere' || $key == 'andFilterWhere' || $key == 'orFilterWhere') && is_array($param)) {
                foreach ($param as $where) {
                    $self->$key($where);
                }
                continue;
            } elseif (in_array($key, ['leftJoin', 'rightJoin', 'innerJoin'])) {
                if (is_array($param) && !empty($param)) {
                    $self->$key($param[0], $param[1]);
                }
                continue;
            } elseif (in_array($key, ['leftJoins', 'rightJoins'])) {
                $key = rtrim($key, 's');
                if (is_array($param) && !empty($param)) {
                    foreach ($param as $join) {
                        if (is_array($join) && !empty($join)) {
                            $self->$key($join[0], $join[1]);
                        }
                    }
                }
                continue;
            } elseif (in_array($key, ['crossJoin'])) {
                if (is_array($param) && !empty($param)) {
                    $self->join('CROSS JOIN', $param[0]);
                }
                continue;
            } elseif (in_array($key, ['crossJoins'])) {
                $key = rtrim($key, 's');
                if (is_array($param) && !empty($param)) {
                    foreach ($param as $join) {
                        if (is_array($join) && !empty($join)) {
                            //$self->$key($join[0], $join[1]);
                            $self->join('CROSS JOIN',$join[0]);
                        }
                    }
                }
                continue;
            }

            if (method_exists($self, $key)) {
                if ($key == 'searchs' && is_array($param)) {
                    foreach ($param as $search) {
                        $self->$key($search[0], $search[1] ?: '', $search[2] ?: '');
                    }
                } else {
                    $self->$key($param);
                }
            }
        }

        // 只返回总数
        if ($options['onlyCount']) {
            return $self->count();
        }

        // 处理分页
        if ($options['pager']) {
            // 读取总数
            $resultArray['total'] = $self->count();
            $self->page($options['page'], $options['pageSize']);
        }

        //返回sql
        if ($options['get_sql']) {
            return $self->createCommand()->getRawSql();
        }

        // 返回数组
        $self->asArray($options['asArray']);

//        // 获取总数
//        if ($options['total']) {
//            $resultArray['total'] = self::find()->where($options['totalWhere'])->count();
//        }

        // 读取列表
        $resultArray['list'] = $self->all();

        // 处理callable
        if (isset($options['callable']) && is_callable($options['callable'])) {
            array_walk($resultArray['list'], $options['callable']);
        }

        // 仅返回列表
        if ($options['onlyList']) {
            return $resultArray['list'];
        }

        // 获取总数
        if ($options['total']) {
            $resultArray['total'] = self::find()->where($options['totalWhere'])->count();
        }

        // 返回分页信息
        if ($options['pager']) {
            //当前页码
            $resultArray['page'] = !is_null($options['page']) ? (int)$options['page'] : RequestHelper::getPage();
            //每页记录数
            $resultArray['page_size'] = !is_null($options['pageSize']) ? (int)$options['pageSize'] : RequestHelper::getPageSize();
            if ($options['total']) {
                //总页数
                $resultArray['page_count'] = ceil($resultArray['total'] / $resultArray['page_size']);
            }
        }

        return $resultArray;
    }

    /**
     * 简单添加
     * @param array $options
     * @return array|void
     * @author likexin
     */
    public static function easyAdd(array $options = [])
    {
        $options['isEdit'] = false;

        return self::easyEdit($options);
    }

    /**
     * 简单编辑
     * @param array $options
     * @return array
     * @author likexin
     */
    public static function easyEdit(array $options = [])
    {
        $options = array_merge([
            'isEdit' => true,   // 是否是添加
            'primaryKey' => null,   // 主键, 为空则自动找主键
            'primaryKeyValue' => null,  // 主键初始值
            'onLoad' => null,   // 加载初始化方法
            'loadParams' => [], // 加载初始化变量
            'beforeSave' => null,   // 保存之前处理
            'attributes' => [], // 保存之前属性
            'afterSave' => null,    // 保存之后处理
            'resultParams' => [],   // 输出初始化变量
            'onResult' => null, // 输出初始化方法
            'andWhere' => [],
            'select' => [],
            'filterAttributes' => [], //输出的过滤的字段
            'filterPostField' => [], // 过滤post字段
            'postDataField' => null,    // post 字段(用于post二维数组)
            'decode' => null, //输出数据解析
            'checkPostPerm' => true,    // 检测post权限
            'getDirtyData' => false,    // 是否获取脏数据
        ], $options);

        // 获取主键Key
        $primaryKey = !empty($options['primaryKey']) ? $options['primaryKey'] : static::primaryKey()[0];
        if (empty($primaryKey)) {
            return error('主键Key错误');
        }
        // 主键值
        if (!empty($options['primaryKeyValue'])) {
            $primaryKeyValue = $options['primaryKeyValue'];
        } else {
            $primaryKeyValue = RequestHelper::isPost() ? RequestHelper::postInt($primaryKey) : RequestHelper::getInt($primaryKey);
        }

        if ($options['isEdit']) {
            if (empty($primaryKeyValue)) {
                return error($primaryKey . '不能为空');
            }
        }

        // 记录
        $insert = false;
        if ($options['isEdit']) {
            //查找记录
            $data = self::find()->where([$primaryKey => $primaryKeyValue])->andWhere($options['andWhere'])->select($options['select'])->limit(1)->one();
        } else {
            $data = new static;
            $insert = true;
        }

        if (RequestHelper::isPost()) {
            if (!$options['checkPostPerm']) {
                return error('没有操作权限');
            }

            // 判断编辑的数据是否存在
            if (empty($data)) {
                return error('数据不存在');
            }


            // 判断POST的数据是否为空
            $postData = RequestHelper::post($options['postDataField']);
            if (empty($postData)) {
                return error('数据为空');
            }


            // 过滤post字段
            if (!empty($options['filterPostField']) && is_array($options['filterPostField'])) {
                foreach ($options['filterPostField'] as $row) {
                    if (isset($postData[$row])) {
                        unset($postData[$row]);
                    }
                }
            }

            // 处理默认带入数据
            if (is_array($options['attributes'])) {
                $postData = ArrayHelper::merge($postData, $options['attributes']);
            }

            $data->setAttributes($postData);

            //保存之前的事件
            if (!empty($options['beforeSave'])) {
                if (is_callable($options['beforeSave'])) {
                    $before = $options['beforeSave']($data, $insert);
                } else {
                    $before = $options['beforeSave'];
                }
                if (is_error($before)) {
                    return $before;
                }
            }

            $dirtyData = '';
            if ($options['getDirtyData']) { //是否需要脏数据
                $dirtyData = $data->getDirtyAttributes2();
            }

            if (!$data->save()) {
                return error($data->getErrorMessage());
            }

            if (is_callable($options['afterSave'])) {
                //保存之后的事件
                $after = $options['afterSave']($data, $insert, $dirtyData);
                if (is_error($after)) {
                    return $after;
                }
            }
            //结果返回主键值
            $result = [
                $primaryKey => $data[$primaryKey]
            ];
            if (is_array($options['resultParams'])) {
                $result = ArrayHelper::merge($options['resultParams'], $result);
            }


            if (is_callable($options['onResult'])) {
                $options['onResult']($data, $insert, $result);
            }

            return success($result);
        }
        //加载记录
        $result = [];
        if ($options['isEdit']) {
            //查找记录
            if (empty($data)) {
                return error('记录不存在');
            }

            $attributes = $data->toArray();
            foreach ($options['filterAttributes'] as $attr) {
                unset($attributes[$attr]);
            }

            if (!empty($options['encode'])) {
                $options['encode']($attributes);
            }

            $result = [
                'data' => $attributes
            ];
        }
        if (is_array($options['loadParams'])) {
            $result = ArrayHelper::merge($options['loadParams'], $result);
        }

        if (is_callable($options['onLoad'])) {
            $options['onLoad']($result);
        }

        return success($result);
    }

    /**
     * 简单删除
     * @param array $options
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author likexin
     */
    public static function easyDelete(array $options = [])
    {
        $options = array_merge([
            'field' => null,
            'requestKey' => null,   // 请求Key, null时使用$option['field']
            'isPost' => true,    // POST请求
            'andWhere' => [],
            'beforeDelete' => null,
            'beforeAllDelete' => null,
            'afterDelete' => null,
            'afterAllDelete' => null,
            'checkPostPerm' => true,    // 检测post权限
        ], $options);

        // 判断是否POST
        if ($options['isPost']) {
            if (!RequestHelper::isPost()) {
                return error('错误的请求');
            }
            if (!$options['checkPostPerm']) {
                return error('没有操作权限');
            }
        }

        // 获取主键Key
        $primaryKey = !empty($options['field']) ? $options['field'] : static::primaryKey()[0];
        if (empty($primaryKey)) {
            return error('主键Key错误');
        }

        // 请求Key
        $requestKey = $options['requestKey'] ?: $primaryKey;

        // 获取操作的主键
        $primaryKeyIds = $options['isPost'] ? RequestHelper::postArray($requestKey) : RequestHelper::getArray($requestKey);
        if (empty($primaryKeyIds)) {
            return error($primaryKey . '不能为空');
        }

        // 读取数据
        $models = static::find()->where(['in', $primaryKey, $primaryKeyIds])->andWhere($options['andWhere'])->all();
        if (empty($models)) {
            return error('未找到可删除的记录');
        }

        // 影响行数
        $count = 0;

        // 遍历更新
        if (is_callable($options['beforeAllDelete'])) {
            $allow = $options['beforeAllDelete']();
            if (is_error($allow)) {
                return $allow;
            }
        }

        foreach ($models as $model) {
            $allow = true;
            //删除前置事件
            if (is_callable($options['beforeDelete'])) {
                $allow = $options['beforeDelete']($model);
            }
            if (is_error($allow)) {
                return $allow;
            }
            if ($model->delete()) {
                $count++;
                //删除回调 lfg
                if (is_callable($options['afterDelete'])) {
                    $options['afterDelete']($model);
                }
            }
        }
        if (is_callable($options['afterAllDelete'])) {
            $options['afterAllDelete']($count);
        }

        return success([
            'count' => $count
        ]);
    }

    /**
     * 简单切换数据
     * @param string $field
     * @param array $options
     * @return array
     * @author likexin
     */
    public static function easySwitch(string $field, array $options = [])
    {
        $options['dataType'] = 'int';
        return static::easyProperty($field, $options);
    }

    /**
     * 简单设置字段值
     * @param string $field
     * @param array $options
     * @return array|bool|void
     * @author likexin
     */
    public static function easyProperty(string $field, array $options = [])
    {
        $options = array_merge([
            'requestKey' => null,   // 请求Key, null时使用$field
            'primaryKey' => null,   // 主键, 为空则自动找主键
            'dataType' => 'string', //强制数字类型值
            'attributes' => null,    // 附加字段
            'value' => null,    // 传入值
            'validateValue' => null,    // 验证值
            'isPost' => true,    // POST请求
            'nonNull' => false,    // 判断空
            'andWhere' => [],
            'beforeAction' => null,
            'beforeAllAction' => null,
            'afterAction' => null,
            'afterAllAction' => null,
            'checkPostPerm' => true,    // 检测post权限
        ], $options);

        // 判断是否POST
        if ($options['isPost']) {
            if (!RequestHelper::isPost()) {
                return error('错误的请求');
            }
            if (!$options['checkPostPerm']) {
                return error('没有操作权限');
            }
        }

        // 获取主键Key
        $primaryKey = !empty($options['primaryKey']) ? $options['primaryKey'] : static::primaryKey()[0];
        if (empty($primaryKey)) {
            return error('主键Key错误');
        }

        // 获取操作的主键
        $primaryKeyIds = $options['isPost'] ? RequestHelper::postArray($primaryKey) : RequestHelper::getArray($primaryKey);

        if (empty($primaryKeyIds)) {
            return error($primaryKey . '不能为空');
        }

        // 获取操作字段
        $requestKey = !empty($options['requestKey']) ? $options['requestKey'] : $field;

        // 获取目标值
        if (!is_null($options['value'])) {
            $value = $options['value'];
        } else {
            $value = $options['isPost'] ? RequestHelper::post($requestKey) : RequestHelper::get($requestKey);
        }

        if ($options['dataType'] == 'int' || $options['dataType'] == 'integer') {
            $value = (int)$value;
        }

        // 判断不能为空
        if ($options['nonNull']) {
            if (is_null($value) || $value == '') {
                return error($requestKey . '不能为空');
            }
        }

        // 验证value值
        if (is_array($options['validateValue'])) {
            if (!in_array($value, $options['validateValue'])) {
                return error($requestKey . '值不合法');
            }
        }

        // 读取数据
        $models = static::find()->where(['in', $primaryKey, $primaryKeyIds])->andWhere($options['andWhere'])->all();
        if (empty($models)) {
            return error('可操作数据为空');
        }

        // 影响行数
        $count = 0;
        if (is_callable($options['beforeAllAction'])) {
            $allow = $options['beforeAllAction']();
            if (is_error($allow)) {
                return $allow;
            }
        }
        // 遍历更新
        foreach ($models as $model) {
            $allow = true;
            //前置事件
            if (is_callable($options['beforeAction'])) {
                $allow = $options['beforeAction']($model);
            }
            if (is_error($allow)) {
                return $allow;
            }

            $model->setAttribute($field, $value);

            // 处理附加字段
            if (!is_null($options['attributes']) && is_array($options['attributes'])) {
                $model->setAttributes($options['attributes']);
            }

            if ($model->save()) {
                $count++;
            }
            //前置事件
            if (is_callable($options['afterAction'])) {
                $options['afterAction']($model);
            }
        }
        if (is_callable($options['afterAllAction'])) {
            $options['afterAllAction']($count);
        }

        return success([
            'count' => $count
        ]);
    }

    /**
     * 删除到回收站或恢复(is_deleted = 1)
     * @param array $options
     * @return array|bool|void
     * @author likexin
     */
    public static function easyRecycle(array $options = [])
    {
        $options = array_merge([
            'field' => 'is_deleted',   // 数据库删除标记
            'isRestore' => false, //是否是恢复
            'primaryKey' => null,   // 主键, 为空则自动找主键
            'andWhere' => [],
            'isPost' => true,    // POST请求
            'beforeSave' => null,   // 保存之前事件
            'afterSave' => null,   // 保存之后事件
            'checkPostPerm' => true,    // 检测post权限
        ], $options);

        // 判断是否POST
        if ($options['isPost']) {
            if (!RequestHelper::isPost()) {
                return error('错误的请求');
            }
            if (!$options['checkPostPerm']) {
                return error('没有操作权限');
            }
        }

        // 获取主键Key
        $primaryKey = !empty($options['primaryKey']) ? $options['primaryKey'] : static::primaryKey()[0];
        if (empty($primaryKey)) {
            return error('主键Key错误');
        }

        // 获取操作的主键
        $primaryKeyIds = $options['isPost'] ? RequestHelper::postArray($primaryKey) : RequestHelper::getArray($primaryKey);
        if (empty($primaryKeyIds)) {
            return error($primaryKey . '不能为空');
        }

        // 读取数据
        $models = static::find()->where(['in', $primaryKey, $primaryKeyIds])->andWhere($options['andWhere'])->all();
        if (empty($models)) {
            return error('可操作数据为空');
        }

        // 影响行数
        $count = 0;

        // 遍历更新
        foreach ($models as $model) {
            $allow = true;
            //删除前置事件
            if (is_callable($options['beforeSave'])) {
                $allow = $options['beforeSave']($model);
            }
            if (is_error($allow)) {
                return $allow;
            }

            $model->setAttribute($options['field'], $options['isRestore'] ? 0 : 1);
            if ($model->save()) {
                $count++;

                if (is_callable($options['afterSave'])) {
                    $options['afterSave']($model);
                }
            }
        }

        return success([
            'count' => $count
        ]);
    }

    /**
     * 获得save()后错误信息
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function getErrorMessage()
    {
        return current($this->getFirstErrors());
    }

    /**
     * 复写获取脏数据
     * @param bool $contrastType 是否判断数据类型
     * @param bool $isKV 是否需要返回KV格式
     * @param null $names
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public function getDirtyAttributes2(bool $contrastType = false, bool $isKV = false, $names = null)
    {
        if ($names === null) {
            $names = $this->attributes();
        }
        $names = array_flip($names);
        $attributes = [];
        if ($this->getOldAttributes() === null) {
            foreach ($this->getAttributes() as $name => $value) {
                if (isset($names[$name])) {
                    $attributes[$name] = $value;
                }
            }
        } else {
            foreach ($this->getAttributes() as $name => $value) {
                if ($contrastType) {
                    $check = $value !== $this->getOldAttributes()[$name];
                } else {
                    $check = $value != $this->getOldAttributes()[$name];
                }

                if (isset($names[$name]) && (!array_key_exists($name, $this->getOldAttributes()) || $check)) {
                    $attributes[$name] = $value;
                }
            }
        }

        if (!$isKV) {
            $attributes = $this->getAttributeRemark($attributes);
        }

        return $attributes;
    }

    /**
     * 根据字段获取数据库备注
     * @param array $attributes
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public function getAttributeRemark(array $attributes)
    {
        $attributeLabels = $this->attributeLabels();
        $data = [];
        foreach ($attributes as $attributesIndex => $attributesItem) {
            if (array_key_exists($attributesIndex, $attributeLabels) && $attributesItem != '') {
                $data[] = '[' . $attributeLabels[$attributesIndex] . '：' . $attributesItem . ']';
            }
        }

        return implode(" - ", $data);
    }

    /**
     * 获取要保存日志的字段映射
     * @param array $attributes
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function getLogAttributeRemark(array $attributes)
    {
        return $this->getRemarkMap($attributes, $this->logAttributeLabels());
    }

    /**
     * 递归转译map
     * @param array $attributes
     * @param array $logAttributeLabels
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    private function getRemarkMap(array $attributes, array $logAttributeLabels = [])
    {
        $data = [];
        foreach ($attributes as $attributesIndex => $attributesItem) {
            if (is_array($logAttributeLabels[$attributesIndex]) && isset($logAttributeLabels[$attributesIndex]['item'])) {
                //如果是多维数组 则递归
                $data[$logAttributeLabels[$attributesIndex]['title']] = $this->getRemarkMap($attributesItem, $logAttributeLabels[$attributesIndex]['item']);
                continue;
            } elseif (is_int($attributesIndex) && is_array($attributesItem)) { //如果是整形的话，默认认为是索引数组
                foreach ($attributesItem as $attributesItemIndex => $attributesItemItem) {

                    if (!isset($logAttributeLabels[$attributesItemIndex])) {
                        continue;
                    }

                    if (is_array($attributesItemItem)) {
                        //如果是多维数组 则递归
                        $data[$attributesIndex][$logAttributeLabels[$attributesItemIndex]] = $this->getRemarkMap($attributesItemItem, $logAttributeLabels[$attributesItemIndex]);
                        continue;
                    }

                    //最终赋值
                    $data[$attributesIndex][$logAttributeLabels[$attributesItemIndex]] = $attributesItemItem;
                }
                continue;
            }

            if (!isset($logAttributeLabels[$attributesIndex])) {
                continue;
            }

            //最终赋值
            $data[$logAttributeLabels[$attributesIndex]] = $attributesItem;
        }

        return $data;
    }

    /**
     * 批量插入
     * @param array $cloumns
     * @param array $value
     * @return int
     * @throws \yii\db\Exception
     * @author likexin
     */
    public static function batchInsert(array $cloumns, array $value)
    {
        return \Yii::$app->db->createCommand()->batchInsert(static::tableName(), $cloumns, $value)->execute();
    }

    /**
     * 静态方法当find 调用 e.g self::where(['name']);
     * @param $name
     * @param $arguments
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public static function __callStatic($name, $arguments)
    {
        return static::find()->$name(...$arguments);
    }

}
