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
    <title>得意先商品マスタ</title>
    <!--[if lt IE 9]><script src="js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/M4_tokuisaki_shohin_master.js?p=(new Date()).getTime()"></script>
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
                    <dt>代表電話番号</dt>
                    <dd><input name="tokuisaki_tel" id="tokuisaki_tel" type="tel" maxlength="12" value="" class="ip_w50"></dd>
                </dl>
                <dl>
                    <dt>得意先名</dt>
                    <dd><input name="tokuisaki_nm" id="tokuisaki_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>商品コード</dt>
                    <dd><input name="product_cd" id="product_cd" type="text" maxlength="20" value="" class="ip_w50" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>商品名</dt>
                    <dd><input name="product_nm" id="product_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>商品名略称</dt>
                    <dd><input name="product_nm_abrv" id="product_nm_abrv" type="text" maxlength="20" value="" class="ip_w100" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>商品分類</dt>
                    <dd>
                        <select name="product_type" id="product_type" class="ip_w50">
                            <option value="all">全て</option>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "product.type") {
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
                    <button type="button" class="btnCommon" id="btnPrint">台帳発行</button>
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
                            <dt>代表電話番号</dt>
                            <dd><input name="tokuisaki_tel" id="tokuisaki_tel" type="tel" maxlength="11" value="" class="ip_w100"></dd>
                        </dl>
                        <dl>
                            <dt>得意先名</dt>
                            <dd><input name="tokuisaki_nm" id="tokuisaki_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>商品コード</dt>
                            <dd><input name="product_cd" id="product_cd" type="text" maxlength="20" value="" class="ip_w50" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>商品名</dt>
                            <dd><input name="product_nm" id="product_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>商品名略称</dt>
                            <dd><input name="product_nm_abrv" id="product_nm_abrv" type="text" maxlength="20" value="" class="ip_w50" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>商品分類</dt>
                            <dd>
                                <select name="product_type" id="product_type" class="ip_w50">
                                    <option value="all">全て</option>
                                    <?php
                                    foreach ($_SESSION["code_list"] as &$obj) {
                                        if ($obj["kanri_key"] == "product.type") {
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

            </section>

            <!-- 検索結果（スマホ） -->
            <section id="users_sp">
                <h3>得意先別商品マスタ</h3>
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
        <form class="itemEdit" id="editFrm" autocomplete="off">
            <input type="hidden" name="tokuisaki_cd" id="tokuisaki_cd">
            <dl class="bt id-disp">
                <dt>代表電話番号</dt>
                <dd>
                    <label id="tokuisaki_tel"></label>
                    <input name="tokuisaki_tel" id="tokuisaki_tel" type="tel" maxlength="12" value="" class="ip_w100" required>
                </dd>
            </dl>
            <dl class="">
                <dt>得意先名</dt>
                <dd>
                    <label id="tokuisaki_nm"></label>
                </dd>
            </dl>
            <dl class="id-disp">
                <dt>商品コード</dt>
                <dd>
                    <label id="product_cd"></label>
                    <input type="text" id="product_cd" name="product_cd" class="ip_w100" maxlength="3" required>
                </dd>
            </dl>
            <dl class="">
                <dt>商品名</dt>
                <dd>
                    <label id="product_nm"></label>
                </dd>
            </dl>
            <dl class="">
                <dt>商品名略称</dt>
                <dd>
                    <label id="product_nm_abrv"></label>
                </dd>
            </dl>
            <dl class="">
                <dt>商品分類</dt>
                <dd>
                    <label id="product_type"></label>
                </dd>
            </dl>
            <dl class="">
                <dt>売上単位</dt>
                <dd>
                    <label id="sale_tani"></label>
                    <!-- <input type="text" id="sale_tani" name="" class="ip_w100"> -->
                </dd>
            </dl>
            <dl class="">
                <dt>売上単価</dt>
                <dd>
                    <input type="text" id="sale_price" name="sale_price" class="ip_w100" maxlength="7" required>
                </dd>
            </dl>
            <dl class="">
                <dt>仕入単価</dt>
                <dd>
                    <input type="text" id="unit_price" name="unit_price" class="ip_w100" maxlength="7" required>
                </dd>
            </dl>
        </form>
    </div>

    <div class="dialog" id="tokuisaki_print">
        <p class="err_msg"></p>
        <form class="itemEdit" id="printFrm">
            <dl>
                <dt>代表電話番号</dt>
                <dd>
                    <input type="tel" class="ip_w100" name="tokuisaki_tel" id="tokuisaki_tel" maxlength="12">
                </dd>
            </dl>
            <dl>
                <dt>得意先名</dt>
                <dd>
                    <label id="tokuisaki_nm"></label>
                    <!-- <input type="text" class="ip_w100" name="tokuisaki_nm" maxlength="40"> -->
                </dd>
            </dl>
            <div class="" style="display: flex; flex-direction:column;">

                <dl style="border-top: 0;">
                    <dt>From</dt>
                    <dd>
                        <input type="text" class="ip_w100" name="product_from" maxlength="3">
                    </dd>
                </dl>
                <dl>
                    <dt>To</dt>
                    <dd>
                        <input type="text" class="ip_w100" name="product_to" maxlength="3">
                    </dd>
                </dl>
            </div>

        </form>
    </div>

</body>

</html>
