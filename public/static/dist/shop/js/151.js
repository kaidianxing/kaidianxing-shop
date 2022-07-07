(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[151],{"1e94":function(e,t,n){"use strict";n.d(t,"a",(function(){return a})),n.d(t,"b",(function(){return i}));var a=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("div",{staticClass:"refund-address-list-box"},[n("kdx-header-bar",{attrs:{type:"little"},scopedSlots:e._u([{key:"header",fn:function(){return[n("Button",{attrs:{type:"primary",disabled:e.noManagePerm,to:"/setting/address/refundAddress/add"}},[e._v("+添加退货地址")])]},proxy:!0}])},[n("Form",{ref:"form",attrs:{model:e.search,"label-width":120,inline:""},nativeOn:{submit:function(e){e.preventDefault()}}},[n("FormItem",{attrs:{label:"名称："}},[n("Input",{staticClass:"width-340",attrs:{type:"text",placeholder:"请输入名称"},on:{"on-enter":e.handleSearch},model:{value:e.search.keyword,callback:function(t){e.$set(e.search,"keyword",t)},expression:"search.keyword"}})],1),n("div",{staticClass:"ivu-form-item-btn"},[n("Button",{attrs:{type:"primary"},on:{click:e.handleSearch}},[e._v("搜索")])],1)],1)],1),n("div",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],staticClass:"refund-address-list"},[n("Table",{ref:"table",attrs:{columns:e.columns,data:e.data},on:{"on-selection-change":e.selectChange}}),n("div",{directives:[{name:"show",rawName:"v-show",value:e.data.length,expression:"data.length"}],staticClass:"footer-action"},[n("Checkbox",{attrs:{disabled:e.noManagePerm},on:{"on-change":e.checkboxChange},model:{value:e.isSelectAll,callback:function(t){e.isSelectAll=t},expression:"isSelectAll"}}),n("Button",{attrs:{disabled:e.selectDisabled||e.noManagePerm},on:{click:e.bulkDelete}},[e._v("删除")])],1),n("div",{staticClass:"footer-page"},[n("kdx-page-component",{ref:"page",attrs:{total:e.page.total},on:{"on-change":e.changePage}})],1)],1),e._t("default")],2)},i=[]},2083:function(e,t,n){"use strict";n("26ad")},"26ad":function(e,t,n){},ac42:function(e,t,n){var a=n("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,n("ac1f"),n("841c"),n("d81d");var i=a(n("5530")),s={inject:["returnToTop"],components:{},props:{},data:function(){var e=this;this.$createElement;return{total:0,isSelectAll:!1,selectRows:[],search:{keyword:""},page:{pageSize:10,pageNumber:1},data:[],loading:!1,columns:[{type:"selection",width:40,align:"center"},{title:"名称",key:"title",render:function(e,t){var n;return"1"==t.row.is_default&&(n=e("kdx-tag-label",{attrs:{type:"info",size:"small"},class:"marginL-10"},["默认"])),e("div",{class:"refund-address-title"},[e("span",{class:"title"},[t.row.title]),n])}},{title:"联系人",key:"name"},{title:"手机",key:"mobile"},{title:"地址",key:"address"},{title:"操作",key:"action",render:function(t,n){var a;return a=1==n.row.is_default?t("Button",{attrs:{type:"text",disabled:e.noManagePerm},on:{click:function(){e.changeDefault(n.row.id,n.row.is_default)}}},["取消默认"]):t("Button",{attrs:{type:"text",disabled:e.noManagePerm},on:{click:function(){e.changeDefault(n.row.id,n.row.is_default)}}},["设置默认"]),t("div",[a,t("Button",{attrs:{type:"text",disabled:e.noManagePerm},class:"marginL-10",on:{click:function(){e.handleEdit(n.row.id)}}},["编辑"]),t("Button",{attrs:{type:"text",disabled:e.noManagePerm},class:"marginL-10",on:{click:function(){e.handleDelete(n.row.id)}}},["删除"])])}}]}},computed:{noManagePerm:function(){return!this.$getPermMap("sysset.refund_address.manage")},selectDisabled:function(){return 0===this.selectRows.length}},created:function(){},mounted:function(){this.getList()},methods:{getList:function(){var e=this;this.returnToTop(),this.loading=!0;var t=(0,i.default)((0,i.default)({},this.search),{},{pagesize:this.page.pageSize,page:this.page.pageNumber});this.$api.settingApi.getRefundAddressList(t).then((function(t){e.loading=!1,0==t.error&&(e.page.total=t.total,e.data=t.list)}))},selectChange:function(e){this.selectRows=e,this.isSelectAll=this.selectRows.length===this.data.length},checkboxChange:function(e){this.$refs["table"].selectAll(e)},handleEdit:function(e){this.$router.push({path:"/setting/address/refundAddress/edit",query:{id:e}})},handleDelete:function(e){var t=this;this.$Modal.confirm({title:"提示",content:"确认删除退货地址",onOk:function(){t.$api.settingApi.deleteRefundAddress({id:e}).then((function(e){0==e.error&&(t.getList(),t.$Message.success("删除成功"))}))},onCancel:function(){}})},bulkDelete:function(){var e=this;this.$Modal.confirm({title:"提示",content:"确认删除？",onOk:function(){var t=e.selectRows.map((function(e){return e.id}));e.$api.settingApi.deleteRefundAddress({id:t}).then((function(t){0==t.error&&(e.getList(),e.$Message.success("删除成功"))}))},onCancel:function(){}})},reset:function(){this.$refs["page"].reset()},changePage:function(e){this.page=e,this.getList()},handleSearch:function(){this.getList()},changeDefault:function(e,t){var n=this,a={id:e,is_default:1==t?0:1};this.$api.settingApi.changeRefundDefault(a).then((function(e){0==e.error&&(n.getList(),n.$Message.success("默认状态修改成功"))}))}}};t.default=s},c53b:function(e,t,n){"use strict";n.r(t);var a=n("1e94"),i=n("f4cd");for(var s in i)["default"].indexOf(s)<0&&function(e){n.d(t,e,(function(){return i[e]}))}(s);n("2083");var r=n("2877"),o=Object(r["a"])(i["default"],a["a"],a["b"],!1,null,"39765257",null);t["default"]=o.exports},f4cd:function(e,t,n){"use strict";n.r(t);var a=n("ac42"),i=n.n(a);for(var s in a)["default"].indexOf(s)<0&&function(e){n.d(t,e,(function(){return a[e]}))}(s);t["default"]=i.a}}]);