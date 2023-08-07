$(document).ready(() => {

  /**
  * DETAIL ・ 詳細
  */
  $(document.body).on('click', '.btnEdit_icon, .link', function () {
    fncDetail(this.id);
  })

  /**
   * SEARCH
   */
  $(".btnSearch").on("click", function () {
    fncSearch(this, 1);
  });

  /**
   * ADD
   */
  $(".btnAdd").on("click", () => {
    fncDispAdd();
  });

  /**
   * 印刷
   */
  $("#btnPrint").click(() => {
    fncDispPrint();
  });

  $(".num").keyup(function () {
    $(this).val($(this).val().replace(/\D/g, ""));
  })

  /** 商品名略称（自動設定） **/
  $("#product_nm").on('input', function () {
    $("#product_nm_abrv").val($(this).val().substring(0, 10));
  })

  $(document.body).on('keyup', ".inputError", function () {
    if (this.value == "") return;
    $(this).removeClass("inputError");
    $(this).parent().prev().removeClass("inputError");
  })

  /**
   * 2023/07/11
   * 追加
   */

  //空き番リスト
  $("#btnCodeList").click(() => {
    dispCodeList();
  });

  $("#codeListDialog #sort").on('click', async function () {
    codeOrderToggle();
    getCodeList();
  });

});

const fncSearch = async (el, pg) => {
  try {

    var frm = new FormData($(`#${$(el).attr("form")}`).get(0));
    dispLoading("処理中...");

    $("article .inner").empty();
    $(".pagenavi, .kensu").addClass("disnon");

    const res = await fetch(`${API_PATH}productList&pagenum=${pg}`, { body: frm, method: "POST" });

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      alert(data.error);
      console.log(data.error)
      return;
    };

    //Page total & Total count
    const { total_page, count } = data[data.length - 1];

    //remove total & Total count
    data.pop();

    //LOOP
    $("#list").append($(`
      <table class="" id="shohin_table">
        <thead>
          <tr>
            <th class="w_50px">詳細</th>
            <th class="sort desc">商品</th>
            <th class="sort desc">商品名</th>
            <th class="sort desc">商品名略称</th>
            <th class="sort desc">商品分類</th>
            <th class="sort desc tac">売上単位</th>
            <th class="sort desc">売上単価</th>
            <th class="sort desc">仕入単価</th>
          </tr>
        </thead>
        <tbody class="list"></tbody>
      </table>`))

    const list = $("#shohin_table .list");
    const sp = $("#users_sp .inner");
    data.forEach((obj) => {
      /** PC TABLE VIEW **/
      var tr = $(`
      <tr>
        <td class="w_50px">
          <img src="/images/icon_edit.svg" class="btnEdit_icon" alt="詳細" id="${obj.product_cd}" loading="lazy"/>
        </td>
        <td>${obj.product_cd}</td>
        <td class="">${(obj.sale_kbn == "1") ? "セール　" + obj.product_nm : obj.product_nm}</td>
        <td>${obj.product_nm_abrv}</td>
        <td>${obj.product_type}</td>
        <td class="tac">${obj.sale_tani}</td>
        <td class="tar">${obj.sale_price}</td>
        <td class="tar">${obj.unit_price}</td>
      </tr>
      `);
      list.append(tr);

      /** PHONE TABLE VIEW **/
      var dl = $(`
      <dl class="itemList_sp">
        <a class="link" id="${obj.product_cd}"></a>
        <dt>${obj.product_nm_abrv}</dt>
        ${obj.product_type} | ${obj.sale_tani} | ${obj.sale_price}
      </dl>
      `);

      sp.append(dl);
    });

    /** PAGE NAVIGATION **/
    pagination(pg, total_page, count, $(el).attr("form"));
    pagination_sp(pg, total_page, $(el).attr("form"));

    $("#shohin_table, .pagenavi, .btnBlock, .kensu").removeClass("disnon");
    $("#input").prop("checked", false);
    //list.scrollTop(0);
  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading();
  }

}

const fncDispEdit = () => {
  editInit();

  $("#dialog").dialog({
    title: "商品詳細",
    height: screen.availHeight * 0.8,
    maxHeight: $("body").height(),
    width: isTouch ? screen.availWidth - 8 : 650,
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
            subLoading($("#dialog"), null, null, 0, 'absolute');

            var frm = new FormData($("#productFrm").get(0));

            if (frm.get("product_cd") == "") {
              $(".err_msg").text("商品コードを入力してください。").slideDown();
              return;
            }
            if (frm.get("product_nm") == "") {
              $(".err_msg").text("商品名を入力してください。").slideDown();
              return;
            };
            if (frm.get("sale_price") == "") {
              $(".err_msg").text("売上単価を入力してください。").slideDown();
              return;
            }

            if (frm.get("product_nm").indexOf("セール　") != -1) {
              var nm = frm.get("product_nm").substring(4);
              frm.set("product_nm", nm);
            };

            if ($("#productFrm #sale_kbn").prop("checked")) {
              frm.set("sale_kbn", "1");
            } else {
              frm.set("sale_kbn", "0");
            }

            const res = await fetch(`${API_PATH}productUpdate`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              $(".err_msg").text(data.error).slideDown();
              console.log(data.error);
              return;
            };

            //alert("商品を更新しました。");
            $(this).dialog("destroy");
            $("#frmSearch_pc .btnSearch").click();
          } catch (err) {
            console.log(err);
            alert(err);
          } finally {
            removeSubLoading($("#dialog"));
          }
        },
      },
      {
        text: "削除",
        class: "btn-delete",
        click: async function () {
          try {
            subLoading($("#dialog"), null, null, 0, 'absolute');

            if ($("#productFrm #product_cd").val() == "") {
              alert("商品コードを入力してください。");
              return;
            };

            const res = await fetch(`${API_PATH}productDelete&product_cd=${$("#productFrm #product_cd").val()}`);

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              alert(data.error);
              console.log(data.error);
              return;
            };

            $(this).dialog("destroy");
            $("#frmSearch_pc .btnSearch").click();
          } catch (err) {
            console.log(err);
            alert(err);
          } finally {
            removeSubLoading($("#dialog"));
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


const fncDispAdd = () => {
  addInit();
  $("#dialog").dialog({
    title: "商品登録",
    height: screen.availHeight * 0.8,
    maxHeight: $("body").height(),
    width: isTouch ? screen.availWidth - 10 : 650,
    buttons: [
      {
        text: "登録",
        class: "btn-edit",
        click: async function () {
          try {
            subLoading($("#dialog"), null, null, 0, 'absolute');

            if (!checkFrm()) return;

            var frm = new FormData($("#productFrm").get(0));
            // if (frm.get("product_cd") == "") {
            //   $(".err_msg").text("商品コードを入力してください。").slideDown();
            //   return;
            // };
            // if (frm.get("product_nm") == "") {
            //   $(".err_msg").text("商品名を入力してください。").slideDown();
            //   return;
            // };
            // if (frm.get("sale_price") == "") {
            //   $(".err_msg").text("売上単価を入力してください。").slideDown();
            //   return;
            // };

            if ($("#productFrm #sale_kbn").prop("checked")) {
              frm.set("sale_kbn", "1");
            } else {
              frm.set("sale_kbn", "0");
            }

            const res = await fetch(`${API_PATH}productAdd`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              $(".err_msg").text(data.error).slideDown();
              console.log(data.error);
              return;
            };

            // alert("新たな商品を登録しました。");
            $(this).dialog("destroy");
            $("#frmSearch_pc .btnSearch").click();
          } catch (err) {
            console.log(err);
            alert(err);
          } finally {
            removeSubLoading($("#dialog"));
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


const fncDispPrint = () => {
  $("#printFrm input").val("").prop("disabled", false);
  $(".err_msg").text("").slideUp();
  $("#shohin_print").dialog({
    title: "商品台帳印刷",
    modal: true,
    height: 300,
    maxHeight: $("body").height(),
    width: isTouch ? screen.availWidth - 8 : 400,
    minWidth: 300,
    buttons: [
      {
        text: "印刷",
        class: "btn-edit",
        click: async function () {
          try {
            dispLoading("処理中...");
            var frm = new FormData($("#printFrm").get(0));

            if (frm.get("product_from") != "" && frm.get("product_to") != "") {

              if (parseInt(frm.get("product_from")) > parseInt(frm.get("product_to"))) {
                $(".err_msg").text("商品コード[開始]の値が商品コード[終了]の値より大きくなっています").slideDown();
                return;
              };

            }

            const res = await fetch(`${API_PATH}productPdf`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            };

            const type = res.headers.get("content-type");

            //If Error
            if (type.indexOf("text/html") !== -1) {
              const data = await res.json();
              $(".err_msg").text(data.error).slideDown();
              console.log(data);
              return;
            };

            const data = await res.blob();

            var link = document.createElement("a");
            link.href = window.URL.createObjectURL(data);
            link.download = `商品台帳_${fileDateTime()}.pdf`;
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
  });
}


const fncDetail = async (id) => {
  try {
    const res = await fetch(`${API_PATH}productDetail&product_cd=${id}`)

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    };

    const data = await res.json();

    if (data.error != undefined) {
      alert(data.error);
      return;
    };

    for (const [key, value] of Object.entries(data)) {
      $(`#productFrm #${key}`).val(value);
    }

    //$("#productFrm .id-disp label").html(data.product_cd);
    $("#productFrm #prev_code").val(data.product_cd);
    if (data.sale_kbn == "1") {
      $("#productFrm #sale_kbn").prop("checked", true);
      $("#productFrm #product_nm").val(`セール　${data.product_nm}`)
    } else {
      $("#productFrm #sale_kbn").prop("checked", false);
      $("#productFrm #product_nm").val(data.product_nm);
    }

    fncDispEdit();
  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading();
  }
}

const checkFrm = () => {
  var isValid = true;
  $(".inputError").removeClass("inputError");
  $("#dialog .err_msg").text("").hide();

  $("#productFrm input[required], #productFrm select[required]").each(function () {
    if (!$(this).val()) {
      $("#dialog .err_msg").text(`${$(this).parent().prev().text()}を入力してください。`).slideDown();
      $(this).addClass("inputError").focus();
      $(this).parent().prev().addClass("inputError");
      return isValid = false;
    };
  })
  return isValid;
}

const dispCodeList = async () => {
  $("#codeListDialog #sort").removeClass("desc").addClass("asc").attr("data-toggle", "ASC");
  $("#codeListDialog").dialog({
    title: "商品空き番一覧",
    height: 850,
    maxHeight: $("body").height(),
    width: isTouch ? screen.availWidth - 8 : 1250,
    minWidth: 300,
    // buttons: [
    //   {
    //     text: "検索",
    //     class: "btn-edit",
    //     click: async function () {
    //       getCodeList();
    //     },
    //   },
    // ]
  });
  getCodeList();
}

const getCodeList = async () => {
  try {
    subLoading($("#codeListDialog"), null, null, 0, 'absolute');

    // let tbody = $("#codeList");
    // tbody.empty();

    //let order = $("#codeListDialog #sort").attr("data-toggle");

    const res = await fetch(`${API_PATH}productCodeList`);

    if (!res.ok) {
      alert('ネットワークエラーが発生しました。');
      return;
    }

    const data = await res.json();

    if (data.hasOwnProperty('error')) {
      alert(data.error);
      return;
    };

    //Remove all classes
    $(".group-1").removeClass("group-1");
    $(".group-2").removeClass("group-2");
    $(".group-3").removeClass("group-3");
    $(".group-4").removeClass("group-4");
    $(".group-5").removeClass("group-5");
    $(".group-6").removeClass("group-6");
    $(".group-7").removeClass("group-7");
    $(".group-8").removeClass("group-8");
    $(".group-9").removeClass("group-9");
    $(".group-10").removeClass("group-10");

    //Add class name if in use
    data.forEach((obj) => {
      let cls = "";
      let val = Number(obj.product_cd);
      if (val >= 0 && val <= 99) {
        cls = "group-1";
      } else if (val >= 100 && val <= 199) {
        cls = "group-2";
      } else if (val >= 200 && val <= 299) {
        cls = "group-3";
      } else if (val >= 300 && val <= 399) {
        cls = "group-4";
      } else if (val >= 400 && val <= 499) {
        cls = "group-5";
      } else if (val >= 500 && val <= 599) {
        cls = "group-6";
      } else if (val >= 600 && val <= 699) {
        cls = "group-7";
      } else if (val >= 700 && val <= 799) {
        cls = "group-8";
      } else if (val >= 800 && val <= 899) {
        cls = "group-9";
      } else if (val >= 900 && val <= 999) {
        cls = "group-10";
      }

      $(`#codeList #${obj.product_cd}`).addClass(cls);
    })
  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeSubLoading($("#codeListDialog"));
  }
}

const codeOrderToggle = () => {
  let order = $("#codeListDialog #sort").attr("data-toggle");
  $("#codeListDialog #sort").toggleClass("asc").toggleClass("desc");
  if (order == "ASC") {
    $("#codeListDialog #sort").attr("data-toggle", "DESC");
  } else if (order == "DESC") {
    $("#codeListDialog #sort").attr("data-toggle", "ASC");
  }
}