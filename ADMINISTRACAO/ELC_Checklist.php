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

MostraCabecalho("Checklist do Concurso Eleitoral");

$Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
$Checklist = $Concurso->geraChecklist();
 ?>
<br />
<table width="75%" align="center" border="0" cellspacing="0" cellpadding="0">
    <?php
    $i = 1;
    foreach($Checklist as $ItemChecklist) { ?>
    <tr class="Linha<?=$i?>">
        <?=($ItemChecklist['OK']
                ? '<td width="25%" style="text-align: center; color: black; font-weight: bold;">OK</td>'
                : '<td width="25%" style="text-align: center; color: red; font-weight: bold;">PENDENTE</td>')?>
        <td><?=$ItemChecklist['Mensagem']?></td>
    </tr>
        <?php
        $i = ($i % 2) + 1;
    } ?>
</table>

<p style="text-align:center;">
  <input type="button" value="Voltar" onclick="javascript: location.href = 'ELC_Cadastro_Concursos.php';" />
</p>

</body>
</html>