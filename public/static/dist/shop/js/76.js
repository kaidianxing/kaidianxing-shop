(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[76],{"0f8a":function(e,t,a){var n=a("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(a("5530")),s={name:"SearchHeader",data:function(){return{model:{keyword:"",status:"all",start_time:"",end_time:"",type:"all"},createTime:"",optionData:{status:[{id:"all",name:"全部"},{id:"0",name:"审核中"},{id:"1",name:"通过"},{id:"-1",name:"拒绝"}],type:[{id:"0",name:"虚拟评论"},{id:"1",name:"真实评论"}],sourceList:[]}}},created:function(){this.getSource()},methods:{getSource:function(){var e=this;this.$api.orderApi.getCommentType().then((function(t){e.optionData.sourceList=t.data}))},toAdd:function(){this.$utils.openNewWindowPage("/commentHelper/comment/list")},handleSearch:function(){var e=Object.assign({},this.model);e.status="all"===e.status?"":e.status,e.type="all"===e.type?"":e.type,this.$emit("on-change",e)},handleReset:function(){this.$emit("on-change",{}),this.resetModel()},changeTime:function(e){this.model.start_time=e[0],this.model.end_time=e[1]},resetModel:function(e){this.model=(0,i.default)((0,i.default)({keyword:"",start_time:"",end_time:"",type:"all",merchant:""},e),{},{status:null!==e&&void 0!==e&&e.status?null===e||void 0===e?void 0:e.status:0==(null===e||void 0===e?void 0:e.status)?"0":"all"}),this.createTime="",(this.model.start_time||this.model.end_time)&&(this.createTime=[this.model.start_time,this.model.end_time])}}};t.default=s},"190b":function(e,t,a){var n=a("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(a("5530"));a("ac1f"),a("841c");var s=n(a("78b1")),r=n(a("a60e")),o={name:"index",components:{SearchHeader:s.default,EvaluateList:r.default},inject:["returnToTop"],data:function(){return{search:{},table:{data:[],total:0,loading:!1},page:{pageSize:10,pageNumber:1}}},methods:{handleSearch:function(e){this.search=e,this.refreshTable()},refreshTable:function(e){var t;this.page=(0,i.default)({pageSize:10,pageNumber:1},e),this.getList(),null===(t=this.$refs["table"])||void 0===t||t.reset(this.page)},changePage:function(e){this.page=e,this.getList()},getList:function(){var e=this;this.returnToTop(),this.$history.setData({search:this.search,page:this.page}),this.table.loading=!0;var t=Object.assign({pagesize:this.page.pageSize,page:this.page.pageNumber},this.search);this.$api.orderApi.getCommentList(t).then((function(t){e.table.loading=!1,0===t.error&&(e.table.data=t.list||[],e.table.total=t.total||0)}))}},mounted:function(){var e=this;this.$history.setRoute(this.$route).getData((function(t){e.search=t.search||{},e.page=e.$utils.deepCopy(t.page)||{pageSize:10,pageNumber:1},e.$nextTick((function(){e.$refs.search.resetModel(e.search),e.refreshTable(e.page)}))}))}};t.default=o},"1ab0":function(e,t,a){"use strict";a.d(t,"a",(function(){return n})),a.d(t,"b",(function(){return i}));var n=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("kdx-header-bar",{scopedSlots:e._u([{key:"header",fn:function(){return[a("Button",{attrs:{type:"primary"},on:{click:e.toAdd}},[e._v("+添加评价")])]},proxy:!0}])},[a("Form",{ref:"form",attrs:{model:e.model,"label-width":100,inline:""},nativeOn:{submit:function(e){e.preventDefault()}}},[a("FormItem",{attrs:{label:"关键词："}},[a("Input",{staticClass:"width-340",attrs:{type:"text",placeholder:"商品名称/订单编号"},on:{"on-enter":e.handleSearch},model:{value:e.model.keyword,callback:function(t){e.$set(e.model,"keyword",t)},expression:"model.keyword"}})],1),a("FormItem",{attrs:{label:"审核状态："}},[a("Select",{staticClass:"width-160",model:{value:e.model.status,callback:function(t){e.$set(e.model,"status",t)},expression:"model.status"}},e._l(e.optionData.status,(function(t){return a("Option",{key:t.id,attrs:{value:t.id}},[e._v(e._s(t.name))])})),1)],1),a("FormItem",{attrs:{label:"评价时间："}},[a("DatePicker",{staticClass:"width-340",attrs:{type:"datetimerange",placeholder:"评价时间"},on:{"on-change":e.changeTime},model:{value:e.createTime,callback:function(t){e.createTime=t},expression:"createTime"}})],1),a("FormItem",{attrs:{label:"评价来源："}},[a("Select",{staticClass:"width-160",attrs:{placeholder:"全部"},model:{value:e.model.type,callback:function(t){e.$set(e.model,"type",t)},expression:"model.type"}},[a("Option",{key:"all",attrs:{value:"all"}},[e._v("全部")]),e._l(e.optionData.sourceList,(function(t){return a("Option",{key:t.key,attrs:{value:t.key}},[e._v(e._s(t.value))])}))],2)],1),a("div",{staticClass:"ivu-form-item-btn"},[a("Button",{attrs:{type:"primary"},on:{click:e.handleSearch}},[e._v("搜索")]),a("Button",{attrs:{type:"text"},on:{click:e.handleReset}},[e._v("重置")])],1)],1)],1)},i=[]},"1e96":function(e,t,a){},"2ae4":function(e,t,a){"use strict";a.d(t,"a",(function(){return n})),a.d(t,"b",(function(){return i}));var n=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"order-evaluate-list"},[a("Table",{ref:"table",attrs:{columns:e.columns,data:e.getData},on:{"on-selection-change":e.selectChange,"on-select-all":e.selectAll},scopedSlots:e._u([{key:"comment",fn:function(t){var n=t.row;return[a("div",{staticClass:"comment"},[a("div",{staticClass:"content"},[1==n.is_choice?a("span",{staticClass:"mark more"},[e._v("精选")]):e._e(),0==n.type?a("span",{staticClass:"mark real"},[e._v("默认")]):e._e(),1==n.type?a("span",{staticClass:"mark real"},[e._v("客评")]):e._e(),2==n.type?a("span",{staticClass:"mark danger"},[e._v("创建")]):e._e(),3==n.type?a("span",{staticClass:"mark danger"},[e._v("抓取")]):e._e(),a("span",{staticClass:"word-break"},[e._v(e._s(n.content))])]),Array.isArray(n.images)&&n.images.length?[a("div",{staticClass:"image-box"},e._l(n.images.slice(0,5),(function(t,i){return a("div",{key:i,staticClass:"image"},[a("img",{attrs:{src:e.$media(t),alt:""},on:{error:e.replaceImage}}),a("div",{staticClass:"shade",on:{mousedown:function(t){return e.previewImg(!0,n.images,i)}}},[a("div",{staticClass:"shade-content"},[a("p",[a("Icon",{attrs:{type:"ios-search"}})],1),a("p",[e._v("查看")])])])])})),0)]:e._e()],2)]}},{key:"level",fn:function(t){var n=t.row;return[a("div",{staticClass:"start pointer"},e._l(5,(function(e,t){return a("i",{key:t,staticClass:"iconfont icon-xingji-shixin",class:{active:n.level>=e}})})),0)]}},{key:"action",fn:function(t){var n=t.row;return[a("div",[2==n.type||3==n.type?a("Button",{staticClass:"marginR-10",attrs:{type:"text",disabled:e.noViewPerm},on:{click:function(t){return e.handleView(n)}}},[e._v("详情")]):a("Button",{staticClass:"marginR-10",attrs:{type:"text"},on:{click:function(t){return e.handleView(n)}}},[e._v("详情")]),a("Button",{staticClass:"marginR-10",attrs:{type:"text"},on:{click:function(t){return e.setSift(n)}}},[e._v("精选")]),2==n.type||3==n.type?a("Button",{staticClass:"marginR-10",attrs:{type:"text",disabled:e.noManagePerm},on:{click:function(t){return e.checkComment(n)}}},[e._v(e._s(1==n.status?"隐藏":"显示"))]):e._e(),2!=n.type&&3!=n.type?a("Button",{staticClass:"marginR-10",attrs:{type:"text"},on:{click:function(t){return e.handleReward(n)}}},[e._v("奖励")]):e._e(),0==n.type||1==n.type?a("Button",{staticClass:"marginR-10",attrs:{type:"text",disabled:e.noOrderManagePerm},on:{click:function(t){return e.handleAudit(n)}}},[e._v("审核")]):e._e(),a("Button",{staticClass:"marginR-10",attrs:{type:"text"},on:{click:function(t){return e.handleReplay(n)}}},[e._v("回复")]),a("Button",{attrs:{type:"text",disabled:e.noOrderManagePerm},on:{click:function(t){return e.handleDelete({type:"single",id:n.id})}}},[e._v("删除")])],1)]}}])}),a("div",{staticClass:"footer-action"},[a("Checkbox",{attrs:{disabled:e.noOrderManagePerm},on:{"on-change":e.checkboxChange},model:{value:e.isSelectAll,callback:function(t){e.isSelectAll=t},expression:"isSelectAll"}}),a("Button",{attrs:{disabled:e.selectDisabled},on:{click:function(t){return e.handleDelete({type:"multiple"})}}},[e._v("删除")])],1),a("div",{staticClass:"footer-page"},[a("kdx-page-component",{ref:"page",attrs:{total:e.total},on:{"on-change":e.changePage}})],1),a("sift-comment",{attrs:{"sift-data":e.siftData},on:{handleOk:e.siftOk},model:{value:e.siftModel,callback:function(t){e.siftModel=t},expression:"siftModel"}}),a("reward-comment",{attrs:{"reward-data":e.rewardData},on:{handleOk:e.rewardOk},model:{value:e.rewardModel,callback:function(t){e.rewardModel=t},expression:"rewardModel"}}),a("audit-comment",{attrs:{"audit-data":e.auditData},on:{handleOk:e.changeAuditStatus},model:{value:e.auditModel,callback:function(t){e.auditModel=t},expression:"auditModel"}}),a("kdx-modal-frame",{attrs:{title:"商家回复",width:520},on:{"on-cancel":e.replayModalClose,"on-ok":e.replayModalOk},model:{value:e.replayModal,callback:function(t){e.replayModal=t},expression:"replayModal"}},[a("div",{staticClass:"remark-modal"},[a("Form",[a("FormItem",[a("Input",{attrs:{type:"textarea",placeholder:"请输入",maxlength:"200",autosize:{minRows:4},"show-word-limit":"",disabled:e.noOrderManagePerm},model:{value:e.replayData.reply_content,callback:function(t){e.$set(e.replayData,"reply_content",t)},expression:"replayData.reply_content"}})],1)],1)],1)]),a("preview-img",{directives:[{name:"show",rawName:"v-show",value:e.imgModal,expression:"imgModal"}],attrs:{imgList:e.previewImages||[],currentIndex:e.current},on:{"on-close":function(t){return e.previewImg(!1)}}})],1)},i=[]},"3c3a":function(e,t,a){"use strict";a.r(t);var n=a("7d90"),i=a("3d14");for(var s in i)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return i[e]}))}(s);a("d6c8");var r=a("2877"),o=Object(r["a"])(i["default"],n["a"],n["b"],!1,null,"ef22c95c",null);t["default"]=o.exports},"3d14":function(e,t,a){"use strict";a.r(t);var n=a("190b"),i=a.n(n);for(var s in n)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(s);t["default"]=i.a},"78b1":function(e,t,a){"use strict";a.r(t);var n=a("1ab0"),i=a("8716");for(var s in i)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return i[e]}))}(s);var r=a("2877"),o=Object(r["a"])(i["default"],n["a"],n["b"],!1,null,"705fa534",null);t["default"]=o.exports},"7d90":function(e,t,a){"use strict";a.d(t,"a",(function(){return n})),a.d(t,"b",(function(){return i}));var n=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"order-evaluate"},[a("search-header",{ref:"search",on:{"on-change":e.handleSearch}}),a("div",{directives:[{name:"loading",rawName:"v-loading",value:e.table.loading,expression:"table.loading"}],staticClass:"content"},[a("evaluate-list",{ref:"table",attrs:{data:e.table.data,total:e.table.total},on:{"on-page-change":e.changePage,"on-refresh":e.refreshTable}})],1),e._t("default")],2)},i=[]},8716:function(e,t,a){"use strict";a.r(t);var n=a("0f8a"),i=a.n(n);for(var s in n)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(s);t["default"]=i.a},a60e:function(e,t,a){"use strict";a.r(t);var n=a("2ae4"),i=a("dc6e");for(var s in i)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return i[e]}))}(s);a("dbc9");var r=a("2877"),o=Object(r["a"])(i["default"],n["a"],n["b"],!1,null,"52c16ca4",null);t["default"]=o.exports},bd38:function(e,t,a){},d6c8:function(e,t,a){"use strict";a("bd38")},dbc9:function(e,t,a){"use strict";a("1e96")},dc6e:function(e,t,a){"use strict";a.r(t);var n=a("ef5b"),i=a.n(n);for(var s in n)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(s);t["default"]=i.a},ef5b:function(e,t,a){var n=a("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(a("5530"));a("a9e3"),a("d81d"),a("d3b7"),a("159b");var s=n(a("effd")),r=n(a("036a")),o=n(a("9d4d")),l=n(a("329f")),c={name:"EvaluateList",components:{SiftComment:s.default,RewardComment:r.default,AuditComment:o.default,PreviewImg:l.default},props:{data:{type:Array,default:function(){return[]}},total:{type:Number,default:0}},computed:{getData:function(){var e=this;return this.data.map((function(t){return t._disabled=e.noOrderManagePerm,t}))},selectDisabled:function(){return 0===this.selectRows.length},noManagePerm:function(){return!this.$getPermMap("commentHelper.index.manage")},noViewPerm:function(){return!this.$getPermMap("commentHelper.index.view")},noOrderManagePerm:function(){return!this.$getPermMap("order.comment.manage")}},data:function(){var e=this;this.$createElement;return{siftModel:!1,siftData:{},rewardModel:!1,rewardData:{},auditModel:!1,auditData:{},replayModal:!1,replayData:{id:"",reply_content:""},previewImages:[],imgModal:!1,current:0,columns:[{type:"selection",width:40,align:"center"},{title:"商品",key:"shop",minWidth:180,render:function(t,a){return t("div",{class:{shop:!0}},[t("div",{class:{"shop-image":!0}},[t("img",{attrs:{src:e.$media(a.row.thumb)},on:{error:function(t){e.replaceImage(t)}}})]),t("div",{class:{"shop-information":!0}},[t("div",{class:{title:!0}},a.row.title)])])}},{title:"评价信息",key:"content",slot:"comment",minWidth:200},{title:"评分等级",key:"level",slot:"level",minWidth:120},{title:"评价状态",key:"status",minWidth:40,render:function(e,t){return e("div",{class:1!=t.row.status?"disabled-color":""},[1==t.row.status?"显示":"隐藏"])}},{title:"审核状态",key:"status",minWidth:40,render:function(e,t){return 2==t.row.type||3==t.row.type?e("div",["-"]):e("div",{class:1==t.row.status?"success-color":0==t.row.status?"warning-color":"danger-color"},[1==t.row.status?"通过":0==t.row.status?"审核中":"不通过"])}},{title:"来源",key:"type",minWidth:40,render:function(e,t){var a="";switch(+t.row.type){case 0:a="默认";break;case 1:a="客户";break;case 2:a="手动";break;case 3:a="抓取";break}return e("div",[a])}},{title:"评价时间",key:"created_at",minWidth:120},{title:"操作",key:"action",width:140,slot:"action"}],selectRows:[],isSelectAll:!1}},methods:{reset:function(e){this.$refs["page"].reset(e),this.selectRows=[],this.isSelectAll=!1},changePage:function(e){this.$emit("on-page-change",e)},checkboxChange:function(e){var t=this;this.$refs["table"].selectAll(e),this.data.forEach((function(a,n){t.$set(t.data[n],"_checked",e)})),this.selectRows=e?this.data:[]},selectChange:function(e){this.selectRows=e,this.isSelectAll=this.selectRows.length===this.data.length},selectAll:function(e,t){console.log("all",e,t)},handleDelete:function(e){var t=this,a=e.type,n=e.id;this.$Modal.confirm({title:"提示",content:"确认彻底删除评论？",onOk:function(){var e=[];e="multiple"===a?t.selectRows.map((function(e){return e.id})):[n],t.deleteComment(e)},onCancel:function(){}})},deleteComment:function(e){var t=this;this.$api.orderApi.deleteComment({id:e}).then((function(e){0===e.error&&(t.$Message.success("评论删除成功"),t.$emit("on-refresh"))}))},handleView:function(e){var t="/commentHelper/comment/single/edit";"1"!=e.type&&"0"!=e.type||(t="/order/evaluate/audit"),this.$router.push({path:t,query:{id:e.id,goodId:e.goods_id}})},previewImg:function(e,t,a){this.previewImages=t||[],this.current=a||0,this.imgModal=e},setSift:function(e){this.siftData={sort_by:e.sort_by,is_choice:+e.is_choice,id:e.id},this.siftModel=!0},siftOk:function(e){var t=this;this.$api.commentHelperApi.setChoice(e).then((function(e){0===e.error&&(t.$Message.success("修改成功"),t.getList())}))},handleReward:function(e){this.rewardData={id:e.id,member_id:e.member_id,is_reward:(null===e||void 0===e?void 0:e.is_reward)||0,reward_content:(null===e||void 0===e?void 0:e.reward_content)||null},this.rewardModel=!0},rewardOk:function(e){var t=this;this.$api.commentHelperApi.saveReward(e).then((function(e){0===e.error&&(t.$Message.success("保存成功"),t.getList())}))},checkComment:function(e){var t=this,a=1==e.status?0:1;this.$Modal.confirm({title:"提示",content:"确定".concat(0==a?"隐藏":"显示","此条评价"),onOk:function(){t.changeStatus({id:e.id,status:a})},onCancel:function(){}})},handleAudit:function(e){this.auditData={id:e.id,status:+e.status},this.auditModel=!0},changeStatus:function(e){var t=this;this.$api.commentHelperApi.changeStatus(e).then((function(e){0===e.error&&(t.$Message.success("修改成功"),t.getList())}))},changeAuditStatus:function(e){var t=this;this.$api.orderApi.checkComment(e).then((function(e){0===e.error&&(t.$Message.success("修改成功"),t.getList())}))},getList:function(){this.$emit("on-refresh")},handleReplay:function(e){this.replayModal=!0,this.replayData={id:e.id,reply_content:e.reply_content}},replayModalClose:function(){this.replayModal=!1},replayModalOk:function(){var e=this;this.replayModal=!1,this.replayData.reply_content&&this.$api.orderApi.replayComment((0,i.default)({},this.replayData)).then((function(t){0===t.error&&(e.$Message.success("评价回复成功"),e.getList())}))},routerPath:function(e){this.$utils.openNewWindowPage("/member/detail",{id:e})}}};t.default=c}}]);