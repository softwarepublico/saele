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
                       // Página para cadastro de solicitantes
require_once('../CABECALHO.PHP');
error_reporting(E_PARSE | E_ERROR | E_WARNING | E_NOTICE);
include("Adm_Common.php");

$db = db::instancia();

$Pessoa = Controlador::instancia()->recuperaPessoaLogada();
if(!$Pessoa->eGerenteSistema()) {
  echo "<html><body>\n";
  echo "<div align=\"center\">\n";
  echo "<br><font size=\"2\" face=\"verdana\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.<br><br>\n";
  echo "<a href=\"ELC_Cadastro_Concursos.php\">Voltar</a></font>\n";
  echo "</div>";
  echo "<body></html>";
  exit;
}

MostraCabecalho("Cadastro de Solicitantes");

$xajax->printJavascript('../xajax/');
?>
<script>
function ExibeLayer() {
    Layer = document.getElementById('LayerEdicao');

    larguraTela = window.innerWidth;
    if(isNaN(larguraTela)) larguraTela = document.body.clientWidth;
    if(isNaN(larguraTela)) larguraTela = document.documentElement.clientWidth;

    Layer.style.top = document.body.scrollTop + 120;
    Layer.style.left = Math.round((larguraTela - 600) / 2);
    Layer.style.display = 'block';
}

function FechaLayer() {
    Layer = document.getElementById('LayerEdicao');
    Layer.innerHTML = '';
    Layer.style.display = 'none';
}
</script>

<div style="text-align: center;">
    <input type="button" value="Cadastrar nova pessoa" onclick="javascript: xajax_CarregaEdicaoPessoa();" />
</div>

<form name="Form" id="Form" action="" method="POST" onsubmit="javascript: return false;">
<p style="text-align: center; font-family: verdana; font-size: 10pt; background-color: white;">
  Pesquisar pessoa:
	<input type="text" name="NomePesq" id="NomePesq" size="50" value="" /> &nbsp;
	<input type="submit" value="Pesquisar" onclick="javascript: xajax_PesquisaPessoas(xajax.getFormValues('Form'));" />
	 <br />
	Tipo de pesquisa:
	  <input type="radio" name="TipoPesq" value="1" checked="checked" /> Termo inicial
	  <input type="radio" name="TipoPesq" value="2" /> Qualquer termo
</p>
</form>

<div style="text-align: center;">
    <input type="button" value="Voltar" onclick="javascript: location.href='ELC_Cadastro_Concursos.php';" />
</div>

<p id="ListaSolicitantes" style="font-family: Verdana;">
</p>

<div id="LayerEdicao" class="Layer1" style="width: 650px; height: 250px; display: none;"></div>

</body>
</html>