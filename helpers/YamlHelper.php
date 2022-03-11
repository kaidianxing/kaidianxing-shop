<?php

namespace shopstar\helpers;

use Symfony\Component\Yaml\Yaml;

/**
 * yaml操作助手
 * Class HttpHelper
 * @package shopstar\helpers
 */
class YamlHelper
{

    /**
     * 加载文件
     * 避免每次都parse，所以增加缓存逻辑，能减少一定的消耗
     * 默认缓存到php中，直接require即可
     * @param string $path
     * @param array $options
     * @return array
     * @author likexin
     */
    public static function loadFile(string $path, array $options = []): array
    {
        $options = array_merge([
            'cacheDir' => SHOP_STAR_RUNTIME_PATH . '/yaml',    // 缓存目录
        ], $options);

        // 配置文件不存在
        if (!is_file($path)) {
            return [];
        }

        // 定义缓存存储路径
        $cachePath = $options['cacheDir'] . '/' . md5($path) . '.php';

        // 如果有缓存文件修改时间大于原yaml的修改时间
        if (is_file($cachePath) && (filemtime($cachePath) >= filemtime($path))) {
            return require_once $cachePath;
        }

        // 解析yaml文件
        $config = Yaml::parseFile($path);
        if (empty($config)) {
            return [];
        }

        /**
         * 转为json格式，
         * 目前没有找到直接转为 return ['mysql' => '']
         * 所以使用json存储
         */
        $configStr = json_encode($config);

        // 写入缓存php文件中
        FileHelper::write($cachePath, "<?php
return json_decode('{$configStr}', true);
");

        return $config;
    }

}