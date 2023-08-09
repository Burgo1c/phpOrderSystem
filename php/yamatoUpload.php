<?php
ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
ini_set('max_execution_time', 0);

require_once("config.php");

try {

    //VARIABLES
    $timeFile = YAMATO_PENDING_FOLDER . "/" . YAMATO_PENDING_TIME_FILE;
    $currentDate = date("Y-m-d");
    $contents = array();
    $deleteFiles = array();
    $data_ary = array();
    $err_ary = array();
    $err_cnt = 0;
    $line = 0;
    $fileToExtract = strtolower(YAMATO_UPLOAD_FILE); // Convert the file name to lowercase
    $extracted = false;
    $dbh = null;
    $sth = null;

    //DB CONNECTION
    $dbh = new PDO(DB_CON_STR, DB_USER, DB_PASS);
    $dbh->beginTransaction();

    //GET LATEST UPDATE DATE
    $sql = "SELECT COALESCE(MAX(create_dt),'0') AS create_dt FROM m_yamato";
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
    , :create_dt )";
    $insert_sth = $dbh->prepare($sql);

    //If file does not exist exit
    if (!file_exists($timeFile)) exit;

    //Get upload file and upload time from file
    $info = file_get_contents($timeFile);
    $info = explode(PHP_EOL, $info);
    $startDatetime = date("Y-m-d", strtotime($info[0]));
    $filepath = $info[1];
    $type = pathinfo($filepath, PATHINFO_EXTENSION);

    //If date already passed
    if ($startDatetime < $currentDate) {
        unlink($timeFile);
        unlink($filepath);
        throw new Exception("[エラー：開始日時は既に過ぎています。] [開始日時：$startDatetime]");
    };

    //If not update time
    if ($startDatetime !== $currentDate) exit;

    //If not LZH file
    if ($type !== "lzh") throw new Exception("[エラー：LZH アーカイブではありません。]");

    // Open the LZH archive
    $archive = rar_open($filepath);

    if ($archive === false) throw new Exception("[エラー：LZH アーカイブが開けませんでした。]");

    // Get the entries (files) in the archive
    //$entries = rar_list($archive);

    // Iterate over the entries to find the file to extract
    foreach ($entries as $entry) {
        // Get the entry name and convert it to lowercase or uppercase
        $entryName = strtolower($entry->getName()); // or strtoupper($entry->getName()) for uppercase

        // Check if it matches the file you want to extract
        if ($entryName === $fileToExtract) {
            //SET FILE PATH
            $uploadFile = TEMP_FOLDER . $entry->getName();

            // Extract the file
            $entry->extract(TEMP_FOLDER); // Specify the destination directory here
            $extracted = true;
            break;
        }
    }

    // Close the LZH archive
    rar_close($archive);

    //If no file extracted
    if (!$extracted) throw new Exception("[エラー：LZH アーカイブ内に必要なファイルが見つかりませんでした。]");

    //GET LATEST UPDATE DATE
    $get_sth->execute();
    $dt = $get_sth->fetchColumn();

    //Open file
    $fp = fopen($uploadFile, "r");

    //Get first row
    $row = fgets($fp);

    //Check if file is empty
    if ($row == "") throw new Exception("[エラー：取込ファイルは空です。]");

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
    }
    fclose($fp);

    //If error create file
    if ($err_cnt != 0) {
        Create_Error_File($err_ary);
        exit;
    };

    //If no data
    if (count($data_ary) == 0) throw new Exception("[エラー：ヤマト郵便番号対応仕分マスタに取り込むﾃﾞｰﾀがありません。]");

    //Delete current data
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
        $params["record_kbn"]  = substr($obj, 0, 1);
        $params["key_part"]    = str_replace(" ", "", substr($obj, 1, 11));
        $params["delivery_cd"] = substr($obj, 12, 7);
        $params["mail_cd"]     = substr($obj, 19, 7);
        $params["start_dt"]    = substr($obj, 26, 8);
        $params["kubun"]       = substr($obj, 34, 2);
        $params["yobi"]        = substr($obj, 36, 6);
        $params["create_dt"]   = substr($obj, 42, 8);

        $insert_sth->execute($params);
    }

    //DELETE FILES
    unlink($timeFile);
    unlink($filepath);
    unlink($uploadFile);

    $dbh->commit();
} catch (Exception $e) {
    Create_Error_File($e->getMessage());

    if ($dbh) {
        $dbh->rollBack();
    }
} finally {
    exit;
}

/**
 * Create Error download file
 * @param String $file The name of file
 * @param Array $data The data being written to the file
 */
function Create_Error_File($data)
{
    $file = YAMATO_LOG_PATH . YAMATO_UPLOAD_ERROR_LOG;

    if (is_array($data)) {
        foreach ($data as $row) {
            file_put_contents($file, "[" . date("Y-m-d h:i:s") . "] " . $row . PHP_EOL, FILE_APPEND);
        };
    } elseif (is_string($data)) {
        file_put_contents($file, "[" . date("Y-m-d h:i:s") . "] " . $data . PHP_EOL, FILE_APPEND);
    }
}
