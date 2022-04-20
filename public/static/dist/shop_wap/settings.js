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
var config = {
    base_url: 'https://demo-free.kaidianxing.com/h5/api',
    attachment_url: 'https://demo-free.kaidianxing.com/data/attachment/',
    public_url: 'https://demo-free.kaidianxing.com/',
    wap_dist_url: 'https://demo-free.kaidianxing.com/static/dist/shop/kdx_wap/',
    wap_url: 'https://demo-free.kaidianxing.com/h5',
    with_live: false, //直播
    with_recharge: false// 个人中心 支付入口
};

// window.wxDebug = false;
console.log(config);

/*
*  with_live （小程序直播为true时请在以下路径内打开 playerPlugin 注释）
*  src/common/util.js    playerPlugin = null  =>  playerPlugin = requirePlugin('live-player-plugin')
* */

// with_recharge    支付
// with_live     直播

//#ifdef H5
try {
    if (window) {
        window.config = window.config ? window.config : config;
        config = window.config;
    }
} catch (e) {
}

//#endif
try {
    exports.config = config;
} catch (e) {
}
