/**
 * API PATH
 */
const API_PATH = "../php/pcService.php?Type=";

$(document).ready(() => {

  if (isTouch) {
    initializeMobile();
  } else {
    jQuery("#openmenu").attr("style", "display:none !important;");
    setBacktopButton();
    jQuery("a,.button").on({
      // mouseenter: function () {
      // 	jQuery(this).stop(true).css("opacity", 0.5);
      // },
      // mouseleave: function () {
      // 	jQuery(this).stop(true).css("opacity", 0.5).animate({
      // 		opacity: 1
      // 	}, 150, "linear");
      // }
    });
  }

  //BELOW IS NOT IN USE

  /* ローディング */

  // $(function () {
  //   var h = $(window).height();

  //   $("#wrap").css("display", "none");
  //   $("#loader-bg ,#loader").height(h).css("display", "block");
  // });

  // $(window).load(function () {
  //   //すべての読み込みが完了したら実行
  //   $("#loader-bg").delay(900).fadeOut(800);
  //   $("#loader").delay(600).fadeOut(300);
  //   $("#wrap").css("display", "block");
  // });

  //5秒たったら強制的にロード画面を非表示
  // $(function () {
  //   setTimeout("stopload()", 5000);
  // });

  // function stopload() {
  //   $("#wrap").css("display", "block");
  //   $("#loader-bg").delay(900).fadeOut(800);
  //   $("#loader").delay(600).fadeOut(300);
  // }

  //ABOVE IS NOT IN USE

  function initializeMobile() {
    $("#mobilemenu a.close, #openmenu").on("click", toggleMenu);

    function toggleMenu() {
      if (!$("#mobilemenu").hasClass("open")) {
        $("#mobilemenu").addClass("open");
        $("body, html").css({
          overflow: "hidden",
        });
        $("#mobilemenu, #openmenu").css({
          top: $("body").scrollTop(),
          position: "absolute",
        });
        $("#innerwrap, #openmenu").stop(true).transitionStop(true).transit(
          {
            x: 276,
          },
          500,
          "cubic-bezier(0, 0.99, 0.13, 0.995)"
        );
        $("body").on("touchmove", function (e) {
          e.preventDefault();
        });

        $("#wrapper").append($("<div></div>").addClass("mask"));
      } else {
        $("#mobilemenu").removeClass("open");
        $("body, html").css({
          overflow: "visible",
        });
        $("#mobilemenu, #openmenu").css({
          top: 0,
          position: "fixed",
        });
        $("#innerwrap, #openmenu").stop(true).transitionStop(true).transit(
          {
            x: 0,
          },
          500,
          "cubic-bezier(0, 0.99, 0.13, 0.995)"
        );
        $("body").off("touchmove");

        $("#wrapper div.mask").remove();
      }
      return false;
    }

    var scrolling = false;
    $("a#backtop").on("click", backToTop);
    $("body").on("touchstart", stopScrolling);
    function backToTop() {
      scrolling = true;
      $("html, body")
        .stop(true)
        .animate({ scrollTop: 0 }, 750, "easeOutExpo", function () {
          scrolling = false;
        });
      return false;
    }
    function stopScrolling() {
      if (scrolling) {
        scrolling = false;
        $("html, body").stop(true);
      }
    }
  }

  function setBacktopButton() {
    var backtopButton = $("a#backtop");
    var visibleBorder = 50;
    var scrolling = false;

    backtopButton.on("click", backToTop);

    $(window).on("mousewheel", stopScrolling);

    $(window).on("scroll", scrollHandler);
    scrollHandler();

    function backToTop() {
      if ($(this).hasClass("disable") || scrolling) return false;

      scrolling = true;
      $("html, body")
        .stop(true)
        .animate({ scrollTop: 0 }, 750, "easeOutExpo", function () {
          scrolling = false;
        });
      return false;
    }
    function stopScrolling() {
      if (scrolling) {
        scrolling = false;
        $("html, body").stop(true);
      }
    }
    function scrollHandler() {
      var scrollTop = $(window).scrollTop();
      var totalHeight = $(document).height();
      var viewportHeight = $(window).height();
      var limit = totalHeight - viewportHeight - 63;

      if (scrollTop >= limit) {
        if (!backtopButton.hasClass("limit")) {
          backtopButton.addClass("limit");
        }
      } else {
        if (backtopButton.hasClass("limit")) {
          backtopButton.removeClass("limit");
        }
      }

      if (backtopButton.hasClass("disable")) {
        if (scrollTop >= visibleBorder) {
          backtopButton
            .removeClass("disable")
            .stop(true)
            .css({
              display: "block",
              opacity: 0,
            })
            .animate(
              {
                opacity: 1,
              },
              250,
              "easeOutExpo"
            );
        }
      } else {
        if (scrollTop <= visibleBorder) {
          backtopButton
            .addClass("disable")
            .stop(true)
            .animate(
              {
                opacity: 0,
              },
              250,
              "easeOutExpo",
              function () {
                display: "none";
              }
            );
        }
      }
    }
  }


  /** PAGE TITLE **/
  $(".pageTitle").text($(document).attr('title'));

  /** ログアウト **/
  $(".logout").click(async () => {
    try {
      dispLoading("処理中．．．");

      const res = await fetch(`${API_PATH}logout`, {
        method: "POST"
      });

      if (!res.ok) {
        alert("ネットワークエラーが発生しました。");
        return;
      };

      const data = await res.json();

      if (data.error != undefined) {
        alert(data.error);
        return;
      };

      location.href = "login";

    } catch {
      console.log(err);
      alert("サーバーでエラーが発生しました。");
    } finally {
      removeLoading();
    };

  })

  /** クリアボタン **/
  $(".btnReset").on("click", () => {
    //$("#input").prop("checked", false);
    $("#users table, .pagenavi, .content .btnBlock").addClass("disnon");
  });

  /**PICK**/
  $(document.body).on("click", ".pick", () => {
    $(".checkbox").prop("checked", true);
  });
  /**UNPICK**/
  $(document.body).on("click", ".unpick", () => {
    $(".checkbox").prop("checked", false);
  });

  $(window).resize(function () {
    //SALES DIALOG
    $("#saleDialog").dialog("option", "width", screen.availWidth * 0.90);
    $("#saleDialog").dialog("option", "height", screen.availHeight * 0.90);
  });

  /**
   * ADD EVENT TO DELETE BUTTON IN SALES ORDER IFRAME
   */
  $('#saleDialog').find('iframe').on('load', function () {
    $(this).contents().find('#btnDelete').on('click', async function () {
      if (!confirm("この売上伝票を削除してよろしでしょうか？\n削除した売上伝票は復元できません。")) return;

      let order_no = $("#order_no").val();

      let ret = await fncDeleteSale(order_no);

      if (!ret) return;

      $("iframe").attr("src", ``);
      $('#saleDialog').dialog('destroy');

      fncSearch($("#searchBtn"), $(".current").text());
    });

    $(this).contents().find('#btnCancel').on('click', async function () {
      try {
        if (!confirm("キャンセルしますか？")) return;

        _cancel = true;

        await cancelSale();

        $('#saleDialog').dialog('destroy');

        //fncSearch($("#searchBtn"), $("span.current").text());
      } catch (error) {
        console.log(error);
      }
    });

    $(this).contents().find('#updated').on('click',function(){
      $('#saleDialog').dialog('destroy');
    })
  });

  /** Table Row Sort **/
  $(document.body).on('click', ".sort", function () {
    $(this).toggleClass("asc").toggleClass("desc");
    var tr = Array.from($(".inner table").rows());
    tr.shift();
    tr.sort(
      comparer(
        Array.from(this.parentNode.children).indexOf(this),
        (this.asc = !this.asc)
      )
    ).forEach((tr) => $(".inner table tbody").append(tr));
  });

});

/**
 * DELETE SALES ORDER
 * @returns true if deleted
 */
fncDeleteSale = async (order_no) => {
  try {
    dispLoading("処理中．．．");

    const res = await fetch(`${API_PATH}salesDelete&order_no=${order_no}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      alert(data.error);
      return false;
    };

    alert(`受注番号[${order_no}]を削除しました。`);

    return true;
  } catch (error) {
    console.log(error);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading();
  }
}

//カンマ付与処理
function addComma(num) {
  num = num.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
  return num;
}

//カンマ削除処理
function delComma(num) {
  num = num.replace(/,/g, "");
  return num;
}

/**
 * 数字処理
 * - ￥を削除
 * - カンマを削除
 * @param {*} val
 * @returns val
 */
// function delYen(val) {
//   if (val.indexOf("￥") == -1) return val;
//   return val.replace(/[,￥]/g, "");
// }

//小数点以下が0であれば整数表記、3桁カンマ区切り
// function getNumericString(target) {
//   var result = target;

//   if (target != "" && target != null) {
//     if (target == ".00") {
//       return "0";
//     }
//     if (target.match(/[0]{2}$/) != null) {
//       result = target.replace(".00", "");
//       return addComma(result);
//     }
//     return addComma(result);
//   } else {
//     return "";
//   }
//}

//ページネーション
//c:現在ページ、m:最終ページ
/**
 * ページネーション
 * Creates page links
 * @param {int} c CURRENT PAGE、現在ページ
 * @param {int} m LAST PAGE、最終ページ
 * @param {int} cnt TOTAL COUNT
 * @param {String} frm THE FORM TO SEND
 */
function pagination(c, m, cnt, frm) {
  var current = c,
    last = m,
    delta = 3,
    left = current - delta,
    right = current + delta + 1,
    range = [],
    rangeWithDots = [],
    end;

  $("#pagenation").empty();
  $(".pageNo").empty();

  if (m > 4) {
    if (c <= delta) {
      for (let i = 1; i <= delta + 1; i++) {
        range.push(i);
      }
      range.push("...");
      range.push(last);
    } else {
      end = m - delta;
      if (c > end) {
        range.push(1);
        range.push("...");
        for (let i = end; i <= m; i++) {
          range.push(i);
        }
      } else {
        range.push(1);
        range.push("...");
        for (let i = c - 1; i <= c + 1; i++) {
          range.push(i);
        }
        range.push("...");
        range.push(last);
      }
    }
  } else {
    for (let i = 1; i <= m; i++) {
      range.push(i);
    }
  }
  if (c > 1) {
    $("#pagenation").append(
      $(`<a class="prevpostslink" rel="prev" form="${frm}" onClick=fncSearch(this,${c - 1})>＜</a>`)
    );
  }

  for (let i = 0; i < range.length; i++) {
    if (range[i] == c) {
      $("#pagenation").append(
        $(`<span aria-current="page" class="current">${c}</span>`)
      );
    } else if (range[i] == "...") {
      $("#pagenation").append($("<span>...</span>"));
    } else {
      $("#pagenation").append(
        $(`<a class="page" form="${frm}" onClick=fncSearch(this,${range[i]})>${range[i]}</a>`)
      );
    }
  }
  if (c < m) {
    $("#pagenation").append(
      $(`<a class="nextpostslink" rel="next" form="${frm}" onClick=fncSearch(this,${c + 1})>＞</a>`)
    );
  }

  $("#pagenation").append(`<p class="pages">Page ${c} of ${m}</p>`);
  $(".kensu").html(`件数：${addComma(String(cnt))}`);

  pagination_sp(c, m, frm);
}

function mesaiPagination(c, m, fnc) {
  var current = c,
    last = m,
    delta = 3,
    range = [],
    end;

  $("#mesaiPagination").empty();

  if (m > 4) {
    if (c <= delta) {
      for (let i = 1; i <= delta + 1; i++) {
        range.push(i);
      }
      range.push("...");
      range.push(last);
    } else {
      end = m - delta;
      if (c > end) {
        range.push(1);
        range.push("...");
        for (let i = end; i <= m; i++) {
          range.push(i);
        }
      } else {
        range.push(1);
        range.push("...");
        for (let i = c - 1; i <= c + 1; i++) {
          range.push(i);
        }
        range.push("...");
        range.push(last);
      }
    }
  } else {
    for (let i = 1; i <= m; i++) {
      range.push(i);
    }
  }
  if (c > 1) {
    $("#mesaiPagination").append(
      $(`<a class="prevpostslink" rel="prev" onClick=${fnc}(${c - 1})>＜</a>`)
    );
  }

  for (let i = 0; i < range.length; i++) {
    if (range[i] == c) {
      $("#mesaiPagination").append(
        $(`<span aria-current="page" class="current">${c}</span>`)
      );
    } else if (range[i] == "...") {
      $("#mesaiPagination").append($("<span>...</span>"));
    } else {
      $("#mesaiPagination").append(
        $(`<a class="page" onClick=${fnc}(${range[i]})>${range[i]}</a>`)
      );
    }
  }
  if (c < m) {
    $("#mesaiPagination").append(
      $(`<a class="nextpostslink" rel="next" onClick=${fnc}(${c + 1})>＞</a>`)
    );
  }
}
function salePagination(c, m, fnc) {
  var current = c,
    last = m,
    delta = 3,
    range = [],
    end;

  $("#salePagination").empty();

  if (m > 4) {
    if (c <= delta) {
      for (let i = 1; i <= delta + 1; i++) {
        range.push(i);
      }
      range.push("...");
      range.push(last);
    } else {
      end = m - delta;
      if (c > end) {
        range.push(1);
        range.push("...");
        for (let i = end; i <= m; i++) {
          range.push(i);
        }
      } else {
        range.push(1);
        range.push("...");
        for (let i = c - 1; i <= c + 1; i++) {
          range.push(i);
        }
        range.push("...");
        range.push(last);
      }
    }
  } else {
    for (let i = 1; i <= m; i++) {
      range.push(i);
    }
  }
  if (c > 1) {
    $("#salePagination").append(
      $(`<a class="prevpostslink" rel="prev" onClick=${fnc}(${c - 1})>＜</a>`)
    );
  }

  for (let i = 0; i < range.length; i++) {
    if (range[i] == c) {
      $("#salePagination").append(
        $(`<span aria-current="page" class="current">${c}</span>`)
      );
    } else if (range[i] == "...") {
      $("#salePagination").append($("<span>...</span>"));
    } else {
      $("#salePagination").append(
        $(`<a class="page" onClick=${fnc}(${range[i]})>${range[i]}</a>`)
      );
    }
  }
  if (c < m) {
    $("#salePagination").append(
      $(`<a class="nextpostslink" rel="next" onClick=${fnc}(${c + 1})>＞</a>`)
    );
  }
}
//ページネーション・スマートフォン用
//c:現在ページ、m:最終ページ
/**
 * ページネーション（スマートフォン用）
 * Creates page links for Phone
 * @param {int} c CURRENT PAGE、現在ページ
 * @param {int} m LAST PAGE、最終ページ
 * @param {String} frm THE FORM TO SEND
 */
function pagination_sp(c, m, frm) {
  var next = c + 1;
  var prev = c - 1;

  $("#pagenation_sp").empty();

  if (c > 1) {
    $("#pagenation_sp").append(
      $(`<a class="prevpostslink" rel="prev" form="${frm}" onClick="fncSearch(this, ${prev})">＜前へ</a>`)
    );
  } else {
    $("#pagenation_sp").append($("<a></a>"));
  }

  if (c < m) {
    $("#pagenation_sp").append(
      $(`<a class="nextpostslink" rel="next" form="${frm}" onClick="fncSearch(this, ${next})">次へ＞</a>`)
    );
  }
}

//ページネーションの最終ページを求める
function paginationLastPage(datalen) {
  var pageof_num = $("#pageof_num").val();
  var last = Math.floor(datalen / pageof_num);
  var LastPage = 1;
  if (last == 0) {
    LastPage = 1;
  } else {
    if (datalen % pageof_num == 0) {
      LastPage = last;
    } else {
      LastPage = last + 1;
    }
  }

  return LastPage;
}

//総件数を表示する
function paginationTotalKensu(datalen) {
  $("#list_cnt").html("件数：" + addComma(String(datalen)));
}

/** 今日の日付（yyyy-mm-dd）**/
function todayDate() {
  var year = new Date().getFullYear();
  var month = new Date().getMonth() + 1;
  month = month < 10 ? "0" + month : month;
  var day = new Date().getDate();
  day = day < 10 ? "0" + day : day;
  return year + "-" + month + "-" + day;
}
/** 翌日の日付（yyyy-mm-dd）**/
function tommorrowDate() {
  var today = new Date();
  var tomorrow = new Date();
  tomorrow.setDate(today.getDate() + 1);

  var year = tomorrow.getFullYear();
  var month = tomorrow.getMonth() + 1;
  var day = tomorrow.getDate();

  var tomorrowDateString = year + '-' + month.toString().padStart(2, '0') + '-' + day.toString().padStart(2, '0');

  return tomorrowDateString;
}

/** 今月（yyyy-mm）**/
function thisMonth() {
  var year = new Date().getFullYear();
  var month = new Date().getMonth() + 1;
  month = month < 10 ? "0" + month : month;

  return year + "-" + month;
}

/** 今月（yyyy-mm-01）**/
function thisMonthFrstDay() {
  var year = new Date().getFullYear();
  var month = new Date().getMonth() + 1;
  month = month < 10 ? "0" + month : month;
  var day = "01";
  return year + "-" + month + "-" + day;
}
function thisMonthLastDay() {
  var year = new Date().getFullYear();
  var month = new Date().getMonth();

  var date = new Date(year, month + 1, 0);
  var yearStr = date.getFullYear().toString();
  var monthStr = (date.getMonth() + 1).toString().padStart(2, '0');
  var dayStr = date.getDate().toString().padStart(2, '0');
  return yearStr + '-' + monthStr + '-' + dayStr;
}

/** 前月（yyyy-mm-dd）**/
function prevMonthFrstDay() {
  var year = new Date().getFullYear();
  var month = new Date().getMonth();
  var d = new Date(year, month, 0).toISOString().substring(0, 8);
  return d + "01";
}
function prevMonthLastDay() {
  var year = new Date().getFullYear();
  var month = new Date().getMonth();

  var date = new Date(year, month, 0);
  var yearStr = date.getFullYear().toString();
  var monthStr = (date.getMonth() + 1).toString().padStart(2, '0');
  var dayStr = date.getDate().toString().padStart(2, '0');
  return yearStr + '-' + monthStr + '-' + dayStr;
}
/** 前月（yyyy-mm-dd_HHmmss）**/
function fileDateTime() {
  var date = new Date();
  var Y = date.getFullYear();
  var M = date.getMonth() + 1;
  var D = date.getDate();
  var H = date.getHours();
  var m = date.getMinutes();
  var s = date.getSeconds();
  return `${Y}${M}${D}${H}${m}${s}`;
}

// JavaScript Document

var isTouch = false;
var isIpad = false;
if (
  navigator.userAgent.indexOf("iPhone") > 0 ||
  navigator.userAgent.indexOf("iPod") > 0 ||
  navigator.userAgent.indexOf("iPad") > 0 ||
  navigator.userAgent.indexOf("Windows Phone") > 0 ||
  navigator.userAgent.indexOf("BlackBerry") > 0 ||
  navigator.userAgent.indexOf("Android") > 0
) {
  isTouch = true;
  if (navigator.userAgent.indexOf("iPad") > 0 || navigator.userAgent.indexOf("tablet") > 0) {
    isIpad = true;
  }
}

// jQuery(function () {
//   if (isTouch) {
//     initializeMobile();
//   } else {
//     jQuery("#openmenu").attr("style", "display:none !important;");
//     setBacktopButton();
//     jQuery("a,.button").on({
//       // mouseenter: function () {
//       // 	jQuery(this).stop(true).css("opacity", 0.5);
//       // },
//       // mouseleave: function () {
//       // 	jQuery(this).stop(true).css("opacity", 0.5).animate({
//       // 		opacity: 1
//       // 	}, 150, "linear");
//       // }
//     });
//   }

//   /* ローディング */

//   $(function () {
//     var h = $(window).height();

//     $("#wrap").css("display", "none");
//     $("#loader-bg ,#loader").height(h).css("display", "block");
//   });

//   $(window).load(function () {
//     //すべての読み込みが完了したら実行
//     $("#loader-bg").delay(900).fadeOut(800);
//     $("#loader").delay(600).fadeOut(300);
//     $("#wrap").css("display", "block");
//   });

//   //5秒たったら強制的にロード画面を非表示
//   $(function () {
//     setTimeout("stopload()", 5000);
//   });

//   function stopload() {
//     $("#wrap").css("display", "block");
//     $("#loader-bg").delay(900).fadeOut(800);
//     $("#loader").delay(600).fadeOut(300);
//   }

//   function initializeMobile() {
//     $("#mobilemenu a.close, #openmenu").on("click", toggleMenu);

//     function toggleMenu() {
//       if (!$("#mobilemenu").hasClass("open")) {
//         $("#mobilemenu").addClass("open");
//         $("body, html").css({
//           overflow: "hidden",
//         });
//         $("#mobilemenu, #openmenu").css({
//           top: $("body").scrollTop(),
//           position: "absolute",
//         });
//         $("#innerwrap, #openmenu").stop(true).transitionStop(true).transit(
//           {
//             x: 276,
//           },
//           500,
//           "cubic-bezier(0, 0.99, 0.13, 0.995)"
//         );
//         $("body").on("touchmove", function (e) {
//           e.preventDefault();
//         });

//         $("#wrapper").append($("<div></div>").addClass("mask"));
//       } else {
//         $("#mobilemenu").removeClass("open");
//         $("body, html").css({
//           overflow: "visible",
//         });
//         $("#mobilemenu, #openmenu").css({
//           top: 0,
//           position: "fixed",
//         });
//         $("#innerwrap, #openmenu").stop(true).transitionStop(true).transit(
//           {
//             x: 0,
//           },
//           500,
//           "cubic-bezier(0, 0.99, 0.13, 0.995)"
//         );
//         $("body").off("touchmove");

//         $("#wrapper div.mask").remove();
//       }
//       return false;
//     }

//     var scrolling = false;
//     $("a#backtop").on("click", backToTop);
//     $("body").on("touchstart", stopScrolling);
//     function backToTop() {
//       scrolling = true;
//       $("html, body")
//         .stop(true)
//         .animate({ scrollTop: 0 }, 750, "easeOutExpo", function () {
//           scrolling = false;
//         });
//       return false;
//     }
//     function stopScrolling() {
//       if (scrolling) {
//         scrolling = false;
//         $("html, body").stop(true);
//       }
//     }
//   }

//   function setBacktopButton() {
//     var backtopButton = $("a#backtop");
//     var visibleBorder = 50;
//     var scrolling = false;

//     backtopButton.on("click", backToTop);

//     $(window).on("mousewheel", stopScrolling);

//     $(window).on("scroll", scrollHandler);
//     scrollHandler();

//     function backToTop() {
//       if ($(this).hasClass("disable") || scrolling) return false;

//       scrolling = true;
//       $("html, body")
//         .stop(true)
//         .animate({ scrollTop: 0 }, 750, "easeOutExpo", function () {
//           scrolling = false;
//         });
//       return false;
//     }
//     function stopScrolling() {
//       if (scrolling) {
//         scrolling = false;
//         $("html, body").stop(true);
//       }
//     }
//     function scrollHandler() {
//       var scrollTop = $(window).scrollTop();
//       var totalHeight = $(document).height();
//       var viewportHeight = $(window).height();
//       var limit = totalHeight - viewportHeight - 63;

//       if (scrollTop >= limit) {
//         if (!backtopButton.hasClass("limit")) {
//           backtopButton.addClass("limit");
//         }
//       } else {
//         if (backtopButton.hasClass("limit")) {
//           backtopButton.removeClass("limit");
//         }
//       }

//       if (backtopButton.hasClass("disable")) {
//         if (scrollTop >= visibleBorder) {
//           backtopButton
//             .removeClass("disable")
//             .stop(true)
//             .css({
//               display: "block",
//               opacity: 0,
//             })
//             .animate(
//               {
//                 opacity: 1,
//               },
//               250,
//               "easeOutExpo"
//             );
//         }
//       } else {
//         if (scrollTop <= visibleBorder) {
//           backtopButton
//             .addClass("disable")
//             .stop(true)
//             .animate(
//               {
//                 opacity: 0,
//               },
//               250,
//               "easeOutExpo",
//               function () {
//                 display: "none";
//               }
//             );
//         }
//       }
//     }
//   }
// })(jQuery);

/* ------------------------------
 Loading イメージ表示関数
 引数： msg 画面に表示する文言
 ------------------------------ */
function dispLoading(msg) {
  // 引数なし（メッセージなし）を許容
  if (msg == undefined) {
    msg = "";
  }
  // 画面表示メッセージ
  var dispMsg = "<div class='loadingMsg'>" + msg + "</div>";
  // ローディング画像が表示されていない場合のみ出力
  if ($("#loading").length == 0) {
    $("body").append("<div id='loading'>" + dispMsg + "</div>");
  }
}

/* ------------------------------
 Loading イメージ削除関数
 ------------------------------ */
function removeLoading() {
  $("#loading").remove();
}

function isNumeric(target) {
  if (target != "" && target != null && !target.match(/^[0-9]*$/)) {
    return false;
  }
  return true;
}

//日付を作成
function createDate() {
  const d = new Date();

  var year = d.getFullYear();
  var month = d.getMonth() + 1;
  if (month < 10) {
    month = "0" + month;
  }
  var day = d.getDate();
  if (day < 10) {
    day = "0" + day;
  }
  var hour = d.getHours();
  if (hour < 10) {
    hour = "0" + hour;
  }
  var min = d.getMinutes();
  if (min < 10) {
    min = "0" + min;
  }
  var sec = d.getSeconds();
  if (sec < 10) {
    sec = "0" + sec;
  }

  var date =
    year + "_" + month + "_" + day + "(" + hour + "-" + min + "-" + sec + ")";

  return date;
}

/**
 * CHECK IF STRING IS 全角カタカナ
 * @param {String} value String being checked
 * @returns Boolean
 */
function isZenKakuKana(value) {
  var l = value.length
  for (let i = 0; i < l; i++) {
    const code = value.charCodeAt(i);
    //code >= 0x30A0 && code <= 0x30FF
    if ((code >= 0x30A0 && code <= 0x30FF) || (code >= 0xFF10 && code <= 0xFF19)) {
      continue;
    } else {
      return false;
    }
  }
  return true;
}

/**
 * CHECK IF STRING IS 半角カタカナ
 * @param {String} value String being checked
 * @returns Boolean
 */
function isHankaku(str) {
  return !!str.match(/^[ｦ-ﾟ 0-9 ･]*$/);
  // var l = str.length;
  // for (let i = 0; i < l; i++) {
  //   const code = str.charCodeAt(i);
  //   //code > 255 || (code >= 0xFF66 && code <= 0xFF9F) || (code >= 0xFF10 && code <= 0xFF19)
  //   if (code > 255 || (code >= 0x30A0 && code <= 0x30FF) || (code >= 0xFF10 && code <= 0xFF19)) {
  //     return false; // If a character is not hankaku, return false
  //   }
  // }
  //return true; // All characters are hankaku
}
/**
 * INITAILIZE ADD FORM
 * - enable all inputs
 */
function addInit() {
  $(".itemEdit input, .itemEdit option, .itemEdit textarea").prop("disabled", false);
  $(".itemEdit select").prop("selectedIndex", 0);
  $(".itemEdit input, .itemEdit textarea").val("");
  $(".itemEdit input[type=checkbox]").prop("checked", false);
  $(".itemEdit label").text("");
  $(".id-disp input").attr("type", "text");
  $(".id-disp label").hide();
  $(".pwd").show();
  $(".err_msg").text("").slideUp();
  $(".inputError").removeClass("inputError");
}

/**
 * INITAILIZE EDIT FORM
 * - disable all inputs
 */
function editInit() {
  $(".itemEdit input, .itemEdit option, .itemEdit textarea").prop("disabled", true);
  $(".id-disp input").attr("type", "hidden");
  $(".pwd").hide();
  $(".id-disp label").show();
  $(".err_msg").text("").slideUp();
  $(".inputError").removeClass("inputError");
}

async function tokuisakiTelCheck(val) {
  try {
    const res = await fetch(`${API_PATH}tokuisakiTelCheck&tokuisaki_tel=${val}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    };

    const tel = await res.json();

    if (tel.hasOwnProperty("error")) {
      console.log(tel.error);
      return tel.error;
    };

    return tel;
  } catch (err) {
    console.log(err);
  }
}

function dispSaleDialog(order_no) {
  $("#order_no").val(order_no);
  $("iframe").attr("src", `common/sales_form.php?order_no=${order_no}`);
  $(`#saleDialog`)
    .dialog({
      title: "売上詳細",
      modal: true,
      height: screen.availHeight * 0.8,
      maxHeight: screen.availHeight,
      width: isTouch ? screen.availWidth - 10 : screen.availWidth * 0.95,
      resizable: false,
      draggable: false
    })
    .css({ padding: 0, overflow: "hidden" });
}

/**
 * CREATE DENPYO・売上伝票
 */
async function createDenpyo(order_no) {
  const res = await fetch(`/php/pcService.php?Type=uriageDenpyo&order_no=${order_no}`);

  if (!res.ok) {
    alert("ネットワークエラーが発生しました。");
    return;
  };

  const type = res.headers.get("content-type");

  // If Error
  if (type.indexOf("text/html") !== -1) {
    const data = await res.json();
    alert(data.error);
    console.log(data);
    return;
  };

  const data = await res.blob();

  var link = document.createElement("a");
  link.href = window.URL.createObjectURL(data);
  link.download = `売上伝票_${order_no}_${fileDateTime()}.pdf`;
  link.click();
}

/**
 * OUTPUT RECEIPT・領収書
 */
async function createReceipt(order_no) {
  const res = await fetch(`/php/pdfApi.php?Type=receiptPdf&order_no=${order_no}`);

  if (!res.ok) {
    alert("ネットワークエラーが発生しました。");
    return;
  };

  const type = res.headers.get("content-type");

  // If Error
  if (type.indexOf("text/html") !== -1) {
    const data = await res.json();
    alert(data.error);
    console.log(data);
    return;
  };

  const data = await res.blob();

  var link = document.createElement("a");
  link.href = window.URL.createObjectURL(data);
  link.download = `領収書_${order_no}_${fileDateTime()}.pdf`;
  link.click();
}

/**
 * OUTPUT HIKAE DENPYO・出荷依頼書（売上伝票控）
 */
async function createDenpyoHikae(order_no) {
  const res = await fetch(`/php/pcService.php?Type=hikaePdf&order_no=${order_no}`);

  if (!res.ok) {
    alert("ネットワークエラーが発生しました。");
    return;
  };

  const type = res.headers.get("content-type");

  // If Error
  if (type.indexOf("text/html") !== -1) {
    const data = await res.json();
    alert(data.error);
    console.log(data);
    return;
  };

  const data = await res.blob();

  var link = document.createElement("a");
  link.href = window.URL.createObjectURL(data);
  link.download = `売上伝票控_${order_no}_${fileDateTime()}.pdf`;
  link.click();
}

/**
 * CREATE A3 注文書・納品書
 */
async function createNouhin(order_no) {
  const res = await fetch(`/php/pcService.php?Type=nouhinPdf&order_no=${order_no}`);

  if (!res.ok) {
    alert("ネットワークエラーが発生しました。");
    return;
  };

  const type = res.headers.get("content-type");

  // If Error
  if (type.indexOf("text/html") !== -1) {
    const data = await res.json();
    alert(data.error);
    console.log(data);
    return;
  };

  const data = await res.blob();

  var link = document.createElement("a");
  link.href = window.URL.createObjectURL(data);
  link.download = `注文書・納品書_${order_no}_${fileDateTime()}.pdf`;
  link.click();
}

function subLoading(obj, w = 0, h = 0, top = 65, position = 'fixed') {
  // 画面表示メッセージ
  var dispMsg = "<div class='loadingMsg'>処理中．．．</div>";
  // ローディング画像が表示されていない場合のみ出力
  //if ($("#loading").length == 0) {
  $(obj).append("<div id='subLoading'>" + dispMsg + "</div>");
  //}
  w = ($(obj).width() <= 0) ? w : $(obj).width();
  h = ($(obj).height() <= 0) ? h : $(obj).height();
  $(obj).find("#subLoading").css({ "width": w, "height": h, 'position': position, 'top': top});
}

function removeSubLoading(obj) {
  $(obj).find("#subLoading").remove();
}

/** ROW SORT **/
var getCellValue = (tr, idx) =>
  delComma(tr.children[idx].innerText) || tr.children[idx].textContent;

var comparer = (idx, asc) => (a, b) =>
  ((v1, v2) =>
    v1 !== "" && v2 !== "" && !isNaN(v1) && !isNaN(v2)
      ? v1 - v2
      : v1.toString().localeCompare(v2))(
        getCellValue(asc ? a : b, idx),
        getCellValue(asc ? b : a, idx)
      );

(function ($) {
  $.fn.rows = function () {
    var tbl = document.getElementById(this[0].id);
    return tbl.rows;
  };
})(jQuery);

async function cancelSale() {
  try {
    const res = await fetch(`${API_PATH}salesCancel`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      throw new Error(data.error);
    }

  } catch (error) {
    console.log(error);
    throw error;
  }
}
