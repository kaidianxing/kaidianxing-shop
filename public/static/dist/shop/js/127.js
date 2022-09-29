(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[127],{"2b92":function(t,e,a){"use strict";a.d(e,"a",(function(){return n})),a.d(e,"b",(function(){return i}));var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("kdx-header-bar",{scopedSlots:t._u([{key:"header",fn:function(){return[a("Button",{attrs:{type:"primary",disabled:t.noManagePerm},on:{click:t.addActivity}},[t._v("+添加活动")])]},proxy:!0}])},[a("Form",{ref:"form",attrs:{model:t.model,"label-width":100,inline:""},nativeOn:{submit:function(t){t.preventDefault()}}},[a("FormItem",{attrs:{label:"活动名称："}},[a("i-input",{staticClass:"width-340",attrs:{type:"text",placeholder:"活动名称"},on:{"on-enter":t.handleSearch},model:{value:t.model.activity_name,callback:function(e){t.$set(t.model,"activity_name",e)},expression:"model.activity_name"}})],1),a("FormItem",{attrs:{label:"活动状态："}},[a("Select",{staticClass:"width-160",model:{value:t.model.status,callback:function(e){t.$set(t.model,"status",e)},expression:"model.status"}},t._l(t.statusList,(function(e){return a("Option",{key:e.value,attrs:{value:e.value}},[t._v(" "+t._s(e.label)+" ")])})),1)],1),a("FormItem",{attrs:{label:"活动时间："}},[a("DatePicker",{staticClass:"width-340",attrs:{type:"datetimerange",format:"yyyy-MM-dd HH:mm",placeholder:"活动时间",editable:!1},on:{"on-change":t.changeDate},model:{value:t.date,callback:function(e){t.date=e},expression:"date"}})],1),a("div",{staticClass:"ivu-form-item-btn"},[a("Button",{attrs:{type:"primary"},on:{click:t.handleSearch}},[t._v("搜索")]),a("Button",{attrs:{type:"text"},on:{click:t.handleReset}},[t._v("重置")])],1)],1)],1)},i=[]},"430f":function(t,e,a){var n=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("ac1f"),a("841c");var i=n(a("5530")),s=n(a("4b9c")),r={name:"index",components:{SearchHeader:s.default},data:function(){var t=this;this.$createElement;return{loading:!1,data:[],total:0,columns:[{title:"活动名称",key:"activity_name",minWidth:160},{title:"活动时间",slot:"date",minWidth:200},{title:"停止时间",key:"stop_time",minWidth:170,render:function(e,a){return e("div",[t.getStopTime(a.row)])}},{title:"累计签到人数",key:"sign_person_count",minWidth:120,render:function(e,a){return e("div",[t.signTotalNum(a.row.sign_person_count)])}},{title:"总签到次数",key:"sign_count",minWidth:100,render:function(e,a){return e("div",[t.signTotalNum(a.row.sign_count)])}},{title:"奖励领取情况",slot:"rewards",minWidth:140},{title:"活动状态",slot:"status",minWidth:90},{title:"操作",key:"action",minWidth:140,render:function(e,a){return e("div",{class:"action"},[e("Button",{attrs:{type:"text"},on:{click:function(){t.handleView(a.row.id,"view")}}},["查看"]),e("Button",{attrs:{type:"text",disabled:"1"!=a.row.status&&"0"!=a.row.status||t.noManagePerm},on:{click:function(){t.handleEdit(a.row.id,"edit")}}},["编辑"]),e("Button",{attrs:{type:"text",disabled:t.noManagePerm},on:{click:function(){t.handleView(a.row.id,"copy")}}},["复制"]),e("Button",{attrs:{type:"text",disabled:"1"!=a.row.status||t.noManagePerm},on:{click:function(){t.handleStop(a.row.id)}}},["停止"]),e("Button",{attrs:{type:"text",disabled:t.noManagePerm},on:{click:function(){t.handleDelete(a.row)}}},["删除"])])}}],page:{pageSize:10,pageNumber:1},search:{}}},computed:{noManagePerm:function(){return!this.$getPermMap("creditSign.list.manage")}},created:function(){this.getList()},methods:{getList:function(){var t=this;this.loading=!0;var e=(0,i.default)((0,i.default)({},this.search),{},{pagesize:this.page.pageSize,page:this.page.pageNumber});this.$api.creditSignApi.creditSignList(e).then((function(e){t.loading=!1,0===e.error&&(t.data=e.list,t.total=e.total)}))},handleSearch:function(t){this.search=(0,i.default)((0,i.default)({},this.search),t),this.refreshTable()},changePage:function(t){this.page=t,this.getList()},handleView:function(t,e){this.$router.push({path:"/creditSign/manage/add",query:{id:t,type:e}})},handleEdit:function(t,e){this.$router.push({path:"/creditSign/manage/add",query:{id:t,type:e}})},handleStop:function(t){var e=this;this.$Modal.confirm({title:"提示",content:"确认要停该活动吗？停止后不可重新开启",onOk:function(){e.stopActivity(t)},onCancel:function(){}})},stopActivity:function(t){var e=this;this.$api.creditSignApi.stopActivity({id:t}).then((function(t){0===t.error&&(e.refreshTable(),e.$Message.success("操作成功"))}))},handleDelete:function(t){var e=this;this.$Modal.confirm({title:"确认删除",content:"确定要删除该活动？",onOk:function(){e.deleteActivity(t.id)},onCancel:function(){}})},deleteActivity:function(t){var e=this;this.$api.creditSignApi.deleteActivity({id:t}).then((function(t){0===t.error&&(e.refreshTable(),e.$Message.success("删除成功"))}))},handleActivityData:function(t){this.$router.push({path:"/performanceAward/award/index",query:{title:t}})},refreshTable:function(){this.page={pageSize:10,pageNumber:1},this.$refs["page"].reset(),this.getList()},getTime:function(t){return"0000-00-00 00:00:00"===t?"-":t},getStopTime:function(t){return 0==t.status||1==t.status||"0000-00-00 00:00:00"===t.stop_time?"-":t.stop_time},signTotalNum:function(t){return t||"-"}}};e.default=r},"4b9c":function(t,e,a){"use strict";a.r(e);var n=a("2b92"),i=a("dd3e");for(var s in i)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(s);var r=a("2877"),o=Object(r["a"])(i["default"],n["a"],n["b"],!1,null,"fc5e15ca",null);e["default"]=o.exports},"5fe5":function(t,e,a){"use strict";a.d(e,"a",(function(){return n})),a.d(e,"b",(function(){return i}));var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"spell-group-list"},[a("search-header",{ref:"search_header",on:{"on-search":t.handleSearch}}),a("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticClass:"table-list"},[a("Table",{ref:"table",attrs:{columns:t.columns,data:t.data},scopedSlots:t._u([{key:"date",fn:function(e){var n=e.row;return[a("div",{staticClass:"time"},[t._v("起："+t._s(t.getTime(n.start_time)))]),a("div",{staticClass:"time"},[t._v("止："+t._s(t.getTime(n.end_time)))])]}},{key:"status",fn:function(e){var n=e.row;return[1==n.status?a("kdx-status-text",{attrs:{type:"success"}},[t._v("进行中")]):t._e(),-1==n.status?a("kdx-status-text",{attrs:{type:"danger"}},[t._v("已停止")]):t._e(),-2==n.status?a("kdx-status-text",{attrs:{type:"danger"}},[t._v("手动停止")]):t._e(),-3==n.status?a("kdx-status-text",{attrs:{type:"danger"}},[t._v("已失效")]):t._e(),0==n.status?a("kdx-status-text",{attrs:{type:"warning"}},[t._v("未开始")]):t._e()]}},{key:"rewards",fn:function(e){var n=e.row;return["0"!=n.status?a("div",[t._v("积分："+t._s(n.credit_num>0?n.credit_num+"个":"-"))]):t._e(),"0"!=n.status?a("div",[t._v("优惠券："+t._s(n.coupon_num>0?n.coupon_num+"张":"-"))]):a("div",[t._v("-")]),a("div")]}}])}),a("div",{directives:[{name:"show",rawName:"v-show",value:t.data.length>0,expression:"data.length > 0"}],staticClass:"footer-page"},[a("kdx-page-component",{ref:"page",attrs:{total:t.total},on:{"on-change":t.changePage}})],1)],1)],1)},i=[]},"7a26":function(t,e,a){"use strict";a.r(e);var n=a("430f"),i=a.n(n);for(var s in n)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(s);e["default"]=i.a},b638:function(t,e,a){var n=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=n(a("5530")),s={data:function(){return{model:{title:"",status:"all",start_time:"",end_time:""},date:[],statusList:[{value:"all",label:"全部"},{value:"1",label:"进行中"},{value:"0",label:"未开始"},{value:"-1",label:"停止"},{value:"-2",label:"手动停止"}],level:[{id:"all",name:"全部"}]}},computed:{noManagePerm:function(){return!this.$getPermMap("creditSign.list.manage")}},created:function(){},methods:{addActivity:function(){this.$router.push({path:"/creditSign/manage/add",query:{type:"add"}})},changeDate:function(t){this.model.start_time=t[0],this.model.end_time=t[1]},handleSearch:function(){var t=(0,i.default)((0,i.default)({},this.model),{},{status:"all"===this.model.status?"":this.model.status});this.$emit("on-search",t)},handleReset:function(){this.reset(),this.handleSearch()},reset:function(){this.model={activity_name:"",status:"all",start_time:"",end_time:""},this.date=[]}}};e.default=s},be8d:function(t,e,a){"use strict";a("eb64")},d777:function(t,e,a){"use strict";a.r(e);var n=a("5fe5"),i=a("7a26");for(var s in i)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(s);a("be8d");var r=a("2877"),o=Object(r["a"])(i["default"],n["a"],n["b"],!1,null,"f1075c80",null);e["default"]=o.exports},dd3e:function(t,e,a){"use strict";a.r(e);var n=a("b638"),i=a.n(n);for(var s in n)["default"].indexOf(s)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(s);e["default"]=i.a},eb64:function(t,e,a){}}]);