(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[74],{"19e5":function(t,o,n){var e=n("288e");n("8e6e"),n("ac6a"),n("456d"),Object.defineProperty(o,"__esModule",{value:!0}),o.default=void 0,n("a481");var i=e(n("bd86")),a=e(n("c96e")),c=e(n("a64f"));function r(t,o){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var e=Object.getOwnPropertySymbols(t);o&&(e=e.filter((function(o){return Object.getOwnPropertyDescriptor(t,o).enumerable}))),n.push.apply(n,e)}return n}function f(t){for(var o=1;o<arguments.length;o++){var n=null!=arguments[o]?arguments[o]:{};o%2?r(Object(n),!0).forEach((function(o){(0,i.default)(t,o,n[o])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):r(Object(n)).forEach((function(o){Object.defineProperty(t,o,Object.getOwnPropertyDescriptor(n,o))}))}return t}var s={mixins:[c.default],components:{goodsLike:a.default},data:function(){return{couponId:"",couponInfo:{},requestFlag:!1,credit_text:""}},filters:{formatPrice:function(t){return parseFloat(t)}},mounted:function(){this.credit_text=this.$store.state.setting.systemSetting.credit_text||"积分",this.couponId=this.$Route.query.id,this.getCouponDetail()},methods:{getCouponDetail:function(){var t=this;this.$api.memberApi.getCouponDetail({id:this.couponId}).then((function(o){0===o.error?t.couponInfo=f({},o.data):uni.showToast({title:o.message,icon:"none"}),t.requestFlag=!0}))},jumpIndex:function(){this.$Router.replace({path:"/"})}}};o.default=s},"28e5":function(t,o,n){"use strict";n.r(o);var e=n("8f35"),i=n("b071");for(var a in i)["default"].indexOf(a)<0&&function(t){n.d(o,t,(function(){return i[t]}))}(a);n("c880");var c,r=n("f0c5"),f=Object(r["a"])(i["default"],e["b"],e["c"],!1,null,"f650d848",null,!1,e["a"],c);o["default"]=f.exports},7980:function(t,o,n){var e=n("24fb");o=e(!1),o.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.content[data-v-f650d848]{position:relative;min-height:100vh;min-height:calc(100vh - 44px)}.content .content-wrap[data-v-f650d848]{padding:%?48?% %?24?% %?32?%;padding-bottom:0;margin-bottom:%?48?%}.content img.bg[data-v-f650d848]{position:absolute;width:100%;top:0;left:0}.content .content-info[data-v-f650d848]{background-color:#fff;border-radius:%?12?%;box-shadow:0 0 10px rgba(0,0,0,.1);position:relative;padding-left:%?24?%;padding-right:%?24?%;padding-bottom:%?32?%}.content .content-info img.content-info-bg[data-v-f650d848]{position:absolute;width:%?488?%;left:%?88?%;top:0}.content .content-info .receive-info[data-v-f650d848]{position:relative;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-align:center;-webkit-align-items:center;align-items:center;padding-bottom:%?52?%}.content .content-info .receive-info .icon[data-v-f650d848]{color:#ff3c29;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;font-size:%?140?%;line-height:1;margin-top:%?32?%;margin-bottom:%?32?%}.content .content-info .receive-info .congratulate[data-v-f650d848]{font-weight:700;font-size:%?36?%;line-height:%?50?%;color:#ff3c29}.content .content-info .receive-info .use[data-v-f650d848]{margin-top:%?8?%;font-weight:500;font-size:%?24?%;line-height:%?34?%;color:#969696}.content .content-info .receive-info .use span[data-v-f650d848]{color:#ff3c29}.content .content-info .receive-info .use.hidden[data-v-f650d848]{visibility:hidden}.content .content-info .coupon-info[data-v-f650d848]{position:relative;box-sizing:border-box;padding-top:%?14?%}.content .content-info .coupon-info img.coupon-info-bg[data-v-f650d848]{position:absolute;width:100%;top:0;left:0}.content .content-info .coupon-info .coupon[data-v-f650d848]{padding-left:%?24?%;padding-right:%?24?%;position:relative}.content .content-info .coupon-info .coupon .bg[data-v-f650d848]{position:absolute;top:0;left:%?24?%;z-index:2;width:calc(100% - %?48?%);height:%?20?%;background:-webkit-linear-gradient(top,rgba(0,0,0,.3),hsla(0,0%,76.9%,0));background:linear-gradient(180deg,rgba(0,0,0,.3),hsla(0,0%,76.9%,0))}.content .content-info .coupon-info .coupon .info[data-v-f650d848]{box-shadow:0 2px 10px rgba(0,0,0,.05);position:relative;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;background-color:#fff;border-bottom-right-radius:%?8?%;border-bottom-left-radius:%?8?%;overflow:hidden}.content .content-info .coupon-info .coupon .info .left[data-v-f650d848]{width:%?204?%;height:%?160?%;-webkit-flex-shrink:0;flex-shrink:0;position:relative;font-weight:500;font-size:%?24?%;line-height:%?34?%;color:#fff;background-color:#ff3c29;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;text-align:center}.content .content-info .coupon-info .coupon .info .left .box[data-v-f650d848]{margin-top:%?-16?%}.content .content-info .coupon-info .coupon .info .left .box .price[data-v-f650d848]{font-size:%?28?%}.content .content-info .coupon-info .coupon .info .left .box .price .num[data-v-f650d848]{font-size:%?48?%;line-height:%?100?%}.content .content-info .coupon-info .coupon .info .left .box .rule[data-v-f650d848]{margin-top:%?-8?%}.content .content-info .coupon-info .coupon .info .left.coupon-type1[data-v-f650d848]{background-color:#ff3c29}.content .content-info .coupon-info .coupon .info .left.coupon-type2[data-v-f650d848]{background-color:#518def}.content .content-info .coupon-info .coupon .info .left .dot-list[data-v-f650d848]{position:absolute;right:%?-6?%;top:%?-6?%}.content .content-info .coupon-info .coupon .info .left .dot-list li[data-v-f650d848]{width:%?12?%;height:%?12?%;border-radius:50%;background-color:#f5f5f5;margin-bottom:%?12?%}.content .content-info .coupon-info .coupon .info .left .dot-list li[data-v-f650d848]:nth-child(1){margin-bottom:%?8?%}.content .content-info .coupon-info .coupon .info .left .dot-list li[data-v-f650d848]:nth-child(7){margin-bottom:%?8?%}.content .content-info .coupon-info .coupon .info .left .dot-list li[data-v-f650d848]:nth-child(8){margin-bottom:0}.content .content-info .coupon-info .coupon .info .right[data-v-f650d848]{-webkit-box-flex:1;-webkit-flex:1;flex:1;height:%?160?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;padding-left:%?24?%;padding-right:%?24?%}.content .content-info .coupon-info .coupon .info .right .r-left[data-v-f650d848]{font-weight:500;-webkit-box-flex:1;-webkit-flex:1;flex:1}.content .content-info .coupon-info .coupon .info .right .r-left .name[data-v-f650d848]{font-size:%?24?%;line-height:%?34?%;color:#212121;margin-bottom:%?16?%}.content .content-info .coupon-info .coupon .info .right .r-left .time[data-v-f650d848]{font-size:%?20?%;line-height:%?28?%;color:#969696}.content .content-info .coupon-info .coupon .info[data-v-f650d848]:last-child{border-bottom:none}.content .content-info .btn[data-v-f650d848]{margin-top:%?96?%;font-weight:500;font-size:%?28?%;line-height:%?40?%;color:#fff;background:-webkit-linear-gradient(277.58deg,#ff3c29,#ff6f29 94.38%);background:linear-gradient(172.42deg,#ff3c29,#ff6f29 94.38%);height:%?80?%;border-radius:%?40?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}',""]),t.exports=o},"8f35":function(t,o,n){"use strict";var e;n.d(o,"b",(function(){return i})),n.d(o,"c",(function(){return a})),n.d(o,"a",(function(){return e}));var i=function(){var t=this,o=t.$createElement,n=t._self._c||o;return n("page-box",[n("div",{staticClass:"content"},[n("v-uni-view",{staticClass:"content-wrap"},[n("img",{staticClass:"bg",attrs:{mode:"widthFix",src:t.$utils.staticMediaUrl("member/coupon-detail-bg.png"),alt:""}}),t.requestFlag?n("div",{staticClass:"content-info"},[n("img",{staticClass:"content-info-bg",attrs:{src:t.$utils.staticMediaUrl("member/receive-bg.png"),alt:""}}),n("div",{staticClass:"receive-info"},[n("div",{staticClass:"icon iconfont-m- icon-m-yes"}),n("div",{staticClass:"congratulate"},[t._v("恭喜您领取成功")]),n("div",{staticClass:"use",class:{hidden:"1"===t.couponInfo.is_free}},[t._v("您购买优惠券共消耗"),n("span",[t._v(t._s(t.couponInfo.credit)+t._s(t.credit_text)+"+"+t._s(t.couponInfo.balance)+"元")])])]),n("div",{staticClass:"coupon-info"},[n("img",{staticClass:"coupon-info-bg",attrs:{src:t.$utils.staticMediaUrl("member/receive-coupon-bg.png"),mode:"widthFix"}}),n("div",{staticClass:"coupon"},[n("div",{staticClass:"bg"}),n("div",{staticClass:"info"},[n("div",{staticClass:"left",class:{"coupon-type1":"1"===t.couponInfo.coupon_sale_type,"coupon-type2":"2"===t.couponInfo.coupon_sale_type}},[n("div",{staticClass:"box"},[n("div",{staticClass:"price"},["1"===t.couponInfo.coupon_sale_type?n("span",[t._v("¥")]):t._e(),n("span",{staticClass:"num"},[t._v(t._s(t._f("formatPrice")(t.couponInfo.discount_price)))]),"2"===t.couponInfo.coupon_sale_type?n("span",[t._v("折")]):t._e()]),n("div",{staticClass:"rule"},[t._v("满￥"+t._s(t._f("formatPrice")(t.couponInfo.enough))+"元可用")])]),n("ul",{staticClass:"dot-list"},t._l(8,(function(t){return n("li",{key:t})})),0)]),n("div",{staticClass:"right"},[n("div",{staticClass:"r-left"},[n("div",{staticClass:"name"},[t._v(t._s(t.couponInfo.coupon_name))]),"0"===t.couponInfo.time_limit&&(t.couponInfo.start_time||t.couponInfo.end_time)||"1"===t.couponInfo.time_limit?n("div",{staticClass:"time"},[t._v(t._s("0"===t.couponInfo.time_limit?t.couponInfo.start_time+"~"+t.couponInfo.end_time:"0"===t.couponInfo.limit_day?"无限制":"即领取日内"+t.couponInfo.limit_day+"天有效"))]):t._e()])])])])]),n("btn",{attrs:{styles:"margin-top:96rpx;",type:"do",size:"middle"},on:{"btn-click":function(o){arguments[0]=o=t.$handleEvent(o),t.jumpIndex.apply(void 0,arguments)}}},[t._v("去使用")])],1):t._e()]),n("goods-like")],1)])},a=[]},a64f:function(t,o,n){(function(t){var e=n("288e");n("8e6e"),n("ac6a"),n("456d"),Object.defineProperty(o,"__esModule",{value:!0}),o.default=void 0,n("28a5");var i=e(n("bd86")),a=n("2f62"),c=e(n("fead")),r=(e(n("b531")),n("3014"));function f(t,o){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var e=Object.getOwnPropertySymbols(t);o&&(e=e.filter((function(o){return Object.getOwnPropertyDescriptor(t,o).enumerable}))),n.push.apply(n,e)}return n}function s(t){for(var o=1;o<arguments.length;o++){var n=null!=arguments[o]?arguments[o]:{};o%2?f(Object(n),!0).forEach((function(o){(0,i.default)(t,o,n[o])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):f(Object(n)).forEach((function(o){Object.defineProperty(t,o,Object.getOwnPropertyDescriptor(n,o))}))}return t}var l={created:function(){this.startTime=+new Date},data:function(){return{loadingFlg:0}},watch:{isSkeleton:function(t){t||++this.loadingFlg}},mounted:function(){t.error("组建的渲染时间："+this.__route__+"："+(+new Date-this.startTime))},computed:s(s({},(0,a.mapGetters)("loading",["isSkeleton"])),(0,a.mapState)("setting",{shareTitle:function(t){var o,n;return(null===(o=t.systemSetting)||void 0===o||null===(n=o.share)||void 0===n?void 0:n.title)||""},shareDesc:function(t){var o,n;return(null===(o=t.systemSetting)||void 0===o||null===(n=o.share)||void 0===n?void 0:n.description)||""},shareLogo:function(t){var o,n;return null===(o=t.systemSetting)||void 0===o||null===(n=o.share)||void 0===n?void 0:n.logo}})),methods:{handlerOptions:function(t){if(null!==t&&void 0!==t&&t.scene){for(var o=decodeURIComponent(decodeURIComponent(null===t||void 0===t?void 0:t.scene)).split("&"),n={},e=0;e<o.length;e++){var i=o[e].split("=");n[i[0]]=i[1]}null!==n&&void 0!==n&&n.inviter_id&&r.sessionStorage.setItem("inviter-id",n.inviter_id)}}},onPullDownRefresh:function(){var t=this;"function"==typeof this.pullDownRefresh&&this.pullDownRefresh(),setTimeout((function(){t.$closePageLoading()}),2e3)},onLoad:function(t){this.showTabbar=!0},onShow:function(){var t,o,n;uni.hideLoading(),c.default.setNavigationBarColor(this.$Route),this.$decorator.getPage(this.$Route.path).onLoad();var e,i,a,f,s=this.$Route.query;(null!==s&&void 0!==s&&s.inviter_id&&r.sessionStorage.setItem("inviter-id",s.inviter_id),this.$decorator.getDecorateModel({pagePath:this.$Route.path,otherdata:s}),null!==(t=this.pageInfo)&&void 0!==t&&t.gotop&&null!==(o=this.pageInfo.gotop.params)&&void 0!==o&&o.scrollTop)?this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:(null===(e=this.pageInfo.gotop)||void 0===e||null===(i=e.params)||void 0===i?void 0:i.scrollTop)>=(null===(a=this.pageInfo.gotop)||void 0===a||null===(f=a.params)||void 0===f?void 0:f.gotopheight)}},"pagemixin/onshow1"):null!==(n=this.pageInfo)&&void 0!==n&&n.gotop&&this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1,params:{scrolltop:0}}},"pagemixin/onshow2")},onHide:function(){this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1}},"pagemixin/onhide"),this.$closePageLoading()},onPageScroll:function(t){this.$decorator.getModule("gotop").onPageScroll(t,this.$Route)}};o.default=l}).call(this,n("5a52")["default"])},b071:function(t,o,n){"use strict";n.r(o);var e=n("19e5"),i=n.n(e);for(var a in e)["default"].indexOf(a)<0&&function(t){n.d(o,t,(function(){return e[t]}))}(a);o["default"]=i.a},c880:function(t,o,n){"use strict";var e=n("f2c3"),i=n.n(e);i.a},f2c3:function(t,o,n){var e=n("7980");"string"===typeof e&&(e=[[t.i,e,""]]),e.locals&&(t.exports=e.locals);var i=n("4f06").default;i("5499ff1c",e,!0,{sourceMap:!1,shadowMode:!1})}}]);