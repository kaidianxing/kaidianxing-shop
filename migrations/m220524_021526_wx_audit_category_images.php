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
 * 视频号交易组件分类图片
 * Class m220524_021526_wx_audit_category_images
 * @author likexin
 */
class m220524_021526_wx_audit_category_images extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'wx_audit_category_images';
    }

    /**
     * 更新
     * @author likexin
     */
    public function safeUp(): bool
    {
        $this->execute("CREATE TABLE `{$this->getTableName()}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wx_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联自定义交易表id',
  `audit_category_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联的分类审核表id',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态 10营业执照 20资质材料',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `category_id` (`audit_category_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='应用-微信自定义交易组件-类目资质审核关联图片';");

        return true;
    }

    /**
     * 回滚
     * @author likexin
     */
    public function safeDown(): bool
    {
        // 删除表
        $this->dropTable($this->getTableName());

        echo "m220524_021526_wx_audit_category_images cannot be reverted.\n";

        return true;
    }

}
