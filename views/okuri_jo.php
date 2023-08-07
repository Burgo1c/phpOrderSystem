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

if ($_SESSION["auth_cd"] != "Z") {
    $_SESSION['errMsg'] = "このページにアクセス権がありません。";
    header('Location:sales.php');
    exit;
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
    <title>送り状発行</title>
    <!--[if lt IE 9]><script src="js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/S3_okuri_jo.js?p=(new Date()).getTime()"></script>
</head>

<body>
    <?php
    include_once("common/header.php");
    ?>
    <main role="main">

        <article class="content">
            <section id="users">
                <!-- <h3>送り状発行</h3> -->
                <p class="err_msg" style="background: none;"></p>
                <form class="mt-1em itemEdit uploadFrm" id="uploadFrm_pc">
                    <dl>
                        <dt>得意先電話番号</dt>
                        <dd>
                            <input type="text" name="" id="" class="ip_w100 tel bg-yellow" maxlength="12">
                        </dd>

                    </dl>

                    <div class="livesearch_row">
                        <div class="" style="justify-content: end;">
                            <ul class="livesearch" form="uploadFrm_pc" style="width: calc(100% - 30%); float: right;"></ul>
                        </div>
                    </div>


                    <dl class="">
                        <dt>得意先名</dt>
                        <dd class="tokuisaki_nm"></dd>

                    </dl>

                    <div class="btnBlock">
                        <button type="button" class="btnCommon btnSagawa" form="uploadFrm_pc">佐川送り状</button>
                        <button type="button" class="btnCommon btnYamato" form="uploadFrm_pc">ヤマト送り状</button>
                    </div>

                    <input type="hidden" name="tokuisaki_cd" class="tokuisaki_cd">
                </form>
            </section>
            <section id="users_sp">
                <h3>送り状発行</h3>
                <form class="mt-1em itemEdit uploadFrm" id="uploadFrm_sp">
                    <dl>
                        <dt>得意先電話番号</dt>
                        <dd>
                            <input type="text" name="" id="" class="ip_w100 tel bg-yellow" maxlength="12">
                        </dd>

                    </dl>
                    <div class="livesearch_row">
                        <div class="" style="justify-content: end;">
                            <ul class="livesearch" form="uploadFrm_sp" style="width: calc(100% - 30%); float: right;">
                            </ul>
                        </div>
                    </div>
                    <dl class="">
                        <dt>得意先名</dt>
                        <dd class="tokuisaki_nm"></dd>
                    </dl>

                    <div class="btnBlock">
                        <button type="button" class="btnCommon" id="btnSagawa" form="uploadFrm_sp">佐川送り状</button>
                        <button type="button" class="btnCommon" id="btnYamato" form="uploadFrm_sp">ヤマト送り状</button>
                    </div>

                    <input type="hidden" name="tokuisaki_cd" class="tokuisaki_cd">
                </form>
            </section>

        </article>

    </main>

    <div class="dialog" id="tokuisaki-search">
        <p class="err_msg"></p>
        <form class="itemEdit" id="tokuisakiSrchFrm">
            <dl class="">
                <dt>電話番号</dt>
                <dd>
                    <input type="text" class="ip_w100" name="tokuisaki_tel" />
                </dd>
            </dl>
            <dl class="">
                <dt>得意先名</dt>
                <dd>
                    <input type="text" class="ip_w100" name="tokuisaki_nm" />
                </dd>
            </dl>
            <div class="frm-table sub-table">
                <table class="" id="telTable">
                    <thead>
                        <tr>
                            <th>電話番号</th>
                            <th>得意先名</th>
                        </tr>
                    </thead>
                    <tbody class="sm-table"></tbody>
                </table>
            </div>
        </form>
    </div>
</body>

</html>