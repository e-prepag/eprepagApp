(function () {
  'use strict';

  var config = window.efinanceiraInterativaConfig || {};
  var endpoint = config.endpoint || 'processar_interativo.php';
  var storageKey = 'efinanceira_processamento_interativo';
  var processamentoAtivo = false;
  var ticketAtual = null;
  var relogioId = null;
  var ultimaMensagemErro = '';

  function postBody(dados) {
    var body = new URLSearchParams();
    Object.keys(dados).forEach(function (chave) {
      body.append(chave, dados[chave] == null ? '' : String(dados[chave]));
    });
    body.append('csrf_token', config.csrfToken || '');
    return body;
  }

  async function postJson(dados) {
    var response = await fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: postBody(dados).toString()
    });
    var retorno = await response.json();
    if (!response.ok || !retorno.sucesso) {
      throw new Error(retorno.mensagem || 'Falha na comunicacao com o servidor.');
    }
    return retorno;
  }

  function conteudoPainel() {
    return '' +
      '<div class="efin-processamento">' +
        '<div class="alert alert-warning">' +
          '<strong>Importante:</strong> mantenha esta pagina aberta ate o processamento terminar.' +
        '</div>' +
        '<div class="progress">' +
          '<div id="efinBarraProgresso" class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" style="width: 100%"></div>' +
        '</div>' +
        '<div class="efin-processamento-resumo">' +
          '<div class="efin-processamento-item"><strong id="efinEventos">0</strong><span>EVENTOS</span></div>' +
          '<div class="efin-processamento-item"><strong id="efinArquivos">0</strong><span>ARQUIVOS XML</span></div>' +
          '<div class="efin-processamento-item"><strong id="efinTempo">00:00</strong><span>TEMPO DECORRIDO</span></div>' +
        '</div>' +
        '<div id="efinMensagem" class="efin-processamento-status">Preparando o processamento...</div>' +
        '<div class="efin-processamento-conexao">' +
          '<span class="glyphicon glyphicon-transfer"></span><span id="efinConexao">Conectando ao servidor...</span>' +
        '</div>' +
      '</div>';
  }

  function abrirPainel() {
    Swal.fire({
      title: 'Gerando lotes da e-Financeira',
      html: conteudoPainel(),
      icon: 'info',
      width: 620,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: function () {
        Swal.showLoading();
        atualizarEstado(ticketAtual && ticketAtual.estado ? ticketAtual.estado : {});
        atualizarRelogio();
      }
    });
  }

  function textoFase(fase) {
    var textos = {
      preparando: 'Preparando os arquivos...',
      gerando_xml: 'Gerando e salvando os arquivos XML...',
      compactando: 'Compactando os arquivos XML...',
      concluido: 'Processamento concluido.'
    };
    return textos[fase] || 'Processando os lotes...';
  }

  function atualizarTexto(id, valor) {
    var elemento = document.getElementById(id);
    if (elemento) {
      elemento.textContent = valor;
    }
  }

  function formatarNumero(valor) {
    return Number(valor || 0).toLocaleString('pt-BR');
  }

  function atualizarEstado(estado, mensagem) {
    if (!estado) {
      estado = {};
    }

    atualizarTexto('efinEventos', formatarNumero(estado.eventos_processados));
    atualizarTexto('efinArquivos', formatarNumero(estado.arquivos_gerados));
    atualizarTexto('efinMensagem', mensagem || textoFase(estado.fase));

    var barra = document.getElementById('efinBarraProgresso');
    if (!barra) {
      return;
    }

    if (estado.fase === 'compactando' && Number(estado.arquivos_gerados) > 0) {
      var percentual = Math.min(100, Math.round((Number(estado.arquivos_compactados || 0) / Number(estado.arquivos_gerados)) * 100));
      barra.style.width = percentual + '%';
      barra.classList.remove('active');
      barra.textContent = percentual + '%';
    } else {
      barra.style.width = '100%';
      barra.classList.add('active');
      barra.textContent = '';
    }
  }

  function atualizarConexao(texto) {
    atualizarTexto('efinConexao', texto);
  }

  function atualizarRelogio() {
    if (!ticketAtual) {
      return;
    }
    var inicio = Number(ticketAtual.iniciadoEm || Date.now());
    var segundos = Math.max(0, Math.floor((Date.now() - inicio) / 1000));
    var horas = Math.floor(segundos / 3600);
    var minutos = Math.floor((segundos % 3600) / 60);
    var resto = segundos % 60;
    var partes = [];
    if (horas > 0) {
      partes.push(String(horas).padStart(2, '0'));
    }
    partes.push(String(minutos).padStart(2, '0'));
    partes.push(String(resto).padStart(2, '0'));
    atualizarTexto('efinTempo', partes.join(':'));
  }

  function iniciarRelogio() {
    if (relogioId) {
      clearInterval(relogioId);
    }
    relogioId = setInterval(atualizarRelogio, 1000);
  }

  function pararRelogio() {
    if (relogioId) {
      clearInterval(relogioId);
      relogioId = null;
    }
  }

  function salvarTicket() {
    if (ticketAtual) {
      localStorage.setItem(storageKey, JSON.stringify(ticketAtual));
    }
  }

  function limparTicket() {
    localStorage.removeItem(storageKey);
  }

  function aguardar(milisegundos) {
    return new Promise(function (resolve) {
      setTimeout(resolve, milisegundos);
    });
  }

  function tratarEvento(evento) {
    if (evento.estado) {
      ticketAtual.estado = evento.estado;
      salvarTicket();
      atualizarEstado(evento.estado, evento.mensagem);
    } else if (evento.mensagem) {
      atualizarTexto('efinMensagem', evento.mensagem);
    }

    if (evento.tipo === 'erro') {
      ultimaMensagemErro = evento.mensagem || 'O servidor informou uma falha durante a geracao.';
    }

    if (evento.tipo === 'heartbeat' || evento.tipo === 'progresso' || evento.tipo === 'fase' || evento.tipo === 'inicio') {
      atualizarConexao('Conexao ativa. Progresso salvo no servidor.');
    } else if (evento.tipo === 'ocupado') {
      atualizarConexao('Aguardando a etapa que ja esta em execucao...');
    }

    return evento.tipo;
  }

  async function consumirStream() {
    var response = await fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Accept': 'application/x-ndjson'
      },
      body: postBody({
        acao: 'processar',
        ticket_id: ticketAtual.ticketId,
        token: ticketAtual.token
      }).toString()
    });

    if (!response.ok) {
      var erroTexto = await response.text();
      try {
        var erroJson = JSON.parse(erroTexto);
        throw new Error(erroJson.mensagem || 'Falha ao processar o ticket.');
      } catch (erroParse) {
        if (erroParse instanceof SyntaxError) {
          throw new Error(erroTexto || 'Falha ao processar o ticket.');
        }
        throw erroParse;
      }
    }

    if (!response.body || !response.body.getReader) {
      throw new Error('Este navegador nao suporta o acompanhamento progressivo.');
    }

    var reader = response.body.getReader();
    var decoder = new TextDecoder('utf-8');
    var buffer = '';
    var ultimoTipo = '';

    while (true) {
      var leitura = await reader.read();
      buffer += decoder.decode(leitura.value || new Uint8Array(), { stream: !leitura.done });
      var linhas = buffer.split('\n');
      buffer = linhas.pop();

      linhas.forEach(function (linha) {
        linha = linha.trim();
        if (!linha) {
          return;
        }
        ultimoTipo = tratarEvento(JSON.parse(linha));
      });

      if (leitura.done) {
        break;
      }
    }

    if (buffer.trim()) {
      ultimoTipo = tratarEvento(JSON.parse(buffer.trim()));
    }
    return ultimoTipo;
  }

  async function consultarTicket() {
    return postJson({
      acao: 'status',
      ticket_id: ticketAtual.ticketId,
      token: ticketAtual.token
    });
  }

  async function executarLoop() {
    while (processamentoAtivo) {
      try {
        atualizarConexao('Conectando ao servidor...');
        var ultimoTipo = await consumirStream();

        if (ultimoTipo === 'concluido') {
          concluirProcessamento();
          return;
        }
        if (ultimoTipo === 'erro') {
          falharProcessamento(ultimaMensagemErro || 'O servidor informou uma falha durante a geracao.');
          return;
        }

        await aguardar(ultimoTipo === 'ocupado' ? 5000 : 600);
      } catch (erro) {
        atualizarConexao('Conexao interrompida. Tentando retomar automaticamente...');
        await aguardar(3000);

        try {
          var status = await consultarTicket();
          if (status.estado) {
            ticketAtual.estado = status.estado;
            salvarTicket();
            atualizarEstado(status.estado);
          }
          if (status.status === 'CONCLUIDO') {
            concluirProcessamento();
            return;
          }
          if (status.status === 'ERRO') {
            falharProcessamento(status.mensagem || erro.message);
            return;
          }
        } catch (erroStatus) {
          if (erroStatus.message.indexOf('Sessao expirada') !== -1 || erroStatus.message.indexOf('Acesso negado') !== -1) {
            falharProcessamento(erroStatus.message);
            return;
          }
        }
      }
    }
  }

  function concluirProcessamento() {
    processamentoAtivo = false;
    pararRelogio();
    habilitarBotao(true);

    Swal.fire({
      title: 'Download pronto!',
      text: 'Os lotes foram gerados e compactados com sucesso.',
      icon: 'success',
      confirmButtonText: 'Baixar arquivo ZIP',
      allowOutsideClick: false
    }).then(function (resultado) {
      if (resultado.isConfirmed) {
        baixarArquivo();
      }
    });
  }

  function falharProcessamento(mensagem) {
    processamentoAtivo = false;
    pararRelogio();
    limparTicket();
    habilitarBotao(true);
    Swal.fire('Erro', mensagem || 'Nao foi possivel gerar os lotes.', 'error');
  }

  function baixarArquivo() {
    if (!ticketAtual) {
      return;
    }

    var iframe = document.getElementById('efinDownloadFrame');
    if (!iframe) {
      iframe = document.createElement('iframe');
      iframe.id = 'efinDownloadFrame';
      iframe.name = 'efinDownloadFrame';
      iframe.style.display = 'none';
      document.body.appendChild(iframe);
    }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = endpoint;
    form.target = iframe.name;
    form.style.display = 'none';

    var campos = {
      acao: 'baixar',
      ticket_id: ticketAtual.ticketId,
      token: ticketAtual.token,
      csrf_token: config.csrfToken || ''
    };
    Object.keys(campos).forEach(function (nome) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = nome;
      input.value = campos[nome];
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    limparTicket();
  }

  function habilitarBotao(habilitar) {
    var botao = document.getElementById('btnGerarInterativo');
    if (botao) {
      botao.disabled = !habilitar;
    }
  }

  window.iniciarGeracaoInterativa = async function () {
    if (processamentoAtivo) {
      return;
    }

    var dataInicial = document.getElementById('dt_inicial').value;
    var dataFinal = document.getElementById('dt_final').value;
    var tipoDoc = document.getElementById('tipo_doc').value;
    var cpfCnpj = document.getElementById('cpfcnpj').value;

    processamentoAtivo = true;
    habilitarBotao(false);

    try {
      var retorno = await postJson({
        acao: 'solicitar',
        data_inicial: dataInicial,
        data_final: dataFinal,
        tipo_doc: tipoDoc,
        cpfcnpj: cpfCnpj
      });

      ticketAtual = {
        ticketId: retorno.ticket_id,
        token: retorno.token,
        estado: retorno.estado || {},
        iniciadoEm: Date.now()
      };
      salvarTicket();
      abrirPainel();
      iniciarRelogio();
      executarLoop();
    } catch (erro) {
      falharProcessamento(erro.message);
    }
  };

  async function tentarRetomar() {
    var salvo = localStorage.getItem(storageKey);
    if (!salvo) {
      return;
    }

    try {
      ticketAtual = JSON.parse(salvo);
      if (!ticketAtual || !ticketAtual.ticketId || !ticketAtual.token) {
        limparTicket();
        return;
      }

      var status = await consultarTicket();
      if (status.status === 'ERRO') {
        limparTicket();
        Swal.fire('Erro', status.mensagem || 'O processamento anterior terminou com erro.', 'error');
        return;
      }

      ticketAtual.estado = status.estado || ticketAtual.estado || {};
      if (status.status === 'CONCLUIDO') {
        concluirProcessamento();
        return;
      }

      processamentoAtivo = true;
      habilitarBotao(false);
      salvarTicket();
      abrirPainel();
      iniciarRelogio();
      executarLoop();
    } catch (erro) {
      limparTicket();
    }
  }

  window.addEventListener('beforeunload', function (evento) {
    if (!processamentoAtivo) {
      return;
    }
    evento.preventDefault();
    evento.returnValue = '';
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tentarRetomar);
  } else {
    tentarRetomar();
  }
})();
