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
    <title>出荷日報</title>
    <!--[if lt IE 9]><script src="/js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/S2_shuka_report.js?p=(new Date()).getTime()"></script>
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
                        <dt>荷受人</dt>
                        <dd>
                            <select name="customer_cd" id="customer_cd" class="ip_w100" form="uploadFrm_pc">
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "sales.sender.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>
                    <dl>
                        <dt>出荷日</dt>
                        <dd>
                            <input type="date" name="shuka_dt" class="shuka_dt" form="uploadFrm_pc">
                        </dd>
                    </dl>
                    <dl>
                        <dt>発行区分</dt>
                        <dd class="selectorBox">
                            <div>
                                <input type="radio" form="uploadFrm_pc" name="print_flg" id="print" value="0" checked>
                                <label for="print">発行</label>
                            </div>

                            <div>
                                <input type="radio" form="uploadFrm_pc" name="print_flg" id="re-print" value="1">
                                <label for="re-print">再発行</label>
                            </div>

                            <div class="tac" style="width:50%;">
                                <label>締め回数</label>
                                <select class="ip_w50 count" name="shuka_print_qty"></select>
                                <!-- <input type="text" class="ip_w50" name="shuka_print_qty" value="0" maxlength="7"> -->
                            </div>
                        </dd>
                    </dl>

                    <div class="btnBlock">
                        <button type="button" class="btnCommon btnPrint" form="uploadFrm_pc">印刷</button>
                        <button type="button" class="btnCommon btnCsv" form="uploadFrm_pc">CSV</button>
                    </div>
                </form>

            </section>

            <section id="users_sp">
                <h3>出荷日報</h3>
                <p class="err_msg"></p>
                <form class="mt-1em itemEdit uploadFrm" id="uploadFrm_sp">
                    <dl>
                        <dt>荷受人</dt>
                        <dd>
                            <select name="customer_cd" id="customer_cd" class="ip_w100" form="uploadFrm_sp">
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "sales.sender.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>
                    <dl>
                        <dt>出荷日</dt>
                        <dd>
                            <input type="date" name="shuka_dt" class="shuka_dt" form="uploadFrm_sp">
                        </dd>
                    </dl>
                    <dl>
                        <dt style="height:150px;">発行区分</dt>
                        <dd class="selectorBox" style="height:150px; flex-direction:column; justify-content: center;">
                            <div style="width: 100%;">
                                <input type="radio" form="uploadFrm_sp" name="print_flg" id="print" value="0" checked>
                                <label for="print">発行</label>
                            </div>

                            <div style="width: 100%;">
                                <input type="radio" form="uploadFrm_sp" name="print_flg" id="re-print" value="1">
                                <label for="re-print">再発行</label>
                            </div>

                            <div class="" style="width:100%;">
                                <label>締め回数</label>
                                <select class="ip_w50 count" name="shuka_print_qty"></select>
                                <!-- <input type="text" class="ip_w50" name="shuka_print_qty" value="0" maxlength="7"> -->
                            </div>
                        </dd>
                    </dl>

                    <div class="btnBlock">
                        <button type="button" class="btnCommon btnPrint" form="uploadFrm_sp">印刷</button>
                    </div>
                </form>
            </section>

        </article>

    </main>

</body>

</html>