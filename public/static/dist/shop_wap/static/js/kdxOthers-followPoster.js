(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[110],{9772:function(t,e,o){"use strict";var n;o.d(e,"b",(function(){return i})),o.d(e,"c",(function(){return r})),o.d(e,"a",(function(){return n}));var i=function(){var t=this,e=t.$createElement,o=t._self._c||e;return o("page-box",[o("poster-modal",{ref:"poster",attrs:{"poster-type":"follow"}})],1)},r=[]},a64f:function(t,e,o){(function(t){var n=o("288e");o("8e6e"),o("ac6a"),o("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,o("28a5");var i=n(o("bd86")),r=o("2f62"),a=n(o("fead")),s=(n(o("b531")),o("3014"));function l(t,e){var o=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),o.push.apply(o,n)}return o}function u(t){for(var e=1;e<arguments.length;e++){var o=null!=arguments[e]?arguments[e]:{};e%2?l(Object(o),!0).forEach((function(e){(0,i.default)(t,e,o[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(o)):l(Object(o)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(o,e))}))}return t}var c={created:function(){this.startTime=+new Date},data:function(){return{loadingFlg:0}},watch:{isSkeleton:function(t){t||++this.loadingFlg}},mounted:function(){t.error("组建的渲染时间："+this.__route__+"："+(+new Date-this.startTime))},computed:u(u({},(0,r.mapGetters)("loading",["isSkeleton"])),(0,r.mapState)("setting",{shareTitle:function(t){var e,o;return(null===(e=t.systemSetting)||void 0===e||null===(o=e.share)||void 0===o?void 0:o.title)||""},shareDesc:function(t){var e,o;return(null===(e=t.systemSetting)||void 0===e||null===(o=e.share)||void 0===o?void 0:o.description)||""},shareLogo:function(t){var e,o;return null===(e=t.systemSetting)||void 0===e||null===(o=e.share)||void 0===o?void 0:o.logo}})),methods:{handlerOptions:function(t){if(null!==t&&void 0!==t&&t.scene){for(var e=decodeURIComponent(decodeURIComponent(null===t||void 0===t?void 0:t.scene)).split("&"),o={},n=0;n<e.length;n++){var i=e[n].split("=");o[i[0]]=i[1]}null!==o&&void 0!==o&&o.inviter_id&&s.sessionStorage.setItem("inviter-id",o.inviter_id)}}},onPullDownRefresh:function(){var t=this;"function"==typeof this.pullDownRefresh&&this.pullDownRefresh(),setTimeout((function(){t.$closePageLoading()}),2e3)},onLoad:function(t){this.showTabbar=!0},onShow:function(){var t,e,o;uni.hideLoading(),a.default.setNavigationBarColor(this.$Route),this.$decorator.getPage(this.$Route.path).onLoad();var n,i,r,l,u=this.$Route.query;(null!==u&&void 0!==u&&u.inviter_id&&s.sessionStorage.setItem("inviter-id",u.inviter_id),this.$decorator.getDecorateModel({pagePath:this.$Route.path,otherdata:u}),null!==(t=this.pageInfo)&&void 0!==t&&t.gotop&&null!==(e=this.pageInfo.gotop.params)&&void 0!==e&&e.scrollTop)?this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:(null===(n=this.pageInfo.gotop)||void 0===n||null===(i=n.params)||void 0===i?void 0:i.scrollTop)>=(null===(r=this.pageInfo.gotop)||void 0===r||null===(l=r.params)||void 0===l?void 0:l.gotopheight)}},"pagemixin/onshow1"):null!==(o=this.pageInfo)&&void 0!==o&&o.gotop&&this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1,params:{scrolltop:0}}},"pagemixin/onshow2")},onHide:function(){this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1}},"pagemixin/onhide"),this.$closePageLoading()},onPageScroll:function(t){this.$decorator.getModule("gotop").onPageScroll(t,this.$Route)}};e.default=c}).call(this,o("5a52")["default"])},cd9f:function(t,e,o){"use strict";o.r(e);var n=o("ff4a"),i=o.n(n);for(var r in n)["default"].indexOf(r)<0&&function(t){o.d(e,t,(function(){return n[t]}))}(r);e["default"]=i.a},f3e2:function(t,e,o){"use strict";o.r(e);var n=o("9772"),i=o("cd9f");for(var r in i)["default"].indexOf(r)<0&&function(t){o.d(e,t,(function(){return i[t]}))}(r);var a,s=o("f0c5"),l=Object(s["a"])(i["default"],n["b"],n["c"],!1,null,"5af9a862",null,!1,n["a"],a);e["default"]=l.exports},ff4a:function(t,e,o){(function(t){var n=o("288e");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=n(o("c12a")),r=n(o("a64f")),a={mixins:[r.default],components:{PosterModal:i.default},mounted:function(){var e=this;this.$store.dispatch("login/checkLogin").then((function(o){o?e.$refs.poster.toggle((function(e){t.log("toggle",e)})):e.$store.commit("login/setModal",!0)}))}};e.default=a}).call(this,o("5a52")["default"])}}]);