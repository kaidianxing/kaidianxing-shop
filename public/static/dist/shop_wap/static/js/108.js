(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[108],{"0998":function(t,e,i){var a=i("288e");i("8e6e"),i("ac6a"),i("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=a(i("bd86"));i("c5f6");var o=a(i("0347"));function d(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(t);e&&(a=a.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,a)}return i}function c(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?d(Object(i),!0).forEach((function(e){(0,n.default)(t,e,i[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):d(Object(i)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))}))}return t}var r={components:{MyVideo:o.default},props:{refreshKey:{type:[String,Number],default:""},height:{type:String,default:""},list:{type:Array,default:function(){return[]}},startIndex:{type:Number,default:0}},watch:{list:{immediate:!0,deep:!0,handler:function(){this.key=Math.random(),this.value=this.startIndex}}},computed:{getList:function(){var t=this;return this.list.map((function(e){return e.video&&(e=c(c({},e),{},{clickBtn:t.clickBtn.bind(t),pause:t.pause.bind(t),stop:t.stop.bind(t),playing:t.playing})),e}))}},data:function(){return{id:"goodswiper",video:null,$myvideo:null,playing:0,key:"",value:0,setting:{autoplay:!1,loop:!1}}},mounted:function(){var t=this;this.$decorator.$on("refreshMyVideo",(function(){t.playing=0,t.$emit("refresh",t.playing)}))},methods:{clickBtn:function(){var t;null===(t=this.$refs.myvideo[0])||void 0===t||t.click(),this.playing=1,this.$emit("custom-event",{target:"video/clickBtn"})},stop:function(){var t=this;this.$nextTick((function(){t.playing=0}))},pause:function(){var t=this;this.$nextTick((function(){t.playing=2}))},change:function(t){var e=t.detail.current;this.$emit("change",e)},click:function(t){this.$emit("click",t)}}};e.default=r},"0f55":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.detail_swipe.decorate[data-v-33db50fe],\n.detail_swipe.decorate .content-box[data-v-33db50fe]{-webkit-overflow-scrolling:touch;position:relative;width:%?750?%;height:%?750?%;margin:0 auto;overflow:hidden}.detail_swipe.decorate uni-swiper[data-v-33db50fe],\n.detail_swipe.decorate .content-box uni-swiper[data-v-33db50fe]{width:100%;height:%?750?%!important}.detail_swipe.decorate uni-swiper uni-view[data-v-33db50fe],\n.detail_swipe.decorate uni-swiper uni-swiper[data-v-33db50fe],\n.detail_swipe.decorate uni-swiper uni-swiper-item[data-v-33db50fe],\n.detail_swipe.decorate uni-swiper uni-image[data-v-33db50fe],\n.detail_swipe.decorate .content-box uni-swiper uni-view[data-v-33db50fe],\n.detail_swipe.decorate .content-box uni-swiper uni-swiper[data-v-33db50fe],\n.detail_swipe.decorate .content-box uni-swiper uni-swiper-item[data-v-33db50fe],\n.detail_swipe.decorate .content-box uni-swiper uni-image[data-v-33db50fe]{width:100%!important;height:100%!important;display:block}.detail_swipe.decorate .swiper[data-v-33db50fe],\n.detail_swipe.decorate .content-box .swiper[data-v-33db50fe]{width:100%;height:%?750?%!important}.detail_swipe.decorate .swiper uni-view[data-v-33db50fe],\n.detail_swipe.decorate .swiper uni-swiper[data-v-33db50fe],\n.detail_swipe.decorate .swiper uni-swiper-item[data-v-33db50fe],\n.detail_swipe.decorate .swiper img[data-v-33db50fe],\n.detail_swipe.decorate .content-box .swiper uni-view[data-v-33db50fe],\n.detail_swipe.decorate .content-box .swiper uni-swiper[data-v-33db50fe],\n.detail_swipe.decorate .content-box .swiper uni-swiper-item[data-v-33db50fe],\n.detail_swipe.decorate .content-box .swiper img[data-v-33db50fe]{width:100%!important;height:100%!important}.detail_swipe.decorate .dots[data-v-33db50fe],\n.detail_swipe.decorate .content-box .dots[data-v-33db50fe]{position:absolute;display:-webkit-box;display:-webkit-flex;display:flex;bottom:%?32?%;box-sizing:border-box;padding:0 0;left:0;right:0;padding:0 %?48?%;-webkit-box-pack:center!important;-webkit-justify-content:center!important;justify-content:center!important;text-align:center}.detail_swipe.decorate .dots .dot[data-v-33db50fe],\n.detail_swipe.decorate .content-box .dots .dot[data-v-33db50fe]{width:%?12?%;height:%?12?%;background:#212121;margin:auto %?6?%;opacity:.34;border-radius:50%;position:relative;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.detail_swipe.decorate .dots .dot-img[data-v-33db50fe],\n.detail_swipe.decorate .content-box .dots .dot-img[data-v-33db50fe]{width:%?24?%;height:%?24?%;font-size:%?24?%;line-height:%?24?%;text-align:center;margin:auto}.detail_swipe.decorate .dots .dot.active[data-v-33db50fe],\n.detail_swipe.decorate .content-box .dots .dot.active[data-v-33db50fe]{opacity:1}.detail_swipe.decorate .dots.round .dot.video[data-v-33db50fe],\n.detail_swipe.decorate .content-box .dots.round .dot.video[data-v-33db50fe]{width:%?24?%;height:%?24?%;background:transparent!important;color:#c4c4c4;opacity:1;-webkit-transform:scale(.9);transform:scale(.9)}.detail_swipe.decorate .dots.round .dot.video.active[data-v-33db50fe],\n.detail_swipe.decorate .content-box .dots.round .dot.video.active[data-v-33db50fe]{background:#c4c4c4!important;color:#212121;opacity:1}.detail_swipe.decorate .dots.rectangle .dot[data-v-33db50fe],\n.detail_swipe.decorate .content-box .dots.rectangle .dot[data-v-33db50fe]{width:%?20?%;height:%?8?%;border-radius:%?4?%;position:relative;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.detail_swipe.decorate .detail_video[data-v-33db50fe],\n.detail_swipe.decorate .content-box .detail_video[data-v-33db50fe]{width:%?750?%;height:%?750?%;display:-webkit-box;display:-webkit-flex;display:flex;position:relative}.detail_swipe.decorate .detail_video.ratio-0[data-v-33db50fe],\n.detail_swipe.decorate .content-box .detail_video.ratio-0[data-v-33db50fe]{height:%?421.41694?%}.detail_swipe.decorate .detail_video.ratio-1[data-v-33db50fe],\n.detail_swipe.decorate .content-box .detail_video.ratio-1[data-v-33db50fe]{height:%?560.66775?%}.detail_swipe.decorate .detail_video.ratio-2[data-v-33db50fe],\n.detail_swipe.decorate .content-box .detail_video.ratio-2[data-v-33db50fe]{height:%?747.557?%}.detail_swipe.decorate .detail_video .myvideo[data-v-33db50fe],\n.detail_swipe.decorate .content-box .detail_video .myvideo[data-v-33db50fe]{width:100%;height:100%;border-radius:%?12?%}.detail_swipe.decorate .detail_video .modal[data-v-33db50fe],\n.detail_swipe.decorate .content-box .detail_video .modal[data-v-33db50fe]{position:absolute;top:0;bottom:0;left:0;right:0;background:rgba(0,0,0,.5);display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;z-index:10}.detail_swipe.decorate .detail_video .modal .poster[data-v-33db50fe],\n.detail_swipe.decorate .content-box .detail_video .modal .poster[data-v-33db50fe]{position:absolute;top:0;bottom:0;left:0;right:0;width:100%;height:100%}.detail_swipe.decorate .detail_video .modal .pause[data-v-33db50fe],\n.detail_swipe.decorate .content-box .detail_video .modal .pause[data-v-33db50fe]{display:block;width:%?146.5798?%;margin:auto;position:relative;z-index:100}.detail_swipe.decorate .number[data-v-33db50fe],\n.detail_swipe.decorate .content-box .number[data-v-33db50fe]{position:absolute;display:-webkit-box;display:-webkit-flex;display:flex;bottom:%?32?%;box-sizing:border-box;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;color:#fff;background:rgba(0,0,0,.54);border-radius:%?44?%;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;text-align:center;width:-webkit-fit-content;width:fit-content;min-width:%?60?%;font-size:%?18?%;line-height:%?18?%;padding:%?6?% %?10?%;left:50%;-webkit-transform:translate(-50%);transform:translate(-50%)}.detail_swipe.decorate .number.left[data-v-33db50fe],\n.detail_swipe.decorate .content-box .number.left[data-v-33db50fe]{left:%?48?%;-webkit-transform:translate(0);transform:translate(0)}.detail_swipe.decorate .number.right[data-v-33db50fe],\n.detail_swipe.decorate .content-box .number.right[data-v-33db50fe]{left:auto;right:%?48?%;-webkit-transform:translate(0);transform:translate(0)}',""]),t.exports=e},"1e6e":function(t,e,i){"use strict";i.r(e);var a=i("babe"),n=i("ca89");for(var o in n)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("8e90");var d,c=i("f0c5"),r=Object(c["a"])(n["default"],a["b"],a["c"],!1,null,"cd8ca85c",null,!1,a["a"],d);e["default"]=r.exports},3341:function(t,e,i){var a=i("288e");i("8e6e"),i("ac6a"),i("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=a(i("bd86")),o=i("2f62"),d=i("dc11");function c(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(t);e&&(a=a.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,a)}return i}function r(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?c(Object(i),!0).forEach((function(e){(0,n.default)(t,e,i[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):c(Object(i)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))}))}return t}var s={computed:r({},(0,o.mapState)("decorate",{pageList:function(t){return t.pageList}})),props:{startLoadImg:{type:Boolean,default:!0},componentData:{type:Object,default:function(){return{style:{},params:{}}}}},methods:{px2rpx:d.px2rpx}};e.default=s},"421e":function(t,e,i){"use strict";i.r(e);var a=i("8c10"),n=i("4814");for(var o in n)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("e587"),i("f107");var d,c=i("f0c5"),r=Object(c["a"])(n["default"],a["b"],a["c"],!1,null,"33db50fe",null,!1,a["a"],d);e["default"]=r.exports},"44f9":function(t,e,i){var a=i("74f2");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("346307aa",a,!0,{sourceMap:!1,shadowMode:!1})},4814:function(t,e,i){"use strict";i.r(e);var a=i("4de1"),n=i.n(a);for(var o in a)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},"4de1":function(t,e,i){var a=i("288e");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=a(i("75fc")),o=a(i("3341")),d=a(i("1e6e")),c={mixins:[o.default],name:"detail_swipe",components:{MySwiper:d.default},data:function(){return{activeIndex:0,key:""}},computed:{getAlign:function(){var t={left:"flex-start!important",center:"center!important",right:"flex-end!important"};return t[this.componentData.style.dotalign]},getList:function(){var t=[];return this.componentData.data instanceof Array&&(t=this.componentData.data.map((function(t){return{img:t.imgurl}}))),this.componentData.params.video&&(t=[this.componentData.params].concat((0,n.default)(t))),t}},methods:{refresh:function(t){this.key=t},change:function(t){this.activeIndex=t},click:function(t){this.componentData.params.video&&(t-=1),this.$emit("custom-event",{target:"detail_swipe/clickImg",data:{items:this.componentData.data.map((function(t){return t.imgurl})),index:t}})},getBg:function(t){return this.activeIndex==t?this.componentData.style.background:"#212121"},getIconClass:function(t){return"number"!==this.componentData.style.dotstyle&&this.activeIndex==t?this.componentData.style.background:"#c4c4c4"}}};e.default=c},"74f2":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.img-box[data-v-cd8ca85c]{height:100%}.img-box img[data-v-cd8ca85c]{object-fit:contain;object-position:center;background:#000}uni-swiper[data-v-cd8ca85c]{height:100%}.swiper-img[data-v-cd8ca85c]{text-align:center;width:100%;display:block;height:%?284?%}.swiper[data-v-cd8ca85c]{height:%?284?%;overflow:hidden}.detail_swipe[data-v-cd8ca85c],\n.detail_swipe .content-box[data-v-cd8ca85c]{position:relative;width:%?750?%;height:%?750?%;margin:0 auto;overflow:hidden}.detail_swipe uni-swiper[data-v-cd8ca85c],\n.detail_swipe .content-box uni-swiper[data-v-cd8ca85c]{width:100%;height:%?750?%!important}.detail_swipe uni-swiper uni-view[data-v-cd8ca85c],\n.detail_swipe uni-swiper uni-swiper[data-v-cd8ca85c],\n.detail_swipe uni-swiper uni-swiper-item[data-v-cd8ca85c],\n.detail_swipe uni-swiper uni-image[data-v-cd8ca85c],\n.detail_swipe .content-box uni-swiper uni-view[data-v-cd8ca85c],\n.detail_swipe .content-box uni-swiper uni-swiper[data-v-cd8ca85c],\n.detail_swipe .content-box uni-swiper uni-swiper-item[data-v-cd8ca85c],\n.detail_swipe .content-box uni-swiper uni-image[data-v-cd8ca85c]{width:100%!important;height:100%!important;display:block}.detail_swipe .swiper[data-v-cd8ca85c],\n.detail_swipe .content-box .swiper[data-v-cd8ca85c]{width:100%;height:%?750?%!important}.detail_swipe .swiper uni-view[data-v-cd8ca85c],\n.detail_swipe .swiper uni-swiper[data-v-cd8ca85c],\n.detail_swipe .swiper uni-swiper-item[data-v-cd8ca85c],\n.detail_swipe .swiper img[data-v-cd8ca85c],\n.detail_swipe .content-box .swiper uni-view[data-v-cd8ca85c],\n.detail_swipe .content-box .swiper uni-swiper[data-v-cd8ca85c],\n.detail_swipe .content-box .swiper uni-swiper-item[data-v-cd8ca85c],\n.detail_swipe .content-box .swiper img[data-v-cd8ca85c]{width:100%!important;height:100%!important}.detail_swipe .dots[data-v-cd8ca85c],\n.detail_swipe .content-box .dots[data-v-cd8ca85c]{position:absolute;display:-webkit-box;display:-webkit-flex;display:flex;bottom:%?32?%;box-sizing:border-box;padding:0 0;left:0;right:0;padding:0 %?48?%;-webkit-box-pack:center!important;-webkit-justify-content:center!important;justify-content:center!important;text-align:center}.detail_swipe .dots .dot[data-v-cd8ca85c],\n.detail_swipe .content-box .dots .dot[data-v-cd8ca85c]{width:%?12?%;height:%?12?%;background:#212121;margin:auto %?6?%;opacity:.34;border-radius:50%;position:relative;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.detail_swipe .dots .dot-img[data-v-cd8ca85c],\n.detail_swipe .content-box .dots .dot-img[data-v-cd8ca85c]{width:%?24?%;height:%?24?%;font-size:%?24?%;line-height:%?24?%;text-align:center;margin:auto}.detail_swipe .dots .dot.active[data-v-cd8ca85c],\n.detail_swipe .content-box .dots .dot.active[data-v-cd8ca85c]{opacity:1}.detail_swipe .dots.round .dot.video[data-v-cd8ca85c],\n.detail_swipe .content-box .dots.round .dot.video[data-v-cd8ca85c]{width:%?24?%;height:%?24?%;background:transparent!important;color:#c4c4c4;opacity:1;-webkit-transform:scale(.9);transform:scale(.9)}.detail_swipe .dots.round .dot.video.active[data-v-cd8ca85c],\n.detail_swipe .content-box .dots.round .dot.video.active[data-v-cd8ca85c]{background:#c4c4c4!important;color:#212121;opacity:1}.detail_swipe .dots.rectangle .dot[data-v-cd8ca85c],\n.detail_swipe .content-box .dots.rectangle .dot[data-v-cd8ca85c]{width:%?20?%;height:%?8?%;border-radius:%?4?%;position:relative;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.detail_swipe .detail_video[data-v-cd8ca85c],\n.detail_swipe .content-box .detail_video[data-v-cd8ca85c]{width:%?750?%;height:%?750?%;display:-webkit-box;display:-webkit-flex;display:flex;position:relative}.detail_swipe .detail_video.ratio-0[data-v-cd8ca85c],\n.detail_swipe .content-box .detail_video.ratio-0[data-v-cd8ca85c]{height:%?421.41694?%}.detail_swipe .detail_video.ratio-1[data-v-cd8ca85c],\n.detail_swipe .content-box .detail_video.ratio-1[data-v-cd8ca85c]{height:%?560.66775?%}.detail_swipe .detail_video.ratio-2[data-v-cd8ca85c],\n.detail_swipe .content-box .detail_video.ratio-2[data-v-cd8ca85c]{height:%?747.557?%}.detail_swipe .detail_video .myvideo[data-v-cd8ca85c],\n.detail_swipe .content-box .detail_video .myvideo[data-v-cd8ca85c]{width:100%;height:100%}.detail_swipe .detail_video .modal[data-v-cd8ca85c],\n.detail_swipe .content-box .detail_video .modal[data-v-cd8ca85c]{position:absolute;top:0;bottom:0;left:0;right:0;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;z-index:10;color:#fff}.detail_swipe .detail_video .modal .poster[data-v-cd8ca85c],\n.detail_swipe .content-box .detail_video .modal .poster[data-v-cd8ca85c]{position:absolute;top:0;bottom:0;left:0;right:0;width:100%;height:100%;background-repeat:no-repeat;background-position:50%;background-size:cover}.detail_swipe .detail_video .modal .pause[data-v-cd8ca85c],\n.detail_swipe .content-box .detail_video .modal .pause[data-v-cd8ca85c]{display:block;width:%?146.5798?%;margin:auto;position:relative;z-index:100}.detail_swipe .detail_video .modal .iconfont-m-[data-v-cd8ca85c],\n.detail_swipe .content-box .detail_video .modal .iconfont-m-[data-v-cd8ca85c]{display:inline-block;position:absolute;top:50%;left:50%;-webkit-transform:translate(-50%,-50%);transform:translate(-50%,-50%);font-size:%?144?%;width:%?144?%!important;height:%?144?%!important;text-align:center;line-height:%?144?%;color:#fff}.detail_swipe .detail_video .modal .iconfont-m-[data-v-cd8ca85c]::after,\n.detail_swipe .content-box .detail_video .modal .iconfont-m-[data-v-cd8ca85c]::after{content:"";position:absolute;background:rgba(0,0,0,.32);top:0;left:0;right:0;bottom:0;margin:auto;width:82%;height:82%;border-radius:50%;z-index:-1}.detail_swipe .number[data-v-cd8ca85c],\n.detail_swipe .content-box .number[data-v-cd8ca85c]{position:absolute;display:-webkit-box;display:-webkit-flex;display:flex;bottom:%?32?%;box-sizing:border-box;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;color:#fff;background:rgba(0,0,0,.54);border-radius:%?44?%;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;text-align:center;width:-webkit-fit-content;width:fit-content;min-width:%?60?%;font-size:%?18?%;line-height:%?18?%;padding:%?6?% %?10?%;left:50%;-webkit-transform:translate(-50%);transform:translate(-50%)}.detail_swipe .number.left[data-v-cd8ca85c],\n.detail_swipe .content-box .number.left[data-v-cd8ca85c]{left:%?48?%;-webkit-transform:translate(0);transform:translate(0)}.detail_swipe .number.right[data-v-cd8ca85c],\n.detail_swipe .content-box .number.right[data-v-cd8ca85c]{left:auto;right:%?48?%;-webkit-transform:translate(0);transform:translate(0)}',""]),t.exports=e},"7d0f":function(t,e,i){var a=i("875a");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("4b919fe0",a,!0,{sourceMap:!1,shadowMode:!1})},"875a":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */.isbottom[data-v-33db50fe], .istop[data-v-33db50fe]{z-index:999990}._i[data-v-33db50fe]{display:inline}uni-view[data-v-33db50fe]{box-sizing:border-box}.def-pad[data-v-33db50fe]{padding:%?8?% %?16?%}*[data-v-33db50fe]{box-sizing:border-box;margin:0;padding:0;border:none}li[data-v-33db50fe]{list-style:none}ul[data-v-33db50fe]{padding:0}uni-image[data-v-33db50fe]{height:auto}',""]),t.exports=e},"8c10":function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.componentData&&"detail_swipe"==t.componentData.id?i("div",{staticClass:"detail_swipe decorate"},[i("MySwiper",{ref:"myswiper",attrs:{list:t.getList,refreshKey:t.key},on:{refresh:function(e){arguments[0]=e=t.$handleEvent(e),t.refresh.apply(void 0,arguments)},change:function(e){arguments[0]=e=t.$handleEvent(e),t.change.apply(void 0,arguments)},click:function(e){arguments[0]=e=t.$handleEvent(e),t.click.apply(void 0,arguments)}}}),t.componentData.style&&"number"!=t.componentData.style.dotstyle&&t.getList.length>1?i("ul",{staticClass:"dots",class:[t.componentData.style.dotstyle],style:{justifyContent:t.getAlign,margin:t.px2rpx(t.componentData.style.bottom)+" "+t.px2rpx(t.componentData.style.leftright),opacity:t.componentData.style.opacity}},t._l(t.getList,(function(e,a){return i("li",{key:a,staticClass:"dot",class:{active:t.activeIndex==a,video:t.getList[a].video},style:{background:t.getBg(a)}},[t.getList[a].video&&"round"==t.componentData.style.dotstyle?i("i",{staticClass:"dot-img icon-m-shipin iconfont-m-",style:{color:t.getIconClass(a)}}):t._e()])})),0):t._e(),t.componentData.style&&"number"==t.componentData.style.dotstyle?i("p",{staticClass:"number",class:[t.componentData.style.dotalign],style:{background:t.componentData.style.background}},[t._v(t._s(t.activeIndex+1+"/"+t.getList.length))]):t._e()],1):t._e()},o=[]},"8e90":function(t,e,i){"use strict";var a=i("44f9"),n=i.n(a);n.a},babe:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"detail_swipe swiper",style:"height:"+t.height+";"},[i("v-uni-swiper",{key:t.key,attrs:{current:t.value,touchable:!0,autoplay:!1===!t.setting.autoplay,circular:!1===!t.setting.loop,interval:t.setting.autoplaySpeed,duration:t.setting.duration||500},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.change.apply(void 0,arguments)}}},t._l(t.getList,(function(e,a){return i("v-uni-swiper-item",{key:a,style:"height:"+t.height},[i("div",{staticClass:"content-box"},[e.video?i("div",{staticClass:"detail_video",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),e.clickBtn.apply(void 0,arguments)}}},[i("my-video",{key:e.video,ref:"myvideo",refInFor:!0,staticClass:"myvideo",attrs:{src:t.$utils.mediaUrl(e.video,"","video")},on:{pause:function(i){arguments[0]=i=t.$handleEvent(i),e.pause.apply(void 0,arguments)},stop:function(i){arguments[0]=i=t.$handleEvent(i),e.stop.apply(void 0,arguments)}}}),0==t.playing&&e.video_thumb?i("div",{staticClass:"modal"},[i("p",{staticClass:"poster",style:{backgroundImage:"url("+t.$utils.mediaUrl(e.video_thumb)+")"}}),i("i",{staticClass:"iconfont-m- icon-m-shipinplay"})]):t._e()],1):i("div",{staticClass:"img-box"},[i("img",{attrs:{src:t.$utils.mediaUrl(e.img),alt:""},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.click(a)}}})])])])})),1)],1)},o=[]},ca89:function(t,e,i){"use strict";i.r(e);var a=i("0998"),n=i.n(a);for(var o in a)["default"].indexOf(o)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},e587:function(t,e,i){"use strict";var a=i("7d0f"),n=i.n(a);n.a},f107:function(t,e,i){"use strict";var a=i("fdec"),n=i.n(a);n.a},fdec:function(t,e,i){var a=i("0f55");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("adc2635e",a,!0,{sourceMap:!1,shadowMode:!1})}}]);