<?php

//このファイルはメモ帳で編集してはいけない。文字コードがおかしくなり、ダウンロードファイルが文字化けする

// DB接続文字列
define("DB_CON_STR", "pgsql:dbname=public host=localhost port=5432");

// データベース
define("DB_NAME", ";Database=public");

// DBユーザー名
define("DB_USER", "user");

// DBパスワード
define("DB_PASS", "password");

// 一覧表示件数
define("LIST_CNT", "500");
define("MESAI_HISTORY_LIST_CNT", "50");
define("SALE_HISTORY_LIST_CNT", "50");

//ダウンロード先
define("TEMP_FOLDER", "/temp/WEB/temp/");
define("YAMATO_FOLDER", "/temp/WEB/yamato/shuka/");
define("YAMATO_PENDING_FOLDER", "/temp/WEB/yamato/pending");
define("YAMATO_PENDING_TIME_FOLDER", "/temp/WEB/yamato/pending_start");
define("YAMATO_LOG_PATH", "/temp/WEB/yamato/log/");

//ERROR FOLDER
define("ZIP_UPLOAD_ERROR_FILE", "zip_upload_error.csv");
define("YAMATO_UPLOAD_ERROR_FILE", "yamato_upload_error.csv");
define("YAMATO_UPLOAD_ERROR_LOG", "yamato_upload_error.log");

//CSV
define("CSV", ".csv");

//PDF
define("PDF", ".pdf");

//ヤマトファイル名
define("YAMATO_FILE", "YOTEI100");
define("YAMATO_PENDING_TIME_FILE", "yamato_upload_start.txt");
define("YAMATO_UPLOAD_FILE", "YMSTPOST.DAT");

//株式会社〇〇〇〇の情報
define("COMPANY", "株式会社〇〇〇〇");
define("POST_COMPANY", "株式会社　〇〇〇〇");
define("ZIP", "000-0000");
define("ADDRESS", "京都市");
define("POST_ADDRESS", "京都市");
define("TEL", "000-000-0000");
define("FAX", "000-000-0000");
define("NINUSHI_CODE", "1234567890");
define("INQUIRE_TEL", "000-000-0000");

//登録番号
define("REGISTER_NO", "T1234567890");

/**
 * 帳票ID
 */

//領収書
define("RECEIPT_REPORT_ID", "0001");
//出荷依頼書
define("SHUKA_IRAI_REPORT_ID", "0002");
//売上伝票
define("DENPYO_REPORT_ID", "0003");
//納品書・注文書
define("ORDER_REPORT_ID", "0004");
//ヤマト代引
define("YAMATO_DAIBIKI_REPORT_ID", "0005");
//ヤマト元払い
define("YAMATO_MOTO_BARAI_REPORT_ID", "0006");

//ログ
// ログファイル出力フラグ true=出力あり/false=なし
define("LOG_OUT", true);
// ログレベル 0=ERROR/1=WARN/2=INFO/3=DEBUG
define("LOG_LEVEL", "3");
// ログファイル出力ディレクトリ
define("LOG_PATH", "/var/www/log/");
// ログファイル名
define("LOG_FILE", "console.log");
// ログファイル最大サイズ（Byte）
define("LOG_MAXSIZE", "10485760");
// ログ保存期間（日）
define("LOG_PERIOD", "30");

/** 取込の可能なCSVファイルタイプ **/
define("CSV_MIMES", array(
    'text/x-comma-separated-values',
    'text/comma-separated-values',
    'application/x-csv',
    'text/x-csv',
    'text/csv',
    'application/csv',
));

//YAMATO 使用期限
define("YAMATO_EXPIRE_DATE", '+25 days');

//軽減税率対象のマーク
define("TAX_MARK", "※");

//軽減税率
// 1 => 軽減税率：8%
// 2 => 軽減税率：10%
// 3 => 非課税
define("REDUCE_TAX_RATE", array('1'));

//Shuka send wait time
//in seconds
// 連続でファイルを送信する場合は、15分以上間隔を開けて下さい。
define('SHUKA_SEND_WAIT_TIME', 900);

//YAMATO LZH file upload try times
define("YAMATO_UPLOAD_RETRY", 3);

//出荷送信バイト数
define("SHUKA_SEND_BYTE", 992);
