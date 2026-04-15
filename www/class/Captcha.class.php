<?php
/*
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	Captcha Version 2.0 by László Zsidi, http://gifs.hu
::
::	This class is a rewritten 'Captcha.class.php' version.
::
::  Modification:
::   - Simplified and easy code,
::   - Stable working
::
::
::	Created at 2007. 02. 01. '07.47.AM'
::
*/

require_once "GIFEncoder.class.php";

define ( 'ANIM_FRAMES',  5 );
define ( 'ANIM_DELAYS', 10 );

Class Captcha {
	var $image;

	function Captcha ( $text, $font, $color ) {
		$C              = HexDec ( $color );
		$R              = floor ( $C / pow ( 256, 2 ) );
		$G              = floor ( ( $C % pow ( 256, 2 ) ) / pow ( 256, 1 ) );
		$B              = floor ( ( ( $C % pow ( 256, 2 ) ) % pow ( 256, 1 ) ) / pow ( 256, 0 ) );
		$fsize          = 32;
		$bound          = array ( );
		$bound          = imageTTFBbox ( $fsize, 0, $font, $text );
		$this->image    = imageCreateTrueColor ( $bound [ 4 ] + 5, abs($bound [ 5 ] ) + 15 );
		imageFill       ( $this->image, 0, 0, ImageColorAllocate ( $this->image, 255, 255, 204 ) );
		imagettftext    ( $this->image, $fsize, 0, 2, abs( $bound [ 5 ] ) + 5, ImageColorAllocate ( $this->image, $R, $G, $B ), $font, $text );

		$W = imageSX ( $this->image );
		$H = imageSY ( $this->image );
		for ($i=0; $i<10; $i++) {
			imageLine($this->image, rand(0, $W), rand(0, $H), rand(0, $W), rand(0, $H), imagecolorallocate($this->image, rand(0,255), rand(0,255), rand(0,255)));    
		}

	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::::::::
	::
	::	DoNoise...
	::
	*/
	function DoNoise ( $image, $G0, $C0 ) {
		$W = imageSX ( $image );
		$H = imageSY ( $image );

		for ( $i = 0; $i < 768; $i++ ) {
			$arrLUT [ $i ] = $i < 512 ? ( $i < 255 ? 0 : ( $i - 256 ) ) : 255;
		}

		$G1 = $G0 / 2;
		$C1 = $C0 / 2;
		for ( $y = 0; $y < $H; $y++ ) {
			for ( $x = 0; $x < $W; $x++ ) {
				$P  = imageColorAt ( $image, $x, $y );
				$R  = ( $P >> 16 ) & 0xFF;
				$G  = ( $P >>  8 ) & 0xFF;
				$B  = ( $P >>  0 ) & 0xFF;
				$N  = rand ( 0, $G0 ) - $G1;
				$R += 255 + $N + mt_rand ( 0, $C0 ) - $C1;
				$G += 255 + $N + mt_rand ( 0, $C0 ) - $C1;
				$B += 255 + $N + mt_rand ( 0, $C0 ) - $C1;
				imageSetPixel ( $image, $x, $y, ( $arrLUT [ $R ] << 16 ) | ( $arrLUT [ $G ] << 8 ) | $arrLUT [ $B ] );
			}
		}
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::::::::
	::
	::	AnimatedOut...
	::
	*/
	function AnimatedOut ( ) {

		for ( $i = 0; $i < ANIM_FRAMES; $i++ ) {
			$image = imageCreateTrueColor ( imageSX ( $this->image ), imageSY ( $this->image ) );

			if ( imageCopy ( $image, $this->image, 0, 0, 0, 0, imageSX ( $this->image ), imageSY ( $this->image ) ) ) {
				Captcha::DoNoise ( $image, 200, 127 );

				Ob_Start		(			);
				imageGif		( $image	);
				imageDestroy	( $image	);

				$f_arr [ ] = Ob_Get_Contents ( );
				$d_arr [ ] = ANIM_DELAYS;

				Ob_End_Clean	(			);
			}
		}
		$GIF = new GIFEncoder ( $f_arr, $d_arr, 0, 2, -1, -1, -1, "bin" );
		return ( $GIF->GetAnimation ( ) );
	}
}
?>
