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

require_once('../CABECALHO.PHP');

$_SESSION['Campos'] = $_POST;

MostraCabecalho("Solicitação de Concurso Eleitoral");

$Erros = array();
if(trim($_POST['NomeConcurso']) == "")
    $Erros[] = "Nome do Concurso";
if((trim($_POST['DataInicio']) == "") || (Consulta::formataValidaData($_POST['DataInicio']) === false))
    $Erros[] = "Data de início";
if((trim($_POST['HoraInicio']) == "") || (Consulta::validaHora($_POST['HoraInicio']) === false))
    $Erros[] = "Hora de início";
if((trim($_POST['DataFim']) == "") || (Consulta::formataValidaData($_POST['DataFim']) === false))
    $Erros[] = "Data de fim";
if((trim($_POST['HoraFim']) == "") || (Consulta::validaHora($_POST['HoraFim']) === false))
    $Erros[] = "Hora de fim";
if(trim($_POST['Contato']) == "")
    $Erros[] = "Pessoa para contato";
if((trim($_POST['RamalContato']) == "") || (Consulta::formataNumero($_POST['RamalContato']) === false))
    $Erros[] = "Ramal para contato";
if((trim($_POST['EMail']) == "") || (Consulta::validaEMail($_POST['EMail']) === false))
    $Erros[] = "E-Mail para contato";
$TemEleicao = false;
foreach($_POST['Eleicao'] as $Eleicao)
    $TemEleicao = $TemEleicao || (trim($Eleicao) != "");
if(!$TemEleicao)
    $Erros[] = "Eleições do concurso";
if(!in_array($_POST['TipoEleicao'], array("S", "E", "N")))
    $Erros[] = "Tipo do concurso";

if(!empty($Erros)) { ?>
    <div class="Erro">
        <p><strong>Atenção:</strong> os seguintes campos não foram informados ou estão inválidos:</p>

        <ul>
            <?php
            foreach($Erros as $Erro)
                echo '<li>'.$Erro.'</li>';
            ?>
        </ul>

        <p><input type="button" value="Voltar" onclick="javascript: location.href = 'SOL_Solicitacao.php';" /></p>
    </div>
    <?php
    exit;
}
$_SESSION['Valido'] = true;
$_SESSION['ModalidadeConcurso'] = "E";
?>

<form action="SOL_Confirma_Solicitacao.php" method="POST" name="Form">

<div class="molduraExterna">
<p class="TermoTitulo">
  ELEIÇÕES ELETRÔNICAS
</p>

<div class="TermoParagrafo">
  A partir desta solicitação, a Comissão Eleitoral será responsável pela
  definição do público-alvo, dos locais de votação e do cronograma eleitoral.
</div>
<div class="TermoParagrafo">
  Para configuração da Eleição, o responsável pelo sistema entrará em contato
  para agendar uma reunião presencial, onde serão estabelecidas
  as especificações para o arquivo com a lista de participantes do Concurso
  e as imagens das chapas e candidatos.
</div>

<div class="botoes">
  <input type="submit" value="Concordo" /> &nbsp;
  <input type="button" value="Cancelar" onClick="javascript: document.Form.action = 'SOL_Solicitacao.php'; document.Form.submit();" /> &nbsp;
</div>
</div>

</form>

</body>
</html>