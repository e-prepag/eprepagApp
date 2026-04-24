<?php
require('../fpdf.php');

class PDF extends FPDF
{
public int $B = 0;
public int $I = 0;
public int $U = 0;
public string $HREF = '';

function __construct(string $orientation='P', string $unit='mm', mixed $size='A4')
{
	// Call parent constructor
	parent::__construct($orientation,$unit,$size);
	// Initialization
	$this->B = 0;
	$this->I = 0;
	$this->U = 0;
	$this->HREF = '';
}

/**
 * Backward compatibility
 */
function PDF(string $orientation='P', string $unit='mm', mixed $size='A4'): void
{
	$this->__construct($orientation, $unit, $size);
}

function WriteHTML(string $html): void
{
	// HTML parser
	$html = str_replace("\n",' ',$html);
	$a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	if ($a === false) {
		return;
	}
	foreach($a as $i=>$e)
	{
		if($i%2==0)
		{
			// Text
			if($this->HREF)
				$this->PutLink($this->HREF,$e);
			else
				$this->Write(5,$e);
		}
		else
		{
			// Tag
			if(isset($e[0]) && $e[0]=='/')
				$this->CloseTag(strtoupper(substr($e,1)));
			else
			{
				// Extract attributes
				$a2 = explode(' ',$e);
				$tag = strtoupper((string)array_shift($a2));
				$attr = array();
				foreach($a2 as $v)
				{
					if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
						$attr[strtoupper($a3[1])] = $a3[2];
				}
				$this->OpenTag($tag,$attr);
			}
		}
	}
}

function OpenTag(string $tag, array $attr): void
{
	// Opening tag
	if($tag=='B' || $tag=='I' || $tag=='U')
		$this->SetStyle($tag,true);
	if($tag=='A' && isset($attr['HREF']))
		$this->HREF = (string)$attr['HREF'];
	if($tag=='BR')
		$this->Ln(5);
}

function CloseTag(string $tag): void
{
	// Closing tag
	if($tag=='B' || $tag=='I' || $tag=='U')
		$this->SetStyle($tag,false);
	if($tag=='A')
		$this->HREF = '';
}

function SetStyle(string $tag, bool $enable): void
{
	// Modify style and select corresponding font
	if ($tag === 'B' || $tag === 'I' || $tag === 'U') {
		$this->$tag += ($enable ? 1 : -1);
	}
	$style = '';
	foreach(array('B', 'I', 'U') as $s)
	{
		if($this->$s>0)
			$style .= $s;
	}
	$this->SetFont('',$style);
}

function PutLink(string $URL, string $txt): void
{
	// Put a hyperlink
	$this->SetTextColor(0,0,255);
	$this->SetStyle('U',true);
	$this->Write(5,$txt,$URL);
	$this->SetStyle('U',false);
	$this->SetTextColor(0);
}
}

$html = 'You can now easily print text mixing different styles: <b>bold</b>, <i>italic</i>,
<u>underlined</u>, or <b><i><u>all at once</u></i></b>!<br><br>You can also insert links on
text, such as <a href="http://www.fpdf.org">www.fpdf.org</a>, or on an image: click on the logo.';

$pdf = new PDF();
// First page
$pdf->AddPage();
$pdf->SetFont('Arial','',20);
$pdf->Write(5,"To find out what's new in this tutorial, click ");
$pdf->SetFont('','U');
$link = $pdf->AddLink();
$pdf->Write(5,'here',$link);
$pdf->SetFont('');
// Second page
$pdf->AddPage();
$pdf->SetLink($link);
$pdf->Image('logo.png',10,12,30,0,'','http://www.fpdf.org');
$pdf->SetLeftMargin(45);
$pdf->SetFontSize(14);
$pdf->WriteHTML($html);
$pdf->Output();
?>
