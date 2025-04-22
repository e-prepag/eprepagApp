<?php
require_once '/www/includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";

$nome_operador = $_SESSION["userlogin_bko"];
?>
<link href="https://cdn.datatables.net/v/dt/dt-1.13.4/datatables.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/v/dt/dt-1.13.5/datatables.min.js"></script>

<style>
    .container-filtros {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .col-filtro {
        flex: 1;
        min-width: 150px;
    }
</style>

<div class="bottom10">
    <h1 class="titulo-solicitacoes">Consulta de Pagamentos PIX</h1>
    <div id="form" class="form-solicitacoes">
        <div class="container-filtros">
            <div class="col-filtro">
                <label for="dt_inicial">Data inicial</label>
                <input type="date" id="dt_inicial" class="form-control" value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div class="col-filtro">
                <label for="dt_final">Data final</label>
                <input type="date" id="dt_final" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-filtro">
                <label for="tipo">Tipo</label>
                <select id="tipo" class="form-control">
                    <option value="todos">Todos</option>
                    <option value="pf">Pessoa Física</option>
                    <option value="pdv">PDV</option>
                </select>
            </div>
        </div>
        <div class="d-flex top10">
            <button type="button" class="btn btn-success btn-filtrar">Filtrar</button>
            <button id="exportCSV" type="button" class="btn btn-info">Baixar CSV</button>
        </div>
    </div>
</div>

<div style="overflow-x: auto;">
    <table id="table" class="display compact hover stripe cell-border" style="width:100%;text-align: center;">
        <thead>
            <tr>
                <th>Data</th>
                <th>Id Cliente</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Nº Compra</th>
                <th>CPF/CNPJ Cadastro</th>
                <th>Nome Cadastro</th>
                <th>CPF/CNPJ Pagador</th>
                <th>Nome Pagador</th>
                <!-- <th>Chave PIX Pagadora</th> -->
                <th>Cliente Nome</th>
        <th>Id Pagto</th>
        <th>Id Venda</th>
       
     
            </tr>
        </thead>
    </table>
</div>

<script>
    $(document).ready(function () {
        let jsonData = [];

        $(".btn-filtrar").on("click", function () {
            let dt_inicial = $("#dt_inicial").val();
            let dt_final = $("#dt_final").val();
            let tipo = $("#tipo").val();

            if (!dt_inicial || !dt_final) {
                Swal.fire("Erro!", "As datas inicial e final são obrigatórias!", "error");
                return;
            }

            Swal.fire({
                title: 'Carregando...',
                html: 'Aguarde enquanto os dados estão sendo carregados.',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            let ajaxUrl = `ajax_log_pix.php?data_inicio=${dt_inicial}&data_fim=${dt_final}&tipo=${tipo}`;

            let table = $('#table').DataTable({
                ajax: {
        url: ajaxUrl,
        dataSrc: function (json) {
            jsonData = json.data; // <-- aqui você salva os dados
            return json.data;
        },
        complete: function () {
            Swal.close();
        },
        error: function (xhr, error, code) {
            console.error("Erro na requisição AJAX:", xhr.responseText);
            Swal.fire("Erro!", "Houve um problema ao carregar os dados.", "error");
        }
    },
    cache: false,
    order: [[0, 'desc']],
    columns: [
        {
    data: 'data',
    render: function (data, type, row) {
        if (!data) return '';
        const dt = new Date(data);
        const dia = String(dt.getDate()).padStart(2, '0');
        const mes = String(dt.getMonth() + 1).padStart(2, '0');
        const ano = dt.getFullYear();
        const hora = String(dt.getHours()).padStart(2, '0');
        const min = String(dt.getMinutes()).padStart(2, '0');
        const seg = String(dt.getSeconds()).padStart(2, '0');
        return `${dia}/${mes}/${ano} ${hora}:${min}:${seg}`;
    }
},
        { data: 'idcliente' },
        { 
    data: 'total',
    render: function (data, type, row) {
        if (!data) return 'R$ 0,00';
      //  const valor = parseFloat(data);
        const valor = parseFloat(data) / 100; // <-- divide por 100 aqui
        return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
},
        { data: 'tipo' },
        { data: 'numcompra' },
        { 
    data: 'cpf_cnpj_cadastro',
    render: function (data, type, row) {
        return data === '99999999999' ? 'N/A' : data;
    }
},
        { data: 'nome_cadastro' },
      
        { 
    data: 'cpf_cnpj_pagador',
    render: function (data, type, row) {
        return data === '99999999999' ? 'N/A' : data;
    }
},
        { data: 'nome_pagador' },
        // ? Novos campos JSON
     
    { data: 'cliente_nome' },
    { data: 'idpagto' },
    { data: 'idvenda' },
    // { data: 'location' }
    ],
    destroy: true,
    language: {
        "zeroRecords": "Nenhum registro encontrado",
        "lengthMenu": "Mostrar _MENU_ registros",
        "info": "Mostrando página _PAGE_ de _PAGES_",
        "infoEmpty": "Nenhum dado disponível",
        "infoFiltered": "(filtrado de _MAX_ registros)",
        "sSearch": "Pesquisar",
        "paginate": {
            "previous": "Anterior",
            "next": "Próximo"
        }
    }
});


        });

        $("#exportCSV").on("click", function () {
            if (jsonData.length === 0) {
                Swal.fire("Aviso!", "Nenhum dado para exportar.", "warning");
                return;
            }

            let csv = convertToCSV(jsonData);
            let blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
            let url = URL.createObjectURL(blob);
            let link = document.createElement("a");
            let fileName = `pagamentos_pix_${getCurrentTimestamp()}.csv`;

            link.setAttribute("href", url);
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            Swal.fire("Download realizado!", "", "success");
        });

        function convertToCSV(data) {
            if (data.length === 0) return '';
            const headers = Object.keys(data[0]).join(',') + '\n';
            const rows = data.map(row => Object.values(row).map(value => `"${value}"`).join(',')).join('\n');
            return headers + rows;
        }

        function getCurrentTimestamp() {
            let now = new Date();
            let dd = String(now.getDate()).padStart(2, '0');
            let mm = String(now.getMonth() + 1).padStart(2, '0');
            let yy = String(now.getFullYear()).slice(-2);
            let hh = String(now.getHours()).padStart(2, '0');
            let min = String(now.getMinutes()).padStart(2, '0');
            return `${dd}${mm}${yy}_${hh}${min}`;
        }
    });
</script>

<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>