<?php
require_once("config.php");
require_once('phpmail.php');

$dbh = null;
$sth = null;
$tmp = null;
$file = null;
$mail_host = "";
$mail_port = "";
$mail_uname = "";
$mail_pwd = "";
$mail_from = "";
$ftp_host = "";
$ftp_ip = "";
$ftp_port = null;
$ftp_username = "";
$ftp_userpass = "";
$ftp_pasv = "";
$ftp_file = "";
$mail_address = null;
$ftp_info = null;
$dbh = new PDO(DB_CON_STR, DB_USER, DB_PASS);
$dbh->beginTransaction();

//GET FTP & MAIL INFO
try {
    $sql = "SELECT mail FROM m_mail;";
    $sth = $dbh->prepare($sql);
    $sth->execute();
    $mail_address = $sth->fetchAll(PDO::FETCH_ASSOC);

    if (count($mail_address) == 0) throw new Exception("送信宛先がありません。");

    //GET FTP INFO
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

    //MAIL INFO
    $mail_host = $ftp_info["mail_host"];
    $mail_port = $ftp_info["mail_port"];
    $mail_uname = $ftp_info["mail_user"];
    $mail_pwd = $ftp_info["mail_pwd"];
    $mail_from = $ftp_info["mail_from"];

    //SFTP INFO
    $ftp_host = $ftp_info["host"];
    $ftp_ip = $ftp_info["server_ip"];
    $ftp_port = intval($ftp_info["port"]);
    $ftp_username = $ftp_info["account_id"];
    $ftp_userpass = $ftp_info["account_pwd"];
    $ftp_pasv = ($ftp_info["pasv_mode"] == '1') ? true : false;
    $ftp_file = $ftp_info["file_name"];
} catch (Exception $e) {
    if ($dbh != null) {
        $dbh->rollBack();
    }

    error_log($e->getMessage());
    exit;
}

// CREATE & SEND DATA VIA SFTP
try {
    if (empty($ftp_host)) throw new Exception("SFTPのホスト名はありません。");
    if (empty($ftp_username)) throw new Exception("SFTPのログイン情報はありません。");
    if (empty($ftp_userpass)) throw new Exception("SFTPのログイン情報はありません。");

    $shuka_dt = date('Y-m-d');

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
                    WHERE sale_dt <= :sale_dt
                    AND send_flg = '0'
                    AND h.sale_kbn = '1'
                    AND r.label_flg = '1'
                    AND h.delivery_kbn = '1'
                    ORDER BY order_no;";
    //AND h.sale_kbn = '1'
    $params = array();
    $params["sale_dt"] = $shuka_dt;

    $sth = $dbh->prepare($sql);
    $sth->execute($params);
    $list = $sth->fetchAll(PDO::FETCH_ASSOC);

    if (count($list) == 0) throw new Exception("送信対象のデータが存在しません。");

    $total_kensu = count($list);
    $total_kosu = number_format(array_sum(array_column($list, "kosu")));

    //CREATE YAMATO DAT FILE
    $file = YAMATO_FOLDER . YAMATO_FILE . "_" .  date('YmdHis');
    $tmp = TEMP_FOLDER . YAMATO_FILE . "_" .  date('YmdHis') . ".txt";
    $fp = fopen($file, 'w');
    $tmpFP = fopen($tmp, 'w');

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
        fwrite($tmpFP, $str);
    }

    fclose($fp);
    fclose($tmpFP);

    /**
     * SEND FILE
     * SFTP
     */

    //SSH Connection
    $ssh_conn = ssh2_connect($ftp_host, $ftp_port);

    if (!$ssh_conn) {
        unlink($file);
        throw new Exception("SFTPの接続に失敗しました。");
    }

    //SSH Login
    if (!ssh2_auth_password($ssh_conn, $ftp_username, $ftp_userpass)) {
        unlink($file);
        throw new Exception("SFTPのログインに失敗しました。");
    }

    //SFTP Connection
    $sftp_conn = ssh2_sftp($ssh_conn);

    if (!$sftp_conn) {
        unlink($file);
        throw new Exception("ファイルシステムにアクセスできません。");
    }

    //OPEN Remote file
    $sftp_stream = fopen("ssh2.sftp://$sftp_conn/$ftp_file", 'w');

    if (!$sftp_stream) {
        unlink($file);
        throw new Exception("リモートファイルが開けません。");
    }

    //Get data from local file
    $send_data = file_get_contents($file);

    if (!$send_data) {
        unlink($file);
        throw new Exception("送信ファイルが開けません。");
    }

    //WRITE data to remote file
    if (fwrite($sftp_stream, $send_data) === false) {
        unlink($file);
        throw new Exception("SFTPの送信に失敗しました。");
    }

    //Close remote file
    fclose($sftp_stream);

    //Close sftp connection
    ssh2_disconnect($sftp_conn);

    //Close ssh server connection
    ssh2_disconnect($ssh_conn);

    //UPDATE t_sale_h
    $send_dt = $shuka_dt . " " . date("h:i:s");

    $sql = "UPDATE t_sale_h
                    SET send_flg = '1',
                    send_dt = :send_dt,
                    update_user_id = :user_id,
                    update_date = CURRENT_TIMESTAMP
                    WHERE sale_dt <= :sale_dt
                    AND send_flg = '0'
                    AND delivery_kbn = '1';";
    $params = array();
    $params["send_dt"] = $send_dt;
    $params["sale_dt"] = $shuka_dt;
    $params["user_id"] = "ueda";

    $sth = $dbh->prepare($sql);
    $sth->execute($params);

    //INSERT LOG TABLE
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
    $params["shuka_dt"] = $send_dt;
    $params["kensu"] = $total_kensu;
    $params["kosu"] = $total_kosu;

    $sth = $dbh->prepare($sql);
    $sth->execute($params);

    //send mail
    $title = "受注管理システム　出荷予定データ送信正常終了";
    $body = "出荷予定データ送信が正常に終了しました。";

    if (!empty($mail_host) && !empty($mail_port) && !empty($mail_uname) && !empty($mail_pwd) && !empty($mail_from)) {
        SendMail($mail_host, $mail_from, $mail_uname, $mail_pwd, $mail_port, $mail_address, $title, $body, $tmp);
    } else {
        error_log("メールを送信するための情報はありません。");
    }

    unlink($tmp);

    $dbh->commit();
} catch (Exception $e) {

    if ($dbh != null) {
        $dbh->rollBack();
    }

    //log
    error_log($e->getMessage());

    //send mail
    $title = "受注管理システム　出荷予定データ送信異常終了";
    $body = "出荷予定データ送信が異常に終了しました。" . PHP_EOL . $e->getMessage();

    if (!empty($mail_host) && !empty($mail_port) && !empty($mail_uname) && !empty($mail_pwd) && !empty($mail_from)) {
        SendMail($mail_host, $mail_from, $mail_uname, $mail_pwd, $mail_port, $mail_address, $title, $body, $tmp);
    };

    //delete temp file
    if (!empty($tmp)) {
        unlink($tmp);
    };

    //delete send file
    if (!empty($file)) {
        unlink($file);
    };
} finally {
    exit;
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
