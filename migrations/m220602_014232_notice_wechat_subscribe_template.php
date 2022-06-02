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
 * 公众号订阅消息模板
 * Class m220602_014232_notice_wechat_subscribe_template
 * @author likexin
 */
class m220602_014232_notice_wechat_subscribe_template extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'notice_wechat_subscribe_template';
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
  `title` varchar(191) NOT NULL DEFAULT '' COMMENT '模板名称',
  `scene_code` varchar(50) NOT NULL DEFAULT '' COMMENT '场景值',
  `template_id` varchar(191) NOT NULL DEFAULT '' COMMENT '模板消息tid',
  `pri_tmpl_id` varchar(191) NOT NULL DEFAULT '0' COMMENT '可发送模板消息id',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `content` mediumtext COMMENT '内容',
  `kid_list` varchar(191) NOT NULL DEFAULT '' COMMENT '字段id顺序',
  `scene_desc` varchar(191) NOT NULL DEFAULT '' COMMENT '服务场景描述',
  PRIMARY KEY (`id`),
  KEY `idx_type_code` (`scene_code`) USING BTREE,
  KEY `idx_template_code` (`template_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COMMENT='消息通知-公众号一次性订阅消息-模板库';");

        return true;
    }

    /**
     * 回滚
     * {@inheritdoc}
     * @author likexin
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());

        echo "m220602_014232_notice_wechat_subscribe_template cannot be reverted.\n";

        return true;
    }

}
