(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[94],{"02ed":function(t,e,i){"use strict";var o=i("0bbb"),a=i.n(o);a.a},"0bbb":function(t,e,i){var o=i("802f");"string"===typeof o&&(o=[[t.i,o,""]]),o.locals&&(t.exports=o.locals);var a=i("4f06").default;a("7eeb21a8",o,!0,{sourceMap:!1,shadowMode:!1})},"1bec":function(t,e,i){"use strict";i.r(e);var o=i("a5f8"),a=i.n(o);for(var r in o)["default"].indexOf(r)<0&&function(t){i.d(e,t,(function(){return o[t]}))}(r);e["default"]=a.a},"3cc1":function(t,e,i){"use strict";var o;i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return r})),i.d(e,"a",(function(){return o}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("page-box",[i("div",{staticClass:"info-box",style:{paddingBottom:t.areaBottom+"px"}},[new Set(["20"]).has(t.status)?i("div",{staticClass:"map-box"},[i("v-uni-map",{staticClass:"map",attrs:{scale:13,markers:t.markers,"include-points":t.includePoints}}),i("div",{staticClass:"refresh",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.refresh.apply(void 0,arguments)}}},[i("i",{staticClass:"iconfont-m- icon-m-shuaxin"})])],1):t._e(),i("div",{staticClass:"track"},[i("div",{staticClass:"title"},[t._v("订单追踪")]),i("v-uni-scroll-view",{staticClass:"scroll-view",class:{height:!t.dispatch.transporter_phone},attrs:{"scroll-y":!0,"show-scrollbar":!1}},[i("ul",{staticClass:"track-list"},t._l(t.order_trace,(function(e,o){return i("li",{key:o,staticClass:"track-item van-hairline--left active"},[i("div",{staticClass:"line"}),i("div",{staticClass:"left"},[i("div",{staticClass:"dot"}),i("div",{staticClass:"text"},[t._v(t._s(e.status_text))])]),i("div",{staticClass:"right"},[t._v(t._s(t._f("formatTime")(e.status_time)))])])})),0)]),t.dispatch.transporter_phone?i("div",{staticClass:"contcat van-hairline--top",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.makePhoneCall.apply(void 0,arguments)}}},[i("i",{staticClass:"iconfont-m- icon-m-dianhua-2"}),i("div",{staticClass:"text"},[t._v("致电骑手")])]):t._e()],1)])])},r=[]},"802f":function(t,e,i){var o=i("24fb");e=o(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.iconfont-m-[data-v-414cd3c4]{font-size:%?48?%}.info-box[data-v-414cd3c4]{height:100vh;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.info-box .map-box[data-v-414cd3c4]{-webkit-box-flex:1;-webkit-flex:1;flex:1;position:relative}.info-box .map-box .map[data-v-414cd3c4]{height:100%;width:100%}.info-box .map-box .refresh[data-v-414cd3c4]{background-color:#fff;position:absolute;z-index:9999;left:%?24?%;bottom:%?16?%;width:%?64?%;height:%?64?%;border-radius:%?12?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.info-box .track[data-v-414cd3c4]{background-color:#fff;height:%?720?%;border-radius:%?20?% %?20?% 0 0;box-sizing:border-box;padding-top:%?32?%}.info-box .track .title[data-v-414cd3c4]{text-align:center;font-weight:700;font-size:%?32?%;line-height:%?44?%;color:#212121;padding-bottom:%?24?%}.info-box .track .scroll-view[data-v-414cd3c4]{height:%?540?%}.info-box .track .scroll-view.height[data-v-414cd3c4]{height:%?620?%}.info-box .track .track-list[data-v-414cd3c4]{padding:%?48?%;padding-top:%?24?%}.info-box .track .track-list .track-item[data-v-414cd3c4]{margin-left:%?8?%;display:-webkit-box;display:-webkit-flex;display:flex;font-size:%?24?%;line-height:%?34?%;color:#565656;padding-bottom:%?48?%;position:relative}.info-box .track .track-list .track-item.van-hairline--left[data-v-414cd3c4]:after{border-left-color:#e6e7eb;border-style:solid}.info-box .track .track-list .track-item:first-child .line[data-v-414cd3c4]{display:block;top:0}.info-box .track .track-list .track-item[data-v-414cd3c4]:last-child{padding-bottom:0}.info-box .track .track-list .track-item:last-child.active[data-v-414cd3c4]{color:#09c15f}.info-box .track .track-list .track-item:last-child.active .left .dot[data-v-414cd3c4]{width:%?16?%;height:%?16?%;background-color:#fff;border:%?4?% solid #09c15f;-webkit-transform:translateX(%?-8?%);transform:translateX(%?-8?%)}.info-box .track .track-list .track-item:last-child.active .left .text[data-v-414cd3c4]{font-weight:700}.info-box .track .track-list .track-item:last-child.active .right[data-v-414cd3c4]{font-weight:700}.info-box .track .track-list .track-item:last-child .line[data-v-414cd3c4]{display:block;bottom:0}.info-box .track .track-list .track-item .line[data-v-414cd3c4]{display:none;position:absolute;left:%?-2?%;background-color:#fff;width:%?1?%;height:%?18?%}.info-box .track .track-list .track-item .left[data-v-414cd3c4]{-webkit-box-flex:1;-webkit-flex:1;flex:1;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.info-box .track .track-list .track-item .left .dot[data-v-414cd3c4]{width:%?12?%;height:%?12?%;box-sizing:border-box;background-color:#c4c4c4;border-radius:50%;-webkit-transform:translateX(%?-7?%);transform:translateX(%?-7?%);position:relative;z-index:1}.info-box .track .track-list .track-item .left .text[data-v-414cd3c4]{padding-left:%?18?%;padding-right:%?24?%}.info-box .track .contcat[data-v-414cd3c4]{box-sizing:border-box;height:%?80?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;font-size:%?28?%;line-height:%?40?%;color:#212121}.info-box .track .contcat.van-hairline--top[data-v-414cd3c4]:after{border-top-color:#e6e7eb;border-style:solid}',""]),t.exports=e},a5f8:function(t,e,i){var o=i("288e");i("8e6e"),i("ac6a"),i("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a=o(i("75fc"));i("28a5");var r=o(i("bd86")),n=i("2f62"),s=o(i("a64f"));function c(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,o)}return i}function l(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?c(Object(i),!0).forEach((function(e){(0,r.default)(t,e,i[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):c(Object(i)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))}))}return t}var d={mixins:[s.default],data:function(){return{order_id:"",status:"",dispatch_type:"",dispatch:{},order_trace:[],timeId:null}},computed:l(l({},(0,n.mapState)(["areaBottom"])),{},{buyer:function(){var t,e;return{latitude:null===(t=this.dispatch)||void 0===t?void 0:t.buyer_lat,longitude:null===(e=this.dispatch)||void 0===e?void 0:e.buyer_lng,title:"买家"}},shop:function(){var t,e;return{latitude:null===(t=this.dispatch)||void 0===t?void 0:t.shop_lat,longitude:null===(e=this.dispatch)||void 0===e?void 0:e.shop_lng,title:"商家"}},transporter:function(){var t,e;return{latitude:null===(t=this.dispatch)||void 0===t?void 0:t.transporter_lat,longitude:null===(e=this.dispatch)||void 0===e?void 0:e.transporter_lng,title:"骑手"}},includePoints:function(){return this.transporter.latitude?[this.buyer,this.shop,this.transporter]:[this.buyer,this.shop]},markers:function(){var t=[this.$utils.staticMediaUrl("order/map/icon-buyer.png"),this.$utils.staticMediaUrl("order/map/icon-shop.png"),this.$utils.staticMediaUrl("order/map/icon-transporter.png")],e=this.includePoints.map((function(e,i){return{id:i,latitude:e.latitude,longitude:e.longitude,title:e.title,iconPath:t[i],width:36,height:36}}));return e}}),filters:{formatTime:function(t){return t.split(" ")[1]}},mounted:function(){var t=this;this.order_id=this.$Route.query.order_id,this.status=this.$Route.query.status,this.dispatch_type=this.$Route.query.dispatch_type,this.queryOrderStatus(),this.timeId=setInterval((function(){t.queryOrderStatus()}),1e4)},beforeDestroy:function(){clearInterval(this.timeId)},methods:{queryOrderStatus:function(){var t=this;this.$api.orderApi.queryOrderStatus({order_id:this.order_id,dispatch_type:this.dispatch_type}).then((function(e){0==e.error&&(t.status=e.data.order_status,e.data.order_trace&&(t.order_trace=(0,a.default)(e.data.order_trace)),t.dispatch=l({},e.data.dispatch))})).finally((function(){uni.hideLoading()}))},makePhoneCall:function(){this.dispatch.transporter_phone?uni.makePhoneCall({phoneNumber:this.dispatch.transporter_phone}):this.$toast("骑手未接单，获取骑手电话失败")},refresh:function(){this.queryOrderStatus()}}};e.default=d},a64f:function(t,e,i){(function(t){var o=i("288e");i("8e6e"),i("ac6a"),i("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("28a5");var a=o(i("bd86")),r=i("2f62"),n=o(i("fead")),s=(o(i("b531")),i("3014"));function c(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,o)}return i}function l(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?c(Object(i),!0).forEach((function(e){(0,a.default)(t,e,i[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):c(Object(i)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))}))}return t}var d={created:function(){this.startTime=+new Date},data:function(){return{loadingFlg:0}},watch:{isSkeleton:function(t){t||++this.loadingFlg}},mounted:function(){t.error("组建的渲染时间："+this.__route__+"："+(+new Date-this.startTime))},computed:l(l({},(0,r.mapGetters)("loading",["isSkeleton"])),(0,r.mapState)("setting",{shareTitle:function(t){var e,i;return(null===(e=t.systemSetting)||void 0===e||null===(i=e.share)||void 0===i?void 0:i.title)||""},shareDesc:function(t){var e,i;return(null===(e=t.systemSetting)||void 0===e||null===(i=e.share)||void 0===i?void 0:i.description)||""},shareLogo:function(t){var e,i;return null===(e=t.systemSetting)||void 0===e||null===(i=e.share)||void 0===i?void 0:i.logo}})),methods:{handlerOptions:function(t){if(null!==t&&void 0!==t&&t.scene){for(var e=decodeURIComponent(decodeURIComponent(null===t||void 0===t?void 0:t.scene)).split("&"),i={},o=0;o<e.length;o++){var a=e[o].split("=");i[a[0]]=a[1]}null!==i&&void 0!==i&&i.inviter_id&&s.sessionStorage.setItem("inviter-id",i.inviter_id)}}},onPullDownRefresh:function(){var t=this;"function"==typeof this.pullDownRefresh&&this.pullDownRefresh(),setTimeout((function(){t.$closePageLoading()}),2e3)},onLoad:function(t){this.showTabbar=!0},onShow:function(){var t,e,i;uni.hideLoading(),n.default.setNavigationBarColor(this.$Route),this.$decorator.getPage(this.$Route.path).onLoad();var o,a,r,c,l=this.$Route.query;(null!==l&&void 0!==l&&l.inviter_id&&s.sessionStorage.setItem("inviter-id",l.inviter_id),this.$decorator.getDecorateModel({pagePath:this.$Route.path,otherdata:l}),null!==(t=this.pageInfo)&&void 0!==t&&t.gotop&&null!==(e=this.pageInfo.gotop.params)&&void 0!==e&&e.scrollTop)?this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:(null===(o=this.pageInfo.gotop)||void 0===o||null===(a=o.params)||void 0===a?void 0:a.scrollTop)>=(null===(r=this.pageInfo.gotop)||void 0===r||null===(c=r.params)||void 0===c?void 0:c.gotopheight)}},"pagemixin/onshow1"):null!==(i=this.pageInfo)&&void 0!==i&&i.gotop&&this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1,params:{scrolltop:0}}},"pagemixin/onshow2")},onHide:function(){this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1}},"pagemixin/onhide"),this.$closePageLoading()},onPageScroll:function(t){this.$decorator.getModule("gotop").onPageScroll(t,this.$Route)}};e.default=d}).call(this,i("5a52")["default"])},b9f0:function(t,e,i){"use strict";i.r(e);var o=i("3cc1"),a=i("1bec");for(var r in a)["default"].indexOf(r)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(r);i("02ed");var n,s=i("f0c5"),c=Object(s["a"])(a["default"],o["b"],o["c"],!1,null,"414cd3c4",null,!1,o["a"],n);e["default"]=c.exports}}]);