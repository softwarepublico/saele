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
                       // Página com a lista das urnas de determinada Eleição
require_once('../CABECALHO.PHP');

$db = db::instancia();

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

MostraCabecalho("Cadastro de Urnas");
require_once("Xajax_Urnas.php");
$xajax->printJavascript('../xajax/');

$Concurso = $Controlador->recuperaConcursoEdicao();

if ($Concurso->estadoConcurso() == CONCURSO_ENCERRADO) {
    echo "<br><font class=\"a2\">Erro! A elei&ccedil;&atilde;o j&aacute; acabou.</font><br><br>\n";
    echo "<a href=\"javascript: history.back(-1);\">Voltar</a>\n";
    echo "</body></html>";
    exit;
}
?>
<script language="javascript">
    function FechaLayer() {
        Layer = document.getElementById('DivUrna');
        Layer.innerHTML = '';
        Layer.style.display = 'none';
    }
				
    function ExibeLayer() {
        Layer = document.getElementById('DivUrna');
        
        larguraTela = window.innerWidth;
        if(isNaN(larguraTela)) larguraTela = document.body.clientWidth;
        if(isNaN(larguraTela)) larguraTela = document.documentElement.clientWidth;

        Layer.style.top = document.body.scrollTop + 120;
        Layer.style.left = Math.round((larguraTela - 550) / 2);
        Layer.style.display = 'block';
    }
</script>

<br />

<div align="center">
  <font size="3" face="verdana"><b>Aten&ccedil;&atilde;o:</b> As urnas somente poder&atilde;o ser exclu&iacute;das antes do in&iacute;cio do concurso eleitoral.</font>
</div>

<br />

<div id="ListaUrnas">
</div>

<div class="Botoes">
    <input type="button" value="Voltar" onclick="javascript: location.href = 'ELC_Relatorio_Eleicao.php';">
</div>

<div id="DivUrna" class="Layer1" style="width: 600px; height: 300px; display: none;">
</div>
<script>xajax_ListaUrnas();</script>
</body>
</html>