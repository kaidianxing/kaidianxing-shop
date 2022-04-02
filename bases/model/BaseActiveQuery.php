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

use shopstar\helpers\RequestHelper;
use yii\db\ActiveQuery;

/**
 * Class BaseActiveQuery
 * @package shopstar\bases\model
 * @author 青岛开店星信息技术有限公司
 */
class BaseActiveQuery extends ActiveQuery
{

    /**
     * 分页处理
     * @param null $page
     * @param null $pageSize
     * @param string $pageKey
     * @param string $pageSizeKey
     * @return BaseActiveQuery
     */
    public function page($page = null, $pageSize = null, string $pageKey = 'page', string $pageSizeKey = 'pagesize')
    {
        if (is_null($page)) {
            $page = RequestHelper::getPage($pageKey);
        }
        if (is_null($pageSize)) {
            $pageSize = RequestHelper::getPageSize(20, $pageSizeKey);
        }
        $page = max(1, $page);
        $offset = ($page - 1) * $pageSize;
        return $this->offset($offset)->limit($pageSize);
    }

    /**
     * 快速搜索
     * @param string|array $searchField 搜索字段
     * @param string $searchType 搜索类型
     * @param string $formKey
     * @return $this|BaseActiveQuery
     * @author likexin
     */
    public function search($searchField, string $searchType = 'int', string $formKey = '')
    {
        $value = '';
        if (is_string($searchField)) {
            $value = RequestHelper::get($searchField, '');
        }
        if (!empty($formKey)) {
            $value = RequestHelper::get($formKey, '');
        }
        if ($searchType == 'like') {
            if (is_array($searchField)) {
                $andWhere = ['or'];
                foreach ($searchField as $f) {
                    $andWhere[] = ['like', $f, $value];
                }
                return $this->andFilterWhere($andWhere);
            }
            return $this->andFilterWhere(['like', $searchField, $value]);

        } else if ($searchType == 'between') {
            if (is_array($value)) {
                if (!empty($value[0]) && !empty($value[1])) {
                    return $this->andFilterWhere(['between', $searchField, $value[0], $value[1]]);
                } else if (!empty($value[0])) {
                    return $this->andFilterWhere(['>=', $searchField, $value[0]]);
                } else if (!empty($value[1])) {
                    return $this->andFilterWhere(['<=', $searchField, $value[1]]);
                }
            }
            return $this;
        } else if ($searchType == 'int' || $searchType == 'string') {
            if (is_array($searchField)) {
                $andWhere = ['or'];
                foreach ($searchField as $f) {
                    // $andWhere[] = [$f => $searchType == 'int' ? Request::get($f, '') : Request::get($formKey, '')];
                }
                return $this->andFilterWhere($andWhere);
            }

            if ($value !== '' && !is_null($value)) {
                if ($searchType == 'int') {
                    $value = (int)$value;
                }
            }
        } else if ($searchType == 'in') {
            if ($value !== '' && is_string($value)) {
                $value = explode(',', $value);
            }
        }

        return $this->andFilterWhere([$searchField => $value]);
    }

    /**
     * @param null $callableOrdb
     * @param null $db
     * @return array|\yii\db\ActiveRecord[]
     */
    public function all($callableOrdb = null, $db = null)
    {
        if (is_callable($callableOrdb)) {
            $list = parent::all($db);
            array_walk($list, $callableOrdb);
            return $list;
        } else if (is_callable($db)) {
            $list = parent::all($callableOrdb);
            array_walk($list, $db);
            return $list;
        }

        return parent::all($callableOrdb);
    }


    /**
     * 统计数量
     * @param string $q
     * @param null $db
     * @return int|string
     */
    public function count($q = '*', $db = null)
    {
        return (int)parent::count($q, $db);
    }


    /**
     * 默认数组形式
     * @param null $callableOrdb
     * @param null $db
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public function get($callableOrdb = null, $db = null)
    {
        return $this->asArray()->all($callableOrdb, $db);
    }

    /**默认数组形式
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public function first()
    {
        return $this->asArray()->one();
    }

    /**
     * debug  获取执行的sql
     * @return string
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    public function debug()
    {
        return $this->createCommand()->getRawSql();
    }

    /**
     * 添加搜索条件
     * @param $searchField
     * @param string $searchType
     * @param string $formKey
     * @return BaseActiveQuery
     * @author 青岛开店星信息技术有限公司
     */
    public function searchs($searchField, $searchType = 'int', $formKey = '')
    {
        $value = '';
        if (is_string($searchField)) {
            $value = RequestHelper::get($searchField, '');
        }
        if (!empty($formKey)) {
            $value = RequestHelper::get($formKey, '');
        }
        if ($searchType == 'like') {
            if (is_array($searchField)) {
                $andWhere = ['or'];
                foreach ($searchField as $f) {
                    $andWhere[] = ['like', $f, $value];
                }
                return $this->andFilterWhere($andWhere);
            }
            return $this->andFilterWhere(['like', $searchField, $value]);

        } else if ($searchType == 'between') {
            if (is_array($value)) {
                if (!empty($value[0]) && !empty($value[1])) {
                    return $this->andFilterWhere(['between', $searchField, $value[0], $value[1]]);
                } else if (!empty($value[0])) {
                    return $this->andFilterWhere(['>=', $searchField, $value[0]]);
                } else if (!empty($value[1])) {
                    return $this->andFilterWhere(['<=', $searchField, $value[1]]);
                }
            }
            return $this;
        } else if ($searchType == 'int' || $searchType == 'string') {
            if (is_array($searchField)) {
                $andWhere = ['or'];
                foreach ($searchField as $f) {
                    // $andWhere[] = [$f => $searchType == 'int' ? Request::get($f, '') : Request::get($formKey, '')];
                    // $andWhere[] = [$f => Request::get($formKey, '')];
                }
                return $this->andFilterWhere($andWhere);
            }

            if ($value !== '' && !is_null($value)) {
                if ($searchType == 'int') {
                    $value = (int)$value;
                }
            }
        } else if ($searchType == 'in') {
            if ($value !== '' && is_string($value)) {
                $value = explode(',', $value);
            }
        }

        return $this->andFilterWhere([$searchField => $value]);
    }
}
