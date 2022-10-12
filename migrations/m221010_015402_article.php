<?php

use yii\db\Migration;

/**
 * Class m221010_015402_article
 */
class m221010_015402_article extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'article';
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组id',
  `group_id_origin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '原分组id, 分组隐藏后,显示, 需切回到原分组',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '标题',
  `cover` varchar(191) NOT NULL DEFAULT '' COMMENT '封面图片地址',
  `digest` varchar(120) NOT NULL DEFAULT '' COMMENT '文章简介',
  `author` varchar(20) NOT NULL DEFAULT '' COMMENT '作者',
  `display_order` int(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序(0-9999 数字越大越靠前)',
  `content` mediumtext COMMENT '详情',
  `content_origin` mediumtext COMMENT '详情原始数据-后台编辑时熏染用',
  `goods_ids` text COMMENT '文章包含的商品ids',
  `coupon_ids` text COMMENT '文章中包含的优惠券ids',
  `read_number_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '阅读数状态 0:隐藏 1:显示',
  `read_number_init` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '初始阅读数',
  `read_number` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读数',
  `read_number_step` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '阅读数增长步长,取随机 1-n内随机',
  `read_number_real` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '真实阅读数',
  `thumps_up_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '点赞数状态 0:隐藏 1:显示',
  `thumps_up_number_init` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '初始点赞数',
  `thumps_up_number` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞数',
  `thumps_up_number_real` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '真实点赞数',
  `share_number` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分享数',
  `share_number_real` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '真实分享数',
  `member_level_limit_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '会员等级限制类型 0: 不限制 1:指定等级',
  `member_level_limit_ids` text COMMENT '会员等级限制-等级',
  `commission_level_limit_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '分销商等级限制类型 0: 不限制 1:指定等级',
  `commission_level_limit_ids` text COMMENT '分销商等级限制-等级',
  `reward_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '奖励类型 0: 无奖励 1:积分 2:余额',
  `reward_rule` text COMMENT '奖励规则 credit 积分   once: 每次获得的积分   max: 最多获得的积分 balance 余额   first: 第一次获得的余额   max: 最多获得的余额 ',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 0: 隐藏(未发布) 1:显示(发布)',
  `is_top` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '置顶 0: 不置顶 1: 置顶',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1: 已删除',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `top_thumb` varchar(191) NOT NULL DEFAULT '' COMMENT '文章头图',
  `top_thumb_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '头图类型 0 单图 1轮播',
  `top_thumb_all` text COMMENT '轮播图',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_category_id` (`group_id`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_group_id_origin` (`group_id_origin`) USING BTREE,
  KEY `idx_display_order` (`display_order`) USING BTREE,
  KEY `idx_topping` (`is_top`) USING BTREE,
  KEY `idx_is_delete` (`is_deleted`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='应用-文章营销';");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());
        echo "m221010_015402_article cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221010_015402_article cannot be reverted.\n";

        return false;
    }
    */
}
