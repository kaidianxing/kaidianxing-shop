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

use Yii;
use yii\base\Controller;
use yii\base\InvalidConfigException;

/**
 * @author 青岛开店星信息技术有限公司
 * Class Application
 * @package shopstar\base
 * @property \yii\redis\Connection $redis The Redis component. This property is read-only.
 * @property \yii\redis\Connection $redisPermanent The Redis component. This property is read-only.
 * @property \yii\queue\db\Queue|\yii\queue\redis\Queue $queue The Queue component. This property is read-only.
 * @property \shopstar\components\platform\Wechat $wechat 微信实例
 */
class Application extends \yii\web\Application
{

    /**
     * @author likexin
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Checks if class name or prefix is incorrect
     *
     * @param string $className
     * @param string $prefix
     * @return bool
     */
    private function isIncorrectClassNameOrPrefix(string $className, string $prefix): bool
    {
        if (!preg_match('%^[a-z][a-z0-9\\-_]*$%', $className)) {
            return true;
        }
        if ($prefix !== '' && !preg_match('%^[a-z0-9_/]+$%i', $prefix)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $id
     * @return object|Controller|null
     * @throws InvalidConfigException
     * @author 青岛开店星信息技术有限公司
     */
    public function createControllerByID($id)
    {
        $pos = strrpos($id, '/');
        if ($pos === false) {
            $prefix = '';
            $className = $id;
        } else {
            $prefix = substr($id, 0, $pos + 1);
            $className = substr($id, $pos + 1);
        }

        if ($this->isIncorrectClassNameOrPrefix($className, $prefix)) {
            return null;
        }

        $className = preg_replace_callback('%-([a-z0-9_])%i', function ($matches) {
                return ucfirst($matches[1]);
            }, ucfirst($className)) . 'Controller';

        $className = ltrim($this->controllerNamespace . '\\' . str_replace('/', '\\', $prefix) . $className, '\\');

        $customNamespace = 'custom\\' . $className;

        if (class_exists($customNamespace)) {
            $className = $customNamespace;
        }

        if (strpos($className, '-') !== false || !class_exists($className)) {
            return null;
        }

        if (is_subclass_of($className, 'yii\base\Controller')) {
            $controller = Yii::createObject($className, [$id, $this]);
            return get_class($controller) === $className ? $controller : null;
        } elseif (YII_DEBUG) {
            throw new InvalidConfigException('Controller class must extend from \\yii\\base\\Controller.');
        }

        return null;
    }

}
