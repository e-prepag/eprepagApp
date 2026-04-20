<?php
require('../fpdf.php');

class PDF extends FPDF
{
// Load data
/**
 * @param string $file
 * @return array<int, array<int, string>>
 */
function LoadData(string $file): array
{
	// Read file lines
	$lines = file($file);
	if ($lines === false) {
		return [];
	}
	$data = array();
	foreach($lines as $line)
		$data[] = explode(';',trim($line));
	return $data;
}

// Simple table
/**
 * @param array<int, string> $header
 * @param array<int, array<int, string>> $data
 */
function BasicTable(array $header, array $data): void
{
	// Header
	foreach($header as $col)
		$this->Cell(40,7,(string)$col,1);
	$this->Ln();
	// Data
	foreach($data as $row)
	{
		foreach($row as $col)
			$this->Cell(40,6,(string)$col,1);
		$this->Ln();
	}
}

// Better table
/**
 * @param array<int, string> $header
 * @param array<int, array<int, string>> $data
 */
function ImprovedTable(array $header, array $data): void
{
	// Column widths
	$w = array(40, 35, 40, 45);
	// Header
	for($i=0;$i<count($header);$i++)
		$this->Cell($w[$i],7,(string)$header[$i],1,0,'C');
	$this->Ln();
	// Data
	foreach($data as $row)
	{
		$this->Cell($w[0],6,(string)$row[0],'LR');
		$this->Cell($w[1],6,(string)$row[1],'LR');
		$this->Cell($w[2],6,number_format((float)($row[2] ?? 0)),'LR',0,'R');
		$this->Cell($w[3],6,number_format((float)($row[3] ?? 0)),'LR',0,'R');
		$this->Ln();
	}
	// Closing line
	$this->Cell(array_sum($w),0,'','T');
}

// Colored table
/**
 * @param array<int, string> $header
 * @param array<int, array<int, string>> $data
 */
function FancyTable(array $header, array $data): void
{
	// Colors, line width and bold font
	$this->SetFillColor(255,0,0);
	$this->SetTextColor(255);
	$this->SetDrawColor(128,0,0);
	$this->SetLineWidth(.3);
	$this->SetFont('','B');
	// Header
	$w = array(40, 35, 40, 45);
	for($i=0;$i<count($header);$i++)
		$this->Cell($w[$i],7,(string)$header[$i],1,0,'C',true);
	$this->Ln();
	// Color and font restoration
	$this->SetFillColor(224,235,255);
	$this->SetTextColor(0);
	$this->SetFont('');
	// Data
	$fill = false;
	foreach($data as $row)
	{
		$this->Cell($w[0],6,(string)($row[0] ?? ''),'LR',0,'L',$fill);
		$this->Cell($w[1],6,(string)($row[1] ?? ''),'LR',0,'L',$fill);
		$this->Cell($w[2],6,number_format((float)($row[2] ?? 0)),'LR',0,'R',$fill);
		$this->Cell($w[3],6,number_format((float)($row[3] ?? 0)),'LR',0,'R',$fill);
		$this->Ln();
		$fill = !$fill;
	}
	// Closing line
	$this->Cell(array_sum($w),0,'','T');
}
}

$pdf = new PDF();
// Column headings
$header = array('Country', 'Capital', 'Area (sq km)', 'Pop. (thousands)');
// Data loading
$data = $pdf->LoadData('countries.txt');
$pdf->SetFont('Arial','',14);
$pdf->AddPage();
$pdf->BasicTable($header,$data);
$pdf->AddPage();
$pdf->ImprovedTable($header,$data);
$pdf->AddPage();
$pdf->FancyTable($header,$data);
$pdf->Output();
?>
