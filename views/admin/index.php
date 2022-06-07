<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" href="/favicon.ico">
    <title></title>
    <link rel="stylesheet" href="/static/dist/shop/css/loading.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_1534025_3r4j1r8yrkq.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_1872121_xxkupf2vsz.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_3137624_luqu2scokt.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_2199488_00ye6ht7d6zp.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_2199566_7pueb97i5h.css">
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
        }</style>
    <script>window.config = <?php echo $settingsJson ?? ''?>;</script>
    <link href="/static/dist/shop/css/app.css?v=1654588126006" rel="preload" as="style">
    <link href="/static/dist/shop/css/chunk-vendors.css?v=1654588126006" rel="preload" as="style">
    <link href="/static/dist/shop/js/app.js?v=1654588126006" rel="preload" as="script">
    <link href="/static/dist/shop/js/chunk-vendors.js?v=1654588126006" rel="preload" as="script">
    <link href="/static/dist/shop/css/chunk-vendors.css?v=1654588126006" rel="stylesheet">
    <link href="/static/dist/shop/css/app.css?v=1654588126006" rel="stylesheet">
</head>
<body>
<div class="app-loading" id="app-loading">
    <div class="loading-spinner">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill-opacity="0" fill="none"/>
        </svg>
        <p class="text">正在加载...</p></div>
</div>
<div id="app"></div>
<script>window.requestAnimationFrame = (function () {
        return (
            window.requestAnimationFrame ||
            window.webkitRequestAnimationFrame ||
            window.mozRequestAnimationFrame ||
            function (callback) {
                window.setTimeout(callback, 1000 / 60)
            }
        )
    })()</script>
<script src="https://cdn.bootcdn.net/ajax/libs/three.js/r83/three.min.js" async></script>
<script src="/static/dist/shop/js/chunk-vendors.js?v=1654588126006"></script>
<script src="/static/dist/shop/js/app.js?v=1654588126006"></script>
</body>
</html>