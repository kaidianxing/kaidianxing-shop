(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[85],{"0045":function(t,e,i){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("6762");var a={created:function(){this.$loading.showLoading()},watch:{isSkeleton:function(t){var e=["/kdxGoods/goodList/index","/kdxGoods/detail/index","/kdxMember/index/index"];e.includes(this.$Route.path)||(t?uni.showLoading({title:"加载中"}):uni.hideLoading())}}};e.default=a},"0beab":function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("page-box",[i("v-uni-view",{staticClass:"order-list"},[i("list-tab",{attrs:{current:t.status}}),i("v-uni-view",{staticClass:"container"},[t.list.length>0?[i("list-goods-card",{attrs:{list:t.list,is_comment:t.is_comment},on:{btnClick:function(e){arguments[0]=e=t.$handleEvent(e),t.btnClick.apply(void 0,arguments)}}}),t.list.length!=t.count?i("page-loading",{attrs:{loadingType:t.loadingType}}):t._e()]:t._e()],2),!t.loading&&t.count<=0?[i("v-uni-view",{staticClass:"default-page flex-column"},[i("v-uni-view",{staticClass:"bg"},[i("v-uni-image",{attrs:{src:t.$utils.staticMediaUrl("default/bill.png")}})],1),i("v-uni-view",{staticClass:"default-text"},[t._v("您暂时还没有订单哦")]),i("div",{staticClass:"ib"},[i("btn",{attrs:{styles:"padding:0 60rpx;margin-top:32rpx",ghost:!0,classNames:"theme-primary-color theme-primary-border",size:"middle"},on:{"btn-click":function(e){arguments[0]=e=t.$handleEvent(e),t.toIndex.apply(void 0,arguments)}}},[t._v("去逛逛")])],1)],1),i("goods-like")]:t._e(),i("list-modal",{ref:"modals",attrs:{title:t.getTitle},on:{cancelOrderOk:function(e){arguments[0]=e=t.$handleEvent(e),t.cancelOrderOk.apply(void 0,arguments)},sendOrderOk:function(e){arguments[0]=e=t.$handleEvent(e),t.sendOrderOk.apply(void 0,arguments)},deleteOrderOk:function(e){arguments[0]=e=t.$handleEvent(e),t.deleteOrderOk.apply(void 0,arguments)}}}),i("order-pay",{ref:"orderPay",attrs:{type:"orderList",orderId:t.orderData.id,orderData:t.orderData}})],2),i("modalReward",{attrs:{visible:t.visible,activityType:t.activityType,activityData:t.activityData},on:{"update:visible":function(e){arguments[0]=e=t.$handleEvent(e),t.changeShowReward.apply(void 0,arguments)}}}),i("modalReward",{attrs:{visible:t.showShoppingRewardFlag,activityType:"shoppingReward",activityData:t.shoppingRewardData},on:{"update:visible":function(e){arguments[0]=e=t.$handleEvent(e),t.changeShowShoppingRewardFlag.apply(void 0,arguments)}}}),i("redpacket",{attrs:{isShow:t.customVisible,activityType:t.activityType,activityData:t.customActivityData},on:{"update:visible":function(e){arguments[0]=e=t.$handleEvent(e),t.changeCloseCustom.apply(void 0,arguments)}}}),i("redpacket",{attrs:{isShow:t.showShoppingVisible,activityType:t.shoppingRewardData,activityData:t.showShoppingActivityData},on:{"update:visible":function(e){arguments[0]=e=t.$handleEvent(e),t.changeCloseShopping.apply(void 0,arguments)}}})],1)},o=[]},"12e4":function(t,e,i){"use strict";i.r(e);var a=i("bf1c"),n=i.n(a);for(var o in a)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},"1d4f":function(t,e,i){"use strict";var a=i("4317"),n=i.n(a);n.a},2085:function(t,e,i){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("a481");var a={name:"ListTab",props:{current:{type:String,default:"all"}},data:function(){return{status:[{text:"全部",type:"all"},{text:"待付款",type:"pay"},{text:"待发货",type:"send"},{text:"待收货",type:"pick"},{text:"已完成",type:"finish"}]}},computed:{},created:function(){},mounted:function(){},methods:{changeTab:function(t){this.$Router.replace({path:"/kdxOrder/list",query:{status:t}})}}};e.default=a},"21b3":function(t,e,i){"use strict";i.r(e);var a=i("d19d"),n=i("93d1");for(var o in n)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("1d4f");var r,s=i("f0c5"),d=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"31984278",null,!1,a["a"],r);e["default"]=d.exports},4097:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.goods-block-inner[data-v-4656ab3a]{position:relative;overflow:hidden}.goods-block-inner .goods-image[data-v-4656ab3a]{-webkit-flex-shrink:0;flex-shrink:0;position:relative;width:%?160?%;height:%?160?%;margin-right:%?24?%;border-radius:%?4?%;background-color:#fff;background-position:0 0;background-size:100% 100%;background-repeat:no-repeat;overflow:hidden}.goods-block-inner .goods-image uni-image[data-v-4656ab3a]{width:%?160?%;height:%?160?%;border-radius:%?4?%;display:block}.goods-block-inner .goods-image .send-icon[data-v-4656ab3a]{position:absolute;left:0;bottom:0;width:100%;height:%?32?%;line-height:%?32?%;color:#fff;font-size:%?20?%;text-align:center;background:#212121;opacity:.7}.goods-block-inner .goods-info[data-v-4656ab3a]{overflow:hidden;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;color:#212121;height:%?160?%}.goods-block-inner .goods-info .goods-type[data-v-4656ab3a]{margin-right:%?10?%;padding:0 %?16?%;height:%?38?%;line-height:%?38?%;border-radius:%?20?%;background-color:#ff3c29;font-size:%?24?%;color:#fff}.goods-block-inner .goods-info .goods-type.activity[data-v-4656ab3a]{margin-right:%?8?%;border-radius:%?4?%;padding:%?2?% %?8?%;font-size:%?20?%;line-height:%?28?%;background:-webkit-linear-gradient(335.43deg,#ff8a00 19.05%,#ff4c14 87.67%);background:linear-gradient(114.57deg,#ff8a00 19.05%,#ff4c14 87.67%)}.goods-block-inner .goods-info .title[data-v-4656ab3a]{line-height:%?40?%;font-size:%?28?%}.goods-block-inner .goods-info .option-title[data-v-4656ab3a]{width:-webkit-fit-content;width:fit-content;margin-top:%?4?%;padding:1px %?16?%;max-width:%?390?%;height:%?32?%;color:#969696;font-size:%?20?%;background:#f5f5f5;border-radius:%?22?%}.goods-block-inner .goods-info .refund-money-label[data-v-4656ab3a]{font-size:%?24?%;color:#212121}.goods-block-inner .goods-info .refund-money-value[data-v-4656ab3a]{font-size:%?24?%;font-weight:700;color:#ff3c29}.goods-block-inner .goods-info .price-box[data-v-4656ab3a]{-webkit-box-align:center;-webkit-align-items:center;align-items:center}.goods-block-inner .goods-info .price[data-v-4656ab3a]{color:#ff3c29;font-size:%?24?%}.goods-block-inner .goods-info .price.refund[data-v-4656ab3a]{color:#212121}.goods-block-inner .goods-info .add-num uni-text[data-v-4656ab3a]:nth-of-type(2){height:%?38?%;line-height:%?38?%;background:#f5f5f5;border-radius:%?22?%;color:#000;text-align:center;padding:0 %?32?%}',""]),t.exports=e},"42ad":function(t,e,i){"use strict";var a=i("6275"),n=i.n(a);n.a},4317:function(t,e,i){var a=i("5189");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("16a31ecc",a,!0,{sourceMap:!1,shadowMode:!1})},5189:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.list-tab[data-v-31984278]{position:fixed;left:0;top:0;width:100%;z-index:999}.list-tab .tab[data-v-31984278]{background-color:#fff;border-top:1px solid #e6e7eb}.list-tab .tab .item[data-v-31984278]{-webkit-box-flex:1;-webkit-flex:1;flex:1;text-align:center;line-height:%?80?%;font-size:%?28?%;color:#565656;position:relative}.list-tab .tab .item .line[data-v-31984278]{width:%?56?%;height:%?4?%;position:absolute;bottom:0;left:50%;display:none;background-color:initial;-webkit-transform:translateX(-50%);transform:translateX(-50%);border-radius:%?4?%!important}.list-tab .tab .item.active[data-v-31984278]{font-size:%?32?%;color:#ff3c29;font-weight:600}.list-tab .tab .item.active .line[data-v-31984278]{display:block;background-color:#ff3c29}',""]),t.exports=e},5828:function(t,e,i){"use strict";i.r(e);var a=i("b88d"),n=i("e3b6");for(var o in n)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("42ad");var r,s=i("f0c5"),d=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"7ba67ed4",null,!1,a["a"],r);e["default"]=d.exports},6242:function(t,e,i){var a=i("288e");i("8e6e"),i("ac6a"),i("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("6762");var n=a(i("bd86"));i("c5f6");var o=a(i("c58f")),r=i("2f62");function s(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(t);e&&(a=a.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,a)}return i}function d(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?s(Object(i),!0).forEach((function(e){(0,n.default)(t,e,i[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):s(Object(i)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))}))}return t}var c={name:"ListGoodCard",components:{goodsCard:o.default},props:{list:{type:Array,default:function(){return[]}},is_comment:{type:[String,Number],default:0}},data:function(){return{orderList:[],cancelModal:!1}},watch:{list:{handler:function(t){var e=this;t=t.map((function(t){return d(d({},t),{},{count_text:"后订单关闭",format_text:e.formatText(t)||"总金额",is_buy:!1})})),this.orderList=t},deep:!0,immediate:!0}},computed:d(d({},(0,r.mapState)("setting",{credit_text:function(t){return t.systemSetting.credit_text}})),{},{backgroundImage:function(){return"background-image:url(".concat(this.$utils.staticMediaUrl("decorate/logo_default.png"),")")}}),created:function(){},mounted:function(){},methods:{formatText:function(t){return"总金额"},showFooterBtn:function(t){var e=["-1","0","10","11","20","30"];return t.refund_finish&&"1"==t.refund_finish||e.includes(t.status)},btnClick:function(t,e,i){i||this.$emit("btnClick",{name:t,item:e})},isLoadGroups:function(t){var e,i,a;return"3"==t.activity_type&&"0"==(null===(e=t.groups_team_info)||void 0===e?void 0:e.success)||"4"==t.activity_type&&"0"==(null===(i=t.groups_rebate_team_info)||void 0===i?void 0:i.success)||"6"==t.activity_type&&"0"==(null===(a=t.groups_fission_team_info)||void 0===a?void 0:a.success)},toDetail:function(t){this.$Router.auto({path:"/kdxOrder/detail",query:{order_id:t}})},toGoodsDetail:function(t,e){var i,a="";i="/kdxGoods/detail/index",a={goods_id:t.id},this.$Router.auto({path:i,query:a})}}};e.default=c},6275:function(t,e,i){var a=i("7b71");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("48e0f9ba",a,!0,{sourceMap:!1,shadowMode:!1})},"697c":function(t,e,i){(function(t){var a=i("288e");i("8e6e"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("96cf");var n=a(i("3b8d"));i("5df3"),i("4f7f");var o=a(i("75fc"));i("456d"),i("ac6a"),i("a481");var r=a(i("bd86")),s=a(i("21b3")),d=a(i("5828")),c=a(i("ed7f")),l=a(i("9bd4")),u=a(i("8eed")),f=a(i("c34f")),p=a(i("a64f")),h=a(i("0045")),g=a(i("5ebd"));a(i("898e"));function v(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(t);e&&(a=a.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,a)}return i}function b(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?v(Object(i),!0).forEach((function(e){(0,r.default)(t,e,i[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):v(Object(i)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))}))}return t}var m={mixins:[p.default,h.default],name:"list",components:{listTab:s.default,listGoodsCard:d.default,listModal:c.default,orderPay:l.default,goodsLike:u.default,modalReward:f.default,redpacket:g.default},props:{},data:function(){return{status:"all",list:[],page:1,loading:!1,count:0,apiObj:{all:"allOrderList",pay:"payOrderList",send:"sendOrderList",pick:"pickOrderList",finish:"finishOrderList",delete:"deleteOrderList"},orderData:{},is_comment:0,reqesting:!1,visible:!1,activityType:"consumeReward",shoppingReward:"shoppingReward",activityData:{couponList:[],credit:{title:"消费奖励",number:""},balance:{title:"消费奖励",number:""},red_package:{title:"消费奖励",number:""},popup_type:"0"},shoppingRewardData:{couponList:[],credit:{title:"购物奖励",number:""},balance:{title:"购物奖励",number:""},red_package:{title:"购物奖励",number:""},popup_type:"0"},showShoppingRewardFlag:!1,customVisible:!1,customActivityData:{},showShoppingActivityData:{},showShoppingVisible:!1}},computed:{loadingType:function(){var t=0;return this.loading?t=1:this.list.length==this.count&&this.count>0&&0==this.loading&&(t=2),t},getTitle:function(){return""}},onShow:function(){this.$Route.query.status&&(this.status=this.$Route.query.status),this.page=1,this.list=[],this.getList()},created:function(){},methods:{getList:function(){var t=this;this.loading=!0,this.$api.orderApi[this.apiObj[this.status]]({page:this.page}).then((function(e){if(0==e.error){if(e.list.length>0){var i=function(i){if(e.list[i]=b(b({},e.list[i]),{},{countTime:[]}),0==e.list[i].status)var a=new Date(Date.parse(e.list[i].auto_close_time.replace(/-/g,"/"))).getTime(),n=parseInt(a/1e3),o=setInterval((function(){e.list[i].countTime=t.$utils.countDown(n,!1),e.list[i].countTime||clearInterval(o)}),1e3)};for(var a in e.list)i(a);t.list=t.list.concat(e.list)}t.page=t.page+1,t.is_comment=e.comment_setting.order_comment,t.count=e.total}else t.$toast(e.message);t.loading=!1,t.graceLazyload.load(0,t),t.reqesting=!1})).catch((function(e){t.reqesting=!1})).finally((function(e){t.$loading.hideLoading(),setTimeout((function(){t.$loading.hideLoading(),uni.hideLoading()}),100)}))},btnClick:function(t){var e=this,i={};switch(t.name){case"cancelOrder":this.orderData=t.item,this.cancelOrder();break;case"payOrder":this.orderData=t.item,setTimeout((function(){e.payOrder()}),100);break;case"express":this.orderData=t.item,t.item.orderGoods.forEach((function(t){i[t.package_id]=t.package_id}));var a=Object.keys(i);1==a.length?this.express(a[0]):this.express();break;case"sendOrder":this.orderData=t.item,this.sendOrder();break;case"deleteOrder":this.orderData=t.item,this.deleteOrder();break;case"comment":this.orderData=t.item,t.item.orderGoods.forEach((function(t){i[t.id]=t.id}));var n=Object.keys(i);1==n.length?this.comment(n[0]):this.comment();break;case"toGroups":this.orderData=t.item,this.toGroups();break}},cancelOrder:function(){this.$refs.modals.showCancelModal()},cancelOrderOk:function(){var t=this;this.$api.orderApi.cancelOrder({id:this.orderData.id}).then((function(e){0==e.error?(t.page=1,t.list=[],t.getList()):uni.showToast({title:e.message,icon:"none"})}))},payOrder:function(){this.$refs.orderPay.showPayPicker()},express:function(e){t.log(e,"uuuuuuuuuuuuuu"),e?this.$Router.auto({path:"/kdxOrder/package/detail",query:{package_id:e,order_id:this.orderData.id}}):this.$Router.auto({path:"/kdxOrder/package/list",query:{id:this.orderData.id}})},sendOrder:function(){this.$refs.modals.showSendModal()},sendOrderOk:function(){var t=this;this.$api.orderApi.finishOrder({id:this.orderData.id},{errorToast:!1}).then((function(e){0==e.error?(t.page=1,t.list=[],t.getList(),t.getActivityData(),t.sendShoppingReward()):uni.showToast({title:e.message,icon:"none"})}))},getActivityData:function(){var t=this;this.$api.memberApi.getConsumeRewardActivity({order_id:this.orderData.id},{errorToast:!1}).then((function(e){var i,a;0===e.error&&(t.activityData.popup_type=e.popup_type,(null===e||void 0===e||null===(i=e.coupon_info)||void 0===i?void 0:i.length)>0&&(t.activityData.couponList=(0,o.default)(e.coupon_info)),e.credit&&0!==parseFloat(e.credit)&&(t.activityData.credit.number=e.credit),e.balance&&0!==parseFloat(e.balance)&&(t.activityData.balance.number=e.balance),new Set(e.reward_array).has("4")&&e.red_package&&null!==(a=e.red_package)&&void 0!==a&&a.money&&0!==parseFloat(e.red_package.money)?t.activityData.red_package.number=e.red_package.money:t.activityData.red_package={},new Set(null===e||void 0===e?void 0:e.reward_array).has("4")&&t.getCustomeReward(e.log_id),t.visible=!0)}))},getCustomeReward:function(t){var e=this;this.$api.memberApi.getNoRedpacket({scene_id:t,scene:12}).then((function(t){if(0==t.error&&t.list.length){var i=t.list[0];e.customActivityData={blessing:i.extend.blessing,created_at:i.created_at,expire_time:i.expire_time,limit:i.extend.limit,money:i.money,id:i.id}}}))},changeShowReward:function(t){this.visible=t,"{}"!==JSON.stringify(this.customActivityData)&&(this.customVisible=!0)},changeCloseCustom:function(t){this.customVisible=t.update},deleteOrder:function(){this.$refs.modals.showDeleteModal()},deleteOrderOk:function(){var t=this;this.$api.orderApi.deleteOrder({id:this.orderData.id}).then((function(e){0==e.error?(t.page=1,t.list=[],t.getList()):uni.showToast({title:e.message,icon:"none"})}))},toGroups:function(){var t;this.$store.commit("groups/setGroupsTeamId",null),this.$Router.auto({path:"/kdxGoods/groups/detail",query:{team_id:null===(t=this.orderData.groups_team_info)||void 0===t?void 0:t.id}})},comment:function(t){t?this.$Router.auto({path:"/kdxOrder/comment/index",query:{id:t}}):this.$Router.auto({path:"/kdxOrder/comment/list",query:{order_id:this.orderData.id}})},toIndex:function(){this.$Router.auto({path:"/"})},sendShoppingReward:function(){var t=(0,n.default)(regeneratorRuntime.mark((function t(){var e,i;return regeneratorRuntime.wrap((function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,this.$store.dispatch("member/sendShoppingReward",this.orderData.id);case 2:e=t.sent,e.send_type&&"1"===e.send_type&&(this.shoppingRewardData.popup_type=e.popup_type,e.coupon_info&&e.coupon_info.length&&(this.shoppingRewardData.couponList=(0,o.default)(e.coupon_info)),e.credit&&0!==parseFloat(e.credit)&&(this.shoppingRewardData.credit.number=e.credit),e.balance&&0!==parseFloat(e.balance)&&(this.shoppingRewardData.balance.number=e.balance),new Set(e.reward_array).has("4")&&e.red_package&&null!==(i=e.red_package)&&void 0!==i&&i.money&&0!==parseFloat(e.red_package.money)?this.shoppingRewardData.red_package.number=e.red_package.money:this.activityData.red_package={},new Set(null===e||void 0===e?void 0:e.reward_array).has("4")&&this.getShoppingReward(e.log_id),this.showShoppingRewardFlag=!0);case 4:case"end":return t.stop()}}),t,this)})));function e(){return t.apply(this,arguments)}return e}(),getShoppingReward:function(t){var e=this;this.$api.memberApi.getNoRedpacket({scene_id:t,scene:11}).then((function(t){if(0==t.error&&t.list.length){var i=t.list[0];e.showShoppingActivityData={blessing:i.extend.blessing,created_at:i.created_at,expire_time:i.expire_time,limit:i.extend.limit,money:i.money,id:i.id}}}))},changeShowShoppingRewardFlag:function(t){this.showShoppingRewardFlag=t,"{}"!==JSON.stringify(this.showShoppingActivityData)&&(this.showShoppingVisible=!0)},changeCloseShopping:function(t){this.showShoppingVisible=t.update}},onReachBottom:function(){this.list.length==this.count&&this.page>1||this.getList()},onPageScroll:function(t){this.graceLazyload.load(t.scrollTop,this)}};e.default=m}).call(this,i("5a52")["default"])},"6e58":function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"list-modal"},[i("rr-modal",{attrs:{title:"确定取消订单吗？"},on:{handlerCancel:function(e){arguments[0]=e=t.$handleEvent(e),t.handlerCancel.apply(void 0,arguments)},handlerOK:function(e){arguments[0]=e=t.$handleEvent(e),t.cancelOrderOk.apply(void 0,arguments)}},model:{value:t.cancelModal,callback:function(e){t.cancelModal=e},expression:"cancelModal"}}),i("rr-modal",{attrs:{title:"确定删除订单吗？"},on:{handlerCancel:function(e){arguments[0]=e=t.$handleEvent(e),t.handlerCancel.apply(void 0,arguments)},handlerOK:function(e){arguments[0]=e=t.$handleEvent(e),t.deleteOrderOk.apply(void 0,arguments)}},model:{value:t.deleteModal,callback:function(e){t.deleteModal=e},expression:"deleteModal"}}),i("rr-modal",{attrs:{title:"确定收到所有商品了吗？"},on:{handlerCancel:function(e){arguments[0]=e=t.$handleEvent(e),t.handlerCancel.apply(void 0,arguments)},handlerOK:function(e){arguments[0]=e=t.$handleEvent(e),t.sendOrderOk.apply(void 0,arguments)}},model:{value:t.sendModal,callback:function(e){t.sendModal=e},expression:"sendModal"}})],1)},o=[]},7086:function(t,e,i){var a=i("b3e8");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("2a82dc87",a,!0,{sourceMap:!1,shadowMode:!1})},"7b71":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.list-goods-card .card-item[data-v-7ba67ed4]{padding:0 %?24?%;margin-bottom:%?16?%;background-color:#fff;border-radius:%?12?%}.list-goods-card .card-item .create-time[data-v-7ba67ed4]{height:%?72?%;margin-bottom:%?16?%;line-height:%?72?%;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between}.list-goods-card .card-item .create-time > uni-view[data-v-7ba67ed4]{font-size:%?24?%}.list-goods-card .card-item .create-time .shop-item[data-v-7ba67ed4]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;font-size:%?28?%}.list-goods-card .card-item .create-time .shop-item .shop-logo[data-v-7ba67ed4]{margin-right:%?16?%;width:%?48?%;height:%?48?%;font-size:%?28?%;background-size:100% 100%;background-repeat:no-repeat}.list-goods-card .card-item .create-time .shop-item .shop-img[data-v-7ba67ed4]{width:%?48?%;height:%?48?%;border-radius:50%}.list-goods-card .card-item .create-time .shop-item .self-label[data-v-7ba67ed4]{margin-right:%?8?%;padding:%?2?% %?6?%;line-height:%?24?%;background:-webkit-linear-gradient(317.43deg,#ff3c29,#ff6f29 94.38%);background:linear-gradient(132.57deg,#ff3c29,#ff6f29 94.38%);color:#fff;font-size:%?18?%;font-weight:600;border-radius:%?4?%}.list-goods-card .card-item .create-time .shop-item .shop-name[data-v-7ba67ed4]{line-height:20px}.list-goods-card .card-item .create-time .status[data-v-7ba67ed4]{color:#969696;font-size:%?28?%;font-weight:700}.list-goods-card .card-item .create-time .primary[data-v-7ba67ed4]{color:#ff3c29;font-size:%?28?%;font-weight:700}.list-goods-card .card-item .create-time .orange[data-v-7ba67ed4]{color:#f90;font-size:%?28?%;font-weight:700}.list-goods-card .card-item .create-time .blue[data-v-7ba67ed4]{color:#367bf5}.list-goods-card .card-item .create-time .success[data-v-7ba67ed4]{color:#09c15f}.list-goods-card .card-item .goods-item[data-v-7ba67ed4]{padding-bottom:%?32?%}.list-goods-card .card-item .goods-item[data-v-7ba67ed4]:first-child{padding-top:%?32?%}.list-goods-card .card-item .card-footer[data-v-7ba67ed4]{text-align:right;padding-bottom:%?32?%}.list-goods-card .card-item .card-footer > div[data-v-7ba67ed4]{line-height:%?44?%;font-size:%?24?%;color:#212121}.list-goods-card .card-item .card-footer > div.uni-color-primary[data-v-7ba67ed4]{font-family:PingFang SC;font-variant-numeric:tabular-nums;font-family:Helvetica Neue;line-height:%?34?%;margin-top:%?8?%}.list-goods-card .card-item .card-footer .price[data-v-7ba67ed4]{font-weight:700;font-size:%?32?%}.list-goods-card .card-item .card-btn[data-v-7ba67ed4]{-webkit-box-pack:end;-webkit-justify-content:flex-end;justify-content:flex-end;padding-bottom:%?16?%}.list-goods-card .card-item .card-btn > uni-view[data-v-7ba67ed4]{padding:%?11?% %?30?%;line-height:%?34?%;font-size:%?24?%;text-align:center;border-radius:%?28?%;color:#212121;border:1px solid #e6e7eb;margin-right:%?24?%}.list-goods-card .card-item .card-btn > uni-view[data-v-7ba67ed4]:last-child{margin-right:0}.list-goods-card .card-item .card-btn .border-primary[data-v-7ba67ed4]{border:1px solid #ff3c29}.list-goods-card .card-item .card-btn .disabled[data-v-7ba67ed4]{color:#ccc;border:1px solid #ccc}',""]),t.exports=e},"7f50":function(t,e,i){"use strict";i.r(e);var a=i("0beab"),n=i("f92b");for(var o in n)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("c1ef");var r,s=i("f0c5"),d=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"853f8186",null,!1,a["a"],r);e["default"]=d.exports},"891e":function(t,e,i){"use strict";i.r(e);var a=i("ccdb"),n=i.n(a);for(var o in a)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},9e3:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"goods-block-inner"},[i("v-uni-view",{staticClass:"flex"},[i("v-uni-view",{staticClass:"goods-image",style:t.backgroundImage},[i("v-uni-image",{staticClass:"grace-img-lazy",attrs:{src:t.handleThumb()},on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.detail(t.goodsData.goods_id,t.goodsData)}}}),11==t.status?[20==t.goodsData.status?i("v-uni-view",{staticClass:"send-icon"},[t._v("已发货")]):t._e(),10==t.goodsData.status?i("v-uni-view",{staticClass:"send-icon"},[t._v("未发货")]):t._e()]:t._e()],2),i("v-uni-view",{staticClass:"flex1"},[i("v-uni-view",{staticClass:"goods-info flex-column"},[i("v-uni-view",{staticClass:"title-box"},[i("v-uni-view",{staticClass:"title two-line-hide"},[t._v(t._s(t.goodsData.title))]),t.handleOptionTitle()?i("v-uni-view",{staticClass:"option-title line-hide"},[t._v(t._s(t.handleOptionTitle()))]):t._e()],1),t.isRefund?i("v-uni-view",{staticClass:"flex align-center"},[i("v-uni-view",{staticClass:"refund-money-label"},[t._v("退款金额：")]),"0.00"===t.goodsData.price&&"0"!==t.goodsData.credit?i("v-uni-view",{staticClass:"refund-money-value"},[t._v(t._s(t.goodsData.credit)+t._s(t.credit_text))]):t._e(),"0.00"!==t.goodsData.price&&"0"===t.goodsData.credit?i("v-uni-view",{staticClass:"refund-money-value"},[t._v("￥"+t._s(t.goodsData.price))]):t._e(),"0.00"!==t.goodsData.price&&"0"!==t.goodsData.credit?i("v-uni-view",{staticClass:"refund-money-value"},[t._v(t._s(t.goodsData.credit)+t._s(t.credit_text)+"+￥"+t._s(t.goodsData.price))]):t._e()],1):t._e(),t.isRefund||"3"==t.goodsData.type?t._e():i("v-uni-view",{staticClass:"price-box flex-between"},[t.goodsData.credit_unit?i("v-uni-view",{staticClass:"credit-price price"},[i("span",{staticClass:"primary-price theme-primary-price"},[t._v(t._s(t.goodsData.credit_unit)+t._s(t.credit_text))]),"0.00"!==t.goodsData.price_unit?i("span",{staticClass:"primary-price theme-primary-price"},[t._v("+￥"+t._s(t.goodsData.price_unit))]):t._e()]):i("v-uni-view",{staticClass:"price theme-primary-price"},[t._v("￥"),i("span",{staticClass:"primary-price theme-primary-price"},[t._v(t._s(t.goodsData.price_unit||t.goodsData.price))])]),i("v-uni-view",{staticClass:"add-num"},[i("v-uni-text",[t._v("x"+t._s(t.goodsData.total))])],1)],1)],1)],1)],1),t.$slots.btn?i("v-uni-view",{staticClass:"flex flex-end btn"},[t._t("btn")],2):t._e()],1)},o=[]},"93d1":function(t,e,i){"use strict";i.r(e);var a=i("2085"),n=i.n(a);for(var o in a)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},"94de":function(t,e,i){"use strict";var a=i("a659"),n=i.n(a);n.a},a659:function(t,e,i){var a=i("4097");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("4943b5f0",a,!0,{sourceMap:!1,shadowMode:!1})},b3e8:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.order-list[data-v-853f8186]{position:relative;min-height:100vh;overflow:auto;background-color:#f5f5f5}.order-list .none[data-v-853f8186]{position:fixed;top:%?-99999?%;left:%?-99999?%;z-index:-99999}.order-list .container[data-v-853f8186]{padding:%?102?% %?24?% %?16?%}[data-v-853f8186] uni-canvas{position:fixed;top:%?-99999?%;left:%?-99999?%;z-index:-99999}',""]),t.exports=e},b88d:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"list-goods-card"},t._l(t.orderList,(function(e,a){return i("v-uni-view",{key:a,staticClass:"card-item",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.toDetail(e.id)}}},[i("v-uni-view",{staticClass:"create-time flex"},[[i("div",{staticClass:"uni-text-color-grey"},[t._v(t._s(e.created_at))])],[e.refund_finish&&"1"==e.refund_finish?i("v-uni-view",{staticClass:"status success"},[t._v("售后完成")]):i("v-uni-view",[0==e.status?i("v-uni-view",{staticClass:"status uni-color-primary"},[t._v("待付款")]):t._e(),10==e.status?i("v-uni-view",{staticClass:"status"},[t.isLoadGroups(e)?i("span",{staticClass:"primary"},[t._v("待成团")]):i("span",{staticClass:"orange"},[t._v("待发货")])]):t._e(),11==e.status?i("v-uni-view",{staticClass:"status orange"},[t._v("部分发货")]):t._e(),20==e.status?i("v-uni-view",{staticClass:"status blue"},[t._v("待收货")]):t._e(),30==e.status?i("v-uni-view",{staticClass:"status success"},[t._v("已完成")]):t._e(),-1==e.status?i("v-uni-view",{staticClass:"status"},[t._v("已关闭")]):t._e()],1)]],2),t._l(e.orderGoods,(function(a,n){return i("v-uni-view",{key:n,staticClass:"goods-item"},[i("goods-card",{attrs:{goodsData:a,status:e.status,orderData:e},on:{detail:function(i){arguments[0]=i=t.$handleEvent(i),t.toGoodsDetail(i,e.activity_type)}}})],1)})),i("v-uni-view",{staticClass:"card-footer"},[i("div",[t._v("共"+t._s(e.orderGoods.length)+"件商品，"+t._s(e.format_text)),i("span",{staticClass:"price"},[e.pay_credit?i("span",{staticClass:"price theme-primary-colo"},[t._v(t._s(e.pay_credit+t.credit_text)+"+")]):t._e(),t._v("￥"+t._s(e.pay_price))])]),0==e.status&&e.countTime.length>0?i("div",{staticClass:"uni-color-primary"},[i("span",[t._v(t._s(e.countTime[0])+"天")]),i("span",[t._v(t._s(e.countTime[1])+"小时")]),i("span",[t._v(t._s(e.countTime[2])+"分")]),i("span",[t._v(t._s(e.countTime[3])+"秒")]),i("span",[t._v(t._s(e.count_text))])]):t._e()]),t.showFooterBtn(e)?i("v-uni-view",{staticClass:"card-btn flex"},[e.refund_finish&&"1"==e.refund_finish?[i("v-uni-view",{on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.btnClick("deleteOrder",e)}}},[t._v("删除订单")])]:[0==e.status?[i("v-uni-view",{on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.btnClick("cancelOrder",e)}}},[t._v("取消订单")])]:t._e(),0==e.status?i("v-uni-view",{class:e.is_buy?"disabled":"border-primary theme-primary-color theme-primary-border",on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.btnClick("payOrder",e,e.is_buy)}}},[t._v("去支付")]):t._e(),(20==e.status&&"0"===e.orderGoods[0].type||11==e.status||30==e.status)&&"10"===e.dispatch_type?i("v-uni-view",{on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.btnClick("express",e)}}},[t._v("查看物流")]):t._e(),20==e.status?i("v-uni-view",{staticClass:"border-primary theme-primary-color theme-primary-border",on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.btnClick("sendOrder",e)}}},[t._v("确认收货")]):t._e(),3==e.activity_type&&10==e.status?[i("v-uni-view",{staticClass:"border-primary theme-primary-color theme-primary-border",on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.btnClick("toGroups",e)}}},[t._v("查看团详情")])]:t._e(),30==e.status||-1==e.status?i("v-uni-view",{on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.btnClick("deleteOrder",e)}}},[t._v("删除订单")]):t._e(),30==e.status&&1==t.is_comment&&0==e.comment_status&&"5"!==e.activity_type?i("v-uni-view",{staticClass:"border-primary theme-primary-color theme-primary-border",on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.btnClick("comment",e)}}},[t._v("评价")]):t._e()]],2):t._e()],2)})),1)},o=[]},bf1c:function(t,e,i){var a=i("288e");i("8e6e"),i("ac6a"),i("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=a(i("bd86"));i("c5f6");var o=i("2f62");function r(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(t);e&&(a=a.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,a)}return i}function s(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?r(Object(i),!0).forEach((function(e){(0,n.default)(t,e,i[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):r(Object(i)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))}))}return t}var d={name:"GoodsInfo",components:{},props:{goodsData:{type:Object,default:function(){}},isRefund:{type:Boolean,default:!1},status:{type:[String,Number]},orderData:{type:Object,default:function(){}},orderType:{type:[String,Number]}},data:function(){return{}},watch:{goodsData:{immediate:!0,handler:function(t){t.thumb="".concat(this.$utils.mediaUrl(t.thumb)),this.goodsData=t}}},computed:s(s({},(0,o.mapState)("setting",{credit_text:function(t){var e;return(null===(e=t.systemSetting)||void 0===e?void 0:e.credit_text)||"积分"}})),{},{backgroundImage:function(){return"background-image:url(".concat(this.$utils.staticMediaUrl("decorate/goods_col2.png"),")")},order_id:function(){var t,e;return!!(null!==(t=this.$Route.query)&&void 0!==t&&t.order_id||null!==(e=this.$Route.query)&&void 0!==e&&e.order_goods_id)},chooseType:function(){if(this.orderData)return this.orderData.activity_type}}),methods:{detail:function(t,e){this.$emit("detail",{id:t,goodsData:e})},handleThumb:function(){if(this.goodsData&&this.goodsData.thumb)return"".concat(this.$utils.mediaUrl(this.goodsData.thumb))},handleOptionTitle:function(){var t;return(null===(t=this.goodsData)||void 0===t?void 0:t.option_title)||""}}};e.default=d},c1ef:function(t,e,i){"use strict";var a=i("7086"),n=i.n(a);n.a},c58f:function(t,e,i){"use strict";i.r(e);var a=i("9000"),n=i("12e4");for(var o in n)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("94de");var r,s=i("f0c5"),d=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"4656ab3a",null,!1,a["a"],r);e["default"]=d.exports},ccdb:function(t,e,i){(function(t){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i={name:"ListModal",components:{},props:{title:String},data:function(){return{cancelModal:!1,deleteModal:!1,sendModal:!1}},computed:{},created:function(){},mounted:function(){},methods:{showCancelModal:function(){t.log(this._uid),this.cancelModal=!0},showDeleteModal:function(){this.deleteModal=!0},showSendModal:function(){this.sendModal=!0},handlerCancel:function(){this.cancelModal=!1,this.deleteModal=!1,this.sendModal=!1},cancelOrderOk:function(){this.cancelModal=!1,this.$emit("cancelOrderOk")},deleteOrderOk:function(){this.deleteModal=!1,this.$emit("deleteOrderOk")},sendOrderOk:function(){this.sendModal=!1,this.$emit("sendOrderOk")}}};e.default=i}).call(this,i("5a52")["default"])},d19d:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("theme-content",[i("v-uni-view",{staticClass:"list-tab"},[i("v-uni-view",{staticClass:"tab flex"},t._l(t.status,(function(e,a){return i("v-uni-view",{key:a,staticClass:"item",class:t.current==e.type?"active theme-primary-color":"",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.changeTab(e.type)}}},[t._v(t._s(e.text)),i("span",{staticClass:"line theme-primary-bgcolor"})])})),1)],1)],1)},o=[]},e3b6:function(t,e,i){"use strict";i.r(e);var a=i("6242"),n=i.n(a);for(var o in a)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},ed7f:function(t,e,i){"use strict";i.r(e);var a=i("6e58"),n=i("891e");for(var o in n)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(o);var r,s=i("f0c5"),d=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"f9c1f4ae",null,!1,a["a"],r);e["default"]=d.exports},f92b:function(t,e,i){"use strict";i.r(e);var a=i("697c"),n=i.n(a);for(var o in a)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a}}]);