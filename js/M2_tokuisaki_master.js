var offset = 0;
var _ajax = false;
$(document).ready(() => {

  /** SEARCH **/
  $(".btnSearch").on("click", function () {
    fncSearch(this, 1);
  });

  /** EDIT **/
  $(document.body).on('click', '.btnEdit_icon, .link', function () {
    fncDetail(this.id);
  })

  /** ADD **/
  $(".btnAdd").on("click", () => {
    fncDispAdd();
  });

  /** TEL **/
  $("input[type='tel']").keyup(async function () {
    $(this).val($(this).val().replace(/[^0-9]/g, ''));

  });

  $("#editFrm #tokuisaki_zip").on("keyup", async function () {
    if ($(this).val() == "") {
      $(".livesearch_row").slideUp();
      $(".livesearch").empty();
      $("#editFrm #tokuisaki_adr_1").val("");
      $("#editFrm #tokuisaki_adr_2").val("");
      return;
    };

    if (this.value.length == 7) {
      liveZipSearch(this.value);
      $(".livesearch_row").slideUp();
      $(".livesearch").empty();
      return;
    };

    offset = 0;
    getZipList(this.value);
  });

  $(".livesearch").on("scroll", async function () {
    if (_ajax) return;
    const { scrollTop, scrollHeight, clientHeight } = this;
    const scrollPercent = scrollTop + clientHeight;
    if (scrollPercent > 0 && scrollPercent >= (scrollHeight - 75)) {
      _ajax = true;
      offset += 50;
      // user has scrolled to the 75%, load next 50
      getZipList($("#editFrm #tokuisaki_zip").val());
    };
  });

  $(".livesearch").on('click', '.livesearch_zip', async function () {
    liveZipSearch(this.id);
    $(".livesearch_row").slideUp();
    $(".livesearch").empty();
  })

  $("#okurisakiFrm #okurisaki_zip").keyup(function () {
    if ($(this).val() == "") return;

    liveOkuriZipSearch($(this).val());
  })

  $("#btnOkurisaki").click(() => {
    fncGetOkurisaki($("#tokuisaki_cd").val(), 1);
    fncDispOkurisakiList();
  });

  /** OKURISAKI NAV **/
  $("#okurisaki_nav .prevpostslink, #okurisaki_nav .nextpostslink").click(function () {
    //GET okurisaki
    fncGetOkurisaki($("#tokuisaki_cd").val(), $(this).attr('page'));

  });

  $(document.body).on('keyup', ".inputError", function () {
    if (this.value == "") return;
    $(this).removeClass("inputError");
    $(this).parent().prev().removeClass("inputError");
  });

  // $("#dispTelList").click(function () {
  //   // if ($("#telDialog").is(":visible")) return;
  //   dispTelDialog();
  // })

  $("#tokuisakiTelFrm").on('click', '.telRowDelete', function () {
    let total_index = $(`#tokuisakiTelFrm table tbody tr`);
    if (total_index.length == 1) return;

    if ($(this).prev().prop("disabled")) return;

    $(this).parent().parent().remove();
  });

  $("#telList").on('keyup', 'tr input', function (e) {
    $(this).val($(this).val().replace(/[^0-9]/g, ''));

    if (e.which == 13) {
      let lastEl = $("#tokuisakiTelFrm table tbody tr:last-child input");

      if ($(this).val() !== "" && $(this).is(lastEl)) {
        addRow();
        $("#tokuisakiTelFrm table tbody tr:last-child input").focus();
      }
    }

    e.preventDefault();

  });

  // $("#telList").on('dblclick', 'tr', function(){
  //   let val = $(this).find("input").val();
  //   if($(this).find("input").prop("disabled")) return;
  //   if(val !== ""){
  //     $("#tokuisaki_tel").val(val);
  //   };
  // })

  // $("#dialog").on("dialogclose", function (event, ui) {
  //   closeTelDialog();
  // });

  // $("#tokusiakiTelFrm").on('submit', function (e) {
  //   e.preventDefault();
  // })

});

const fncSearch = async (el, pg) => {
  try {

    var frm = new FormData($(`#${$(el).attr("form")}`).get(0));

    dispLoading("処理中...");
    $("article .inner").empty();
    $(".pagenavi, .kensu").addClass("disnon");

    const res = await fetch(`${API_PATH}tokuisakiList&pagenum=${pg}`, { body: frm, method: "POST" });

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    }

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      alert(data.error);
      return;
    };

    //Page total & Total count
    //var last = data[data.length - 1];
    const { total_page, count } = data[data.length - 1];

    //remove total & Total count
    data.pop();

    $("#list").append($(`
      <table class="" id="tokuisaki_table">
          <thead>
              <tr>
                  <th class="w_50px">詳細</th>
                  <th class="sort desc">代表番号</th>
                  <th class="sort desc">得意先名</th>
                  <th class="sort desc">担当者</th>
                  <th class="sort desc">郵便番号</th>
                  <th class="tal">住所01</th>
                  <th class="tal">住所02</th>
                  <th class="tal">住所03</th>
              </tr>
          </thead>
          <tbody class="list"></tbody>
      </table>
    `))

    const list = $("#tokuisaki_table .list");
    const sp = $("#users_sp .inner");

    data.forEach((obj) => {
      const tr = $(`
      <tr>
        <td class="w_50px">
          <img src="/images/icon_edit.svg" class="btnEdit_icon" alt="詳細" id="${obj.tokuisaki_cd}" loading="lazy"/>
        </td>
        <td>${obj.tokuisaki_tel}</td>
        <td>${obj.tokuisaki_nm}</td>
        <td>${obj.tanto_nm}</td>
        <td>${obj.tokuisaki_zip}</td>
        <td>${obj.tokuisaki_adr_1}</td>
        <td class="tal">${obj.tokuisaki_adr_2}</td>
        <td class="tal">${obj.tokuisaki_adr_3}</td>
      </tr>
      `);
      list.append(tr);

      /** PHONE TABLE VIEW **/
      const dl = $(`
      <dl class="itemList_sp">
        <a class="link" id="${obj.tokuisaki_cd}"></a>
        <dt>${obj.tokuisaki_nm}</dt>
        ${obj.tokuisaki_tel} | ${obj.tanto_nm}
      </dl>
      `);
      sp.append(dl);
    });

    /** PAGE NAVIGATION **/
    pagination(pg, total_page, count, $(el).attr("form"));
    pagination_sp(pg, total_page, $(el).attr("form"));

    $(".pagenavi, .kensu").removeClass("disnon");
    $("#input").prop("checked", false);
  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading();
  }
}

const fncDetail = async (id) => {
  try {
    dispLoading("処理中．．．");

    const res = await fetch(`${API_PATH}tokuisakiDetail&tokuisaki_cd=${id}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    }

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      alert(data.error);
      return;
    };

    for (const [key, value] of Object.entries(data)) {
      $(`#editFrm #${key}`).val(value);
    }

    fncDispEdit();

  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading();
  }
}

const fncDispEdit = () => {
  getTelList();
  editInit();
  $(".livesearch_row").hide();
  //$("#tokuisakiTelFrm table tbody tr input").prop('disabled', true);
  $(".inputError").removeClass("inputError");
  $("#btnOkurisaki, #dispTelList").show().prop('disabled', true);
  $("#dialog").dialog({
    title: "得意先詳細",
    modal: true,
    height: screen.availHeight * 0.85,
    maxHeight: $(".content").height(),
    width: isTouch ? screen.availWidth - 10 : 1040,
    resizable: false,
    buttons: [
      {
        text: "編集",
        class: "btn-edit",
        click: () => {
          $(".btn-edit").css("display", "none");
          $(".itemEdit input, .itemEdit option, .itemEdit textarea").prop("disabled", false);
          $(".btn-update").css("display", "inline-block");
          $(".btn-delete").css("display", "inline-block");
          $("#btnOkurisaki, #dispTelList").prop("disabled", false);
          $("#telList input").prop('disabled', false);
        },
      },
      {
        text: "更新",
        class: "btn-update",
        click: async function () {
          try {
            dispLoading("処理中．．．");

            if (!frmCheck()) return;

            $("#dialog .err_msg").text("").slideUp();

            var frm = new FormData($("#editFrm").get(0));
            frm.append("tel_rows", JSON.stringify(createTelArray()));

            const res = await fetch(`${API_PATH}tokuisakiUpdate`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              $("#dialog .err_msg").text(data.error).slideDown();
              console.log(data.error);
              return;
            };

            $(this).dialog("destroy");
            $("#frmSearch_pc .btnSearch").click();
            closeTelDialog();
          } catch (err) {
            console.log(err);
            alert(err);
          } finally {
            removeLoading();
            //closeTelDialog();
          }
        },
      },
      {
        text: "削除",
        class: "btn-delete",
        click: async function () {
          try {
            dispLoading("処理中．．．");

            if ($("#editFrm #tokuisaki_cd").val() == "") {
              alert("得意先を指定してください。");
              return;
            };

            if (!confirm("本当に削除してよろしでしょうか？")) return;

            const res = await fetch(`${API_PATH}tokuisakiDelete&tokuisaki_cd=${$("#editFrm #tokuisaki_cd").val()}`);

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              $("#dialog .err_msg").text(data.error).slideDown();
              console.log(data.error);
              return;
            };

            $(this).dialog("destroy");
            $("#frmSearch_pc .btnSearch").click();

          } catch (err) {
            console.log(err);
            alert(err);
          } finally {
            removeLoading();
          }
        },
      },
      {
        text: "閉じる",
        class: "btn-close",
        click: function () {
          closeTelDialog();
          $(this).dialog("destroy");
        },
      },
    ],
  });
}

// function editInit() {
//   $(".itemEdit input, .itemEdit option, .itemEdit textarea").prop("disabled", true);
// }

// const frmCheck = () => {
//   var ret = true;
//   $(".inputError").removeClass("inputError");
//   $("#editFrm input, #editFrm select").each(function () {
//     if ($(this).attr("required") && $(this).val() == "") {
//       $("#dialog .err_msg").text(`${$(this).parent().prev().text()}を入力してください。`).slideDown();
//       $(this).addClass("inputError").focus();
//       $(this).parent().prev().addClass("inputError");
//       return ret = false;
//     };
//     //締め日
//     if (this.id == "bill_dt") {
//       if ($(this).val() < 0 || $(this).val() > 32) {
//         $("#dialog .err_msg").text(`${$(this).parent().prev().text()}は1～31の間で入力して下さい。`).slideDown();
//         $(this).addClass("inputError").focus();
//         $(this).parent().prev().addClass("inputError");
//         return ret = false;
//       }
//     }
//     //全角カタカナ
//     if (this.id == "tokuisaki_kana" && !isZenKakuKana($(this).val().replace('・', ""))) {
//       $("#dialog .err_msg").text(`得意先カナは全角カナを入力してください。`).slideDown();
//       $(this).addClass("inputError").focus();
//       $(this).parent().prev().addClass("inputError");
//       return ret = false;
//     }
//     //SAGAWA TIME CHECK
//     if(this.id == "delivery_kbn"){
//       let kbn = this.value;
//       if(kbn === '2' && $("#delivery_instruct_kbn").val() !== '3' && $("#delivery_time_hr").val() === ""){
//        // if($("#delivery_time_hr").val() === ""  && kbn === '2'){
//           $("#dialog .err_msg").text(`佐川時間は１時～１２時で入力して下さい。`).slideDown();
//           $("#delivery_time_hr").addClass("inputError").focus();
//           $(this).parent().prev().addClass("inputError");
//           return ret = false;
//        // };
//       }

//       const deliveryTimeHr = Number($("#delivery_time_hr").val());
//       const deliveryTimeMin = Number($("#delivery_time_min").val());

//       if (deliveryTimeHr < 1 || deliveryTimeHr > 12) {
//         $("#dialog .err_msg").text("佐川時間は１時～１２時で入力して下さい。").slideDown();
//         $("#delivery_time_hr").addClass("inputError").focus();
//         $(this).parent().prev().addClass("inputError");
//         return ret = false;
//       }

//       if (deliveryTimeMin < 0 || deliveryTimeMin >= 60) {
//         $("#dialog .err_msg").text("佐川時間は０分～５９分で入力して下さい。").slideDown();
//         $("#delivery_time_min").addClass("inputError").focus();
//         $(this).parent().prev().addClass("inputError");
//         return ret = false;
//       }
//     }
//   });

//   return ret;
// }

const frmCheck = () => {
  $(".inputError").removeClass("inputError");
  $("#dialog .err_msg").text("").hide(); // Clear previous error messages

  let isValid = true;

  $("#editFrm input[required], #editFrm select[required]").each(function () {
    if (!$(this).val()) {
      const msg = $(this).parent().prev().text();
      $("#dialog .err_msg").text(`${msg}を入力してください。`).slideDown();
      $(this).addClass("inputError").focus();
      $(this).parent().prev().addClass("inputError");
      isValid = false;
      return false; // Exit the loop early
    }
  });

  if (!isValid) return false;

  const billDtInput = $("#bill_dt");
  if (billDtInput.val() !== "" && (billDtInput.val() < 1 || billDtInput.val() > 31)) {
    const msg = billDtInput.parent().prev().text();
    $("#dialog .err_msg").text(`${msg}は1～31の間で入力してください。`).slideDown();
    billDtInput.addClass("inputError").focus();
    billDtInput.parent().prev().addClass("inputError");
    isValid = false;
  }

  if (!isValid) return false;

  const tokuisakiKanaInput = $("#tokuisaki_kana");
  if (tokuisakiKanaInput.val() !== "" && !isHankaku(tokuisakiKanaInput.val().replace("・", ""))) {
    $("#dialog .err_msg").text(`得意先カナは半角カナで入力してください。`).slideDown();
    tokuisakiKanaInput.addClass("inputError").focus();
    tokuisakiKanaInput.parent().prev().addClass("inputError");
    isValid = false;
  }

  if (!isValid) return false;

  const deliveryKbnInput = $("#delivery_kbn");
  if (
    deliveryKbnInput.val() === "2" &&
    $("#delivery_instruct_kbn").val() !== "3" &&
    $("#delivery_time_hr").val() === ""
  ) {
    $("#dialog .err_msg").text(`佐川時間は１時～１２時で入力して下さい。`).slideDown();
    $("#delivery_time_hr").addClass("inputError").focus();
    $("#delivery_time_hr").parent().prev().addClass("inputError");
    isValid = false;
  }

  if (!isValid) return false;

  const deliveryTimeHr = Number($("#delivery_time_hr").val());
  const deliveryTimeMin = Number($("#delivery_time_min").val());
  if ($("#delivery_time_hr").val() !== "" && (deliveryTimeHr < 1 || deliveryTimeHr > 12)) {
    $("#dialog .err_msg").text("佐川時間は１時～１２時で入力して下さい。").slideDown();
    $("#delivery_time_hr").addClass("inputError").focus();
    deliveryKbnInput.parent().prev().addClass("inputError");
    isValid = false;
  }

  if (!isValid) return false;

  if ($("#delivery_time_min").val() !== "" && (deliveryTimeMin < 0 || deliveryTimeMin >= 60)) {
    $("#dialog .err_msg").text("佐川時間は０分～５９分で入力して下さい。").slideDown();
    $("#delivery_time_min").addClass("inputError").focus();
    deliveryKbnInput.parent().prev().addClass("inputError");
    isValid = false;
  }

  return isValid;
};


function fncDispAdd() {
  addInit();
  $("#telList").empty();
  addRow();
  $(".livesearch_row").hide();
  //, #dispTelList
  $("#btnOkurisaki").hide();
  $(".inputError").removeClass("inputError");
  $("#dialog").dialog({
    title: "得意先登録",
    modal: true,
    height: screen.availHeight * 0.8,
    maxHeight: $(".content").height(),
    width: isTouch ? screen.availWidth - 10 : 1040,
    resizable: false,
    buttons: [
      {
        text: "登録",
        class: "btn-edit",
        click: async function () {
          try {
            dispLoading("処理中．．．");

            if (!frmCheck()) return;

            $("#dialog .err_msg").text("").slideUp();

            var frm = new FormData($("#editFrm").get(0));
            frm.append("tel_rows", JSON.stringify(createTelArray()));

            const res = await fetch(`${API_PATH}tokuisakiAdd`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              $("#dialog .err_msg").text(data.error).slideDown();
              console.log(data.error);
              return false;
            };

            $(this).dialog("destroy");
            $("#frmSearch_pc .btnSearch").click();
            closeTelDialog();
          } catch (err) {
            console.log(err);
            alert("サーバーでエラーが発生しました。");
          } finally {
            removeLoading();
            //closeTelDialog();
          }
        },
      },
      {
        text: "閉じる",
        class: "btn-close",
        click: function () {
          closeTelDialog();
          $(this).dialog("destroy");
        },
      },
    ],
  });
}

/**
 * GET ADDRESS FROM ZIP
 * @param {*} val zip number
 * @returns 
 */
const liveZipSearch = async (val) => {
  try {
    const res = await fetch(`${API_PATH}zipLiveSearch&zip=${val}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    }

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      $("#editFrm #tokuisaki_adr_1").val("");
      $("#editFrm #tokuisaki_adr_2").val("");
      return;
    };
    $("#editFrm #tokuisaki_zip").val(val);
    $("#editFrm #tokuisaki_adr_1").val(data.ken_fu);
    $("#editFrm #tokuisaki_adr_2").val(data.shi_ku + data.machi);

  } catch (err) {
    console.log(err);
  }
};

const liveOkuriZipSearch = async (val) => {
  try {
    const res = await fetch(`${API_PATH}zipLiveSearch&zip=${val}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    }

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      $("#okurisakiFrm #okurisaki_adr_1").val("");
      $("#okurisakiFrm #okurisaki_adr_2").val("");
      return;
    };

    $("#okurisakiFrm #okurisaki_adr_1").val(data.ken_fu);
    $("#okurisakiFrm #okurisaki_adr_2").val(data.shi_ku + data.machi);

  } catch (err) {
    console.log(err);
  }
};

const fncDispOkurisakiList = async () => {
  $("#okurisakiDialog").dialog({
    title: "送り先",
    modal: true,
    height: screen.availHeight * 0.8,
    maxHeight: $(".content").height(),
    width: isTouch ? screen.availWidth - 10 : 600,
    buttons: [
      {
        text: "送り先登録",
        class: "btn-edit",
        click: async () => {
          try {
            dispLoading("処理中．．．");

            $("#okurisakiDialog .err_msg").text("").slideUp();

            //DATA CHECK
            if (!okurisakiFrmCheck()) return;

            const frm = new FormData($("#okurisakiFrm")[0]);
            frm.append("okurisaki_tokuisaki_cd", $("#tokuisaki_cd").val());

            let type = (frm.get("okurisaki_cd") == "") ? "okurisakiAdd" : "okurisakiUpdate";

            const res = await fetch(`${API_PATH}${type}`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              $("#okurisakiDialog .err_msg").text(data.error).slideDown();
              return;
            };

            alert("送り先登録を完了しました。");

            if (type === "okurisakiAdd") {
              fncGetOkurisaki($("#tokuisaki_cd").val(), 1);
            };

          } catch (error) {
            console.log(error);
            alert("サーバーでエラーが発生しました。");
          } finally {
            removeLoading();
          }
        }
      },
      {
        text: "送り先削除",
        class: "btn-delete",
        click: async () => {
          try {
            dispLoading("処理中．．．");

            $("#okurisakiDialog .err_msg").text("").slideUp();

            const frm = new FormData($("#okurisakiFrm")[0]);
            frm.append("okurisaki_tokuisaki_cd", $("#tokuisaki_cd").val());

            if (frm.get("okurisaki_cd") == "") {
              alert("送り先を指定してください。");
              return;
            };

            if (frm.get("okurisaki_cd") == "0000000001") {
              alert("この送り先を削除出来ません。");
              return;
            };

            if (!confirm("送り先を削除してよろしいでしょうか？")) return;

            const res = await fetch(`${API_PATH}okurisakiDelete`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              $("#okurisakiDialog .err_msg").text(data.error).slideDown();
              return;
            };

            alert("送り先登録を削除しました。");

            fncGetOkurisaki($("#tokuisaki_cd").val(), 1);

          } catch (error) {
            console.log(error);
            alert("サーバーでエラーが発生しました。");
          } finally {
            removeLoading();
          }
        }
      }
    ]
  })
  $(".btn-delete").show();
}

const fncGetOkurisaki = async (id, pg) => {
  try {
    $(".err_msg").text("").slideUp();
    $(".inputError").removeClass("inputError");

    if (pg == "new") {
      $("#okurisakiFrm #okurisaki_cd").val("");
      $("#okurisakiFrm #okurisaki_nm").val("");
      $("#okurisakiFrm #okurisaki_kana").val("");
      $("#okurisakiFrm #okurisaki_tel").val("");
      $("#okurisakiFrm #okurisaki_fax").val("");
      $("#okurisakiFrm #okurisaki_zip").val("");
      $("#okurisakiFrm #okurisaki_adr_1").val("");
      $("#okurisakiFrm #okurisaki_adr_2").val("");
      $("#okurisakiFrm #okurisaki_adr_3").val("");
      $("#okurisakiFrm #okurisaki_tanto_nm").val("");
      $("#okurisakiFrm #okurisaki_fuzai_contact").val("");
      //$("#okurisakiFrm #okurisaki_industry_cd").prop("selectedIndex", 0);
      $("#okurisakiFrm #okurisaki_delivery_instruct").val("");

      var link = $("#okurisaki_nav .pg_num").text() == "新登録" ? link : $("#okurisaki_nav .pg_num").text().substr(0, 1);
      $("#okurisaki_nav .pg_num").text(`新登録`);
      $("#okurisaki_nav .prevpostslink").attr("page", link);
      $("#okurisaki_nav .nextpostslink").attr("page", "new");
      $("#okurisaki_nav").removeClass("disnon");
      return;
    }

    const res = await fetch(`${API_PATH}getOkurisakiById&tokuisaki_cd=${id}&pagenum=${pg}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    }

    const result = await res.json();

    if (result.hasOwnProperty("error")) {
      $("#okurisakiDialog .err_msg").text(result.error).slideDown();
      return;
    };

    $("#okurisakiFrm #okurisaki_cd").val(result[0].okurisaki_cd)
    $("#okurisakiFrm #okurisaki_nm").val(result[0].okurisaki_nm);
    $("#okurisakiFrm #okurisaki_kana").val(result[0].okurisaki_kana);
    $("#okurisakiFrm #okurisaki_tel").val(result[0].okurisaki_tel);
    $("#okurisakiFrm #okurisaki_fax").val(result[0].okurisaki_fax);
    $("#okurisakiFrm #okurisaki_zip").val(result[0].okurisaki_zip);
    $("#okurisakiFrm #okurisaki_adr_1").val(result[0].okurisaki_adr_1);
    $("#okurisakiFrm #okurisaki_adr_2").val(result[0].okurisaki_adr_2);
    $("#okurisakiFrm #okurisaki_adr_3").val(result[0].okurisaki_adr_3);
    $("#okurisakiFrm #okurisaki_tanto_nm").val(result[0].tanto_nm);
    $("#okurisakiFrm #okurisaki_fuzai_contact").val(result[0].fuzai_contact);
    //$("#okurisakiFrm #okurisaki_industry_cd").val(result[0].industry_cd);
    $("#okurisakiFrm #okurisaki_delivery_instruct").val(result[0].delivery_instruct);

    $("#okurisaki_nav .pg_num").text(`${pg}/${result[1].count}`);
    $("#okurisaki_nav .prevpostslink").attr("page", (Number(pg) - 1 == 0) ? 1 : pg - 1);
    $("#okurisaki_nav .nextpostslink").attr("page", (Number(pg) + 1 > result[1].count) ? "new" : Number(pg) + 1);
    $("#okurisaki_nav").removeClass("disnon");

    //fncDispOkurisakiList();
  } catch (error) {
    console.error(error);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading()
  }
}

const okurisakiFrmCheck = () => {
  var isValid = true;
  $(".inputError").removeClass("inputError");
  $("#dialog .err_msg").text("").hide(); // Clear previous error messages
  // if ($okurisakiFormTokuisakiCd.val() == "") {
  //   alert("得意先を選択してください。");
  //   return ret = false;
  // };
  $("#okurisakiFrm input[required], #okurisakiFrm select[required]").each(function () {
    if (!$(this).val()) {
      $("#okurisakiDialog .err_msg").text(`${$(this).parent().prev().text()}を入力してください。`).slideDown();
      // alert(`${$(this).prev().text()}を入力してください。`);
      $(this).parent().prev().addClass("inputError");
      $(this).addClass("inputError").focus();
      return isValid = false;
    };
  });

  if (!isValid) return false;

  if ($("#okurisaki_kana").val() !== "" && !isHankaku($("#okurisaki_kana").val().replace('・', ""))) {
    $("#okurisakiDialog .err_msg").text(`送り先カナは半角カナで入力してください。`).slideDown();
    $("#okurisaki_kana").parent().prev().addClass("inputError");
    $("#okurisaki_kana").addClass("inputError").focus();
    return isValid = false;
  }

  return isValid;
}

const getZipList = async (zip) => {
  try {
    const res = await fetch(`${API_PATH}zipList&zip=${zip}&offset=${offset}`);

    if (!res.ok) {
      $(".livesearch_row").slideUp();
      offset = 0;
      alert("ネットワークエラーが発生しました。");
      return;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      return;
    }

    if (offset == 0 && data.length == 0) {
      $(".livesearch_row").slideUp();
      offset = 0;
      return;
    };

    var disp = true;

    if (offset == 0) {
      $(".livesearch").empty();
    };

    data.forEach((obj) => {
      if (obj.zip == zip) {
        disp = false;
        return false;
      };
      $(".livesearch").append(`<li class="livesearch_zip" id="${obj.zip}">${obj.zip}</li>`);
    });

    if (!disp) {
      $(".livesearch_row").slideUp();
      //  liveZipSearch(zip);
      offset = 0;
      return;
    };

    $(".livesearch_row").slideDown();

  } catch (error) {
    console.log(error);
    offset = 0;
  } finally {
    _ajax = false;
  }
}

const dispTelDialog = async () => {
  $("#telDialog").dialog({
    title: "追加電話番号",
    height: 500,
    maxHeight: $(".content").height(),
    width: 375,
    // buttons: [
    //   {
    //     text: "登録",
    //     class: "btn-edit",
    //     click: async function () {

    //     }
    //   }
    // ]
  })
}

const getTelList = async () => {
  try {
    $("#telDialog .err_msg").text('').hide();
    let tbody = $("#telList");
    tbody.empty();

    const res = await fetch(`${API_PATH}getTelList&tokuisaki_cd=${$("#tokuisaki_cd").val()}`);

    if (!res.ok) return;

    const data = await res.json();

    if (data.hasOwnProperty('error')) {
      $("#telDialog .err_msg").text(data.error).slideDown();
      const tr = $(`
      <tr>
        <td>
          <input type="tel" class="ip_w70" maxlength="11" value="" disabled>
          <img src="/images/trash_icon.svg" alt="削除" width="22px" class="telRowDelete">
        </td>
      </tr>
      `);
      tbody.append(tr);
      return;
    };

    data.forEach((obj) => {
      const tr = $(`
      <tr>
        <td>
          <input type="tel" class="ip_w70" maxlength="11" value="${obj.tel_no}" disabled>
          <img src="/images/trash_icon.svg" alt="削除" width="22px" class="telRowDelete">
        </td>
      </tr>
      `);
      tbody.append(tr);
    });

    const tr = $(`
    <tr>
      <td>
        <input type="tel" class="ip_w70" maxlength="11" value="" disabled>
        <img src="/images/trash_icon.svg" alt="削除" width="22px" class="telRowDelete">
      </td>
    </tr>
    `);
    tbody.append(tr);

  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  };
}

const createTelArray = () => {

  const telValue = $("#tokuisaki_tel").val().trim();
  const faxValue = $("#tokuisaki_fax").val().trim();
  const fuzaiValue = $("#fuzai_contact").val().trim();

  var tel_array = [];
  tel_array.push({
    tel: telValue,
  });
  if (faxValue !== "" &&
    faxValue !== telValue) {
    tel_array.push({
      tel: faxValue,
    });
  }
  if (fuzaiValue !== "" &&
    fuzaiValue !== telValue &&
    fuzaiValue !== faxValue) {
    tel_array.push({
      tel: fuzaiValue,
    });
  }

  $("#tokuisakiTelFrm table tbody tr input").each(function () {
    const currentValue = $(this).val().trim();
    let exists = false;

    if (currentValue !== "") {

      // if (currentValue === telValue) {
      //   exists = true;
      // }
      // if (currentValue === faxValue) {
      //   exists = true;
      // }
      // if (currentValue === fuzaiValue) {
      //   exists = true;
      // };
      // Check if currentValue already exists in tel_array
      exists = tel_array.some(row => row.tel === currentValue);

      if (!exists) {
        const row_object = {
          tel: currentValue,
        };

        tel_array.push(row_object);
      }
    }
  });

  return tel_array;
};


const addRow = () => {
  let tbody = $("#telList");
  const tr = $(`
  <tr>
    <td>
      <input type="tel" class="ip_w70" maxlength="11" value="">
      <img src="/images/trash_icon.svg" alt="削除" width="22px" class="telRowDelete">
    </td>
  </tr>
  `);
  tbody.append(tr);
}

const closeTelDialog = () => {
  if ($("#telDialog").is(":visible")) {
    $("#telDialog").dialog('destroy');
  }
}
