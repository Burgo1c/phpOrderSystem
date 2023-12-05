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
    <meta content="株式会社〇〇〇〇" name="author">
    <title>ユーザーマスタ</title>
    <!--[if lt IE 9]><script src="js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/M1_user_master.js?p=(new Date()).getTime()"></script>
</head>

<body>

    <?php
    include_once("common/header.php");
    ?>
    <main role="main">

        <!-- アイテム検索開閉 -->
        <input id="input" type="checkbox">
        <label id="btnSearch" for="input"><b>操作パネル</b></label>

        <section class="searchBlock">
            <h3>操作パネル</h3>
            <form class="itemSearch" id="frmSearch_pc">
                <dl>
                    <dt>ユーザーID</dt>
                    <dd><input name="user_id" type="text" maxlength="20" value="" class="ip_w100"></dd>
                </dl>
                <dl>
                    <dt>ユーザー名</dt>
                    <dd><input name="user_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>権限</dt>
                    <dd>
                        <select name="auth_cd" id="" class="ip_w50">
                            <option value="all">全て</option>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "auth") {
                                    echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </dd>
                </dl>
                <div class="btnBlock">
                    <button type="button" class="btnSearch" form="frmSearch_pc">検索する</button>
                    <button type="reset" class="btnReset">クリア</button>
                </div>
                <div class="btnBlock_more">
                    <button type="button" class="btnCommon btnAdd">新規</button>
                </div>
            </form>
        </section>

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
                <div class="itemSearch_sp_title"><img src="/images/icon_search_g.svg" alt="入荷" /><span>操作パネル</span>
                </div>
                <ul class="itemSearch_sp_open">
                    <form class="itemSearch" id="frmSearch_sp">
                        <dl>
                            <dt>ユーザーID</dt>
                            <dd><input name="user_id" type="text" maxlength="20" value="" class="ip_w100"></dd>
                        </dl>
                        <dl>
                            <dt>ユーザー名</dt>
                            <dd><input name="user_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder="">
                            </dd>
                        </dl>
                        <dl>
                            <dt>権限</dt>
                            <dd>
                                <select name="auth_cd" id="" class="ip_w50">
                                    <option value="all">全て</option>
                                    <?php
                                    foreach ($_SESSION["code_list"] as &$obj) {
                                        if ($obj["kanri_key"] == "auth") {
                                            echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </dd>
                        </dl>
                        <div class="btnBlock">
                            <button type="button" class="btnSearch" form="frmSearch_sp">検索する</button>
                            <button type="reset" class="btnReset">クリア</button>
                        </div>
                        <div class="btnBlock_more">
                            <button type="button" class="btnCommon btnAdd">新規</button>
                        </div>
                    </form>
                </ul>
            </li>
        </ul>

        <article class="content">

            <section id="users">
                <h3>検索結果</h3>
                <div class="btnBlock">
                    <p class="kensu disnon"></p>
                </div>

                <div class="inner" id="list"></div>

                <!-- <div class="btnBlock">
                    <div>
                        <button type="button" class="btnPick">全選択</button>
                        <button type="button" class="btnPick">全解除</button>
                    </div>
                    <p>件数：30</p>
                </div> -->

            </section>

            <!-- 検索結果（スマホ） -->
            <section id="users_sp">
                <h3>ユーザーマスク</h3>
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

    <div class="dialog" id="dialog">
        <p class="err_msg"></p>
        <form class="itemEdit" id="userFrm" autocomplete="off">
            <dl class="id-disp">
                <dt>ユーザーID</dt>
                <dd>
                    <label></label>
                    <input type="text" name="user_id" id="user_id" type="text" maxlength="50" class="ip_w100" required>
                </dd>
            </dl>
            <dl class="">
                <dt>ユーザー名</dt>
                <dd><input name="user_nm" id="user_nm" type="text" maxlength="50" class="ip_w100" required></dd>
            </dl>
            <dl class="pwd ">
                <dt>パスワード</dt>
                <dd>
                    <input type="password" id="password" name="password" maxlength="20" class="ip_w100" required>
                </dd>
            </dl>
            <dl class="">
                <dt>権限</dt>
                <dd>
                    <select name="auth_cd" id="auth_cd" class="ip_w50" required>
                        <?php
                        foreach ($_SESSION["code_list"] as &$obj) {
                            if ($obj["kanri_key"] == "auth") {
                                echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </dd>
            </dl>
        </form>
    </div>

</body>

</html>
