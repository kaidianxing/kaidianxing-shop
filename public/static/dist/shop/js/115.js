(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[115],{"15b9":function(t,e,a){"use strict";a.r(e);var n=a("a2bd"),s=a.n(n);for(var i in n)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(i);e["default"]=s.a},"429e":function(t,e,a){"use strict";a.d(e,"a",(function(){return n})),a.d(e,"b",(function(){return s}));var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"good-label"},[a("kdx-header-bar",{attrs:{type:"little"},scopedSlots:t._u([{key:"header",fn:function(){return[a("Button",{attrs:{type:"primary",disabled:t.noManagePerm},on:{click:t.toAdd}},[t._v("创建底部菜单")])]},proxy:!0}])},[a("br"),a("Form",{ref:"form",attrs:{model:t.search,"label-width":130,inline:""},nativeOn:{submit:function(t){t.preventDefault()}}},[a("FormItem",{attrs:{label:"菜单名称："}},[a("Input",{staticClass:"width-160",attrs:{type:"text",placeholder:"请输入菜单名称"},on:{"on-enter":t.handleSearch},model:{value:t.search.name,callback:function(e){t.$set(t.search,"name",e)},expression:"search.name"}})],1),a("FormItem",{staticStyle:{"margin-bottom":"0"},attrs:{label:"创建时间："}},[a("div",{staticClass:"flex"},[a("FormItem",{attrs:{label:""}},[a("DatePicker",{staticClass:"width-170",attrs:{type:"datetime",format:"yyyy-MM-dd HH:mm:ss",placeholder:"请选择开始时间"},model:{value:t.search.start_time,callback:function(e){t.$set(t.search,"start_time",e)},expression:"search.start_time"}})],1),a("span",{staticStyle:{"padding-left":"10px","padding-right":"10px"}},[t._v(" ~ ")]),a("FormItem",{attrs:{label:""}},[a("DatePicker",{staticClass:"width-170",attrs:{type:"datetime",format:"yyyy-MM-dd HH:mm:ss",placeholder:"请选择结束时间"},model:{value:t.search.end_time,callback:function(e){t.$set(t.search,"end_time",e)},expression:"search.end_time"}})],1)],1)]),a("div",{staticClass:"ivu-form-item-btn"},[a("Button",{attrs:{type:"primary"},on:{click:t.handleSearch}},[t._v("搜索")]),a("Button",{attrs:{type:"text"},on:{click:t.handleReset}},[t._v("重置")])],1)],1)],1),a("div",{directives:[{name:"loading",rawName:"v-loading",value:t.table.loading,expression:"table.loading"}],staticClass:"table-list"},[a("table-list",{ref:"table_list",attrs:{data:t.getTableData,type:"2",total:t.table.total},on:{"on-refresh":t.getList,"on-page-change":t.changePage}})],1),t._t("default")],2)},s=[]},7424:function(t,e,a){"use strict";a("e266")},"93a4":function(t,e,a){"use strict";a.r(e);var n=a("429e"),s=a("c4e1");for(var i in s)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return s[t]}))}(i);a("c214");var r=a("2877"),o=Object(r["a"])(s["default"],n["a"],n["b"],!1,null,"ec6fc000",null);e["default"]=o.exports},9823:function(t,e,a){},a2bd:function(t,e,a){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("a9e3"),a("d81d"),a("d3b7");var n={name:"TableList",props:{data:{type:Array,default:function(){return[]}},total:{type:Number,default:0},type:{type:String,default:"1"}},data:function(){var t=this;this.$createElement;return{columns:[{title:"排序",key:"sort_order",width:100},{title:"菜单名称",key:"name"},{title:"跳转链接",key:"url"},{title:"创建时间",key:"created_at"},{title:"状态",key:"status",width:100,render:function(t,e){return"0"==e.row.status?t("kdx-status-text",["禁用"]):"1"==e.row.status?t("kdx-status-text",{attrs:{type:"success"}},["启用"]):void 0}},{title:"操作",key:"action",width:200,render:function(e,a){var n;return n="0"===a.row.status?e("Button",{attrs:{type:"text",disabled:t.noManagePerm},on:{click:function(){t.handleLabelGroupStatus(a.row,"1")}}},["启用"]):e("Button",{attrs:{type:"text",disabled:t.noManagePerm},on:{click:function(){t.handleLabelGroupStatus(a.row,"0")}}},["禁用"]),e("div",{class:"action"},[n,e("Button",{attrs:{type:"text",disabled:t.noManagePerm},on:{click:function(){t.$router.push({path:"/home/pc/menus/add",query:{id:a.row.id,type:t.type}})}}},["编辑"]),e("Button",{attrs:{type:"text",disabled:t.noManagePerm||"1"==a.row.is_default},on:{click:function(){t.handleDelete(a.row)}}},["删除"])])}}],selectRows:[],isSelectAll:!1}},computed:{getTableData:function(){var t=this;return this.data.map((function(e){return e._disabled=t.noManagePerm,e}))},noManagePerm:function(){return!this.$getPermMap("pc.menus.manage")},isDisabled:function(){return 0===this.selectRows.length},selectHasDefault:function(){return this.selectRows.some((function(t){return"1"==t.is_default}))}},methods:{selectChange:function(t){this.selectRows=t,this.isSelectAll=this.selectRows.length===this.data.length},checkboxChange:function(t){this.$refs["table"].selectAll(t)},handleLabelGroupStatus:function(t,e){var a=this;this.$api.homeApi.changeStatus({id:t.id,status:e}).then((function(t){0===t.error&&(a.$Message.success("状态修改成功"),a.$emit("on-refresh"))}))},handleDelete:function(t){var e=this;this.$Modal.confirm({title:"提示",content:"是否确认删除？",onOk:function(){e.$api.homeApi.menuDelete({id:t.id}).then((function(t){0===t.error&&(e.$Message.success("删除成功"),e.$emit("on-refresh"))})).catch()}})},deleteLabel:function(t){var e=this;this.$api.goodsApi.deleteLabelGroup({id:t}).then((function(t){0===t.error&&(e.$Message.success("标签删除成功"),e.$emit("on-refresh"))})).catch()},reset:function(t){this.$refs["page"].reset(t)},changePage:function(t){this.$emit("on-page-change",t)},initFooterData:function(){this.isSelectAll=!1,this.selectRows=[]}}};e.default=n},a611:function(t,e,a){"use strict";a.r(e);var n=a("e797"),s=a("15b9");for(var i in s)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return s[t]}))}(i);a("7424");var r=a("2877"),o=Object(r["a"])(s["default"],n["a"],n["b"],!1,null,"6cb8211c",null);e["default"]=o.exports},b7bb:function(t,e,a){var n=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("d81d"),a("ac1f"),a("841c");var s=n(a("a611")),i=a("d1be"),r={name:"list",inject:["returnToTop"],components:{TableList:s.default},computed:{noManagePerm:function(){return!this.$getPermMap("pc.menus.manage")},getTableData:function(){var t=this;return this.table.data.map((function(e){return e._disabled=t.noManagePerm,e}))}},data:function(){return{search:{name:"",start_time:"",end_time:""},table:{data:[],loading:!1,total:0},optionData:{status:[{value:"all",name:"全部"},{value:"1",name:"启用"},{value:"0",name:"禁用"}]},page:{pageSize:10,pageNumber:1}}},methods:{handleSearch:function(){this.refreshTable()},handleReset:function(){this.search={name:"",start_time:"",end_time:""},this.refreshTable()},toAdd:function(){this.$router.push({path:"/home/pc/menus/add?type=2"})},refreshTable:function(t){t||(this.page={pageSize:10,pageNumber:1}),this.$refs["table_list"].reset(t),this.getList()},changePage:function(t){this.page=t,this.getList()},getList:function(){var t=this;this.$history.setData({search:this.search,page:this.page}),this.returnToTop(),this.$refs["table_list"].initFooterData(),this.table.loading=!0;var e=Object.assign({},this.search);e.start_time=(0,i.formatDate)(e.start_time,"yyyy-MM-dd hh:mm:ss"),e.end_time=(0,i.formatDate)(e.end_time,"yyyy-MM-dd hh:mm:ss");var a=Object.assign({pagesize:this.page.pageSize,page:this.page.pageNumber,type:2},e);this.$api.homeApi.menuList(a).then((function(e){t.table.loading=!1,0===e.error&&(t.table.data=e.list,t.table.total=e.total)})).catch()}},mounted:function(){var t=this;this.$history.setRoute(this.$route).getData((function(e){var a=e.search,n=e.page;t.search=t.$utils.deepCopy(a)||t.search,t.page=n,t.$nextTick((function(){t.refreshTable(t.page)}))}))}};e.default=r},c214:function(t,e,a){"use strict";a("9823")},c4e1:function(t,e,a){"use strict";a.r(e);var n=a("b7bb"),s=a.n(n);for(var i in n)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(i);e["default"]=s.a},e266:function(t,e,a){},e797:function(t,e,a){"use strict";a.d(e,"a",(function(){return n})),a.d(e,"b",(function(){return s}));var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"good-group-table-list"},[a("Table",{ref:"table",attrs:{columns:t.columns,data:t.getTableData},on:{"on-selection-change":t.selectChange}}),a("div",{staticClass:"footer-page"},[a("kdx-page-component",{ref:"page",attrs:{total:t.total},on:{"on-change":t.changePage}})],1)],1)},s=[]}}]);