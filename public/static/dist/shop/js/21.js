(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[21],{"00e9":function(t,e,a){},"0b88":function(t,e,a){},"3a47":function(t,e,a){var i=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("a434");var s=i(a("5530")),o=i(a("aee3")),n=i(a("474b")),r={components:{GoodsSelector:n.default},mixins:[o.default],props:{type:{type:String,default:""}},data:function(){var t=this;this.$createElement;return{refreshkey:"0",show:!1,goodsShow:!1,columns:[{key:"thumb",width:94,title:"商品",align:"left",render:function(e,a){return e("div",{class:"goods-thumb"},[e("i",{class:"iconfont icon-move move-icon"}),e("img",{attrs:{src:t.$utils.media(a.row.thumb),alt:""},style:{width:"40px",height:"40px",display:"block",margin:"0 10px"}})])}},{key:"title",width:220,align:"center",title:" ",render:function(t,e){var a,i="";switch(e.row.bargain?i="砍":e.row.credit&&(i="积"),e.row.type){case"0":a={goodsName:"mark real",goodsText:"实"};break;case"1":a={goodsName:"mark virtual",goodsText:"虚"};break;case"2":a={goodsName:"mark secret",goodsText:"密"};break}return t("div",{class:"title"},[t("span",{class:"mark",style:{display:"1"===e.row.has_option?"inline-block":"none"}},["多"]),t("span",{class:a.goodsName},[a.goodsText]),i?t("span",{class:"icon"},[i]):"",e.row.title])}},{key:"price",title:"价格",align:"center",render:function(t,e){return t("p",{class:"price",style:"text-align:center;"},["￥",e.row.price])}},{key:"option",title:"操作",align:"center",render:function(e,a){return e("p",{class:"option",style:"text-align:center;"},[e("span",{on:{click:function(){t.delete(a)}}},["删除"])])}}],replaceIndex:-1,goodsParams:{activity_type:this.type}}},computed:{},methods:{draggeTable:function(t,e){var a=(0,s.default)({},this.currentModal.data[t]);Object.assign(this.currentModal.data[t],this.currentModal.data[e]),Object.assign(this.currentModal.data[e],a)},handleCancel:function(){this.show=!1},handleChange:function(t){var e;this.currentModal.data.length=0;for(var a=t.length<=this.currentModal.params.goodsnum?t.length:this.currentModal.params.goodsnum,i=0;i<a;i++){var s,o=t[i];this.currentModal.data.push({thumb:o.thumb,price:o.price,productprice:o.original_price,sales:1*o.sales,sub_name:o.sub_name,title:o.title,gid:o.id,id:o.id,bargain:0,credit:0,ctype:0,has_option:o.has_option,type:o.type,seckillData:o.seckillData||(null===o||void 0===o||null===(s=o.activitys)||void 0===s?void 0:s.seckill)||null})}this.refreshkey=Math.random(),"goods"==(null===(e=this.errorInfo)||void 0===e?void 0:e.id)&&this.$emit("validateGoodsForm")},replace:function(t){this.replaceIndex=t.index},delete:function(t){this.currentModal.data.splice(t.index,1)},addGood:function(){this.show=!0}}};e.default=r},"3f7b":function(t,e,a){"use strict";a("0b88")},"474b":function(t,e,a){"use strict";a.r(e);var i=a("d486"),s=a("9863");for(var o in s)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return s[t]}))}(o);a("df20");var n=a("2877"),r=Object(n["a"])(s["default"],i["a"],i["b"],!1,null,"2f6e3ede",null);e["default"]=r.exports},4784:function(t,e,a){var i=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var s=i(a("2909")),o=i(a("5530")),n=i(a("b85c"));a("a9e3"),a("a434"),a("4de4"),a("d3b7"),a("159b"),a("ac1f"),a("841c");var r={name:"goodsSelector",props:{current:{type:[String,Object],default:function(){}},currentList:{type:Array,default:function(){return[]}},value:{type:Boolean,default:!1},multiple:{type:Boolean,default:!1},limit:{type:Number},isStore:{type:Boolean,default:!1},params:{type:Object,default:function(){}},type:{type:String,default:""},flag:{type:String,default:""},show_activity:{type:[Number],default:0},activity_type:{type:String}},computed:{columns:function(){var t=this,e=(this.$createElement,[{title:"商品",key:"shop",width:450,render:function(e,a){var i;switch(a.row.type){case"0":i={goodsText:"实"};break;case"1":i={goodsText:"虚"};break;case"2":i={goodsText:"密"};break;case"3":i={goodsText:"预"};break}return e("div",{class:{"shop-box":!0}},[e("div",{class:{image:!0}},[e("img",{attrs:{src:t.$media(a.row.thumb)},on:{error:function(e){t.replaceImage(e)}}})]),e("div",{class:{content:!0}},[e("div",{class:{"content-text":!0}},[e("span",{class:{mark:!0},style:{display:"1"===a.row.has_option?"inline-block":"none"}},"多"),e("span",{class:{mark:!0,real:"0"===a.row.type,virtual:"1"===a.row.type,secret:"2"===a.row.type}},i.goodsText),e("span",{class:{text:!0}},a.row.title)])])])}},{title:"活动时间",slot:"date",minWidth:130,maxWidth:300},{title:"价格",key:"activity_price",minWidth:60,render:function(e,a){if("1"===a.row.has_option){var i=a.row.activitys[t.activity_type].price_range.min_price.length+a.row.activitys[t.activity_type].price_range.max_price.length;return i<=10?e("div",{class:"price",style:"white-space: pre-wrap;display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;"},[e("span",["￥",a.row.activitys[t.activity_type].price_range.min_price," ~ ￥",a.row.activitys[t.activity_type].price_range.max_price])]):e("div",{class:"price",style:"white-space: pre-wrap;"},[e("p",["￥",a.row.activitys[t.activity_type].price_range.min_price," "]),e("p",["~ ￥",a.row.activitys[t.activity_type].price_range.max_price])])}return e("div",{class:"price"},[e("span",["￥",a.row.activitys[t.activity_type][a.column.key]])])}},{title:"库存",key:"activity_stock",width:60,render:function(e,a){var i=parseInt(a.row.activitys[t.activity_type][a.column.key]);return"3"==a.row.type?e("div","-"):e("div",i||0===i?i<=999?i:"999+":"-")}},{title:"操作",key:"action",width:80,render:function(e,a){return e("div",{class:"action"},[e("Button",{class:"default-primary",style:{display:a.row.checked?"none":"block"},on:{click:function(){t.setChecked(a.index,!0)}}},["选择"]),e("Button",{attrs:{type:"primary"},style:{display:a.row.checked?"block":"none"},on:{click:function(){t.setChecked(a.index,!1)}}},["已选"])])}}]);return this.show_activity&&e.splice(-1,0,{title:"营销活动",key:"is_activity_goods",render:function(e,a){return e("div",{class:1===a.row.is_activity_goods?"brand-color pointer":"pointer",on:{click:function(){t.viewActivity(a.row)}}},[1===a.row.is_activity_goods?"查看参与的活动":"未参与活动"])}}),e}},data:function(){var t=this.$createElement;return{search:{keywords:"",group:""},goodsGroup:[],page:{pageSize:10,pageNumber:1},selectRows:[],selectRow:{},table:{data:[],loading:!1,total:0},activity:{show:!1,columns:[{title:"活动名称",key:"title"},{title:"活动状态",key:"status"},{title:"活动时间",key:"time"},{title:"活动类型",key:"type_text"},{title:"活动来源",key:"sub_shop_id",render:function(){return t("div",["平台"])}}],data:[]}}},methods:{handleSearch:function(){this.resetPage(),this.getShopList()},resetPage:function(){var t;this.page={pageSize:10,pageNumber:1},null===(t=this.$refs["page"])||void 0===t||t.reset()},handlePageChange:function(t){this.page=t,this.getShopList();try{this.$refs.scrollBox.scrollTop=0}catch(e){console.log(e)}},setChecked:function(t,e){var a=this;if(this.multiple){if(this.limit&&this.selectRows.length===this.limit&&e)return void this.$Message.error("已超出最大可选数量");this.$set(this.table.data[t],"checked",e),0===this.selectRows.length||e?this.selectRows.push(this.table.data[t]):this.selectRows=this.selectRows.filter((function(e){return e.id!==a.table.data[t].id}))}else e?(this.table.data.forEach((function(t,e){a.$set(a.table.data[e],"checked",!1)})),this.$set(this.table.data[t],"checked",!0),this.selectRow=this.table.data[t]):(this.$set(this.table.data[t],"checked",!1),this.selectRow={})},defaultChecked:function(){var t,e=this,a=this.isStore?"broadcast_goods_id":"id",i=this.isStore?"broadcast_goods_id":"id";this.multiple?this.table.data.forEach((function(t,s){var o,r=(0,n.default)(e.selectRows);try{for(r.s();!(o=r.n()).done;){var c=o.value;if(c[i]===t[a]){e.$set(e.table.data[s],"checked",!0);break}}}catch(l){r.e(l)}finally{r.f()}})):null===(t=this.table.data)||void 0===t||t.forEach((function(t,s){e.selectRow&&t[a]===e.selectRow[i]?e.$set(e.table.data[s],"checked",!0):e.$set(e.table.data[s],"checked",!1)}))},getShopList:function(){var t=this;if(this.isStore)this.getGoodsStore();else{this.table.loading=!0;var e=Object.assign((0,o.default)({page:this.page.pageNumber,pagesize:this.page.pageSize,type:this.type,is_merchant:1},this.params),this.search);this.$api.goodsApi.getActivityGoods(e).then((function(e){t.table.loading=!1,0===e.error&&(t.table.data=e.list,t.table.total=e.total,t.defaultChecked())})).catch()}},getGoodsStore:function(){var t=this;this.table.loading=!0;var e=Object.assign({page:this.page.pageNumber,pagesize:this.page.pageSize,bro_goods_status:2},{title:this.search.keywords});this.$api.liverApi.getGoodsStore(e).then((function(e){t.table.loading=!1,0===e.error&&(t.table.data=e.list,t.table.total=e.total,t.defaultChecked())})).catch()},goodGroupList:function(){var t=this;this.$api.goodsApi.goodGroupList({pager:0}).then((function(e){0===e.error?t.goodsGroup=e.list:t.$Message.error("商品组获取失败")}))},handleSave:function(){this.multiple?this.$emit("on-change",this.selectRows):this.$emit("on-change",this.selectRow),this.handleCancel()},handleCancel:function(){this.$emit("on-cancel")},resetSearch:function(){this.search={keywords:"",group:""}},viewActivity:function(t){console.log(t,"data>>>>>>>>viewActivity"),t.is_activity_goods&&(this.activity.show=!0,this.activity.data=t.join_activity)},getTime:function(t){return"0000-00-00 00:00:00"===t?"-":t}},watch:{value:{handler:function(t){t&&(this.multiple?this.selectRows=(0,s.default)(this.currentList)||[]:this.selectRow=this.current,this.resetSearch(),this.resetPage(),this.getShopList())},immediate:!0}}};e.default=r},"5af6":function(t,e,a){"use strict";a.r(e);var i=a("f434"),s=a("8bf0");for(var o in s)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return s[t]}))}(o);a("3f7b");var n=a("2877"),r=Object(n["a"])(s["default"],i["a"],i["b"],!1,null,"5f82fdec",null);e["default"]=r.exports},"8bf0":function(t,e,a){"use strict";a.r(e);var i=a("3a47"),s=a.n(i);for(var o in i)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(o);e["default"]=s.a},9863:function(t,e,a){"use strict";a.r(e);var i=a("4784"),s=a.n(i);for(var o in i)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(o);e["default"]=s.a},d486:function(t,e,a){"use strict";a.d(e,"a",(function(){return i})),a.d(e,"b",(function(){return s}));var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("kdx-modal-frame",{attrs:{value:t.value,title:"商品选择器",width:"1000","ok-text":"保存"},on:{"on-ok":t.handleSave,"on-cancel":t.handleCancel},scopedSlots:t._u([{key:"footer",fn:function(){return[t.activity.show?a("div",{staticClass:"btn-group"},[a("Button",{attrs:{type:"primary"},on:{click:function(e){t.activity.show=!1}}},[t._v("返回选择商品")])],1):t._e()]},proxy:!0}])},[a("div",{staticClass:"goods-preview"},[a("div",{ref:"scrollBox",staticClass:"goods-preview-content"},[t.activity.show?t._e():a("div",{staticClass:"search"},[a("Input",{staticClass:"width-250",attrs:{search:"","enter-button":"搜索",placeholder:"请输入"},on:{"on-search":t.handleSearch,"on-enter":t.handleSearch},model:{value:t.search.keywords,callback:function(e){t.$set(t.search,"keywords",e)},expression:"search.keywords"}})],1),t.activity.show?t._e():a("Table",{directives:[{name:"loading",rawName:"v-loading",value:t.table.loading,expression:"table.loading"}],ref:"table",attrs:{columns:t.columns,data:t.table.data},scopedSlots:t._u([{key:"date",fn:function(e){var i=e.row;return[a("div",{staticClass:"time"},[t._v("起："+t._s(t.getTime(i.activitys[t.activity_type].start_time)))]),a("div",{staticClass:"time"},[t._v("止："+t._s(t.getTime(i.activitys[t.activity_type].end_time)))])]}}],null,!1,3564124247)}),t.activity.show?a("Table",{ref:"activityTable",attrs:{columns:t.activity.columns,data:t.activity.data},scopedSlots:t._u([{key:"status",fn:function(e){var i=e.row;return[1===+i.status?a("kdx-status-text",{attrs:{type:"success"}},[t._v("进行中")]):t._e(),-1===+i.status?a("kdx-status-text",{attrs:{type:"danger"}},[t._v("已停止")]):t._e(),-2===+i.status?a("kdx-status-text",{attrs:{type:"danger"}},[t._v("手动停止")]):t._e(),0===+i.status?a("kdx-status-text",{attrs:{type:"warning"}},[t._v("未开始")]):t._e()]}},{key:"date",fn:function(e){var i=e.row;return[a("div",{staticClass:"time"},[t._v("起："+t._s(t.getTime(i.start_time)))]),a("div",{staticClass:"time"},[t._v("止："+t._s(t.getTime(i.end_time)))])]}}],null,!1,940490386)}):t._e()],1),a("div",{directives:[{name:"show",rawName:"v-show",value:t.table.total>10&&!t.activity.show,expression:"table.total > 10&&!activity.show"}],staticClass:"footer-page"},[a("kdx-page-component",{ref:"page",attrs:{total:t.table.total},on:{"on-change":t.handlePageChange}})],1)])])},s=[]},df20:function(t,e,a){"use strict";a("00e9")},f434:function(t,e,a){"use strict";a.d(e,"a",(function(){return i})),a.d(e,"b",(function(){return s}));var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"select-good1"},[a("Table",{key:t.refreshkey,attrs:{draggable:"",border:"",columns:t.columns,data:t.currentModal.data,size:"small"},on:{"on-drag-drop":t.draggeTable}}),t.currentModal.data&&t.currentModal.data.length<t.currentModal.params.goodsnum?a("div",{staticClass:"add",on:{click:t.addGood}},[t._v("+添加商品("+t._s(t.currentModal.data.length)+"/"+t._s(t.currentModal.params.goodsnum)+")")]):t._e(),a("goods-selector",{attrs:{params:t.goodsParams,multiple:"","current-list":t.currentModal.data,activity_type:"seckill"},on:{"on-cancel":t.handleCancel,"on-change":t.handleChange},model:{value:t.show,callback:function(e){t.show=e},expression:"show"}})],1)},s=[]}}]);