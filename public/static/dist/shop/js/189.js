(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[189],{"260b":function(t,e,n){var a=n("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,n("96cf");var r=a(n("1da1"));n("d3b7");var i={components:{},props:{},data:function(){return{submitLoading:!1,qiNiuData:{},rule:{wx:[{required:!0,message:"请填写七牛原始域名",trigger:"blur"}],key:[{required:!0,message:"请填写七牛新域名",trigger:"blur"}]}}},computed:{},created:function(){},mounted:function(){},methods:{validateForm:function(){var t=this;return new Promise((function(e,n){t.$refs["form"].validate((function(t){t?e(t):n()}))}))},handleSave:function(){this.submitLoading=!0,this.customForm(this.qiNiuData)},customForm:function(t){var e=this;return(0,r.default)(regeneratorRuntime.mark((function n(){return regeneratorRuntime.wrap((function(n){while(1)switch(n.prev=n.next){case 0:return n.prev=0,n.next=3,e.validateForm();case 3:e.submit(t),n.next=9;break;case 6:n.prev=6,n.t0=n["catch"](0),e.submitLoading=!1;case 9:case"end":return n.stop()}}),n,null,[[0,6]])})))()},submit:function(t){console.log(t),this.submitLoading=!1}}};e.default=i},"67aa":function(t,e,n){"use strict";n.r(e);var a=n("8b04"),r=n("9dc5");for(var i in r)["default"].indexOf(i)<0&&function(t){n.d(e,t,(function(){return r[t]}))}(i);n("a5d7");var u=n("2877"),o=Object(u["a"])(r["default"],a["a"],a["b"],!1,null,null,null);e["default"]=o.exports},"8b04":function(t,e,n){"use strict";n.d(e,"a",(function(){return a})),n.d(e,"b",(function(){return r}));var a=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("kdx-content-bar",{scopedSlots:t._u([{key:"btn",fn:function(){return[n("Button",{attrs:{type:"primary",loading:t.submitLoading},on:{click:t.handleSave}},[t._v("保存")])]},proxy:!0}])},[n("div",{staticClass:"qiNiu"},[n("kdx-form-title",[t._v("七牛修复")]),n("Form",{ref:"form",attrs:{"label-width":150,model:t.qiNiuData,rules:t.rule}},[n("Alert",[n("div",{staticClass:"tip"},[n("p",[n("span",{staticClass:"danger-color"},[t._v("【重要】")]),t._v(" 此工具用于修复七牛收回临时域名后导致图片无法显示的问题")]),n("p",[n("span",{staticClass:"danger-color"},[t._v("【重要】")]),t._v("请您填写原始域名和新域名的时候都要填写完带有带有http或https协议头的域名")]),n("p",[t._v("例如:")]),n("p",[t._v("您曾用的七牛临时域名为http://omn8drpan.bkt.clouddn.com,要转为新域名https://www.storage.com")]),n("p",[t._v("原始域名请填写 http://omn8drpan.bkt.clouddn.com")]),n("p",[t._v("新域名请填写 https://www.storage.com")])])]),n("FormItem",{attrs:{label:"七牛原始域名：",prop:"wx"}},[n("Input",{staticClass:"width-250",model:{value:t.qiNiuData.wx,callback:function(e){t.$set(t.qiNiuData,"wx",e)},expression:"qiNiuData.wx"}})],1),n("FormItem",{attrs:{label:"七牛新域名：",prop:"key"}},[n("Input",{staticClass:"width-250",model:{value:t.qiNiuData.key,callback:function(e){t.$set(t.qiNiuData,"key",e)},expression:"qiNiuData.key"}})],1)],1)],1)])},r=[]},"9dc5":function(t,e,n){"use strict";n.r(e);var a=n("260b"),r=n.n(a);for(var i in a)["default"].indexOf(i)<0&&function(t){n.d(e,t,(function(){return a[t]}))}(i);e["default"]=r.a},a5d7:function(t,e,n){"use strict";n("f28c")},f28c:function(t,e,n){}}]);