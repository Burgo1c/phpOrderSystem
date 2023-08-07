$(document).ready(() => {
    saleDateSet("1");
    $(".btnPrint").click(function () {
        fncPrintDailyReport($(this).attr('form'));
    })

    $(".sale_kbn").on("change", function () {
        saleDateSet($(this).val());
    });
});

const fncPrintDailyReport = async (form) => {
    try {
        dispLoading("処理中...");

        const frm = new FormData($(`#${form}`)[0]);

        const file = (frm.get("daily_kbn") == "1") ? "得意先別" : "商品別";
        const saleKbn = frm.get("sale_kbn");
        const dateFrom = frm.get("date_from");
        const dateTo = frm.get("date_to");

        if (saleKbn === "4" && dateFrom === "") {
            // alert("売上日を選択してください。");
            $(".err_msg").text("売上日[開始]を選択してください。").slideDown();
            return;
        };

        if (saleKbn === "4" && dateTo === "") {
            // alert("売上日を選択してください。");
            $(".err_msg").text("売上日[終了]を選択してください。").slideDown();
            return;
        };

        if (saleKbn === "4" && dateFrom !== "" && dateTo !== "") {
            if (dateFrom > dateTo) {
                //  alert("売上日Fromは売上日Toより大きくなっています。");
                $(".err_msg").text("売上日[開始]が売上日[終了]より大きくなっています。").slideDown();
                return;
            }
        };

        $(".err_msg").text("").slideUp();
        const res = await fetch(`${API_PATH}reportPdf`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

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
        link.download = `${file}_${fileDateTime()}.pdf`;
        link.click();

    } catch (error) {
        console.log(error);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}

const saleDateSet = (val) => {
    switch (val) {
        case "1":
            $(".date_from,.date_to").val(todayDate()).prop('readonly', true);
            break;
        case "2":
            $(".date_from").val(thisMonthFrstDay()).prop('readonly', true);
            $(".date_to").val(thisMonthLastDay()).prop('readonly', true);
            break;
        case "3":
            $(".date_from").val(prevMonthFrstDay()).prop('readonly', true);
            $(".date_to").val(prevMonthLastDay()).prop('readonly', true);
            break;
        case "4":
            $(".date_from").val("").prop('readonly', false);
            $(".date_to").val("").prop('readonly', false);
            break;
        default:
            break;
    };
}