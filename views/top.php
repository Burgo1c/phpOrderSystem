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
    <meta content="株式会社ロジ・グレス" name="author">

    <title>TOP</title>
    <!--[if lt IE 9]><script src="js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/C2_top.js?p=(new Date()).getTime()"></script>
</head>

<body>
    <?php
    include_once("common/header.php");
    ?>
    <main role="main">
        <!-- アイテム検索開閉（スマホ用） -->
        <script>
            $(function() {
                $("ul.itemSearch_sp_open").hide();
                $("div.itemSearch_sp_title").click(function() {
                    $("ul.itemSearch_sp_open").slideUp();
                    $("div.itemSearch_sp_title").removeClass("close");
                    if ($("+ul", this).css("display") == "none") {
                        $("+ul", this).slideDown();
                        $(this).addClass("close");
                        return false;
                    }
                    $("+ul", this).slideUp();
                });
            });
        </script>

        <ul class="itemSearch_sp">
            <li>
                <div class="itemSearch_sp_title"><img src="/images/icon_search_g.svg" alt="検索" /><span>操作パネル</span>
                </div>
                <ul class="itemSearch_sp_open">
                    <form class="itemSearch" id="topFrm_sp">
                        <dl>
                            <dt>ログインID</dt>
                            <dd>
                                <select name="user_id" id="user_id" class="ip_w100">
                                    <option value="all">全て</option>
                                    <?php
                                    foreach ($_SESSION["user_list"] as &$obj) {
                                        echo '<option value="' . $obj["user_id"] . '">' . $obj["user_nm"] . '</option>';
                                    }

                                    ?>
                                </select>
                            </dd>
                        </dl>
                        <dl>
                            <dt>得意先名</dt>
                            <dd>
                                <input name="tokuisaki_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder="">
                            </dd>
                        </dl>
                        <dl>
                            <dt>電話番号</dt>
                            <dd>
                                <input type="tel" name="tokuisaki_tel" id="tokuisaki_tel" maxlength="12" class="ip_w100">
                            </dd>
                        </dl>
                        <dl>
                            <dt>問合せ番号</dt>
                            <dd>
                                <input type="tel" name="inquire_no" id="inquire_no" maxlength="12" class="ip_w100">
                            </dd>
                        </dl>
                        <div class="btnBlock">
                            <button type="button" class="btnSearch searchBtn" form="topFrm_sp">検索する</button>
                            <button type="reset" class="btnReset">クリア</button>
                        </div>
                    </form>
                </ul>
            </li>
        </ul>

        <article class="content">
            <section id="top-search">
                <form id="topFrm" autocomplete="off">
                    <div class="frmGroup">
                        <label for="user_id">ログインID</label>
                        <select name="user_id" id="user_id">
                            <option value="all">全て</option>
                            <?php
                            foreach ($_SESSION["user_list"] as &$obj) {
                                echo '<option value="' . $obj["user_id"] . '">' . $obj["user_nm"] . '</option>';
                            }

                            ?>
                        </select>
                    </div>
                    <div class="frmGroup">
                        <label for="tokuisaki_nm">得意先名</label>
                        <input type="text" name="tokuisaki_nm" id="tokuisaki_nm">
                    </div>
                    <div class="frmGroup">
                        <label for="tokuisaki_tel">電話番号</label>
                        <input type="tel" name="tokuisaki_tel" id="tokuisaki_tel" maxlength="12">
                    </div>
                    <div class="frmGroup">
                        <label for="inquire_no">問合せ番号</label>
                        <input type="tel" name="inquire_no" id="inquire_no" maxlength="12">
                    </div>

                    <button type="button" form="topFrm" id="searchBtn" class="searchBtn">検索</button>
                </form>

            </section>

            <section id="users">
                <div class="inner" id="top">
                    <table id="top_table">
                        <thead>
                            <tr>
                                <th class="w_50px">詳細</th>
                                <th class="w_150px">売上日</th>
                                <th class="tal">得意先名</th>
                                <th>受注番号</th>
                                <th class="tal">問合せ番号</th>
                                <th class="tal">ログインID</th>
                            </tr>
                        </thead>
                        <tbody class="list"></tbody>
                    </table>
                </div>
            </section>

            <!-- 検索結果（スマホ） -->
            <section id="users_sp">
                <h3>当日受注</h3>
                <div class="btnBlock">
                    <p class="kensu disnon"></p>
                </div>

                <div class="inner"></div>

            </section>

            <section class="pagenavi disnon" id="pagenation" role="navigation"></section>
            <!-- ページナビ（スマホ） -->
            <section class="pagenavi_sp" id="pagenation_sp" role="navigation"></section>
        </article>

    </main>

    <div class="dialog" id="saleDialog">
        <input type="hidden" id="order_no" value="">
        <iframe src=""></iframe>
    </div>

</body>

</html>