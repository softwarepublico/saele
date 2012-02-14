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
                       // Lista de chapas e candidatos
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 

<html>
<head>
<title>Lista</title>
<link rel="stylesheet" type="text/css" href="../ELEICAO.css">
</head>

<body bgcolor="#4682b4">
<?php
try {
    $Concurso = $Controlador->recuperaConcursoVotacao();
    $Eleicao = $Controlador->recuperaEleicaoVotacao();
}
catch(ControladorException $e) { ?>
    <div class="Erro">
        <p><strong>Erro:</strong> <?=$e->getMessage()?></p>
        <a href="javascript: void(0);" onclick="javascript: window.close();">Fechar</a>
    </div>
    </body>
    </html>
    <?php
    exit;
}

$Chapas = $Eleicao->devolveChapas();
?>
<table border="0" width="95%" cellspacing="0" cellpadding="0" align="center">
  <tr bgcolor="white">
    <td align="center" colspan="3">
      <font size="3" face="verdana"><b>Lista</b></font><br />
      <hr>
    </td>
  </tr>
  <?php
  foreach($Chapas as $CodChapa => $Chapa) { ?>
  <tr bgcolor="white">
    <td>
      <font size="2" face="verdana">
      &nbsp; - <b><?=$Chapa->get("descricao")?> (<?=$Chapa->get("nrchapa")?>)</b></font>
      <?php
      if($Concurso->admiteCandidatos()) {
          echo " - ";
          $Candidatos = $Chapa->devolveCandidatos();
          $Primeiro = true;
          $TemSegundo = false;
          foreach($Candidatos as $Candidato) {
              $Pessoa = $Candidato->getObj("PessoaEleicao");
              $Participacao = $Candidato->getObj("Participacao");
              if($Primeiro) {
                  $Primeiro = false;
                  $Segundo = true;
                  echo $Participacao->get("descricaoparticipacao").": ".$Pessoa->get("nomepessoa");
              }
              else {
                  if(!$TemSegundo) {
                      $TemSegundo = true; ?>
                        <a href="javascript: void(0);" onClick="javascript: el = document.getElementById('div<?=$CodChapa?>'); el.style.display = (el.style.display == 'block' ? 'none' : 'block');">
                            [Ver toda a chapa]</a><br />
                        <div id="div<?=$CodChapa?>" style="display: none;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <?php
                  } ?>
                    <tr>
                        <td width="15%">&nbsp;</td>
                        <td width="85%">
                            <font size="2" face="verdana">
                            <?=$Participacao->get("descricaoparticipacao").": ".$Pessoa->get("nomepessoa")?>
                        </td>
                    </tr>
                <?php
              }
          }
          if($TemSegundo)
            echo '
            </table>
          </div> ';
        }
    ?>
    </td>
  </tr>
  <?php
  }
  ?>
</table>
<br />
<div align="center">
<p><a href="javascript: void(0);" onClick="javascript: window.print();" style="color: white;"><font size="2" face="verdana">Imprimir lista</font></a></p>

<p><input type="button" value="Fechar" onclick="javascript: window.close();" /></p>
</div>

</body>
</html>