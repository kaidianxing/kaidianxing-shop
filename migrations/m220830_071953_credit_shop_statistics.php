<?php

use yii\db\Migration;

/**
 * Class m220830_071953_credit_shop_statistics
 */
class m220830_071953_credit_shop_statistics extends Migration
{
    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'credit_shop_statistics';
    }

    /**
     * 更新
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function safeUp(): bool
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '统计日期',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建日期',
  `goods_num` int(11) NOT NULL DEFAULT '0' COMMENT '当前',
  `order_count` int(11) NOT NULL DEFAULT '0' COMMENT '订单数量',
  `order_credit_sum` int(11) NOT NULL DEFAULT '0' COMMENT '累计积分',
  `order_price_sum` decimal(10,2) NOT NULL COMMENT '累计金额',
  `view_count` int(11) NOT NULL DEFAULT '0' COMMENT '访问量',
  `member_count` int(11) NOT NULL DEFAULT '0' COMMENT '访客量',
  `wechat_order_price_sum` decimal(10,2) NOT NULL COMMENT '微信渠道订单金额',
  `wxapp_order_price_sum` decimal(10,2) NOT NULL COMMENT '微信小程序渠道订单金额',
  `h5_order_price_sum` decimal(10,2) NOT NULL COMMENT 'h5渠道订单金额',
  `byte_dance_order_price_sum` decimal(10,2) NOT NULL COMMENT '字节跳动小程序渠道订单金额',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_shop` (`date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='应用-积分商城-数据';");

        return true;
    }

    /**
     * 回滚
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function safeDown(): bool
    {
        // 删除表
        $this->dropTable($this->getTableName());

        echo "m220830_071953_credit_shop_statistics cannot be reverted.\n";

        return true;
    }
}
