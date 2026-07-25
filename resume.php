<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/lib/fpdf.php';

$data = load_data();
$profile = $data['profile'] ?? [];
$social = $data['social'] ?? [];
$education = $data['education'] ?? [];
$experience = $data['experience'] ?? [];
$skills = $data['skills'] ?? [];
$skillProgress = $data['skillProgress'] ?? [];
$projects = array_values(array_filter($data['projects'] ?? [], fn ($p) => empty($p['private'])));

// FPDF only accepts single-byte (Windows-1252) text, so every string that
// might contain UTF-8 (curly quotes, accented names, the em dash, etc.)
// goes through this before being handed to the PDF.
function pdf_text(?string $value): string {
    $value = $value ?? '';
    $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $value);
    return $converted !== false ? $converted : $value;
}

$brandPurple = [139, 92, 246];
$brandCyan = [34, 211, 238];
$darkText = [17, 24, 39];
$mutedText = [100, 116, 139];

class Resume extends FPDF {
    public $brandPurple;
    public $mutedText;

    function Header() {
        // Intentionally blank — this resume uses its own custom header block
        // drawn per-page in the script below instead of FPDF's auto-header.
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor($this->mutedText[0], $this->mutedText[1], $this->mutedText[2]);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function SectionTitle($title) {
        $this->Ln(4);
        $this->SetFont('Helvetica', 'B', 12);
        $this->SetTextColor($this->brandPurple[0], $this->brandPurple[1], $this->brandPurple[2]);
        $this->Cell(0, 8, strtoupper($title), 0, 1);
        $this->SetDrawColor($this->brandPurple[0], $this->brandPurple[1], $this->brandPurple[2]);
        $this->SetLineWidth(0.6);
        $this->Line($this->GetX(), $this->GetY(), $this->GetX() + 190, $this->GetY());
        $this->Ln(3);
    }
}

$pdf = new Resume();
$pdf->brandPurple = $brandPurple;
$pdf->mutedText = $mutedText;
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

// --- Header block ---
$pdf->SetFont('Helvetica', 'B', 22);
$pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
$pdf->Cell(0, 10, pdf_text($profile['name'] ?? ''), 0, 1);

$pdf->SetFont('Helvetica', '', 12);
$pdf->SetTextColor($brandPurple[0], $brandPurple[1], $brandPurple[2]);
$pdf->Cell(0, 7, pdf_text($profile['title'] ?? ''), 0, 1);

$pdf->SetFont('Helvetica', '', 9.5);
$pdf->SetTextColor($mutedText[0], $mutedText[1], $mutedText[2]);
$contactLine = array_filter([
    $profile['phone'] ?? '',
    $profile['email'] ?? '',
    $social['github'] ?? '',
]);
$pdf->Cell(0, 6, pdf_text(implode('   |   ', $contactLine)), 0, 1);
$pdf->Ln(2);

// --- About ---
if (!empty($profile['about'])) {
    $pdf->SectionTitle('About');
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
    $pdf->MultiCell(0, 5.5, pdf_text($profile['about']));
}

// --- Experience ---
if (!empty($experience)) {
    $pdf->SectionTitle('Experience');
    foreach ($experience as $exp) {
        $pdf->SetFont('Helvetica', 'B', 10.5);
        $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
        $pdf->Cell(140, 6, pdf_text(($exp['role'] ?? '') . '  -  ' . ($exp['company'] ?? '')), 0, 0);
        $pdf->SetFont('Helvetica', 'I', 9.5);
        $pdf->SetTextColor($mutedText[0], $mutedText[1], $mutedText[2]);
        $pdf->Cell(50, 6, pdf_text($exp['period'] ?? ''), 0, 1, 'R');

        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
        foreach (($exp['bullets'] ?? []) as $bullet) {
            $pdf->Cell(4);
            $pdf->MultiCell(0, 5, pdf_text('- ' . $bullet));
        }
        $pdf->Ln(1);
    }
}

// --- Education ---
if (!empty($education)) {
    $pdf->SectionTitle('Education');
    foreach ($education as $edu) {
        $pdf->SetFont('Helvetica', 'B', 10.5);
        $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
        $pdf->Cell(140, 6, pdf_text(($edu['degree'] ?? '') . '  -  ' . ($edu['school'] ?? '')), 0, 0);
        $pdf->SetFont('Helvetica', 'I', 9.5);
        $pdf->SetTextColor($mutedText[0], $mutedText[1], $mutedText[2]);
        $pdf->Cell(50, 6, pdf_text($edu['period'] ?? ''), 0, 1, 'R');
        if (!empty($edu['grade'])) {
            $pdf->SetFont('Helvetica', '', 9.5);
            $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
            $pdf->Cell(0, 5, pdf_text($edu['grade']), 0, 1);
        }
    }
}

// --- Skill expertise (with simple bar visuals) ---
if (!empty($skillProgress)) {
    $pdf->SectionTitle('Skill Expertise');
    $pdf->SetFont('Helvetica', '', 9.5);
    foreach ($skillProgress as $sp) {
        $label = pdf_text(($sp['label'] ?? '') . ' - ' . (int) ($sp['percent'] ?? 0) . '%');
        $y = $pdf->GetY();
        $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
        $pdf->Cell(70, 5, $label, 0, 0);

        $barX = $pdf->GetX();
        $barW = 90;
        $pdf->SetFillColor(230, 230, 235);
        $pdf->Rect($barX, $y + 0.8, $barW, 3, 'F');
        $fillW = $barW * max(0, min(100, (int) ($sp['percent'] ?? 0))) / 100;
        $pdf->SetFillColor($brandCyan[0], $brandCyan[1], $brandCyan[2]);
        $pdf->Rect($barX, $y + 0.8, $fillW, 3, 'F');
        $pdf->Ln(6);
    }
}

// --- Skills (flat chip list) ---
if (!empty($skills)) {
    $pdf->SectionTitle('Skills');
    $pdf->SetFont('Helvetica', '', 9.5);
    $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
    $pdf->MultiCell(0, 5.5, pdf_text(implode('   *   ', $skills)));
}

// --- Projects ---
if (!empty($projects)) {
    $pdf->SectionTitle('Projects');
    foreach ($projects as $project) {
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor($darkText[0], $darkText[1], $darkText[2]);
        $pdf->Cell(0, 5.5, pdf_text($project['name'] ?? ''), 0, 1);
        if (!empty($project['description'])) {
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor($mutedText[0], $mutedText[1], $mutedText[2]);
            $pdf->MultiCell(0, 5, pdf_text($project['description']));
        }
        $pdf->Ln(1);
    }
}

$filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', $profile['name'] ?? 'resume') . '-resume.pdf';
$pdf->Output('D', $filename);
