(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[68],{"069b":function(t,e,i){},"0c09":function(t,e,i){"use strict";i.d(e,"a",(function(){return s})),i.d(e,"b",(function(){return a}));var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("kdx-modal-frame",{attrs:{value:t.value,width:430,closable:!1,okText:"确认"},on:{"on-cancel":t.handleCancel,"on-ok":t.handleOk}},[i("div",{staticClass:"order-list-refund-modal"},[i("div",{staticClass:"refund-box"},[i("div",{staticClass:"icon"},[i("Icon",{attrs:{type:"ios-help-circle"}})],1),i("div",{staticClass:"content"},[i("div",{staticClass:"title"},[t._v(" 提示 ")]),t._t("default")],2)])])])},a=[]},"0fe0":function(t,e,i){"use strict";i.r(e);var s=i("38f3"),a=i.n(s);for(var n in s)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return s[t]}))}(n);e["default"]=a.a},2549:function(t,e,i){"use strict";i.d(e,"a",(function(){return s})),i.d(e,"b",(function(){return a}));var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{directives:[{name:"show",rawName:"v-show",value:t.value,expression:"value"}],staticClass:"modal",on:{click:function(t){t.stopPropagation()}}},[i("div",{staticClass:"modal-mask"}),i("div",{staticClass:"modal-content"},[i("div",{staticClass:"close",on:{click:t.fnCloseModal}},[i("Icon",{attrs:{type:"ios-close",size:24}})],1),t.checking?i("div",{staticClass:"icon iconfont icon-xitong-sousuo"}):t._e(),t.process_success?i("div",{staticClass:"icon iconfont icon-yes"}):t._e(),t.process_fail?i("div",{staticClass:"icon iconfont icon-del"}):t._e(),i("div",{staticClass:"tips"},[t._v(t._s(t.tipsText))]),i("div",{staticClass:"check"},[t.loading?i("div",{staticClass:"progress"},[i("div",{staticClass:"bg"},[i("div",{staticClass:"current",style:"width: "+t.progress+"%;"})])]):t._e()])])])},a=[]},"38f3":function(t,e,i){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("d3b7"),i("6062"),i("3ca3"),i("ddb0");var s={props:["value","loading","step","tipsText"],data:function(){return{progress:0,timeId:null,interval:10}},computed:{check_start:function(){return new Set(["","50"]).has(this.step)},checking:function(){return new Set(["","0"]).has(this.step)||"1"===this.step&&this.loading},check_finished:function(){return"1"===this.step},process_success:function(){return new Set(["1"]).has(this.step)&&!this.loading},process_fail:function(){return new Set(["12"]).has(this.step)}},watch:{loading:{handler:function(t){var e=this;t?(this.progress=0,this.interval=10,this.timeId=setInterval((function(){e.progress+=e.interval,e.progress>=90&&1!=e.step?e.progress=90:e.progress,e.progress>=100&&clearInterval(e.timeId)}),1e3)):this.timeId&&clearInterval(this.timeId)},immediate:!0}},methods:{fnCloseModal:function(){this.timeId&&clearInterval(this.timeId),this.$emit("input",!1)}}};e.default=s},"49de8":function(t,e){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i={name:"index",props:{value:{type:Boolean,default:!1}},methods:{inputHandler:function(t){console.log(t)},handleCancel:function(){this.$emit("on-cancel")},handleOk:function(){this.$emit("on-ok")}}};e.default=i},"4fdf":function(t,e,i){},"558f":function(t,e,i){},5599:function(t,e,i){"use strict";i.d(e,"a",(function(){return s})),i.d(e,"b",(function(){return a}));var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"system-base"},[i("div",{staticClass:"system-base-box"},[i("Form",{ref:"form"},[i("kdx-form-title",[t._v(" 检查队列 ")]),i("FormItem",[i("kdx-hint-alert",{staticClass:"hint-alert",attrs:{showIcon:!0}},[i("div",{staticClass:"hint-alert-content"},[i("div",{staticClass:"text"},[t._v("检查系统队列以是否正常执行，保证系统部分业务正常进行，例如 系统数据每日更新、短信发送、消息模板等能够正常收发。")])])]),i("Button",{staticClass:"primary-long marginT-20",on:{click:t.checkQueue}},[t._v("检查队列")])],1),i("kdx-form-title",[t._v(" 清除缓存 ")]),i("FormItem",[i("kdx-hint-alert",{staticClass:"hint-alert",attrs:{showIcon:!0}},[i("div",{staticClass:"hint-alert-content"},[i("div",{staticClass:"text"},[t._v("如数据读取有偏差时，清除缓存已确保数据同步。")])])]),i("div",{staticClass:"cacheData marginT-10"},[t._v("当前缓存数据："+t._s(t.cacheData))]),i("Button",{staticClass:"primary-long marginT-20",on:{click:t.clearCache}},[t._v("立即清除缓存")])],1)],1),i("check-modal",{attrs:{loading:t.loading,tipsText:t.tipsText,step:t.step},on:{input:t.clickStop},model:{value:t.modalShowFlag,callback:function(e){t.modalShowFlag=e},expression:"modalShowFlag"}}),i("modal-tip",{attrs:{value:t.modalShow},on:{"on-ok":t.handleOk,"on-cancel":t.handleCancel}},[i("div",{staticClass:"clearText"},[t._v(" 确定清除缓存？ ")])]),t._t("default")],2)])},a=[]},"5aba3":function(t,e,i){"use strict";i("4fdf")},"66b1":function(t,e,i){"use strict";i.r(e);var s=i("2549"),a=i("0fe0");for(var n in a)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(n);i("5aba3");var o=i("2877"),c=Object(o["a"])(a["default"],s["a"],s["b"],!1,null,"56a3714a",null);e["default"]=c.exports},8415:function(t,e,i){"use strict";i.r(e);var s=i("49de8"),a=i.n(s);for(var n in s)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return s[t]}))}(n);e["default"]=a.a},"89f5":function(t,e,i){"use strict";i.r(e);var s=i("0c09"),a=i("8415");for(var n in a)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(n);i("8ffa");var o=i("2877"),c=Object(o["a"])(a["default"],s["a"],s["b"],!1,null,"5073e61a",null);e["default"]=c.exports},"8ffa":function(t,e,i){"use strict";i("069b")},cb99:function(t,e,i){var s=i("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a=s(i("66b1")),n=s(i("89f5")),o={name:"index",components:{CheckModal:a.default,ModalTip:n.default},data:function(){return{cacheData:"",modalShowFlag:!1,modalShow:!1,tipsText:"正在检测队列是否正常执行",step:"",loading:!1,tmp_job_id:"",times:0,timer:null,stopGet:!0}},created:function(){this.getCacheData()},methods:{getCacheData:function(){var t=this;this.$api.systemApi.getCacheData().then((function(e){0===e.error&&(t.cacheData=e.redis.used_memory_human)}))},checkQueue:function(){var t=this;this.loading=!0,this.step="0",this.stopGet=!0,this.modalShowFlag=!0,this.times=0,this.tipsText="正在检测队列是否正常执行",this.$api.systemApi.checkQueue().then((function(e){0===e.error&&(t.tmp_job_id=e.tmp_job_id,t.queueStatus())}))},queueStatus:function(){var t=this;if(this.times++>50)clearTimeout(this.timer),this.loading=!1,this.step="12",this.stopGet=!0,this.tipsText="队列异常，请检查队列";else{if(!this.stopGet&&"1"!=this.step)return clearTimeout(this.timer),this.stopGet=!0,this.loading=!1,void(this.times=0);this.$api.systemApi.queueStatus({tmp_job_id:this.tmp_job_id},{message:!1}).then((function(e){0===e.error?(setTimeout((function(){t.loading=!1,t.step="1",t.tipsText="队列正常运行"}),1e3),clearTimeout(t.timer)):(clearTimeout(t.timer),t.step="0",t.timer=setTimeout(t.queueStatus,1e3))}))}},clearCache:function(){this.modalShow=!0},handleOk:function(){var t=this;this.$api.systemApi.clearCacheData({}).then((function(e){0===e.error&&(t.modalShow=!1,t.$Message.success("清除成功"),t.getCacheData())}))},handleCancel:function(){this.modalShow=!1},clickStop:function(t){this.stopGet=this.times>50||t}}};e.default=o},e17f:function(t,e,i){"use strict";i.r(e);var s=i("cb99"),a=i.n(s);for(var n in s)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return s[t]}))}(n);e["default"]=a.a},fba4:function(t,e,i){"use strict";i("558f")},fc16:function(t,e,i){"use strict";i.r(e);var s=i("5599"),a=i("e17f");for(var n in a)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(n);i("fba4");var o=i("2877"),c=Object(o["a"])(a["default"],s["a"],s["b"],!1,null,"9e87a766",null);e["default"]=c.exports}}]);