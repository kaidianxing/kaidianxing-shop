(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[46],{"0b16":function(t,e,o){"use strict";var n=o("1985"),r=o("35e86");function s(){this.protocol=null,this.slashes=null,this.auth=null,this.host=null,this.port=null,this.hostname=null,this.hash=null,this.search=null,this.query=null,this.pathname=null,this.path=null,this.href=null}e.parse=j,e.resolve=x,e.resolveObject=P,e.format=w,e.Url=s;var i=/^([a-z0-9.+-]+:)/i,a=/:[0-9]*$/,h=/^(\/\/?(?!\/)[^\?\s]*)(\?[^\s]*)?$/,u=["<",">",'"',"`"," ","\r","\n","\t"],l=["{","}","|","\\","^","`"].concat(u),c=["'"].concat(l),f=["%","/","?",";","#"].concat(c),p=["/","?","#"],d=255,v=/^[+a-z0-9A-Z_-]{0,63}$/,g=/^([+a-z0-9A-Z_-]{0,63})(.*)$/,m={javascript:!0,"javascript:":!0},y={javascript:!0,"javascript:":!0},b={http:!0,https:!0,ftp:!0,gopher:!0,file:!0,"http:":!0,"https:":!0,"ftp:":!0,"gopher:":!0,"file:":!0},O=o("b383");function j(t,e,o){if(t&&r.isObject(t)&&t instanceof s)return t;var n=new s;return n.parse(t,e,o),n}function w(t){return r.isString(t)&&(t=j(t)),t instanceof s?t.format():s.prototype.format.call(t)}function x(t,e){return j(t,!1,!0).resolve(e)}function P(t,e){return t?j(t,!1,!0).resolveObject(e):e}s.prototype.parse=function(t,e,o){if(!r.isString(t))throw new TypeError("Parameter 'url' must be a string, not "+typeof t);var s=t.indexOf("?"),a=-1!==s&&s<t.indexOf("#")?"?":"#",u=t.split(a),l=/\\/g;u[0]=u[0].replace(l,"/"),t=u.join(a);var j=t;if(j=j.trim(),!o&&1===t.split("#").length){var w=h.exec(j);if(w)return this.path=j,this.href=j,this.pathname=w[1],w[2]?(this.search=w[2],this.query=e?O.parse(this.search.substr(1)):this.search.substr(1)):e&&(this.search="",this.query={}),this}var x=i.exec(j);if(x){x=x[0];var P=x.toLowerCase();this.protocol=P,j=j.substr(x.length)}if(o||x||j.match(/^\/\/[^@\/]+@[^@\/]+/)){var I="//"===j.substr(0,2);!I||x&&y[x]||(j=j.substr(2),this.slashes=!0)}if(!y[x]&&(I||x&&!b[x])){for(var C,R,A=-1,S=0;S<p.length;S++){var $=j.indexOf(p[S]);-1!==$&&(-1===A||$<A)&&(A=$)}R=-1===A?j.lastIndexOf("@"):j.lastIndexOf("@",A),-1!==R&&(C=j.slice(0,R),j=j.slice(R+1),this.auth=decodeURIComponent(C)),A=-1;for(S=0;S<f.length;S++){$=j.indexOf(f[S]);-1!==$&&(-1===A||$<A)&&(A=$)}-1===A&&(A=j.length),this.host=j.slice(0,A),j=j.slice(A),this.parseHost(),this.hostname=this.hostname||"";var q="["===this.hostname[0]&&"]"===this.hostname[this.hostname.length-1];if(!q)for(var L=this.hostname.split(/\./),U=(S=0,L.length);S<U;S++){var _=L[S];if(_&&!_.match(v)){for(var D="",k=0,T=_.length;k<T;k++)_.charCodeAt(k)>127?D+="x":D+=_[k];if(!D.match(v)){var E=L.slice(0,S),N=L.slice(S+1),F=_.match(g);F&&(E.push(F[1]),N.unshift(F[2])),N.length&&(j="/"+N.join(".")+j),this.hostname=E.join(".");break}}}this.hostname.length>d?this.hostname="":this.hostname=this.hostname.toLowerCase(),q||(this.hostname=n.toASCII(this.hostname));var M=this.port?":"+this.port:"",z=this.hostname||"";this.host=z+M,this.href+=this.host,q&&(this.hostname=this.hostname.substr(1,this.hostname.length-2),"/"!==j[0]&&(j="/"+j))}if(!m[P])for(S=0,U=c.length;S<U;S++){var H=c[S];if(-1!==j.indexOf(H)){var J=encodeURIComponent(H);J===H&&(J=escape(H)),j=j.split(H).join(J)}}var K=j.indexOf("#");-1!==K&&(this.hash=j.substr(K),j=j.slice(0,K));var Z=j.indexOf("?");if(-1!==Z?(this.search=j.substr(Z),this.query=j.substr(Z+1),e&&(this.query=O.parse(this.query)),j=j.slice(0,Z)):e&&(this.search="",this.query={}),j&&(this.pathname=j),b[P]&&this.hostname&&!this.pathname&&(this.pathname="/"),this.pathname||this.search){M=this.pathname||"";var B=this.search||"";this.path=M+B}return this.href=this.format(),this},s.prototype.format=function(){var t=this.auth||"";t&&(t=encodeURIComponent(t),t=t.replace(/%3A/i,":"),t+="@");var e=this.protocol||"",o=this.pathname||"",n=this.hash||"",s=!1,i="";this.host?s=t+this.host:this.hostname&&(s=t+(-1===this.hostname.indexOf(":")?this.hostname:"["+this.hostname+"]"),this.port&&(s+=":"+this.port)),this.query&&r.isObject(this.query)&&Object.keys(this.query).length&&(i=O.stringify(this.query));var a=this.search||i&&"?"+i||"";return e&&":"!==e.substr(-1)&&(e+=":"),this.slashes||(!e||b[e])&&!1!==s?(s="//"+(s||""),o&&"/"!==o.charAt(0)&&(o="/"+o)):s||(s=""),n&&"#"!==n.charAt(0)&&(n="#"+n),a&&"?"!==a.charAt(0)&&(a="?"+a),o=o.replace(/[?#]/g,(function(t){return encodeURIComponent(t)})),a=a.replace("#","%23"),e+s+o+a+n},s.prototype.resolve=function(t){return this.resolveObject(j(t,!1,!0)).format()},s.prototype.resolveObject=function(t){if(r.isString(t)){var e=new s;e.parse(t,!1,!0),t=e}for(var o=new s,n=Object.keys(this),i=0;i<n.length;i++){var a=n[i];o[a]=this[a]}if(o.hash=t.hash,""===t.href)return o.href=o.format(),o;if(t.slashes&&!t.protocol){for(var h=Object.keys(t),u=0;u<h.length;u++){var l=h[u];"protocol"!==l&&(o[l]=t[l])}return b[o.protocol]&&o.hostname&&!o.pathname&&(o.path=o.pathname="/"),o.href=o.format(),o}if(t.protocol&&t.protocol!==o.protocol){if(!b[t.protocol]){for(var c=Object.keys(t),f=0;f<c.length;f++){var p=c[f];o[p]=t[p]}return o.href=o.format(),o}if(o.protocol=t.protocol,t.host||y[t.protocol])o.pathname=t.pathname;else{var d=(t.pathname||"").split("/");while(d.length&&!(t.host=d.shift()));t.host||(t.host=""),t.hostname||(t.hostname=""),""!==d[0]&&d.unshift(""),d.length<2&&d.unshift(""),o.pathname=d.join("/")}if(o.search=t.search,o.query=t.query,o.host=t.host||"",o.auth=t.auth,o.hostname=t.hostname||t.host,o.port=t.port,o.pathname||o.search){var v=o.pathname||"",g=o.search||"";o.path=v+g}return o.slashes=o.slashes||t.slashes,o.href=o.format(),o}var m=o.pathname&&"/"===o.pathname.charAt(0),O=t.host||t.pathname&&"/"===t.pathname.charAt(0),j=O||m||o.host&&t.pathname,w=j,x=o.pathname&&o.pathname.split("/")||[],P=(d=t.pathname&&t.pathname.split("/")||[],o.protocol&&!b[o.protocol]);if(P&&(o.hostname="",o.port=null,o.host&&(""===x[0]?x[0]=o.host:x.unshift(o.host)),o.host="",t.protocol&&(t.hostname=null,t.port=null,t.host&&(""===d[0]?d[0]=t.host:d.unshift(t.host)),t.host=null),j=j&&(""===d[0]||""===x[0])),O)o.host=t.host||""===t.host?t.host:o.host,o.hostname=t.hostname||""===t.hostname?t.hostname:o.hostname,o.search=t.search,o.query=t.query,x=d;else if(d.length)x||(x=[]),x.pop(),x=x.concat(d),o.search=t.search,o.query=t.query;else if(!r.isNullOrUndefined(t.search)){if(P){o.hostname=o.host=x.shift();var I=!!(o.host&&o.host.indexOf("@")>0)&&o.host.split("@");I&&(o.auth=I.shift(),o.host=o.hostname=I.shift())}return o.search=t.search,o.query=t.query,r.isNull(o.pathname)&&r.isNull(o.search)||(o.path=(o.pathname?o.pathname:"")+(o.search?o.search:"")),o.href=o.format(),o}if(!x.length)return o.pathname=null,o.search?o.path="/"+o.search:o.path=null,o.href=o.format(),o;for(var C=x.slice(-1)[0],R=(o.host||t.host||x.length>1)&&("."===C||".."===C)||""===C,A=0,S=x.length;S>=0;S--)C=x[S],"."===C?x.splice(S,1):".."===C?(x.splice(S,1),A++):A&&(x.splice(S,1),A--);if(!j&&!w)for(;A--;A)x.unshift("..");!j||""===x[0]||x[0]&&"/"===x[0].charAt(0)||x.unshift(""),R&&"/"!==x.join("/").substr(-1)&&x.push("");var $=""===x[0]||x[0]&&"/"===x[0].charAt(0);if(P){o.hostname=o.host=$?"":x.length?x.shift():"";I=!!(o.host&&o.host.indexOf("@")>0)&&o.host.split("@");I&&(o.auth=I.shift(),o.host=o.hostname=I.shift())}return j=j||o.host&&x.length,j&&!$&&x.unshift(""),x.length?o.pathname=x.join("/"):(o.pathname=null,o.path=null),r.isNull(o.pathname)&&r.isNull(o.search)||(o.path=(o.pathname?o.pathname:"")+(o.search?o.search:"")),o.auth=t.auth||o.auth,o.slashes=o.slashes||t.slashes,o.href=o.format(),o},s.prototype.parseHost=function(){var t=this.host,e=a.exec(t);e&&(e=e[0],":"!==e&&(this.port=e.substr(1)),t=t.substr(0,t.length-e.length)),t&&(this.hostname=t)}},"13af":function(t,e,o){"use strict";o.r(e);var n=o("9be9"),r=o.n(n);for(var s in n)["default"].indexOf(s)<0&&function(t){o.d(e,t,(function(){return n[t]}))}(s);e["default"]=r.a},1985:function(t,e,o){(function(t,n){var r;/*! https://mths.be/punycode v1.4.1 by @mathias */(function(s){e&&e.nodeType,t&&t.nodeType;var i="object"==typeof n&&n;i.global!==i&&i.window!==i&&i.self;var a,h=2147483647,u=36,l=1,c=26,f=38,p=700,d=72,v=128,g="-",m=/^xn--/,y=/[^\x20-\x7E]/,b=/[\x2E\u3002\uFF0E\uFF61]/g,O={overflow:"Overflow: input needs wider integers to process","not-basic":"Illegal input >= 0x80 (not a basic code point)","invalid-input":"Invalid input"},j=u-l,w=Math.floor,x=String.fromCharCode;function P(t){throw new RangeError(O[t])}function I(t,e){var o=t.length,n=[];while(o--)n[o]=e(t[o]);return n}function C(t,e){var o=t.split("@"),n="";o.length>1&&(n=o[0]+"@",t=o[1]),t=t.replace(b,".");var r=t.split("."),s=I(r,e).join(".");return n+s}function R(t){var e,o,n=[],r=0,s=t.length;while(r<s)e=t.charCodeAt(r++),e>=55296&&e<=56319&&r<s?(o=t.charCodeAt(r++),56320==(64512&o)?n.push(((1023&e)<<10)+(1023&o)+65536):(n.push(e),r--)):n.push(e);return n}function A(t){return I(t,(function(t){var e="";return t>65535&&(t-=65536,e+=x(t>>>10&1023|55296),t=56320|1023&t),e+=x(t),e})).join("")}function S(t){return t-48<10?t-22:t-65<26?t-65:t-97<26?t-97:u}function $(t,e){return t+22+75*(t<26)-((0!=e)<<5)}function q(t,e,o){var n=0;for(t=o?w(t/p):t>>1,t+=w(t/e);t>j*c>>1;n+=u)t=w(t/j);return w(n+(j+1)*t/(t+f))}function L(t){var e,o,n,r,s,i,a,f,p,m,y=[],b=t.length,O=0,j=v,x=d;for(o=t.lastIndexOf(g),o<0&&(o=0),n=0;n<o;++n)t.charCodeAt(n)>=128&&P("not-basic"),y.push(t.charCodeAt(n));for(r=o>0?o+1:0;r<b;){for(s=O,i=1,a=u;;a+=u){if(r>=b&&P("invalid-input"),f=S(t.charCodeAt(r++)),(f>=u||f>w((h-O)/i))&&P("overflow"),O+=f*i,p=a<=x?l:a>=x+c?c:a-x,f<p)break;m=u-p,i>w(h/m)&&P("overflow"),i*=m}e=y.length+1,x=q(O-s,e,0==s),w(O/e)>h-j&&P("overflow"),j+=w(O/e),O%=e,y.splice(O++,0,j)}return A(y)}function U(t){var e,o,n,r,s,i,a,f,p,m,y,b,O,j,I,C=[];for(t=R(t),b=t.length,e=v,o=0,s=d,i=0;i<b;++i)y=t[i],y<128&&C.push(x(y));n=r=C.length,r&&C.push(g);while(n<b){for(a=h,i=0;i<b;++i)y=t[i],y>=e&&y<a&&(a=y);for(O=n+1,a-e>w((h-o)/O)&&P("overflow"),o+=(a-e)*O,e=a,i=0;i<b;++i)if(y=t[i],y<e&&++o>h&&P("overflow"),y==e){for(f=o,p=u;;p+=u){if(m=p<=s?l:p>=s+c?c:p-s,f<m)break;I=f-m,j=u-m,C.push(x($(m+I%j,0))),f=w(I/j)}C.push(x($(f,0))),s=q(o,O,n==r),o=0,++n}++o,++e}return C.join("")}function _(t){return C(t,(function(t){return m.test(t)?L(t.slice(4).toLowerCase()):t}))}function D(t){return C(t,(function(t){return y.test(t)?"xn--"+U(t):t}))}a={version:"1.4.1",ucs2:{decode:R,encode:A},decode:L,encode:U,toASCII:D,toUnicode:_},r=function(){return a}.call(e,o,e,t),void 0===r||(t.exports=r)})()}).call(this,o("62e4")(t),o("c8ba"))},"35e86":function(t,e,o){"use strict";t.exports={isString:function(t){return"string"===typeof t},isObject:function(t){return"object"===typeof t&&null!==t},isNull:function(t){return null===t},isNullOrUndefined:function(t){return null==t}}},8873:function(t,e,o){"use strict";o.r(e);var n=o("a9d5"),r=o("13af");for(var s in r)["default"].indexOf(s)<0&&function(t){o.d(e,t,(function(){return r[t]}))}(s);var i,a=o("f0c5"),h=Object(a["a"])(r["default"],n["b"],n["c"],!1,null,null,null,!1,n["a"],i);e["default"]=h.exports},"91dd":function(t,e,o){"use strict";function n(t,e){return Object.prototype.hasOwnProperty.call(t,e)}t.exports=function(t,e,o,s){e=e||"&",o=o||"=";var i={};if("string"!==typeof t||0===t.length)return i;var a=/\+/g;t=t.split(e);var h=1e3;s&&"number"===typeof s.maxKeys&&(h=s.maxKeys);var u=t.length;h>0&&u>h&&(u=h);for(var l=0;l<u;++l){var c,f,p,d,v=t[l].replace(a,"%20"),g=v.indexOf(o);g>=0?(c=v.substr(0,g),f=v.substr(g+1)):(c=v,f=""),p=decodeURIComponent(c),d=decodeURIComponent(f),n(i,p)?r(i[p])?i[p].push(d):i[p]=[i[p],d]:i[p]=d}return i};var r=Array.isArray||function(t){return"[object Array]"===Object.prototype.toString.call(t)}},"9be9":function(t,e,o){var n=o("288e");o("8e6e"),o("ac6a"),o("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r=n(o("bd86")),s=o("2f62"),i=n(o("a64f"));function a(t,e){var o=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),o.push.apply(o,n)}return o}function h(t){for(var e=1;e<arguments.length;e++){var o=null!=arguments[e]?arguments[e]:{};e%2?a(Object(o),!0).forEach((function(e){(0,r.default)(t,e,o[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(o)):a(Object(o)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(o,e))}))}return t}var u=o("0b16"),l={mixins:[i.default],mounted:function(){uni.showLoading({mask:!0}),this.postData()},computed:h({},(0,s.mapState)("login",["oldLength"])),methods:h(h({},(0,s.mapMutations)("login",["setLogin"])),{},{postData:function(){var t=this,e=u.parse(decodeURIComponent(location.href),!0).query,o=e.code,n=e.state;e.target_url,e.target_params;this.$api.loginApi.getLogin({code:o,state:n,type:"wechat"}).then((function(e){if(0===e.error){t.setLogin(!0),uni.hideLoading(),t.$toast("登录成功");var o=history.length;null!=t.oldLength?history.go(t.oldLength-o):2==o?history.go(-1):history.go(-2)}else{t.$toast(e.message),uni.hideLoading();var n=history.length;setTimeout((function(){2==n?history.go(-1):history.go(-2)}),1e3)}}))}})};e.default=l},a64f:function(t,e,o){(function(t){var n=o("288e");o("8e6e"),o("ac6a"),o("456d"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,o("28a5");var r=n(o("bd86")),s=o("2f62"),i=n(o("fead")),a=(n(o("b531")),o("3014"));function h(t,e){var o=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),o.push.apply(o,n)}return o}function u(t){for(var e=1;e<arguments.length;e++){var o=null!=arguments[e]?arguments[e]:{};e%2?h(Object(o),!0).forEach((function(e){(0,r.default)(t,e,o[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(o)):h(Object(o)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(o,e))}))}return t}var l={created:function(){this.startTime=+new Date},data:function(){return{loadingFlg:0}},watch:{isSkeleton:function(t){t||++this.loadingFlg}},mounted:function(){t.error("组建的渲染时间："+this.__route__+"："+(+new Date-this.startTime))},computed:u(u({},(0,s.mapGetters)("loading",["isSkeleton"])),(0,s.mapState)("setting",{shareTitle:function(t){var e,o;return(null===(e=t.systemSetting)||void 0===e||null===(o=e.share)||void 0===o?void 0:o.title)||""},shareDesc:function(t){var e,o;return(null===(e=t.systemSetting)||void 0===e||null===(o=e.share)||void 0===o?void 0:o.description)||""},shareLogo:function(t){var e,o;return null===(e=t.systemSetting)||void 0===e||null===(o=e.share)||void 0===o?void 0:o.logo}})),methods:{handlerOptions:function(t){if(null!==t&&void 0!==t&&t.scene){for(var e=decodeURIComponent(decodeURIComponent(null===t||void 0===t?void 0:t.scene)).split("&"),o={},n=0;n<e.length;n++){var r=e[n].split("=");o[r[0]]=r[1]}null!==o&&void 0!==o&&o.inviter_id&&a.sessionStorage.setItem("inviter-id",o.inviter_id)}}},onPullDownRefresh:function(){var t=this;"function"==typeof this.pullDownRefresh&&this.pullDownRefresh(),setTimeout((function(){t.$closePageLoading()}),2e3)},onLoad:function(t){this.showTabbar=!0},onShow:function(){var t,e,o;uni.hideLoading(),i.default.setNavigationBarColor(this.$Route),this.$decorator.getPage(this.$Route.path).onLoad();var n,r,s,h,u=this.$Route.query;(null!==u&&void 0!==u&&u.inviter_id&&a.sessionStorage.setItem("inviter-id",u.inviter_id),this.$decorator.getDecorateModel({pagePath:this.$Route.path,otherdata:u}),null!==(t=this.pageInfo)&&void 0!==t&&t.gotop&&null!==(e=this.pageInfo.gotop.params)&&void 0!==e&&e.scrollTop)?this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:(null===(n=this.pageInfo.gotop)||void 0===n||null===(r=n.params)||void 0===r?void 0:r.scrollTop)>=(null===(s=this.pageInfo.gotop)||void 0===s||null===(h=s.params)||void 0===h?void 0:h.gotopheight)}},"pagemixin/onshow1"):null!==(o=this.pageInfo)&&void 0!==o&&o.gotop&&this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1,params:{scrolltop:0}}},"pagemixin/onshow2")},onHide:function(){this.$decorator.getPage(this.$Route).setPageInfo({gotop:{show:!1}},"pagemixin/onhide"),this.$closePageLoading()},onPageScroll:function(t){this.$decorator.getModule("gotop").onPageScroll(t,this.$Route)}};e.default=l}).call(this,o("5a52")["default"])},a9d5:function(t,e,o){"use strict";var n;o.d(e,"b",(function(){return r})),o.d(e,"c",(function(){return s})),o.d(e,"a",(function(){return n}));var r=function(){var t=this,e=t.$createElement,o=t._self._c||e;return o("v-uni-view")},s=[]},b383:function(t,e,o){"use strict";e.decode=e.parse=o("91dd"),e.encode=e.stringify=o("e099")},e099:function(t,e,o){"use strict";var n=function(t){switch(typeof t){case"string":return t;case"boolean":return t?"true":"false";case"number":return isFinite(t)?t:"";default:return""}};t.exports=function(t,e,o,a){return e=e||"&",o=o||"=",null===t&&(t=void 0),"object"===typeof t?s(i(t),(function(i){var a=encodeURIComponent(n(i))+o;return r(t[i])?s(t[i],(function(t){return a+encodeURIComponent(n(t))})).join(e):a+encodeURIComponent(n(t[i]))})).join(e):a?encodeURIComponent(n(a))+o+encodeURIComponent(n(t)):""};var r=Array.isArray||function(t){return"[object Array]"===Object.prototype.toString.call(t)};function s(t,e){if(t.map)return t.map(e);for(var o=[],n=0;n<t.length;n++)o.push(e(t[n],n));return o}var i=Object.keys||function(t){var e=[];for(var o in t)Object.prototype.hasOwnProperty.call(t,o)&&e.push(o);return e}}}]);