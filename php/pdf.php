<?php
include_once("lib/tcpdf.php");
include_once("config.php");

class shohinPDF extends TCPDF
{
    //Page header
    public function Header()
    {
        $this->setFillColor(204, 204, 204);
        $this->SetFont('kozgopromedium', 'I', 22);
        // Title
        $this->Cell(0, 15, '商　　品　　台　　帳', 0, false, 'C', true, '', 0, false, 'M', 'M');
        $this->SetFont('kozgopromedium', '', 10);
        $this->text(260, 25, date("Y/m/d"));
    }
};

class shohinSalePDF extends TCPDF
{
    //Page header
    public function Header()
    {
        $this->setFillColor(204, 204, 204);
        $this->SetFont('kozgopromedium', 'I', 22);
        // Title
        $this->Cell(0, 15, '商品別売上一覧', 0, false, 'C', true, '', 0, false, 'M', 'M');
        $this->SetFont('kozgopromedium', '', 10);
        $this->text(180, 25, date("Y/m/d"));
    }
};

class tokuisakiPDF extends TCPDF
{
    private $tokuisaki_nm;
    private $tokuisaki_tel;
    private $industry_kbn;
    public function getTokuisakiNm()
    {
        return $this->tokuisaki_nm;
    }

    public function setTokuisakiNm($tokuisaki_nm)
    {
        $this->tokuisaki_nm = $tokuisaki_nm;
    }

    public function getTokuisakiTel()
    {
        return $this->tokuisaki_tel;
    }

    public function setTokuisakiTel($tokuisaki_tel)
    {
        $this->tokuisaki_tel = $tokuisaki_tel;
    }

    public function getIndustryKbn()
    {
        return $this->industry_kbn;
    }

    public function setIndustryKbn($industry_kbn)
    {
        $this->industry_kbn = $industry_kbn;
    }

    //Page header
    public function Header()
    {
        $this->setFillColor(204, 204, 204);
        $this->SetFont('kozgopromedium', 'I', 22);
        // Title
        $this->Cell(0, 15, '得　意　先　別　商　品　台　帳', 0, false, 'C', true, '', 0, false, 'M', 'M');
        $this->SetFont('kozgopromedium', '', 14);
        $this->text(15, 25, $this->getTokuisakiNm());
        $this->SetFont('kozgopromedium', '', 12);
        $this->text(30, 33, '電話番号　' . $this->getTokuisakiTel());
        $this->text(100, 33, '業種：' . $this->getIndustryKbn());
        $this->SetFont('kozgopromedium', '', 10);
        $this->text(265, 25, date("Y/m/d"));
    }
};

class tokuisakiSalePDF extends TCPDF
{
    //Page header
    public function Header()
    {
        $this->setFillColor(204, 204, 204);
        $this->SetFont('kozgopromedium', 'I', 22);
        // Title
        $this->Cell(0, 15, '売上累計表', 0, false, 'C', true, '', 0, false, 'M', 'M');
        $this->SetFont('kozgopromedium', '', 10);
        $this->text(180, 25, date("Y/m/d"));
    }
};

class shukaPDF extends TCPDF
{
    private $order_no;
    private $inquire_no;
    private $okurisaki_zip;
    private $okurisaki_tel;
    private $okurisaki_adr_1;
    private $okurisaki_adr_2;
    private $okurisaki_adr_3;
    private $okurisaki_nm;
    private $grand_total;

    public function getOrderNo()
    {
        return $this->order_no;
    }

    public function setOrderNo($order_no)
    {
        $this->order_no = $order_no;
    }

    public function getInquireNo()
    {
        return $this->inquire_no;
    }

    public function setInquireNo($inquire_no)
    {
        $this->inquire_no = $inquire_no;
    }

    public function getOkurisakiZip()
    {
        return $this->okurisaki_zip;
    }

    public function setOkurisakiZip($okurisaki_zip)
    {
        $this->okurisaki_zip = $okurisaki_zip;
    }

    public function getOkurisakiTel()
    {
        return $this->okurisaki_tel;
    }

    public function setOkurisakiTel($okurisaki_tel)
    {
        $this->okurisaki_tel = $okurisaki_tel;
    }

    public function getOkurisakiAdr1()
    {
        return $this->okurisaki_adr_1;
    }

    public function setOkurisakiAdr1($okurisaki_adr_1)
    {
        $this->okurisaki_adr_1 = $okurisaki_adr_1;
    }

    public function getOkurisakiAdr2()
    {
        return $this->okurisaki_adr_2;
    }

    public function setOkurisakiAdr2($okurisaki_adr_2)
    {
        $this->okurisaki_adr_2 = $okurisaki_adr_2;
    }

    public function getOkurisakiAdr3()
    {
        return $this->okurisaki_adr_3;
    }

    public function setOkurisakiAdr3($okurisaki_adr_3)
    {
        $this->okurisaki_adr_3 = $okurisaki_adr_3;
    }

    public function getOkurisakiNm()
    {
        return $this->okurisaki_nm;
    }

    public function setOkurisakiNm($okurisaki_nm)
    {
        $this->okurisaki_nm = $okurisaki_nm;
    }

    public function getGrandTotal()
    {
        return $this->grand_total;
    }

    public function setGrandTotal($grand_total)
    {
        $this->grand_total = $grand_total;
    }

    //Page header
    public function Header()
    {
        $this->setFillColor(204, 204, 204);
        $this->SetFont('kozgopromedium', '', 20);
        // Title
        $this->Cell(0, 15, '出荷依頼書', 0, false, 'C', false, '', 0, false, 'M', 'M');

        $this->SetFont('kozgopromedium', '', 12);

        $this->MultiCell(0, 6, "〒　" . $this->getOkurisakiZip(), 0, "J", false, 0, 10, 20);
        $this->MultiCell(0, 6, "電話　" . $this->getOkurisakiTel(), 0, "J", false, 0, 10, 27);
        $this->MultiCell(0, 6, $this->getOkurisakiAdr1() . $this->getOkurisakiAdr2(), 0, "J", false, 0, 10, 34);
        $this->MultiCell(0, 6, $this->getOkurisakiAdr3(), 0, "J", false, 0, 10, 42);

        //INQUIRE NO
        $this->write1DBarcode($this->getOkurisakiTel(), 'CODABAR', 150, 28, 60, 10, 0.4);

        $this->MultiCell(35, 6, date("Y年m月d日"), "B", "C", false, 0, 130, 42);

        //ORDER NO
        $this->MultiCell(50, 6, "受注番号：" . $this->getOrderNo(), "B", "C", false, 0, 224, 12);
        $this->write1DBarcode($this->getOrderNo(), 'CODABAR', 225, 20, 60, 10, 0.4);

        $this->SetFont('kozgopromedium', '', 14);
        $this->MultiCell(150, 6, $this->getOkurisakiNm() . "　様", 0, "J", false, 0, 15, 55);
        $this->MultiCell(0, 6, COMPANY, 0, "C", false, 1, 225, 55);

        //table
        $this->SetFont('kozgopromedium', '', 12);
        $this->Cell(45, 6, "品番", "LTB", 0, "C", true);
        $this->Cell(165, 6, "品番名", "LTB", 0, "C", true);
        $this->Cell(60, 6, "数量", 1, 1, "C", true);

        $this->SetFont('kozgopromedium', '', 12);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);
        $this->Cell(45, 6, "", "LTB", 0, "C", false);
        $this->Cell(165, 6, "", "LTB", 0, "C", false);
        $this->Cell(60, 6, "", 1, 1, "C", false);

        $this->SetFont('kozgopromedium', '', 14);
        $this->Cell(210, 10, "ミヤコ様、指定商品の発送を願います。", 0, 0, "C");
        $this->Cell(30, 10, "合計締高", 1, 0, "C", true);
        $this->Cell(30, 10, "", "LBR", 1, "C");

        $this->SetFont('kozgopromedium', '', 14);
        $this->setCellPaddings(null, null, 2);
        $this->MultiCell(30, 6, number_format($this->getGrandTotal()), 0, 'R', false, 0, 250, 190);
    }
};

class urikakePDF extends TCPDF
{
    private $tokuisaki_cd;
    private $tokuisaki_nm;
    private $total;
    private $tax_8;
    private $tax_10;
    private $grand_total;
    private $date_from;
    private $date_to;

    /**
     * SET TOKUISAKI CD
     */
    public function setTokuisakiCd($tokuisaki_cd)
    {
        $this->tokuisaki_cd = $tokuisaki_cd;
    }
    /**
     * GET TOKUISAKI CD
     */
    public function getTokuisakiCd()
    {
        return $this->tokuisaki_cd;
    }

    /**
     * SET TOKUISAKI NAME
     */
    public function setTokuisakiNm($name)
    {
        $this->tokuisaki_nm = $name;
    }
    /**
     * GET TOKUISAKI NAME
     */
    public function getTokuisakiNm()
    {
        return $this->tokuisaki_nm;
    }

    /**
     * SET TOTAL
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }
    /**
     * GET TOTAL
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * SET 8% TAX
     */
    public function setTax8($tax_8)
    {
        $this->tax_8 = $tax_8;
    }
    /**
     * GET 8% TAX
     */
    public function getTax8()
    {
        return $this->tax_8;
    }

    /**
     * SET 10% TAX
     */
    public function setTax10($tax_10)
    {
        $this->tax_10 = $tax_10;
    }
    /**
     * GET 10% TAX
     */
    public function getTax10()
    {
        return $this->tax_10;
    }

    /**
     * SET GRAND TOTAL
     */
    public function setGrandTotal($grand_total)
    {
        $this->grand_total = $grand_total;
    }
    /**
     * GET GRAND TOTAL
     */
    public function getGrandTotal()
    {
        return $this->grand_total;
    }

    /**
     * SET DATE FROM
     */
    public function setDateFrom($date)
    {
        $this->date_from = date("Y年m月d日", strtotime($date));
    }
    /**
     * GET DATE FROM
     */
    public function getDateFrom()
    {
        return $this->date_from;
    }

    /**
     * SET DATE TO
     */
    public function setDateTo($date)
    {
        $this->date_to = date("Y年m月d日", strtotime($date));
    }
    /**
     * GET DATE FROM
     */
    public function getDateTo()
    {
        return $this->date_to;
    }

    //Page header
    public function Header()
    {
        $this->setFillColor(204, 204, 204);
        $this->SetFont('kozgopromedium', '', 20);
        // Title
        $this->Cell(0, 15, '売掛金元帳', 0, false, 'C', false, '', 0, false, 'M', 'M');
        $this->SetFont('kozgopromedium', '', 10);
        $this->text(255, 25, date("Y年m月d日"));

        $this->setFontSize(16);
        $this->MultiCell(100, 6, $this->getTokuisakiNm() . "　御中", 0, "L", false, 1, 15, 30);

        $this->setCellMargins(null, 5);
        $this->Cell(150, 6, "期間：" . $this->getDateFrom() . " ～ " . $this->getDateTo(), 0, 1, "C");

        $this->setFontSize(10);
        $this->SetFont('kozgopromedium', '', 10);
        $this->Cell(30, 6, "受注日", "LBT", 0, "C", true);
        $this->Cell(30, 6, "到着日", "LBT", 0, "C", true);
        $this->Cell(100, 6, "商品名", "LBT", 0, "C", true);
        $this->Cell(30, 6, "数量", "LBT", 0, "C", true);
        $this->Cell(40, 6, "単価", "LBT", 0, "C", true);
        $this->Cell(45, 6, "金額", 1, 1, "C", true);

        $this->setCellMargins(null, 0);

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(40, 6, "", "LB", 0, "R");
        $this->Cell(45, 6, "", "LBR", 1, "R");

        $this->SetFont('kozgopromedium', '', 12);
        $this->setCellPaddings(null, null, 2);
        $this->Cell(190, 6, "", 0, 0, "C");
        $this->Cell(40, 6, "小計", 1, 0, "C", true);
        $this->Cell(45, 6, "￥" . $this->getTotal(), "BR", 1, "R");

        $this->Cell(190, 6, "", 0, 0, "C");
        $this->Cell(40, 6, "消費税10%", 1, 0, "C", true);
        $this->Cell(45, 6, "￥" . $this->getTax10(), "BR", 1, "R");

        $this->Cell(190, 6, "", 0, 0, "C");
        $this->Cell(40, 6, "消費税8%", 1, 0, "C", true);
        $this->Cell(45, 6, "￥" . $this->getTax8(), "BR", 1, "R");

        $this->Cell(190, 6, "", 0, 0, "C");
        $this->Cell(40, 6, "合計金額", 1, 0, "C", true);
        $this->Cell(45, 6, "￥" . $this->getGrandTotal(), "BR", 1, "R");
    }
}

class invoicePDF extends TCPDF
{
    private $order_no;
    private $tokuisaki_nm;
    private $total;
    private $tax_8;
    private $tax_10;
    private $grand_total;
    private $bank_info_1;
    private $bank_info_2;
    private $bank_info_3;
    private $nohin_dt;
    private $seikyu_dt;

    /**
     * SET ORDER NO.
     */
    public function setOrderNo($no)
    {
        $this->order_no = $no;
    }
    /**
     * GET ORDER NO.
     */
    public function getOrderNo()
    {
        return $this->order_no;
    }

    /**
     * SET TOKUISAKI NAME
     */
    public function setTokuisakiNm($name)
    {
        $this->tokuisaki_nm = $name;
    }
    /**
     * GET TOKUISAKI NAME
     */
    public function getTokuisakiNm()
    {
        return $this->tokuisaki_nm;
    }

    /**
     * SET TOTAL
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }
    /**
     * GET TOTAL
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * SET 8% TAX
     */
    public function setTax8($tax_8)
    {
        $this->tax_8 = $tax_8;
    }
    /**
     * GET 8% TAX
     */
    public function getTax8()
    {
        return $this->tax_8;
    }

    /**
     * SET 10% TAX
     */
    public function setTax10($tax_10)
    {
        $this->tax_10 = $tax_10;
    }
    /**
     * GET 10% TAX
     */
    public function getTax10()
    {
        return $this->tax_10;
    }

    /**
     * SET GRAND TOTAL
     */
    public function setGrandTotal($grand_total)
    {
        $this->grand_total = $grand_total;
    }
    /**
     * GET GRAND TOTAL
     */
    public function getGrandTotal()
    {
        return $this->grand_total;
    }

    /**
     * SET FIRST BANK INFO 
     */
    public function setBank1($info)
    {
        $this->bank_info_1 = $info;
    }
    /**
     * GET FIRST BANK INFO
     */
    public function getBank1()
    {
        return $this->bank_info_1;
    }

    /**
     * SET SECOND BANK INFO
     */
    public function setBank2($info)
    {
        $this->bank_info_2 = $info;
    }
    /**
     * GET SECOND BANK INFO
     */
    public function getBank2()
    {
        return $this->bank_info_2;
    }

    /**
     * SET THIRD BANK INFO
     */
    public function setBank3($info)
    {
        $this->bank_info_3 = $info;
    }
    /**
     * GET THIRD BANK INFO
     */
    public function getBank3()
    {
        return $this->bank_info_3;
    }

    /**
     * SET NOHIN DATE/納品日
     */
    public function setNohinDt($date)
    {
        $this->nohin_dt = $date;
    }
    public function getNohinDt()
    {
        return $this->nohin_dt;
    }

    public function setSeikyuDt($date)
    {
        $this->seikyu_dt = $date;
    }
    public function getSeikyuDt()
    {
        return $this->seikyu_dt;
    }

    //Page header
    public function Header()
    {
        $this->setFillColor(204, 204, 204);

        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $this->SetFont('kozgopromedium', '', 24);
        // Title
        $this->Cell(30, 15, '請求書', 0, false, 'C', false, '', 0, false, 'M', 'M');

        $this->SetFont('kozgopromedium', '', 10);
        $this->MultiCell(20, 6, "受付　", 0, 'R', false, 0, 242, 7);
        $this->MultiCell(30, 6, date("Y年m月d日"), 0, 'L', false, 0, 260, 7);

        $this->SetFont('kozgopromedium', '', 10);
        // $this->MultiCell(20, 6, "納品日　", 0, "R", false, 0, 242, 12);
        // $this->MultiCell(30, 6, $this->getNohinDt(), 0, "L", false, 0, 260, 12);

        // $this->MultiCell(20, 6, "受注番号　", 0, "R", false, 0, 242, 17);
        // $this->MultiCell(50, 6, $this->getOrderNo(), 0, "L", false, 0, 260, 17);

        $this->MultiCell(20, 6, "請求日", 0, "R", false, 0, 238, 12);
        $this->MultiCell(50, 6, $this->getSeikyuDt(), 0, "L", false, 0, 260, 12);

        $this->SetFont('kozgopromedium', '', 14);
        $this->MultiCell(100, 6, $this->getTokuisakiNm() . "　御中", 0, "L", false, 0, 12, 30);

        $this->Image('../images/logo_pdf.jpg', 217, 28, 65, 12, 'JPG', 'https://uskk.com/', '', true, 150, '', false, false, 0, false, false, false);

        $this->SetFont('kozgopromedium', '', 10);
        $this->MultiCell(100, 6, "登録番号：" . REGISTER_NO, 0, "L", false, 1, 195, 45);
        $this->MultiCell(100, 6, "〒" . ZIP . "　" . ADDRESS, 0, "L", false, 1, 195, 50);
        $this->MultiCell(100, 6, COMPANY, 0, "L", false, 1, 195, 56);
        $this->MultiCell(100, 6, "TEL：" . TEL . "　　FAX：" . FAX, 0, "L", false, 1, 195, 62);

        $this->MultiCell(100, 6, "下記の通りご請求申し上げます。", 0, "L", false, 1, 10, 62);

        $this->setCellMargins(null, 4);
        $this->SetFont('kozgopromedium', '', 16);
        $this->Cell(50, 6, "税込合計金額", "TB", 0, "C");
        $this->Cell(50, 6, "¥" . $this->getGrandTotal(), "TB", 0, "L");
        $this->Cell(40, 6, "消費税（8%）", "TB", 0, "C");
        $this->Cell(40, 6, "¥" . $this->getTax8(), "TB", 0, "L");
        $this->Cell(40, 6, "消費税（10%）", "TB", 0, "C");
        $this->Cell(40, 6, "¥" . $this->getTax10(), "TB", 0, "L");
        $this->Cell(0, 6, "", "TB", 1);


        $this->SetFont('kozgopromedium', '', 12);
        $this->Cell(30, 6, "受注日", "LTB", 0, "C", true);
        $this->Cell(30, 6, "到着日", "LTB", 0, "C", true);
        $this->Cell(100, 6, "商品名", "LTB", 0, "C", true);
        $this->Cell(30, 6, "数量", "LTB", 0, "C", true);
        $this->Cell(37, 6, "単価", "LTB", 0, "C", true);
        $this->Cell(50, 6, "金額", 1, 1, "C", true);

        $this->setCellMargins(null, 0);

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(30, 6, "", "LB", 0, "C");
        $this->Cell(100, 6, "", "LB", 0, "L");
        $this->Cell(30, 6, "", "LB", 0, "R");
        $this->Cell(37, 6, "", "LB", 0, "R");
        $this->Cell(50, 6, "", "LBR", 1, "R");

        $this->MultiCell(200, 6, $this->getBank1(), 0, "L", false, 0, 10, 176);
        $this->MultiCell(200, 6, $this->getBank2(), 0, "L", false, 0, 10, 182);
        $this->MultiCell(200, 6, $this->getBank3(), 0, "L", false, 0, 10, 188);

        $this->setCellPaddings(null, null, 2);

        $this->MultiCell(37, 6, "小計", 'LB', "C", true, 0, 200, 161.5);
        $this->Cell(50, 6, "¥" . $this->getTotal(), "LBR", 1, "R");
        $this->MultiCell(37, 6, "消費税（10%）", 'LBT', "C", true, 0, 200);
        $this->Cell(50, 6, "¥" . $this->getTax10(), "LBR", 1, "R");
        $this->MultiCell(37, 6, "消費税（8%）", 'LBT', "C", true, 0, 200);
        $this->Cell(50, 6, "¥" . $this->getTax8(), "LBR", 1, "R");
        $this->MultiCell(37, 6, "合計金額", 'LBT', "C", true, 0, 200);
        $this->Cell(50, 6, "¥" . $this->getGrandTotal(), "LBR", 1, "R");

        //  $this->MultiCell(null,6,"",0,'J',false,1,10,84);
        $this->setFontSize(9);
        $this->MultiCell(50, 6, "※は軽減税率対象です。", 0, "L", false, 0, 12, 163);
    }

    //Footer
    public function Footer()
    {
        $w_page = isset($this->l['w_page']) ? $this->l['w_page'] . ' ' : '';
        if (empty($this->pagegroups)) {
            $pagenumtxt = $w_page . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages();
        } else {
            $pagenumtxt = $w_page . $this->getPageNumGroupAlias() . ' / ' . $this->getPageGroupAlias();
        }
        $this->setY(-15);
        //Print page number
        if ($this->getRTL()) {
            $this->setX($this->original_rMargin);
            $this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
        } else {
            $this->setX($this->original_lMargin);
            $this->Cell(0, 0, $this->getAliasRightShift() . $pagenumtxt, 'T', 0, 'R');
        }
    }
}

class orderPDF extends TCPDF
{
    //Page header
    public function Header()
    {
        $this->SetFont('kozgopromedium', '', 24);
        // Title
        $this->Cell(30, 15, '注文書', 0, false, 'C', false, '', 0, false, 'M', 'M');

        // $this->SetFont('kozgopromedium', '', 10);
        // $this->text(260, 5, date("Y年m月d日"));
    }

    // Page footer
    public function Footer()
    {
        // Position at 60 mm from bottom
        $this->SetY(-60);
        $this->Image('../images/footer.jpg', 9, 241, 192, 50, 'JPG', 'https://uskk.com/', '', true, 150, '', false, false, 0, false, false, false);
    }
}

class nouhinPDF extends TCPDF
{
    private $total_qty;
    private $total_cost;
    private $grand_total;
    private $tax_8;
    private $tax_10;
    private $okurisaki_nm;
    private $order_no;

    public function setTotalQty($qty)
    {
        $this->total_qty = $qty;
    }
    public function getTotalQty()
    {
        return $this->total_qty;
    }

    public function setTotalCost($cost)
    {
        $this->total_cost = $cost;
    }
    public function getTotalCost()
    {
        return $this->total_cost;
    }

    public function setGrandTotal($total)
    {
        $this->grand_total = $total;
    }
    public function getGrandTotal()
    {
        return $this->grand_total;
    }

    public function setTax8($tax_8)
    {
        $this->tax_8 = $tax_8;
    }
    public function getTax8()
    {
        return $this->tax_8;
    }

    public function setTax10($tax_10)
    {
        $this->tax_10 = $tax_10;
    }
    public function getTax10()
    {
        return $this->tax_10;
    }

    public function setOkurisakiNm($okurisaki_nm)
    {
        $this->okurisaki_nm = $okurisaki_nm;
    }

    public function getOkurisakiNm()
    {
        return $this->okurisaki_nm;
    }

    public function setOrderNo($order_no)
    {
        $this->order_no = $order_no;
    }

    public function getOrderNo()
    {
        return $this->order_no;
    }

    //Page header
    public function Header()
    {
        $this->setFillColor(204, 204, 204);
        $this->SetFont('kozgopromedium', '', 20);
        // Title
        $this->Cell(null, 15, '売　　上　　伝　　票', 0, false, 'C', false, '', 0, false, 'M', 'M');
        $this->SetFont('kozgopromedium', '', 10);
        $this->MultiCell(75, null, "", "B", 'J', false, 0, 67);

        $this->setFontSize(12);
        $this->MultiCell(60, 5, $this->getOkurisakiNm() . "　様", 0, 'L', false, 0, 15, 25);

        $this->setFontSize(10);
        $this->MultiCell(60, 5, "受注番号：" . $this->getOrderNo(), 0, 'L', false, 0, 15, 48);

        $this->Image('../images/logo_pdf.jpg', 132, 23, 65, 12, 'JPG', 'https://uskk.com/', '', true, 150, '', false, false, 0, false, false, false);

        $this->MultiCell(0, 5, "605-0851　京都市東山区東大路松原上る二丁目 玉水町73", 0, "R", false, 0, 50, 38);
        $this->MultiCell(0, 5, "TEL：" . TEL . "　　FAX：" . FAX, 0, "R", false, 0, 50, 43);
        $this->MultiCell(50, 5, COMPANY, 0, "L", false, 0, 130, 48);
        $this->MultiCell(0, 5, "https://uskk.com/", 0, "R", false, 0, 50, 48);

        $this->MultiCell(20, 5, "品　番", "LTB", "C", true, 0, 10, 55);
        $this->cell(80, 5, "商　　品　　名", "LTB", 0, "C", true);
        $this->Cell(25, 5, "数　量", "LTB", 0, "C", true);
        $this->Cell(30, 5, "単　価", "LTB", 0, "C", true);
        $this->Cell(35, 5, "金　　額", 1, 1, "C", true);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        $this->Cell(20, 5, "", "LB", 0, "L", false);
        $this->Cell(80, 5, "", "LB", 0, "L", false);
        $this->Cell(25, 5, "", "LB", 0, "R", false);
        $this->Cell(30, 5, "", "LB", 0, "R", false);
        $this->Cell(35, 5, "", "LBR", 1, "R", false);

        //BOTTOM
        $this->Cell(80, 5, "", 0, 0, "L");
        $this->Cell(20, 5, "合計数量", 1, 0, "C", true);
        $this->setCellPaddings(null, null, 2);
        $this->Cell(25, 5, number_format($this->getTotalQty(), 1), "BT", 0, "R");

        $this->setCellPaddings(null, null, 0);
        $this->Cell(30, 5, "合計金額", 1, 0, "C", true);
        $this->setCellPaddings(null, null, 2);
        $this->Cell(35, 5, "¥" . number_format($this->getTotalCost()), "BR", 1, "R");

        $this->setCellPaddings(null, null, 0);
        $this->Cell(125, 5, "", 0, 0, "C");
        $this->Cell(30, 5, "消費税(10%)", 1, 0, "C", true);
        $this->setCellPaddings(null, null, 2);
        $this->Cell(35, 5, "¥" . number_format($this->getTax10()), "BR", 1, "R");

        $this->setCellPaddings(null, null, 0);
        $this->SetFont('kozgopromedium', '', 9);
        $this->Cell(125, 20, "この度は、当社にご用命頂きありがとうございます。", 0, 0, "L", false, "", 0, false, "T", "T");

        $this->SetFont('kozgopromedium', '', 10);
        $this->Cell(30, 5, "消費税(8%)", 1, 0, "C", true);
        $this->setCellPaddings(null, null, 2);
        $this->Cell(35, 5, "¥" . number_format($this->getTax8()), "BR", 1, "R");

        $this->setCellPaddings(null, null, 0);
        $this->SetFont('kozgopromedium', '', 9);
        $this->Cell(125, 20, "上記の通りに納品申し上げます。", 0, 0, "L", false, "", 0, false, "T", "T");

        $this->SetFont('kozgopromedium', '', 10);
        $this->Cell(30, 5, "合計締高", 1, 0, "C", true);
        $this->setCellPaddings(null, null, 2);
        $this->Cell(35, 5, "¥" . number_format($this->getGrandTotal()), "BR", 1, "R");
    }
}

class denpyoPDF extends TCPDF
{
    //Page header
    public function Header()
    {
        //$this->setFillColor(204, 204, 204);
        $this->SetFont('kozgopromedium', 'U', 18);
        // Title
        $this->Cell(0, 10, '売上伝票 (控)', 0, false, 'L', false, '', 0, false, 'M', 'M');
    }
}

/**
 * 商品台帳
 */
function shohinDaicho($fname, $data)
{
    try {

        //border styles
        $dotted = array('B' => array('width' => 0, 'color' => array(0, 0, 0), 'dash' => 3, 'cap' => 'square'));
        $line = array('B' => array('width' => 0.5, 'color' => array(0, 0, 0), 'dash' => 0, 'cap' => 'square'));

        $pdf = new shohinPDF('L');

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('商品台帳');
        $pdf->SetSubject('商品台帳');
        $pdf->SetHeaderMargin(15);
        $pdf->setFooterMargin(15);
        $pdf->setAutoPageBreak(false);

        $pdf->SetFont('kozgopromedium', '', 10);

        for ($i = 0; $i < count($data); $i++) {
            if ($i % 25 == 0) {
                $pdf->AddPage();
                //table header
                $pdf->setTopMargin(35);

                $pdf->Cell(100, 6, "商品", $line);
                $pdf->Cell(60, 6, "商品名略", "B");
                $pdf->Cell(35, 6, "商品分類", "B");
                $pdf->Cell(20, 6, "単位", "B");
                $pdf->Cell(30, 6, "売上単価", "B", 0, "R");
                $pdf->Cell(30, 6, "仕入単価", "B", true, "R");
            }

            //table body
            $pdf->Cell(100, 6, $data[$i]["product_nm"], $dotted);
            $pdf->Cell(60, 6, $data[$i]["product_nm_abrv"], "B");
            $pdf->Cell(35, 6, $data[$i]["product_type"], "B");
            $sale_tani = $data[$i]["sale_tani"];
            if ($sale_tani == "なし") {
                $sale_tani = "";
            }
            $pdf->Cell(20, 6, $sale_tani, "B");
            $pdf->Cell(30, 6, number_format($data[$i]["sale_price"]), "B", false, "R");
            $pdf->Cell(30, 6, number_format($data[$i]["unit_price"]), "B", true, "R");
        };

        $pdf->Output($fname, "F"); //F = saves to folder, I = displays in browser           
    } catch (Exception $e) {
        throw $e;
    }
};

/**
 * 得意先台帳
 */
function tokuisakiDaicho($fname, $data)
{
    try {
        $dotted = array('B' => array('width' => 0, 'color' => array(0, 0, 0), 'dash' => 3, 'cap' => 'square'));
        $line = array('B' => array('width' => 0.5, 'color' => array(0, 0, 0), 'dash' => 0, 'cap' => 'square'));

        $pdf = new tokuisakiPDF("L");

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('得意先別商品帳');
        $pdf->SetSubject('得意先別商品帳');
        $pdf->SetHeaderMargin(15);
        $pdf->setFooterMargin(15);
        $pdf->setAutoPageBreak(false);

        $pdf->SetFont('kozgopromedium', '', 10);

        $pdf->setTokuisakiNm($data[0]["tokuisaki_nm"]);
        $pdf->setTokuisakiTel(substr($data[0]["tokuisaki_tel"], 0, 3) . '-' . substr($data[0]["tokuisaki_tel"], 3, 3) . '-' . substr($data[0]["tokuisaki_tel"], 6));
        $pdf->setIndustryKbn($data[0]["industry_kbn"]);

        for ($i = 0; $i < count($data); $i++) {
            if ($i % 24 == 0) {
                $pdf->AddPage();
                //table header
                $pdf->setTopMargin(40);
                // $pdf->Cell(25, 6, "代表電話番号", $line);
                // $pdf->Cell(45, 6, "得意先名", "B");
                $pdf->Cell(140, 6, "　　商品名", $line);
                // $pdf->Cell(60, 6, "商品", "B");
                $pdf->Cell(40, 6, "商品名略", "B");
                $pdf->Cell(35, 6, "商品分類", "B");
                $pdf->Cell(10, 6, "単位", "B");
                $pdf->Cell(25, 6, "売上単価", "B", false, "R");
                $pdf->Cell(25, 6, "仕入単価", "B", true, "R");
            }

            //table body
            // $pdf->Cell(25, 6, $data[$i]["tokuisaki_tel"], $dotted);
            // $pdf->Cell(45, 6, $data[$i]["tokuisaki_nm"], "B");
            $pdf->Cell(15, 6, $data[$i]["product_cd"], $dotted, 0, 'C');
            $pdf->Cell(125, 6, $data[$i]["product_nm"], "B");
            $pdf->Cell(40, 6, $data[$i]["product_nm_abrv"], "B");
            $pdf->Cell(35, 6, $data[$i]["product_type"], "B");
            $sale_tani = $data[$i]["sale_tani"];
            if ($sale_tani == "なし") {
                $sale_tani = "";
            }
            $pdf->Cell(10, 6, $sale_tani, "B", 0, 'C');
            $pdf->Cell(25, 6, number_format($data[$i]["sale_price"]), "B", false, "R");
            $pdf->Cell(25, 6, number_format($data[$i]["unit_price"]), "B", true, "R");
        };

        $pdf->Output($fname, "F"); //F = saves to folder, I = displays in browser
    } catch (Exception $e) {
        throw $e;
    }
};

/**
 * 商品別売上一覧
 */
function shohinSalesList($fname, $data, $date_from, $date_to)
{
    $icnt = count($data);
    $total_qty = number_format(array_sum(array_column($data, "qty")), 1);
    $total_cost = number_format(array_sum(array_column($data, "cost")));

    $dotted = array('B' => array('width' => 0, 'color' => array(0, 0, 0), 'dash' => 3, 'cap' => 'square'));
    $line = array('B' => array('width' => 0.5, 'color' => array(0, 0, 0), 'dash' => 0, 'cap' => 'square'));

    $pdf = new shohinSalePDF();

    $pdf->SetCreator("株式会社〇〇〇〇");
    $pdf->SetAuthor("株式会社〇〇〇〇");
    $pdf->SetTitle('商品別売上一覧');
    $pdf->SetSubject('商品別売上一覧');
    $pdf->SetHeaderMargin(15);
    $pdf->setFooterMargin(15);
    $pdf->setAutoPageBreak(false);

    $pdf->SetFont('kozgopromedium', '', 10);
    $y = 0;
    for ($i = 0; $i < $icnt; $i++) {
        //$y++;
        if ($i % 39 == 0) {
            $pdf->AddPage();
            //table header
            $pdf->setTopMargin(30);
            $pdf->Cell(0, 6, $date_from . " ～ " . $date_to, 0, 1, "C");

            $pdf->Cell(30, 6, "商品コード", $line);
            $pdf->Cell(90, 6, "商品名", "B", 0, "L");
            $pdf->Cell(30, 6, "数量", "B", 0, "R");
            $pdf->Cell(40, 6, "合計金額", "B", 1, "R");
        }

        //table body
        $pdf->Cell(30, 6, $data[$i]["product_cd"], $dotted);
        $pdf->Cell(90, 6, $data[$i]["product_nm"], "B", 0, "L");
        $pdf->Cell(30, 6,   number_format($data[$i]["qty"], 1), "B", 0, "R");
        $pdf->Cell(40, 6,  number_format($data[$i]["cost"]), "B", 1, "R");

        if ($i == ($icnt - 1)) {
            $pdf->Cell(120, 6, "【総合計】");

            $pdf->Cell(30, 6, $total_qty, 0, 0, "R");
            $pdf->Cell(40, 6, $total_cost, 0, 1, "R");
        };
    };


    $pdf->Output($fname, "I"); //F = saves to folder, I = displays in browser
};

/**
 * 売上累計表
 */
function tokuisakiSale($fname, $data, $date_from, $date_to)
{
    try {

        $icnt = count($data);
        $total_qty = number_format(array_sum(array_column($data, "qty")), 1);
        $total_cost = number_format(array_sum(array_column($data, "cost")));
        $total_unit_cost = number_format(array_sum(array_column($data, "unit_cost")));
        $grand_total = number_format(array_sum(array_column($data, "total")));



        $daysOfWeek = array('日', '月', '火', '水', '木', '金', '土');

        $dotted = array('B' => array('width' => 0, 'color' => array(0, 0, 0), 'dash' => 3, 'cap' => 'square'));
        $line = array('B' => array('width' => 0.5, 'color' => array(0, 0, 0), 'dash' => 0, 'cap' => 'square'));

        $pdf = new tokuisakiSalePDF();

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('売上累計表');
        $pdf->SetSubject('売上累計表');
        $pdf->SetHeaderMargin(15);
        $pdf->setFooterMargin(15);
        $pdf->setAutoPageBreak(false);

        $pdf->SetFont('kozgopromedium', '', 10);

        for ($i = 0; $i < $icnt; $i++) {

            //DATE WITH 曜日
            $date = DateTime::createFromFormat('Y/m/d', $data[$i]["sale_dt"]);
            $dayOfWeek = $daysOfWeek[(int)$date->format('w')];
            $formattedDate = $date->format('Y/m/d') . '　(' . $dayOfWeek . ')';

            if ($i % 39 == 0) {
                $y = 0;
                $pdf->AddPage();
                //table header
                $pdf->setTopMargin(30);
                $pdf->Cell(0, 6, $date_from . " ～ " . $date_to, 0, 1, "C");

                $pdf->Cell(30, 6, "売上日", $line);
                $pdf->Cell(40, 6, "売上個数", "B", 0, "R");
                $pdf->Cell(40, 6, "売上金額", "B", 0, "R");
                $pdf->Cell(40, 6, "仕入金額", "B", 0, "R");
                $pdf->Cell(40, 6, "粗利益", "B", 1, "R");
            }
            //$y++;
            //table body
            $pdf->Cell(30, 6, $formattedDate, $dotted);
            $pdf->Cell(40, 6, number_format($data[$i]["qty"], 1), "B", 0, "R");
            $pdf->Cell(40, 6,   number_format($data[$i]["cost"]), "B", 0, "R");
            $pdf->Cell(40, 6,  number_format($data[$i]["unit_cost"]), "B", 0, "R");
            $pdf->Cell(40, 6,  number_format($data[$i]["total"]), "B", 1, "R");

            //if $i = count($data)
            if ($i == ($icnt - 1)) {
                $pdf->Cell(30, 6, "【総合計】");
                $pdf->Cell(40, 6, $total_qty, 0, 0, "R");
                $pdf->Cell(40, 6, $total_cost, 0, 0, "R");
                $pdf->Cell(40, 6, $total_unit_cost, 0, 0, "R");
                $pdf->Cell(40, 6, $grand_total, 0, 1, "R");
            }
        };


        $pdf->Output($fname, "I"); //F = saves to folder, I = displays in browser
    } catch (Exception $e) {
        throw $e;
    }
};

/**
 * 出荷依頼書
 */
function shukaIrai($fname, $data)
{
    try {
        $cnt = count($data);

        $pdf = new shukaPDF('L');

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('出荷依頼書');
        $pdf->SetSubject('出荷依頼書');
        $pdf->SetHeaderMargin(15);
        $pdf->setFooterMargin(10);
        $pdf->setAutoPageBreak(false);

        $pdf->setFillColor(204, 204, 204);

        $h = 68;

        for ($i = 0; $i < $cnt; $i++) {

            if ($i % 20 == 0) {
                $pdf->setOrderNo($data[$i]["order_no"]);
                $pdf->setInquireNo($data[$i]["inquire_no"]);
                $pdf->setOkurisakiZip($data[$i]["okurisaki_zip"]);
                $pdf->setOkurisakiTel($data[$i]["okurisaki_tel"]);
                $pdf->setOkurisakiAdr1($data[$i]["okurisaki_adr_1"]);
                $pdf->setOkurisakiAdr2($data[$i]["okurisaki_adr_2"]);
                $pdf->setOkurisakiAdr3($data[$i]["okurisaki_adr_3"]);
                $pdf->setOkurisakiNm($data[$i]["okurisaki_nm"]);
                $pdf->setGrandTotal($data[$i]["grand_total"]);

                $h = 68;
                $pdf->AddPage();
            };

            $pdf->SetFont('kozgopromedium', '', 12);
            $pdf->MultiCell(45, 6, $data[$i]["product_cd"], 0, 'C', false, 0, 10, $h);
            $pdf->MultiCell(165, 6, $data[$i]["product_nm"], 0, 'L', false, 0, 55, $h);
            $sale_tani = $data[$i]["tani"];
            if ($sale_tani == "なし") {
                $sale_tani = "";
            };
            $pdf->MultiCell(60, 6, number_format($data[$i]["qty"], 1) . "　" . $sale_tani, 0, 'C', false, 0, 220, $h);

            $h = $h + 6;
        }

        $pdf->Output($fname, "F"); //F = saves to folder, I = displays in browser

    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * 売掛金元帳
 */
function urikake($fname, $data, $date_from, $date_to)
{
    try {
        $dbh = new PDO(DB_CON_STR, DB_USER, DB_PASS);
        $sql = "select sum(cast(total_cost as integer)) as total_cost, 
                sum(cast(tax_8 as integer)) as tax_8,
                sum(cast(tax_10 as integer)) as tax_10,
                sum(cast(grand_total as integer)) as grand_total from t_sale_h
                where tokuisaki_cd = :tokuisaki_cd
                AND sale_dt >= :dt_from 
                AND sale_dt <= :dt_to;";

        $sth = $dbh->prepare($sql);

        $cnt = count($data);
        $tokuisaki_cd = null;

        $pdf = new urikakePDF("L");

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('売掛金元帳');
        $pdf->SetSubject('売掛金元帳');
        $pdf->SetHeaderMargin(15);
        $pdf->setFooterMargin(10);
        $pdf->setAutoPageBreak(false);

        $pdf->setDateFrom($date_from);
        $pdf->setDateTo($date_to);

        $pdf->SetFont('kozgopromedium', '', 10);

        for ($i = 0; $i < $cnt; $i++) {
            if ($data[$i]["tokuisaki_cd"] != $tokuisaki_cd) {
                $tokuisaki_cd = $data[$i]["tokuisaki_cd"];

                $params = array();
                $params["tokuisaki_cd"] = $tokuisaki_cd;
                $params["dt_from"] = $date_from;
                $params["dt_to"] = $date_to;

                $sth->execute($params);
                $cost_data = $sth->fetchAll(PDO::FETCH_ASSOC);

                $pdf->setTokuisakiCd($tokuisaki_cd);
                $pdf->setTokuisakiNm($data[$i]["tokuisaki_nm"]);
                $pdf->setTotal(number_format($cost_data[0]["total_cost"]));
                $pdf->setTax8(number_format($cost_data[0]["tax_8"]));
                $pdf->setTax10(number_format($cost_data[0]["tax_10"]));
                $pdf->setGrandTotal(number_format($cost_data[0]["grand_total"]));

                $pdf->AddPage();
                $pdf->setY(60);
                $y = 0;
            };

            $pdf->setCellPaddings(null, null, 2);
            $pdf->Cell(30, 6, $data[$i]["sale_dt"], 0, 0, "C");
            $pdf->Cell(30, 6, $data[$i]["receive_dt"], 0, 0, "C");
            $pdf->Cell(100, 6, $data[$i]["product_nm"], 0, 0, "L");

            $pdf->Cell(30, 6, number_format($data[$i]["qty"], 1), 0, 0, "R");
            $pdf->Cell(40, 6, "¥" . number_format($data[$i]["sale_price"]), 0, 0, "R");
            $pdf->Cell(45, 6, "¥" . number_format($data[$i]["row_cost"]), 0, 1, "R");

            $y++;

            if ($y != 0 && ($y % 19) == 0) {
                $pdf->AddPage();
                $pdf->setY(60);
            }
        }

        $pdf->Output($fname, "F"); //F = saves to folder, I = displays in browser
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * CREATE INVOICE/請求書
 * @param string $fname The file name
 * @param array $data An object array of the data
 * @param array $bank_info An array of the different bank accounts
 */
function invoice($fname, $data, $bank_info, $dt_from, $dt_to)
{
    try {
        $dbh = new PDO(DB_CON_STR, DB_USER, DB_PASS);
        $sql = "select sum(cast(total_cost as integer)) as total_cost, 
                sum(cast(tax_8 as integer)) as tax_8,
                sum(cast(tax_10 as integer)) as tax_10,
                sum(cast(grand_total as integer)) as grand_total from t_sale_h
                where tokuisaki_cd = :tokuisaki_cd
                AND sale_dt >= :dt_from 
                AND sale_dt <= :dt_to;";
        $sth = $dbh->prepare($sql);

        $cnt = count($data);

        $order_no = null;
        $tokuisaki_cd = null;
        $grand_total = null;
        $pdf = new invoicePDF("L");

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('請求書');
        $pdf->SetSubject('請求書');
        $pdf->SetHeaderMargin(15);
        $pdf->setAutoPageBreak(false);

        $pdf->setFillColor(204, 204, 204);

        $pdf->setBank1($bank_info["account_1"]);
        $pdf->setBank2($bank_info["account_2"]);
        $pdf->setBank3($bank_info["account_3"]);

        $pdf->SetFont('kozgopromedium', '', 10);

        for ($i = 0; $i < $cnt; $i++) {
            if ($data[$i]["tokuisaki_cd"] != $tokuisaki_cd) {
                $order_no = ltrim($data[$i]["order_no"], '0');
                $tokuisaki_cd = $data[$i]["tokuisaki_cd"];

                //get total cost
                $params = array();
                $params["tokuisaki_cd"] = $tokuisaki_cd;
                $params["dt_from"] = $dt_from;
                $params["dt_to"] = $dt_to;

                $sth->execute($params);
                $cost_data = $sth->fetchAll(PDO::FETCH_ASSOC);

                $pdf->setOrderNo($order_no);
                $pdf->setNohinDt($data[$i]["nohin_dt"]);
                $pdf->setTokuisakiNm($data[$i]["tokuisaki_nm"]);
                $pdf->setTotal(number_format($cost_data[0]["total_cost"]));
                $pdf->setTax8(number_format($cost_data[0]["tax_8"]));
                $pdf->setTax10(number_format($cost_data[0]["tax_10"]));
                $pdf->setGrandTotal(number_format($cost_data[0]["grand_total"]));

                $dateTime = new DateTime($data[$i]["seikyu_dt"] . '/01');
                $lastDay = $dateTime->format('t');
                $day = min($data[$i]["bill_dt"], $lastDay);
                $formattedDate = $dateTime->format('Y/m/') . $day;
                $pdf->setSeikyuDt($formattedDate);

                $pdf->AddPage();
                $pdf->setY(90);
                $y = 0;
            };

            $pdf->setCellPaddings(null, null, null);
            $pdf->Cell(30, 6, $data[$i]["sale_dt"], 0, 0, "C");
            $pdf->Cell(30, 6, $data[$i]["receive_dt"], 0, 0, "C");

            $product_nm = $data[$i]["product_nm"];
            $tax_mark = "";
            if (in_array($data[$i]["tax_kbn"], REDUCE_TAX_RATE)) {
                $tax_mark = TAX_MARK;
            };

            $pdf->Cell(94, 6, $product_nm, 0, 0, "L");
            $pdf->Cell(6, 6, $tax_mark, 0, 0, "L");

            $pdf->setCellPaddings(null, null, 2);
            $pdf->Cell(30, 6, number_format($data[$i]["qty"], 1), 0, 0, "R");
            $pdf->Cell(37, 6, "¥" . number_format($data[$i]["sale_price"]), 0, 0, "R");
            $pdf->Cell(50, 6, "¥" . number_format($data[$i]["row_cost"]), 0, 1, "R");

            $y++;

            if ($y != 0 && ($y % 12) == 0) {
                $pdf->AddPage();
                $pdf->setY(90);
            }
        };

        $pdf->Output($fname, "F"); //F = saves to folder, I = displays in browser

    } catch (Exception $e) {
        throw $e;
    }
};
/**
 * CREATE A3 注文書・納品書・納品書（控）
 */
function A3Denpyo($fname, $data)
{
    try {
        $cnt = count($data);
        $barcode1 = ltrim($data[0]["order_no"], "0");
        $barcode2 = $data[0]["tokuisaki_tel"];

        $pdf = new TCPDF("L", "mm", "A3");

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('売上伝票');
        $pdf->SetSubject('売上伝票');
        $pdf->SetHeaderMargin(0);
        $pdf->setFooterMargin(0);
        $pdf->setAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setFillColor(204, 204, 204);

        // $y = 0;

        for ($i = 0; $i < $cnt; $i++) {
            //  $y++;
            if ($i % 13 == 0) {

                $order_y = 70;
                $nouhin_y = 50;
                $denpyo_y = 193;

                $pdf->AddPage();

                //注文書
                // $pdf->MultiCell(210, 297, "", 1, "", false, 0, 0, 0);

                //title
                $pdf->SetFont('msmincho', '', 24);
                $pdf->MultiCell(210, "", "注　文　書", 0, "J", false, 0, 15, 10);
                //$pdf->Image('../images/order_title_pdf.jpg', 20, 12, 50, 12, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);

                //top
                $pdf->SetFont('kozgopromedium', '', 10);
                $pdf->MultiCell(190, 5, "屋号・お名前", "T", "L", false, 0, 10, 25);
                $pdf->MultiCell(190, 5, "不在時連絡先（オプション）携帯番号など", 0, "R", false, 0, 10, 25);

                $pdf->MultiCell(190, 5, "お電話", "T", "L", false, 0, 10, 35);
                $pdf->MultiCell(190, 5, "FAX（オプション）", 0, "R", false, 0, 10, 35);
                $pdf->SetFont('msmincho', '', 10);
                if ($data[0]["tokuisaki_disp_kbn"] == "1") {
                    $pdf->MultiCell(190, 5, $data[0]["tokuisaki_nm"] . "　様", 0, "L", false, 0, 15, 30);
                    $pdf->MultiCell(190, 5, substr($data[0]["tokuisaki_tel"], 0, 3) . '-' . substr($data[0]["tokuisaki_tel"], 3, 3) . '-' . substr($data[0]["tokuisaki_tel"], 6), 0, "L", false, 0, 15, 40);
                }

                $pdf->MultiCell(190, 5, "お住所", "T", "L", false, 0, 10, 45);
                $pdf->SetFont('kozgopromedium', 'I', 9);
                $pdf->MultiCell(75, 5, "（変更があればお書きください。）", 0, "C", false, 0, 10, 45);
                $pdf->SetFont('kozgopromedium', '', 10);
                $pdf->MultiCell(190, 5, "〒", 0, "L", false, 0, 10, 50);
                $pdf->MultiCell(100, 5, "", "B", "L", false, 0, 10, 55);


                $pdf->MultiCell(85, 5, "ご注文は数量だけの記入でも承ります。", 0, "L", false, 0, 115, 53);
                $pdf->MultiCell(85, 5, "↓（合計はこちらで計算します。）", 0, "L", false, 0, 115, 58);

                //table header
                $pdf->MultiCell(90, 5, "商品名", "LBT", "C", true, 0, 10, 65);
                $pdf->MultiCell(25, 5, "単価", "LBT", "C", true, 0, 100, 65);
                $pdf->MultiCell(30, 5, "数量", "LBT", "C", true, 0, 125, 65);
                $pdf->MultiCell(45, 5, "合計価格", 1, "C", true, 0, 155, 65);

                //table rows
                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 70);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 70);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 70);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 70);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 80);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 80);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 80);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 80);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 90);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 90);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 90);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 90);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 100);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 100);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 100);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 100);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 110);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 110);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 110);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 110);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 120);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 120);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 120);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 120);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 130);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 130);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 130);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 130);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 140);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 140);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 140);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 140);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 150);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 150);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 150);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 150);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 160);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 160);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 160);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 160);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 170);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 170);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 170);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 170);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 180);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 180);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 180);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 180);

                $pdf->MultiCell(90, 10, "", "LBT", "C", false, 0, 10, 190);
                $pdf->MultiCell(25, 10, "", "LBT", "C", false, 0, 100, 190);
                $pdf->MultiCell(30, 10, "", "LBT", "C", false, 0, 125, 190);
                $pdf->MultiCell(45, 10, "", 1, "C", false, 0, 155, 190);

                //order table bottom
                $pdf->write1DBarcode($barcode2, 'CODABAR', 30, 205, 50, 8, 0.4);

                $pdf->MultiCell(25, 15, "送料", "LB", "C", false, 0, 100, 200, true, 0, false, true, 15, "M");

                $pdf->MultiCell(30, 5, "□無料", "LB", "L", false, 0, 125, 200);
                $pdf->MultiCell(30, 5, "□￥500", "LB", "L", false, 0, 125, 205);
                $pdf->MultiCell(30, 5, "□￥1,000", "LB", "L", false, 0, 125, 210);
                $pdf->MultiCell(30, 5, "合計", "LB", "L", false, 0, 125, 215);

                $pdf->MultiCell(45, 5, "※税込10800円以上の注文", "LBR", "L", false, 0, 155, 200);
                $pdf->MultiCell(45, 5, "※5400円以上の注文", "LBR", "L", false, 0, 155, 205);
                $pdf->MultiCell(45, 5, "※5400円未満の注文", "LBR", "L", false, 0, 155, 210);
                $pdf->MultiCell(45, 5, "", "LBR", "L", false, 0, 155, 215);

                //Footer
                $pdf->Image('../images/footer.jpg', 10, 222, 190, 45, 'JPG', 'https://uskk.com/', '', true, 150, '', false, false, 0, false, false, false);
                $pdf->Image('../images/order_bottom.jpg', 10, 268, 190, 12, 'JPG', 'https://uskk.com/', '', true, 150, '', false, false, 0, false, false, false);

                //納品書
                //title
                // $pdf->MultiCell(210, 148, "", 1, "", false, 0, 210, 0);
                $pdf->SetFont('msmincho', '', 24);
                $pdf->MultiCell(62, 10, "納　　品　　書", 'B', "J", false, 0, 235, 6);

                //top
                $pdf->SetFont('msmincho', '', 10);
                $pdf->MultiCell(45, 5, "受注番号：" . $barcode1, 0, "L", false, 0, 340, 6);
                $pdf->MultiCell(40, 5, date("Y/m/d", strtotime($data[0]["sale_dt"])), 0, "R", false, 0, 368, 6);

                // $pdf->MultiCell(40, 5, $barcode1, 0, "R", false, 0, 330, 6);
                //$pdf->MultiCell(40, 5, date("Y/m/d", strtotime($data[0]["sale_dt"])), 0, "R", false, 0, 368, 6);
                // $pdf->SetFont('msmincho', '', 10);
                $pdf->SetFont('kozgopromedium', '', 10);
                $pdf->MultiCell(100, 5, $data[0]["tokuisaki_nm"] . "　様", 0, "L", false, 0, 225, 20);
                $pdf->SetFont('msmincho', '', 10);
                $pdf->MultiCell(60, 5, "〒" . $data[0]["tokuisaki_zip"], 0, "L", false, 0, 225, 25);
                $pdf->MultiCell(85, 5,  $data[0]["tokuisaki_adr_1"] . $data[0]["tokuisaki_adr_2"] . $data[0]["tokuisaki_adr_3"], 0, "L", false, 0, 225, 30);
                // $pdf->MultiCell(60, 5, , 0, "L", false, 0, 225, 35);

                $pdf->MultiCell(60, 5, "TEL：" . substr($data[0]["tokuisaki_tel"], 0, 3) . '-' . substr($data[0]["tokuisaki_tel"], 3, 3) . '-' . substr($data[0]["tokuisaki_tel"], 6), 0, "L", false, 0, 225, 40);

                $pdf->Image('../images/logo_pdf.jpg', 345, 12, 60, 10, 'JPG', 'https://uskk.com/', '', true, 150, '', false, false, 0, false, false, false);

                $pdf->SetFont('kozgopromedium', '', 10);
                //得意先の売上区分 == 売掛
                if ($data[0]["sale_kbn"] != "3") {
                    $pdf->MultiCell(0, 5, "登録番号：" . REGISTER_NO, 0, "R", false, 0, 250, 25);
                }

                $pdf->MultiCell(0, 5, "605-0851　京都市東山区東大路松原上る二丁目 玉水町73", 0, "R", false, 0, 250, 30);
                $pdf->MultiCell(0, 5, "TEL：" . TEL . "　　FAX：" . FAX, 0, "R", false, 0, 250, 35);
                $pdf->MultiCell(50, 5, COMPANY, 0, "L", false, 0, 338, 40);
                $pdf->MultiCell(0, 5, "https://uskk.com/", 0, "R", false, 0, 250, 40);

                //table header
                $pdf->MultiCell(105, 5, "商品名", "LBT", "C", true, 0, 220, 45);
                $pdf->MultiCell(25, 5, "単価", "LBT", "C", true, 0, 320, 45);
                $pdf->MultiCell(30, 5, "注文数", "LBT", "C", true, 0, 345, 45);
                $pdf->MultiCell(35, 5, "合計価格", 1, "C", true, 0, 375, 45);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 50);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 50);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 50);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 50);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 55);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 55);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 55);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 55);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 60);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 60);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 60);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 60);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 65);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 65);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 65);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 65);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 70);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 70);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 70);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 70);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 75);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 75);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 75);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 75);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 80);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 80);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 80);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 80);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 85);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 85);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 85);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 85);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 90);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 90);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 90);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 90);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 95);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 95);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 95);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 95);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 100);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 100);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 100);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 100);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 105);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 105);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 105);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 105);

                $pdf->MultiCell(105, 5, "", "LBT", "C", false, 0, 220, 110);
                $pdf->MultiCell(25, 5, "", "LBT", "C", false, 0, 320, 110);
                $pdf->MultiCell(30, 5, "", "LBT", "C", false, 0, 345, 110);
                $pdf->MultiCell(35, 5, "", "LBR", "C", false, 0, 375, 110);

                $pdf->SetFont('msmincho', '', 7);
                $pdf->MultiCell(50, 5, "※は軽減税率対象です", 0, "L", false, 0, 220, 115);
                $pdf->MultiCell(100, 5, "当社にご用命頂き有難うございます。上記の通りに納品申し訳上げます。", 0, "L", false, 0, 220, 118);
                $pdf->MultiCell(100, 5, "代引き、コレクトの場合は、運送会社の控えが領収書になります。", 0, "L", false, 0, 220, 121);
                $pdf->write1DBarcode($barcode2, 'CODABAR', 240, 125, 50, 8, 0.4);

                $pdf->SetFont('kozgopromedium', '', 8);
                $pdf->MultiCell(50, 5, $data[0]["order_kbn"], 0, "L", false, 0, 220, 135);

                $pdf->SetFont('kozgopromedium', '', 10);
                $pdf->MultiCell(30, 5, "合計金額", "LTB", "C", true, 0, 345, 115);
                $pdf->setCellPaddings(null, null, 2);
                $pdf->SetFont('msmincho', '', 10);
                $pdf->MultiCell(35, 5, "¥" . number_format($data[0]["total_cost"]), 1, "R", false, 0, 375, 115);

                //得意先の売上区分 == 売掛
                if ($data[0]["sale_kbn"] != "3") {
                    $pdf->setCellPaddings(null, null, null);
                    $pdf->SetFont('kozgopromedium', '', 10);
                    $pdf->MultiCell(30, 5, "消費税10%", "LTB", "C", true, 0, 345, 120);
                    $pdf->MultiCell(30, 5, "消費税8%", "LTB", "C", true, 0, 345, 125);
                    $pdf->MultiCell(30, 5, "合計締高", "LTB", "C", true, 0, 345, 130);

                    $pdf->SetFont('msmincho', '', 10);
                    $pdf->setCellPaddings(null, null, 2);
                    $pdf->MultiCell(35, 5, "¥" . number_format($data[0]["tax_10"]), 1, "R", false, 0, 375, 120);
                    $pdf->MultiCell(35, 5, "¥" . number_format($data[0]["tax_8"]), 1, "R", false, 0, 375, 125);
                    $pdf->MultiCell(35, 5, "¥" . number_format($data[0]["grand_total"]), 1, "R", false, 0, 375, 130);

                    $pdf->write1DBarcode($barcode1, 'CODABAR', 353, 137, 50, 8, 0.4);
                } else {
                    $pdf->write1DBarcode($barcode1, 'CODABAR', 353, 122, 50, 8, 0.4);
                }

                $pdf->setCellPaddings(null, null, null);

                //売上伝票（控え）
                // $pdf->MultiCell(210, 149, "", 0, "", false, 0, 210, 148);
                //得意先の売上区分 == 売掛
                if ($data[0]["sale_kbn"] === "3") {
                    //$pdf->setFillColor(0, 0, 0);
                    //$pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));
                    //$pdf->SetTextColor(255, 255, 255);
                    $pdf->MultiCell(93, 129, '', 0, 'J', true, 0, 318, 152);
                    //title
                    $pdf->SetFont('kozgopromedium', 'U', 16);
                    $pdf->MultiCell(35, "", "納品書(控え)", 0, "C", false, 0, 320, 158);

                    //top
                    $pdf->SetFont('kozgopromedium', '', 9);
                    $pdf->MultiCell(55, 5, $data[0]["tokuisaki_nm"] . "　様", 0, "L", false, 0, 320, 165, true, 0, false, true, 5, 'T', true);
                    $pdf->MultiCell(55, 5, $data[0]["tokuisaki_adr_1"] . $data[0]["tokuisaki_adr_2"] . $data[0]["tokuisaki_adr_3"], 0, "L", false, 0, 320, 170);
                    //$pdf->MultiCell(55, 5, $data[0]["tokuisaki_adr_3"], 0, "L", false, 0, 320, 175);
                    $pdf->MultiCell(60, 5, "TEL：" . substr($data[0]["tokuisaki_tel"], 0, 3) . '-' . substr($data[0]["tokuisaki_tel"], 3, 3) . '-' . substr($data[0]["tokuisaki_tel"], 6), 0, "L", false, 0, 320, 182);


                    $pdf->MultiCell(32, 5, "受注番号", "T", "R", false, 0, 377, 155);
                    $pdf->MultiCell(32, 5, $barcode1, 0, "R", false, 0, 377, 160);
                    $pdf->MultiCell(32, 5, "受注日", "T", "R", false, 0, 377, 165);
                    $pdf->MultiCell(32, 5, date("Y年m月d日", strtotime($data[0]["sale_dt"])), 0, "R", false, 0, 377, 170);
                    $pdf->MultiCell(32, 5, "印字時間", "T", "R", false, 0, 377, 175);
                    $pdf->MultiCell(32, 5, date("Y/m/d H:i"), "B", "R", false, 0, 377, 180);

                    $pdf->setFontSize(9);
                    //table header
                    $pdf->MultiCell(35, 5, "商品名（略）", "LTB", "C", true, 0, 320, 188);
                    $pdf->MultiCell(16, 5, "単価", "LTB", "C", true, 0, 355, 188);
                    $pdf->MultiCell(19, 5, "数量", "LTB", "C", true, 0, 371, 188);
                    $pdf->MultiCell(20, 5, "金額", 1, "C", true, 0, 389, 188);

                    //table body
                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 193);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 193);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 193);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 193);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 198);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 198);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 198);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 198);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 203);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 203);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 203);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 203);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 208);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 208);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 208);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 208);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 213);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 213);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 213);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 213);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 218);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 218);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 218);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 218);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 223);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 223);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 223);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 223);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 228);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 228);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 228);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 228);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 233);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 233);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 233);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 233);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 238);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 238);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 238);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 238);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 243);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 243);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 243);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 243);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 248);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 248);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 248);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 248);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 253);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 253);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 253);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 253);
                    $pdf->setFontSize(8);
                    $pdf->MultiCell(50, 20, "", 1, "C", false, 0, 320, 260);
                    $pdf->MultiCell(18, 5, "合計金額", 1, "C", true, 0, 371, 260);
                    //$pdf->setFontSize(8);
                    $pdf->MultiCell(20, 5, "¥" . number_format($data[0]["total_cost"]), 1, "R", false, 0, 389, 260);
                    //$pdf->setFontSize(9);
                    $pdf->MultiCell(18, 5, "消費税10%", 1, "C", true, 0, 371, 265);
                    //$pdf->setFontSize(8);
                    $pdf->MultiCell(20, 5, "¥" . number_format($data[0]["tax_10"]), 1, "R", false, 0, 389, 265);
                    //$pdf->setFontSize(9);
                    $pdf->MultiCell(18, 5, "消費税8%", 1, "C", true, 0, 371, 270);
                    //$pdf->setFontSize(8);
                    $pdf->MultiCell(20, 5, "¥" . number_format($data[0]["tax_8"]), 1, "R", false, 0, 389, 270);
                    //$pdf->setFontSize(9);
                    $pdf->MultiCell(18, 5, "合計締高", 1, "C", true, 0, 371, 275);
                    //$pdf->setFontSize(8);
                    $pdf->MultiCell(20, 5, "¥" . number_format($data[0]["grand_total"]), 1, "R", false, 0, 389, 275);

                    $pdf->MultiCell(50, 5, "※は軽減税率対象です", 0, "L", false, 0, 322, 260);

                    $pdf->write1DBarcode($barcode2, 'CODABAR', 350, 282, 50, 6, 0.4);

                    //$pdf->SetTextColor(0, 0, 0);
                    $pdf->MultiCell(50, 5, $barcode2, 0, "C", false, 0, 350, 288);
                } else {
                    //title
                    $pdf->SetFont('kozgopromedium', 'U', 16);
                    $pdf->MultiCell(35, "", "納品書(控え)", 0, "C", false, 0, 320, 158);

                    //top
                    $pdf->SetFont('kozgopromedium', '', 9);
                    $pdf->MultiCell(55, 5, $data[0]["tokuisaki_nm"] . "　様", 0, "L", false, 0, 320, 165, true, 0, false, true, 5, 'T', true);
                    $pdf->MultiCell(55, 5, $data[0]["tokuisaki_adr_1"] . $data[0]["tokuisaki_adr_2"] . $data[0]["tokuisaki_adr_3"], 0, "L", false, 0, 320, 170);
                    //$pdf->MultiCell(55, 5, $data[0]["tokuisaki_adr_3"], 0, "L", false, 0, 320, 175);
                    $pdf->MultiCell(60, 5, "TEL：" . substr($data[0]["tokuisaki_tel"], 0, 3) . '-' . substr($data[0]["tokuisaki_tel"], 3, 3) . '-' . substr($data[0]["tokuisaki_tel"], 6), 0, "L", false, 0, 320, 182);


                    $pdf->MultiCell(32, 5, "受注番号", "T", "R", false, 0, 377, 155);
                    $pdf->MultiCell(32, 5, $barcode1, 0, "R", false, 0, 377, 160);
                    $pdf->MultiCell(32, 5, "受注日", "T", "R", false, 0, 377, 165);
                    $pdf->MultiCell(32, 5, date("Y年m月d日", strtotime($data[0]["sale_dt"])), 0, "R", false, 0, 377, 170);
                    $pdf->MultiCell(32, 5, "印字時間", "T", "R", false, 0, 377, 175);
                    $pdf->MultiCell(32, 5, date("Y/m/d H:i"), "B", "R", false, 0, 377, 180);

                    $pdf->setFontSize(9);
                    //table header
                    $pdf->MultiCell(35, 5, "商品名（略）", "LTB", "C", true, 0, 320, 188);
                    $pdf->MultiCell(16, 5, "単価", "LTB", "C", true, 0, 355, 188);
                    $pdf->MultiCell(19, 5, "数量", "LTB", "C", true, 0, 371, 188);
                    $pdf->MultiCell(20, 5, "金額", 1, "C", true, 0, 389, 188);

                    //table body
                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 193);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 193);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 193);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 193);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 198);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 198);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 198);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 198);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 203);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 203);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 203);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 203);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 208);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 208);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 208);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 208);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 213);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 213);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 213);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 213);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 218);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 218);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 218);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 218);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 223);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 223);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 223);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 223);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 228);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 228);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 228);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 228);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 233);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 233);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 233);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 233);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 238);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 238);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 238);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 238);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 243);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 243);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 243);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 243);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 248);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 248);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 248);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 248);

                    $pdf->MultiCell(35, 5, "", "LTB", "C", false, 0, 320, 253);
                    $pdf->MultiCell(16, 5, "", "LTB", "C", false, 0, 355, 253);
                    $pdf->MultiCell(19, 5, "", "LTB", "C", false, 0, 371, 253);
                    $pdf->MultiCell(20, 5, "", 1, "C", false, 0, 389, 253);
                    $pdf->setFontSize(8);
                    $pdf->MultiCell(50, 20, "", 1, "C", false, 0, 320, 260);
                    $pdf->MultiCell(18, 5, "合計金額", 1, "C", true, 0, 371, 260);
                    //$pdf->setFontSize(8);
                    $pdf->MultiCell(20, 5, "¥" . number_format($data[0]["total_cost"]), 1, "R", false, 0, 389, 260);
                    //$pdf->setFontSize(9);
                    $pdf->MultiCell(18, 5, "消費税10%", 1, "C", true, 0, 371, 265);
                    //$pdf->setFontSize(8);
                    $pdf->MultiCell(20, 5, "¥" . number_format($data[0]["tax_10"]), 1, "R", false, 0, 389, 265);
                    //$pdf->setFontSize(9);
                    $pdf->MultiCell(18, 5, "消費税8%", 1, "C", true, 0, 371, 270);
                    //$pdf->setFontSize(8);
                    $pdf->MultiCell(20, 5, "¥" . number_format($data[0]["tax_8"]), 1, "R", false, 0, 389, 270);
                    //$pdf->setFontSize(9);
                    $pdf->MultiCell(18, 5, "合計締高", 1, "C", true, 0, 371, 275);
                    //$pdf->setFontSize(8);
                    $pdf->MultiCell(20, 5, "¥" . number_format($data[0]["grand_total"]), 1, "R", false, 0, 389, 275);

                    $pdf->write1DBarcode($barcode2, 'CODABAR', 350, 281, 50, 6, 0.4);
                    $pdf->MultiCell(50, 5, $barcode2, 0, "C", false, 0, 350, 287);

                    //$pdf->setFontSize(9);
                    $pdf->MultiCell(50, 5, "※は軽減税率対象です", 0, "L", false, 0, 322, 260);
                }

                $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
                $pdf->SetTextColor(0, 0, 0);

                /**
                 * 領収書
                 */
                // $pdf->setFontSize(20);
                if ($data[0]["receipt_flg"] == "1") {
                    $pdf->SetFont('msmincho', '', 20);
                    $pdf->MultiCell(50, 10, "領収書", 'B', 'C', false, 0, 240, 160, true, 4);
                    //$pdf->SetFont('kozgopromedium');
                    $pdf->setFontSize(10);
                    $pdf->MultiCell(40, 5, date("Y/m/d", strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 290, 172);

                    $pdf->MultiCell(90, 6, $data[0]["tokuisaki_nm"] . "　様", 0, 'L', false, 0, 220, 177, true, 0, false, true, 6, 'T', true);

                    $pdf->setFontSize(20);
                    $pdf->MultiCell(90, 15, "￥" . number_format($data[0]["grand_total"]), 1, "C", false, 0, 220, 184, true, 0, false, true, 15, 'M');

                    $pdf->setFontSize(10);
                    // $pdf->MultiCell(40, 5, "受注日：" . date("Y/m/d", strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 270, 175);
                    $pdf->MultiCell(90, 5, "上記の金額を正に領収いたしました。", 0, "C", false, 0, 220, 200);

                    //LOGO
                    $pdf->Image('../images/logo_pdf.jpg', 245, 209, 40, 8, 'JPG', 'https://uskk.com/', '', true, 150, '', false, false, 0, false, false, false);

                    $pdf->setFontSize(8);
                    $pdf->MultiCell(15, 15, "", 1, 'C', false, 0, 220, 214, true, 0, false, true, 15, 'T');
                    $pdf->MultiCell(15, 5, "収　入", 0, 'C', false, 0, 220, 216, true, 0);
                    $pdf->MultiCell(15, 15, "", 0, 'C', false, 0, 220, 217, true, 0, false, true, 15, 'B');
                    $pdf->MultiCell(15, 5, "印　紙", 0, 'C', false, 0, 220, 222, true, 0);

                    $pdf->MultiCell(15, 5, "取扱者印", 'B', 'C', false, 0, 295, 214, true, 0);
                    $pdf->MultiCell(15, 20, "", 1, 'C', false, 0, 295, 214, true, 0);

                    // $pdf->setFontSize(10);
                    $pdf->MultiCell(60, 5, ADDRESS, 0, 'C', false, 0, 235, 219, true, 0);
                    $pdf->MultiCell(60, 5, "TEL：" . TEL . "　FAX：" . FAX, 0, 'C', false, 0, 235, 224, true, 0);
                    $pdf->setFontSize(10);
                    $pdf->MultiCell(60, 5, COMPANY, 0, 'C', false, 0, 235, 229, true, 0);
                    $pdf->MultiCell(60, 5, "登録番号：" . REGISTER_NO, 0, 'C', false, 0, 235, 234, true, 0);

                    $pdf->setFontSize(8);
                    $pdf->MultiCell(95, 5, "尚、代金引換の場合は運送会社の控えが領収書としてお使い頂けます。", 0, 'C', false, 0, 218, 241, true, 0);
                }
            }
            $pdf->SetFont('kozgopromedium', '', 9);

            $product_nm = $data[$i]["product_nm"] ?? $data[$i]["product_nm_abrv"];
            $product_nm_abrv = $data[$i]["product_nm_abrv"];
            $tax_mark = "";
            if (in_array($data[$i]["tax_kbn"], REDUCE_TAX_RATE)) {
                $tax_mark = TAX_MARK;
            };

            $pdf->SetTextColor(0, 0, 0);

            //order table
            $pdf->SetFont('kozgopromedium', '', 10);
            //$pdf->SetFont('msmincho', '', 10);
            $pdf->setCellPaddings(null, null, null);
            if ($data[$i]["product_disp_kbn"] == "1" && $data[$i]["haiban_kbn"] == "0") {
                $pdf->MultiCell(90, 10, $data[$i]["product_cd"] . " " . $data[$i]["product_nm"], 0, "L", false, 0, 10, $order_y, true, 0, false, true, 10, "M");
                $pdf->setCellPaddings(null, null, 2);
                // $pdf->MultiCell(25, 10, "¥" . number_format($i * 100), 0, "R", false, 0, 100, $order_y, true, 0, false, true, 10, "M");
                $sale_tani = $data[$i]["tani"];
                if ($sale_tani == "なし") {
                    $sale_tani = "";
                };
                $pdf->MultiCell(30, 10, $sale_tani, 0, "R", false, 0, 125, $order_y, true, 0, false, true, 10, "M");
                // $pdf->MultiCell(45, 10, "¥" . number_format(($i * 100) * ($i * 10)), 01, "R", false, 0, 155, $order_y, true, 0, false, true, 10, "M");

                $order_y += 10;
            }

            //nouhin table
            //$pdf->SetFont('kozgopromedium', '', 9);
            $pdf->SetFont('msmincho', '', 9);
            $pdf->setCellPaddings(null, null, null);
            $pdf->MultiCell(105, 5, $data[$i]["product_cd"] . " " . $product_nm, 0, "L", false, 0, 223, $nouhin_y);
            $pdf->Text(315, $nouhin_y, $tax_mark);
            $pdf->setCellPaddings(null, null, 2);
            $pdf->MultiCell(25, 5, "¥" . number_format($data[$i]["sale_price"]), 0, "R", false, 0, 320, $nouhin_y);
            $sale_tani = $data[$i]["tani"];
            if ($sale_tani == "なし") {
                $sale_tani = "";
            };
            $qty = $data[$i]["qty"];
            if (strpos($qty, ".")) {
                $qty = number_format($data[$i]["qty"], 1);
            }
            $pdf->MultiCell(30, 5, $qty . $sale_tani, 0, "R", false, 0, 345, $nouhin_y);
            $pdf->MultiCell(35, 5, "¥" . number_format($data[$i]["row_total"]), 1, "R", false, 0, 375, $nouhin_y);

            $nouhin_y += 5;

            $pdf->SetFont('kozgopromedium', '', 8);
            // if ($data[0]["sale_kbn"] === "3") {
            //$pdf->SetTextColor(255, 255, 255);
            //$pdf->SetTextColor(255, 0, 0);
            //    $pdf->SetTextColor(0, 0, 0);
            // } else {
            $pdf->SetTextColor(0, 0, 0);
            // }
            //denpyo table
            $pdf->setCellPaddings(null, null, null);
            $pdf->MultiCell(35, 5, $data[$i]["product_cd"] . " " . $product_nm_abrv, 0, "L", false, 0, 320, $denpyo_y);
            $pdf->Text(350, $denpyo_y, $tax_mark);
            $pdf->setCellPaddings(null, null, 2);
            $pdf->MultiCell(16, 5, "¥" . number_format($data[$i]["sale_price"]), 0, "R", false, 0, 355, $denpyo_y);
            $sale_tani = $data[$i]["tani"];
            if ($sale_tani == "なし") {
                $sale_tani = "";
            };
            $qty = $data[$i]["qty"];
            if (strpos($qty, ".")) {
                $qty = number_format($data[$i]["qty"], 1);
            }
            $pdf->MultiCell(19, 5, $qty . $sale_tani, 0, "R", false, 0, 371, $denpyo_y);
            $pdf->MultiCell(20, 5, "¥" . number_format($data[$i]["row_total"]), 0, "R", false, 0, 389, $denpyo_y);

            $denpyo_y += 5;
        }
        $pdf->Output($fname, "F"); //F = saves to folder, I = displays in browser
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * 注文書
 */
function order()
{
    $pdf = new orderPDF();

    $pdf->SetCreator("株式会社〇〇〇〇");
    $pdf->SetAuthor("株式会社〇〇〇〇");
    $pdf->SetTitle('注文書');
    $pdf->SetSubject('注文書');
    $pdf->SetHeaderMargin(15);
    $pdf->setFooterMargin(15);
    $pdf->setAutoPageBreak(false);

    $pdf->setFillColor(204, 204, 204);


    for ($i = 0; $i < 10; $i++) {
        //$y++;
        if ($i % 15 == 0) {
            $pdf->AddPage();
            $y = 70;
            $pdf->setTopMargin(25);
            $pdf->SetFont('kozgopromedium', '', 10);

            $pdf->Cell(90, 5, "屋号・お名前", "T", 0, "L", false, "", 0, false, "T", "T");
            $pdf->Cell(100, 5, "不在時連絡先（オプション）携帯番号など", "T", 1, "R", false, "", 0, false, "T", "T");

            $pdf->Cell(0, 5, "　〇〇　様", 0, 1, "L");

            $pdf->Cell(90, 5, "お電話", "T", 0, "L", false, "", 0, false, "T", "T");
            $pdf->Cell(100, 5, "FAX（オプション）", "T", 1, "R", false, "", 0, false, "T", "T");

            $pdf->Cell(0, 5, "000-000-000", 0, 1, "L");

            $pdf->Cell(190, 5, "お住所　（変更であればお書きください。）", "T", 1, "L", false, "", 0, false, "T", "T");
            $pdf->Cell(0, 5, "　〒", 0, 1, "L");
            $pdf->Cell(120, 6, "", "B", 0, "L");
            $pdf->Cell(70, 5, "ご注文は数量だけの記入でも承ります。", 0, 1, "C");
            $pdf->Cell(120, 6, "", 0, 0, "L");
            $pdf->Cell(70, 5, "↓（合計はこちらで計算します。）", 0, 1, "C");

            $pdf->Cell(100, 5, "商品名", "LBT", 0, "C", true);
            $pdf->Cell(20, 5, "単価", "LBT", 0, "C", true);
            $pdf->Cell(30, 5, "数量", "LBT", 0, "C", true);
            $pdf->Cell(40, 5, "合計価格", 1, 1, "C", true);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->Cell(100, 10, "", "LBT", 0);
            $pdf->Cell(20, 10, "", "LBT", 0);
            $pdf->Cell(30, 10, "", "LBT", 0);
            $pdf->Cell(40, 10, "", 1, 1);

            $pdf->SetFont('kozgopromedium', '', 8);
            $pdf->Cell(100, 5, "", "", 0);
            $pdf->Cell(20, 15, "送料", "LB", 0, "C");

            $pdf->Cell(30, 5, "□無料", "LB", 0, "L");
            $pdf->Cell(40, 5, "※税込10800円以上の注文", "LBR", 1, "L");

            $pdf->write1DBarcode('1111111111', 'CODABAR', 35, "", 50, 8, 0.4);

            $pdf->Cell(120, 5, "", "", 0);
            $pdf->Cell(30, 5, "□￥500", "LB", 0, "L");
            $pdf->Cell(40, 5, "※5400円以上の注文", "LBR", 1, "L");

            $pdf->Cell(120, 5, "", "", 0);
            $pdf->Cell(30, 5, "□￥1,000", "LB", 0, "L");
            $pdf->Cell(40, 5, "※5400円未満の注文", "LBR", 1, "L");

            $pdf->Cell(120, 5, "", "", 0);
            $pdf->Cell(30, 5, "合計", "LB", 0, "L");
            $pdf->Cell(40, 5, "", "LBR", 1, "L");
        };
        $pdf->SetFont('kozgopromedium', '', 11);
        $pdf->MultiCell(100, 10, "商品テスト0" . $i, 0, "L", false, 0, 10, $y);
        $pdf->MultiCell(20, 10, "¥" . number_format($i * 100), 0, "R", false, 0, 110, $y);
        $pdf->MultiCell(30, 10, number_format($i * 10), 0, "R", false, 0, 130, $y);
        $pdf->MultiCell(40, 10, "¥" . number_format(($i * 10) * ($i * 100)), 0, "R", false, 0, 160, $y);

        $y += 10;
        // $pdf->Cell(100,10,"商品テスト0".$i,"LBT",0,"C");
        // $pdf->Cell(20,10,"¥".number_format($i*100),"LBT",0,"R");
        // $pdf->Cell(30,10,number_format($i*10),"LBT",0,"R");
        // $pdf->Cell(40,10,"¥".number_format(($i*10)*($i*100)),1,1,"R");

        // if($y%15 == 0 || $i == 59){
        //     $pdf->SetFont('kozgopromedium', '', 8);
        //     $pdf->Cell(100,5,"","",0);
        //     $pdf->Cell(20,15,"送料","LB",0,"C");

        //     $pdf->Cell(30,5,"□無料","LB",0,"L");
        //     $pdf->Cell(40,5,"※税込10800円以上の注文","LBR",1,"L");

        //     $pdf->write1DBarcode('1111111111', 'CODABAR', 35, "", 50, 8, 0.4);

        //     $pdf->Cell(120,5,"","",0);
        //     $pdf->Cell(30,5,"□￥500","LB",0,"L");
        //     $pdf->Cell(40,5,"※5400円以上の注文","LBR",1,"L");

        //     $pdf->Cell(120,5,"","",0);
        //     $pdf->Cell(30,5,"□￥1,000","LB",0,"L");
        //     $pdf->Cell(40,5,"※5400円未満の注文","LBR",1,"L");


        // }
    }

    $pdf->Output("order.pdf", "I"); //F = saves to folder, I = displays in browser
}

/**
 * 売上伝票
 */
function A4Denpyo($fname, $data)
{
    $cnt = count($data);

    $pdf = new nouhinPDF();

    $pdf->SetCreator("株式会社〇〇〇〇");
    $pdf->SetAuthor("株式会社〇〇〇〇");
    $pdf->SetTitle('売上伝票');
    $pdf->SetSubject('売上伝票');
    $pdf->SetHeaderMargin(15);
    $pdf->setFooterMargin(15);
    $pdf->setAutoPageBreak(false);

    $pdf->setFillColor(204, 204, 204);
    $pdf->SetFont('kozgopromedium', '', 9);
    for ($i = 0; $i < $cnt; $i++) {

        if ($i % 40 == 0) {
            $pdf->setTotalQty($data[$i]["total_qty"]);
            $pdf->setTotalCost($data[$i]["total_cost"]);
            $pdf->setTax10($data[$i]["tax_10"]);
            $pdf->setTax8($data[$i]["tax_8"]);
            $pdf->setGrandTotal($data[$i]["grand_total"]);
            $pdf->setOkurisakiNm($data[$i]["tokuisaki_nm"]);
            $pdf->setOrderNo($data[$i]["order_no"]);

            $y = 60;
            $pdf->AddPage();
        };

        $pdf->setCellPaddings(null, null, 0);
        $pdf->MultiCell(20, 5, $data[$i]["product_cd"], 0, 'C', false, 0, 10, $y);
        $pdf->Cell(80, 5, $data[$i]["product_nm"], "LB", 0, "L", false);

        $pdf->setCellPaddings(null, null, 2);
        $pdf->Cell(25, 5,  number_format($data[$i]["qty"], 1), "LB", 0, "R", false);
        $pdf->Cell(30, 5, "¥" . number_format($data[$i]["sale_price"]), "LB", 0, "R", false);
        $pdf->Cell(35, 5, "¥" . number_format($data[$i]["row_cost"]), "LBR", 1, "R", false);

        $y = $y + 5;
    }
    $pdf->Output($fname, "F"); //F = saves to folder, I = displays in browser
}

/**
 * 納品控え
 * - NOT USING
 */
function sales_hikae()
{
    $pdf = new denpyoPDF("P", "mm", "A5");

    $pdf->SetCreator("株式会社〇〇〇〇");
    $pdf->SetAuthor("株式会社〇〇〇〇");
    $pdf->SetTitle('売上伝票（控え）');
    $pdf->SetSubject('売上伝票（控え）');
    $pdf->SetHeaderMargin(15);
    $pdf->setFooterMargin(15);
    $pdf->setAutoPageBreak(false);

    $total = 9801000;
    $tax = $total * 0.08;

    $pdf->setFillColor(204, 204, 204);
    $y = 0;
    for ($i = 0; $i < 10; $i++) {
        $y++;
        if ($i % 20 == 0) {
            $pdf->AddPage();

            $pdf->SetFont('kozgopromedium', '', 10);

            $pdf->MultiCell(60, 5, "〇〇　様", 0, "J", false, 0, 10, 20);
            $pdf->MultiCell(60, 5, "〒000-0000", 0, "J", false, 0, 10, 25);
            $pdf->MultiCell(60, 5, "〇〇市〇〇区〇〇町", 0, "J", false, 0, 10, 30);
            $pdf->MultiCell(60, 5, "1-10-11", 0, "J", false, 0, 10, 35);
            $pdf->MultiCell(60, 5, "TEL 000-000-000", 0, "L", false, 0, 10, 40);

            $pdf->SetFont('kozgopromedium', '', 10);

            $pdf->MultiCell(0, 5, "受注番号", "T", "R", false, 0, 100, 10);
            $pdf->MultiCell(0, 5, "00000", 0, "R", false, 0, 100, 15);
            $pdf->MultiCell(0, 5, "受注日", "T", "R", false, 0, 100, 20);
            $pdf->MultiCell(0, 5, date("Y年m月d日"), 0, "R", false, 0, 100, 25);
            $pdf->MultiCell(0, 5, "印字時間", "T", "R", false, 0, 100, 30);
            $pdf->MultiCell(0, 5, date("Y/m/d H:i"), "B", "R", false, 0, 100, 35);

            $pdf->MultiCell(60, 5, "〒000-0000", 0, "J", false, 0, 10, 25);
            $pdf->MultiCell(60, 5, "〇〇市〇〇区〇〇町", 0, "J", false, 0, 10, 30);
            $pdf->MultiCell(60, 5, "1-10-11", 0, "J", false, 0, 10, 35);
            $pdf->MultiCell(60, 5, "TEL 000-000-000", 0, "L", false, 1, 10, 40);

            $pdf->setCellMargins(null, 4);
            $pdf->cell(60, 5, "商品名（略）", "LTB", 0, "C", true);
            $pdf->cell(30, 5, "数量", "LTB", 0, "C", true);
            $pdf->cell(40, 5, "金額", 1, 1, "C", true);

            $pdf->setCellMargins(null, 0);
        };

        $pdf->cell(60, 5, "商品" . $i, "LB", 0, "C");
        $pdf->cell(30, 5, number_format($i * 1000), "LB", 0, "R");
        $pdf->cell(40, 5, "¥" . number_format($i * 100), "LBR", 1, "R");

        if ($y % 20 == 0 || $i == 9) {
            $pdf->cell(140, 5, "", 0, 1, "C");

            $pdf->Cell(60, 5, "", "LTR", 0, "C");
            $pdf->Cell(30, 5, "合計金額", "TBR", 0, "C", true);
            $pdf->Cell(40, 5, "¥" . number_format($total), "TBR", 1, "R");

            $pdf->Cell(60, 5, "", "LR", 0, "C");
            $pdf->Cell(30, 5, "消費税", "BR", 0, "C", true);
            $pdf->Cell(40, 5, "¥" . number_format($tax), "BR", 1, "R");

            $pdf->Cell(60, 5, "", "LBR", 0, "C");
            $pdf->Cell(30, 5, "合計締高", "BR", 0, "C", true);
            $pdf->Cell(40, 5, "¥" . number_format($total + $tax), "BR", 1, "R");

            $pdf->cell(140, 4, "", 0, 1, "C");
            $pdf->write1DBarcode('1111111111', 'CODABAR', 50, null, 50, 8, 0.4);
            $pdf->cell(130, 20, "1111111111", 0, 1, "C");
        }
    }

    $pdf->Output("uriage_denpyo_hikae.pdf", "I"); //F = saves to folder, I = displays in browser
}

/**
 * 領収書
 */
function reciept($fname, $data)
{
    try {
        $pdf = new TCPDF('P', 'mm', 'A5');

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('領収書');
        $pdf->SetSubject('領収書');
        $pdf->SetHeaderMargin(15);
        $pdf->setFooterMargin(15);
        $pdf->setAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();
        $pdf->SetFont('msmincho', '', 20);

        $pdf->MultiCell(70, 10, "領収書", "B", "C", false, 0, 39.25, null, true, 4);

        $pdf->SetFont('msmincho', '', 10);

        $pdf->MultiCell(40, 5, date("Y/m/d", strtotime($data[0]["sale_dt"])), 0, "L", false, 0, 100, 22);

        $pdf->SetFont('msmincho', '', 12);

        $pdf->MultiCell(90, 6, $data[0]["okurisaki_nm"] . "　様", 0, "L", false, 0, 30, 27, true, 0, false, true, 6, 'T', true);

        $pdf->SetFont('msmincho', '', 18);
        $pdf->MultiCell(90, 15, "￥" . number_format($data[0]["grand_total"]), 1, "C", false, 0, 29.25, 35, true, 0, false, true, 15, 'M');

        $pdf->SetFont('msmincho', '', 10);
        $pdf->MultiCell(90, 10, "上記の金額を正に領収いたしました。", 0, "C", false, 0, 29.25, 52);

        $pdf->MultiCell(15, 15, "", 1, "C", false, 0, 29.25, 66);

        $pdf->SetFont('msmincho', '', 8);
        $pdf->MultiCell(15, 5, "収　入", 0, "C", false, 0, 29.25, 68);
        $pdf->MultiCell(15, 5, "印　紙", 0, "C", false, 0, 29.25, 77);

        $pdf->MultiCell(15, 5, "取扱者印", 1, "C", false, 0, 103, 66);
        $pdf->MultiCell(15, 12, "", "LBR", "C", false, 0, 103, 71);

        //LOGO
        $pdf->Image('../images/logo_pdf.jpg', 50, 60, 48, 9, 'JPG', 'https://uskk.com/', '', true, 150, '', false, false, 0, false, false, false);

        //INFO
        $pdf->MultiCell(80, 5, ADDRESS, 0, "C", false, 0, 34.25, 72);
        $pdf->MultiCell(40, 5, "TEL : " . TEL, 0, "C", false, 0, 39.25, 76);

        $pdf->MultiCell(40, 5, "FAX : " . FAX, 0, "C", false, 0, 68.25, 76);
        $pdf->MultiCell(80, 5, COMPANY, 0, "C", false, 0, 34.25, 80);
        $pdf->MultiCell(80, 5, "登録番号：" . REGISTER_NO, 0, "C", false, 0, 34.25, 84);

        $pdf->MultiCell(100, 10, "尚、代金引換の場合は運送会社の控えが領収書としてお使い頂けます。", 0, "C", false, 0, 24.25, 90);
        $pdf->Output($fname, "F");
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * ヤマト送り状の代引
 */
function yamatoDaibiki($fname, $data)
{
    try {
        $kosu = $data[0]["kosu"];
        $barcode1 = $data[0]["inquire_no"];
        $barcode1_str = "A" . $data[0]["inquire_no"] . "A";
        $barcode2 = $data[0]["yamato_delivery_cd"];
        $barcode2_str = substr($data[0]["yamato_delivery_cd"], 1, 2) . "-" . substr($data[0]["yamato_delivery_cd"], 3, 2) . "-" . substr($data[0]["yamato_delivery_cd"], 5, 2);

        $dash = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2);
        $line = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0);
        /**
         * WIDTH
         * HEIGHT
         */
        $pageLayout = array(108, 229);

        $pdf = new TCPDF('P', 'mm', $pageLayout);

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('ヤマト送り状');
        $pdf->SetSubject('ヤマト送り状');
        $pdf->SetHeaderMargin(0);
        $pdf->setFooterMargin(0);
        $pdf->setAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        //for ($i = 0; $i < $kosu; $i++) {
        $pdf->AddPage();

        $pdf->SetFont('kozgopromedium', '', 9);

        /** TOP **/
        //電話
        $pdf->MultiCell(50, 5, "TEL. " . $data[0]["okurisaki_tel"], 0, 'L', false, 0, 10, 2);
        //郵便
        $pdf->MultiCell(50, 5, "〒" . $data[0]["okurisaki_zip"], 0, 'L', false, 0, 10, 6);
        //住所
        $pdf->SetFont('kozgopromedium', '', 8);
        $pdf->MultiCell(65, 5, $data[0]["okurisaki_adr_1"] . $data[0]["okurisaki_adr_2"] . $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 10, 10);
        //$pdf->MultiCell(50, 5, $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 10, 14);
        //得意先
        $pdf->SetFont('kozgopromedium', '', 9);
        $jikai = "";
        if ($data[0]["jikai_kbn_1"] != "なし") {
            $jikai .= $data[0]["jikai_kbn_1"];
        }
        if ($data[0]["jikai_kbn_2"] != "なし") {
            $jikai .= $data[0]["jikai_kbn_2"];
        }
        if ($data[0]["jikai_kbn_3"] != "なし") {
            $jikai .= $data[0]["jikai_kbn_3"];
        }
        $pdf->MultiCell(65, 5, $data[0]["okurisaki_nm"] . $jikai . "　様", 0, 'L', false, 0, 10, 21);

        //問い合わせ番号
        $pdf->SetFont('kozgopromedium', '', 10);
        $pdf->MultiCell(30, 5, substr($data[0]["inquire_no"], 0, 4) . "-" . substr($data[0]["inquire_no"], 4, 4) . "-" . substr($data[0]["inquire_no"], 8), 0, 'L', false, 0, 75, 0);
        //代金引替額
        $pdf->SetFont('kozgopromedium', '', 16);
        $pdf->MultiCell(25, 5, number_format($data[0]["grand_total"]), 0, 'R', false, 0, 75, 13);

        //消費税
        $pdf->SetFont('kozgopromedium', '', 7);
        $pdf->MultiCell(12, null, number_format(($data[0]["tax_8"] + $data[0]["tax_10"])), 0, 'R', false, 0, 86, 23);

        //〇〇〇〇の情報
        $pdf->SetFont('kozgopromedium', '', 9);
        $pdf->MultiCell(50, 5, "TEL. " . TEL, 0, 'L', false, 0, 10, 25);
        $pdf->MultiCell(50, 5, "〒" . ZIP, 0, 'L', false, 0, 10, 28);
        $pdf->MultiCell(50, 5, POST_ADDRESS, 0, 'L', false, 0, 10, 32);
        $pdf->MultiCell(50, 5, COMPANY, 0, 'L', false, 0, 10, 36);

        //受注番号
        $pdf->SetFont('kozgopromedium', '', 8);
        $pdf->MultiCell(50, 5, "受注No.　" . $data[0]["order_no"], 0, 'L', false, 0, 10, 44);

        //年
        $pdf->MultiCell(6, null, date("y", strtotime($data[0]["receive_dt"])), 0, 'R', false, 0, 38, 51);
        //月
        $pdf->MultiCell(6, null, date("m", strtotime($data[0]["receive_dt"])), 0, 'R', false, 0, 48, 51);
        //日
        $pdf->MultiCell(6, null, date("d", strtotime($data[0]["receive_dt"])), 0, 'R', false, 0, 57, 51);

        //BARCODE
        $pdf->SetFont('kozgopromedium', '', 20);
        $pdf->write1DBarcode($barcode2, 'CODABAR', 5, 63, 30, 10, 0.4);
        $pdf->MultiCell(50, null, $barcode2_str, 0, 'L', false, 0, 45, 63);

        /** MIDDLE **/

        $pdf->SetFont('kozgopromedium', '', 9);
        //TEL
        $pdf->MultiCell(50, null, "TEL. " . $data[0]["okurisaki_tel"], 0, 'L', false, 0, 10, 77);
        //ZIP
        $pdf->MultiCell(50, null, "〒" . $data[0]["okurisaki_zip"], 0, 'L', false, 0, 10, 81);
        //ADDRESS
        $pdf->SetFont('kozgopromedium', '', 7);
        $pdf->MultiCell(55, 5, $data[0]["okurisaki_adr_1"] . $data[0]["okurisaki_adr_2"] . $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 10, 85);
        // $pdf->MultiCell(50, null, $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 10, 90);
        //得意先
        $pdf->SetFont('kozgopromedium', '', 9);
        $jikai = "";
        if ($data[0]["jikai_kbn_1"] != "なし") {
            $jikai .= $data[0]["jikai_kbn_1"];
        }
        if ($data[0]["jikai_kbn_2"] != "なし") {
            $jikai .= $data[0]["jikai_kbn_2"];
        }
        if ($data[0]["jikai_kbn_3"] != "なし") {
            $jikai .= $data[0]["jikai_kbn_3"];
        }
        $pdf->MultiCell(65, null, $data[0]["okurisaki_nm"] . $jikai . "　様", 0, 'L', false, 0, 10, 95);

        //問い合わせ番号
        $pdf->MultiCell(30, 5, substr($data[0]["inquire_no"], 0, 4) . "-" . substr($data[0]["inquire_no"], 4, 4) . "-" . substr($data[0]["inquire_no"], 8), 0, 'L', false, 0, 75, 74);

        $pdf->SetFont('kozgopromedium', '', 6);
        //年
        $pdf->MultiCell(6, null, date('y', strtotime($data[0]["sale_dt"])), 0, 'R', false, 0, 79, 78);
        //月
        $pdf->MultiCell(6, null, date("m", strtotime($data[0]["sale_dt"])), 0, 'R', false, 0, 89, 78);
        //日
        $pdf->MultiCell(6, null, date("d", strtotime($data[0]["sale_dt"])), 0, 'R', false, 0, 98, 78);

        $pdf->SetFont('kozgopromedium', '', 9);
        //お届け予定
        $pdf->MultiCell(20, null, date('m月d日', strtotime($data[0]["receive_dt"])), 0, 'C', false, 0, 66, 87);
        //時間帯
        $pdf->MultiCell(20, null, $data[0]["yamato_kbn"], 0, 'C', false, 0, 86, 87);
        $pdf->SetFont('kozgopromedium', '', 6);
        //支払い
        $pdf->MultiCell(30, null, "現金 クレジット・デビット可", 0, 'C', false, 0, 76, 96);

        $pdf->SetFont('kozgopromedium', '', 14);
        //代金
        $pdf->MultiCell(30, null, number_format($data[0]["grand_total"]), 0, 'R', false, 0, 69, 105);

        //〇〇〇〇の情報
        $pdf->SetFont('kozgopromedium', '', 9);
        $pdf->MultiCell(50, 5, "TEL. " . TEL, 0, 'L', false, 0, 10, 101);
        $pdf->MultiCell(80, 5, "〒" . ZIP . " " . POST_ADDRESS, 0, 'L', false, 0, 10, 105);
        $pdf->MultiCell(50, 5, COMPANY, 0, 'L', false, 0, 10, 109);

        //個口
        $pdf->SetFont('kozgopromedium', '', 8);
        $pdf->MultiCell(20, 7, "", 1, 'L', false, 0, 52, 110);
        $pdf->MultiCell(20, null, 1 . "／" . $kosu, 0, 'C', false, 0, 52, 110);
        $pdf->SetFont('kozgopromedium', '', 6);
        $pdf->MultiCell(20, null, "個口", 0, 'R', false, 0, 52, 114);

        //商品
        //for loop
        $y = 114;
        $pCnt = 0;
        $pdf->SetFont('kozgopromedium', '', 8);
        $pdf->MultiCell(32, 30, "", 1, 'L', false, 0, 10, 114);
        $product = "";
        foreach ($data as &$obj) {
            //if ($pCnt == 7) break;
            //if ($obj["product_disp_kbn"] != "1") continue;
            $sale_tani = $obj["sale_tani"];
            if ($sale_tani == "なし") {
                $sale_tani = "";
            };
            $qty = $obj["qty"];
            if (strpos($qty, ".")) {
                $qty = number_format($obj["qty"], 1);
            }
            $product .= "□" . $obj["product_nm_abrv"] . "　" . $qty . $sale_tani . PHP_EOL;
            // $pdf->MultiCell(32, 5, "□" . $obj["product_nm_abrv"] . "　" . $qty . $sale_tani, 0, 'L', false, 0, 10, $y);
            //$y = $y - 3;
            $pCnt++;
        };
        $pdf->MultiCell(32, 30, $product, 0, 'L', false, 0, 10, $y, true, 0, false, true, 30, 'B', true);

        //サイズ
        $pdf->SetFont('kozgopromedium', '', 8);
        $pdf->MultiCell(10, null, "**", 0, 'C', false, 0, 28, 146);

        //発店コード
        $pdf->MultiCell(20, null, $data[0]["haten_cd"], 0, 'C', false, 0, 27, 151);

        //使用期限
        $pdf->MultiCell(30, null, date('Y年m月d日迄', strtotime(YAMATO_EXPIRE_DATE)), 0, 'L', false, 0, 15, 156);

        //印
        $pdf->SetFont('kozgopromedium', '', 7);
        $pdf->MultiCell(20, 20, "", 1, 'C', false, 0, 80, 123);
        $pdf->MultiCell(20, null, "受領印", 0, 'C', false, 0, 80, 123);
        $pdf->MultiCell(20, null, "印", 0, 'C', false, 0, 80, 132);
        $pdf->SetLineStyle($dash);
        $pdf->Circle(90, 134, 8, 0, 360);

        $pdf->SetLineStyle($line);

        //BARCODE
        $pdf->SetFont('kozgopromedium', '', 6);
        $pdf->write1DBarcode($barcode1, 'CODABAR', 50, 148, 50, 10, 0.4);
        $pdf->MultiCell(50, null, strtolower($barcode1_str), 0, 'C', false, 0, 50, 159);

        /** BOTTOM **/
        $pdf->SetFont('kozgopromedium', '', 9);
        //TEL
        $pdf->MultiCell(50, null, "TEL. " . $data[0]["okurisaki_tel"], 0, 'L', false, 0, 10, 166);
        //ZIP
        $pdf->MultiCell(50, null, "〒" . $data[0]["okurisaki_zip"], 0, 'L', false, 0, 10, 170);
        //ADDRESS
        $pdf->SetFont('kozgopromedium', '', 8);
        $pdf->MultiCell(65, null, $data[0]["okurisaki_adr_1"] . $data[0]["okurisaki_adr_2"] . $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 10, 174);
        //$pdf->MultiCell(50, null, $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 10, 178);
        //得意先
        $pdf->SetFont('kozgopromedium', '', 9);
        $jikai = "";
        if ($data[0]["jikai_kbn_1"] != "なし") {
            $jikai .= $data[0]["jikai_kbn_1"];
        }
        if ($data[0]["jikai_kbn_2"] != "なし") {
            $jikai .= $data[0]["jikai_kbn_2"];
        }
        if ($data[0]["jikai_kbn_3"] != "なし") {
            $jikai .= $data[0]["jikai_kbn_3"];
        }
        $pdf->MultiCell(65, null, $data[0]["okurisaki_nm"] . $jikai . "　様", 0, 'L', false, 0, 10, 182);

        //問い合わせ番号
        $pdf->MultiCell(30, 5, substr($data[0]["inquire_no"], 0, 4) . "-" . substr($data[0]["inquire_no"], 4, 4) . "-" . substr($data[0]["inquire_no"], 8), 0, 'L', false, 0, 76, 164);

        $pdf->SetFont('kozgopromedium', '', 6);
        //年
        $pdf->MultiCell(6, null, date('y', strtotime($data[0]["sale_dt"])), 0, 'R', false, 0, 82, 171);
        //月
        $pdf->MultiCell(6, null, date('m', strtotime($data[0]["sale_dt"])), 0, 'R', false, 0, 90, 171);
        //日
        $pdf->MultiCell(6, null, date('d', strtotime($data[0]["sale_dt"])), 0, 'R', false, 0, 98, 171);
        $pdf->SetFont('kozgopromedium', '', 9);
        //お届け予定
        $pdf->MultiCell(20, null, date('m月d日', strtotime($data[0]["receive_dt"])), 0, 'C', false, 0, 80, 178);
        //時間帯
        $pdf->MultiCell(20, null, $data[0]["yamato_kbn"], 0, 'C', false, 0, 80, 187);

        //〇〇〇〇の情報
        $pdf->SetFont('kozgopromedium', '', 9);
        $pdf->MultiCell(50, 5, "TEL. " . TEL, 0, 'L', false, 0, 10, 188);
        $pdf->MultiCell(80, 5, "〒" . ZIP, 0, 'L', false, 0, 10, 192);
        $pdf->MultiCell(80, 5, POST_ADDRESS, 0, 'L', false, 0, 10, 196);
        $pdf->MultiCell(50, 5, COMPANY, 0, 'L', false, 0, 10, 200);

        //個口
        $pdf->SetFont('kozgopromedium', '', 8);
        $pdf->MultiCell(18, 7, "", 1, 'L', false, 0, 56, 192);
        $pdf->MultiCell(18, null, 1 . "／" . $kosu, 0, 'C', false, 0, 56, 192);
        $pdf->SetFont('kozgopromedium', '', 6);
        $pdf->MultiCell(18, null, "個口", 0, 'R', false, 0, 56, 196);

        //発店コード
        $pdf->SetFont('kozgopromedium', '', 8);
        $pdf->MultiCell(20, null, $data[0]["haten_cd"], 0, 'L', false, 0, 85, 202);

        //問い合わせ番号
        $pdf->MultiCell(20, null, $data[0]["yamato_inquire_no"], 0, 'L', false, 0, 85, 205);

        //受注番号
        $pdf->SetFont('kozgopromedium', '', 9);
        $pdf->MultiCell(50, null, "受注No." . $data[0]["order_no"], 0, 'L', false, 0, 10, 217);

        //BARCODE
        $pdf->write1DBarcode($barcode1, 'CODABAR', 50, 210, 50, 10, 0.4);
        $pdf->SetFont('kozgopromedium', '', 6);
        $pdf->MultiCell(50, null, strtolower($barcode1_str), 0, 'C', false, 0, 50, 221);
        // }

        $pdf->Output($fname, "F");
    } catch (Exception $e) {
        throw $e;
        // var_dump($e);
    }
}

/**
 * ヤマト送り状の元払い
 */
function yamatoMotoBarai($fname, $data)
{
    try {

        $kosu = $data[0]["kosu"];
        $barcode1 = $data[0]["inquire_no"];
        $barcode1_str = "A" . $data[0]["inquire_no"] . "A";
        $barcode2 = $data[0]["yamato_delivery_cd"];
        $barcode2_str = substr($data[0]["yamato_delivery_cd"], 1, 2) . "-" . substr($data[0]["yamato_delivery_cd"], 3, 2) . "-" . substr($data[0]["yamato_delivery_cd"], 5, 2);

        $dash = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2);
        $line = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0);

        /**
         * WIDTH
         * HEIGHT
         */
        $pageLayout = array(108, 178);

        $pdf = new TCPDF('P', 'mm', $pageLayout);

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('ヤマト送り状');
        $pdf->SetSubject('ヤマト送り状');
        $pdf->SetHeaderMargin(0);
        $pdf->setFooterMargin(0);
        $pdf->setAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        for ($i = 0; $i < $kosu; $i++) {

            if ($i != 0) {
                $barcode1 = getMotoBaraiInquireNo();
                $barcode1_str = "A" . $barcode1 . "A";
            };

            $pdf->AddPage();

            //商品 Y position
            $y = 60;

            /** TOP **/

            //BARCODE
            $pdf->SetFont('kozgopromedium', '', 20);
            $pdf->write1DBarcode($barcode2, 'CODABAR', 13, 2, 30, 10, 0.4);
            $pdf->MultiCell(50, null, $barcode2_str, 0, 'L', false, 0, 55, 2);

            /** お届け先 **/
            $pdf->SetFont('kozgopromedium', '', 9);
            //TEL
            $pdf->MultiCell(50, null, "TEL. " . $data[0]["okurisaki_tel"], 0, 'L', false, 0, 13, 19);
            //ZIP
            $pdf->MultiCell(50, null, "〒" . $data[0]["okurisaki_zip"], 0, 'L', false, 0, 13, 23);
            //ADDRESS
            $pdf->MultiCell(65, 5, $data[0]["okurisaki_adr_1"] . $data[0]["okurisaki_adr_2"] . $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 13, 27);
            //$pdf->MultiCell(65, null, $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 13, 31);
            //NAME
            $jikai = "";
            if ($data[0]["jikai_kbn_1"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_1"];
            }
            if ($data[0]["jikai_kbn_2"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_2"];
            }
            if ($data[0]["jikai_kbn_3"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_3"];
            }
            $pdf->MultiCell(65, null, $data[0]["okurisaki_nm"] . $jikai . "　様", 0, 'L', false, 0, 13, 38);

            //問い合わせ番号
            $pdf->MultiCell(50, null, substr($barcode1, 0, 4) . "-" . substr($barcode1, 4, 4) . "-" . substr($barcode1, 8), 0, 'L', false, 0, 70, 16);

            //年
            $pdf->MultiCell(50, null, date('y', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 86, 19);
            //月
            $pdf->MultiCell(50, null, date('m', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 94, 19);
            //日
            $pdf->MultiCell(50, null, date('d', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 101, 19);

            //お届け先
            //月
            $pdf->MultiCell(50, null, date('m', strtotime($data[0]["receive_dt"])), 0, 'L', false, 0, 93, 34);
            //日
            $pdf->MultiCell(50, null, date('d', strtotime($data[0]["receive_dt"])), 0, 'L', false, 0, 99, 34);

            //時間帯
            $pdf->SetFontSize(8);
            $pdf->MultiCell(20, null, $data[0]["yamato_kbn"], 0, 'L', false, 0, 93, 53);

            $pdf->SetFontSize(9);
            /** ご依頼主 **/
            //TEL
            $pdf->MultiCell(50, null, "TEL. " . TEL, 0, 'L', false, 0, 13, 46);
            //ADDRESS
            $pdf->MultiCell(65, null, "〒" . ZIP . " " . POST_ADDRESS, 0, 'L', false, 0, 13, 50);
            //COMPANY NAME
            $pdf->MultiCell(50, null, COMPANY, 0, 'L', false, 0, 13, 54);

            //個口
            $pdf->SetFont('kozgopromedium', '', 8);
            $pdf->MultiCell(20, 7, "", 1, 'L', false, 0, 66, 52);
            $pdf->MultiCell(20, null, $i + 1 . "／" . $kosu, 0, 'C', false, 0, 66, 52);
            $pdf->SetFont('kozgopromedium', '', 6);
            $pdf->MultiCell(20, null, "個口", 0, 'R', false, 0, 66, 56);

            $pdf->SetFont('kozgopromedium', '', 8);

            //商品
            $pCnt = 0;
            $pdf->MultiCell(30, 28, "", 1, 'L', false, 0, 13, 60);
            $product = "";
            foreach ($data as &$obj) {
                //if ($pCnt == 7) break;
                //発行フラグ is 発行しない Go next
                //if ($obj["product_disp_kbn"] != "1") continue;
                $sale_tani = $obj["sale_tani"];
                if ($sale_tani == "なし") {
                    $sale_tani = "";
                }
                $qty = $obj["qty"];
                if (strpos($qty, ".")) {
                    $qty = number_format($obj["qty"], 1);
                }

                $product .= "□" . $obj["product_nm_abrv"] . "　" . $qty . $sale_tani . PHP_EOL;
                // $pdf->MultiCell(32, 5, "□" . $obj["product_nm_abrv"] . "　" . $qty . $sale_tani, 0, 'L', false, 0, 13, $y);
                //$y = $y - 3;
                $pCnt++;
            };

            $pdf->MultiCell(32, 5, $product, 0, 'L', false, 0, 13, $y, true, 0, false, true, 28, 'B', true);
            // for ($i = 0; $i < 7; $i++) {
            //     $pdf->MultiCell(32, 5, $data[$i]["product_nm_abrv"] . "　" . $data[$i]["qty"] . $data[$i]["sale_tani"], 0, 'L', false, 0, 10, $y);
            //     $y = $y - 4;
            // };

            $pdf->SetFont('kozgopromedium', '', 8);
            //SIZE
            $pdf->MultiCell(20, null, "**", 0, 'L', false, 0, 13, 93);

            //発店コード
            $pdf->MultiCell(20, null, $data[0]["haten_cd"], 0, 'L', false, 0, 31, 93);

            //使用期限
            $pdf->MultiCell(30, null, date('Y年m月d日迄', strtotime(YAMATO_EXPIRE_DATE)), 0, 'L', false, 0, 15, 97);

            //印
            $pdf->SetFont('kozgopromedium', '', 7);
            $pdf->MultiCell(20, 20, "", 1, 'C', false, 0, 85, 67);
            $pdf->MultiCell(20, null, "受領印", 0, 'C', false, 0, 85, 67);
            $pdf->MultiCell(20, null, "印", 0, 'C', false, 0, 85, 77);
            $pdf->SetLineStyle($dash);
            $pdf->Circle(95, 78, 8, 0, 360);

            $pdf->SetLineStyle($line);

            //BARCODE
            $pdf->write1DBarcode($barcode1, 'CODABAR', 52, 90, 40, 10, 0.4);
            $pdf->MultiCell(35, null, strtolower($barcode1_str), 0, 'C', false, 0, 52, 100);

            /** BOTTOM **/
            $pdf->SetFont('kozgopromedium', '', 9);
            //TEL
            $pdf->MultiCell(50, null, "TEL. " . $data[0]["okurisaki_tel"], 0, 'L', false, 0, 13, 110);
            //ZIP
            $pdf->MultiCell(50, null, "〒" . $data[0]["okurisaki_zip"], 0, 'L', false, 0, 13, 114);
            //ADDRESS
            $pdf->MultiCell(65, 5, $data[0]["okurisaki_adr_1"] . $data[0]["okurisaki_adr_2"] . $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 13, 118);
            //$pdf->MultiCell(65, null, $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 13, 122);
            //NAME
            $jikai = "";
            if ($data[0]["jikai_kbn_1"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_1"];
            }
            if ($data[0]["jikai_kbn_2"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_2"];
            }
            if ($data[0]["jikai_kbn_3"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_3"];
            }
            $pdf->MultiCell(65, null, $data[0]["okurisaki_nm"] . $jikai . "　様", 0, 'L', false, 0, 13, 130);

            //問い合わせ番号
            $pdf->MultiCell(50, null, substr($barcode1, 0, 4) . "-" . substr($barcode1, 4, 4) . "-" . substr($barcode1, 8), 0, 'L', false, 0, 70, 105);

            //年
            $pdf->MultiCell(50, null, date('y', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 86, 109);
            //月
            $pdf->MultiCell(50, null, date('m', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 94, 109);
            //日
            $pdf->MultiCell(50, null, date('d', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 101, 109);

            //お届け予定
            //月
            $pdf->MultiCell(50, null, date('m', strtotime($data[0]["receive_dt"])), 0, 'L', false, 0, 93, 124);
            //日
            $pdf->MultiCell(50, null, date('d', strtotime($data[0]["receive_dt"])), 0, 'L', false, 0, 99, 124);

            //時間帯
            $pdf->SetFontSize(8);
            $pdf->MultiCell(20, null, $data[0]["yamato_kbn"], 0, 'L', false, 0, 93, 141);

            $pdf->SetFontSize(9);
            /** ご依頼主 **/
            //TEL
            $pdf->MultiCell(50, null, "TEL. " . TEL, 0, 'L', false, 0, 13, 137);
            //ADDRESS
            $pdf->MultiCell(65, null, "〒" . ZIP . " " . POST_ADDRESS, 0, 'L', false, 0, 13, 141);
            //COMPANY NAME
            $pdf->MultiCell(50, null, COMPANY, 0, 'L', false, 0, 13, 145);

            //個口
            $pdf->SetFont('kozgopromedium', '', 8);
            $pdf->MultiCell(20, 7, "", 1, 'L', false, 0, 66, 142);
            $pdf->MultiCell(20, null, $i + 1 . "／" . $kosu, 0, 'C', false, 0, 66, 142);
            $pdf->SetFont('kozgopromedium', '', 6);
            $pdf->MultiCell(20, null, "個口", 0, 'R', false, 0, 66, 146);

            //ORDER NO
            $pdf->SetFont('kozgopromedium', '', 9);
            $pdf->MultiCell(33, null, "受注No." . $data[0]["order_no"], 0, 'L', false, 0, 12, 158);

            //発店コード
            $pdf->SetFont('kozgopromedium', '', 7);
            $pdf->MultiCell(20, null, $data[0]["haten_cd"], 0, 'L', false, 0, 18, 165);

            //問い合わせ番号
            $pdf->MultiCell(20, null, $data[0]["yamato_inquire_no"], 0, 'L', false, 0, 18, 169);

            //BARCODE
            $pdf->write1DBarcode($barcode1, 'CODABAR', 50, 160, 40, 10, 0.4);
            $pdf->MultiCell(35, null, strtolower($barcode1_str), 0, 'C', false, 0, 50, 170);
        }


        $pdf->Output($fname, "F");
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * ヤマト送り状の元払い
 */
function yamatoDaibikiMotoBarai($fname, $data)
{
    try {

        $kosu = $data[0]["kosu"];
        // $barcode1 = getMotoBaraiInquireNo() ?? $data[0]["inquire_no"];
        // $barcode1_str = "A" . $barcode1 . "A";
        $barcode2 = $data[0]["yamato_delivery_cd"];
        $barcode2_str = substr($data[0]["yamato_delivery_cd"], 1, 2) . "-" . substr($data[0]["yamato_delivery_cd"], 3, 2) . "-" . substr($data[0]["yamato_delivery_cd"], 5, 2);

        $dash = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2);
        $line = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0);

        /**
         * WIDTH
         * HEIGHT
         */
        $pageLayout = array(108, 178);

        $pdf = new TCPDF('P', 'mm', $pageLayout);

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('ヤマト送り状');
        $pdf->SetSubject('ヤマト送り状');
        $pdf->SetHeaderMargin(0);
        $pdf->setFooterMargin(0);
        $pdf->setAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        for ($i = 1; $i < $kosu; $i++) {

            $barcode1 = getMotoBaraiInquireNo() ?? $data[0]["inquire_no"];
            $barcode1_str = "A" . $barcode1 . "A";

            $pdf->AddPage();

            //商品 Y position
            $y = 60;

            /** TOP **/

            //BARCODE
            $pdf->SetFont('kozgopromedium', '', 20);
            $pdf->write1DBarcode($barcode2, 'CODABAR', 13, 2, 30, 10, 0.4);
            $pdf->MultiCell(50, null, $barcode2_str, 0, 'L', false, 0, 55, 2);

            /** お届け先 **/
            $pdf->SetFont('kozgopromedium', '', 9);
            //TEL
            $pdf->MultiCell(50, null, "TEL. " . $data[0]["okurisaki_tel"], 0, 'L', false, 0, 13, 19);
            //ZIP
            $pdf->MultiCell(50, null, "〒" . $data[0]["okurisaki_zip"], 0, 'L', false, 0, 13, 23);
            //ADDRESS
            $pdf->SetFont('kozgopromedium', '', 8);
            $pdf->MultiCell(65, 5, $data[0]["okurisaki_adr_1"] . $data[0]["okurisaki_adr_2"] . $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 13, 27);
            //$pdf->MultiCell(65, null, $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 13, 31);
            //NAME
            $jikai = "";
            if ($data[0]["jikai_kbn_1"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_1"];
            }
            if ($data[0]["jikai_kbn_2"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_2"];
            }
            if ($data[0]["jikai_kbn_3"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_3"];
            }
            $pdf->MultiCell(65, null, $data[0]["okurisaki_nm"] . $jikai . "　様", 0, 'L', false, 0, 13, 38);

            //問い合わせ番号
            $pdf->MultiCell(50, null, substr($barcode1, 0, 4) . "-" . substr($barcode1, 4, 4) . "-" . substr($barcode1, 8), 0, 'L', false, 0, 70, 16);

            //年
            $pdf->MultiCell(50, null, date('y', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 86, 19);
            //月
            $pdf->MultiCell(50, null, date('m', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 94, 19);
            //日
            $pdf->MultiCell(50, null, date('d', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 101, 19);

            //お届け先
            //月
            $pdf->MultiCell(50, null, date('m', strtotime($data[0]["receive_dt"])), 0, 'L', false, 0, 93, 34);
            //日
            $pdf->MultiCell(50, null, date('d', strtotime($data[0]["receive_dt"])), 0, 'L', false, 0, 99, 34);

            //時間帯
            $pdf->SetFontSize(8);
            $pdf->MultiCell(20, null, $data[0]["yamato_kbn"], 0, 'L', false, 0, 93, 53);

            $pdf->SetFontSize(9);
            /** ご依頼主 **/
            //TEL
            $pdf->MultiCell(50, null, "TEL. " . TEL, 0, 'L', false, 0, 13, 46);
            //ADDRESS
            $pdf->MultiCell(65, null, "〒" . ZIP . " " . POST_ADDRESS, 0, 'L', false, 0, 13, 50);
            //COMPANY NAME
            $pdf->MultiCell(50, null, COMPANY, 0, 'L', false, 0, 13, 54);

            //個口
            $pdf->SetFont('kozgopromedium', '', 8);
            $pdf->MultiCell(20, 7, "", 1, 'L', false, 0, 66, 52);
            $pdf->MultiCell(20, null, $i + 1 . "／" . $kosu, 0, 'C', false, 0, 66, 52);
            $pdf->SetFont('kozgopromedium', '', 6);
            $pdf->MultiCell(20, null, "個口", 0, 'R', false, 0, 66, 56);

            $pdf->SetFont('kozgopromedium', '', 8);

            //商品
            $pCnt = 0;
            $pdf->MultiCell(30, 28, "", 1, 'L', false, 0, 13, 60);
            $product = "";
            foreach ($data as &$obj) {
                //if ($pCnt == 7) break;
                //発行フラグ is 発行しない Go next
                //if ($obj["product_disp_kbn"] != "1") continue;
                $sale_tani = $obj["sale_tani"];
                if ($sale_tani == "なし") {
                    $sale_tani = "";
                }
                $qty = $obj["qty"];
                if (strpos($qty, ".")) {
                    $qty = number_format($obj["qty"], 1);
                }
                $product .= "□" . $obj["product_nm_abrv"] . "　" . $qty . $sale_tani . PHP_EOL;
                //$pdf->MultiCell(32, 5, "□" . $obj["product_nm_abrv"] . "　" . $qty . $sale_tani, 0, 'L', false, 0, 13, $y);
                //$y = $y - 3;
                $pCnt++;
            };
            $pdf->MultiCell(30, 28, $product, 0, 'L', false, 0, 13, $y, true, 0, false, true, 28, 'B', true);
            // for ($i = 0; $i < 7; $i++) {
            //     $pdf->MultiCell(32, 5, $data[$i]["product_nm_abrv"] . "　" . $data[$i]["qty"] . $data[$i]["sale_tani"], 0, 'L', false, 0, 10, $y);
            //     $y = $y - 4;
            // };

            $pdf->SetFont('kozgopromedium', '', 8);
            //SIZE
            $pdf->MultiCell(20, null, "**", 0, 'L', false, 0, 13, 93);

            //発店コード
            $pdf->MultiCell(20, null, $data[0]["haten_cd"], 0, 'L', false, 0, 31, 93);

            //使用期限
            $pdf->MultiCell(30, null, date('Y年m月d日迄', strtotime(YAMATO_EXPIRE_DATE)), 0, 'L', false, 0, 15, 97);

            //印
            $pdf->SetFont('kozgopromedium', '', 7);
            $pdf->MultiCell(20, 20, "", 1, 'C', false, 0, 85, 67);
            $pdf->MultiCell(20, null, "受領印", 0, 'C', false, 0, 85, 67);
            $pdf->MultiCell(20, null, "印", 0, 'C', false, 0, 85, 77);
            $pdf->SetLineStyle($dash);
            $pdf->Circle(95, 78, 8, 0, 360);

            $pdf->SetLineStyle($line);

            //BARCODE
            $pdf->write1DBarcode($barcode1, 'CODABAR', 52, 90, 40, 10, 0.4);
            $pdf->MultiCell(35, null, strtolower($barcode1_str), 0, 'C', false, 0, 52, 100);

            /** BOTTOM **/
            $pdf->SetFont('kozgopromedium', '', 9);
            //TEL
            $pdf->MultiCell(50, null, "TEL. " . $data[0]["okurisaki_tel"], 0, 'L', false, 0, 13, 110);
            //ZIP
            $pdf->MultiCell(50, null, "〒" . $data[0]["okurisaki_zip"], 0, 'L', false, 0, 13, 114);
            //ADDRESS
            $pdf->SetFont('kozgopromedium', '', 8);
            $pdf->MultiCell(65, 5, $data[0]["okurisaki_adr_1"] . $data[0]["okurisaki_adr_2"] . $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 13, 118);
            //$pdf->MultiCell(65, null, $data[0]["okurisaki_adr_3"], 0, 'L', false, 0, 13, 122);
            //NAME
            $jikai = "";
            if ($data[0]["jikai_kbn_1"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_1"];
            }
            if ($data[0]["jikai_kbn_2"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_2"];
            }
            if ($data[0]["jikai_kbn_3"] != "なし") {
                $jikai .= $data[0]["jikai_kbn_3"];
            }
            $pdf->MultiCell(65, null, $data[0]["okurisaki_nm"] . $jikai . "　様", 0, 'L', false, 0, 13, 130);

            //問い合わせ番号
            $pdf->MultiCell(50, null, substr($barcode1, 0, 4) . "-" . substr($barcode1, 4, 4) . "-" . substr($barcode1, 8), 0, 'L', false, 0, 70, 105);

            //年
            $pdf->MultiCell(50, null, date('y', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 86, 109);
            //月
            $pdf->MultiCell(50, null, date('m', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 94, 109);
            //日
            $pdf->MultiCell(50, null, date('d', strtotime($data[0]["sale_dt"])), 0, 'L', false, 0, 101, 109);

            //お届け予定
            //月
            $pdf->MultiCell(50, null, date('m', strtotime($data[0]["receive_dt"])), 0, 'L', false, 0, 93, 124);
            //日
            $pdf->MultiCell(50, null, date('d', strtotime($data[0]["receive_dt"])), 0, 'L', false, 0, 99, 124);

            //時間帯
            $pdf->SetFontSize(8);
            $pdf->MultiCell(20, null, $data[0]["yamato_kbn"], 0, 'L', false, 0, 93, 141);

            $pdf->SetFontSize(9);
            /** ご依頼主 **/
            //TEL
            $pdf->MultiCell(50, null, "TEL. " . TEL, 0, 'L', false, 0, 13, 137);
            //ADDRESS
            $pdf->MultiCell(65, null, "〒" . ZIP . " " . POST_ADDRESS, 0, 'L', false, 0, 13, 141);
            //COMPANY NAME
            $pdf->MultiCell(50, null, COMPANY, 0, 'L', false, 0, 13, 145);

            //個口
            $pdf->SetFont('kozgopromedium', '', 8);
            $pdf->MultiCell(20, 7, "", 1, 'L', false, 0, 66, 142);
            $pdf->MultiCell(20, null, $i + 1 . "／" . $kosu, 0, 'C', false, 0, 66, 142);
            $pdf->SetFont('kozgopromedium', '', 6);
            $pdf->MultiCell(20, null, "個口", 0, 'R', false, 0, 66, 146);

            //ORDER NO
            $pdf->SetFont('kozgopromedium', '', 9);
            $pdf->MultiCell(33, null, "受注No." . $data[0]["order_no"], 0, 'L', false, 0, 12, 158);

            //発店コード
            $pdf->SetFont('kozgopromedium', '', 7);
            $pdf->MultiCell(20, null, $data[0]["haten_cd"], 0, 'L', false, 0, 18, 165);

            //問い合わせ番号
            $pdf->MultiCell(20, null, $data[0]["yamato_inquire_no"], 0, 'L', false, 0, 18, 169);

            //BARCODE
            $pdf->write1DBarcode($barcode1, 'CODABAR', 50, 160, 40, 10, 0.4);
            $pdf->MultiCell(35, null, strtolower($barcode1_str), 0, 'C', false, 0, 50, 170);
        }

        $pdf->Output($fname, "F");
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * ヤマト送り状
 * - USED FOR 送り状発行画面
 */
function yamato_ship_invoice($fname, $data)
{
    try {
        /**
         * WIDTH
         * HEIGHT
         */
        $pageLayout = array(229, 115);

        $pdf = new TCPDF('L', 'mm', $pageLayout);

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('ヤマト送り状');
        $pdf->SetSubject('ヤマト送り状');
        $pdf->SetHeaderMargin(0);
        $pdf->setFooterMargin(0);
        $pdf->setAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

        $pdf->SetFont('kozgopromedium');

        //ZIP
        $zip_left = substr($data["tokuisaki_zip"], 0, 3);
        $zip_right = substr($data["tokuisaki_zip"], 3);

        $pdf->setFontSize(18);
        $pdf->MultiCell(19, 7.5, $zip_left, 0, 'J', false, 0, 45.5, 9, true, 4);
        $pdf->MultiCell(25.5, 7.5, $zip_right, 0, 'J', false, 0, 69, 9, true, 4);

        //TEL
        $pdf->MultiCell(51, 7.5, $data["tokuisaki_tel"], 0, 'J', false, 0, 45, 21, true, 4);

        //ADDRESS
        $pdf->setFontSize(11);
        $pdf->MultiCell(65, 5, $data["tokuisaki_adr"], 0, 'C', false, 0, 36, 33, true);
        $pdf->MultiCell(65, 5, $data["tokuisaki_adr_3"], 0, 'C', false, 0, 36, 41, true);

        //NAME
        $pdf->MultiCell(65, 5, $data["tokuisaki_nm"], 0, 'C', false, 0, 36, 57.5, true);

        $pdf->Output($fname, "F");
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * 佐川急便の送り状
 */
function sagawa_ship_invoice($fname, $data)
{
    try {
        /**
         * WIDTH
         * HEIGHT
         */
        $pageLayout = array(190, 102);

        $pdf = new TCPDF('L', 'mm', $pageLayout);

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('佐川急便送り状');
        $pdf->SetSubject('佐川急便送り状');
        $pdf->SetHeaderMargin(0);
        $pdf->setFooterMargin(0);
        $pdf->setAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

        $pdf->SetFont('kozgopromedium');

        //ZIP
        $zip_left = substr($data["tokuisaki_zip"], 0, 3);
        $zip_right = substr($data["tokuisaki_zip"], 3);

        $pdf->setFontSize(18);
        $pdf->MultiCell(22, 7.5, $zip_left, 0, 'J', false, 0, 33.5, 8.5, true, 4);
        $pdf->MultiCell(29.5, 7.5, $zip_right, 0, 'J', false, 0, 59, 8.5, true, 4);

        //ADDRESS
        $pdf->setFontSize(11);
        $pdf->MultiCell(66, 5, $data["tokuisaki_adr"], 0, 'C', false, 0, 24, 20.5, true);
        $pdf->MultiCell(66, 5, $data["tokuisaki_adr_3"], 0, 'C', false, 0, 24, 29, true);

        //NAME
        $pdf->MultiCell(66, 5, $data["tokuisaki_nm"], 0, 'C', false, 0, 24, 45, true);

        //TEL
        $pdf->MultiCell(61, 7.5, $data["tokuisaki_tel"], 0, 'J', false, 0, 30, 51, true, 4);

        $pdf->Output($fname, "F");
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * 荷物受渡書
 */
function statementOfDelivery($fname, $data, $shuka_dt)
{
    try {
        $cnt = count($data);
        $total_kensu = number_format(array_sum(array_column($data, "shuka_cnt")));
        $total_kosu = number_format(array_sum(array_column($data, "kosu_total")));
        $date_from =  $data[0]["shuka_dt"];
        $date_to = $data[$cnt - 1]["shuka_dt"];
        $ninushi_cd = $data[0]["ninushi_cd"];

        $pdf = new TCPDF('P', 'mm');

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('荷物受渡書');
        $pdf->SetSubject('荷物受渡書');
        $pdf->SetHeaderMargin(0);
        $pdf->setFooterMargin(0);
        $pdf->setAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();
        $pdf->SetFont('kozgopromedium');
        $pdf->setFontSize(10);
        $pdf->MultiCell(80, 10, "出荷日：" . $date_from . " ～ " . $date_to, 0, 'L', false, 0, 10, 20);

        $pdf->MultiCell(60, 10, "指定日：" . $shuka_dt, 0, 'R', false, 0, 140, 20);

        //TITLE
        $pdf->setFontSize(16);
        $pdf->MultiCell(70, 12, "荷物受渡書", 1, 'J', false, 0, 70, 40, true, 4, false, true, 12, 'M');

        $pdf->setFontSize(10);
        $pdf->MultiCell(40, 10, $ninushi_cd, 0, 'J', false, 0, 160, 35);
        $pdf->setFontSize(12);
        $pdf->MultiCell(60, 10, "出荷日：" . date("Y年m月d日", strtotime($date_to)), 0, 'L', false, 0, 10, 60);

        if ($data[0]["sender_cd"] == "1") {
            //ADDRESS
            $pdf->MultiCell(60, null, ADDRESS, 0, 'L', false, 0, 10, 80);
            //NAME
            $pdf->MultiCell(60, 10, POST_COMPANY, 0, 'L', false, 0, 10, 110);
            //TEL
            $pdf->MultiCell(null, 10, "TEL．" . TEL, 'B', 'L', false, 0, 10, 140);
        } else {
            $pdf->MultiCell(60, 10, "その他", 0, 'L', false, 0, 10, 110);
        }

        $pdf->MultiCell(10, 10, "様", 0, 'L', false, 0, 140, 120);

        $pdf->MultiCell(60, 10, "総出荷件数", 0, 'L', false, 0, 10, 160);
        $pdf->MultiCell(60, 10, $total_kensu . "　件", 0, 'L', false, 0, 80, 160);

        $pdf->MultiCell(60, 10, "総出荷個数", 0, 'L', false, 0, 10, 170);
        $pdf->MultiCell(60, 10, $total_kosu . "　個", 0, 'L', false, 0, 80, 170);

        $pdf->MultiCell(60, 10, "確かにお預かり致しました。", 0, 'L', false, 0, 10, 185);

        $pdf->setFontSize(10);
        $pdf->MultiCell(20, 25, "", 1, 'L', false, 0, 180, 160);
        $pdf->MultiCell(20, 5, "受領印", "B", 'C', false, 0, 180, 160);

        $pdf->MultiCell(null, 10, "", 'B', 'L', false, 0, 10, 200);

        $pdf->Output($fname, "F");
    } catch (Exception $e) {
        throw $e;
    }
}

class shukaReportPDF extends TCPDF
{
    //Page header
    // public function Header()
    // {
    //     $this->setFillColor(204, 204, 204);
    //     $this->SetFont('kozgopromedium', 'I', 22);
    //     // Title
    //     $this->Cell(0, 15, '商　　品　　台　　帳', 0, false, 'C', true, '', 0, false, 'M', 'M');
    //     $this->SetFont('kozgopromedium', '', 10);
    //     $this->text(260, 25, date("Y/m/d"));
    // }
    public function Footer()
    {
        $this->SetFont('kozgopromedium', '', 10);
        $this->MultiCell(null, 10, $this->getAliasNumPage() . ' ／ ' . $this->getAliasNbPages() . " ページ", 0, 'C', false, 0, 35, -10);
    }
};


function shukaReportData($fname, $data, $mesai, $shuka_dt)
{
    try {
        $cnt = count($data);
        $kosu = 0;
        $dt = $data[0]["shuka_dt"];
        // $mesai_cnt = count($mesai);

        $date_from =  $data[0]["shuka_dt"];
        $date_to = $data[$cnt - 1]["shuka_dt"];

        $pdf = new shukaReportPDF('P', 'mm');

        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('出荷日報');
        $pdf->SetSubject('出荷日報');
        $pdf->SetHeaderMargin(0);
        $pdf->setFooterMargin(15);
        $pdf->setAutoPageBreak(true, 15);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);

        $pdf->AddPage();
        $pdf->SetFont('kozgopromedium');

        $pdf->setFontSize(10);
        $pdf->MultiCell(80, 10, "出荷日：" . $date_from . " ～ " . $date_to, 0, 'L', false, 0, 10, 10);

        $pdf->MultiCell(60, 10, "指定日：" . $shuka_dt, 0, 'R', false, 0, 140, 10);

        // $pdf->MultiCell(60, 10, $pdf->getAliasNumPage() . ' ／ ' . $pdf->getAliasNbPages() . " ページ", 0, 'R', false, 0, 145, 18);

        //TITLE
        $pdf->setFontSize(18);
        $pdf->MultiCell(70, 12, "出荷日報", 0, 'C', false, 0, 70, 25, true, 0, false, true, 12, 'M');

        //出荷日
        $pdf->setFontSize(10);
        $pdf->MultiCell(60, 10, "◆出荷日：" . date("Y年m月d日", strtotime($dt)), 0, 'L', false, 0, 10, 45);
        //NAME
        if ($data[0]["sender_cd"] == 1) {
            $pdf->MultiCell(null, 10, "荷送人：　" . COMPANY, "B", 'L', false, 0, 10, 52);
        } else {
            $pdf->MultiCell(null, 10, "荷送人：　その他", "B", 'L', false, 0, 10, 52);
        }

        $pdf->MultiCell(60, 10, "ご依頼主", 0, 'L', false, 0, 10, 65);
        $pdf->MultiCell(60, 10, "お届け先名", 0, 'L', false, 0, 22, 72);
        $pdf->MultiCell(60, 10, "商品明細", 0, 'R', false, 1, 10, 78);
        $pdf->MultiCell(null, 6, "個数", "B", 'R', false, 1, 10, 78);
        $pdf->MultiCell(null, 2, "", 0, 'L', false, 1);

        for ($r = 0; $r < $cnt; $r++) {
            if ($dt != $data[$r]["shuka_dt"]) {
                $kosu = 0;
                $dt = $data[$r]["shuka_dt"];
                $pdf->AddPage();
                $pdf->setFontSize(10);
                $pdf->MultiCell(80, 10, "出荷日：" . $date_from . " ～ " . $date_to, 0, 'L', false, 0, 10, 10);

                $pdf->MultiCell(60, 10, "指定日：" . $shuka_dt, 0, 'R', false, 0, 140, 10);

                //$pdf->MultiCell(60, 10, $pdf->getAliasNumPage() . ' ／ ' . $pdf->getAliasNbPages() . " ページ", 0, 'R', false, 0, 145, 18);

                //TITLE
                $pdf->setFontSize(18);
                $pdf->MultiCell(70, 12, "出荷日報", 0, 'C', false, 0, 70, 25, true, 0, false, true, 12, 'M');

                //出荷日
                $pdf->setFontSize(10);
                $pdf->MultiCell(60, 10, "◆出荷日：" . date("Y年m月d", strtotime($dt)), 0, 'L', false, 0, 10, 45);
                //NAME
                if ($data[$r]["sender_cd"] == 1) {
                    $pdf->MultiCell(null, 10, "荷送人：　" . COMPANY, "B", 'L', false, 0, 10, 52);
                } else {
                    $pdf->MultiCell(null, 10, "荷送人：　その他", "B", 'L', false, 0, 10, 52);
                }

                $pdf->MultiCell(60, 10, "ご依頼主", 0, 'L', false, 0, 10, 65);
                $pdf->MultiCell(60, 10, "お届け先名", 0, 'L', false, 0, 22, 72);
                $pdf->MultiCell(60, 10, "商品明細", 0, 'R', false, 1, 10, 78);
                $pdf->MultiCell(null, 6, "個数", "B", 'R', false, 1, 10, 78);
                $pdf->MultiCell(null, 2, "", 0, 'L', false, 1);
            }
            $kosu += $data[$r]["kosu"];
            $order_no = $data[$r]["order_no"];

            $pdf->setFontSize(10);
            //TEL
            $pdf->Cell(40, 5, $data[$r]["tokuisaki_tel"]);
            //NAME
            $pdf->Cell(null, 5, $data[$r]["tokuisaki_nm"], 0, 1);
            $pdf->Cell(null, 5, "", 0, 1);
            //問い合わせ番号
            $pdf->MultiCell(60, 8, "問合番号：" . $data[$r]["inquire_no"], 0, 'L', false, 0, 20);
            //受注番号
            $pdf->MultiCell(60, 8, "受注番号：" . $order_no, 0, 'L', false, 1, 80);
            //ADDRESS
            $pdf->MultiCell(80, 6, $data[$r]["address"], 0, 'L', false, 0, 20);
            //金額
            $pdf->MultiCell(60, 6, "代引金額：" . number_format($data[$r]["grand_total"]), 0, 'L', false, 1, 140);
            //ADDRESS
            $pdf->MultiCell(80, 10, $data[$r]["building"], 0, 'L', false, 0, 20);
            //個数
            $pdf->MultiCell(10, 10, $data[$r]["kosu"], 0, 'L', false, 1, 190);

            //PRODUCT LIST
            $pdf->setFontSize(9);
            foreach ($mesai as &$obj) {
                if ($obj["order_no"] == $order_no) {
                    //商品名
                    $pdf->MultiCell(100, 6, $obj["product_nm"], 0, 'L', false, 0, 15);
                    //単位
                    $sale_tani = $obj["tani"];
                    if ($sale_tani == "なし") {
                        $sale_tani = "";
                    };
                    $qty = $obj["qty"];
                    if (strpos($qty, ".")) {
                        $qty = number_format($obj["qty"], 1);
                    }

                    $pdf->MultiCell(50, 6, $qty . "　" . $sale_tani, 0, 'L', false, 1, 115);
                };
            }
            //BOTTOM BORDER
            $pdf->MultiCell(null, 6, "", "T", 'L', false, 1);

            if ($r + 1 == $cnt || $dt != $data[$r + 1]["shuka_dt"]) {
                $pdf->MultiCell(null, 6, "計　（荷送人）", "B", 'C', false, 0);
                $pdf->MultiCell(10, 6, $kosu, 0, 'L', false, 1, 190);

                $pdf->MultiCell(null, 6, "小計　（出荷日）", 0, 'C', false, 0);
                $pdf->MultiCell(10, 6, $kosu, 0, 'L', false, 1, 190);
            }
        }


        $pdf->Output($fname, "F");
    } catch (Exception $e) {
        throw $e;
    }
}

class shukaReportPDFTest extends TCPDF
{
    private $first_pg;
    function setFirstPg($first_pg)
    {
        $this->first_pg = $first_pg;
    }
    function getFirstPg()
    {
        return $this->first_pg;
    }

    private $date_from;
    function setDateFrom($dt)
    {
        $this->date_from = $dt;
    }
    function getDateFrom()
    {
        return $this->date_from;
    }

    private $date_to;
    function setDateTo($dt)
    {
        $this->date_to = $dt;
    }
    function getDateTo()
    {
        return $this->date_to;
    }

    private $shuka_dt;
    function setShukaDt($dt)
    {
        $this->shuka_dt = $dt;
    }
    function getShukaDt()
    {
        return $this->shuka_dt;
    }

    private $ninushi_cd;
    function setNinushiCd($cd)
    {
        $this->ninushi_cd = $cd;
    }
    function getNinushiCd()
    {
        return $this->ninushi_cd;
    }

    private $sender_cd;
    function setSenderCd($cd)
    {
        $this->sender_cd = $cd;
    }
    function getSenderCd()
    {
        return $this->sender_cd;
    }

    private $kensu;
    function setKensu($kensu)
    {
        $this->kensu = $kensu;
    }
    function getKensu()
    {
        return $this->kensu;
    }

    private $kosu;
    function setKosu($kosu)
    {
        $this->kosu = $kosu;
    }
    function getKosu()
    {
        return $this->kosu;
    }

    //Page header
    public function Header()
    {
        if ($this->getFirstPg()) {

            $this->SetFont('kozgopromedium');
            $this->setFontSize(10);
            $this->MultiCell(80, 10, "出荷日：" . $this->getDateFrom() . " ～ " . $this->getDateTo(), 0, 'L', false, 0, 10, 20);

            $this->MultiCell(60, 10, "指定日：" . $this->getShukaDt(), 0, 'R', false, 0, 140, 20);

            //TITLE
            $this->setFontSize(16);
            $this->setCellPadding(2);
            $this->MultiCell(70, 12, "荷物受渡書", 1, 'J', false, 0, 70, 40, true, 4, false, true, 12, 'M');

            $this->setCellPadding(null);
            $this->setFontSize(10);
            $this->MultiCell(40, 10, $this->getNinushiCd(), 0, 'J', false, 0, 160, 35);
            $this->setFontSize(12);
            $this->MultiCell(60, 10, "出荷日：" . date("Y年m月d日"), 0, 'L', false, 0, 10, 60);

            if ($this->getSenderCd() == "1") {
                //ADDRESS
                $this->MultiCell(58, null, ADDRESS, 0, 'L', false, 0, 10, 80);
                //NAME
                $this->MultiCell(60, 10, POST_COMPANY, 0, 'L', false, 0, 10, 110);
                //TEL
                $this->MultiCell(null, 10, "TEL．" . TEL, 'B', 'L', false, 0, 10, 140);
            } else {
                $this->MultiCell(60, 10, "その他", 0, 'L', false, 0, 10, 110);
            }

            $this->MultiCell(10, 10, "様", 0, 'L', false, 0, 140, 120);

            $this->MultiCell(60, 10, "総出荷件数", 0, 'L', false, 0, 10, 160);
            $this->MultiCell(60, 10, $this->getKensu() . "　件", 0, 'L', false, 0, 80, 160);

            $this->MultiCell(60, 10, "総出荷個数", 0, 'L', false, 0, 10, 170);
            $this->MultiCell(60, 10, $this->getKosu() . "　個", 0, 'L', false, 0, 80, 170);

            $this->MultiCell(60, 10, "確かにお預かり致しました。", 0, 'L', false, 0, 10, 185);

            $this->setFontSize(10);
            $this->MultiCell(20, 25, "", 1, 'L', false, 0, 180, 160);
            $this->MultiCell(20, 5, "受領印", "B", 'C', false, 0, 180, 160);

            $this->MultiCell(null, 10, "", 'B', 'L', false, 0, 10, 200);
        } else {
            $this->setFillColor(204, 204, 204);
            $this->SetFont('kozgopromedium', '', 14);
            // Title
            $this->setCellPaddings(null, null, null, 2);
            $this->Cell(0, 20, '出　荷　日　報', 'B', false, 'C', false, '', 0, false, 'M', 'B');

            $this->SetFont('msmincho', '', 10);
            $this->setCellPaddings(null, null, null, null);
            $this->text(10, 10, "出荷日：" . $this->getDateFrom() . " ～ " . $this->getDateTo());
            $this->text(155, 10, "出力日：" . date("Y年m月d日"));
        }
    }
    public function Footer()
    {
        $this->SetFont('msmincho', '', 10);
        $this->MultiCell(null, 10, $this->getAliasNumPage() . ' ／ ' . $this->getAliasNbPages() . " ページ", 0, 'C', false, 0, 35, -15);
    }
};
function shukaReportDataTest($fname, $data, $mesai, $shuka_dt, $top_pg, $sender_cd)
{
    try {
        $pdf = new shukaReportPDFTest('P', 'mm');
        $pdf->SetCreator("株式会社〇〇〇〇");
        $pdf->SetAuthor("株式会社〇〇〇〇");
        $pdf->SetTitle('出荷日報');
        $pdf->SetSubject('出荷日報');
        $pdf->SetHeaderMargin(10);
        $pdf->setFooterMargin(25);
        $pdf->setAutoPageBreak(true, 30);
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);

        $cnt = count($data);
        $pdf->setDateFrom($data[0]["shuka_dt"]);
        $pdf->setDateTo($data[$cnt - 1]["shuka_dt"]);
        $pdf->setShukaDt($shuka_dt);
        $pdf->setNinushiCd($top_pg[0]["ninushi_cd"]);
        $pdf->setSenderCd($sender_cd);
        $pdf->setKensu($top_pg[0]["shuka_cnt"]);
        $pdf->setKosu($top_pg[0]["kosu_total"]);
        $pdf->setFirstPg(true);

        $pdf->AddPage();

        for ($i = 0; $i < $cnt; $i++) {
            if ($i == 0) {
                $pdf->setFirstPg(false);
                $pdf->AddPage();
                $pdf->setTopMargin(22);
            }
            $order_no = $data[$i]["order_no"];
            $pdf->SetFont('kozgopromedium', '', 10);
            //$pdf->Cell(50,5,$data[$i]["tokuisaki_tel"],0,0,'L');
            $pdf->MultiCell(30, 6, substr($data[$i]["tokuisaki_tel"], 0, 3) . "-" . substr($data[$i]["tokuisaki_tel"], 3, 3) . "-" . substr($data[$i]["tokuisaki_tel"], 6), 0, 'L', false, 0);
            $pdf->Cell(50, 5, $data[$i]["tokuisaki_nm"], 0, 1, 'L');

            $pdf->SetFont('msmincho', '', 9);
            $pdf->MultiCell(120, 6, $data[$i]["address"], 0, 'L', false, 0, 15);
            $pdf->MultiCell(50, 6, "問合番号：" . substr($data[$i]["inquire_no"], 0, 4) . "-" . substr($data[$i]["inquire_no"], 4, 4) . "-" . substr($data[$i]["inquire_no"], 8), 0, 'L', false, 1, 150);

            //barcode
            $pdf->write1DBarcode($data[$i]["inquire_no"], 'CODABAR', 145, null, 50, 10, 0.4);

            $pdf->MultiCell(40, 5, "代引金額：" . number_format($data[$i]["grand_total"]) . "円", 0, 'L', false, 0, 15);

            $pdf->MultiCell(40, 5, "個数：" . $data[$i]["kosu"], 0, 'L', false, 1, 100);

            $pdf->MultiCell(40, 5, "受注番号：" . $data[$i]["order_no"], 0, 'L', false, 1, 15);

            $product = "";
            foreach ($mesai as &$obj) {
                if ($obj["order_no"] == $order_no) {
                    //数慮
                    $qty = $obj["qty"];
                    if (strpos($qty, ".")) {
                        $qty = number_format($obj["qty"], 1);
                    }

                    //単位
                    $sale_tani = $obj["tani"];
                    if ($sale_tani == "なし") {
                        $sale_tani = "";
                    };

                    $product .= $obj["product_nm"] . "　" . $qty . $sale_tani . "       ";
                };
            }

            $pdf->MultiCell(null, 6, $product, 'B', 'L', false, 1, null, null, true, 0, false, true, 6, 'M', true);
            //$pdf->Cell(null, 1, '', 'T', 1);
        }

        $pdf->Output($fname, "F");
    } catch (Exception $e) {
        throw $e;
    }
}

function getMotoBaraiInquireNo()
{
    $dbh = null;
    try {
        $dbh = new PDO(DB_CON_STR, DB_USER, DB_PASS);
        $dbh->beginTransaction();

        $sql = "SELECT 
        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'yamato.motobarai.start') AS yamato_motobarai_start,
        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'yamato.motobarai.end') AS yamato_motobarai_end,
        (SELECT kanri_cd FROM m_code WHERE kanri_key = 'yamato.motobarai.current') AS yamato_motobarai_current";

        $sth = $dbh->prepare($sql);
        $sth->execute();
        $list = $sth->fetch();
        $inquire_no = $list["yamato_motobarai_current"];

        //UPDATE YAMATO INQUIRE NO
        //ヤマトの問い合わせ番号を更新
        // +1
        $sql = "UPDATE m_code
            SET kanri_cd = :yamato_current,
            update_date = CURRENT_TIMESTAMP
            WHERE kanri_key = 'yamato.motobarai.current';";

        $param = array();
        if ($inquire_no + 1 > $list["yamato_motobarai_end"]) {
            $param["yamato_current"] = $list["yamato_motobarai_start"];
        } else {
            $param["yamato_current"] = $inquire_no + 1;
        }
        $sth = $dbh->prepare($sql);
        $sth->execute($param);
        $dbh->commit();

        $inquire_no = $list["yamato_motobarai_current"] . Create7DRCheckDigitMotobarai($list["yamato_motobarai_current"]);

        return $inquire_no;
    } catch (Exception $e) {
        if ($dbh != null) {
            $dbh->rollBack();
        }
        error_log($e->getMessage());
        return null;
    }
}

/**
 * Create last digit for Yamato inquire number
 * @param String $strString Inquire number
 * @return Int Last digit for Yamato inquire_no
 */
function Create7DRCheckDigitMotobarai($strString)
{
    $lCheckDigit = 0;
    for ($i = 0; $i < strlen($strString); $i++) {
        $iAsc = ord(substr($strString, $i, 1));
        if ($iAsc < ord('0') || ord('9') < $iAsc) {
            return -1;
        }
        $lCheckDigit = ($lCheckDigit * 10 + $iAsc - ord('0')) % 7;
    }
    return $lCheckDigit;
}
