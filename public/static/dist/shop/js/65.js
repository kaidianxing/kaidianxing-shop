(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[65],{"04dae":function(t,e,a){t.exports=a.p+"static/dist/shop/img/wechat-config2.png"},"0ed6":function(t,e,a){t.exports=a.p+"static/dist/shop/img/wechat-config3.png"},"271e":function(t,e,a){},"439d":function(t,e,a){"use strict";a.r(e);var s=a("b5da"),n=a.n(s);for(var i in s)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return s[t]}))}(i);e["default"]=n.a},"46b6":function(t,e,a){t.exports=a.p+"static/dist/shop/img/wechat-config1.png"},"51d2":function(t,e,a){"use strict";a.r(e);var s=a("5c1a"),n=a.n(s);for(var i in s)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return s[t]}))}(i);e["default"]=n.a},"5c1a":function(t,e,a){var s=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=s(a("5530"));a("b0c0"),a("d3b7");var i=a("d08c"),r=a("3191"),o=["name","type","app_id","secret","logo","qr_code","file"],c={name:"BaseInfo",data:function(){return{model:{name:"",type:"",app_id:"",secret:"",logo:"",qr_code:"",file:"",fileName:""},rules:{name:[{required:!0,message:"公众号名称必填"}],type:[{required:!0,message:"公众号类型必填"}],app_id:[{required:!0,message:"AppID必填"}],secret:[{required:!0,message:"AppSecret必填"}],logo:[{required:!0,message:"logo必填"}],qr_code:[{required:!0,message:"二维码必填"}]},typeList:[{key:10,name:"未认证订阅号"}],type:"create"}},created:function(){this.type=this.$route.query.type||"create",this.getWechatType(),"edit"===this.type&&this.getData()},methods:{selectFile:function(){this.$refs["file_input"].click()},changeFile:function(t){this.model.file=t.target.files[0],this.model.fileName=this.model.file.name,this.$refs["file_input"].value=""},validate:function(){var t=this;return new Promise((function(e){t.$refs["form"].validate((function(t){t&&e()}))}))},changeImage:function(t,e){this.model[e]=t},getParams:function(){var t=this,e=new FormData;return o.forEach((function(a){("file"!==a||t.model.file)&&e.append(a,t.model[a])})),"edit"===this.type&&e.append("edit","edit"),e},save:function(){var t=this;(0,i.uploadFile)(r.homeApi.setWechatConfig.api,this.getParams()).then((function(e){0===e.error?(t.$Message.success("保存成功"),t.$emit("on-change",t.model)):t.$Message.error(e.message)}))},getWechatType:function(){var t=this;this.$api.homeApi.getWechatType().then((function(e){0===e.error&&(t.typeList=e.data||[])}))},getData:function(){var t=this;this.$api.homeApi.getWechatConfig().then((function(e){if(0===e.error){var a=e.data.type;t.model=(0,n.default)((0,n.default)({},e.data),{},{type:+a,fileName:""})}}))}}};e.default=c},"61b1":function(t,e,a){"use strict";a.r(e);var s=a("94cf"),n=a("439d");for(var i in n)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(i);a("8932");var r=a("2877"),o=Object(r["a"])(n["default"],s["a"],s["b"],!1,null,"5574a3fe",null);e["default"]=o.exports},"64e4":function(t,e){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"SettingGuide",props:{info:{type:Object,default:function(){}}},data:function(){return{type:"create",data:{}}},created:function(){this.type=this.$route.query.type||"create",this.getToken()},methods:{getToken:function(){var t=this,e={};"renew"===this.type&&(e={sign:"sign"}),this.$api.homeApi.getWechatToken(e).then((function(e){0===e.error&&(t.data=e.data||{})}))}}};e.default=a},6856:function(t,e,a){"use strict";a.d(e,"a",(function(){return s})),a.d(e,"b",(function(){return n}));var s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"setting-guide"},[a("div",{staticClass:"setting-guide-item"},[a("kdx-form-title",[t._v("当前微信公众号")]),a("div",{staticClass:"flex"},[a("div",{staticClass:"wechat-box"},[a("div",{staticClass:"logo"},[a("img",{attrs:{src:t.$utils.media(t.info.logo),alt:""}})]),a("div",{staticClass:"name"},[t._v(" "+t._s(t.info.name)+" ")])])])],1),a("div",{staticClass:"setting-guide-item"},[a("kdx-form-title",[t._v("公众号设置引导")]),t._m(0),a("div",{staticClass:"guide-item"},[t._m(1),a("div",{staticClass:"nest-box"},[a("kdx-hint-alert",{attrs:{"show-icon":!1,type:"warning"}},[t._v(" *请将以下链接填入对应输入框，"),a("span",{staticClass:"danger-color"},[t._v("请将以下信息提前保存好，提交保存后此页面将不会再展示")])]),a("Form",{attrs:{"label-width":120}},[a("FormItem",{attrs:{label:"URL："}},[t._v(" "+t._s(t.data.url)+" "),a("kdx-copy-text",{attrs:{text:t.data.url}},[a("Button",{attrs:{type:"text"}},[t._v("复制")])],1)],1),a("FormItem",{attrs:{label:"Token："}},[t._v(" "+t._s(t.data.token)+" "),a("kdx-copy-text",{attrs:{text:t.data.token}},[a("Button",{attrs:{type:"text"}},[t._v("复制")])],1)],1),a("FormItem",{attrs:{label:"EncodingAESKey:"}},[t._v(" "+t._s(t.data.encoding_aes_key)+" "),a("kdx-copy-text",{attrs:{text:t.data.encoding_aes_key}},[a("Button",{attrs:{type:"text"}},[t._v("复制")])],1)],1)],1),t._m(2)],1),a("div",{staticClass:"nest-box marginT-10"},[a("kdx-hint-alert",{staticClass:"marginB-10",attrs:{"show-icon":!1,type:"warning"}},[a("p",[t._v("*如果以前已填写过URL和Token,请点击[修改配置],再填写上述链接")]),a("p",[t._v("*请点击 "),a("span",{staticClass:"success-color"},[t._v("【启用】")]),t._v("，以启用服务器配置")])]),t._m(3)],1),a("div",{staticClass:"circle-num"},[t._v("2")]),a("div",{staticClass:"line-num"})])],1)])},n=[function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"guide-item"},[s("div",{staticClass:"title"},[t._v(" 第一步 登录 "),s("span",{staticClass:"success-color"},[t._v("微信公众平台")]),t._v(" ，点击左侧菜单 "),s("span",{staticClass:"success-color"},[t._v("开发 --\x3e 基本配置")])]),s("div",{staticClass:"nest-box"},[s("div",{staticClass:"image"},[s("img",{attrs:{src:a("46b6"),alt:""}})]),s("div",{staticClass:"text brand-color"},[t._v(" *如果您未成为开发者，请勾选页面上的同意协议，再点击[成为开发者]按钮 ")])]),s("div",{staticClass:"circle-num"},[t._v("1")]),s("div",{staticClass:"line-num"})])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"title"},[t._v(" 第二步 在 "),a("span",{staticClass:"success-color"},[t._v(" 基本配置 --\x3e 服务器配置")]),t._v(" 栏目下 "),a("span",{staticClass:"success-color"},[t._v("设置URL、Token和EncodingAESKey")])])},function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"image"},[s("img",{attrs:{src:a("04dae"),alt:""}})])},function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"image"},[s("img",{attrs:{src:a("0ed6"),alt:""}})])}]},"6a90":function(t,e,a){},"7fb6":function(t,e,a){"use strict";a("271e")},8932:function(t,e,a){"use strict";a("6a90")},"94cf":function(t,e,a){"use strict";a.d(e,"a",(function(){return s})),a.d(e,"b",(function(){return n}));var s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("kdx-content-bar",{scopedSlots:t._u([{key:"btn",fn:function(){return[a("Button",{staticClass:"default-long",on:{click:t.handleBack}},[t._v("返回")]),a("Button",{directives:[{name:"show",rawName:"v-show",value:1===t.step,expression:"step === 1"}],staticClass:"default-long",on:{click:t.handlePrev}},[t._v("上一步")]),a("Button",{directives:[{name:"show",rawName:"v-show",value:0===t.step,expression:"step === 0"}],staticClass:"primary-long",on:{click:t.handleNext}},[t._v("下一步")]),a("Button",{directives:[{name:"show",rawName:"v-show",value:1===t.step,expression:"step === 1"}],staticClass:"primary-long",on:{click:t.handleSave}},[t._v("保存")])]},proxy:!0}])},[a("div",{staticClass:"wechat-config-settings"},[a("div",{staticClass:"create-header"},[a("Steps",{attrs:{current:t.step}},[a("Step",{attrs:{title:"设置公众号信息"}}),a("Step",{attrs:{title:"公众号设置引导"}})],1)],1),a("div",{staticClass:"line"}),a("div",{staticClass:"create-content"},[a("base-info",{directives:[{name:"show",rawName:"v-show",value:0===t.step,expression:"step === 0"}],ref:"base_info",on:{"on-change":t.changeInfo}}),1===t.step?a("setting-guide",{attrs:{info:t.baseInfo}}):t._e()],1)])])},n=[]},b5da:function(t,e,a){var s=a("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=s(a("c7eb")),i=s(a("1da1")),r=s(a("ced2")),o=s(a("f99a")),c={name:"index",components:{BaseInfo:r.default,SettingGuide:o.default},data:function(){return{step:0,baseInfo:null}},methods:{handleNext:function(){var t=this;return(0,i.default)((0,n.default)().mark((function e(){return(0,n.default)().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return e.next=2,t.$refs["base_info"].validate();case 2:t.$refs["base_info"].save();case 3:case"end":return e.stop()}}),e)})))()},changeInfo:function(t){this.baseInfo=t,this.step=1},handlePrev:function(){this.step=0},handleBack:function(){this.$router.go(-1)},handleSave:function(){this.handleBack()}}};e.default=c},c004:function(t,e,a){"use strict";a.d(e,"a",(function(){return s})),a.d(e,"b",(function(){return n}));var s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"settings-base-info"},[a("kdx-form-title",[t._v(" 设置公众号信息 ")]),a("Form",{ref:"form",attrs:{model:t.model,rules:t.rules,"label-width":120}},[a("FormItem",{attrs:{label:"公众号名称：",prop:"name"}},[a("Input",{staticClass:"width-250",attrs:{placeholder:"请输入公众号名称"},model:{value:t.model.name,callback:function(e){t.$set(t.model,"name",e)},expression:"model.name"}})],1),a("FormItem",{attrs:{label:"公众号类型：",prop:"type"}},[a("Select",{staticClass:"width-250",model:{value:t.model.type,callback:function(e){t.$set(t.model,"type",e)},expression:"model.type"}},t._l(t.typeList,(function(e){return a("Option",{key:e.key,attrs:{value:e.key}},[t._v(t._s(e.value))])})),1),a("kdx-hint-text",[t._v("注意:即使公众平台显示为“未认证”, 但只要【公众号设置】/【账号详情】下【认证情况】显示资质审核通过, 即可认定为认证号 ")])],1),a("FormItem",{attrs:{label:"AppID：",prop:"app_id"}},[a("Input",{staticClass:"width-250",attrs:{disabled:"edit"===t.type,placeholder:"请输入微信公众平台的AppID"},model:{value:t.model.app_id,callback:function(e){t.$set(t.model,"app_id",e)},expression:"model.app_id"}})],1),a("FormItem",{attrs:{label:"AppSecret：",prop:"secret"}},[a("Input",{staticClass:"width-250",attrs:{placeholder:"请输入微信公众平台的AppSecret"},model:{value:t.model.secret,callback:function(e){t.$set(t.model,"secret",e)},expression:"model.secret"}})],1),a("FormItem",{attrs:{label:"LOGO：",prop:"logo"}},[a("kdx-image-video",{attrs:{current:t.model.logo},on:{"on-change":function(e){return t.changeImage(e,"logo")}}}),a("kdx-hint-text",[t._v("建议尺寸：200X200px")])],1),a("FormItem",{attrs:{label:"二维码：",prop:"qr_code"}},[a("kdx-image-video",{attrs:{current:t.model.qr_code},on:{"on-change":function(e){return t.changeImage(e,"qr_code")}}}),a("kdx-hint-text",[t._v("建议尺寸：200X200px")])],1),a("FormItem",{attrs:{label:"上传验证文件：",prop:"file"}},[a("input",{ref:"file_input",staticStyle:{display:"none"},attrs:{type:"file"},on:{change:t.changeFile}}),a("Input",{staticClass:"width-250",attrs:{value:t.model.fileName,disabled:""}},[a("span",{attrs:{slot:"append"},on:{click:t.selectFile},slot:"append"},[t._v("选择文件")])]),a("kdx-hint-text",[t._v("设置JS接口安全域名，需要上传的文件。")])],1)],1)],1)},n=[]},c7cf:function(t,e,a){"use strict";a.r(e);var s=a("64e4"),n=a.n(s);for(var i in s)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return s[t]}))}(i);e["default"]=n.a},ced2:function(t,e,a){"use strict";a.r(e);var s=a("c004"),n=a("51d2");for(var i in n)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(i);var r=a("2877"),o=Object(r["a"])(n["default"],s["a"],s["b"],!1,null,"2cf9efa9",null);e["default"]=o.exports},f99a:function(t,e,a){"use strict";a.r(e);var s=a("6856"),n=a("c7cf");for(var i in n)["default"].indexOf(i)<0&&function(t){a.d(e,t,(function(){return n[t]}))}(i);a("7fb6");var r=a("2877"),o=Object(r["a"])(n["default"],s["a"],s["b"],!1,null,"66ef05c3",null);e["default"]=o.exports}}]);