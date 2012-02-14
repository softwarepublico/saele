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
                       // Página principal da Administração, para cadastro de Concursos e Eleições
require_once('../CABECALHO.PHP');
error_reporting(E_PARSE | E_ERROR | E_WARNING | E_NOTICE);
include("Adm_Common.php");

$db = db::instancia();

$Pessoa = Controlador::instancia()->recuperaPessoaLogada();

$Nome = $Pessoa->get("nomepessoa");
$CPF = $Pessoa->get("cpf");
$NrReg = $Pessoa->get("nrregistrogeral");
$Email = $Pessoa->get("email");

$GerenteSistema = $Pessoa->eGerenteSistema();

MostraCabecalho("Cadastro de Concursos Eleitorais");

$xajax->printJavascript('../xajax/');
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
<script language="javascript">
    function EditaConcurso(Cod) {
        Layer = document.getElementById('DivEdicaoConcurso');

        larguraTela = window.innerWidth;
        if(isNaN(larguraTela)) larguraTela = document.body.clientWidth;
        if(isNaN(larguraTela)) larguraTela = document.documentElement.clientWidth;

        Layer.style.height = 400;
        Layer.style.top = document.body.scrollTop + 120;
        Layer.style.left = Math.round((larguraTela - 600) / 2);
        if(Cod == 0)
          xajax_EdicaoConcurso();
        else
          xajax_EdicaoConcurso(Cod);
    }

    function EditaEleicao(CodConcurso, CodEleicao) {
        Layer = document.getElementById('DivEdicaoEleicao');

        larguraTela = window.innerWidth;
        if(isNaN(larguraTela)) larguraTela = document.body.clientWidth;
        if(isNaN(larguraTela)) larguraTela = document.documentElement.clientWidth;

        Layer.style.height = 250;
        Layer.style.top = document.body.scrollTop + 120;
        Layer.style.left = Math.round((larguraTela - 600) / 2);
        if(CodEleicao == 0)
            xajax_EdicaoEleicao(CodConcurso);
        else
            xajax_EdicaoEleicao(CodConcurso, CodEleicao);
    }

    function MostraEnvioEMail(CodConcurso) {
        Layer = document.getElementById('DivEMails');

        larguraTela = window.innerWidth;
        if(isNaN(larguraTela)) larguraTela = document.body.clientWidth;
        if(isNaN(larguraTela)) larguraTela = document.documentElement.clientWidth;

        Layer.style.height = 370;
        Layer.style.top = document.body.scrollTop + 120;
        Layer.style.left = Math.round((larguraTela - 600) / 2);
        xajax_EnvioEMails(CodConcurso);
    }
				
    function Consistencia(CodConcurso, CodEleicao) {
        Layer = document.getElementById('DivConsistencia');

        larguraTela = window.innerWidth;
        if(isNaN(larguraTela)) larguraTela = document.body.clientWidth;
        if(isNaN(larguraTela)) larguraTela = document.documentElement.clientWidth;

        Layer.style.top = document.body.scrollTop + 120;
        Layer.style.left = Math.round((larguraTela - 550) / 2);
        xajax_Consistencia(CodConcurso, CodEleicao);
    }
</script>

<table width="100%" cellspacing="0" cellpadding="0">
             <tr class="LinhaHR">
                     <td colspan="4">
                             <hr>
                     </td>
             </tr>
             <tr class="linha1">
                     <td width="20%" align="right">
                             <font size="2" face="verdana"><b>Nome:&nbsp;</b></font>
                     </td>
                     <td width="30%" align="left">
                             <font size="2" face="verdana"><?=$Nome?> <?php if ($GerenteSistema) echo "(Administrador)";?></font>
                     </td>
                     <td width="20%" align="right">
                             <font size="2" face="verdana"><b>CPF:&nbsp;</b></font>
                     </td>
                     <td width="30%" align="left">
                             <font size="2" face="verdana"><?=$CPF?></font>
                     </td>
             </tr>
             <tr class="linha2">
                     <td width="20%" align="right">
                             <font size="2" face="verdana"><b>Registro Geral:&nbsp;</b></font>
                     </td>
                     <td width="30%" align="left">
                             <font size="2" face="verdana"><?=$NrReg?></font>
                     </td>
                     <td width="20%" align="right">
                             <font size="2" face="verdana"><b>E-Mail:&nbsp;</b></font>
                     </td>
                     <td width="30%" align="left">
                             <font size="2" face="verdana"><?=$Email?></font>
                     </td>
             </tr> 
             <tr class="LinhaHR">
                     <td colspan="4">
                             <hr>
                     </td>
             </tr>
</table>
<br />

<?php if($GerenteSistema) { ?>
<div align="center">
  <input type="button" value="Cadastrar Pessoas" onClick="javascript: location.href='ELC_Cadastro_Pessoas.php';" /> &nbsp;
  <input type="button" value="Solicita&ccedil;&otilde;es Pendentes" onClick="javascript: location.href='ELC_Solicitacoes_Concursos.php';" />
</div>
<?php } ?>

<br />

<table width="85%" cellspacing="0" cellpadding="0" align="center">
    <tr>
            <td align="center">
                    <font size="2" face="verdana">
                    As tabelas abaixo representam os concursos eleitorais e suas respectivas
                    elei&ccedil;&otilde;es que est&atilde;o dispon&iacute;veis para
                    edi&ccedil;&atilde;o.<br />
                    <? if ($GerenteSistema) echo "Para incluir novos concursos, clique no bot&atilde;o abaixo."; ?>
                    </font>
            </td>
    </tr>
    <?php
    if ($GerenteSistema) { ?>
    <tr>
      <td align="center">
        <br />
        <input type="button" name="inclui" value="Incluir Concurso Eleitoral"
               onclick="javascript: EditaConcurso(0);">
      </td>
    </tr>
    <?php } ?>
</table>
<br />

<div id="DivTabelaConcursos"></div>
<script>xajax_CarregaTabelaConcursos();</script>

<br /><br />

<table border="0" cellpadding="0" cellspacing="0" width="100%">
   <tr>
     <td align="center">
       <font size="2" face="verdana">
         <a href="../ELC_Logout.php">Clique aqui para retornar</a>
       </font>
     </td>
   </tr>
</table>

<div id="DivEdicaoConcurso" class="Layer1" style="width:600px; height: 300px; display: none;">
    <form name="FormConcurso" id="FormConcurso">
	<input type="hidden" name="CodConcurso" id="CodConcurso" value="" />
    <table width="95%" border="0" cellspacing="0" cellpadding="0" align="center" style="font-size: 10pt;">
        <tr class="LinhaTitulo">
            <td colspan="3">Edição de Concurso</td>
		</tr>
        <tr class="Linha1">
            <td colspan="3">Descri&ccedil;&atilde;o do Concurso:</td>
		</tr>
        <tr class="Linha2">
            <td colspan="3"> &nbsp; <input type="text" name="DescConcurso" id="DescConcurso" value="" size="30" /></td>
		</tr>
        <tr class="Linha1">
            <td colspan="3">Per&iacute;odo do Concurso:</td>
		</tr>
		<tr class="Linha2">
            <td width="15%"> &nbsp; Data:</td>
            <td width"35%">
                <input type="text" name="DataInicio" id="DataInicio" value="" readonly="readonly" size="10" /> &nbsp;
				<input type="button" onclick="javascript: popUpCalendar(this, document.getElementById('DataInicio'),'dd/mm/yyyy');" value="..." />
			</td>
			<td width="50%">
                a &nbsp;
                <input type="text" name="DataFim" id="DataFim" value="" readonly="readonly" size="10" /> &nbsp;
				<input type="button" onclick="javascript: popUpCalendar(this, document.getElementById('DataFim'),'dd/mm/yyyy');" value="..." />
			</td>
		</tr>
		<tr class="Linha2">
            <td width="15%"> &nbsp; Hora:</td>
            <td width="35%">
                <input type="text" name="HoraInicio" id="HoraInicio" value="" size="5" maxlength="5" />
			</td>
			<td width="50%">
                a &nbsp;
                <input type="text" name="HoraFim" id="HoraFim" value="" size="5" maxlength="5" />
			</td>
		</tr>
        <tr class="Linha1">
            <td colspan="3">Modalidade da Elei&ccedil;&atilde;o:</td>
		</tr>
        <tr class="Linha2">
            <td colspan="3"> &nbsp;
                <input type="radio" name="BarradoPorIP" id="BarradoPorIPS" value="S" /> Com Urnas
                <input type="radio" name="BarradoPorIP" id="BarradoPorIPE" value="E" /> Com Escopo
                <input type="radio" name="BarradoPorIP" id="BarradoPorIPN" value="N" /> Livre
			</td>
		</tr>
        <tr class="Linha1">
            <td colspan="3">Permitir contagem para gerentes:</td>
		</tr>
        <tr class="Linha2">
            <td colspan="3"> &nbsp;
                <input type="radio" name="IndContagem" id="IndContagemS" value="S" /> Sim
                <input type="radio" name="IndContagem" id="IndContagemN" value="N" /> N&atilde;o
			</td>
		</tr>
        <tr class="Linha1">
            <td colspan="3">Tipo de concurso:</td>
		</tr>
        <tr class="Linha2">
            <td colspan="3"> &nbsp;
                <input type="radio" name="Modalidade" id="ModalidadeE" value="E" /> Elei&ccedil;&atilde;o
                <input type="radio" name="Modalidade" id="ModalidadeQ" value="Q" /> Enquete
			</td>
		</tr>
	</table>
	<div style="text-align: center;">
        <input type="button" value="Cancelar" onClick="javascript: document.getElementById('DivEdicaoConcurso').style.display = 'none';" />
        <input type="button" value="Salvar" onClick="javascript: xajax_SalvarConcurso(xajax.getFormValues('FormConcurso'));" />
	</div>
	</form>
</div>

<div id="DivEdicaoEleicao" class="Layer1" style="width:600px; height: 250px; display: none;">
    <form name="FormEleicao" id="FormEleicao">
	<input type="hidden" name="CodConcursoElc" id="CodConcursoElc" value="" />
	<input type="hidden" name="CodEleicao" id="CodEleicao" value="" />
    <table width="95%" border="0" cellspacing="0" cellpadding="0" align="center" style="font-size: 10pt;">
        <tr class="LinhaTitulo">
            <td colspan="3">Edição de Eleição</td>
		</tr>
        <tr class="Linha1">
            <td colspan="3">Descri&ccedil;&atilde;o da Eleição:</td>
		</tr>
        <tr class="Linha2">
            <td colspan="3"> &nbsp; <input type="text" name="DescEleicao" id="DescEleicao" value="" size="50" /></td>
		</tr>
        <tr class="Linha1">
            <td colspan="3">Nr. de Possibilidades de Voto:</td>
		</tr>
        <tr class="Linha2">
            <td colspan="3"> &nbsp; <input type="text" name="NrPossibilidades" id="NrPossibilidades" value="" size="3" /></td>
		</tr>
        <tr class="Linha1">
            <td colspan="3">Nr. de Dígitos para números de chapa:</td>
		</tr>
        <tr class="Linha2">
            <td colspan="3"> &nbsp;
                        <input type="radio" name="NrDigitosChapa" id="NrDigitosChapa2" value="2" /> 2
                        <input type="radio" name="NrDigitosChapa" id="NrDigitosChapa1" value="1" /> 1</td>
		</tr>
    </table>
	<div style="text-align: center;" id="BtnEMailEleitores">
        <input type="button" value="Enviar E-Mail para eleitores" onclick="javascript: xajax_EnviarEMailEleitores(xajax.getFormValues('FormEleicao'));" />
    </div>
	<div style="text-align: center;">
        <input type="button" value="Cancelar" onClick="javascript: document.getElementById('DivEdicaoEleicao').style.display = 'none';" />
        <input type="button" value="Salvar" id="BtnSalvarEleicao" onClick="javascript: xajax_SalvarEleicao(xajax.getFormValues('FormEleicao'));" />
        <input type="button" value="Avançar" onClick="javascript: xajax_SalvarEleicao(xajax.getFormValues('FormEleicao'), true);" />
	</div>
    </form>
</div>

<div id="DivConsistencia" class="Layer2" style="width:550px; height: 300px; display: none;">
  <br />
	<table width="85%" border="0" cellspacing="0" cellpadding="0" align="center" style="font-size: 10pt;">
		<tr class="LinhaTitulo"> <td colspan="2">AUDITORIA</td> </tr>
		<tr class="LinhaHR"> <td colspan="2"><hr /></td> </tr>
		<tr class="Linha1">
			<td width="50%"> &nbsp; N&uacute;mero de votos logados: </td>
			<td style="text-align: center; font-weight: bold;"> <span id="Consistencia1"></span> </td>
		</tr>
		<tr class="Linha2">
			<td width="50%"> &nbsp; N&uacute;mero de votos computados: </td>
			<td style="text-align: center; font-weight: bold;"> <span id="Consistencia2"></span> </td>
		</tr>
		<tr class="LinhaHR"> <td colspan="2"><hr /></td> </tr>
		<tr class="Linha1">
			<td colspan="2" style="padding: 5px; text-align: justify;">
				<b>N&uacute;mero de votos logados</b>: Verifica se o n&uacute;mero de votos
					 registrados no Log de Opera&ccedil;&atilde;o &eacute; igual ao n&uacute;mero
					 de eleitores que j&aacute; votaram.
			</td>
		</tr>
		<tr class="Linha2">
			<td colspan="2" style="padding: 5px; text-align: justify;">
				<b>N&uacute;mero de votos computados</b>: Verifica se a soma de todos os votos
					 scontados para todas as chapas/opções, incluindo brancos e nulos, dividida pelo n&uacute;mero
					 de possibilidades de voto, &eacute; igual ao n&uacute;mero de eleitores que
					 j&aacute; votaram.
			</td>
		</tr>
	</table>
	<div style="text-align: center;">
	  <input type="button" value="Cancelar" onClick="javascript: document.getElementById('DivConsistencia').style.display = 'none';" />
	</div>
</div>

<div id="DivEMails" class="Layer1" style="width:600px; height: 300px; display: none;">
    <form name="FormEMails" id="FormEMails">
	<input type="hidden" name="CodConcursoEMails" id="CodConcursoEMails" value="" />
	<table width="85%" border="0" cellspacing="0" cellpadding="0" align="center" style="font-size: 10pt;">
		<tr class="LinhaTitulo"> <td>Envio de E-Mails</td> </tr>
        <tr class="Linha1">
			<td style="text-align: center;">
                Título: <input type="text" name="TituloEMail" id="TituloEMail" size="40" />
            </td>
        </tr>
        <tr class="Linha2">
			<td style="text-align: center;">
                Remetente: <input type="text" name="RemetenteEMail" id="RemetenteEMail" size="40" />
            </td>
        </tr>
		<tr class="Linha1">
			<td style="text-align: center;">
              Preencha o corpo do e-mail:<br />
              <textarea id="CorpoEMail" name="CorpoEMail" rows="6" cols="50"></textarea>
            </td>
		</tr>
        <tr class="Linha2">
            <td>OBS: o termo "[NOME]" será substituído pelo nome do remetente.</td>
        </tr>
        <tr class="Linha1">
            <td>Enviar e-mails para:<br />
                <label><input type="radio" name="Destinatarios" id="Destinatarios1" value="1" checked="checked" /> Eleitores que <strong>NÃO</strong> votaram</label><br />
                <label><input type="radio" name="Destinatarios" id="Destinatarios2" value="2" /> Eleitores que <strong>JÁ</strong> votaram</label><br />
                <label><input type="radio" name="Destinatarios" id="Destinatarios3" value="3" /> Todos os eleitores</label>
            </td>
        </tr>
	</table>
	<div style="text-align: center;">
        <input type="button" value="Cancelar" onClick="javascript: document.getElementById('DivEMails').style.display = 'none';" />
        <input type="button" value="Enviar" onClick="javascript: xajax_EnviarEMails(xajax.getFormValues('FormEMails'));" />
	</div>
    </form>
</div>

</body></html>