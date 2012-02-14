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
db::instancia();

require_once("../PUBLIC/ConcursoEleitoral.class.php");

require("../xajax/xajax_core/xajax.inc.php");

$xajax = new xajax('Adm_Common.php');

$xajax->setCharEncoding("iso-8859-1");
$xajax->configure("decodeUTF8Input", true);

$xajax->register(XAJAX_FUNCTION, "CarregaTabelaConcursos");
$xajax->register(XAJAX_FUNCTION, "EdicaoConcurso");
$xajax->register(XAJAX_FUNCTION, "SalvarConcurso");
$xajax->register(XAJAX_FUNCTION, "EdicaoEleicao");
$xajax->register(XAJAX_FUNCTION, "SalvarEleicao");
$xajax->register(XAJAX_FUNCTION, "FinalizarConcurso");
$xajax->register(XAJAX_FUNCTION, "Consistencia");
$xajax->register(XAJAX_FUNCTION, "EnvioEMails");
$xajax->register(XAJAX_FUNCTION, "EnviarEMails");
$xajax->register(XAJAX_FUNCTION, "EnviarEMailEleitores");
$xajax->register(XAJAX_FUNCTION, "ListaUrnas");
$xajax->register(XAJAX_FUNCTION, "CarregaUrna");
$xajax->register(XAJAX_FUNCTION, "SalvaUrna");
$xajax->register(XAJAX_FUNCTION, "ExcluiUrna");
$xajax->register(XAJAX_FUNCTION, "ListaEscopos");
$xajax->register(XAJAX_FUNCTION, "CarregaEscopo");
$xajax->register(XAJAX_FUNCTION, "SalvaEscopo");
$xajax->register(XAJAX_FUNCTION, "ExcluiEscopo");

$xajax->register(XAJAX_FUNCTION, "PesquisaPessoas");
$xajax->register(XAJAX_FUNCTION, "AlteraSolicitante");

$xajax->register(XAJAX_FUNCTION, "CarregaEdicaoPessoa");
$xajax->register(XAJAX_FUNCTION, "SalvaPessoa");

function CarregaTabelaConcursos() {
    $objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();
    $Pessoa = $Controlador->recuperaPessoaLogada();
    $Direito = $Pessoa->eGerenteSistema();
    $Campos = array();

	$SQLadd = " WHERE TAB.situacaoconcurso < ".SITUACAOCONCURSO_ARQUIVADO;

	if(!$Direito) {
        $SQLadd .= " AND EXISTS
                      (SELECT * from eleicoes.comissaoeleitoral CE
                       WHERE CE.CodConcurso = TAB.CodConcurso
                         AND CE.CodPessoaEleicao = :CodPessoaEleicao[numero]) ";
        $Campos['CodPessoaEleicao'] = $Pessoa->getChave();
	}
	$SQLadd .= " ORDER BY TAB.DataHoraInicio DESC ";
	$Str = NULL;

    $IteradorConcursos = new Iterador("ConcursoEleitoral", $SQLadd, $Campos);
	foreach($IteradorConcursos as $CodConcurso => $Concurso) {
        $Campos['CodConcurso'] = $CodConcurso;
        $EstadoConcurso = $Concurso->estadoConcurso();

        $Ativo = ($EstadoConcurso == CONCURSO_INICIADO);
        $Encerrou = ($EstadoConcurso == CONCURSO_ENCERRADO);

		$EmAberto = (($EstadoConcurso == CONCURSO_NAOINICIADO) && ($Concurso->get("situacaoconcurso") < SITUACAOCONCURSO_HOMOLOGADO));
        $JaContou = $Concurso->get("situacaoconcurso") >= SITUACAOCONCURSO_APURADO;
        $Arquivado = $Concurso->get("situacaoconcurso") == SITUACAOCONCURSO_ARQUIVADO;
        $DireitoContagem = $Direito;

		$Str .= '<table width="85%" border="1" cellspacing="0" cellpadding="0" class="tabela" align="center">';
		$Str .= '  <tr>';
		if($Direito && $EmAberto)
            $Str .= '    <td class="Centro" width="10%"><a href="javascript: void(0);" onclick="javascript: EditaEleicao('.$CodConcurso.', 0);">[INCLUIR]</a></td>';

		$Str .= '    <td class="Centro">'.$Concurso->retornaString(STR_CONCURSOELEITORAL).': ['.$Campos['CodConcurso'].'] '.$Concurso->get("descricao").' (de '.$Concurso->get("datahorainicio", datahora).' at&eacute; '.$Concurso->get("datahorafim", datahora).') ';

        if(!$EmAberto && !$JaContou)
            $Str .= '<a href="javascript: void(0);" onClick="javascript: MostraEnvioEMail('.$Campos['CodConcurso'].');">[Enviar E-Mails]</a> ';

		if($Direito && ($Concurso->get("situacaoconcurso") < SITUACAOCONCURSO_HOMOLOGADO)) {
            $Str .= '<a href="ELC_Valida_Pessoas_Concurso.php?CodConcurso='.$Campos['CodConcurso'].'">[Homologa&ccedil;&atilde;o]</a>
			       <a href="ELC_Valida_Pessoas_Concurso.php?CodConcurso='.$Campos['CodConcurso'].'&Final">[Homologa&ccedil;&atilde;o final]</a> ';

            $Str .= '<a href="javascript: void(0);" onClick="javascript: EditaConcurso('.$Campos['CodConcurso'].');">[Editar]</a> ';
        }

		if($DireitoContagem && ($Concurso->estadoConcurso() == CONCURSO_ENCERRADO)) {
            $Str .= '<a href="ELC_Contagem.php?CodConcurso='.$CodConcurso.'">';
            if($JaContou)
                $Str .= '[Recontagem de votos]';
            else
                $Str .= '[Contagem de votos]';
			$Str .= '</a>';
		}

		if($Direito && $Encerrou && $JaContou && !$Arquivado) {
		  $Str .= '<a href="javascript: void(0);" onclick="javascript: if(confirm(\'Tem certeza de que deseja finalizar este concurso?\')) xajax_FinalizarConcurso('.$Campos['CodConcurso'].');">[Finalizar concurso]</a>';
		}

		if(!$Ativo && !$Encerrou)
		  $Str .= '    <td class="Centro" width="10%"><a href="ELC_Checklist.php?CodConcurso='.$Campos['CodConcurso'].'">[CHECKLIST]</a></td>';

		$Str .= '    </td>
		           </tr>
							 <tr>
							   <td colspan="3">
								   <table border="0" width="100%" cellspacing="0" cellpadding="0" class="tabela">
									   <tr class="LinhaTitulo">
										   <td>'.$Concurso->retornaString(STR_ELEICAO).'</td>
										   <td>Status</td>
										   <td class="Centro">Auditoria</td>
										   <td class="Centro">Editar</td>
										 </tr> ';
		$Eleicoes = $Concurso->devolveEleicoes();
        $i = 1;
		foreach($Eleicoes as $CodEleicao => $Eleicao) {
		  $GerenteEleicao = $Eleicao->verificaComissao($Pessoa);
		  $Campos['CodEleicao'] = $CodEleicao;

          $Zerada = $Eleicao->eleicaoZerada();
		  $Str .= '<tr class="Linha'.$i.'">
			           <td width="50%">['.$CodEleicao.'] '.$Eleicao->get("descricao").'</td>
								 <td width="30%"> ';
		  if($Ativo && $Zerada && !$EmAberto) {
			  $SQL = " SELECT SUM(1) as NumEleitores,
				                SUM(CASE WHEN IndEfetuouVoto = 'S' THEN 1 ELSE NULL END) as NumVotantes
								 from eleicoes.ELEITOR
								 WHERE CodConcurso = :CodConcurso[numero]
								   AND CodEleicao = :CodEleicao[numero] ";
			  $ConsultaVotantes = new Consulta($SQL);
				$ConsultaVotantes->setparametros("CodConcurso,CodEleicao", $Campos);
				$ConsultaVotantes->executa(true);

				if($ConsultaVotantes->campo("NumEleitores") == 0)
				  $Porcentagem = 0;
				else
				  $Porcentagem = round(($ConsultaVotantes->campo("NumVotantes") / $ConsultaVotantes->campo("NumEleitores")) * 100, 2);
				$Str .= "Ativo. ".$Porcentagem."% dos eleitores j&aacute; votaram.";
			}
			else {
                if($Encerrou) {
                    if($JaContou) {
                        $Str .= 'Encerrada. <a href="ELC_Apuracao.php?CodConcurso='.$CodConcurso.'&CodEleicao='.$CodEleicao.'">Visualizar a apura&ccedil;&atilde;o</a>';
                    }
					else
                        $Str .= 'Encerrada; aguardando a contagem dos votos.';
				}
				else {
                    if($Direito)
                    if(($GerenteEleicao == COMISSAO_GERENTE) || $Direito)
                        $Str .= 'Inativa. <a href="ELC_Apuracao.php?CodConcurso='.$CodConcurso.'&CodEleicao='.$CodEleicao.'">Verificar os votos</a>';
					else
                        $Str .= 'Inativa.';
				}
			}
			$Str .= ' </td>
			          <td class="Centro">';
			if(($Direito || (($GerenteEleicao == COMISSAO_GERENTE) && !$JaContou)) && !$Encerrou)
                  $Str .= '<a href="javascript: void(0);" onClick="javascript: Consistencia('.$CodConcurso.', '.$CodEleicao.');">Checar</a>';
			else
                $Str .= '&nbsp;';
			$Str .= ' </td>
			          <td class="Centro">';
			if(($Direito || (($GerenteEleicao == COMISSAO_GERENTE) && !$JaContou)) && !$Encerrou)
                $Str .= '<a href="javascript: void(0);" onclick="javascript: EditaEleicao('.$CodConcurso.', '.$CodEleicao.');">Editar</a>';
			else
                $Str .= '&nbsp;';
			$Str .= ' </td>
			        </tr>
							<tr class="Linha'.$i.'"><td colspan="4">&nbsp;</td></tr> ';
			$i = ($i % 2) + 1;
		}
		$Str .= '			 </table>
		             </td>
							 </tr>
						 </table>';
	}
  $objResponse->assign("DivTabelaConcursos", "innerHTML", $Str);
  return $objResponse;
}

function EdicaoConcurso($CodConcurso=NULL) {
    $objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();
    $Pessoa = $Controlador->recuperaPessoaLogada();

    if(!$Pessoa->eGerenteSistema()) {
        $objResponse->alert("O usuário não possui direito para esta operação.");
		return $objResponse;
	}

	if(is_null($CodConcurso)) {
        $DescConcurso = NULL;
		$DataInicio = NULL;
		$DataFim = NULL;
		$HoraInicio = NULL;
		$HoraFim = NULL;
        $BarradoPorIP = "S";
		$Modalidade = "S";
		$IndContagem = "S";
		$IndEleicao = "S";
	}
	else {
        $Concurso = new ConcursoEleitoral($CodConcurso);
        $DescConcurso = $Concurso->get("descricao");
		$DataInicio = $Concurso->get("datahorainicio", data);
		$DataFim = $Concurso->get("datahorafim", data);
		$HoraInicio = $Concurso->get("datahorainicio", hora);
		$HoraFim = $Concurso->get("datahorafim", hora);
		$BarradoPorIP = $Concurso->get("indbarradoporip");
		$IndContagem = $Concurso->get("indhabilitacontagem");
		$Modalidade = $Concurso->get("modalidadeconcurso");
	}

  $objResponse->assign("CodConcurso", "value", $CodConcurso)
              ->assign("DescConcurso", "value", $DescConcurso)
              ->assign("DataInicio", "value", $DataInicio)
              ->assign("DataFim", "value", $DataFim)
              ->assign("HoraInicio", "value", $HoraInicio)
              ->assign("HoraFim", "value", $HoraFim)
              ->assign("BarradoPorIPS", "checked", ($BarradoPorIP == "S" ? 'checked="checked"' : NULL))
              ->assign("BarradoPorIPE", "checked", ($BarradoPorIP == "E" ? 'checked="checked"' : NULL))
              ->assign("BarradoPorIPN", "checked", ($BarradoPorIP == "N" ? 'checked="checked"' : NULL))
              ->assign("IndContagemS", "checked", ($IndContagem == "S" ? 'checked="checked"' : NULL))
			  ->assign("IndContagemN", "checked", ($IndContagem == "N" ? 'checked="checked"' : NULL))
              ->assign("ModalidadeE", "checked", ($Modalidade == "E" ? 'checked="checked"' : NULL))
              ->assign("ModalidadeQ", "checked", ($Modalidade == "Q" ? 'checked="checked"' : NULL))
              ->assign("DivEdicaoConcurso", "style.display", "block");
	return $objResponse;
}

function SalvarConcurso($Form) {
    $objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();
    $Pessoa = $Controlador->recuperaPessoaLogada();

    if(!$Pessoa->eGerenteSistema()) {
	  $objResponse->alert("O usuário não possui direito para esta operação.");
		return $objResponse;
	}

	if(trim($Form['DescConcurso']) == "")
	  $objResponse->alert("Preencha a descrição do concurso.");
	elseif(trim($Form['DataInicio']) == "")
	  $objResponse->alert("Preencha a data de início do concurso.");
	elseif(trim($Form['DataFim']) == "")
	  $objResponse->alert("Preencha a data de fim do concurso.");
	elseif(trim($Form['HoraInicio']) == "")
	  $objResponse->alert("Preencha a hora de início do concurso.");
	elseif(trim($Form['HoraFim']) == "")
	  $objResponse->alert("Preencha a hora de fim do concurso.");
	else {
        $Form['DescConcurso'] = utf8_decode($Form['DescConcurso']);
		if(trim($Form['CodConcurso']) == "")
            $Concurso = new ConcursoEleitoral();
        else
            $Concurso = new ConcursoEleitoral($Form['CodConcurso']);

        $Concurso->set('descricao', $Form['DescConcurso']);
        $Concurso->set('datahorainicio', $Form['DataInicio'].' '.$Form['HoraInicio']);
        $Concurso->set('datahorafim', $Form['DataFim'].' '.$Form['HoraFim']);
        $Concurso->set('indbarradoporip', $Form['BarradoPorIP']);
        $Concurso->set('modalidadeconcurso', $Form['Modalidade']);
        $Concurso->set('indhabilitacontagem', $Form['IndContagem']);
        $Concurso->set('situacaoconcurso', SITUACAOCONCURSO_CRIADO);


        $Concurso->salva();
		$objResponse->assign("DivEdicaoConcurso", "style.display", "none");
		$objResponse->loadCommands(CarregaTabelaConcursos());
	}
	return $objResponse;
}

function EdicaoEleicao($CodConcurso, $CodEleicao=NULL) {
    $objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();
    $Pessoa = $Controlador->recuperaPessoaLogada();
    $Concurso = new ConcursoEleitoral($CodConcurso);

    $ConcursoAtivo = ($Concurso->estadoConcurso() >= CONCURSO_INICIADO);

	if(is_null($CodEleicao)) {
        if(!$Pessoa->eGerenteSistema()) {
            $objResponse->alert("PERMISSÃO NEGADA: Operação exclusiva para gerentes do sistema");
            return $objResponse;
        }

        $DescEleicao = NULL;
        $NrPossibilidades = NULL;
        $NrDigitos = 2;
        $EditarNrDigitos = true;
        $objResponse->assign("BtnEMailEleitores", "style.display", "none");
	}
	else {
        $Eleicao = $Concurso->devolveEleicao($CodEleicao);

        if(!$Pessoa->eGerenteSistema() && ($Eleicao->verificaComissao($Pessoa) != COMISSAO_GERENTE)) {
            $objResponse->alert("PERMISSÃO NEGADA: Operação exclusiva para gerentes do sistema e da eleição");
            return $objResponse;
        }

        $DescEleicao = $Eleicao->get("descricao");
        $NrPossibilidades = $Eleicao->get("nrpossibilidades");
        $NrDigitos = $Eleicao->get("nrdigitoschapa");

        $Chapas = $Eleicao->devolveChapas();
        $EditarNrDigitos = (!$Chapas->temRegistro() and !$ConcursoAtivo);
        if( ($Pessoa->eGerenteSistema() || ($Eleicao->verificaComissao($Pessoa) == COMISSAO_GERENTE))
         && ($Concurso->get("indbarradoporip") != "S") && (LogOperacao::getNumLogsPorDescricao(DESCRICAO_EMAILS, $Concurso, $Eleicao) == 0))
            $objResponse->assign("BtnEMailEleitores", "style.display", "block");
        else
            $objResponse->assign("BtnEMailEleitores", "style.display", "none");
	}
    

    $objResponse->assign("CodConcursoElc", "value", $CodConcurso)
                ->assign("CodEleicao", "value", $CodEleicao)
                ->assign("DescEleicao", "value", $DescEleicao)
                ->assign("DescEleicao", "disabled", ($ConcursoAtivo ? 'disabled' : null))
                ->assign("NrPossibilidades", "value", $NrPossibilidades)
                ->assign("NrPossibilidades", "disabled", ($ConcursoAtivo ? 'disabled' : null))
                ->assign("NrDigitosChapa2", "checked", $NrDigitos == 2 ? 'checked' : null)
                ->assign("NrDigitosChapa1", "checked", $NrDigitos == 1 ? 'checked' : null)
                ->assign("NrDigitosChapa2", "disabled", $EditarNrDigitos ? null : 'disabled')
                ->assign("NrDigitosChapa1", "disabled", $EditarNrDigitos ? null : 'disabled')
                ->assign("BtnSalvarEleicao", "disabled", ($ConcursoAtivo ? 'disabled' : null))
                ->assign("DivEdicaoEleicao", "style.display", "block");
	return $objResponse;
}

function SalvarEleicao($Form, $VaiParaRelatorio=false) {
    $objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();
    $Pessoa = $Controlador->recuperaPessoaLogada();
    $Concurso = new ConcursoEleitoral($Form['CodConcursoElc']);
    if($Concurso->estadoConcurso() < CONCURSO_INICIADO) {
        if(trim($Form['CodEleicao']) == "") {
            if(!$Pessoa->eGerenteSistema()) {
                $objResponse->alert("PERMISSÃO NEGADA: Operação exclusiva para gerentes do sistema");
                return $objResponse;
            }

            $Eleicao = $Concurso->geraEleicao();
        }
        else {
            $Eleicao = $Concurso->devolveEleicao($Form['CodEleicao']);

            if(!$Pessoa->eGerenteSistema() && ($Eleicao->verificaComissao($Pessoa) != COMISSAO_GERENTE)) {
                $objResponse->alert("PERMISSÃO NEGADA: Operação exclusiva para gerentes do sistema e da eleição");
                return $objResponse;
            }
        }

        if(trim($Form['DescEleicao']) == "")
            return $objResponse->alert("Preencha a descrição da eleição");
        if((trim($Form['NrPossibilidades'])) == "" || (!is_numeric($Form['NrPossibilidades'])))
            return $objResponse->alert("Preencha o número de possibilidades de voto (apenas dígitos)");
        if(isset($Form['NrDigitosChapa']) && ($Form['NrDigitosChapa'] != 1) && ($Form['NrDigitosChapa'] != 2))
            return $objResponse->alert("Selecione o número de digitos por chapa");
        $Eleicao->set("descricao", $Form['DescEleicao']);
        $Eleicao->set("nrpossibilidades", $Form['NrPossibilidades']);
        if(isset($Form['NrDigitosChapa']))
            $Eleicao->set("nrdigitoschapa", $Form['NrDigitosChapa']);
        $Eleicao->salva();
    }

    $objResponse->assign("DivEdicaoEleicao", "style.display", "none");
    if($VaiParaRelatorio)
        $objResponse->script("location.href = 'ELC_Relatorio_Eleicao.php?CodConcurso=".$Form['CodConcursoElc']."&CodEleicao=".$Form['CodEleicao']."';");
    else
        $objResponse->loadCommands(CarregaTabelaConcursos());
    return $objResponse;
}

function EnviarEMailEleitores($Form) {
    $objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();
    $Pessoa = $Controlador->recuperaPessoaLogada();
    $Concurso = new ConcursoEleitoral($Form['CodConcursoElc']);
    $Eleicao = $Concurso->devolveEleicao($Form['CodEleicao']);
    if(!$Pessoa->eGerenteSistema() && ($Eleicao->verificaComissao($Pessoa) != COMISSAO_GERENTE))
        return $objResponse->alert("PERMISSÃO NEGADA: Operação exclusiva para gerentes do sistema e da eleição.");

    if($Concurso->get("indbarradoporip") == "S")
        return $objResponse->alert("ERRO: Operação não permitida para concursos com urnas.");
    if(LogOperacao::getNumLogsPorDescricao(DESCRICAO_EMAILS, $Concurso, $Eleicao) > 0)
        return $objResponse->alert("ERRO: E-Mails já enviados para esta eleição.");

    $Titulo = "Convocação para ".$Concurso->retornaString(STR_CONCURSOELEITORAL).": ".$Concurso->get("descricao");
    $Mensagem = '<p>Prezado eleitor:</p>
<p>Você está sendo convidado à votação para '.($Concurso->get("modalidadeconcurso") == "E" ? "o concurso eleitoral " : "a enquete ").' "'.$Concurso->get("descricao").'",
que ocorre no dia '.$Concurso->get("datahorainicio", data).' às '.$Concurso->get("datahorainicio", hora).'.</p>
<p><a href="http://'.$_SERVER['SERVER_NAME'].'/LoginEleicoes.php?CodConcurso='.$Concurso->getChave().'">Acesse a urna de votação através deste link</a>.</p>
<p>Participe!</p>';
    $Eleitores = $Eleicao->devolveEleitores(ELEITOR_NAOVOTOU);
    foreach($Eleitores as $Eleitor) {
        $Pessoa = $Eleitor->getObj("PessoaEleicao");
        $Destinatario = $Pessoa->get("email");
        mail($Destinatario, $Titulo, $Mensagem, "Content-Type: text/html\r\nFrom: gerenciaeleicoes@".$_SERVER['SERVER_NAME']);
    }
    $Eleicao->geraLogOperacao(DESCRICAO_EMAILS);

    return $objResponse->assign("BtnEMailEleitores", "style.display", "none")
                       ->alert("E-Mails enviados com sucesso.");
}

function FinalizarConcurso($CodConcurso) {
	$objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();
    $Pessoa = $Controlador->recuperaPessoaLogada();
    if(!$Pessoa->eGerenteSistema()) {
        return $objResponse->alert("O usuário não possui direito para esta operação.");
	}

    $Concurso = new ConcursoEleitoral($CodConcurso);
    if($Concurso->get("situacaoconcurso") < SITUACAOCONCURSO_APURADO) {
        return $objResponse->alert("O Concurso ainda não foi apurado.");
	}

    $Concurso->finalizaConcurso();
	$objResponse->alert("Concurso Eleitoral finalizado com sucesso.");
    $objResponse->loadCommands(CarregaTabelaConcursos());

	return $objResponse;
}

function Consistencia($CodConcurso, $CodEleicao) {
	$objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();
    $Pessoa = $Controlador->recuperaPessoaLogada();
	$Concurso = new ConcursoEleitoral($CodConcurso);
    $Eleicao = $Concurso->devolveEleicao($CodEleicao);

    if(!$Pessoa->eGerenteSistema() && ($Eleicao->verificaComissao($Pessoa) != COMISSAO_GERENTE)) {
        return $objResponse->alert("O usuário não possui direito para esta operação.");
	}

	$NrPossibilidades = $Eleicao->get("nrpossibilidades");
    $NumVotantes = count($Eleicao->devolveEleitores(ELEITOR_JAVOTOU));
    $NumLogs = LogOperacao::getNumLogsPorDescricao(DESCRICAO_VOTOEFETUADO, $Concurso, $Eleicao);
    $NumVotos = $Eleicao->devolveNrVotos();

	$OK1 = ($NumVotantes == $NumLogs ? "OK" : "Erro");
	$OK2 = ($NumVotantes == ($NumVotos / $NrPossibilidades) ? "OK" : "Erro");

	$objResponse->assign("Consistencia1", "innerHTML", $OK1)
	            ->assign("Consistencia2", "innerHTML", $OK2)
                ->assign("DivConsistencia", "style.display", "block");
	return $objResponse;
}

function EnvioEMails($CodConcurso) {
    $objResponse = new xajaxResponse();

    $Pessoa = controlador::instancia()->recuperaPessoaLogada();

    $Concurso = new ConcursoEleitoral($CodConcurso);
    $TituloEMail = "Convocação para votação";
    $RemetenteEMail = $Pessoa->get("email");
    $TextoEMail =
"Prezado [NOME]:
No dia ".$Concurso->get("datahorainicio", data).", às ".$Concurso->get("datahorainicio", hora).",
será dado início ".($Concurso->get("modalidadeconcurso") == "E" ? "ao concurso eleitoral " : "à enquete ").$Concurso->get("descricao").". Para votar, acesse o link abaixo:

http://".$_SERVER['SERVER_NAME']."/LoginEleicoes.php?CodConcurso=".$CodConcurso;
    $objResponse->assign("TituloEMail", "value", $TituloEMail)
                ->assign("RemetenteEMail", "value", $RemetenteEMail)
                ->assign("CorpoEMail", "value", $TextoEMail)
                ->assign("CodConcursoEMails", "value", $CodConcurso)
                ->assign("Destinatarios1", "checked", "checked")
                ->assign("Destinatarios2", "checked", null)
                ->assign("Destinatarios3", "checked", null)
                ->assign("DivEMails", "style.display", "block");
    return $objResponse;
}

function EnviarEMails($Form) {
    $objResponse = new xajaxResponse();
    $ListaEleitores = array();

    $Titulo = $Form['TituloEMail'];
    $Remetente = $Form['RemetenteEMail'];
    $Concurso = new ConcursoEleitoral($Form['CodConcursoEMails']);

    $SQLAdd = null;
    switch($Form['Destinatarios']) {
        case 1: // Apenas quem não votou
            $SQLAdd = " and datahoravoto is null "; break;
        case 2: // Apenas quem já votou
            $SQLAdd = " and datahoravoto is not null "; break;
    }

    $Pessoas = new Iterador("PessoaEleicao",
                            " where exists (select * from eleicoes.eleitor E
                                            where E.codconcurso = :CodConcurso[numero]
                                              and E.codpessoaeleicao = TAB.codpessoaeleicao ".$SQLAdd.") ",
                            array("CodConcurso" => $Form['CodConcursoEMails']));

    foreach($Pessoas as $PessoaEleicao) {
        $Texto = str_replace("[NOME]", $PessoaEleicao->get("nomepessoa"), $Form['CorpoEMail']);
        mail($PessoaEleicao->get("email"), $Titulo, $Texto, "From: ".$Remetente);
    }

    $objResponse->alert("E-mails enviados com sucesso.")
                ->assign("DivEMails", "style.display", "none");
    return $objResponse;
}

function PesquisaPessoas($Form) {
    $objResponse = new xajaxResponse();
    $Campos = array();

    $NomePesq = trim(str_replace("%", "", $Form['NomePesq']));

	if(strlen($NomePesq) < 3) {
        $objResponse->alert("O critério de pesquisa deve possui, no mínimo, 3 caracteres.");
		return $objResponse;
	}
	if($Form['TipoPesq'] == "1")
        $Campos['Criterio'] = $NomePesq."%";
	else
        $Campos['Criterio'] = "%".$NomePesq."%";

	$SQLAdd = " WHERE UPPER(nomepessoa) like UPPER(:Criterio[texto]) ";
    $Pessoas = new Iterador("PessoaEleicao", $SQLAdd, $Campos);
    if($Pessoas->temRegistro()) {
        $objResponse->assign("ListaSolicitantes", "style.textAlign", "left");
        $objResponse->assign("ListaSolicitantes", "style.fontWeight", "normal");
		$Str = "<ul>";
		foreach($Pessoas as $CodPessoaEleicao => $Pessoa) {
            $Str .= '<li><a id="Pessoa'.$CodPessoaEleicao.'" href="javascript: void(0);" onclick="javascript: xajax_CarregaEdicaoPessoa('.$CodPessoaEleicao.');">'.$Pessoa->get("nomepessoa");
			if(!$Pessoa->homologada())
                $Str .= ' <strong>(NÃO HOMOLOGADA)</strong>';
			if($Pessoa->eSolicitante())
                $Str .= ' <strong>(Solicitante)</strong>';
			if($Pessoa->eGerenteSistema())
                $Str .= ' <strong>(Gerente)</strong>';
			$Str .= "</li>";
		}
        $Str .= "</ul>";
        $objResponse->assign("ListaSolicitantes", "innerHTML", $Str);
	}
	else {
	  $objResponse->assign("ListaSolicitantes", "style.textAlign", "center");
	  $objResponse->assign("ListaSolicitantes", "style.fontWeight", "bold");
	  $objResponse->assign("ListaSolicitantes", "innerHTML", "Não foi encontrada pessoa com o critério selecionado.");
	}
	return $objResponse;
}

function CarregaEdicaoPessoa($CodPessoaEleicao=NULL) {
	$objResponse = new xajaxResponse();
    $Controlador = Controlador::instancia();
    if(!$Controlador->recuperaPessoaLogada()->eGerenteSistema())
        throw new Exception("Permissão negada", 0);

    if(!is_null($CodPessoaEleicao) && is_numeric($CodPessoaEleicao)) {
        $Pessoa = new PessoaEleicao($CodPessoaEleicao);
        if(!$Pessoa->valido())
            return $objResponse->alert("Pessoa inválida.");
        $IdentificacaoUsuario = $Pessoa->get("identificacaousuario");
        $NomePessoa = $Pessoa->get("nomepessoa");
        $LocalTrabalho = $Pessoa->get("localtrabalho");
        $NrRegistroGeral = $Pessoa->get("nrregistrogeral");
        $CPF = $Pessoa->get("cpf");
        $EMail = $Pessoa->get("email");
        $Gerente = $Pessoa->eGerenteSistema();
        $Solicitante = $Pessoa->eSolicitante();
    }
    else {
        $CodPessoaEleicao = $IdentificacaoUsuario = $NomePessoa =
        $LocalTrabalho = $NrRegistroGeral = $CPF = $EMail = null;
        $Gerente = $Solicitante = false;
    }

    $HTML = '
<h1>Edição de Pessoa</h1>

<form id="EdicaoPessoa" name="EdicaoPessoa">
<input type="hidden" name="codpessoaeleicao" value="'.$CodPessoaEleicao.'" />
<table>
    <tr class="Linha1">
        <td>Código do usuário:</td>
        <td><input type="text" size="30" name="identificacaousuario" value="'.$IdentificacaoUsuario.'" /></td>
    </tr>
    <tr class="Linha2">
        <td>Nome:</td>
        <td><input type="text" size="30" name="nomepessoa" value="'.$NomePessoa.'" /></td>
    </tr>
    <tr class="Linha1">
        <td>Local de Trabalho:</td>
        <td><input type="text" size="30" name="localtrabalho" value="'.$LocalTrabalho.'" /></td>
    </tr>
    <tr class="Linha2">
        <td>Registro geral:</td>
        <td><input type="text" size="30" name="nrregistrogeral" value="'.$NrRegistroGeral.'" /></td>
    </tr>
    <tr class="Linha1">
        <td>CPF:</td>
        <td><input type="text" size="30" name="cpf" value="'.$CPF.'" /></td>
    </tr>
    <tr class="Linha2">
        <td>E-Mail:</td>
        <td><input type="text" size="30" name="email" value="'.$EMail.'" /></td>
    </tr>
    <tr class="Linha1">
        <td colspan="2">
            <label>
            <input type="checkbox" name="gerentesistema" id="gerentesistema" value="S" '
                  .($Gerente ? ' checked="checked" ' : null)
                  .($CodPessoaEleicao == $Controlador->recuperaPessoaLogada()->getChave() ? 'disabled="disabled"' : null).' /> Gerente</label><br />
            <label>
            <input type="checkbox" name="solicitante" id="solicitante" value="S" '.($Solicitante ? 'checked="checked"' : null).' /> Solicitante</label>
        </td>
    </tr>
</table>

<div class="botoes">
    <input type="button" value="Cancelar" onclick="javascript: FechaLayer();" />
    <input type="button" value="Salvar" onclick="javascript: xajax_SalvaPessoa(xajax.getFormValues(\'EdicaoPessoa\'));" />
</div>
</form>
';
    $objResponse->assign("LayerEdicao", "innerHTML", $HTML);
    $objResponse->assign("LayerEdicao", "style.height", "370px");
    $objResponse->script('ExibeLayer()');
    return $objResponse;
}

function SalvaPessoa($Form) {
	$objResponse = new xajaxResponse();
    $Controlador = Controlador::instancia();
    if(!$Controlador->recuperaPessoaLogada()->eGerenteSistema())
        throw new Exception("Permissão negada", 0);

    if(isset($Form['codpessoaeleicao']) && is_numeric($Form['codpessoaeleicao'])) {
        $Pessoa = new PessoaEleicao($Form['codpessoaeleicao']);
        if(!$Pessoa->valido())
            return $objResponse->alert("Pessoa inválida.");
    }
    else
        $Pessoa = new PessoaEleicao();
    
    if(trim($Form['identificacaousuario']) == "")
        return $objResponse->alert("Preencha o código do usuário.");
    if(trim($Form['nomepessoa']) == "")
        return $objResponse->alert("Preencha o nome da pessoa.");
    if(trim($Form['nrregistrogeral']) == "")
        return $objResponse->alert("Preencha o Registro Geral da pessoa.");
    if(trim($Form['cpf']) == "")
        return $objResponse->alert("Preencha o CPF da pessoa.");
    if(trim($Form['email']) == "")
        return $objResponse->alert("Preencha o E-Mail da pessoa.");
    try {
        $Pessoa->set("identificacaousuario", $Form['identificacaousuario']);
        $Pessoa->set("nomepessoa", $Form['nomepessoa']);
        $Pessoa->set("localtrabalho", $Form['localtrabalho']);
        $Pessoa->set("nrregistrogeral", $Form['nrregistrogeral']);
        $Pessoa->set("cpf", $Form['cpf']);
        $Pessoa->set("email", $Form['email']);

        require_once("../Funcoes_Pessoa.php");
        $Resposta = HomologaPessoa($Pessoa->getAll());
        if(is_null($Resposta)) {
            $Pessoa->set("pessoaautenticada", "S");
        }
        else
            return $objResponse->alert("Aviso: os dados da pessoa não foram homologados.\nResposta: ".$Resposta);

        if($Pessoa->novo() || ($Pessoa->getChave() != $Controlador->recuperaPessoaLogada()->getChave())) {
            if(isset($Form['gerentesistema']) && ($Form['gerentesistema'] == "S"))
                $Pessoa->set("gerentesistema", "S");
            else
                $Pessoa->set("gerentesistema", "N");
        }

        if(isset($Form['solicitante']) && ($Form['solicitante'] == "S"))
            $Pessoa->set("solicitante", "S");
        else
            $Pessoa->set("solicitante", "N");

        $Pessoa->salva();
    }
    catch(EntidadeValorInvalidoException $e) {
        return $objResponse->alert("Valor inválido: ".$e->getMessage());
    }

    $Str = $Pessoa->get("nomepessoa");
    if(!$Pessoa->homologada())
        $Str .= ' <strong>(NÃO HOMOLOGADA)</strong>';
    if($Pessoa->eSolicitante())
        $Str .= ' <strong>(Solicitante)</strong>';
    if($Pessoa->eGerenteSistema())
        $Str .= ' <strong>(Gerente)</strong>';
    $objResponse->assign("Pessoa".$Form['codpessoaeleicao'], "innerHTML", $Str);
    $objResponse->script("FechaLayer()");
    return $objResponse;
}

$xajax->processRequest();
?>