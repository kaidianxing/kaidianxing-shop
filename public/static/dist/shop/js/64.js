(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[64],{"164f":function(e,t,a){},"1e54":function(e,t,a){"use strict";a.r(t);var n=a("5ae2"),i=a.n(n);for(var s in n)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(s);t["default"]=i.a},4298:function(e,t,a){"use strict";a.d(t,"a",(function(){return n})),a.d(t,"b",(function(){return i}));var n=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("kdx-content-bar",{attrs:{loading:e.loading}},[a("div",{staticClass:"shop-menu-setting"},[a("kdx-form-title",[e._v("菜单设置")]),a("menu-setting-list",{attrs:{type:"replace",data:e.menu.data},on:{"on-replace":e.replaceMenu}}),a("menu-selector",{ref:"menu_selector",attrs:{current:e.current},on:{"on-change":e.changeMenu}})],1)])},i=[]},"4edc":function(e,t,a){"use strict";a.r(t);var n=a("4298"),i=a("1e54");for(var s in i)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return i[e]}))}(s);a("c5c1");var r=a("2877"),c=Object(r["a"])(i["default"],n["a"],n["b"],!1,null,"414a2fe8",null);t["default"]=c.exports},"5ae2":function(e,t,a){var n=a("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,a("4de4"),a("d3b7");var i=n(a("961b")),s=n(a("6043")),r={name:"index",components:{MenuSettingList:i.default,MenuSelector:s.default},data:function(){return{menu:{data:[]},loading:!1,current:{},type:""}},methods:{replaceMenu:function(e){var t=this;this.type=e.type,this.current=e,this.$nextTick((function(){t.$refs["menu_selector"].setValue()}))},changeMenu:function(e){var t=this;this.$api.shopApi.replaceMenu({type:this.type,id:e.id}).then((function(e){0===e.error&&(t.$Message.success("菜单替换成功"),t.getList())}))},getList:function(){var e=this;this.loading=!0,this.$api.shopApi.getMenuList().then((function(t){e.loading=!1,0===t.error&&(e.menu.data=t.list.filter((function(e){return"1"==e.is_used})))}))}},mounted:function(){this.getList()}};t.default=r},6043:function(e,t,a){"use strict";a.r(t);var n=a("9c7f"),i=a("6a2a");for(var s in i)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return i[e]}))}(s);a("cb69");var r=a("2877"),c=Object(r["a"])(i["default"],n["a"],n["b"],!1,null,"b42a055a",null);t["default"]=c.exports},"6a2a":function(e,t,a){"use strict";a.r(t);var n=a("ef45"),i=a.n(n);for(var s in n)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(s);t["default"]=i.a},"961b":function(e,t,a){"use strict";a.r(t);var n=a("f65b"),i=a("ed53");for(var s in i)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return i[e]}))}(s);a("d531");var r=a("2877"),c=Object(r["a"])(i["default"],n["a"],n["b"],!1,null,"60cb30f7",null);t["default"]=c.exports},"9c7f":function(e,t,a){"use strict";a.d(t,"a",(function(){return n})),a.d(t,"b",(function(){return i}));var n=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("kdx-modal-frame",{attrs:{title:"菜单选择器",width:810},on:{"on-ok":e.handleOk,"on-cancel":e.handleCancel},model:{value:e.value,callback:function(t){e.value=t},expression:"value"}},[a("div",{staticClass:"menu-selector"},[a("div",{ref:"scrollBox",staticClass:"menu-selector-content"},[a("div",{staticClass:"search"},[a("Input",{staticClass:"width-250",attrs:{placeholder:"请输入",search:"","enter-button":"搜索"},on:{"on-search":e.handleSearch},model:{value:e.search.keywords,callback:function(t){e.$set(e.search,"keywords",t)},expression:"search.keywords"}})],1),a("Table",{directives:[{name:"loading",rawName:"v-loading",value:e.loading,expression:"loading"}],ref:"table",attrs:{columns:e.columns,data:e.data}})],1),a("div",{directives:[{name:"show",rawName:"v-show",value:e.total>10,expression:"total > 10"}],staticClass:"footer-page"},[a("kdx-page-component",{ref:"page",attrs:{total:e.total},on:{"on-change":e.handlePageChange}})],1)])])},i=[]},a074:function(e,t,a){},b400:function(e,t){Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a={name:"MenuSettingList",props:{type:{type:String,default:"edit"},data:{type:Array,default:function(){return[]}}},methods:{replacePage:function(e){this.$emit("on-replace",e)}}};t.default=a},c4fb:function(e,t,a){},c5c1:function(e,t,a){"use strict";a("164f")},cb69:function(e,t,a){"use strict";a("a074")},d531:function(e,t,a){"use strict";a("c4fb")},ed53:function(e,t,a){"use strict";a.r(t);var n=a("b400"),i=a.n(n);for(var s in n)["default"].indexOf(s)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(s);t["default"]=i.a},ef45:function(e,t,a){var n=a("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(a("b85c")),s=n(a("2909"));a("a9e3"),a("ac1f"),a("841c"),a("4de4"),a("d3b7"),a("159b");var r={name:"MenuSelector",props:{current:{type:[String,Object],default:function(){}},currentList:{type:Array,default:function(){return[]}},multiple:{type:Boolean,default:!1},type:{type:[String,Number],default:""}},data:function(){var e=this;this.$createElement;return{value:!1,search:{keywords:""},columns:[{title:"菜单名称",key:"name",render:function(e,t){return e("div",{class:"menu-name"},[e("span",[t.row[t.column.key]])])}},{title:"修改时间",key:"updated_at",width:180,render:function(e,t){return"0000-00-00 00:00:00"==t.row.updated_at?e("div",[t.row.created_at]):e("div",[t.row.updated_at])}},{title:"预览",key:"thumb",width:200,render:function(t,a){return t("div",{class:"preview"},[t("img",{attrs:{src:e.$utils.media(a.row[a.column.key]),alt:""}})])}},{title:"操作",key:"action",width:80,render:function(t,a){return t("div",{class:"action"},[t("Button",{class:"default-primary",style:{display:a.row.checked?"none":"block"},on:{click:function(){e.setChecked(a.index,!0)}}},["选择"]),t("Button",{attrs:{type:"primary"},style:{display:a.row.checked?"block":"none"},on:{click:function(){e.setChecked(a.index,!1)}}},["已选"])])}}],data:[],total:0,page:{pageSize:10,pageNumber:1},selectRow:{},selectRows:[],loading:!1}},methods:{setValue:function(){this.value=!this.value,this.value&&(this.multiple?this.selectRows=(0,s.default)(this.currentList):(this.selectRow=this.current,this.search.keywords="",this.resetSearch(),this.resetPage(),this.getList()))},handleSearch:function(){this.resetPage(),this.getList()},resetSearch:function(){this.search={keywords:""}},resetPage:function(){var e;this.page={pageSize:10,pageNumber:1},null===(e=this.$refs["page"])||void 0===e||e.reset()},setChecked:function(e,t){var a=this;this.multiple?(this.$set(this.data[e],"checked",t),0===this.selectRows.length||t?this.selectRows.push(this.data[e]):this.selectRows=this.selectRows.filter((function(t){return t.id!==a.data[e].id}))):(this.data.forEach((function(e,t){a.$set(a.data[t],"checked",!1)})),this.$set(this.data[e],"checked",t),this.selectRow=t?this.data[e]:{})},defaultChecked:function(){var e=this;this.multiple?this.data.forEach((function(t,a){var n,s=(0,i.default)(e.selectRows);try{for(s.s();!(n=s.n()).done;){var r=n.value;if(r.id===t.id){e.$set(e.data[a],"checked",!0);break}}}catch(c){s.e(c)}finally{s.f()}})):this.data.forEach((function(t,a){e.selectRow&&t.id===e.selectRow.id?e.$set(e.data[a],"checked",!0):e.$set(e.data[a],"checked",!1)}))},handlePageChange:function(e){this.page=e;try{this.$refs.scrollBox.scrollTop=0}catch(t){console.log(t)}this.getList()},handleOk:function(){this.multiple?this.$emit("on-change",this.selectRows):this.$emit("on-change",this.selectRow),this.setValue()},handleCancel:function(){this.setValue()},getList:function(){var e=this;this.loading=!0;var t=Object.assign({pagesize:this.page.pageSize,page:this.page.pageNumber,type:this.type},this.search);this.$api.shopApi.getNewMenuList(t).then((function(t){e.loading=!1,0===t.error&&(e.data=t.list||[],e.total=t.total,e.defaultChecked())}))}}};t.default=r},f65b:function(e,t,a){"use strict";a.d(t,"a",(function(){return n})),a.d(t,"b",(function(){return i}));var n=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"shop-app-page"},[a("ul",e._l(e.data,(function(t,n){return a("li",{key:n},["1"==t.type?a("div",{staticClass:"page-label shouye"},[a("kdx-svg-icon",{attrs:{type:"icon-shouye1"}}),a("span",[e._v("商城菜单")])],1):"2"==t.type?a("div",{staticClass:"page-label fenxiao"},[a("kdx-svg-icon",{attrs:{type:"icon-fenxiao"}}),a("span",[e._v("分销菜单")])],1):"3"==t.type?a("div",{staticClass:"page-label"},[a("span",[e._v("自定义菜单")])]):e._e(),a("div",{staticClass:"page-title"},[e._v(" "+e._s(t.name)+" ")]),a("div",{staticClass:"update-time"},[e._v(" 修改时间："+e._s("0000-00-00 00:00:00"===t.updated_at?t.created_at:t.updated_at)+" ")]),a("div",{staticClass:"menu-image"},[a("img",{attrs:{src:t.thumbnail,alt:""}})]),a("Button",{staticClass:"default-primary",on:{click:function(a){return e.replacePage(t)}}},[e._v(" 替换 ")])],1)})),0)])},i=[]}}]);