<?php
require_once 'Auth_check.php';
require_once 'Require_role.php';
require_once 'db/db_connect.php';
require_once 'fpdf.php';

require_role(['admin']);

$academic_year = trim($_GET['academic_year'] ?? '');
$level         = trim($_GET['level'] ?? '');
$status        = trim($_GET['status'] ?? '');

$where = [];
$params = [];

if ($academic_year !== '') {
    $where[] = 'd.academic_year = :ay';
    $params['ay'] = $academic_year;
}
if (in_array($level, ['100', '200', '300', '400'], true)) {
    $where[] = 'd.level = :lvl';
    $params['lvl'] = $level;
}
if (in_array($status, ['pending', 'success', 'failed'], true)) {
    $where[] = 'p.status = :st';
    $params['st'] = $status;
}

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare(
    "SELECT p.*, d.title AS due_title, d.level, d.academic_year, u.username, u.index_number, u.certification
     FROM payments p
     JOIN dues d ON d.id = p.due_id
     JOIN users u ON u.id = p.student_id
     $where_sql
     ORDER BY d.academic_year DESC, d.level ASC, p.created_at DESC"
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$total_amount = 0;
foreach ($rows as $r) {
    if ($r['status'] === 'success') {
        $total_amount += $r['amount'];
    }
}

class DuesReportPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(27, 42, 107);
        $this->Cell(0, 8, 'Computer Science Department, KsTU', 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 6, 'Dues Payment Report', 0, 1, 'C');
        $this->Ln(4);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 287, $this->GetY());
        $this->Ln(4);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' | Generated ' . date('d M Y, g:i A'), 0, 0, 'C');
    }
}

$pdf = new DuesReportPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

$filters_text = [];
if ($academic_year !== '') $filters_text[] = "Academic Year: $academic_year";
if ($level !== '') $filters_text[] = "Level: $level";
if ($status !== '') $filters_text[] = "Status: " . ucfirst($status);
if (!empty($filters_text)) {
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, 'Filters: ' . implode(' | ', $filters_text), 0, 1, 'L');
    $pdf->Ln(2);
}

$headers = ['Student', 'Index No.', 'Cert.', 'Due', 'Level', 'Amount (GHS)', 'Reference', 'Status', 'Date'];
$widths  = [35, 25, 20, 55, 15, 25, 45, 22, 35];

$pdf->SetFillColor(27, 42, 107);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
foreach ($headers as $i => $h) {
    $pdf->Cell($widths[$i], 8, $h, 1, 0, 'C', true);
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(30, 30, 30);
$fill = false;
foreach ($rows as $r) {
    $pdf->SetFillColor(245, 247, 250);
    $pdf->Cell($widths[0], 7, $r['username'], 1, 0, 'L', $fill);
    $pdf->Cell($widths[1], 7, $r['index_number'] ?? '-', 1, 0, 'L', $fill);
    $pdf->Cell($widths[2], 7, ucfirst($r['certification'] ?? '-'), 1, 0, 'L', $fill);
    $pdf->Cell($widths[3], 7, substr($r['due_title'], 0, 32), 1, 0, 'L', $fill);
    $pdf->Cell($widths[4], 7, $r['level'], 1, 0, 'C', $fill);
    $pdf->Cell($widths[5], 7, number_format($r['amount'], 2), 1, 0, 'R', $fill);
    $pdf->Cell($widths[6], 7, $r['paystack_reference'], 1, 0, 'L', $fill);
    $pdf->Cell($widths[7], 7, ucfirst($r['status']), 1, 0, 'C', $fill);
    $pdf->Cell($widths[8], 7, date('d M Y', strtotime($r['created_at'])), 1, 0, 'C', $fill);
    $pdf->Ln();
    $fill = !$fill;
}

$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(27, 42, 107);
$pdf->Cell(0, 8, 'Total Collected (Successful Payments Only): GHS ' . number_format($total_amount, 2), 0, 1, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 6, 'Total Records: ' . count($rows), 0, 1, 'L');

$filename = 'dues_report_' . date('Y-m-d_His') . '.pdf';
$pdf->Output('D', $filename);