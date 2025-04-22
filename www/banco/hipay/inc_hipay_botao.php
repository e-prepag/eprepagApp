<form action="/prepag2/pag/hpy/hipay_single_payment.php" target="_blank">
<input type="hidden" name="numcompra" id="numcompra" value="<?php echo $_SESSION['pagamento.numorder'] ?>">
<input type="hidden" name="amount" id="amount" value="<?php echo number_format($amount,2) ?>">
<input type="image" src="http://www.e-prepag.com.br/prepag2/commerce/images/botao_hipay.gif" border="0" name="submit" title="PayPal">
</form>
