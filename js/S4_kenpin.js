var offset = 0;
var _ajax = false;
var tableLength = 0;
var LIST_CNT;
var msg = "";

$(document).ready(() => {
    //CACHE OBJECTS
    window.shukaDt = $("#shuka_dt");
    window.inquireNo = $("#inquire_no");
    window.errorEle = $(".error");

    LIST_CNT = Number($("#list_cnt").val());

    // shukaDt.text(todayDate().replace(/-/g, "/"));
    shukaDt.val(todayDate());

    shukaDt.on('change', () => {
        shukaReportCount();
        getKenpinCount();
        getKenpinList();
    })

    shukaReportCount();
    getKenpinCount();
    getKenpinList();


    $("#searchBtn").click(() => {
        getKenpinCount();
        getKenpinList();
    });

    $(".list").on('scroll', async function () {
        if (_ajax || tableLength < LIST_CNT) return;

        const { scrollTop, scrollHeight, clientHeight } = this;
        if (scrollTop + clientHeight >= scrollHeight - 75) {
            offset += LIST_CNT;
            // user has scrolled to 75%, load next 50
            getKenpinList();
        };
    });

    inquireNo.on('keyup', async function (e) {
        errorEle.text("").hide();

        if (e.which == 13) {
            getKenpinDetail(this.value);
        };

        this.value = this.value.replace(/[a ]/g, "");
    })

    $(document.body).on('click', '.btnEdit_icon', function () {
        dispSaleDialog(this.id);
    });

    $(document.body).on('click', '.kenpinBtn', function () {
        if (!confirm(`問合せ番号[${this.id}]を検品しますか？`)) return;
        inquireNo.val(this.id);
        getKenpinDetail(this.id);
    });
});


const getKenpinList = async () => {
    try {
        _ajax = true;
        subLoading($(".table-wrapper"));
        $(".table-wrapper").find("#subLoading").css({ "top": 0, "position": "absolute" });

        //$(".kenpin span").text('');
        errorEle.text('').hide();

        if (shukaDt.val() == "") {
            errorEle.text('出荷日を選択してください。').slideDown();
            shukaDt.focus();
            return;
        }

        const list = $(".list");
        if (offset == 0) {
            list.empty();
        }

        const frm = new FormData($("#kenpinSearch")[0]);

        frm.append("offset", offset);
        frm.append("shuka_dt", shukaDt.val());

        const res = await fetch(`${API_PATH}kenpinList`, { body: frm, method: "POST" });

        if (!res.ok) return;

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            console.log(data.error);
            if (offset == 0) {
                const tr = $(`
                <tr>
                    <td colspan="9" class="tac">${data.error}</td>
                </tr>
            `);
                list.append(tr);
            }

            // $("#complete_qty").text(0);
            // $("#complete_kosu").text(0);
            // $("#kenpin_qty").text(0);
            // $("#kenpin_kosu").text(0);
            return;
        }

        const rows = data.rows;
        tableLength = rows.length;
        rows.forEach((obj) => {
            const tr = $(`
                    <tr>
                        <td>
                            <img src="/images/icon_edit.svg" class="btnEdit_icon" alt="詳細" id="${obj.order_no}" loading="lazy"/>
                        </td>
                        <td>
                            <button type="button" class="subBtn kenpinBtn" id="${obj.inquire_no}">検品</button>
                        </td>
                        <td>${obj.inquire_no}</td>
                        <td class="">${obj.order_no}</td>
                        <td>${obj.okurisaki_nm}</td>
                        <td>${obj.okurisaki_adr_1}${obj.okurisaki_adr_2}${obj.okurisaki_adr_3}</td>
                        <td>${obj.okurisaki_tel}</td>
                        <td>${obj.kosu}</td>
                        <td>${obj.kenpin_kbn}</td>
                        <td>${obj.shuka_print_qty}</td>
                    </tr>
                `);
            list.append(tr);
        });

        // $("#complete_qty").text(data.complete_qty);
        // $("#complete_kosu").text(data.complete_kosu);
        // $("#kenpin_qty").text(data.kenpin_qty);
        // $("#kenpin_kosu").text(data.kenpin_kosu);

    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeSubLoading($(".table-wrapper"));
        _ajax = false;
    }
}

const getKenpinCount = async () => {
    try {

        const frm = new FormData($("#kenpinSearch")[0]);
        frm.append("shuka_dt", shukaDt.val());

        const res = await fetch(`${API_PATH}kenpinCount`, { body: frm, method: "POST" });

        if (!res.ok) return;

        const data = await res.json();

        $("#complete_qty").text(data.complete_qty);
        $("#complete_kosu").text(data.complete_kosu);
        $("#kenpin_qty").text(data.kenpin_qty);
        $("#kenpin_kosu").text(data.kenpin_kosu);
    } catch (err) {
        console.log(err);
    }
}

const getKenpinDetail = async (inquire_no) => {
    try {
        errorEle.text("").hide();
        subLoading($(".kenpin"));
        $(".kenpin").find("#subLoading").css({ "top": -30, "position": "absolute" });

        if (shukaDt.val() == "") {
            errorEle.text('出荷日を選択してください。').slideDown();
            shukaDt.focus();

            $("#order_no, #sale_dt, #kosu, #delivery_kbn, #sale_kbn, #okurisaki_nm, #okurisaki_tel, #address").text("");
            return;
        }

        if (inquireNo.val() == "") {
            errorEle.text('問合せ番号を入力してください。').slideDown()
            inquireNo.focus();

            $("#order_no, #sale_dt, #kosu, #delivery_kbn, #sale_kbn, #okurisaki_nm, #okurisaki_tel, #address").text("");
            return;
        }

        const res = await fetch(`${API_PATH}kenpinDetail&inquire_no=${inquire_no}&shuka_dt=${shukaDt.val()}`);

        if (!res.ok) return;

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            console.log(data.error);
            errorEle.text(data.error).slideDown();
            inquireNo.val("").focus();
            $("#order_no, #sale_dt, #kosu, #delivery_kbn, #sale_kbn, #okurisaki_nm, #okurisaki_tel, #address").text("");
            $(".content").scrollTop(0);
            return;
        };

        for (const [key, value] of Object.entries(data)) {
            $(`#${key}`).text(value);
        }

        await kenpinCheck();

        if (msg !== "") {
            dispCheckDialog();
            return;
        };

        kenpinUpdate();
    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeSubLoading($(".kenpin"));
    }
}

const kenpinCheck = async () => {
    try {
        msg = "";
        errorEle.text("").hide();

        const res = await fetch(`${API_PATH}kenpinCheck&order_no=${$(`#order_no`).text()}`);

        if (!res.ok) return;

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            console.log(data.error);
            errorEle.text(data.error).slideDown();
            inquireNo.val("").focus();
            $("#order_no, #sale_dt, #kosu, #delivery_kbn, #sale_kbn, #okurisaki_nm, #okurisaki_tel, #address").text("");
            $(".content").scrollTop(0);
            return;
        };

        if (data !== "OK") {
            msg = data;
        }

        return;
    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    }
}

const kenpinUpdate = async () => {
    try {

        errorEle.text("").hide();

        const res = await fetch(`${API_PATH}kenpinUpdate&order_no=${$(`#order_no`).text()}`);

        if (!res.ok) return;

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            console.log(data.error);
            errorEle.text(data.error).slideDown();
            $(".content").scrollTop(0);
            inquireNo.focus();
            return;
        }

        offset = 0;
        //getKenpinCount();
        alert("検品に成功しました。");
        getKenpinList();
        getKenpinCount();
    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    } finally {
        inquireNo.val("");
    }
}

const shukaReportCount = async () => {
    try {
        const res = await fetch(`${API_PATH}kenpinShukaCount&shuka_dt=${shukaDt.val()}`);

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            console.log(data.error);
            //errorEle.text(data.error).slideDown();
            return;
        };

        $("#shuka_print_qty option").remove();

        $("#shuka_print_qty").append($('<option>', {
            value: "all",
            text: '全て'
        }));

        data.forEach((obj) => {
            $("#shuka_print_qty").append($('<option>', {
                value: parseInt(obj.cnt),
                text: `${obj.cnt}回目`
            }));
        })

    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    };
}

const dispCheckDialog = async () => {
    $(".checkMsg").text(msg);
    $("#checkDialog").dialog({
        title: "通常検品確認",
        height: isTouch ? 600 : 275,
        width: isTouch ? screen.availWidth - 8 : 600,
        maxHeight: $("body").height(),
        resizable: false,
        draggable: false,
        buttons: [
            {
                text: "確認",
                class: "btn-edit",
                click: async function() {
                    kenpinUpdate();
                    $(this).dialog("destroy");
                },
            },
            {
                text: "キャンセル",
                class: "btn-close btn-cancel",
                click: function () {
                    $(this).dialog("destroy");
                },
            },
        ]
    })
}