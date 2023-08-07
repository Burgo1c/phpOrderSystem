<?php

//このファイルはメモ帳で編集してはいけない。文字コードがおかしくなり、ダウンロードファイルが文字化けする

// DB接続文字列
define("DB_CON_STR", "pgsql:dbname=uedaorder host=localhost port=5432");

// データベース
define("DB_NAME", ";Database=uedaorder");

// DBユーザー名
define("DB_USER", "ueda");

// DBパスワード
define("DB_PASS", "UedaUskk");

// 一覧表示件数
define("LIST_CNT", "500");
define("MESAI_HISTORY_LIST_CNT", "50");
define("SALE_HISTORY_LIST_CNT", "50");

//ダウンロード先
define("TEMP_FOLDER", "/ADD_DISK/WEB/temp/");
define("YAMATO_FOLDER", "/ADD_DISK/WEB/yamato/shuka/");
define("YAMATO_PENDING_FOLDER", "/ADD_DISK/WEB/yamato/pending");
define("YAMATO_PENDING_TIME_FOLDER", "/ADD_DISK/WEB/yamato/pending_start");
define("YAMATO_LOG_PATH", "/ADD_DISK/WEB/yamato/log/");

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

//株式会社ウエダ食品の情報
define("COMPANY", "株式会社ウエダ食品");
define("POST_COMPANY", "株式会社　ウエダ食品");
define("ZIP", "605-0851");
define("ADDRESS", "京都市東山区東大路松原上ル２丁目玉水７３");
define("POST_ADDRESS", "京都市東山区玉水町73");
define("TEL", "075-561-7259");
define("FAX", "075-532-2273");
define("NINUSHI_CODE", "156172560006");
define("INQUIRE_TEL", "075-604-2255");

//登録番号
define("REGISTER_NO", "T7130001009038");

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
define("LOG_PATH", "/var/www/uskk-order.com/log/");
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