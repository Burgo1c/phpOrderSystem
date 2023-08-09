/**
 * USED WHEN ADDING NEW ROW
 * - in the id or row-index attr
 */
var iRow = 1;
/**
 * USED FOR FUNCTION KEY EVENTS
 */
var ctrl_key = false;
/**
 * NOT IN USE
 */
var abortControl;
/**
 * NOT IN USE
 */
var signal;
/**
 * USED TO INDICATE UPDATE MODE
 */
var update_flg = false;
/**
 * USED FOR SQL OFFSET WHEN SCROLLING
 */
var offset = 0;
/**
 * USED TO INDICATE IF AJAX REQUEST IS ACTIVE
 */
var _ajax = false;
/**
 * USED TO GET CURRENT POSISTION OF TABLE SCROLL
 */
var tableLength = 0;
/**
 * NOT IN USE
 */
var _updating = false;
/**
 * USED TO INDICATE CANCEL OF AJAX REQUEST
 */
var _cancel = false;
/**
 * API PATH 
 */
var _API_PATH = "/php/pcService.php?Type="

/**
 * カンマ付与処理
 * - f.format(数字) => 123,456
 */
const f = new Intl.NumberFormat();

/**
 * USED FOR GET TOKUISAKI AJAX REQUEST
 */
var tokuAjax = false;

/**
 * USED FOR GET OKURISAKI AJAX REQUEST
 */
var okuriAjax = false;

$(document).ready(() => {

  $("form").on('submit', function (e) {
    e.preventDefault();
  })

  /** CACHE ELEMENTS **/
  cacheObjects();

  /** GET&DISPLAY MEMO **/
  getMemo();
  /** GET&DISPLAY 売上明細履歴 **/
  // getMesaiHistory(1);
  /** GET&DISPLAY 売上履歴 **/
  // getSalesHistory(1);

  /** 更新状態 CHECK
   * - When displaying in 照会画面 
   **/
  var params = location.href;
  if (params.indexOf("?") != -1) {
    $(".container").addClass("detail");
    params = params.split("=");

    $("form input, form option, textarea, button").prop("disabled", true);
    $("#btnEdit").removeClass("disnon");
    $("#btnRegMain, #btnCancel").hide();

    //KEEP MEMO ACTIVE
    $memo.prop("disabled", false);
    $("#btnEdit, #memoBtn").prop("disabled", false);
    //GET 詳細
    fncSalesDetail(params[1]);
    update_flg = true;

  } else {
    _API_PATH = API_PATH;
    // $(".container").css("height", "calc(100vh - 220px)")

    /** GET ORDER NUMBER **/
    getOrderNo();

    /** 売上日 **/
    salesForm.saleDt.val(todayDate());
    /** お届け日 **/
    salesForm.recieveDt.val(tommorrowDate());

    $(".container").removeClass("disnon");
    $("#btnInqueryNo").hide();

    /** キャンセルボタン**/
    document.getElementById("btnCancel").addEventListener("click", function () {
      fncCancel();
      _cancel = false;
    });
    $(".input_box").each(function () {
      $(this).val($(this).next().val());
    });
    // $("#btnCancel").click(() => {
    //   fncCancel();
    //   _cancel = false;
    // });

    if (salesForm.tokuisakiNm.text() == '') {
      $("#mesai_table input").prop('disabled', true);
      salesForm.nextKbn.prop('disabled', true);
      salesForm.nextKbn2.prop('disabled', true);
      salesForm.nextKbn3.prop('disabled', true);
    };
  };

  salesForm.tokuisakiTel.focus();

  $("#formToggle").on('click', function () {
    let screen = $(this).attr("screen");

    if (screen == "right") {
      $(".left").addClass("hide");
      $(".right").removeClass("hide");
      $(this).attr("screen", "left");
      $(this).text("売上伝票");
    } else {
      $(".right").addClass("hide");
      $(".left").removeClass("hide");
      $(this).attr("screen", "right");
      $(this).text("詳細情報");
    }
  });

  /** 得意先電話番号で検索 **/
  salesForm.tokuisakiTel.on("keydown", function (e) {
    if (e.which == 9) {
      e.preventDefault();
      $(".livesearch_row").slideUp();
      $("#mesai_shohin_cd_1").focus().select();
    }
  });
  salesForm.tokuisakiTel.on("keyup", async function (e) {

    if (e.which == 13) {
      //nextMesaiInput(this);
      $(".livesearch_row").slideUp();
      if (salesForm.tokuisakiNm.text() != "") {
        $("#mesai_shohin_cd_1").focus().select();
        return;
      }

      let val = this.value;
      $(".livesearch_row li").each(function () {
        var text = $(this).text().split("　");
        if (val === text[0]) {
          getTokuisakiById(this.id);
          getOkurisakiById(this.id, 1);
          return;
        }
      });
      return;
    }

    /** （左側）電話番号を（右側）電話番号に反映 **/
    //tokuisakiForm.tokuisakiTel.val($(this).val());

    if ($(this).val() == "") {
      fncDeleteRow();
      $("#mesai_table input").prop('disabled', true);
      salesForm.tokuisakiNm.text("");
      //salesForm.tokuisakiZip.text("");
      salesForm.tokuisakiAdr1.text("");
      //salesForm.tokuisakiAdr2.text("");
      //salesForm.tokuisakiAdr3.text("");
      salesForm.nextKbn.prop("selectedIndex", 0).prop('disabled', true).css("background-color", "#fff");
      salesForm.nextKbn2.prop("selectedIndex", 0).prop('disabled', true).css("background-color", "#fff");
      salesForm.nextKbn3.prop("selectedIndex", 0).prop('disabled', true).css("background-color", "#fff");

      salesForm.comment.val("");
      salesForm.totalQty.text('');
      salesForm.totalAmt.text('');
      salesForm.tax8.text('');
      salesForm.tax10.text('');
      salesForm.grandTotal.text('');
      salesForm.kosu.val('');
      salesForm.yamatoKbn.prop("selectedIndex", 0);
      salesForm.yamatoKbn.prev().val(0);
      salesForm.deliveryTimeKbn.prop("selectedIndex", 0);
      salesForm.deliveryTimeKbn.prev().val(1);
      salesForm.deliveryInstructKbn.prop("selectedIndex", 0);
      salesForm.deliveryInstructKbn.prev().val(1);
      salesForm.deliveryTimeHr.val('');
      salesForm.deliveryTimeMin.val('');

      $("#uriage_table tbody, #sales_history_table tbody,  #content_1 .pagenavi, #content_2 .pagenavi").empty();

      tokuisakiForm.tokuisakiCd.val("");
      tokuisakiForm.tokuisakiName.val("");
      tokuisakiForm.tokuisakiKana.val("");
      tokuisakiForm.tokuisakiZip.val("");
      tokuisakiForm.tokuisakiAdr1.val("");
      tokuisakiForm.tokuisakiAdr2.val("");
      tokuisakiForm.tokuisakiAdr3.val("");
      tokuisakiForm.tokuisakiDelivery.val("");
      tokuisakiForm.tokuisakiIndustry.prop("selectedIndex", 0);
      tokuisakiForm.tokuisakiTel.val("");
      tokuisakiForm.tokuisakiYobiTel.val("");
      tokuisakiForm.tokuisakiFax.val("");
      tokuisakiForm.tokuisakiTanto.val("");
      tokuisakiForm.tokuisakiComment.val("");

      okurisakiForm.tokuisakiCd.val("");
      okurisakiForm.okurisakiCd.val("");
      okurisakiForm.okurisakiName.val("");
      okurisakiForm.okurisakiKana.val("");
      okurisakiForm.okurisakiTel.val("");
      okurisakiForm.okurisakiFax.val("");
      okurisakiForm.okurisakiZip.val("");
      okurisakiForm.okurisakiAdr1.val("");
      okurisakiForm.okurisakiAdr2.val("");
      okurisakiForm.okurisakiAdr3.val("");
      okurisakiForm.okurisakiTanto.val("");
      okurisakiForm.okurisakiYobiTel.val("");
      //okurisakiForm.industryCd.prop("selectedIndex", 0);
      okurisakiForm.okurisakiDelivery.val("");

      $("#okurisaki_nav").addClass("disnon");

      $(".livesearch_row").slideUp();
      return;
    }
    offset = 0;
    tokuisakiTelList($(this).val());

  });

  /**
   * TEL LIVESEARCH ON SCROLL GET NEXT 50
   */
  $(".livesearch").on("scroll", async function () {
    if (_ajax) return;
    const { scrollTop, scrollHeight, clientHeight } = this;
    if (scrollTop + clientHeight >= scrollHeight - 75) {
      _ajax = true;
      offset += 50;
      // user has scrolled to the 75%, load next 50
      tokuisakiTelList(tokuisakiForm.tokuisakiTel.val());
    };
  });


  /** SELECT TEL ROW **/
  $(".livesearch_row").on('click', '.livesearch_tel', function () {
    $(".livesearch_row").slideUp();
    fncDeleteRow();
    salesForm.tokuisakiTel.val($(this).text());
    // GET tokuisaki
    getTokuisakiById(this.id);
    //GET okurisaki
    getOkurisakiById(this.id, 1);
    okurisakiForm.errorRow.hide();
    $("#mesai_shohin_cd_1").focus();
  })

  /** TOKUISAKI SUB SEARCH DIALOG **/
  salesForm.tokuisakiNm.dblclick(() => {
    if (update_flg) return
    fncDispTokuisakiSearch();
  });

  /** SELECT TOKUISAKI ROW **/
  $("#tokuisakiSrchFrm").on('dblclick', 'tr', function () {
    fncDeleteRow();
    // GET tokuisaki
    getTokuisakiById(this.id);
    //GET okurisaki
    getOkurisakiById(this.id, 1);
    $("#tokuisaki-search").dialog("destroy");

    if (salesForm.tokuisakiTel.hasClass("error")) {
      salesForm.tokuisakiTel.removeClass("error");
      salesForm.tokuisakiTel.closest("div").removeClass("inputError");
      salesForm.errorRow.slideUp();
    };
    okurisakiForm.errorRow.hide();
    $(".livesearch_row").slideUp();
    $("#mesai_shohin_cd_1").focus();
  })

  /** ZIP LIVESEARCH **/
  tokuisakiForm.tokuisakiZip.keyup(async function () {
    if ($(this).val() == "" || $(this).val().length < 7) return;
    zipLiveSearch($(this).val());
  });
  okurisakiForm.okurisakiZip.keyup(async function () {
    if ($(this).val() == "" || $(this).val().length < 7) return;
    zipLiveSearch($(this).val());
  });

  /** MESAI TABLE PRODUCT SEARCH **/
  $("#mesai_table").on("keyup", "[id^=mesai_shohin_cd_]", async function (e) {
    e.preventDefault();
    if (e.which == 13) {
      //nextMesaiInput(this);
      return;
    };
    let row = $(this).attr("row-index");
    if ($(this).val().length != 3) {
      $(`#mesai_table tbody #mesai_shohin_nm_${row}`).html("");
      $(`#mesai_table tbody #mesai_tanka_${row}`).html("");
      $(`#mesai_table tbody #mesai_tax_rate_${row}`).val("");
      $(`#mesai_table tbody #mesai_shohin_qty_${row}`).val("");
      $(`#mesai_table tbody #mesai_row_total_${row}`).text("");
      return;
    };

    await getMesaiProduct($(this).val(), row);
  });

  /** PRODUCT SEARCH DIALOG **/
  //#shohin_cd
  $("#mesai_table").on("dblclick", "[id^=mesai_shohin_cd_]", function () {
    if ($(this).prop("disabled")) return;
    dispSubProductSearch($(this).attr("row-index"));
  });

  /** SELECT TOKUISAKI ROW **/
  $("#productSrchFrm").on('dblclick', 'tr', function () {
    let ary = $(this).children();
    // $(`#mesai_table #row_${$(this).attr("row-index")} #shohin_cd`).val($(ary[1]).text());
    // $(`#mesai_table #row_${$(this).attr("row-index")} #shohin_nm`).text($(ary[2]).text());
    // $(`#mesai_table #row_${$(this).attr("row-index")} #tanka`).text(delComma($(ary[3]).text()));
    // $(`#mesai_table #row_${$(this).attr("row-index")} #tax_rate`).val($(ary[0]).val());
    $(`#mesai_table #mesai_shohin_cd_${$(this).attr("row-index")}`).val($(ary[1]).text());
    $(`#mesai_table #mesai_shohin_nm_${$(this).attr("row-index")}`).text($(ary[2]).text());
    $(`#mesai_table #mesai_tanka_${$(this).attr("row-index")}`).text(delComma($(ary[3]).text()));
    $(`#mesai_table #mesai_tax_rate_${$(this).attr("row-index")}`).val($(ary[0]).val());

    $("#product-search").dialog("destroy");

    if (iRow == 1 || $(`#mesai_table tbody tr:last .shohin_nm`).text() != "") {
      addRow();
    };

    if ($(`#mesai_table #mesai_shohin_qty_${$(this).attr("row-index")}`).val() != "") {
      autoCalculate($(this).attr("row-index"));
    }

  })

  /** ON SCROLL GET NEXT 50 **/
  $("#productSrchFrm table tbody").on("scroll", async function () {
    if (_ajax) return;
    const { scrollTop, scrollHeight, clientHeight } = this;
    if (scrollTop + clientHeight >= scrollHeight - 75) {
      _ajax = true;
      offset += 50;
      let row = $(this).find(":first-child").attr("row-index");
      // user has scrolled to the 75%, load next 20
      productSearch(row);
    };
  });

  /** MESAI TABLE QTY COMMA **/
  $("#mesai_table").on("keyup", "[id^=mesai_shohin_qty_]", function (e) {
    e.preventDefault();

    // if ($(this).val() == "") {
    //   if (e.which == 13) {
    //     nextMesaiInput(this);
    //     return;
    //   }
    //   return;
    // };

    var num = $(this).val();

    // if ($("#akaden").prop("checked")) {
    num = num.replace(/[^-0-9.]|(?<=.)-/g, "") ?? "";
    $(this).val(num);
    // } else {
    //   num = num.replace(/\D/g, "") ?? "";
    // }

    // if (num != "" && num != "-") {
    //   num = Number(num).toFixed(1);
    // };

    let id = $(this).attr("row-index");

    //ENTER KEY
    // if (e.which == 13) {
    //   autoCalculate(id);
    //   nextMesaiInput(this);
    //   return;
    // }

  });

  $("#mesai_table").on("keydown", "[id^=mesai_shohin_qty_]", function (e) {
    // e.preventDefault();
    let id = $(this).attr("row-index");

    //TAB KEY
    if (e.which == 9) {
      e.preventDefault();
      //autoCalculate(id);
      //nextMesaiInput(this);
      var i = $(this);
      i = $("input[type=text],input[type=date],input[type=tel],textarea").index(i);
      $("input[type=text],input[type=date],input[type=tel],textarea").eq(i + 1).focus().select();
      return;
    };
  });

  $("#mesai_table").on('blur', "[id^=mesai_shohin_qty_]", function () {
    let id = $(this).attr("row-index");
    autoCalculate(id);
  })

  $("#mesai_table").on('focus', "[id^=mesai_shohin_qty_]", function () {
    $(this).val(delComma($(this).val()));
  })
  // $("#mesai_table").on("keydown", "[id^=mesai_shohin_qty_]", function (e) {
  //   // if ($(this).val() == "") {
  //   //   if (e.which == 13) {
  //   //     nextMesaiInput(this);
  //   //     return;
  //   //   }
  //   //   return;
  //   // };
  //   let id = $(this).attr("row-index");
  //   //TAB KEY
  //   if (e.which == 9) {
  //     autoCalculate(id);
  //     return;
  //   };
  //   //ENTER KEY
  //   if (e.which == 13) {
  //     autoCalculate(id);
  //     //nextMesaiInput(this);
  //     return;
  //   }
  // })

  /** SAGAWA TIME ONLY ALLOW NUMBERS **/
  salesForm.deliveryTimeHr.on('keyup', function () {
    salesForm.deliveryTimeHr.val(salesForm.deliveryTimeHr.val().replace(/\D/g, ""));
  })
  salesForm.deliveryTimeMin.on('keyup', function () {
    salesForm.deliveryTimeMin.val(salesForm.deliveryTimeMin.val().replace(/\D/g, ""));
  })

  /** REGISTER (MAIN) **/
  $("#btnRegMain").click(() => {
    fncReg();
  });

  /** SALE HISTORY DETAIL **/
  $("#sales_history_table tbody").on('click', 'tr', function () {
    if (!update_flg || this.id != salesForm.orderNo.text()) {
      $("#btnInqueryNo").hide();
    } else {
      $("#btnInqueryNo").show();
    };
    $(".activeTr").removeClass("activeTr");
    $(this).addClass("activeTr");
    salesHistoryDetail(this.id);
  })

  /** INQUIRE NO・問い合わせ番号 **/
  $("#btnInqueryNo").click(() => {
    if (!update_flg) return;
    if (salesHistory.orderNo.text() == "" || salesHistory.orderNo.text() != salesForm.orderNo.text()) return;
    fncDispInqueryChange();
  });

  $("#inqueryNoFrm input[name=inquire_no]").on('keyup', function () {
    this.value = this.value.replace(/[a ]/g, "");
  })

  /** 新規画面の右側の操作ボタン **/
  $(".tab label").on("click", function () {
    var obj = $(this).attr("tab-content");

    $(".tab .activeBtn").removeClass("activeBtn");
    $(this).addClass("activeBtn");

    $(".tab_content.activeTbl").addClass("disnon").removeClass("activeTbl");
    $(`#${obj}`).addClass("activeTbl").removeClass("disnon");
  });

  /** FAX COPY BTN **/
  $(".faxCopyBtn").click(() => {
    tokuisakiForm.tokuisakiFax.val(tokuisakiForm.tokuisakiTel.val());
    okurisakiForm.okurisakiFax.val(okurisakiForm.okurisakiTel.val());
  });

  /** OKURISAKI NAV **/
  $("#okurisaki_nav .prevpostslink, #okurisaki_nav .nextpostslink").click(function () {
    //GET okurisaki
    getOkurisakiById(tokuisakiForm.tokuisakiCd.val(), $(this).attr('page'));
  })

  /**
 * 複写
 */
  $("#copyBtn").click(() => {
    $("#akaden").prop("checked", false);
    copyPrevOrder();
  })

  /**
   * 赤伝
   */
  $("#minusDen").click(() => {
    $("#akaden").prop("checked", true);
    minuseDen();
  })

  /**
   * TOKUISAKI COMMENT
   * link text
   */
  $(".tokuisaki_comment").keyup(function () {
    $(".tokuisaki_comment").val($(this).val());
  })

  /** 得意先登録 **/
  $("#btnTokuisakiReg").click(async () => {
    fncTokuisakiReg();
  });

  /** 送り先登録 **/
  $("#btnOkuriReg").click(async () => {
    fncOkurisakiReg();
  })

  /** TEL EDIT **/
  $("input[type=tel]").keyup(function () {
    $(this).val($(this).val().replace(/[^0-9]/g, ''));
  });

  /** FORM REMOVE inputError CLASS **/
  // $("input,select,textarea").on('input', function (e) {
  //   // if ($(this).hasClass("inputError")) {
  //   //   $(this).removeClass("inputError");
  //   //   $(this).closest("div").removeClass("inputError");
  //   //   salesForm.errorRow.hide();
  //   // }

  //   // if ($(this).hasClass("input_box_select")) {
  //   //   //input_box_select
  //   //   $(this).prev().val($(this).val());
  //   // }
  // });

  $(document.body).on('input', '.error', function () {
    $(this).removeClass("error");
    $(this).removeClass("inputError");
    $(this).closest("div").removeClass("inputError");
    salesForm.errorRow.slideUp();
    tokuisakiForm.errorRow.slideUp();
    okurisakiForm.errorRow.slideUp();
  });

  $(".input_box_select").on('input', function () {
    $(this).prev().val($(this).val());
    $(this).prev().removeClass("error");
    $(this).closest("div").removeClass("inputError");
  })
  // $(document.body).on('keyup', '.inputError input', function () {
  //   if ($(this).val() != "" && $(this).closest("div").hasClass("inputError")) {
  //     $(this).closest("div").removeClass("inputError");
  //   };
  // })

  /**
   * UPDATE MEMO
   */
  $("#memoBtn").click(() => {
    updateMemo();
  });

  /**
   * ENTER KEY MOVE
   */
  $(document.body).on('keyup', "input[type=text],input[type=date],input[type=tel],textarea", function (e) {
    //e.preventDefault();
    if (e.which != 13) return;

    if (this.id == "last_move_input") {
      $("#btnRegMain").focus();
      return;
    }
    var i = $(this);
    i = $("input[type=text],input[type=date],input[type=tel],textarea").index(i);
    $("input[type=text],input[type=date],input[type=tel],textarea").eq(i + 1).focus().select();
  });

  /**
   * SELECT INPUT BOX
   */
  $(".input_box").keyup(function (e) {
    $(this).val($(this).val().replace(/\D/g, ''))
    if ($(this).val() == "") return;
    var val = $(this).val();
    var obj = $(this).next();
    //obj.children().each(function (e) {
    //  if ($(this).val() == val) {
    obj.val(val);
    //    return;
    //  };
    //});

    $(this).closest("div").removeClass("inputError");
    $(this).next().removeClass("error");
  });

  // salesForm.kosu.keyup(function () {
  //   $(this).val($(this).val().replace(/[^-0-9]|(?<=.)-/g, ""));
  // });

  /** 編集ボタン **/
  $("#btnEdit").click(function () {
    $(this).hide();
    $("#btnDelete").removeClass("disnon");
    $("#btnRegMain, #btnCancel").show();
    $("form input, form option, textarea, button").prop("disabled", false);
    $("#mesai_table input").prop('disabled', false);
    salesForm.tokuisakiTel.prop('disabled', true);
    salesForm.nextKbn.prop('disabled', false);
    salesForm.nextKbn2.prop('disabled', false);
    salesForm.nextKbn3.prop('disabled', false);
  });

  /** Delete table row **/
  $("#row_delete").on("click", function (e) {
    fncDeleteCheckRow();
  });

  /** CONTROL KEYS **/
  $(window).on("keydown", async function (e) {

    //Control
    if (!ctrl_key) {
      if (e.which == 17) {
        ctrl_key = true;
      }
      return;
    }

    //f1 => 登録
    if (e.which == 112) {
      if (ctrl_key && !$("#btnEdit").is(":visible")) {
        fncReg();
        return;
      }
      ctrl_key = false;
    }

    //f3 => メモを更新
    if (e.which == 114) {
      if (ctrl_key) {
        updateMemo();
        return;
      }
      ctrl_key = false;
    }

    //f6 => 削除
    if (e.which == 117) {
      if (ctrl_key && !$("#btnEdit").is(":visible")) {
        fncDeleteCheckRow();
        return;
      }
      ctrl_key = false;
    }

    //f8 => cancel
    if (e.which == 119) {
      if (ctrl_key && !$("#btnEdit").is(":visible")) {
        // fncCancel();
        // _cancel = false;
        $("#btnCancel").click();
        return;
      }
      ctrl_key = false;
    }

    //f12 => 得意先登録
    if (e.which == 123) {
      if (ctrl_key && $(".activeBtn").attr("tab-content") == "content_3" && !$("#btnEdit").is(":visible")) {
        fncTokuisakiReg();
      }
      ctrl_key = false;
    }

    //f10 => 送先登録
    if (e.which == 121) {
      if (ctrl_key && $(".activeBtn").attr("tab-content") == "content_4" && !$("#btnEdit").is(":visible")) {
        fncOkurisakiReg();
      }
      ctrl_key = false;
    }

    return;
  });

  /**
   * TABLE BODY SCROLL GET NEXT 50 ROWS
   */
  $("#telTable tbody").on('scroll', async function () {
    if (_ajax || tableLength < 50) return;

    const { scrollTop, scrollHeight, clientHeight } = this;
    if (scrollTop + clientHeight >= scrollHeight - 75) {
      offset += 50;
      // user has scrolled to 75%, load next 50
      tokuisakiSearch();
    };
  });

  // salesForm.salesKbn.on('change', function () {
  //   if ($(this).val() != '1' && $(this).val() != '4') {
  //     salesForm.label.prop('checked', false);
  //   } else {
  //     salesForm.label.prop('checked', true);
  //   }
  // });

  // $("#saleFrm_sales_kbn_box").on('input', function () {
  //   if ($(this).val() != '1' && $(this).val() != '4') {
  //     salesForm.label.prop('checked', false);
  //   } else {
  //     salesForm.label.prop('checked', true);
  //   }
  // })

  salesForm.nextKbn.on('change', function () {
    nextKbnColorChange($(this));
    changeKigo();
  });
  salesForm.nextKbn2.on('change', function () {
    nextKbnColorChange($(this));
    changeKigo();
  });
  salesForm.nextKbn3.on('change', function () {
    nextKbnColorChange($(this));
    changeKigo();
  });

});

/**
 * CACHE INPUT ELEMENTS AS OBJECTS
 */
const cacheObjects = () => {
  //MEMO
  window.$memo = $("#memo");
  window.$memoUpdateDate = $("#memo_update_date");

  //SALES FORM
  window.salesForm = {
    saleDt: $("#saleFrm_sale_dt"),
    orderNo: $("#saleFrm_order_no"),
    tokuisakiTel: $("#saleFrm_tokuisaki_tel"),
    tokuisakiNm: $("#saleFrm_tokuisaki_nm"),
    tokuisakiZip: $("#saleFrm_tokuisaki_zip"),
    tokuisakiAdr1: $("#saleFrm_tokuisaki_adr_1"),
    tokuisakiAdr2: $("#saleFrm_tokuisaki_adr_2"),
    tokuisakiAdr3: $("#saleFrm_tokuisaki_adr_3"),
    orderKbn: $("#saleFrm_order_kbn"),
    deliveryKbn: $("#saleFrm_delivery_kbn"),
    salesKbn: $("#saleFrm_sales_kbn"),
    recieveDt: $("#saleFrm_recieve_dt"),
    deliveryTimeKbn: $("#saleFrm_delivery_time_kbn"),
    deliveryTimeHr: $("#saleFrm_delivery_time_hr"),
    deliveryTimeMin: $("#saleFrm_delivery_time_min"),
    deliveryInstructKbn: $("#saleFrm_delivery_instruct_kbn"),
    totalQty: $("#saleFrm_qty-total"),
    totalAmt: $("#saleFrm_amt-total"),
    tax8: $("#saleFrm_tax_8"),
    tax10: $("#saleFrm_tax_10"),
    grandTotal: $("#saleFrm_shime_taka"),
    kosu: $("#saleFrm_kosu"),
    comment: $("#saleFrm_tokuisaki_comment"),
    saleSlip: $("#sale_slip"),
    saleHikae: $("#sale_hikae"),
    reciept: $("#reciept"),
    label: $("#label"),
    yamatoKbn: $("#saleFrm_yamato_kbn"),
    orderForm: $("#order_frm"),
    errorRow: $("#salesFrm .error-row"),
    errorMsg: $("#saleFrm_error"),
    nextKbn: $("#saleFrm_next_kbn"),
    nextKbn2: $("#saleFrm_next_kbn2"),
    nextKbn3: $("#saleFrm_next_kbn3"),
    senderCd: $("#sender_cd"),
    zeroTax: $("#zero-tax"),
    kigo: ""
  };

  //SALES HISTORY
  window.salesHistory = {
    orderNo: $("#sales_history_detail_order_no"),
    saleDate: $("#sales_history_detail_sale_dt"),
    orderKbn: $("#sales_history_detail_order_kbn"),
    saleKbn: $("#sales_history_detail_sale_kbn"),
    deliveryTime: $("#sales_history_detail_delivery_time"),
    deliveryKbn: $("#sales_history_detail_delivery_type"),
    inquireNo: $("#sales_history_detail_inquery_no"),
    kosu: $("#sales_history_detail_kosu"),
  };

  //TOKUISAKI FORM
  window.tokuisakiForm = {
    frm: $("#tokuisakiFrm").get(0),
    tokuisakiCd: $("#tokuisakiFrm_tokuisaki_cd"),
    tokuisakiName: $("#tokuisakiFrm_tokuisaki_nm"),
    tokuisakiKana: $("#tokuisakiFrm_tokuisaki_kana"),
    tokuisakiTel: $("#tokuisakiFrm_tokuisaki_tel"),
    tokuisakiFax: $("#tokuisakiFrm_tokuisaki_fax"),
    tokuisakiZip: $("#tokuisakiFrm_tokuisaki_zip"),
    tokuisakiAdr1: $("#tokuisakiFrm_tokuisaki_adr_1"),
    tokuisakiAdr2: $("#tokuisakiFrm_tokuisaki_adr_2"),
    tokuisakiAdr3: $("#tokuisakiFrm_tokuisaki_adr_3"),
    tokuisakiTanto: $("#tokuisakiFrm_tanto_nm"),
    tokuisakiComment: $("#tokuisakiFrm_tokuisaki_comment"),
    tokuisakiYobiTel: $("#tokuisakiFrm_fuzai_contact"),
    tokuisakiIndustry: $("#tokuisakiFrm_industry_cd"),
    tokuisakiDelivery: $("#tokuisakiFrm_delivery_instruct"),
    tokuisakiKbnDeliver: $("#tokuisakiFrm_kbn_deliver"),
    tokuisakiKbnOrder: $("#tokuisakiFrm_kbn_order"),
    errorRow: $("#tokuisakiFrm .error-row"),
    errorMsg: $("#tokuisaki_error")
  }

  //OKURISAKI FORM
  window.okurisakiForm = {
    frm: $("#okurisakiFrm").get(0),
    okurisakiCd: $("#okurisaki_cd"),
    okurisakiName: $("#okurisaki_nm"),
    okurisakiKana: $("#okurisaki_kana"),
    okurisakiTel: $("#okurisaki_tel"),
    okurisakiFax: $("#okurisaki_fax"),
    okurisakiZip: $("#okurisaki_zip"),
    okurisakiAdr1: $("#okurisaki_adr_1"),
    okurisakiAdr2: $("#okurisaki_adr_2"),
    okurisakiAdr3: $("#okurisaki_adr_3"),
    okurisakiTanto: $("#okurisaki_tanto_nm"),
    okurisakiYobiTel: $("#okurisaki_fuzai_contact"),
    //industryCd: $("#okurisaki_industry_cd"),
    okurisakiDelivery: $("#okurisaki_delivery_instruct"),
    tokuisakiCd: $("#okurisakiFrm_tokuisaki_cd"),
    errorRow: $("#okurisakiFrm .error-row"),
    errorMsg: $("#okurisaki_error")
  }
};

/**
 * GET MEMO
 */
const getMemo = async () => {
  try {
    subLoading($(".memo_section"), "15%", "100%");

    const res = await fetch(`${_API_PATH}getMemo`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const result = await res.json();

    if (result.hasOwnProperty("error")) {
      console.log(result.error);
      return false;
    }

    $memo.val(result.memo);
    $memoUpdateDate.val(result.update_date);
  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeSubLoading($(".memo_section"));
  }

}

/**
 * UPDATE RIGHT MEMO
 */
const updateMemo = async () => {
  try {
    subLoading($(".memo_section"));

    let val = encodeURIComponent($memo.val());
    const response = await fetch(`${_API_PATH}updateMemo&memo=${val}&update_date=${$memoUpdateDate.val()}`);

    if (!response.ok) {
      alert("メモ更新に失敗しました。");
      return false;
    };

    const result = await response.json();

    if (result.hasOwnProperty("error")) {
      console.log(result.error);
      alert("メモ更新に失敗しました。");
      return false;
    };

    if (result == "NG") {
      alert("メモ更新に失敗しました。");
      return false;
    };

    alert("メモを更新しました。");

    // getMemo();
  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeSubLoading($(".memo_section"));
    getMemo();
  }

}

/**
 * GET ORDER NO
 */
const getOrderNo = async () => {
  try {
    const response = await fetch(`${_API_PATH}getOrderNo`);

    if (!response.ok) {
      alert("受注番号を取得できませんでした。");
      return false;
    };

    const result = await response.json();

    salesForm.orderNo.text(result);
  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  }
}

/**
 * GET TOKUISAKI BY ID
 */
const getTokuisakiById = async (id) => {
  try {
    if (tokuAjax) return;

    tokuAjax = true;
    $("#tokuisakiFrm .inputError").removeClass("inputError");
    $("#tokuisakiFrm .error").removeClass("error");

    tokuisakiForm.errorRow.hide();

    const res = await fetch(`${_API_PATH}getTokuisakiById&tokuisaki_cd=${id}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const result = await res.json();

    if (result.hasOwnProperty("error")) {
      console.log(result.error);

      //fncFormReset();
      return false;
    };

    //SALES FORM
    //$salesTokuisakiCd.val(result.tokuisaki_cd);
    salesForm.tokuisakiTel.val(result.tokuisaki_tel);

    salesForm.kigo = result.jikai_kbn_1_nm + result.jikai_kbn_2_nm + result.jikai_kbn_3_nm;
    salesForm.tokuisakiNm.text(salesForm.kigo + result.tokuisaki_nm);
    //salesForm.tokuisakiZip.text(result.tokuisaki_zip);
    salesForm.tokuisakiAdr1.text(result.tokuisaki_adr_1);
    // salesForm.tokuisakiAdr2.text(result.tokuisaki_adr_2);
    // salesForm.tokuisakiAdr3.text(result.tokuisaki_adr_3);
    salesForm.comment.val(result.comment);
    salesForm.nextKbn.val(result.jikai_kbn_1);
    nextKbnColorChange(salesForm.nextKbn);
    salesForm.nextKbn2.val(result.jikai_kbn_2);
    nextKbnColorChange(salesForm.nextKbn2);
    salesForm.nextKbn3.val(result.jikai_kbn_3);
    nextKbnColorChange(salesForm.nextKbn3);

    //autoCalculate("1");

    if (!update_flg) {
      salesForm.orderKbn.val('1');
      salesForm.orderKbn.prev().val('1');
      salesForm.deliveryKbn.val(result.delivery_kbn);
      salesForm.deliveryKbn.prev().val(result.delivery_kbn);
      salesForm.salesKbn.val(result.sale_kbn);
      salesForm.salesKbn.prev().val(result.sale_kbn);
      // if (result.sale_kbn != '1' && result.sale_kbn != '4') {
      //   salesForm.label.prop('checked', false);
      // } else {
      //   salesForm.label.prop('checked', true);
      // }

      salesForm.yamatoKbn.val(result.yamato_kbn);
      salesForm.yamatoKbn.prev().val(result.yamato_kbn);
      salesForm.deliveryTimeKbn.val(result.delivery_time_kbn);
      salesForm.deliveryTimeKbn.prev().val(result.delivery_time_kbn);
      salesForm.deliveryTimeHr.val(result.delivery_time_hr);
      salesForm.deliveryTimeMin.val(result.delivery_time_min);
      salesForm.deliveryInstructKbn.val(result.delivery_instruct_kbn);
      salesForm.deliveryInstructKbn.prev().val(result.delivery_instruct_kbn);

      salesForm.totalQty.text('');
      salesForm.totalAmt.text('');
      salesForm.tax8.text('');
      salesForm.tax10.text('');
      salesForm.grandTotal.text('');
      salesForm.kosu.val('1');

      salesForm.nextKbn.prop('disabled', false);
      salesForm.nextKbn2.prop('disabled', false);
      salesForm.nextKbn3.prop('disabled', false);
    }

    //TOKUISAKI FORM
    tokuisakiForm.tokuisakiCd.val(result.tokuisaki_cd);
    tokuisakiForm.tokuisakiName.val(result.tokuisaki_nm);
    tokuisakiForm.tokuisakiKana.val(result.tokuisaki_kana);
    tokuisakiForm.tokuisakiTel.val(result.tokuisaki_tel);
    tokuisakiForm.tokuisakiZip.val(result.tokuisaki_zip);
    tokuisakiForm.tokuisakiAdr1.val(result.tokuisaki_adr_1);
    tokuisakiForm.tokuisakiAdr2.val(result.tokuisaki_adr_2);
    tokuisakiForm.tokuisakiAdr3.val(result.tokuisaki_adr_3);
    tokuisakiForm.tokuisakiDelivery.val(result.delivery_instruct);
    tokuisakiForm.tokuisakiIndustry.val(result.industry_cd);
    tokuisakiForm.tokuisakiYobiTel.val(result.fuzai_contact);
    tokuisakiForm.tokuisakiFax.val(result.tokuisaki_fax);
    tokuisakiForm.tokuisakiTanto.val(result.tanto_nm);
    tokuisakiForm.tokuisakiComment.val(result.comment);

    okurisakiForm.tokuisakiCd.val(result.tokuisaki_cd);

    getMesaiHistory(1);
    getSalesHistory(1);

    $("#mesai_table input").prop('disabled', false);
  } catch (err) {
    console.log(err);
  } finally {
    tokuAjax = false;
  }
}

/**
 * GET OKURISAKI PAGE BY TOKUISAKI CD
 * @param {*} id tokuisaki_cd
 * @param {*} pg page number
 * @returns 
 */
const getOkurisakiById = async (id, pg) => {
  try {

    if (okuriAjax) return;

    okuriAjax = true;

    $("#okurisakiFrm .inputError").removeClass("inputError");
    $("#okurisakiFrm .error").removeClass("error");
    okurisakiForm.errorRow.hide();

    okurisakiForm.tokuisakiCd.val(id);

    if (pg == "new") {
      okurisakiForm.okurisakiCd.val("");
      okurisakiForm.okurisakiName.val("");
      okurisakiForm.okurisakiKana.val("");
      okurisakiForm.okurisakiTel.val("");
      okurisakiForm.okurisakiFax.val("");
      okurisakiForm.okurisakiZip.val("");
      okurisakiForm.okurisakiAdr1.val("");
      okurisakiForm.okurisakiAdr2.val("");
      okurisakiForm.okurisakiAdr3.val("");
      okurisakiForm.okurisakiTanto.val("");
      okurisakiForm.okurisakiYobiTel.val("");
      //okurisakiForm.industryCd.prop("selectedIndex", 0);
      okurisakiForm.okurisakiDelivery.val("");
      //$saleOkurisakiCd.val("");

      var link = $("#okurisaki_nav .pg_num").text() == "新登録" ? link : $("#okurisaki_nav .pg_num").text().substr(0, 1);
      $("#okurisaki_nav .pg_num").text(`新登録`);
      $("#okurisaki_nav .prevpostslink").attr("page", link);
      $("#okurisaki_nav .nextpostslink").attr("page", "new");
      $("#okurisaki_nav").removeClass("disnon");
      return;
    }

    const res = await fetch(`${_API_PATH}getOkurisakiById&tokuisaki_cd=${id}&pagenum=${pg}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const result = await res.json();

    if (result.hasOwnProperty("error")) {
      console.log(result.error);

      okurisakiForm.okurisakiCd.val("");
      okurisakiForm.okurisakiName.val("");
      okurisakiForm.okurisakiKana.val("");
      okurisakiForm.okurisakiTel.val("");
      okurisakiForm.okurisakiFax.val("");
      okurisakiForm.okurisakiZip.val("");
      okurisakiForm.okurisakiAdr1.val("");
      okurisakiForm.okurisakiAdr2.val("");
      okurisakiForm.okurisakiAdr3.val("");
      okurisakiForm.okurisakiTanto.val("");
      okurisakiForm.okurisakiYobiTel.val("");
      //okurisakiForm.industryCd.prop("selectedIndex", 0);
      okurisakiForm.okurisakiDelivery.val("");
      //$saleOkurisakiCd.val("");

      $("#okurisaki_nav .pg_num").text(`新登録`);
      $("#okurisaki_nav .prevpostslink").attr("page", (Number(pg) - 1 == 0) ? 1 : pg - 1);
      $("#okurisaki_nav .nextpostslink").attr("page", (Number(pg) + 1 > result[1].count) ? "new" : Number(pg) + 1);
      $("#okurisaki_nav").removeClass("disnon");
      return false;
    };

    //OKURISAKI FORM
    okurisakiForm.okurisakiCd.val(result[0].okurisaki_cd)
    okurisakiForm.okurisakiName.val(result[0].okurisaki_nm);
    okurisakiForm.okurisakiKana.val(result[0].okurisaki_kana);
    okurisakiForm.okurisakiTel.val(result[0].okurisaki_tel);
    okurisakiForm.okurisakiFax.val(result[0].okurisaki_fax);
    okurisakiForm.okurisakiZip.val(result[0].okurisaki_zip);
    okurisakiForm.okurisakiAdr1.val(result[0].okurisaki_adr_1);
    okurisakiForm.okurisakiAdr2.val(result[0].okurisaki_adr_2);
    okurisakiForm.okurisakiAdr3.val(result[0].okurisaki_adr_3);
    okurisakiForm.okurisakiTanto.val(result[0].tanto_nm);
    okurisakiForm.okurisakiYobiTel.val(result[0].fuzai_contact);
    //okurisakiForm.industryCd.val(result[0].industry_cd);
    okurisakiForm.okurisakiDelivery.val(result[0].delivery_instruct);
    //$saleOkurisakiCd.val(result[0].okurisaki_cd);

    $("#okurisaki_nav .pg_num").text(`${pg}/${result[1].count}`);
    $("#okurisaki_nav .prevpostslink").attr("page", (Number(pg) - 1 == 0) ? 1 : pg - 1);
    $("#okurisaki_nav .nextpostslink").attr("page", (Number(pg) + 1 > result[1].count) ? "new" : Number(pg) + 1);
    $("#okurisaki_nav").removeClass("disnon");

  } catch (err) {
    console.log(err);
  } finally {
    okuriAjax = false;
  }
}

/**
 * TOKUISAKI SEARCH DIALOG
 */
const fncDispTokuisakiSearch = async () => {
  $("#tokuisakiSrchFrm input").val("");
  $("#tokuisakiSrchFrm table tbody").empty();
  $("#tokuisakiSrchFrm .frm-table").addClass("disnon");
  $("#tokuisaki-search .err_msg").text("").slideUp();
  $("#tokuisaki-search").dialog({
    title: "得意先検索",
    modal: true,
    height: isIpad ? "auto" : isTouch ? screen.availHeight : 600,
    width: isTouch ? screen.availWidth - 8 : 600,
    maxHeight: $("body").height(),
    minWidth: isTouch ? screen.availWidth - 8 : 325,
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
/**
 * GET TOKUISAKI LIST
 * @returns 
 */
const tokuisakiSearch = async () => {
  try {
    _ajax = true;

    if (offset == 0) {
      $("#tokuisakiSrchFrm table tbody").empty();
      $("#tokuisaki-search .err_msg").text("").slideUp();
    }

    const frm = new FormData($("#tokuisakiSrchFrm")[0]);

    const res = await fetch(`${_API_PATH}tokuisakiSubSearch&offset=${offset}`, { body: frm, method: "POST" });

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty('error')) {
      $("#tokuisaki-search .err_msg").text(data.error).slideDown();
      //alert(data.error);
      return false;
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

/**
 * ZIP LIVESEARCH
 */
const zipLiveSearch = async (zip) => {
  try {
    const res = await fetch(`${_API_PATH}zipLiveSearch&zip=${zip}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty('error')) {
      //TOKUISAKI FORM
      tokuisakiForm.tokuisakiAdr1.val("");
      tokuisakiForm.tokuisakiAdr2.val("");
      tokuisakiForm.tokuisakiAdr3.val("");

      //OKURISAKI FORM
      okurisakiForm.okurisakiAdr1.val("");
      okurisakiForm.okurisakiAdr2.val("");
      okuirisakiForm.okurisakiAdr3.va("");
      return false;
    };

    //TOKUISAKI FORM
    tokuisakiForm.tokuisakiZip.val(data.zip);
    tokuisakiForm.tokuisakiAdr1.val(data.ken_fu);
    tokuisakiForm.tokuisakiAdr2.val(data.shi_ku + data.machi);

    //OKURISAKI FORM
    okurisakiForm.okurisakiZip.val(data.zip);
    okurisakiForm.okurisakiAdr1.val(data.ken_fu);
    okurisakiForm.okurisakiAdr2.val(data.shi_ku + data.machi);

  } catch (err) {
    console.log(err);
  }
}

/**
 * REGISTER FORM
 */
const fncReg = async () => {
  try {
    dispLoading("処理中．．．");

    // _updating = true;
    salesForm.errorRow.hide();
    salesForm.errorMsg.next().text('')
    tokuisakiForm.errorRow.hide();
    okurisakiForm.errorRow.hide();
    $(".error").removeClass("error");
    $(".inputError").removeClass("inputError");

    if (update_flg && !confirm(`受注［${salesForm.orderNo.text()}］を更新してよろしでしょうか？`)) return;

    //1) input check
    if (!await saleFrmCheck()) return;

    //CREATE ABORT SIGNAL
    // abortControl = new AbortController();
    // signal = abortControl.signal;

    //2) 
    /**
     * INSERT/UPDATE
     * 
     */
    // await fncSalesReg();
    //CREATE DATA
    const frm = new FormData();

    //ORDER NO
    frm.append("order_no", salesForm.orderNo.text());
    //MESAI TABLE
    frm.append("mesai_rows", JSON.stringify(createMesaiArray()));
    //TOTAL QTY 
    frm.append("total_qty", delComma(salesForm.totalQty.text()));
    // TOTAL COST
    frm.append("total_cost", delComma(salesForm.totalAmt.text()));
    //TAX 8%
    frm.append("tax_8", delComma(salesForm.tax8.text()));
    //TAX 10%
    frm.append("tax_10", delComma(salesForm.tax10.text()));
    //GRAND TOTAL
    frm.append("grand_total", delComma(salesForm.grandTotal.text()));
    //INQUIRE NUMBER
    if (update_flg) {
      frm.append("inquire_no", salesHistory.inquireNo.text());
    }
    //NOT NEEDED
    //To update inquire_no use fncDispInqueryChange
    //frm.append("inquire_no", ($inquireNoInput.val() == "") ? $inquireNo.text() : $inquireNoInput.val());

    //領収書
    frm.append("receipt_flg", (salesForm.reciept.prop("checked")) ? "1" : "0");
    //売上伝票（控）
    frm.append("hikae_flg", (salesForm.saleHikae.prop("checked")) ? "1" : "0");
    //送り状
    frm.append("label_flg", (salesForm.label.prop("checked")) ? "1" : "0");
    //売上伝票
    frm.append("denpyo_flg", (salesForm.saleSlip.prop("checked")) ? "1" : "0");
    //注文書
    frm.append("order_flg", (salesForm.orderForm.prop("checked")) ? "1" : "0");

    //ADD SALES FORM AND TOKUISAKI FORM AND OKURISAKI FORM
    $('#salesFrm, #tokuisakiFrm, #okurisakiFrm').each(function () {
      var form = $(this)[0];
      var fields = new FormData(form);
      for (var key of fields.keys()) {
        frm.append(key, fields.get(key));
      };
    });

    //DECLARE TRANSACTION TYPE
    var type = update_flg ? "saleUpdate" : "saleReg";

    //IF CANCEL
    if (_cancel) return;

    const res = await fetch(`${_API_PATH}${type}`, { body: frm, method: 'POST' });

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      salesForm.errorMsg.text(data.error);
      salesForm.errorRow.slideDown('fast');
      //alert(data.error);
      console.log(data.error);
      return;
    };

    //IF CANCELLED OR ERROR
    if (data == "NG") {
      alert("売上入力をキャンセルしました。");
      return;
    };

    //4) get updated list data OR reset form
    if (update_flg) {
      // /** GET&DISPLAY 売上明細履歴 **/
      // getMesaiHistory(1);
      // /** GET&DISPLAY 売上履歴 **/
      // getSalesHistory(1);
      // /** GET&DISPLAY 問合せ番号 **/
      // getInquireNo();
      alert("売上入力を完了しました。");
      $("#updated").click();
      return;
    } else {
      fncFormReset();
    }

    //5) ALERT
    alert("売上入力を完了しました。");
  } catch (err) {
    // if (err.name == "AbortError") {
    //   alert("売上入力をキャンセルしました。");
    // } else {
    alert("サーバーでエラーが発生しました。");
    // }
    console.log(err);
  } finally {
    removeLoading();
    //abortControl = false;
    // _updating = false;
  }
}

/**
 * SALES FORM INPUT CHECK
 */
const saleFrmCheck = async () => {
  var isValid = true;
  $(".inputError").removeClass("inputError");

  //ORDER NO
  if (!salesForm.orderNo.text()) {
    // alert("受注番号は指定されていません。");
    salesForm.errorMsg.text(`受注番号が指定されていません。`);
    salesForm.errorRow.slideDown('fast');
    return false;
  }

  $("#salesFrm input[required], #salesFrm select[required]").each(function () {
    if (!$(this).val()) {
      let msg = $(this).prev().text().replace(/　/g, "");
      //alert(`${msg || "この項目"}を入力してください。`);
      salesForm.errorMsg.text(`${msg || "この項目"}を入力してください。`);
      salesForm.errorRow.slideDown('fast');
      //alert(`この項目を入力してください。`);
      $(this).addClass("error");
      $(this).closest("div").addClass("inputError");
      $(this).focus();
      return isValid = false;
    };
  });

  if (!isValid) return false;

  $(".input_box").each(function () {
    if ($(this).val() == "") {
      let msg = $(this).prev().text().replace(/　/g, "");
      salesForm.errorMsg.text(`${msg || "この項目"}を入力してください。`);
      salesForm.errorRow.slideDown('fast');
      $(this).addClass("error");
      $(this).closest("div").addClass("inputError");
      $(this).focus();
      return isValid = false;
    };

    if ($(this).val() != $(this).next().val()) {
      let msg = $(this).prev().text().replace(/　/g, "");
      salesForm.errorMsg.text(`${msg || "この項目"}は入力されたコードと選択内容が一致しません。`);
      salesForm.errorRow.slideDown('fast');
      $(this).addClass("error");
      $(this).closest("div").addClass("inputError");
      $(this).focus();
      return isValid = false;
    }
  })

  if (!isValid) return false;

  if (salesForm.tokuisakiNm.text() === "") {
    //alert(`得意先を指定してください。`);
    salesForm.errorMsg.text(`得意先が指定されていますん。`);
    salesForm.errorRow.slideDown('fast');
    salesForm.tokuisakiTel.addClass("error");
    salesForm.tokuisakiTel.closest("div").addClass("inputError");
    salesForm.tokuisakiTel.focus();
    return isValid = false;
  }

  if (!isValid) return false;

  //SAGAWA TIME CHECK
  if (
    salesForm.deliveryKbn.val() == "2" &&
    salesForm.deliveryTimeKbn.val() !== "3" &&
    salesForm.deliveryTimeHr.val() === "") {
    // alert("佐川時間は１時～１２時で入力して下さい。");
    salesForm.errorMsg.text(`佐川時間は１時～１２時で入力して下さい。`);
    salesForm.errorRow.slideDown('fast');
    salesForm.deliveryTimeHr.addClass("error");
    salesForm.deliveryTimeHr.addClass("inputError").focus();
    return isValid = false;
  }

  if (!isValid) return false;

  const deliveryTimeHr = Number(salesForm.deliveryTimeHr.val());
  const deliveryTimeMin = Number(salesForm.deliveryTimeMin.val());

  if (salesForm.deliveryTimeHr.val() !== "" && (deliveryTimeHr < 1 || deliveryTimeHr > 12)) {
    // alert("佐川時間は１時～１２時で入力して下さい。");
    salesForm.errorMsg.text(`佐川時間は１時～１２時で入力して下さい。`);
    salesForm.errorRow.slideDown('fast');
    salesForm.deliveryTimeHr.addClass("error");
    salesForm.deliveryTimeHr.addClass("inputError").focus();
    return isValid = false;
  }

  if (salesForm.deliveryTimeMin.val() !== "" && (deliveryTimeMin < 0 || deliveryTimeMin >= 60)) {
    //  alert("佐川時間は０分～５９分で入力して下さい。");
    salesForm.errorMsg.text(`佐川時間は０分～５９分で入力して下さい。`);
    salesForm.errorRow.slideDown('fast');
    salesForm.deliveryTimeMin.addClass("error");
    salesForm.deliveryTimeMin.addClass("inputError").focus();
    return isValid = false;
  }

  if (!isValid) return false;

  //MESAI BODY CHECK
  $("#mesai_table tbody tr").each(function () {
    var id = this.id.split("_")[1];
    var productName = $(`#mesai_shohin_nm_${id}`).text().trim();
    var productCode = $(`#mesai_shohin_cd_${id}`).val().trim();
    var productQty = $(`#mesai_shohin_qty_${id}`).val().trim();
    var rowTotal = $(`#mesai_row_total_${id}`).text().trim();

    autoCalculate(id);

    //First row must have input
    if (this.rowIndex == 1 && (productName === "" || rowTotal === "")) {
      // alert(`売上明細を必ず入力してください。`);
      salesForm.errorMsg.text(`売上明細を入力してください。`);
      salesForm.errorRow.slideDown('fast');
      $(`#mesai_shohin_cd_${id}`).addClass("error").focus();
      return isValid = false;
    }

    if (productName === "" && productCode !== "") {
      //alert(`売上明細の商品コードを確認してください。`);
      salesForm.errorMsg.text(`売上明細の商品コードを確認してください。`);
      salesForm.errorRow.slideDown('fast');
      $(`#mesai_shohin_cd_${id}`).addClass("error").focus();
      return isValid = false;
    }

    if (productName !== "" && productQty === "") {
      //alert(`売上明細の数量を入力してください。`);
      salesForm.errorMsg.text(`売上明細の数量を入力してください。`);
      salesForm.errorRow.slideDown('fast');
      $(`#mesai_shohin_qty_${id}`).addClass("error").focus();
      return isValid = false;
    }

    if (productName != "" && rowTotal === "") {
      //alert(`売上明細を確認してください。`);
      salesForm.errorMsg.text(`売上明細の数量を入力してください。`);
      salesForm.errorRow.slideDown('fast');
      $(`#mesai_shohin_qty_${id}`).addClass("error").focus();
      return isValid = false;
    }
  });

  if (!isValid) return false;

  //締め高
  if (Number(delComma(salesForm.grandTotal.text())) > 999999999) {
    //alert("合計金額は最大値より大きいです。");
    salesForm.errorMsg.text(`合計金額が最大値を超えています。`);
    salesForm.errorMsg.next().text('入力内容を確認してください。')
    salesForm.errorRow.slideDown('fast');
    return isValid = false;
  }

  if (!isValid) return false;

  if (!tokuisakiForm.tokuisakiKbnOrder.prop("checked")) {
    //alert("注文者を選択してください。");
    salesForm.errorMsg.text(`注文者を選択してください。`);
    salesForm.errorRow.slideDown('fast');
    $(".tab label[tab-content=content_3]").click();
    return isValid = false;
  };

  if (!tokuisakiForm.tokuisakiKbnDeliver.prop('checked')) {
    if ($(".activeBtn").attr('tab-content') != "content_4") {
      //alert("送り先を選択してください。");
      salesForm.errorMsg.text(`送り先を選択してください。`);
      salesForm.errorRow.slideDown('fast');
      $(".tab label[tab-content=content_4]").click();
      return isValid = false;
    };
  };

  //CHECK TOKUISAKI
  isValid = tokuisakiFrmCheck();
  if (!isValid) {
    $(".tab label[tab-content=content_3]").click();
    return false
  };

  //CHECK OKURISAKI 
  isValid = okurisakiFrmCheck();
  if (!isValid) {
    $(".tab label[tab-content=content_4]").click();
    return false
  };

  //CHECK YAMATO ZIP
  isValid = await yamatoZipCheck();
  if (!isValid) {
    if (tokuisakiForm.tokuisakiKbnDeliver.prop('checked')) {
      $(".tab label[tab-content=content_3]").click();
    } else {
      $(".tab label[tab-content=content_4]").click();
    }
    return false;
  }

  return isValid;
}

/**
 * CREATE ARRAY OBJECT OF 売上明細
 */
const createMesaiArray = () => {
  var mesai_array = [];

  $("#mesai_table tbody tr").each(function () {
    var id = this.id.split("_")[1];
    var productName = $(`#mesai_shohin_nm_${id}`).text().trim();
    var productCode = $(`#mesai_shohin_cd_${id}`).val().trim();
    var productQty = $(`#mesai_shohin_qty_${id}`).val().trim();
    var rowTotal = $(`#mesai_row_total_${id}`).text().trim();
    var productCost = $(`#mesai_tanka_${id}`).text().trim();

    if (productName) {
      const row_object = {
        row_no: id,
        product_cd: productCode,
        product_nm: productName,
        tanka: delComma(productCost),
        qty: delComma(productQty),
        total_cost: delComma(rowTotal)
      };

      mesai_array.push(row_object);
    }
  });

  return mesai_array;
}

/**
 * GET MESAI PRODUCT
 * @param id product_cd
 */
const getMesaiProduct = async (id, row) => {
  try {
    const res = await fetch(`${_API_PATH}productLiveSearch&product_cd=${id}&tokuisaki_cd=${tokuisakiForm.tokuisakiCd.val()}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      $(`#mesai_table tbody #mesai_shohin_nm_${row}`).html("");
      $(`#mesai_table tbody #mesai_tanka_${row}`).html("");
      $(`#mesai_table tbody #mesai_tax_rate_${row}`).val("");
      $(`#mesai_table tbody #mesai_shohin_qty_${row}`).val("");
      $(`#mesai_table tbody #mesai_row_total_${row}`).text("");
      return false;
    }

    let tax = 0;
    if (data.tax_kbn == "1") {
      tax = 0.08;
    } else if (data.tax_kbn == "2") {
      tax = 0.1;
    }

    //SET DATA
    $(`#mesai_table tbody #mesai_shohin_nm_${row}`).text(data.product_nm_abrv);
    $(`#mesai_table tbody #mesai_tanka_${row}`).text(data.tokuisaki_price == '0' ? data.sale_price : data.tokuisaki_price);
    $(`#mesai_table tbody #mesai_tax_rate_${row}`).val(tax);

    //ADD NEW ROW
    if (iRow == 1 || $(`#mesai_table tbody tr:last .shohin_nm`).text() != "") {
      addRow();
      //  $(`#mesai_table tbody #row_${row} #shohin_qty`).focus();
    };

    //CALCULATE ROW 
    if ($(`#mesai_table tbody #mesai_shohin_qty_${row}`).val() != "") {
      autoCalculate(row);
    };

  } catch (err) {
    console.log(err);
  }
}

/**
 * ADD NEW ROW TO MESAI TABLE
 * - left table
 */
const addRow = () => {
  iRow++;
  var tr = $(`
  <tr id="row_${iRow}">
    <td>
      <input type="checkbox" class="checkbox" value="${iRow}">
    </td>
    <td>
      <input type="text" id="mesai_shohin_cd_${iRow}" row-index="${iRow}" class="mesai_shohin_cd" maxlength="3">
    </td>
    <td class="shohin_nm" id="mesai_shohin_nm_${iRow}"></td>
    <td class="tar" id="mesai_tanka_${iRow}"></td>
    <td>
      <input type="text" maxlength="7" id="mesai_shohin_qty_${iRow}" class="tar mesai_shohin_qty" row-index="${iRow}">
    </td>
    <td class="tar mesai_row_total" id="mesai_row_total_${iRow}" row-index="${iRow}"></td>
    <input type="hidden" id="mesai_tax_rate_${iRow}" class="mesai_tax_rate" row-index="${iRow}">
    <input type="hidden" id="mesai_shohin_tax_${iRow}" >
  </tr>
  `);

  $("#mesai_table tbody").append(tr);
}

/**
 * CALCULATE ROW TOTAL
 */
const calcRowTotal = (id) => {
  //売上単価
  var cost = Number(delComma($(`#mesai_table tbody #mesai_tanka_${id}`).html()));
  //数量
  var qty = Number(delComma($(`#mesai_table tbody #mesai_shohin_qty_${id}`).val())).toFixed(1);
  qty = isNaN(qty) ? 0 : qty;
  //TOTAL
  var res = Math.round(cost * qty);
  res = isNaN(res) ? 0 : res;
  $(`#mesai_table tbody #mesai_row_total_${id}`).html(f.format(res));
  $(`#mesai_table tbody #mesai_shohin_qty_${id}`).val(f.format(qty));
  //calcRowTax(id);
}

/**
 * CALCULATE ROW TAX
 */
const calcRowTax = (id) => {
  var rate = $(`#mesai_table tbody #mesai_tax_rate_${id}`).val();
  var total = Number(delComma($(`#mesai_table tbody #mesai_row_total_${id}`).text()));
  var tax = total * rate;
  $(`#mesai_table tbody #mesai_shohin_tax_${id}`).val(tax);
}

/**
 * CALCULATE TOTAL QTY
 */
const calcTotalQty = () => {
  let qty = 0;
  $(`#mesai_table tbody tr input.mesai_shohin_qty`).each(function () {
    var rowId = $(this).attr('row-index');
    if ($(`#mesai_table tbody #mesai_tanka_${rowId}`).html() != "") {
      let val = Number(delComma($(this).val()));
      val = isNaN(val) ? 0 : val;
      qty += val;
    };
  });
  salesForm.totalQty.html(f.format(qty));
}

/**
 * CALCULATE TOTAL AMOUNT ￥
 */
const calcTotalAmt = () => {
  let amt = 0;
  let taxFreeAmt = 0;
  $(`#mesai_table tbody tr .mesai_row_total`).each(function (e) {
    // var rowId = $(this).attr('row-index');
    let val = Number(delComma($(this).text()));
    //if($(`#mesai_table tbody tr #mesai_tax_rate_${rowId}`).val() == "0"){
    // taxFreeAmt += val;
    // }else{
    amt += val;
    //}
  });
  salesForm.totalAmt.html(f.format(amt));
  //salesForm.zeroTax.val(taxFreeAmt);
}

/**
 * CALCULATE TOTAL TAX
 */
const calcTax = () => {
  let tax8 = 0;
  let tax10 = 0;

  $(`#mesai_table tbody tr .mesai_tax_rate`).each(function () {
    let id = $(this).attr('row-index');
    let total = Number(delComma($(`#mesai_table tbody #mesai_row_total_${id}`).text()));
    let rate = $(this).val();

    if (rate == 0.1) {
      tax10 += total;
    } else if (rate == 0.08) {
      tax8 += total;
    };

  })

  tax8 = Math.floor(tax8 * 0.08);
  tax10 = Math.floor(tax10 * 0.1);
  salesForm.tax8.html(f.format(tax8));
  salesForm.tax10.html(f.format(tax10));
}

/**
 * CALCULATE GRAND TOTAL
 */
const calcGrandTotal = () => {
  var tax8 = Number(delComma(salesForm.tax8.html()));
  var tax10 = Number(delComma(salesForm.tax10.html()));
  var amt = Number(delComma(salesForm.totalAmt.html()));
  // var zeroTax = Number(salesForm.zeroTax.val());
  var res = tax8 + tax10 + amt;// + zeroTax;
  salesForm.grandTotal.html(f.format(res));
}


/**
 * RE-CALCULATE TOTAL
 */
const mainTotalCalc = () => {
  calcTotalQty();
  calcTotalAmt();
  calcTax();
  calcGrandTotal();
}

/**
 * AUTO CALCULATE
 * @param id ROW ID
 */
const autoCalculate = (id) => {
  if ($(`#mesai_table tbody #mesai_tanka_${id}`).text() == '') return;

  //ROW TOTAL
  calcRowTotal(id);
  //ROW TAX
  // calcRowTax(id);
  //TOTAL QTY
  calcTotalQty();
  //TOTAL AMOUNT ￥
  calcTotalAmt();
  //TOTAL TAX
  calcTax();
  //GRAND TOTAL
  calcGrandTotal();
}

/**
 * MESAI HISTORY LIST
 */
const getMesaiHistory = async (pg) => {
  try {
    subLoading("#content_1", '35%', '100%');

    $("#uriage_table tbody, #content_1 .pagenavi").empty();
    const res = await fetch(`${_API_PATH}mesaiHistoryList&pagenum=${pg}&tokuisaki_cd=${tokuisakiForm.tokuisakiCd.val()}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      return false;
    }

    //Page total & Total count
    var last = data[data.length - 1];

    //remove total & Total count
    data.pop();

    data.forEach((obj) => {
      var tr = $(`
      <tr class="${getDateColor(obj.sale_dt)}">
        <td>${obj.sale_dt}</td>
        <td class="tal">${obj.product_nm ?? obj.sub_nm}</td>
        <td class="tar">${f.format(obj.qty)}</td>
        <td class="tar">${f.format(obj.total_cost)}</td>
        <td>${obj.product_cd}</td>
      </tr>
      `);
      $("#uriage_table tbody").append(tr);
    });

    /** PAGE NAVIGATION **/
    mesaiPagination(pg, last.total_page, "getMesaiHistory");

  } catch (err) {
    console.log(err);
  } finally {
    removeSubLoading($("#content_1"));
  }
}

/**
 * SALES HISTORY LIST
 */
const getSalesHistory = async (pg) => {
  try {
    subLoading("#content_2");

    $("#sales_history_table tbody, #content_2 .pagenavi").empty();

    const res = await fetch(`${_API_PATH}saleHistoryList&pagenum=${pg}&tokuisaki_cd=${tokuisakiForm.tokuisakiCd.val()}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      return false;
    }

    //Page total & Total count
    var last = data[data.length - 1];

    //remove total & Total count
    data.pop();

    data.forEach((obj) => {
      let tr = $(`
      <tr id="${obj.order_no}">
        <td>${obj.order_no}</td>
        <td>${obj.sale_dt}</td>
        <td class="tar">${f.format(obj.grand_total)}</td>
      </tr>
      `);
      $("#sales_history_table tbody").append(tr);
    });

    /** PAGE NAVIGATION **/
    salePagination(pg, last.total_page, "getSalesHistory");

  } catch (err) {
    console.log(err);
  } finally {
    removeSubLoading($("#content_2"));
  }
}

/**
 * GET ROW COLOR FOR MESAI HISTORY
 * - Today => red
 * - Within 7 days before today => orange
 * - Within Current month => yellow
 */
const getDateColor = (dateString) => {
  const today = new Date();
  const date = new Date(dateString);
  const oneDay = 24 * 60 * 60 * 1000; // One day in milliseconds
  const oneWeek = 7 * oneDay; // One week in milliseconds
  const oneMonth = 30 * oneDay; // One month in milliseconds

  // Check if input date is today
  if (
    date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear()
  ) {
    return "bk-red";
  }

  // Check if input date is within 7 days before today
  const diff = today.getTime() - date.getTime();
  if (diff >= 0 && diff <= oneWeek) {
    return "bk-orange";
  }

  // Check if input date is within the current month
  const monthDiff =
    date.getMonth() - today.getMonth() + 12 * (date.getFullYear() - today.getFullYear());
  if (monthDiff === 0 && diff > oneWeek && diff <= oneMonth) {
    return "bk-yellow";
  }

  // Return default color (white)
  return "";
}

/**
 * CHANGE ROW VALUE TO 0
 */
const changeToZero = (id) => {
  $(`#mesai_table tbody #mesai_shohin_qty_${id}`).val(0);
  $(`#mesai_table tbody #mesai_shohin_tax_${id}`).val(0);
  $(`#mesai_table tbody #mesai_row_total_${id}`).text(0);
}

/**
 * NEXT MESAI INPUT
 * @param {*} obj 
 */
const nextMesaiInput = (obj) => {
  var current = obj;
  var list = [];
  var cur_index;
  var i = 0;
  $("#mesai_table input[type=text]").each(function () {
    i++;
    list.push(this);
    if (this == current) {
      cur_index = i;
    }
  });
  if (list[cur_index]) {
    list[cur_index].focus();
    return;
  };
  $("#saleFrm_order_kbn_box").focus().select();
}

/**
 * SELECT ROW AND GET DETAIL from 売上履歴
 * @param {*} obj row being selected
 */
const salesHistoryDetail = async (order_no) => {
  try {
    const res = await fetch(`${_API_PATH}saleHistoryDetail&order_no=${order_no}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      return false;
    };

    $("#inquery_no_input").addClass("disnon");

    salesHistory.orderNo.text(data.order_no);
    salesHistory.saleDate.text(data.sale_dt)
    salesHistory.orderKbn.text(data.order_kbn)
    salesHistory.saleKbn.text(data.sale_kbn);
    salesHistory.deliveryTime.text(data.delivery_time);
    salesHistory.deliveryKbn.text(data.delivery_type);
    salesHistory.inquireNo.removeClass("disnon").text(data.inquire_no);
    //$inquireNoInput.val(data.inquire_no);

    salesHistory.kosu.text(data.kosu);

  } catch (err) {
    console.log(err);
  };
};


/**
 * COPY PREVIOUS ORDER
 */
const copyPrevOrder = async () => {
  if (salesHistory.orderNo.text() == "") {
    alert("売上履歴から対象の受注を選択してください。")
    return false;
  };

  try {
    const res = await fetch(`${_API_PATH}copyOrder&order_no=${salesHistory.orderNo.text()}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      return false;
    }

    // $salesFrm;
    //$saleDt.val(data[0].sale_dt);
    // $orderNo;
    // $salesTokuisakiCd.val(data[0].tokuisaki_cd);
    //salesForm.tokuisakiTel.val(data[0].tokuisaki_tel);
    //salesForm.tokuisakiNm.text(data[0].tokuisaki_nm);
    //salesForm.tokuisakiZip.text(data[0].tokuisaki_zip);
    //salesForm.tokuisakiAdr1.text(data[0].tokuisaki_adr_1);
    //salesForm.tokuisakiAdr2.text(data[0].tokuisaki_adr_2);
    //salesForm.tokuisakiAdr3.text(data[0].tokuisaki_adr_3);
    salesForm.orderKbn.val(data[0].order_kbn);
    salesForm.orderKbn.prev().val(data[0].order_kbn);
    salesForm.deliveryKbn.val(data[0].delivery_kbn);
    salesForm.deliveryKbn.prev().val(data[0].delivery_kbn);
    salesForm.salesKbn.val(data[0].sale_kbn);
    salesForm.salesKbn.prev().val(data[0].sale_kbn);
    // $recieveDt.val(data[0].recieve_dt);
    salesForm.yamatoKbn.val(data[0].yamato_kbn == '' ? '0' : data[0].yamato_kbn);
    salesForm.yamatoKbn.prev().val(data[0].yamato_kbn == '' ? '0' : data[0].yamato_kbn);
    salesForm.deliveryTimeKbn.val(data[0].delivery_time_kbn == '' ? "3" : data[0].delivery_time_kbn);
    salesForm.deliveryTimeKbn.prev().val(data[0].delivery_time_kbn == '' ? "3" : data[0].delivery_time_kbn);
    salesForm.deliveryTimeHr.val(data[0].delivery_time_hr);
    salesForm.deliveryTimeMin.val(data[0].delivery_time_min);
    salesForm.deliveryInstructKbn.val(data[0].delivery_instruct_kbn == '' ? "3" : data[0].delivery_instruct_kbn);
    salesForm.deliveryInstructKbn.prev().val(data[0].delivery_instruct_kbn == '' ? "3" : data[0].delivery_instruct_kbn);
    //salesForm.totalQty.text(f.format(data[0].total_qty));
    //salesForm.totalAmt.text(f.format(data[0].total_cost));
    //salesForm.tax8.text(f.format(data[0].tax_8));
    //salesForm.tax10.text(f.format(data[0].tax_10));
    //salesForm.grandTotal.text(f.format(Number(data[0].grand_total)));
    salesForm.kosu.val(data[0].kosu);
    //salesForm.comment.val(data[0].comment);

    $("#mesai_table tbody").empty();
    data.forEach((obj) => {
      let tax = 0;
      if (obj.tax_kbn == "1") {
        tax = 0.08;
      } else if (obj.tax_kbn == "2") {
        tax = 0.1;
      }
      let tr = $(`
      <tr id="row_${iRow}">
        <td>
          <input class="checkbox" type="checkbox" value="${iRow}">
        </td>
        <td>
          <input type="text" row-index="${iRow}" id="mesai_shohin_cd_${iRow}" class="mesai_shohin_cd" value="${obj.product_cd}">
        </td>
        <td class="shohin_nm" id="mesai_shohin_nm_${iRow}">${obj.product_nm_abrv ?? obj.product_nm}</td>
        <td id="mesai_tanka_${iRow}" class="tar">${obj.tokuisaki_price == '0' ? f.format(obj.sale_price) : f.format(obj.tokuisaki_price)}</td>
        <td>
          <input type="text" row-index="${iRow}" id="mesai_shohin_qty_${iRow}" class="mesai_shohin_qty tar" value="${Number(obj.qty).toFixed(1)}">
        </td>
        <td id="mesai_row_total_${iRow}" class="tar mesai_row_total">${f.format(obj.row_total_cost)}</td>
        <input type="hidden" class="mesai_tax_rate" id="mesai_tax_rate_${iRow}" value="${tax}" row-index="${iRow}">
        <input type="hidden" id="mesai_shohin_tax_${iRow}">
      </tr>
      `);

      $("#mesai_table tbody").append(tr);
      autoCalculate(iRow);
      iRow++;
    });

    if (Number(data[0].total_cost) < 0) {
      $("#akaden").prop("checked", true);
    }

    addRow();

    colorRed();

    $("#salesFrm .inputError").removeClass("inputError");
    $("#salesFrm .error").removeClass("error");

    salesForm.errorRow.hide();
    //getTokuisakiById(data[0].tokuisaki_cd);
    //getOkurisakiById(data[0].tokuisaki_cd, 1);
    return true
  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  }

}

/**
 * 得意先 REGISTER FORM
 */
const fncTokuisakiReg = async () => {
  try {
    subLoading($(".right"));

    tokuisakiForm.errorRow.hide();

    if (!tokuisakiFrmCheck()) return;

    var frm = new FormData(tokuisakiForm.frm);
    frm.append("sale_reg", "1");

    var type = (tokuisakiForm.tokuisakiCd.val() == "") ? "tokuisakiAdd" : "tokuisakiUpdate";

    const res = await fetch(`${_API_PATH}${type}`, { body: frm, method: "POST" });

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      // throw new Error(data.error);
      //alert(data.error);
      tokuisakiForm.errorMsg.text(data.error);
      tokuisakiForm.errorRow.slideDown('fast');
      return false;
    };

    if (tokuisakiForm.tokuisakiCd.val() == "") {
      tokuisakiForm.tokuisakiCd.val(data);
      okurisakiForm.tokuisakiCd.val(data);
    }

    let cd;
    let pg = 1;
    if (type === "tokuisakiAdd") {
      cd = data;
    } else {
      cd = tokuisakiForm.tokuisakiCd.val();
      pg = Number(okurisakiForm.okurisakiCd.val());
    }

    await getOkurisakiById(cd, pg);

    //TEL
    //fncTokuisakiTelReg();
    alert("得意先登録を完了しました。");

    if (update_flg) {
      getTokuisakiById(tokuisakiForm.tokuisakiCd.val());
      return false;
    };

    //fncFormReset();
  } catch (error) {
    //throw error;
    console.log(error);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeSubLoading($(".right"));
  }
}

/**
 * 送り先 REGISTER FORM
 */
const fncOkurisakiReg = async () => {
  try {
    subLoading($(".right"));

    okurisakiForm.errorRow.hide();

    if (!okurisakiFrmCheck()) return;

    var frm = new FormData(okurisakiForm.frm);

    var type = (okurisakiForm.okurisakiCd.val() == "") ? "okurisakiAdd" : "okurisakiUpdate";

    const res = await fetch(`${_API_PATH}${type}`, { body: frm, method: "POST" });

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      //alert(data.error);
      okurisakiForm.errorMsg.text(data.error);
      okurisakiForm.errorRow.slideDown('fast');
      return false;
      //throw new Error(data.error);
    };

    alert("送り先登録を完了しました。");
  } catch (error) {
    //throw error;
    console.log(error);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeSubLoading($(".right"));
  }
}

/**
 * RESET ALL FORMS
 * - CANCEL TRANSACTION
 */
const fncCancel = async () => {
  try {
    //if no transaction do nothing
    //if (!abortControl) return;
    //if (!update_flg && !_updating) return;

    if (!confirm("キャンセルしますか？")) return;

    _cancel = true;

    // abortControl.abort();
    await cancelSale();
    // if (update_flg) {
    //   $('#saleDialog').dialog('destroy');
    //   return;
    // };

    fncDelete();
    fncFormReset();
  } catch (error) {
    console.log(error);
  }

}

const fncDelete = async () => {
  try {
    const res = await fetch(`${_API_PATH}salesDelete&order_no=${salesForm.orderNo.text()}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
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

const okurisakiFrmCheck = () => {
  var ret = true;
  if (okurisakiForm.tokuisakiCd.val() == "") {
    //  alert("得意先を選択してください。");
    okurisakiForm.errorMsg.text(`得意先を指定してください。`);
    okurisakiForm.errorRow.slideDown('fast');
    return ret = false;
  };
  $("#okurisakiFrm input[required], #okurisakiFrm select[required]").each(function () {
    if (!$(this).val()) {
      // $("#err_msg").text(`${$(this).parent().prev().text()}を入力してください。`).slideDown();
      //  alert(`送り先${$(this).prev().text().replace("　", "")}を入力してください。`);
      okurisakiForm.errorMsg.text(`${$(this).prev("label").text().replace("　", "")}を入力してください。`);
      okurisakiForm.errorRow.slideDown('fast');
      $(this).addClass("error");
      $(this).closest("div").addClass("inputError");
      $(this).focus();
      return ret = false;
    };
  });

  if (!ret) return false;

  if (okurisakiForm.okurisakiKana.val() !== "" && !isHankaku(okurisakiForm.okurisakiKana.val().replace('・', ""))) {
    // alert(`送り先カナは半角カナを入力してください。`);
    okurisakiForm.errorMsg.text(`送り先カナは半角カナで入力してください。`);
    okurisakiForm.errorRow.slideDown('fast');
    okurisakiForm.okurisakiKana.addClass("error");
    okurisakiForm.okurisakiKana.closest("div").addClass("inputError");
    okurisakiForm.okurisakiKana.focus();
    return ret = false;
  }

  return ret;
}

const yamatoZipCheck = async () => {
  try {

    //IF SAGAWA
    if (salesForm.deliveryKbn.val() != "1") return true;

    // IS 代引 OR 元払い (売上区分) CHECK
    if (salesForm.salesKbn.val() != "1" && salesForm.salesKbn.val() != "4") return true;

    /**
     * IF TOKUISAKI IS OKURISAKI THEN CHECK TOKUISAKI_ZIP
     * ELSE IF DIFFERENT CHECK OKURISAKI_ZIP
     */
    var zip = (tokuisakiForm.tokuisakiKbnDeliver.prop('checked')) ? tokuisakiForm.tokuisakiZip.val() : okurisakiForm.okurisakiZip.val();

    if (zip == "") return true;

    const res = await fetch(`${_API_PATH}yamatoZipCheck&zip=${zip}`);

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();
    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      // alert(data.error);
      if (tokuisakiForm.tokuisakiKbnDeliver.prop('checked')) {
        tokuisakiForm.errorMsg.text(data.error);
        tokuisakiForm.errorRow.slideDown('fast');
      } else {
        okurisakiForm.errorMsg.text(data.error);
        okurisakiForm.errorRow.slideDown('fast');
      }
      return false;
    };

    return true;
  } catch (error) {
    throw error;
  }
}

/**
 * TOKUISAKI FORM INPUT CHECK
 */
const tokuisakiFrmCheck = () => {
  var isValid = true;
  $("#tokuisakiFrm input[required], #tokuisakiFrm select[required]").each(function () {
    if (!$(this).val()) {
      //alert(`得意先${$(this).prev("label").text().replace("　", "")}を入力してください。`);
      tokuisakiForm.errorMsg.text(`${$(this).prev("label").text().replace("　", "")}を入力してください。`);
      tokuisakiForm.errorRow.slideDown('fast');
      $(this).addClass("error");
      $(this).closest("div").addClass("inputError");
      $(this).focus();
      isValid = false;
      return false;
    };
  });

  if (!isValid) return false;

  if (tokuisakiForm.tokuisakiKana.val() !== "" && !isHankaku(tokuisakiForm.tokuisakiKana.val().replace('・', ""))) {
    //alert(`得意先カナは半角カナを入力してください。`);
    tokuisakiForm.errorMsg.text(`得意先カナは半角カナで入力してください。`);
    tokuisakiForm.errorRow.slideDown('fast');
    tokuisakiForm.tokuisakiKana.addClass("error");
    tokuisakiForm.tokuisakiKana.closest("div").addClass("inputError");
    tokuisakiForm.tokuisakiKana.focus();
    isValid = false;
    return false;
  }

  return isValid;
}


/**
 * RESET FORM
 */
const fncFormReset = () => {
  //HIDE ERROR ROW
  salesForm.errorRow.hide();
  okurisakiForm.errorRow.hide();
  tokuisakiForm.errorRow.hide();
  $(".error").removeClass("error");
  $(".inputError").removeClass("inputError");

  //売上伝票・SALES FORM
  $("#salesFrm input").val("");
  $("#salesFrm .label").text("");
  $("#salesFrm select").prop("selectedIndex", 0).css("background-color", "#fff");
  $("#salesFrm input[type=checkbox]").prop("checked", false);
  salesForm.comment.val("");
  salesForm.saleDt.val(todayDate());
  salesForm.recieveDt.val(tommorrowDate());
  salesForm.kosu.val("1");

  $(".input_box").each(function () {
    $(this).val($(this).next().val());
  });

  salesForm.saleSlip.prop('checked', true);
  salesForm.label.prop('checked', true);
  salesForm.orderForm.prop('checked', true);

  //DELETE ALL MESAI ROWS
  fncDeleteRow();
  $("#mesai_table input").prop('disabled', true);

  $(".tab:first-child label").click();

  //TOKUISAKI
  tokuisakiForm.tokuisakiKbnOrder.prop('checked', true);
  tokuisakiForm.tokuisakiKbnDeliver.prop('checked', true);
  $("#tokuisakiFrm input, #tokuisakiFrm textarea").val("");
  $("#tokuisakiFrm select").prop("selectedIndex", 0);

  //OKURISAKI
  $("#okurisakiFrm input, #okurisakiFrm textarea").val("");
  $("#okurisakiFrm select").prop("selectedIndex", 0);
  $("#okurisakiFrm .pagenavi").addClass("disnon");

  /** GET ORDER NUMBER **/
  getOrderNo();
  /** GET&DISPLAY MEMO **/
  getMemo();
  /** GET&DISPLAY 売上明細履歴 **/
  //getMesaiHistory(1);
  /** GET&DISPLAY 売上履歴 **/
  //getSalesHistory(1);

  //CLEAR 売上明細履歴
  $("#uriage_table tbody").empty();
  $("#mesaiPagination").empty();

  //CLEAR 売上履歴
  $("#sales_history_table tbody").empty();
  $("#salePagination").empty();

  //売上情報・SALES HISTORY DETAIL
  $("#inquery_no_input").addClass("disnon");
  salesHistory.inquireNo.removeClass("disnon").text("");
  salesHistory.orderNo.text("");
  salesHistory.saleDate.text("");
  salesHistory.orderKbn.text("");
  salesHistory.saleKbn.text("");
  salesHistory.deliveryTime.text("");
  salesHistory.deliveryKbn.text("");
  salesHistory.kosu.text("");

  //REMOVE ERROR STYLE
  $(".inputError").removeClass("inputError");

  //IF MINUS DENPYO
  $("#akaden").prop("checked", false);
  colorRed();

  salesForm.tokuisakiTel.focus();
}

/**
 * DELETE ALL MESAI TABLE ROW'S
 */
const fncDeleteRow = () => {
  $("#mesai_table input[type=checkbox]").parent().parent().remove();
  $(`#uriage_table tbody .newRow`).remove();
  iRow = 0;
  addRow();
  // $("#mesai_table tr th:first-child").prop("title", "全選択");
}

const fncDeleteCheckRow = () => {
  var total_index;
  $("#mesai_table input[type=checkbox]").each(function () {
    total_index = $(`#mesai_table tbody tr`);
    if (total_index.length == 1) return;

    if ($(this).prop("checked")) {
      $(`#uriage_table tbody #row_${$(this).val()}, #mesai_table tbody #row_${$(this).val()}`).remove();
    }
  });
  if ($(`#mesai_table tbody tr`).length == 1 && $(`#mesai_table tbody tr:first-child .shohin_nm`).text() != "") {
    addRow();
  }
  mainTotalCalc();
}

/**
 * CREATE PDF DATA
 */
const creatPdfData = async () => {
  try {
    var urlParams = `&order_no=${salesHistory.orderNo.text()}`;
    //領収書
    if ($("#reciept").prop("checked")) {
      urlParams = urlParams + "receipt_flg=1";
    };
    //売上伝票（控）
    if ($("#sale_hikae").prop("checked")) {
      urlParams = urlParams + "hikae_flg=1";
    };
    //送り状
    if ($("#label").prop("checked")) {
      urlParams = urlParams + "label_flg=1";
    };
    //売上伝票
    if ($("#sale_slip").prop('checked')) {
      urlParams = urlParams + "denpyo_flg=1";
    };
    //注文書
    if ($("#order_frm").prop('checked')) {
      urlParams = urlParams + "order_flg=1";
    };
  } catch (error) {
    throw error;
  }
}

const fncSalesDetail = async (order_no) => {
  try {
    dispLoading("処理中．．．");

    const res = await fetch(`${_API_PATH}getSalesDetail&order_no=${order_no}`);
    if (!res.ok) {
      //alert("ネットワークエラーが発生しました。");
      $(`#saleDialog`).dialog("destroy");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      alert(data.error);
      return;
    }

    //salesForm.nextKbn.val(data[0].next_kbn);
    salesForm.senderCd.val(data[0].sender_cd);
    salesForm.orderNo.text(order_no);
    salesForm.saleDt.val(data[0].sale_dt);
    //$salesTokuisakiCd.val(data[0].tokuisaki_cd);
    salesForm.tokuisakiTel.val(data[0].tokuisaki_tel);
    salesForm.tokuisakiNm.text(data[0].tokuisaki_nm);
    //salesForm.tokuisakiZip.text(data[0].tokuisaki_zip);
    salesForm.tokuisakiAdr1.text(data[0].tokuisaki_adr_1);
    //salesForm.tokuisakiAdr2.text(data[0].tokuisaki_adr_2);
    //salesForm.tokuisakiAdr3.text(data[0].tokuisaki_adr_3);
    salesForm.orderKbn.val(data[0].order_kbn);
    salesForm.orderKbn.prev().val(data[0].order_kbn);
    salesForm.deliveryKbn.val(data[0].delivery_kbn);
    salesForm.deliveryKbn.prev().val(data[0].delivery_kbn);
    salesForm.salesKbn.val(data[0].sale_kbn);
    salesForm.salesKbn.prev().val(data[0].sale_kbn);
    salesForm.recieveDt.val(data[0].receive_dt);
    salesForm.deliveryTimeKbn.val(data[0].delivery_time_kbn == '' ? "3" : data[0].delivery_time_kbn);
    salesForm.deliveryTimeKbn.prev().val(data[0].delivery_time_kbn == '' ? "3" : data[0].delivery_time_kbn);
    salesForm.deliveryTimeHr.val(data[0].delivery_time_hr);
    salesForm.deliveryTimeMin.val(data[0].delivery_time_min);
    salesForm.deliveryInstructKbn.val(data[0].delivery_instruct_kbn == '' ? '3' : data[0].delivery_instruct_kbn);
    salesForm.deliveryInstructKbn.prev().val(data[0].delivery_instruct_kbn == '' ? '3' : data[0].delivery_instruct_kbn);
    salesForm.totalQty.text(f.format(data[0].total_qty));
    salesForm.totalAmt.text(f.format(data[0].total_cost));
    salesForm.tax8.text(f.format(data[0].tax_8));
    salesForm.tax10.text(f.format(data[0].tax_10));
    salesForm.grandTotal.text(f.format(data[0].grand_total));
    salesForm.kosu.val(data[0].kosu);
    salesForm.comment.val(data[0].comment);
    salesForm.yamatoKbn.val(data[0].yamato_kbn == '' ? '0' : data[0].yamato_kbn);
    salesForm.yamatoKbn.prev().val(data[0].yamato_kbn == '' ? '0' : data[0].yamato_kbn);

    //帳票
    if (data[0].denpyo_flg == "1") {
      salesForm.saleSlip.prop('checked', true);
    } else {
      salesForm.saleSlip.prop('checked', false);
    }
    if (data[0].hikae_flg == "1") {
      salesForm.saleHikae.prop('checked', true);
    } else {
      salesForm.saleHikae.prop('checked', false);
    }
    if (data[0].receipt_flg == "1") {
      salesForm.reciept.prop('checked', true);
    } else {
      salesForm.reciept.prop('checked', false);
    }
    if (data[0].label_flg == "1") {
      salesForm.label.prop('checked', true);
    } else {
      salesForm.label.prop('checked', false);
    }
    if (data[0].order_flg == "1") {
      salesForm.orderForm.prop('checked', true);
    } else {
      salesForm.orderForm.prop('checked', false);
    }

    $("#mesai_table tbody").empty();
    data.forEach((obj) => {
      let tax = 0;
      if (obj.tax_kbn == "1") {
        tax = 0.08;
      } else if (obj.tax_kbn == "2") {
        tax = 0.1;
      }
      let tr = $(`
      <tr id="row_${iRow}">
        <td>
          <input class="checkbox" type="checkbox" value="${iRow}" >
        </td>
        <td>
          <input type="text" row-index="${iRow}" id="mesai_shohin_cd_${iRow}" class="mesai_shohin_cd" value="${obj.product_cd}" >
        </td>
        <td class="shohin_nm" id="mesai_shohin_nm_${iRow}">${obj.product_nm_abrv ?? obj.product_nm}</td>
        <td id="mesai_tanka_${iRow}" class="tar">${f.format(obj.tanka)}</td>
        <td>
          <input type="text" row-index="${iRow}" id="mesai_shohin_qty_${iRow}" class="mesai_shohin_qty tar" value="${Number(obj.qty).toFixed(1)}" >
        </td>
        <td id="mesai_row_total_${iRow}" class="tar mesai_row_total">${f.format(obj.row_total_cost)}</td>
        <input type="hidden" class="mesai_tax_rate" id="mesai_tax_rate_${iRow}" value="${tax}" row-index="${iRow}">
        <input type="hidden" id="mesai_shohin_tax_${iRow}">
      </tr>
      `);
      $("#mesai_table tbody").append(tr);
      iRow++;
    });

    addRow();

    //$("#mesai_table tbody input").prop("disabled", true);
    //$("#mesai_table input").prop('disabled', true);

    if (Number(data[0].total_cost) < 0) {
      $("#akaden").prop("checked", true);
    }

    colorRed();

    let pg = Number(data[0].okurisaki_cd) ?? 1;
    await getTokuisakiById(data[0].tokuisaki_cd);
    getOkurisakiById(data[0].tokuisaki_cd, pg);
    salesHistoryDetail(order_no);

    if (pg != 1) {
      tokuisakiForm.tokuisakiKbnDeliver.prop("checked", false);
    }
    $("#mesai_table input").prop('disabled', true);
    $(".container").removeClass("disnon");

  } catch (error) {
    console.log(error);
    alert("サーバーでエラーが発生しました。");
    $("#saleDialog").dialog("destroy");
  } finally {
    removeLoading();
  }
}

const minuseDen = async () => {
  //COPY ORDER
  if (!await copyPrevOrder()) return;

  //MAKE MINUS
  // $("#mesai_table #shohin_qty").each(function () {
  //   if ($(this).val() != "" && $(this).val().indexOf("-") == -1 && $(this).val() != "0") {
  //     $(this).val(`-${$(this).val()}`);
  //   };
  //   autoCalculate($(this).attr("row-index"));
  // });
  $("#mesai_table tbody tr").each(function () {
    let id = this.id.split("_")[1]
    let tanka = $(`#mesai_table #mesai_tanka_${id}`).text();
    let qty = $(`#mesai_table #mesai_shohin_qty_${id}`);
    let rowCost = $(`#mesai_table #mesai_row_total_${id}`);

    if (tanka != "" && tanka.indexOf("-") == -1 && qty.val().indexOf("-") == -1 && qty.val() != "0") {
      qty.val(`-${qty.val()}`);
    };

    // if (tanka != "" && tanka.indexOf("-") == -1 && rowCost.text().indexOf("-") == -1 && rowCost.text() != "0") {
    //   rowCost.text(`-${rowCost.text()}`);
    // };

    autoCalculate(qty.attr("row-index"));
  });
}

const colorRed = () => {
  if ($("#akaden").prop("checked")) {
    //COLOR RED
    $("main, .container").css("background", "red");
    $(".container label,.container table caption, .container legend,.container table th").css("color", "white");
    $("label.label").css("color", "black");
  } else {
    $("main, .container").css("background", "rgb(230, 230, 230)");
    $(".container label,.container table caption, .container legend,.container table th").css("color", "black");
    $("label.label").css("color", "black");
  }
}

const colorRed_test = () => {
  // if ($("#akaden").prop("checked")) {
  //COLOR RED
  // $("main, .container").css("background", "rgb(255, 200, 200)");
  $(".container label,.container table caption, .container legend,.container table th").css("color", "red");
  $("fieldset, caption, table, th, td, .tab_content").css("border-color", "red");
  $("label.label").css("color", "black");
  // } else {
  //   $("main, .container").css("background", "rgb(230, 230, 230)");
  //   $(".container label,.container table caption, .container legend,.container table th").css("color", "black");
  //   $("label.label").css("color", "black");
  // }
}

/**
 * UPDATE INQUIRE NO SCREEN
 * - used to update inquire_no
 */
const fncDispInqueryChange = async () => {
  $("#inqueryNoFrm input").val("").prop('disabled', false);
  $("#inquery-change .err_msg").text("").hide();

  $("#inqueryNoFrm_inquery_no").text(salesHistory.inquireNo.text());
  $("#inqueryNoFrm_order_no").val(salesForm.orderNo.text());
  $("#inqueryNoFrm_sale_dt").val(salesForm.saleDt.val())
  $("#inqueryNoFrm_order_no_disp").text(salesForm.orderNo.text());

  $("#inquery-change").dialog({
    title: "問合せ番号変更",
    modal: true,
    height: 340,
    width: isTouch ? screen.availWidth - 10 : 600,
    maxHeight: $("body").height(),
    minWidth: isTouch ? screen.availWidth - 10 : 335,
    buttons: [
      {
        text: "変更",
        class: "btn-edit",
        click: async () => {
          try {
            $("#inquery-change .err_msg").text("").hide();

            const frm = new FormData($("#inqueryNoFrm").get(0));

            if (frm.get("inquire_no") == "") {
              $("#inquery-change .err_msg").text("新しい問合せ番号を入力してください。").slideDown();
              return;
            }
            // if (frm.get("inquire_no").length != 12) {
            //   $("#inquery-change .err_msg").text("１２桁数を入力してください。").slideDown();
            //   return;
            // }

            if (frm.get("inquire_no") == $("#inqueryNoFrm_inquery_no").text()) {
              $("#inquery-change .err_msg").text("問合せ番号と新しい問合せ番号が同じです。").slideDown();
              return;
            }

            const res = await fetch(`${_API_PATH}changeInquireNo`, { body: frm, method: "POST" });

            if (!res.ok) {
              alert("ネットワークエラーが発生しました。");
              return false;
            };

            const data = await res.json();

            if (data.hasOwnProperty('error')) {
              $("#inquery-change .err_msg").text(data.error).slideDown();
              return false;
            };

            salesHistory.inquireNo.text(frm.get("inquire_no"));

            alert("問合せ番号を変更しました。");
            $("#inquery-change").dialog("destroy");

          } catch (err) {
            console.log(err);
            alert("サーバーでエラーが発生しました。");
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
 * GET TOKUISAKI TEL LIST
 * @param {*} tel tokuisaki_tel
 * @returns 
 */
const tokuisakiTelList = async (tel) => {
  try {
    const res = await fetch(`${_API_PATH}telLiveSearch&tel=${tel}&offset=${offset}`, { method: "GET" });

    if (!res.ok) {
      $(".livesearch_row").slideUp();
      offset = 0;
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty("error")) {
      console.log(data.error);
      return false;
    }

    if (offset == 0 && data.length == 0) {
      $(".livesearch_row").slideUp();
      offset = 0;
      return false;
    };

    var disp = true;
    var id;

    if (offset == 0) {
      $(".livesearch").empty();
    }

    data.forEach((obj) => {
      // if (obj.tel_no == tel) {
      //   disp = false;
      //   id = obj.tokuisaki_cd;
      //   return false;
      // };
      $(".livesearch").append(`<li class="livesearch_tel" id="${obj.tokuisaki_cd}">${obj.tel_no}<small>　${obj.tokuisaki_nm}</small></li>`);
    });
    // if (!disp) {
    //   $(".livesearch_row").slideUp();
    //   fncDeleteRow();
    //   // GET tokuisaki
    //   getTokuisakiById(id);
    //   //GET okurisaki
    //   getOkurisakiById(id, 1);
    //   offset = 0;
    //   return false;
    // };
    $(".livesearch_row").slideDown();
  } catch (error) {
    console.log(error);
    offset = 0;
  } finally {
    _ajax = false;
  }
}

/**
 * CACHE RESULT FROM FUNCTION
 * @param {*} func function to be used
 * - result from function is cached
 * @returns 
 */
function memoize(func) {
  const cache = new Map();
  return function (...args) {
    const key = JSON.stringify(args);
    if (cache.has(key)) {
      return cache.get(key);
    }
    const result = func(...args);
    cache.set(key, result);
    return result;
  };
}

const dispSubProductSearch = async (row) => {
  $("#productSrchFrm input").val("");
  $("#productSrchFrm table tbody").empty();
  $("#productSrchFrm .frm-table").addClass("disnon");
  $("#product-search .err_msg").text("").slideUp();
  $("#product-search").dialog({
    title: "商品検索",
    modal: true,
    height: isIpad ? "auto" : 600,
    width: isTouch ? screen.availWidth - 8 : 600,
    maxHeight: $("body").height(),
    minWidth: isTouch ? screen.availWidth - 8 : 325,
    buttons: [
      {
        text: "検索",
        class: "btn-edit",
        click: async () => {
          offset = 0;
          if (_ajax) return;
          productSearch(row);
        },
      },
      {
        text: "閉じる",
        class: "btn-close",
        click: function () {
          $("#productSrchFrm .frm-table").addClass("disnon");
          $(this).dialog("destroy");
        },
      },
    ],
  });
}

const productSearch = async (row) => {
  try {
    _ajax = true;

    if (offset == 0) {
      $("#productSrchFrm table tbody").empty();
      $("#product-search .err_msg").text("").slideUp();
    }

    const frm = new FormData($("#productSrchFrm")[0]);

    const res = await fetch(`${_API_PATH}productSearch&offset=${offset}&tokuisaki_cd=${tokuisakiForm.tokuisakiCd.val()}`, { body: frm, method: "POST" });

    if (!res.ok) {
      alert("ネットワークエラーが発生しました。");
      return false;
    };

    const data = await res.json();

    if (data.hasOwnProperty('error')) {
      if (offset == 0) {
        $("#product-search .err_msg").text(data.error).slideDown();
      }
      //alert(data.error);
      return false;
    };

    tableLength = data.length;

    const list = $("#productSrchFrm table tbody");
    data.forEach((obj) => {
      const tr = $(`
      <tr row-index="${row}">
        <input type="hidden" value="${obj.tax_kbn}">
        <td>${obj.product_cd}</td>
        <td>${obj.product_nm_abrv}</td>
        <td class="tar">${obj.tokuisaki_price == '0' ? f.format(obj.sale_price) : f.format(obj.tokuisaki_price)}</td>
      </tr>
      `);
      list.append(tr);
    })
    $("#productSrchFrm .frm-table").removeClass("disnon");

  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    _ajax = false;
  }
}

const getInquireNo = async () => {
  try {
    const res = await fetch(`${_API_PATH}getInquireNo&order_no=${salesForm.orderNo.text()}`);

    if (!res.ok) return;

    const inquireNo = await res.json();

    salesHistory.inquireNo.text(inquireNo);
  } catch (err) {
    console.log(err);
  }
}

//change background color based on selected value
const nextKbnColorChange = (el) => {
  let color = el.find(":selected").attr("color");
  el.css("background-color", color);
}

const nextKbnDisableToggle = () => {
  if (salesForm.tokuisakiNm.text() == '') {
    salesForm.nextKbn.prop('disabled', true);
    salesForm.nextKbn2.prop('disabled', true);
    salesForm.nextKbn3.prop('disabled', true);
  } else {
    salesForm.nextKbn.prop('disabled', false);
    salesForm.nextKbn2.prop('disabled', false);
    salesForm.nextKbn3.prop('disabled', false);
  };
}

const changeKigo = () => {
  let curKigo = salesForm.kigo;
  let newKigo = "";
  let curName = salesForm.tokuisakiNm.text();

  if (salesForm.nextKbn.val() != '0') {
    newKigo += salesForm.nextKbn.find(":selected").text();
  };
  if (salesForm.nextKbn2.val() != '0') {
    newKigo += salesForm.nextKbn2.find(":selected").text();
  };
  if (salesForm.nextKbn3.val() != '0') {
    newKigo += salesForm.nextKbn3.find(":selected").text();
  };
  curName = curName.replace(curKigo, '');
  salesForm.tokuisakiNm.text(newKigo + curName);
  salesForm.kigo = newKigo;
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