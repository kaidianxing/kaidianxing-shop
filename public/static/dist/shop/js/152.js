(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[152],{"260b":function(t,e,a){var n=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=n(a("c7eb")),r=n(a("1da1"));a("d3b7");var u={components:{},props:{},data:function(){return{submitLoading:!1,qiNiuData:{},rule:{wx:[{required:!0,message:"请填写七牛原始域名",trigger:"blur"}],key:[{required:!0,message:"请填写七牛新域名",trigger:"blur"}]}}},computed:{},created:function(){},mounted:function(){},methods:{validateForm:function(){var t=this;return new Promise((function(e,a){t.$refs["form"].validate((function(t){t?e(t):a()}))}))},handleSave:function(){this.submitLoading=!0,this.customForm(this.qiNiuData)},customForm:function(t){var e=this;return(0,r.default)((0,i.default)().mark((function a(){return(0,i.default)().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return a.prev=0,a.next=3,e.validateForm();case 3:e.submit(t),a.next=9;break;case 6:a.prev=6,a.t0=a["catch"](0),e.submitLoading=!1;case 9:case"end":return a.stop()}}),a,null,[[0,6]])})))()},submit:function(t){console.log(t),this.submitLoading=!1}}};e.default=u},"67aa":function(t,e,a){"use strict";a.r(e);var n=a("8b04"),i=a("9dc5");for(var r in i)["default"].indexOf(r)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(r);a("a5d7");var u=a("2877"),o=Object(u["a"])(i["default"],n["a"],n["b"],!1,null,null,null);e["default"]=o.exports},"8b04":function(t,e,a){"use strict";a.d(e,"a",(function(){return n})),a.d(e,"b",(function(){return i}));var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("kdx-content-bar",{scopedSlots:t._u([{key:"btn",fn:function(){return[a("Button",{attrs:{type:"primary",loading:t.submitLoading},on:{click:t.handleSave}},[t._v("保存")])]},proxy:!0}])},[a("div",{staticClass:"qiNiu"},[a("kdx-form-title",[t._v("七牛修复")]),a("Form",{ref:"form",attrs:{"label-width":150,model:t.qiNiuData,rules:t.rule}},[a("Alert",[a("div",{staticClass:"tip"},[a("p",[a("span",{staticClass:"danger-color"},[t._v("【重要】")]),t._v(" 此工具用于修复七牛收回临时域名后导致图片无法显示的问题")]),a("p",[a("span",{staticClass:"danger-color"},[t._v("【重要】")]),t._v("请您填写原始域名和新域名的时候都要填写完带有带有http或https协议头的域名")]),a("p",[t._v("例如:")]),a("p",[t._v("您曾用的七牛临时域名为http://omn8drpan.bkt.clouddn.com,要转为新域名https://www.storage.com")]),a("p",[t._v("原始域名请填写 http://omn8drpan.bkt.clouddn.com")]),a("p",[t._v("新域名请填写 https://www.storage.com")])])]),a("FormItem",{attrs:{label:"七牛原始域名：",prop:"wx"}},[a("Input",{staticClass:"width-250",model:{value:t.qiNiuData.wx,callback:function(e){t.$set(t.qiNiuData,"wx",e)},expression:"qiNiuData.wx"}})],1),a("FormItem",{attrs:{label:"七牛新域名：",prop:"key"}},[a("Input",{staticClass:"width-250",model:{value:t.qiNiuData.key,callback:function(e){t.$set(t.qiNiuData,"key",e)},expression:"qiNiuData.key"}})],1)],1)],1)])},i=[]},"9dc5":function(t,e,a){"use strict";a.r(e);var n=a("260b"),i=a.n(n);for(var r in n)["default"].indexOf(r)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(r);e["default"]=i.a},a5d7:function(t,e,a){"use strict";a("f28c")},f28c:function(t,e,a){}}]);