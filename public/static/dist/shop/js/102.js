(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[102],{2736:function(e,t,a){},"2ecd":function(e,t,a){"use strict";a.r(t);var o=a("fcec"),n=a.n(o);for(var r in o)["default"].indexOf(r)<0&&function(e){a.d(t,e,(function(){return o[e]}))}(r);t["default"]=n.a},"50b6":function(e,t,a){"use strict";a.r(t);var o=a("7d16"),n=a("2ecd");for(var r in n)["default"].indexOf(r)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(r);a("675f");var i=a("2877"),s=Object(i["a"])(n["default"],o["a"],o["b"],!1,null,"f4a461ee",null);t["default"]=s.exports},"675f":function(e,t,a){"use strict";a("2736")},"7d16":function(e,t,a){"use strict";a.d(t,"a",(function(){return o})),a.d(t,"b",(function(){return n}));var o=function(){var e=this,t=e.$createElement,o=e._self._c||t;return o("kdx-content-bar",{scopedSlots:e._u([{key:"btn",fn:function(){return[o("Button",{staticClass:"handler-btn primary-long",attrs:{disabled:e.noManagePerm},on:{click:e.handleSave}},[e._v("保存")])]},proxy:!0}])},[o("div",{staticClass:"container"},[o("Form",{ref:"form",staticClass:"content",attrs:{model:e.model,rules:e.rules,"label-width":140},on:{"on-validate":e.onValidate}},[o("div",{staticClass:"content-box"},[o("kdx-form-title",[e._v("站点设置")]),o("FormItem",{attrs:{label:"商城状态：",prop:"mall_status"}},[o("RadioGroup",{model:{value:e.model.mall_status,callback:function(t){e.$set(e.model,"mall_status",t)},expression:"model.mall_status"}},[o("Radio",{attrs:{disabled:e.noManagePerm,label:"1"}},[o("span",[e._v("营业")])]),o("Radio",{attrs:{disabled:e.noManagePerm,label:"0"}},[o("span",[e._v("关闭")])])],1)],1),0==e.model.mall_status?o("FormItem",{attrs:{label:"商城关闭跳转链接："}},[o("Input",{staticStyle:{width:"500px"},attrs:{disabled:e.noManagePerm,placeholder:"请输入"},model:{value:e.model.mall_close_url,callback:function(t){e.$set(e.model,"mall_close_url",t)},expression:"model.mall_close_url"}},[o("span",{attrs:{slot:"prepend"},slot:"prepend"},[e._v("http://")])])],1):e._e(),o("FormItem",{directives:[{name:"error-item",rawName:"v-error-item.name",modifiers:{name:!0}}],attrs:{label:"商城名称：",prop:"name"}},[o("Input",{staticStyle:{width:"300px"},attrs:{disabled:e.noManagePerm,placeholder:"请输入",maxlength:"15","show-word-limit":""},model:{value:e.model.name,callback:function(t){e.$set(e.model,"name",t)},expression:"model.name"}})],1),o("FormItem",{directives:[{name:"error-item",rawName:"v-error-item.description",modifiers:{description:!0}}],attrs:{label:"商城简介：",prop:"description"}},[o("Input",{staticStyle:{width:"500px"},attrs:{disabled:e.noManagePerm,type:"textarea",placeholder:"用于商城系统分享副标题"},model:{value:e.model.description,callback:function(t){e.$set(e.model,"description",t)},expression:"model.description"}})],1),o("FormItem",{directives:[{name:"error-item",rawName:"v-error-item.logo",modifiers:{logo:!0}}],attrs:{label:"商城LOGO：",prop:"logo"}},[o("kdx-image-video",{attrs:{type:"image",current:e.model.logo},on:{"on-change":function(t){return e.changeThumb(t,"logo")}}}),o("kdx-hint-text",[e._v("图片为长方形，建议尺寸200*100，用于商城首页分享的系统默认宣传图")])],1),o("FormItem",{attrs:{label:"登录页背景图：",prop:"login_show_img"}},[o("kdx-image-video",{attrs:{height:67,type:"image",current:e.model.login_show_img},on:{"on-change":function(t){return e.changeThumb(t,"login_show_img")}}}),o("kdx-hint-text",[e._v("登录页面背景图片，可进行自定义修改，建议尺寸1920*1080")])],1),o("FormItem",{attrs:{label:"售罄图标：",prop:"sale_out"}},[o("kdx-image-video",{attrs:{type:"image",current:e.model.sale_out},on:{"on-change":function(t){return e.changeThumb(t,"sale_out")}}}),o("div",{staticClass:"flex"},[o("kdx-hint-text",{attrs:{content:"建议尺寸比例为1:1，用于商品售罄后的提示图标"}}),o("kdx-hint-tooltip",{attrs:{type:"image",image:a("c23b")}})],1)],1),o("FormItem",{attrs:{label:"加载图标：",prop:"loading"}},[o("kdx-image-video",{attrs:{type:"image",current:e.model.loading},on:{"on-change":function(t){return e.changeThumb(t,"loading")}}}),o("div",{staticClass:"flex"},[o("kdx-hint-text",{attrs:{content:"图片为正方形，建议尺寸200*200，用于网络较慢图片未加载出来时的占位图"}}),o("kdx-hint-tooltip",{attrs:{type:"image",image:a("ef0a")}})],1)],1),o("FormItem",{attrs:{label:"商城图片预览："}},[o("RadioGroup",{model:{value:e.model.photo_preview,callback:function(t){e.$set(e.model,"photo_preview",t)},expression:"model.photo_preview"}},[o("Radio",{attrs:{disabled:e.noManagePerm,label:"1"}},[o("span",[e._v("开启")])]),o("Radio",{attrs:{disabled:e.noManagePerm,label:"0"}},[o("span",[e._v("关闭")])])],1),o("div",{staticClass:"flex"},[o("kdx-hint-text",{attrs:{content:"如果开启此选项，则商品详情、轮播图的图片可以放大预览"}}),o("kdx-hint-tooltip",{attrs:{type:"image",image:a("87ac")}})],1)],1),o("FormItem",{attrs:{label:"网站备案号："}},[o("Input",{staticClass:"agree-input",attrs:{disabled:e.noManagePerm,placeholder:"请输入网站备案号"},model:{value:e.model.icp_code,callback:function(t){e.$set(e.model,"icp_code",t)},expression:"model.icp_code"}})],1)],1),o("div",{staticClass:"content-box"},[o("kdx-form-title",[e._v("协议信息")]),o("FormItem",{attrs:{label:"协议标题：",prop:"agreement_name"}},[o("Input",{staticClass:"agree-input",attrs:{disabled:e.noManagePerm,placeholder:"请输入协议标题",maxlength:"10","show-word-limit":""},on:{"on-blur":e.agreeInput},model:{value:e.model.agreement_name,callback:function(t){e.$set(e.model,"agreement_name",t)},expression:"model.agreement_name"}}),o("kdx-hint-tooltip",{attrs:{type:"image",image:a("8149")}})],1),o("FormItem",{attrs:{label:"协议内容：",prop:"agreement_content"}},[o("editor",{attrs:{height:667},on:{input:e.contentInput},model:{value:e.model.agreement_content,callback:function(t){e.$set(e.model,"agreement_content",t)},expression:"model.agreement_content"}})],1)],1)])],1)])},n=[]},8149:function(e,t,a){e.exports=a.p+"static/dist/shop/img/agreement.png"},"87ac":function(e,t,a){e.exports=a.p+"static/dist/shop/img/preview_img.png"},c23b:function(e,t,a){e.exports=a.p+"static/dist/shop/img/sale_out.png"},ef0a:function(e,t,a){e.exports=a.p+"static/dist/shop/img/load_picture.png"},fcec:function(e,t,a){var o=a("4ea4").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=o(a("ade3")),r=o(a("15fd"));a("d9e2"),a("498a"),a("d3b7");var i=o(a("5530")),s=o(a("ceb0")),l=a("2f62"),d=["error","shop_info"],m={name:"index",components:{Editor:s.default},computed:(0,i.default)((0,i.default)({},(0,l.mapState)("account",{shopId:function(e){return e.shop.shopId}})),{},{noManagePerm:function(){return!this.$getPermMap("sysset.mall.basic.manage")},isRoot:function(){var e,t;return 1==(null===(e=this.$store.state.config)||void 0===e||null===(t=e.user)||void 0===t?void 0:t.is_root)}}),data:function(){return{model:{mall_status:"1",mall_close_url:"",name:"",logo:"",login_show_img:"",description:"",sale_out:"",loading:"",photo_preview:"1",agreement_name:"用户注册使用协议",agreement_content:"用户注册使用协议"},rules:{mall_status:[{required:!0,message:"请选择商城状态"}],name:[{required:!0,message:"请输入商城名称"}],logo:[{required:!0,message:"请上传商城LOGO",trigger:"change"}],login_show_img:[{required:!0,message:"请上传登录页展示图",trigger:"change"}],description:[{required:!0,message:"请输入商城简介"}]},submitLoading:!1,shop_info:{},passModel:{password:""},passRules:{password:[{required:!0,message:"请输入密码"}],checked:[{required:!0,validator:function(e,t,a){t?a():a(new Error("请先确认风险"))}}]}}},methods:{changeThumb:function(e,t){this.$set(this.model,t,e)},getData:function(){var e=this;this.$api.settingApi.getBaseSetting({}).then((function(t){var a=t.error,o=t.shop_info,n=(0,r.default)(t,d);0===a&&(e.model=(0,i.default)((0,i.default)({},e.model),n),e.shop_info=o||{},e.$store.commit("config/setBaseSetting",n))}))},agreeInput:function(){""===this.model.agreement_name.trim()&&(this.model.agreement_name="用户注册使用协议")},contentInput:function(){""===this.model.agreement_content.trim()&&(this.model.agreement_content="用户注册使用协议")},onValidate:function(e,t){this.cacheError=(0,i.default)((0,i.default)({},this.cacheError),{},(0,n.default)({},e,t))},validate:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:function(){};return new Promise((function(a){e.$refs["form"].validate((function(o){a(o),t(o),e.$nextTick((function(){if(!o)for(var t in e.cacheError)if(!e.cacheError[t]){e.$focusError(t);break}}))}))}))},handleSave:function(){var e=this;this.validate((function(t){t&&e.$api.settingApi.changeBaseSetting(e.model).then((function(t){0===t.error&&(e.$Message.success("操作成功"),e.getData())}))}))},okHandler:function(){var e=this;this.$refs.passForm.validate((function(t){t&&e.$api.settingApi.deleleShop({password:e.passModel.password}).then((function(t){0==t.error&&e.getData()}))}))},reNewBtn:function(){this.$utils.openNewWindowPage("/create/renew",{shop_id:this.shopId})}},mounted:function(){this.getData()}};t.default=m}}]);