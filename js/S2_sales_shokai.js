$(document).ready(() => {

    $(".btnSearch").click(function () {
        fncSearch(this, 1);
    });

    $(".btnDenpyo").click(() => {

        if (!$("#sales_table input:checked").length > 0) {
            alert("対象のデータが選択されていません。");
            return;
        }

        dispDenpyoDialog();
    });

    $(document.body).on('click', '.btnEdit_icon', function () {
        dispSaleDialog(this.id);
    });

    $("input[type='tel']").keyup(async function () {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });
});

/**
 * ASYNC SEARCH FUNCTION
 * 
 */
const fncSearch = async (el, pg) => {
    try {

        dispLoading("処理中...");

        $("article .inner").empty();
        $(".pagenavi, .kensu, .content .btnBlock").addClass("disnon");

        var frm = new FormData($(`#${$(el).attr("form")}`).get(0));

        const res = await fetch(`${API_PATH}getSaleList&pagenum=${pg}`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            alert(data.error);
            return;
        };

        //Page total & Total count
        const { total_page, count } = data[data.length - 1];

        //remove total & Total count
        data.pop();

        $("#list").append($(`
            <table class="" id="sales_table">
                <thead>
                    <tr>
                        <th class="w_50px">選択</th>
                        <th class=" w_50px">詳細</th>
                        <th class="w_150px sort desc" th-col="2">売上日</th>
                        <th class="tal sort desc">得意先</th>
                        <th class="sort desc">受注番号</th>
                        <th class="tal sort desc">問合せ番号</th>
                        <th class="tal sort desc">ログインID</th>
                    </tr>
                </thead>
                <tbody class="list"></tbody>
            </table>
        `))

        const list = $("#sales_table .list");
        const sp = $("#users_sp .inner");

        data.forEach((obj) => {
            const tr = $(`
            <tr>
                <td class="w_50px" >
                    <input class="checkbox" type="checkbox" value="${obj.order_no}">
                </td>
                <td class="w_50px">
                    <img src="/images/icon_edit.svg" class="btnEdit_icon" alt="詳細" id="${obj.order_no}" loading="lazy"/>
                </td>
                <td class="w_150px">${obj.sale_dt}</td>
                <td class="tal">${(obj.tokuisaki_nm == "") ? "存在しない" : obj.tokuisaki_nm}</td>
                <td >${obj.order_no}</td>
                <td class="tal">${obj.inquire_no}</td>
                <td class="tal">${obj.user_nm}</td>
            </tr>
            `);
            list.append(tr);

            /** PHONE TABLE VIEW **/
            var dl = $(`
            <dl class="itemList_sp">
                <a class="link" id="${obj.order_no}"></a>
                <dt>${obj.order_no}</dt>
                ${obj.sale_dt} | ${(obj.tokuisaki_nm == "") ? "存在しない" : obj.tokuisaki_nm} | ${obj.user_nm}
            </dl>
            `);
            sp.append(dl);
        });

        /** PAGE NAVIGATION **/
        pagination(pg, total_page, count, $(el).attr("form"));
        pagination_sp(pg, total_page, $(el).attr("form"));

        $(".content .disnon").removeClass("disnon");

        $("#input").prop("checked", false);

    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}

/**
 * RE-CREATE PDF
 * - Outputs a zip file with all created pdf's
 */
const dispDenpyoDialog = async () => {
    $("#denpyo-dialog .err_msg").text("").hide();
    $("#denpyo-dialog .itemEdit input").prop("disabled", false).prop('checked', false);
    $("#denpyo-dialog").dialog({
        title: "伝票再発行",
        modal: true,
        height: 500,
        maxHeight: $("body").height(),
        width: isTouch ? screen.availWidth - 10 : 500,
        buttons: [
            {
                text: "発行",
                class: "btn-edit",
                click: async function () {
                    try {
                        dispLoading("処理中...");

                        $("#denpyo-dialog .err_msg").text("").hide();

                        if(!$("#reciept").prop("checked") && 
                            !$("#sale_hikae").prop("checked") && 
                            !$("#label").prop("checked") &&
                            !$("#sale_slip").prop("checked") &&
                            !$("#order_frm").prop("checked")){
                            $("#denpyo-dialog .err_msg").text("再発行帳票が選択されていません。").slideDown();
                            return;
                        }

                        var order_no_ary = [];

                        $("#sales_table input:checked").each(function () {
                            order_no_ary.push($(this).val());
                        });

                        const frm = new FormData();
                        frm.append("order_no", JSON.stringify(order_no_ary));
                        //領収書
                        frm.append("receipt_flg", ($("#reciept").prop("checked")) ? "1" : "0");
                        //売上伝票（控）
                        frm.append("hikae_flg", ($("#sale_hikae").prop("checked")) ? "1" : "0");
                        //送り状
                        frm.append("label_flg", ($("#label").prop("checked")) ? "1" : "0");
                        //売上伝票
                        frm.append("denpyo_flg", ($("#sale_slip").prop("checked")) ? "1" : "0");
                        //注文書
                        frm.append("order_flg", ($("#order_frm").prop("checked")) ? "1" : "0");

                        const res = await fetch(`${API_PATH}pdfReportUpdate`, { body: frm, method: "POST" });

                        if (!res.ok) {
                            alert("ネットワークエラーが発生しました。");
                            return;
                        };

                        const data = await res.json();

                        // If Error
                        if (data.hasOwnProperty("error")) {
                            $("#denpyo-dialog .err_msg").text(data.error).slideDown();
                            console.log(data);
                            return;
                        };
                        alert("再発行帳票を更新しました。");
                        $(this).dialog("destroy");
                    } catch (error) {
                        console.log(error);
                        alert("サーバーでエラーが発生しました。");
                    } finally {
                        removeLoading();
                    }

                },
            },
            {
                text: "閉じる",
                class: "btn-close",
                click: function () {
                    $(this).dialog("destroy");
                },
            },
        ],
    });
}

/**
 * OUTPUT PDF
 */
const createPdf = async () => {
    //領収書
    if ($("#reciept").prop("checked")) {
        createReceipt($salesOrderNo.text());
    };
    //売上伝票（控）
    if ($("#sale_hikae").prop("checked")) {
        createDenpyoHikae($salesOrderNo.text());
    };
    //送り状
    if ($("#label").prop("checked")) {
        if ($salesDeliveryKbn.val() == "1") {
            if ($salesKbn.val() == "1" || $salesKbn.val() == "4") {
                createYamatoOkurijo($salesOrderNo.text());
            };
        } else if ($salesDeliveryKbn.val() == "2") {
            createSagawaOkurijo($salesOrderNo.text());
        };
    };
    //売上伝票
    if ($("#sale_slip").prop('checked')) {
        createDenpyo($salesOrderNo.text());
    };
    //注文書
    if ($("#order_frm").prop('checked')) {
        createNouhin($salesOrderNo.text());
    };

    registerReportPrint();
}