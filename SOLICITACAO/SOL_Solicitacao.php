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

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

if(!$Pessoa->eSolicitante()) {
    echo '
    <div class="Erro">
        <p><strong>Erro!</strong> O usuário não tem permissão para acessar esta página.</p>
        <p><a href="../ELC_Logout.php">Voltar</a></p>
    </div>
    </body></html> ';
    exit;
}

$ConsultaDias = new consulta(" select now() + interval '20 days' as vintedias ");
$ConsultaDias->executa(true);
$VinteDias = $ConsultaDias->campo("vintedias", datahora);

if(isset($_SESSION['Campos']))
    $Campos = $_SESSION['Campos'];
else
    $Campos = array();

MostraCabecalho("Solicitação de Concurso Eleitoral");
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

<form name="Form" action="SOL_Termo_Compromisso.php" method="post">

<br />
<div align="center">
  <font size="3" face="verdana"><b>Formulário para Solicitação de Eleições</b></font>
</div>
<br />

<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr bgcolor="white">
    <td colspan="2">
      <font size="2" face="verdana"><b>&nbsp; Nome do Concurso:</b></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="2">
      &nbsp;&nbsp;&nbsp; <input name="NomeConcurso" type="text" size="60" maxlength="120" value="<?=(isset($Campos['NomeConcurso']) ? $Campos['NomeConcurso'] : null)?>" />
    </td>
  </tr>
  <tr bgcolor="white">
    <td colspan="2">
      <font size="2" face="verdana"><b>&nbsp; Per&iacute;odo:</b></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="2">
      <font size="2" face="verdana">
        &nbsp;&nbsp;&nbsp; De <input name="DataInicio" id="DataInicio" type="text" size="10" maxlength="10" value="<?=(isset($Campos['DataInicio']) ? $Campos['DataInicio'] : null)?>" readonly="readonly" />
          <input type="button" value=" ... " onclick="javascript: popUpCalendar(this, document.getElementById('DataInicio'),'dd/mm/yyyy');" />
          &agrave;s <input name="HoraInicio" id="HoraInicio" type="text" size="8" maxlength="5" value="<?=(isset($Campos['HoraInicio']) ? $Campos['HoraInicio'] : null)?>" />
          <b>Aten&ccedil;&atilde;o:</b> s&oacute; ser&atilde;o aceitas elei&ccedil;&otilde;es com data de in&iacute;cio a partir de <?=$VinteDias?> <br />
        &nbsp;&nbsp;&nbsp; At&eacute; <input name="DataFim" id="DataFim" type="text" size="10" maxlength="10" value="<?=(isset($Campos['DataFim']) ? $Campos['DataFim'] : null)?>" readonly="readonly" />
          <input type="button" value=" ... " onclick="javascript: popUpCalendar(this, document.getElementById('DataFim'),'dd/mm/yyyy');" />
          &agrave;s <input name="HoraFim" id="HoraFim" type="text" size="8" maxlength="5" value="<?=(isset($Campos['HoraFim']) ? $Campos['HoraFim'] : null)?>" /> </font>
    </td>
  </tr>
  <tr bgcolor="white">
    <td colspan="2">
      <font size="2" face="verdana"><b>&nbsp; Contato:</b></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="2">
      &nbsp;&nbsp;&nbsp; <input name="Contato" id="Contato" type="text" size="60" maxlength="120" style="font-family: Verdana, Tahoma, Arial, sans-serif; color: #000000; background-color: #FFFFA0; font-size: 12px;" value="<?=(isset($Campos['Contato']) ? $Campos['Contato'] : null)?>" />
    </td>
  </tr>
  <tr bgcolor="white">
    <td colspan="2">
      <font size="2" face="verdana"><b>&nbsp; Ramal:</b></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="2">
      &nbsp;&nbsp;&nbsp; <input name="RamalContato" type="text" size="10" maxlength="5" value="<?=(isset($Campos['RamalContato']) ? $Campos['RamalContato'] : null)?>" />
    </td>
  </tr>
  <tr bgcolor="white">
    <td colspan="2">
      <font size="2" face="verdana"><b>&nbsp; E-Mail:</b></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="2">
      &nbsp;&nbsp;&nbsp; <input name="EMail" type="text" size="60" maxlength="60" value="<?=(isset($Campos['EMail']) ? $Campos['EMail'] : null)?>" />
    </td>
  </tr>
    <tr bgcolor="white">
      <td colspan="2">
        <font size="2" face="Verdana"><b>&nbsp; Elei&ccedil;&otilde;es do Concurso: </b></font>
      </td>
    </tr>
    <tr bgcolor="#f5f5f5">
      <td colspan="2">
        <?php
        if(!isset($Campos['Eleicao'])) { ?>
            &nbsp;&nbsp;&nbsp; <input type="text" name="Eleicao[]" id="Eleicao_0" size="60" value="" />
            <input type="button" id="AdicionaEl" value="+" onclick="javascript: AdicionaEleicao();" />
            <input type="button" id="RemoveEl" value="&minus;" onclick="javascript: RemoveEleicao();" disabled="disabled" />
            <br />
            <div id="EleicoesConcurso">
        <?php
        }
        else foreach($Campos['Eleicao'] as $Indice => $Descr) {
          if($Indice == 1) echo '<div id="EleicoesConcurso">'; ?>
          &nbsp;&nbsp;&nbsp; <input type="text" name="Eleicao[]" id="Eleicao_<?=$Indice?>" size="60" value="<?=$Descr?>" />
            <?=($Indice == 0 ? '<input type="button" value="+" onclick="javascript: AdicionaEleicao();" />' : NULL)?>
            <?=($Indice == 0 ? '<input type="button" value="&minus;" onclick="javascript: RemoveEleicao();" '
                              .(count($Campos['Eleicao']) > 1 ? NULL : 'disabled="disabled"').' />' : NULL)?><br />
        <?php } ?>
        </div>
      </td>
    </tr>
    <tr bgcolor="white">
      <td colspan="2">
        <font size="2" face="Verdana"><b>&nbsp; Tipo de Elei&ccedil;&atilde;o: </b></font>
      </td>
    </tr>
    <tr bgcolor="#f5f5f5">
      <td colspan="2">
        <font size="2" face="Verdana">&nbsp;&nbsp;&nbsp;
          <input name="TipoEleicao" id="TipoEleicaoU" type="radio" value="S" <?=(!isset($Campos['TipoEleicao']) || ($Campos['TipoEleicao'] == "S") ? 'checked="checked"' : '')?> />
            <label for="TipoEleicaoU">Urna</label>
          <input name="TipoEleicao" id="TipoEleicaoE" type="radio" value="E" <?=(isset($Campos['TipoEleicao']) && ($Campos['TipoEleicao'] == "E") ? 'checked="checked"' : '')?> />
            <label for="TipoEleicaoE">Escopo</label>
          <input name="TipoEleicao" id="TipoEleicaoR" type="radio" value="N" <?=(isset($Campos['TipoEleicao']) && ($Campos['TipoEleicao'] == "N") ? 'checked="checked"' : '')?>>
            <label for="TipoEleicaoR">Livre</label>
        </font>
      </td>
    </tr>
    <tr bgcolor="white">
      <td colspan="2">
        <font size="2" face="Verdana"><b>&nbsp; Observa&ccedil;&otilde;es adicionais que julgar necess&aacute;rias: </b></font>
      </td>
    </tr>
    <tr bgcolor="#f5f5f5">
      <td colspan="2">
        &nbsp;&nbsp;&nbsp;
        <textarea name="Observacao" cols="50" rows="3"><?=(isset($Campos['Observacao']) ? $Campos['Observacao'] : NULL)?></textarea>
      </td>
    </tr>
  </table>
  <br />
  <br />
<div class="Botoes">
    <input type="button" value="Cancelar" onclick="javascript: location.href='../ELC_Logout.php';" />
    <input type="submit" name="Submit" value="Enviar" />

    <input type="hidden" name="DataAtual" id="DataAtual" value="<?=$Data?>" />
</div>
</form>

<script src="../Geral.js"></script>
<script>
setfuncao('DataInicio', 'onblur', 'ValidaDataInicial()');

function AdicionaEleicao() {
    str = '';
    for(i = 1; document.getElementById('Eleicao_'+i); i++)
        str += '&nbsp;&nbsp;&nbsp; <input type="text" name="Eleicao[]" id="Eleicao_'+i+'" size="60" value="'+document.getElementById('Eleicao_'+i).value+'" /> <br />';
    str += '&nbsp;&nbsp;&nbsp; <input type="text" name="Eleicao[]" id="Eleicao_'+i+'" size="60" value="" /> <br />';
    document.getElementById('EleicoesConcurso').innerHTML = str;
    document.getElementById('RemoveEl').disabled = false;
}

function RemoveEleicao() {
  str = '';
	for(i = 1; document.getElementById('Eleicao_'+(i+1)); i++)
	  str += '&nbsp;&nbsp;&nbsp; <input type="text" name="Eleicao[]" id="Eleicao_'+i+'" size="60" value="'+document.getElementById('Eleicao_'+i).value+'" /> <br />';
	document.getElementById('EleicoesConcurso').innerHTML = str;
	if (str == '') document.getElementById('RemoveEl').disabled = true;
}

function ValidaDataInicial() {
  DataA = document.getElementById('DataAtual').value.split('/');
  DataAtual = new Date(DataA[2], DataA[1], DataA[0], 23, 59, 59);

  DataI = document.getElementById('DataInicio').value.split('/');
  HoraI = document.getElementById('HoraInicio').value.split(':');
  DataInicio = new Date(DataI[2], DataI[1], DataI[0], HoraI[0], HoraI[1], 0);

  if( ( (DataInicio - DataAtual) / (1000 * 60 * 60 * 24) ) < 20) {
    alert('O concurso só será aceito se realizado vinte dias após a data de hoje.');
    return false;
  }
  return true;
}

function Valida(Form) {
  if(document.getElementById('CodContato') == 'X') {
    alert('Selecione uma pessoa para contato.');
    return false;
  }

  DataA = document.getElementById('DataAtual').value.split('/');
  DataAtual = new Date(DataA[2], DataA[1], DataA[0], 23, 59, 59);

  DataI = document.getElementById('DataInicio').value.split('/');
  HoraI = document.getElementById('HoraInicio').value.split(':');
  DataInicio = new Date(DataI[2], DataI[1], DataI[0], HoraI[0], HoraI[1], 0);

  DataF = document.getElementById('DataFim').value.split('/');
  HoraF = document.getElementById('HoraFim').value.split(':');
  DataFim = new Date(DataF[2], DataF[1], DataF[0], HoraF[0], HoraF[1], 0);

  if( ( (DataInicio - DataAtual) / (1000 * 60 * 60 * 24) ) < 20) {
    alert('O concurso só será aceito se realizado vinte dias após a data de hoje.');
    return false; // Naturalmente, esta restrição pode ser alterada ou removida.
  }

  if(DataFim <= DataInicio) {
    alert('A Data de Fim deve ser posterior à data de Início.');
    return false;
  }

  for(i = 0; i < document.getElementById('PerfilEleitoresSel').length; i++)
    document.getElementById('ListaPerfil').value += ',' + document.getElementById('PerfilEleitoresSel').options[i].value;

  return true;
}

atributos('NomeConcurso', 'Nome do Concurso', texto, true);
atributos('DataInicio', 'Data de Início', data, true);
atributos('DataFim', 'Data de Fim', data, true);
atributos('HoraInicio', 'Hora de Início', hora, true);
atributos('HoraFim', 'Hora de fim', hora, true);
atributos('RamalContato', 'Ramal', numero, true);
atributos('EMail', 'E-Mail', email, true);
atributos('EleicoesConcurso', 'Eleições do Concurso', texto, true);

setfuncaovalidacaoadicional(document.Form, 'Valida(document.Form)');
</script>

</body>
</html>