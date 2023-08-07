const f = new Intl.NumberFormat();

$(document).ready(() => {

  /**
  * DETAIL ・ 詳細
  */
  $(document.body).on('click', '.btnEdit_icon, .link', function () {
    fncDetail(this.id);
  })

  $(".btnSearch").on("click", function () {
    fncSearch(this, 1);
  });

  $(".btnAdd").on("click", () => {
    fncDispAdd();
  });

  $("#btnPrint").click(() => {
    fncDispPrint();
  });

  $("input[type='tel']").keyup(function () {
    $(this).val($(this).val().replace(/[^0-9]/g, ''));
  })

  /** AJAX TOKUISAKI SEARCH **/
  $("#editFrm #tokuisaki_tel").keyup(async function () {
    // $(this).val($(this).val().replace(/[^0-9]/g, ''));

    if ($(this).val() == "") {
      $("#editFrm #tokuisaki_cd").val("");
      $("#editFrm #tokuisaki_nm").text("");
      return;
    };

    let data = await tokuisakiTelSearch($(this).val());

    if (!data) {
      $("#editFrm #tokuisaki_cd").val("");
      $("#editFrm #tokuisaki_nm").text("");
      return;
    };

    $("#editFrm #tokuisaki_cd").val(data.tokuisaki_cd);
    $("#editFrm #tokuisaki_nm").text(data.tokuisaki_nm);
  });

  $("#printFrm #tokuisaki_tel").keyup(async function () {
    // $(this).val($(this).val().replace(/[^0-9]/g, ''));

    if ($(this).val() == "") {
      $("#printFrm #tokuisaki_nm").text("");
      return;
    }

    let data = await tokuisakiTelSearch($(this).val());

    if (!data) {
      $("#printFrm #tokuisaki_nm").text("");
      return;
    };


    $("#printFrm #tokuisaki_nm").text(data.tokuisaki_nm);
  });

  /** AJAX PRODUCT SEARCH **/
  $("#editFrm #product_cd").keyup(function () {

    if ($(this).val() == "") return;

    productSubSearch($(this).val());
  });

  $(document.body).on('keyup', ".inputError", function () {
    if (this.value == "") return;
    $(this).removeClass("inputError");
    $(this).parent().prev().removeClass("inputError");
  })

});

/**
 * SEARCH FUNCTION
 * @param {*} el SEARCH BUTTON ELEMENT
 * @param {*} pg PAGENUM
 * @returns 
 */
const fncSearch = async (el, pg) => {
  try {

    var frm = new FormData($(`#${$(el).attr("form")}`).get(0));

    dispLoading("処理中...");
    $("article .inner").empty();
    $(".pagenavi, .kensu").addClass("disnon");

    const res = await fetch(`${API_PATH}customerProductList&pagenum=${pg}`, { body: frm, method: "POST" });

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
      <table class="" id="tokuisaki_shohin_table">
        <thead>
            <tr>
                <th class="w_50px">詳細</th>
                <th class="sort desc">代表電話番号</th>
                <th class="sort desc">得意先名</th>
                <th class="sort desc">商品コード</th>
                <th class="sort desc">商品名</th>
                <th class="sort desc">商品分類</th>
                <th class="sort desc">売上単位</th>
                <th class="sort desc">売上単価</th>
                <th class="sort desc">仕入単価</th>
            </tr>
        </thead>
        <tbody class="list"></tbody>
      </table>
    `))

    const list = $("#tokuisaki_shohin_table .list");
    const sp = $("#users_sp .inner");
    data.forEach((obj) => {
      const tr = $(`
      <tr>
        <td class="w_50px">
          <img src="/images/icon_edit.svg" class="btnEdit_icon" id="${obj.tokuisaki_cd}_${obj.product_cd}" alt="詳細" loading="lazy"/>
        </td>
        <td>${obj.tokuisaki_tel}</td>
        <td class="">${obj.tokuisaki_nm}</td>
        <td>${obj.product_cd}</td>
        <td>${obj.product_nm}</td>
        <td>${obj.product_type}</td>
        <td>${obj.sale_tani}</td>
        <td class="tar">${f.format(obj.sale_price)}</td>
        <td class="tar">${f.format(obj.unit_price)}</td>
      </tr>
      `);
      list.append(tr);

      /** PHONE TABLE VIEW **/
      var dl = $(`
      <dl class="itemList_sp">
        <a class="link" id="${obj.tokuisaki_cd}_${obj.product_cd}"></a>
        <dt>${obj.tokuisaki_nm}</dt>
        ${obj.product_nm} | ${obj.product_type} | ${f.format(obj.sale_price)}
      </dl>
      `);
      sp.append(dl);
    });

    /** PAGE NAVIGATION **/
    pagination(pg, total_page, count, $(el).attr("form"));
    pagination_sp(pg, total_page, $(el).attr("form"));

    $(".pagenavi, .btnBlock, .kensu").removeClass("disnon");
    $("#input").prop("checked", false);

  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading();
  }
}

/**
 * ADD DIALOG
 */
const fncDispAdd = () => {
  addInit();
  $("#dialog").dialog({
    title: "得意先別商品登録",
    modal: true,
    height: isTouch ? screen.availHeight * 0.8 : 620,
    maxHeight: $("body").height(),
    width: isTouch ? screen.availWidth - 10 : 550,
    buttons: [
      {
        text: "登録",
        class: "btn-edit",
        click: async function () {
          try {
            dispLoading("処理中．．．");

            if (!inputCheck()) return;

            var frm = new FormData($("#editFrm").get(0));

            const res = await fetch(`${API_PATH}customerProductAdd`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            };

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              $(".err_msg").text(data.error).slideDown();
              console.log(data.error);
              return;
            };

            $(this).dialog("destroy");
            $("#frmSearch_pc .btnSearch").click();

          } catch (err) {
            console.log(err);
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
 * DETAIL DIALOG
 * @param {*} param 
 */
const fncDetail = async (param) => {
  try {
    
    dispLoading("処理中．．．");
 
    var id = param.split("_");

    if (id[0] == "" || id[1] == "") {
      alert("対象となるデータを見つかりません。");
      return;
    };

    const res = await fetch(`${API_PATH}customerProductDetail&tokuisaki_cd=${id[0]}&product_cd=${id[1]}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      alert(data.error);
      console.log(data.error);
      return;
    };
    $("#editFrm label#tokuisaki_tel").text(data.tokuisaki_tel);
    $("#editFrm input#tokuisaki_tel").val(data.tokuisaki_tel);
    $("#editFrm #tokuisaki_cd").val(data.tokuisaki_cd);
    $("#editFrm #tokuisaki_nm").text(data.tokuisaki_nm);

    $("#editFrm label#product_cd").text(data.product_cd);
    $("#editFrm input#product_cd").val(data.product_cd);
    $("#editFrm #product_nm").text(data.product_nm);
    $("#editFrm #product_nm_abrv").text(data.product_nm_abrv);
    $("#editFrm #product_type").text(data.product_type);
    $("#editFrm #sale_tani").text(data.sale_tani);
    $("#editFrm #sale_price").val(data.sale_price);
    $("#editFrm #unit_price").val(data.unit_price);

    fncDispEdit();

  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading();
  }
}

const tokuisakiTelSearch = async (val) => {
  try {
    const res = await fetch(`${API_PATH}findTokuisakiByTel&tokuisaki_tel=${val}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      return false;
    };

    return data;

  } catch (err) {
    console.log(err);
  }
};

const productSubSearch = async (val) => {
  try {
    const res = await fetch(`${API_PATH}productSubSearch&product_cd=${val}`);

    if (!res.ok) return;

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      $("#editFrm #product_nm").text("");
      $("#editFrm #product_nm_abrv").text("");
      $("#editFrm #product_type").text("");
      $("#editFrm #sale_tani").text("");
      $("#editFrm #sale_price").val("");
      $("#editFrm #unit_price").val("");
      console.log(data.error);
      return;
    };
    $("#editFrm #product_nm").text(data.product_nm);
    $("#editFrm #product_nm_abrv").text(data.product_nm_abrv);
    $("#editFrm #product_type").text(data.product_type);
    $("#editFrm #sale_tani").text(data.sale_tani);
    $("#editFrm #sale_price").val(data.sale_price);
    $("#editFrm #unit_price").val(data.unit_price);

  } catch (err) {
    console.log(err);
  }
}

const inputCheck = () => {
  var isValid = true;
  $(".inputError").removeClass("inputError");
  $(".err_msg").text("").hide();

  $("#editFrm input[required]").each(function () {
    if (!$(this).val()) {
      $(this).addClass("inputError").focus();
      $(this).parent().prev().addClass("inputError");
      $(".err_msg").text(`${$(this).parent().prev().text()}を入力してください。`).slideDown();
      return isValid = false;
    }
  });

  if (!isValid) return;

  if (!$("#editFrm #tokuisaki_nm").text()) {
    $("#editFrm #tokuisaki_tel").addClass("inputError").focus();
    $("#editFrm #tokuisaki_tel").parent().prev().addClass("inputError");
    $(".err_msg").text(`得意先を入力してください。`).slideDown();
    return isValid = false;
  }

  if (!isValid) return;

  if (!$("#editFrm #product_nm").text()) {
    $("#editFrm #product_cd").addClass("inputError").focus();
    $("#editFrm #product_cd").parent().prev().addClass("inputError");
    $(".err_msg").text(`商品を入力してください。`).slideDown();
    return isValid = false;
  }

  return isValid;
}

const fncDispEdit = () => {
  editInit();
  $("#dialog").dialog({
    title: "得意先別商品詳細",
    modal: true,
    height: isTouch ? screen.availHeight * 0.8 : 620,
    maxHeight: $("body").height(),
    width: isTouch ? screen.availWidth - 10 : 700,
    buttons: [
      {
        text: "編集",
        class: "btn-edit",
        click: function () {
          $(".btn-edit").css("display", "none");
          $(".itemEdit input, .itemEdit option").prop("disabled", false);
          $(".btn-update").css("display", "inline-block");
          $(".btn-delete").css("display", "inline-block");
        },
      },
      {
        text: "更新",
        class: "btn-update",
        click: async function () {
          try {
            dispLoading("処理中．．．");

            if (!inputCheck());

            var frm = new FormData($("#editFrm").get(0));

            const res = await fetch(`${API_PATH}customerProductUpdate`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            };

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              console.log(data.error);
              $(".err_msg").text(data.error).slideDown();
              return;
            };

            $(this).dialog("destroy");
            $("#frmSearch_pc .btnSearch").click();

          } catch (err) {
            console.log(err);
            alert("サーバーでエラーが発生しました。");
          } finally {
            removeLoading();
          }
        },
      },
      {
        text: "削除",
        class: "btn-delete",
        click: async function () {
          try {
            dispLoading("処理中．．．");

            if(!confirm("得意先別商品を削除してよろしでしょうか？")) return;

            if (!inputCheck());

            var frm = new FormData($("#editFrm").get(0));

            const res = await fetch(`${API_PATH}customerProductDelete`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            };

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              console.log(data.error);
              $(".err_msg").text(data.error).slideDown();
              return;
            };

            $(this).dialog("destroy");
            $("#frmSearch_pc .btnSearch").click();

          } catch (err) {
            console.log(err);
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
const fncDispPrint = () => {
  $("#printFrm input").val("").prop("disabled", false);
  $(".err_msg").text("").slideUp();
  $("#printFrm #tokuisaki_nm").text("");
  $("#tokuisaki_print").dialog({
    title: "得意先別商品台帳印刷",
    modal: true,
    height: 400,
    maxHeight: $("body").height(),
    width: isTouch ? screen.availWidth - 10 : 450,
    minWidth: 300,
    buttons: [
      {
        text: "印刷",
        class: "btn-edit",
        click: async function () {
          try {
            dispLoading("処理中...");

            var frm = new FormData($("#printFrm").get(0));

            if(frm.get("tokuisaki_tel") === ""){
              $(".err_msg").text("代表電話番号を入力してください。").slideDown();
              return;
            }

            if (frm.get("product_from") != "" && frm.get("product_to") != "") {

              if (parseInt(frm.get("product_from")) > parseInt(frm.get("product_to"))) {
                $(".err_msg").text("商品コード[開始]の値が商品コード[終了]の値より大きくなっています").slideDown();
                return;
              };

            }

            const res = await fetch(`${API_PATH}customerProductPdf`, { body: frm, method: "POST" });

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
            link.download = `得意先別商品台帳_${fileDateTime()}.pdf`;
            link.click();

            $(this).dialog("destroy");
          } catch (err) {
            console.log(err);
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
  })
}
