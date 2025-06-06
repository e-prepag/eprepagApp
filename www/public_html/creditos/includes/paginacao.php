<?php
            if(isset($paginacao['paginas']))
            {

?>               
            <form id="formPaginacao" method="post">
<?php
                if(!empty($paginacao['iptHidden']))
                {
                    foreach ($paginacao['iptHidden'] as $id => $val)
                    {
?>
                    <input type="hidden" name="hidden_<?php echo $id;?>" id="<?php echo $id;?>" value="<?php echo $val;?>">
<?php                
                    }
                }
?>               
                <input type="hidden" name="p" id="p" value="">
                <input type="hidden" name="inicial" id="inicial" value="<?php echo $inicial;?>">

                <nav class="text-center">
                    <ul class="pagination">
<?php
                        if(isset($paginacao['ponteiros']['first']))
                        {
?>
                        <li>
                            <a href="#" page="1" class="paginacao" title="Primeira página" aria-label="Primeira página">
                            <span aria-hidden="true">l<</span>
                          </a>
                        </li>
<?php
                        }
                        if(isset($paginacao['ponteiros']['prev']))
                        {
?>
                        <li>
                          <a href="#" page="<?php echo $paginacao['paginaAtual']-1; ?>" title="Página anterior" class="paginacao" aria-label="Página anterior">
                            <span aria-hidden="true"><</span>
                          </a>
                        </li>
<?php
                        }
                        foreach($paginacao['paginas'] as $val)
                        {
                            $cssClass = ($paginacao['paginaAtual'] == $val) ? "btn-simples-selecionado" : "";
                            echo "<li><a href='#' page='{$val}' title='Página {$val}' class='paginacao {$cssClass}'>{$val}</a></li>";
                        }
                        if(isset($paginacao['ponteiros']['next']))
                        {
?>
                        <li>
                          <a href="#" page="<?php echo $paginacao['paginaAtual']+1; ?>" class="paginacao" title="Próxima página" aria-label="Próxima página">
                            <span aria-hidden="true">></span>
                          </a>
                        </li>
<?php
                        }
                        if(isset($paginacao['ponteiros']['last']))
                        {
?>
                        <li>
                          <a href="#" page="<?php echo ceil($paginacao['totalTable']/$paginacao['limit']); ?>" class="paginacao" title="Última página" aria-label="Última página">
                            <span aria-hidden="true">>l</span>
                          </a>
                        </li>
<?php
                        }
?>
                    </ul>
                </nav>
            </form>
            <script>
                $(".paginacao").click(function(){
                    var inicial = $(this).attr("page");
                    var totalPagina = <?php echo $paginacao['limit']; ?>;
                    $("#inicial").val((inicial * totalPagina) - totalPagina);
                    
                    $("#p").val(inicial);
                        
                    $("#formPaginacao").submit();
                });
            </script>
<?php
            }
?>