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

namespace shopstar\components\storage\drivers;

use Exception;
use shopstar\components\storage\bases\BaseStorageDriver;
use shopstar\components\storage\bases\StorageDriverInterface;

/**
 * FTP存储驱动类
 * Class FtpDriver.
 * @package shopstar\components\storage\drivers
 */
class FtpDriver extends BaseStorageDriver implements StorageDriverInterface
{
    /**
     * 注意：passive_mode于直接Yii::createObject注入，直接读取设置中字段所有使用下划线分隔单词
     */

    /**
     * @var string FTP主机地址
     */
    public string $host;

    /**
     * @var string FTP主机端口
     */
    public $port = 21;

    /**
     * @var string FTP用户名
     */
    public string $username;

    /**
     * @var string FTP密码
     */
    public string $password;

    /**
     * @var int 开启SSL
     */
    public int $ssl = 0;

    /**
     * @var int 被动模式
     */
    public int $passive_mode = 1;

    /**
     * @var string FTP空间存储路径
     */
    public string $path = '/';

    /**
     * @var int FTP上传超时时间
     */
    public int $timeout = 0;

    /**
     * @var resource 资源
     */
    private $resource = null;

    /**
     * 获取超时时间(getter)
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    private function getTimeout(): int
    {
        return $this->timeout > 0 ? $this->timeout : 4;
    }

    /**
     * 初始化
     * @return array|void
     * @author 青岛开店星信息技术有限公司
     */
    public function init()
    {
        // 验证FTP扩展、必要参数
        if (!extension_loaded('ftp')) {
            return error('当前PHP服务未安装FTP扩展');
        } elseif (empty($this->host)) {
            return error('参数host错误');
        } elseif (empty($this->username)) {
            return error('参数username错误');
        } elseif (empty($this->password)) {
            return error('参数password错误');
        }

        // 处理上传路径
        if (!empty($this->path)) {
            $this->path = str_replace('\\', '/', $this->path);
            $this->path = trim($this->path, '/');
        }

        parent::init();
    }

    /**
     * 连接服务
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function connect()
    {
        // 根据ssl参数选择使用哪种方式去连接
        $connectFunc = $this->ssl == 1 ? 'ftp_ssl_connect' : 'ftp_connect';

        // 连接FTP服务
        $this->resource = $connectFunc($this->host, $this->port, $this->getTimeout());
        if ($this->resource === false) {
            $this->setError('无法连接至FTP服务器');
            return;
        }

        // 登录FTP服务
        try {
            ftp_login($this->resource, $this->username, $this->password);
        } catch (Exception $exception) {
            $this->setError('FTP服务登录失败');
            return;
        }

        // 设置被动模式
        ftp_pasv($this->resource, $this->passive_mode);
    }

    /**
     * 上传文件
     * @param string $localPath 本地路径
     * @param string $targetPath 目标路径
     * @param array $params 附加参数
     * @return array|bool|null
     * @author 青岛开店星信息技术有限公司
     */
    public function upload(string $localPath, string $targetPath, array $params = [])
    {
        // 判断错误
        $error = $this->getError();
        if (is_error($error)) {
            return $error;
        }

        // 判断文件是否存在
        if (!is_file($localPath)) {
            return error('本地文件不存在');
        }

        // 处理远端目录
        $targetDir = dirname($targetPath);
        if ($targetDir == '.' || $targetDir == '..') {
            $targetDir = '';
        }
        $remoteDirPath = $this->path . '/' . $targetDir;
        if (trim($remoteDirPath) != '/') {
            if (!$this->isDir($remoteDirPath)) {
                $this->mkMultiDir($remoteDirPath);
            }
        }

        // 上传文件
        $result = ftp_put($this->resource, $targetPath, $localPath, $this->getModeByFile($targetPath));
        if (!$result) {
            return error('FTP上传失败');
        }

        return true;
    }

    /**
     * 移除文件
     * @param string $targetPath
     * @return array|bool|null
     * @author 青岛开店星信息技术有限公司
     */
    public function remove(string $targetPath)
    {
        // 判断错误
        $error = $this->getError();
        if (is_error($error)) {
            return $error;
        }

        // 文件不存在直接返回成功
        if (!$this->fileExists($targetPath)) {
            return true;
        }

        // 执行删除远端文件
        return ftp_delete($this->resource, $targetPath);
    }

    /**
     * 是否是FTP连接资源
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    private function isResource(): bool
    {
        return is_resource($this->resource);
    }

    /**
     * 判断远端文件是否存在
     * @param string $targetPath
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    private function fileExists(string $targetPath): bool
    {
        return $this->size($targetPath) != -1;
    }

    /**
     * 检测路径是否存在
     * @param string $targetPath 目标路径
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    private function isDir(string $targetPath): bool
    {
        return $this->size($targetPath) == -1 && $this->listFiles($targetPath);
    }

    /**
     * 创建远端多级目录
     * @param string $targetPath
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    private function mkMultiDir(string $targetPath = ''): void
    {
        $targets = explode('/', $targetPath);
        $dir = '';
        $comma = '';

        foreach ($targets as $pathItem) {
            $dir .= $comma . $pathItem;
            $comma = '/';
            if (!empty($dir) && !$this->mkdir($dir)) {
                return;
            }
        }
    }

    /**
     * 创建远端目录
     * @param string $targetPath
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    private function mkdir(string $targetPath): bool
    {
        if (empty($targetPath) || !$this->isResource()) {
            return false;
        } elseif ($this->isDir($targetPath)) {
            return true;
        }

        // 创建目录
        $result = ftp_mkdir($this->resource, $targetPath);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * 返回远端文件大小
     * @param $targetPath
     * @return false|int
     * @author 青岛开店星信息技术有限公司
     */
    private function size($targetPath)
    {
        if (empty($targetPath) || !$this->isResource()) {
            return false;
        }

        return ftp_size($this->resource, $targetPath);
    }

    /**
     * 返回远端文件列表
     * @param string $targetPath
     * @return array|false|string[]
     * @author 青岛开店星信息技术有限公司
     */
    private function listFiles(string $targetPath = '.')
    {
        if (!$this->isResource()) {
            return false;
        }

        return ftp_nlist($this->resource, $targetPath);
    }

    /**
     * 根据文件获取传输模式
     * @param string $path
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    private function getModeByFile(string $path = ''): int
    {
        $extensions = [
            'txt', 'text', 'php', 'phps', 'php4', 'js', 'css', 'htm', 'html', 'phtml', 'shtml', 'log', 'xml'
        ];

        $fileExt = (($dot = strrpos($path, '.')) === false) ? 'txt' : substr($path, $dot + 1);

        return in_array($fileExt, $extensions, true) ? FTP_ASCII : FTP_BINARY;
    }
}
