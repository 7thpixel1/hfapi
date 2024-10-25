<?php
require_once('tcpdf.php');
class SmartPdf extends TCPDF{
    private $_footer_text, $_header_text, $_showDefaultFooter, $_orintation;
    
    function getFooterText() {
        return $this->_footer_text;
    }

    function getHeaderText() {
        return $this->_header_text;
    }

    function setFooterText($footer_text) {
        $this->_footer_text = $footer_text;
    }

    function setHeaderText($header_text) {
        $this->_header_text = $header_text;
    }
    function setShowDefaultFooter($showDefaultFooter) {
        $this->_showDefaultFooter = $showDefaultFooter;
    }

    function setOrintation($orintation) {
        $this->_orintation = $orintation;
    }

        
    public function Header() {
        $image_file = K_PATH_IMAGES.'report-logo.png';
        $this->Image($image_file, 0, 0, 120, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'N', 16);
        // Title
        $capWidth = ($this->_orintation === 'L')?230:190;
        $line = ($this->_orintation === 'L')?290:200;
        $this->MultiCell($capWidth, 0, "\n".$this->getHeaderText(), 0, 'R', false, 1, 10);
        $style = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(196, 196, 196));
        $this->Line(6, 25, $line, 25, $style);
        
    }

    public function Footer() {
        $margin = ($this->_showDefaultFooter === TRUE)?-10:-20;
        $this->SetY($margin);
        $this->SetFont('helvetica', 'N', 7);
        $this->SetTextColor(96, 96, 96); 
        $this->Image(K_FOOTER_IMAGE, 140, 270, 40, 24, '', '', '', false, 300, '', false, false, 0);
        $text = $this->getFooterText();
        $this->MultiCell(130, 0, $text, 0, "L", false, 1,5);
        $this->Cell(0, 0, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}
