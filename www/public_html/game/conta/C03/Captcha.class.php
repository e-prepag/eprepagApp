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

include "GIFEncoder.class.php";

define ( 'ANIM_FRAMES',  5 );
define ( 'ANIM_DELAYS', 10 );

Class Captcha {
	var $image;

	function __construct ( $text, $font, $color ) {
		$this->Captcha ( $text, $font, $color );
	}

	function Captcha ( $text, $font, $color ) {
		if ( !function_exists ( "imageCreateTrueColor" ) ) {
			error_log ( "Captcha: GD imageCreateTrueColor indisponivel" );
			$this->image = null;
			return;
		}
		$C              = HexDec ( $color );
		$R              = floor ( $C / pow ( 256, 2 ) );
		$G              = floor ( ( $C % pow ( 256, 2 ) ) / pow ( 256, 1 ) );
		$B              = floor ( ( ( $C % pow ( 256, 2 ) ) % pow ( 256, 1 ) ) / pow ( 256, 0 ) );
		$fsize          = 32;
		$bound          = is_file($font) ? imageTTFBbox ( $fsize, 0, $font, $text ) : false;
		if ( $bound === false ) {
			error_log ( "Captcha: fonte TTF invalida ou bbox falhou: " . $font );
			$this->image = $this->createFallbackImage ( $text, $R, $G, $B );
			return;
		}

		$this->image    = imageCreateTrueColor ( max(1, $bound [ 4 ] + 5), max(1, abs($bound [ 5 ] ) + 15) );
		if ( $this->image === false ) {
			error_log ( "Captcha: imageCreateTrueColor falhou" );
			$this->image = $this->createFallbackImage ( $text, $R, $G, $B );
			return;
		}

		imageFill       ( $this->image, 0, 0, ImageColorAllocate ( $this->image, 255, 255, 204 ) );
		if ( imagettftext    ( $this->image, $fsize, 0, 2, abs( $bound [ 5 ] ) + 5, ImageColorAllocate ( $this->image, $R, $G, $B ), $font, $text ) === false ) {
			error_log ( "Captcha: imagettftext falhou com fonte: " . $font );
			imageDestroy ( $this->image );
			$this->image = $this->createFallbackImage ( $text, $R, $G, $B );
			return;
		}

		$W = imageSX ( $this->image );
		$H = imageSY ( $this->image );
		for ($i=0; $i<10; $i++) {
			imageLine($this->image, rand(0, $W), rand(0, $H), rand(0, $W), rand(0, $H), imagecolorallocate($this->image, rand(0,255), rand(0,255), rand(0,255)));    
		}

	}
	function createFallbackImage ( $text, $R, $G, $B ) {
		if ( !function_exists ( "imageCreateTrueColor" ) ) {
			error_log ( "Captcha: fallback sem GD" );
			return null;
		}
		$image = imageCreateTrueColor ( 90, 45 );
		if ( $image === false ) {
			error_log ( "Captcha: createFallbackImage falhou" );
			return null;
		}

		imageFill ( $image, 0, 0, ImageColorAllocate ( $image, 255, 255, 204 ) );
		imageString ( $image, 5, 18, 14, $text, ImageColorAllocate ( $image, $R, $G, $B ) );
		return $image;
	}

	function emptyGif ( ) {
		if ( !function_exists ( "imageCreateTrueColor" ) ) {
			error_log ( "Captcha: gerando GIF minimo porque GD esta indisponivel" );
			return base64_decode ( "R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" );
		}

		$image = imageCreateTrueColor ( 1, 1 );
		if ( $image === false ) {
			return base64_decode ( "R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" );
		}

		imageFill ( $image, 0, 0, ImageColorAllocate ( $image, 255, 255, 255 ) );
		Ob_Start ( );
		imageGif ( $image );
		imageDestroy ( $image );
		$gif = Ob_Get_Contents ( );
		Ob_End_Clean ( );
		return $gif;
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

		if ( !is_resource ( $this->image ) && !( $this->image instanceof GdImage ) ) {
			error_log ( "Captcha: imagem principal invalida antes de AnimatedOut" );
			return $this->emptyGif ( );
		}

		$f_arr = array ( );
		$d_arr = array ( );

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
		if ( count ( $f_arr ) === 0 ) {
			return $this->emptyGif ( );
		}

		$GIF = new GIFEncoder ( $f_arr, $d_arr, 0, 2, -1, -1, -1, "bin" );
		return ( $GIF->GetAnimation ( ) );
	}
}
?>
