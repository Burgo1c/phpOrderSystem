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
    <meta content="株式会社ロジ・グレス" name="author">
    <title>得意先マスタ</title>
    <!--[if lt IE 9]><script src="/js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=<?php echo date("YmdHis") ?>"></script>
    <script src="/js/M2_tokuisaki_master.js?p=<?php echo date("YmdHis") ?>"></script>
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
                    <dt>得意先名</dt>
                    <dd><input name="tokuisaki_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>得意先カナ</dt>
                    <dd><input name="tokuisaki_kana" type="text" maxlength="50" value="" class="ip_w100" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>郵便番号</dt>
                    <dd><input name="tokuisaki_zip" type="tel" maxlength="10" value="" class="ip_w50" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>住所</dt>
                    <dd><input name="tokuisaki_adr" type="text" value="" class="ip_w100" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>電話番号</dt>
                    <dd><input name="tokuisaki_tel" type="tel" maxlength="12" value="" class="ip_w50" placeholder=""></dd>
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
                            <dt>得意先名</dt>
                            <dd><input name="tokuisaki_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>得意先カナ</dt>
                            <dd><input name="tokuisaki_kana" type="text" maxlength="50" value="" class="ip_w100" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>郵便番号</dt>
                            <dd><input name="tokuisaki_zip" type="tel" maxlength="10" value="" class="ip_w100" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>住所</dt>
                            <dd><input name="tokuisaki_adr" type="text" value="" class="ip_w100" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>電話番号</dt>
                            <dd><input name="tokuisaki_tel" type="tel" maxlength="11" value="" class="ip_w100" placeholder=""></dd>
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
                <h3>得意先マスク</h3>
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

            <div style="display: flex;" id="tokuisakiFrmBlock">

                <div class="form-col">
                    <dl class="bt">
                        <dt>代表番号</dt>
                        <dd>
                            <input name="tokuisaki_tel" id="tokuisaki_tel" type="tel" maxlength="11" value="" class="ip_w100" required>
                        </dd>
                    </dl>
                    <dl class="">
                        <dt>得意先名</dt>
                        <dd>
                            <input name="tokuisaki_nm" id="tokuisaki_nm" type="text" maxlength="40" value="" class="ip_w100" required>
                        </dd>
                    </dl>
                    <dl class="">
                        <dt>得意先カナ</dt>
                        <dd>
                            <input name="tokuisaki_kana" id="tokuisaki_kana" type="text" maxlength="64" value="" placeholder="半角カナ" class="ip_w100">
                        </dd>
                    </dl>
                    <dl class="">
                        <dt>〒</dt>
                        <dd>
                            <input name="tokuisaki_zip" id="tokuisaki_zip" type="tel" maxlength="8" class="ip_w100" value="" placeholder="ハイフンなし">
                        </dd>
                    </dl>
                    <div class="livesearch_row bb">
                        <ul class="livesearch" id="tokuisaki_zip_search">
                            <li>test</li>
                        </ul>
                    </div>

                    <dl class="">
                        <dt>得意先住所01</dt>
                        <dd>
                            <input name="tokuisaki_adr_1" id="tokuisaki_adr_1" type="text" maxlength="10" value="" class="ip_w100" placeholder="〇〇府〇〇市〇〇区">
                        </dd>
                    </dl>

                    <dl class="">
                        <dt>得意先住所02</dt>
                        <dd>
                            <input name="tokuisaki_adr_2" id="tokuisaki_adr_2" type="text" maxlength="32" value="" class="ip_w100" placeholder="〇〇町1-0-10">
                        </dd>
                    </dl>
                    <dl class="">
                        <dt>得意先住所03</dt>
                        <dd>
                            <input name="tokuisaki_adr_3" id="tokuisaki_adr_3" type="text" maxlength="32" value="" class="ip_w100" placeholder="ビル名">
                        </dd>
                    </dl>

                    <dl class="">
                        <dt>配達指示</dt>
                        <dd>
                            <input name="tokuisaki_delivery_instruct" id="delivery_instruct" type="text" maxlength="32" value="" class="ip_w100">
                        </dd>
                    </dl>

                    <dl class="">
                        <dt>業種</dt>
                        <dd>
                            <select name="industry_cd" id="industry_cd" class="ip_w100" required>
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "industry.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>

                    <dl class="">
                        <dt>FAX番号</dt>
                        <dd>
                            <input name="tokuisaki_fax" id="tokuisaki_fax" type="tel" maxlength="11" value="" class="ip_w100">
                        </dd>
                    </dl>
                    <dl class="">
                        <dt>予備連絡先</dt>
                        <dd>
                            <input name="fuzai_contact" id="fuzai_contact" type="tel" maxlength="11" value="" class="ip_w100">
                        </dd>
                    </dl>

                    <dl class="">
                        <dt>担当者</dt>
                        <dd>
                            <input name="tanto_nm" id="tanto_nm" type="text" maxlength="20" value="" class="ip_w100">
                        </dd>
                    </dl>
                </div>

                <div class="form-col">
                    <dl id="tokuisakiTelFrm">
                        <table>
                            <thead>
                                <tr>
                                    <th colspan="1">追加電話番号</th>
                                </tr>
                            </thead>
                            <tbody id="telList">

                            </tbody>
                        </table>
                    </dl>

                    <dl class="">
                        <dt>注文書発行</dt>
                        <dd>
                            <select name="order_print_kbn" id="order_print_kbn" class="ip_w100" required>
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "order.print.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>

                    <dl class="">
                        <dt>売上区分</dt>
                        <dd>
                            <select name="sales_kbn" id="sales_kbn" class="ip_w100" required>
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "sale.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>

                    <dl class="">
                        <dt>請求締日</dt>
                        <dd>
                            <input name="bill_dt" id="bill_dt" type="tel" maxlength="2" value="" class="ip_w50">
                        </dd>
                    </dl>

                    <dl class="">
                        <dt>便種</dt>
                        <dd>
                            <select name="delivery_kbn" id="delivery_kbn" class="ip_w100" required>
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "delivery.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>

                    <dl class="">
                        <dt>ヤマト時</dt>
                        <dd>
                            <select name="yamato_kbn" id="yamato_kbn" class="ip_w100" required>
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "yamato.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>

                    <dl class="sagawa-block">
                        <dt>佐川時</dt>
                        <dd>

                            <select name="delivery_time_kbn" id="delivery_time_kbn" required>
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "delivery.time.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <input class="ip_w50px" type="tel" name="delivery_time_hr" id="delivery_time_hr" maxlength="2">
                            <p>：</p>
                            <input class="ip_w50px" type="tel" name="delivery_time_min" id="delivery_time_min" maxlength="2">

                            <select name="delivery_instruct_kbn" id="delivery_instruct_kbn" required>
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "delivery.instruct.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>

                    <dl>
                        <dt>検索対象</dt>
                        <dd>
                            <select name="search_flg" id="search_flg">
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "tokuisaki.search.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>

                </div>
            </div>

            <div class="btnBlock" style="display:flex; justify-content: center;">
                <button type="button" class="subBtn" id="btnOkurisaki">送り先</button>
            </div>

            <div class="memo-block">
                <label for="memo">備考</label>
                <textarea name="comment" id="comment" maxlength="512"></textarea>
            </div>



        </form>
    </div>

    <div class="dialog" id="okurisakiDialog">
        <p class="err_msg"></p>
        <form class="itemEdit" id="okurisakiFrm" autocomplete="off">
            <input type="hidden" name="okurisaki_cd" id="okurisaki_cd">
            <dl class="bt">
                <dt>代表番号</dt>
                <dd>
                    <input name="okurisaki_tel" id="okurisaki_tel" type="tel" maxlength="11" value="" class="ip_w100" required>
                </dd>
            </dl>

            <dl>
                <dt>送り先名</dt>
                <dd>
                    <input name="okurisaki_nm" id="okurisaki_nm" type="text" maxlength="40" value="" class="ip_w100" required>
                </dd>
            </dl>
            <dl class="">
                <dt>送り先カナ</dt>
                <dd>
                    <input name="okurisaki_kana" id="okurisaki_kana" type="text" maxlength="64" placeholder="半角カナ" value="" class="ip_w100">
                </dd>
            </dl>

            <dl class="">
                <dt>〒</dt>
                <dd>
                    <input name="okurisaki_zip" id="okurisaki_zip" type="tel" maxlength="8" value="" class="ip_w50" placeholder="ハイフンなし">

                </dd>
            </dl>

            <dl class="">
                <dt>送り先住所01</dt>
                <dd>
                    <input name="okurisaki_adr_1" id="okurisaki_adr_1" type="text" maxlength="10" value="" class="ip_w100" placeholder="〇〇府〇〇市〇〇区">
                </dd>
            </dl>
            <dl class="">
                <dt>送り先住所02</dt>
                <dd>
                    <input name="okurisaki_adr_2" id="okurisaki_adr_2" type="text" maxlength="32" value="" class="ip_w100" placeholder="〇〇町1-0-10">
                </dd>
            </dl>
            <dl class="">
                <dt>送り先住所03</dt>
                <dd>
                    <input name="okurisaki_adr_3" id="okurisaki_adr_3" type="text" maxlength="32" value="" class="ip_w100" placeholder="ビル名">
                </dd>
            </dl>
            <dl class="">
                <dt>配達指示</dt>
                <dd>
                    <input name="okurisaki_delivery_instruct" id="okurisaki_delivery_instruct" type="text" maxlength="32" value="" class="ip_w100">
                </dd>
            </dl>
            <!-- <dl class="">
                <dt>業種</dt>
                <dd>
                    <select name="okurisaki_industry_cd" id="okurisaki_industry_cd" class="ip_w50">

                    </select>
                </dd>
            </dl> -->
            <dl class="">
                <dt>FAX番号</dt>
                <dd>
                    <input name="okurisaki_fax" id="okurisaki_fax" type="tel" maxlength="11" value="" class="ip_w100">
                </dd>
            </dl>
            <dl class="">
                <dt>予備連絡先</dt>
                <dd>
                    <input name="okurisaki_fuzai_contact" id="okurisaki_fuzai_contact" type="tel" maxlength="11" value="" class="ip_w100">
                </dd>
            </dl>
            <dl class="">
                <dt>担当者</dt>
                <dd>
                    <input name="okurisaki_tanto_nm" id="okurisaki_tanto_nm" type="text" maxlength="20" value="" class="ip_w100">
                </dd>
            </dl>

            <section class="pagenavi disnon" id="okurisaki_nav" role="navigation">
                <a class="prevpostslink" rel="next" href="#" page="1">＜＜</a>
                <span class="pg_num">1/2</span>
                <a class="nextpostslink" rel="next" href="#" page="2">＞＞</a>
            </section>
        </form>
    </div>

    <!-- <div class="dialog" id="telDialog">
        <p class="err_msg"></p>
        <div id="tokuisakiTelFrm" autocomplete="off">
            <table>
                <thead>
                    <tr>
                        <th colspan="1">電話番号</th>
                    </tr>
                </thead>
                <tbody id="telList">
                    
                </tbody>
            </table>
        </div>
    </div> -->

</body>

</html>