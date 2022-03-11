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



namespace shopstar\helpers;

use yii\db\Exception;

/**
 * 数据库工具助手类
 * Class DbUtilHelper
 * @package shopstar\helpers
 */
class DbUtilHelper
{

    /**
     * 获取表前缀
     * @return mixed
     * @author likexin
     */
    public static function getTablePrefix()
    {
        return \Yii::$app->components['db']['tablePrefix'];
    }

    /**
     * 获取数据库名称
     * @return mixed
     * @author likexin
     */
    protected static function getDatabaseName()
    {
        $dsns = explode("=", \Yii::$app->components['db']['dsn']);
        return $dsns[count($dsns) - 1];
    }

    /**
     * 获取所有数据表
     * @param string|null $prefix 表前缀
     * @return array
     * @throws Exception
     */
    public static function getAllTable(string $prefix = null)
    {
        $allTables = \Yii::$app->db->createCommand("show tables")->queryAll();
        if (empty($allTables)) {
            return [];
        }

        $newTables = [];

        foreach ($allTables as $tables) {
            $tableName = array_values($tables);
            if (!is_null($prefix)) {
                if (strpos($tableName[0], $prefix) !== 0) {
                    continue;
                }
            }
            $newTables[] = $tableName[0];
        }

        return $newTables;
    }

    /**
     * 获取表名
     * @param string $table
     * @return string
     * @author likexin
     */
    public static function getTableName(string $table)
    {
        $prefix = static::getTablePrefix();
        if (empty($prefix)) {
            return $table;
        }
        $table = (strpos($table, $prefix) === 0) ? $table : "`{$prefix}{$table}`";
        return trim($table, '`');
    }

    /**
     * 获取表结构
     * @param $tableName
     * @return bool
     * @throws Exception
     * @author likexin
     */
    public static function getTableSchema($tableName)
    {
        $db = \Yii::$app->getDb();
        $tableName = static::getTableName($tableName);

        $result = $db->createCommand("SHOW TABLE STATUS LIKE '{$tableName}'")->queryOne();
        if (empty($result) || empty($result['created_at'])) {
            return false;
        }
        $ret['table_name'] = $result['Name'];
        $ret['charset'] = $result['Collation'];
        $ret['engine'] = $result['Engine'];
        $ret['increment'] = $result['Auto_increment'];
        $result = $db->createCommand("SHOW FULL COLUMNS FROM {$tableName}")->queryAll();

        foreach ($result as $value) {
            $temp = [];
            $type = explode(" ", $value['Type'], 2);

            $temp['name'] = $value['Field'];
            $pieces = explode('(', $type[0], 2);

            $temp['type'] = $pieces[0];
            $temp['length'] = isset($pieces[1]) ? rtrim($pieces[1], ')') : "0";
            $temp['null'] = $value['Null'] != 'NO';
            $temp['signed'] = empty($type[1]);
            $temp['increment'] = $value['Extra'] == 'auto_increment';
            $temp['default'] = $value['Default'];
            $temp['extra'] = $value['Extra'];
            /* @change likexin
             * $temp['default'] = $value['Default'];
             * if ($value['Default'] === NULL) {
             * $temp['default'] = "NULL";
             * }
             * // 扩展字段
             * if (!empty($value['Extra']) && $value['Extra'] !== 'auto_increment') {
             * $temp['extra'] = $value['Extra'];
             * }*/

            // 字段字符集
            $temp['collation'] = $value['Collation'];

            $ret['fields'][$value['Field']] = $temp;
        }

        $result = $db->createCommand("SHOW INDEX FROM {$tableName} ")->queryAll();

        foreach ($result as $value) {
            $ret['indexes'][$value['Key_name']]['name'] = $value['Key_name'];
            $ret['indexes'][$value['Key_name']]['type'] = ($value['Key_name'] == 'PRIMARY') ? 'primary' : ($value['Non_unique'] == 0 ? 'unique' : 'index');
            $ret['indexes'][$value['Key_name']]['fields'][] = $value['Column_name'];
        }

        return $ret;
    }

    /**
     * 获得数据库结构
     * @return array|bool
     * @throws Exception
     * @author likexin
     */
    public static function getDatabaseStructs()
    {
        $dbName = static::getDatabaseName();
        $tables = \Yii::$app->getDb()->createCommand('SHOW TABLES')->queryAll();
        if (empty($tables)) {
            return false;
        }

        $structs = [];
        foreach ($tables as $value) {
            $schema = static::getTableSchema(substr($value['Tables_in_' . $dbName], strpos($value['Tables_in_' . $dbName], '_') + 1));
            if (!empty($schema)) {
                $structs[] = $schema;
            }
        }

        return $structs;
    }


    /**
     *  从schema 创建sql
     * @param $schema
     * @return string
     * @author likexin
     */
    public static function createSqlFromSchema($schema)
    {
        $pieces = explode('_', $schema['charset']);
        $charset = $pieces[0];
        $engine = $schema['engine'];
        $prefix = static::getTablePrefix();

        $schema['table_name'] = str_replace('ims_', $prefix, $schema['table_name']);
        $sql = "CREATE TABLE IF NOT EXISTS `{$schema['table_name']}` (\n";

        foreach ($schema['fields'] as $value) {
            $piece = static::buildFieldSql($value);
            $sql .= "`{$value['name']}` {$piece},\n";
        }

        foreach ($schema['indexes'] as $value) {
            $fields = implode('`,`', $value['fields']);
            if ($value['name'] == 'PRIMARY' || $value['type'] == 'primary') {
                $sql .= "PRIMARY KEY (`{$fields}`),\n";
            } elseif ($value['type'] == 'unique') {
                $sql .= "UNIQUE KEY `{$value['name']}` (`{$fields}`),\n";
            } elseif ($value['type'] == 'index') {
                $sql .= "KEY `{$value['name']}` (`{$fields}`),\n";
            }
        }

        $sql = rtrim($sql);
        $sql = rtrim($sql, ',');

        $sql .= "\n) ENGINE=$engine DEFAULT CHARSET=$charset;\n\n";

        return $sql;
    }


    /**
     * 表结构对比
     * @param $schema1
     * @param $schema2
     * @return array
     * @author likexin
     */
    protected static function schemaCompare($schema1, $schema2)
    {
        $ret = [];

        $schema1['charset'] == $schema2['charset'] ? '' : $ret['diffs']['charset'] = true;

        $fields1 = array_keys($schema1['fields']);
        $fields2 = array_keys($schema2['fields']);
        $diffs = array_diff($fields1, $fields2);
        if (!empty($diffs)) {
            $ret['fields']['greater'] = array_values($diffs);
        }

        $diffs = array_diff($fields2, $fields1);
        if (!empty($diffs)) {
            $ret['fields']['less'] = array_values($diffs);
        }

        $diffs = [];
        $intersects = array_intersect($fields1, $fields2);
        if (!empty($intersects)) {
            foreach ($intersects as $field) {
                if ($schema1['fields'][$field] != $schema2['fields'][$field]) {

                    $diffs[] = $field;
                }
            }
        }
        if (!empty($diffs)) {
            $ret['fields']['diff'] = array_values($diffs);
        }

        $indexes1 = !empty($schema1['indexes']) && is_array($schema1['indexes']) ? array_keys($schema1['indexes']) : [];
        $indexes2 = !empty($schema2['indexes']) && is_array($schema2['indexes']) ? array_keys($schema2['indexes']) : [];

        $diffs = array_diff($indexes1, $indexes2);
        if (!empty($diffs)) {
            $ret['indexes']['greater'] = array_values($diffs);
        }

        $diffs = array_diff($indexes2, $indexes1);
        if (!empty($diffs)) {
            $ret['indexes']['less'] = array_values($diffs);
        }

        $diffs = [];
        $intersects = array_intersect($indexes1, $indexes2);
        if (!empty($intersects)) {
            foreach ($intersects as $index) {
                if ($schema1['indexes'][$index] != $schema2['indexes'][$index]) {
                    $diffs[] = $index;
                }
            }
        }

        if (!empty($diffs)) {
            $ret['indexes']['diff'] = array_values($diffs);
        }

        return $ret;
    }

    /**
     * 对比结构生成 sql
     * @param $schema1
     * @param $schema2
     * @param bool $strict
     * @return array
     * @author likexin
     */
    public static function getFixSqls($schema1, $schema2, $strict = false)
    {
        if (empty($schema1)) {
            return [static::createSqlFromSchema($schema2)];
        }

        $diff = static::schemaCompare($schema1, $schema2);
        if (!empty($diff['diffs']['table_name'])) {
            return [static::createSqlFromSchema($schema2)];
        }

        $sqls = [];
        if (!empty($diff['diffs']['engine'])) {
            $sqls[] = "ALTER TABLE `{$schema1['table_name']}` ENGINE = {$schema2['engine']}";
        }

        if (!empty($diff['diffs']['charset'])) {
            $pieces = explode('_', $schema2['charset']);
            $charset = $pieces[0];
            $sqls[] = "ALTER TABLE `{$schema1['table_name']}` DEFAULT CHARSET = {$charset}";
        }

        if (!empty($diff['fields'])) {
            if (!empty($diff['fields']['less'])) {
                foreach ($diff['fields']['less'] as $fieldName) {
                    $field = $schema2['fields'][$fieldName];
                    $piece = static::buildFieldSql($field);

                    if (!empty($field['rename']) && !empty($schema1['fields'][$field['rename']])) {
                        $sql = "ALTER TABLE `{$schema1['table_name']}` CHANGE `{$field['rename']}` `{$field['name']}` {$piece}";
                        unset($schema1['fields'][$field['rename']]);
                    } else {
                        $pos = !empty($field['position']) ? (' ' . $field['position']) : '';
                        $sql = "ALTER TABLE `{$schema1['table_name']}` ADD `{$field['name']}` {$piece}{$pos}";
                    }

                    $primary = [];
                    $isincrement = [];

                    if (StringHelper::exists($sql, 'AUTO_INCREMENT')) {
                        $isincrement = $field;
                        $sql = str_replace('AUTO_INCREMENT', 'PRIMARY KEY AUTO_INCREMENT ', $sql);
                        foreach ($schema1['fields'] as $field) {
                            if ($field['increment']) {
                                $primary = $field;
                                break;
                            }
                        }
                        if (!empty($primary)) {
                            $piece = static::buildFieldSql($primary);
                            if (!empty($piece)) {
                                $piece = str_replace('AUTO_INCREMENT', '', $piece);
                            }
                            $sqls[] = "ALTER TABLE `{$schema1['table_name']}` CHANGE `{$primary['name']}` `{$primary['name']}` {$piece}";
                        }
                    }

                    $sqls[] = $sql;
                }
            }

            //删除多余字段
            if (!empty($diff['fields']['greater'])) {
                foreach ($diff['fields']['greater'] as $fieldName) {
                    $field = $schema1['fields'][$fieldName];
                    $sql = "ALTER TABLE `{$schema1['table_name']}` DROP COLUMN `{$field['name']}` ";
                    $sqls[] = $sql;
                }
            }

            if (!empty($diff['fields']['diff'])) {
                foreach ($diff['fields']['diff'] as $fieldName) {
                    $field = $schema2['fields'][$fieldName];
                    $piece = static::buildFieldSql($field);

                    if (!empty($schema1['fields'][$fieldName])) {
                        $sqls[] = "ALTER TABLE `{$schema1['table_name']}` CHANGE `{$field['name']}` `{$field['name']}` {$piece}";
                    }
                }
            }

            if ($strict && !empty($diff['fields']['greater'])) {
                foreach ($diff['fields']['greater'] as $fieldName) {
                    if (!empty($schema1['fields'][$fieldName])) {
                        $sqls[] = "ALTER TABLE `{$schema1['table_name']}` DROP `{$fieldName}`";
                    }
                }
            }
        }

        if (!empty($diff['indexes'])) {
            if (!empty($diff['indexes']['less'])) {
                foreach ($diff['indexes']['less'] as $indexname) {
                    $index = $schema2['indexes'][$indexname];
                    $piece = static::buildIndexSql($index);
                    if (empty($isincrement)) {
                        $sqls[] = "ALTER TABLE `{$schema1['table_name']}` ADD {$piece}";
                    }
                }
            }
            if (!empty($diff['indexes']['diff'])) {
                foreach ($diff['indexes']['diff'] as $indexname) {
                    $index = $schema2['indexes'][$indexname];
                    $piece = static::buildIndexSql($index);

                    $sqls[] = "ALTER TABLE `{$schema1['table_name']}` DROP " . ($indexname == 'PRIMARY' ? " PRIMARY KEY " : "INDEX {$indexname}") . ", ADD {$piece}";
                }
            }
            if ($strict && !empty($diff['indexes']['greater'])) {
                foreach ($diff['indexes']['greater'] as $indexname) {
                    $sqls[] = "ALTER TABLE `{$schema1['table_name']}` DROP `{$indexname}`";
                }
            }
        }

        if (!empty($isincrement)) {
            $piece = static::buildFieldSql($isincrement);
            // $sqls[] = "ALTER TABLE `{$schema1['table_name']}` CHANGE `{$isincrement['name']}` `{$isincrement['name']}` {$piece}";
        }

        return $sqls;
    }

    /**
     * 创建所引sql
     * @param $index
     * @return string
     * @author likexin
     */
    protected static function buildIndexSql($index)
    {
        $piece = '';
        $fields = implode('`,`', $index['fields']);

        if ($index['name'] == 'PRIMARY' || $index['type'] == 'primary') {
            $piece .= "PRIMARY KEY (`{$fields}`)";
        } elseif ($index['type'] == 'index') {
            $piece .= " INDEX `{$index['name']}` (`{$fields}`)";
        } elseif ($index['type'] == 'unique') {
            $piece .= "UNIQUE `{$index['name']}` (`{$fields}`)";
        }

        return $piece;
    }

    /**
     * 创建字段sql
     * @param $field
     * @return string
     * @author likexin
     */
    protected static function buildFieldSql($field)
    {
        if (!empty($field['length'])) {
            $length = "({$field['length']})";
        } else {
            $length = '';
        }

        if (strpos(strtolower($field['type']), 'int') !== false || in_array(strtolower($field['type']), array('decimal', 'float', 'dobule'))) {
            $signed = empty($field['signed']) ? ' unsigned' : '';
        } else {
            $signed = '';
        }

        $charset = '';
        if (isset($field['collation']) && !empty($field['collation'])) {
            if (StringHelper::exists($field['collation'], 'utf8mb4')) {
                $charset = ' CHARACTER SET utf8mb4 COLLATE ' . $field['collation'] . ' ';
            } elseif (StringHelper::exists($field['collation'], 'utf8')) {
                $charset = ' CHARACTER SET utf8 COLLATE ' . $field['collation'] . ' ';
            }
        }

        if (empty($field['null'])) {
            $null = ' NOT NULL';
        } else {
            $null = ' NULL';
        }

        if (isset($field['default']) && $field['default'] !== 'NULL') {
            if ($field['default'] == 'CURRENT_TIMESTAMP') {
                $default = ' DEFAULT CURRENT_TIMESTAMP ';
            } else {
                $default = " DEFAULT '" . $field['default'] . "'";
            }
        } else {
            $default = '';
        }

        // 扩展字段
        $extra = ' ';
        if (!empty($field['extra']) && $field['extra'] !== 'auto_increment') {
            $extra = ' ' . $field['extra'] . ' ';
        }

        if ($field['increment']) {
            $increment = ' AUTO_INCREMENT';
        } else {
            $increment = '';
        }

        return "{$field['type']}{$length}{$signed}{$charset}{$null}{$default}{$extra}{$increment}";
    }

    /**
     * 判断字段是否存在
     * @param $tableName
     * @param $field
     * @return bool
     * @throws Exception
     * @author likexin
     */
    public static function hasField($tableName, $field)
    {
        $db = \Yii::$app->getDb();
        $fields = [];
        $result = $db->createCommand("SHOW FULL COLUMNS FROM {$tableName}")->queryAll();

        foreach ($result as $value) {
            $fields[] = strtolower($value['Field']);
        }

        return in_array($field, $fields);
    }

    /**
     * 增加字段
     * @param $tableName
     * @param $field
     * @param string $options
     * @return array|bool
     * @throws Exception
     * @author likexin
     */
    public static function addField($tableName, $field, $options = '')
    {
        if (static::hasField($tableName, $field)) {
            return true;
        }

        $db = \Yii::$app->getDb();
        $sql = "ALTER TABLE `{$tableName}` ADD `{$field}` {$options}";

        try {
            $result = $db->createCommand($sql)->execute();
        } catch (Exception $ex) {
            return error($ex->getMessage());
        }

        return true;
    }

}