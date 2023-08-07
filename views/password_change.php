<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header('Location:login.php');
    exit;
};

//タイムアウトの場合
if ((!isset($_SESSION['created'])) || (time() - $_SESSION['created'] > 3600)) {
    session_unset();
    session_destroy();
    header('Location:login.php');
};

?>
<!DOCTYPE html>
<html xml:lang="ja" lang="ja">

<head>
    <meta name="robots" content="noindex,nofollow">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=0">
    <meta content="" name="description" />
    <meta content="" name="keywords" />
    <meta content="株式会社ロジ・グレス" name="author">
    <title>パスワード変更</title>
    <!--[if lt IE 9]><script src="js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/O1_password_change.js?p=(new Date()).getTime()"></script>
</head>

<body>
    <?php
    include_once("common/header.php");
    ?>
    <main role="main">

        <article class="content">
            <section id="users">
                <p class="err_msg" style="background: none;"></p>
                <form class="mt-1em itemEdit uploadFrm" id="pwdUpdateFrm_pc">
                    <dl>
                        <dt>ユーザーID</dt>
                        <dd>
                            <?php echo $_SESSION["user_id"] ?>
                        </dd>
                    </dl>
                    <dl>
                        <dt>ユーザー名</dt>
                        <dd>
                            <?php echo $_SESSION["user_nm"] ?>
                        </dd>
                    </dl>
                    <dl>
                        <dt>現在のパスワード</dt>
                        <dd>
                            <input type="password" class="ip_w100" name="current_password" id="current_password" required>
                        </dd>
                    </dl>
                    <dl>
                        <dt>新しいパスワード</dt>
                        <dd>
                            <input type="password" class="ip_w100" name="new_password" id="new_password" required>
                        </dd>
                    </dl>
                    <dl>
                        <dt>新しいパスワード（確認用）</dt>
                        <dd>
                            <input type="password" class="ip_w100" name="new_password_chk" id="new_password_chk" required>
                        </dd>
                    </dl>
                    <div class="btnBlock">
                        <button type="button" class="btnCommon" form="pwdUpdateFrm_pc">変更</button>
                    </div>
                </form>
            </section>
            <section id="users_sp">
                <h3>パスワード変更</h3>
                <p class="err_msg"></p>
                <form class="mt-1em itemEdit uploadFrm" id="pwdUpdateFrm_sp">
                    <dl>
                        <dt>ユーザーID</dt>
                        <dd>
                            ユーザー01
                        </dd>
                    </dl>
                    <dl>
                        <dt>ユーザー名</dt>
                        <dd>
                            ユーザー01
                        </dd>
                    </dl>
                    <dl>
                        <dt>現在のパスワード</dt>
                        <dd>
                            <input type="password" class="ip_w100" name="current_password" id="current_password" required>
                        </dd>
                    </dl>
                    <dl>
                        <dt>新しいパスワード</dt>
                        <dd>
                            <input type="password" class="ip_w100" name="new_password" id="new_password" required>
                        </dd>
                    </dl>
                    <dl>
                        <dt>新しいパスワード（確認用）</dt>
                        <dd>
                            <input type="password" class="ip_w100" name="new_password_chk" id="new_password_chk" required>
                        </dd>
                    </dl>
                    <div class="btnBlock">
                        <button type="button" class="btnCommon" form="pwdUpdateFrm_sp">変更</button>
                    </div>
                </form>
            </section>

        </article>

    </main>

</body>

</html>