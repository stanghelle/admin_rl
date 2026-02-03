<?php
/**
 * Print Program Oversikt PDF
 * Generates a PDF of the currently published program schedule
 * Uses PDO prepared statements for security
 */
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

// Get DB instance (PDO-based)
$db = DB::getInstance();

// Get week and year info - use current week by default or from prg_pdf dagid=8 if available
$uke = date('W'); // Current week number
$aar = date('Y'); // Current year

// Try to get week/year from prg_pdf table (same source as print_pdf.php for consistency)
$weekData = $db->query("SELECT kl, program FROM prg_pdf WHERE dagid = ? ORDER BY sort_order ASC, id ASC LIMIT 1", [8]);

if ($weekData->count() > 0) {
    $row = $weekData->first();
    if (!empty($row->kl) && !empty($row->program)) {
        $uke = $row->kl;
        $aar = $row->program;
    }
}

// Set up locale for Norwegian date formatting
$originalLocales = explode(";", setlocale(LC_ALL, 0));
setlocale(LC_ALL, "nb_NO.utf8");
$date = new DateTime();
$locale = "no_NO.UTF-8";
$formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, "Europe/Oslo");
$formatter->setPattern('EEEE d.MMMM');

// Generate day names with dates
$date->setISODate($aar, $uke, 1);
$dman = $formatter->format($date);

$date->setISODate($aar, $uke, 2);
$dtirs = $formatter->format($date);

$date->setISODate($aar, $uke, 3);
$dons = $formatter->format($date);

$date->setISODate($aar, $uke, 4);
$dtors = $formatter->format($date);

$date->setISODate($aar, $uke, 5);
$dfre = $formatter->format($date);

$date->setISODate($aar, $uke, 6);
$dlor = $formatter->format($date);

$date->setISODate($aar, $uke, 7);
$dson = $formatter->format($date);

/**
 * Generate HTML content for a specific day from program_oversikt table
 * Uses PDO prepared statements
 * @param int $dagid Day ID (1-7 for Mon-Sun, 9 for Andakt)
 * @param bool $isAndakt Whether this is the Andakt row (different formatting)
 * @return string HTML content
 */
function generateOversiktContent($dagid, $isAndakt = false) {
    $contents = '';
    $db = DB::getInstance();

    $data = $db->query(
        "SELECT kl, program FROM program_oversikt WHERE dagid = ? ORDER BY sort_order ASC, id ASC",
        [$dagid]
    );

    if ($data->count() > 0) {
        foreach ($data->results() as $row) {
            if ($isAndakt) {
                $contents .= "<strong>" . htmlspecialchars($row->program) . "</strong><br>\n";
            } else {
                $contents .= "<strong>" . htmlspecialchars($row->kl) . "</strong> " . htmlspecialchars($row->program) . "<br>\n";
            }
        }
    }

    return $contents;
}

// Generate content for each day
$row1 = generateOversiktContent(1);
$row2 = generateOversiktContent(2);
$row3 = generateOversiktContent(3);
$row4 = generateOversiktContent(4);
$row5 = generateOversiktContent(5);
$row6 = generateOversiktContent(6);
$row7 = generateOversiktContent(7);
$row9 = generateOversiktContent(9, true); // Andakt

require_once('assets/plugins/tcpdf/tcpdf.php');

$pageDimension = array('106,245'); //width,height
$pdf = new TCPDF('L', 'mm', $pageDimension, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle("Programoversikt Radio Lyngdal - Uke " . $uke);
$pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetMargins(PDF_MARGIN_LEFT, '1', PDF_MARGIN_RIGHT);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(true, 1);
$pdf->SetFont('helvetica', '', 7);
$pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');

$page_format = array(
    'MediaBox' => array ('llx' => 0, 'lly' => 0, 'urx' => 106, 'ury' => 245),
    'Rotate' => 0,
);

$pdf->AddPage('L', $page_format, false, false);

// Get andakt content
$andaktHtml = !empty($row9) ? 'Andakt denne uke: ' . $row9 : '';

$html = '
<table border="0" cellspacing="1" cellpadding="1">
    <tr>
        <th><img src="assets/images/logorl_ps.png" width="200"></th>
        <th><strong>
            Få med deg Riksnyheter<br>
            hver hele time: Man - Tors kl 06 til 20, fre kl 06 til 19<br>
            Man - fre kl 06:30, 07:30, 14:30, 15:30<br>
            Lør kl 10 til 15 og Søn kl 13 til 15 </strong></th>
        <th>Hør oss på FM i Lister, DAB+ i Agder, nettradio i hele verden.<br>
            Bingobrett kan kjøpes på radiobingo.no.  Les mer på radiolyngdal.no<br>
            ' . $andaktHtml . '</th>
    </tr>
</table>
<table border="0" cellspacing="2" cellpadding="2">
    <tr>
        <th>' . $dman . '</th>
        <th>' . $dtirs . '</th>
        <th>' . $dons . '</th>
        <th>' . $dtors . '</th>
        <th>' . $dfre . '</th>
        <th>' . $dlor . '</th>
        <th>' . $dson . '</th>
    </tr>
    <tr>
        <td>' . $row1 . '</td>
        <td>' . $row2 . '</td>
        <td>' . $row3 . '</td>
        <td>' . $row4 . '</td>
        <td>' . $row5 . '</td>
        <td>' . $row6 . '</td>
        <td>' . $row7 . '</td>
    </tr>
</table>
';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('Programoversikt_publisert_uke' . $uke . '.pdf', 'D');
?>
