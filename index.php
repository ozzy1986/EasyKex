<!DOCTYPE html>
<html>

<head>
    <title>EasyKex 0.0.1</title>

    <link rel="icon" type="image/png" href="/img/easykex_favicon2.png">

    <?php /* <link rel="stylesheet" href="/css/some.css"/> */ ?>

    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/kex.js"></script>

</head>

<body style="background-color: floralwhite;">

<div id="wrapper" style="padding: 30px; height: 100%;">

    <form action="/kex.php" method="post" id="loginForm"></form>

    <div id="logo">
        <img src="/img/easykex_logo.png" />
    </div>

    <div class="form" style="margin-top: 30px;">
        <label for="userEmail">Input your email</label>
        <input type="email" id="userEmail" name="email" autocomplete="off"/>
        <br/><br/><button id="clearButton" style="font-weight: 700;" title="Reload"><img src="/img/reload.png" title="Reload" alt="Reload" width="30" height="26" /></button>
    </div>

    <div id="result"></div>

</div>

</body>
</html>