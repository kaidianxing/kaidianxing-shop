(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[92],{"46a3d":function(t,e,n){"use strict";var o=n("d47d"),i=n.n(o);i.a},"66c0":function(t,e,n){"use strict";var o;n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return a})),n.d(e,"a",(function(){return o}));var i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("page-box",{attrs:{setting:{background:"#fff"}}},[n("v-uni-view",{staticClass:"img-box"},[n("v-uni-image",{staticClass:"close-img",attrs:{src:t.$utils.staticMediaUrl("shop_close.png")}}),n("v-uni-view",{staticClass:"close-text"},[t._v(t._s("noMall"==t.from?"未找到店铺":"店铺已打烊"))])],1),t.linkurl?n("div",{staticStyle:{padding:"90rpx 50rpx 0 50rpx"}},[n("btn",{attrs:{size:"middle"},on:{"btn-click":function(e){arguments[0]=e=t.$handleEvent(e),t.skipPage.apply(void 0,arguments)}}},[t._v("查看")])],1):t._e()],1)},a=[]},"6bde":function(t,e,n){var o=n("24fb");e=o(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.img-box[data-v-694d9c52]{padding-top:%?180?%}.img-box .close-img[data-v-694d9c52]{display:block;margin:0 auto;width:%?432?%;height:%?586?%}.img-box .close-text[data-v-694d9c52]{margin-top:%?64?%;font-size:%?36?%;text-align:center;line-height:%?50?%;color:#969696}.btn[data-v-694d9c52]{margin:%?180?% auto %?100?%;border-radius:%?128?%;width:%?588?%;height:%?80?%;font-size:%?32?%;line-height:%?80?%;text-align:center;color:#fff;background:#ff3c29}',""]),t.exports=e},a64f:function(t,e,n){(function(t){var o=n("288e");n("8e6e"),n("ac6a"),n("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,n("28a5");var i=o(n("bd86")),a=n("2f62"),s=o(n("fead")),r=(o(n("b531")),n("3014"));function l(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,o)}return n}function u(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?l(Object(n),!0).forEach((function(e){(0,i.default)(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):l(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}var c={created:function(){this.startTime=+new Date},data:function(){return{loadingFlg:0}},watch:{isSkeleton:function(t){t||++this.loadingFlg}},mounted:function(){t.error("组建的渲染时间："+this.__route__+"："+(+new Date-this.startTime))},computed:u(u({},(0,a.mapGetters)("loading",["isSkeleton"])),(0,a.mapState)("setting",{shareTitle:function(t){var e,n;return(null===(e=t.systemSetting)||void 0===e||null===(n=e.share)||void 0===n?void 0:n.title)||""},shareDesc:function(t){var e,n;return(null===(e=t.systemSetting)||void 0===e||null===(n=e.share)||void 0===n?void 0:n.description)||""},shareLogo:function(t){var e,n;return null===(e=t.systemSetting)||void 0===e||null===(n=e.share)||void 0===n?void 0:n.logo}})),methods:{handlerOptions:function(t){if(null!==t&&void 0!==t&&t.scene){for(var e=decodeURIComponent(decodeURIComponent(null===t||void 0===t?void 0:t.scene)).split("&"),n={},o=0;o<e.length;o++){var i=e[o].split("=");n[i[0]]=i[1]}null!==n&&void 0!==n&&n.inviter_id&&r.sessionStorage.setItem("inviter-id",n.inviter_id)}}},onPullDownRefresh:function(){var t=this;"function"==typeof this.pullDownRefresh&&this.pullDownRefresh(),setTimeout((function(){t.$closePageLoading()}),2e3)},onLoad:function(t){this.showTabbar=!0},onShow:function(){var t,e,n;uni.hideLoading(),s.default.setNavigationBarColor(this.$Route),this.$decorator.getPage(this.$Route.path).onLoad();var o,i,a,l,u=this.$Route.query;(null!==u&&void 0!==u&&u.inviter_id&&r.sessionStorage.setItem("inviter-id",u.inviter_id),this.$decorator.getDecorateModel({pagePath:this.$Route.path,otherdata:u}),null!==(t=this.pageInfo)&&void 0!==t&&t.gotop&&null!==(e=this.pageInfo.gotop.params)&&void 0!==e&&e.scrollTop)?this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:(null===(o=this.pageInfo.gotop)||void 0===o||null===(i=o.params)||void 0===i?void 0:i.scrollTop)>=(null===(a=this.pageInfo.gotop)||void 0===a||null===(l=a.params)||void 0===l?void 0:l.gotopheight)}},"pagemixin/onshow1"):null!==(n=this.pageInfo)&&void 0!==n&&n.gotop&&this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1,params:{scrolltop:0}}},"pagemixin/onshow2")},onHide:function(){this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1}},"pagemixin/onhide"),this.$closePageLoading()},onPageScroll:function(t){this.$decorator.getModule("gotop").onPageScroll(t,this.$Route)}};e.default=c}).call(this,n("5a52")["default"])},aae1:function(t,e,n){var o=n("288e");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=n("325c"),a=(n("858b"),o(n("a64f"))),s={mixins:[a.default],data:function(){return{linkurl:"",from:""}},mounted:function(){var t,e=this;this.$loading.hideLoading(),this.from=this.$Route.query.from,this.linkurl=null===(t=this.$store.state.setting.systemSetting.basic)||void 0===t?void 0:t.mall_close_url,this.$store.dispatch("setting/getSysSetting").then((function(t){if(t){var n=t.basic.mall_status;e.$store.commit("setting/setSystemSetting",t),e.$store.commit("setting/setCloseStatus","0"==n),"1"==n&&e.getChannelStatus()}}))},methods:{skipPage:function(){window.open("http://"+this.linkurl)},getChannelStatus:function(){var t=this,e=this.$store.state.setting.systemSetting.admin_status,n=this.$store.state.setting.systemSetting.basic.mall_status;0!=e&&"0"!=n&&this.$store.dispatch("setting/getChannelStatus").then((function(e){e&&(t.$store.commit("setting/setChannelStatus",e),i.isWx&&1==e.wechat||!(0,i.is_weixin)()&&1==e.h5?(t.$store.commit("setting/setCloseStatus",!1),t.$Router.replaceAll({path:"/"})):t.$store.commit("setting/setCloseStatus",!0))}))}}};e.default=s},d47d:function(t,e,n){var o=n("6bde");"string"===typeof o&&(o=[[t.i,o,""]]),o.locals&&(t.exports=o.locals);var i=n("4f06").default;i("2c297ca5",o,!0,{sourceMap:!1,shadowMode:!1})},d493:function(t,e,n){"use strict";n.r(e);var o=n("aae1"),i=n.n(o);for(var a in o)["default"].indexOf(a)<0&&function(t){n.d(e,t,(function(){return o[t]}))}(a);e["default"]=i.a},e5b6:function(t,e,n){"use strict";n.r(e);var o=n("66c0"),i=n("d493");for(var a in i)["default"].indexOf(a)<0&&function(t){n.d(e,t,(function(){return i[t]}))}(a);n("46a3d");var s,r=n("f0c5"),l=Object(r["a"])(i["default"],o["b"],o["c"],!1,null,"694d9c52",null,!1,o["a"],s);e["default"]=l.exports}}]);