(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[109],{2492:function(t,e,a){"use strict";a("2617")},2617:function(t,e,a){},3696:function(t,e,a){"use strict";a.r(e);var n=a("464c"),i=a("e0cc");for(var o in i)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return i[t]}))}(o);a("2492");var s=a("2877"),r=Object(s["a"])(i["default"],n["a"],n["b"],!1,null,"3cd139f8",null);e["default"]=r.exports},"464c":function(t,e,a){"use strict";a.d(e,"a",(function(){return n})),a.d(e,"b",(function(){return i}));var n=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"good-add-param"},[n("div",{staticClass:"box"},[n("div",{staticClass:"edit"},[n("div",{staticClass:"title"},[n("span",[t._v("商品参数编辑：")]),n("RadioGroup",{model:{value:t.model_params_switch,callback:function(e){t.model_params_switch=e},expression:"model_params_switch"}},[n("Radio",{attrs:{label:"1"}},[t._v("开启")]),n("Radio",{attrs:{label:"0"}},[t._v("关闭")])],1)],1),"1"===t.model_params_switch?n("div",[n("Table",{attrs:{columns:t.columns,data:t.model_params,border:"",draggable:""},on:{"on-drag-drop":t.dragSort}}),n("Button",{staticClass:"brand",on:{click:t.addSpecification}},[t._v("+添加参数")])],1):t._e()]),"1"===t.model_params_switch?n("div",{staticClass:"preview"},[n("p",{staticClass:"title"},[t._v(" 商品参数预览 "),n("kdx-hint-tooltip",{attrs:{type:"image",image:a("e76c")}})],1),n("div",{staticClass:"page"},[t._m(0),n("div",{staticClass:"content"},[t.model_params.length>0?n("table",{attrs:{border:"0",cellspacing:"0"}},t._l(t.model_params,(function(e,a){return n("tr",{key:a},[n("td",[t._v(t._s(e.key))]),n("td",[t._v(t._s(e.value))])])})),0):t._e()]),t._m(1)])]):t._e()])])},i=[function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"img-header"},[n("img",{attrs:{src:a("a078"),alt:""}})])},function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"img-footer"},[n("img",{attrs:{src:a("d193"),alt:""}})])}]},a078:function(t,e,a){t.exports=a.p+"static/dist/shop/img/top-param.png"},d193:function(t,e,a){t.exports=a.p+"static/dist/shop/img/foot_tab.png"},d31a:function(t,e,a){var n=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,a("a434");var i=n(a("2909")),o=n(a("5530")),s=a("8812"),r=n(a("aa47")),l={name:"index",computed:(0,o.default)({},(0,s.modelMap)()),data:function(){var t=this;return{columns:[{title:" ",key:"icon",align:"center",width:40,render:function(t){return t("Icon",{props:{type:"ios-menu"},style:{cursor:"move"}})}},{title:"参数名称",key:"key",render:function(e,a){return e("div",{class:{"edit-table-input":!0}},[e("Input",{props:{value:a.row[a.column.key],placeholder:"输入参数名称",maxlength:10},style:{width:"100%"},on:{input:function(e){a.row[a.column.key]=e,t.setTableInput(a.index,a.column.key,e)}}})])}},{title:"参数值",key:"value",render:function(e,a){return e("div",[e("Input",{props:{value:a.row[a.column.key],placeholder:"输入参数值",maxlength:200},style:{width:"100%"},on:{input:function(e){a.row[a.column.key]=e,t.setTableInput(a.index,a.column.key,e)}}})])}},{title:"操作",key:"action",align:"center",width:80,render:function(e,a){return e("div",[e("Button",{props:{type:"text"},on:{click:function(){t.remove(a.index)}}},"删除")])}}],item:{key:"",value:""}}},methods:{dragSort:function(t,e){var a=this.model_params[t];this.model_params.splice(t,1),this.model_params.splice(e,0,a)},remove:function(t){var e=this;this.$Modal.confirm({title:"提示",content:"确认删除?",onOk:function(){e.model_params.splice(t,1)},onCancel:function(){}})},addSpecification:function(){this.model_params.push(Object.assign({},this.item))},setTableInput:function(t,e,a){this.model_params[t][e]=a,this.model_params=(0,i.default)(this.model_params)},initSortable:function(){var t=document.querySelectorAll(".ivu-table-tbody")[0];t&&new r.default(t,{handle:".ivu-icon",onEnd:this.endSortable})},endSortable:function(t){var e=this.model_params[t.oldIndex];this.model_params.splice(t.oldIndex,1),this.model_params.splice(t.newIndex,0,e)}},mounted:function(){this.initSortable()}};e.default=l},e0cc:function(t,e,a){"use strict";a.r(e);var n=a("d31a"),i=a.n(n);for(var o in n)["default"].indexOf(o)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(o);e["default"]=i.a},e76c:function(t,e,a){t.exports=a.p+"static/dist/shop/img/shop_params.png"}}]);