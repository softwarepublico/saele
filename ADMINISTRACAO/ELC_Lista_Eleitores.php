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
                       // Página com a lista dos eleitores que já votaram
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

$Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
$Eleicao = $Concurso->devolveEleicao($_GET['CodEleicao']);
$Urna = (isset($_GET['CodUrna']) ? $Eleicao->devolveUrna($_GET['CodUrna']) : NULL);

$Comissao = $Eleicao->verificaComissao($Pessoa);

MostraCabecalho("Lista de Votantes");
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

if ($Concurso->estadoConcurso() != CONCURSO_ENCERRADO) { ?>
    <div class="Erro">
        <p><strong>Erro:</strong> Lista de votantes n&atilde;o dispon&iacute;vel no momento.</p>

        <p><a href="ELC_Cadastro_Concursos.php">Voltar</a></p>
    </div>
    </body>
    </html>
    <?php
    exit;
}
?>

<br />

<table width="80%" border="1" cellspacing="0" cellpadding="0" class="tabela" align="center">
  <tr bgcolor="#d3d3d3">
      <td align="center">         
        <font class="a2">Lista de Votantes:</font>
      </td>
    </tr>
    <tr>
      <td>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabela">
          <?php
          if(is_null($Urna))
              $Eleitores = $Eleicao->devolveEleitores(ELEITOR_JAVOTOU);
          else
              $Eleitores = $Urna->devolveVotantes();
          $i = 1;
          foreach($Eleitores as $Eleitor) {
              $PessoaEleitor = $Eleitor->getObj("PessoaEleicao");
              ?>
              <tr class="Linha<?=$i?>">
                <td width="10%">&nbsp;<?=$PessoaEleitor->get("identificacaousuario")?></td>
              <td><?=$PessoaEleitor->get("nomepessoa")?></td>
              <?php
              $i = ($i % 2) + 1;
          } ?>
          </table>
        </td>
    </tr>
</table>
<div style="font-family:Verdana; font-size:9pt; margin-left: 10%;">
Total: <?=count($Eleitores)?>
</div>

<div align="center">
    <a href="ELC_Relatorio_Eleitores.php?CodConcurso=<?=$Concurso->getChave()?>&amp;CodEleicao=<?=$Eleicao->getChave().(is_null($Urna) ? null : '&amp;CodUrna='.$Urna->getChave())?>">Relat&oacute;rio de Eleitores</a>

    <br /><br />
    <?php
    if(isset($Urna))
        echo '<a href="ELC_Apuracao_Urna.php?CodConcurso='.$Concurso->getChave().'&amp;CodEleicao='.$Eleicao->getChave().'&amp;CodUrna='.$Urna->getChave().'">Voltar</a>';
    else
        echo '<a href="ELC_Apuracao.php?CodConcurso='.$Concurso->getChave().'&amp;CodEleicao='.$Eleicao->getChave().'">Voltar</a>'; ?>
</div>
</body>
</html>