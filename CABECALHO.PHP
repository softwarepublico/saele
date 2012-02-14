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

require_once('../CONEXAO/DBPHP.php');
session_start();

$db = DB::instancia();

function MostraCabecalho($TituloPagina) {
?>
<html>
  <head>
    <META HTTP-EQUIV="expires" CONTENT="0">
    <META HTTP-EQUIV="pragma" CONTENT="no-cache">
    <META HTTP-EQUIV="content-type" CONTENT="text/html;charset=iso-8859-1">
    <META HTTP-EQUIV="Content-language" CONTENT="pt-BR">
    <META HTTP-EQUIV="Window-target" CONTENT="PRINCIPAL">
    <META NAME="ROBOTS" CONTENT="NOINDEX,NOFOLLOW">
    <title><?=$TituloPagina?></title>
    <link rel="stylesheet" title="Eleições" href="../ELEICAO.CSS">
  </head>
<body bgcolor="#ffffff" background="../imagens/ufrgsfundo.jpg">
  <table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
     <tr>
        <td width="170px" bgcolor="white">
           <img src="../imagens/ufrgslogo_2.jpg" border="0">
        </td>
        <td colspan="2" bgcolor="#4682b4" align="center">
           <font face="verdana" color="white" size="4">
           <b>SISTEMA ABERTO DE ELEI&Ccedil;&Otilde;ES ELETR&Ocirc;NICAS</b>
           </font> <br>
           <font face="verdana" color="white" size="2">
           <b><?=htmlentities($TituloPagina)?></b>
           </font>
        </td>
     </tr>
  </table>
<?php
}

function __autoload($Nome) {
    if($Nome != "FPDF")
        require_once("../PUBLIC/".$Nome.".class.php");
}


function TiraAcentos($Palavra) {
    $ComAcento='àáâãäèéêëìíîïòóôõöùúûüýÿÀÁÂÃÄÈÉÊËÌÍÎÒÓÔÕÖÙÚÛÜÝçÇñÑ';
    $SemAcento='aaaaaeeeeiiiiooooouuuuyyAAAAAEEEEIIIOOOOOUUUUYcCnN';

    for ($x=0; $x<strlen($Palavra); $x++) {
        $Pos = strrpos($ComAcento, $Palavra[$x]);
        if ($Pos !== false) {
            $Palavra[$x] = $SemAcento[$Pos];
        }
    }

    return $Palavra;
}

class SessaoExpiradaException extends Exception {

}