(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[73],{"169d":function(t,e,a){"use strict";a("9208")},"1c62":function(t,e,a){"use strict";a.r(e);var i=a("3fb2"),n=a.n(i);for(var s in i)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(s);e["default"]=n.a},"2de4":function(t,e,a){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("a9e3");var i={name:"PageTemplateList",props:{data:{type:Array,default:function(){return[]}},type:{type:String,required:!0},loading:{type:Boolean,default:!1},pageNow:{type:Number,default:1},pageCount:{type:Number,default:1}},data:function(){return{}},computed:{isShowMore:function(){return"custom"!==this.type&&(this.pageNow===this.pageCount||0===this.pageCount)}},methods:{addModule:function(){var t="";"index"===this.type?t="/decorate/index":"detail"===this.type?t="/decorate/goods-detail":"vip"===this.type?t="/decorate/vip-center":"commission"===this.type&&(t="/decorate/commission"),this.$router.push({path:t})},handleUsing:function(t){this.$emit("on-using",t)}}};e.default=i},"3fb2":function(t,e){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"SearchHeader",data:function(){return{model:{keywords:""}}},methods:{handleSearch:function(){this.$emit("on-change",this.model)},handleReset:function(){this.$emit("on-change",{}),this.model.keywords=""}}};e.default=a},4810:function(t,e,a){"use strict";a.r(e);var i=a("ec1e"),n=a("1c62");for(var s in n)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(s);var r=a("2877"),o=Object(r["a"])(n["default"],i["a"],i["b"],!1,null,null,null);e["default"]=o.exports},"4b0b":function(t,e,a){"use strict";a.r(e);var i=a("2de4"),n=a.n(i);for(var s in i)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(s);e["default"]=n.a},"79da":function(t,e,a){"use strict";a.r(e);var i=a("e9050"),n=a("d0be");for(var s in n)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(s);a("e3d8");var r=a("2877"),o=Object(r["a"])(n["default"],i["a"],i["b"],!1,null,"36f84178",null);e["default"]=o.exports},8755:function(t,e,a){var i=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("ac1f"),a("841c");var n=i(a("4810")),s=i(a("93aa")),r={name:"index",components:{SearchHeader:n.default,PageTemplateList:s.default},data:function(){return{total:0,page:{pageSize:10,pageNumber:1},type:"",data:[],status:"1",loading:!1,pageCount:1}},computed:{moduleTitle:function(){var t;switch(this.type){case"index":t="首页模板";break;case"detail":t="商品详情模板";break;case"vip":t="会员中心模板";break;case"commission":t="分销模板";break}return t}},watch:{"$route.path":{handler:function(){this.type=this.$route.params.type;var t={index:"1",detail:"2",vip:"3",commission:"4",custom:"5"};this.status=t[this.type],"custom"===this.type?this.getPageList():this.getTemplateList()},immediate:!0}},methods:{handleSearch:function(t){this.search=t,this.refreshTable()},changePage:function(t){this.page=t,this.loadDataType()},refreshTable:function(){var t;this.page={pageSize:10,pageNumber:1},null===(t=this.$refs["page"])||void 0===t||t.reset(),this.loadDataType()},loadDataType:function(){"custom"===this.type?this.getPageList():this.getTemplateList()},getTemplateList:function(){var t=this;this.loading=!0;var e=Object.assign({pagesize:this.page.pageSize,page:this.page.pageNumber,type:this.status},this.search);this.$api.shopApi.getTemplateList(e).then((function(e){t.loading=!1,0===e.error&&(t.data=e.list,t.total=e.total,t.pageCount=e.pageCount)}))},getPageList:function(){var t=this;this.loading=!0;var e=Object.assign({pageSize:this.page.pageSize,pageCount:this.page.pageNumber,type:"5"},this.search);this.$api.shopApi.getPageList(e).then((function(e){t.loading=!1,0===e.error&&(t.data=e.list||[],t.total=e.total,t.pageCount=e.pageCount)}))},handleUsing:function(t){var e;e="index"==this.type?"/decorate/index":"detail"==this.type?"/decorate/goods-detail":"vip"==this.type?"/decorate/vip-center":"custom"===this.type?"/decorate/custom":"/decorate/distribution",this.$router.push({path:e,query:{id:t.id,type:"add"}})}}};e.default=r},9208:function(t,e,a){},"93aa":function(t,e,a){"use strict";a.r(e);var i=a("f8e58"),n=a("4b0b");for(var s in n)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(s);a("169d");var r=a("2877"),o=Object(r["a"])(n["default"],i["a"],i["b"],!1,null,"3d206309",null);e["default"]=o.exports},cf650:function(t,e){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKwAAACsAQMAAADc/9WbAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAJcEhZcwAAFiUAABYlAUlSJPAAAAAGUExURUdwTJOXmdestcUAAAABdFJOUwBA5thmAAAAMElEQVRIx+3QoREAIAwAsSrWYvVuBgbbu6piEvnyIwA+W7Is13mfJzvZQVluZIBBF0qqE5N6crahAAAAAElFTkSuQmCC"},d0be:function(t,e,a){"use strict";a.r(e);var i=a("8755"),n=a.n(i);for(var s in i)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(s);e["default"]=n.a},e3d8:function(t,e,a){"use strict";a("e74f")},e74f:function(t,e,a){},e9050:function(t,e,a){"use strict";a.d(e,"a",(function(){return i})),a.d(e,"b",(function(){return n}));var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("kdx-content-bar",[a("div",{staticClass:"shop-home-template"},[a("search-header",{on:{"on-change":t.handleSearch}}),a("div",{staticClass:"shop-home-template-content"},[a("div",{staticClass:"shop-home-template-box"},[a("kdx-form-title",{directives:[{name:"show",rawName:"v-show",value:"custom"!==t.type,expression:"type !== 'custom'"}]},[t._v(t._s(t.moduleTitle))]),a("page-template-list",{attrs:{data:t.data,type:t.type,loading:t.loading,"page-now":t.page.pageNumber,"page-count":t.pageCount},on:{"on-using":t.handleUsing}})],1),a("div",{directives:[{name:"show",rawName:"v-show",value:t.total>10,expression:"total > 10"}],staticClass:"footer-page"},[a("kdx-page-component",{ref:"page",attrs:{total:t.total},on:{"on-change":t.changePage}})],1)])],1)])},n=[]},ec1e:function(t,e,a){"use strict";a.d(e,"a",(function(){return i})),a.d(e,"b",(function(){return n}));var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("kdx-header-bar",[a("Form",{ref:"form",attrs:{model:t.model,"label-width":100,inline:""}},[a("FormItem",{attrs:{label:"模板名称："}},[a("Input",{staticClass:"width-340",attrs:{type:"text",placeholder:"输入模板名称"},on:{"on-enter":t.handleSearch},model:{value:t.model.keywords,callback:function(e){t.$set(t.model,"keywords",e)},expression:"model.keywords"}})],1),a("div",{staticClass:"ivu-form-item-btn"},[a("Button",{attrs:{type:"primary"},on:{click:t.handleSearch}},[t._v("搜索")]),a("Button",{attrs:{type:"text"},on:{click:t.handleReset}},[t._v("重置")])],1)],1)],1)},n=[]},f8e58:function(t,e,a){"use strict";a.d(e,"a",(function(){return i})),a.d(e,"b",(function(){return n}));var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticClass:"shop-page-template-list"},[a("ul",[a("li",{on:{click:t.addModule}},[t._m(0),a("div",{staticClass:"footer"},[t._m(1),a("Button",{staticClass:"primary-border"},[t._v("立即创建")])],1)]),t._l(t.data,(function(e){return a("li",{key:e.id,on:{click:function(a){return t.handleUsing(e)}}},[a("div",{staticClass:"page-image"},[a("img",{attrs:{src:t.$media(e.thumb),alt:""},on:{error:function(e){return t.replaceImage(e,"template")}}})]),a("div",{staticClass:"footer"},[a("div",{staticClass:"title"},[a("span",{staticClass:"default"},[t._v("默认")]),a("span",{staticClass:"page-title"},[t._v(t._s(e.name))])]),"custom"===t.type?["1"==e.is_used?a("Button",{staticClass:"primary-border"},[t._v("应用中")]):a("Button",{staticClass:"primary-border"},[t._v("立即使用")])]:[a("Button",{staticClass:"primary-border"},[t._v("立即使用")])]],2)])})),t.isShowMore?a("li",{staticClass:"more"},[t._m(2)]):t._e()],2)])},n=[function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"page-add-icon"},[i("img",{attrs:{src:a("cf650"),alt:""}})])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"title"},[a("span",{staticClass:"page-title"},[t._v("空白页")])])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"text"},[a("p",[t._v("更多模板")]),a("p",[t._v("敬请期待")])])}]}}]);