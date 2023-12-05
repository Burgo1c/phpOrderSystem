<header>
  <section class="inner">
    <!-- <a href="top" class="logo"><img src="/images/top-logo.png" alt="株式会社〇〇〇〇" /></a> -->
    <a href="top" class="logo_sp"><img src="/images/top-logo.png" alt="株式会社〇〇〇〇" /></a>
    <h1><a href="top">受注管理システム</a></h1>
    <h2 class="pageTitle"></h2>
    <p class="user"><?php echo $_SESSION["user_nm"] ?></p>
    <a class="logout">ログアウト</a>
  </section>
</header>
<nav>
  <!-- メニュー（PC用） -->
  <ul class="openMenu_pc">
    <li><a title="売上"><img src="/images/icon-sales.svg" alt="売上" /></a>
      <ul>
        <div class="menuTitle">売上</div>
        <li><a href="sales">売上入力</a></li>
        <li><a href="sales_shokai">売上照会</a></li>
        <!-- <li><a href="okuri_jo">送り状発行</a></li> -->
        <li><a href="kenpin">通常検品</a></li>
        <!-- <li><a href="report">日報・月報</a></li> -->
      </ul>
    </li>
    <li><a title="日報"><img src="/images/icon_calendar.svg" alt="日報" /></a>
      <ul>
        <div class="menuTitle">日報・月報</div>
        <li><a href="report">日報・月報</a></li>
      </ul>
    </li>
    <li><a title="出荷"><img src="/images/icon_shipment.svg" alt="出荷" /></a>
      <ul>
        <div class="menuTitle">出荷</div>
        <li><a href="shuka_report">出荷日報</a></li>
        <li><a href="shuka_send">出荷データ送信</a></li>
      </ul>
    </li>
    <li><a title="請求書"><img src="/images/icon-invoice.svg" alt="請求書" /></a>
      <ul>
        <div class="menuTitle">請求書発行</div>
        <li><a href="invoice">請求書発行</a>
        </li>
      </ul>
    </li>

    <li><a title="マスタ"><img src="/images/icon_master.svg" alt="マスタ" /></a>
      <ul class="master">
        <div class="menuTitle">マスタ</div>
        <li><a href="user_master">ユーザーマスタ</a>
        </li>
        <li><a href="tokuisaki_master">得意先マスタ</a>
        </li>
        <li><a href="shohin_master">商品マスタ</a>
        </li>
        <li><a href="tokuisaki_shohin_master">得意先別商品マスタ</a>
        </li>
        <!-- <li><a href="/teiban_master">定番・セール商品スマスタ</a>
                </li> -->
        <li><a href="zip_master">郵便番号マスタ</a>
        </li>
        <li><a href="yamato_master">ヤマト仕分マスタ取込</a>
        </li>
      </ul>
    </li>
    <li><a title="その他"><img src="/images/icon_other.svg" alt="その他" /></a>
      <ul>
        <div class="menuTitle">その他</div>
        <li><a href="password_change">パスワード変更</a>
        </li>
      </ul>
    </li>
  </ul>

  <!-- メニュー（スマホ／タブレット用） -->
  <script>
    $(function() {
      $("ul.menuOpen").hide();
      $(".menu").click(function() {
        $("ul.menuOpen").slideUp();
        $(".menu").removeClass("close");
        if ($("+ul", this).css("display") == "none") {
          $("+ul", this).slideDown();
          $(this).addClass("close");
          return false;
        }
        $("+ul", this).slideUp();
      });
    });
  </script>
  <ul class="openMenu">
    <li>
      <a class="menu nv1" title="トップ" href="top">
        <img src="/images/icon.png" alt="トップ">
      </a>
    </li>
    <li>
      <a class="menu nv2" title="売上"><img src="/images/icon-sales.svg" alt="売上" /></a>
      <ul class="menuOpen">
        <!-- <div class="menuTitle">売上</div> -->
        <li class="menuTitle"><a href="sales">売上入力</a></li>
        <li class="menuTitle"><a href="sales_shokai">売上照会</a></li>
        <!-- <li class="menuTitle"><a href="okuri_jo">送り状発行</a></li> -->
        <li class="menuTitle"><a href="kenpin">通常検品</a></li>
        <!-- <li><a href="report">日報・月報</a></li> -->
      </ul>
    </li>
    <li><a class="menu nv3" title="日報"><img src="/images/icon_calendar.svg" alt="日報" /></a>
      <ul class="menuOpen">
        <!-- <div class="menuTitle">その他</div> -->
        <li class="menuTitle"><a href="report">日報・月報</a></li>
      </ul>
    </li>
    <li><a class="menu nv4" title="出荷"><img src="/images/icon_shipment.svg" alt="出荷" /></a>
      <ul class="menuOpen">
        <!-- <div class="menuTitle">出荷</div> -->
        <!-- <li class="menuTitle"><a href="shuka">出荷データ送信</a></li> -->

        <li class="menuTitle"><a href="shuka_report">出荷日報</a></li>
      </ul>
    </li>
    <li><a class="menu nv5" title="請求書"><img src="/images/icon-invoice.svg" alt="請求書" /></a>
      <ul class="menuOpen">
        <!-- <div class="menuTitle">請求書発行</div> -->
        <li class="menuTitle"><a href="invoice">請求書発行</a></li>
      </ul>
    </li>
    <li><a class="menu nv6" title="マスタ"><img src="/images/icon_master.svg" alt="マスタ" /></a>
      <ul class="menuOpen">
        <!-- <div class="menuTitle">マスタ</div> -->
        <li class="menuTitle"><a href="user_master">ユーザーマスタ</a></li>

        <li class="menuTitle"><a href="tokuisaki_master">得意先マスタ</a></li>

        <li class="menuTitle"><a href="shohin_master">商品マスタ</a></li>

        <li class="menuTitle"><a href="tokuisaki_shohin_master">得意先別商品マスタ</a></li>

        <!-- <li class="menuTitle"><a href="/teiban_master">定番・セール商品スマスタ</a></li> -->

        <!-- <li class="menuTitle"><a href="zip_master">郵便番号マスタ</a></li>

          <li class="menuTitle"><a href="yamato_master">ヤマト仕分マスタ取込</a></li> -->
      </ul>
    </li>
    <li>
      <a class="menu nv7 logout" title="ログアウト">
        <img src="/images/icon_logout.svg" alt="ログアウト">
      </a>
    </li>
  </ul>
  <!--    <footer>
        <div id="page-top"><a href="#header"><img src="/images/page_top.svg" width="100%" alt="Page Top" /></a></div> 
        <p class="copyright">© Logigress Co., Ltd. All Rights Reserved.</p>
    </footer>
-->
</nav>
</nav>
