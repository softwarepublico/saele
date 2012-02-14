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

$Campos = $_SESSION['Campos'];

MostraCabecalho("Solicitação de Concurso Eleitoral");

if(!isset($_SESSION['Valido'])) { ?>
    <div class="Erro">
        <p><strong>Solicitação inválida</strong>.</p>

        <p><input type="button" value="Voltar" onclick="javascript: location.href = 'SOL_Solicitacao.php';" /></p>
    </div>
    <?php
    exit;
}
?>

<br />

<table width="95%" border="0" cellspacing="0" cellpadding="0">
  <tr bgcolor="white">
    <td colspan="3">
      <font size="2" face="verdana"><b>&nbsp; Nome do Concurso:</b> <?=$Campos['NomeConcurso']?></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="3">
      <font size="2" face="verdana"><b>&nbsp; Per&iacute;odo:</b> De <?=$Campos['DataInicio']?> &agrave;s <?=$Campos['HoraInicio']?> at&eacute; <?=$Campos['DataFim']?> &agrave;s <?=$Campos['HoraFim']?></font>
    </td>
  </tr> 
  <tr bgcolor="white">
    <td>
      <font size="2" face="verdana"><b>&nbsp; Contato:</b> <?=$Campos['Contato']?></font>
    </td>
    <td>
      <font size="2" face="verdana"><b>Ramal:</b> <?=$Campos['RamalContato']?></font>
    </td>
    <td>
      <font size="2" face="verdana"><b>E-Mail:</b> <?=$Campos['EMail']?></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="3">
      <font size="2" face="verdana"><b>&nbsp; Elei&ccedil;&otilde;es do Concurso:</b><br /> &nbsp;&nbsp;&nbsp;
			<?=implode("<br /> &nbsp;&nbsp;&nbsp; ", $Campos['Eleicao'])?></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="3">
      <font size="2" face="verdana"><b>&nbsp; Tipo de Elei&ccedil;&atilde;o:</b>
        <?php switch($Campos['TipoEleicao']) {
          case "S": echo "Urna"; break;
          case "E": echo "Escopo"; break;
          case "N": echo "Livre"; break; }
        ?></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="3">
      <font size="2" face="verdana"><b>&nbsp; Observa&ccedil;&otilde;es adicionais:</b> <?=$Campos['Observacao']?></font>
    </td>
  </tr> 
</table>
<br />
<form action="SOL_Realiza_Solicitacao.php" method="POST" name="Form">
<div align="center">
  <font size="3" face="verdana"><b>Confirma os dados acima?</b></font><br /><br />

  <input type="button" value="Voltar" onclick="javascript: document.Form.action = 'SOL_Solicitacao.php'; document.Form.submit();" /> &nbsp;
  <input type="submit" value="Confirmar" />
</div>
</form>

</body>
</html>