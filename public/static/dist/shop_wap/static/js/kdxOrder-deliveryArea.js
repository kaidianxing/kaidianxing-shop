(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[81],{"0fc9f":function(t,e,i){"use strict";i.r(e);var o=i("3035"),n=i("c412");for(var a in n)["default"].indexOf(a)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(a);i("d0b5");var r,s=i("f0c5"),l=Object(s["a"])(n["default"],o["b"],o["c"],!1,null,"549a7b91",null,!1,o["a"],r);e["default"]=l.exports},3035:function(t,e,i){"use strict";var o;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return a})),i.d(e,"a",(function(){return o}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("page-box",[i("div",{staticClass:"area-box",style:{paddingBottom:t.areaBottom+"px"}},[i("div",{staticClass:"map-box"},[i("v-uni-map",{staticClass:"map",attrs:{id:"map",scale:15,latitude:t.lat,longitude:t.lng,markers:t.markers,circles:t.circles,polygons:t.polygons}})],1),i("div",{staticClass:"info"},[i("div",{staticClass:"left"},[i("div",{staticClass:"left-l"},[t.is_merchant?i("img",{staticStyle:{height:"100%"},attrs:{mode:"widthFix",src:t.$utils.mediaUrl(t.merchant_info.logo),alt:""}}):i("img",{attrs:{mode:"widthFix",src:t.logo,alt:""}})]),i("div",{staticClass:"left-r"},[t.is_merchant?i("div",{staticClass:"name"},[t._v(t._s(t.merchant_info.name))]):i("div",{staticClass:"name"},[t._v(t._s(t.systemSetting.basic.name))]),i("div",{staticClass:"addr"},[t._v(t._s(t.address))])])]),i("div",{staticClass:"right"},[i("span",{staticClass:"iconfont-m- icon-m-dianhua",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.fnCall.apply(void 0,arguments)}}})])])])])},a=[]},"595c":function(t,e,i){var o=i("cbfa");"string"===typeof o&&(o=[[t.i,o,""]]),o.locals&&(t.exports=o.locals);var n=i("4f06").default;n("a833c6ac",o,!0,{sourceMap:!1,shadowMode:!1})},a64f:function(t,e,i){(function(t){var o=i("288e");i("8e6e"),i("ac6a"),i("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("28a5");var n=o(i("bd86")),a=i("2f62"),r=o(i("fead")),s=(o(i("b531")),i("3014"));function l(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,o)}return i}function d(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?l(Object(i),!0).forEach((function(e){(0,n.default)(t,e,i[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):l(Object(i)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))}))}return t}var c={created:function(){this.startTime=+new Date},data:function(){return{loadingFlg:0}},watch:{isSkeleton:function(t){t||++this.loadingFlg}},mounted:function(){t.error("组建的渲染时间："+this.__route__+"："+(+new Date-this.startTime))},computed:d(d({},(0,a.mapGetters)("loading",["isSkeleton"])),(0,a.mapState)("setting",{shareTitle:function(t){var e,i;return(null===(e=t.systemSetting)||void 0===e||null===(i=e.share)||void 0===i?void 0:i.title)||""},shareDesc:function(t){var e,i;return(null===(e=t.systemSetting)||void 0===e||null===(i=e.share)||void 0===i?void 0:i.description)||""},shareLogo:function(t){var e,i;return null===(e=t.systemSetting)||void 0===e||null===(i=e.share)||void 0===i?void 0:i.logo}})),methods:{handlerOptions:function(t){if(null!==t&&void 0!==t&&t.scene){for(var e=decodeURIComponent(decodeURIComponent(null===t||void 0===t?void 0:t.scene)).split("&"),i={},o=0;o<e.length;o++){var n=e[o].split("=");i[n[0]]=n[1]}null!==i&&void 0!==i&&i.inviter_id&&s.sessionStorage.setItem("inviter-id",i.inviter_id)}}},onPullDownRefresh:function(){var t=this;"function"==typeof this.pullDownRefresh&&this.pullDownRefresh(),setTimeout((function(){t.$closePageLoading()}),2e3)},onLoad:function(t){this.showTabbar=!0},onShow:function(){var t,e,i;uni.hideLoading(),r.default.setNavigationBarColor(this.$Route),this.$decorator.getPage(this.$Route.path).onLoad();var o,n,a,l,d=this.$Route.query;(null!==d&&void 0!==d&&d.inviter_id&&s.sessionStorage.setItem("inviter-id",d.inviter_id),this.$decorator.getDecorateModel({pagePath:this.$Route.path,otherdata:d}),null!==(t=this.pageInfo)&&void 0!==t&&t.gotop&&null!==(e=this.pageInfo.gotop.params)&&void 0!==e&&e.scrollTop)?this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:(null===(o=this.pageInfo.gotop)||void 0===o||null===(n=o.params)||void 0===n?void 0:n.scrollTop)>=(null===(a=this.pageInfo.gotop)||void 0===a||null===(l=a.params)||void 0===l?void 0:l.gotopheight)}},"pagemixin/onshow1"):null!==(i=this.pageInfo)&&void 0!==i&&i.gotop&&this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1,params:{scrolltop:0}}},"pagemixin/onshow2")},onHide:function(){this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1}},"pagemixin/onhide"),this.$closePageLoading()},onPageScroll:function(t){this.$decorator.getModule("gotop").onPageScroll(t,this.$Route)}};e.default=c}).call(this,i("5a52")["default"])},c412:function(t,e,i){"use strict";i.r(e);var o=i("ff20"),n=i.n(o);for(var a in o)["default"].indexOf(a)<0&&function(t){i.d(e,t,(function(){return o[t]}))}(a);e["default"]=n.a},cbfa:function(t,e,i){var o=i("24fb");e=o(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.area-box[data-v-549a7b91]{height:100vh;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.area-box .map-box[data-v-549a7b91]{-webkit-box-flex:1;-webkit-flex:1;flex:1;height:calc(100% - %?112?%)}.area-box .map-box .map[data-v-549a7b91]{height:100%;width:100%}.area-box .info[data-v-549a7b91]{background-color:#fff;-webkit-flex-shrink:0;flex-shrink:0;height:%?112?%;padding-left:%?24?%;padding-right:%?24?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.area-box .info .left[data-v-549a7b91]{box-sizing:border-box;width:calc(100% - %?72?%);display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.area-box .info .left .left-l[data-v-549a7b91]{-webkit-flex-shrink:0;flex-shrink:0;width:%?80?%;height:%?80?%;border-radius:%?12?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;border:%?2?% solid #e6e7eb}.area-box .info .left .left-l img[data-v-549a7b91]{width:100%}.area-box .info .left .left-r[data-v-549a7b91]{padding-left:%?16?%;box-sizing:border-box;width:calc(100% - %?160?%)}.area-box .info .left .left-r .name[data-v-549a7b91]{font-size:%?28?%;line-height:%?40?%;color:#212121}.area-box .info .left .left-r .addr[data-v-549a7b91]{width:100%;box-sizing:border-box;margin-top:%?4?%;font-size:%?24?%;line-height:%?34?%;color:#969696;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.area-box .info .right .iconfont-m-[data-v-549a7b91]{font-size:%?72?%;color:#19be6b}',""]),t.exports=e},d0b5:function(t,e,i){"use strict";var o=i("595c"),n=i.n(o);n.a},ff20:function(t,e,i){(function(t){var o=i("288e");i("8e6e"),i("ac6a"),i("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=o(i("75fc")),a=o(i("bd86")),r=i("2f62"),s=o(i("a64f"));function l(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,o)}return i}function d(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?l(Object(i),!0).forEach((function(e){(0,a.default)(t,e,i[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):l(Object(i)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))}))}return t}var c={mixins:[s.default],data:function(){return{shop_address:{},division_way:"",dispatch_area:[],circles:[],polygons:[],merchant_info:{}}},computed:d(d(d({},(0,r.mapState)(["areaBottom"])),(0,r.mapState)("setting",{systemSetting:function(t){return t.systemSetting},is_merchant:function(t){return t.is_merchant}})),{},{logo:function(){var t,e;return this.$utils.mediaUrl(null===(t=this.systemSetting)||void 0===t||null===(e=t.basic)||void 0===e?void 0:e.logo)},address:function(){var t,e,i,o,n,a,r,s,l,d;return null!==(t=this.shop_address)&&void 0!==t&&null!==(e=t.address)&&void 0!==e&&e.province?"".concat(null===(i=this.shop_address)||void 0===i||null===(o=i.address)||void 0===o?void 0:o.province).concat(null===(n=this.shop_address)||void 0===n||null===(a=n.address)||void 0===a?void 0:a.city).concat(null===(r=this.shop_address)||void 0===r||null===(s=r.address)||void 0===s?void 0:s.area).concat(null===(l=this.shop_address)||void 0===l||null===(d=l.address)||void 0===d?void 0:d.detail):""},lng:function(){var t,e,i,o,n;return this.is_merchant?null===(t=this.shop_address)||void 0===t||null===(e=t.address)||void 0===e?void 0:e.lng:null===(i=this.shop_address)||void 0===i||null===(o=i.address)||void 0===o||null===(n=o.address)||void 0===n?void 0:n.lng},lat:function(){var t,e,i,o,n;return this.is_merchant?null===(t=this.shop_address)||void 0===t||null===(e=t.address)||void 0===e?void 0:e.lat:null===(i=this.shop_address)||void 0===i||null===(o=i.address)||void 0===o||null===(n=o.address)||void 0===n?void 0:n.lat},markers:function(){return[{id:0,latitude:this.lat,longitude:this.lng,title:this.address}]}}),mounted:function(){this.getDispatchArea()},methods:{getDispatchArea:function(){var e=this,i={};this.is_merchant&&(i={sub_shop_id:this.$Route.query.sub_shop_id}),this.$api.orderApi.getDispatchArea(i).then((function(i){t.log(i),0==i.error?(e.shop_address=d({},i.data.shop_address),e.is_merchant&&(e.merchant_info=d({},i.data.merchant_info)),e.division_way=i.data.division_way,e.dispatch_area=(0,n.default)(i.data.dispatch_area),0==e.division_way?e.circles=e.dispatch_area.map((function(t){return{latitude:1*t.center_lat,longitude:1*t.center_lng,radius:1e3*t.radius,strokeWidth:2,fillColor:"#4a67ff33",color:"#4a67ff"}})):e.circles=[],1===e.division_way?e.polygons=e.dispatch_area.map((function(t){var e=t.location.map((function(t){return{latitude:1*t.lat,longitude:1*t.lng}}));return{points:e,strokeWidth:2,fillColor:"#4a67ff33",strokeColor:"#4a67ff",zIndex:1}})):e.polygons=[]):e.$toast(i.message)}))},fnCall:function(){var t,e,i=(null===(t=this.shop_address.address)||void 0===t?void 0:t.tel1)||(null===(e=this.shop_address)||void 0===e?void 0:e.tel1);i&&uni.makePhoneCall({phoneNumber:i})}}};e.default=c}).call(this,i("5a52")["default"])}}]);