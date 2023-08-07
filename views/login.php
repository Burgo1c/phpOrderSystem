<!DOCTYPE html>
<html xml:lang="ja" lang="ja">

<head>
    <meta name="robots" content="noindex,nofollow">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=0">
    <meta content="" name="description" />
    <meta content="" name="keywords" />
    <meta content="株式会社ロジ・グレス" name="author">
    <title>受注管理システム</title>
    <!--[if lt IE 9]><script src="js/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/C1_Login.js?p=(new Date()).getTime()"></script>
</head>

<body>

    <main role="main" class="fullscreen" style="background: #FFF;">

        <form class="login" id="loginFrm" autocomplete="off">

            <img src="/images/top-logo.png" class="logo" alt="株式会社ウエダ食品" />
            <h3 class="sys-name">受注管理システム</h3>
            <p class="err_msg"></p>
            <dl>
                <dt>ユーザーID</dt>
                <dd class="icon-left">
                    <span class="icon left">
                        <img src="/images/icon_user_black.svg" width="25px" alt="">
                    </span>
                    <input name="user_id" id="user_id" type="text" maxlength="50" class="ip_w100" style="padding: 5px 10px 5px 2.5rem;">
                </dd>
            </dl>
            <dl>
                <dt>パスワード</dt>
                <dd class="icon-left">
                    <span class="icon left">
                        <img src="/images/lock.svg" width="20px">
                    </span>
                    <input name="password" id="password" type="password" maxlength="20" class="ip_w100" style="padding: 5px 10px 5px 2.5rem;">
                </dd>
            </dl>
            <button type="button" class="btnLogin">ログイン</button>
        </form>

    </main>
    <!--
    <footer>
        <div id="page-top"><a href="#header"><img src="images/page_top.svg" width="100%" alt="Page Top" /></a></div>
        <p class="copyright loginOnly">© Logigress Co., Ltd. All Rights Reserved.</p>
    </footer>
-->
</body>

</html>