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
    <title>商品マスタ</title>
    <!--[if lt IE 9]><script src="js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/M3_shohin_master.js?p=(new Date()).getTime()"></script>
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
                    <dt>商品コード</dt>
                    <dd><input name="product_cd" type="text" maxlength="3" value="" class="ip_w50" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>商品名</dt>
                    <dd><input name="product_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>商品名略称</dt>
                    <dd><input name="product_nm_abrv" type="text" maxlength="10" value="" class="ip_w100" placeholder=""></dd>
                </dl>
                <dl>
                    <dt>商品分類</dt>
                    <dd>
                        <select name="product_type" id="" class="ip_w50">
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
                    <button type="button" class="btnCommon " id="btnPrint">台帳発行</button>
                    <button type="button" class="btnCommon" id="btnCodeList">空き番</button>
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
        <ul class="itemSearch_sp" id="">
            <li>
                <div class="itemSearch_sp_title"><img src="/images/icon_search_g.svg" alt="入荷" /><span>操作パネル</span>
                </div>
                <ul class="itemSearch_sp_open">
                    <form class="itemSearch" id="frmSearch_sp">
                        <dl>
                            <dt>商品コード</dt>
                            <dd><input name="product_cd" type="text" maxlength="3" value="" class="ip_w50" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>商品名</dt>
                            <dd><input name="product_nm" type="text" maxlength="20" value="" class="ip_w100" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>商品名略称</dt>
                            <dd><input name="product_nm_abrv" type="text" maxlength="10" value="" class="ip_w100" placeholder=""></dd>
                        </dl>
                        <dl>
                            <dt>商品分類</dt>
                            <dd>
                                <select name="product_type" id="" class="ip_w50">
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

                <div class="inner" id="list">

                </div>
            </section>

            <!-- 検索結果（スマホ） -->
            <section id="users_sp">
                <h3>商品マスタ</h3>
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
        <form class="itemEdit" id="productFrm" autocomplete="off">
            <dl class="itemDetail_sp "><!-- id-disp -->
                <dt>商品コード</dt>
                <dd>
                    <!-- <label></label> -->
                    <input type="hidden" name="prev_code" id="prev_code">
                    <input name="product_cd" id="product_cd" type="text" maxlength="3" value="" class="ip_w100" required>
                </dd>

            </dl>
            <dl class="itemDetail_sp">
                <dt>商品名</dt>
                <dd>
                    <input name="product_nm" id="product_nm" type="text" value="" class="ip_w100" required>
                </dd>
            </dl>
            <dl class="itemDetail_sp">
                <dt>商品名略称</dt>
                <dd>
                    <input name="product_nm_abrv" id="product_nm_abrv" type="text" maxlength="10" value="" class="ip_w100" required>
                </dd>
            </dl>
            <dl class="itemDetail_sp">
                <dt>商品分類</dt>
                <dd>
                    <select name="product_type" id="product_type" class="ip_w50">
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
            <dl class="itemDetail_sp">
                <dt>売上単位</dt>
                <dd>
                    <!-- <input name="" id="tani" type="text" maxlength="20" value="" class="ip_w50"> -->
                    <select name="sale_tani" id="sale_tani">
                        <?php
                        foreach ($_SESSION["code_list"] as &$obj) {
                            if ($obj["kanri_key"] == "sale.tani") {
                                echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </dd>
            </dl>
            <dl class="itemDetail_sp">
                <dt>売上単価</dt>
                <dd>
                    <input name="sale_price" id="sale_price" type="text" maxlength="8" value="" class="ip_w100 num" required>
                </dd>
            </dl>
            <dl class="itemDetail_sp">
                <dt>仕入単価</dt>
                <dd>
                    <input name="unit_price" id="unit_price" type="text" maxlength="8" value="" class="ip_w100 num">
                </dd>
            </dl>
            <dl class="itemDetail_sp">
                <dt>荷札表示区分</dt>
                <dd>
                    <select name="label_disp_kbn" id="label_disp_kbn" class="ip_w50">
                        <?php
                        foreach ($_SESSION["code_list"] as &$obj) {
                            if ($obj["kanri_key"] == "disp.kbn") {
                                echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </dd>
            </dl>
            <dl class="itemDetail_sp">
                <dt>注文書表示区分</dt>
                <dd>
                    <select name="order_disp_kbn" id="order_disp_kbn" class="ip_w50">
                        <?php
                        foreach ($_SESSION["code_list"] as &$obj) {
                            if ($obj["kanri_key"] == "disp.kbn") {
                                echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </dd>
            </dl>
            <dl class="itemDetail_sp">
                <dt>廃盤商品区分</dt>
                <dd>
                    <select name="haiban_kbn" id="haiban_kbn" class="ip_w50">
                        <?php
                        foreach ($_SESSION["code_list"] as &$obj) {
                            if ($obj["kanri_key"] == "haiban.kbn") {
                                echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </dd>
            </dl>
            <dl class="itemDetail_sp">
                <dt>税区分</dt>
                <dd>
                    <select name="tax_kbn" id="tax_kbn" class="ip_w50">
                        <?php
                        foreach ($_SESSION["code_list"] as &$obj) {
                            if ($obj["kanri_key"] == "tax.kbn") {
                                echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </dd>
            </dl>
            <dl class="itemDetail_sp">
                <dt>セール</dt>
                <dd>
                    <input type="checkbox" name="sale_kbn" id="sale_kbn" value="1">
                </dd>
            </dl>
        </form>
    </div>

    <div class="dialog" id="shohin_print">
        <p class="err_msg">test</p>
        <form class="itemEdit" id="printFrm">
            <h3>商品コードを入力してください。</h3>
            <div class="mt-1em" style="display: flex; flex-direction:column;">

                <dl class="">
                    <dt>From</dt>
                    <dd>
                        <input type="text" class="ip_w100" id="product_cd_from" name="product_from" maxlength="3">
                    </dd>
                </dl>
                <dl class="">
                    <dt>To</dt>
                    <dd>
                        <input type="text" class="ip_w100" id="product_cd_to" name="product_to" maxlength="3">
                    </dd>
                </dl>
            </div>

        </form>
    </div>

    <!-- 商品コード空き番 -->
    <div class="dialog" id="codeListDialog">
        <table>
            <tbody class="list" id="codeList">
                <?php
                for ($i = 0; $i < 50; $i++) {
                    echo '<tr>';
                    for ($j = 0; $j < 20; $j++) {
                        $number = $i + $j * 50;
                        $formattedNumber = str_pad($number, 3, '0', STR_PAD_LEFT);
                        echo '<td id="' . $formattedNumber . '">' . $formattedNumber . '</td>';
                    }
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
