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

namespace shopstar\bases\yii;

use Exception;
use Yii;
use yii\db\DataReader;

/**
 * 数据库连接，复写Yii
 * Class DbCommand
 * @package shopstar\bases\yii
 */
class DbCommand extends \yii\db\Command
{

    /**
     * @var int 重连次数
     */
    public $reconnectTimes = 3;

    /**
     * @var int 当前重连次数
     */
    public $reconnectCount = 0;

    /**
     * Determine if a given string contains a given substring.
     *
     * @param string $haystack
     * @param array|string $needles
     * @return bool
     */
    public static function contains(string $haystack, $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }


    /**
     * 检查指定的异常是否为可以重连的错误类型
     *
     * @param $exception
     * @return bool
     */
    public function isConnectionError($exception): bool
    {
        if ($exception instanceof Exception || $exception instanceof \Error) {
            $errorInfo = $this->pdoStatement->errorInfo();
            $message = $exception->getMessage();

            return self::contains($message, [
                'server has gone away',
                'no connection to the server',
                'Lost connection',
                'is dead or not enabled',
                'Error while sending',
                'decryption failed or bad record mac',
                'server closed the connection unexpectedly',
                'SSL connection has been closed unexpectedly',
                'Error writing data to the connection',
                'Resource deadlock avoided',
                'Transaction() on null',
                'child connection forced to terminate due to client_idle_limit',
                'query_wait_timeout',
                'reset by peer',
                'Physical connection is not usable',
                'TCP Provider: Error code 0x68',
                'Name or service not known',
                'ORA-03114',
                'Packets out of order. Expected',
                'Error while sending QUERY packet. PID=',
            ]);
        }
        return false;
    }

    /**
     * Executes the SQL statement.
     * This method should only be used for executing non-query SQL statement, such as `INSERT`, `DELETE`, `UPDATE` SQLs.
     * No result set will be returned.
     * @return int number of rows affected by the execution.
     * @throws \Exception execution failed
     */
    public function execute(): int
    {
        $sql = $this->getSql();
        list($profile, $rawSql) = $this->logQuery(__METHOD__);

        if ($sql == '') {
            return 0;
        }

        $this->prepare(false);
        $token = $rawSql;
        try {
            $profile and Yii::beginProfile($token, __METHOD__);
            $this->internalExecute($rawSql);
            $n = $this->pdoStatement->rowCount();

            $profile and Yii::endProfile($token, __METHOD__);

            $this->refreshTableSchema();
            $this->reconnectCount = 0;
            return $n;
        } catch (Exception $e) {
            $profile and Yii::endProfile($token, __METHOD__);
            if ($this->reconnectCount >= $this->reconnectTimes) {
                throw $this->db->getSchema()->convertException($e, $rawSql);
            }
            $isConnectionError = $this->isConnectionError($e);
            if ($isConnectionError) {
                $this->cancel();
                $this->db->close();
                $this->db->open();
                $this->reconnectCount++;
                return $this->execute();
            }
            throw $this->db->getSchema()->convertException($e, $rawSql);
        }
    }


    /**
     * Logs the current database query if query logging is enabled and returns
     * the profiling token if profiling is enabled.
     * @param string $category the log category.
     * @return array array of two elements, the first is boolean of whether profiling is enabled or not.
     * The second is the rawSql if it has been created.
     */
    protected function logQuery($category): array
    {
        if ($this->db->enableLogging) {
            $rawSql = $this->getRawSql();
            Yii::info($rawSql, $category);
        }
        if (!$this->db->enableProfiling) {
            return [false, $rawSql ?? null];
        }

        return [true, $rawSql ?? $this->getRawSql()];
    }

    /**
     * Performs the actual DB query of a SQL statement.
     * @param string $method method of PDOStatement to be called
     * @param int $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return mixed the method execution result
     * @throws \Exception if the query causes any problem
     * @since 2.0.1 this method is protected (was private before).
     */
    protected function queryInternal($method, $fetchMode = null)
    {
        list($profile, $rawSql) = $this->logQuery('yii\db\Command::query');

        if ($method !== '') {
            $info = $this->db->getQueryCacheInfo($this->queryCacheDuration, $this->queryCacheDependency);
            if (is_array($info)) {
                /* @var $cache \yii\caching\CacheInterface */
                $cache = $info[0];
                $cacheKey = [
                    __CLASS__,
                    $method,
                    $fetchMode,
                    $this->db->dsn,
                    $this->db->username,
                    $rawSql ?: $rawSql = $this->getRawSql(),
                ];
                $result = $cache->get($cacheKey);
                if (is_array($result) && isset($result[0])) {
                    Yii::debug('Query result served from cache', 'yii\db\Command::query');
                    return $result[0];
                }
            }
        }

        $this->prepare(true);
        $token = $rawSql;
        try {
            $profile and Yii::beginProfile($token, 'yii\db\Command::query');

            $this->internalExecute($rawSql);

            if ($method === '') {
                $result = new DataReader($this);
            } else {
                if ($fetchMode === null) {
                    $fetchMode = $this->fetchMode;
                }
                $result = call_user_func_array([$this->pdoStatement, $method], (array)$fetchMode);
                $this->pdoStatement->closeCursor();
            }

            $profile and Yii::endProfile($token, 'yii\db\Command::query');
        } catch (Exception $e) {
            $profile and Yii::endProfile($token, 'yii\db\Command::query');

            if ($this->reconnectCount >= $this->reconnectTimes) {
                throw $this->db->getSchema()->convertException($e, $rawSql);
            }
            $isConnectionError = $this->isConnectionError($e);
            if ($isConnectionError) {
                $this->cancel();
                $this->db->close();
                $this->db->open();
                $this->reconnectCount++;
                return $this->queryInternal($method, $fetchMode);
            }
            throw $this->db->getSchema()->convertException($e, $rawSql);
        }

        if (isset($cache, $cacheKey, $info)) {
            $cache->set($cacheKey, [$result], $info[1], $info[2]);
            Yii::debug('Saved query result in cache', 'yii\db\Command::query');
        }
        $this->reconnectCount = 0;
        return $result;
    }

}
