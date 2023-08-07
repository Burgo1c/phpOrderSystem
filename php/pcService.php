<?php
ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
ini_set('max_execution_time', 0);
date_default_timezone_set('Asia/Tokyo');

require_once("config.php");
require_once("pdf.php");
require_once("logger.php");
require_once('phpmail.php');

/**
 * FILE ERROR CHECK
 * - Get error message in relation to error code
 * @param String $err Error code
 * @return String Error message
 */
function fileErrorCheck($err)
{
    $msg = "";
    switch ($err) {
        case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
            $msg = "ファイル サイズが大きすぎてアップロードできません。";
            break;
        case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
            $msg = "ファイル サイズが大きすぎてアップロードできません。";
            break;
        case 3: //uploaded file was only partially uploaded
            $msg = "アップロードされたファイルは一部のみしかアップロードされていません。";
            break;
        case 4: //no file was uploaded
            $msg = "ファイルを選択してください。";
            break;
        default: //a default error
            $msg = "アップロードに問題が発生しました。";
            break;
    }
    return $msg;
};

$dbh = null;
$sth = null;
$type = isset($_REQUEST["Type"]) ? $_REQUEST["Type"] : "";
if ($type == "") {
    header('Location:login.php');
    exit;
}

try {
    $dbh = new PDO(DB_CON_STR, DB_USER, DB_PASS);

    $current_date = date("Y/m/d H:i:s");
    $current_date_numeric = date("YmdHis");
    $dbh->beginTransaction();
    switch ($type) {
            /** ログイン **/
        case "login":
            if (!isset($_REQUEST["user_id"]) || $_REQUEST["user_id"] == "") throw new Exception("ユーザーIDを入力してください。");
            if (!isset($_REQUEST["password"]) || $_REQUEST["password"] == "") throw new Exception("パスワードを入力してください。");

            $sql = "SELECT user_id
                    , user_nm
                    , auth_cd
                    , password
                    FROM m_user
                    WHERE user_id = :user_id;";

            $params = array();
            $params["user_id"] = $_REQUEST["user_id"];
            // $params["password"] = $_REQUEST["password"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("ユーザーID、パスワードが正しくありません。");

            if (!password_verify($_REQUEST["password"], $list[0]["password"])) throw new Exception("ユーザーID、パスワードが正しくありません。");

            // GET CODE MASTER
            $sql = "SELECT kanri_key
                            ,kanri_key_nm
                            ,kanri_cd
                            ,kanri_nm
                            FROM m_code
                            ORDER BY kanri_key, kanri_cd;";

            $sth = $dbh->prepare($sql);
            $sth->execute();
            $code_list = $sth->fetchAll(PDO::FETCH_ASSOC);

            //GET USER LIST
            $sql = "SELECT user_id
                    ,user_nm
                    FROM m_user
                    ORDER BY user_id;";

            $sth = $dbh->prepare($sql);
            $sth->execute();
            $user_list = $sth->fetchAll(PDO::FETCH_ASSOC);

            //ORDER NO
            $sql = "SELECT lpad(CAST(nextval('seq_order_no') as character varying) , 10 , '0')";
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $order_no = $sth->fetchColumn();

            //GET 次回内容
            $sql = "SELECT 
                    c.kanri_cd
                    , c.kanri_nm
                    , c1.kanri_nm AS color
                    FROM m_code c
                    LEFT JOIN m_code c1 ON c.kanri_cd = c1.kanri_cd AND c1.kanri_key = 'jikai.kbn.color'
                    WHERE c.kanri_key = 'jikai.kbn'";
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $jikai_kbn_list = $sth->fetchAll();

            //START SESSION
            session_start();
            $_SESSION['created'] = time();
            $_SESSION['errMsg'] = "";
            $_SESSION['user_id'] = $list[0]["user_id"];
            $_SESSION['user_nm'] = $list[0]["user_nm"];
            $_SESSION['auth_cd'] = $list[0]["auth_cd"];
            $_SESSION['code_list'] = $code_list;
            $_SESSION["user_list"] = $user_list;
            $_SESSION["order_no"] = $order_no;
            $_SESSION["list_cnt"] = LIST_CNT;
            $_SESSION["jikai_kbn_list"] = $jikai_kbn_list;
            $_SESSION["shuka_send_time"] = null;

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** ログアウト **/
        case "logout":
            session_start();
            session_unset();
            session_destroy();
            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** トップ & 売上照会**/
        case "getSaleList":
            session_start();
            $_SESSION['created'] = time();

            $offset = LIST_CNT * ($_REQUEST["pagenum"] - 1);

            // $sql = "SELECT 
            //         order_no
            //         , inquire_no
            //         , sale_dt
            //         , tokuisaki_nm
            //         , user_id
            //         , user_nm
            //         FROM ";

            // $sub = "(SELECT h.order_no as order_no
            //             , h.inquire_no as inquire_no
            //             , TO_CHAR(h.sale_dt,'YYYY/MM/DD') as sale_dt
            //             , COALESCE(t.tokuisaki_nm, '存在しない') as tokuisaki_nm
            //             , h.entry_user_id as user_id
            //             , COALESCE(u.user_nm, '存在しない') AS user_nm
            //             FROM t_sale_h h
            //             LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
            //             LEFT JOIN m_user u ON h.entry_user_id = u.user_id";
            //LEFT JOIN t_sale_d d ON h.order_no = d.order_no
            //LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
            $sql = "SELECT 
                        h.order_no as order_no
                        , h.inquire_no as inquire_no
                        , TO_CHAR(h.sale_dt,'YYYY/MM/DD') as sale_dt
                        , COALESCE(CONCAT(c.kanri_nm,c2.kanri_nm,c3.kanri_nm,t.tokuisaki_nm), '存在しない') as tokuisaki_nm
                        , COALESCE(u.user_nm, '存在しない') AS user_nm
                        FROM t_sale_h h
                        LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                        LEFT JOIN m_user u ON h.entry_user_id = u.user_id
                        LEFT JOIN m_code c ON t.jikai_kbn_1 = c.kanri_cd AND c.kanri_key = 'jikai.kbn' AND t.jikai_kbn_1 <> '0'
                        LEFT JOIN m_code c2 ON t.jikai_kbn_2 = c2.kanri_cd AND c2.kanri_key = 'jikai.kbn' AND t.jikai_kbn_2 <> '0'
                        LEFT JOIN m_code c3 ON t.jikai_kbn_3 = c3.kanri_cd AND c3.kanri_key = 'jikai.kbn' AND t.jikai_kbn_3 <> '0'";

            $whr = " WHERE 1=1";

            $params = array();
            if (isset($_REQUEST["sale_dt"]) && $_REQUEST["sale_dt"] != "") {
                $whr .= " AND h.sale_dt = :sale_dt";
                $params["sale_dt"] = $_REQUEST["sale_dt"];
            };
            if (isset($_REQUEST["user_id"]) && $_REQUEST["user_id"] != "all") {
                $whr .= " AND h.entry_user_id = :user_id";
                $params["user_id"] = $_REQUEST["user_id"];
            };
            if (isset($_REQUEST["tokuisaki_nm"]) && $_REQUEST["tokuisaki_nm"] != "") {
                $whr .= " AND t.tokuisaki_nm LIKE :tokuisaki_nm";
                $params["tokuisaki_nm"] = "%" . $_REQUEST["tokuisaki_nm"] . "%";
            };
            if (isset($_REQUEST["order_no"]) && $_REQUEST["order_no"] != "") {
                $whr .= " AND h.order_no = :order_no";
                $params["order_no"] = $_REQUEST["order_no"];
            };
            if (isset($_REQUEST["inquire_no"]) && $_REQUEST["inquire_no"] != "") {
                $whr .= " AND h.inquire_no = :inquire_no";
                $params["inquire_no"] = $_REQUEST["inquire_no"];
            };
            if (isset($_REQUEST["tel_last_four"]) && $_REQUEST["tel_last_four"] != "") {
                $whr .= " AND t.tokuisaki_tel LIKE :tel_last_four";
                $params["tel_last_four"] = "%" . $_REQUEST["tel_last_four"];
            };
            if (isset($_REQUEST["tokuisaki_tel"]) && $_REQUEST["tokuisaki_tel"] != "") {
                //$whr .= " AND tel.tel_no = :tokuisaki_tel";
                $whr .= " AND t.tokuisaki_cd IN (SELECT tel.tokuisaki_cd FROM m_tokuisaki_tel tel WHERE tel.tel_no LIKE :tokuisaki_tel)";
                $params["tokuisaki_tel"] = "%" . $_REQUEST["tokuisaki_tel"] . "%";
            };
            // if (isset($_REQUEST["product_cd"]) && $_REQUEST["product_cd"] != "") {
            //     $whr .= " AND s.product_cd = :product_cd";
            //     $params["product_cd"] = $_REQUEST["product_cd"];
            // };
            // if (isset($_REQUEST["product_nm"]) && $_REQUEST["product_nm"] != "") {
            //     $whr .= " AND s.product_nm LIKE :product_nm";
            //     $params["product_nm"] = "%" . $_REQUEST["product_nm"] . "%";
            // };

            // $sql .= $sub . $whr . ") sub ORDER BY order_no DESC LIMIT " . LIST_CNT . " OFFSET " . $offset;
            $sql .= $whr . " ORDER BY h.sale_dt DESC, h.order_no DESC LIMIT " . LIST_CNT . " OFFSET " . $offset;
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            $sql = "SELECT COUNT(order_no) 
                    FROM t_sale_h h 
                    LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN m_user u ON h.entry_user_id = u.user_id" . $whr;
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetch();

            $total_pg = ceil($cnt[0] / LIST_CNT);

            array_push($list, array("total_page" => $total_pg, "count" => $cnt[0]));

            echo json_encode($list, JSON_UNESCAPED_UNICODE);

            break;

            /** ユーザーリスト **/
        case "userList":
            session_start();
            $_SESSION['created'] = time();

            $offset = LIST_CNT * ($_REQUEST["pagenum"] - 1);

            $sql = "SELECT user_id
                    , user_nm
                    , kanri_nm as auth
                    FROM m_user u
                    LEFT JOIN m_code c ON u.auth_cd = c.kanri_cd 
                    AND c.kanri_key = 'auth' ";
            $whr = "WHERE 1=1 ";

            $params = array();
            if ($_REQUEST["user_id"] != "") {
                $params["user_id"] = $_REQUEST["user_id"];
                $whr .= " AND user_id = :user_id ";
            };
            if ($_REQUEST["user_nm"] != "") {
                $params["user_nm"] = "%" . $_REQUEST["user_nm"] . "%";
                $whr .= " AND user_nm LIKE :user_nm ";
            };
            if ($_REQUEST["auth_cd"] != "all") {
                $params["auth_cd"] = $_REQUEST["auth_cd"];
                $whr .= " AND auth_cd = :auth_cd ";
            };
            $sql .= $whr . "ORDER BY user_id LIMIT " . LIST_CNT . " OFFSET " . $offset;

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            if (count($list) > 0) {
                $sql = "SELECT COUNT(*) FROM m_user " . $whr;
                $sth = $dbh->prepare($sql);
                $sth->execute($params);
                $cnt = $sth->fetch();

                $total_pg = ceil($cnt[0] / LIST_CNT);

                array_push($list, array("total_page" => $total_pg, "count" => $cnt[0]));
            };

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;

            /** ユーザー詳細 **/
        case "userDetail":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["user_id"]) || $_REQUEST["user_id"] == "") throw new Exception("ユーザーIDを入力してください。");

            $sql = "SELECT user_id
                    , user_nm
                    , auth_cd
                    FROM m_user
                    WHERE user_id = :user_id;";

            $params = array();
            $params["user_id"] = $_REQUEST["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetch(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;

            /** ユーザー作成 **/
        case "userAdd":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["user_id"]) || $_REQUEST["user_id"] == "") throw new Exception("ユーザーIDを入力してください。");

            $sql = "SELECT COUNT(*) FROM m_user WHERE user_id = :user_id;";

            $params = array();
            $params["user_id"] = $_REQUEST["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetch();

            if ($cnt[0] > 0) throw new Exception("既に同じユーザーIDが登録されています。");

            $sql = "INSERT INTO m_user(
                    user_id
                    , user_nm
                    , password
                    , auth_cd
                    , entry_user_id
                    , entry_date
                    , update_user_id
                    , update_date
                    )VALUES(
                    :user_id
                    , :user_nm
                    , :password
                    , :auth_cd
                    , :entry_user
                    , CURRENT_TIMESTAMP
                    , :entry_user
                    , CURRENT_TIMESTAMP);";

            $params = array();
            $params["user_id"] = $_REQUEST["user_id"];
            $params["user_nm"] = $_REQUEST["user_nm"];
            $params["password"] = password_hash($_REQUEST["password"], PASSWORD_BCRYPT, array('cost' => 12));
            $params["auth_cd"] = $_REQUEST["auth_cd"];
            $params["entry_user"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** ユーザー更新 **/
        case "userUpdate":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["user_id"]) || $_REQUEST["user_id"] == "") throw new Exception("ユーザーIDを入力してください。");

            $sql = "UPDATE m_user SET
                    user_nm = :user_nm
                    , auth_cd = :auth_cd
                    , update_user_id = :update_user
                    , update_date = CURRENT_TIMESTAMP
                    WHERE user_id = :user_id";

            $params = array();
            $params["user_id"] = $_REQUEST["user_id"];
            $params["user_nm"] = $_REQUEST["user_nm"];
            $params["auth_cd"] = $_REQUEST["auth_cd"];
            $params["update_user"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $icnt = $sth->rowCount();

            if ($icnt == 0) throw new Exception("ユーザー更新に失敗しました。");

            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** ユーザー削除 **/
        case "userDelete":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["user_id"]) || $_REQUEST["user_id"] == "") throw new Exception("ユーザーIDを入力してください。");

            $sql = "DELETE FROM m_user WHERE user_id = :user_id;";

            $params = array();
            $params["user_id"] = $_REQUEST["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);

            break;

            /** 得意先リスト **/
        case "tokuisakiList":
            session_start();
            $_SESSION['created'] = time();

            $offset = LIST_CNT * ($_REQUEST["pagenum"] - 1);

            $sql = "SELECT 
            t.tokuisaki_cd
            , CONCAT(c.kanri_nm,c2.kanri_nm,c3.kanri_nm,tokuisaki_nm) AS tokuisaki_nm
            , tokuisaki_zip
            , tokuisaki_adr_1
            , tokuisaki_adr_2
            , tokuisaki_adr_3
            , tokuisaki_tel
            , tanto_nm
            FROM m_tokuisaki t
            LEFT JOIN m_code c ON t.jikai_kbn_1 = c.kanri_cd AND c.kanri_key = 'jikai.kbn' AND t.jikai_kbn_1 <> '0'
            LEFT JOIN m_code c2 ON t.jikai_kbn_2 = c2.kanri_cd AND c2.kanri_key = 'jikai.kbn' AND t.jikai_kbn_2 <> '0'
            LEFT JOIN m_code c3 ON t.jikai_kbn_3 = c3.kanri_cd AND c3.kanri_key = 'jikai.kbn' AND t.jikai_kbn_3 <> '0'";
            $whr =  "WHERE 1=1 ";

            $params = array();

            if (isset($_REQUEST["tokuisaki_nm"]) && $_REQUEST["tokuisaki_nm"] != "") {
                $params["tokuisaki_nm"] = "%" . $_REQUEST["tokuisaki_nm"] . "%";
                $whr .= " AND tokuisaki_nm LIKE :tokuisaki_nm";
            };

            if (isset($_REQUEST["tokuisaki_kana"]) && $_REQUEST["tokuisaki_kana"] != "") {
                $params["tokuisaki_kana"] = "%" . $_REQUEST["tokuisaki_kana"] . "%";
                $whr .= " AND tokuisaki_kana LIKE :tokuisaki_kana";
            };

            if (isset($_REQUEST["tokuisaki_zip"]) && $_REQUEST["tokuisaki_zip"] != "") {
                $params["zip"] = $_REQUEST["tokuisaki_zip"];
                $whr .= " AND tokuisaki_zip = :zip";
            };

            if (isset($_REQUEST["tokuisaki_tel"]) && $_REQUEST["tokuisaki_tel"] != "") {
                $params["tel"] = "%" . $_REQUEST["tokuisaki_tel"] . "%";
                // $whr .= " AND tel.tel_no = :tel";
                $whr .= " AND t.tokuisaki_cd IN (SELECT tel.tokuisaki_cd FROM m_tokuisaki_tel tel WHERE tel.tel_no LIKE :tel)";
            };

            $sql .= $whr . " ORDER BY tokuisaki_cd LIMIT " . LIST_CNT . " OFFSET " . $offset;

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            if (count($list) > 0) {
                $sql = "SELECT COUNT(*) FROM m_tokuisaki t " . $whr;
                $sth = $dbh->prepare($sql);
                $sth->execute($params);
                $cnt = $sth->fetch();

                $total_pg = ceil($cnt[0] / LIST_CNT);

                array_push($list, array("total_page" => $total_pg, "count" => $cnt[0]));
            }

            echo json_encode($list, JSON_UNESCAPED_UNICODE);

            break;

            /** 得意先詳細 **/
        case "tokuisakiDetail":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先コードを入力してください。");

            $sql = "SELECT
                tokuisaki_cd
                , tokuisaki_nm
                , tokuisaki_kana
                , tokuisaki_zip
                , tokuisaki_adr_1
                , tokuisaki_adr_2
                , tokuisaki_adr_3
                , tokuisaki_tel
                , tokuisaki_fax
                , delivery_kbn
                , delivery_time_kbn
                , delivery_time_hr
                , delivery_time_min
                , delivery_instruct_kbn
                , tanto_nm
                , fuzai_contact
                , industry_cd
                , order_print_kbn
                , delivery_instruct
                , bill_dt
                , sale_kbn AS sales_kbn
                , comment
                , yamato_kbn
                , search_flg
                FROM m_tokuisaki
                WHERE tokuisaki_cd = :tokuisaki_cd";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list[0], JSON_UNESCAPED_UNICODE);
            break;
            /** GET TOKUIKSAKI BY ID **/
        case "getTokuisakiById":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先コードを入力してください。");

            $sql = "SELECT
                tokuisaki_cd
                , tokuisaki_nm AS tokuisaki_nm           
                , tokuisaki_kana
                , tokuisaki_zip
                , tokuisaki_adr_1
                , tokuisaki_adr_2
                , tokuisaki_adr_3
                , tokuisaki_tel
                , tokuisaki_fax
                , delivery_kbn
                , delivery_time_kbn
                , delivery_time_hr
                , delivery_time_min
                , delivery_instruct_kbn
                , tanto_nm
                , fuzai_contact
                , industry_cd
                , order_print_kbn
                , fuzai_contact
                , delivery_instruct
                , bill_dt
                , sale_kbn
                , yamato_kbn
                , comment
                , COALESCE(t.jikai_kbn_1, '0') AS jikai_kbn_1
                , COALESCE(c.kanri_nm, '') AS jikai_kbn_1_nm
                , COALESCE(t.jikai_kbn_2, '0') AS jikai_kbn_2
                , COALESCE(c2.kanri_nm, '') AS jikai_kbn_2_nm
                , COALESCE(t.jikai_kbn_3, '0') AS jikai_kbn_3
                , COALESCE(c3.kanri_nm, '') AS jikai_kbn_3_nm
                FROM m_tokuisaki t
                LEFT JOIN m_code c ON t.jikai_kbn_1 = c.kanri_cd AND c.kanri_key = 'jikai.kbn' AND t.jikai_kbn_1 <> '0'
                LEFT JOIN m_code c2 ON t.jikai_kbn_2 = c2.kanri_cd AND c2.kanri_key = 'jikai.kbn' AND t.jikai_kbn_2 <> '0'
                LEFT JOIN m_code c3 ON t.jikai_kbn_3 = c3.kanri_cd AND c3.kanri_key = 'jikai.kbn' AND t.jikai_kbn_3 <> '0'
                WHERE tokuisaki_cd = :tokuisaki_cd
                AND t.search_flg = '1';";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list[0], JSON_UNESCAPED_UNICODE);
            break;

            /** 得意先作成 **/
        case "tokuisakiAdd":
            session_start();
            $_SESSION['created'] = time();

            //YAMATO ZIP CHECK
            if (isset($_REQUEST["delivery_kbn"]) && $_REQUEST["delivery_kbn"] == "1" && $_REQUEST["sales_kbn"] == "1" && $_REQUEST["tokuisaki_zip"] != "") {
                $sql = "SELECT delivery_cd FROM m_yamato WHERE key_part = :zip;";
                $params = array();
                $params["zip"] = $_REQUEST["tokuisaki_zip"];
                $sth = $dbh->prepare($sql);
                $sth->execute($params);
                $zip = $sth->fetchColumn();

                if ($zip == "") throw new Exception($_REQUEST["tokuisaki_zip"] . "がヤマト郵便番号対応仕分マスタに存在しません。");
            };

            if (isset($_REQUEST["sale_reg"]) && $_REQUEST["sale_reg"] === "1") {
                $sql  = "SELECT COUNT(tel_no) FROM m_tokuisaki_tel WHERE tel_no = :tokuisaki_tel;";
                $params = array();
                $params["tokuisaki_tel"] = $_REQUEST["tokuisaki_tel"];
                // $sql = "SELECT COUNT(tel_no) FROM m_tokuisaki_tel WHERE tel_no IN (";

                $tel = $_REQUEST["tokuisaki_tel"];
                // $sql .= "'$tel'";

                //$lst4dig = substr($_REQUEST["tokuisaki_tel"], -4);
                //$sql .= ", '$lst4dig'";

                // if ($_REQUEST["tokuisaki_fax"] != "") {
                //     $fax = $_REQUEST["tokuisaki_fax"];
                //     $sql .= ", '$fax'";
                // };

                // if ($_REQUEST["fuzai_contact"] != "") {
                //     $fuzai = $_REQUEST["fuzai_contact"];
                //     $sql .= ", '$fuzai'";
                // };
                //  $sql .= ")";
                $sth = $dbh->prepare($sql);
                $sth->execute($params);
                $cnt = $sth->fetchColumn();

                //if ($cnt != 0) throw new Exception("代表電話、FAX番号、予備連絡先のいずれかはすでに別の得意先に登録されています。");
                if ($cnt != 0) throw new Exception("電話番号[$tel]はすでに別の得意先に登録されています。");
            } else {
                //PHONE CHECK
                $rows = json_decode($_REQUEST["tel_rows"], true);
                if (count($rows) == 0 || $_REQUEST["tel_rows"] == "") throw new Exception("追加電話番号を入力してください。");

                $sql = "SELECT COUNT(tel_no) FROM m_tokuisaki_tel WHERE tel_no = :tel_no";
                $sth = $dbh->prepare(($sql));
                $params = array();

                for ($i = 0; $i < count($rows); $i++) {
                    $tel = $rows[$i]["tel"];
                    $params["tel_no"] = $rows[$i]["tel"];
                    $sth->execute($params);
                    $cnt = $sth->fetchColumn();
                    if ($cnt != 0) throw new Exception("電話番号[$tel]はすでに別の得意先に登録されています。");
                }
            }

            //INSERT TOKUISAKI
            $sql = "INSERT INTO m_tokuisaki (
                tokuisaki_cd
                , tokuisaki_nm
                , tokuisaki_kana
                , tokuisaki_zip
                , tokuisaki_adr_1
                , tokuisaki_adr_2
                , tokuisaki_adr_3
                , tokuisaki_tel
                , tokuisaki_fax
                , delivery_kbn
                , delivery_time_kbn
                , delivery_time_hr
                , delivery_time_min
                , delivery_instruct_kbn
                , tanto_nm
                , fuzai_contact
                , industry_cd
                , order_print_kbn
                , delivery_instruct
                , bill_dt
                , sale_kbn
                , yamato_kbn
                , comment
                , entry_user_id
                , entry_date
                , update_user_id
                , update_date
                , search_flg
                ) VALUES (
                nextval('seq_tokuisaki_cd')
                , :tokuisaki_nm
                , :tokuisaki_kana
                , :tokuisaki_zip
                , :tokuisaki_adr_1
                , :tokuisaki_adr_2
                , :tokuisaki_adr_3
                , :tokuisaki_tel
                , :tokuisaki_fax
                , :delivery_kbn
                , :delivery_time_kbn
                , :delivery_time_hr
                , :delivery_time_min
                , :delivery_instruct_kbn
                , :tanto_nm
                , :fuzai_contact
                , :industry_cd
                , :order_print_kbn
                , :delivery_instruct
                , :bill_dt
                , :sale_kbn
                , :yamato_kbn
                , :comment
                , :entry_user_id
                , CURRENT_TIMESTAMP
                , :entry_user_id
                , CURRENT_TIMESTAMP
                , :search_flg);";

            $params = array();
            $params["tokuisaki_nm"] = $_REQUEST["tokuisaki_nm"] ?? "";
            $params["tokuisaki_kana"] = $_REQUEST["tokuisaki_kana"] ?? "";
            $params["tokuisaki_zip"] = $_REQUEST["tokuisaki_zip"] ?? "";
            $params["tokuisaki_adr_1"] = $_REQUEST["tokuisaki_adr_1"] ?? "";
            $params["tokuisaki_adr_2"] = $_REQUEST["tokuisaki_adr_2"] ?? "";
            $params["tokuisaki_adr_3"] = $_REQUEST["tokuisaki_adr_3"] ?? "";
            $params["tokuisaki_tel"] = $_REQUEST["tokuisaki_tel"] ?? "";
            $params["tokuisaki_fax"] =  $_REQUEST["tokuisaki_fax"] ?? "";
            $params["delivery_kbn"] = $_REQUEST["delivery_kbn"] ?? "1";
            $params["delivery_time_kbn"] = $_REQUEST["delivery_time_kbn"] ?? "1";
            $params["delivery_time_hr"] = $_REQUEST["delivery_time_hr"] ?? "";
            $params["delivery_time_min"] = $_REQUEST["delivery_time_min"] ?? "";
            $params["delivery_instruct_kbn"] = $_REQUEST["delivery_instruct_kbn"] ?? "1";
            $params["tanto_nm"] = $_REQUEST["tanto_nm"] ?? "";
            $params["fuzai_contact"] = $_REQUEST["fuzai_contact"] ?? "";
            $params["industry_cd"] = $_REQUEST["industry_cd"] ?? "1";
            $params["order_print_kbn"] = $_REQUEST["order_print_kbn"] ?? "1";
            $params["delivery_instruct"] = $_REQUEST["tokuisaki_delivery_instruct"] ?? "";
            $params["bill_dt"] = $_REQUEST["bill_dt"] ?? "";
            $params["sale_kbn"] = $_REQUEST["sales_kbn"] ?? "1";
            $params["yamato_kbn"] = $_REQUEST["yamato_kbn"] ?? "0";
            $params["comment"] = $_REQUEST["comment"] ?? "";
            $params["entry_user_id"] = $_SESSION["user_id"];
            $params["search_flg"] = $_REQUEST["search_flg"] ?? "1";

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            //INSERT OKURISAKI
            $sql = "INSERT INTO m_okurisaki(
                            tokuisaki_cd
                            , okurisaki_cd
                            , okurisaki_nm
                            , okurisaki_kana
                            , okurisaki_zip
                            , okurisaki_adr_1
                            , okurisaki_adr_2
                            , okurisaki_adr_3
                            , okurisaki_tel
                            , okurisaki_fax
                            , tanto_nm
                            , fuzai_contact
                            , delivery_instruct
                            , entry_user_id
                            , entry_date
                            , update_user_id
                            , update_date
                            )VALUES(
                            currval('seq_tokuisaki_cd')
                            , :okurisaki_cd
                            , :okurisaki_nm
                            , :okurisaki_kana
                            , :okurisaki_zip
                            , :okurisaki_adr_1
                            , :okurisaki_adr_2
                            , :okurisaki_adr_3
                            , :okurisaki_tel
                            , :okurisaki_fax
                            , :tanto_nm
                            , :fuzai_contact
                            , :delivery_instruct
                            , :user_id
                            , CURRENT_TIMESTAMP
                            , :user_id
                            , CURRENT_TIMESTAMP)";

            $params = array();
            //$params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["okurisaki_cd"] = sprintf("%010d", 1);
            $params["okurisaki_nm"] = $_REQUEST["tokuisaki_nm"];
            $params["okurisaki_kana"] = $_REQUEST["tokuisaki_kana"];
            $params["okurisaki_zip"] = $_REQUEST["tokuisaki_zip"];
            $params["okurisaki_adr_1"] = $_REQUEST["tokuisaki_adr_1"];
            $params["okurisaki_adr_2"] = $_REQUEST["tokuisaki_adr_2"];
            $params["okurisaki_adr_3"] = $_REQUEST["tokuisaki_adr_3"];
            $params["okurisaki_tel"] = $_REQUEST["tokuisaki_tel"];
            $params["okurisaki_fax"] = $_REQUEST["tokuisaki_fax"];
            $params["tanto_nm"] = $_REQUEST["tanto_nm"];
            $params["fuzai_contact"] = $_REQUEST["fuzai_contact"];
            $params["delivery_instruct"] = $_REQUEST["tokuisaki_delivery_instruct"];
            $params["user_id"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            //TOKUISAKI TEL INSERT
            $sql = "INSERT INTO m_tokuisaki_tel(
                            tokuisaki_cd
                            ,tel_no
                            )VALUES(
                            currval('seq_tokuisaki_cd')
                            ,:tel_no)";
            $params = array();
            $sth = $dbh->prepare($sql);
            if (isset($_REQUEST["sale_reg"]) && $_REQUEST["sale_reg"] === "1") {
                    $params["tel_no"] = $_REQUEST["tokuisaki_tel"];
                    $sth->execute($params);

                // $params["tel_no"] = substr($_REQUEST["tokuisaki_tel"], -4);
                // if ($params["tel_no"] !== $_REQUEST["tokuisaki_tel"]) {
                //     $sth->execute($params);
                // }

                //     if (
                //         $_REQUEST["tokuisaki_fax"] !== "" &&
                //         $_REQUEST["tokuisaki_fax"] !== $_REQUEST["tokuisaki_tel"] &&
                //         $_REQUEST["tokuisaki_fax"] !== $_REQUEST["fuzai_contact"]
                //     ) {

                //         $params["tel_no"] = $_REQUEST["tokuisaki_fax"];
                //         $sth->execute($params);
                //     }

                //     if (
                //         $_REQUEST["fuzai_contact"] !== "" &&
                //         $_REQUEST["fuzai_contact"] !== $_REQUEST["tokuisaki_tel"] &&
                //         $_REQUEST["fuzai_contact"] !== $_REQUEST["tokuisaki_fax"]
                //     ) {

                //         $params["tel_no"] = $_REQUEST["fuzai_contact"];
                //         $sth->execute($params);
                //     };
            } else {
                $rows = json_decode($_REQUEST["tel_rows"], true);
                if (count($rows) == 0 || $_REQUEST["tel_rows"] == "") throw new Exception("追加電話番号を入力してください。");
                for ($i = 0; $i < count($rows); $i++) {
                    $params["tel_no"] = $rows[$i]["tel"];
                    $sth->execute($params);
                };
            }

            $sql = "SELECT currval('seq_tokuisaki_cd');";
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $data = $sth->fetchColumn();

            $dbh->commit();

            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            break;

            /** 得意先更新 **/
        case "tokuisakiUpdate":

            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先を入力してください。");

            //YAMATO ZIP CHECK
            if (isset($_REQUEST["delivery_kbn"]) && $_REQUEST["delivery_kbn"] == "1" && $_REQUEST["sales_kbn"] == "1" && $_REQUEST["tokuisaki_zip"] !== "") {
                $sql = "SELECT delivery_cd FROM m_yamato WHERE key_part = :zip;";
                $param = array();
                $param["zip"] = $_REQUEST["tokuisaki_zip"];
                $sth = $dbh->prepare($sql);
                $sth->execute($param);
                $zip = $sth->fetchColumn();

                if ($zip == "") throw new Exception($_REQUEST["tokuisaki_zip"] . "がヤマト郵便番号対応仕分マスタに存在しません。");
            };

            if (isset($_REQUEST["sale_reg"]) && $_REQUEST["sale_reg"] === "1") {
                //TOKUISAKI TEL DELETE        
                $delete_sql = "DELETE FROM m_tokuisaki_tel 
                                WHERE tokuisaki_cd = :tokuisaki_cd
                                AND tel_no = :tel_no;";
                //AND tel_no = :tel_no
                $delete_sth = $dbh->prepare($delete_sql);

                $params = array();
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
                // $delete_sth->execute($params);
                //tel
                if ($_REQUEST["tokuisaki_tel"] != "") {
                    $params["tel_no"] = $_REQUEST["tokuisaki_tel"];
                    $delete_sth->execute($params);

                    $params["tel_no"] = substr($_REQUEST["tokuisaki_tel"], -4);
                    $delete_sth->execute($params);
                }
                //fax
                if ($_REQUEST["tokuisaki_fax"] != "") {
                    $params["tel_no"] = $_REQUEST["tokuisaki_fax"];
                    $delete_sth->execute($params);
                }
                //fuzai
                if ($_REQUEST["fuzai_contact"] != "") {
                    $params["tel_no"] = $_REQUEST["fuzai_contact"];
                    $delete_sth->execute($params);
                }

                //PHONE CHECK
                $sql = "SELECT COUNT(tel_no) FROM m_tokuisaki_tel WHERE tel_no = :tel_no;";
                // $sql = "SELECT COUNT(tel_no) FROM m_tokuisaki_tel WHERE tel_no IN (";
                $params = array();
                $tel = $_REQUEST["tokuisaki_tel"];
                $params["tel_no"] = $tel;
                // $sql .= "'$tel'";

                // $lst4dig = substr($_REQUEST["tokuisaki_tel"], -4);
                // // $sql .= ", '$lst4dig'";

                // if ($_REQUEST["tokuisaki_fax"] != "") {
                //     $fax = $_REQUEST["tokuisaki_fax"];
                //     $sql .= ", '$fax'";
                // };

                // if ($_REQUEST["fuzai_contact"] != "") {
                //     $fuzai = $_REQUEST["fuzai_contact"];
                //     $sql .= ", '$fuzai'";
                // };
                // $sql .= ")";
                $sth = $dbh->prepare($sql);
                $sth->execute($params);
                $cnt = $sth->fetchColumn();

                //if ($cnt != 0) throw new Exception("代表電話、FAX番号、予備連絡先のいずれかはすでに別の得意先に登録されています。");
                if ($cnt != 0) throw new Exception("電話番号[$tel]はすでに別の得意先に登録されています。");

                //TOKUISAKI TEL INSERT
                $sql = "INSERT INTO m_tokuisaki_tel(
                tokuisaki_cd
                ,tel_no
                , entry_date
                , update_date
                )VALUES(
                :tokuisaki_cd
                ,:tel_no
                , CURRENT_TIMESTAMP
                , CURRENT_TIMESTAMP)";

                $sth = $dbh->prepare($sql);

                $params = array();
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
                // $params["user_id"] = $_SESSION["user_id"];
                $params["tel_no"] = $_REQUEST["tokuisaki_tel"];
                $sth->execute($params);

                //LAST 4 DIGITS
                // $params["tel_no"] = substr($_REQUEST["tokuisaki_tel"], -4);
                // if ($params["tel_no"] !== $_REQUEST["tokuisaki_tel"]) {
                //     $sth->execute($params);
                // }

                // if (
                //     $_REQUEST["tokuisaki_fax"] !== "" &&
                //     $_REQUEST["tokuisaki_fax"] !== $_REQUEST["tokuisaki_tel"] &&
                //     $_REQUEST["tokuisaki_fax"] !== $_REQUEST["fuzai_contact"]
                // ) {

                //     $params["tel_no"] = $_REQUEST["tokuisaki_fax"];
                //     $sth->execute($params);
                // }

                // if (
                //     $_REQUEST["fuzai_contact"] !== "" &&
                //     $_REQUEST["fuzai_contact"] !== $_REQUEST["tokuisaki_tel"] &&
                //     $_REQUEST["fuzai_contact"] !== $_REQUEST["tokuisaki_fax"]
                // ) {

                //     $params["tel_no"] = $_REQUEST["fuzai_contact"];
                //     $sth->execute($params);
                // }
            } else {
                //TOKUISAKI TEL DELETE        
                $sql = "DELETE FROM m_tokuisaki_tel WHERE tokuisaki_cd = :tokuisaki_cd;";
                $sth = $dbh->prepare($sql);
                $params = array();
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
                $sth->execute($params);

                //PHONE CHECK
                $rows = json_decode($_REQUEST["tel_rows"], true);
                if (count($rows) == 0 || $_REQUEST["tel_rows"] == "") throw new Exception("追加電話番号を入力してください。");

                $sql = "SELECT COUNT(tel_no) FROM m_tokuisaki_tel WHERE tel_no = :tel_no";
                $sth = $dbh->prepare(($sql));
                $params = array();

                for ($i = 0; $i < count($rows); $i++) {
                    $tel = $rows[$i]["tel"];
                    $params["tel_no"] = $rows[$i]["tel"];
                    $sth->execute($params);
                    $cnt = $sth->fetchColumn();
                    if ($cnt != 0) throw new Exception("電話番号[$tel]はすでに別の得意先に登録されています。");
                }

                //TOKUISAKI TEL INSERT
                $sql = "INSERT INTO m_tokuisaki_tel(
                            tokuisaki_cd
                            ,tel_no
                            ,update_date
                            )VALUES(
                            :tokuisaki_cd
                            ,:tel_no
                            ,CURRENT_TIMESTAMP)";

                $sth = $dbh->prepare($sql);

                $check_sql = "SELECT COUNT(*) FROM m_tokuisaki_tel
                            WHERE tokuisaki_cd = :tokuisaki_cd
                            AND tel_no = :tel_no;";
                $check_sth = $dbh->prepare($check_sql);

                $params = array();
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
                for ($i = 0; $i < count($rows); $i++) {
                    $params["tel_no"] = $rows[$i]["tel"];
                    $check_sth->execute($params);
                    $cnt = $check_sth->fetchColumn();

                    if ($cnt === 0) {
                        $sth->execute($params);
                    }
                };
            }

            //UPDATE TOKUISAKI
            $sql = "UPDATE m_tokuisaki SET 
                tokuisaki_nm = :tokuisaki_nm
                , tokuisaki_kana = :tokuisaki_kana
                , tokuisaki_zip = :tokuisaki_zip
                , tokuisaki_adr_1 = :tokuisaki_adr_1
                , tokuisaki_adr_2 = :tokuisaki_adr_2
                , tokuisaki_adr_3 = :tokuisaki_adr_3
                , tokuisaki_tel = :tokuisaki_tel
                , tokuisaki_fax = :tokuisaki_fax
                , delivery_kbn = :delivery_kbn
                , delivery_time_kbn = :delivery_time_kbn
                , delivery_time_hr = :delivery_time_hr
                , delivery_time_min = :delivery_time_min
                , delivery_instruct_kbn = :delivery_instruct_kbn
                , tanto_nm = :tanto_nm
                , fuzai_contact = :fuzai_contact
                , industry_cd = :industry_cd
                , order_print_kbn = :order_print_kbn
                , delivery_instruct = :delivery_instruct
                , bill_dt = :bill_dt
                , comment = :comment
                , yamato_kbn = :yamato_kbn
                , sale_kbn = :sale_kbn
                , update_user_id = :update_id
                , update_date = CURRENT_TIMESTAMP
                , search_flg = :search_flg
                WHERE tokuisaki_cd = :tokuisaki_cd";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["tokuisaki_nm"] = $_REQUEST["tokuisaki_nm"];
            $params["tokuisaki_kana"] = $_REQUEST["tokuisaki_kana"];
            $params["tokuisaki_zip"] = $_REQUEST["tokuisaki_zip"];
            $params["tokuisaki_adr_1"] = $_REQUEST["tokuisaki_adr_1"];
            $params["tokuisaki_adr_2"] = $_REQUEST["tokuisaki_adr_2"];
            $params["tokuisaki_adr_3"] = $_REQUEST["tokuisaki_adr_3"];
            $params["tokuisaki_tel"] = $_REQUEST["tokuisaki_tel"];
            $params["tokuisaki_fax"] = $_REQUEST["tokuisaki_fax"];
            $params["delivery_instruct"] = $_REQUEST["tokuisaki_delivery_instruct"];
            $params["industry_cd"] = $_REQUEST["industry_cd"] ?? "1";
            $params["fuzai_contact"] = $_REQUEST["fuzai_contact"];
            $params["tanto_nm"] = $_REQUEST["tanto_nm"];
            $params["bill_dt"] = $_REQUEST["bill_dt"] ?? "";
            $params["order_print_kbn"] = $_REQUEST["order_print_kbn"] ?? "1";
            $params["delivery_time_kbn"] = $_REQUEST["delivery_time_kbn"] ?? "1";
            $params["delivery_time_hr"] = $_REQUEST["delivery_time_hr"] ?? "";
            $params["delivery_time_min"] = $_REQUEST["delivery_time_min"] ?? "";
            $params["delivery_instruct_kbn"] = $_REQUEST["delivery_instruct_kbn"] ?? "1";
            $params["delivery_kbn"] = $_REQUEST["delivery_kbn"] ?? "1";
            $params["yamato_kbn"] = $_REQUEST["yamato_kbn"] ?? "0";
            $params["sale_kbn"] = $_REQUEST["sales_kbn"] ?? "1";
            $params["comment"] = $_REQUEST["comment"] ?? "";
            $params["update_id"] = $_SESSION["user_id"];
            $params["search_flg"] = $_REQUEST["search_flg"] ?? "1";

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $icnt = $sth->rowCount();

            if ($icnt == 0) throw new Exception("得意先更新に失敗しました。");

            //UPDATE OKURISAKI
            $sql = "UPDATE m_okurisaki SET
                    okurisaki_nm = :okurisaki_nm
                    , okurisaki_kana = :okurisaki_kana
                    , okurisaki_zip = :okurisaki_zip
                    , okurisaki_adr_1 = :okurisaki_adr_1
                    , okurisaki_adr_2 = :okurisaki_adr_2
                    , okurisaki_adr_3 = :okurisaki_adr_3
                    , okurisaki_tel = :okurisaki_tel
                    , okurisaki_fax = :okurisaki_fax
                    , tanto_nm = :tanto_nm
                    , fuzai_contact = :fuzai_contact
                    , delivery_instruct = :delivery_instruct
                    , update_user_id = :update_id
                    , update_date = CURRENT_TIMESTAMP
                    WHERE tokuisaki_cd = :tokuisaki_cd
                    AND okurisaki_cd = :okurisaki_cd";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["okurisaki_cd"] = sprintf("%010d", 1);
            $params["okurisaki_nm"] = $_REQUEST["tokuisaki_nm"];
            $params["okurisaki_kana"] = $_REQUEST["tokuisaki_kana"];
            $params["okurisaki_zip"] = $_REQUEST["tokuisaki_zip"];
            $params["okurisaki_adr_1"] = $_REQUEST["tokuisaki_adr_1"];
            $params["okurisaki_adr_2"] = $_REQUEST["tokuisaki_adr_2"];
            $params["okurisaki_adr_3"] = $_REQUEST["tokuisaki_adr_3"];
            $params["okurisaki_tel"] = $_REQUEST["tokuisaki_tel"];
            $params["okurisaki_fax"] = $_REQUEST["tokuisaki_fax"];
            $params["tanto_nm"] = $_REQUEST["tanto_nm"];
            $params["fuzai_contact"] = $_REQUEST["fuzai_contact"];
            $params["delivery_instruct"] = $_REQUEST["tokuisaki_delivery_instruct"];
            $params["update_id"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $icnt = $sth->rowCount();

            if ($icnt == 0) throw new Exception("送り先更新に失敗しました。");

            $dbh->commit();
            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;
        case "tokuisakiTelCheck":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_tel"]) || $_REQUEST["tokuisaki_tel"] == "") throw new Exception("電話番号を入力してください。");

            //TEL CHECK
            $sql = "SELECT tel_no FROM m_tokuisaki_tel WHERE tel_no = :tel";
            $param = array();
            $param["tel"] = $_REQUEST["tokuisaki_tel"];
            $sth = $dbh->prepare($sql);
            $sth->execute($param);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) > 0) throw new Exception($_REQUEST["tokuisaki_tel"] . " は既に別の得意先で登録されています。");

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;
            /** 得意先削除 **/
        case "tokuisakiDelete":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先を入力してください。");

            $tokuisaki_delete = "DELETE FROM m_tokuisaki WHERE tokuisaki_cd = :tokuisaki_cd";
            //$okurisaki_delete = "DELETE FROM m_okurisaki WHERE tokuisaki_cd = :tokuisaki_cd";

            $tokuisaki_sth = $dbh->prepare($tokuisaki_delete);
            //$okurisaki_sth = $dbh->prepare($okurisaki_delete);

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

            $tokuisaki_sth->execute($params);
            // $okurisaki_sth->execute($params);

            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);

            break;
            /** TOKUISAKI SUB SEARCH **/
        case "tokuisakiSubSearch":
            session_start();
            $_SESSION['created'] = time();

            $offset = $_REQUEST["offset"] ?? 0;

            $sql = "SELECT
                    t.tokuisaki_cd
                    , tokuisaki_nm
                    , tokuisaki_tel
                    FROM m_tokuisaki t
                    INNER JOIN m_tokuisaki_tel tel ON t.tokuisaki_cd = tel.tokuisaki_cd
                    WHERE search_flg = '1'";

            $params = array();

            if (isset($_REQUEST["tokuisaki_cd"]) && $_REQUEST["tokuisaki_cd"] != "") {
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
                $sql .= " AND tokuisaki_cd = :tokuisaki_cd";
            };
            if (isset($_REQUEST["tokuisaki_nm"]) && $_REQUEST["tokuisaki_nm"] != "") {
                $params["tokuisaki_nm"] = "%" . $_REQUEST["tokuisaki_nm"] . "%";
                $sql .= " AND tokuisaki_nm LIKE :tokuisaki_nm";
            };
            if (isset($_REQUEST["tokuisaki_tel"]) && $_REQUEST["tokuisaki_tel"] != "") {
                $params["tokuisaki_tel"] = "%" . $_REQUEST["tokuisaki_tel"] . "%";
                $sql .= " AND tel.tel_no LIKE :tokuisaki_tel";
            };

            $sql .= " GROUP BY t.tokuisaki_cd ORDER BY tokuisaki_cd LIMIT 50 OFFSET $offset;";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list, JSON_UNESCAPED_UNICODE);

            break;

            /** 送り先リスト **/
        case "okurisakiAdd":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["okurisaki_tokuisaki_cd"]) || $_REQUEST["okurisaki_tokuisaki_cd"] == "") throw new Exception("得意先を選択してください。");

            //GET LAST okurisaki_cd
            $sql = "SELECT okurisaki_cd
            FROM m_okurisaki 
            WHERE tokuisaki_cd = :tokuisaki_cd
            ORDER BY okurisaki_cd";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["okurisaki_tokuisaki_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_COLUMN);

            if (count($list) == 0) {
                $okurisaki_cd = sprintf("%010d", 1);
            } else {
                $cd = $list[count($list) - 1];
                $okurisaki_cd = sprintf("%010d", ($cd + 1));
            }

            $sql = "INSERT INTO m_okurisaki(
                    tokuisaki_cd
                    , okurisaki_cd
                    , okurisaki_nm
                    , okurisaki_kana
                    , okurisaki_zip
                    , okurisaki_adr_1
                    , okurisaki_adr_2
                    , okurisaki_adr_3
                    , okurisaki_tel
                    , okurisaki_fax
                    , tanto_nm
                    , fuzai_contact
                    , delivery_instruct
                    , entry_user_id
                    , entry_date
                    , update_user_id
                    , update_date
                    )VALUES(
                    :tokuisaki_cd
                    , :okurisaki_cd
                    , :okurisaki_nm
                    , :okurisaki_kana
                    , :okurisaki_zip
                    , :okurisaki_adr_1
                    , :okurisaki_adr_2
                    , :okurisaki_adr_3
                    , :okurisaki_tel
                    , :okurisaki_fax
                    , :tanto_nm
                    , :fuzai_contact
                    , :delivery_instruct
                    , :user_id
                    , CURRENT_TIMESTAMP
                    , :user_id
                    , CURRENT_TIMESTAMP)";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["okurisaki_tokuisaki_cd"];
            $params["okurisaki_cd"] = $okurisaki_cd;
            $params["okurisaki_nm"] = $_REQUEST["okurisaki_nm"];
            $params["okurisaki_kana"] = $_REQUEST["okurisaki_kana"];
            $params["okurisaki_zip"] = $_REQUEST["okurisaki_zip"];
            $params["okurisaki_adr_1"] = $_REQUEST["okurisaki_adr_1"];
            $params["okurisaki_adr_2"] = $_REQUEST["okurisaki_adr_2"];
            $params["okurisaki_adr_3"] = $_REQUEST["okurisaki_adr_3"];
            $params["okurisaki_tel"] = $_REQUEST["okurisaki_tel"];
            $params["okurisaki_fax"] = $_REQUEST["okurisaki_fax"];
            $params["tanto_nm"] = $_REQUEST["okurisaki_tanto_nm"];
            $params["fuzai_contact"] = $_REQUEST["okurisaki_fuzai_contact"];
            $params["delivery_instruct"] = $_REQUEST["okurisaki_delivery_instruct"];
            $params["user_id"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** 送り先更新 **/
        case "okurisakiUpdate":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["okurisaki_tokuisaki_cd"]) || $_REQUEST["okurisaki_tokuisaki_cd"] == "") throw new Exception("得意先を選択してください。");

            //UPDATE OKURISAKI
            $sql = "UPDATE m_okurisaki SET
             okurisaki_nm = :okurisaki_nm
             , okurisaki_kana = :okurisaki_kana
             , okurisaki_zip = :okurisaki_zip
             , okurisaki_adr_1 = :okurisaki_adr_1
             , okurisaki_adr_2 = :okurisaki_adr_2
             , okurisaki_adr_3 = :okurisaki_adr_3
             , okurisaki_tel = :okurisaki_tel
             , okurisaki_fax = :okurisaki_fax
             , tanto_nm = :tanto_nm
             , fuzai_contact = :fuzai_contact
             , delivery_instruct = :delivery_instruct
             , update_user_id = :update_id
             , update_date = CURRENT_TIMESTAMP
             WHERE tokuisaki_cd = :tokuisaki_cd
             AND okurisaki_cd = :okurisaki_cd";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["okurisaki_tokuisaki_cd"];
            $params["okurisaki_cd"] = $_REQUEST["okurisaki_cd"] == "" ? sprintf("%010d", 1) : $_REQUEST["okurisaki_cd"];
            $params["okurisaki_nm"] = $_REQUEST["okurisaki_nm"];
            $params["okurisaki_kana"] = $_REQUEST["okurisaki_kana"];
            $params["okurisaki_zip"] = $_REQUEST["okurisaki_zip"];
            $params["okurisaki_adr_1"] = $_REQUEST["okurisaki_adr_1"];
            $params["okurisaki_adr_2"] = $_REQUEST["okurisaki_adr_2"];
            $params["okurisaki_adr_3"] = $_REQUEST["okurisaki_adr_3"];
            $params["okurisaki_tel"] = $_REQUEST["okurisaki_tel"];
            $params["okurisaki_fax"] = $_REQUEST["okurisaki_fax"];
            $params["tanto_nm"] = $_REQUEST["okurisaki_tanto_nm"];
            $params["fuzai_contact"] = $_REQUEST["okurisaki_fuzai_contact"];
            $params["delivery_instruct"] = $_REQUEST["okurisaki_delivery_instruct"];
            $params["update_id"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $icnt = $sth->rowCount();

            if ($icnt == 0) throw new Exception("送り先更新に失敗しました。");

            $dbh->commit();
            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

        case "getOkurisakiById":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先を入力してください。");

            $offset = ($_REQUEST["pagenum"] - 1);

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

            $sql = "SELECT
                    okurisaki_cd
                    ,okurisaki_nm
                    ,okurisaki_kana
                    ,okurisaki_zip
                    ,okurisaki_adr_1
                    ,okurisaki_adr_2
                    ,okurisaki_adr_3
                    ,okurisaki_tel
                    ,okurisaki_fax
                    ,tanto_nm
                    ,fuzai_contact
                    ,delivery_instruct
                    FROM m_okurisaki
                    WHERE tokuisaki_cd = :tokuisaki_cd
                    ORDER BY okurisaki_cd
                    LIMIT 1 OFFSET $offset;";

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            if (count($list) > 0) {
                $sql = "SELECT COUNT(*) FROM m_okurisaki WHERE tokuisaki_cd = :tokuisaki_cd";
                $sth = $dbh->prepare($sql);
                $sth->execute($params);
                $cnt = $sth->fetch();

                array_push($list, array("count" => $cnt[0]));
            }

            echo json_encode($list, JSON_UNESCAPED_UNICODE);

            break;

            /** 商品リスト **/
        case "productList":
            session_start();
            $_SESSION['created'] = time();

            $offset = LIST_CNT * ($_REQUEST["pagenum"] - 1);

            $sql = "SELECT product_cd
                    , product_nm
                    , product_nm_abrv
                    , COALESCE(c.kanri_nm, '未設定') as product_type
                    , COALESCE(c1.kanri_nm, '未設定') as sale_tani
                    , COALESCE(sale_price, '未設定') as sale_price
                    , unit_price
                    , sale_kbn
                    FROM m_shohin s
                    LEFT JOIN m_code c ON s.product_type = c.kanri_cd AND c.kanri_key = 'product.type'
                    LEFT JOIN m_code c1 ON s.sale_tani = c1.kanri_cd AND c1.kanri_key = 'sale.tani'";
            $whr = "WHERE 1=1 ";

            $params = array();
            if ($_REQUEST["product_cd"] != "") {
                $params["product_cd"] = $_REQUEST["product_cd"];
                $whr .= " AND s.product_cd = :product_cd ";
            };
            if ($_REQUEST["product_nm"] != "") {
                $params["product_nm"] = "%" . $_REQUEST["product_nm"] . "%";
                $whr .= " AND s.product_nm LIKE :product_nm ";
            };
            if ($_REQUEST["product_nm_abrv"] != "") {
                $params["product_nm_abrv"] = "%" . $_REQUEST["product_nm_abrv"] . "%";
                $whr .= " AND s.product_nm_abrv LIKE :product_nm_abrv ";
            };
            if ($_REQUEST["product_type"] != "all") {
                $params["product_type"] = $_REQUEST["product_type"];
                $whr .= " AND s.product_type = :product_type ";
            };
            $sql .= $whr . "ORDER BY product_cd LIMIT " . LIST_CNT . " OFFSET " . $offset;

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            $sql = "SELECT COUNT(*) FROM m_shohin s " . $whr;
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetch();

            $total_pg = ceil($cnt[0] / LIST_CNT);

            array_push($list, array("total_page" => $total_pg, "count" => $cnt[0]));

            echo json_encode($list, JSON_UNESCAPED_UNICODE);

            break;
            /** 商品検索 **/
        case "productLiveSearch":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["product_cd"]) || $_REQUEST["product_cd"] == "") throw new Exception("商品コードを入力してください。");
            // if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先コードを入力してください。");

            $ts_sql = "SELECT 
                    s.product_cd AS product_cd
                    , s.product_nm_abrv AS product_nm_abrv
                    , COALESCE(s.sale_price, '0') as sale_price
                    , s.tax_kbn AS tax_kbn
                    , COALESCE(ts.sale_price, '0') AS tokuisaki_price
                    FROM m_shohin s
                    LEFT JOIN m_tokuisaki_shohin ts ON s.product_cd = ts.product_cd AND ts.tokuisaki_cd = :tokuisaki_cd
                    WHERE 1=1 
                    AND s.haiban_kbn = '0'
                    AND s.product_cd = :product_cd;";

            $p_sql = "SELECT 
                    s.product_cd AS product_cd
                    , s.product_nm_abrv AS product_nm_abrv
                    , COALESCE(s.sale_price, '0') as sale_price
                    , s.tax_kbn AS tax_kbn
                    , 0 AS tokuisaki_price
                    FROM m_shohin s
                    WHERE 1=1 
                    AND s.haiban_kbn = '0'
                    AND s.product_cd = :product_cd;";

            $params = array();
            $params["product_cd"] = $_REQUEST["product_cd"];
            // $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

            if (isset($_REQUEST["tokuisaki_cd"]) && $_REQUEST["tokuisaki_cd"] != "") {
                $sql = $ts_sql;
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            } else {
                $sql = $p_sql;
            };

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list[0], JSON_UNESCAPED_UNICODE);

            break;

        case "productSubSearch":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["product_cd"]) || $_REQUEST["product_cd"] == "") throw new Exception("商品コードを入力してください。");

            $sql = "SELECT s.product_cd
                    , product_nm
                    , product_nm_abrv
                    , COALESCE(c.kanri_nm, '未設定') as product_type
                    , COALESCE(c1.kanri_nm, '未設定') as sale_tani
                    , COALESCE(s.sale_price, '0') as sale_price
                    , s.unit_price
                    , sale_kbn
                    , tax_kbn
                    FROM m_shohin s
                    LEFT JOIN m_code c ON s.product_type = c.kanri_cd AND c.kanri_key = 'product.type'
                    LEFT JOIN m_code c1 ON s.sale_tani = c1.kanri_cd AND c1.kanri_key = 'sale.tani'
                    WHERE 1=1 
                    AND s.haiban_kbn = '0'
                    AND s.product_cd = :product_cd";

            $params = array();
            $params["product_cd"] = $_REQUEST["product_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list[0], JSON_UNESCAPED_UNICODE);
            break;

        case "productSearch":
            session_start();
            $_SESSION['created'] = time();

            $offset = $_REQUEST["offset"] ?? 0;

            $ts_sql = "SELECT 
                s.product_cd AS product_cd
                , s.product_nm_abrv AS product_nm_abrv
                , COALESCE(s.sale_price, '0') as sale_price
                , s.tax_kbn AS tax_kbn
                , COALESCE(ts.sale_price, '0') AS tokuisaki_price
                FROM m_shohin s
                LEFT JOIN m_tokuisaki_shohin ts ON s.product_cd = ts.product_cd AND ts.tokuisaki_cd = :tokuisaki_cd
                WHERE 1=1 
                AND s.haiban_kbn = '0'";

            $p_sql = "SELECT 
                s.product_cd AS product_cd
                , s.product_nm_abrv AS product_nm_abrv
                , COALESCE(s.sale_price, '0') as sale_price
                , s.tax_kbn AS tax_kbn
                , 0 AS tokuisaki_price
                FROM m_shohin s
                WHERE 1=1 
                AND s.haiban_kbn = '0'";

            $whr = "";
            // $sql = "SELECT
            //         product_cd AS product_cd
            //         , product_nm_abrv AS product_nm_abrv
            //         , COALESCE(sale_price, '0') as sale_price
            //         , tax_kbn AS tax_kbn
            //         FROM m_shohin
            //         WHERE 1=1 AND haiban_kbn = '1'";

            $params = array();

            if (isset($_REQUEST["product_cd"]) && $_REQUEST["product_cd"] != "") {
                $params["product_cd"] = $_REQUEST["product_cd"];
                $whr .= " AND s.product_cd = :product_cd";
            };
            if (isset($_REQUEST["product_nm"]) && $_REQUEST["product_nm"] != "") {
                $params["product_nm"] = "%" . $_REQUEST["product_nm"] . "%";
                $whr .= " AND s.product_nm LIKE :product_nm";
            };

            if (isset($_REQUEST["tokuisaki_cd"]) && $_REQUEST["tokuisaki_cd"] != "") {
                $sql = $ts_sql;
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            } else {
                $sql = $p_sql;
            };

            $sql .= $whr . " ORDER BY s.product_cd LIMIT 50 OFFSET $offset;";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;
            /** 商品詳細 **/
        case "productDetail":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["product_cd"]) || $_REQUEST["product_cd"] == "") throw new Exception("商品コードを入力してください。");

            $sql = "SELECT product_cd
            , product_nm
            , product_nm_abrv
            , product_type
            , sale_tani
            , label_disp_kbn
            , order_disp_kbn
            , haiban_kbn
            , tax_kbn
            , sale_kbn
            , sale_price
            , unit_price
            FROM m_shohin s
            WHERE product_cd = :product_cd;";

            $params = array();
            $params["product_cd"] = $_REQUEST["product_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            echo json_encode($list[0], JSON_UNESCAPED_UNICODE);
            break;

            /** 商品作成 **/
        case "productAdd":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["product_cd"]) || $_REQUEST["product_cd"] == "") throw new Exception("商品コードを入力してください。");
            if (!isset($_REQUEST["product_nm"]) || $_REQUEST["product_nm"] == "") throw new Exception("商品名を入力してください。");
            if (!isset($_REQUEST["product_type"]) || $_REQUEST["product_type"] == "") throw new Exception("商品分類を選択してください。");
            if (!isset($_REQUEST["sale_tani"]) || $_REQUEST["sale_tani"] == "") throw new Exception("売上単位を選択してください。");
            if (!isset($_REQUEST["sale_price"]) || $_REQUEST["sale_price"] == "") throw new Exception("売上単価を入力してください。");
            if (!isset($_REQUEST["label_disp_kbn"]) || $_REQUEST["label_disp_kbn"] == "") throw new Exception("荷札表示区分を選択してください。");
            if (!isset($_REQUEST["haiban_kbn"]) || $_REQUEST["haiban_kbn"] == "") throw new Exception("注文書表示区分を選択してください。");
            if (!isset($_REQUEST["order_disp_kbn"]) || $_REQUEST["order_disp_kbn"] == "") throw new Exception("廃盤商品区分を選択してください。");
            if (!isset($_REQUEST["tax_kbn"]) || $_REQUEST["tax_kbn"] == "") throw new Exception("税区分を選択してください。");

            $sql = "SELECT COUNT(*) FROM m_shohin WHERE product_cd = :product_cd;";

            $params = array();
            $params["product_cd"] = $_REQUEST["product_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            if ($cnt > 0) throw new Exception("既に同じ商品コードが登録されています。");

            $sql = "INSERT INTO m_shohin(
                                product_cd
                                , product_nm
                                , product_nm_abrv
                                , product_type
                                , sale_tani
                                , label_disp_kbn
                                , order_disp_kbn
                                , haiban_kbn
                                , tax_kbn
                                , sale_kbn
                                , sale_price
                                , unit_price
                                , entry_user_id
                                , entry_date
                                , update_user_id
                                , update_date
                                ) VALUES (
                                :product_cd
                                , :product_nm
                                , :product_nm_abrv
                                , :product_type
                                , :sale_tani
                                , :label_disp_kbn
                                , :order_disp_kbn
                                , :haiban_kbn
                                , :tax_kbn
                                , :sale_kbn
                                , :sale_price
                                , :unit_price
                                , :user_id
                                , CURRENT_TIMESTAMP
                                , :user_id
                                , CURRENT_TIMESTAMP)";

            $params = array();
            $params["product_cd"] = $_REQUEST["product_cd"];
            $params["product_nm"] = $_REQUEST["product_nm"];
            $params["product_nm_abrv"] = $_REQUEST["product_nm_abrv"];
            $params["product_type"] = $_REQUEST["product_type"];
            $params["sale_tani"] = $_REQUEST["sale_tani"];
            $params["label_disp_kbn"] = $_REQUEST["label_disp_kbn"];
            $params["order_disp_kbn"] = $_REQUEST["order_disp_kbn"];
            $params["haiban_kbn"] = $_REQUEST["haiban_kbn"];
            $params["tax_kbn"] = $_REQUEST["tax_kbn"];
            $params["sale_kbn"] = $_REQUEST["sale_kbn"];
            $params["sale_price"] = $_REQUEST["sale_price"];
            $params["unit_price"] = $_REQUEST["unit_price"];
            $params["user_id"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            //update product code maste
            $sql = "UPDATE m_product_cd
                    SET in_use = TRUE
                    WHERE product_cd = :product_cd;";

            $params = array();
            $params["product_cd"] = $_REQUEST["product_cd"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** 商品更新 **/
        case "productUpdate":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["product_cd"]) || $_REQUEST["product_cd"] == "") throw new Exception("商品コードを入力してください。");
            if (!isset($_REQUEST["product_nm"]) || $_REQUEST["product_nm"] == "") throw new Exception("商品名を入力してください。");
            if (!isset($_REQUEST["product_type"]) || $_REQUEST["product_type"] == "") throw new Exception("商品分類を選択してください。");
            if (!isset($_REQUEST["sale_tani"]) || $_REQUEST["sale_tani"] == "") throw new Exception("売上単位を選択してください。");
            if (!isset($_REQUEST["sale_price"]) || $_REQUEST["sale_price"] == "") throw new Exception("売上単価を入力してください。");
            if (!isset($_REQUEST["label_disp_kbn"]) || $_REQUEST["label_disp_kbn"] == "") throw new Exception("荷札表示区分を選択してください。");
            if (!isset($_REQUEST["haiban_kbn"]) || $_REQUEST["haiban_kbn"] == "") throw new Exception("注文書表示区分を選択してください。");
            if (!isset($_REQUEST["order_disp_kbn"]) || $_REQUEST["order_disp_kbn"] == "") throw new Exception("廃盤商品区分を選択してください。");
            if (!isset($_REQUEST["tax_kbn"]) || $_REQUEST["tax_kbn"] == "") throw new Exception("税区分を選択してください。");

            $sql = "UPDATE m_shohin SET
                product_nm = :product_nm
                , product_nm_abrv = :product_nm_abrv
                , product_type = :product_type
                , sale_tani = :sale_tani
                , label_disp_kbn = :label_disp_kbn
                , order_disp_kbn = :order_disp_kbn
                , haiban_kbn = :haiban_kbn
                , tax_kbn = :tax_kbn
                , sale_kbn = :sale_kbn
                , sale_price = :sale_price
                , unit_price = :unit_price
                , update_user_id = :user_id
                , update_date = CURRENT_TIMESTAMP
                WHERE product_cd = :prev_code;";

            $params = array();
            //$params["product_cd"] = $_REQUEST["product_cd"];
            $params["product_nm"] = $_REQUEST["product_nm"];
            $params["product_nm_abrv"] = $_REQUEST["product_nm_abrv"];
            $params["product_type"] = $_REQUEST["product_type"];
            $params["sale_tani"] = $_REQUEST["sale_tani"];
            $params["label_disp_kbn"] = $_REQUEST["label_disp_kbn"];
            $params["order_disp_kbn"] = $_REQUEST["order_disp_kbn"];
            $params["haiban_kbn"] = $_REQUEST["haiban_kbn"];
            $params["tax_kbn"] = $_REQUEST["tax_kbn"];
            $params["sale_kbn"] = $_REQUEST["sale_kbn"];
            $params["sale_price"] = $_REQUEST["sale_price"];
            $params["unit_price"] = $_REQUEST["unit_price"];
            $params["user_id"] = $_SESSION["user_id"];
            $params["prev_code"] = $_REQUEST["prev_code"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $icnt = $sth->rowCount();

            if ($icnt == 0) throw new Exception("商品更新に失敗しました。");

            //IF product code change
            if ($_REQUEST["prev_code"] != $_REQUEST["product_cd"]) {
                $sql = "SELECT COUNT(*) FROM m_shohin WHERE product_cd = :product_cd";

                $params = array();
                $params["product_cd"] = $_REQUEST["product_cd"];

                $sth = $dbh->prepare($sql);
                $sth->execute($params);
                $cnt = $sth->fetchColumn();

                if ($cnt > 0) throw new Exception("既に同じ商品コードが登録されています。");

                //UPDATE MASTER
                //On update cascade down
                $sql = "UPDATE m_shohin
                        SET product_cd = :product_cd
                        WHERE product_cd = :prev_code;";
                $params = array();
                $params["product_cd"] = $_REQUEST["product_cd"];
                $params["prev_code"] = $_REQUEST["prev_code"];

                $sth = $dbh->prepare($sql);
                $sth->execute($params);

                $icnt = $sth->rowCount();

                if ($icnt == 0) throw new Exception("商品コード更新に失敗しました。");

                //UPDATE 得意先別商品アスタ
                $sql = "UPDATE m_tokuisaki_shohin
                        SET product_cd = :product_cd
                        WHERE product_cd = :prev_code;";

                $sth = $dbh->prepare($sql);
                $sth->execute($params);

                //UPDATE 売上(D)
                $sql = "UPDATE t_sale_d
                SET product_cd = :product_cd
                WHERE product_cd = :prev_code;";

                $sth = $dbh->prepare($sql);
                $sth->execute($params);

                //update product code master
                $sql = "UPDATE m_product_cd
                        SET in_use = TRUE
                        WHERE product_cd = :product_cd;";

                $params = array();
                $params["product_cd"] = $_REQUEST["product_cd"];
                $sth = $dbh->prepare($sql);
                $sth->execute($params);

                $sql = "UPDATE m_product_cd
                        SET in_use = FALSE
                        WHERE product_cd = :prev_code;";

                $params = array();
                $params["prev_code"] = $_REQUEST["prev_code"];
                $sth = $dbh->prepare($sql);
                $sth->execute($params);
            }

            //update t_sale_d product name
            $sql = "UPDATE t_sale_d
            SET product_nm = :product_nm
            WHERE product_cd = :product_cd;";

            $params = array();
            $params["product_cd"] = $_REQUEST["product_cd"];
            $params["product_nm"] = $_REQUEST["product_nm_abrv"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** 商品削除 **/
        case "productDelete":

            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["product_cd"]) || $_REQUEST["product_cd"] == "") throw new Exception("商品コードを入力してください。");

            $sql = "DELETE FROM m_shohin WHERE product_cd = :product_cd;";

            $params = array();
            $params["product_cd"] = $_REQUEST["product_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);


            $sql = "UPDATE m_product_cd
            SET in_use = FALSE
            WHERE product_cd = :product_cd;";

            $params = array();
            $params["product_cd"] = $_REQUEST["product_cd"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** 商品台帳 **/
        case "productPdf":
            session_start();
            $_SESSION['created'] = time();

            //1) GET data 
            $sql = "SELECT 
                    product_cd
                    ,CONCAT(product_cd, '　', product_nm) as product_nm
                    , product_nm_abrv
                    , c1.kanri_nm AS product_type
                    , c2.kanri_nm AS sale_tani
                    , COALESCE(sale_price,'0') as sale_price
                    , COALESCE(unit_price,'0') as unit_price
                    FROM m_shohin s
                    LEFT JOIN m_code c1 ON s.product_type = c1.kanri_cd AND c1.kanri_key = 'product.type'
                    LEFT JOIN m_code c2 ON s.sale_tani = c2.kanri_cd AND c2.kanri_key = 'sale.tani'
                    WHERE 1 = 1";
            $params = array();
            if ($_REQUEST["product_from"] != "") {
                $sql .= " AND product_cd >= :product_from";
                $params["product_from"] = $_REQUEST["product_from"];
            };
            if ($_REQUEST["product_to"] != "") {
                $sql .= " AND product_cd <= :product_to";
                $params["product_to"] = $_REQUEST["product_to"];
            };
            $sql .= " ORDER BY product_cd";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            //2) create PDF
            $fname = TEMP_FOLDER . "shohin_daicho_" . uniqid(mt_rand(), true) . PDF;
            shohinDaicho($fname, $list);

            //3) send pdf blob
            header('Content-Type: application/pdf');
            header("Content-Disposition: attachment; filename=shohin_daicho.pdf");
            header('Content-Length: ' . filesize($fname));
            readfile($fname);
            unlink($fname);
            break;

            /** 得意先別商品リスト **/
        case "customerProductList":
            session_start();
            $_SESSION['created'] = time();

            $offset = LIST_CNT * ($_REQUEST["pagenum"] - 1);

            $sql = "SELECT ts.tokuisaki_cd
                    , t.tokuisaki_tel
                    , CONCAT(c3.kanri_nm, c4.kanri_nm, c5.kanri_nm, t.tokuisaki_nm) AS tokuisaki_nm
                    , c1.kanri_nm AS product_type
                    , ts.product_cd
                    , s.product_nm
                    , c2.kanri_nm AS sale_tani
                    , ts.sale_price
                    , ts.unit_price
                    FROM m_tokuisaki_shohin ts
                    INNER JOIN m_tokuisaki t ON ts.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN m_shohin s ON ts.product_cd = s.product_cd
                    LEFT JOIN m_code c1 ON s.product_type = c1.kanri_cd AND c1.kanri_key = 'product.type'
                    LEFT JOIN m_code c2 ON s.sale_tani = c2.kanri_cd AND c2.kanri_key = 'sale.tani'
                    LEFT JOIN m_code c3 ON t.jikai_kbn_1 <> '0' AND t.jikai_kbn_1 = c3.kanri_cd AND c3.kanri_key = 'jikai.kbn' 
                    LEFT JOIN m_code c4 ON t.jikai_kbn_2 <> '0' AND t.jikai_kbn_2 = c4.kanri_cd AND c4.kanri_key = 'jikai.kbn' 
                    LEFT JOIN m_code c5 ON t.jikai_kbn_3 <> '0' AND t.jikai_kbn_3 = c5.kanri_cd AND c5.kanri_key = 'jikai.kbn'";
            $whr = "WHERE 1=1";

            $params = array();
            if ($_REQUEST["tokuisaki_tel"] != "") {
                $params["tokuisaki_tel"] = "%" . $_REQUEST["tokuisaki_tel"] . "%";
                $whr .= " AND t.tokuisaki_tel LIKE :tokuisaki_tel";
            };
            if ($_REQUEST["tokuisaki_nm"] != "") {
                $params["tokuisaki_nm"] = "%" . $_REQUEST["tokuisaki_nm"] . "%";
                $whr .= " AND t.tokuisaki_nm LIKE :tokuisaki_nm";
            };
            if ($_REQUEST["product_cd"] != "") {
                $params["product_cd"] = $_REQUEST["product_cd"];
                $whr .= " AND ts.product_cd = :product_cd";
            };
            if ($_REQUEST["product_nm"] != "") {
                $params["product_nm"] = "%" . $_REQUEST["product_nm"] . "%";
                $whr .= " AND s.product_nm LIKE :product_nm";
            };
            if ($_REQUEST["product_nm_abrv"] != "") {
                $params["product_nm_abrv"] = "%" . $_REQUEST["product_nm_abrv"] . "%";
                $whr .= " AND s.product_nm_abrv LIKE :product_nm_abrv";
            };
            if ($_REQUEST["product_type"] != "all") {
                $params["product_type"] = $_REQUEST["product_type"];
                $whr .= " AND s.product_type = :product_type";
            };

            $sql .= $whr . " ORDER BY ts.tokuisaki_cd, ts.product_cd LIMIT " . LIST_CNT . " OFFSET " . $offset;

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            $sql = "SELECT COUNT(*) 
            FROM m_tokuisaki_shohin ts
            INNER JOIN m_tokuisaki t ON ts.tokuisaki_cd = t.tokuisaki_cd
            LEFT JOIN m_shohin s ON ts.product_cd = s.product_cd " . $whr;

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetch();

            $total_pg = ceil($cnt[0] / LIST_CNT);

            array_push($list, array("total_page" => $total_pg, "count" => $cnt[0]));

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;

            /** 得意先別商品詳細 **/
        case "customerProductDetail":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先コードを入力してください。");
            if (!isset($_REQUEST["product_cd"]) || $_REQUEST["product_cd"] == "") throw new Exception("商品コードを入力してください。");

            $sql = "SELECT 
                    ts.tokuisaki_cd
                    , t.tokuisaki_tel
                    , CONCAT(c3.kanri_nm, c4.kanri_nm, c5.kanri_nm, t.tokuisaki_nm) AS tokuisaki_nm
                    , ts.product_cd
                    , s.product_nm
                    , s.product_nm_abrv
                    , c1.kanri_nm as product_type
                    , c2.kanri_nm as sale_tani
                    , ts.sale_price
                    , ts.unit_price
                    FROM m_tokuisaki_shohin ts
                    LEFT JOIN m_tokuisaki t ON ts.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN m_shohin s ON ts.product_cd = s.product_cd
                    LEFT JOIN m_code c1 ON s.product_type = c1.kanri_cd AND c1.kanri_key = 'product.type'
                    LEFT JOIN m_code c2 ON s.sale_tani = c2.kanri_cd AND c2.kanri_key = 'sale.tani'
                    LEFT JOIN m_code c3 ON t.jikai_kbn_1 <> '0' AND t.jikai_kbn_1 = c3.kanri_cd AND c3.kanri_key = 'jikai.kbn' 
                    LEFT JOIN m_code c4 ON t.jikai_kbn_2 <> '0' AND t.jikai_kbn_2 = c4.kanri_cd AND c4.kanri_key = 'jikai.kbn' 
                    LEFT JOIN m_code c5 ON t.jikai_kbn_3 <> '0' AND t.jikai_kbn_3 = c5.kanri_cd AND c5.kanri_key = 'jikai.kbn'
                    WHERE ts.tokuisaki_cd = :tokuisaki_cd
                    AND ts.product_cd = :product_cd;";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["product_cd"] = $_REQUEST["product_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            echo json_encode($list[0], JSON_UNESCAPED_UNICODE);
            break;

            /** 得意先別商品作成 **/
        case "customerProductAdd":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先コードを入力してください。");
            if (!isset($_REQUEST["product_cd"]) || $_REQUEST["product_cd"] == "") throw new Exception("商品コードを入力してください。");

            $sql = "SELECT COUNT(*) FROM m_tokuisaki_shohin
                    WHERE tokuisaki_cd = :tokuisaki_cd 
                    AND product_cd = :product_cd;";
            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["product_cd"] = $_REQUEST["product_cd"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            if ($cnt != 0) throw new Exception("既に同じ得意先と商品が登録されています。");

            $sql = "INSERT INTO m_tokuisaki_shohin
                    (tokuisaki_cd
                    , product_cd
                    , sale_price
                    , unit_price
                    , entry_user_id
                    , entry_date
                    , update_user_id
                    , update_date
                    )VALUES(
                    :tokuisaki_cd
                    , :product_cd
                    , :sale_price
                    , :unit_price
                    , :user_id
                    , CURRENT_TIMESTAMP
                    , :user_id
                    , CURRENT_TIMESTAMP)";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["product_cd"] = $_REQUEST["product_cd"];
            $params["sale_price"] = $_REQUEST["sale_price"];
            $params["unit_price"] = $_REQUEST["unit_price"];
            $params["user_id"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** 得意先別商品更新 **/
        case "customerProductUpdate":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先コードを入力してください。");
            if (!isset($_REQUEST["product_cd"]) || $_REQUEST["product_cd"] == "") throw new Exception("商品コードを入力してください。");

            $sql = "UPDATE m_tokuisaki_shohin 
                    SET sale_price = :sale_price
                    , unit_price = :unit_price
                    , update_user_id = :user_id
                    , update_date = CURRENT_TIMESTAMP
                    WHERE tokuisaki_cd = :tokuisaki_cd
                    AND product_cd = :product_cd;";

            $params = array();
            $params["sale_price"] = $_REQUEST["sale_price"];
            $params["unit_price"] = $_REQUEST["unit_price"];
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["product_cd"] = $_REQUEST["product_cd"];
            $params["user_id"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $icnt = $sth->rowCount();

            if ($icnt == 0) throw new Exception("得意先別商品更新に失敗しました。");

            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** 得意先別商品削除 **/
        case "customerProductDelete":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先コードを入力してください。");
            if (!isset($_REQUEST["product_cd"]) || $_REQUEST["product_cd"] == "") throw new Exception("商品コードを入力してください。");

            $sql = "DELETE FROM m_tokuisaki_shohin
                    WHERE tokuisaki_cd = :tokuisaki_cd
                    AND product_cd = :product_cd";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["product_cd"] = $_REQUEST["product_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** 得意先別商品台帳 **/
        case "customerProductPdf":
            session_start();
            $_SESSION['created'] = time();

            // 1) GET data 
            //if (!isset($_REQUEST["product_from"]) || $_REQUEST["product_from"] == "") throw new Exception("商品コードFromを入力してください。");
            if (!isset($_REQUEST["tokuisaki_tel"]) || $_REQUEST["tokuisaki_tel"] == "") throw new Exception("代表電話番号を入力してください。");
            $sql = "SELECT
                    t.tokuisaki_tel
                    , t.tokuisaki_nm
                    , COALESCE(c3.kanri_nm, '') AS industry_kbn
                    , ts.product_cd
                    , s.product_nm
                    , s.product_nm_abrv
                    , c1.kanri_nm AS product_type
                    , c2.kanri_nm AS sale_tani
                    , ts.sale_price
                    , ts.unit_price
                    FROM m_tokuisaki_shohin ts
                    LEFT JOIN m_tokuisaki t ON ts.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN m_shohin s ON ts.product_cd = s.product_cd
                    LEFT JOIN m_code c1 ON s.product_type = c1.kanri_cd AND c1.kanri_key = 'product.type'
                    LEFT JOIN m_code c2 ON s.sale_tani = c2.kanri_cd AND c2.kanri_key = 'sale.tani'
                    LEFT JOIN m_code c3 ON t.industry_cd = c3.kanri_cd AND c3.kanri_key = 'industry.kbn'
                    WHERE t.tokuisaki_tel = :tokuisaki_tel";

            $params = array();
            $params["tokuisaki_tel"] = $_REQUEST["tokuisaki_tel"];
            if ($_REQUEST["product_from"] != "") {
                $sql .= " AND ts.product_cd >= :product_from";
                $params["product_from"] = $_REQUEST["product_from"];
            };
            if ($_REQUEST["product_to"] != "") {
                $sql .= " AND ts.product_cd <= :product_to";
                $params["product_to"] = $_REQUEST["product_to"];
            };
            // if ($_REQUEST["tokuisaki_tel"] != "") {
            //     $sql .= " AND t.tokuisaki_tel = :tokuisaki_tel";
            //     $params["tokuisaki_tel"] = $_REQUEST["tokuisaki_tel"];
            // }
            // if ($_REQUEST["tokuisaki_nm"] != "") {
            //     $sql.= . " AND t.tokuisaki_nm LIKE :tokuisaki_nm";
            //     $params["tokuisaki_nm"] = "%" . $_REQUEST["tokuisaki_nm"] . "%";
            // }

            $sql .= " ORDER BY ts.tokuisaki_cd, ts.product_cd";

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            //2) create PDF
            $fname = TEMP_FOLDER . "tokuisaki_shohin_daicho_" . uniqid(mt_rand(), true) . PDF;
            tokuisakiDaicho($fname, $list);

            //3) send pdf blob
            header('Content-Type: application/pdf');
            header("Content-Disposition: attachment; filename=tokuisaki_shoihin_daicho.pdf");
            header('Content-Length: ' . filesize($fname));
            readfile($fname);
            unlink($fname);

            break;

            /** 郵便取込 **/
        case "zipUpload":
            session_start();
            $_SESSION['created'] = time();

            $err_cnt = 0;
            $update_cnt = 0;
            $insert_cnt = 0;
            $err_msg = array();

            $check_sql = "SELECT COUNT(*) FROM m_zip WHERE zip = :zip";
            $insert_sql = "INSERT INTO m_zip(zip, ken_fu, shi_ku, machi, entry_date) VALUES (:zip, :ken_fu, :shi_ku, :machi, CURRENT_TIMESTAMP);";
            $update_sql = "UPDATE m_zip SET ken_fu = :ken_fu, shi_ku = :shi_ku, machi = :machi, entry_date = CURRENT_TIMESTAMP WHERE zip = :zip";

            //error check
            if (!is_uploaded_file($_FILES["file"]["tmp_name"])) throw new Exception(fileErrorCheck($_FILES["file"]["error"]));

            if ($_FILES["file"]["error"] != 0) throw new Exception(fileErrorCheck($_FILES["file"]["error"]));

            if (!in_array($_FILES["file"]["type"], CSV_MIMES)) throw new Exception("不正なファイルタイプです。");

            $file = fopen($_FILES["file"]["tmp_name"], "r");
            $data = fgetcsv($file);

            $sth_check = $dbh->prepare($check_sql);
            $sth_insert = $dbh->prepare($insert_sql);
            $sth_update = $dbh->prepare($update_sql);

            //check row size
            $row = fgetcsv($file);
            if ($row == "") throw new Exception("取込ファイルは空です。");
            if (count($row) != 7) throw new Exception("郵便番号の取込用のファイルではありません。");

            while (($data = fgetcsv($file)) !== FALSE) {
                try {
                    $data = mb_convert_encoding($data, "UTF-8", "CP932");
                    //郵便番後 => $data[0]
                    //県府 => $data[1]
                    //市区 => $data[2]
                    //町 => $data[3]

                    if ($data[3] == "以下に掲載がない場合") {
                        continue;
                    }

                    //CHECK
                    $params = array("zip" => $data[0]);
                    $sth_check->execute($params);
                    $cnt = $sth_check->fetchColumn();

                    //SET PARAMS
                    $params = array();
                    $params["zip"] = $data[0];
                    $params["ken_fu"] = $data[1];
                    $params["shi_ku"] = $data[2];
                    $params["machi"] = $data[3];

                    //UPDATE
                    if ($cnt > 0) {
                        $res = $sth_update->execute($params);
                        if ($res) {
                            $update_cnt++;
                        };
                    } //INSERT
                    else {
                        $res = $sth_insert->execute($params);
                        if ($res) {
                            $insert_cnt++;
                        };
                    }
                } catch (Exception $e) {
                    $err_cnt++;
                    $err_msg[] = '[エラー：' . $e->getCode() . '] [' . $e->getMessage() . '] [郵便番号：' . $data[2] . ']';
                }
            };

            fclose($file);
            $dbh->commit();

            if ($err_cnt != 0) {
                $file = TEMP_FOLDER . date("Ymdhis") . "_" . ZIP_UPLOAD_ERROR_FILE;
                Create_Error_File($file, $err_msg);
                header('Content-Type: text/csv', false);
                header("Content-Disposition: attachment; filename=zip_upload_error.csv");
                header('Content-Length: ' . filesize($file));
                readfile($file);
                unlink($file);
                return;
            }

            //echo json_encode(array("insert_cnt" => $insert_cnt, "update_cnt" => $update_cnt, "error_cnt" => $err_cnt), JSON_UNESCAPED_UNICODE);
            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** ヤマト郵便番号対応仕分取込 **/
        case "yamatoUpload":
            session_start();
            $_SESSION['created'] = time();
            $file = null;
            $type = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            $type = strtolower($type);
            $files = null;

            //ERROR CHECK
            if (!is_uploaded_file($_FILES["file"]["tmp_name"])) throw new Exception(fileErrorCheck($_FILES["file"]["error"]));

            if ($_FILES["file"]["error"] != 0) throw new Exception(fileErrorCheck($_FILES["file"]["error"]));

            if ($type != "dat" && $type != "lzh") throw new Exception("不正なファイルタイプです。");

            //GET LATEST UPDATE DATE
            $sql = "SELECT COALESCE(MAX(create_dt),'0') AS create_dt FROM m_yamato;";
            $get_sth = $dbh->prepare($sql);

            //DELETE SQL
            $sql = "DELETE FROM m_yamato;";
            $delete_sth = $dbh->prepare($sql);

            //INSERT SQL
            $sql = "INSERT INTO m_yamato(
                    record_kbn
                    , key_part
                    , delivery_cd
                    , mail_cd
                    , start_dt
                    , kubun
                    , yobi
                    , create_dt
                    )VALUES(
                    :record_kbn
                    , :key_part
                    , :delivery_cd
                    , :mail_cd
                    , :start_dt
                    , :kubun
                    , :yobi
                    , :create_dt)";
            $insert_sth = $dbh->prepare($sql);

            //SET FILE PATH
            $filepath = $_FILES["file"]["tmp_name"];
            $extractDir = YAMATO_PENDING_FOLDER;
            $fileToExtract = strtolower(YAMATO_UPLOAD_FILE);

            for ($i = 0; $i <= YAMATO_UPLOAD_RETRY; $i++) {
                try {
                    $err_cnt = 0;
                    $line = 0;
                    $err_ary = array();
                    $data_ary = array();
                    $extracted = false;
                    $errMsg = "";
                    $errDetail = "";
                    $err = false;

                    //EXTRACT FILE FROM LZH
                    if ($type == "lzh") {

                        //Command to open lzh file
                        $command = "lha -x {$filepath} -w {$extractDir}";

                        // Execute the command
                        exec($command, $output, $returnCode);

                        if ($returnCode !== 0) {
                            $errMsg = "ヤマトのLZH アーカイブ内のファイルを取得できませんでした。";
                            $err = true;
                            continue;
                        };

                        $files = scandir(YAMATO_PENDING_FOLDER);
                        // Remove the current directory (".") and parent directory ("..") from the list
                        $fileNames = array_diff($files, array('.', '..'));

                        // Output the file names
                        foreach ($fileNames as $fileName) {
                            $filepath = YAMATO_PENDING_FOLDER . "/" . $fileName;
                            if (strtolower($fileName) === $fileToExtract) {
                                $extracted = true;
                                break;
                            };
                        }

                        //If no file extracted
                        if (!$extracted) {
                            $errMsg = "ヤマトのLZH アーカイブ内に必要なファイルが見つかりませんでした。";
                            $err = true;
                            continue;
                        };
                    };

                    //Get first row
                    $row = file_get_contents($filepath);

                    //Check if file is empty
                    if ($row == "") {
                        $errMsg = "取込ファイルは空です。";
                        $err = true;
                        continue;
                    }

                    //Open file
                    $fp = fopen($filepath, "r");

                    //Get latest update date
                    $get_sth->execute();
                    $dt = $get_sth->fetchColumn();

                    //CHECK FILE
                    while (($data = fgets($fp)) !== FALSE) {
                        try {
                            $line++;
                            if (substr($data, 0, 3) == "\xef\xbb\xbf") { // check for BOM
                                $data = substr($data, 3); // remove BOM
                            };

                            if (strlen(str_replace(array("\n", "\r\n", "\r"), '', $data)) != 50) throw new Exception("行の長さが正しくありません。] [行の長さ：" . strlen(str_replace(array("\n", "\r\n", "\r"), '', $data)));

                            if (substr($data, 42, 8) < $dt) throw new Exception("ヤマト郵便番号対応仕分マスタの作成日時が新しくありません。] [作成日時：$dt");

                            array_push($data_ary, $data);
                        } catch (Exception $e) {
                            $err_cnt++;
                            $err_ary[] = '[ファイル名：' . $_FILES["file"]["name"] . '] [行目：' . $line . '] [エラー：' . $e->getMessage() . ']';
                        }
                    };

                    fclose($fp);

                    //If error create file
                    if ($err_cnt != 0) {
                        $errMsg = "取込ファイルのレイアウトは正しくありません。";
                        $err = true;
                        continue;
                    }

                    //If no data
                    if (count($data_ary) == 0) {
                        $errMsg = "ヤマト郵便番号対応仕分マスタに取り込むﾃﾞｰﾀがありません。";
                        $err = true;
                        continue;
                    }

                    //Delete current
                    $delete_sth->execute();

                    //Insert for loop
                    foreach ($data_ary as $obj) {
                        //レコード区分 => substr($data,0,1)
                        //キー部 => substr($data,1,11)
                        //仕分コード（宅急便用） => substr($data,12,7)
                        //仕分コード（メール便用） => substr($data,19,7)
                        //適用開始年月日 => substr($data,26,8)
                        //区分 => substr($data,34,2)
                        //予備 => substr($data,36,6)
                        //作成年月日 => substr($data,42,8)

                        //SET PARAMS
                        $params = array();
                        $params["record_kbn"] = substr($obj, 0, 1);
                        $params["key_part"] = str_replace(" ", "", substr($obj, 1, 11));
                        $params["delivery_cd"] = substr($obj, 12, 7);
                        $params["mail_cd"] = substr($obj, 19, 7);
                        $params["start_dt"] = substr($obj, 26, 8);
                        $params["kubun"] = substr($obj, 34, 2);
                        $params["yobi"] = substr($obj, 36, 6);
                        $params["create_dt"] = substr($obj, 42, 8);

                        $insert_sth->execute($params);
                    }

                    break;
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $errDetail = $e->getMessage();
                    $err = true;
                    continue;
                } finally {
                    //Delete all files from folder regardless
                    if ($type == "lzh") {
                        $files = scandir(YAMATO_PENDING_FOLDER);
                        $fileNames = array_diff($files, array('.', '..'));
                        foreach ($fileNames as $fileName) {
                            $filepath = YAMATO_PENDING_FOLDER . "/" . $fileName;
                            unlink($filepath);
                        };
                    }
                }
            }

            if ($err) {
                //If error create file
                if ($err_cnt != 0) {
                    $file = TEMP_FOLDER . date("Ymdhis") . "_" . YAMATO_UPLOAD_ERROR_FILE;
                    Create_Error_File($file, $err_ary);
                    header('Content-Type: text/csv', false);
                    header("Content-Disposition: attachment; filename=yamato_upload_error.csv");
                    header('Content-Length: ' . filesize($file));
                    readfile($file);
                    //return;
                };

                //email
                $sql = "SELECT mail FROM m_mail;";
                $sth = $dbh->prepare($sql);
                $sth->execute();
                $mail_address = $sth->fetchAll(PDO::FETCH_ASSOC);

                $sql = "SELECT 
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.host') AS mail_host,
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.port') AS mail_port,
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.user') AS mail_user,
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.pwd') AS mail_pwd,
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.address') AS mail_address,
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.from') AS mail_from;";
                $sth = $dbh->prepare($sql);
                $sth->execute();
                $send_info = $sth->fetch(PDO::FETCH_ASSOC);

                $mail_host = $send_info["mail_host"];
                $mail_port = $send_info["mail_port"];
                $mail_uname = $send_info["mail_user"];
                $mail_pwd = $send_info["mail_pwd"];
                $mail_from = $send_info["mail_from"];
                $title = "受注管理システム　出荷予定データヤマト仕分取込エラー";
                $body = $errMsg . PHP_EOL . $errDetail;

                if (!empty($mail_host) && !empty($mail_port) && !empty($mail_uname) && !empty($mail_pwd) && !empty($mail_from)) {
                    SendMail($mail_host, $mail_from, $mail_uname, $mail_pwd, $mail_port, $mail_address, $title, $body, $file);
                } else {
                    error_log("メールを送信するための情報はありません。");
                }

                //Delete CSV error file
                if (!empty($file)) {
                    unlink($file);
                } else {
                    throw new Exception($errMsg);
                }
            } else {
                $dbh->commit();
                echo json_encode("ヤマト仕分け取込を完了しました。", JSON_UNESCAPED_UNICODE);
            }

            break;

            /** 売上登録 **/
        case "saleReg":
            session_start();
            $_SESSION['created'] = time();
            $_SESSION['transaction_state'] = true;

            //必須項目を確認
            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号が指定されていません。");
            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先が指定されていません。");

            //明細データ
            $rows = json_decode($_REQUEST["mesai_rows"], true);
            if (count($rows) == 0 || $_REQUEST["mesai_rows"] == "") throw new Exception("売上明細を入力してください。");

            // && $_REQUEST["sales_kbn"] == "1"
            //便種はヤマトの場合
            if (isset($_REQUEST["delivery_kbn"]) && $_REQUEST["delivery_kbn"] == "1") {

                //郵便番号を確認
                //ヤマト仕分けマスタに存在するか
                if ($_REQUEST["tokuisaki_zip"] !== "") {
                    //YAMATO ZIP CHECK
                    $sql = "SELECT delivery_cd FROM m_yamato WHERE key_part = :zip;";
                    $param = array();
                    $param["zip"] = $_REQUEST["tokuisaki_zip"];
                    $sth = $dbh->prepare($sql);
                    $sth->execute($param);
                    $zip = $sth->fetchColumn();

                    if ($zip == "") throw new Exception($_REQUEST["tokuisaki_zip"] . "がヤマト郵便番号対応仕分マスタに存在しません。");
                }

                //YAMATO INQUIRE NO
                //ヤマト問い合わせ番号
                $sql = "SELECT 
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'yamato.inquire.start') AS yamato_inquire_start,
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'yamato.inquire.end') AS yamato_inquire_end,
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'yamato.inquire.current') AS yamato_inquire_current";
                $sth = $dbh->prepare($sql);
                $sth->execute();
                $list = $sth->fetch();
                $inquire_no = $list["yamato_inquire_current"];

                //UPDATE YAMATO INQUIRE NO
                //ヤマトの問い合わせ番号を更新
                // +1
                $sql = "UPDATE m_code
                        SET kanri_cd = :yamato_current,
                        update_date = CURRENT_TIMESTAMP
                        WHERE kanri_key = 'yamato.inquire.current';";
                $param = array();
                if ($inquire_no + 1 > $list["yamato_inquire_end"]) {
                    $param["yamato_current"] = $list["yamato_inquire_start"];
                } else {
                    $param["yamato_current"] = $inquire_no + 1;
                }
                $sth = $dbh->prepare($sql);
                $sth->execute($param);
            };

            //TOKUISAKI TEL DELETE        
            //得意先電話を削除
            //TEL・FAX・不在連絡先
            $delete_sql = "DELETE FROM m_tokuisaki_tel 
            WHERE tokuisaki_cd = :tokuisaki_cd
            AND tel_no = :tel_no;";
            $delete_sth = $dbh->prepare($delete_sql);

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            //tel
            if ($_REQUEST["tokuisaki_tel"] != "") {
                $params["tel_no"] = $_REQUEST["tokuisaki_tel"];
                $delete_sth->execute($params);

                $params["tel_no"] = substr($_REQUEST["tokuisaki_tel"], -4);
                $delete_sth->execute($params);
            }
            //fax
            if ($_REQUEST["tokuisaki_fax"] != "") {
                $params["tel_no"] = $_REQUEST["tokuisaki_fax"];
                $delete_sth->execute($params);
            }
            //fuzai
            if ($_REQUEST["fuzai_contact"] != "") {
                $params["tel_no"] = $_REQUEST["fuzai_contact"];
                $delete_sth->execute($params);
            }

            //PHONE CHECK
            //$sql = "SELECT COUNT(tel_no) FROM m_tokuisaki_tel WHERE tel_no IN (";
            //新しい電話番号は存在するか確認
            $sql = "SELECT COUNT(tel_no) FROM m_tokuisaki_tel WHERE tel_no = :tel_no;";
            $params = array();
            $tel = $_REQUEST["tokuisaki_tel"];
            $params["tel_no"] = $tel;
            //$sql .= "'$tel'";

            //$lst4dig = substr($_REQUEST["tokuisaki_tel"], -4);
            //$sql .= ", '$lst4dig'";

            // if ($_REQUEST["tokuisaki_fax"] != "") {
            //     $fax = $_REQUEST["tokuisaki_fax"];
            //     $sql .= ", '$fax'";
            // };

            // if ($_REQUEST["fuzai_contact"] != "") {
            //     $fuzai = $_REQUEST["fuzai_contact"];
            //     $sql .= ", '$fuzai'";
            // };
            // $sql .= ")";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            // if ($cnt != 0) throw new Exception("代表電話、FAX番号、予備連絡先のいずれかはすでに別の得意先に登録されています。");
            if ($cnt != 0) throw new Exception("電話番号[$tel]はすでに別の得意先に登録されています。");

            //TOKUISAKI TEL INSERT
            //得意先電話番号を登録
            $sql = "INSERT INTO m_tokuisaki_tel(
                            tokuisaki_cd
                            ,tel_no
                            ,entry_date
                            ,update_date
                            )VALUES(
                            :tokuisaki_cd
                            ,:tel_no
                            ,CURRENT_TIMESTAMP
                            ,CURRENT_TIMESTAMP)";

            $sth = $dbh->prepare($sql);

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            // $params["user_id"] = $_SESSION["user_id"];
            $params["tel_no"] = $_REQUEST["tokuisaki_tel"];
            $sth->execute($params);

            //LAST 4 DIGITS
            // $params["tel_no"] = substr($_REQUEST["tokuisaki_tel"], -4);
            // if ($params["tel_no"] !== $_REQUEST["tokuisaki_tel"]) {
            //     $sth->execute($params);
            // }

            // if (
            //     $_REQUEST["tokuisaki_fax"] !== "" &&
            //     $_REQUEST["tokuisaki_fax"] !== $_REQUEST["tokuisaki_tel"] &&
            //     $_REQUEST["tokuisaki_fax"] !== $_REQUEST["fuzai_contact"]
            // ) {

            //     $params["tel_no"] = $_REQUEST["tokuisaki_fax"];
            //     $sth->execute($params);
            // }

            // if (
            //     $_REQUEST["fuzai_contact"] !== "" &&
            //     $_REQUEST["fuzai_contact"] !== $_REQUEST["tokuisaki_tel"] &&
            //     $_REQUEST["fuzai_contact"] !== $_REQUEST["tokuisaki_fax"]
            // ) {

            //     $params["tel_no"] = $_REQUEST["fuzai_contact"];
            //     $sth->execute($params);
            // }

            //TOKUISAKI UPDATE
            //得意先を更新
            $sql = "UPDATE m_tokuisaki SET 
                        tokuisaki_nm = :tokuisaki_nm
                        , tokuisaki_kana = :tokuisaki_kana
                        , tokuisaki_zip = :tokuisaki_zip
                        , tokuisaki_adr_1 = :tokuisaki_adr_1
                        , tokuisaki_adr_2 = :tokuisaki_adr_2
                        , tokuisaki_adr_3 = :tokuisaki_adr_3
                        , tokuisaki_tel = :tokuisaki_tel
                        , tokuisaki_fax = :tokuisaki_fax
                        , delivery_time_kbn = :delivery_time_kbn
                        , delivery_time_hr = :delivery_time_hr
                        , delivery_time_min = :delivery_time_min
                        , delivery_instruct_kbn = :delivery_instruct_kbn
                        , tanto_nm = :tanto_nm
                        , fuzai_contact = :fuzai_contact
                        , industry_cd = :industry_cd
                        , delivery_instruct = :delivery_instruct
                        , yamato_kbn = :yamato_kbn
                        , comment = :comment
                        , update_user_id = :update_id
                        , update_date = CURRENT_TIMESTAMP
                        , jikai_kbn_1 = :jikai_kbn_1
                        , jikai_kbn_2 = :jikai_kbn_2
                        , jikai_kbn_3 = :jikai_kbn_3
                        WHERE tokuisaki_cd = :tokuisaki_cd";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["tokuisaki_nm"] = $_REQUEST["tokuisaki_nm"];
            $params["tokuisaki_kana"] = $_REQUEST["tokuisaki_kana"];
            $params["tokuisaki_zip"] = $_REQUEST["tokuisaki_zip"];
            $params["tokuisaki_adr_1"] = $_REQUEST["tokuisaki_adr_1"];
            $params["tokuisaki_adr_2"] = $_REQUEST["tokuisaki_adr_2"];
            $params["tokuisaki_adr_3"] = $_REQUEST["tokuisaki_adr_3"];
            $params["tokuisaki_tel"] = $_REQUEST["tokuisaki_tel"];
            $params["tokuisaki_fax"] = $_REQUEST["tokuisaki_fax"];
            $params["delivery_instruct"] = $_REQUEST["tokuisaki_delivery_instruct"];
            $params["industry_cd"] = isset($_REQUEST["industry_cd"]) ? $_REQUEST["industry_cd"] : "1";
            $params["fuzai_contact"] = $_REQUEST["fuzai_contact"];
            $params["tanto_nm"] = $_REQUEST["tanto_nm"];
            $params["delivery_time_kbn"] = isset($_REQUEST["delivery_time_kbn"]) ? $_REQUEST["delivery_time_kbn"] : "3";
            $params["delivery_time_hr"] = isset($_REQUEST["delivery_time_hr"]) ? $_REQUEST["delivery_time_hr"] : "";
            $params["delivery_time_min"] = isset($_REQUEST["delivery_time_min"]) ? $_REQUEST["delivery_time_min"] : "";
            $params["delivery_instruct_kbn"] = isset($_REQUEST["delivery_instruct_kbn"]) ? $_REQUEST["delivery_instruct_kbn"] : "3";
            //$params["delivery_kbn"] = isset($_REQUEST["delivery_kbn"]) ? $_REQUEST["delivery_kbn"] : "1";
            $params["yamato_kbn"] = isset($_REQUEST["yamato_kbn"]) ? $_REQUEST["yamato_kbn"] : "0";
            $params["comment"] = $_REQUEST["comment"];
            $params["update_id"] = $_SESSION["user_id"];
            $params["jikai_kbn_1"] = $_REQUEST["next_kbn"];
            $params["jikai_kbn_2"] = $_REQUEST["next_kbn2"];
            $params["jikai_kbn_3"] = $_REQUEST["next_kbn3"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            //IF NEW OKURISAKI ADD
            //!isset($_REQUEST["okurisaki_cd"])
            //送り先を新規登録
            if (empty($_REQUEST["okurisaki_cd"])) {

                //GET LAST okurisaki_cd
                $sql = "SELECT okurisaki_cd
                        FROM m_okurisaki 
                        WHERE tokuisaki_cd = :tokuisaki_cd
                        ORDER BY okurisaki_cd;";

                $params = array();
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

                $sth = $dbh->prepare($sql);
                $sth->execute($params);
                $list = $sth->fetchAll(PDO::FETCH_COLUMN);

                if (count($list) == 0) {
                    $okurisaki_cd = sprintf("%010d", 1);
                } else {
                    $cd = $list[count($list) - 1];
                    $okurisaki_cd = sprintf("%010d", ($cd + 1));
                };

                $sql = "INSERT INTO m_okurisaki(
                    tokuisaki_cd
                    , okurisaki_cd
                    , okurisaki_nm
                    , okurisaki_kana
                    , okurisaki_zip
                    , okurisaki_adr_1
                    , okurisaki_adr_2
                    , okurisaki_adr_3
                    , okurisaki_tel
                    , okurisaki_fax
                    , tanto_nm
                    , fuzai_contact
                    , delivery_instruct
                    , entry_user_id
                    , entry_date
                    , update_user_id
                    , update_date
                    )VALUES(
                    :tokuisaki_cd
                    , :okurisaki_cd
                    , :okurisaki_nm
                    , :okurisaki_kana
                    , :okurisaki_zip
                    , :okurisaki_adr_1
                    , :okurisaki_adr_2
                    , :okurisaki_adr_3
                    , :okurisaki_tel
                    , :okurisaki_fax
                    , :tanto_nm
                    , :fuzai_contact
                    , :delivery_instruct
                    , :user_id
                    , CURRENT_TIMESTAMP
                    , :user_id
                    , CURRENT_TIMESTAMP)";

                $params = array();
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
                $params["okurisaki_cd"] = $okurisaki_cd;
                $params["okurisaki_nm"] = $_REQUEST["okurisaki_nm"];
                $params["okurisaki_kana"] = $_REQUEST["okurisaki_kana"];
                $params["okurisaki_zip"] = $_REQUEST["okurisaki_zip"];
                $params["okurisaki_adr_1"] = $_REQUEST["okurisaki_adr_1"];
                $params["okurisaki_adr_2"] = $_REQUEST["okurisaki_adr_2"];
                $params["okurisaki_adr_3"] = $_REQUEST["okurisaki_adr_3"];
                $params["okurisaki_tel"] = $_REQUEST["okurisaki_tel"];
                $params["okurisaki_fax"] = $_REQUEST["okurisaki_fax"];
                $params["tanto_nm"] = $_REQUEST["okurisaki_tanto_nm"];
                $params["fuzai_contact"] = $_REQUEST["okurisaki_fuzai_contact"];
                $params["delivery_instruct"] = $_REQUEST["okurisaki_delivery_instruct"];
                $params["user_id"] = $_SESSION["user_id"];

                $sth = $dbh->prepare($sql);
                $sth->execute($params);
            } else {

                //UPDATE OKURISAKI
                //送り先を更新
                $sql = "UPDATE m_okurisaki SET
                    okurisaki_nm = :okurisaki_nm
                    , okurisaki_kana = :okurisaki_kana
                    , okurisaki_zip = :okurisaki_zip
                    , okurisaki_adr_1 = :okurisaki_adr_1
                    , okurisaki_adr_2 = :okurisaki_adr_2
                    , okurisaki_adr_3 = :okurisaki_adr_3
                    , okurisaki_tel = :okurisaki_tel
                    , okurisaki_fax = :okurisaki_fax
                    , tanto_nm = :tanto_nm
                    , fuzai_contact = :fuzai_contact
                    , delivery_instruct = :delivery_instruct
                    , update_user_id = :update_id
                    , update_date = CURRENT_TIMESTAMP
                    WHERE tokuisaki_cd = :tokuisaki_cd
                    AND okurisaki_cd = :okurisaki_cd;";

                $params = array();
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
                $params["okurisaki_cd"] = $_REQUEST["okurisaki_cd"]; // ?? sprintf("%010d", 1)
                $params["okurisaki_nm"] = $_REQUEST["okurisaki_nm"] ?? $_REQUEST["tokuisaki_nm"];
                $params["okurisaki_kana"] = $_REQUEST["okurisaki_kana"] ?? $_REQUEST["tokuisaki_kana"];
                $params["okurisaki_zip"] = $_REQUEST["okurisaki_zip"] ?? $_REQUEST["tokuisaki_zip"];
                $params["okurisaki_adr_1"] = $_REQUEST["okurisaki_adr_1"] ?? $_REQUEST["tokuisaki_adr_1"];
                $params["okurisaki_adr_2"] = $_REQUEST["okurisaki_adr_2"] ?? $_REQUEST["tokuisaki_adr_2"];
                $params["okurisaki_adr_3"] = $_REQUEST["okurisaki_adr_3"] ?? $_REQUEST["tokuisaki_adr_3"];
                $params["okurisaki_tel"] = $_REQUEST["okurisaki_tel"] ?? $_REQUEST["tokuisaki_tel"];
                $params["okurisaki_fax"] = $_REQUEST["okurisaki_fax"] ?? $_REQUEST["tokuisaki_fax"];
                $params["tanto_nm"] = $_REQUEST["okurisaki_tanto_nm"] ?? $_REQUEST["tanto_nm"];
                $params["fuzai_contact"] = $_REQUEST["okurisaki_fuzai_contact"] ?? $_REQUEST["fuzai_contact"];
                $params["delivery_instruct"] = $_REQUEST["okurisaki_delivery_instruct"] ?? $_REQUEST["tokuisaki_delivery_instruct"];
                $params["update_id"] = $_SESSION["user_id"];

                $sth = $dbh->prepare($sql);
                $sth->execute($params);
            }

            // ORDER FORM
            //t_sale_h
            //売上ヘッダーを登録
            $sql = "INSERT INTO t_sale_h(
                order_no
                , next_kbn
                , sale_dt
                , tokuisaki_cd
                , okurisaki_cd
                , inquire_no
                , order_kbn
                , sale_kbn
                , delivery_kbn
                , receive_dt
                , delivery_time_kbn
                , delivery_time_hr
                , delivery_time_min
                , delivery_instruct_kbn
                , total_qty
                , total_cost
                , tax_8
                , tax_10
                , grand_total
                , kosu
                , sender_cd
                , yamato_kbn
                , send_flg
                , kenpin_kbn
                , entry_user_id
                , entry_date
                , update_user_id
                , update_date
                )VALUES(
                 :order_no
                , :next_kbn
                , :sale_dt
                , :tokuisaki_cd
                , :okurisaki_cd
                , lpad(CAST(nextval('seq_inquire_no') as character varying) , 12 , '0')
                , :order_kbn
                , :sale_kbn
                , :delivery_kbn
                , :receive_dt
                , :delivery_time_kbn
                , :delivery_time_hr
                , :delivery_time_min
                , :delivery_instruct_kbn
                , :total_qty
                , :total_cost
                , :tax_8
                , :tax_10
                , :grand_total
                , :kosu
                , :sender_cd
                , :yamato_kbn
                , '0'
                , '0'
                , :user_id
                , CURRENT_TIMESTAMP
                , :user_id
                , CURRENT_TIMESTAMP)";

            $sth = $dbh->prepare($sql);

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];
            $params["next_kbn"] = $_REQUEST["next_kbn"];
            $params["sale_dt"] = $_REQUEST["sale_dt"]; // . " " . date("H:i:s");
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["okurisaki_cd"] = (empty($_REQUEST["okurisaki_cd"])) ? $okurisaki_cd : $_REQUEST["okurisaki_cd"]; //(!isset($_REQUEST["okurisaki_cd"]) || $_REQUEST["okurisaki_cd"] == "") ? sprintf("%010d", 1) : $_REQUEST["okurisaki_cd"];
            $params["order_kbn"] = $_REQUEST["order_kbn"];
            $params["sale_kbn"] = $_REQUEST["sales_kbn"];
            $params["delivery_kbn"] = $_REQUEST["delivery_kbn"];
            $params["receive_dt"] = $_REQUEST["receive_dt"];
            $params["delivery_time_kbn"] = ($_REQUEST["delivery_time_hr"] == "") ? "3" : $_REQUEST["delivery_time_kbn"];
            $params["delivery_time_hr"] = $_REQUEST["delivery_time_hr"];
            $params["delivery_time_min"] = $_REQUEST["delivery_time_min"];
            $params["delivery_instruct_kbn"] = ($_REQUEST["delivery_time_hr"] == "") ? "3" : $_REQUEST["delivery_instruct_kbn"];
            $params["total_qty"] = $_REQUEST["total_qty"];
            $params["total_cost"] = $_REQUEST["total_cost"];
            $params["tax_8"] = $_REQUEST["tax_8"];
            $params["tax_10"] = $_REQUEST["tax_10"];
            $params["grand_total"] = $_REQUEST["grand_total"];
            $params["kosu"] = $_REQUEST["kosu"];
            $params["sender_cd"] = $_REQUEST["sender_cd"];
            $params["yamato_kbn"] = $_REQUEST["yamato_kbn"] ?? "0";
            $params["user_id"] = $_SESSION["user_id"];
            $sth->execute($params);

            //MESAI ROWS

            //Check product exists
            //商品存在するか確認
            $sql = "SELECT count(*) FROM m_shohin
                    WHERE product_cd = :product_cd;";
            $sth = $dbh->prepare($sql);

            $params = array();
            for ($i = 0; $i < count($rows); $i++) {
                $params["product_cd"] = $rows[$i]["product_cd"];
                $sth->execute($params);
                $chk = $sth->fetchColumn();

                if (empty($chk)) throw new Exception("商品マスタに無いコードが入力されています。");
            };

            //t_sale_d
            //売上明細を登録
            $sql = "INSERT INTO t_sale_d(
                    order_no
                    , row_no
                    , product_cd
                    , product_nm
                    , tanka
                    , qty
                    , total_cost
                    , entry_user_id
                    , entry_date)
                    VALUES (
                    :order_no
                    , :row_no
                    , :product_cd
                    , :product_nm
                    , :tanka
                    , :qty
                    , :total_cost
                    , :user_id
                    , CURRENT_TIMESTAMP);";

            $sth = $dbh->prepare($sql);

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];
            $params["user_id"] = $_SESSION["user_id"];

            for ($i = 0; $i < count($rows); $i++) {
                $params["row_no"] = $rows[$i]["row_no"];
                $params["product_cd"] = $rows[$i]["product_cd"];
                $params["product_nm"] = $rows[$i]["product_nm"];
                $params["tanka"] = $rows[$i]["tanka"];
                $params["qty"] = $rows[$i]["qty"];
                $params["total_cost"] = $rows[$i]["total_cost"];

                $sth->execute($params);
            };

            //INSERT INTO AUTO REPORT PRINT TABLE
            //自動発行帳票に登録
            $sql = "INSERT INTO t_sale_report
                        (order_no
                        , denpyo_flg
                        , hikae_flg
                        , receipt_flg
                        , order_flg
                        , label_flg
                        , print_flg
                        , entry_user_id
                        , entry_date
                        , update_user_id
                        , update_date
                        ) VALUES (
                        :order_no
                        , :denpyo_flg
                        , :hikae_flg
                        , :receipt_flg
                        , :order_flg
                        , :label_flg
                        , :print_flg
                        , :user_id
                        , CURRENT_TIMESTAMP
                        , :user_id
                        , CURRENT_TIMESTAMP);";

            $params = array();
            $params["denpyo_flg"] = '0';
            $params["hikae_flg"] = $_REQUEST["hikae_flg"];
            $params["receipt_flg"] = $_REQUEST["receipt_flg"];
            if ($_REQUEST["denpyo_flg"] == '1' || $_REQUEST["order_flg"] == '1') {
                $params["order_flg"] = '1';
            } else {
                $params["order_flg"] = '0';
            }
            $params["label_flg"] = $_REQUEST["label_flg"];
            $params["print_flg"] = '0';
            $params["user_id"] = $_SESSION["user_id"];
            $params["order_no"] = $_REQUEST["order_no"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            //UPDATE INQUIRE NO
            //問い合わせ番号を更新
            if (isset($inquire_no)) {
                $sql = "UPDATE t_sale_h
                        SET inquire_no = :inquire_no
                        WHERE order_no = :order_no;";

                $params = array();
                $params["order_no"] = $_REQUEST["order_no"];
                $params["inquire_no"] = $inquire_no . Create7DRCheckDigit($inquire_no);

                $sth = $dbh->prepare($sql);
                $sth->execute($params);
            }

            //IF CANCELED
            //キャンセルボタンが押されて、キャンセルする
            if (!$_SESSION['transaction_state']) {
                $dbh->rollBack();
                $ret = "NG";
            } else {
                $dbh->commit();
                $ret = "OK";

                //GET NEXT ORDER NO
                //連番の受注番号を返す
                $sql = "SELECT lpad(CAST(nextval('seq_order_no') as character varying) , 10 , '0')";
                $sth = $dbh->prepare($sql);
                $sth->execute();
                $order_no = $sth->fetchColumn();
                $_SESSION["order_no"] = $order_no;
            }

            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            break;

            /** UPDATE SALE **/
        case "saleUpdate":
            session_start();
            $_SESSION['created'] = time();
            $_SESSION['transaction_state'] = true;

            //必須項目を確認
            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号が指定されていません。");
            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先が指定されていません。");

            //明細データ
            $rows = json_decode($_REQUEST["mesai_rows"], true);
            if (count($rows) == 0 || $_REQUEST["mesai_rows"] == "") throw new Exception("売上明細を入力してください。");

            // && $_REQUEST["sales_kbn"] == "1"
            //便種はヤマトの場合
            if (isset($_REQUEST["delivery_kbn"]) && $_REQUEST["delivery_kbn"] == "1") {

                //郵便番号を確認
                //ヤマト仕分けマスタに存在するか
                if ($_REQUEST["tokuisaki_zip"] !== "") {
                    //YAMATO ZIP CHECK
                    $sql = "SELECT delivery_cd FROM m_yamato WHERE key_part = :zip;";
                    $param = array();
                    $param["zip"] = $_REQUEST["tokuisaki_zip"];
                    $sth = $dbh->prepare($sql);
                    $sth->execute($param);
                    $zip = $sth->fetchColumn();

                    if ($zip == "") throw new Exception($_REQUEST["tokuisaki_zip"] . "がヤマト郵便番号対応仕分マスタに存在しません。");
                }

                //YAMATO INQUIRE NO
                //ヤマト問合せ番号を取得
                $sql = "SELECT 
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'yamato.inquire.start') AS yamato_inquire_start,
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'yamato.inquire.end') AS yamato_inquire_end,
                            (SELECT kanri_cd FROM m_code WHERE kanri_key = 'yamato.inquire.current') AS yamato_inquire_current";
                $sth = $dbh->prepare($sql);
                $sth->execute();
                $list = $sth->fetch();

                //更新する前は佐川または、その他の便種の場合
                //問い合わせ番号を更新
                if (substr($_REQUEST["inquire_no"], 0, 4) != '3775') {
                    $inquire_no = $list["yamato_inquire_current"];

                    //UPDATE YAMATO INQUIRE NO
                    $sql = "UPDATE m_code
                            SET kanri_cd = :yamato_current,
                            update_date = CURRENT_TIMESTAMP
                            WHERE kanri_key = 'yamato.inquire.current'";
                    $param = array();
                    if ($inquire_no + 1 > $list["yamato_inquire_end"]) {
                        $param["yamato_current"] = $list["yamato_inquire_start"];
                    } else {
                        $param["yamato_current"] = $inquire_no + 1;
                    }
                    $sth = $dbh->prepare($sql);
                    $sth->execute($param);

                    $inquire_no = $list["yamato_inquire_current"] . Create7DRCheckDigit($list["yamato_inquire_current"]);
                };
            } else {
                //佐川・その他の問い合わせ番号
                //if (substr($_REQUEST["inquire_no"], 0, 4) == '3775') {
                $sql = "SELECT lpad(CAST(nextval('seq_inquire_no') as character varying) , 12 , '0');";
                $sth = $dbh->prepare($sql);
                $sth->execute();
                $inquire_no = $sth->fetchColumn();
                //}
            }

            //TOKUISAKI TEL DELETE        
            //得意先電話番号を削除
            $delete_sql = "DELETE FROM m_tokuisaki_tel 
            WHERE tokuisaki_cd = :tokuisaki_cd
            AND tel_no = :tel_no;";
            $delete_sth = $dbh->prepare($delete_sql);

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            //tel
            if ($_REQUEST["tokuisaki_tel"] != "") {
                $params["tel_no"] = $_REQUEST["tokuisaki_tel"];
                $delete_sth->execute($params);

                $params["tel_no"] = substr($_REQUEST["tokuisaki_tel"], -4);
                $delete_sth->execute($params);
            }
            //fax
            if ($_REQUEST["tokuisaki_fax"] != "") {
                $params["tel_no"] = $_REQUEST["tokuisaki_fax"];
                $delete_sth->execute($params);
            }
            //fuzai
            if ($_REQUEST["fuzai_contact"] != "") {
                $params["tel_no"] = $_REQUEST["fuzai_contact"];
                $delete_sth->execute($params);
            }

            //PHONE CHECK
            //$sql = "SELECT COUNT(tel_no) FROM m_tokuisaki_tel WHERE tel_no IN (";
            //得意先電話番号は存在するか
            $sql = "SELECT COUNT(tel_no) FROM m_tokuisaki_tel WHERE tel_no = :tel_no;";
            $tel = $_REQUEST["tokuisaki_tel"];
            $params = array();
            $params["tel_no"] = $tel;
            //$sql .= "'$tel'";

            //            $lst4dig = substr($_REQUEST["tokuisaki_tel"], -4);
            //          $sql .= ", '$lst4dig'";

            // if ($_REQUEST["tokuisaki_fax"] != "") {
            //     $fax = $_REQUEST["tokuisaki_fax"];
            //     $sql .= ", '$fax'";
            // };

            // if ($_REQUEST["fuzai_contact"] != "") {
            //     $fuzai = $_REQUEST["fuzai_contact"];
            //     $sql .= ", '$fuzai'";
            // };
            // $sql .= ")";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            // if ($cnt != 0) throw new Exception("代表電話、FAX番号、予備連絡先のいずれかはすでに別の得意先に登録されています。");
            if ($cnt != 0) throw new Exception("電話番号[$tel]はすでに別の得意先に登録されています。");

            //TOKUISAKI TEL INSERT
            //得意先電話番号を登録
            $sql = "INSERT INTO m_tokuisaki_tel(
                            tokuisaki_cd
                            ,tel_no
                            , entry_date
                            , update_date
                            )VALUES(
                            :tokuisaki_cd
                            ,:tel_no
                            , CURRENT_TIMESTAMP
                            , CURRENT_TIMESTAMP)";

            $sth = $dbh->prepare($sql);

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            // $params["user_id"] = $_SESSION["user_id"];
            $params["tel_no"] = $_REQUEST["tokuisaki_tel"];
            $sth->execute($params);

            //LAST 4 DIGITS
            //        $params["tel_no"] = substr($_REQUEST["tokuisaki_tel"], -4);
            //      if ($params["tel_no"] !== $_REQUEST["tokuisaki_tel"]) {
            //        $sth->execute($params);
            //  }

            // if (
            //     $_REQUEST["tokuisaki_fax"] !== "" &&
            //     $_REQUEST["tokuisaki_fax"] !== $_REQUEST["tokuisaki_tel"] &&
            //     $_REQUEST["tokuisaki_fax"] !== $_REQUEST["fuzai_contact"]
            // ) {

            //     $params["tel_no"] = $_REQUEST["tokuisaki_fax"];
            //     $sth->execute($params);
            // }

            // if (
            //     $_REQUEST["fuzai_contact"] !== "" &&
            //     $_REQUEST["fuzai_contact"] !== $_REQUEST["tokuisaki_tel"] &&
            //     $_REQUEST["fuzai_contact"] !== $_REQUEST["tokuisaki_fax"]
            // ) {

            //     $params["tel_no"] = $_REQUEST["fuzai_contact"];
            //     $sth->execute($params);
            // }

            //TOKUISAKI UPDATE
            //得意先を更新
            $sql = "UPDATE m_tokuisaki SET 
                        tokuisaki_nm = :tokuisaki_nm
                        , tokuisaki_kana = :tokuisaki_kana
                        , tokuisaki_zip = :tokuisaki_zip
                        , tokuisaki_adr_1 = :tokuisaki_adr_1
                        , tokuisaki_adr_2 = :tokuisaki_adr_2
                        , tokuisaki_adr_3 = :tokuisaki_adr_3
                        , tokuisaki_tel = :tokuisaki_tel
                        , tokuisaki_fax = :tokuisaki_fax
                        , delivery_time_kbn = :delivery_time_kbn
                        , delivery_time_hr = :delivery_time_hr
                        , delivery_time_min = :delivery_time_min
                        , delivery_instruct_kbn = :delivery_instruct_kbn
                        , tanto_nm = :tanto_nm
                        , fuzai_contact = :fuzai_contact
                        , industry_cd = :industry_cd
                        , delivery_instruct = :delivery_instruct
                        , yamato_kbn = :yamato_kbn
                        , comment = :comment
                        , update_user_id = :update_id
                        , update_date = CURRENT_TIMESTAMP
                        , jikai_kbn_1 = :jikai_kbn_1
                        , jikai_kbn_2 = :jikai_kbn_2
                        , jikai_kbn_3 = :jikai_kbn_3
                        WHERE tokuisaki_cd = :tokuisaki_cd";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["tokuisaki_nm"] = $_REQUEST["tokuisaki_nm"];
            $params["tokuisaki_kana"] = $_REQUEST["tokuisaki_kana"];
            $params["tokuisaki_zip"] = $_REQUEST["tokuisaki_zip"];
            $params["tokuisaki_adr_1"] = $_REQUEST["tokuisaki_adr_1"];
            $params["tokuisaki_adr_2"] = $_REQUEST["tokuisaki_adr_2"];
            $params["tokuisaki_adr_3"] = $_REQUEST["tokuisaki_adr_3"];
            $params["tokuisaki_tel"] = $_REQUEST["tokuisaki_tel"];
            $params["tokuisaki_fax"] = $_REQUEST["tokuisaki_fax"];
            $params["delivery_instruct"] = $_REQUEST["tokuisaki_delivery_instruct"];
            $params["industry_cd"] = isset($_REQUEST["industry_cd"]) ? $_REQUEST["industry_cd"] : "1";
            $params["fuzai_contact"] = $_REQUEST["fuzai_contact"];
            $params["tanto_nm"] = $_REQUEST["tanto_nm"];
            $params["delivery_time_kbn"] = isset($_REQUEST["delivery_time_kbn"]) ? $_REQUEST["delivery_time_kbn"] : "3";
            $params["delivery_time_hr"] = isset($_REQUEST["delivery_time_hr"]) ? $_REQUEST["delivery_time_hr"] : "";
            $params["delivery_time_min"] = isset($_REQUEST["delivery_time_min"]) ? $_REQUEST["delivery_time_min"] : "";
            $params["delivery_instruct_kbn"] = isset($_REQUEST["delivery_instruct_kbn"]) ? $_REQUEST["delivery_instruct_kbn"] : "3";
            $params["yamato_kbn"] = isset($_REQUEST["yamato_kbn"]) ? $_REQUEST["yamato_kbn"] : "0";
            $params["comment"] = $_REQUEST["comment"];
            $params["update_id"] = $_SESSION["user_id"];
            $params["jikai_kbn_1"] = $_REQUEST["next_kbn"];
            $params["jikai_kbn_2"] = $_REQUEST["next_kbn2"];
            $params["jikai_kbn_3"] = $_REQUEST["next_kbn3"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            //IF NEW OKURISAKI ADD
            //送り先を新規登録
            if (empty($_REQUEST["okurisaki_cd"])) {
                //GET LAST okurisaki_cd
                $sql = "SELECT okurisaki_cd
                        FROM m_okurisaki 
                        WHERE tokuisaki_cd = :tokuisaki_cd
                        ORDER BY okurisaki_cd";

                $params = array();
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

                $sth = $dbh->prepare($sql);
                $sth->execute($params);
                $list = $sth->fetchAll(PDO::FETCH_COLUMN);

                if (count($list) == 0) {
                    $okurisaki_cd = sprintf("%010d", 1);
                } else {
                    $cd = $list[count($list) - 1];
                    $okurisaki_cd = sprintf("%010d", ($cd + 1));
                };

                $sql = "INSERT INTO m_okurisaki(
                    tokuisaki_cd
                    , okurisaki_cd
                    , okurisaki_nm
                    , okurisaki_kana
                    , okurisaki_zip
                    , okurisaki_adr_1
                    , okurisaki_adr_2
                    , okurisaki_adr_3
                    , okurisaki_tel
                    , okurisaki_fax
                    , tanto_nm
                    , fuzai_contact
                    , delivery_instruct
                    , entry_user_id
                    , entry_date
                    , update_user_id
                    , update_date
                    )VALUES(
                    :tokuisaki_cd
                    , :okurisaki_cd
                    , :okurisaki_nm
                    , :okurisaki_kana
                    , :okurisaki_zip
                    , :okurisaki_adr_1
                    , :okurisaki_adr_2
                    , :okurisaki_adr_3
                    , :okurisaki_tel
                    , :okurisaki_fax
                    , :tanto_nm
                    , :fuzai_contact
                    , :delivery_instruct
                    , :user_id
                    , CURRENT_TIMESTAMP
                    , :user_id
                    , CURRENT_TIMESTAMP)";

                $params = array();
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
                $params["okurisaki_cd"] = $okurisaki_cd;
                $params["okurisaki_nm"] = $_REQUEST["okurisaki_nm"];
                $params["okurisaki_kana"] = $_REQUEST["okurisaki_kana"];
                $params["okurisaki_zip"] = $_REQUEST["okurisaki_zip"];
                $params["okurisaki_adr_1"] = $_REQUEST["okurisaki_adr_1"];
                $params["okurisaki_adr_2"] = $_REQUEST["okurisaki_adr_2"];
                $params["okurisaki_adr_3"] = $_REQUEST["okurisaki_adr_3"];
                $params["okurisaki_tel"] = $_REQUEST["okurisaki_tel"];
                $params["okurisaki_fax"] = $_REQUEST["okurisaki_fax"];
                $params["tanto_nm"] = $_REQUEST["okurisaki_tanto_nm"];
                $params["fuzai_contact"] = $_REQUEST["okurisaki_fuzai_contact"];
                $params["delivery_instruct"] = $_REQUEST["okurisaki_delivery_instruct"];
                $params["user_id"] = $_SESSION["user_id"];

                $sth = $dbh->prepare($sql);
                $sth->execute($params);
            } else {
                //UPDATE OKURISAKI
                //送り先を更新
                $sql = "UPDATE m_okurisaki SET
                    okurisaki_nm = :okurisaki_nm
                    , okurisaki_kana = :okurisaki_kana
                    , okurisaki_zip = :okurisaki_zip
                    , okurisaki_adr_1 = :okurisaki_adr_1
                    , okurisaki_adr_2 = :okurisaki_adr_2
                    , okurisaki_adr_3 = :okurisaki_adr_3
                    , okurisaki_tel = :okurisaki_tel
                    , okurisaki_fax = :okurisaki_fax
                    , tanto_nm = :tanto_nm
                    , fuzai_contact = :fuzai_contact
                    , delivery_instruct = :delivery_instruct
                    , update_user_id = :update_id
                    , update_date = CURRENT_TIMESTAMP
                    WHERE tokuisaki_cd = :tokuisaki_cd
                    AND okurisaki_cd = :okurisaki_cd";

                $params = array();
                $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
                $params["okurisaki_cd"] = $_REQUEST["okurisaki_cd"];
                $params["okurisaki_nm"] = $_REQUEST["okurisaki_nm"] ?? $_REQUEST["tokuisaki_nm"];
                $params["okurisaki_kana"] = $_REQUEST["okurisaki_kana"] ?? $_REQUEST["tokuisaki_kana"];
                $params["okurisaki_zip"] = $_REQUEST["okurisaki_zip"] ?? $_REQUEST["tokuisaki_zip"];
                $params["okurisaki_adr_1"] = $_REQUEST["okurisaki_adr_1"] ?? $_REQUEST["tokuisaki_adr_1"];
                $params["okurisaki_adr_2"] = $_REQUEST["okurisaki_adr_2"] ?? $_REQUEST["tokuisaki_adr_2"];
                $params["okurisaki_adr_3"] = $_REQUEST["okurisaki_adr_3"] ?? $_REQUEST["tokuisaki_adr_3"];
                $params["okurisaki_tel"] = $_REQUEST["okurisaki_tel"] ?? $_REQUEST["tokuisaki_tel"];
                $params["okurisaki_fax"] = $_REQUEST["okurisaki_fax"] ?? $_REQUEST["tokuisaki_fax"];
                $params["tanto_nm"] = $_REQUEST["okurisaki_tanto_nm"] ?? $_REQUEST["tanto_nm"];
                $params["fuzai_contact"] = $_REQUEST["okurisaki_fuzai_contact"] ?? $_REQUEST["fuzai_contact"];
                $params["delivery_instruct"] = $_REQUEST["okurisaki_delivery_instruct"] ?? $_REQUEST["tokuisaki_delivery_instruct"];
                $params["update_id"] = $_SESSION["user_id"];

                $sth = $dbh->prepare($sql);
                $sth->execute($params);
            }

            //Check product exists
            //商品存在するか
            $sql = "SELECT count(*) FROM m_shohin
                        WHERE product_cd = :product_cd";
            $sth = $dbh->prepare($sql);

            $params = array();
            for ($i = 0; $i < count($rows); $i++) {
                $chk = null;
                $params["product_cd"] = $rows[$i]["product_cd"];
                $sth->execute($params);
                $chk = $sth->fetchColumn();

                if (empty($chk)) throw new Exception("商品マスタに無いコードが入力されています。");
            };

            //DELETE ALL CURRENT MESAI ROWS
            //明細を削除
            $sql = "DELETE FROM t_sale_d WHERE order_no = :order_no;";
            $sth = $dbh->prepare($sql);

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];
            $sth->execute($params);

            //INSERT MESAI ROWS
            //明細を登録
            $sql = "INSERT INTO t_sale_d(
                    order_no
                    , row_no
                    , product_cd
                    , product_nm
                    , tanka
                    , qty
                    , total_cost
                    , entry_user_id
                    , entry_date)
                    VALUES (
                    :order_no
                    , :row_no
                    , :product_cd
                    , :product_nm
                    , :tanka
                    , :qty
                    , :total_cost
                    , :user_id
                    , CURRENT_TIMESTAMP);";

            $sth = $dbh->prepare($sql);

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];
            $params["user_id"] = $_SESSION["user_id"];

            for ($i = 0; $i < count($rows); $i++) {
                $params["row_no"] = $rows[$i]["row_no"];
                $params["product_cd"] = $rows[$i]["product_cd"];
                $params["product_nm"] = $rows[$i]["product_nm"];
                $params["tanka"] = $rows[$i]["tanka"];
                $params["qty"] = $rows[$i]["qty"];
                $params["total_cost"] = $rows[$i]["total_cost"];

                $sth->execute($params);
            };

            //UPDATE ORDER FORM
            //売上ヘッダーを更新
            $sql = "UPDATE t_sale_h
                    SET next_kbn = :next_kbn
                        , sale_dt = :sale_dt
                        , tokuisaki_cd = :tokuisaki_cd
                        , okurisaki_cd = :okurisaki_cd
                        , order_kbn = :order_kbn
                        , sale_kbn = :sale_kbn
                        , delivery_kbn = :delivery_kbn
                        , receive_dt = :receive_dt
                        , delivery_time_kbn = :delivery_time_kbn
                        , delivery_time_hr = :delivery_time_hr
                        , delivery_time_min = :delivery_time_min
                        , delivery_instruct_kbn = :delivery_instruct_kbn
                        , total_qty = :total_qty
                        , total_cost = :total_cost
                        , tax_8 = :tax_8
                        , tax_10 = :tax_10
                        , grand_total = :grand_total
                        , kosu = :kosu
                        , sender_cd = :sender_cd
                        , yamato_kbn = :yamato_kbn
                        , update_user_id = :user_id
                        , update_date = CURRENT_TIMESTAMP";

            $params = array();

            //UPDATE INQUIRE NO
            //問い合わせ番号を更新
            if (isset($inquire_no)) {
                $sql .= ", inquire_no = :inquire_no";
                $params["inquire_no"] = $inquire_no;
            }

            $sql .= " WHERE order_no = :order_no;";
            $sth = $dbh->prepare($sql);


            $params["order_no"] = $_REQUEST["order_no"];
            $params["next_kbn"] = $_REQUEST["next_kbn"];
            $params["sale_dt"] = $_REQUEST["sale_dt"]; //. " " . date("H:i:s");
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $params["okurisaki_cd"] = (empty($_REQUEST["okurisaki_cd"])) ? $okurisaki_cd : $_REQUEST["okurisaki_cd"]; //($_REQUEST["okurisaki_cd"] == "") ? sprintf("%010d", 1) : $_REQUEST["okurisaki_cd"];
            // $params["inquire_no"] = $_REQUEST["inquire_no"];
            $params["order_kbn"] = $_REQUEST["order_kbn"];
            $params["sale_kbn"] = $_REQUEST["sales_kbn"];
            $params["delivery_kbn"] = $_REQUEST["delivery_kbn"];
            $params["receive_dt"] = $_REQUEST["receive_dt"];
            $params["delivery_time_kbn"] = ($_REQUEST["delivery_time_hr"] == "") ? "3" : $_REQUEST["delivery_time_kbn"];
            $params["delivery_time_hr"] = $_REQUEST["delivery_time_hr"];
            $params["delivery_time_min"] = $_REQUEST["delivery_time_min"];
            $params["delivery_instruct_kbn"] = ($_REQUEST["delivery_time_hr"] == "") ? "3" : $_REQUEST["delivery_instruct_kbn"];
            $params["total_qty"] = $_REQUEST["total_qty"];
            $params["total_cost"] = $_REQUEST["total_cost"];
            $params["tax_8"] = $_REQUEST["tax_8"];
            $params["tax_10"] = $_REQUEST["tax_10"];
            $params["grand_total"] = $_REQUEST["grand_total"];
            $params["kosu"] = $_REQUEST["kosu"];
            $params["sender_cd"] = $_REQUEST["sender_cd"];
            $params["yamato_kbn"] = $_REQUEST["yamato_kbn"] ?? "0";
            $params["user_id"] = $_SESSION["user_id"];
            $sth->execute($params);

            //UPDATE AUTO REPORT PRINT TABLE
            //自動発行帳票を更新
            $sql = "UPDATE t_sale_report
                        SET denpyo_flg = :denpyo_flg
                        , hikae_flg = :hikae_flg
                        , receipt_flg = :receipt_flg
                        , order_flg = :order_flg
                        , label_flg = :label_flg
                        , print_flg = :print_flg
                        , update_user_id = :user_id
                        , update_date = CURRENT_TIMESTAMP
                        WHERE order_no = :order_no;";

            $params = array();
            $params["denpyo_flg"] = '0';
            $params["hikae_flg"] = $_REQUEST["hikae_flg"];
            $params["receipt_flg"] = $_REQUEST["receipt_flg"];
            if ($_REQUEST["denpyo_flg"] == '1' || $_REQUEST["order_flg"] == '1') {
                $params["order_flg"] = '1';
            } else {
                $params["order_flg"] = '0';
            }
            $params["label_flg"] = $_REQUEST["label_flg"];
            $params["print_flg"] = '0';
            $params["user_id"] = $_SESSION["user_id"];
            $params["order_no"] = $_REQUEST["order_no"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            //キャンセルボタンが押されて、キャンセルする
            if (!$_SESSION['transaction_state']) {
                $dbh->rollBack();
                $ret = "NG";
            } else {
                $dbh->commit();
                $ret = "OK";
            }
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            break;

            /** DELETE SALE **/
        case "salesDelete":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号が指定されていません。");

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];

            //ON DELETE WILL CASCADE DOWN TO t_sale_d
            $delete_sale_h = "DELETE FROM t_sale_h WHERE order_no = :order_no;";
            $delete_sale_d = "DELETE FROM t_sale_d WHERE order_no = :order_no;";

            $sth_h = $dbh->prepare($delete_sale_h);
            $sth_d = $dbh->prepare($delete_sale_d);

            $sth_h->execute($params);
            $sth_d->execute($params);

            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** 売上詳細 **/
        case "getSalesDetail":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号が指定されていません。");

            $sql = "SELECT h.order_no, 
                    h.next_kbn,
                    h.sender_cd,
                    TO_CHAR(h.sale_dt,'YYYY-MM-DD') as sale_dt, 
                    h.tokuisaki_cd, 
                    h.okurisaki_cd,
                    t.tokuisaki_nm,
                    t.tokuisaki_zip,
                    t.tokuisaki_adr_1,
                    t.tokuisaki_adr_2,
                    t.tokuisaki_adr_3,
                    t.tokuisaki_tel,
                    h.okurisaki_cd, 
                    h.inquire_no, 
                    h.order_kbn, 
                    h.sale_kbn,
                    h.delivery_kbn,
                    TO_CHAR(h.receive_dt,'YYYY-MM-DD') as receive_dt,
                    h.delivery_time_kbn,
                    h.delivery_time_hr,
                    h.delivery_time_min,
                    h.delivery_instruct_kbn,
                    h.total_qty, 
                    h.total_cost,
                    h.tax_8,
                    h.tax_10,
                    h.grand_total,
                    h.kosu, 
                    h.yamato_kbn,
                    d.product_cd,
                    s.product_nm_abrv,
                    COALESCE(d.product_nm, '存在しない') AS product_nm,
                    d.tanka,
                    d.qty,
                    s.tax_kbn,
                    d.total_cost as row_total_cost,
                    t.comment,
                    r.denpyo_flg,
                    r.hikae_flg,
                    r.receipt_flg,
                    r.order_flg,
                    r.label_flg
                    FROM t_sale_h h
                    LEFT JOIN t_sale_d d ON h.order_no = d.order_no
                    LEFT JOIN t_sale_report r ON h.order_no = r.order_no
                    LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                    WHERE h.order_no = :order_no
                    ORDER BY d.row_no";

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;

            /** 売上明細履歴リスト **/
        case "mesaiHistoryList":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先を選択してください。");

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

            $offset = MESAI_HISTORY_LIST_CNT * ($_REQUEST["pagenum"] - 1);

            $sql = "SELECT 
                    TO_CHAR(h.sale_dt,'YYYY/MM/DD') as sale_dt
                    , d.product_cd
                    , s.product_nm as product_nm
                    , COALESCE(d.product_nm, '存在しない') as sub_nm
                    , d.qty as qty
                    , d.total_cost as total_cost
                    FROM t_sale_d d
                    LEFT JOIN t_sale_h h ON d.order_no = h.order_no
                    LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                    WHERE h.tokuisaki_cd = :tokuisaki_cd
                    ORDER BY h.sale_dt DESC, h.order_no DESC, row_no ASC
                    LIMIT " . MESAI_HISTORY_LIST_CNT . " OFFSET " . $offset;

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            $sql = "SELECT COUNT(*) FROM t_sale_d d
                    LEFT JOIN t_sale_h h ON d.order_no = h.order_no
                    WHERE h.tokuisaki_cd = :tokuisaki_cd;";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            $total_pg = ceil($cnt / MESAI_HISTORY_LIST_CNT);

            array_push($list, array("total_page" => $total_pg, "count" => $cnt));

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;

            /** 売上履歴リスト **/
        case "saleHistoryList":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先を選択してください。");

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

            $offset = SALE_HISTORY_LIST_CNT * ($_REQUEST["pagenum"] - 1);

            $sql = "SELECT
                    h.order_no as order_no
                    , TO_CHAR(h.sale_dt,'YYYY/MM/DD') as sale_dt
                    , h.grand_total AS grand_total
                    FROM t_sale_h h
                    WHERE h.tokuisaki_cd = :tokuisaki_cd
                    GROUP BY h.order_no, h.sale_dt
                    ORDER BY h.sale_dt DESC, h.order_no DESC
                    LIMIT " . SALE_HISTORY_LIST_CNT . " OFFSET " . $offset;

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("対象となるデータはありません。");

            $sql = "SELECT COUNT(*) FROM t_sale_h WHERE tokuisaki_cd = :tokuisaki_cd;";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            $total_pg = ceil($cnt / SALE_HISTORY_LIST_CNT);

            array_push($list, array("total_page" => $total_pg, "count" => $cnt));

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;

            /** 売上履歴詳細 **/
        case "saleHistoryDetail":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号が指定されていません。");

            $sql = "SELECT 
                    order_no
                    , TO_CHAR(h.receive_dt, 'YYYY/MM/DD') as sale_dt
                    , CASE WHEN h.delivery_kbn = '1' THEN c6.kanri_nm
                        WHEN h.delivery_kbn = '2' THEN CONCAT(c4.kanri_nm, ' ',delivery_time_hr,':',delivery_time_min, ' ', c5.kanri_nm)
                        ELSE ''
                    END AS delivery_time
                    , c1.kanri_nm as order_kbn
                    , c2.kanri_nm  as sale_kbn
                    , c3.kanri_nm as delivery_type
                    , inquire_no
                    , kosu
                    FROM t_sale_h h
                    LEFT JOIN m_code c1 ON h.order_kbn = c1.kanri_cd AND c1.kanri_key = 'sales.order.kbn'
                    LEFT JOIN m_code c2 ON h.sale_kbn = c2.kanri_cd AND c2.kanri_key = 'sale.kbn'
                    LEFT JOIN m_code c3 ON h.delivery_kbn = c3.kanri_cd AND c3.kanri_key = 'delivery.kbn'
                    LEFT JOIN m_code c4 ON h.delivery_time_kbn = c4.kanri_cd AND c4.kanri_key = 'delivery.time.kbn'
                    LEFT JOIN m_code c5 ON h.delivery_instruct_kbn = c5.kanri_cd AND c5.kanri_key = 'delivery.instruct.kbn'
                    LEFT JOIN m_code c6 ON h.yamato_kbn = c6.kanri_cd AND c6.kanri_key = 'yamato.kbn'
                    WHERE order_no = :order_no";

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list[0], JSON_UNESCAPED_UNICODE);
            break;

            /** COPY ORDER **/
        case "copyOrder":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号が指定されていません。");

            $sql = "SELECT h.order_no, 
                        h.next_kbn,
                        TO_CHAR(h.sale_dt,'YYYY-MM-DD') AS sale_dt, 
                        h.inquire_no,
                        h.inquire_no, 
                        h.order_kbn, 
                        h.sale_kbn,
                        h.delivery_kbn,
                        TO_CHAR(h.receive_dt,'YYYY-MM-DD') AS receive_dt,
                        h.delivery_time_kbn,
                        h.delivery_time_hr,
                        h.delivery_time_min,
                        h.delivery_instruct_kbn,
                        h.yamato_kbn,
                        h.total_qty, 
                        h.total_cost,
                        h.tax_8,
                        h.tax_10,
                        h.grand_total,
                        h.kosu, 
                        d.product_cd,
                        s.product_nm_abrv,
                        s.sale_price,
                        COALESCE(ts.sale_price, '0') AS tokuisaki_price,
                        d.qty,
                        s.tax_kbn,
                        d.total_cost as row_total_cost
                        FROM t_sale_h h
                        LEFT JOIN t_sale_d d ON h.order_no = d.order_no
                        LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                        LEFT JOIN m_tokuisaki_shohin ts ON h.tokuisaki_cd = ts.tokuisaki_cd AND s.product_cd = ts.product_cd 
                        WHERE h.order_no = :order_no
                        ORDER BY d.row_no";

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;
            /** 問い合わせ番号を変更 **/
        case "changeInquireNo":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号が指定されていません。");
            if (!isset($_REQUEST["inquire_no"]) || $_REQUEST["inquire_no"] == "") throw new Exception("問合せ番号を入力してください。");
            if (!isset($_REQUEST["sale_dt"]) || $_REQUEST["sale_dt"] == "") throw new Exception("売上日を選択してください。");

            // $sql = "SELECT count(*) FROM t_sale_h 
            // WHERE inquire_no = :inquire_no 
            // AND order_no <> :order_no
            // AND kenpin_kbn = '0';";

            //CHECK IF INQUIRE NUMBER EXISTS FOR SAME SALE DATE
            $sql = "SELECT COUNT(*) FROM t_sale_h
                    WHERE inquire_no = :inquire_no
                    AND TO_CHAR(sale_dt, 'YYYY-MM-DD') = :sale_dt;";

            $params = array();
            $params["inquire_no"] = $_REQUEST["inquire_no"];
            $params["sale_dt"] = $_REQUEST["sale_dt"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            //if ($cnt != 0) throw new Exception("未検品の問合せ番号[" . $_REQUEST["inquire_no"] . "]が存在します。");
            if ($cnt != 0) throw new Exception("同じ売上日に問合せ番号[" . $_REQUEST["inquire_no"] . "]が既に存在します。");

            //UPDATE
            $sql = "UPDATE t_sale_h
                    SET inquire_no = :inquire_no
                    , update_user_id = :user_id
                    , update_date = CURRENT_TIMESTAMP
                    WHERE order_no = :order_no;";

            $params = array();
            $params["inquire_no"] = $_REQUEST["inquire_no"];
            $params["order_no"] = $_REQUEST["order_no"];
            $params["user_id"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $cnt = $sth->rowCount();

            if ($cnt != 1) throw new Exception("問合せ番号変更に失敗しました。");

            $dbh->commit();
            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** 電話番号ライブ検索 **/
        case "telLiveSearch":
            session_start();
            $_SESSION['created'] = time();

            if ($_REQUEST["tel"] == "") throw new Exception("電話番号を入力してください。");
            $offset = $_REQUEST["offset"];
            $sql = "SELECT tel.tokuisaki_cd, tel.tel_no , t.tokuisaki_nm
            FROM m_tokuisaki_tel tel
            INNER JOIN m_tokuisaki t ON tel.tokuisaki_cd = t.tokuisaki_cd
            WHERE t.search_flg = '1' 
            AND tel_no LIKE :tel 
            ORDER BY tokuisaki_cd
            LIMIT 50 OFFSET $offset;";

            $params = array();
            $params["tel"] = "%" . $_REQUEST["tel"] . "%";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($list, JSON_UNESCAPED_UNICODE);

            break;

        case "findTokuisakiByTel":
            session_start();
            $_SESSION['created'] = time();

            if ($_REQUEST["tokuisaki_tel"] == "") throw new Exception("電話番号を入力してください。");

            $sql = "SELECT t.tokuisaki_cd, 
                            tokuisaki_nm 
                    FROM m_tokuisaki t
                    WHERE t.tokuisaki_tel = :tel
                    ORDER BY tokuisaki_cd";

            $params = array();
            $params["tel"] = $_REQUEST["tokuisaki_tel"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) != 1) throw new Exception("対象となるデータはありません。");

            echo json_encode($list[0], JSON_UNESCAPED_UNICODE);

            break;

            /** 郵便番号ライブ検索 **/
        case "zipLiveSearch":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["zip"]) || $_REQUEST["zip"] == "") throw new Exception("郵便番号を入力してください。");

            $sql = "SELECT zip
            , ken_fu
            , REPLACE(shi_ku, '　', '') AS shi_ku
            , REPLACE(machi, '　', '') AS machi
            FROM m_zip 
            WHERE zip = :zip";

            $params = array();
            $params["zip"] = $_REQUEST["zip"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list[0], JSON_UNESCAPED_UNICODE);
            break;
        case "zipList":
            if (!isset($_REQUEST["zip"]) || $_REQUEST["zip"] == "") throw new Exception("郵便番号を入力してください。");

            $offset = $_REQUEST["offset"] ?? 0;

            $sql = "SELECT zip
                    FROM m_zip 
                    WHERE zip LIKE :zip
                    ORDER BY zip
                    LIMIT 50 OFFSET $offset";

            $params = array();
            $params["zip"] = $_REQUEST["zip"] . "%";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            // if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;

            /** パスワード変更 **/
        case "passwordChange":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["current_password"]) || $_REQUEST["current_password"] == "") throw new Exception("現在のパスワードを入力してください。");
            if (!isset($_REQUEST["new_password"]) || $_REQUEST["new_password"] == "") throw new Exception("新しいパスワードを入力してください。");

            $sql = "SELECT password
                    FROM m_user
                    WHERE user_id = :user_id;";
            $param = array();
            $param["user_id"] = $_SESSION["user_id"];
            $sth = $dbh->prepare($sql);
            $sth->execute($param);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) < 1) throw new Exception("該当の担当者が見つかりませんでした。");

            if (!password_verify($_REQUEST["current_password"], $list[0]["password"])) throw new Exception("現在のパスワードが正しくありません。");

            $sql = "UPDATE m_user
                    SET password = :new_password
                    , update_user_id = :user_id
                    , update_date = CURRENT_TIMESTAMP
                    WHERE user_id = :user_id";

            $params = array();
            $params["new_password"] = password_hash($_REQUEST["new_password"], PASSWORD_BCRYPT, array('cost' => 12));
            $params["user_id"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $row_num = $sth->rowCount();
            if ($row_num == 0) throw new Exception("パスワード更新に失敗しました。");

            $dbh->commit();
            echo json_encode(array("result" => ""), JSON_UNESCAPED_UNICODE);
            break;

            /** メモを取得 **/
        case "getMemo":
            session_start();
            $_SESSION['created'] = time();

            $sql = "SELECT memo, update_date FROM t_memo;";
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $res = $sth->fetch(PDO::FETCH_ASSOC);
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

            /** メモを更新**/
        case "updateMemo":
            session_start();
            $_SESSION['created'] = time();

            $check = "SELECT count(memo) FROM t_memo WHERE update_date = :update_date;";

            $params = array();
            $params["update_date"] = $_REQUEST["update_date"];
            $sth = $dbh->prepare($check);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            if ($cnt == 0) {
                echo json_encode("NG", JSON_UNESCAPED_UNICODE);
                return;
            }

            $update = "UPDATE t_memo 
                        SET memo = :memo
                        , update_user_id = :update_id
                        , update_date = CURRENT_TIMESTAMP;";

            $params = array();
            $params["memo"] = urldecode($_REQUEST["memo"]);
            $params["update_id"] = $_SESSION["user_id"];
            $sth = $dbh->prepare($update);
            $sth->execute($params);
            $icnt = $sth->rowCount();

            $res = ($icnt > 0) ? "OK" : "NG";

            $dbh->commit();
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            break;

        case "yamatoZipCheck":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["zip"]) || $_REQUEST["zip"] == "") throw new Exception("郵便番号を入力してください。");

            $sql = "SELECT delivery_cd FROM m_yamato WHERE key_part = :zip;";
            $param = array();
            $param["zip"] = $_REQUEST["zip"];
            $sth = $dbh->prepare($sql);
            $sth->execute($param);
            $zip = $sth->fetchColumn();

            if ($zip == "") throw new Exception($_REQUEST["zip"] . "がヤマト郵便番号対応仕分マスタに存在しません。");
            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;
            /** RETURN ORDER NO **/
        case "getOrderNo":
            session_start();
            $_SESSION['created'] = time();
            echo json_encode($_SESSION["order_no"], JSON_UNESCAPED_UNICODE);
            break;

            /** 再発行 **/
        case "pdfReportUpdate":
            session_start();
            $_SESSION['created'] = time();

            $order_no = json_decode($_REQUEST["order_no"], true);
            if (count($order_no) == 0 || $_REQUEST["order_no"] == "") throw new Exception("受注番号を選択してください。");

            $sql = "UPDATE t_sale_report
                        SET denpyo_flg = :denpyo_flg
                        , hikae_flg = :hikae_flg
                        , receipt_flg = :receipt_flg
                        , order_flg = :order_flg
                        , label_flg = :label_flg
                        , print_flg = :print_flg
                        , update_user_id = :user_id
                        , update_date = CURRENT_TIMESTAMP
                        WHERE order_no = :order_no;";
            $sth = $dbh->prepare($sql);

            $params = array();
            $params["denpyo_flg"] = '0';
            $params["hikae_flg"] = $_REQUEST["hikae_flg"];
            $params["receipt_flg"] = $_REQUEST["receipt_flg"];
            if ($_REQUEST["denpyo_flg"] == '1' || $_REQUEST["order_flg"] == '1') {
                $params["order_flg"] = '1';
            } else {
                $params["order_flg"] = '0';
            }
            $params["label_flg"] = $_REQUEST["label_flg"];
            $params["print_flg"] = '0';
            $params["user_id"] = $_SESSION["user_id"];

            for ($i = 0; $i < count($order_no); $i++) {
                $params["order_no"] = $order_no[$i];
                $sth->execute($params);
            }

            //Commit transaction
            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** ヤマト送り状 **/
        case "yamatoShipInvoice":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先を選択してください");

            //1) GET DATA

            $sql = "SELECT
                    tokuisaki_nm
                    , tokuisaki_tel
                    , tokuisaki_zip
                    , CONCAT(tokuisaki_adr_1,tokuisaki_adr_2) as tokuisaki_adr
                    , tokuisaki_adr_3
                    FROM m_tokuisaki
                    WHERE tokuisaki_cd = :tokuisaki_cd
                    AND delivery_kbn = '1'
                    AND sale_kbn = '4';";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($data) != 1) throw new Exception("対象となるデータはありません。");

            //2) create PDF
            $fname = TEMP_FOLDER . "yamato_ship_invoice_" . uniqid(mt_rand(), true) . PDF;
            yamato_ship_invoice($fname, $data[0]);

            //3) send pdf blob
            header('Content-Type: application/pdf');
            header("Content-Disposition: attachment; filename=yamato_ship_invoice.pdf");
            header('Content-Length: ' . filesize($fname));
            readfile($fname);
            unlink($fname);

            break;

        case "sagawaShipInvoice":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先を選択してください");

            //1) GET DATA

            $sql = "SELECT
                        tokuisaki_nm
                        , tokuisaki_tel
                        , tokuisaki_zip
                        , CONCAT(tokuisaki_adr_1,tokuisaki_adr_2) as tokuisaki_adr
                        , tokuisaki_adr_3
                        FROM m_tokuisaki
                        WHERE tokuisaki_cd = :tokuisaki_cd
                        AND delivery_kbn = '2'
                        AND sale_kbn = '4';";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($data) != 1) throw new Exception("対象となるデータはありません。");

            //2) create PDF
            $fname = TEMP_FOLDER . "sagawa_ship_invoice_" . uniqid(mt_rand(), true) . PDF;
            sagawa_ship_invoice($fname, $data[0]);

            //3) send pdf blob
            header('Content-Type: application/pdf');
            header("Content-Disposition: attachment; filename=sagawa_ship_invoice.pdf");
            header('Content-Length: ' . filesize($fname));
            readfile($fname);
            unlink($fname);

            break;

            /** 日報・月報 **/
        case "reportPdf":
            session_start();
            $_SESSION['created'] = time();

            //得意先別
            $tokuisaki_sql = "SELECT
                                sale_dt
                                , SUM(qty) AS qty
                                , SUM(cost) AS cost
                                , SUM(unit_cost) AS unit_cost
                                , SUM(cost) - SUM(unit_cost) AS total
                                FROM
                                (
                                    SELECT
                                        TO_CHAR(h.sale_dt,'YYYY/MM/DD') as sale_dt
                                        , SUM(CAST(d.qty AS DECIMAL)) as qty
                                        , SUM(CAST(d.total_cost AS DECIMAL)) as cost
                                        , SUM(CAST(d.qty AS DECIMAL) * CAST(s.unit_price AS DECIMAL)) as unit_cost
                                    FROM t_sale_h h
                                    LEFT JOIN t_sale_d d ON h.order_no = d.order_no
                                    LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                                    WHERE 1=1";
            $tokuisaki_group = " GROUP BY h.order_no) sub GROUP BY sale_dt ORDER BY sale_dt;";

            //商品別                            
            $product_sql = "SELECT
                                d.product_cd
                                , s.product_nm
                                , SUM(CAST(d.qty AS DECIMAL)) as qty
                                , SUM(CAST(d.total_cost AS DECIMAL)) as cost
                                FROM t_sale_h h
                                LEFT JOIN t_sale_d d ON h.order_no = d.order_no
                                LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                                WHERE 1=1";
            $product_group = " GROUP BY d.product_cd , s.product_nm ORDER BY d.product_cd";

            $whr = "";
            $params = array();

            switch ($_REQUEST["sale_kbn"]) {
                case "1":
                    $whr .= " AND h.sale_dt = CURRENT_DATE";
                    // if ($_REQUEST["date_from"] == "") {
                    //     $params["sale_dt"] = "TO_CHAR(now(),'YYYY/MM/DD')";
                    // } else {
                    //     $params["sale_dt"] = $_REQUEST["date_from"];
                    // }
                    $date_from = date("Y/m/d");
                    $date_to = date("Y/m/d");
                    break;
                case "2":
                    $whr .= " AND TO_CHAR(h.sale_dt,'YYYY/MM') = TO_CHAR(now(),'YYYY/MM')";
                    // if ($_REQUEST["date_from"] == "") {
                    //     $params["sale_dt"] = "TO_CHAR(now(),'YYYY/MM')";
                    // } else {
                    //     $params["sale_dt"] = "TO_CHAR(".$_REQUEST["date_from"].",'YYYY/MM')";
                    // }
                    $date_from = date("Y/m/01");
                    $date_to = date("Y/m/t");
                    break;
                case "3":
                    $whr .= " AND TO_CHAR(h.sale_dt,'YYYY/MM') = TO_CHAR(now() - interval '1 months','YYYY/MM')";
                    // if ($_REQUEST["date_from"] == "") {
                    //     $params["sale_dt"] = "TO_CHAR(now() - interval '1 months','YYYY/MM')";
                    // } else {
                    //     $params["sale_dt"] = $_REQUEST["date_from"];
                    // }
                    $date_from = date("Y/m/01", strtotime('last month'));
                    $date_to = date("Y/m/t", strtotime('last month'));
                    break;
                case "4":
                    $whr .= " AND h.sale_dt >= :date_from 
                                    AND h.sale_dt <= :date_to";

                    if ($_REQUEST["date_from"] == "") throw new Exception("売上日[開始]を選択してください。");
                    if ($_REQUEST["date_to"] == "") throw new Exception("売上日[終了]を選択してください。");

                    $params["date_from"] = $_REQUEST["date_from"];
                    $params["date_to"] = $_REQUEST["date_to"];

                    $date_from =  date("Y/m/d", strtotime($_REQUEST["date_from"]));
                    $date_to = date("Y/m/d", strtotime($_REQUEST["date_to"]));

                    break;
                default:
                    throw new Exception("売上区分を選択してください。");
                    break;
            };

            if ($_REQUEST["daily_kbn"] == "1") {
                $pdf_func = "tokuisakiSale";
                $fname = "tokuisaki_sales_total_" . uniqid(mt_rand(), true) . PDF;
                $sql = $tokuisaki_sql . $whr . $tokuisaki_group;
            } else {
                $pdf_func = "shohinSalesList";
                $fname = "product_sales_total_" . uniqid(mt_rand(), true) . PDF;
                $sql = $product_sql . $whr . $product_group;
            }

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($data) == 0) throw new Exception("対象となるデータはありません。");

            //2) create PDF
            $pdf_path = TEMP_FOLDER . $fname;
            $pdf_func($pdf_path, $data, $date_from, $date_to);

            //3) send pdf blob
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $fname . '"');
            header('Content-Length: ' . filesize($pdf_path));
            readfile($pdf_path);
            unlink($pdf_path);

            break;

            /** 荷物受渡書 **/
        case "statementOfDelivery":
            session_start();
            $_SESSION['created'] = time();

            if ($_REQUEST["customer_cd"] == "") throw new Exception("荷受人を選択してください。");
            if ($_REQUEST["shuka_dt"] == "") throw new Exception("出荷日を選択してください。");
            if (!isset($_REQUEST["print_flg"]) || $_REQUEST["print_flg"] == "") throw new Exception("発行区分を選択してください。");

            $params = array();
            $params["customer_cd"] = $_REQUEST["customer_cd"];

            //CHECK IF DATA EXIST
            $sql = "SELECT COUNT(*) FROM t_sale_h 
                    WHERE 1=1";
            $whr = "";

            //発行
            if ($_REQUEST["print_flg"] == 0) {
                $whr .= " AND sale_dt <= :shuka_dt";
                $params["shuka_dt"] = $_REQUEST["shuka_dt"];

                //再発行
            } else {
                $whr .= " AND shuka_print_dt = :shuka_dt";
                $params["shuka_dt"] = $_REQUEST["shuka_dt"];
            };

            $whr .= " AND sender_cd = :customer_cd
                    AND sale_kbn = '1'
                    AND delivery_kbn = '1'";

            //発行
            if ($_REQUEST["print_flg"] == 0) {
                $whr .= " AND shuka_report_flg = '0'";

                //再発行
            } else {
                $whr .= " AND shuka_report_flg = '1'";
                if ($_REQUEST["shuka_print_qty"] > 0) {
                    $whr .= " AND shuka_print_qty = :shuka_print_qty";
                    $params["shuka_print_qty"] = $_REQUEST["shuka_print_qty"];
                };
            };

            $sql .= $whr;

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            if ($cnt == 0) throw new Exception("出荷日報・荷物受渡確認書のデータが存在しません。");

            //UPDATE 締め回数
            //発行の場合のみ
            if ($_REQUEST["print_flg"] == 0) {
                $sql = "UPDATE t_sale_h
                        SET shuka_print_qty = :shuka_print_qty
                        , shuka_print_dt = :shuka_print_dt
                        , update_user_id = :user_id
                        WHERE sale_dt <= :shuka_dt
                        AND sender_cd = :customer_cd
                        AND shuka_report_flg = '0'
                        AND sale_kbn = '1'
                        AND delivery_kbn = '1';";

                $params = array();
                $params["shuka_dt"] = $_REQUEST["shuka_dt"];
                $params["shuka_print_dt"] = $_REQUEST["shuka_dt"];
                $params["shuka_print_qty"] = intval($_REQUEST["shuka_print_qty"]);
                $params["customer_cd"] = $_REQUEST["customer_cd"];
                $params["user_id"] = $_SESSION["user_id"];

                $sth = $dbh->prepare($sql);
                $sth->execute($params);
            };

            //GET 荷物受渡書 DATA
            $sql = "SELECT
                    TO_CHAR(sale_dt,'YYYY/MM/DD') AS shuka_dt,
                    sender_cd,
                    COUNT(inquire_no) as shuka_cnt,
                    SUM(CAST(kosu AS INTEGER)) AS kosu_total,
                    (SELECT kanri_cd FROM m_code WHERE kanri_key = 'ninushi.code') AS ninushi_cd
                    FROM t_sale_h
                    WHERE shuka_print_dt = :shuka_dt
                    AND sender_cd = :customer_cd";

            $whr = "";
            $params = array();
            $params["customer_cd"] = $_REQUEST["customer_cd"];
            $params["shuka_dt"] = $_REQUEST["shuka_dt"];

            if ($_REQUEST["shuka_print_qty"] > 0) {
                $whr = " AND shuka_print_qty = :shuka_print_qty";
                $params["shuka_print_qty"] = $_REQUEST["shuka_print_qty"];
            };

            $sql .= $whr . " GROUP BY shuka_dt, sender_cd ORDER BY shuka_dt";

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            //2) create PDF
            $fname = TEMP_FOLDER . "statementOfDelivery_" . uniqid(mt_rand(), true) . PDF;

            statementOfDelivery($fname, $data, date("Y年m月d日", strtotime($_REQUEST["shuka_dt"])));

            //3) send pdf blob
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="statementOfDelivery.pdf"');
            header('Content-Length: ' . filesize($fname));
            readfile($fname);
            unlink($fname);

            $dbh->commit();
            break;

            /** 出荷日報 **/
        case "shukaReportData":
            session_start();
            $_SESSION['created'] = time();

            //GET 出荷日報 DATA
            $sql = "SELECT
                        h.order_no AS order_no
                        , TO_CHAR(sale_dt,'YYYY/MM/DD') AS shuka_dt
                        , h.inquire_no AS inquire_no
                        , h.grand_total AS grand_total
                        , h.kosu AS kosu
                        , o.okurisaki_tel AS tokuisaki_tel
                        , o.okurisaki_nm AS tokuisaki_nm
                        , CONCAT(o.okurisaki_adr_1, o.okurisaki_adr_2) AS address
                        , o.okurisaki_adr_3 AS building
                        , h.sender_cd AS sender_cd
                    FROM t_sale_h h
                    LEFT JOIN m_okurisaki o ON h.okurisaki_cd = o.okurisaki_cd AND h.tokuisaki_cd = o.tokuisaki_cd
                    WHERE shuka_print_dt = :shuka_dt
                    AND sender_cd = :customer_cd";

            $params = array();
            $params["customer_cd"] = $_REQUEST["customer_cd"];
            $params["shuka_dt"] = $_REQUEST["shuka_dt"];

            if ($_REQUEST["shuka_print_qty"] > 0) {
                $sql .= " AND shuka_print_qty = :shuka_print_qty";
                $params["shuka_print_qty"] = $_REQUEST["shuka_print_qty"];
            };
            $sql .= " ORDER BY shuka_dt, h.order_no;";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $order_data = $sth->fetchAll(PDO::FETCH_ASSOC);

            //GET 明細 DATA
            $sql = "SELECT 
                    d.order_no AS order_no
                    ,s.product_nm_abrv AS product_nm
                    , c.kanri_nm AS tani
                    , d.qty AS qty
                    FROM t_sale_d d 
                    LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                    LEFT JOIN m_code c ON s.sale_tani = c.kanri_cd AND c.kanri_key = 'sale.tani'
                    WHERE order_no IN (";
            $whr = "";
            for ($i = 0; $i < count($order_data); $i++) {
                if ($i == 0) {
                    $whr .= "'" . $order_data[$i]["order_no"] . "'";
                } else {
                    $whr .= "," . "'" . $order_data[$i]["order_no"] . "'";
                };
            };
            $sql .= $whr . ") ORDER BY order_no, row_no;";
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $product_data = $sth->fetchAll(PDO::FETCH_ASSOC);

            //2) create PDF
            $fname = TEMP_FOLDER . "shukaReportData_" . uniqid(mt_rand(), true) . PDF;
            shukaReportData($fname, $order_data, $product_data, date("Y年m月d日", strtotime($_REQUEST["shuka_dt"])));

            //3) send pdf blob
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="shukaReportData.pdf"');
            header('Content-Length: ' . filesize($fname));
            readfile($fname);
            unlink($fname);

            //UPDATE PRINT FLG
            if ($_REQUEST["print_flg"] == "0") {
                $sql = "UPDATE t_sale_h
                SET delivery_form_flg = '1'
                , shuka_report_flg = '1'
                , update_user_id = :user_id
                WHERE shuka_print_dt = :shuka_dt
                AND sender_cd = :customer_cd
                AND shuka_print_qty = :shuka_print_qty;";

                $params = array();
                $params["customer_cd"] = $_REQUEST["customer_cd"];
                $params["shuka_dt"] = $_REQUEST["shuka_dt"];
                $params["shuka_print_qty"] = $_REQUEST["shuka_print_qty"];
                $params["user_id"] = $_SESSION["user_id"];

                $sth = $dbh->prepare($sql);
                $sth->execute($params);

                $dbh->commit();
            };
            break;

            /** 締め回数 **/
        case "shukaReportCount":
            session_start();
            $_SESSION['created'] = time();

            if ($_REQUEST["customer_cd"] == "") throw new Exception("荷送人を選択してください。");
            if ($_REQUEST["shuka_dt"] == "") throw new Exception("出荷日を選択してください。");

            $hako_sql = "SELECT MAX(shuka_print_qty)
                            FROM t_sale_h
                            WHERE shuka_print_dt = :shuka_dt
                            AND sender_cd = :customer_cd;";

            $re_hako_sql = "SELECT shuka_print_qty AS cnt
                            FROM t_sale_h
                            WHERE shuka_print_dt = :shuka_dt
                            AND sender_cd = :customer_cd
                            AND shuka_report_flg = '1'
                            GROUP BY shuka_print_qty
                            ORDER BY shuka_print_qty;";

            $sql = ($_REQUEST["print_flg"] == "0") ? $hako_sql : $re_hako_sql;

            $params = array();
            $params["customer_cd"] = $_REQUEST["customer_cd"];
            $params["shuka_dt"] = $_REQUEST["shuka_dt"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($cnt, JSON_UNESCAPED_UNICODE);
            break;

        case "shukaReportCsv":
            session_start();
            $_SESSION['created'] = time();

            if ($_REQUEST["customer_cd"] == "") throw new Exception("荷受人を選択してください。");
            if ($_REQUEST["shuka_dt"] == "") throw new Exception("出荷日を選択してください。");
            if (!isset($_REQUEST["print_flg"]) || $_REQUEST["print_flg"] == "") throw new Exception("発行区分を選択してください。");

            //CHECK IF DATA EXIST
            $sql = "SELECT COUNT(*) FROM t_sale_h 
                    WHERE sender_cd = :customer_cd
                    AND sale_kbn = '1'
                    AND delivery_kbn = '1'";
            $whr = "";
            $params = array();
            $params["customer_cd"] = $_REQUEST["customer_cd"];

            //発行
            if ($_REQUEST["print_flg"] == 0) {
                $whr .= " AND sale_dt <= :shuka_dt
                                AND shuka_report_flg = '0'";
                $params["shuka_dt"] = $_REQUEST["shuka_dt"];

                //再発行
            } else {
                $whr .= " AND shuka_print_dt  = :shuka_dt
                                AND shuka_report_flg = '1'";
                $params["shuka_dt"] = $_REQUEST["shuka_dt"];

                if ($_REQUEST["shuka_print_qty"] > 0) {
                    $whr .= " AND shuka_print_qty = :shuka_print_qty";
                    $params["shuka_print_qty"] = $_REQUEST["shuka_print_qty"];
                };
            }
            $sql .= $whr;

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            if ($cnt == 0) throw new Exception("出荷日報・荷物受渡確認書のデータが存在しません。");

            $params = array();

            //GET 出荷日報 DATA
            $sql = "SELECT
                    TO_CHAR(h.sale_dt,'YYYY/MM/DD') AS sale_dt
                    , h.order_no AS order_no
                    , h.tokuisaki_cd AS tokuisaki_cd
                    , o.okurisaki_nm AS tokuisaki_nm
                    , CONCAT(o.okurisaki_adr_1, o.okurisaki_adr_2, o.okurisaki_adr_3) AS tokuisaki_adr
                    , o.okurisaki_tel AS tokuisaki_tel
                    , h.inquire_no AS inquire_no
                    , h.kosu AS kosu
                    , c1.kanri_nm AS sale_kbn_nm
                    , c2.kanri_nm AS delivery_kbn_nm
                    FROM t_sale_h h
                    LEFT JOIN m_okurisaki o ON h.okurisaki_cd = o.okurisaki_cd AND h.tokuisaki_cd = o.tokuisaki_cd
                    LEFT JOIN m_code c1 ON h.sale_kbn = c1.kanri_cd AND c1.kanri_key = 'sale.kbn'
                    LEFT JOIN m_code c2 ON h.delivery_kbn = c2.kanri_cd AND c2.kanri_key = 'delivery.kbn'
                    WHERE shuka_print_dt = :shuka_dt
                    AND sender_cd = :customer_cd";

            $whr = "";
            if ($_REQUEST["shuka_print_qty"] > 0) {
                $whr .= " AND shuka_print_qty = :shuka_print_qty";
                $params["shuka_print_qty"] = $_REQUEST["shuka_print_qty"];
            };

            $params["shuka_dt"] = $_REQUEST["shuka_dt"];
            $params["customer_cd"] = $_REQUEST["customer_cd"];

            $sql .= $whr . " ORDER BY sale_dt, sender_cd, tokuisaki_tel, inquire_no;";

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("出荷日報・荷物受渡確認書のデータが存在しません。");

            //LOOP THROUGH AND CREATE CSV
            $fname = TEMP_FOLDER . "shuka_data_" . date("YmdHis") . CSV;

            //OPEN STREAM
            $fp = fopen($fname, 'w');

            // HEADERS 
            $fields = array('売上日', '受注No.', '得意先コード', '得意先名', '住所', '電話番号', '問い合せNo.', '個数', '売上区分', '運送会社');
            fputcsv($fp, $fields);

            //LOOP
            foreach ($list as $row) {
                fputcsv($fp, $row);
            };

            //CLOSE STREAM
            fclose($fp);

            //SEND CSV TO USER
            header('Content-Type: text/csv', false);
            header("Content-Disposition: attachment; filename=shuka_data.csv");
            header('Content-Length: ' . filesize($fname));
            readfile($fname);
            unlink($fname);

            break;

        case "shukaDataCheck":

            if (!isset($_REQUEST["shuka_dt"]) || $_REQUEST["shuka_dt"] == "") throw new Exception("出荷日を選択してください。");

            //受託データの取得
            //GET DATA
            //YAMATO DELIVERY TIME CODE
            $sql = "SELECT COUNT(*) FROM t_sale_h h
                    LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN m_okurisaki o ON h.okurisaki_cd = o.okurisaki_cd AND h.tokuisaki_cd = o.tokuisaki_cd
                    LEFT JOIN m_yamato y ON o.okurisaki_zip = y.key_part
                    LEFT JOIN t_sale_report r ON h.order_no = r.order_no
                    WHERE sale_dt <= :sale_dt
                    AND send_flg = '0'
                    AND h.shuka_report_flg = '0'
                    AND r.label_flg = '1'
                    AND h.sale_kbn = '1'
                    AND h.delivery_kbn = '1';";

            $params = array();
            $params["sale_dt"] = $_REQUEST["shuka_dt"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->fetchColumn();

            echo json_encode($cnt, JSON_UNESCAPED_UNICODE);

            break;
            /** 出荷データ送信 **/
        case "shukaDataSend":
            session_start();
            $_SESSION['created'] = time();

            //必須項目を確認
            if (!isset($_REQUEST["shuka_dt"]) || $_REQUEST["shuka_dt"] == "") throw new Exception("出荷日を選択してください。");

            //first time set time
            //連続で送信する場合、15分開けてからできる
            //１回目は時間を設定
            if (empty($_SESSION["shuka_send_time"])) {
                $_SESSION["shuka_send_time"] = time();
            } else {
                //not yet reached alloted wait time
                //時間を確認
                //１５分間まだ立てない
                if ((time() - $_SESSION['shuka_send_time']) < SHUKA_SEND_WAIT_TIME) {
                    throw new Exception("連続でファイルを送信する場合は、15分以上間隔を開けて下さい。");
                } else {
                    //reached wait time 
                    //reset time
                    //時間を設定
                    $_SESSION["shuka_send_time"] = time();
                }
            }

            //GET FTP INFO
            //SFTP送信情報・メーカー送信情報
            $sql = "SELECT 
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'ftp.info' AND kanri_nm = 'account_id') AS account_id,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'ftp.info' AND kanri_nm = 'account_name') AS account_name,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'ftp.info' AND kanri_nm = 'account_pwd') AS account_pwd,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'ftp.info' AND kanri_nm = 'host') AS host,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'ftp.info' AND kanri_nm = 'server_ip') AS server_ip,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'ftp.info' AND kanri_nm = 'port') AS port,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'ftp.info' AND kanri_nm = 'pasv_mode') AS pasv_mode,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'ftp.info' AND kanri_nm = 'file_name') AS file_name,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.host') AS mail_host,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.port') AS mail_port,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.user') AS mail_user,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.pwd') AS mail_pwd,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.address') AS mail_address,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'mail.from') AS mail_from;";

            $sth = $dbh->prepare($sql);
            $sth->execute();
            $ftp_info = $sth->fetch(PDO::FETCH_ASSOC);

            if (count($ftp_info) == 0) throw new Exception("送信情報がありません。");

            //PRODUCT SQL
            //USE IN LOOP
            //商品取得SQL
            $product_sql = "SELECT 
                            s.product_cd
                            , s.product_nm_abrv
                            FROM t_sale_d d
                            INNER JOIN m_shohin s ON d.product_cd = s.product_cd
                            WHERE d.order_no = :order_no
                            LIMIT 10;";
            $product_sth = $dbh->prepare($product_sql);

            //受託データの取得
            //GET DATA
            //YAMATO DELIVERY TIME CODE
            $sql = "SELECT
                    h.order_no AS order_no
                    , TO_CHAR(h.sale_dt,'YYYYMMDD') AS sale_dt
                    , h.yamato_kbn AS yamato_kbn
                    , h.inquire_no AS inquire_no
                    , h.delivery_form_flg AS delivery_form_flg
                    , h.shuka_report_flg AS shuka_report_flg
                    , h.kosu AS kosu
                    , TO_CHAR(h.receive_dt,'YYYYMMDD') AS receive_dt
                    , h.grand_total AS grand_total
                    , t.delivery_instruct AS delivery_instruct
                    , o.okurisaki_cd AS okurisaki_cd
                    , o.okurisaki_nm AS okurisaki_nm
                    , o.okurisaki_zip AS okurisaki_zip
                    , CONCAT(o.okurisaki_adr_1,o.okurisaki_adr_2,o.okurisaki_adr_3) AS okurisaki_adr
                    , o.okurisaki_tel AS okurisaki_tel
                    , y.delivery_cd AS yamato_delivery_cd
                    FROM t_sale_h h
                    LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN m_okurisaki o ON h.okurisaki_cd = o.okurisaki_cd AND h.tokuisaki_cd = o.tokuisaki_cd
                    LEFT JOIN m_yamato y ON o.okurisaki_zip = y.key_part
                    LEFT JOIN t_sale_report r ON h.order_no = r.order_no
                    WHERE sale_dt <= :sale_dt
                    AND send_flg = '0'
                    AND h.sale_kbn = '1'
                    AND r.label_flg = '1'
                    AND h.delivery_kbn = '1'
                    ORDER BY order_no;";
            //                    AND h.sale_kbn = '1'
            $params = array();
            $params["sale_dt"] = $_REQUEST["shuka_dt"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("送信対象のデータが存在しません。");

            //合計件数・合計個数
            $total_kensu = count($list);
            $total_kosu = number_format(array_sum(array_column($list, "kosu")));

            //CREATE YAMATO DAT FILE
            //ファイルを開く
            $file = YAMATO_FOLDER . YAMATO_FILE . "_" .  date('YmdHis');
            $tmp = TEMP_FOLDER . YAMATO_FILE . "_" .  date('YmdHis') . ".txt";
            $fp = fopen($file, 'w');
            $tmpFP = fopen($tmp, 'w');

            //WRITE TO FILE
            //書き込むデータを作成
            //バイト数は固定
            foreach ($list as $row) {
                $str = "";

                // お客様管理番号
                $str .= mb_convert_encoding(StrBytePad(trim($row["order_no"]), 20, " "), 'CP932', 'UTF-8');

                // 問合せ番号・ヤマト伝票番号
                $str .= mb_convert_encoding(StrBytePad(trim($row["inquire_no"]), 12, " "), 'CP932', 'UTF-8');

                // 電話番号
                $str .= mb_convert_encoding(StrBytePad(trim($row["okurisaki_tel"]), 12, " "), 'CP932', 'UTF-8');

                // 送り先名
                $str .= mb_convert_encoding(StrBytePad(trim($row["okurisaki_nm"]), 30, " "), 'CP932', 'UTF-8');

                // 送り先郵便番号
                $str .= mb_convert_encoding(StrBytePad(trim($row["okurisaki_zip"]), 7, " "), 'CP932', 'UTF-8');

                // 送り先住所
                $str .= mb_convert_encoding(StrBytePad(trim($row["okurisaki_adr"]), 96, " "), 'CP932', 'UTF-8');

                // 届け先部門名称1
                $str .= mb_convert_encoding(str_repeat(" ", 50), 'CP932', 'UTF-8');

                // 届け先部門名称2
                $str .= mb_convert_encoding(str_repeat(" ", 50), 'CP932', 'UTF-8');

                // ウエダ食品電話番号
                $str .= mb_convert_encoding(StrBytePad(trim(str_replace("-", "", TEL)), 12, " "), 'CP932', 'UTF-8');

                // ウエダ食品名
                $str .= mb_convert_encoding(StrBytePad(trim(POST_COMPANY), 30, " "), 'CP932', 'UTF-8');

                // ウエダ食品住所
                $str .= mb_convert_encoding(StrBytePad(trim(POST_ADDRESS), 96, " "), 'CP932', 'UTF-8');

                // 顧客コード
                $str .= mb_convert_encoding("075561725901", 'CP932', 'UTF-8');

                // 顧客コード枝番
                $str .= mb_convert_encoding(str_repeat(" ", 3), 'CP932', 'UTF-8');

                // 備考
                $str .= mb_convert_encoding(str_repeat(" ", 3), 'CP932', 'UTF-8');

                // 仕分けコード
                $str .= mb_convert_encoding(StrBytePad(trim($row["yamato_delivery_cd"]), 7, " "), 'CP932', 'UTF-8');

                // 商品
                $param = array();
                $param["order_no"] = $row["order_no"];
                $product_sth->execute($param);
                $products = $product_sth->fetchAll(PDO::FETCH_ASSOC);

                if (count($products) <= 5) {
                    $disp_name = true;
                } else {
                    $disp_name = false;
                }

                $cd = "";
                $productNm = "";
                foreach ($products as $product) {
                    // 商品コード
                    $cd .= mb_convert_encoding(StrBytePad(trim($product["product_cd"]), 3, " "), 'CP932', 'UTF-8');

                    // 商品名
                    if ($disp_name) {
                        $productNm .= mb_convert_encoding(StrBytePad(trim($product["product_nm_abrv"]), 10, " "), 'CP932', 'UTF-8');
                    }
                }

                // 商品コード
                $str .= mb_convert_encoding(StrBytePad($cd, 30, " "), 'CP932', 'UTF-8');

                // 商品名
                $str .= str_pad($productNm, 50);

                // サイズ品目コード
                $str .= mb_convert_encoding("0401", 'CP932', 'UTF-8');

                // 配達指示
                $str .= mb_convert_encoding(StrBytePad(trim($row["delivery_instruct"]), 20, " "), 'CP932', 'UTF-8');

                // 品代金
                if (intval($row["grand_total"]) <= 0) {
                    $str .= mb_convert_encoding("0000000", 'CP932', 'UTF-8');
                } else {
                    $str .= mb_convert_encoding(StrBytePad(trim($row["grand_total"]), 7, " "), 'CP932', 'UTF-8');
                }

                // クール区分
                $str .= mb_convert_encoding(" ", 'CP932', 'UTF-8');

                // 送り先コード
                $str .= mb_convert_encoding(StrBytePad(trim($row["okurisaki_cd"]), 12, " "), 'CP932', 'UTF-8');

                // 出荷予定日
                $str .= mb_convert_encoding($row["sale_dt"], 'CP932', 'UTF-8');

                // 配達指定日 (receive_dt)
                $str .= mb_convert_encoding($row["receive_dt"], 'CP932', 'UTF-8');

                // 配達時間帯
                $str .= mb_convert_encoding(StrBytePad(trim($row["yamato_kbn"]), 2, " "), 'CP932', 'UTF-8');

                // OMS
                $str .= mb_convert_encoding("0", 'CP932', 'UTF-8');

                // 予備
                $str .= mb_convert_encoding(str_repeat(" ", 409), 'CP932', 'UTF-8');

                if (strlen($str) != SHUKA_SEND_BYTE) {
                    fclose($fp);
                    unlink($file);
                    throw new Exception("送信データのバイト数が正しくありません。\n" . strlen($str) . "バイトが作成されましたが、992バイトが必要です。");
                }

                //送信ファイルに書き込む
                fwrite($fp, $str);
                //メール送信ファイルに書き込む
                fwrite($tmpFP, $str);
            }

            //ファイルを閉じる
            fclose($fp);
            fclose($tmpFP);

            /**
             * SEND FILE
             */

            //SFTP 情報を設定
            $ftp_host = $ftp_info["host"];
            $ftp_ip = $ftp_info["server_ip"];
            $ftp_port = intval($ftp_info["port"]);
            $ftp_username = $ftp_info["account_id"];
            $ftp_userpass = $ftp_info["account_pwd"];
            $ftp_pasv = ($ftp_info["pasv_mode"] == '1') ? true : false;
            $ftp_file = $ftp_info["file_name"];

            if (empty($ftp_host)) throw new Exception("SFTPのホスト名はありません。");

            /**
             * SFTP
             */

            //SSH Connection
            //SSH 接続
            $ssh_conn = ssh2_connect($ftp_host, $ftp_port);

            if (!$ssh_conn) {
                unlink($file);
                throw new Exception("SFTPの接続に失敗しました。");
            }

            //SSH Login
            //SSH ログイン
            if (!ssh2_auth_password($ssh_conn, $ftp_username, $ftp_userpass)) {
                unlink($file);
                throw new Exception("SFTPのログインに失敗しました。");
            }

            //SFTP Connection
            //SFTP 接続
            $sftp_conn = ssh2_sftp($ssh_conn);

            if (!$sftp_conn) {
                unlink($file);
                throw new Exception("ファイルシステムにアクセスできません。");
            }

            //OPEN Remote file
            //SFTP サーバーでファイルを開く
            $sftp_stream = fopen("ssh2.sftp://$sftp_conn/$ftp_file", 'w');

            if (!$sftp_stream) {
                unlink($file);
                throw new Exception("リモートファイルが開けません。");
            }

            //Get data from local file
            //送信データを取得
            $send_data = file_get_contents($file);

            if (!$send_data) {
                unlink($file);
                throw new Exception("送信ファイルが開けません。");
            }

            //WRITE data to remote file
            //SFTP ファイルに書き込む
            if (fwrite($sftp_stream, $send_data) === false) {
                unlink($file);
                throw new Exception("SFTPの送信に失敗しました。");
            }

            //Close remote file
            //SFTP ファイルを閉じる
            fclose($sftp_stream);

            //Close sftp connection
            //SFTP 切断
            ssh2_disconnect($sftp_conn);

            //Close ssh server connection
            //SSH 切断
            ssh2_disconnect($ssh_conn);

            //UPDATE t_sale_h
            //売上ヘッダーを更新
            //送信済フラグ・送信日
            $shuka_dt = $_REQUEST["shuka_dt"] . " " . date("h:i:s");

            $sql = "UPDATE t_sale_h
                    SET send_flg = '1',
                    send_dt = :send_dt,
                    update_user_id = :user_id,
                    update_date = CURRENT_TIMESTAMP
                    WHERE sale_dt <= :sale_dt
                    AND send_flg = '0'
                    AND sale_kbn = '1'
                    AND delivery_kbn = '1';";
            // AND sale_kbn = '1'
            $params = array();
            $params["send_dt"] = $shuka_dt;
            $params["sale_dt"] = $_REQUEST["shuka_dt"];
            $params["user_id"] = $_SESSION["user_id"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            //INSERT LOG TABLE
            //送信ログに登録
            $sql = "INSERT INTO t_shuka_log(
                        shuka_dt
                        , kensu
                        , kosu
                        , send_dt
                        ) VALUES (
                        :shuka_dt
                        , :kensu
                        , :kosu
                        , CURRENT_TIMESTAMP);";
            $params = array();
            $params["shuka_dt"] = $shuka_dt;
            $params["kensu"] = $total_kensu;
            $params["kosu"] = $total_kosu;

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            //send mail
            //メールを送信
            $sql = "SELECT mail FROM m_mail;";
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $mail_address = $sth->fetchAll(PDO::FETCH_ASSOC);

            //メール情報を設定
            $mail_host = $ftp_info["mail_host"];
            $mail_port = $ftp_info["mail_port"];
            $mail_uname = $ftp_info["mail_user"];
            $mail_pwd = $ftp_info["mail_pwd"];
            //$mail_address = $ftp_info["mail_address"];
            $mail_from = $ftp_info["mail_from"];
            $title = "受注管理システム　出荷予定データ送信正常終了";
            $body = "出荷予定データ送信が正常に終了しました。";

            //メール送信
            if (!empty($mail_host) && !empty($mail_port) && !empty($mail_uname) && !empty($mail_pwd) && !empty($mail_from)) {
                SendMail($mail_host, $mail_from, $mail_uname, $mail_pwd, $mail_port, $mail_address, $title, $body, $tmp);
            } else {
                error_log("メールを送信するための情報はありません。");
            }

            //TEMP ファイルを削除
            unlink($tmp);

            $dbh->commit();
            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

            /** 出荷データ送信ログ **/
        case "shukaDataLog":
            session_start();
            $_SESSION['created'] = time();

            $sql = "SELECT 
                    shuka_dt
                    , send_dt
                    , kensu AS kensu
                    , kosu AS kosu
                    FROM t_shuka_log
                    ORDER BY send_dt DESC
                    LIMIT 10;";

            $sth = $dbh->prepare($sql);
            $sth->execute();

            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;

        case "downloadshukaDataLog":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["previous_dt"]) || $_REQUEST["previous_dt"] == "") throw new Exception("送信ログを選択してください。");

            //PRODUCT SQL
            //USE IN LOOP
            $product_sql = "SELECT 
                            s.product_cd
                            , s.product_nm_abrv
                            FROM t_sale_d d
                            INNER JOIN m_shohin s ON d.product_cd = s.product_cd
                            WHERE d.order_no = :order_no
                            LIMIT 10;";
            $product_sth = $dbh->prepare($product_sql);

            //受託データの取得
            //GET DATA
            //YAMATO DELIVERY TIME CODE
            $sql = "SELECT
                    h.order_no AS order_no
                    , TO_CHAR(h.sale_dt,'YYYYMMDD') AS sale_dt
                    , h.yamato_kbn AS yamato_kbn
                    , h.inquire_no AS inquire_no
                    , h.delivery_form_flg AS delivery_form_flg
                    , h.shuka_report_flg AS shuka_report_flg
                    , h.kosu AS kosu
                    , TO_CHAR(h.receive_dt,'YYYYMMDD') AS receive_dt
                    , h.grand_total AS grand_total
                    , t.delivery_instruct AS delivery_instruct
                    , o.okurisaki_cd AS okurisaki_cd
                    , o.okurisaki_nm AS okurisaki_nm
                    , o.okurisaki_zip AS okurisaki_zip
                    , CONCAT(o.okurisaki_adr_1,o.okurisaki_adr_2,o.okurisaki_adr_3) AS okurisaki_adr
                    , o.okurisaki_tel AS okurisaki_tel
                    , y.delivery_cd AS yamato_delivery_cd
                    FROM t_sale_h h
                    LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN m_okurisaki o ON h.okurisaki_cd = o.okurisaki_cd AND h.tokuisaki_cd = o.tokuisaki_cd
                    LEFT JOIN m_yamato y ON o.okurisaki_zip = y.key_part
                    LEFT JOIN t_sale_report r ON h.order_no = r.order_no
                    WHERE h.send_dt = :send_dt
                    AND send_flg = '1'
                    AND h.sale_kbn = '1'
                    AND r.label_flg = '1'
                    AND h.delivery_kbn = '1'
                    ORDER BY order_no;";
            //TO_CHAR(h.sale_dt,'YYYY/MM/DD') <= :sale_dt
            //AND h.sale_kbn = '1'
            $params = array();
            $params["send_dt"] = $_REQUEST["previous_dt"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("送信対象のデータが存在しません。");

            $total_kensu = count($list);
            $total_kosu = number_format(array_sum(array_column($list, "kosu")));

            //CREATE YAMATO DAT FILE
            $file = TEMP_FOLDER . YAMATO_FILE . uniqid(mt_rand(), true);

            $fp = fopen($file, 'w');

            //WRITE TO FILE
            foreach ($list as $row) {
                $str = "";

                // お客様管理番号
                $str .= mb_convert_encoding(StrBytePad(trim($row["order_no"]), 20, " "), 'CP932', 'UTF-8');

                // 問合せ番号・ヤマト伝票番号
                $str .= mb_convert_encoding(StrBytePad(trim($row["inquire_no"]), 12, " "), 'CP932', 'UTF-8');

                // 電話番号
                $str .= mb_convert_encoding(StrBytePad(trim($row["okurisaki_tel"]), 12, " "), 'CP932', 'UTF-8');

                // 送り先名
                $str .= mb_convert_encoding(StrBytePad(trim($row["okurisaki_nm"]), 30, " "), 'CP932', 'UTF-8');

                // 送り先郵便番号
                $str .= mb_convert_encoding(StrBytePad(trim($row["okurisaki_zip"]), 7, " "), 'CP932', 'UTF-8');

                // 送り先住所
                $str .= mb_convert_encoding(StrBytePad(trim($row["okurisaki_adr"]), 96, " "), 'CP932', 'UTF-8');

                // 届け先部門名称1
                $str .= mb_convert_encoding(str_repeat(" ", 50), 'CP932', 'UTF-8');

                // 届け先部門名称2
                $str .= mb_convert_encoding(str_repeat(" ", 50), 'CP932', 'UTF-8');

                // ウエダ食品電話番号
                $str .= mb_convert_encoding(StrBytePad(trim(str_replace("-", "", TEL)), 12, " "), 'CP932', 'UTF-8');

                // ウエダ食品名
                $str .= mb_convert_encoding(StrBytePad(trim(POST_COMPANY), 30, " "), 'CP932', 'UTF-8');

                // ウエダ食品住所
                $str .= mb_convert_encoding(StrBytePad(trim(POST_ADDRESS), 96, " "), 'CP932', 'UTF-8');

                // 顧客コード
                $str .= mb_convert_encoding("075561725901", 'CP932', 'UTF-8');

                // 顧客コード枝番
                $str .= mb_convert_encoding(str_repeat(" ", 3), 'CP932', 'UTF-8');

                // 備考
                $str .= mb_convert_encoding(str_repeat(" ", 3), 'CP932', 'UTF-8');

                // 仕分けコード
                $str .= mb_convert_encoding(StrBytePad(trim($row["yamato_delivery_cd"]), 7, " "), 'CP932', 'UTF-8');

                // 商品
                $param = array();
                $param["order_no"] = $row["order_no"];
                $product_sth->execute($param);
                $products = $product_sth->fetchAll(PDO::FETCH_ASSOC);

                if (count($products) <= 5) {
                    $disp_name = true;
                } else {
                    $disp_name = false;
                }

                $cd = "";
                $productNm = "";
                foreach ($products as $product) {
                    // 商品コード
                    $cd .= mb_convert_encoding(StrBytePad(trim($product["product_cd"]), 3, " "), 'CP932', 'UTF-8');

                    // 商品名
                    if ($disp_name) {
                        $productNm .= mb_convert_encoding(StrBytePad(trim($product["product_nm_abrv"]), 10, " "), 'CP932', 'UTF-8');
                    }
                }

                // 商品コード
                $str .= mb_convert_encoding(StrBytePad($cd, 30, " "), 'CP932', 'UTF-8');

                // 商品名
                $str .= str_pad($productNm, 50);

                // サイズ品目コード
                $str .= mb_convert_encoding("0401", 'CP932', 'UTF-8');

                // 配達指示
                $str .= mb_convert_encoding(StrBytePad(trim($row["delivery_instruct"]), 20, " "), 'CP932', 'UTF-8');

                // 品代金
                if (intval($row["grand_total"]) <= 0) {
                    $str .= mb_convert_encoding("0000000", 'CP932', 'UTF-8');
                } else {
                    $str .= mb_convert_encoding(StrBytePad(trim($row["grand_total"]), 7, " "), 'CP932', 'UTF-8');
                }

                // クール区分
                $str .= mb_convert_encoding(" ", 'CP932', 'UTF-8');

                // 送り先コード
                $str .= mb_convert_encoding(StrBytePad(trim($row["okurisaki_cd"]), 12, " "), 'CP932', 'UTF-8');

                // 出荷予定日
                $str .= mb_convert_encoding($row["sale_dt"], 'CP932', 'UTF-8');

                // 配達指定日 (receive_dt)
                $str .= mb_convert_encoding($row["receive_dt"], 'CP932', 'UTF-8');

                // 配達時間帯
                $str .= mb_convert_encoding(StrBytePad(trim($row["yamato_kbn"]), 2, " "), 'CP932', 'UTF-8');

                // OMS
                $str .= mb_convert_encoding("0", 'CP932', 'UTF-8');

                // 予備
                $str .= mb_convert_encoding(str_repeat(" ", 409), 'CP932', 'UTF-8');

                if (strlen($str) != 992) {
                    fclose($fp);
                    unlink($file);
                    throw new Exception("送信データのバイト数が正しくありません。\n" . strlen($str) . "バイトが作成されましたが、992バイトが必要です。");
                }

                fwrite($fp, $str);
            }

            //SEND LOG TO USER
            header('Content-Type: text/bat', false);
            header("Content-Disposition: attachment; filename=YOTEI100");
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);

            break;

            /** 請求書 **/
        case "invoicePdf":
            session_start();
            $_SESSION['created'] = time();

            if ($_REQUEST["bill_dt"] == "") throw new Exception("締日を入力してください。");

            //1) GET data

            //BANK DATA
            $sql = "SELECT
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'bank.info' AND kanri_nm = 'account_1') AS account_1,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'bank.info' AND kanri_nm = 'account_2') AS account_2,
                        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'bank.info' AND kanri_nm = 'account_3') AS account_3;";
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $bank_info = $sth->fetch(PDO::FETCH_ASSOC);

            if (count($bank_info) == 0) throw new Exception("振込先がありません。");

            //DATA
            $sql = "SELECT 
                        h.order_no AS order_no
                        , t.tokuisaki_cd as tokuisaki_cd
                        , t.tokuisaki_nm AS tokuisaki_nm
                        , h.tax_8 AS tax_8
                        , h.tax_10 AS tax_10
                        , h.total_cost AS total_cost
                        , h.grand_total AS grand_total
                        , TO_CHAR(h.sale_dt, 'MM/DD') AS sale_dt
                        , TO_CHAR(h.receive_dt, 'MM/DD') AS receive_dt
                        , TO_CHAR(h.receive_dt, 'YYYY/MM/DD') AS nohin_dt
                        , s.product_nm AS product_nm
                        , d.qty AS qty
                        , s.sale_price AS sale_price
                        , d.total_cost AS row_cost
                        , s.tax_kbn AS tax_kbn
                        , TO_CHAR(h.sale_dt, 'YYYY/MM') AS seikyu_dt
                        , t.bill_dt AS bill_dt
                    FROM t_sale_h h
                    LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN t_sale_d d ON h.order_no = d.order_no
                    LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                    WHERE t.bill_dt = :bill_dt";

            $params = array();
            $params["bill_dt"] = $_REQUEST["bill_dt"];

            if ($_REQUEST["dt_from"] != "") {
                $params["dt_from"] = $_REQUEST["dt_from"];
                $sql .= " AND h.sale_dt >= :dt_from";
            };
            if ($_REQUEST["dt_to"] != "") {
                $params["dt_to"] = $_REQUEST["dt_to"];
                $sql .= " AND h.sale_dt <= :dt_to";
            };
            if ($_REQUEST["tokuisaki_tel"] != "") {
                $params["tokuisaki_tel"] = $_REQUEST["tokuisaki_tel"];
                //$sql .= " AND tel.tel_no = :tokuisaki_tel";
                $sql .= " AND t.tokuisaki_cd IN (SELECT tel.tokuisaki_cd FROM m_tokuisaki_tel tel WHERE tel.tel_no = :tokuisaki_tel)";
            };

            //$sql .= " ORDER BY receive_dt, h.order_no, t.tokuisaki_cd, s.product_cd";
            $sql .= " ORDER BY t.tokuisaki_cd, receive_dt, s.product_cd";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($data) == 0) throw new Exception("請求書の対象となるデータがありません。");

            //2) create pdf
            $fname = TEMP_FOLDER . "invoice_" . uniqid(mt_rand(), true) . PDF;
            invoice($fname, $data, $bank_info);

            //3) send pdf blob
            header('Content-Type: application/pdf', false);
            header("Content-Disposition: attachment; filename=invoice.pdf");
            header('Content-Length: ' . filesize($fname));
            readfile($fname);
            unlink($fname);
            break;

            /** 売掛金元帳 **/
        case "accountsRecievablePdf":
            session_start();
            $_SESSION['created'] = time();

            if ($_REQUEST["bill_dt"] == "") throw new Exception("締日を入力してください。");
            if ($_REQUEST["dt_from"] == "") throw new Exception("期間[開始]を入力してください。");
            if ($_REQUEST["dt_to"] == "") throw new Exception("期間[終了]を入力してください。");

            //1) GET data
            $sql = "SELECT 
                        t.tokuisaki_cd AS tokuisaki_cd
                        , t.tokuisaki_nm AS tokuisaki_nm
                        , h.tax_8 AS tax_8
                        , h.tax_10 AS tax_10
                        , h.total_cost AS total_cost
                        , h.grand_total AS grand_total
                        , TO_CHAR(h.sale_dt, 'MM/DD') AS sale_dt
                        , TO_CHAR(h.receive_dt, 'MM/DD') AS receive_dt
                        , TO_CHAR(h.receive_dt, 'YYYY/MM/DD') AS nohin_dt
                        , s.product_nm AS product_nm
                        , d.qty AS qty
                        , s.sale_price AS sale_price
                        , d.total_cost AS row_cost
                    FROM t_sale_h h
                    LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN t_sale_d d ON h.order_no = d.order_no
                    LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                    WHERE t.bill_dt = :bill_dt
                    AND h.sale_dt >= :dt_from
                    AND h.sale_dt <= :dt_to";

            $params = array();
            $params["bill_dt"] = $_REQUEST["bill_dt"];
            $params["dt_from"] = $_REQUEST["dt_from"];
            $params["dt_to"] = $_REQUEST["dt_to"];

            if ($_REQUEST["tokuisaki_tel"] != "") {
                $params["tokuisaki_tel"] = $_REQUEST["tokuisaki_tel"];
                $sql .= " AND t.tokuisaki_tel = :tokuisaki_tel";
            };

            $sql .= " ORDER BY t.tokuisaki_cd, h.receive_dt, s.product_cd";

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($data) == 0) throw new Exception("売掛金元帳の対象となるデータがありません。");
            //2) create pdf
            $fname = TEMP_FOLDER . "urikake_" . uniqid(mt_rand(), true) . PDF;
            urikake($fname, $data, $_REQUEST["dt_from"], $_REQUEST["dt_to"]);

            //3) send pdf blob
            header('Content-Type: application/pdf', false);
            header("Content-Disposition: attachment; filename=urikake.pdf");
            header('Content-Length: ' . filesize($fname));
            readfile($fname);
            unlink($fname);
            break;

            /** CANCEL SALE TRANSACTION **/
        case "salesCancel":
            session_start();
            $_SESSION['created'] = time();
            $_SESSION['transaction_state'] = false;
            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;

        case "getInquireNo":
            if ($_REQUEST["order_no"] == "") throw new Exception("受注番号を指定してください。");
            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];
            $sql = "SELECT inquire_no FROM t_sale_h WHERE order_no = :order_no;";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $inquire_no = $sth->fetchColumn();
            echo json_encode($inquire_no, JSON_UNESCAPED_UNICODE);
            break;

        case "kenpinList":
            // session_start();
            // $_SESSION['created'] = time();
            // $start = microtime(true);
            // $result = array();
            $offset = $_REQUEST["offset"] ?? 0;

            $sql = "SELECT
            h.inquire_no AS inquire_no
            , h.order_no AS order_no
            , o.okurisaki_nm AS okurisaki_nm
            , o.okurisaki_adr_1
            , o.okurisaki_adr_2
            , o.okurisaki_adr_3
            , o.okurisaki_tel AS okurisaki_tel
            , h.kosu AS kosu
            , c1.kanri_nm AS kenpin_kbn
            , h.shuka_print_qty AS shuka_print_qty
            FROM t_sale_h h
            LEFT JOIN m_okurisaki o ON h.tokuisaki_cd = o.tokuisaki_cd AND h.okurisaki_cd = o.okurisaki_cd
            LEFT JOIN m_code c1 ON h.kenpin_kbn = c1.kanri_cd AND c1.kanri_key = 'kenpin.kbn'
            WHERE h.sale_dt = :shuka_dt";

            //$whr = " WHERE h.sale_dt = :shuka_dt";
            $cntWhr = " WHERE h.sale_dt = :shuka_dt AND h.kenpin_kbn = :kenpin_kbn";

            $params = array();
            $params["shuka_dt"] = $_REQUEST["shuka_dt"];

            if (isset($_REQUEST["shuka_print_qty"]) && $_REQUEST["shuka_print_qty"] != "all") {
                $params["shuka_print_qty"] = $_REQUEST["shuka_print_qty"];
                $sql .= " AND h.shuka_print_qty = :shuka_print_qty";
                $cntWhr .= " AND h.shuka_print_qty = :shuka_print_qty";
            }
            if (isset($_REQUEST["kenpin_kbn"]) && $_REQUEST["kenpin_kbn"] != "all") {
                $params["kenpin_kbn"] = $_REQUEST["kenpin_kbn"];
                $sql .= " AND h.kenpin_kbn = :kenpin_kbn";
            }
            if (isset($_REQUEST["sale_kbn"]) && $_REQUEST["sale_kbn"] != "all") {
                $params["sale_kbn"] = $_REQUEST["sale_kbn"];
                $sql .= " AND h.sale_kbn = :sale_kbn";
                $cntWhr .= " AND h.sale_kbn = :sale_kbn";
            }
            if (isset($_REQUEST["delivery_kbn"]) && $_REQUEST["delivery_kbn"] != "all") {
                $params["delivery_kbn"] = $_REQUEST["delivery_kbn"];
                $sql .= " AND h.delivery_kbn = :delivery_kbn";
                $cntWhr .= " AND h.delivery_kbn = :delivery_kbn";
            }

            $sql .= " ORDER BY order_no DESC LIMIT " . LIST_CNT . " OFFSET $offset";
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($data) == 0) throw new Exception("対象となる検品データはありません。");

            $result["rows"] = $data;

            //検品済件数
            // $sql = "SELECT COUNT(*) FROM t_sale_h" . $cntWhr;
            // $params["kenpin_kbn"] = '1';
            // $sth = $dbh->prepare($sql);
            // $sth->execute($params);
            // $data = $sth->fetchColumn();

            //$result["complete_qty"] = $data ?? 0;
            $result["complete_qty"] = 0;

            //検品済個数
            // $sql = "SELECT SUM(kosu) FROM t_sale_h" . $cntWhr;
            // $sth = $dbh->prepare($sql);
            // $sth->execute($params);
            // $data = $sth->fetchColumn();

            //$result["complete_kosu"] = $data ?? 0;
            $result["complete_kosu"] = 0;

            //未検品件数
            // $sql = "SELECT COUNT(*) FROM t_sale_h" . $cntWhr;
            // $params["kenpin_kbn"] = '0';
            // $sth = $dbh->prepare($sql);
            // $sth->execute($params);
            // $data = $sth->fetchColumn();

            //$result["kenpin_qty"] = $data ?? 0;
            $result["kenpin_qty"] = 0;

            //未検品個数
            // $sql = "SELECT SUM(kosu) FROM t_sale_h" . $cntWhr;
            // $sth = $dbh->prepare($sql);
            // $sth->execute($params);
            // $data = $sth->fetchColumn();

            //$result["kenpin_kosu"] = $data ?? 0;
            $result["kenpin_kosu"] = 0;

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            // $end = microtime(true);
            // $executionTime = $end - $start;
            // timeLog("Script time : [$executionTime]");
            break;

        case "kenpinCount":
            $result = array();
            $cntWhr = " WHERE sale_dt = :shuka_dt AND kenpin_kbn = :kenpin_kbn";

            $params = array();
            $params["shuka_dt"] = $_REQUEST["shuka_dt"];

            // if (isset($_REQUEST["shuka_print_qty"]) && $_REQUEST["shuka_print_qty"] != "all") {
            //     $params["shuka_print_qty"] = $_REQUEST["shuka_print_qty"];
            //     $cntWhr .= " AND shuka_print_qty = :shuka_print_qty";
            // }
            // if (isset($_REQUEST["sale_kbn"]) && $_REQUEST["sale_kbn"] != "all") {
            //     $params["sale_kbn"] = $_REQUEST["sale_kbn"];
            //     $cntWhr .= " AND sale_kbn = :sale_kbn";
            // }
            // if (isset($_REQUEST["delivery_kbn"]) && $_REQUEST["delivery_kbn"] != "all") {
            //     $params["delivery_kbn"] = $_REQUEST["delivery_kbn"];
            //     $cntWhr .= " AND delivery_kbn = :delivery_kbn";
            // }

            //検品済件数
            $sql = "SELECT COUNT(*) FROM t_sale_h" . $cntWhr;
            $params["kenpin_kbn"] = '1';
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchColumn();

            $result["complete_qty"] = $data ?? 0;

            //検品済個数
            $sql = "SELECT SUM(kosu) FROM t_sale_h" . $cntWhr;
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchColumn();

            $result["complete_kosu"] = $data ?? 0;

            //未検品件数
            $sql = "SELECT COUNT(*) FROM t_sale_h" . $cntWhr;
            $params["kenpin_kbn"] = '0';
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchColumn();

            $result["kenpin_qty"] = $data ?? 0;

            //未検品個数
            $sql = "SELECT SUM(kosu) FROM t_sale_h" . $cntWhr;
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchColumn();

            $result["kenpin_kosu"] = $data ?? 0;

            echo json_encode($result, JSON_UNESCAPED_UNICODE);

            break;

        case "kenpinDetail":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["inquire_no"]) || $_REQUEST["inquire_no"] == "") throw new Exception("問合せ番号を入力してください。");
            if (!isset($_REQUEST["shuka_dt"]) || $_REQUEST["shuka_dt"] == "") throw new Exception("出荷日を入力してください。");

            $sql = "SELECT
                    h.order_no AS order_no
                    , TO_CHAR(h.sale_dt, 'YYYY/MM/DD') AS sale_dt
                    , h.kosu AS kosu
                    , c1.kanri_nm AS delivery_kbn
                    , c2.kanri_nm AS sale_kbn
                    , o.okurisaki_nm AS okurisaki_nm
                    , o.okurisaki_tel AS okurisaki_tel
                    , CONCAT(o.okurisaki_adr_1, o.okurisaki_adr_2, o.okurisaki_adr_3) AS address
                    FROM t_sale_h h 
                    LEFT JOIN m_okurisaki o ON h.tokuisaki_cd = o.tokuisaki_cd AND h.okurisaki_cd = o.okurisaki_cd
                    LEFT JOIN m_code c1 ON h.delivery_kbn = c1.kanri_cd AND c1.kanri_key = 'delivery.kbn'
                    LEFT JOIN m_code c2 ON h.sale_kbn = c2.kanri_cd AND c2.kanri_key = 'sale.kbn'
                    WHERE h.inquire_no = :inquire_no AND h.sale_dt = :shuka_dt
                    ORDER BY h.sale_dt, h.order_no;";

            $params = array();
            $params["inquire_no"] = $_REQUEST["inquire_no"];
            $params["shuka_dt"] = $_REQUEST["shuka_dt"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetch(PDO::FETCH_ASSOC);

            if (empty($data)) throw new Exception("対象となるデータはありません。");

            echo json_encode($data, JSON_UNESCAPED_UNICODE);

            break;

        case "kenpinUpdate":
            session_start();
            $_SESSION['created'] = time();

            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号を指定してください。");
            $order = $_REQUEST["order_no"];
            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];

            $sql = "SELECT COUNT(*)
                    FROM t_sale_h
                    WHERE order_no = :order_no
                    AND kenpin_kbn = '1';";

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchColumn();

            if ($data != 0) throw new Exception("受注番号[$order]は既に検品済です。");

            $sql = "UPDATE t_sale_h 
                    SET kenpin_kbn = '1'
                    WHERE order_no = :order_no
                    AND kenpin_kbn = '0';";

            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $dbh->commit();

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);

            break;

        case "kenpinShukaCount":
            if (!isset($_REQUEST["shuka_dt"]) || $_REQUEST["shuka_dt"] == "") throw new Exception("出荷日を入力してください。");

            $sql = "SELECT shuka_print_qty AS cnt
                    FROM t_sale_h
                    WHERE sale_dt = :shuka_dt
                    GROUP BY shuka_print_qty
                    ORDER BY shuka_print_qty ASC;";

            $params = array();
            $params["shuka_dt"] = $_REQUEST["shuka_dt"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (empty($data)) throw new Exception("締め回数がありません。");

            echo json_encode($data, JSON_UNESCAPED_UNICODE);

            break;

            /** 商品コード空き番 **/
        case "productCodeList":
            // $order = $_REQUEST["order"] ?? "ASC";
            $sql = "SELECT product_cd 
                    FROM m_product_cd
                    WHERE in_use = TRUE
                    ORDER BY product_cd;";

            $sth = $dbh->prepare($sql);
            $sth->execute();
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (empty($data)) throw new Exception("使用できる商品コードはありません。");

            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            break;

        case "getTelList":
            if (!isset($_REQUEST["tokuisaki_cd"]) || $_REQUEST["tokuisaki_cd"] == "") throw new Exception("得意先コードは指定されていません。");

            $sql = "SELECT tokuisaki_cd, tel_no 
                    FROM m_tokuisaki_tel
                    WHERE tokuisaki_cd = :tokuisaki_cd;";

            $params = array();
            $params["tokuisaki_cd"] = $_REQUEST["tokuisaki_cd"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($data) == 0) throw new Exception("対象となるデータはありません。");

            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            break;

        case "kenpinCheck":
            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号を指定してください。");

            $sql = "SELECT * FROM t_sale_h WHERE order_no = :order_no;";
            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);

            $order = $sth->fetch(PDO::FETCH_ASSOC);

            if (empty($order)) throw new Exception("対象となる受注がありません。");

            //CHECK kosu
            if ($order["kosu"] < 1) {
                echo json_encode($order["kosu"] . "個数です。", JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode("OK", JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {

    if ($dbh != null) {
        $dbh->rollBack();
    }
    echo json_encode(array("error" => $e->getMessage()), JSON_UNESCAPED_UNICODE);
}

/**
 * Create Error download file
 * @param String $file The name of file
 * @param Array $data The data being written to the file
 */
function Create_Error_File($file, $data)
{
    $fp = fopen($file, 'w');

    foreach ($data as $row) {
        fputcsv($fp, array($row));
    };

    fclose($fp);
}

function timeLog($data)
{
    // $fp = fopen(TEMP_FOLDER . "sql_log", 'w');

    file_put_contents(TEMP_FOLDER . "sql_log", $data . PHP_EOL, FILE_APPEND);

    //fclose($fp);
}

/**
 * Pad a string to specified byte amount
 * @param String $strString The string being padded
 * @param Int $lLength The length of the string in Bytes
 * @param String $strPaddingChar The string used to pad
 * @return String Padded string to specified byte length
 */
function StrBytePad(string $strString, int $lLength, string $strPaddingChar = " ")
{
    $lSumBytes = 0;
    $lStrStart = 0;
    $strChar = "";
    $lCharByte = 0;
    $lPadLength = 0;
    $strPadString = "";

    $length = mb_strlen($strString);
    for ($lStrStart = 0; $lStrStart < $length; $lStrStart++) {
        $strChar = mb_substr($strString, $lStrStart, 1);
        $lCharByte = strlen(mb_convert_encoding($strChar, 'CP932', 'UTF-8'));
        if ($lLength < $lSumBytes + $lCharByte) {
            $strString = mb_substr($strString, 0, $lStrStart);
            break;
        }
        $lSumBytes += $lCharByte;
    }

    $lPadLength = mb_strlen(mb_convert_encoding($strPaddingChar, 'CP932', 'UTF-8'));
    while ($lSumBytes + $lPadLength <= $lLength) {
        $strPadString .= $strPaddingChar;
        $lSumBytes += $lPadLength;
    }

    return $strString . $strPadString;
}

/**
 * Create last digit for Yamato inquire number
 * @param String $strString Inquire number
 * @return Int Last digit for Yamato inquire_no
 */
function Create7DRCheckDigit($strString)
{
    $lCheckDigit = 0;
    for ($i = 0; $i < strlen($strString); $i++) {
        $iAsc = ord(substr($strString, $i, 1));
        if ($iAsc < ord('0') || ord('9') < $iAsc) {
            return -1;
        }
        $lCheckDigit = ($lCheckDigit * 10 + $iAsc - ord('0')) % 7;
    }
    return $lCheckDigit;
}
