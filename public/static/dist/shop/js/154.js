(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[154],{"084b":function(t,e,a){"use strict";a.r(e);var n=a("b201"),s=a.n(n);for(var i in n)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(i);e["default"]=s.a},"0889":function(t,e,a){"use strict";a("c72b")},"24c5":function(t,e,a){"use strict";var n=a("3859");a.o(n,"render")&&a.d(e,"render",(function(){return n["render"]})),a.o(n,"staticRenderFns")&&a.d(e,"staticRenderFns",(function(){return n["staticRenderFns"]}))},3290:function(t,e,a){"use strict";a.r(e);var n=a("24c5"),s=a("084b");for(var i in s)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return s[t]}))}(i);a("0889");var r=a("2877"),o=Object(r["a"])(s["default"],n["render"],n["staticRenderFns"],!1,null,"7cef4a96",null);e["default"]=o.exports},3859:function(t,e,a){Object.defineProperty(e,"__esModule",{value:!0}),e.staticRenderFns=e.render=void 0,a("9911");var n=function(){var t=this,e=t._self._c;return e("div",{staticClass:"container"},[e("kdx-header-bar",{attrs:{type:"little"},scopedSlots:t._u([{key:"header",fn:function(){return[e("Button",{attrs:{disabled:t.noManagePerm,type:"primary",to:"/setting/shopSetting/announcementManagement/add"}},[t._v(" +添加公告 ")])]},proxy:!0}])},[e("Form",{ref:"form",attrs:{"label-width":100,inline:""},nativeOn:{submit:function(t){t.preventDefault()}}},[e("FormItem",{attrs:{label:"关键词："}},[e("Input",{staticClass:"width-340",attrs:{type:"text",placeholder:"请输入关键词"},on:{"on-enter":t.handleSearch},model:{value:t.searchData.keyword,callback:function(e){t.$set(t.searchData,"keyword",e)},expression:"searchData.keyword"}})],1),e("FormItem",{attrs:{label:"状态："}},[e("Select",{staticClass:"width-160",model:{value:t.searchData.status,callback:function(e){t.$set(t.searchData,"status",e)},expression:"searchData.status"}},t._l(t.statusList,(function(a,n){return e("Option",{key:n,attrs:{value:a.value}},[t._v(" "+t._s(a.label)+" ")])})),1)],1),e("div",{staticClass:"ivu-form-item-btn"},[e("Button",{attrs:{type:"primary"},on:{click:t.handleSearch}},[t._v("搜索")]),e("Button",{attrs:{type:"text"},on:{click:t.handleReset}},[t._v("重置")])],1)],1)],1),e("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticClass:"list-wrap"},[e("Table",{ref:"table",attrs:{columns:t.columns,data:t.list},on:{"on-selection-change":t.selectionChange},scopedSlots:t._u([{key:"link",fn:function(a){var n=a.row;return[e("div",{staticClass:"link"},[t._v(" "+t._s(n.link||"-")+" ")])]}},{key:"status",fn:function(a){var n=a.row;return["1"===n.status?e("kdx-status-text",{attrs:{type:"success"}},[t._v(" 显示 ")]):t._e(),"0"===n.status?e("kdx-status-text",{attrs:{type:"disabled"}},[t._v(" 隐藏 ")]):t._e()]}},{key:"action",fn:function(a){var n=a.row;return[e("Button",{attrs:{disabled:t.noManagePerm,type:"text"},on:{click:function(e){return t.fnOperate({operate:"changeStatus",type:"single",id:n.id,status:"0"===n.status?"1":"0"})}}},[t._v(" "+t._s("0"===n.status?"显示":"隐藏")+" ")]),e("Button",{staticClass:"mL-10",attrs:{disabled:t.noManagePerm,type:"text"},on:{click:function(e){return t.fnOperate({operate:"edit",id:n.id})}}},[t._v(" 编辑 ")]),e("Button",{staticClass:"mL-10",attrs:{disabled:t.noManagePerm,type:"text"},on:{click:function(e){return t.fnOperate({operate:"delete",type:"single",id:n.id})}}},[t._v(" 删除 ")])]}}])}),e("div",{directives:[{name:"show",rawName:"v-show",value:t.list.length>0,expression:"list.length > 0"}],staticClass:"footer-action"},[e("Checkbox",{attrs:{disabled:t.noManagePerm},on:{"on-change":t.checkboxChange},model:{value:t.isSelectAll,callback:function(e){t.isSelectAll=e},expression:"isSelectAll"}}),e("Button",{attrs:{disabled:t.selectDisabled||t.noManagePerm},on:{click:function(e){return t.fnOperate({operate:"changeStatus",type:"multiple",status:"1"})}}},[t._v(" 显示 ")]),e("Button",{attrs:{disabled:t.selectDisabled||t.noManagePerm},on:{click:function(e){return t.fnOperate({operate:"changeStatus",type:"multiple",status:"0"})}}},[t._v(" 隐藏 ")]),e("Button",{attrs:{disabled:t.selectDisabled||t.noManagePerm},on:{click:function(e){return t.fnOperate({operate:"delete",type:"multiple"})}}},[t._v(" 删除 ")])],1),e("div",{staticClass:"footer-page"},[e("kdx-page-component",{ref:"page",attrs:{total:t.total},on:{"on-change":t.changePage}})],1)],1),t._t("default")],2)};e.render=n;var s=[];e.staticRenderFns=s},9911:function(t,e,a){"use strict";var n=a("23e7"),s=a("857a"),i=a("af03");n({target:"String",proto:!0,forced:i("link")},{link:function(t){return s(this,"a","href",t)}})},b201:function(t,e,a){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("d3b7"),a("159b"),a("a15b"),a("d81d"),a("14d9");var n={inject:["returnToTop"],data:function(){return{page:1,pagesize:10,loading:!1,statusList:[{value:"all",label:"全部"},{value:"1",label:"显示"},{value:"0",label:"隐藏"}],searchData:{keyword:"",status:"all"},columns:[{type:"selection",width:40,align:"center"},{title:"排序",key:"sort_by",width:80},{title:"标题",key:"title",minWidth:300},{title:"链接",slot:"link"},{title:"状态",slot:"status"},{title:"操作",slot:"action"}],selectRows:[],list:[],total:0}},computed:{noManagePerm:function(){return!this.$getPermMap("sysset.mall.notice.manage")},isSelectAll:function(){return this.selectRows.length===this.list.length},selectDisabled:function(){return 0===this.selectRows.length}},created:function(){this.getList()},methods:{getList:function(){var t=this;this.returnToTop(),this.loading=!0,this.$api.settingApi.getNoticeList({page:this.page,pagesize:this.pagesize,keyword:this.searchData.keyword,status:"all"===this.searchData.status?"":this.searchData.status}).then((function(e){0===e.error&&(t.list=e.list,t.total=e.total,t.loading=!1)}))},handleSearch:function(){var t;this.selectRows=[],this.page=1,this.pagesize=10,null===(t=this.$refs["page"])||void 0===t||t.reset(),this.getList()},handleReset:function(){var t;this.selectRows=[],this.searchData.status="all",this.searchData.keyword="",this.page=1,this.pagesize=10,null===(t=this.$refs["page"])||void 0===t||t.reset(),this.getList()},selectionChange:function(t){this.selectRows=t},checkboxChange:function(t){var e=this;this.list.forEach((function(a,n){e.$set(e.list[n],"_checked",t)})),this.selectRows=t?this.list:[]},fnOperate:function(t){var e=this;if("delete"===t.operate){var a="";"single"===t.type?a=t.id:"multiple"===t.type&&(a=this.selectRows.map((function(t){return t.id})).join(",")),this.$Modal.confirm({title:"提示",content:"确定删除吗?",onOk:function(){e.deleteNotice(a)},onCancel:function(){}})}else if("edit"===t.operate)this.$router.push({path:"/setting/shopSetting/announcementManagement/add",query:{id:t.id}});else if("changeStatus"===t.operate){var n=t.status,s="";"single"===t.type?s=t.id:"multiple"===t.type&&(s=this.selectRows.map((function(t){return t.id})).join(",")),this.$Modal.confirm({title:"提示",content:"确定修改状态吗?",onOk:function(){e.changeNoticeStatus(s,n)},onCancel:function(){}})}},changePage:function(t){this.page=t.pageNumber,this.pagesize=t.pageSize,this.getWithdrawList()},changeNoticeStatus:function(t,e){var a=this;this.$api.settingApi.changeNoticeStatus({id:t,status:e}).then((function(t){0===t.error&&(a.$Message.success("操作成功"),a.selectRows=[],a.getList())}))},deleteNotice:function(t){var e=this;this.$api.settingApi.deleteNotice({id:t}).then((function(t){0===t.error&&(e.$Message.success("操作成功"),e.selectRows=[],e.getList())}))}}};e.default=n},c72b:function(t,e,a){}}]);