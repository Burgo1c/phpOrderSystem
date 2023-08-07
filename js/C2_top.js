$(document).ready(() => {
    fncSearch($("#searchBtn"), 1);

    $(".searchBtn").click(function () {
        fncSearch(this, 1);
    });

    $(document.body).on('click', '.btnEdit_icon', function () {
        dispSaleDialog(this.id);
    });
    $("input[type='tel']").keyup(async function () {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });

})

const fncSearch = async (el, pg) => {
    try {
        dispLoading("処理中...");

        $("#top_table .list,#users_sp .inner").empty();

        var frm = new FormData($(`#${$(el).attr("form")}`).get(0));
        //frm.append("sale_dt", todayDate());

        const res = await fetch(`${API_PATH}getSaleList&pagenum=${pg}`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        }

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            $("#top_table .list").html(`<tr><td colspan="5">${data.error}</td></tr>`)
            return;
        };

        //Page total & Total count
        const { total_page, count } = data[data.length - 1];

        //remove total & Total count
        data.pop();

        const list = $("#top_table .list");
        const sp = $("#users_sp .inner");
        data.forEach((obj) => {
            const tr = $(`
            <tr>
                <td class="w_50px">
                    <img src="/images/icon_edit.svg" class="btnEdit_icon" alt="詳細" id="${obj.order_no}" loading="lazy"/>
                </td>
                <td class="w_150px">${obj.sale_dt}</td>
                <td class="tal">${obj.tokuisaki_nm}</td>
                <td>${obj.order_no}</td>
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
                ${obj.sale_dt} | ${obj.tokuisaki_nm} | ${obj.user_nm}
            </dl>
            `);
            sp.append(dl);
        });

        /** PAGE NAVIGATION **/
        pagination(pg, total_page, count, $(el).attr("form"));
        pagination_sp(pg, total_page, $(el).attr("form"));
        $("#top_table, .pagenavi, .kensu").removeClass("disnon");

    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}