<?php 
// Decodifica a URL e sanitiza o valor
$target = urldecode($_GET['target']); 
$safeTarget = basename($target); // Remove qualquer caminho do arquivo

// Validação do tipo de arquivo (exemplo: permitir apenas imagens jpg, png, gif)
$allowedExtensions = ['jpg', 'png', 'gif'];
$fileExtension = strtolower(pathinfo($safeTarget, PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    die("Arquivo inválido.");
}
?>
<!doctype html public "-//W3C//DTD HTML 4.0 Frameset//EN">
<html>
<head>
<title> Test suite for JpGraph</title>
<script type="text/javascript" language="javascript">
<!--
function resize()
{
	return true;
}
//-->
</script>
</head>
<frameset rows="*,*" onLoad="resize()">
    <?php 
    // Verifica se o target não contém "csim"
    if (!strstr($target, "csim")) {
        echo '<frame src="show-image.php?target=' . htmlspecialchars($safeTarget, ENT_QUOTES, 'UTF-8') . '" name="image">';
    } else {
        echo '<frame src="' . htmlspecialchars($safeTarget, ENT_QUOTES, 'UTF-8') . '" name="image">';
    }
    ?>
    <frame src="show-source.php?target=<?php echo htmlspecialchars($safeTarget, ENT_QUOTES, 'UTF-8'); ?>" name="source">
</frameset>
</html>
