(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[62],{"200b":function(t,i,e){(function(t){var o=e("4ea4");Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0,e("a481"),e("ac6a");var n=o(e("3a63")),a={components:{Goods:n.default},data:function(){return{type:"activity",goodsID:"",list:[],page:1,loading:!1,count:0,reqesting:!1,activityId:"",countTime:["0","23","59","59"],activeInfo:{},isFinish:!1}},computed:{loadingType:function(){var t=0;return this.loading?t=1:this.list.length==this.count&&this.count>0&&0==this.loading&&(t=2),t}},onLoad:function(){"activity"===this.$Route.query.type?this.activityId=this.$Route.query.id:"goods"===this.$Route.query.type&&(this.goodsID=this.$Route.query.goodsID),this.type=this.$Route.query.type},onShow:function(){this.reset()},methods:{getList:function(){var t=this;if(!this.reqesting){this.reqesting=!0;var i={page:this.page,get_activity:1,activity_type:"groups"};"activity"===this.type?i.activity_id=this.activityId:i.id=this.goodsID,this.loading=!0,this.$api.goodApi.goodList(i).then((function(i){0==i.error&&(i.list.length>0&&("goods"===t.type&&i.list.forEach((function(i){var e,o;i.countTime=["0","23","59","59"],i.groupsData=(null===i||void 0===i||null===(e=i.activities)||void 0===e?void 0:e.groups)||(null===i||void 0===i||null===(o=i.activities)||void 0===o?void 0:o.preheat_activity),i.isPreheat=t.isPreheat(i.groupsData),t.cutdownTime(i.groupsData,i)})),t.list=t.list.concat(i.list)),t.page=t.page+1,t.loading=!1,t.count=i.total,t.graceLazyload.load(0,t),t.reqesting=!1)})).catch((function(i){t.reqesting=!1})).finally((function(t){setTimeout((function(){uni.hideLoading()}),100)}))}},getActivity:function(){var i=this;this.$api.goodApi.getActivity({activity_id:this.activityId}).then((function(t){0==t.error&&(i.activeInfo=t,i.isFinish=t.status<0,i.isFinish?i.$Router.replace("/kdxGoods/activity/expire"):(i.getList(),i.cutdownTime(i.activeInfo,i)))})).catch((function(i){t.log(i)})).finally((function(){uni.hideLoading()}))},isPreheat:function(){var t=this.activeInfo,i=t.is_preheat,e=t.start_time;if(!e)return!1;var o=new Date(Date.parse(e.replace(/-/g,"/"))).getTime();return"1"==i&&o>Date.now()},reset:function(){this.list=[],this.page=1,"activity"===this.type&&this.getActivity(),"goods"===this.type&&this.getList(),setTimeout((function(){uni.stopPullDownRefresh()}),1e3)},cutdownTime:function(t,i){var e=t.start_time,o=t.end_time,n=this.isPreheat(t)?e:o,a=new Date(Date.parse(n.replace(/-/g,"/"))).getTime();if(a-Date.now()>0){var s=a/1e3;this.startCount(s,i)}},startCount:function(t,i){var e=this;i.countTime=this.$utils.countDown(t,!1);var o=setInterval((function(){i.countTime=e.$utils.countDown(t,!1),0==i.countTime&&(clearInterval(o),e.reset())}),1e3);this.$once("hook:beforeDestroy",(function(){clearInterval(o)}))},clickGoods:function(t){this.$Router.auto({path:"/kdxGoods/detail/index",query:{goods_id:t.id}})}},onReachBottom:function(){this.list.length==this.count&&this.page>1||this.getList()},onPageScroll:function(t){this.graceLazyload.load(t.scrollTop,this)}};i.default=a}).call(this,e("5a52")["default"])},"237f":function(t,i,e){var o=e("4ea4");e("8e6e"),e("ac6a"),e("456d"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n=o(e("ade3"));function a(t,i){var e=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);i&&(o=o.filter((function(i){return Object.getOwnPropertyDescriptor(t,i).enumerable}))),e.push.apply(e,o)}return e}function s(t){for(var i=1;i<arguments.length;i++){var e=null!=arguments[i]?arguments[i]:{};i%2?a(Object(e),!0).forEach((function(i){(0,n.default)(t,i,e[i])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(e)):a(Object(e)).forEach((function(i){Object.defineProperty(t,i,Object.getOwnPropertyDescriptor(e,i))}))}return t}e("a481");var r=null,c={name:"GoodsGroup",props:{item:{type:Object,default:function(){}},hasBtn:{type:Boolean,default:!0}},data:function(){return{preloading:!0,loading:{},timestamp:null,dateTimer:null}},computed:{getLoadingSrc:function(){var t,i,e,o;return null!==(t=this.$store.state.setting.systemSetting)&&void 0!==t&&null!==(i=t.basic)&&void 0!==i&&i.loading?this.$store.state.setting.cacheLoadingImg||this.$utils.mediaUrl(null===(e=this.$store.state.setting.systemSetting)||void 0===e||null===(o=e.basic)||void 0===o?void 0:o.loading):this.$utils.staticMediaUrl("decorate/goods_col2.png")},getGroupsPrice:function(){var t,i,e,o=(null===(t=this.item.activities)||void 0===t?void 0:t.groups)||(null===(i=this.item.activities)||void 0===i?void 0:i.preheat_activity)||{};return 0==this.item.has_option&&"0"==o.inner_type?o.activity_price:(null===o||void 0===o||null===(e=o.price_range)||void 0===e?void 0:e.min_price)||0},getPrice:function(){return"1"==this.item.has_option?this.item.price:this.item.min_price},isPreheat:function(){var t;return null===(t=this.item.activities)||void 0===t?void 0:t.preheat_activity},getGroupsNum:function(){var t,i,e,o=null===(t=this.item)||void 0===t||null===(i=t.activities)||void 0===i?void 0:i.groups;return 0==(null===o||void 0===o?void 0:o.inner_type)?"".concat(null===o||void 0===o||null===(e=o.rules)||void 0===e?void 0:e.success_num,"人团"):"阶梯团"}},mounted:function(){var t=this;setTimeout((function(){t.preloading=!1}),3e3)},methods:{loaded:function(t){var i=this;if(t){var e=t.replace(/\./g,"_");this.loading[e]=!1,clearTimeout(r),r=setTimeout((function(){i.loading=s({},i.loading)}),100)}},clickGood:function(){this.$emit("on-click",this.item)}},filters:{formatMoney:function(t){return"number"===typeof t||"string"===typeof t&&""!==t.trim()?t>=1e4?parseInt(t/100)/100+"万":parseFloat(t):0}}};i.default=c},"2fd1":function(t,i,e){"use strict";var o;e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return a})),e.d(i,"a",(function(){return o}));var n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",{staticClass:"goods-group"},[e("div",{staticClass:"image-box"},[t.item.preloading&&t.preloading?e("img",{staticClass:"goods-img preload",attrs:{mode:"widthFix","lazy-load":!0,src:t.getLoadingSrc,alt:""}}):t._e(),e("img",{staticClass:"goods-img",class:{loading:t.item.preloading&&t.preloading},attrs:{mode:"widthFix","lazy-load":!0,src:t.$utils.mediaUrl(t.item.thumb),alt:""},on:{load:function(i){arguments[0]=i=t.$handleEvent(i),t.loaded(t.item.thumb)},error:function(i){arguments[0]=i=t.$handleEvent(i),t.loaded(t.item.thumb)}}})]),e("div",{staticClass:"goods-content"},[e("div",[e("div",{staticClass:"goods-title",class:[t.item.sub_name?"omit-1":"omit-2"]},[t.isPreheat?t._e():e("span",{staticClass:"group-label"},[t._v(t._s(t.getGroupsNum))]),t._v(t._s(t.item.title))]),t.item.sub_name?e("div",{staticClass:"goods-sub-title line-hide"},[t._v(t._s(t.item.sub_name))]):t._e()]),t._t("countdown",null,{data:t.item}),e("div",{staticClass:"goods-price-box"},[e("div",{staticClass:"goods-price"},[e("div",{staticClass:"group-price"},[e("span",{staticClass:"name"},[t._v("拼团价")]),e("span",{staticClass:"price"},[e("span",{staticClass:"unit"},[t._v("￥")]),t._v(t._s(t._f("formatMoney")(t.getGroupsPrice)))])]),e("div",{staticClass:"original-price"},[e("span",{staticClass:"name"},[t._v("单买价")]),e("span",{staticClass:"price"},[e("span",{staticClass:"unit"},[t._v("￥")]),t._v(t._s(t._f("formatMoney")(t.getPrice)))])])]),t.hasBtn?e("div",{staticClass:"buy-btn",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.clickGood.apply(void 0,arguments)}}},[t._v("马上拼")]):t._e()])],2)])},a=[]},"33e3":function(t,i,e){var o=e("24fb");i=o(!1),i.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 开店星新零售管理系统\r\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\r\n * @author 青岛开店星信息技术有限公司\r\n * @link https://www.kaidianxing.com\r\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\r\n * @copyright 版权归青岛开店星信息技术有限公司所有\r\n * @warning Unauthorized deletion of copyright information is prohibited.\r\n * @warning 未经许可禁止私自删除版权信息\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.goods-group[data-v-10159cd0]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;padding:%?16?% %?24?%;border-radius:%?12?%;background:#fff}.goods-group .image-box[data-v-10159cd0]{position:relative;width:%?220?%;height:%?220?%;border-radius:%?12?%;overflow:hidden;-webkit-flex-shrink:0;flex-shrink:0}.goods-group .image-box .goods-img[data-v-10159cd0]{width:100%;height:100%;border-radius:%?12?%}.goods-group .goods-content[data-v-10159cd0]{padding-left:%?24?%;-webkit-box-flex:1;-webkit-flex:1;flex:1;width:0;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;height:%?220?%}.goods-group .goods-content .goods-sub-title[data-v-10159cd0]{font-size:%?24?%;line-height:%?34?%;color:#969696;padding-top:%?8?%}.goods-group .goods-content .goods-price-box[data-v-10159cd0]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between}.goods-group .goods-content .group-price .name[data-v-10159cd0],\r\n.goods-group .goods-content .original-price .name[data-v-10159cd0]{margin-right:%?8?%}.goods-group .goods-content .group-price[data-v-10159cd0]{font-size:%?24?%;line-height:%?34?%;color:#ff3c29;padding-top:%?8?%;padding-bottom:%?4?%}.goods-group .goods-content .group-price .price[data-v-10159cd0]{font-size:%?32?%;font-weight:700;line-height:%?46?%}.goods-group .goods-content .group-price .unit[data-v-10159cd0]{font-size:%?24?%;line-height:%?34?%}.goods-group .goods-content .original-price[data-v-10159cd0]{font-size:%?24?%;line-height:%?34?%;color:#969696}.goods-group .goods-content .buy-btn[data-v-10159cd0]{border-radius:%?48?%;width:%?128?%;height:%?54?%;font-size:%?24?%;font-weight:700;text-align:center;line-height:%?54?%;color:#fff;background:-webkit-linear-gradient(335.43deg,#ff8a00 19.05%,#ff4c14 87.67%);background:linear-gradient(114.57deg,#ff8a00 19.05%,#ff4c14 87.67%)}.group-label[data-v-10159cd0]{font-size:%?20?%;line-height:%?28?%;padding:%?2?% %?8?%;color:#ff6f29;background:#fff9f0;border:%?2?% solid #ffdaa3}.goods-title[data-v-10159cd0]{font-size:%?28?%;line-height:%?40?%;color:#212121}.goods-title .group-label[data-v-10159cd0]{border-radius:%?4?%;margin-right:%?4?%}.omit-1[data-v-10159cd0]{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.omit-2[data-v-10159cd0]{overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2}',""]),t.exports=i},"3a63":function(t,i,e){"use strict";e.r(i);var o=e("2fd1"),n=e("4b7d");for(var a in n)["default"].indexOf(a)<0&&function(t){e.d(i,t,(function(){return n[t]}))}(a);e("4ec4");var s,r=e("f0c5"),c=Object(r["a"])(n["default"],o["b"],o["c"],!1,null,"10159cd0",null,!1,o["a"],s);i["default"]=c.exports},4603:function(t,i,e){"use strict";var o=e("92b2"),n=e.n(o);n.a},"4b7d":function(t,i,e){"use strict";e.r(i);var o=e("237f"),n=e.n(o);for(var a in o)["default"].indexOf(a)<0&&function(t){e.d(i,t,(function(){return o[t]}))}(a);i["default"]=n.a},"4ec4":function(t,i,e){"use strict";var o=e("c485"),n=e.n(o);n.a},7148:function(t,i,e){"use strict";var o;e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return a})),e.d(i,"a",(function(){return o}));var n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",[e("div",{staticClass:"groups"},[e("div",{staticClass:"groups-img-box"},[e("img",{staticClass:"groups-img",attrs:{src:t.$utils.staticMediaUrl("activity/group/banner.png")}}),"activity"===t.type?e("div",{staticClass:"count-down"},[t.isFinish?e("div",{staticClass:"groups-surplus"},[t._v("本场已结束")]):t.isPreheat(t.activeInfo)?t.isPreheat(t.activeInfo)?e("div",{staticClass:"groups-surplus"},[t._v("距本场开始")]):t._e():e("div",{staticClass:"groups-surplus"},[t._v("距本场结束")]),t.countTime&&!t.isFinish?e("div",{staticClass:"groups-count"},[e("span",{staticClass:"text"},[t._v(t._s(t.countTime[0])+"天")]),e("span",{staticClass:"time"},[t._v(t._s(t.countTime[1]))]),e("span",{staticClass:"colon"},[t._v(":")]),e("span",{staticClass:"time"},[t._v(t._s(t.countTime[2]))]),e("span",{staticClass:"colon"},[t._v(":")]),e("span",{staticClass:"time"},[t._v(t._s(t.countTime[3]))])]):t._e()]):t._e()])]),e("div",{staticClass:"goods"},[t._l(t.list,(function(i){return e("v-uni-view",{key:i.id,staticClass:"goods-list"},["goods"===t.type?e("div",[e("Goods",{attrs:{item:i,type:"groups"},on:{"on-click":function(i){arguments[0]=i=t.$handleEvent(i),t.clickGoods.apply(void 0,arguments)}},scopedSlots:t._u([{key:"countdown",fn:function(i){var o=i.data;return[e("div",{staticClass:"activity-time"},[e("span",{staticClass:"text"},[t._v(t._s(o.isPreheat?"距开始":"距结束"))]),e("span",{staticClass:"time"},[t._v(t._s(o.countTime[0]))]),e("span",{staticClass:"colon"},[t._v("天")]),e("span",{staticClass:"time"},[t._v(t._s(o.countTime[1]))]),e("span",{staticClass:"colon"},[t._v(":")]),e("span",{staticClass:"time"},[t._v(t._s(o.countTime[2]))]),e("span",{staticClass:"colon"},[t._v(":")]),e("span",{staticClass:"time"},[t._v(t._s(o.countTime[3]))])])]}}],null,!0)})],1):e("div",[e("Goods",{attrs:{item:i,type:"groups"},on:{"on-click":function(i){arguments[0]=i=t.$handleEvent(i),t.clickGoods.apply(void 0,arguments)}}})],1)])})),t.list.length!=t.count?e("page-loading",{attrs:{loadingType:t.loadingType}}):t._e(),!t.loading&&t.count<=0?[e("v-uni-view",{staticClass:"default-page flex-column"},[e("v-uni-view",{staticClass:"bg"},[e("v-uni-image",{attrs:{src:t.$utils.staticMediaUrl("default/search.png")}})],1),e("v-uni-view",{staticClass:"default-text"},[t._v("没找到相关宝贝")])],1)]:t._e()],2)])},a=[]},"816b":function(t,i,e){var o=e("24fb");i=o(!1),i.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 开店星新零售管理系统\r\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\r\n * @author 青岛开店星信息技术有限公司\r\n * @link https://www.kaidianxing.com\r\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\r\n * @copyright 版权归青岛开店星信息技术有限公司所有\r\n * @warning Unauthorized deletion of copyright information is prohibited.\r\n * @warning 未经许可禁止私自删除版权信息\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.groups[data-v-368002a4]{position:relative;height:%?386?%}.groups .groups-img[data-v-368002a4]{height:%?346?%;width:%?750?%}.groups .count-down[data-v-368002a4]{position:absolute;left:%?24?%;bottom:%?0?%;height:%?80?%;width:%?702?%;background:#fff;border-radius:6px;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.groups .count-down .groups-surplus[data-v-368002a4]{font-size:%?24?%;line-height:%?34?%;color:#212121}.groups .count-down .groups-count[data-v-368002a4]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;height:%?32?%;color:#fff}.groups .count-down .groups-count .time[data-v-368002a4]{width:%?32?%;height:%?32?%;line-height:%?32?%;text-align:center;background-color:#ff6f29;color:#fff;font-size:%?24?%;border-radius:%?4?%}.groups .count-down .groups-count .text[data-v-368002a4]{margin-left:%?16?%;margin-right:%?12?%;font-size:%?24?%;color:#212121}.groups .count-down .groups-count .colon[data-v-368002a4]{margin:0 %?4?%;line-height:%?32?%;color:#ff6f29}.goods[data-v-368002a4]{margin-top:%?16?%;padding:0 %?24?%}.goods .activity-time[data-v-368002a4]{width:-webkit-fit-content;width:fit-content;margin-top:%?8?%;padding:0 %?8?%;line-height:%?28?%;font-size:%?20?%;border:1px solid #ff6f29;border-radius:%?4?%}.goods .activity-time > span[data-v-368002a4]{color:#ff6f29}.goods .goods-list[data-v-368002a4]{margin-bottom:%?16?%}',""]),t.exports=i},"92b2":function(t,i,e){var o=e("816b");"string"===typeof o&&(o=[[t.i,o,""]]),o.locals&&(t.exports=o.locals);var n=e("4f06").default;n("84a439ea",o,!0,{sourceMap:!1,shadowMode:!1})},c485:function(t,i,e){var o=e("33e3");"string"===typeof o&&(o=[[t.i,o,""]]),o.locals&&(t.exports=o.locals);var n=e("4f06").default;n("c77fa9ac",o,!0,{sourceMap:!1,shadowMode:!1})},edb1:function(t,i,e){"use strict";e.r(i);var o=e("7148"),n=e("f921");for(var a in n)["default"].indexOf(a)<0&&function(t){e.d(i,t,(function(){return n[t]}))}(a);e("4603");var s,r=e("f0c5"),c=Object(r["a"])(n["default"],o["b"],o["c"],!1,null,"368002a4",null,!1,o["a"],s);i["default"]=c.exports},f921:function(t,i,e){"use strict";e.r(i);var o=e("200b"),n=e.n(o);for(var a in o)["default"].indexOf(a)<0&&function(t){e.d(i,t,(function(){return o[t]}))}(a);i["default"]=n.a}}]);