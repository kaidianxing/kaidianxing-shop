(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[117],{"4f90":function(t,e,o){"use strict";o.d(e,"a",(function(){return s})),o.d(e,"b",(function(){return n}));var s=function(){var t=this,e=t.$createElement,o=t._self._c||e;return o("div",[o("FormItem",{attrs:{label:"购买指定商品：","label-width":120,prop:"commission_level"}},[o("Button",{staticClass:"simple-btn",staticStyle:{"margin-bottom":"10px"},style:{color:t.noManagePerm?"":t.$css["--theme-color"]},attrs:{disabled:t.noManagePerm},on:{click:t.addGood}},[t._v("+添加指定商品")]),o("div",{directives:[{name:"show",rawName:"v-show",value:t.goodsList.length>0,expression:"goodsList.length > 0"}],staticClass:"goods-list"},[o("shop-name-page-list",{ref:"shop_name_list",attrs:{list:t.goodsList},on:{"on-delete":t.deleteGoods}})],1),o("goods-selector",{attrs:{multiple:"",limit:5,currentList:t.goodsList},on:{"on-cancel":t.handleCancel,"on-change":t.handleChange},model:{value:t.show,callback:function(e){t.show=e},expression:"show"}})],1),o("FormItem",{staticStyle:{"margin-bottom":"0"},attrs:{label:"统计方式：","label-width":120,prop:"commission_level"}},[o("MyRadioGroup",{attrs:{items:t.type},model:{value:t.settings.become_order_status,callback:function(e){t.$set(t.settings,"become_order_status",e)},expression:"settings.become_order_status"}})],1)],1)},n=[]},"687c":function(t,e,o){var s=o("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,o("d81d"),o("a15b"),o("a434");var n=s(o("2909")),i=s(o("5530")),a=s(o("5471")),d=s(o("416b")),r=s(o("0659")),l=o("2f62"),u={components:{GoodsSelector:r.default,MyRadioGroup:a.default,ShopNamePageList:d.default},computed:(0,i.default)((0,i.default)({},(0,l.mapState)("commission/settings",{goodsList:function(t){return t.goodsList},settings:function(t){return t.settings}})),{},{noManagePerm:function(){return!this.$getPermMap("commission.settings.commission.manage")},type:function(){var t=this;return[{label:"订单付款后",id:"1"},{label:"订单完成后",id:"2"}].map((function(e){return e.disabled=t.noManagePerm,e}))}}),data:function(){return{show:!1}},methods:(0,i.default)((0,i.default)((0,i.default)({},(0,l.mapMutations)("commission/settings",["setGoodsList"])),(0,l.mapActions)("commission/settings",["getGoodsList"])),{},{addGood:function(){this.show=!0},handleCancel:function(){this.show=!1},handleChange:function(t){var e=t.map((function(t){return t.id}));this.settings.become_goods_ids=e.join(","),this.setGoodsList(t),this.show=!1},deleteGoods:function(t){var e=(0,n.default)(this.goodsList);e.splice(t,1),this.setGoodsList(e)}})};e.default=u},ad2f:function(t,e,o){},b835:function(t,e,o){"use strict";o.r(e);var s=o("687c"),n=o.n(s);for(var i in s)["default"].indexOf(i)<0&&function(t){o.d(e,t,(function(){return s[t]}))}(i);e["default"]=n.a},e8ba:function(t,e,o){"use strict";o("ad2f")},ec4b:function(t,e,o){"use strict";o.r(e);var s=o("4f90"),n=o("b835");for(var i in n)["default"].indexOf(i)<0&&function(t){o.d(e,t,(function(){return n[t]}))}(i);o("e8ba");var a=o("2877"),d=Object(a["a"])(n["default"],s["a"],s["b"],!1,null,"224ee3b8",null);e["default"]=d.exports}}]);