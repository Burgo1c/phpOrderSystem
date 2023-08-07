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
    <title>通常検品</title>
    <!--[if lt IE 9]><script src="js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/S4_kenpin.js?p=(new Date()).getTime()"></script>
</head>

<body>
    <?php
    include_once("common/header.php");
    ?>
    <main role="main">

        <article class="content h_100" style="overflow: auto;">
            <p class="error ip_w100 tac" style="background: none; "></p>

            <fieldset class="">
                <legend>出荷検品情報</legend>
                <div class="row">
                    <dl class="shuka_dt">
                        <dt class="">出荷日</dt>
                        <!-- <dd class="label bk-lightblue" id="shuka_dt"></dd> -->
                        <input type="date" id="shuka_dt">
                    </dl>
                    <dl class="inquire_no">
                        <dt>問合せ番号</dt>
                        <dd>
                            <input type="text" class="ip_w100" id="inquire_no" name="inquire_no" maxlength="12" autofocus>
                        </dd>
                    </dl>
                </div>
            </fieldset>

            <fieldset class="ip_w100 kenpin">
                <legend>スキャン情報</legend>
                <div class="row">
                    <dl class="order_no">
                        <dt class="">受注番号</dt>
                        <dd class="label bk-lighyellow"><span id="order_no"></span></dd>
                    </dl>
                    <dl class="shuka_dt">
                        <dt class="">出荷日</dt>
                        <dd class="label bk-lighyellow"><span id="sale_dt"></span></dd>
                    </dl>
                    <dl class="kosu">
                        <dt class="">個数</dt>
                        <dd class="label bk-lighyellow"><span id="kosu"></span></dd>
                    </dl>

                    <dl class="delivery_kbn">
                        <dt class="">便種</dt>
                        <dd class="label bk-lighyellow"><span id="delivery_kbn"></span></dd>
                    </dl>

                    <dl class="sale_kbn">
                        <dt class="">売上区分</dt>
                        <dd class="label bk-lighyellow"><span id="sale_kbn"></span></dd>
                    </dl>
                </div>
                <div class="row">
                    <dl class="okurisaki_nm">
                        <dt>送り先名</dt>
                        <dd class="label bk-lighyellow"><span id="okurisaki_nm"></span></dd>
                    </dl>

                    <dl class="tel">
                        <dt>電話番号</dt>
                        <dd class="label bk-lighyellow"><span id="okurisaki_tel"></span></dd>
                    </dl>
                </div>
                <div class="row">
                    <dl class="address">
                        <dt>住所</dt>
                        <dd class="label bk-lighyellow"><span id="address"></span></dd>
                    </dl>
                </div>
            </fieldset>

            <form class="kenpinSearch" id="kenpinSearch">
                <dl>
                    <dt>締め回数</dt>
                    <dd>
                        <select name="shuka_print_qty" id="shuka_print_qty" class="ip_w100">
                            <option value="all">全て</option>
                        </select>
                    </dd>
                </dl>
                <dl>
                    <dt>検品区分</dt>
                    <dd>
                        <select name="kenpin_kbn" id="" class="ip_w100">
                            <option value="all">全て</option>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "kenpin.kbn") {
                                    echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </dd>
                </dl>
                <dl>
                    <dt>売上区分</dt>
                    <dd>
                        <select name="sale_kbn" id="" class="ip_w100">
                            <option value="all">全て</option>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "sales.order.kbn") {
                                    echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </dd>
                </dl>
                <dl>
                    <dt>便種区分</dt>
                    <dd>
                        <select name="delivery_kbn" id="" class="ip_w100">
                            <option value="all">全て</option>
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
                <input type="hidden" name="list_cnt" id="list_cnt" value="<?php echo $_SESSION["list_cnt"] ?>">
                <button type="button" class="subBtn" id="searchBtn">検索</button>
            </form>

            <div class="table-wrapper">
                <table class="kenpin-table">
                    <thead>
                        <tr>
                            <th>修正</th>
                            <th class="tac">検品</th>
                            <th>問合せ番号</th>
                            <th>受注番号</th>
                            <th>送り先名</th>
                            <th>住所</th>
                            <th>電話番号</th>
                            <th>個数</th>
                            <th>検品区分</th>
                            <th>締め回数</th>
                        </tr>
                    </thead>
                    <tbody class="list"></tbody>
                </table>
            </div>

            <div class="kenpin-detail">
                <div class="row">
                    <dl class="complete_qty">
                        <dt>検品済件数</dt>
                        <dd class="label bk-lightblue"><span id="complete_qty"></span></dd>
                    </dl>
                    <dl class="complete_kosu">
                        <dt>検品済個数</dt>
                        <dd class="label bk-lightblue"><span id="complete_kosu"></span></dd>
                    </dl>
                    <dl class="kenpin_qty">
                        <dt>未検品件数</dt>
                        <dd class="label bk-lightblue"><span id="kenpin_qty"></span></dd>
                    </dl>
                    <dl class="kenpin_kosu">
                        <dt>未検品個数</dt>
                        <dd class="label bk-lightblue"><span id="kenpin_kosu"></span></dd>
                    </dl>
                </div>
            </div>
        </article>

    </main>

    <!-- 売上入力・受注画面 -->
    <div class="dialog " id="saleDialog">
        <iframe src=""></iframe>
        <input type="hidden" id="order_no" value="">
    </div>

    <div class="dialog" id="checkDialog">
        <div class="checkMsg"></div>
        <br>
        <div>
            検品してよろしいでしょうか?
        </div>
    </div>

</body>

</html>