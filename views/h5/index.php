<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <script>document.addEventListener('DOMContentLoaded', function () {
            document.documentElement.style.fontSize =
                document.documentElement.clientWidth / 20 + 'px'
        })
        var coverSupport =
            'CSS' in window &&
            typeof CSS.supports === 'function' &&
            (CSS.supports('top: env(a)') ||
                CSS.supports('top: constant(a)'))
        document.write(
            '<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0' +
            (coverSupport ? ', viewport-fit=cover' : '') +
            '" />'
        )</script>
    <link rel="stylesheet" href="https://at.alicdn.com/t/font_1534025_u0bot6z90v.css">
    <link rel="stylesheet" href="https://at.alicdn.com/t/font_1872121_xxkupf2vsz.css">
    <link rel="stylesheet" href="https://at.alicdn.com/t/font_2199566_7pueb97i5h.css">
    <link rel="stylesheet" href="https://at.alicdn.com/t/font_2199488_00ye6ht7d6zp.css">
    <link rel=stylesheet href=/static/dist/shop_wap/index.5ca1c9cc.css>
    <script>window.config = <?php echo $settingsJson ?? ''?>;
        window.wxDebug = false;</script>
    <style>/* remixicon图标库 */
        [class^="ri-"],
        [class*=" ri-"] {
            font-family: "iconfont-def";
            font-style: normal;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        [class^="ri-"]::before,
        [class*=" ri-"]::before {
            content: "\e7dd";
        }

        #__vconsole .vc-switch,
        #__vconsole .vc-panel {
            z-index: 9999999 !important;
            /* display: none!important; */
        }

        #__vconsole {
            display: none;
        }

        #vconsole-btn {
            position: fixed;
            /* border: 1px solid #ccc; */
            width: 20px;
            height: 20px;
            z-index: 9999999;
            top: 0;
            bottom: 0;
            margin: auto;
        }

        .special_redpacket {
            border: 1px solid #2d8cf0;
        }</style>
    <script>function loadStyle(url) {
            var link = document.createElement('link');
            link.type = 'text/css';
            link.rel = 'stylesheet';
            link.href = url;
            var head = document.getElementsByTagName('head')[0];
            head.appendChild(link);
        }

        function IsPC() {
            if (document.documentElement.clientWidth > 415) {//浏览器调试环境
                return false;
            }
            var flag = true;
            var userAgentInfo = navigator.userAgent;
            var Agents = ["Android", "iPhone",
                "SymbianOS", "Windows Phone",
                "iPad", "iPod"];

            for (var v = 0; v < Agents.length; v++) {
                if (userAgentInfo.indexOf(Agents[v]) > 0) {
                    flag = false;
                    break;
                }
            }
            return flag;
        }

        if (IsPC()) {
            loadStyle('/static/css/IsPC.css');
            var windowresizing = null;
        }
        window.onload = function () {
            document.getElementById('vconsole-btn').onclick = function () {
                if (typeof window.vconsolecallback == 'function') {
                    window.vconsolecallback()
                }
            }
        }</script>
</head>
<body>
<div id="vconsole-btn"></div>
<div id="app">
    <style>.search {
            width: 100%;
            height: 12.2vw;
            display: flex;
            background: #fff;
            flex-shrink: 0;
        }

        .search::after {
            content: '';
            width: 93.6vw;
            height: 8vw;
            background: rgba(0, 0, 0, 0.12);
            opacity: 0.3;
            border-radius: 5vw;
            margin: auto;
        }

        .app-loading-skeleten {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #F5F5F5;
            display: flex;
            flex-direction: column;
        }

        .app-loading-skeleten .swiper {
            width: 93.6vw;
            height: 37.8vw;
            background: #E9E9E9;
            border-radius: 1vw;
            margin: 2vw auto;
            flex-shrink: 0;
            position: relative;
        }

        .app-loading-skeleten .swiper .dots {
            position: absolute;
            width: 100%;
            height: 1vw;
            display: flex;
            justify-content: center;
            bottom: 2vw;
            left: 0;
        }

        .app-loading-skeleten .swiper p {
            width: 2vw;
            height: 100%;
            margin: 0 1vw;
            border-radius: 1vw;
            background: rgba(255, 255, 255, 0.54);
        }

        .app-loading-skeleten .btns {
            width: 93.6vw;
            height: 47vw;
            border-radius: 1vw;
            background: #fff;
            margin: 2vw auto;
            box-sizing: border-box;
            padding: 0;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .app-loading-skeleten .btn {
            width: 20%;
            height: 23.5vw;
            margin: auto;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            padding: 12 rpx 0;
            box-sizing: border-box;
        }

        .app-loading-skeleten .btn::after,
        .app-loading-skeleten .btn::before {
            content: '';
            width: 12vw;
            background: #F5F5F5;
            margin: auto;
        }

        .app-loading-skeleten .btn::after {
            height: 4vw;
            border-radius: 0.6vw;
        }

        .app-loading-skeleten .btn::before {
            height: 12vw;
            border-radius: 50%;

        }

        .app-loading-skeleten .title {
            margin: 3vw auto;
            width: 40.5vw;
            height: 3vw;
            background: #E9E9E9;
            border-radius: 1vw;
            flex-shrink: 0;
        }

        .app-loading-skeleten .goods {
            width: 93.6vw;
            display: flex;
            flex-wrap: wrap;
            margin: auto;
            justify-content: space-between;
        }

        .app-loading-skeleten .goods .good {
            width: 46vw;
            height: 46vw;
            background: #E9E9E9;
            border-radius: 1vw 1vw 0px 0px;
        }

        .app-loading-skeleten .goods .good-info {
            width: 46vw;
            height: 26vw;
            background: #fff;
            position: relative;
            box-sizing: border-box;
            padding: 2vw;
        }

        .app-loading-skeleten .goods .good-info::after,
        .app-loading-skeleten .goods .good-info::before {
            content: '';
            display: inline-block;
            width: 32vw;
            background: #F5F5F5;
            margin: auto;
            height: 4vw;
            border-radius: 0.6vw;
        }

        .app-loading-skeleten .goods .good-info::after {
            content: '';
            display: inline-block;
            width: 12vw;
            background: #F5F5F5;
            margin: auto;
            height: 3vw;
            border-radius: 0.6vw;
        }</style>
    <div class="app-loading-skeleten">
        <div class="search"></div>
        <div class="swiper">
            <div class="dots"><p></p>
                <p></p>
                <p></p>
                <p style="background:#fff"></p></div>
        </div>
        <div class="btns"><p class="btn"></p>
            <p class="btn"></p>
            <p class="btn"></p>
            <p class="btn"></p>
            <p class="btn"></p>
            <p class="btn"></p>
            <p class="btn"></p>
            <p class="btn"></p>
            <p class="btn"></p>
            <p class="btn"></p></div>
        <div class="title"></div>
        <div class="goods"><p class="good"></p>
            <p class="good"></p>
            <p class="good-info"></p>
            <p class="good-info"></p></div>
    </div>
</div>
<script src="/static/dist/shop_wap/static/js/chunk-vendors.js?v=1.0.7"></script>
<script src="/static/dist/shop_wap/static/js/index.js?v=1.0.7"></script>
</body>
</html>