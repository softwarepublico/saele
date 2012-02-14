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

require_once('../CABECALHO.PHP');
require_once('../Funcoes_Pessoa.php');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

if(!$Pessoa->eGerenteSistema()) {
  echo "<html><body>\n";
  echo "<div align=\"center\">\n";
  echo "<br><font size=\"2\" face=\"verdana\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.<br><br>\n";
  echo "<a href=\"javascript: history.back();\">Voltar</a></font>\n";
  echo "</div>";
  echo "</body></html>";
  exit;
}

$MSG = null;
$Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
$Eleicoes = $Concurso->devolveEleicoes();

foreach($Eleicoes as $CodEleicao => $Eleicao) {
    $MSG .= "<b>Descri&ccedil;&atilde;o: ".$Eleicao->get("descricao")."</b><br /><br />";
    $erro = false;

    $Pessoas = $Eleicao->devolvePessoasNaoHomologadas();
    foreach($Pessoas as $Pessoa) {
        $Retorno = HomologaPessoa($Pessoa->getAll());
        if(is_null($Retorno)) {
            $Pessoa->set("pessoaautenticada", "S");
            $Pessoa->salva();
        }
        else {
            $erro = true;
            $MSG .= "A pessoa ".$Pessoa->get("nomepessoa")>" não foi homologada. Resposta: ".$Retorno."<br />";
        }
    }
    if(!$erro) $MSG .= "Usu&aacute;rios validados sem erros.<br />";
    $MSG .= "<br />";
}
if(isset($_GET['Final'])) {
    $Concurso->set("situacaoconcurso", SITUACAOCONCURSO_HOMOLOGADO);
    $Concurso->salva();
}

MostraCabecalho("Validação de Concurso Eleitoral");
?>
<div align="center">
<br />
<font size="2" face="verdana"><?=$MSG?></font>
<br /><br />
<input type="button" value="Voltar" onClick="javascript: location.href='ELC_Cadastro_Concursos.php';" />
</div>
</body></html>