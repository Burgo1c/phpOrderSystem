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
    <title>日報・月報</title>
    <!--[if lt IE 9]><script src="/js/common/html5.js"></script><![endif]-->
    <script src="/js/common/import.js?p=(new Date()).getTime()"></script>
    <script src="/js/R1_report.js?p=(new Date()).getTime()"></script>
</head>

<body>
    <?php
    include_once("common/header.php");
    ?>
    <main role="main">

        <article class="content">
            <section id="users">
                <!-- <h3>日報・月報</h3> -->
                <p class="err_msg" style="background: none;"></p>
                <form class="mt-1em itemEdit uploadFrm" id="uploadFrm_pc">
                    <dl>
                        <dt>日報区分</dt>
                        <dd>
                            <select name="daily_kbn" id="daily_kbn" class="ip_w100">
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "report.daily.kbn") {
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
                            <select name="sale_kbn" id="sale_kbn" class="ip_w100 sale_kbn">
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "report.sale.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>
                    <dl>
                        <dt>売上日</dt>
                        <dd>
                            <input type="date" class="ip_w50 date_from" name="date_from" id="date_from">
                            <label>～</label>
                            <input type="date" class="ip_w50 date_to" name="date_to" id="date_to">
                        </dd>
                    </dl>

                    <div class="btnBlock">
                        <button type="button" class="btnCommon btnPrint" form="uploadFrm_pc">印刷</button>
                    </div>
                </form>

            </section>

            <section id="users_sp">
                <h3>日報・月報</h3>
                <p class="err_msg"></p>
                <form class="mt-1em itemEdit uploadFrm" id="uploadFrm_sp">
                    <dl>
                        <dt>日報区分</dt>
                        <dd>
                            <select name="daily_kbn" id="daily_kbn" class="ip_w100">
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "report.daily.kbn") {
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
                            <select name="sale_kbn" id="sale_kbn" class="ip_w100 sale_kbn">
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "report.sale.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </dd>
                    </dl>
                    <dl>
                        <dt>売上日</dt>
                        <dd>
                            <input type="date" class="ip_w50 date_from" name="date_from" id="date_from">
                            <label>～</label>
                            <input type="date" class="ip_w50 date_to" name="date_to" id="date_to">
                        </dd>
                    </dl>

                    <div class="btnBlock">
                        <button type="button" class="btnCommon btnPrint" form="uploadFrm_pc">印刷</button>
                    </div>
                </form>
            </section>

        </article>

    </main>

</body>

</html>
