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

if ($_SESSION['errMsg'] != "") {
  echo '<script>alert("' . $_SESSION['errMsg'] . '")</script>';
  $_SESSION['errMsg'] = "";
}
?>

<!DOCTYPE html>
<html xml:lang="ja" lang="ja">

<head>
  <meta name="robots" content="noindex,nofollow" />
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=0" />
  <meta content="" name="description" />
  <meta content="" name="keywords" />
  <meta content="株式会社ロジ・グレス" name="author">
  <title>売上入力</title>
  <!--[if lt IE 9]><script src="/js/common/html5.js"></script><![endif]-->
  <script src="/js/common/import.js?p=(new Date()).getTime()"></script>

</head>

<body>

  <?php
  include_once("common/header.php");
  ?>

  <main role="main" class="main">

    <?php
      include_once("common/sales_form.php");
    ?>

  </main>


</body>
  
</html>