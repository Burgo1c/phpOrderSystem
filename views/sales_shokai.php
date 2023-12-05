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
    <meta content="株式会社〇〇〇〇" name="author">
    <title>売上照会</title>
    <!--[if lt IE 9]><script src="/js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=<?php echo date("YmdHis") ?>"></script>
    <script src="/js/S2_sales_shokai.js?p=<?php echo date("YmdHis") ?>"></script>
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
                    <dt>ログインID</dt>
                    <dd>
                        <select name="user_id" id="user_id" class="ip_w50">
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
                    <dt>受注日</dt>
                    <dd><input name="sale_dt" type="date" value="" class="ip_w50"></dd>
                </dl>
                <dl>
                    <dt>受注番号</dt>
                    <dd><input name="order_no" type="tel" maxlength="10" value="" class="ip_w50" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>問合せ番号</dt>
                    <dd>
                        <input name="inquire_no" type="tel" maxlength="12" value="" class="ip_w50" placeholder="">
                    </dd>
                </dl>
                <dl>
                    <dt>電話番号<small>(下4桁)</small></dt>
                    <dd>
                        <input name="tel_last_four" type="tel" maxlength="4" value="" class="ip_w50" placeholder="">
                    </dd>
                </dl>
                <dl>
                    <dt>電話番号</dt>
                    <dd>
                        <input name="tokuisaki_tel" type="tel" maxlength="11" value="" class="ip_w50" placeholder="">
                    </dd>
                </dl>
                <dl>
                    <dt>得意先名</dt>
                    <dd>
                        <input name="tokuisaki_nm" type="text" maxlength="40" value="" class="ip_w100" placeholder="">
                    </dd>
                </dl>
                <!-- <dl>
                    <dt>商品</dt>
                    <dd>
                        <input name="product_cd" type="text" maxlength="3" value="" class="ip_w50" placeholder="">
                    </dd>
                </dl>
                <dl>
                    <dt>商品名</dt>
                    <dd>
                        <input name="product_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder="">
                    </dd>
                </dl> -->
                <div class="btnBlock">
                    <button type="button" class="btnSearch" form="frmSearch_pc" id="searchBtn">検索する</button>
                    <button type="reset" class="btnReset">クリア</button>
                </div>
                <div class="btnBlock_more">
                    <!-- <button type="button" class="btnCommon btnAdd">新規</button> -->
                    <!-- <button type="button" class="btnCommon btnOrder">注文書</button> -->
                    <!-- <button type="button" class="btnCommon btnNouhin">納品書</button> -->
                    <button type="button" class="btnCommon btnDenpyo">伝票</button>
                    <!-- <button type="button" class="btnCommon btnReport">日報・月報</button> -->

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
                <div class="itemSearch_sp_title"><img src="/images/icon_search_g.svg" alt="売上" /><span>操作パネル</span>
                </div>
                <ul class="itemSearch_sp_open">
                    <form class="itemSearch" id="frmSearch_sp">
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
                            <dt>受注日</dt>
                            <dd><input name="order_dt" type="date" value="" class="ip_w50"></dd>
                        </dl>
                        <dl>
                            <dt>受注番号</dt>
                            <dd><input name="order_no" type="tel" maxlength="10" value="" class="ip_w100" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>問合せ番号</dt>
                            <dd>
                                <input name="inquire_no" type="tel" maxlength="20" value="" class="ip_w100" placeholder="">
                            </dd>
                        </dl>
                        <dl>
                            <dt>電話番号<small>(下4桁)</small></dt>
                            <dd>
                                <input name="tel_last_four" type="tel" maxlength="4" value="" class="ip_w100" placeholder="">
                            </dd>
                        </dl>
                        <dl>
                            <dt>電話番号</dt>
                            <dd>
                                <input name="tokuisaki_tel" type="tel" maxlength="11" value="" class="ip_w100" placeholder="">
                            </dd>
                        </dl>
                        <dl>
                            <dt>得意先名</dt>
                            <dd>
                                <input name="tokuisaki_nm" type="text" maxlength="40" value="" class="ip_w100" placeholder="">
                            </dd>
                        </dl>
                        <!-- <dl>
                            <dt>商品</dt>
                            <dd>
                                <input name="product_cd" type="text" maxlength="3" value="" class="ip_w100" placeholder="">
                            </dd>
                        </dl>
                        <dl>
                            <dt>商品名</dt>
                            <dd>
                                <input name="product_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder="">
                            </dd>
                        </dl> -->
                        <div class="btnBlock">
                            <button type="button" class="btnSearch" form="frmSearch_sp" id="searchBtn">検索する</button>
                            <button type="reset" class="btnReset">クリア</button>
                        </div>
                        <div class="btnBlock_more">
                            <!-- <button type="button" class="btnCommon btnAdd">新規</button> -->
                            <!-- <button type="button" class="btnCommon btnOrder">注文書</button> -->
                            <!-- <button type="button" class="btnCommon btnNouhin">納品書</button> -->
                            <button type="button" class="btnCommon btnDenpyo">伝票</button>
                            <!-- <button type="button" class="btnCommon btnReport">日報・月報</button> -->

                        </div>
                    </form>
                </ul>
            </li>
        </ul>

        <article class="content">

            <section id="users">
                <h3>検索結果</h3>
                <div class="btnBlock disnon">
                    <div>
                        <button type="button" class="btnPick pick">全選択</button>
                        <button type="button" class="btnPick unpick">全解除</button>
                    </div>
                    <p class="kensu "></p>
                </div>

                <div class="inner" id="list"></div>

            </section>

            <!-- 検索結果（スマホ） -->
            <section id="users_sp">
                <h3>売上照会</h3>
                <div class="btnBlock">
                    <p class="kensu "></p>
                </div>

                <div class="inner"></div>

            </section>

            <section class="pagenavi disnon" id="pagenation" role="navigation"></section>

            <!-- ページナビ（スマホ） -->
            <section class="pagenavi_sp" id="pagenation_sp" role="navigation"></section>

        </article>

    </main>

    <div class="dialog" id="tokuisaki-search">
        <form class="itemEdit" id="tokuisakiFrm">
            <dl class="itemDetail_sp">
                <dt>得意先</dt>
                <dd>
                    <input type="text" class="ip_w100" id="tokuisaki_cd">
                </dd>
            </dl>
            <dl class="itemDetail_sp">
                <dt>得意先名</dt>
                <dd>
                    <input type="text" class="ip_w100" id="tokuisaki_nm">
                </dd>
            </dl>
            <div class="frm-table disnon">
                <table class="sub-table" id="telTable">
                    <thead>
                        <tr>
                            <th>電話番号</th>
                            <th>得意先名</th>
                        </tr>
                    </thead>
                    <tbody class="sm-table">
                        <!-- <tr>
                            <td>000-000-000</td>
                            <td>得意先01</td>
                        </tr>
                        <tr>
                            <td>111-111-1111</td>
                            <td>得意先02</td>
                        </tr>
                        <tr>
                            <td>333-333-333</td>
                            <td>得意先03</td>
                        </tr>
                        <tr>
                            <td>444-444-4444</td>
                            <td>得意先04</td>
                        </tr>
                        <tr>
                            <td>555-555-555</td>
                            <td>得意先05</td>
                        </tr> -->
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <!-- 売上入力・受注画面 -->
    <div class="dialog " id="saleDialog">
        <iframe src=""></iframe>
        <input type="hidden" id="order_no" value="">
    </div>

    <!-- SHOHIN SEARCH DIALOG -->
    <div class="dialog" id="sub-shohin">
        <form class="itemEdit">
            <dl>
                <dt>商品</dt>
                <dd>
                    <input type="text" class="ip_w100">
                </dd>
            </dl>
            <dl>
                <dt>商品名</dt>
                <dd>
                    <input type="text" class="ip_w100">
                </dd>
            </dl>
            <div class="frm-table">
                <table class="sub-table" id="shohinTable">
                    <thead>
                        <tr>
                            <th>商品</th>
                            <th>商品名</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>0001</td>
                            <td>商品テスト01</td>
                        </tr>
                        <tr>
                            <td>0002</td>
                            <td>商品テスト02</td>
                        </tr>
                        <tr>
                            <td>0003</td>
                            <td>商品テスト03</td>
                        </tr>
                        <tr>
                            <td>0004</td>
                            <td>商品テスト04</td>
                        </tr>
                        <tr>
                            <td>0005</td>
                            <td>商品テスト05</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <!-- 伝票 DIALOG -->
    <div class="dialog" id="denpyo-dialog">
        <p class="err_msg"></p>
        <form class="itemEdit">
            <dl class="denpyo_col">
                <dt>伝票種類</dt>
                <dd>
                    <div>
                        <input type="checkbox" name="" id="sale_slip">
                        <label for="sale_slip">売上伝票</label>
                    </div>
                    <div>
                        <input type="checkbox" name="" id="sale_hikae">
                        <label for="sale_hikae">売上伝票<small>(控)</small></label>
                    </div>
                    <div>
                        <input type="checkbox" name="" id="reciept">
                        <label for="reciept">領収書</label>
                    </div>
                    <div>
                        <input type="checkbox" name="" id="label">
                        <label for="label">荷札</label>
                    </div>
                    <div>
                        <input type="checkbox" name="" id="order_frm">
                        <label for="order_frm">注文書</label>
                    </div>
                </dd>
            </dl>

        </form>
    </div>

</body>

</html>
