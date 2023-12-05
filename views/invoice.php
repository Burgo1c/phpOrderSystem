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
    <title>請求書発行</title>
    <!--[if lt IE 9]><script src="js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=<?php echo date("YmdHis") ?>"></script>
    <script src="/js/I1_invoice.js?p=<?php echo date("YmdHis") ?>"></script>
</head>

<body>

    <?php
    include_once("common/header.php");
    ?>
    <main role="main">

        <article class="content">
            <section id="users">
                <p class="err_msg" style="background: none;"></p>
                <form class="mt-1em itemEdit uploadFrm" id="printFrm_pc">
                    <dl>
                        <dt>発行伝票</dt>
                        <dd style="justify-content: space-around;">
                            <div>
                                <input type="checkbox" name="invoice" id="invoice" value="invoicePdf" class="">
                                <label for="invoice">請求書</label>
                            </div>
                            <div>
                                <input type="checkbox" name="urikake" id="urikake" value="accountsRecievablePdf" class="">
                                <label for="urikake">売掛金元帳</label>
                            </div>

                        </dd>
                    </dl>
                    <!-- <dl>
                        <dt>日付</dt>
                        <dd>
                            <input type="month" name="invoice_dt" id="invoice_dt" class="ip_w50 invoice_dt">
                        </dd>
                    </dl> -->
                    <dl>
                        <dt>締日</dt>
                        <dd>
                            <input type="text" name="bill_dt" class="ip_w50px bill_dt" id="bill_dt" maxlength="2">
                            <!-- <select name="bill_dt" class="ip_w50px bill_dt" id="bill_dt"></select> -->
                        </dd>
                    </dl>
                    <dl>
                        <dt>期間</dt>
                        <dd>
                            <input type="date" name="dt_from" id="dt_from" class="ip_w50 dt_from">
                            <label>～</label>
                            <input type="date" name="dt_to" id="dt_to" class="ip_w50 dt_to">
                        </dd>
                    </dl>
                    <dl>
                        <dt>代表番号</dt>
                        <dd style="justify-content: space-between;">
                            <input type="text" name="tokuisaki_tel" class="tokuisaki_tel" maxlength="12">
                            <!-- <input type="hidden" name="tokuisaki_cd" class="tokuisaki_cd"> -->
                            <button type="button" class="subBtn">検索</button>
                        </dd>
                    </dl>
                    <!-- <dl class="memo ">
                        <dt class="h-150">備考</dt>
                        <dd class="h-150" style="padding-top: 5px; padding-bottom: 5px;">
                            <textarea name="memo" id="" maxlength="400" class="ip_w100"></textarea>
                        </dd>
                    </dl> -->

                    <div class="btnBlock">
                        <button type="button" class="btnCommon btnPrint" form="printFrm_pc">発行</button>
                    </div>
                </form>
            </section>
            <section id="users_sp">
                <h3>請求書発行</h3>
                <p class="err_msg" style="background: none;"></p>
                <form class="mt-1em itemEdit uploadFrm" id="printFrm_sp">
                    <dl>
                        <dt>発行伝票</dt>
                        <dd style="justify-content: space-around;">
                            <div>
                                <input type="checkbox" name="invoice" id="invoice" value="invoicePdf" class="">
                                <label for="invoice">請求書</label>
                            </div>
                            <div>
                                <input type="checkbox" name="urikake" id="urikake" value="accountsRecievablePdf" class="">
                                <label for="urikake">売掛金元帳</label>
                            </div>

                        </dd>
                    </dl>
                    <!-- <dl>
                        <dt>日付</dt>
                        <dd>
                            <input type="month" name="invoice_dt" id="invoice_dt" class="ip_w50 invoice_dt">
                        </dd>
                    </dl> -->
                    <dl>
                        <dt>締日</dt>
                        <dd>
                            <input type="text" name="bill_dt" class="ip_w50px bill_dt" id="bill_dt" maxlength="2">
                            <!-- <select name="bill_dt" class="ip_w50px bill_dt" id="bill_dt"></select> -->
                        </dd>
                    </dl>
                    <dl>
                        <dt>期間</dt>
                        <dd>
                            <input type="date" name="dt_from" id="dt_from" class="ip_w50 dt_from">
                            <label>～</label>
                            <input type="date" name="dt_to" id="dt_to" class="ip_w50 dt_to">
                        </dd>
                    </dl>
                    <dl>
                        <dt>代表番号</dt>
                        <dd style="justify-content: space-between;">
                            <input type="text" name="tokuisaki_tel" class="tokuisaki_tel ip_w80" maxlength="12">
                            <button type="button" class="subBtn">検索</button>
                        </dd>
                    </dl>
                    <dl class="memo ">
                        <dt class="h-150">備考</dt>
                        <dd class="h-150" style="padding-top: 5px; padding-bottom: 5px;">
                            <textarea name="memo" id="" maxlength="400" class="ip_w100"></textarea>
                        </dd>
                    </dl>

                    <div class="btnBlock">
                        <button type="button" class="btnCommon btnPrint" form="printFrm_sp">発行</button>
                    </div>
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
