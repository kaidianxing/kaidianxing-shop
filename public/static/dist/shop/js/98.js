(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[98],{"0b92":function(e,t,i){"use strict";i("f401f")},"1bf7":function(e,t,i){"use strict";i.d(t,"a",(function(){return a})),i.d(t,"b",(function(){return n}));var a=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("div",{staticClass:"radio-group"},[i("RadioGroup",{attrs:{type:e.type,vertical:e.vertical},on:{"on-change":e.onChange},model:{value:e.selected,callback:function(t){e.selected=t},expression:"selected"}},e._l(e.items,(function(t,a){return i("Radio",{key:a,attrs:{label:a,disabled:t.disabled||e.disabled}},[e.$slots.example?e._e():i("span",[e._v(" "+e._s(t.label)+" ")]),e._t("example",null,{example:t})],2)})),1),e.$slots.tip&&e.$slots.tip.length?i("div",{staticClass:"tip"},[e._t("tip")],2):e._e(),e.$slots.default&&e.$slots.default.length?i("div",{staticClass:"content"},[e._t("default")],2):e._e()],1)},n=[]},5471:function(e,t,i){"use strict";i.r(t);var a=i("1bf7"),n=i("78d8");for(var l in n)["default"].indexOf(l)<0&&function(e){i.d(t,e,(function(){return n[e]}))}(l);i("8c2e");var s=i("2877"),r=Object(s["a"])(n["default"],a["a"],a["b"],!1,null,"4f72f8de",null);t["default"]=r.exports},5720:function(e,t,i){},6523:function(e,t,i){var a=i("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,i("e9c4"),i("d3b7");var n=a(i("5471")),l={components:{MyRadioGroup:n.default},data:function(){return{limits:[{label:"不限制",id:0},{label:"自定义",id:1}],rules:{},model:{enable:1,min_withdraw_price:0,withdraw_limit:0,withdraw_type:[20]}}},computed:{noManagePerm:function(){return!this.$getPermMap("merchant_sysset.commission.manage")}},mounted:function(){var e=this;this.$api.settingApi.getExtentsionSettings().then((function(t){0==t.error&&(e.model=t.settings)}))},methods:{changeWithdrawType:function(e){console.log(e)},submitData:function(){var e=this;this.$api.settingApi.saveExtentsionSettings(this.model).then((function(t){0==t.error&&e.$Message.success("保存成功")}))},validate:function(){var e=this,t=JSON.parse(JSON.stringify(this.model));return new Promise((function(i,a){e.$refs.form.validate((function(e){e?("0"==t.alipay.enable&&delete t.alipay.id,"0"==t.wechat.enable&&(delete t.wechat.wechat,delete t.wechat.wxapp),i(t)):a(!1)}))}))}}};t.default=l},"78d8":function(e,t,i){"use strict";i.r(t);var a=i("bdc7"),n=i.n(a);for(var l in a)["default"].indexOf(l)<0&&function(e){i.d(t,e,(function(){return a[e]}))}(l);t["default"]=n.a},"8c2e":function(e,t,i){"use strict";i("5720")},b16bb:function(e,t,i){"use strict";i.r(t);var a=i("6523"),n=i.n(a);for(var l in a)["default"].indexOf(l)<0&&function(e){i.d(t,e,(function(){return a[e]}))}(l);t["default"]=n.a},bdc7:function(e,t,i){Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,i("a9e3");var a={props:{vertical:{type:Boolean,default:!1},value:{type:[String,Number],default:""},disabled:{type:Boolean,default:!1},lazy:{type:Boolean,default:!1},items:{type:Array,default:function(){return[{label:"label1",iconType:"",disabled:!1},{label:"label2",iconType:"",disabled:!1}]}},type:{type:String,default:void 0}},watch:{value:{immediate:!0,handler:function(){this.selected="-1";for(var e=0;e<this.items.length;e++)this.items[e].id==this.value&&(this.selected=e);if("-1"==this.selected)for(var t=0;t<this.items.length;t++)""===this.items[t].id&&(this.selected=t);this.lastSelected=this.selected}}},data:function(){return{selected:"",lastSelected:null}},methods:{onChange:function(e){var t=this;if(this.lazy)if(null!==this.lastSelected)this.$nextTick((function(){t.selected=t.lastSelected;var i=t.items[e].id;t.$emit("input",i),t.$emit("change",i,t.items[e])}));else{this.lastSelected=this.selected;var i=this.items[this.selected].id;this.$emit("input",i),this.$emit("change",i,this.items[this.selected])}else{var a=this.items[this.selected].id;this.$emit("input",a),this.$emit("change",a,this.items[this.selected])}}}};t.default=a},cca4:function(e,t,i){"use strict";i.d(t,"a",(function(){return a})),i.d(t,"b",(function(){return n}));var a=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("kdx-content-bar",{scopedSlots:e._u([{key:"btn",fn:function(){return[i("Button",{attrs:{type:"primary",disabled:e.noManagePerm},on:{click:e.submitData}},[e._v(" 提交 ")])]},proxy:!0}])},[i("div",{staticClass:"content"},[i("Form",{ref:"form",attrs:{"label-width":120,model:e.model,rules:e.rules}},[i("div",{staticClass:"card-content"},[i("kdx-form-title",[e._v("推广佣金设置")]),i("FormItem",{attrs:{label:"推广佣金提现：",prop:"enable"}},[i("RadioGroup",{model:{value:e.model.enable,callback:function(t){e.$set(e.model,"enable",t)},expression:"model.enable"}},[i("Radio",{attrs:{label:1,disabled:e.noManagePerm}},[i("span",[e._v("开启")])]),i("Radio",{attrs:{label:0,disabled:e.noManagePerm}},[i("span",[e._v("关闭")])])],1),i("p",{staticClass:"tip"},[e._v("是否允许用户将余额提出")])],1),i("FormItem",{attrs:{label:"提现方式：",prop:"withdraw_type"}},[i("CheckboxGroup",{on:{"on-change":e.changeWithdrawType},model:{value:e.model.withdraw_type,callback:function(t){e.$set(e.model,"withdraw_type",t)},expression:"model.withdraw_type"}},[i("Checkbox",{attrs:{label:20,disabled:e.noManagePerm}},[e._v("提现到微信钱包")]),i("Checkbox",{attrs:{label:30,disabled:e.noManagePerm}},[e._v("手动提现到支付宝")])],1)],1),i("FormItem",{attrs:{label:"提现限制：",prop:"min_withdraw_price"}},[i("MyRadioGroup",{attrs:{items:e.limits,disabled:e.noManagePerm},model:{value:e.model.withdraw_limit,callback:function(t){e.$set(e.model,"withdraw_limit",t)},expression:"model.withdraw_limit"}},[e.model.withdraw_limit?i("FormItem",{staticStyle:{margin:"0"},attrs:{label:"推广佣金满："}},[i("div",{staticStyle:{display:"flex"}},[i("kdx-rr-input",{staticStyle:{width:"250px",margin:"0 10px 0 0"},attrs:{placeholder:"请输入",number:"",fixed:2,"min-value":0,"max-value":9999999.99,disabled:e.noManagePerm},model:{value:e.model.min_withdraw_price,callback:function(t){e.$set(e.model,"min_withdraw_price",t)},expression:"model.min_withdraw_price"}},[i("span",{attrs:{slot:"append"},slot:"append"},[e._v("元")])]),e._v("可提现 ")],1)]):e._e()],1)],1)],1)])],1)])},n=[]},f401f:function(e,t,i){},f9cd:function(e,t,i){"use strict";i.r(t);var a=i("cca4"),n=i("b16bb");for(var l in n)["default"].indexOf(l)<0&&function(e){i.d(t,e,(function(){return n[e]}))}(l);i("0b92");var s=i("2877"),r=Object(s["a"])(n["default"],a["a"],a["b"],!1,null,"cbf15ab4",null);t["default"]=r.exports}}]);