(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[77],{"136b":function(e,t,l){"use strict";l.d(t,"a",(function(){return a})),l.d(t,"b",(function(){return i}));var a=function(){var e=this,t=e.$createElement,l=e._self._c||t;return l("div",{staticClass:"good-add-limit"},[l("Form",{ref:"form",attrs:{model:e.$store.state.goodAddEdit.model,rules:e.rules,"label-width":120}},[l("div",{staticClass:"box"},[l("kdx-form-title",[e._v("商品浏览权限")]),l("FormItem",{staticClass:"r-form-item-checkbox",attrs:{label:"会员等级：",prop:"browse_level_perm"}},[l("RadioGroup",{model:{value:e.model_browse_level_perm,callback:function(t){e.model_browse_level_perm=t},expression:"model_browse_level_perm"}},[l("Radio",{attrs:{label:"0"}},[e._v("不限制会员等级")]),l("Radio",{attrs:{label:"1"}},[e._v("指定会员等级可见")])],1),l("kdx-hint-alert",{directives:[{name:"show",rawName:"v-show",value:"1"===e.model_browse_level_perm,expression:"model_browse_level_perm === '1'"}],staticClass:"alert"},[e._v(" 添加后，只有添加后的会员等级内的会员才可浏览该商品 ")]),"1"===e.model_browse_level_perm?l("div",{staticClass:"nest-box"},[l("FormItem",{attrs:{prop:"browse_level_perm_ids"}},[l("Button",{staticClass:"add-label ivu-normal brand",on:{click:function(t){return e.addLevel("browse")}}},[e._v("+添加")]),e.limitLabel.browse_level&&e.limitLabel.browse_level.length>0?l("div",{staticClass:"label-list"},e._l(e.limitLabel.browse_level,(function(t,a){return l("kdx-tag-label",{key:a,staticStyle:{margin:"0 10px 10px 0"},attrs:{type:"info",border:"",closable:!0},on:{"on-close":function(t){return e.closeLevel("browse",a)}}},[e._v(" "+e._s(t.level_name)+" ")])})),1):e._e()],1)],1):e._e()],1),l("FormItem",{staticClass:"r-form-item-checkbox",attrs:{label:"会员标签组：",prop:"browse_tag_perm"}},[l("RadioGroup",{model:{value:e.model_browse_tag_perm,callback:function(t){e.model_browse_tag_perm=t},expression:"model_browse_tag_perm"}},[l("Radio",{attrs:{label:"0"}},[e._v("不限制标签组")]),l("Radio",{attrs:{label:"1"}},[e._v("指定标签组可见")])],1),l("kdx-hint-alert",{directives:[{name:"show",rawName:"v-show",value:"1"===e.model_browse_tag_perm,expression:"model_browse_tag_perm === '1'"}],staticClass:"alert",attrs:{"show-icon":""}},[e._v(" 添加后，只有添加后的会员标签组内的会员才可浏览该商品 ")]),"1"===e.model_browse_tag_perm?l("div",{staticClass:"nest-box"},[l("FormItem",{attrs:{prop:"browse_tag_perm_ids"}},[l("Button",{staticClass:"add-label ivu-normal brand",on:{click:function(t){return e.addLabel("browse")}}},[e._v("+添加")]),e.limitLabel.browse_label&&e.limitLabel.browse_label.length>0?l("div",{staticClass:"label-list"},e._l(e.limitLabel.browse_label,(function(t,a){return l("kdx-tag-label",{key:a,staticStyle:{margin:"0 10px 10px 0"},attrs:{type:"warning",border:"",closable:!0},on:{"on-close":function(t){return e.closeLabel("browse",a)}}},[e._v(" "+e._s(t.group_name)+" ")])})),1):e._e()],1)],1):e._e()],1)],1),l("div",{staticClass:"box"},[l("kdx-form-title",[e._v(" 商品购买权限 ")]),l("FormItem",{staticClass:"r-form-item-checkbox",attrs:{label:"会员等级：",prop:"buy_level_perm"}},[l("RadioGroup",{model:{value:e.model_buy_level_perm,callback:function(t){e.model_buy_level_perm=t},expression:"model_buy_level_perm"}},[l("Radio",{attrs:{label:"0"}},[e._v("不限制会员等级")]),l("Radio",{attrs:{label:"1"}},[e._v("指定会员等级可购买")])],1),l("kdx-hint-alert",{directives:[{name:"show",rawName:"v-show",value:"1"===e.model_buy_level_perm,expression:"model_buy_level_perm === '1'"}],staticClass:"alert",attrs:{"show-icon":""}},[e._v(" 添加后，只有添加后的会员等级内的会员才可购买该商品 ")]),"1"===e.model_buy_level_perm?l("div",{staticClass:"nest-box"},[l("FormItem",{attrs:{prop:"buy_level_perm_ids"}},[l("Button",{staticClass:"add-label ivu-normal brand",on:{click:function(t){return e.addLevel("buy")}}},[e._v("+添加")]),e.limitLabel.buy_level&&e.limitLabel.buy_level.length>0?l("div",{staticClass:"label-list"},e._l(e.limitLabel.buy_level,(function(t,a){return l("kdx-tag-label",{key:a,staticStyle:{margin:"0 10px 10px 0"},attrs:{type:"info",border:"",closable:!0},on:{"on-close":function(t){return e.closeLevel("buy",a)}}},[e._v(" "+e._s(t.level_name)+" ")])})),1):e._e()],1)],1):e._e()],1),l("FormItem",{staticClass:"r-form-item-checkbox",attrs:{label:"会员标签组：",prop:"buy_tag_perm"}},[l("RadioGroup",{model:{value:e.model_buy_tag_perm,callback:function(t){e.model_buy_tag_perm=t},expression:"model_buy_tag_perm"}},[l("Radio",{attrs:{label:"0"}},[e._v("不限制标签组")]),l("Radio",{attrs:{label:"1"}},[e._v("指定标签组可购买")])],1),l("kdx-hint-alert",{directives:[{name:"show",rawName:"v-show",value:"1"===e.model_buy_tag_perm,expression:"model_buy_tag_perm === '1'"}],staticClass:"alert",attrs:{"show-icon":""}},[e._v(" 添加后，只有添加后的会员标签组内的会员才可购买该商品 ")]),"1"===e.model_buy_tag_perm?l("div",{staticClass:"nest-box"},[l("FormItem",{attrs:{prop:"buy_tag_perm_ids"}},[l("Button",{staticClass:"add-label ivu-normal brand",on:{click:function(t){return e.addLabel("buy")}}},[e._v("+添加")]),e.limitLabel.buy_label&&e.limitLabel.buy_label.length>0?l("div",{staticClass:"label-list"},e._l(e.limitLabel.buy_label,(function(t,a){return l("kdx-tag-label",{key:a,staticStyle:{margin:"0 10px 10px 0"},attrs:{type:"warning",border:"",closable:!0},on:{"on-close":function(t){return e.closeLabel("buy",a)}}},[e._v(" "+e._s(t.group_name)+" ")])})),1):e._e()],1)],1):e._e()],1)],1)]),l("label-selector",{ref:"label_modal",attrs:{"current-list":e.label.list},on:{"on-change":e.changeLabel}}),l("level-selector",{ref:"level_modal",attrs:{"current-list":e.level.list},on:{"on-change":e.changeLevel}})],1)},i=[]},"1c3a":function(e,t,l){var a=l("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,l("e9c4"),l("d3b7"),l("159b"),l("a434");var i=a(l("b85c")),s=a(l("2909")),n={name:"labelSelector",props:{currentList:{type:Array,default:function(){return[]}}},data:function(){return{value:!1,selectRows:[],list:[],loading:!1}},methods:{setValue:function(){this.value=!this.value,this.value&&(this.selectRows=(0,s.default)(this.currentList),this.getLabelData())},handleCancel:function(){this.setValue()},handleOk:function(){this.$emit("on-change",JSON.parse(JSON.stringify(this.selectRows))),this.setValue()},initDefaultChecked:function(){var e=this;this.selectRows.length>0&&this.list.forEach((function(t,l){var a,s=(0,i.default)(e.selectRows);try{for(s.s();!(a=s.n()).done;){var n=a.value;if(t.id===n.id){e.$set(e.list[l],"checked",!0);break}}}catch(r){s.e(r)}finally{s.f()}}))},setLabelChecked:function(e,t){var l=this;if(this.$set(this.list[e],"checked",t),t)this.selectRows.push(this.list[e]);else{var a=-1;this.selectRows.forEach((function(t,i){t.id===l.list[e].id&&(a=i)})),this.selectRows.splice(a,1)}},getLabelData:function(){var e=this;this.loading=!0,this.$api.memberApi.getGroupList({is_all:1}).then((function(t){e.loading=!1,0===t.error&&(e.list=t.list||[],e.initDefaultChecked())}))}}};t.default=n},"1e22":function(e,t,l){"use strict";l.r(t);var a=l("1c3a"),i=l.n(a);for(var s in a)["default"].indexOf(s)<0&&function(e){l.d(t,e,(function(){return a[e]}))}(s);t["default"]=i.a},"1ea7":function(e,t,l){"use strict";l.d(t,"a",(function(){return a})),l.d(t,"b",(function(){return i}));var a=function(){var e=this,t=e.$createElement,l=e._self._c||t;return l("kdx-modal-frame",{attrs:{title:"会员等级标签",loading:e.loading,"class-name":"shop-label-modal"},on:{"on-cancel":e.handleCancel,"on-ok":e.handleOk},model:{value:e.value,callback:function(t){e.value=t},expression:"value"}},[l("div",{staticClass:"shop-label"},[l("div",{staticClass:"custom"},[e.list.length>0?l("div",{staticClass:"recommend"},[l("div",{staticClass:"label-content"},e._l(e.list,(function(t,a){return l("div",{key:a,staticClass:"shop-label-item",class:{checked:t.checked},on:{click:function(l){return e.setLabelChecked(a,!t.checked)}}},[l("span",[e._v(" "+e._s(t.level_name))]),l("kdx-svg-icon",{staticClass:"icon",attrs:{type:"icon-chenggong-shixin"}})],1)})),0)]):l("div",{staticStyle:{"text-align":"center"}},[e._v(" 暂无数据 ")])])])])},i=[]},"1ff9":function(e,t,l){"use strict";l.r(t);var a=l("96f6"),i=l.n(a);for(var s in a)["default"].indexOf(s)<0&&function(e){l.d(t,e,(function(){return a[e]}))}(s);t["default"]=i.a},"389d":function(e,t,l){"use strict";l.r(t);var a=l("b20d"),i=l.n(a);for(var s in a)["default"].indexOf(s)<0&&function(e){l.d(t,e,(function(){return a[e]}))}(s);t["default"]=i.a},"546c":function(e,t,l){"use strict";l("8935")},"5c5b":function(e,t,l){},"83b0":function(e,t,l){"use strict";l.r(t);var a=l("1ea7"),i=l("389d");for(var s in i)["default"].indexOf(s)<0&&function(e){l.d(t,e,(function(){return i[e]}))}(s);l("eec44");var n=l("2877"),r=Object(n["a"])(i["default"],a["a"],a["b"],!1,null,"d1a95214",null);t["default"]=r.exports},8935:function(e,t,l){},"8a81":function(e,t,l){"use strict";l.d(t,"a",(function(){return a})),l.d(t,"b",(function(){return i}));var a=function(){var e=this,t=e.$createElement,l=e._self._c||t;return l("kdx-modal-frame",{attrs:{title:"会员标签组",loading:e.loading,"class-name":"shop-label-modal"},on:{"on-cancel":e.handleCancel,"on-ok":e.handleOk},model:{value:e.value,callback:function(t){e.value=t},expression:"value"}},[l("div",{staticClass:"shop-label"},[l("div",{staticClass:"custom"},[e.list.length>0?l("div",{staticClass:"recommend"},[l("div",{staticClass:"label-content"},e._l(e.list,(function(t,a){return l("div",{key:a,staticClass:"shop-label-item",class:{checked:t.checked},on:{click:function(l){return e.setLabelChecked(a,!t.checked)}}},[l("span",[e._v(" "+e._s(t.group_name))]),l("kdx-svg-icon",{staticClass:"icon",attrs:{type:"icon-chenggong-shixin-y"}})],1)})),0)]):l("div",{staticStyle:{"text-align":"center"}},[e._v(" 暂无数据 ")])])])])},i=[]},"96f6":function(e,t,l){var a=l("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,l("d81d"),l("a434"),l("d3b7");var i=a(l("5530")),s=a(l("c35a")),n=a(l("83b0")),r=l("8812"),o=l("2f62"),c={name:"index",components:{LabelSelector:s.default,LevelSelector:n.default},computed:(0,i.default)((0,i.default)({},(0,r.modelMap)()),(0,o.mapState)("goodAddEdit",{limitLabel:function(e){return e.limitLabel}})),data:function(){return{type:"",label:{list:[],type:""},level:{list:[],type:""},rules:{browse_level_perm_ids:[{required:!0,type:"array",message:"商品浏览权限会员等级标签必选"}],browse_tag_perm_ids:[{required:!0,type:"array",message:"商品浏览权限会员标签组必填"}],buy_level_perm_ids:[{required:!0,type:"array",message:"商品购买权限会员等级标签必填"}],buy_tag_perm_ids:[{required:!0,type:"array",message:"商品购买权限会员标签组必填"}],"ext_field.single_max_buy":[{required:!0,message:"单次最多购买必填"}],"ext_field.single_min_buy":[{required:!0,message:"单次最少购买必填"}],"ext_field.max_buy":[{required:!0,message:"总共可购买必填"}]}}},methods:{addLevel:function(e){var t=this;this.level.type=e,"browse"===e?this.level.list=this.limitLabel.browse_level:"buy"===e&&(this.level.list=this.limitLabel.buy_level),this.$nextTick((function(){t.$refs["level_modal"].setValue()}))},addLabel:function(e){var t=this;this.label.type=e,"browse"===e?this.label.list=this.limitLabel.browse_label:"buy"===e&&(this.label.list=this.limitLabel.buy_label),this.$nextTick((function(){t.$refs["label_modal"].setValue()}))},changeLabel:function(e){var t=e.map((function(e){return e.id}));"browse"===this.label.type?(this.limitLabel.browse_label=e,this.model_browse_tag_perm_ids=t):"buy"===this.label.type&&(this.limitLabel.buy_label=e,this.model_buy_tag_perm_ids=t)},changeLevel:function(e){var t=e.map((function(e){return e.id}));"browse"===this.level.type?(this.limitLabel.browse_level=e,this.model_browse_level_perm_ids=t):"buy"===this.level.type&&(this.limitLabel.buy_level=e,this.model_buy_level_perm_ids=t)},closeLevel:function(e,t){var l=this;this.$Modal.confirm({title:"提示",content:"是否删除该会员等级",onOk:function(){"browse"===e?(l.limitLabel.browse_level.splice(t,1),l.model_browse_level_perm_ids.splice(t,1)):"buy"===e&&(l.limitLabel.buy_level.splice(t,1),l.model_buy_level_perm_ids.splice(t,1))},onCancel:function(){}})},closeLabel:function(e,t){var l=this;this.$Modal.confirm({title:"提示",content:"是否删除该会员标签组",onOk:function(){"browse"===e?(l.limitLabel.browse_label.splice(t,1),l.model_browse_tag_perm_ids.splice(t,1)):"buy"===e&&(l.limitLabel.buy_label.splice(t,1),l.model_buy_tag_perm_ids.splice(t,1))},onCancel:function(){}})},validate:function(){var e=this;return new Promise((function(t){e.$refs["form"].validate((function(e){t(e)}))}))}}};t.default=c},"9b6f":function(e,t,l){"use strict";l.r(t);var a=l("136b"),i=l("1ff9");for(var s in i)["default"].indexOf(s)<0&&function(e){l.d(t,e,(function(){return i[e]}))}(s);l("9bc9");var n=l("2877"),r=Object(n["a"])(i["default"],a["a"],a["b"],!1,null,"018d4df4",null);t["default"]=r.exports},"9bc9":function(e,t,l){"use strict";l("f50a")},b20d:function(e,t,l){var a=l("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,l("e9c4"),l("d3b7"),l("159b"),l("a434");var i=a(l("b85c")),s=a(l("2909")),n={name:"levelSelector",props:{currentList:{type:Array,default:function(){return[]}}},data:function(){return{value:!1,selectRows:[],list:[],loading:!1}},methods:{setValue:function(){this.value=!this.value,this.value&&(this.selectRows=(0,s.default)(this.currentList)||[],this.getLabelData())},handleCancel:function(){this.setValue()},handleOk:function(){this.$emit("on-change",JSON.parse(JSON.stringify(this.selectRows))),this.setValue()},initDefaultChecked:function(){var e=this;this.currentList.length>0&&this.list.forEach((function(t,l){var a,s=(0,i.default)(e.selectRows);try{for(s.s();!(a=s.n()).done;){var n=a.value;if(t.id===n.id){e.$set(e.list[l],"checked",!0);break}}}catch(r){s.e(r)}finally{s.f()}}))},setLabelChecked:function(e,t){var l=this;if(this.$set(this.list[e],"checked",t),t)this.selectRows.push(this.list[e]);else{var a=-1;this.selectRows.forEach((function(t,i){t.id===l.list[e].id&&(a=i)})),this.selectRows.splice(a,1)}},getLabelData:function(){var e=this;this.loading=!0,this.$api.memberApi.getLevelList({is_all:1}).then((function(t){e.loading=!1,0===t.error&&(e.list=t.list||[],e.initDefaultChecked())}))}}};t.default=n},c35a:function(e,t,l){"use strict";l.r(t);var a=l("8a81"),i=l("1e22");for(var s in i)["default"].indexOf(s)<0&&function(e){l.d(t,e,(function(){return i[e]}))}(s);l("546c");var n=l("2877"),r=Object(n["a"])(i["default"],a["a"],a["b"],!1,null,"0360cf99",null);t["default"]=r.exports},eec44:function(e,t,l){"use strict";l("5c5b")},f50a:function(e,t,l){}}]);