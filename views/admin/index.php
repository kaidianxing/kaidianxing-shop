<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width,initial-scale=1" name="viewport">
    <link href="/favicon.ico" rel="icon">
    <title></title>
    <link href="/static/dist/shop/css/loading.css" rel="stylesheet">
    <link href="//at.alicdn.com/t/c/font_1534025_zhk9m4y0v6.css" rel="stylesheet">
    <link href="//at.alicdn.com/t/font_1872121_xxkupf2vsz.css" rel="stylesheet">
    <link href="//at.alicdn.com/t/font_3137624_luqu2scokt.css" rel="stylesheet">
    <link href="//at.alicdn.com/t/font_2199488_00ye6ht7d6zp.css" rel="stylesheet">
    <link href="//at.alicdn.com/t/font_2199566_7pueb97i5h.css" rel="stylesheet">
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
    <link as="style" href="/static/dist/shop/css/app.css?v=1669276611710" rel="preload">
    <link as="style" href="/static/dist/shop/css/chunk-vendors.css?v=1669276611710" rel="preload">
    <link as="script" href="/static/dist/shop/js/app.js?v=1669276611710" rel="preload">
    <link as="script" href="/static/dist/shop/js/chunk-vendors.js?v=1669276611710" rel="preload">
    <link href="/static/dist/shop/css/chunk-vendors.css?v=1669276611710" rel="stylesheet">
    <link href="/static/dist/shop/css/app.css?v=1669276611710" rel="stylesheet">
</head>
<body>
<div class="app-loading" id="app-loading">
    <div class="loading-spinner">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" fill="none" fill-opacity="0" r="20"/>
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
<script async src="https://cdn.bootcdn.net/ajax/libs/three.js/r83/three.min.js"></script>
<script src="/static/dist/shop/js/chunk-vendors.js?v=1669276611710"></script>
<script src="/static/dist/shop/js/app.js?v=1669276611710"></script>
</body>
</html>
