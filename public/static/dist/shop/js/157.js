(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[157],{"2cf2d":function(n,i,t){"use strict";t.d(i,"a",(function(){return a})),t.d(i,"b",(function(){return e}));var a=function(){var n=this,i=n.$createElement,t=n._self._c||i;return t("div",{staticClass:"nav-bar"},[t("div",{staticClass:"left"},[t("div",{staticClass:"modal-name",on:{click:n.goBack}},[t("Icon",{attrs:{type:"md-arrow-back"}}),n._v("返回 ")],1),t("div",{staticClass:"tabs"},n._l(n.getMenuTab,(function(i){return t("div",{key:i.id,staticClass:"tab",class:{on:n.checkId==i.id},on:{click:function(t){return n.clickTab(i.id)}}},[t("i",{staticClass:"iconfont tab-icon",class:i.icon}),t("span",{staticClass:"tab-name"},[n._v(n._s(i.name))])])})),0)]),1001!=n.checkId?t("div",{staticClass:"right"},[t("div",{staticClass:"btn-group"},n._l(n.getBtns,(function(i,a){return t("div",{key:a,class:"primary"==i.btntype?"btn-apply":"btn",on:{click:function(t){return n.clickBtn(i.id,i.action)}}},["pageSet"==i.id?t("i",{staticClass:"iconfont icon-zujian-yemianshezhi apply-icon pageSet-icon"}):n._e(),t("div",[n._v(n._s(i.name))]),"primary"==i.btntype?t("i",{staticClass:"iconfont icon-send apply-icon"}):n._e()])})),0)]):n._e()])},e=[]},"58a3":function(n,i,t){"use strict";t("656d")},"656d":function(n,i,t){},a39a:function(n,i,t){"use strict";t.r(i);var a=t("df9f"),e=t.n(a);for(var c in a)["default"].indexOf(c)<0&&function(n){t.d(i,n,(function(){return a[n]}))}(c);i["default"]=e.a},df9f:function(n,i){Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var t={computed:{getBtns:function(){var n={index:10,"goods-detail":11,"vip-center":12,distribution:20};if(null!=n[this.$route.params.page]){var i=[{id:"pageSet",name:"页面设置"},{id:"asTpl",name:"另存为模板"},{id:"preview",name:"保存并预览",btntype:"default"}];return i.push({id:"asPage",name:"发布",btntype:"primary",action:"publish"}),i}return"diymenu"==this.$route.params.page?[{id:"asPage",name:"保存菜单"},{id:"asPage",name:"保存并应用",btntype:"primary",action:"publish"}]:[{id:"asPage",name:"保存并设置",btntype:"primary"}]},getMenuTab:function(){var n={index:{id:10,name:"首页装修",icon:"icon-zujian-zhuangxiu-2"},"goods-detail":{id:11,name:"商品详情装修",icon:"icon-zujian-zhuangxiu-2"},"vip-center":{id:12,name:"会员中心装修",icon:"icon-zujian-zhuangxiu-2"},custom:{id:0,name:"自定义页面装修",icon:"icon-zujian-zhuangxiu-2"},distribution:{id:20,name:"分销中心装修",icon:"icon-zujian-zhuangxiu-2"}},i=this.$route.params.page;if(n[i]){var t=[];return t.push(n[i]),t}return"diymenu"==this.$route.params.page?[{id:1002,name:"底部菜单",icon:"icon-zujian-caidan-2"}]:[{id:4,name:"自定义页面装修"}]}},props:{loading:{type:Boolean,default:!1},backing:{type:Boolean,default:!1}},data:function(){return{checkId:null}},mounted:function(){this.checkId=this.getMenuTab[0].id},methods:{goBack:function(){this.backing||this.$router.go(-1)},clickBtn:function(n,i){this.loading||this.$emit("click",n,i)},clickTab:function(n){this.checkId!==n&&(this.checkId=n,this.$emit("clickTab",n))}}};i.default=t},e0ca:function(n,i,t){"use strict";t.r(i);var a=t("2cf2d"),e=t("a39a");for(var c in e)["default"].indexOf(c)<0&&function(n){t.d(i,n,(function(){return e[n]}))}(c);t("58a3");var s=t("2877"),u=Object(s["a"])(e["default"],a["a"],a["b"],!1,null,"8f93d682",null);i["default"]=u.exports}}]);