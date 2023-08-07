var _ajax = false;
var offset = 0;
var tableLength = 0;
var $scrollTable;
var $searchFrm;

$(document).ready(() => {
    $searchFrm = $("#tokuisakiSrchFrm");
    $scrollTable = $("#telTable tbody");
    //SET date to current month
    // $(".invoice_dt").val(thisMonth());
    // $(".bill_dt").val(lastDayOfMonth());
    // $(".dt_from").val(thisMonthFrstDay());
    // $(".dt_to").val(thisMonthLastDay());

    // $("input").on('input', function(){
    //     $(this).closest("dl").css("color","black");
    // })

    //Only number inputs
    $(".bill_dt").keyup(function () {
        $(".err_msg").hide();

        $(this).val($(this).val().replace(/[^0-9]/g, ''));

        if ($(this).val() == "") return;

        if ($(this).val() > 31) {
            //$(this).val(31);
            $(".err_msg").text("締日は1～31の間で入力してください。").slideDown();
            return;
        };

        if ($(this).val() < 1) {
            //$(this).val(1);
            $(".err_msg").text("締日は1～31の間で入力してください。").slideDown();
            return;
        }
        // setKikanTo($(this).val());
    });

    $(".btnPrint").click(function () {
        let form = $(this).attr('form');

        if (!formCheck(form)) return;

        const frm = new FormData($(`#${form}`)[0]);

        if ($(`#${form} #invoice`).prop('checked')) {
            createInvoicePdf(frm);
        };

        if ($(`#${form} #urikake`).prop('checked')) {
            createAccountsRecievablePdf(frm);
        };
    });

    $(".subBtn").click(() => {
        fncDispTokuisakiSearch();
    });

    $("#tokuisakiSrchFrm").on('dblclick', "tbody tr", function () {
        //$(".tokuisaki_cd").val(this.id);
        $(".tokuisaki_tel").val($(this).find("td:first-child").text());
        $("#tokuisaki-search").dialog("destroy");
    });
    $(".tokuisaki_tel").keyup(function () {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
        // if($(this).val() == ""){
        //     $(".tokuisaki_cd").val("");
        // };
    });


    /**
     * TABLE BODY SCROLL GET NEXT 50 ROWS
     */
    $scrollTable.on('scroll', async function () {
        if (_ajax || tableLength < 50) return;

        const { scrollTop, scrollHeight, clientHeight } = this;
        if (scrollTop + clientHeight >= scrollHeight - 75) {

            offset += 50;
            // user has scrolled to the 75%, load next 50
            tokuisakiSearch();
        };
    });

});

const formCheck = (form) => {
    if (!$(`#${form} #invoice`).prop('checked') && !$(`#${form} #urikake`).prop('checked')) {
        //$(`#${form} dl:first-child`).css("color","red");
        $(".err_msg").text("発行伝票を選択してください。").slideDown();
        return false;
    };

    // if ($(`#${form} #invoice_dt`).val() == "") {
    //     $(".err_msg").text("日付を選択してください。").slideDown();
    //     $(`#${form} #invoice_dt`).focus();
    //     return false;
    // };

    if ($(`#${form} #bill_dt`).val() == "") {
        //$(`#${form} dl:nth-child(2)`).css("color","red");
        $(".err_msg").text("締日を入力してください。").slideDown();
        $(`#${form} #bill_dt`).focus();
        return false;
    };

    if (parseInt($(`#${form} #bill_dt`).val()) < 1 || parseInt($(`#${form} #bill_dt`).val()) > 31) {
        //$(`#${form} dl:nth-child(2)`).css("color","red");
        $(".err_msg").text("締日の入力が不正です。").slideDown();
        $(`#${form} #bill_dt`).focus();
        return false;
    };

    //if ($(`#${form} #urikake`).prop('checked')) {
        if ($(`#${form} #dt_from`).val() == "") {
            //$(`#${form} dl:nth-child(3)`).css("color","red");
            $(".err_msg").text("期間[開始]を入力してください。").slideDown();
            $(`#${form} #dt_from`).focus();
            return false;
        };

        if ($(`#${form} #dt_to`).val() == "") {
           // $(`#${form} dl:nth-child(3)`).css("color","red");
            $(".err_msg").text("期間[終了]を入力してください。").slideDown();
            $(`#${form} #dt_to`).focus();
            return false;
        };

        if ($(`#${form} #dt_from`).val() > $(`#${form} #dt_to`).val()) {
            //$(`#${form} dl:nth-child(3)`).css("color","red");
            $(".err_msg").text("期間[開始]は期間[終了]より大きくなっています。").slideDown();
            $(`#${form} #dt_to`).focus();
            return false;
        };
    //};

    $(".err_msg").text("").slideUp();
    return true;
}

const createInvoicePdf = async (form) => {
    try {
        dispLoading("処理中...");

        const res = await fetch(`${API_PATH}invoicePdf`, { body: form, method: "POST" });

        if(!res.ok){
            alert("ネットワークエラーが発生しました。");
            return;
        }

        const type = res.headers.get("content-type");

        // If Error
        if (type.indexOf("text/html") !== -1) {
            const data = await res.json();
            $(".err_msg").text(data.error).slideDown();
            console.log(data);
            return;
        };

        const data = await res.blob();

        //DOWNLOAD
        var link = document.createElement("a");
        link.href = window.URL.createObjectURL(data);
        link.download = `請求書_${fileDateTime()}.pdf`;
        link.click();

    } catch (error) {
        console.error(error);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}

const createAccountsRecievablePdf = async (form) => {
    try {
        dispLoading("処理中...");

        const res = await fetch(`${API_PATH}accountsRecievablePdf`, { body: form, method: "POST" });
        
        if(!res.ok){
            alert("ネットワークエラーが発生しました。");
            return;
        }

        const type = res.headers.get("content-type");

        // If Error
        if (type.indexOf("text/html") !== -1) {
            const data = await res.json();
            $(".err_msg").text(data.error).slideDown();
            console.log(data);
            return;
        };

        const data = await res.blob();

        //DOWNLOAD
        var link = document.createElement("a");
        link.href = window.URL.createObjectURL(data);
        link.download = `売掛金元帳_${fileDateTime()}.pdf`;
        link.click();
    } catch (error) {
        console.error(error);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}

// const findTokuisakiByTel = async (tel) => {
//     try{
//         const res = await fetch(`../php/pcService.php?Type=findTokuisakiByTel&tokuisaki_tel=${tel}`);

//         if(!res.ok) return;

//         const data = await res.json();

//         if(data.hasOwnProperty("error")){
//             console.error(data.error);
//             return;
//         };


//     }catch(error){  
//         console.error(error);
//     }
// }
// const getBillDate = async () => {
//     try {
//         $(".bill_dt").empty();

//         const res = await fetch('../php/pcService.php?Type=tokuisakiBillDate');

//         if (!res.ok) return;

//         const data = await res.json();

//         if (data.hasOwnProperty("error")) {
//             const [year, month] = $(`#printFrm_pc #bill_dt`).val().split('-');
//             const lastDayOfMonth = new Date(year, month, 0).getDate();
//             $(".bill_dt").append($("option", {
//                 val: lastDayOfMonth,
//                 text: lastDayOfMonth
//             }));
//             console.error(error);
//             return;
//         };

//         array.forEach(obj => {
//             $(".bill_dt").append($("option", {
//                 val: obj.bill_dt,
//                 text: obj.bill_dt
//             }));
//         });

//     } catch (error) {
//         console.error(error);
//     }
// }

const setKikanTo = (bill_dt) => {
    const [year, month, day] = $(`#printFrm_pc .dt_to`).val().split('-');
    const lastDayOfMonth = new Date(year, month, 0).getDate();

    if (bill_dt == 0 || bill_dt > lastDayOfMonth) {
        $(".err_msg").text("締日の入力が不正です。").slideDown();
        return false;
    }

    var date = new Date(year, month, bill_dt);

    var dt_to = `${date.getFullYear()}-${date.getMonth().toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')}`
    $(".dt_to").val(dt_to);

    $(".err_msg").text("").slideUp();
};

// const checkDate = (date, bill_dt) => {
//     const [year, month] = date.split('-');
//     const lastDayOfMonth = new Date(year, month, 0).getDate();

//     if (bill_dt > lastDayOfMonth) {
//         $(".err_msg").text("締日の入力が不正です。").slideDown();
//         return false;
//     }

//     return true;
// }

const lastDayOfMonth = () => {
    var year = new Date().getFullYear();
    var month = new Date().getMonth();
    var date = new Date(year, month + 1, 0);
    var dayStr = date.getDate();

    return dayStr;
}

/**
 * TOKUISAKI SEARCH DIALOG
 */
const fncDispTokuisakiSearch = async () => {
    $("#tokuisakiSrchFrm input").val("");
    $("#tokuisakiSrchFrm table tbody").empty();
    $("#tokuisakiSrchFrm .frm-table").addClass("disnon");
    $("#tokuisaki-search .err_msg").text("").hide();
    $("#tokuisaki-search").dialog({
        title: "得意先検索",
        modal: true,
        height: 600,
        width: isTouch ? screen.availWidth - 8 : 600,
        maxHeight: $("body").height(),
        buttons: [
            {
                text: "検索",
                class: "btn-edit",
                click: async () => {
                    offset = 0;
                    if (_ajax) return;
                    tokuisakiSearch();
                },
            },
            {
                text: "閉じる",
                class: "btn-close",
                click: function () {
                    $("#tokuisakiFrm .frm-table").addClass("disnon");
                    $(this).dialog("destroy");
                },
            },
        ],
    });
}

const tokuisakiSearch = async () => {
    try {
        _ajax = true;
        if (offset == 0) {
            $("#tokuisakiSrchFrm table tbody").empty();
            $("#tokuisaki-search .err_msg").text("").slideUp();
        }

        const frm = new FormData($searchFrm[0]);

        const res = await fetch(`${API_PATH}tokuisakiSubSearch&offset=${offset}`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        }

        const data = await res.json();

        if (data.hasOwnProperty('error')) {
            $("#tokuisaki-search .err_msg").text(data.error).slideDown();
            //alert(data.error);
            return;
        };

        tableLength = data.length;

        const list = $("#tokuisakiSrchFrm table tbody");
        data.forEach((obj) => {
            const tr = $(`
            <tr id="${obj.tokuisaki_cd}">
                <td>${obj.tokuisaki_tel}</td>
                <td class="tal">${obj.tokuisaki_nm}</td>
            </tr>
            `);
            list.append(tr);
        })
        $("#tokuisakiSrchFrm .frm-table").removeClass("disnon");

    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    } finally {
        _ajax = false;
    }
}