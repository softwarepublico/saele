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
                       // Geração de Concurso Eleitoral a partir de Solicitação
require_once('../CABECALHO.PHP');

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

$NrSeqSolicitacaoConcurso = $_REQUEST['NrSeqSolicitacaoConcurso'];
$Solicitacao = new SolicitacaoConcurso($NrSeqSolicitacaoConcurso);
if(!$Solicitacao->valido())  { ?>
    <html><body>
        <div align="center">
        <font size="2" face="verdana">Erro! Solicitação inválida.<br /><br />
        <a href="javascript: history.back();">Voltar</a></font>
        </div>
    </body></html>
    <?php
    exit;
}

if(isset($_POST['NrSeqSolicitacaoConcurso'])) {
    $CodConcurso = $Solicitacao->get("codconcurso");
    if(!is_null($CodConcurso))
        $Concurso = new ConcursoEleitoral($CodConcurso);
    else
        $Concurso = NULL;

    $Descricao = $_POST['DescConcurso'];
    $DataInicio = $_POST['DataInicio'];
    $HoraInicio = $_POST['HoraInicio'];
    $DataFim = $_POST['DataFim'];
    $HoraFim = $_POST['HoraFim'];
    $IndBarradoPorIP = $_POST['IndBarradoPorIP'];
    $HabilitaContagem = $_POST['HabilitaContagem'];
    $ModalidadeConcurso = $Solicitacao->get("modalidadeconcurso");
}
elseif(!is_null($Solicitacao->get("codconcurso"))) {
    $CodConcurso = $Solicitacao->get("codconcurso");
    if(!is_null($CodConcurso))
        $Concurso = new ConcursoEleitoral($CodConcurso);
    else
        $Concurso = NULL;

    $Descricao = $Concurso->get("descricao");
    $DataInicio = $Concurso->get("datahorainicio", data);
    $HoraInicio = $Concurso->get("datahorainicio", hora);
    $DataFim = $Concurso->get("datahorafim", data);
    $HoraFim = $Concurso->get("datahorafim", hora);
    $IndBarradoPorIP = $Concurso->get("indbarradoporip");
    $HabilitaContagem = $Concurso->get("indhabilitacontagem");
    $ModalidadeConcurso = $Concurso->get("modalidadeconcurso");
}
else {
    $Concurso = $CodConcurso = NULL;
    $Descricao = $Solicitacao->get("nomeconcurso");
    $DataInicio = $Solicitacao->get("datainicioconcurso", data);
    $HoraInicio = $Solicitacao->get("datainicioconcurso", hora);
    $DataFim = $Solicitacao->get("datafimconcurso", data);
    $HoraFim = $Solicitacao->get("datafimconcurso", hora);
    $IndBarradoPorIP = $Solicitacao->get("IndBarradoPorIP");
    $ModalidadeConcurso = $Solicitacao->get("modalidadeconcurso");
    $HabilitaContagem = "S";
}
if($ModalidadeConcurso == "C")
  MostraCabecalho("Solicitação de Concursos Eleitorais");
else
  MostraCabecalho("Solicitação de Enquetes");
?>
<script language="javascript" src="../CODE/popcalendar.js"></script>

<script language="javascript">
        addHoliday(25,12,0,"Natal")
		addHoliday(1,1,0,"Ano Novo")
		monthName = new
		Array("Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro")
		showToday = 0
		dayName = new Array ("Dom","Seg","Ter","Qua","Qui","Sex","Sab")
		gotoString = "Vai para o mês atual"
		todayString = "Hoje é"
		weekString = "DS"
		imgDir = "../imagens/"
</script>

<script>
  function Atualiza(Form) {
    Form.action = '';
    Form.submit();
  }

  function LimpaMembroComissao(i) {
    document.getElementById('MembroComissao'+i).value = '';
    document.getElementById('CodMembroComissao'+i).value = 'X';
    document.getElementById('EGerenteMembroComissao'+i).checked = false;
  }
</script>

<br />
<form name="Form" action="ELC_Geracao_ConcursoCODE.php" method="POST">
<input type="hidden" name="NrSeqSolicitacaoConcurso" value="<?=$NrSeqSolicitacaoConcurso?>" />
<input type="hidden" name="CodConcurso" value="<?=$CodConcurso?>" />
<input type="hidden" name="IndicadorEleicao" value="<?=$IndicadorEleicao?>" />
<input type="hidden" name="Finalizar" value="S" />

<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr bgcolor="white">
    <td align="center" colspan="2">
        <font size="3" face="verdana"><b>Solicita&ccedil;&atilde;o de <?=$Solicitacao->retornaString(STR_CONCURSOELEITORAL)?></b></font>
      <hr />
    </td>
  </tr>
  <tr bgcolor="white">
    <td colspan="2">
      <font size="2" face="verdana">&nbsp; <b>Data da Solicita&ccedil;&atilde;o:</b>
      <?=$Solicitacao->get("datasolicitacao", data)?></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="2">
      <font size="2" face="verdana">&nbsp; <b>E-Mail para Contato:</b> <?=$Solicitacao->get("emailcontato")?></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="2">
      <hr />
    </td>
  </td>
  <tr bgcolor="white">
    <td colspan="2">
      <font size="2" face="verdana">&nbsp; Descri&ccedil;&atilde;o:</font>
      <input type="text" size="80" maxlength="120" name="DescConcurso" value="<?=$Descricao?>" />
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="2">
      <font size="2" face="verdana">&nbsp; Per&iacute;odo:</font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td width="20%">
      <font size="2" face="verdana">&nbsp;&nbsp;&nbsp; In&iacute;cio:</font>
    </td>
    <td>
      <input type="text" class="obrigatorio" name="DataInicio" id="DataInicio" size="10" maxlength="10" value="<?=$DataInicio?>" />
      &nbsp;
      <input type="button" onClick="javascript: popUpCalendar(this, document.getElementById('DataInicio'),'dd/mm/yyyy');" value="..." />

      &nbsp;&nbsp;&nbsp;

      <input type="text" class="obrigatorio" name="HoraInicio" size="5" maxlength="5" value="<?=$HoraInicio?>" />
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td width="20%">
      <font size="2" face="verdana">&nbsp;&nbsp;&nbsp; Fim:</font>
    </td>
    <td>
      <input type="text" class="obrigatorio" name="DataFim" id="DataFim" size="10" maxlength="10" value="<?=$DataFim?>" />
      &nbsp;
      <input type="button" onClick="javascript: popUpCalendar(this, document.getElementById('DataFim'),'dd/mm/yyyy');" value="..." />

      &nbsp;&nbsp;&nbsp;

      <input type="text" class="obrigatorio" name="HoraFim" size="5" maxlength="5" value="<?=$HoraFim?>" />
    </td>
  </tr>
  <tr bgcolor="white">
    <td width="20%">
      <font size="2" face="verdana">&nbsp; Tipo:</font>
    </td>
    <td>
      <font size="2" face="verdana">
        <input type="radio" name="IndBarradoPorIP" value="S" <?=($IndBarradoPorIP == "S" ? 'checked="checked"' : '')?> /> Com Urna
        <input type="radio" name="IndBarradoPorIP" value="E" <?=($IndBarradoPorIP == "E" ? 'checked="checked"' : '')?> /> Com Escopo
        <input type="radio" name="IndBarradoPorIP" value="N" <?=($IndBarradoPorIP == "N" ? 'checked="checked"' : '')?> /> Livre
      </font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td width="20%">
      <font size="2" face="verdana">&nbsp; Habilitar Contagem para <?=$Solicitacao->retornaString(STR_GERENTES)?>:</font>
    </td>
    <td>
      <font size="2" face="verdana">
        <input type="radio" name="HabilitaContagem" value="S" <?=($HabilitaContagem == "S" ? 'checked="checked"' : '')?> /> Sim
        <input type="radio" name="HabilitaContagem" value="N" <?=($HabilitaContagem == "N" ? 'checked="checked"' : '')?> /> N&atilde;o
      </font>
    </td>
  </tr>
  <?php if(!is_null($Solicitacao->get("observacao"))) { ?>
  <tr bgcolor="white">
    <td colspan="2">
      <font size="2" face="verdana">&nbsp; Observa&ccedil;&otilde;es adicionais:</font>
      <input type="text" size="80" maxlength="120" name="Observacao" value="<?=$Solicitacao->get("observacao")?>" />
    </td>
  </tr>
  <?php } ?>
</table>

<br />

<?php
if(!is_null($Concurso)) {
    $EditaEleicoes = false;
    $Eleicoes = $Concurso->devolveEleicoes();
    $QuantEleicoes = $NumEleicoes = count($Eleicoes);
} else {
    $EditaEleicoes = true;
    if(isset($_POST['QuantEleicoes'])) {
        $Eleicoes = $_POST['Eleicao'];
        $QuantEleicoes = $_POST['QuantEleicoes'];
        $NumEleicoes = ($QuantEleicoes > 15 ? $QuantEleicoes : 15);
    }
    else {
        $Eleicoes = $Solicitacao->devolveEleicoesSolicitacao();
        $QuantEleicoes = count($Eleicoes);
        $NumEleicoes = 15;
    }
} ?>

<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr bgcolor="white">
    <td colspan="2">
        Quantidade de <?=$Solicitacao->retornaString(STR_ELEICOES)?>:
        <select size="1" name="QuantEleicoes" <?=(is_null($Solicitacao->get("codconcurso")) ? 'onChange="javascript: Atualiza(document.Form);"' : 'disabled="disabled"')?> >
          <option value="0"> -- SELECIONE -- </option>
          <?php for($i = 1; $i <= $NumEleicoes; $i++) {
                echo "<option value=\"".$i."\" ";
                if ($i == $QuantEleicoes) echo "selected=\"selected\"";
                echo ">".$i."</option>\n";
                } ?>
        </select>
    </td>
  </tr>
  <?php
  $linha = "#f5f5f5";
  for($i = 1; $i <= $QuantEleicoes; $i++) {
      if(($Eleicoes instanceof Iterador) && ($Eleicao = $Eleicoes->proximo()))
          $Descricao = $Eleicao->get("descricao");
      elseif(isset($_POST['Eleicao'][$i]))
          $Descricao = $_POST['Eleicao'][$i];
      else $Descricao = NULL;
   ?>
    <tr bgcolor="<?=$linha?>">
      <td width="20%">
          <font size="2" face="verdana">&nbsp;&nbsp;&nbsp; <?=$Solicitacao->retornaString(STR_ELEICAO)?> <?=$i?>:</font>
      </td>
      <td>
        <input type="text" name="Eleicao[<?=$i?>]" size="80" maxlength="120" value="<?=$Descricao?>" <?=($EditaEleicoes ? null : 'readonly="readonly"')?> />
      </td>
    </tr>
  <?php
  $linha = ($linha == "#f5f5f5" ? "white" : "#f5f5f5");
  } ?>
	<tr><td>&nbsp;</td></tr>
	<tr class="Linha1">
	  <td colspan="2">
		  &nbsp; <input type="checkbox" name="Importar" value="S" /> Importar lista de participantes
		</td>
	</tr>
	<tr class="Linha2">
	  <td colspan="2" style="font-size: 10pt;">
		  <b>Aten&ccedil;&atilde;o:</b> os arquivos com as listas de participantes devem estar no
             diret&oacute;rio ARQUIVO, com o nome <b>arq_<?=$NrSeqSolicitacaoConcurso?>_[número da <?=$Solicitacao->retornaString(STR_ELEICAO)?>].txt</b>,
             com um arquivo para cada <?=$Solicitacao->retornaString(STR_ELEICAO)?>. Cada linha do arquivo
             representa um participante, e os dados devem estar dispostos da seguinte forma:<br />
			 <b>[código];[nome da pessoa];[registro geral];[CPF];[local de trabalho];[e-mail]</b>
		</td>
	</tr>
</table>

<br />

<div align="center">
  <input type="button" value="Voltar" onClick="javascript: location.href='ELC_Solicitacoes_Concursos.php';" /> &nbsp;
  <input type="button" value="Cadastrar" onClick="javascript: document.Form.Finalizar.value = 'N'; document.Form.submit();" /> &nbsp;
  <input type="button" value="Finalizar" onClick="javascript: document.Form.Finalizar.value = 'S'; document.Form.submit();" /> &nbsp;
  <input type="button" value="Imprimir" onClick="javascript: window.print();" />
</div>

</form>

<script src="../Geral.js"></script>
<script>
atributos('DescConcurso', "Descrição do Concurso", texto, true);
atributos('DataInicio', "Data de Início", data, true);
atributos('DataFim', "Data de Fim", data, true);
atributos('HoraInicio', "Hora de Início", hora, true);
atributos('HoraFim', "Hora de Fim", hora, true);
</script>
</body></html>