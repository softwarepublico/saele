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

require_once("../CABECALHO.PHP");

function VerificaVoto($Form) {
  global $db;

  $objResponse = new xajaxResponse();

  $Valor = $Form['campoCedula'];
  $Str = NULL;

  if($Valor == "B") {
    $Str = '<table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td colspan="3" class="NomeChapa">
                  VOTO EM BRANCO
                </td>
              </tr>
            </table> ';
    $MostraLink = true;
		$ImagemChapa = NULL;
  }
  elseif($Valor == "N") {
    $Str = '<table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td colspan="3" class="NomeChapa">
                  VOTO NULO
                </td>
              </tr>
            </table> ';
    $MostraLink = true;
		$ImagemChapa = NULL;
  }
  elseif(strlen($Valor) < $Form['NrDigitos']) {
    $Str = '<table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td colspan="3" class="Chapa"> ';
    if($Form['EEleicao'] == "S")
      $Str .= 'Digite seu voto';
    else
      $Str .= 'Digite sua resposta';
    $Str.= '   </td>
              </tr>
            </table> ';
    $MostraLink = true;
		$ImagemChapa = NULL;
  }
  else {
    $SQL = " SELECT NrChapa, CodChapa, Descricao, ImagemChapa
             FROM eleicoes.CHAPA
             WHERE CodConcurso = :Concurso[numero]
               AND CodEleicao = :Eleicao[numero]
               AND NrChapa = :NrChapa[numero] ";
    $ConsultaChapa = new consulta($db, $SQL);
    $ConsultaChapa->setparametros("Concurso,Eleicao", $Form);
    $ConsultaChapa->setparametros("NrChapa", $Valor);
    if($ConsultaChapa->executa(true)) {
      $NrChapa = $ConsultaChapa->campo("NrChapa");
      $Chapa = $ConsultaChapa->campo("CodChapa");
			
		  $NomeArqChapa = "../FOTOS/CHAPA_".$Form['Concurso']."_".$Form['Eleicao']."_".$NrChapa.".JPG";

		  $ImagemChapa = file_exists($NomeArqChapa);
			
      $Indice = 1;

      $SQL = " SELECT
                P.descricao,
                PE.nomepessoa,
								PE.localtrabalho,
                PE.codpessoaeleicao
               FROM eleicoes.CANDIDATO C
               INNER JOIN eleicoes.PESSOAELEICAO PE
                 ON (PE.codpessoaeleicao = C.codpessoaeleicao)
               INNER JOIN eleicoes.PARTICIPACAO P
                 ON (P.codparticipacao = C.codparticipacao)
               WHERE C.codconcurso = :Concurso[numero]
                 AND C.codeleicao = :Eleicao[numero]
                 AND C.codchapa = :Chapa[numero]
               ORDER BY C.codparticipacao ";
      $ConsultaCand = new consulta($db, $SQL);
      $ConsultaCand->setparametros("Concurso,Eleicao", $Form);
      $ConsultaCand->setparametros("Chapa", $Chapa);
      $ConsultaCand->executa();
      $MostraLink = ($ConsultaCand->nrlinhas() > 1);
      if($MostraLink) $width = "50%"; else $width = "80%";
      $ImagemLink = ($MostraLink && $ImagemChapa);

      $Str  = '<table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td colspan="3" class="NomeChapa"> ';
      $Str .= $ConsultaChapa->campo("Descricao");

      if($ImagemLink)
        $Str .= ' <a href="javascript: void(0);" onclick="javascript: DefineImagemChapa('.$Form['Concurso'].','.$Form['Eleicao'].",".$NrChapa.');">[IMAGEM]</a>';

      $Str .= '   </td>
                </tr>  ';
      $TemImagemCandidato = false;
      $TemLotacaoCandidato = false;
      $NomeArqPrimeiroCand = NULL;
      if($ConsultaCand->proximo()) {
        do {
          $CodPessoaEleicao = $ConsultaCand->campo("CodPessoaEleicao");
          $NomeArqCand = "../FOTOS/CAND_".$Form['Concurso']."_".$Form['Eleicao']."_".$NrChapa."_".$CodPessoaEleicao.".JPG";
          $ImagemCand = file_exists($NomeArqCand);
          
          if($ImagemCand && is_null($NomeArqPrimeiroCand))
            $NomeArqPrimeiroCand = $NomeArqCand;
          
          $TemImagemCandidato = $TemImagemCandidato || $ImagemCand;
          
          $Str .= ' <tr bgcolor="#f5f5f5">
                      <td width="20%" style="text-align: right; font-family: verdana; font-size:8pt; font-weight: bold;"> '.$ConsultaCand->campo("Descricao").':</td>
                      <td width="'.$width.'" style="font-family: verdana; font-size:8pt;"> &nbsp; <span id="Cand'.$Indice.'" style="background-color: '.($Indice == 1 ? '#ffff50' : '#f5f5f5').';">'.$ConsultaCand->campo("NomePessoa").'</span> </td> ';
          if ($MostraLink || $ImagemCand) {
            $Str .= ' <td width="30%" style="font-family: verdana; font-size:8pt;"> ';
            
            if($ImagemCand) {
              $Str .= '<a href="javascript: void(0);" onclick="javascript: AtualizaCor('.$Indice.'); ';
              $Str .= 'xajax_DefineLotacao(0);';
              $Str .= 'DefineImagemCandidato('.$Form['Concurso'].','.$Form['Eleicao'].",".$NrChapa.",".$CodPessoaEleicao.');';
              $Str .= ' ">[FOTO]</a> ';
            }

            if(trim($ConsultaCand->campo("localtrabalho")) != "") {
              $TemLotacaoCandidato = true;
              $Str .= ' &nbsp; <a href="javascript: void(0);" onclick="javascript: AtualizaCor('.$Indice.'); ';
              $Str .= 'xajax_DefineLotacao('.$ConsultaCand->campo("CodPessoaEleicao").');';
              $Str .= 'DefineImagemCandidato('.$Form['Concurso'].','.$Form['Eleicao'].",".$NrChapa.",".$CodPessoaEleicao.');">';
              $Str .= ' [LOTA&Ccedil;&Atilde;O]</a> ';
            }

            $Str .= ' </td> ';
          }
          else $Str .= '<td>&nbsp;</td>';
          $Str .= ' </tr> ';
          $Indice++;
        } while ($ConsultaCand->proximo());
      }
    $Str .= ' </table> ';
    }
    else {
      $objResponse->assign("campoCedula", "value", "N");
      $Str = '<table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr bgcolor="#f5f5f5">
            <td colspan="3" style="text-align: center; font-family: verdana; font-size:10pt; font-weight:bold;">
              VOTO NULO
            </td>
          </tr>
        </table> ';
      $MostraLink = false;
      $ImagemChapa = NULL;
    }
  }
  if(is_null($ImagemChapa))
    $objResponse->assign("FotoCandidato", "src", "../nada.GIF");
  elseif(!$ImagemChapa && !$TemImagemCandidato)
    $objResponse->assign("FotoCandidato", "src", "../FOTOS/FotoPadrao.jpg");
  elseif($ImagemChapa)
    $objResponse->assign("FotoCandidato", "src", $NomeArqChapa);
  else
    $objResponse->assign("FotoCandidato", "src", $NomeArqPrimeiroCand);

  if($MostraLink || !$TemLotacaoCandidato)
    $objResponse->assign("Lotacao", "style.visibility", "hidden")
                ->assign("ItensLotacao", "innerHTML", NULL);
  else
    DefineLotacao($CodPessoaEleicao, $objResponse);

  $objResponse->assign('DivChapa', 'innerHTML', $Str);
  return $objResponse;
}

function Acao($Form, $Acao) {
  global $db;

  $objResponse = new xajaxResponse();
//	$objResponse->alert($Acao);
//	return $objResponse;
	
	$Voto = $Form['campoCedula'];
	if($Voto != 'B' && $Voto != 'N') {
    if(trim($Voto) == "") $Voto = 'B';
	  elseif(is_numeric($Voto)) {
		  if(strlen($Voto) < $Form['NrDigitos']) $Voto = 'N';
			else {
			  $SQL = " SELECT * FROM eleicoes.CHAPA
				         WHERE CodConcurso = :Concurso[numero]
								   AND CodEleicao = :Eleicao[numero]
									 AND NrChapa = :NrChapa[numero] ";
				$Consulta = new consulta($db, $SQL);
				$Consulta->setparametros("Concurso,Eleicao", $Form);
				$Consulta->setparametros("NrChapa", $Voto);
				if(!$Consulta->executa(true)) $Voto = 'N';
				elseif(array_search($Voto, $Form["voto"]) !== false && array_search($Voto, $Form["voto"]) != $Form['VotoAtual']) {
				  $objResponse->alert("Atenção! Os votos não podem ser repetidos.");
					return $objResponse;
				}
			}
		}
		else $Voto = 'N';
	}
	if($Form['NrVotos'] > 1) {
    if($Acao == "C") { // CONFIRMA
		  $objResponse->assign("botao_voto_".$Form['VotoAtual'], "value", $Voto)
		              ->assign("campo_voto_".$Form['VotoAtual'], "value", $Voto)
				  				->script("Confirma();");
  	}
	  elseif($Acao == "A" && $Form['VotoAtual'] > 1) { // VOTO ANTERIOR
		  $NovoVoto = $Form['VotoAtual'] - 1;
			$objResponse->assign("botao_voto_".$Form['VotoAtual'], "value", $Voto)
			            ->assign("botao_voto_".$Form['VotoAtual'], "style.backgroundColor", "fff5e5")
			            ->assign("botao_voto_".$NovoVoto, "style.backgroundColor", "ffb5a5")
			            ->assign("campo_voto_".$Form['VotoAtual'], "value", $Voto)
			            ->assign("campoCedula", "value", $Form["voto"][$NovoVoto])
									->assign("VotoAtual", "value", $NovoVoto);
			if($NovoVoto == 1)
			  $objResponse->assign("BotaoVotoAnterior", "innerHTML", 'PRIMEIRO VOTO');
			$objResponse->assign("BotaoVotoPosterior", "innerHTML", '<input type="button" name="botao" value="PR&Oacute;XIMO VOTO &gt;&gt; (ENTER)" onclick="javascript: xajax_Acao(xajax.getFormValues(\'FormCedula\'), \'P\');" />');
			$objResponse->script("xajax_VerificaVoto(xajax.getFormValues('FormCedula'));");
		}
	  elseif($Acao == "P" && $Form['VotoAtual'] < $Form['NrVotos']) { // VOTO POSTERIOR
		  $NovoVoto = $Form['VotoAtual'] + 1;
			$objResponse->assign("botao_voto_".$Form['VotoAtual'], "value", $Voto)
			            ->assign("botao_voto_".$Form['VotoAtual'], "style.backgroundColor", "fff5e5")
			            ->assign("botao_voto_".$NovoVoto, "style.backgroundColor", "ffb5a5")
			            ->assign("campo_voto_".$Form['VotoAtual'], "value", $Voto)
			            ->assign("campoCedula", "value", $Form["voto"][$NovoVoto])
									->assign("VotoAtual", "value", $NovoVoto);
			if($NovoVoto == $Form['NrVotos'])
			  $objResponse->assign("BotaoVotoPosterior", "innerHTML", '&Uacute;LTIMO VOTO');
		  $objResponse->assign("BotaoVotoAnterior", "innerHTML", '<input type="button" name="botao" value="&lt;&lt; VOTO ANTERIOR (ESC)" onclick="javascript: xajax_Acao(xajax.getFormValues(\'FormCedula\'), \'A\');" />');
			$objResponse->script("xajax_VerificaVoto(xajax.getFormValues('FormCedula'));");
		}
		elseif(is_numeric($Acao)) { // VOTO ALEATÓRIO
		  $NovoVoto = $Acao;
			$objResponse->assign("botao_voto_".$Form['VotoAtual'], "value", $Voto)
			            ->assign("botao_voto_".$Form['VotoAtual'], "style.backgroundColor", "fff5e5")
			            ->assign("botao_voto_".$NovoVoto, "style.backgroundColor", "ffb5a5")
			            ->assign("campo_voto_".$Form['VotoAtual'], "value", $Voto)
			            ->assign("campoCedula", "value", $Form["voto"][$NovoVoto])
									->assign("VotoAtual", "value", $NovoVoto);
			if($NovoVoto == 1)
			  $objResponse->assign("BotaoVotoAnterior", "innerHTML", 'PRIMEIRO VOTO');
			else
		    $objResponse->assign("BotaoVotoAnterior", "innerHTML", '<input type="button" name="botao" value="&lt;&lt; VOTO ANTERIOR (ESC)" onclick="javascript: xajax_Acao(xajax.getFormValues(\'FormCedula\'), \'A\');" />');

			if($NovoVoto == $Form['NrVotos'])
			  $objResponse->assign("BotaoVotoPosterior", "innerHTML", '&Uacute;LTIMO VOTO');
			else
			  $objResponse->assign("BotaoVotoPosterior", "innerHTML", '<input type="button" name="botao" value="PR&Oacute;XIMO VOTO &gt;&gt; (ENTER)" onclick="javascript: xajax_Acao(xajax.getFormValues(\'FormCedula\'), \'P\');" />');
      $objResponse->script("xajax_VerificaVoto(xajax.getFormValues('FormCedula'));");
		}
	}
	elseif($Acao == "C") {
		$objResponse->assign("campo_voto_1", "value", $Voto)
		    				->script("Confirma();");
	}
	return $objResponse;
}

function DefineLotacao($CodPessoaEleicao, $objResponse=NULL) {
  global $db;
  
	if(is_null($objResponse)) {
    $objResponse = new xajaxResponse();
		$retorna = true;
	}
	else $retorna = false;

  if($CodPessoaEleicao == 0) {
    $objResponse->assign("Lotacao", "style.visibility", "hidden")
                ->assign("ItensLotacao", "innerHTML", NULL);
  }
  else {
    $SQL = " SELECT LocalTrabalho FROM eleicoes.PESSOAELEICAO
             WHERE CodPessoaEleicao = :CodPessoaEleicao[numero] ";
    $ConsultaLot = new consulta($db, $SQL);                            
    $ConsultaLot->setparametros("CodPessoaEleicao", $CodPessoaEleicao);
    $ConsultaLot->executa(true);
    if($ConsultaLot->campo("LocalTrabalho"))
      $Str = " &nbsp; - ".$ConsultaLot->campo("LocalTrabalho");
    
    $objResponse->assign("Lotacao", "style.visibility", "visible")
                ->assign("ItensLotacao", "innerHTML", $Str);
  }

  if($retorna) return $objResponse;
}

require("Votacao_Common.php");
$xajax->processRequest();
?>