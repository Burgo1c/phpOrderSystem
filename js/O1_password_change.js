$(document).ready(() => {
    $(".btnCommon").click(async function () {
        updatePwd($(this).attr("form"));
    });

    $(document).keyup(function (e) {
        if (e.which != 13) return;
        updatePwd('pwdUpdateFrm_pc');
    })

    $(document.body).on('keyup', ".inputError", function () {
        if (this.value == "") return;
        $(this).removeClass("inputError");
        $(this).parent().prev().removeClass("inputError");
    })
});

const updatePwd = async (form) => {
    try {
        dispLoading("処理中．．．");

        $(".err_msg").text("").hide();

        if (!frmCheck(form)) return;

        var frm = new FormData($(`#${form}`).get(0));

        const res = await fetch(`${API_PATH}passwordChange`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        };

        const data = await res.json();

        if (data.hasOwnProperty("error")) {
            // alert(data.error);
            $(".err_msg").text(data.error).slideDown();
            console.log(data.error);
            return;
        };

        alert("パスワードを更新しました。");
        //location.href = '/views/top.php';
        $(`#${form} input`).val("");
    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}

const frmCheck = (form) => {
    $(".inputError").removeClass("inputError");
    const passwordInput = $(`#${form} #current_password`);
    const newPasswordInput = $(`#${form} #new_password`);
    const newPasswordChkInput = $(`#${form} #new_password_chk`);

    if (passwordInput.val() === "") {
        passwordInput.addClass("inputError").focus();
        passwordInput.parent().prev().addClass("inputError");
        $(".err_msg").text("現在のパスワードを入力してください。").slideDown();
        return false;
    }

    if (newPasswordInput.val() === "") {
        newPasswordInput.addClass("inputError").focus();
        newPasswordInput.parent().prev().addClass("inputError");
        $(".err_msg").text("新しいパスワードを入力してください。").slideDown();
        return false;
    }

    if (newPasswordChkInput.val() === "") {
        newPasswordChkInput.addClass("inputError").focus();
        newPasswordChkInput.parent().prev().addClass("inputError");
        $(".err_msg").text("新しいパスワード（確認用）を入力してください。").slideDown();
        return false;
    }

    if (passwordInput.val() === newPasswordInput.val()) {
        newPasswordInput.addClass("inputError").focus();
        newPasswordInput.parent().prev().addClass("inputError");
        $(".err_msg").text("現在のパスワードと新しいパスワードが同じです。").slideDown();
        return false;
    }

    if (newPasswordInput.val() !== newPasswordChkInput.val()) {
        newPasswordChkInput.addClass("inputError").focus();
        newPasswordChkInput.parent().prev().addClass("inputError");
        $(".err_msg").text("新しいパスワードと新しいパスワード（確認用）が異なります。").slideDown();
        return false;
    }

    return true;
}
