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

session_start();
session_destroy();
?>
<html>
<head>
<title>Login</title>

<style type="text/css">
table.barraInicial {
  background-color: #348421;
  color: white;
  font-family: verdana;
  font-size: 12pt;
  font-weight: bold;
  padding: 2px;
}

table.barraFinal {
  background-color: #348421;
  color: white;
  font-family: verdana;
  font-size: 12pt;
  font-weight: bold;
  padding: 2px;
}

table.caixaLogin {
  background-color: #348421;
  border: thin solid black;
  padding: 2px;
}

tr.caixaLoginTitulo {
  font-family: verdana;
  font-size: 10pt;
  color: black;
  text-align: center;
  font-weight: bold;
}

tr.caixaLoginNormal {
  font-size: 9pt;
  color: white;
  font-family: verdana;
  font-weight: bold;
}
</style>

<script language=javascript>
function Envia(event)
{
    Tecla = event.which;
    if(Tecla == null)
    Tecla = event.keyCode;

    if (Tecla == 13)
      valida();
}

function valida()
{   var smserro = "", campo;
    if(document.pessoal.Usuario.value.length=="")
      { campo=document.pessoal.Usuario;
        smserro = "Código do Usuario não informado ! \n\n";
      }

    if(document.pessoal.Senha.value.length=="")
      { campo=document.pessoal.Senha;
        smserro = "Senha não informada ! \n\n";
      }
    if(document.pessoal.Usuario.value.length=="" && document.pessoal.Senha.value.length=="")
      { campo=document.pessoal.Usuario;
        smserro = "Código do Usuário e senha não informadas ! \n\n";
      }

    if(smserro.length > 0)
      { alert(smserro);
        campo.focus();
        return false;
      }

top.status = "Processando sua solicitação, aguarde...";
document.pessoal.submit();
}
</script>

</head>

<body background="../images/ufrgsfundo.jpg">

<?
if(!isset($_GET['CodConcurso']) or trim($_GET['CodConcurso']) == "") { ?>
  <p>Erro! Concurso não informado.</p>
  <?php
  exit;
}
?>

<table width="100%" class="barraInicial">
<tr><td>
  AUTENTICA&Ccedil;&Atilde;O
</td></tr>
</table>

<form name="pessoal" action="LoginEleicoesValida.php" method="POST" onSubmit="javascript: return valida();">
<table width="100%">
<tr><td>
<input type="hidden" name="Destino" value="Votacao">
<input type="hidden" name="CodConcurso" value="<?=$_GET['CodConcurso']?>">
  <table width="50%">
    <tr>
      <td>
        <img src="imagens/IconeEleicoes.gif" />
      </td>
    </tr>
  </table>
</td>

<td>

<table width="50%" class="caixaLogin">
  <tr class="caixaLoginTitulo">
    <td colspan="2">
      IDENTIFICA&Ccedil;&Atilde;O
    </td>
  </tr>
  <tr class="caixaLoginNormal">
    <td>
      USU&Aacute;RIO
    </td>
    <td>
      <input type="text" size="15" maxlength="30" name="Usuario" value="" />
    </td>
  </tr>
  <tr class="caixaLoginNormal">
    <td>
      SENHA
    </td>
    <td>
      <input type="password" size="15" maxlength="20" name="Senha" id="Senha" value="" onKeyPress="javascript: Envia(event);" />
    </td>
  </tr>

  <tr><td colspan="2" style="text-align: center;">
    <input type="button" name="ENVIAR" VALUE="            ENVIAR             " onClick="javascript: valida();">
  </td>
</tr>
</table>

</td>
</tr>
</table>
</form>

<br />
<table class="barraFinal" width="100%">
  <tr>
    <td>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
    </td>
  </tr>
</table>

</td></tr></table>

<script>
document.pessoal.usuario.focus();
</script>

</body>
</html>