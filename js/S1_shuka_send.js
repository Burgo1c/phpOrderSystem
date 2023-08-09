$(document).ready(() => {
    $(".dt").val(todayDate());

    $(".btnSend").click(function () {
        shukaDataSend($(this).attr("form"));
    });

    $(".btnDownload").click(function () {
        downloadshukaDataLog($(this).attr("form"));
    });

    shukaDataLog();
});

/**
 * SFTP DATA TO YAMATO
 * @param {*} form 
 * @returns 
 */
const shukaDataSend = async (form) => {
    try {
        dispLoading("処理中...");

        $(".err_msg").text("").slideUp();

        var frm = new FormData($(`#${form}`)[0]);

        if (! await shukaDataCheck(frm)) return;

        const res = await fetch(`${API_PATH}shukaDataSend`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const type = res.headers.get("content-type");

        // If Error file exists
        if (type.indexOf("text/csv") !== -1) {
            const data = await res.blob();

            var link = document.createElement("a");
            link.href = window.URL.createObjectURL(data);
            link.download = `shuka_upload_error_${fileDateTime()}.csv`;
            link.text = 'shuka_upload_error.csv';
            // link.click();
            $("#err-file").append(link);
            $(".err").show();
            return;
        };

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            $(".err_msg").text(data.error).slideDown();
            console.log(data.error);
            return;
        };

        alert("出荷データ送信を完了しました。");

        shukaDataLog();
    } catch (err) {
        if(err.name.indexOf("SyntaxError") == -1){
            console.log(err);
            alert("サーバーでエラーが発生しました。");
        }else{
            alert("出荷データ送信を完了しました。");
            shukaDataLog();
        }
    } finally {
        removeLoading();
    }
};

/**
 * GET DATA HISTORY
 * @returns 
 */
const shukaDataLog = async () => {
    try {
        $(".log").empty();

        const res = await fetch(`${API_PATH}shukaDataLog`);

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            $(".log").append($('<option>', {
                value: "0",
                text: '送信データが存在しません。'
            }));
            return;
        };

        data.forEach(obj => {
            $(".log").append($('<option>', {
                value: obj.shuka_dt,
                text: `${obj.send_dt.substring(0, 19)} 件数 [${obj.kensu}]件 個数 [${obj.kosu}]個`
            }));
        });

    } catch (error) {
        console.log(error);
    }
};

/**
 * DOWNLOAD SEND DATA
 * @param {*} form 
 * @returns 
 */
const downloadshukaDataLog = async (form) => {
    try {
        dispLoading("処理中...");

        $(".err_msg").text("").slideUp();

        if($("#log").val() == 0) return;

        var frm = new FormData($(`#${form}`)[0]);

        const res = await fetch(`${API_PATH}downloadshukaDataLog`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const type = res.headers.get("content-type");

        //IF ERROR
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
        link.download = `YOTEI100`;
        link.click();
    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}

/**
 * CHECK SHUKA DATA EXIST
 * @param {*} form 
 */
const shukaDataCheck = async (form) => {
    try {

        const res = await fetch(`${API_PATH}shukaDataCheck`, { body: form, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return false;
        };

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            $(".err_msg").text(data.error).slideDown();
            console.log(data.error);
            return false;
        };

        if (data == 0) return true;

        if (!confirm("荷物受渡書と出荷日報の最新情報が出力されていません。\n出荷データを送信しますか？")) return false;

        return true;
    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
        return false;
    }
}