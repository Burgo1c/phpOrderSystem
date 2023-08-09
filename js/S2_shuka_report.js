$(document).ready(() => {
    $(".shuka_dt").val(todayDate());

    $(".btnPrint").click(function () {
        pdfPrint($(this).attr('form'));
    });

    $(".btnCsv").click(function () {
        createCsv($(this).attr("form"));
    });

    //締め回数
    shukaReportCount("uploadFrm_pc");
    $('input[name="print_flg"]').change(function () {
        shukaReportCount($(this).attr("form"));
    });
    $('select[name="customer_cd"]').change(function () {
        shukaReportCount($(this).attr("form"));
    });
    $('input[type="date"]').change(function () {
        shukaReportCount($(this).attr("form"));
    });

});

/**
 * MASTER PDF CREATE FUNCTION
 * @param {*} form The form id
 * @returns 
 */
const pdfPrint = async (form) => {
    if ($(`#${form} input[name="shuka_dt"]`).val() == "") {
        $(".err_msg").text("出荷日を選択してください。").slideDown();
        return;
    };

    const flg = $(`#${form} input[name="print_flg"]:checked`).val();
    if (flg != '0' && flg != '1') {
        $(".err_msg").text("発行区分を選択してください。").slideDown();
        return;
    };

    const ret = await statementOfDelivery(form);
    if (!ret) return;
    shukaReportData(form);

}

/**
 * OUTPUT 荷物受渡書 AS A PDF
 * @param {*} form The form id
 * @returns 
 */
const statementOfDelivery = async (form) => {
    try {
        dispLoading("処理中...");

        const frm = new FormData($(`#${form}`)[0]);

        $(".err_msg").text("").slideUp();

        const res = await fetch(`${API_PATH}statementOfDelivery`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return false;
        };

        const type = res.headers.get("content-type");

        // If Error
        if (type.indexOf("text/html") !== -1) {
            const data = await res.json();
            $(".err_msg").text(data.error).slideDown();
            console.log(data.error);
            return false;
        };

        const data = await res.blob();

        //DOWNLOAD
        var link = document.createElement("a");
        link.href = window.URL.createObjectURL(data);
        link.download = `荷物受渡書_${fileDateTime()}.pdf`;
        link.click();

        return true;
    } catch (error) {
        alert("サーバーでエラーが発生しました。");
        console.log(error);
    } finally {
        removeLoading();
    }
}

/**
 * OUTPUT 出荷日報 AS A PDF
 * @param {*} form The form id
 * @returns 
 */
const shukaReportData = async (form) => {
    try {
        dispLoading("処理中...");

        const frm = new FormData($(`#${form}`)[0]);

        $(".err_msg").text("").slideUp();

        const res = await fetch(`${API_PATH}shukaReportData`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const type = res.headers.get("content-type");

        // If Error
        if (type.indexOf("text/html") !== -1) {
            const data = await res.json();
            $(".err_msg").text(data.error).slideDown();
            console.log(data.error);
            return;
        };

        const data = await res.blob();

        //DOWNLOAD
        var link = document.createElement("a");
        link.href = window.URL.createObjectURL(data);
        link.download = `出荷日報_${fileDateTime()}.pdf`;
        link.click();
        //GET COUNT
        shukaReportCount(form);
    } catch (error) {
        alert("サーバーでエラーが発生しました。");
        console.log(error);
    } finally {
        removeLoading();
    }
}

/**
 * CREATE 出荷日報 CSV DATA
 * @param {*} form The form id
 * @returns 
 */
const createCsv = async (form) => {
    try {
        dispLoading("処理中...");

        const frm = new FormData($(`#${form}`)[0]);

        $(".err_msg").text("").slideUp();

        const res = await fetch(`${API_PATH}shukaReportCsv`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const type = res.headers.get("content-type");

        // If Error
        if (type.indexOf("text/html") !== -1) {
            const data = await res.json();
            $(".err_msg").text(data.error).slideDown();
            console.log(data.error);
            return;
        };

        const data = await res.blob();

        //DOWNLOAD
        var link = document.createElement("a");
        link.href = window.URL.createObjectURL(data);
        link.download = `出荷日報_${fileDateTime()}.csv`;
        link.click();

    } catch (error) {
        console.error(error);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}

/**
 * GET THE PRINT COUNT
 * @param {*} form The form id
 * @returns 
 */
const shukaReportCount = async (form) => {
    try {
        const frm = new FormData($(`#${form}`)[0]);

        $(".err_msg").text("").slideUp();

        const res = await fetch(`${API_PATH}shukaReportCount`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const data = await res.json();

        // if (data === null) {
        //     $(".count option").remove();
        //     switch (frm.get("print_flg")) {
        //         case "0":
        //             //発行
        //             $(".count").append($('<option>', {
        //                 value: 1,
        //                 text: '1回目'
        //             }));
        //             break;
        //         case "1":
        //             //再発行
        //             $(".count").append($('<option>', {
        //                 value: -1,
        //                 text: '全て'
        //             }));
        //             break;
        //     };
        //     return;
        // }

        if (data.hasOwnProperty("error")) {
            console.log(data.error);
            $(".err_msg").text(data.error).slideDown();
            return;
        };

        $(".count option").remove();

        switch (frm.get("print_flg")) {
            case "0":
                //発行
                if (data[0].max === 0 || data[0].max === null) {
                    $(".count").append($('<option>', {
                        value: 1,
                        text: '1回目'
                    }));
                } else {
                    $(".count").append($('<option>', {
                        value: parseInt(data[0].max) + 1,
                        text: `${parseInt(data[0].max) + 1}回目`
                    }));
                };
                break;
            case "1":
                //再発行
                $(".count").append($('<option>', {
                    value: -1,
                    text: '全て'
                }));
                //if(data[0].cnt == 0) return;
                data.forEach(obj => {
                    $(".count").append($('<option>', {
                        value: parseInt(obj.cnt),
                        text: `${obj.cnt}回目`
                    }));
                });
                break;
        }

    } catch (error) {
        console.error(error);
    }
}