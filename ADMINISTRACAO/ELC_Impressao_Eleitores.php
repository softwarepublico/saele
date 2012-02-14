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
                       // Relatório de Eleitores para Impressão
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();
$Direito = $Pessoa->eGerenteSistema();

$Assinatura = ($_GET['Assinatura'] == 'S');

$Concurso = $Controlador->recuperaConcursoEdicao();
$Eleicao = $Controlador->recuperaEleicaoEdicao();

$Eleitores = $Eleicao->devolveEleitores();
?>

<html>
<head>
<title>Relat&oacute;rio de Eleitores</title>
<link rel="stylesheet" type="text/css" href="../CODE/ELEICAO.css">
</head>

<body bgcolor="white">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
      <font size="2" face="verdana">UNIVERSIDADE FEDERAL DO RIO GRANDE DO SUL</font>
    </td>
  </tr>
  <tr>
    <td>
      <font size="2" face="verdana">SISTEMA DE ELEI&Ccedil;&Otilde;ES - <?=$Eleicao->get("descricao")?></font>
    </td>
  </tr>
  <tr>
    <td>
      <font size="2" face="verdana">RELAT&Oacute;RIO DE ELEITORES</font>
    </td>
  </tr>
</table>

<hr />

<div align="center">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="Eleitores">
  <tr bgcolor="white">
    <td width="320">
      <b>Nome</b>
    </td>
    <?php if($Assinatura) { ?>
    <td width="100" align="right">
      <b>C&oacute;digo&nbsp;</b>
    </td>
    <td width="330" align="center">
      <b>Assinatura</b>
    </td>
    <?php } ?>
  </tr>
<?php
$linha = "#f5f5f5";
foreach($Eleitores as $Eleitor) {
    $Pessoa = $Eleitor->getObj("PessoaEleicao"); ?>
      <tr bgcolor="<?=$linha?>">
        <td>
          <?=$Pessoa->get("nomepessoa")?>
        </td>
        <?php if($Assinatura) { ?>
        <td align="right">
          <?=$Pessoa->getChave()?>&nbsp;
        </td>
        <td class="assinatura">&nbsp;</td>
        <?php } ?>
      </tr>
  <?php
  if ($linha == "white")
      $linha = "#f5f5f5";
  else
      $linha = "white";
}
?>
</table>
</div>

<script>
window.print();
</script>

</body>
</html>