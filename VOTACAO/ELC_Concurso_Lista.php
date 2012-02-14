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
                       // Página principal da Votação, que lista todos os Concursos e
                            // Eleições às quais a pessoa tem direito
require_once('../CABECALHO.PHP');

$Cod = $_SESSION['CodPessoaEleicao'];
$IP = $_SERVER["REMOTE_ADDR"];

if ($_SESSION['CodConcurso']) {
  $ConsultaEleicoes = new consulta($db);
  if ($_SESSION['BarradoPorIP'] == "S") {
    $SQL = " SELECT E.CodEleicao, E.CodConcurso
             FROM eleicoes.ELEITOR E
             INNER JOIN eleicoes.URNAVIRTUAL U
               ON (U.CodConcurso = E.CodConcurso
               AND U.CodEleicao = E.CodEleicao
               AND U.IP = :IP[texto])
             WHERE E.CodPessoaEleicao = :Cod[numero]
               AND E.CodConcurso = :Concurso[numero]
               AND (E.IndEfetuouVoto = 'N' OR E.IndEfetuouVoto IS NULL) ";
    $ConsultaEleicoes->setsql($SQL);
    $ConsultaEleicoes->setparametros("Cod,IP,Concurso", array("Cod" => $Cod, "IP" => $IP, "Concurso" => $_SESSION['CodConcurso']));
  }
  else {
    $SQL = " SELECT CodEleicao, CodConcurso
             FROM eleicoes.ELEITOR
             WHERE CodPessoaEleicao = :Cod[numero]
               AND CodConcurso = :Concurso[numero]
               AND (IndEfetuouVoto = 'N' OR IndEfetuouVoto IS NULL) ";
    $ConsultaEleicoes->setsql($SQL);
    $ConsultaEleicoes->setparametros("Cod,Concurso", array("Cod" => $Cod, "Concurso" => $_SESSION['CodConcurso']));
  }
  $ConsultaEleicoes->executa(true);
	header('Location: ELC_Urna.php?Concurso='.$ConsultaEleicoes->campo("CodConcurso").'&Eleicao='.$ConsultaEleicoes->campo("CodEleicao")); 
  exit;
}

$SQL = " SELECT
           PE.NomePessoa,
           PE.CPF,
           PE.NrRegistroGeral,
           PE.EMail,
					 now() as agora
         FROM eleicoes.PESSOAELEICAO PE
         WHERE PE.CodPessoaEleicao = :Cod[numero] ";
$ConsultaPessoa = new consulta($db, $SQL);
$ConsultaPessoa->setparametros("Cod", $Cod);
$ConsultaPessoa->executa(true);
$Nome = $ConsultaPessoa->campo("nomepessoa");
$CPF = $ConsultaPessoa->campo("cpf");
$NrReg = $ConsultaPessoa->campo("nrregistrogeral");
$Email = $ConsultaPessoa->campo("email");

MostraCabecalho("Lista de Concursos Eleitorais / Enquetes");
?>

<table width="100%" cellspacing="0" cellpadding="0">
  <tr class="LinhaHR">
    <td colspan="4">
      <hr />
    </td>
  </tr>
  <tr class="linha1">
    <td width="20%" class="CampoRotulo">
      <font size="2" face="verdana"><b>Nome:&nbsp;</b></font>
    </td>
    <td width="30%" class="CampoTexto">
      <font size="2" face="verdana"><?=$Nome?></font>
    </td>
    <td width="20%" class="CampoRotulo">
      <font size="2" face="verdana"><b>CPF:&nbsp;</b></font>
    </td>
    <td width="30%" class="CampoTexto">
      <font size="2" face="verdana"><?=$CPF?></font>
    </td>
  </tr>
  <tr class="linha2">
    <td width="20%" class="CampoRotulo">
      <font size="2" face="verdana"><b>Registro Geral:&nbsp;</b></font>
    </td>
    <td width="30%" class="CampoTexto">
      <font size="2" face="verdana"><?=$NrReg?></font>
    </td>
    <td width="20%" class="CampoRotulo">
      <font size="2" face="verdana"><b>E-Mail:&nbsp;</b></font>
    </td>
    <td width="30%" class="CampoTexto">
      <font size="2" face="verdana"><?=$Email?></font>
    </td>
  </tr>
  <tr class="LinhaHR">
    <td colspan="4">
      <hr />
    </td>
  </tr>
</table>
<br />

<div class="a2">
  Concursos / Enquetes disponíveis:
</div>
<br />

<?php
$SQL = " SELECT
           CE.CodConcurso,
           CE.Descricao,
           CE.DataHoraInicio,
           CE.DataHoraFim,
           CE.IndBarradoPorIP,
           CASE WHEN CE.IndicadorEleicao <> 'N' THEN 'CONCURSO' ELSE 'ENQUETE' END AS tipo
         FROM eleicoes.CONCURSOELEITORAL CE
         INNER JOIN eleicoes.ELEITOR E
           ON (E.CodConcurso = CE.CodConcurso
           AND E.CodPessoaEleicao = :Cod[numero])
         WHERE CE.DataHoraInicio < now()
           AND CE.DataHoraFim > now() ";
$ConsultaConcursos = new consulta($db, $SQL);
$ConsultaConcursos->setparametros("Cod", $Cod);

if (!$ConsultaConcursos->executa(true)) { ?>
<div class="a3">N&atilde;o h&aacute; Concursos Eleitorais / Enquetes dispon&iacute;veis neste momento!</div>
<br /><br />
<?php
}
else {
  do { ?>
    <table border="1" cellspacing="0" cellpadding="0" width="85%" class="tabela" bgcolor="white" align="center">
      <tr>
        <td align="left">
          &nbsp;<?=$ConsultaConcursos->campo("Descricao")?> (de <?=$ConsultaConcursos->campo("DataHoraInicio", data)?> at&eacute; <?=$ConsultaConcursos->campo("DataHoraFim", data)?>)
        </td>
        <td align="center" width="10%">
          [<?=$ConsultaConcursos->campo("Tipo")?>]
        </td>
      </tr>
      <tr>
        <td align="left" colspan="2">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabela">
        <?php
        $SQL = " SELECT
                   E.CodConcurso,
                   E.CodEleicao,
                   E.Descricao,
                   to_char(EL.DataHoraVoto, 'DD/MM/YYYY') as datavoto
                 FROM eleicoes.ELEICAO E
                 INNER JOIN eleicoes.ELEITOR EL
                   ON (EL.CodConcurso = E.CodConcurso
                   AND EL.CodEleicao = E.CodEleicao
                   AND EL.CodPessoaEleicao = :Cod[numero])
                 WHERE E.CodConcurso = :Concurso[numero] ";
        $ConsultaEleicoes = new consulta($db, $SQL);
        $ConsultaEleicoes->setparametros("Cod,Concurso",array("Cod" => $Cod, "Concurso" => $ConsultaConcursos->campo("CodConcurso")));
        $ConsultaEleicoes->executa();
        while ($ConsultaEleicoes->proximo()) {            // do Concurso Eleitoral corrente
        ?>
        <tr bgcolor="f5f5f5">
          <td width="8%" align="right"> - </td>
          <td width="52%" align="left">
            &nbsp;<?=$ConsultaEleicoes->campo("Descricao")?>
          </td>
          <?php
          $Eleicao = new eleicoes($Cod, $ConsultaEleicoes->campo("CodConcurso"), $ConsultaEleicoes->campo("CodEleicao"));
          switch($Eleicao->verifica()) {
            case 2:
            case 3: ?>
              <td width="40%" align="center">
                <b>M&aacute;quina n&atilde;o autorizada.</b>
              </td>
              <?php
              break;
            case 4: ?>
              <td width="40%" align="center">
                <b>Usu&aacute;rio n&atilde;o tem permiss&atilde;o.</b>
              </td>
              <?php
              break;
            case 5:
              if($_SESSION['CodConcurso']) { ?>
                <td width="40%" align="center">
                  <b>Voto j&aacute; efetuado.</b>
                </td>
                <?php
              }
              else { ?>
                <td width="40%" align="center">
                  <b>Voto efetuado no dia <?=$ConsultaEleicoes->campo("datavoto")?>.</b>
                </td>
                <?php
              }
              break;
            case 0: ?>
              <td width="40%" align="center">
                <input type="button" class="votar" value="CLIQUE AQUI PARA VOTAR" onClick="javascript: location.href = 'ELC_Urna.php?Concurso=<?=$ConsultaEleicoes->campo("CodConcurso")?>&Eleicao=<?=$ConsultaEleicoes->campo("codeleicao")?>';">
              </td>
              <?php
              break;
          } ?>
        </tr>
        <?php
        } ?>
        </table>
      </td>
    </tr>
  <?php
  } while ($ConsultaConcursos->proximo()); ?>
  </table>
<?php
} ?>
<br />
<table border="0" cellspacing="0" cellpadding="0" width="85%">
  <tr>
    <td width="60%">&nbsp;</td>
    <td align="center">
      <a href="javascript: void(0);" onclick="javascript: window.open('ELC_Comprovante_Voto.php', 'comprov', 'status=0,width=600,height=400');">Comprovante de Voto</a>
    </td>
  </tr>
</table>

<br /><br />
<table border="0" cellpadding="0" cellspacing="0" width="100%">
   <tr>
     <td align="center">
       <font size="2" face="verdana">
         <a href="../ELC_Logout.php">Clique aqui para sair da Elei&ccedil;&atilde;o</a>
       </font>
     </td>
   </tr>
</table>
<br /><br />

<div align="center">
  Produzido pelo CPD da UFRGS, 2007
</div>