(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[134,152,172,173,174,175,176],{4283:function(t,e,a){"use strict";var i=a("fdca"),o=a.n(i);o.a},"49cb":function(t,e,a){"use strict";a.r(e);var i=a("eccf"),o=a.n(i);for(var n in i)["default"].indexOf(n)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(n);e["default"]=o.a},a395:function(t,e,a){var i=a("24fb");e=i(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 开店星新零售管理系统\n * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开\n * @author 青岛开店星信息技术有限公司\n * @link https://www.kaidianxing.com\n * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.\n * @copyright 版权归青岛开店星信息技术有限公司所有\n * @warning Unauthorized deletion of copyright information is prohibited.\n * @warning 未经许可禁止私自删除版权信息\n */\n/* 颜色变量 */\n/* 行为相关颜色 */\n/* 文字基本颜色 */\n/* 背景颜色 */\n/* 边框颜色 */\n/* 尺寸变量 */\n/* 文字尺寸 */\n/* 图片尺寸 */\n/* Border Radius */\n/* 水平间距 */\n/* 垂直间距 */\n/* 透明度 */\n/* 文章场景相关 */.cube .cube-template-img[data-v-335c4195]{display:block;width:100%;height:100%;object-fit:cover}.cube .container[data-v-335c4195]{overflow:hidden}.cube .normal-box[data-v-335c4195]{border-radius:%?12?%;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:flex}.cube .fit-img[data-v-335c4195]{-webkit-box-flex:1;-webkit-flex-grow:1;flex-grow:1;-webkit-flex-shrink:0;flex-shrink:0;object-fit:cover}.cube-img[data-v-335c4195]{-webkit-box-flex:1;-webkit-flex:1;flex:1;display:block;object-fit:cover}.cube .custom-box[data-v-335c4195]{position:relative}.cube .custom-box .custom-img-box[data-v-335c4195]{position:absolute}.cube .custom-box .custom-img[data-v-335c4195]{border-radius:%?12?%;object-fit:cover;height:100%;width:100%}.cube .rect-box[data-v-335c4195]{display:-webkit-box;display:-webkit-flex;display:flex}.cube .rect-box .rect-img-box[data-v-335c4195]{-webkit-box-flex:1;-webkit-flex:1;flex:1;border-radius:%?12?%;overflow:hidden}.cube .rect-box .rect-img-box .cube-img[data-v-335c4195]{width:100%;height:100%;-webkit-box-flex:1;-webkit-flex:1;flex:1;display:block;object-fit:cover}.cube .rect-box .second_box[data-v-335c4195]{-webkit-box-flex:1;-webkit-flex:1;flex:1;display:-webkit-box;display:-webkit-flex;display:flex;overflow:hidden}.cube .rect-box.top1_bottom2[data-v-335c4195]{-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.cube .rect-box.left1_right2 .second_box[data-v-335c4195], .cube .rect-box.left1_right3 .second_box[data-v-335c4195]{-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.cube .rect-box .third-box[data-v-335c4195]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-flex:1;-webkit-flex:1;flex:1;overflow:hidden}.hot_area .container[data-v-335c4195]{position:relative}.hot_area .hot_item[data-v-335c4195]{position:absolute;left:0;right:0;background-color:initial}.hot_area .hot_item[data-v-335c4195]:after{border:0}.hot_area_img[data-v-335c4195]{display:block;width:100%;border-radius:%?12?%}',""]),t.exports=e},afef:function(t,e,a){"use strict";var i;a.d(e,"b",(function(){return o})),a.d(e,"c",(function(){return n})),a.d(e,"a",(function(){return i}));var o=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[t.componentData&&"cube"==t.componentData.id?a("div",{staticClass:"cube",style:{padding:t.px2rpx(t.componentData.style.margintop)+" "+t.px2rpx(t.componentData.style.marginleft)+" "+t.px2rpx(t.componentData.style.marginbottom)}},[0==t.getImgList.length?a("div",{staticClass:"cube-template",style:t.getStyle},[t.startLoadImg?a("img",{staticClass:"cube-template-img",attrs:{src:t.$utils.staticMediaUrl("decorate/cube_default.png")}}):t._e()]):a("div",{staticClass:"container"},["normal"==t.getCubeTemp?a("div",{staticClass:"normal-box",style:{margin:"0 "+t.px2rpx(0-t.componentData.style.margininside/2),borderRadius:t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.bottomradius)+" "+t.px2rpx(t.componentData.style.bottomradius),height:t.px2rpx(t.getNormalHeight)}},t._l(t.componentData.data,(function(e,i){return a("img",{key:i,staticClass:"fit-img",style:{borderRadius:t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.bottomradius)+" "+t.px2rpx(t.componentData.style.bottomradius),margin:"0 "+t.px2rpx(t.componentData.style.margininside/2),width:t.px2rpx(t.getNormalWidth)},attrs:{src:t.startLoadImg?t.$utils.mediaUrl(e.imgurl):"",mode:"widthFix"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem(i)}}})})),0):"rect"==t.getCubeTemp?a("div",{staticClass:"rect-box",class:[t.componentData.params.cubestyle],style:{margin:""+t.px2rpx(0-t.componentData.style.margininside/2),height:t.getRectHeight}},[a("div",{staticClass:"rect-img-box",style:{borderRadius:t.getRectStyle.borderRadius,margin:t.getRectStyle.margin},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem(0)}}},[t.getImgUrl(0)&&t.startLoadImg?a("img",{staticClass:"cube-img",attrs:{src:t.getImgUrl(0)}}):a("div",{staticClass:"cube-img"})]),a("div",{staticClass:"second_box"},[a("div",{staticClass:"rect-img-box",style:{borderRadius:t.getRectStyle.borderRadius,margin:t.getRectStyle.margin},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem(1)}}},[t.getImgUrl(1)&&t.startLoadImg?a("img",{staticClass:"cube-img",attrs:{src:t.getImgUrl(1)}}):a("div",{staticClass:"cube-img"})]),"left1_right3"!=t.componentData.params.cubestyle&&t.startLoadImg?a("div",{staticClass:"rect-img-box",style:{borderRadius:t.getRectStyle.borderRadius,margin:t.getRectStyle.margin},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem(2)}}},[t.getImgUrl(2)&&t.startLoadImg?a("img",{staticClass:"cube-img",attrs:{src:t.getImgUrl(2)}}):a("div",{staticClass:"cube-img"})]):a("div",{staticClass:"third-box"},[a("div",{staticClass:"rect-img-box",style:{borderRadius:t.getRectStyle.borderRadius,margin:t.getRectStyle.margin},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem(2)}}},[t.getImgUrl(2)&&t.startLoadImg?a("img",{staticClass:"cube-img",attrs:{src:t.getImgUrl(2)}}):a("div",{staticClass:"cube-img"})]),a("div",{staticClass:"rect-img-box",style:{borderRadius:t.getRectStyle.borderRadius,margin:t.getRectStyle.margin},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem(3)}}},[t.getImgUrl(3)&&t.startLoadImg?a("img",{staticClass:"cube-img",attrs:{src:t.getImgUrl(3)}}):a("div",{staticClass:"cube-img"})])])])]):"custom"==t.getCubeTemp?a("div",{staticClass:"custom-box",style:{margin:""+t.px2rpx(0-t.componentData.style.margininside/2),height:t.getRectHeight}},t._l(t.getImgList,(function(e,i){return a("div",{key:i,staticClass:"custom-img-box",style:{padding:""+t.px2rpx(t.componentData.style.margininside/2),left:t.getCustomStyle(e).left,top:t.getCustomStyle(e).top,width:t.getCustomStyle(e).width,height:t.getCustomStyle(e).height}},[t.startLoadImg?a("img",{staticClass:"custom-img",style:{borderRadius:t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.bottomradius)+" "+t.px2rpx(t.componentData.style.bottomradius)},attrs:{src:t.$utils.mediaUrl(e.imgurl),mode:"aspectFill"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.clickItem(i)}}}):t._e()])})),0):t._e()])]):t._e(),t.componentData&&"hot_area"==t.componentData.id?a("div",{staticClass:"hot_area",style:{padding:t.px2rpx(t.componentData.style.margintop)+" "+t.px2rpx(t.componentData.style.marginleft)+" "+t.px2rpx(t.componentData.style.marginbottom)}},[a("div",{staticClass:"container"},[a("img",{staticClass:"hot_area_img",style:{borderRadius:t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.topradius)+" "+t.px2rpx(t.componentData.style.bottomradius)+" "+t.px2rpx(t.componentData.style.bottomradius)},attrs:{src:t.$utils.mediaUrl(t.componentData.params.imgurl)||t.$utils.staticMediaUrl("decorate/goods_col1.png"),mode:"widthFix"}}),t._l(t.componentData.data,(function(e,i){return a("v-uni-button",{key:i,staticClass:"hot_item",style:{left:t.getPoint(e).left,top:t.getPoint(e).top,width:t.getPoint(e).width,height:t.getPoint(e).height,zIndex:t.getPoint(e).zIndex},attrs:{"open-type":"wx_service"==e.linkurl?"contact":""},on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.clickHotItem(e)}}})}))],2)]):t._e()])},n=[]},c984:function(t,e,a){var i=a("288e");a("8e6e"),a("ac6a"),a("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var o=i(a("bd86")),n=a("2f62"),r=a("dc11");function c(t,e){var a=Object.keys(t);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(t);e&&(i=i.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),a.push.apply(a,i)}return a}function s(t){for(var e=1;e<arguments.length;e++){var a=null!=arguments[e]?arguments[e]:{};e%2?c(Object(a),!0).forEach((function(e){(0,o.default)(t,e,a[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(a)):c(Object(a)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(a,e))}))}return t}var l={computed:s({},(0,n.mapState)("decorate",{pageList:function(t){return t.pageList}})),props:{startLoadImg:{type:Boolean,default:!0},componentData:{type:Object,default:function(){return{style:{},params:{}}}}},methods:{px2rpx:r.px2rpx}};e.default=l},e7f9:function(t,e,a){"use strict";a.r(e);var i=a("afef"),o=a("49cb");for(var n in o)["default"].indexOf(n)<0&&function(t){a.d(e,t,(function(){return o[t]}))}(n);a("4283");var r,c=a("f0c5"),s=Object(c["a"])(o["default"],i["b"],i["c"],!1,null,"335c4195",null,!1,i["a"],r);e["default"]=s.exports},eccf:function(t,e,a){var i=a("288e");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("28a5");var o=i(a("768b")),n=i(a("c984")),r={cell_two:2,cell_three:3},c={cell_two:"normal",cell_three:"normal",top1_bottom2:"rect",left1_right3:"rect",left1_right2:"rect",custom:"custom"},s={mixins:[n.default],computed:{getNormalWidth:function(){var t=r[this.componentData.params.cubestyle],e=this.componentData.style,a=e.margininside,i=e.marginleft;return(750-2*i-a*(t-1))/t},getNormalHeight:function(){var t=this.componentData.params,e=t.imgheight,a=t.imgwidth;return e*this.getNormalWidth/a},getRectHeight:function(){var t=this.componentData.style.marginleft;return this.px2rpx(750-2*t)},getCubeTemp:function(){return c[this.componentData.params.cubestyle]},getImgList:function(){return this.componentData.data.filter((function(t){return t.imgurl}))},getStyle:function(){var t=["cell_two","cell_three"],e=this.componentData.style.marginleft,a=750-2*e,i=t.indexOf(this.componentData.params.cubestyle)>-1?2:1;return{height:this.px2rpx(a/i),width:this.px2rpx(a)}},getRectStyle:function(){return{borderRadius:"".concat(this.px2rpx(this.componentData.style.topradius)," ").concat(this.px2rpx(this.componentData.style.topradius)," ").concat(this.px2rpx(this.componentData.style.bottomradius)," ").concat(this.px2rpx(this.componentData.style.bottomradius)),margin:"".concat(this.px2rpx(this.componentData.style.margininside/2))}}},methods:{clickItem:function(t){this.$emit("custom-event",{target:"cube/clickItem",data:this.componentData.data[t]})},getCustomStyle:function(t){if(t&&t.startP){var e=t.startP.split(","),a=(0,o.default)(e,2),i=a[0],n=a[1],r=t.endP.split(","),c=(0,o.default)(r,2),s=c[0],l=c[1],d=100/this.componentData.params.cubenum;return{left:(i-1)*d+"%",top:(n-1)*d+"%",width:(s-i+1)*d+"%",height:(l-n+1)*d+"%"}}},getImgUrl:function(t){var e;return this.componentData.data[t]?this.$utils.mediaUrl(null===(e=this.componentData.data[t])||void 0===e?void 0:e.imgurl):""},getPoint:function(t){return{left:t.x+"%",top:t.y+"%",width:t.w+"%",height:t.h+"%",zIndex:this.$isPC?-1:9999}},clickHotItem:function(t){this.$emit("custom-event",{target:"hot_area/clickItem",data:t})}}};e.default=s},fdca:function(t,e,a){var i=a("a395");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var o=a("4f06").default;o("3652f8d8",i,!0,{sourceMap:!1,shadowMode:!1})}}]);