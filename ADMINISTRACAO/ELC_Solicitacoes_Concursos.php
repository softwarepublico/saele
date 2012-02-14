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
                       // Lista de Solicitações Pendentes
require_once('../CABECALHO.PHP');

error_reporting(E_PARSE | E_ERROR);

$Pessoa = Controlador::instancia()->recuperaPessoaLogada();
if(!$Pessoa->eGerenteSistema()) {
    echo "<html><body>\n";
    echo "<div align=\"center\">\n";
    echo "<br><font size=\"2\" face=\"verdana\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.<br><br>\n";
    echo "<a href=\"javascript: history.back();\">Voltar</a></font>\n";
    echo "</div>";
    echo "</body></html>";
    exit;
}
$Solicitacoes = new Iterador("SolicitacaoConcurso", "where dataatendimento is null order by datasolicitacao desc");

MostraCabecalho("Solicitação de Concursos Eleitorais");
?>

<br />

<table width="85%" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr bgcolor="white">
    <td align="center">
      <font size="2" face="verdana"><b>Lista de Solicitações Pendentes</b></font>
    </td>
  </tr>
  <?php if($Solicitacoes->temRegistro()) { ?>
    <tr bgcolor="white">
      <td>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <?php foreach($Solicitacoes as $NrSeqSolicitacaoConcurso => $Solicitacao) { ?>
          <tr>
            <td width="10%">
              <font size="2" face="verdana"><?=$NrSeqSolicitacaoConcurso?>: </font>
            </td>
            <td width="90%">
              <a href="ELC_Geracao_Concurso.php?NrSeqSolicitacaoConcurso=<?=$NrSeqSolicitacaoConcurso?>">
                <font size="2" face="verdana">
                <?=$Solicitacao->get("nomeconcurso")?>
                </font>
              </a>
            </td>
          </tr>
        <?php } ?>
        </table>
      </td>
    </tr>
  <?php } else { ?>
    <tr bgcolor="white">
      <td align="center">
        <br />
        <font size="2" face="verdana">Não há solicitações pendentes.</font>
        <br /><br />
      </td>
    </tr>
  <?php } ?>
</table>

<br />
<div align="center">
  <input type="button" value="Voltar" onClick="javascript: location.href='ELC_Cadastro_Concursos.php';" />
</div>
</body>
</html>