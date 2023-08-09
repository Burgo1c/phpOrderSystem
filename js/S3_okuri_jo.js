var _ajax = false;
var offset = 0;
var tableLength = 0;
var $searchFrm;
var $scrollTable;
$(document).ready(() => {
    $searchFrm = $("#tokuisakiSrchFrm");
    $scrollTable = $("#telTable tbody");
    /**
     * TEL LIVESEARCH
     */
    $(".tel").keyup(async function () {
        $(this).val($(this).val().replace(/[^0-9]/g, ""));
        $(".tokuisaki_nm").text("");
        $(".tokuisaki_cd").val("");

        if ($(this).val() == "") {
            $(".livesearch_row").slideUp();
            $(".tokuisaki_nm").parent().removeClass("bt");
            return;
        };

        offset = 0;
        tokuisakiTelList($(this).val());
    });
    /**
     * ON SCROLL GET NEXT 20
     */
    $(".livesearch").on("scroll", async function () {
        if (_ajax) return;
        const { scrollTop, scrollHeight, clientHeight } = this;
        if (scrollTop + clientHeight >= scrollHeight - 75) {
            _ajax = true;
            offset += 20;
            // user has scrolled to the 75%, load next 20
            tokuisakiTelList($(`#${$(this).attr('form')} .tel`).val());
        };
    })

    $(".tel").dblclick(function () {
        fncTokuisakiDialog();
    });

    /** SELECT TOKUISAKI ROW **/
    $searchFrm.on('dblclick', 'tr', function () {
        // GET tokuisaki
        getTokuisakiById(this.id);
        $("#tokuisaki-search").dialog("destroy");
    })

    /**
     * ROW SELECT
     */
    $(".livesearch_row").on('click', '.livesearch_tel', function () {
        $(".livesearch_row").slideUp();
        $(".tokuisaki_nm").parent().removeClass("bt");
        getTokuisakiById(this.id);
    });

    /**
     * PRINT YAMATO
     */
    $(".btnYamato").click(function () {
        printYamato($(this).attr("form"));
    })

    /**
     * PRINT SAGAWA
     */
    $(".btnSagawa").click(function () {
        printSagawa($(this).attr("form"));
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

const getTokuisakiById = async (id) => {
    try {

        const res = await fetch(`${API_PATH}getTokuisakiById&tokuisaki_cd=${id}`);

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const result = await res.json();

        if (result.hasOwnProperty("error")) {
            console.log(result.error);
            return;
        };

        $(".tokuisaki_cd").val(result.tokuisaki_cd);
        $(".tel").val(result.tokuisaki_tel)
        $(".tokuisaki_nm").text(result.tokuisaki_nm);

        $(".err_msg").slideUp();

    } catch (error) {
        console.log(error);
    };
}

const getTokuisakiByTel = async (tel) => {
    try {

        const res = await fetch(`${API_PATH}findTokuisakiByTel&tokuisaki_tel=${tel}`);

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const result = await res.json();

        if (result.hasOwnProperty("error")) {
            console.log(result.error);
            return;
        };

        $(".tel").val(result.tokuisaki_tel)
        $(".tokuisaki_nm").text(result.tokuisaki_nm);
    } catch (error) {
        console.log(error);
    };
};

const printYamato = async (form) => {
    try {
        dispLoading("処理中．．．");

        const frm = new FormData($(`#${form}`)[0]);

        if (frm.get("tokuisaki_cd") == "") {
            $(".err_msg").text("得意先を指定してください。").slideDown();
            return;
        }

        $(".err_msg").text("").slideUp();

        const res = await fetch(`${API_PATH}yamatoShipInvoice`, { body: frm, method: "POST" });

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
        link.download = `ヤマト送り状_${fileDateTime()}.pdf`;
        link.click();

    } catch (error) {
        console.log(error);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}

const printSagawa = async (form) => {
    try {
        dispLoading("処理中．．．");

        const frm = new FormData($(`#${form}`)[0]);

        if (frm.get("tokuisaki_cd") == "") {
            $(".err_msg").text("得意先を指定してください。").slideDown();
            return;
        }

        $(".err_msg").text("").slideUp();

        const res = await fetch(`${API_PATH}sagawaShipInvoice`, { body: frm, method: "POST" });

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
        link.download = `佐川急便送り状_${fileDateTime()}.pdf`;
        link.click();

    } catch (error) {
        console.log(error);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}

const fncTokuisakiDialog = () => {
    $("#tokuisakiSrchFrm input").val("");
    $("#tokuisakiSrchFrm table tbody").empty();
    $("#tokuisakiSrchFrm .frm-table").addClass("disnon");

    $("#tokuisaki-search").dialog({
        title: "得意先検索",
        modal: true,
        height: isIpad ? "auto" : isTouch ? screen.availHeight : 600,
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

const tokuisakiTelList = async (tel) => {
    try {
        const res = await fetch(`${API_PATH}telLiveSearch&tel=${tel}&offset=${offset}`, { method: "GET" });

        if (!res.ok) {
            $(".livesearch_row").slideUp();
            offset = 0;
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const data = await res.json();

        if (offset == 0 && data.length == 0) {
            $(".livesearch_row").slideUp();
            offset = 0;
            return;
        };

        var disp = true;
        var id;

        if (offset == 0) {
            $(".livesearch").empty();
        }

        data.forEach((obj) => {
            if (obj.tel_no == tel) {
                disp = false;
                id = obj.tokuisaki_cd;
                return;
            };
            $(".livesearch").append(`<li class="livesearch_tel" id="${obj.tokuisaki_cd}">${obj.tel_no}</li>`);
        });
        if (!disp) {
            $(".livesearch_row").slideUp();
            $(".tokuisaki_nm").parent().removeClass("bt");
            // GET tokuisaki
            getTokuisakiById(id);
            return;
        };
        $(".tokuisaki_nm").parent().addClass("bt");
        $(".livesearch_row").slideDown();
    } catch (error) {
        console.log(error);
        offset = 0;
    } finally {
        _ajax = false;
    }
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
        };

        const data = await res.json();

        if (data.hasOwnProperty('error')) {
            $("#tokuisaki-search .err_msg").text(data.error).slideDown();
            //alert(data.error);
            return;
        };

        tableLength = data.length;

        data.forEach((obj) => {
            var tr = $(`<tr id="${obj.tokuisaki_cd}"></tr>`);
            tr.append(`<td>${obj.tokuisaki_tel}</td>`);
            tr.append(`<td class="tal">${obj.tokuisaki_nm}</td>`);
            $("#tokuisakiSrchFrm table tbody").append(tr);
        })
        $("#tokuisakiSrchFrm .frm-table").removeClass("disnon");

    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    } finally {
        _ajax = false;
    }
}