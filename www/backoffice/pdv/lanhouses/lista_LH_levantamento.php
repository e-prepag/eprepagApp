<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

$sql = "SELECT * FROM tb_lans ORDER BY nome ASC;";
$rss = SQLexecuteQuery($sql);
$tot = pg_num_rows($rss);
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12 top20 txt-preto fontsize-p">
    <table class="table table-bordered">
        <thead>
        <tr>
          <th><span class="style1">n</span></th>
          <th><span class="style1">Nome</span></th>
          <th><span class="style1">E-Mail</span></th>
          <th><span class="style1">Endere&ccedil;o</span></th>
          <th><span class="style1">UF</span></th>
          <th align="center"><span class="style1">Telefone</span></th>
        </tr>
    </thead>
    <tbody>

    <?php
      if($tot >0) {
          $i_row = 1; 
         while($valores = pg_fetch_array($rss)) {
            ?>
              <tr>
                <td align="center"><?php echo ($i_row++);?></td>
                <td><?php echo $valores['nome'];?></td>
                <td><?php echo $valores['email'];?></td>
                <td><?php echo $valores['endereco'];?></td>
                <td><?php echo $valores['uf'];?></td>
                <td align="center"><?php echo $valores['telefone'];?></td>
              </tr>    
            <?php  
         }
      }
    ?>
    </tbody>
    </table>
    </div>
  <?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>