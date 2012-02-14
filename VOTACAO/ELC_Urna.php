<?php
/*
Copyright 2011 da UFRGS - Universidade Federal do Rio Grande do Sul

Este arquivo é parte do programa SAELE - Sistema Aberto de Eleições Eletrônicas.

O SAELE é um software livre; você pode redistribuí-lo e/ou modificá-lo dentro dos
termos da Licença Pública Geral GNU como publicada pela Fundação do Software Livre
(FSF); na versão 2 da Licença.

Este programa é distribuído na esperança que possa ser útil, mas SEM NENHUMA GARANTIA;
sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO ou APLICAÇÃO EM PARTICULAR.
Veja a Licença Pública Geral GNU/GPL em português para maiores detalhes.

Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título "LICENCA.txt",
junto com este programa, se não, acesse o Portal do Software Público Brasileiro no
endereço www.softwarepublico.gov.br ou escreva para a Fundação do Software Livre(FSF)
Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA
*/
                       // Página para votação
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

require("Votacao_Common.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Urna de Votação</title>
<link rel="stylesheet" title="Eleições" href="../ELEICAO.CSS">
</head>

<body>

<?php
$xajax->printJavascript('../xajax/');

try {
    $Concurso = $Controlador->recuperaConcursoVotacao();
    $Eleicao = $Controlador->recuperaEleicaoVotacao();
}
catch(ControladorException $e) { ?>
    <div class="Erro">
        <p><strong>Erro:</strong> <?=$e->getMessage()?></p>
        <a href="../ELC_Logout.php">Voltar</a>
    </div>
    </body>
    </html>
    <?php
    exit;
}

$Controlador->inicializaVetorCedula();
$Controlador->registraNrVotoAtual(1);

$Eleicao->geraLogOperacao(DESCRICAO_ACESSOVOTACAO);

$NrVotos = $Eleicao->get("nrpossibilidades");
$NrDigitos = $Eleicao->get("nrdigitoschapa");
?>
<form name="FormCedula" id="FormCedula" action="" method="POST">

<table width="100%" border="0" cellspacing="8" cellpadding="0">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td class="UrnaCabecalho">
            <strong><?=$Concurso->retornaString(STR_CONCURSOELEITORAL)?>:</strong>
            <?=$Concurso->get("descricao")?>
          </td>
        </tr>
        <tr>
          <td class="UrnaCabecalho">
            <strong><?=$Concurso->retornaString(STR_ELEICAO)?>:</strong>
            <?=$Eleicao->get("descricao")?>
          </td>
        </tr>
      </table>
      <table width="100%" border="1" cellspacing="0" cellpadding="0">
        <tr>
          <td width="65%" valign="top"> <!-- Tela de votação -->
            <table width="100%" border="0" cellspacing="2" cellpadding="0">
              <tr>
                <td align="center" valign="top" width="35%">
                  <?php if($NrVotos > 1) { ?>
                  <font size="2" face="verdana">Voto nº 1:</font><br />
                  <?php } else echo "<br />";?>
                  <input type="text" name="campoCedula" id="campoCedula" size="1" maxlength="<?=$NrDigitos?>" class="CampoCedula" value="" onKeyPress="javascript: return(ValidaTeclado(event));" onKeyUp="javascript: xajax_VerificaVoto(xajax.getFormValues('FormCedula'));" />
                  <br /> <br />
                  <div id="Lotacao" style="height: 150px; font-size: 10pt; font-family: verdana; visibility: hidden;">
                    <div id="CabecalhoLotacao" style="text-align: center; font-weight: bold;">
                      Lotação:
                    </div>
                    <div id="ItensLotacao" style="text-align: left;">
                    </div>
                  </div>
                </td>
                <td align="center" valign="top" width="75%">
                  <img height="150" name="Imagem" id="FotoCandidatoNada" src="../nada.GIF" />
                  <img height="150" name="Imagem" id="FotoPadrao" src="../FOTOS/FotoPadrao.jpg" style="display: none;" />
                    <?php
                    $Chapas = $Eleicao->devolveChapas();
                    foreach($Chapas as $CodChapa => $Chapa) {
                        $NomeArqChapa = "../FOTOS/CHAPA_".$Concurso->getChave()."_".$Eleicao->getChave()."_".$Chapa->getChave().".JPG";
                        if(file_exists($NomeArqChapa))
                            echo '<img height="150" name="Imagem" id="FotoChapa'.$CodChapa.'" src="'.$NomeArqChapa.'" style="display: none;">';
                        $Candidatos = $Chapa->devolveCandidatos();
                        $c = 1;
                        foreach($Candidatos as $Candidato) {
                            $NomeArqCand = "../FOTOS/CAND_".$Concurso->getChave()."_".$Eleicao->getChave()."_".$Chapa->getChave()."_".$Candidato->get("codpessoaeleicao").".JPG";
                            if(file_exists($NomeArqCand)) {
                                echo '<img height="150" name="Imagem" id="FotoCandidato'.$CodChapa.'_'.$c.'" src="'.$NomeArqCand.'" style="display: none;">';
                                $c++;
                            }
                        }
                    }
                    ?>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <div style="display: block;" id="DivChapa">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td colspan="3" align="center" class="Chapa">
                        Aguarde...
                      </td>
                    </tr>
                  </table>
                  </div>
                </td>
              </tr>
            </table>
          </td>
          <td width="35%" bgcolor="#d3d3d3"> <!-- Teclado eletrônico -->
            <table width="100%" border="0" cellspacing="0" cellpadding="5">
              <tr>
                <td align="center" colspan="3">
                  <table width="75%" border="0" cellspacing="2" cellpadding="2">
                    <tr>
                      <td align="center">
                        <button type="button" class="BotaoUrna" onClick="javascript: EntraNumero(7);"> 7 </button>
                      </td>
                      <td align="center">
                        <button type="button" class="BotaoUrna" onClick="javascript: EntraNumero(8);"> 8 </button>
                      </td>
                      <td align="center">
                        <button type="button" class="BotaoUrna" onClick="javascript: EntraNumero(9);"> 9 </button>
                      </td>
                    </tr>
                    <tr>
                      <td align="center">
                        <button type="button" class="BotaoUrna" onClick="javascript: EntraNumero(4);"> 4 </button>
                      </td>
                      <td align="center">
                        <button type="button" class="BotaoUrna" onClick="javascript: EntraNumero(5);"> 5 </button>
                      </td>
                      <td align="center">
                        <button type="button" class="BotaoUrna" onClick="javascript: EntraNumero(6);"> 6 </button>
                      </td>
                    </tr>
                    <tr>
                      <td align="center">
                        <button type="button" class="BotaoUrna" onClick="javascript: EntraNumero(1);"> 1 </button>
                      </td>
                      <td align="center">
                        <button type="button" class="BotaoUrna" onClick="javascript: EntraNumero(2);"> 2 </button>
                      </td>
                      <td align="center">
                        <button type="button" class="BotaoUrna" onClick="javascript: EntraNumero(3);"> 3 </button>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="3" align="center">
                        <button type="button" class="BotaoUrna" onClick="javascript: EntraNumero(0);"> 0 </button>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td align="center" valign="middle">
                  <button type="button" class="BotaoBranco" onClick="javascript: Branco();">BRANCO</button>
                </td>
                <td align="center" valign="middle">
                  <button type="button" class="BotaoNulo" onClick="javascript: Nulo();">NULO</button>
                </td>
                <td align="center" valign="middle">
                  <button type="button" class="BotaoCorrige" onClick="javascript: Corrige();">CORRIGE</button>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <?php if ($NrVotos > 1) { ?>
        <tr>
          <td class="Navegacao" width="50%">
					  <div id="BotaoVotoAnterior">PRIMEIRO VOTO</div>
          </td>
          <td class="Navegacao" width="50%">
					  <div id="BotaoVotoPosterior"><input type="button" name="botao" value="PR&Oacute;XIMO VOTO &gt;&gt; (ENTER)" onClick="javascript: xajax_Acao(xajax.getFormValues('FormCedula'), 'P');" /></div>
          </td>
        </tr>
        <?php } ?>
      </table>
    </td>
  </tr>
  <?php if ($NrVotos > 1) {
    $Str1 = "ENCERRAR VOTA&Ccedil;&Atilde;O";
    $Str2 = "todos os votos";
      ?>
      <tr>
        <td>
          <table border="0" width="97%" cellspacing="0" cellpadding="0" align="center">
            <tr>
              <td colspan="2" align="center">
                          <input type="button" style="background-color: #ffb5a5; width: 2.4em;" name="botao[1]" id="botao_voto_1" value=" " onClick="javascript: xajax_Acao(xajax.getFormValues('FormCedula'), 1);" />
                <?php
                for ($i = 2; $i <= $NrVotos; $i++)
                  echo ' - <input type="button" style="background-color: #ffffe5; width: 2.4em;" name="botao['.$i.']" id="botao_voto_'.$i.'" value=" " onclick="javascript: xajax_Acao(xajax.getFormValues(\'FormCedula\'), '.$i.');" />';
                ?>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <?php
  }
  else {
    $Str1 = "CONFIRMA";
    $Str2 = "o voto";
  } ?>
  <tr>
    <td>
      <div align="center">
      <button type="button" id="BotaoEncerra" class="BotaoEncerra" onClick="javascript: xajax_Acao(xajax.getFormValues('FormCedula'), 'C');"> .:: <?=$Str1?> ::. </button><br />
      </div>
    </td>
  </tr>
  <tr>
    <td align="center">
      <a href="javascript: void(0);" onClick="javascript: window.open('ELC_Mostra_Candidatos.php', 'klj', 'status=0,scrollbars=yes,width=760,height=560');"><font size="3" face="verdana"><b>CLIQUE AQUI PARA VISUALIZAR A LISTA DE <?=strtoupper($Concurso->retornaString(STR_CHAPAS))?></b></font></a>
    </td>
  </tr>
</table>
<br />
<div align="center">
<a href="../ELC_Logout.php"><font size="2" face="verdana">Cancelar</font></a>
</div>

<script>
document.getElementById('campoCedula').focus();

xajax_VerificaVoto(xajax.getFormValues('FormCedula'));

function DeselecionaCandidato() {
    AtualizaCor(0);
    document.getElementById('Lotacao').style.visibility = 'hidden';
    document.getElementById('ItensLotacao').innerHTML = null;
}

function EscondeFotos() {
    imagens = document.getElementsByName('Imagem');
    for(i = 0; i < imagens.length; i++)
        imagens[i].style.display = 'none';
}

function MostraFoto(id) {
    if(el = document.getElementById(id)) {
        el.style.display = 'block';
        return true;
    }
    else return false;
}

function DefineImagemNada() {
    EscondeFotos();
    MostraFoto('FotoCandidatoNada');
}

function DefineImagemPadrao() {
    EscondeFotos();
    MostraFoto('FotoPadrao');
}

function DefineImagemChapa(Chapa) {
    EscondeFotos();
    if(MostraFoto('FotoChapa'+Chapa)) {
        return true;
    }
    else if(MostraFoto('FotoCandidato'+Chapa+'_1')) {
        return true;
    }
    else {
        DefineImagemPadrao();
    }
}

function DefineImagemCandidato(Chapa, Cand) {
    EscondeFotos();
    if(MostraFoto('FotoCandidato'+Chapa+'_'+Cand)) {
        return true;
    }
    else DefineImagemPadrao();
}

function AtualizaCor(Indice) {
  for(i = 1; el = document.getElementById('Cand'+i); i++)
    if(i == Indice) el.style.background = 'rgb(255, 255, 80)';
               else el.style.background = 'rgb(245, 245, 245)';
}

function EntraNumero(num) {
  el = document.getElementById('campoCedula');
  if(el.value.length < el.maxLength && el.value != 'N' && el.value != 'B' && (num != 0 || el.value.length > 0))
    el.value += num;
  xajax_VerificaVoto(xajax.getFormValues('FormCedula'));
}

  function Branco() {
    el = document.getElementById('campoCedula');
    if(el.value.length < 1) el.value = "B";
    xajax_VerificaVoto(xajax.getFormValues('FormCedula'));
  }

  function Nulo() {
    el = document.getElementById('campoCedula');
    if(el.value.length < 1) el.value = "N";
    xajax_VerificaVoto(xajax.getFormValues('FormCedula'));
  }

  function Corrige() {
    document.getElementById('campoCedula').value = "";
    DeselecionaCandidato();
    DefineImagemNada();
    xajax_VerificaVoto(xajax.getFormValues('FormCedula'));
  }

  function ValidaTeclado(event) {
    el = document.getElementById('campoCedula');
    if(event.which) { tecla = event.which; IE = false; }
               else { tecla = event.keyCode; IE = true; }

    if(tecla == 8) return true;
    if(tecla == 13) xajax_Acao(xajax.getFormValues('FormCedula'), 'P');
    if(tecla == 27) xajax_Acao(xajax.getFormValues('FormCedula'), 'A');
    if((tecla < 48 || tecla > 57) && tecla != 8 && tecla != 98 && tecla != 66 && tecla != 110 && tecla != 78) return false;
    if(el.value.length == 2 || el.value == 'N' || el.value == 'B' || (tecla == 48 && el.value.length == 0) || ((tecla == 98 || tecla == 66 || tecla == 110 || tecla == 78) && el.value.length > 0)) return false;
    if(tecla == 98) {
      el.value = "B";
      return false;
    }
    if(tecla == 110) {
      el.value = "N";
      return false;
    }

    return true;
  }

  function DefineLotacao(CodPessoaEleicao) {
    xajax_DefineLotacao(CodPessoaEleicao);
  }

  function Confirma() {
    var branco = 0;
    var nulo = 0;

    for(i = 1; el = document.getElementById('campo_voto_'+i); i++) {
      if(el.value == 'B' || el.value == '') branco++;
      if(el.value == 'N') nulo++;
    }
    if(branco > 0 || nulo > 0) {
      msg = 'Atenção! Você estará dando ';
      if(branco > 0 && nulo == 0) msg += branco + ' voto(s) em branco. ';
      if(branco == 0 && nulo > 0) msg += nulo + ' voto(s) nulo(s). ';
      if(branco > 0 && nulo > 0)  msg += branco + ' voto(s) em branco e ' + nulo + ' voto(s) nulo(s). ';
    } else msg = '';
    msg += 'Tem certeza de que deseja confirmar <?=$Str2?>?';
    if(confirm(msg)) {
      document.FormCedula.action = 'ELC_Grava_Votos.php';
      document.FormCedula.submit();
    }
  }

  function Acao(acao) {
	  VotoAtual = document.getElementById('VotoAtual').value;
	  NrVotos = document.getElementById('NrVotos').value;
		if(NrVotos > 1) {
			if(acao == 'A' && VotoAtual > 1) {
				NovoVoto = VotoAtual;
				NovoVoto--;
				document.getElementById('botao_voto_'+VotoAtual).value = document.getElementById('campoCedula').value;
				document.getElementById('campo_voto_'+VotoAtual).value = document.getElementById('campoCedula').value;
				document.getElementById('campoCedula').value = document.getElementById('campo_voto_'+NovoVoto).value;
				document.getElementById('VotoAtual').value = NovoVoto;
				if(NovoVoto == 1)
				  document.getElementById('BotaoVotoAnterior').innerHTML = 'PRIMEIRO VOTO';
				document.getElementById('BotaoVotoPosterior').innerHTML = '<input type="button" name="botao" value="PR&Oacute;XIMO VOTO &gt;&gt; (ENTER)" onclick="javascript: Acao(\'P\');" />';
				xajax_VerificaVoto(xajax.getFormValues('FormCedula'));
			}
			if(acao == 'P' && VotoAtual < NrVotos) {
				NovoVoto = VotoAtual;
				NovoVoto++;
				document.getElementById('botao_voto_'+VotoAtual).value = document.getElementById('campoCedula').value;
				document.getElementById('campo_voto_'+VotoAtual).value = document.getElementById('campoCedula').value;
				document.getElementById('campoCedula').value = document.getElementById('campo_voto_'+NovoVoto).value;
				document.getElementById('VotoAtual').value = NovoVoto;
				if(NovoVoto == NrVotos)
				  document.getElementById('BotaoVotoPosterior').innerHTML = '&Uacute;LTIMO VOTO';
				document.getElementById('BotaoVotoAnterior').innerHTML = '<input type="button" name="botao" value="&lt;&lt; VOTO ANTERIOR (ESC)" onclick="javascript: Acao(\'A\');" />';
				xajax_VerificaVoto(xajax.getFormValues('FormCedula'));
			}
			if(!isNaN(acao)) {
				NovoVoto = acao;
				document.getElementById('botao_voto_'+VotoAtual).value = document.getElementById('campoCedula').value;
				document.getElementById('campo_voto_'+VotoAtual).value = document.getElementById('campoCedula').value;
				document.getElementById('campoCedula').value = document.getElementById('campo_voto_'+NovoVoto).value;
				document.getElementById('VotoAtual').value = NovoVoto;
				if(NovoVoto == 1)
				  document.getElementById('BotaoVotoAnterior').innerHTML = 'PRIMEIRO VOTO';
				else
				  document.getElementById('BotaoVotoAnterior').innerHTML = '<input type="button" name="botao" value="&lt;&lt; VOTO ANTERIOR (ESC)" onclick="javascript: Acao(\'A\');" />';

				if(NovoVoto == NrVotos)
				  document.getElementById('BotaoVotoPosterior').innerHTML = '&Uacute;LTIMO VOTO';
				else
				  document.getElementById('BotaoVotoPosterior').innerHTML = '<input type="button" name="botao" value="PR&Oacute;XIMO VOTO &gt;&gt; (ENTER)" onclick="javascript: Acao(\'P\');" />';
				xajax_VerificaVoto(xajax.getFormValues('FormCedula'));
			}
		}
  }

  function Valida(form) {
    voto = document.getElementById('campoCedula').value;
    if((isNaN(voto) || voto.length < <?=$NrDigitos?>) && voto != 'B' && voto != 'N' && voto != '') {
      alert('Voto inválido.');
      return false;
    }
    VotoAtual = document.getElementById('VotoAtual').value;
    if(voto != 'B' && voto != 'N' && voto != '') {
      for(i = 1; el = document.getElementsByName('cedula['+i+']')[0]; i++) {
        if(el.value == voto && i != VotoAtual) {
          alert('Atenção! Votos não podem ser repetidos.');
          return false;
        }
      }
    }
    if(voto == '') document.getElementById('campoCedula').value = 'B';
    return true;
  }
</script>

</form>

</body>
</html>