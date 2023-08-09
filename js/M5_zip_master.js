$(document).ready(() => {

  $(".btnCommon").click(function () {
    zipUpload($(this).attr("form"));
  });

});

async function zipUpload(obj) {
  try {
    dispLoading("処理中...");
    $(".err_msg").text("").slideUp();

    if ($(`#${obj} #file`)[0].files.length == 0) {
      $(".err_msg").text("ファイルを選択してください。").slideDown();
      return;
    };

    var frm = new FormData($(`#${obj}`).get(0));

    const res = await fetch(`${API_PATH}zipUpload`, { body: frm, method: "POST" });

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
      link.download = `zip_upload_error_${fileDateTime()}.csv`;
      link.text = 'zip_upload_error.csv';
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

    //alert(`アップロードを完了しました。\n登録：${data.insert_cnt}\n更新：${data.update_cnt}\n失敗：${data.error_cnt}`);
    alert("アップロードを完了しました。");
    location.reload();

  } catch (err) {
    console.log(err);
    alert("サーバーでエラーが発生しました。");
  } finally {
    removeLoading();
  }
}