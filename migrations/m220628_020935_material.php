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

use yii\db\Migration;

/**
 * 一键发圈表结构
 * Class m220628_020935_material
 * @package migrations
 * @author likexin
 */
class m220628_020935_material extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'material';
    }

    /**
     * 更新
     * {@inheritdoc}
     * @author likexin
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS  `{$this->getTableName()}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `goods` mediumtext COMMENT '支持商品json',
  `description_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 系统默认 1 自定义',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '介绍',
  `material_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 图片 1 视频',
  `thumb_all` text COMMENT '所有商品封面图',
  `video` varchar(191) NOT NULL DEFAULT '' COMMENT '首图视频',
  `video_thumb` varchar(191) NOT NULL DEFAULT '' COMMENT '视频首图',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 1删除',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_goods_id` (`goods_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='一键发圈';");

        return true;
    }

    /**
     * 回滚
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());

        echo "m220628_020935_material cannot be reverted.\n";

        return true;
    }

}
