<?php
ini_set('error_reporting', E_ALL ^ E_DEPRECATED ^ E_NOTICE);
ini_set('max_execution_time', 0);

require_once("config.php");
require_once("pdf.php");

try {
    $dbh = null;
    $sth = null;
    $type = isset($_REQUEST["Type"]) ? $_REQUEST["Type"] : "";
    if ($type == "") throw new Exception("API エンドポイントは指定されていません。");

    $dbh = new PDO(DB_CON_STR, DB_USER, DB_PASS);
    $dbh->beginTransaction();

    switch ($type) {

            /** GET PRINT LIST **/
        case "printList":
            $sql = "SELECT
                        r.order_no
                        , denpyo_flg
                        , hikae_flg
                        , receipt_flg
                        , order_flg
                        , label_flg
                    FROM t_sale_report r
                    INNER JOIN t_sale_h h ON h.order_no = r.order_no
                    WHERE r.entry_date < CURRENT_TIMESTAMP + INTERVAL '1 day'
                    AND print_flg = '0'
                    ORDER BY order_no DESC;";

            $sth = $dbh->prepare($sql);
            $sth->execute();
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;

            /**
             * プリンタドライバを取得
             */
            // case "printerInfo":
            //     if (!isset($_REQUEST["report_id"]) || $_REQUEST["report_id"] == "") throw new Exception("帳票IDを入力してください。");

            //     $sql = "SELECT
            //             printer_nm
            //             FROM m_printer 
            //             WHERE id = :report_id;";

            //     $params = array();
            //     $params["report_id"] = $_REQUEST["report_id"];
            //     $sth = $dbh->prepare($sql);
            //     $sth->execute($params);
            //     $printer_info = $sth->fetchColumn();

            //     if ($printer_info == "") throw new Exception("プリンタドライバの情報を見つかりません。");

            //     echo json_encode(array("printer" => $printer_info), JSON_UNESCAPED_UNICODE);
            //     break;

            /**
             * 領収書を作成
             */
        case "receiptPdf":
            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号を入力してください。");
            // GET DATA
            $sql = "SELECT grand_total 
                            , sale_dt
                            , receive_dt
                            FROM t_sale_h 
                            WHERE order_no = :order_no;";
            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cost = $sth->fetchAll(PDO::FETCH_ASSOC);

            if ($cost == "") throw new Exception("NG");

            //Create pdf
            $fname = TEMP_FOLDER . "receipt_" . $_REQUEST["order_no"] . "_" . date("YmdHis") . PDF;
            reciept($fname, $cost);

            //Convert file to bytes
            $filedata = file_get_contents($fname);
            $filebytes = base64_encode($filedata);

            // Delete file
            unlink($fname);

            //Get printer info
            $sql = "SELECT
                    printer_nm
                    FROM m_printer 
                    WHERE id = :report_id;";

            $params = array();
            $params["report_id"] = RECEIPT_REPORT_ID;
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $printer_info = $sth->fetchColumn();

            if ($printer_info == "") throw new Exception("プリンタドライバの情報を見つかりません。");

            $result = array("printer_nm" => $printer_info, "pdf" => $filebytes);
            //3) send pdf blob
            echo json_encode(array($result), JSON_UNESCAPED_UNICODE);

            break;

            /**
             * 出荷依頼書を作成
             */
        case "hikaePdf":
            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号を入力してください。");

            //1) GET data 
            $sql = "SELECT
                        h.order_no AS order_no
                        , h.inquire_no AS inquire_no
                        , t.tokuisaki_zip AS okurisaki_zip
                        , t.tokuisaki_tel AS okurisaki_tel
                        , t.tokuisaki_adr_1 AS okurisaki_adr_1
                        , t.tokuisaki_adr_2 AS okurisaki_adr_2
                        , t.tokuisaki_adr_3 AS okurisaki_adr_3
                        , t.tokuisaki_nm AS okurisaki_nm
                        , d.product_cd AS product_cd
                        , s.product_nm AS product_nm
                        , d.qty AS qty
                        , c.kanri_nm AS tani
                        , h.grand_total AS grand_total
                        FROM t_sale_h h
                        LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                        LEFT JOIN t_sale_d d ON h.order_no = d.order_no
                        LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                        LEFT JOIN m_code c ON s.sale_tani = c.kanri_cd AND c.kanri_key = 'sale.tani'
                        WHERE h.order_no = :order_no
                        ORDER BY d.row_no;";

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("NG");

            //2) create PDF
            $fname = TEMP_FOLDER . "shuka_irai_" . date("YmdHis") . PDF;
            shukaIrai($fname, $list);

            //Convert file to bytes
            $filedata = file_get_contents($fname);
            $filebytes = base64_encode($filedata);
            // Delete file
            unlink($fname);

            //Get printer info
            $sql = "SELECT
                    printer_nm
                    FROM m_printer 
                    WHERE id = :report_id;";

            $params = array();
            $params["report_id"] = SHUKA_IRAI_REPORT_ID;
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $printer_info = $sth->fetchColumn();

            if ($printer_info == "") throw new Exception("プリンタドライバの情報を見つかりません。");

            //3) send pdf blob
            $result = array("printer_nm" => $printer_info, "pdf" => $filebytes);
            //3) send pdf blob
            echo json_encode(array($result), JSON_UNESCAPED_UNICODE);
            break;

            /**
             * 納品書・注文書
             */
        case "nouhinPdf":
            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号を入力してください。");

            //1) GET DATA
            $sql = "SELECT
                        h.order_no AS order_no
                        , h.sale_dt AS sale_dt
                        , h.receive_dt AS receive_dt
                        , h.inquire_no AS inquire_no
                        , t.tokuisaki_nm AS tokuisaki_nm
                        , t.tokuisaki_zip AS tokuisaki_zip
                        , t.tokuisaki_adr_1 AS tokuisaki_adr_1
                        , t.tokuisaki_adr_2 AS tokuisaki_adr_2
                        , t.tokuisaki_adr_3 AS tokuisaki_adr_3
                        , t.tokuisaki_tel AS tokuisaki_tel
                        , d.product_cd AS product_cd
                        , s.product_nm AS product_nm
                        , s.product_nm_abrv AS product_nm_abrv
                        , s.sale_price AS sale_price
                        , s.tax_kbn AS tax_kbn
                        , d.qty AS qty
                        , d.total_cost AS row_total
                        , c.kanri_nm AS tani
                        , h.tax_8 AS tax_8
                        , h.tax_10 AS tax_10
                        , h.total_cost AS total_cost
                        , h.grand_total AS grand_total
                        , t.sale_kbn AS sale_kbn
                        , t.order_print_kbn AS tokuisaki_disp_kbn
                        , s.order_disp_kbn AS product_disp_kbn
                        , r.receipt_flg AS receipt_flg
                        , c1.kanri_nm AS order_kbn
                        FROM t_sale_h h
                        LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                        LEFT JOIN t_sale_d d ON h.order_no = d.order_no
                        LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                        LEFT JOIN m_code c ON s.sale_tani = c.kanri_cd AND c.kanri_key = 'sale.tani'
                        LEFT JOIN m_code c1 ON h.order_kbn = c1.kanri_cd AND c1.kanri_key = 'sales.order.kbn'
                        LEFT JOIN t_sale_report r ON h.order_no = r.order_no
                        WHERE h.order_no = :order_no
                        ORDER BY d.row_no;";
            //                        LEFT JOIN m_okurisaki o ON h.okurisaki_cd = o.okurisaki_cd AND h.tokuisaki_cd = o.tokuisaki_cd
            // , o.okurisaki_zip AS okurisaki_zip
            // , o.okurisaki_tel AS okurisaki_tel
            // , o.okurisaki_adr_1 AS okurisaki_adr_1
            // , o.okurisaki_adr_2 AS okurisaki_adr_2
            // , o.okurisaki_adr_3 AS okurisaki_adr_3
            // , o.okurisaki_nm AS okurisaki_nm
            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("NG");

            //2) create PDF
            $fname = TEMP_FOLDER . "nouhin_" . date("YmdHis") . PDF;
            A3Denpyo($fname, $list);

            //Convert file to bytes
            $filedata = file_get_contents($fname);
            $filebytes = base64_encode($filedata);
            // Delete file
            unlink($fname);

            //Get printer info
            $sql = "SELECT
                    printer_nm
                    FROM m_printer 
                    WHERE id = :report_id;";

            $params = array();
            $params["report_id"] = ORDER_REPORT_ID;
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $printer_info = $sth->fetchColumn();

            if ($printer_info == "") throw new Exception("プリンタドライバの情報を見つかりません。");

            //3) send pdf blob
            $result = array("printer_nm" => $printer_info, "pdf" => $filebytes);
            //3) send pdf blob
            echo json_encode(array($result), JSON_UNESCAPED_UNICODE);
            break;

            /** 
             * 売上伝票 
             * **/
        case "denpyoPdf":
            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号を入力してください。");

            //1) GET DATA
            $sql = "SELECT 
                    h.order_no AS order_no
                    , t.tokuisaki_nm AS tokuisaki_nm
                    , d.product_cd AS product_cd
                    , s.product_nm AS product_nm
                    , d.qty AS qty
                    , s.sale_price AS sale_price
                    , d.total_cost AS row_cost
                    , h.total_qty AS total_qty
                    , h.total_cost AS total_cost
                    , h.tax_8 AS tax_8
                    , h.tax_10 AS tax_10
                    , h.grand_total AS grand_total
                    FROM t_sale_h h
                    LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
                    LEFT JOIN t_sale_d d ON h.order_no = d.order_no
                    LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
                    WHERE h.order_no = :order_no
                    ORDER BY d.row_no;";

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("NG");

            //2) CREATE PDF
            $fname = TEMP_FOLDER . "denpyo_" . date("YmdHis") . PDF;
            A4Denpyo($fname, $list);

            //Convert file to bytes
            $filedata = file_get_contents($fname);
            $filebytes = base64_encode($filedata);
            // Delete file
            unlink($fname);

            //Get printer info
            $sql = "SELECT
                    printer_nm
                    FROM m_printer 
                    WHERE id = :report_id;";

            $params = array();
            $params["report_id"] = DENPYO_REPORT_ID;
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $printer_info = $sth->fetchColumn();

            if ($printer_info == "") throw new Exception("プリンタドライバの情報を見つかりません。");

            //3) send pdf blob
            $result = array("printer_nm" => $printer_info, "pdf" => $filebytes);
            //3) send pdf blob
            echo json_encode(array($result), JSON_UNESCAPED_UNICODE);
            break;

            /** 
             * 送り状・荷札
             * */
        case "deliverySlip":
            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号を入力してください。");
            $result = array();
            //GET DATA
            $sql = "SELECT 
            h.order_no AS order_no
            , h.sale_kbn AS sale_kbn
            , h.delivery_kbn AS delivery_kbn
            , h.inquire_no AS inquire_no
            , h.sale_dt AS sale_dt
            , h.receive_dt AS receive_dt
            , c1.kanri_nm AS yamato_kbn
            , h.grand_total AS grand_total
            , h.tax_8 AS tax_8
            , h.tax_10 AS tax_10
            , h.kosu AS kosu
            , o.okurisaki_nm AS okurisaki_nm
            , o.okurisaki_tel AS okurisaki_tel
            , o.okurisaki_zip AS okurisaki_zip
            , o.okurisaki_adr_1 AS okurisaki_adr_1
            , o.okurisaki_adr_2 AS okurisaki_adr_2
            , o.okurisaki_adr_3 AS okurisaki_adr_3
            , s.product_nm_abrv AS product_nm_abrv
            , d.qty AS qty
            , c2.kanri_nm AS sale_tani
            , c3.kanri_cd AS haten_cd
            , c4.kanri_cd AS yamato_inquire_no
            , y.delivery_cd AS yamato_delivery_cd
            , s.label_disp_kbn AS product_disp_kbn
            , c5.kanri_nm AS jikai_kbn_1
            , c6.kanri_nm AS jikai_kbn_2
            , c7.kanri_nm AS jikai_kbn_3
            FROM t_sale_h h
            LEFT JOIN t_sale_d d ON h.order_no = d.order_no
            LEFT JOIN m_shohin s ON d.product_cd = s.product_cd
            LEFT JOIN m_okurisaki o ON h.okurisaki_cd = o.okurisaki_cd AND h.tokuisaki_cd = o.tokuisaki_cd
            LEFT JOIN m_tokuisaki t ON h.tokuisaki_cd = t.tokuisaki_cd
            LEFT JOIN m_code c1 ON h.yamato_kbn = c1.kanri_cd AND c1.kanri_key = 'yamato.kbn'
            LEFT JOIN m_code c2 ON s.sale_tani = c2.kanri_cd AND c2.kanri_key = 'sale.tani'
            LEFT JOIN m_code c3 ON c3.kanri_key = 'yamato.haten.code'
            LEFT JOIN m_code c4 ON c4.kanri_key = 'yamato.inquire.no'
            LEFT JOIN m_code c5 ON t.jikai_kbn_1 = c5.kanri_cd AND c5.kanri_key = 'jikai.kbn'
            LEFT JOIN m_code c6 ON t.jikai_kbn_2 = c6.kanri_cd AND c6.kanri_key = 'jikai.kbn'
            LEFT JOIN m_code c7 ON t.jikai_kbn_3 = c7.kanri_cd AND c7.kanri_key = 'jikai.kbn'
            LEFT JOIN m_yamato y ON o.okurisaki_zip = y.key_part
            WHERE h.order_no = :order_no
            AND h.delivery_kbn = '1'
            ORDER BY d.row_no;";

            // , CASE 
            // WHEN h.next_kbn = '1' THEN CONCAT('◎　', o.okurisaki_nm)
            // ELSE o.okurisaki_nm
            // END AS okurisaki_nm

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) throw new Exception("NG");

            //if ($list[0]["sale_kbn"] != "1" && $list[0]["sale_kbn"] != "4") throw new Exception("送り状を発行できる売上区分ではありません。");
            
            //2) create PDF
            //YAMATO
            // if ($list[0]["delivery_kbn"] == "1") {
            if ($list[0]["sale_kbn"] == "2") {
                throw new Exception("NG");
            }
            if ($list[0]["sale_kbn"] == "1") {
                $report_id = YAMATO_DAIBIKI_REPORT_ID;
                $fname = TEMP_FOLDER . "yamato_daibiki_" . date("YmdHis") . PDF;

                if ($list[0]["kosu"] > 1) {
                    yamatoDaibiki($fname, $list);

                    $report_id_2 = YAMATO_MOTO_BARAI_REPORT_ID;
                    $fname2 = TEMP_FOLDER . "yamato_moto_barai_" . date("YmdHis") . PDF;

                    yamatoDaibikiMotoBarai($fname2, $list);
                } else {
                    yamatoDaibiki($fname, $list);
                }
            } else { // if ($list[0]["sale_kbn"] == "4")
                $report_id = YAMATO_MOTO_BARAI_REPORT_ID;
                $fname = TEMP_FOLDER . "yamato_moto_barai_" . date("YmdHis") . PDF;
                yamatoMotoBarai($fname, $list);
            }
            // };

            //Convert file to bytes
            $filedata = file_get_contents($fname);
            $filebytes = base64_encode($filedata);

            if (isset($fname2)) {
                $filedata2 = file_get_contents($fname2);
                $filebytes2 = base64_encode($filedata2);

                //$pdfArray = array($filebytes, $filebytes2);
                unlink($fname2);
            };

            // Delete file
            unlink($fname);

            //Get printer info
            $sql = "SELECT
                    printer_nm
                    FROM m_printer 
                    WHERE id = :report_id;";

            $params = array();
            $params["report_id"] = $report_id;
            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $printer_info = $sth->fetchColumn();

            if ($printer_info == "") throw new Exception("プリンタドライバの情報を見つかりません。");


            $result[0] = array("printer_nm" => $printer_info, "pdf" => $filebytes);
            if (isset($report_id_2)) {
                //Get printer info
                $sql = "SELECT
                        printer_nm
                        FROM m_printer 
                        WHERE id = :report_id;";

                $params = array();
                $params["report_id"] = $report_id_2;
                $sth = $dbh->prepare($sql);
                $sth->execute($params);
                $printer_info_2 = $sth->fetchColumn();

                if ($printer_info_2 == "") throw new Exception("プリンタドライバの情報を見つかりません。");

                $result[1] = array("printer_nm" => $printer_info_2, "pdf" => $filebytes2);
                //$printer_array = array($printer_info, $printer_info_2);
            }

            //3) send pdf blob
            // if (isset($printer_array)) {
            //     echo json_encode(array("printer_nm" => $printer_array, "pdf" => $pdfArray), JSON_UNESCAPED_UNICODE);
            // } else {
            //     echo json_encode(array("printer_nm" => array($printer_info), "pdf" => array($filebytes)), JSON_UNESCAPED_UNICODE);
            // }
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

            /**
             * 発行フラグを更新
             * **/
        case "updateReportPrint":
            if (!isset($_REQUEST["order_no"]) || $_REQUEST["order_no"] == "") throw new Exception("受注番号を入力してください。");

            $sql = "UPDATE t_sale_report
                    SET print_flg = '1'
                    , update_date = CURRENT_TIMESTAMP
                    WHERE order_no = :order_no;";

            $params = array();
            $params["order_no"] = $_REQUEST["order_no"];

            $sth = $dbh->prepare($sql);
            $sth->execute($params);
            $cnt = $sth->rowCount();

            if ($cnt == 0) throw new Exception("自動発行更新に失敗しました。");

            $dbh->commit();
            echo json_encode(array("result" => "OK"), JSON_UNESCAPED_UNICODE);
            break;

            /** GET LIST OF PRINTED ORDERS **/
        case "completeList":
            $sql = "SELECT
                            order_no
                            , denpyo_flg
                            , hikae_flg
                            , receipt_flg
                            , order_flg
                            , label_flg
                            , print_flg
                        FROM t_sale_report
                        WHERE print_flg = '1'
                        ORDER BY order_no;";

            $sth = $dbh->prepare($sql);
            $sth->execute();
            $list = $sth->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($list, JSON_UNESCAPED_UNICODE);
            break;

            /** PRINT HISTORY **/
        case "printHistory":
            $sql = "SELECT 
                    COALESCE(u.user_nm, '存在しない') AS user_nm
                    , h.order_no AS order_no
                    , o.okurisaki_nm AS tokuisaki_nm
                    , TO_CHAR(h.sale_dt, 'YYYY/MM/DD') AS sale_dt
                    , CASE WHEN r.label_flg = '1' THEN '〇'
                        ELSE ''
                    END AS label_flg
                    , CASE WHEN r.denpyo_flg = '1' THEN '〇'
                        ELSE ''
                    END AS denpyo_flg
                    , CASE WHEN r.hikae_flg = '1' THEN '〇'
                        ELSE ''
                    END AS hikae_flg
                    , CASE WHEN r.receipt_flg = '1' THEN '〇'
                        ELSE ''
                    END AS receipt_flg
                    , CASE WHEN r.order_flg = '1' THEN '〇'
                        ELSE ''
                    END AS order_flg
                    , CASE WHEN r.print_flg = '1' THEN '済'
                        ELSE '未'
                    END AS print_flg
                    FROM t_sale_report r
                    INNER JOIN t_sale_h h ON h.order_no = r.order_no
                    LEFT JOIN m_user u ON h.entry_user_id = u.user_id
                    LEFT JOIN m_okurisaki o ON o.tokuisaki_cd = h.tokuisaki_cd AND o.okurisaki_cd = h.okurisaki_cd
                    WHERE h.sale_dt = CURRENT_DATE
                    ORDER BY h.sale_dt DESC, h.order_no DESC;";

            $sth = $dbh->prepare($sql);
            $sth->execute();
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {

    if ($dbh != null) {
        $dbh->rollBack();
    }
    echo json_encode(array(array("printer_nm" => "", "pdf" => "", "error" => $e->getMessage())), JSON_UNESCAPED_UNICODE);
}
