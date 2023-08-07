<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="container disnon">

    <div class="left">
        <form id="salesFrm" autocomplete="off">
            <fieldset>
                <legend>売上伝票</legend>

                <div class="error-row">
                    <p id="saleFrm_error"></p>
                    <p></p>
                </div>

                <div class="row">

                    <div class="col-6">
                        <label for="saleFrm_next_kbn">次回内容</label>
                        <select name="next_kbn" id="saleFrm_next_kbn" class="">
                            <?php
                            foreach ($_SESSION["jikai_kbn_list"] as &$obj) {
                                //if ($obj["kanri_key"] == "jikai.kbn") {
                                echo '<option color="' . $obj["color"] . '" value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                //}
                            }
                            ?>
                        </select>
                        <select name="next_kbn2" id="saleFrm_next_kbn2" style="margin-left: 5px;">
                            <?php
                            foreach ($_SESSION["jikai_kbn_list"] as &$obj) {
                                //if ($obj["kanri_key"] == "jikai.kbn") {
                                    echo '<option color="' . $obj["color"] . '" value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                // }
                            }
                            ?>
                        </select>
                        <select name="next_kbn3" id="saleFrm_next_kbn3" style="margin-left: 5px;">
                            <?php
                            foreach ($_SESSION["jikai_kbn_list"] as &$obj) {
                                // if ($obj["kanri_key"] == "jikai.kbn") {
                                    echo '<option color="' . $obj["color"] . '" value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                // }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-6">
                        <label class="" for="sender_cd">荷送人</label>
                        <select class="" name="sender_cd" id="sender_cd" required>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "sales.sender.kbn") {
                                    echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                </div>

                <div class="row">

                    <div class="col-6">
                        <label for="saleFrm_sale_dt">売上日</label>
                        <input class="" type="date" name="sale_dt" id="saleFrm_sale_dt" required>
                    </div>

                    <div class="col-6">
                        <label>受注No.</label>
                        <!-- <input class="calc_w100" type="text" name="order_no" id="order_no" > -->
                        <label id="saleFrm_order_no" class="label tal"></label>
                    </div>

                </div>

                <div class="row">
                    <div class="col-5">
                        <label for="saleFrm_tokuisaki_tel" class="tal">得意先</label>
                        <input class="calc_w100 label" type="tel" name="sale_tokuisaki_tel" id="saleFrm_tokuisaki_tel" maxlength="11" required>
                    </div>

                    <div class="col-7">
                        <!-- <label>得意先</label> -->
                        <label class="ip_w100 label tal" id="saleFrm_tokuisaki_nm"></label>

                    </div>
                </div>

                <div class="row livesearch_row">
                    <div class="col-6" style="justify-content: end;">
                        <ul class="livesearch calc_w100"></ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col-5">
                        <label class="tal"></label>
                        <label class="calc_w100 label tal" id="saleFrm_tokuisaki_adr_1"></label>
                    </div>

                    <!-- <div class="col-6">
                        <label>郵便番号</label>
                        <label class="calc_w100 label" id="saleFrm_tokuisaki_zip"></label>
                    </div>

                    <div class="col-6">
                        <label>住所01</label>
                        <label class="calc_w100 label tal" id="saleFrm_tokuisaki_adr_1"></label>
                    </div> -->
                </div>

                <!-- <div class="row">
                    <div class="col-6">
                        <label>住所02</label>
                        <label class="calc_w100 label tal" id="saleFrm_tokuisaki_adr_2"></label>
                    </div>

                    <div class="col-6">
                        <label>住所03</label>
                        <label class="calc_w100 label tal" id="saleFrm_tokuisaki_adr_3"></label>
                    </div>
                </div> -->


                <div class="row">
                    <div class="col-12">
                        <table id="mesai_table">
                            <caption>売上明細</caption>
                            <thead>
                                <tr>
                                    <th>選択</th>
                                    <th>商品コード</th>
                                    <th>商品名</th>
                                    <th class="tar">単価</th>
                                    <th class="tar">数量</th>
                                    <th class="tar">金額</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="row_1">
                                    <td>
                                        <input type="checkbox" class="checkbox" value="1">
                                    </td>
                                    <td>
                                        <input row-index="1" type="text" id="mesai_shohin_cd_1" class="mesai_shohin_cd" maxlength="3">
                                    </td>
                                    <td class="shohin_nm" id="mesai_shohin_nm_1"></td>
                                    <td class="tar" id="mesai_tanka_1"></td>
                                    <td>
                                        <input class="mesai_shohin_qty tar" row-index="1" id="mesai_shohin_qty_1" type="text" maxlength="7">
                                    </td>
                                    <td class="tar mesai_row_total" id="mesai_row_total_1" row-index="1"></td>
                                    <input type="hidden" id="mesai_tax_rate_1" class="mesai_tax_rate" row-index="1" />
                                    <input type="hidden" id="mesai_shohin_tax_1" />
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <button class="ip_w30 btnDelete" id="row_delete" type="button" title="ctrl+F6">削除</button>
                        <button class="ip_w30 common pick" type="button">全選択</button>
                        <button class="ip_w30 common unpick" type="button">全解除</button>
                    </div>

                    <div class="col-6" style="justify-content: right;">
                        <label style="margin-right: 5px;" class="label ip_w100 tar" id="saleFrm_qty-total"></label>
                        <label class="label ip_w100 tar" id="saleFrm_amt-total"></label>
                    </div>

                    <input type="hidden" id="zero-tax" value="">
                </div>

                <div class="row">
                    <div class="col-6">
                        <label for="saleFrm_order_kbn_box">注文区分</label>
                        <input type="text" maxlength="2" class="input_box" id="saleFrm_order_kbn_box">
                        <select name="order_kbn" id="saleFrm_order_kbn" class="calc_w90 input_box_select" required>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "sales.order.kbn") {
                                    echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                }
                            }
                            ?>

                        </select>
                    </div>

                    <div class="col-6">
                        <label>消費税<small>8%</small></label>
                        <label id="saleFrm_tax_8" class="calc_w100 label tar"></label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label for="saleFrm_sales_kbn_box">売上区分</label>
                        <input type="text" maxlength="1" class="input_box" id="saleFrm_sales_kbn_box">
                        <select name="sales_kbn" id="saleFrm_sales_kbn" class="calc_w70 input_box_select" required>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "sale.kbn") {
                                    echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label>消費税<small>10%</small></label>
                        <label id="saleFrm_tax_10" class="ip_w100 label tar"></label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label for="saleFrm_delivery_kbn_box">便種区分</label>
                        <input type="text" maxlength="1" class="input_box" id="saleFrm_delivery_kbn_box">
                        <select name="delivery_kbn" id="saleFrm_delivery_kbn" class="calc_w90 input_box_select" required>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "delivery.kbn") {
                                    echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-6">
                        <label>締　　高</label>
                        <label id="saleFrm_shime_taka" class="calc_w100 label tar"></label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label id="recieve_dt_label" for="saleFrm_recieve_dt">お届け日</label>
                        <input type="date" class="calc_w80" name="receive_dt" id="saleFrm_recieve_dt" required>
                    </div>

                    <div class="col-6">
                        <label for="saleFrm_kosu">個　　数</label>
                        <input style="width:50px;" type="tel" maxlength="3" name="kosu" id="saleFrm_kosu" value="1" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label for="saleFrm_yamato_kbn" class="tal">ヤマト時</label>
                        <input type="text" maxlength="2" class="input_box" id="saleFrm_yamato_kbn_box">
                        <select name="yamato_kbn" id="saleFrm_yamato_kbn" class="calc_w90 input_box_select" required>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "yamato.kbn") {
                                    echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                }
                            }
                            ?>

                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-1">
                        <label id="sagawa_label" for="saleFrm_delivery_time_kbn_box" class="tal">佐川時</label>
                    </div>

                    <div class="col-1">
                        <input type="text" maxlength="1" class="input_box" id="saleFrm_delivery_time_kbn_box">
                        <select name="delivery_time_kbn" id="saleFrm_delivery_time_kbn" class="ip_w100 input_box_select" style="margin-right: 15px;width: 100%;" required>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "delivery.time.kbn") {
                                    echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-1">
                        <input class="tac" type="text" name="delivery_time_hr" id="saleFrm_delivery_time_hr" style="width: 80px;" maxlength="2">
                        <label style="width:auto;">：</label>
                        <input class="tac" type="text" name="delivery_time_min" id="saleFrm_delivery_time_min" style="width: 80px;margin-right: 15px" maxlength="2">
                    </div>

                    <div class="col-1 delivery_col">
                        <input type="text" maxlength="1" class="input_box" id="last_move_input">
                        <select name="delivery_instruct_kbn" id="saleFrm_delivery_instruct_kbn" class="input_box_select" style="width: 100%;" required>
                            <?php
                            foreach ($_SESSION["code_list"] as &$obj) {
                                if ($obj["kanri_key"] == "delivery.instruct.kbn") {
                                    echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                </div>

            </fieldset>

            <div class="btnBlock">
                <button type="button" class="common" id="btnRegMain" title="ctrl + F1">
                    登録
                </button>
                <button type="button" class="cancel" id="btnCancel" title="ctrl + F8">
                    キャンセル
                </button>
                <button type="button" class="btnDelete disnon" id="btnDelete">削除</button>
                <button type="button" class="common disnon" id="btnEdit">編集</button>
            </div>

            <fieldset>
                <legend>帳票出力</legend>
                <div class="row pdf-select">

                    <div class="col-2">
                        <label for="sale_slip">売上伝票</label>
                        <input type="checkbox" name="" id="sale_slip" checked>
                    </div>

                    <div class="col-3">
                        <label for="sale_hikae">売上伝票<small>(控)</small></label>
                        <input type="checkbox" name="" id="sale_hikae">
                    </div>

                    <div class="col-2">
                        <label for="reciept">領収書</label>
                        <input type="checkbox" name="" id="reciept">
                    </div>

                    <div class="col-2">
                        <label for="label">荷札</label>
                        <input type="checkbox" name="" id="label" checked>
                    </div>


                    <div class="col-2">
                        <label for="order_frm">注文書</label>
                        <input type="checkbox" name="" id="order_frm" checked>
                    </div>

                </div>
            </fieldset>

            <!-- <input type="hidden" id="inquire_no" name="inquire_no"> -->
            <!-- <input type="hidden" id="okurisaki_cd" name="sale_okurisaki_cd"> -->
        </form>
    </div>

    <div class="right hide">
        <div class="row tabs">
            <div class="col-3 tab ">
                <label class="activeBtn" tab-content="content_1">売上明細履歴</label>
            </div>
            <div class="col-3 tab">
                <label class="" tab-content="content_2">売上履歴</label>
            </div>
            <div class="col-3 tab">
                <label tab-content="content_3">得意先</label>
            </div>
            <div class="col-3 tab">
                <label class="tac" id="okuri_saki_tab" style="border-right: 1px solid #000;" tab-content="content_4">送り先</label>
            </div>
        </div>

        <div class="content">

            <!----------------- 売上明細履歴 ------------------>
            <div class="tab_content activeTbl" id="content_1">
                <table id="uriage_table">
                    <caption>売上明細履歴</caption>
                    <thead>
                        <tr>
                            <th>売上日</th>
                            <th>商品名</th>
                            <th>数量</th>
                            <th>金額</th>
                            <th>商品コード</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="row" style="height: 10%;">
                    <section class="pagenavi" id="mesaiPagination" role="navigation">
                    </section>
                </div>

                <div class="row memo">
                    <div class="col-12">
                        <textarea class="ip_w100 tokuisaki_comment" id="saleFrm_tokuisaki_comment" maxlength="400"></textarea>
                    </div>
                </div>
            </div>

            <!----------------- 売上履歴 ------------------>
            <div class="tab_content disnon" id="content_2">
                <table id="sales_history_table">
                    <caption>売上履歴</caption>
                    <thead>
                        <tr>
                            <th>受注番号</th>
                            <th>売上日</th>
                            <th>売上金額</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="btnBlock">
                    <button type="button" class="btnDelete" id="minusDen">赤伝</button>
                    <button type="button" class="common" id="copyBtn">複写</button>
                </div>

                <section class="pagenavi" id="salePagination" role="navigation" style="height: 5%;"></section>

                <fieldset id="sales_history_detail">
                    <legend>売上情報</legend>

                    <div class="row">
                        <div class="col-6">
                            <label>受注番号</label>
                            <label class="calc_w100 label tal" id="sales_history_detail_order_no"></label>
                        </div>

                        <div class="col-6">
                            <label>お届け日</label>
                            <label class="calc_w100 label tal" id="sales_history_detail_sale_dt"></label>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label>注文区分</label>
                            <label class="calc_w100 label tal" id="sales_history_detail_order_kbn"></label>
                        </div>

                        <div class="col-6">
                            <label>売上区分</label>
                            <label class="calc_w100 label tal" id="sales_history_detail_sale_kbn"></label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">配達時間</label>
                            <label class="calc_w100 label tal" id="sales_history_detail_delivery_time"></label>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">便　　種</label>
                            <label class="calc_w100 label tal" id="sales_history_detail_delivery_type"></label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label>問合せ<small>No.</small></label>
                            <label class="calc_w100 label tal" id="sales_history_detail_inquery_no"></label>
                            <!-- <input type="text" class="calc_w100 label tal disnon" id="inquery_no_input" maxlength="12"> -->
                        </div>

                        <div class="col-2" style="justify-content: center;">
                            <button type="button" class="common" id="btnInqueryNo">変更</button>
                        </div>

                        <div class="col-4">
                            <label id="kosu_label">個　数</label>
                            <label class="calc_w100 label " id="sales_history_detail_kosu"></label>
                        </div>

                    </div>

                </fieldset>
            </div>

            <!----------------- 得意先情報 ------------------>
            <div class="tab_content disnon" id="content_3">
                <form id="tokuisakiFrm">

                    <div class="error-row">
                        <p id="tokuisaki_error"></p>
                    </div>

                    <input type="hidden" id="tokuisakiFrm_tokuisaki_cd" name="tokuisaki_cd">

                    <div class="row check-row">
                        <div>
                            <label for="tokuisakiFrm_kbn_order">注文者</label>
                            <input type="checkbox" id="tokuisakiFrm_kbn_order" checked="true">
                        </div>
                        <div class="">
                            <label for="tokuisakiFrm_kbn_deliver">送り先</label>
                            <input type="checkbox" id="tokuisakiFrm_kbn_deliver" checked="true">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label class="tal">得意先名</label>
                            <input class="calc_w100" type="text" name="tokuisaki_nm" id="tokuisakiFrm_tokuisaki_nm" maxlength="40" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">得意先<small>カナ</small></label>
                            <input class="calc_w100" type="text" name="tokuisaki_kana" id="tokuisakiFrm_tokuisaki_kana" placeholder="半角カナ" >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">〒</label>
                            <input class="calc_w60" type="tel" name="tokuisaki_zip" id="tokuisakiFrm_tokuisaki_zip" maxlength="8" >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">住所01</label>
                            <input class="calc_w100" type="text" name="tokuisaki_adr_1" id="tokuisakiFrm_tokuisaki_adr_1" placeholder="〇〇県〇〇市〇〇区" >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">住所02</label>
                            <input class="calc_w100" type="text" name="tokuisaki_adr_2" id="tokuisakiFrm_tokuisaki_adr_2" placeholder="〇〇町〇〇番" >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">住所03</label>
                            <input class="calc_w100" type="text" name="tokuisaki_adr_3" id="tokuisakiFrm_tokuisaki_adr_3" placeholder="ビル名">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">配達指示</label>
                            <input class="calc_w100" type="text" name="tokuisaki_delivery_instruct" id="tokuisakiFrm_delivery_instruct">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal" style="letter-spacing: 28px;">業種</label>
                            <select name="industry_cd" id="tokuisakiFrm_industry_cd" class="calc_w60" required>
                                <?php
                                foreach ($_SESSION["code_list"] as &$obj) {
                                    if ($obj["kanri_key"] == "industry.kbn") {
                                        echo '<option value="' . $obj["kanri_cd"] . '">' . $obj["kanri_nm"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">代表番号</label>
                            <input class="calc_w60" type="tel" name="tokuisaki_tel" id="tokuisakiFrm_tokuisaki_tel" maxlength="11" required>
                            <button type="button" class="common faxCopyBtn" style="margin-left: 10px;" id="">FAXへコピー</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">FAX</label>
                            <input class="calc_w60" type="tel" name="tokuisaki_fax" id="tokuisakiFrm_tokuisaki_fax" maxlength="11">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">予備連絡</label>
                            <input class="calc_w60" type="tel" name="fuzai_contact" id="tokuisakiFrm_fuzai_contact" maxlength="11">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">担当者</label>
                            <input class="calc_w60" type="text" name="tanto_nm" id="tokuisakiFrm_tanto_nm" maxlength="20">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">コメント</label>
                            <textarea class="calc_w100 tokuisaki_comment" name="comment" id="tokuisakiFrm_tokuisaki_comment" cols="30" rows="10" maxlength="400"></textarea>
                        </div>
                    </div>

                    <div class="btnBlock ">
                        <button type="button" class="common" id="btnTokuisakiReg" title="ctrl + F12">得意先登録</button>
                    </div>
                </form>
            </div>

            <!----------------- 送り先情報 ------------------>
            <div class="tab_content disnon" id="content_4">
                <form id="okurisakiFrm">

                    <div class="error-row">
                        <p id="okurisaki_error"></p>
                    </div>

                    <input type="hidden" name="okurisaki_tokuisaki_cd" id="okurisakiFrm_tokuisaki_cd">
                    <input type="hidden" id="okurisaki_cd" name="okurisaki_cd">
                    <div class="row">
                        <div class="col-12">
                            <label class="tal">送り先名</label>
                            <input class="calc_w100" type="text" name="okurisaki_nm" id="okurisaki_nm" maxlength="40" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">送り先<small>カナ</small></label>
                            <input class="calc_w100" type="text" name="okurisaki_kana" id="okurisaki_kana" maxlength="64" placeholder="半角カナ" >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">〒</label>
                            <input class="calc_w60" type="tel" name="okurisaki_zip" id="okurisaki_zip" maxlength="8" >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">住所01</label>
                            <input class="calc_w100" type="text" name="okurisaki_adr_1" id="okurisaki_adr_1" placeholder="〇〇県〇〇市〇〇区" >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">住所02</label>
                            <input class="calc_w100" type="text" name="okurisaki_adr_2" id="okurisaki_adr_2" placeholder="〇〇町〇〇番" >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">住所03</label>
                            <input class="calc_w100" type="text" name="okurisaki_adr_3" id="okurisaki_adr_3" placeholder="ビル名">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">配達指示</label>
                            <input class="calc_w100" type="text" name="okurisaki_delivery_instruct" id="okurisaki_delivery_instruct">
                        </div>
                    </div>

                    <!-- <div class="row">
                        <div class="col-12">
                            <label class="tal" style="letter-spacing: 28px;">業種</label>
                            <select name="okurisaki_industry_cd" id="okurisaki_industry_cd" class="calc_w60" required>
                                
                            </select>
                        </div>
                    </div> -->

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">代表番号</label>
                            <input class="calc_w60" type="tel" name="okurisaki_tel" id="okurisaki_tel" maxlength="11" required>
                            <button type="button" class="common faxCopyBtn" style="margin-left: 10px;" id="">FAXへコピー</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">FAX</label>
                            <input class="calc_w60" type="tel" name="okurisaki_fax" id="okurisaki_fax" maxlength="11">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">予備連絡</label>
                            <input class="calc_w60" type="tel" name="okurisaki_fuzai_contact" id="okurisaki_fuzai_contact" maxlength="11">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="tal">担当者</label>
                            <input class="calc_w60" type="text" name="okurisaki_tanto_nm" id="okurisaki_tanto_nm" maxlength="20">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <section class="pagenavi disnon" id="okurisaki_nav" role="navigation">
                                <a class="prevpostslink" rel="next" href="#" page="1">＜＜</a>
                                <span class="pg_num">1/2</span>
                                <a class="nextpostslink" rel="next" href="#" page="2">＞＞</a>
                            </section>
                        </div>
                    </div>

                    <div class="btnBlock ">
                        <button type="button" class="common" id="btnOkuriReg" title="ctrl + F10">送り先登録</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <div class="memo_section">
        <fieldset>
            <legend>メモ</legend>
            <textarea name="" id="memo" maxlength="1000"></textarea>
            <input type="hidden" id="memo_update_date">
            <div class="btnBlock">
                <button type="button" class="common" id="memoBtn" title="ctrl + F3">更新</button>
            </div>
        </fieldset>
    </div>

    <div class="btnBlock ip_w100 toggleBtnBlock">
        <button type="button" class="subBtn" id="formToggle" screen="right">詳細情報</button>
    </div>

    <input class="disnon" type="checkbox" id="akaden">
    <input type="hidden" id="copy-order">
</div>

<div class="dialog" id="tokuisaki-search">
    <p class="err_msg"></p>
    <form class="itemEdit" id="tokuisakiSrchFrm">
        <dl class="">
            <dt>電話番号</dt>
            <dd>
                <input type="tel" class="ip_w100" name="tokuisaki_tel" />
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

<div class="dialog" id="product-search">
    <p class="err_msg"></p>
    <form class="itemEdit" id="productSrchFrm">
        <dl class="">
            <dt>商品コード</dt>
            <dd>
                <input type="tel" class="ip_w100" name="product_cd" />
            </dd>
        </dl>
        <dl class="">
            <dt>商品名</dt>
            <dd>
                <input type="text" class="ip_w100" name="product_nm" />
            </dd>
        </dl>
        <div class="frm-table sub-table">
            <table class="" id="productTable">
                <thead>
                    <tr>
                        <th>商品コード</th>
                        <th>商品名</th>
                        <th>売上単価</th>
                    </tr>
                </thead>

                <tbody class="sm-table"></tbody>
            </table>
        </div>
    </form>
</div>

<div class="dialog" id="inquery-change">
    <p class="err_msg"></p>
    <form class="itemEdit" id="inqueryNoFrm">
        <dl>
            <dt>受注番号</dt>
            <dd id="inqueryNoFrm_order_no_disp"></dd>
        </dl>
        <dl class="">
            <dt>問合せ番号</dt>
            <dd id="inqueryNoFrm_inquery_no"></dd>
        </dl>
        <dl class="">
            <dt>新しい問合せ番号</dt>
            <dd>
                <input type="text" class="ip_w100" name="inquire_no" maxlength="12" />
            </dd>
        </dl>
        <input type="hidden" name="order_no" id="inqueryNoFrm_order_no">
        <input type="hidden" name="sale_dt" id="inqueryNoFrm_sale_dt">
    </form>
</div>
<!-- <input type="hidden" id="updated" value="false"> -->
<button type="button" class="disnon" id="updated"></button>
<script src="/js/common/import.js?p=(new Date()).getTime()"></script>
<script src="/js/S1_sales.js?p=(new Date()).getTime()"></script>