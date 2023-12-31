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

// if ($_SESSION["auth_cd"] != "Z") {
//     $_SESSION['errMsg'] = "このページにアクセス権がありません。";
//     header('Location:sales.php');
//     exit;
// };

?>


<!DOCTYPE html>
<html xml:lang="ja" lang="ja">

<head>
    <meta name="robots" content="noindex,nofollow">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=0">
    <meta content="" name="description" />
    <meta content="" name="keywords" />
    <meta content="株式会社〇〇〇〇" name="author">
    <title>出荷データ送信</title>
    <!--[if lt IE 9]><script src="js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/S1_shuka_send.js?p=(new Date()).getTime()"></script>
</head>

<body>
    <?php
    include_once("common/header.php");
    ?>
    <main role="main">

        <article class="content">
            <section id="users">
                <p class="err_msg" style="background: none;"></p>
                <form class="mt-1em itemEdit uploadFrm" id="uploadFrm_pc">
                    <dl>
                        <dt>日付</dt>
                        <dd>
                            <input type="date" name="shuka_dt" id="" class="ip_w50 dt">
                        </dd>
                    </dl>
                    <dl>
                        <dt>送信ログ</dt>
                        <dd>
                            <!-- <input type="text" class="ip_w100" > -->
                            <select class="ip_w100 log" name="previous_dt" id="log"></select>
                        </dd>
                    </dl>
                    <dl class="err" style="display: none;">
                        <dt>エラー</dt>
                        <dd id="err-file">

                        </dd>
                    </dl>
                    <div class="btnBlock">
                        <button type="button" class="btnCommon btnSend" form="uploadFrm_pc">送信</button>
                        <button type="button" class="btnCommon btnDownload" form="uploadFrm_pc">ダウンロード</button>
                    </div>
                </form>
            </section>
            <section id="users_sp">
                <p class="err_msg" style="background: none;"></p>
                <form class="mt-1em itemEdit uploadFrm" id="uploadFrm_sp">
                    <dl>
                        <dt>日付</dt>
                        <dd>
                            <input type="date" name="" id="" class="ip_w100 dt">
                        </dd>
                    </dl>
                    <dl class="err" style="display: none;">
                        <dt>エラー</dt>
                        <dd id="err-file">

                        </dd>
                    </dl>
                    <div class="btnBlock">
                        <button type="button" class="btnCommon btnSend" form="uploadFrm_pc">送信</button>
                        <button type="button" class="btnCommon btnDownload" form="uploadFrm_pc">ダウンロード</button>
                    </div>
                </form>
            </section>

        </article>

    </main>

</body>

</html>
