<?php
/*******************************************************************************
* Utility to parse TTF font files                                              *
*                                                                              *
* Version: 1.0                                                                 *
* Date:    2011-06-18                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

class TTFParser
{
	/** @var resource|false */
	public $f;
	public array $tables = array();
	public int $unitsPerEm = 0;
	public int $xMin = 0;
	public int $yMin = 0;
	public int $xMax = 0;
	public int $yMax = 0;
	public int $numberOfHMetrics = 0;
	public int $numGlyphs = 0;
	public array $widths = array();
	public array $chars = array();
	public string $postScriptName = '';
	public bool $Embeddable = false;
	public bool $Bold = false;
	public int $typoAscender = 0;
	public int $typoDescender = 0;
	public int $capHeight = 0;
	public int $italicAngle = 0;
	public int $underlinePosition = 0;
	public int $underlineThickness = 0;
	public bool $isFixedPitch = false;

	public function Parse(string $file): void
	{
		$this->f = fopen($file, 'rb');
		if(!$this->f)
			$this->Error('Can\'t open file: '.$file);

		$version = $this->Read(4);
		if($version=='OTTO')
			$this->Error('OpenType fonts based on PostScript outlines are not supported');
		if($version!="\x00\x01\x00\x00")
			$this->Error('Unrecognized file format');
		$numTables = $this->ReadUShort();
		$this->Skip(3*2); // searchRange, entrySelector, rangeShift
		$this->tables = array();
		for($i=0;$i<$numTables;$i++)
		{
			$tag = $this->Read(4);
			$this->Skip(4); // checkSum
			$offset = $this->ReadULong();
			$this->Skip(4); // length
			$this->tables[$tag] = $offset;
		}

		$this->ParseHead();
		$this->ParseHhea();
		$this->ParseMaxp();
		$this->ParseHmtx();
		$this->ParseCmap();
		$this->ParseName();
		$this->ParseOS2();
		$this->ParsePost();

		fclose($this->f);
	}

	public function ParseHead(): void
	{
		$this->Seek('head');
		$this->Skip(3*4); // version, fontRevision, checkSumAdjustment
		$magicNumber = $this->ReadULong();
		if($magicNumber!=0x5F0F3CF5)
			$this->Error('Incorrect magic number');
		$this->Skip(2); // flags
		$this->unitsPerEm = $this->ReadUShort();
		$this->Skip(2*8); // created, modified
		$this->xMin = $this->ReadShort();
		$this->yMin = $this->ReadShort();
		$this->xMax = $this->ReadShort();
		$this->yMax = $this->ReadShort();
	}

	public function ParseHhea(): void
	{
		$this->Seek('hhea');
		$this->Skip(4+15*2);
		$this->numberOfHMetrics = $this->ReadUShort();
	}

	public function ParseMaxp(): void
	{
		$this->Seek('maxp');
		$this->Skip(4);
		$this->numGlyphs = $this->ReadUShort();
	}

	public function ParseHmtx(): void
	{
		$this->Seek('hmtx');
		$this->widths = array();
		for($i=0;$i<$this->numberOfHMetrics;$i++)
		{
			$advanceWidth = $this->ReadUShort();
			$this->Skip(2); // lsb
			$this->widths[$i] = $advanceWidth;
		}
		if($this->numberOfHMetrics<$this->numGlyphs)
		{
			$lastWidth = $this->widths[$this->numberOfHMetrics-1];
			$this->widths = array_pad($this->widths, $this->numGlyphs, $lastWidth);
		}
	}

	public function ParseCmap(): void
	{
		$this->Seek('cmap');
		$this->Skip(2); // version
		$numTables = $this->ReadUShort();
		$offset31 = 0;
		for($i=0;$i<$numTables;$i++)
		{
			$platformID = $this->ReadUShort();
			$encodingID = $this->ReadUShort();
			$offset = $this->ReadULong();
			if($platformID==3 && $encodingID==1)
				$offset31 = $offset;
		}
		if($offset31==0)
			$this->Error('No Unicode encoding found');

		$startCount = array();
		$endCount = array();
		$idDelta = array();
		$idRangeOffset = array();
		$this->chars = array();
		if ($this->f === false) Error('File not open');
		fseek($this->f, $this->tables['cmap']+$offset31, SEEK_SET);
		$format = $this->ReadUShort();
		if($format!=4)
			$this->Error('Unexpected subtable format: '.$format);
		$this->Skip(2*2); // length, language
		$segCount = $this->ReadUShort()/2;
		$this->Skip(3*2); // searchRange, entrySelector, rangeShift
		for($i=0;$i<$segCount;$i++)
			$endCount[$i] = $this->ReadUShort();
		$this->Skip(2); // reservedPad
		for($i=0;$i<$segCount;$i++)
			$startCount[$i] = $this->ReadUShort();
		for($i=0;$i<$segCount;$i++)
			$idDelta[$i] = $this->ReadShort();
		$offset = ftell($this->f);
		if ($offset === false) Error('ftell failed');
		for($i=0;$i<$segCount;$i++)
			$idRangeOffset[$i] = $this->ReadUShort();

		for($i=0;$i<$segCount;$i++)
		{
			$c1 = $startCount[$i];
			$c2 = $endCount[$i];
			$d = $idDelta[$i];
			$ro = $idRangeOffset[$i];
			if($ro>0)
				fseek($this->f, $offset+2*$i+$ro, SEEK_SET);
			for($c=$c1;$c<=$c2;$c++)
			{
				if($c==0xFFFF)
					break;
				if($ro>0)
				{
					$gid = $this->ReadUShort();
					if($gid>0)
						$gid += $d;
				}
				else
					$gid = $c+$d;
				if($gid>=65536)
					$gid -= 65536;
				if($gid>0)
					$this->chars[$c] = $gid;
			}
		}
	}

	public function ParseName(): void
	{
		$this->Seek('name');
		if ($this->f === false) Error('File not open');
		$tableOffset = ftell($this->f);
		if ($tableOffset === false) Error('ftell failed');
		$this->postScriptName = '';
		$this->Skip(2); // format
		$count = $this->ReadUShort();
		$stringOffset = $this->ReadUShort();
		for($i=0;$i<$count;$i++)
		{
			$this->Skip(3*2); // platformID, encodingID, languageID
			$nameID = $this->ReadUShort();
			$length = $this->ReadUShort();
			$offset = $this->ReadUShort();
			if($nameID==6)
			{
				// PostScript name
				fseek($this->f, $tableOffset+$stringOffset+$offset, SEEK_SET);
				$s = $this->Read($length);
				$s = str_replace(chr(0), '', $s);
				$s = preg_replace('|[ \[\](){}<>/%]|', '', $s);
				$this->postScriptName = (string)$s;
				break;
			}
		}
		if($this->postScriptName=='')
			$this->Error('PostScript name not found');
	}

	public function ParseOS2(): void
	{
		$this->Seek('OS/2');
		$version = $this->ReadUShort();
		$this->Skip(3*2); // xAvgCharWidth, usWeightClass, usWidthClass
		$fsType = $this->ReadUShort();
		$this->Embeddable = ($fsType!=2) && ($fsType & 0x200)==0;
		$this->Skip(11*2+10+4*4+4);
		$fsSelection = $this->ReadUShort();
		$this->Bold = ($fsSelection & 32)!=0;
		$this->Skip(2*2); // usFirstCharIndex, usLastCharIndex
		$this->typoAscender = $this->ReadShort();
		$this->typoDescender = $this->ReadShort();
		if($version>=2)
		{
			$this->Skip(3*2+2*4+2);
			$this->capHeight = $this->ReadShort();
		}
		else
			$this->capHeight = 0;
	}

	public function ParsePost(): void
	{
		$this->Seek('post');
		$this->Skip(4); // version
		$this->italicAngle = $this->ReadShort();
		$this->Skip(2); // Skip decimal part
		$this->underlinePosition = $this->ReadShort();
		$this->underlineThickness = $this->ReadShort();
		$this->isFixedPitch = ($this->ReadULong()!=0);
	}

	/**
	 * @return never
	 */
	public function Error(string $msg)
	{
		if(PHP_SAPI=='cli')
			die("Error: $msg\n");
		else
			die("<b>Error</b>: $msg");
	}

	public function Seek(string $tag): void
	{
		if(!isset($this->tables[$tag]))
			$this->Error('Table not found: '.$tag);
		if ($this->f === false) Error('File not open');
		fseek($this->f, $this->tables[$tag], SEEK_SET);
	}

	public function Skip(int $n): void
	{
		if ($this->f === false) Error('File not open');
		fseek($this->f, $n, SEEK_CUR);
	}

	public function Read(int $n): string
	{
		if ($this->f === false) Error('File not open');
		$res = fread($this->f, $n);
		if ($res === false) Error('Read failed');
		return $res;
	}

	public function ReadUShort(): int
	{
		if ($this->f === false) Error('File not open');
		$buf = fread($this->f,2);
		if ($buf === false) Error('Read failed');
		$a = unpack('nn', $buf);
		if ($a === false) Error('unpack failed');
		return (int)$a['n'];
	}

	public function ReadShort(): int
	{
		if ($this->f === false) Error('File not open');
		$buf = fread($this->f,2);
		if ($buf === false) Error('Read failed');
		$a = unpack('nn', $buf);
		if ($a === false) Error('unpack failed');
		$v = (int)$a['n'];
		if($v>=0x8000)
			$v -= 65536;
		return $v;
	}

	public function ReadULong(): int
	{
		if ($this->f === false) Error('File not open');
		$buf = fread($this->f,4);
		if ($buf === false) Error('Read failed');
		$a = unpack('NN', $buf);
		if ($a === false) Error('unpack failed');
		return (int)$a['N'];
	}
}
?>
