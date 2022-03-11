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

namespace shopstar\components\storage\bases;

use yii\base\Component;

/**
 * 存储实现基类
 * Class BaseStorageDriver
 * @package shopstar\components\storage
 */
class BaseStorageDriver extends Component
{

    /**
     * @var string 访问URL
     */
    public $url;

    /**
     * @var string 访问协议
     */
    public $scheme;

    /**
     * @var array|null 错误信息
     */
    protected $error = null;

    /**
     * 获取错误信息
     * @return array
     * @author likexin
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 设置错误信息
     * @param string|array|\Exception $error 错误信息
     * @author likexin
     */
    public function setError($error)
    {
        if ($error instanceof \Exception) {
            $this->error = $error->getMessage();
        } elseif (is_error($error)) {
            $this->error = $error;
        } else {
            $this->error = error($error);
        }
    }

    /**
     * 初始化
     * @author likexin
     */
    public function init()
    {
        $this->connect();
    }

    /**
     * 连接服务
     * @author likexin
     */
    public function connect()
    {
    }

}