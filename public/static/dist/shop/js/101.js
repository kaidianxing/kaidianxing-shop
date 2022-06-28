(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[101],{"0ea1":function(e,t,a){var n=a("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,a("ac1f"),a("841c"),a("d81d");var i=n(a("5530")),r=n(a("b597")),s=a("d1be"),o={name:"list",components:{searchHeader:r.default},data:function(){return{loading:!1,page:{pageSize:10,pageNumber:1},search:{description:"",title:"",create_time:[]},data:[],total:0,columns:[{title:"商品信息",slot:"goods"},{title:"推广文案",slot:"description"},{title:"添加时间",key:"create_time",maxWidth:400},{title:"操作",slot:"action",minWidth:10,maxWidth:120}]}},computed:{noManagePerm:function(){return!this.$getPermMap("material.index.manage")}},created:function(){this.getMaterialList()},methods:{getType:function(e){var t;switch(e.goods_type){case"0":t={goodsName:"mark real",goodsText:"实"};break;case"1":t={goodsName:"mark virtual",goodsText:"虚"};break;case"2":t={goodsName:"mark secret",goodsText:"密"};break;default:t={goodsName:"",goodsText:""};break}return t},getMaterialList:function(){var e=this;this.loading=!0;var t=(0,i.default)((0,i.default)({},this.search),{},{pagesize:this.page.pageSize,page:this.page.pageNumber});t.create_time=t.create_time.map((function(e){return(0,s.formatDate)(e,"yyyy-MM-dd hh:mm:ss")})),this.$api.materialApi.getMaterialList(t).then((function(t){e.loading=!1,0===t.error&&(e.data=t.data.list,e.total=t.data.total)}))},handleSearch:function(e){this.search=(0,i.default)((0,i.default)({},this.search),e),this.refreshTable()},changePage:function(e){this.page=e,this.getMaterialList()},handleDelete:function(e){var t=this;this.$Modal.confirm({title:"提示",content:"确定删除此素材？",onOk:function(){t.delete(e)},onCancel:function(){}})},delete:function(e){var t=this;this.$api.materialApi.deleteMaterial({id:e}).then((function(e){0===e.error&&(t.refreshTable(),t.$Message.success("删除成功"))}))},handleEdit:function(e){this.$router.push({path:"/material/edit",query:{id:e}})},refreshTable:function(){this.page={pageSize:10,pageNumber:1},this.$refs["page"].reset(),this.getMaterialList()}}};t.default=o},"4aba":function(e,t,a){"use strict";a.r(t);var n=a("0ea1"),i=a.n(n);for(var r in n)["default"].indexOf(r)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(r);t["default"]=i.a},"8a80":function(e,t,a){"use strict";a.r(t);var n=a("93d6"),i=a("4aba");for(var r in i)["default"].indexOf(r)<0&&function(e){a.d(t,e,(function(){return i[e]}))}(r);a("fb3b");var s=a("2877"),o=Object(s["a"])(i["default"],n["a"],n["b"],!1,null,"a48ef9da",null);t["default"]=o.exports},"93d6":function(e,t,a){"use strict";a.d(t,"a",(function(){return n})),a.d(t,"b",(function(){return i}));var n=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"activity-wrap"},[a("search-header",{ref:"search_header",on:{"on-search":e.handleSearch}}),a("div",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],staticClass:"table-list"},[a("Table",{ref:"table",attrs:{columns:e.columns,data:e.data},scopedSlots:e._u([{key:"goods",fn:function(t){var n=t.row;return[a("div",{staticClass:"content-box"},[a("div",{staticClass:"image"},[a("img",{attrs:{src:e.$media(n.goods_thumb),alt:""},on:{error:e.replaceImage}})]),a("div",{staticClass:"content"},[a("div",{staticClass:"content-text",staticStyle:{"{display":"-webkit-box}"}},[1==n.goods_has_option?a("span",{staticClass:"mark"},[e._v("多")]):e._e(),a("span",{class:e.getType(n).goodsName},[e._v(" "+e._s(e.getType(n).goodsText)+" ")]),a("span",{staticClass:"text"},[e._v(e._s(n.goods_title))])]),a("p",{staticClass:"label"},e._l(n.goods_category,(function(t,n){return a("span",{key:n},[e._v(e._s(t.name))])})),0)])])]}},{key:"description",fn:function(t){var n=t.row;return[a("div",{staticClass:"text"},[e._v(" "+e._s(n.description)+" ")])]}},{key:"action",fn:function(t){var n=t.row;return[a("div",{staticClass:"btn-box"},[a("Button",{attrs:{type:"text",disabled:e.noManagePerm},on:{click:function(t){return e.handleEdit(n.id)}}},[e._v("编辑")]),a("Button",{attrs:{type:"text",disabled:e.noManagePerm},on:{click:function(t){return e.handleDelete(n.id)}}},[e._v("删除")])],1)]}}])}),a("div",{directives:[{name:"show",rawName:"v-show",value:e.data.length>0,expression:"data.length > 0"}],staticClass:"footer-page"},[a("kdx-page-component",{ref:"page",attrs:{total:e.total},on:{"on-change":e.changePage}})],1)],1),e._t("default")],2)},i=[]},b094:function(e,t,a){"use strict";a.d(t,"a",(function(){return n})),a.d(t,"b",(function(){return i}));var n=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("kdx-header-bar",{scopedSlots:e._u([{key:"header",fn:function(){return[a("Button",{attrs:{disabled:e.noManagePerm,type:"primary"},on:{click:e.addCard}},[e._v("+添加素材")])]},proxy:!0}])},[a("Form",{ref:"form",attrs:{model:e.model,"label-width":100,inline:""},nativeOn:{submit:function(e){e.preventDefault()}}},[a("FormItem",{attrs:{label:"商品信息："}},[a("i-input",{staticClass:"width-340",attrs:{type:"text",placeholder:"请输入商品信息"},on:{"on-enter":e.handleSearch},model:{value:e.model.title,callback:function(t){e.$set(e.model,"title",t)},expression:"model.title"}})],1),a("FormItem",{attrs:{label:"推广文案："}},[a("i-input",{staticClass:"width-340",attrs:{type:"text",placeholder:"请输入推广文案"},on:{"on-enter":e.handleSearch},model:{value:e.model.description,callback:function(t){e.$set(e.model,"description",t)},expression:"model.description"}})],1),a("FormItem",{attrs:{label:"添加时间："}},[a("DatePicker",{staticClass:"width-340",attrs:{type:"datetimerange",format:"yyyy-MM-dd HH:mm:ss",placeholder:"请选择添加时间"},model:{value:e.model.create_time,callback:function(t){e.$set(e.model,"create_time",t)},expression:"model.create_time"}})],1),a("div",{staticClass:"ivu-form-item-btn"},[a("Button",{attrs:{type:"primary"},on:{click:e.handleSearch}},[e._v("搜索")]),a("Button",{attrs:{type:"text"},on:{click:e.handleReset}},[e._v("重置")])],1)],1)],1)},i=[]},b138:function(e,t,a){var n=a("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(a("5530")),r={data:function(){return{model:{description:"",title:"",create_time:[]},typeList:[{value:"全部",label:"全部"},{value:0,label:"图片"},{value:1,label:"视频"}]}},computed:{noManagePerm:function(){return!this.$getPermMap("material.index.manage")}},methods:{addCard:function(){this.$router.push({path:"/material/add"})},handleSearch:function(){var e=(0,i.default)({},this.model);this.$emit("on-search",e)},handleReset:function(){this.reset(),this.handleSearch()},reset:function(){this.model={description:"",title:"",create_time:[]}}}};t.default=r},b597:function(e,t,a){"use strict";a.r(t);var n=a("b094"),i=a("f53d");for(var r in i)["default"].indexOf(r)<0&&function(e){a.d(t,e,(function(){return i[e]}))}(r);var s=a("2877"),o=Object(s["a"])(i["default"],n["a"],n["b"],!1,null,"3ff5b5ec",null);t["default"]=o.exports},db3d:function(e,t,a){},f53d:function(e,t,a){"use strict";a.r(t);var n=a("b138"),i=a.n(n);for(var r in n)["default"].indexOf(r)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(r);t["default"]=i.a},fb3b:function(e,t,a){"use strict";a("db3d")}}]);