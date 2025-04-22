<?php
require_once('C:/Sites/E-Prepag/db/connect.php');

//Conectando ao Banco de dados
$con=pg_connect("host=$host port=$port dbname=$banco user=$usuario password=$senha");

$sql = "SELECT * FROM tb_lans ORDER BY nome ASC;";
$rss = pg_query($con,$sql);
$tot = pg_num_rows($rss);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
<head>
	<title>Teste Grid with Table Pager</title>
	<link rel="stylesheet" href="css/jq.css" type="text/css" media="print, projection, screen" />
	<link rel="stylesheet" href="../themes/blue/style.css" type="text/css" media="print, projection, screen" />
	<script type="text/javascript" src="../jquery-latest.js"></script>
	<script type="text/javascript" src="../jquery.tablesorter.js"></script>
	<script type="text/javascript" src="../addons/pager/jquery.tablesorter.pager.js"></script>
	<script type="text/javascript" src="js/chili/chili-1.8b.js"></script>
	<script type="text/javascript" src="js/docs.js"></script>
	<script type="text/javascript">
	$(document).ready(function() { 
		// call the tablesorter plugin 
		$("table").tablesorter({ 
			// sort on the first column and third column, order asc 
			sortList: [[1,0]],
			
			headers: { 
				// assign the zero column (we start counting zero) 
				0: { 
					// disable it by setting the property sorter to false 
					sorter: false 
				}, 
				
				4: { 
					sorter: false 
				}, 
				
				5: { 
					sorter: false 
				}, 
				
				6: { 
					sorter: false 
				} 
	        }


		}); 
	}); 

	$(function() {
		$("table")
			.tablesorter({widthFixed: true, widgets: ['zebra']})
			.tablesorterPager({container: $("#pager")});
	});
	</script>
</head>
<body>
<div id="main">
  <table cellspacing="1" class="tablesorter">
  <thead>
		<tr align="center">
			<th>idlan</th>
			<th>Nome</th>
			<th>Endereço</th>
			<th>Padrinho</th>
			<th>Latitude</th>
			<th>Longitude</th>
			<th>UF</th>

		</tr>
	</thead>
	<tfoot>
		<tr align="center">
			<th>idlan</th>
			<th>Nome</th>
			<th>Endereço</th>
			<th>Padrinho</th>
			<th>Latitude</th>
			<th>Longitude</th>
			<th>UF</th>

		</tr>
	</tfoot>
	<tbody>
		<?php
        if($tot > 0) {
            while($vlr = pg_fetch_array($rss)) {
		        ?>		
                <tr>
                    <td><nobr><?php echo utf8_decode($vlr['idlan']);?></nobr></td>
                    <td><nobr><?php echo utf8_decode($vlr['nome']);?></nobr></td>
                    <td><nobr><?php echo utf8_decode($vlr['endereco']);?></nobr></td>
        
                    <td><nobr><?php echo utf8_decode($vlr['padrinho']);?></nobr></td>
                    <td><nobr><?php echo utf8_decode($vlr['lat']);?></nobr></td>
                    <td><nobr><?php echo utf8_decode($vlr['lon']);?></nobr></td>
                    <td><nobr><?php echo utf8_decode($vlr['uf']);?></nobr></td>
                </tr>
				<?php
			}
		}
		?>
	</tbody>
</table>
<div id="pager" class="pager">
	<form>
		<img src="../addons/pager/icons/first.png" class="first"/>
		<img src="../addons/pager/icons/prev.png" class="prev"/>
		<input type="text" class="pagedisplay"/>
		<img src="../addons/pager/icons/next.png" class="next"/>
		<img src="../addons/pager/icons/last.png" class="last"/>
		<select class="pagesize">
			<option value="<?php echo $tot;?>">TODOS</option>
            <option selected="selected"  value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option  value="40">40</option>
		</select>
	</form>
</div>
</div>
</body>
</html>

