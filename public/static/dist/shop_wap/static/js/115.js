(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[115],{"10d3":function(t,e,a){"use strict";var i;a.d(e,"b",(function(){return n})),a.d(e,"c",(function(){return o})),a.d(e,"a",(function(){return i}));var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return t.filterList.length?a("div",{staticClass:"detail_sale__group"},[a("div",{staticClass:"title-box van-hairline--bottom",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem.apply(void 0,arguments)}}},[a("span",{staticClass:"title"},[t._v("以下用户正在拼团，可直接参与拼团")]),t.lists.length>2?a("div",{staticClass:"touch-content"},[a("i",{staticClass:"iconfont-m- icon-m-right"})]):t._e()]),a("ul",{staticClass:"groups van-hairline--bottom"},t._l(t.filterList,(function(e,i){return a("li",{key:i,staticClass:"group"},[a("div",{staticClass:"group-left"},[a("img",{staticClass:"group-avatar",attrs:{src:t.$utils.mediaUrl(e.avatar)||t.$utils.staticMediaUrl("decorate/avatar_mobile.png")}}),a("div",{staticClass:"group-leader"},[a("div",{staticClass:"group-leader-name line-hide"},[t._v(t._s(e.nickname))]),t.isLadderGroup?a("div",{staticClass:"group-leader-tag"},[t._v(t._s(e.success_num)+"人团")]):t._e()])]),a("div",{staticClass:"group-right"},[a("div",{staticClass:"group-info"},[a("div",{staticClass:"group-info-desc"},[t._v("还差"),a("span",{staticClass:"group-info-user"},[t._v(t._s(t.getSurplus(e))+"人")]),t._v("成团")]),a("div",{staticClass:"group-time-box"},[a("span",{staticClass:"group-time-desc"},[t._v("剩余")]),a("span",{staticClass:"group-time-num"},[t._v(t._s(e.countTime))])])]),a("div",{staticClass:"group-btn",on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.clickJoin(e)}}},[t._v("去参团")])])])})),0),t.isLadderGroup?a("div",{staticClass:"group-tip"},[t._v("拼团玩法："+t._s(t.getLadderPrice)+"先选择参团人数，支付开团邀请好友参团，人数不足将自动退款")]):a("div",{staticClass:"group-tip"},[t._v("拼团玩法：支持开团邀请好友参团，人数不足自动退款")])]):t._e()},o=[]},"175d":function(t,e,a){var i=a("84be");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=a("4f06").default;n("872d1092",i,!0,{sourceMap:!1,shadowMode:!1})},3341:function(t,e,a){var i=a("288e");a("8e6e"),a("ac6a"),a("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=i(a("bd86")),o=a("2f62"),r=a("dc11");function s(t,e){var a=Object.keys(t);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(t);e&&(i=i.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),a.push.apply(a,i)}return a}function l(t){for(var e=1;e<arguments.length;e++){var a=null!=arguments[e]?arguments[e]:{};e%2?s(Object(a),!0).forEach((function(e){(0,n.default)(t,e,a[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(a)):s(Object(a)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(a,e))}))}return t}var c={computed:l({},(0,o.mapState)("decorate",{pageList:function(t){return t.pageList}})),props:{startLoadImg:{type:Boolean,default:!0},componentData:{type:Object,default:function(){return{style:{},params:{}}}}},methods:{px2rpx:r.px2rpx}};e.default=c},3479:function(t,e,a){"use strict";var i;a.d(e,"b",(function(){return n})),a.d(e,"c",(function(){return o})),a.d(e,"a",(function(){return i}));var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return t.isShowSale?a("div",{staticClass:"detail_sale",style:{padding:t.px2rpx(t.componentData.style.margintop)+" "+t.px2rpx(t.componentData.style.marginleft)+" "+t.px2rpx(t.componentData.style.marginbottom)}},[a("div",{staticStyle:{overflow:"hidden"},style:{"border-radius":t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.bottomradius)+" "+t.px2rpx(t.componentData.style.bottomradius)}},[a("div",["groups"==t.componentData.params.activityName&&t.getGroupsTeam.length?a("detail-sale-group",{attrs:{info:t.componentData.params.activityData,team:t.getGroupsTeam},on:{"click-all":function(e){arguments[0]=e=t.$handleEvent(e),t.clickGroups.apply(void 0,arguments)},"click-detail":function(e){arguments[0]=e=t.$handleEvent(e),t.clickGroupsItem.apply(void 0,arguments)},"special-click":function(e){arguments[0]=e=t.$handleEvent(e),t.clickBuyItem.apply(void 0,arguments)}}}):t._e()],1),a("ul",{staticClass:"container",style:{"border-radius":t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.bottomradius)+" "+t.px2rpx(t.componentData.style.bottomradius),background:t.componentData.style.background,color:t.componentData.style.textcolor}},t._l(t.getList,(function(e){return a("li",{key:e.type,staticClass:"item van-hairline--bottom"},[a("div",{staticClass:"label",style:{color:t.componentData.style.titlecolor}},[t._v(t._s(e.label))]),a("div",{staticClass:"body",class:{"body-coupon":"coupon"==e.type}},["coupon"==e.type?a("ul",{staticClass:"coupons",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem("coupon")}}},t._l(e.items,(function(e,i){return a("li",{key:i,staticClass:"coupon",style:{background:t.componentData.style.couponBackground,borderColor:t.componentData.style.couponBackground}},[t._v(t._s(e.content)),a("i",{staticClass:"after",style:{borderColor:t.componentData.style.couponBackground}})])})),0):"biaoqian"==e.type?a("ul",{staticClass:"biaoqians",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem("biaoqian")}}},t._l(t.severList,(function(e,i){return a("li",{key:i,staticClass:"biaoqian"},[a("p",{staticClass:"word",class:t.getLabelStyle?"":"bg-color",style:{backgroundColor:t.getLabelStyle?t.componentData.style.background:t.componentData.style.servercolor,color:t.componentData.style.textcolor}},[a("i",{class:t.getLabelStyle,style:{color:t.componentData.style.servercolor}}),a("span",{style:{color:t.componentData.style.serverTextColor}},[t._v(t._s(e.name))])])])})),0):"bupeisong"==e.type?a("div",{staticClass:"common"},[a("p",{staticClass:"words",style:{color:t.componentData.style.textcolor}},[t._v(t._s(0!=t.componentData.params.hidedispatch?e.content.value:"待下单时查看"))]),e.showIcon?a("p",{staticStyle:{color:"#969696","font-size":"24rpx"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem("bupeisong")}}},[t._v("查看发货区域")]):t._e()]):"samecity"==e.type?a("v-uni-view",{staticClass:"samecity",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem("samecity")}}},[a("v-uni-view",{staticClass:"words line-hide",style:{color:t.componentData.style.textcolor}},[t._v(t._s(e.content.value))])],1):"active"==e.type?a("ul",{staticClass:"actives",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem("active")}}},t._l(t.activeList,(function(e,i){return a("li",{key:i,staticClass:"active"},[e?a("p",{staticClass:"icon-word",style:{color:t.componentData.style.activeTagColor,borderColor:t.componentData.style.activeTagColor,color:t.componentData.style.activeTagColor}},["score"==e.type?a("span",[t._v(t._s(t.credit_text))]):"balance"==e.type?a("span",[t._v(t._s(t.balance_text))]):a("span",[t._v(t._s(e.name))])]):t._e(),"full"==e.type?a("div",{staticClass:"demon"},[t._v("全场满"),a("i",{style:{color:t.componentData.style.numcolor}},[t._v("￥"+t._s(e.enough))]),t._v("立减"),a("i",{style:{color:t.componentData.style.numcolor}},[t._v("￥"+t._s(e.deduct))])]):t._e(),"freeExpress"==e.type?a("div",{staticClass:"demon"},["full_free_dispatch"==e.classify?a("p",{staticClass:"demon-text"},[t._v("全场满"),a("i",{style:{color:t.componentData.style.numcolor}},[t._v("￥"+t._s(e.value))]),t._v("包邮")]):t._e(),"single_full_unit_num"==e.classify?a("p",{staticClass:"demon-text"},[t._v("单商品满"),a("i",{style:{color:t.componentData.style.numcolor}},[t._v(t._s(e.value)+"件")]),t._v("包邮")]):t._e(),"single_full_quota_price"==e.classify?a("p",{staticClass:"demon-text"},[t._v("单商品满"),a("i",{style:{color:t.componentData.style.numcolor}},[t._v("￥"+t._s(e.value))]),t._v("包邮")]):t._e(),"all_dispatch"==e.classify?a("p",[t._v("该商品支持全国包邮")]):t._e()]):t._e(),"score"==e.type&&"0"!=e.deduction_type?a("p",{staticClass:"demon"},["1"==e.deduction_type?a("span",[t._v("支持"+t._s(t.credit_text)+"抵扣")]):t._e(),"2"==e.deduction_type?a("span",[t._v("支持"+t._s(t.credit_text)+"抵扣"),a("i",{style:{color:t.componentData.style.numcolor}},[t._v(t._s(e.deduction_price))]),t._v("元")]):t._e()]):t._e(),"balance"==e.type?a("p",{staticClass:"demon"},["1"==e.deduction_type?a("span",[t._v("支持"+t._s(t.balance_text)+"抵扣")]):t._e(),"2"==e.deduction_type?a("span",[t._v("支持"+t._s(t.balance_text)+"抵扣"),a("i",{style:{color:t.componentData.style.numcolor}},[t._v(t._s(e.deduction_price))]),t._v("元")]):t._e()]):t._e()])})),0):"bupeisong"==e.type?a("div",{staticClass:"common",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem("bupeisong")}}},[e.content.iconword?a("p",{staticClass:"icon-word"},[t._v(t._s(e.content.iconword))]):t._e(),a("p",{staticClass:"words"},[t._v(t._s(e.content.value))])]):t._e()],1),e.items&&e.items.length||e.chooseAdr?a("i",{staticClass:"iconfont-m- icon-m-right",style:{color:t.componentData.style.titlecolor}}):t._e()])})),0)])]):t._e()},o=[]},"61a8":function(t,e,a){var i=a("24fb");e=i(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.detail_sale__group[data-v-13e1324a]{padding-left:%?24?%;margin-bottom:%?16?%;background:#fff}.detail_sale__group .title-box[data-v-13e1324a]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;-webkit-box-align:center;-webkit-align-items:center;align-items:center;padding-right:%?24?%;height:%?80?%}.detail_sale__group .title-box.van-hairline--bottom[data-v-13e1324a]:after{border-bottom-color:#e6e7eb;border-style:solid}.detail_sale__group .title-box .title[data-v-13e1324a]{font-size:%?28?%}.detail_sale__group .title-box .touch-content[data-v-13e1324a]{height:%?80?%;line-height:%?80?%;padding-left:%?40?%}.detail_sale__group .title-box .icon-m-right[data-v-13e1324a]{font-size:%?32?%;color:#969696}.detail_sale__group .groups.van-hairline--bottom[data-v-13e1324a]:after{border-bottom-color:#e6e7eb;border-style:solid}.detail_sale__group .groups .group[data-v-13e1324a]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;height:%?126?%}.detail_sale__group .groups .group-left[data-v-13e1324a], .detail_sale__group .groups .group-right[data-v-13e1324a]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.detail_sale__group .groups .group-right[data-v-13e1324a]{padding-right:%?24?%}.detail_sale__group .groups .group-avatar[data-v-13e1324a]{margin-right:%?16?%;border-radius:50%;height:%?76?%;width:%?76?%}.detail_sale__group .groups .group-leader[data-v-13e1324a]{width:%?240?%}.detail_sale__group .groups .group-leader-name[data-v-13e1324a]{font-size:%?24?%;line-height:%?34?%}.detail_sale__group .groups .group-leader-tag[data-v-13e1324a]{display:inline-block;border-radius:%?4?%;border:%?2?% solid #ffdaa3;padding:%?2?% %?8?%;font-size:%?20?%;line-height:%?28?%;color:#ff6f29;background:#fff9f0}.detail_sale__group .groups .group-info[data-v-13e1324a]{margin-right:%?16?%}.detail_sale__group .groups .group-info-desc[data-v-13e1324a]{font-size:12px;line-height:17px;text-align:right;color:#212121}.detail_sale__group .groups .group-info-user[data-v-13e1324a]{margin:0 %?8?%;color:#ff3c29}.detail_sale__group .groups .group-time-desc[data-v-13e1324a], .detail_sale__group .groups .group-time-num[data-v-13e1324a]{font-size:%?22?%;line-height:%?30?%;color:#969696}.detail_sale__group .groups .group-time-num[data-v-13e1324a]{font-family:PingFang SC;font-variant-numeric:tabular-nums;font-family:Helvetica Neue;text-align:right;display:inline-block}.detail_sale__group .groups .group-time-desc[data-v-13e1324a]{margin-right:%?4?%}.detail_sale__group .groups .group-btn[data-v-13e1324a]{width:%?136?%;height:%?54?%;font-size:%?24?%;font-weight:700;line-height:%?54?%;text-align:center;color:#fff;background:-webkit-linear-gradient(335.43deg,#ff8a00 19.05%,#ff4c14 87.67%);background:linear-gradient(114.57deg,#ff8a00 19.05%,#ff4c14 87.67%);border-radius:24px}.detail_sale__group .group-tip[data-v-13e1324a]{padding:%?24?% %?24?% %?24?% 0;font-size:%?24?%;line-height:%?34?%;color:#212121}',""]),t.exports=e},"67a0":function(t,e,a){"use strict";a.r(e);var i=a("10d3"),n=a("e43a");for(var o in n)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(o);a("c364");var r,s=a("f0c5"),l=Object(s["a"])(n["default"],i["b"],i["c"],!1,null,"13e1324a",null,!1,i["a"],r);e["default"]=l.exports},"689e":function(t,e,a){var i=a("61a8");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=a("4f06").default;n("61cfd772",i,!0,{sourceMap:!1,shadowMode:!1})},"84be":function(t,e,a){var i=a("24fb");e=i(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */.isbottom[data-v-1a440508], .istop[data-v-1a440508]{z-index:999990}._i[data-v-1a440508]{display:inline}uni-view[data-v-1a440508]{box-sizing:border-box}.def-pad[data-v-1a440508]{padding:%?8?% %?16?%}*[data-v-1a440508]{box-sizing:border-box;margin:0;padding:0;border:none}li[data-v-1a440508]{list-style:none}ul[data-v-1a440508]{padding:0}uni-image[data-v-1a440508]{height:auto}.detail_sale[data-v-1a440508]{width:100%}.detail_sale .container[data-v-1a440508]{width:100%;box-sizing:border-box;padding-left:%?24?%}.detail_sale .container .item[data-v-1a440508]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:nowrap;flex-wrap:nowrap;padding-right:%?24?%}.detail_sale .container .item.van-hairline--bottom[data-v-1a440508]:after{border-bottom-color:#e6e7eb;border-style:solid}.detail_sale .container .item:last-child.van-hairline--bottom[data-v-1a440508]:after{border-width:0}.detail_sale .container .label[data-v-1a440508]{box-sizing:border-box;font-size:%?24?%;min-width:%?96?%;-webkit-flex-shrink:0;flex-shrink:0;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;min-height:%?80?%;color:#969696}.detail_sale .container .body[data-v-1a440508]{width:0;-webkit-box-flex:1;-webkit-flex:1;flex:1;font-size:%?24?%;line-height:%?56.18893?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.detail_sale .container .body.body-coupon[data-v-1a440508]{height:%?80?%;overflow:hidden}.detail_sale .container .body .coupons[data-v-1a440508]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:wrap;flex-wrap:wrap}.detail_sale .container .body .coupons .coupon[data-v-1a440508]{height:%?36?%;line-height:%?36?%;margin:%?22?% %?22?% %?22?% 0;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;font-size:%?20?%;color:#fff;text-align:center;padding:0 %?16?%;background:red;position:relative;border-top-left-radius:%?4?%;border-bottom-left-radius:%?4?%}.detail_sale .container .body .coupons .coupon .after[data-v-1a440508]{position:absolute;top:0;bottom:0;width:0}.detail_sale .container .body .coupons .coupon .after[data-v-1a440508]{border-left:%?2.443?% dotted red;right:%?-2.443?%}.detail_sale .container .body .biaoqians[data-v-1a440508]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:wrap;flex-wrap:wrap}.detail_sale .container .body .biaoqians .biaoqian[data-v-1a440508]{height:%?80?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;text-align:center;font-size:%?24?%;font-weight:500;margin-right:%?24?%}.detail_sale .container .body .biaoqians .biaoqian .word[data-v-1a440508]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:nowrap;flex-wrap:nowrap;position:relative;font-size:%?24?%}.detail_sale .container .body .biaoqians .biaoqian .word .icon-m-yes1[data-v-1a440508]{margin-right:%?8?%;font-size:%?28?%}.detail_sale .container .body .biaoqians .biaoqian .word .icon-m-dian[data-v-1a440508]{margin-right:%?8?%;font-size:%?28?%}.detail_sale .container .body .biaoqians .biaoqian .word.bg-color[data-v-1a440508]{height:%?38?%;line-height:%?24?%;padding:%?8?% %?16?%;border-radius:%?38?%;overflow:hidden}.detail_sale .container .body .biaoqians .biaoqian .word.bg-color .bg[data-v-1a440508]{position:absolute;top:0;left:0;bottom:0;right:0}.detail_sale .container .body .actives[data-v-1a440508]{padding:%?14?% 0}.detail_sale .container .body .actives .active[data-v-1a440508]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:nowrap;flex-wrap:nowrap}.detail_sale .container .body .actives .active i[data-v-1a440508]{font-style:normal}.detail_sale .container .body .actives .active .demon[data-v-1a440508], .detail_sale .container .body .actives .active .demon-text[data-v-1a440508]{line-height:%?24?%;font-size:%?24?%;margin:%?8?% 0;color:#212121}.detail_sale .container .body .actives .active .demon i[data-v-1a440508], .detail_sale .container .body .actives .active .demon-text i[data-v-1a440508]{line-height:%?24?%;font-size:%?24?%}.detail_sale .container .body .common[data-v-1a440508]{display:-webkit-box;display:-webkit-flex;display:flex;line-height:%?24?%;font-size:%?24?%;margin:auto 0}.detail_sale .container .body .common i[data-v-1a440508]{font-style:normal}.detail_sale .container .body .common .words[data-v-1a440508]{-webkit-box-flex:1;-webkit-flex:1;flex:1;margin:auto 0;padding:%?6?% 0;font-weight:500;font-size:%?24?%;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}.detail_sale .container .body .words[data-v-1a440508]{-webkit-box-flex:1;-webkit-flex:1;flex:1;margin:auto 0;font-weight:400;font-size:%?24?%;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}.detail_sale .container .body .samecity[data-v-1a440508]{color:#212121;-webkit-box-align:center;-webkit-align-items:center;align-items:center;font-size:%?24?%;height:%?88?%;line-height:%?88?%;padding-left:%?20?%}.detail_sale .container .body .icon-word[data-v-1a440508]{font-size:%?20?%;height:%?32?%;line-height:%?20?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-shrink:0;flex-shrink:0;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;border:%?1?% solid #ff3c29;color:#ff3c29;border-radius:%?16?%;padding:%?6?% %?16?%;margin:%?8?% %?16?% %?8?% 0}.detail_sale .container .icon-m-right[data-v-1a440508]{position:relative;-webkit-flex-shrink:0;flex-shrink:0;line-height:%?32?%;height:%?32?%;margin:auto 0 auto %?2?%;font-size:%?32?%;color:#969696}',""]),t.exports=e},"9d7c":function(t,e,a){"use strict";var i=a("175d"),n=a.n(i);n.a},accb:function(t,e,a){var i=a("288e");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=i(a("768b"));a("a481"),a("96cf");var o=i(a("3b8d"));a("456d"),a("ac6a");var r={props:{info:{type:Object,default:function(){}},team:{type:Array,default:function(){return[]}}},data:function(){return{lists:[]}},computed:{filterList:function(){return this.lists.slice(0,2)},isLadderGroup:function(){return"1"==this.info.inner_type},getLadderPrice:function(){var t=this.info,e=t.inner_type,a=t.rules.success_num,i=t.every_ladder_min_price,n="";return"1"!=e||(Object.keys(a).forEach((function(t){n+="".concat(a[t],"人团¥").concat(i[t],"，")})),n=n.slice(0,-1)),n}},watch:{team:{handler:function(){var t=this;this.lists=this.team||[],this.lists.forEach((function(e){return t.getTime(e)}))},immediate:!0}},mounted:function(){},methods:{clickItem:function(){this.lists.length>2&&this.$emit("click-all")},clickJoin:function(){var t=(0,o.default)(regeneratorRuntime.mark((function t(e){var a;return regeneratorRuntime.wrap((function(t){while(1)switch(t.prev=t.next){case 0:"1"==e.is_join?this.$emit("click-detail",e):(a={btn:"buy",activeName:"groups",is_join:"1",team_id:e.id},"1"==this.info.inner_type&&(a.ladder=e.ladder),this.$emit("special-click",a));case 1:case"end":return t.stop()}}),t,this)})));function e(e){return t.apply(this,arguments)}return e}(),getSurplus:function(t){var e=t.count,a=void 0===e?0:e,i=t.success_num,n=void 0===i?0:i;return+n-+a},getTime:function(t){var e=this,a=new Date(Date.parse(t.end_time.replace(/-/g,"/"))).getTime(),i=parseInt(a/1e3),o=function(){var a=e.$utils.countDown(i,!1);if(!1!==a){var o=(0,n.default)(a,4),s=o[0],l=o[1],c=o[2],d=o[3];e.$set(t,"countTime","".concat(s,"天").concat(l,":").concat(c,":").concat(d))}else e.lists=e.lists.filter((function(e){return e!==t})),clearInterval(r)};o();var r=setInterval((function(){o()}),1e3);this.$once("hook:beforeDestroy",(function(){clearInterval(r)}))}}};e.default=r},c200:function(t,e,a){"use strict";a.r(e);var i=a("3479"),n=a("fabd");for(var o in n)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(o);a("9d7c");var r,s=a("f0c5"),l=Object(s["a"])(n["default"],i["b"],i["c"],!1,null,"1a440508",null,!1,i["a"],r);e["default"]=l.exports},c364:function(t,e,a){"use strict";var i=a("689e"),n=a.n(i);n.a},d9d3:function(t,e,a){var i=a("288e");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("c5f6"),a("ac6a");var n=i(a("3341")),o=i(a("67a0")),r={mixins:[n.default],name:"detail_sale",components:{DetailSaleGroup:o.default},data:function(){return{balance_text:"余额",credit_text:"积分",activeList:[],severList:[]}},computed:{isShowSale:function(){return this.componentData&&"detail_sale"==this.componentData.id&&(this.getList.length||"groups"==this.componentData.params.activityName&&this.getGroupsTeam.length)&&(this.componentData.params.isBargain||!this.componentData.params.buy_button_status)&&6!=this.componentData.params.goods.type},getLabelStyle:function(){return"0"==this.componentData.params.label_style?"":1==this.componentData.params.label_style?"icon-m-dian iconfont-m-":"icon-m-yes1 iconfont-m-"},getGroupsTeam:function(){var t,e,a;return this.$isPC?[]:(null===(t=this.componentData)||void 0===t||null===(e=t.params)||void 0===e||null===(a=e.activityData)||void 0===a?void 0:a.team)||[]},getList:function(){var t,e={yushou:!1,erci:!1,huiyuan:!0,youhui:!1,jifen:!1,bupeisong:!0,biaoqian:!0,coupon:!0,zengpin:!1,fullback:!1,active:!0,samecity:!0,verify:!0};return null!==(t=this.componentData)&&void 0!==t&&t.data?this.componentData.data.filter((function(t){return e[t.type]})).filter((function(t){return t.label&&(t.items&&t.items.length||t.content)})):[]}},watch:{getList:{handler:function(t){var e=[],a=[];t.forEach((function(t){"active"==t.type?e=t.items:"biaoqian"==t.type&&(a=t.items)})),this.activeList=this.dealData(e),this.severList=a.slice(0,3)},immediate:!0}},methods:{clickItem:function(t){this.$emit("custom-event",{target:"detail_sale/clickItem",data:{type:t,data:this.componentData}})},clickBuyItem:function(t){this.$emit("custom-event",{target:"detail_navbar/clickItem",data:t})},clickGroupsItem:function(t){this.$emit("custom-event",{target:"detail_sale/clickGroupsItem",data:{data:this.componentData,item:t}})},clickGroups:function(){this.$emit("custom-event",{target:"detail_sale/clickGroups",data:{info:this.componentData.params.activityData}})},dealData:function(t){if(this.$isPC)return t;var e={},a=t.filter((function(t){if(!e[t.type])return e[t.type]=!0,t}));return a},sliceList:function(t){return t?t.slice(0,3):[]},is_active:function(t){var e=["seckill","preheat_activity"];return t&&e.some((function(e){return t[e]}))}},filters:{priceUnit:function(t){var e=t||0,a=Number(e);if(!(a<100))return a=Number(e/1e3),a>100?a=">100km":a+="km",a;a="<100m"}},beforeMount:function(){var t,e;this.$isPC||(this.balance_text=null===(t=this.$store.state.setting.systemSetting)||void 0===t?void 0:t.balance_text,this.credit_text=null===(e=this.$store.state.setting.systemSetting)||void 0===e?void 0:e.credit_text)}};e.default=r},e43a:function(t,e,a){"use strict";a.r(e);var i=a("accb"),n=a.n(i);for(var o in i)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(o);e["default"]=n.a},fabd:function(t,e,a){"use strict";a.r(e);var i=a("d9d3"),n=a.n(i);for(var o in i)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(o);e["default"]=n.a}}]);