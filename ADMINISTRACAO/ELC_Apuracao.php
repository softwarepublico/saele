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
                       // Página para apuração dos votos de determinada Eleição
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

if(isset($_POST['CodConcurso']) && isset($_POST['CodEleicao'])) {
    $Concurso = new ConcursoEleitoral($_POST['CodConcurso']);
    $Eleicao = $Concurso->devolveEleicao($_POST['CodEleicao']);

    try {
        $Eleicao->realizaZeresima();
    }
    catch(EleicaoException $e) {
        MostraCabecalho("Apuração da Eleição"); ?>
        <div class="Erro">
            <p><strong>Erro:</strong> <?=$e->getMessage()?>.</p>

            <p><a href="ELC_Cadastro_Concursos.php">Voltar</a></p>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
}
else {
    $Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
    $Eleicao = $Concurso->devolveEleicao($_GET['CodEleicao']);

    $Comissao = $Eleicao->verificaComissao($Pessoa);

    if(!$Pessoa->eGerenteSistema() && ($Comissao == false)) { ?>
        <div class="Erro">
            <p><strong>Erro:</strong> Permissão negada.</p>

            <p><a href="ELC_Cadastro_Concursos.php">Voltar</a></p>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
    if( ( ($Concurso->estadoConcurso() == CONCURSO_INICIADO) && $Eleicao->eleicaoZerada() )
     || (($Concurso->estadoConcurso() == CONCURSO_NAOINICIADO)
         && !$Pessoa->eGerenteSistema() && ($Comissao != COMISSAO_GERENTE))) { ?>
        <div class="Erro">
            <p><strong>Erro:</strong> Esta eleição está indisponível para visualização dos votos.</p>

            <p><a href="ELC_Cadastro_Concursos.php">Voltar</a></p>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
    if($Concurso->estadoConcurso() == CONCURSO_ENCERRADO && ($Concurso->get("situacaoconcurso") != SITUACAOCONCURSO_APURADO)) { ?>
        <div class="Erro">
            <p><strong>Erro:</strong> A contagem de votos ainda não foi efetuada.</p>

            <p><a href="ELC_Cadastro_Concursos.php">Voltar</a></p>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
}

MostraCabecalho("Apuração da Eleição");
?>

<script language="javascript">
    function MostraCandidatos(Chapa){
      el = document.getElementById('D'+Chapa);
      el.style.display = (el.style.display == 'block') ? 'none' : 'block';
    }
        
        function Confirma(Form) {
                if (confirm('Tem certeza que deseja zerar os votos?'))
                     return true;
                else
                     return false;
        }
</script>

<table width="100%" cellspacing="0" cellpadding="0" align="center">
    <tr class="LinhaHR">
        <td colspan="4">
            <hr />
        </td>
    </tr>
    <tr class="linha1">
        <td width="20%" align="right">
            <b><?=$Concurso->retornaString(STR_CONCURSOELEITORAL)?>:&nbsp;</b>
        </td>
        <td width="80%" align="left">
            <?=$Concurso->get("descricao")?>
        </td>
    </tr>
    <tr class="linha2">
        <td width="20%" align="right">
            <b><?=$Concurso->retornaString(STR_ELEICAO)?>:&nbsp;</b>
        </td>
        <td width="80%" align="left">
            <?=$Eleicao->get("descricao")?>
        </td>
    </tr>

    <tr class="LinhaHR">
        <td colspan="4">
            <hr />
        </td>
    </tr>
</table>

<br />

<table border="1" width="85%" cellspacing="0" cellpadding="0" align="center">
    <tr bgcolor="d3d3d3">
        <td width="70%">
            <font size="2" face="verdana">
                <b><?=$Concurso->retornaString(STR_CHAPA)?></b>
            </font>
        </td>
        <td width="30%">
            <font size="2" face="verdana">
                <b>Votos recebidos</b>
            </font>
        </td>
    </tr>
<?php
$Chapas = $Eleicao->devolveChapas();
$i = 1;
foreach($Chapas as $CodChapa => $Chapa) {
    $Candidatos = $Chapa->devolveCandidatos(); ?>
    <tr class="linha<?=$i?>">
        <td style="font-family:Verdana; font-size: 10pt;">
        &nbsp;Chapa <?=$Chapa->get("nrchapa")?> - <?=$Chapa->get("descricao")?>
        <?php
        if($Candidatos->temRegistro()) { ?>
            <a href="javascript: MostraCandidatos(<?=$Chapa->get("nrchapa")?>);">[Mostrar Candidatos]</a>
            <div ID="D<?=$Chapa->get("nrchapa")?>" style="display: block;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabela" align="center">
            <?php
            foreach($Candidatos as $Candidato) {
                $Participacao = $Candidato->getObj("Participacao");
                $Pessoa = $Candidato->getObj("PessoaEleicao"); ?>
                <tr class="linha<?=i?>">
                    <td>
                        <font size="2" face="verdana">
                        &nbsp;&nbsp;&nbsp;<b><?=$Participacao->get("descricaoparticipacao")?></b> - <?=$Pessoa->get("nomepessoa")?>
                        </font>
                    </td>
                </tr>
            <?php
            }  ?>
            </table>
            </div>
        <?php
        } ?>
        </td>
        <td valign="top">
          <font size="2" face="verdana">
            &nbsp;<?=$Chapa->get("nrvotosrecebidos")?>
            </font>
        </td>
    </tr>
<?php
} ?>
        <tr bgcolor="d3d3d3">
            <td align="right">
                <font size="2" face="verdana">
                    <b>Votos em Branco: &nbsp; </b>
                </font>
            </td>
            <td>
                <font size="2" face="verdana">
                    <b>&nbsp;<?=$Eleicao->get("votosbrancos")?></b>
                </font>
            </td>
        </tr>
        <tr bgcolor="d3d3d3">
            <td align="right">
                <font size="2" face="verdana">
                    <b>Votos Nulos: &nbsp; </b>
                </font>
            </td>
            <td>
                <font size="2" face="verdana">
                    <b>&nbsp;<?=$Eleicao->get("votosnulos")?></b>
                </font>
            </td>
        </tr> 
</table>
<?php

if (is_null($Eleicao->get("votosbrancos")) && is_null($Eleicao->get("votosnulos"))
 && ($Pessoa->eGerenteSistema() || ($Comissao == COMISSAO_GERENTE)) && ($Concurso->get("situacaoconcurso") == SITUACAOCONCURSO_HOMOLOGADO)) { ?>
     <form name="zeresima" action="" method="post">
     <div align="center">
         <input type="hidden" name="CodConcurso" value="<?=$Eleicao->get("codconcurso")?>">
         <input type="hidden" name="CodEleicao" value="<?=$Eleicao->get("codeleicao")?>">
         <input type="submit" name="enviar" value="Zerar Votos" onClick="javascript: return(Confirma(zeresima));">
     </div>
     </form>
     <br />
<?php
}
elseif($Concurso->estadoConcurso() == CONCURSO_ENCERRADO) {
    $Eleitores = count($Eleicao->devolveEleitores());
    $Votantes  = count($Eleicao->devolveEleitores(ELEITOR_JAVOTOU));

    $Contagem = LogOperacao::getLogPorDescricao(DESCRICAO_CONTAGEM, $Concurso, null)
    ?>
    <br />
    <table width="85%" border="1" cellspacing="0" cellpadding="0" align="center">
        <tr class="LinhaTitulo">
            <td align="right" width="70%">
                    <b>Total de Eleitores: &nbsp; </b>
            </td>
            <td width="30%">
                    <b>&nbsp;<?=$Eleitores?></b>
            </td>
        </tr>
        <tr class="LinhaTitulo">
            <td align="right">
                <font size="2" face="verdana">
                    <b>Total de Votantes Efetivos: &nbsp; </b>
                </font>
            </td>
            <td>
                <font size="2" face="verdana">
                    <b>&nbsp;<?=$Votantes?></b>
                </font>
            </td>
        </tr>
    </table>

    <table width="85%" border="0" cellspacing="0" cellpadding="0" class="tabela" align="center">
        <tr>
            <td>
            Contagem dos votos realizada em <?=$Contagem->get("dataoperacao", data)?>, &agrave;s <?=$Contagem->get("dataoperacao", hora)?>.
            </td>
        </tr>
        <?php
        $Recontagens = LogOperacao::getIteradorLogsPorDescricao(DESCRICAO_RECONTAGEM, $Concurso);
        foreach($Recontagens as $Recontagem) { ?>
        <tr>
            <td>
            Recontagem dos votos realizada em <?=$Recontagem->get("dataoperacao", data)?>, &agrave;s <?=$Recontagem->get("dataoperacao", hora)?>.
            </td>
        </tr>
        <?php
        } ?>
        <tr>
            <td class="Linha2">
                <input type="button" value="Pesquisar Eleitores" onClick="javascript: window.open('ELC_Consulta_Eleitor.php?CodConcurso=<?=$Concurso->getChave()?>&amp;CodEleicao=<?=$Eleicao->getChave()?>', 'ooo', 'status=0,width=620,height=250');" /> &nbsp;
            </td>
        </tr>
    </table>
<br />
<?php
} ?>

<div align="center">
<?php
if (($Concurso->get("indbarradoporip") == 'S') && ($Concurso->estadoConcurso() == CONCURSO_ENCERRADO)) { ?>
    <a href="ELC_Apuracao_Urna.php?CodConcurso=<?=$Eleicao->get("codconcurso")?>&amp;CodEleicao=<?=$Eleicao->get("codeleicao")?>">Mapa de Urnas</a>
    <br /><br />
<?php } ?>

<a href="ELC_Relatorio_Votos.php?CodConcurso=<?=$Eleicao->get("codconcurso")?>&amp;CodEleicao=<?=$Eleicao->get("codeleicao")?>&ord=nr">Relat&oacute;rio de Votos (por n&uacute;mero de <?=$Concurso->retornaString(STR_CHAPA)?>)</a><br />
<a href="ELC_Relatorio_Votos.php?CodConcurso=<?=$Eleicao->get("codconcurso")?>&amp;CodEleicao=<?=$Eleicao->get("codeleicao")?>&ord=votos">Relat&oacute;rio de Votos (por ordem de coloca&ccedil;&atilde;o)</a>

<br /><br />
<?php
if ($Concurso->estadoConcurso() == CONCURSO_ENCERRADO) { ?>
    <a href="ELC_Lista_Eleitores.php?CodConcurso=<?=$Eleicao->get("codconcurso")?>&amp;CodEleicao=<?=$Eleicao->get("codeleicao")?>">Lista de Votantes</a><br /><br />
<?php
} ?>
<a href="ELC_Cadastro_Concursos.php">Voltar</a>
</div>

</body>
</html>