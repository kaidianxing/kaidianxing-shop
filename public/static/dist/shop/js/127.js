(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[127],{3495:function(i,t,s){},"8c63":function(i,t,s){"use strict";s("3495")},af46:function(i,t,s){var n=s("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var e=n(s("5530")),o={props:[],data:function(){return{commission:{}}},computed:{noPerm:function(){return{commissionAgent:!this.$getPermMap("commission.agent.view"),commissionWaitAgent:!this.$getPermMap("commission.wait_agent.view"),commissionApply:!this.$getPermMap("commission.apply.view")}}},created:function(){this.getCommission()},methods:{getCommission:function(){var i=this;this.$api.homeApi.getCommission({}).then((function(t){0===t.error&&(i.commission=(0,e.default)({},t.data))}))},jumpCommission:function(i,t){if(t)this.$Message.info("暂无查看权限");else{var s=["/commission/distributor","/commission/to-audit","/commission/withdraw/list/waiting","/commission/withdraw/list/paying"];this.$utils.openNewWindowPage(s[i])}}}};t.default=o},d15c:function(i,t,s){"use strict";s.r(t);var n=s("f432"),e=s("e7ead");for(var o in e)["default"].indexOf(o)<0&&function(i){s.d(t,i,(function(){return e[i]}))}(o);s("8c63");var a=s("2877"),c=Object(a["a"])(e["default"],n["a"],n["b"],!1,null,"7d149308",null);t["default"]=c.exports},e7ead:function(i,t,s){"use strict";s.r(t);var n=s("af46"),e=s.n(n);for(var o in n)["default"].indexOf(o)<0&&function(i){s.d(t,i,(function(){return n[i]}))}(o);t["default"]=e.a},f432:function(i,t,s){"use strict";s.d(t,"a",(function(){return n})),s.d(t,"b",(function(){return e}));var n=function(){var i=this,t=i.$createElement,s=i._self._c||t;return s("div",{staticClass:"center-comp"},[s("ul",[s("li",{on:{click:function(t){return i.jumpCommission("0",i.noPerm.commissionAgent)}}},[s("div",{staticClass:"box"},[i._m(0),s("div",{staticClass:"num"},[i._v(" "+i._s(i.commission.agent_count)+" ")])])]),s("li",{on:{click:function(t){return i.jumpCommission("2",i.noPerm.commissionApply)}}},[s("div",{staticClass:"box"},[i._m(1),s("div",{staticClass:"num"},[i._v(" "+i._s(i.commission.pre_check)+" ")])])]),s("li",{on:{click:function(t){return i.jumpCommission("3",i.noPerm.commissionApply)}}},[s("div",{staticClass:"box"},[i._m(2),s("div",{staticClass:"num"},[i._v(" "+i._s(i.commission.check_agree)+" ")])])])])])},e=[function(){var i=this,t=i.$createElement,s=i._self._c||t;return s("div",{staticClass:"tit"},[s("div",{staticClass:"text"},[i._v("分销商总数(人)")]),s("div",{staticClass:"line"})])},function(){var i=this,t=i.$createElement,s=i._self._c||t;return s("div",{staticClass:"tit"},[s("div",{staticClass:"text"},[i._v("待审核佣金(元)")]),s("div",{staticClass:"line"})])},function(){var i=this,t=i.$createElement,s=i._self._c||t;return s("div",{staticClass:"tit"},[s("div",{staticClass:"text"},[i._v("待打款佣金(元)")]),s("div",{staticClass:"line"})])}]}}]);