/**
 * @author Liam Burgess
 */
$(document).ready(() => {
    $("#user_id").focus();

    /**
     * LOGIN
     */
    $("input").on("keyup", function (e) {
        if (e.which != 13) return;

        if (this.value == "") return;

        if ($("#user_id").val() != "" && $("#password").val() != "") {
            login();
            return;
        };

        if (this.id == "user_id") {
            $("#password").focus();
        };

        if (this.id == "password") {
            $("#user_id").focus();
        };
    });

    $(".btnLogin").click(() => {
        login();
    })


})

const login = async () => {
    if ($("#user_id").val() == "") {
        $(".err_msg").text("ユーザーIDを入力してください。").slideDown();
        $("#user_id").focus();
        return;
    };

    if ($("#password").val() == "") {
        $(".err_msg").text("パスワードを入力してください。").slideDown();
        $("#password").focus();
        return;
    };

    try {
        dispLoading("処理中．．．");

        $(".err_msg").text("").hide();

        const frm = new FormData($("#loginFrm").get(0));

        const res = await fetch(`${API_PATH}login`, { body: frm, method: "POST" });

        if (!res.ok) {
            alert("ネットワークエラーが発生しました。");
            return;
        }

        const data = await res.json();

        if (data.hasOwnProperty('error')) {
            //alert(data.error);
            $(".err_msg").text(data.error).slideDown();
            $("#password").focus();
            return;
        };

        location.href = "sales";

    } catch (err) {
        console.log(err);
        alert("サーバーでエラーが発生しました。");
    } finally {
        removeLoading();
    }
}