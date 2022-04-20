<?php

namespace install\bases;

use Throwable;
use yii\base\Exception;

/**
 * 安装异常类
 */
class InstallException extends Exception
{

    /**
     * 处理中文
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @author likexin
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        // 转为中文提示
        if (is_numeric($message) && $code == 0) {
            $code = $message;
            $message = self::$massages[$message] ?? $message;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @var string[] 所有的错误消息
     */
    private static $massages = [
        self::BASE_CHECK_POST_FAIL => '错误的请求',
        self::BASE_CHECK_TOKEN_SESSION_ID_EMPTY => 'session-id为空',
        self::BASE_CHECK_TOKEN_SESSION_ID_INVALID => 'session-id无效',
        self::BASE_CHECK_TOKEN_INVALID => 'Auth-Token无效',
        self::BASE_CHECK_AUTH_FAIL => '未登录或登录状态失效',
    ];

    public const BASE_CHECK_POST_FAIL = -10000;
    public const BASE_CHECK_TOKEN_SESSION_ID_EMPTY = -10010;
    public const BASE_CHECK_TOKEN_SESSION_ID_INVALID = -10011;
    public const BASE_CHECK_TOKEN_INVALID = -10020;
    public const BASE_CHECK_AUTH_FAIL = -10100;

}