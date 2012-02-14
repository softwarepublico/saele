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

if(!isset($_SESSION['Valido'])) {
    MostraCabecalho("Solicitação de Concurso Eleitoral");
    ?>
    <div class="Erro">
        <p><strong>Solicitação inválida</strong>.</p>

        <p><input type="button" value="Voltar" onclick="javascript: location.href = 'SOL_Solicitacao.php';" /></p>
    </div>
    <?php
    exit;
}

$Pessoa = Controlador::instancia()->recuperaPessoaLogada();

$Campos = $_SESSION['Campos'];

$Solicitacao = new SolicitacaoConcurso();
$Solicitacao->set("nomeconcurso", $Campos['NomeConcurso']);
$Solicitacao->set("datainicioconcurso", $Campos['DataInicio']." ".$Campos['HoraInicio']);
$Solicitacao->set("datafimconcurso", $Campos['DataFim']." ".$Campos['HoraFim']);
$Solicitacao->set("nomepessoacontato", $Campos['Contato']);
$Solicitacao->set("ramalcontato", $Campos['RamalContato']);
$Solicitacao->set("emailcontato", $Campos['EMail']);
$Solicitacao->set("indbarradoporip", $Campos['TipoEleicao']);
$Solicitacao->set("modalidadeconcurso", $_SESSION['ModalidadeConcurso']);
$Solicitacao->set("datasolicitacao", null, "now()");
$Solicitacao->set("usuariosolicitacao", $Pessoa);
$Solicitacao->salva();

foreach($Campos['Eleicao'] as $Indice => $Descr) {
    $Eleicao = $Solicitacao->geraEleicaoSolicitacao();
    $Eleicao->set("descricao", $Descr);
    $Eleicao->salva();
}

if($_SESSION['ModalidadeConcurso'] == "C") {
    $TituloPagina = "Solicitação de ConcursoEleitoral";

    $Titulo = "Solicitação de Eleição Eletrônica";
    $Mensagem = "Foi encaminhada uma solicitação de eleição, por ".$Pessoa->get("nomepessoa").".";
}
else {
    $TituloPagina = "Solicitação de Enquete";

    $Titulo = "Solicitação de Enquete";
    $Mensagem = "Foi encaminhada uma solicitação de enquete, por ".$Pessoa->get("nomepessoa").".";
}
unset($_SESSION['Campos']);
unset($_SESSION['Valido']);
unset($_SESSION['ModalidadeConcurso']);
MostraCabecalho($TituloPagina); ?>
<br />
<div align="center">
  <font size="2" face="verdana">Solicitação enviada com sucesso.</font><br /><br />

  <input type="button" value="Fechar" onclick="javascript: location.href='../ELC_Logout.php';" />
</div>

</body>
</html>