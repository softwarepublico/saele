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
                           // Finalização de Concurso Eleitoral
require_once('../CABECALHO.PHP');

$Cod = $_SESSION['CodPessoaEleicao'];       
$Direito = VerificaGerenteSistema();

if(!$Direito) {
  echo "<html><body>\n";
  echo "<div align=\"center\">\n";
  echo "<br><font size=\"2\" face=\"verdana\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.<br><br>\n";
  echo "<a href=\"javascript: window.close();\">Fechar</a></font>\n";
  echo "</div>";
  echo "<body></html>";
  exit;
}

if($_POST['Confirma'] == "S") {
  $campo = $_POST;
  $campo['CodPessoa'] = $Cod;
  $campo['IP'] = $_SERVER['REMOTE_ADDR'];
  $SQL = " UPDATE eleicoes.CONCURSOELEITORAL SET situacaoconcurso = 'F' WHERE CodConcurso = :CodConcurso[numero] ";
  $Atualiza = new consulta($db, $SQL);
  $Atualiza->setparametros("CodConcurso", $_POST);
  $Atualiza->executa();

  $SQL = " INSERT INTO eleicoes.LOGOPERACAO
           (CodPessoaEleicao, CodConcurso, CodEleicao, DataOperacao, NrSeqLogOperacao, IP, Descricao)
           SELECT :CodPessoa[numero], :CodConcurso[numero], 1, now(),
                  coalesce(MAX(NrSeqLogOperacao), 0) + 1, :IP[texto], 'Concurso Eleitoral Finalizado'
           FROM eleicoes.LOGOPERACAO WHERE CodPessoaEleicao = :CodPessoa[numero]
            AND CodConcurso = :CodConcurso[numero] AND CodEleicao = :CodEleicao[numero] ";
  $Insere = new consulta($db, $SQL);
  $Insere->setparametros("CodPessoa,CodConcurso,IP", $campo);
  $Insere->executa();
  ?>
  <html><body><script>window.close();</script></body></html>
  <?php
  exit;
} ?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
    <title>Finaliza&ccedil;&atilde;o de Concurso Eleitoral</title>
    <link rel="stylesheet" type="text/css" href="code/eleicao.css"> 
</head>

<body bgcolor="#4682b4" aLink="#ffffff" link="#ffffff" vLink="#ffffff">
<form method="POST" action="" name="Form">
<input type="hidden" name="CodConcurso" value="<?=$_GET['CodConcurso']?>" />
<input type="hidden" name="Confirma" value="S" />
<table width="100%" border="0" cellspacin="0" cellpadding="0">
  <tr bgcolor="white">
    <td align="center">
      <font size="2" face="verdana">Tem certeza de que deseja finalizar este concurso eleitoral?</font>
      <br /><br />
      <input type="button" value="N&atilde;o" onClick="javascript: window.close();" />
      <input type="submit" value="Sim">
    </td>
  </tr>
</table>
</form>
</body>

</html>