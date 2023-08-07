$(document).ready(() => {

  //SEARCH
  $(".btnSearch").click(function () {
    fncSearch(this, 1);
  });

  //ADD NEW
  $(".btnAdd").on("click", () => {
    fncDispAdd();
  });

  /**
 * DETAIL ・ 詳細
 */
  $(document.body).on('click', '.btnEdit_icon, .link', function () {
    fncDetail(this.id);
  })

  $(document.body).on('keyup', ".inputError", function () {
    if (this.value == "") return;
    $(this).removeClass("inputError");
    $(this).parent().prev().removeClass("inputError");
  })

});

/**
 * DISPLAY USER DIALOG
 * 
 */
function fncDispEdit() {
  editInit();

  $("#dialog").dialog({
    title: "ユーザー詳細",
    modal: true,
    height: isTouch ? screen.availHeight * 0.8 : 500,
    maxHeight: $(".content").height(),
    width: isTouch ? screen.availWidth - 10 : 500,
    buttons: [
      {
        text: "編集",
        class: "btn-edit",
        click: function () {
          $(".btn-edit").css("display", "none");
          $(".itemEdit input, .itemEdit option").prop("disabled", false);
          $(".btn-update, .btn-delete").css("display", "inline-block");
        },
      },
      {
        text: "更新",
        class: "btn-update",
        click: async function () {
          try {
            dispLoading("処理中．．．");
            if (!frmCheck()) return;

            var frm = new FormData($("#userFrm").get(0));
            // if (frm.get("user_nm") == "") {
            //   $(".err_msg").text("ユーザー名を入力してください。").slideDown();
            //   return;
            // };
            // if (frm.get("auth_cd") == "") {
            //   $(".err_msg").text("権限を選択してください。").slideDown();
            //   return;
            // };

            const res = await fetch(`${API_PATH}userUpdate`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              $(".err_msg").text(data.error).slideDown();
              return;
            };

            //alert("ユーザーを更新しました。");
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
        text: "削除",
        class: "btn-delete",
        click: async function () {
          try {
            if ($("#userFrm #user_id").val() == "") {
              alert("ユーザーを見つかりません。");
              return;
            };

            if (!confirm("ユーザーを削除してよろしでしょうか？")) return;

            dispLoading("処理中．．．");

            const res = await fetch(`${API_PATH}userDelete&user_id=${$("#userFrm #user_id").val()}`);

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              alert(data.error);
              return;
            };

            //alert("ユーザーを削除しました。");
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
          $(this).dialog("destroy");
        },
      },
    ],
  });
}
// function editInit() {
//   $(".itemEdit input, .itemEdit option").prop("disabled", true);
//   $(".id-disp input").attr("type", "hidden");
//   $(".pwd").hide();
//   $(".id-disp label").show();
// }

/**
 * OPEN ADD NEW USER DIALOG
 */
function fncDispAdd() {
  addInit();
  $("#dialog").dialog({
    title: "ユーザー登録",
    modal: true,
    height: isTouch ? screen.availHeight * 0.8 : 500,
    maxHeight: $(".content").height(),
    width: isTouch ? screen.availWidth - 10 : 500,
    buttons: [
      {
        text: "登録",
        class: "btn-edit",
        click: async function () {
          try {
            dispLoading("処理中．．．");

            if (!frmCheck()) return;

            var frm = new FormData($("#userFrm").get(0));

            // if (frm.get("user_id") == "") {
            //   $(".err_msg").text("ユーザーIDを入力してください。").slideDown();
            //   return;
            // };
            // if (frm.get("user_nm") == "") {
            //   $(".err_msg").text("ユーザー名を入力してください。").slideDown();
            //   return;
            // };
            // if (frm.get("password") == "") {
            //   $(".err_msg").text("パスワードを入力してください。").slideDown();
            //   return;
            // };
            // if (frm.get("auth_cd") == "") {
            //   $(".err_msg").text("権限を選択してください。").slideDown();
            //   return;
            // };

            const res = await fetch(`${API_PATH}userAdd`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return;
            }

            const data = await res.json();

            if (data.hasOwnProperty("error")) {
              $(".err_msg").text(data.error).slideDown();
              return;
            };

            //alert("新たなユーザーを登録しました。");
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
          $(this).dialog("destroy");
        },
      },
    ],
  });
};

/**
 * INITAILIZE ADD FORM
 * - enable all inputs
 */
// function addInit() {
//   $(".itemEdit input, .itemEdit option").prop("disabled", false);
//   $(".itemEdit select").prop("selectedIndex", 0);
//   $(".itemEdit input").val("");
//   $(".id-disp input").attr("type", "text");
//   $(".id-disp label").hide();
//   $(".pwd").show();
// }

/**
 * ASYNC SEARCH FUNCTION
 * 
 */
const fncSearch = async (el, pg) => {
  try {

    var frm = new FormData($(`#${$(el).attr("form")}`).get(0));

    dispLoading("処理中．．．");
    $("article .inner").empty();
    $(".pagenavi, .kensu").addClass("disnon");

    const res = await fetch(`${API_PATH}userList&pagenum=${pg}`, { body: frm, method: "POST" })

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
    const { total_page, count } = data[data.length - 1];

    //remove total & Total count
    data.pop();

    $("#list").append($(`
      <table id="user_table">
          <thead>
              <tr>
                  <th class="w_50px">詳細</th>
                  <th class="sort asc">ユーザーID</th>
                  <th class="sort asc">ユーザー名</th>
                  <th class="sort asc">権限</th>
              </tr>
          </thead>
          <tbody class="list"></tbody>
      </table>
    `))

    const list = $(".list");
    const sp = $("#users_sp .inner");

    //LOOP
    data.forEach((obj) => {
      /** PC TABLE VIEW **/
      const tr = $(`
      <tr>
        <td class="w_50px">
          <img src="/images/icon_edit.svg" class="btnEdit_icon" alt="詳細" id="${obj.user_id}" loading="lazy"/>
        </td>
        <td>${obj.user_id}</td>
        <td class="">${obj.user_nm}</td>
        <td>${obj.auth}</td>
      </tr>
    `);
      list.append(tr);

      /** PHONE TABLE VIEW **/
      const dl = $(`
        <dl class="itemList_sp">
          <a class="link" id="${obj.user_id}"></a>
          <dt>${obj.user_id}</dt>
          ${obj.user_nm} | ${obj.auth}
        </dl>`
      );

      sp.append(dl);
    });

    /** PAGE NAVIGATION **/
    pagination(pg, total_page, count, $(el).attr("form"));
    pagination_sp(pg, total_page, $(el).attr("form"));

    $("#user_table, .pagenavi, .btnBlock, .kensu").removeClass("disnon");
    $("#input").prop("checked", false);

  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading();
  }
};

/**
 * GET USER DETAIL
 * @param {String} id user id 
 */
const fncDetail = async (id) => {

  try {
    dispLoading("処理中．．．");
    const res = await fetch(`${API_PATH}userDetail&user_id=${id}`)

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    }

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      alert(data.error);
      return;
    };

    $("#userFrm .id-disp label").html(data.user_id);
    for (const [key, value] of Object.entries(data)) {
      $(`#userFrm #${key}`).val(value);
    }

    fncDispEdit();
  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading();
  }
}

const frmCheck = () => {
  var isValid = true;
  $(".inputError").removeClass("inputError");
  $(".err_msg").text("").hide();

  $("#userFrm input[required], #userFrm select[required]").each(function () {
    if (!$(this).val()) {
      $(this).addClass("inputError").focus();
      $(this).parent().prev().addClass("inputError");
      $(".err_msg").text(`${$(this).parent().prev().text()}を入力してください。`).slideDown();
      return isValid = false;
    }
  })

  return isValid;
}

/** ROW SORT **/
// const getCellValue = (tr, idx) =>
//   delComma(tr.children[idx].innerText) || tr.children[idx].textContent;

// const comparer = (idx, asc) => (a, b) =>
//   ((v1, v2) =>
//     v1 !== "" && v2 !== "" && !isNaN(v1) && !isNaN(v2)
//       ? v1 - v2
//       : v1.toString().localeCompare(v2))(
//         getCellValue(asc ? a : b, idx),
//         getCellValue(asc ? b : a, idx)
//       );

// (function ($) {
//   $.fn.rows = function () {
//     var tbl = document.getElementById(this[0].id);
//     return tbl.rows;
//   };
// })(jQuery);