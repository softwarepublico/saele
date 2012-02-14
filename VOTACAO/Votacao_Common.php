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

$xajax = new xajax('Votacao_Common.php');
//$xajax->setFlag('debug', true);
$xajax->setCharEncoding("iso-8859-1");

$xajax->register(XAJAX_FUNCTION, "VerificaVoto"); 
$xajax->register(XAJAX_FUNCTION, "Acao"); 
$xajax->register(XAJAX_FUNCTION, "DefineLotacao");

function VerificaVoto($Form) {
    $Controlador = Controlador::instancia();
    $Concurso = $Controlador->recuperaConcursoVotacao();
    $Eleicao = $Controlador->recuperaEleicaoVotacao();

    $objResponse = new xajaxResponse();

    $Valor = $Form['campoCedula'];
    $Str = NULL;
    $MostraLink = false;
    $TemLotacaoCandidato = false;

    if($Valor == "B") {
        $Str = '
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td colspan="3" class="NomeChapa">
                        VOTO EM BRANCO
                        </td>
                    </tr>
                </table> ';
        $MostraLink = true;
        $Chapa = NULL;
    }
    elseif($Valor == "N") {
        $Str = '
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td colspan="3" class="NomeChapa">
                        VOTO NULO
                        </td>
                     </tr>
                </table> ';
        $MostraLink = true;
		$Chapa = NULL;
    }
    elseif(strlen($Valor) < $Eleicao->get("nrdigitoschapa")) {
        $Str = '
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td colspan="3" class="Chapa"> ';
        if($Concurso->get("modalidadeconcurso") == "E")
            $Str .= 'Digite seu voto';
        else
            $Str .= 'Digite sua resposta';

        $Str.= '
                        </td>
                    </tr>
                </table> ';
        $MostraLink = true;
        $Chapa = NULL;
    }
    else {
        $Chapa = $Eleicao->devolveChapaPorNumero($Valor);
        if(!is_null($Chapa)) {
            $Indice = 1;
            $NomeArqChapa = "../FOTOS/CHAPA_".$Concurso->getChave()."_".$Eleicao->getChave()."_".$Chapa->getChave().".JPG";
            $ImagemChapa = file_exists($NomeArqChapa);

            $Candidatos = $Chapa->devolveCandidatos();

            $MostraLink = (count($Candidatos) > 1);
            $ImagemLink = ($MostraLink && $ImagemChapa);

            $Width = ($MostraLink ? '50%' : '80%');

            $Str  = '
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td colspan="3" class="NomeChapa">'.$Chapa->get("descricao");
            if($ImagemLink)
                $Str .= ' <a href="javascript: void(0);" onclick="javascript: DefineImagemChapa('.$Chapa->getChave().'); DeselecionaCandidato();">[IMAGEM]</a>';
            $Str .= '
                        </td>
                    </tr> ';

            $TemLotacaoCandidato = false;
            $c = 1;
            foreach($Candidatos as $Candidato) {
                $PessoaCand = $Candidato->getObj("PessoaEleicao");
                $Participacao = $Candidato->getObj("Participacao");

                $NomeArqCand = "../FOTOS/CAND_".$Concurso->getChave()."_".$Eleicao->getChave()."_".$Chapa->getChave()."_".$PessoaCand->getChave().".JPG";
                $ImagemCand = file_exists($NomeArqCand);

                $TemLotacaoCandidato = $TemLotacaoCandidato || (trim($PessoaCand->get("localtrabalho")) != "");

                $Str .= '
                    <tr bgcolor="#f5f5f5">
                        <td width="20%" style="text-align: right; font-family: verdana; font-size:8pt; font-weight: bold;"> '.$Participacao->get("descricaoparticipacao").':</td>
                        <td width="'.$Width.'" style="font-family: verdana; font-size:8pt;"> &nbsp; <span id="Cand'.$Indice.'" style="background-color: '.(!$ImagemChapa && ($Indice == 1) ? '#ffff50' : '#f5f5f5').';">'.$PessoaCand->get("nomepessoa").'</span> </td> ';

                if($MostraLink || $ImagemCand || (trim($PessoaCand->get("localtrabalho")) != "")) {
                    $Str .= ' <td width="30%" style="font-family: verdana; font-size:8pt;"> ';
                    $Str .= ' &nbsp; <a href="javascript: void(0);" onclick="javascript: AtualizaCor('.$Indice.'); ';
                    if(trim($PessoaCand->get("localtrabalho")) != "")
                        $Str .= 'xajax_DefineLotacao('.$PessoaCand->getChave().');';

                    if($ImagemCand) {
                        $Str .= 'DefineImagemCandidato('.$Chapa->getChave().','.$c.');';
                        $c++;
                    }
                    else
                        $Str .= 'DefineImagemChapa('.$Chapa->getChave().');';

                    $Str .= '"> [DETALHES]</a> ';
                    $Str .= '
                        </td> ';
                }
                else $Str .= '
                        <td>&nbsp;</td>';
                $Str .= '
                    </tr> ';
                $Indice++;
            }
        }
        else {
            $objResponse->assign("campoCedula", "value", "N");
            $Str = '
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr bgcolor="#f5f5f5">
                        <td colspan="3" style="text-align: center; font-family: verdana; font-size:10pt; font-weight:bold;">
                        VOTO NULO
                        </td>
                    </tr>
                </table> ';
        }
    }
    if(!is_null($Chapa))
        $objResponse->script('DefineImagemChapa('.$Chapa->getChave().')');
    else
        $objResponse->script('DefineImagemNada()');

    if($MostraLink || !$TemLotacaoCandidato)
        $objResponse->assign("Lotacao", "style.visibility", "hidden")
                    ->assign("ItensLotacao", "innerHTML", NULL);
    else
        $objResponse->loadCommands(DefineLotacao($PessoaCand));

    $objResponse->assign('DivChapa', 'innerHTML', $Str);
    return $objResponse;
}

function Acao($Form, $Acao) {
    $Controlador = Controlador::instancia();
    $Concurso = $Controlador->recuperaConcursoVotacao();
    $Eleicao = $Controlador->recuperaEleicaoVotacao();
    $VotoAtual = $Controlador->devolveNrVotoAtual();

    $objResponse = new xajaxResponse();

	$Voto = $Form['campoCedula'];
	if($Voto != 'B' && $Voto != 'N') {
        if(trim($Voto) == "")
            $Voto = 'B';
        elseif(is_numeric($Voto)) {
            if(strlen($Voto) < $Eleicao->get("nrdigitoschapa"))
                $Voto = 'N';
            else {
                $Chapa = $Eleicao->devolveChapaPorNumero($Voto);
                if(is_null($Voto))
                    $Voto = 'N';
                else {
                    $PosicaoVoto = array_search($Voto, $Controlador->devolveVetorCedula());
                    if(($PosicaoVoto !== false) && ($PosicaoVoto != $VotoAtual)) {
                        return $objResponse->alert("Atenção! Os votos não podem ser repetidos.");
                    }
                    $Voto = $Chapa->get("nrchapa");
                }
            }
        }
        else
            $Voto = 'N';
	}
	if($Eleicao->get("nrpossibilidades") > 1) {
        if($Acao == "C") { // CONFIRMA
            $Controlador->registraVoto($Voto);
            $objResponse->assign("botao_voto_".$VotoAtual, "value", $Voto)
                        ->script("Confirma();");
        }
        elseif(($Acao == "A") && ($VotoAtual > 1)) { // VOTO ANTERIOR
            $NovoVoto = $VotoAtual - 1;
            $Controlador->registraVoto($Voto);
            $Controlador->registraNrVotoAtual($NovoVoto);
            $objResponse->assign("botao_voto_".$VotoAtual, "value", $Voto)
                        ->assign("botao_voto_".$VotoAtual, "style.backgroundColor", "fff5e5")
                        ->assign("botao_voto_".$NovoVoto, "style.backgroundColor", "ffb5a5")
                        ->assign("campoCedula", "value", $Controlador->devolveVoto($NovoVoto));
            if($NovoVoto == 1)
                $objResponse->assign("BotaoVotoAnterior", "innerHTML", 'PRIMEIRO VOTO');
            $objResponse->assign("BotaoVotoPosterior", "innerHTML", '<input type="button" name="botao" value="PRÓXIMO VOTO &gt;&gt; (ENTER)" onclick="javascript: xajax_Acao(xajax.getFormValues(\'FormCedula\'), \'P\');" />');
            $objResponse->script("xajax_VerificaVoto(xajax.getFormValues('FormCedula'));");
        }
        elseif(($Acao == "P") && ($VotoAtual < $Eleicao->get("nrpossibilidades"))) { // VOTO POSTERIOR
            $NovoVoto = $VotoAtual + 1;
            $Controlador->registraVoto($Voto);
            $Controlador->registraNrVotoAtual($NovoVoto);
            $objResponse->assign("botao_voto_".$VotoAtual, "value", $Voto)
                        ->assign("botao_voto_".$VotoAtual, "style.backgroundColor", "fff5e5")
                        ->assign("botao_voto_".$NovoVoto, "style.backgroundColor", "ffb5a5")
                        ->assign("campoCedula", "value", $Controlador->devolveVoto($NovoVoto));
            if($NovoVoto == $Eleicao->get("nrpossibilidades"))
                $objResponse->assign("BotaoVotoPosterior", "innerHTML", 'ÚLTIMO VOTO');
            $objResponse->assign("BotaoVotoAnterior", "innerHTML", '<input type="button" name="botao" value="&lt;&lt; VOTO ANTERIOR (ESC)" onclick="javascript: xajax_Acao(xajax.getFormValues(\'FormCedula\'), \'A\');" />');
            $objResponse->script("xajax_VerificaVoto(xajax.getFormValues('FormCedula'));");
        }
        elseif(is_numeric($Acao) && ($Acao >= 1) && ($Acao <= $Eleicao->get("nrpossibilidades"))) { // VOTO ALEATÓRIO
            $NovoVoto = $Acao;
            $Controlador->registraVoto($Voto);
            $Controlador->registraNrVotoAtual($NovoVoto);
            $objResponse->assign("botao_voto_".$VotoAtual, "value", $Voto)
                        ->assign("botao_voto_".$VotoAtual, "style.backgroundColor", "fff5e5")
                        ->assign("botao_voto_".$NovoVoto, "style.backgroundColor", "ffb5a5")
                        ->assign("campoCedula", "value", $Controlador->devolveVoto($NovoVoto));

            if($NovoVoto == 1)
                $objResponse->assign("BotaoVotoAnterior", "innerHTML", 'PRIMEIRO VOTO');
            else
                $objResponse->assign("BotaoVotoAnterior", "innerHTML", '<input type="button" name="botao" value="&lt;&lt; VOTO ANTERIOR (ESC)" onclick="javascript: xajax_Acao(xajax.getFormValues(\'FormCedula\'), \'A\');" />');

            if($NovoVoto == $Form['NrVotos'])
                $objResponse->assign("BotaoVotoPosterior", "innerHTML", 'ÚLTIMO VOTO');
            else
                $objResponse->assign("BotaoVotoPosterior", "innerHTML", '<input type="button" name="botao" value="PR&Oacute;XIMO VOTO &gt;&gt; (ENTER)" onclick="javascript: xajax_Acao(xajax.getFormValues(\'FormCedula\'), \'P\');" />');
            $objResponse->script("xajax_VerificaVoto(xajax.getFormValues('FormCedula'));");
		}
	}
	elseif($Acao == "C") {
        $Controlador->registraVoto($Voto);
		$objResponse->script("Confirma();");
	}
	return $objResponse;
}

function DefineLotacao($Pessoa = null) {
    $objResponse = new xajaxResponse();

    if(!is_null($Pessoa) && is_numeric($Pessoa))
        $Pessoa = new PessoaEleicao($Pessoa);
    elseif(!is_null($Pessoa) && !($Pessoa instanceof PessoaEleicao))
        throw new Exception("Pessoa inválida", 0);

    if(is_null($Pessoa)) {
        $objResponse->assign("Lotacao", "style.visibility", "hidden")
                    ->assign("ItensLotacao", "innerHTML", NULL);
    }
    else {
        if(!is_null($Pessoa->get("localtrabalho")))
            $Str = " &nbsp; - ".$Pessoa->get("localtrabalho");

        $objResponse->assign("Lotacao", "style.visibility", "visible")
                    ->assign("ItensLotacao", "innerHTML", $Str);
    }

    return $objResponse;
}

$xajax->processRequest();
?>