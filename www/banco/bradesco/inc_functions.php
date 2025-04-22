<?php

if(!function_exists('formataAssinatura')) {
	// Esta função formata a assinatura digital da transação, que é um número de 256 caracteres.
	// A formatação consiste em colocar todos esses caracteres em uma tabela, separando-os em grupos
	// de 4 caracteres cada um
	function formataAssinatura($entrada) {
	?>
		<table border='1' cellpadding='1' cellspacing='2' bordercolor='#cccccc' style='border-collapse:collapse;'>
		<TR>
		<?php
		$pos=0;
		$tam = strlen($entrada);
		$numColuna=0;
		while ($pos<$tam-1) {
		?>
			<TD align="center"><font size="1"><?php echo substr($entrada, $pos, 4)?></font></TD>
			<?php
			$pos += 4;
			$numColuna++;
			if ($numColuna==16) {
				$numColuna = 0;
			?>
		</TR>
		<TR>
		<?php
			}
		}
		?>
		</TR>
	</table>
	<?php
	}

}
?>