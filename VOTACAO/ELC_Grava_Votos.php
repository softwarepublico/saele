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
$db = db::instancia();

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

try {
    $Concurso = $Controlador->recuperaConcursoVotacao();
    $Eleicao = $Controlador->recuperaEleicaoVotacao();
}
catch(ControladorException $e) { ?>
    <div class="Erro">
        <p><strong>Erro:</strong> <?=$e->getMessage()?></p>
        <a href="../ELC_Logout.php">Voltar</a>
    </div>
    </body>
    </html>
    <?php
    exit;
}

$Eleitor = new Eleitor($Concurso, $Eleicao, $Pessoa);

switch($Concurso->get("indbarradoporip")) {
    case "S":
        $Urna = $Eleicao->devolveUrnaPorIP($_SERVER['REMOTE_ADDR']); break;
    case "E":
        $Escopo = $Eleicao->devolveEscopoPorPrefixoIP($_SERVER['REMOTE_ADDR']); break;
}

$db->iniciaTransacao();

$Consulta = new Consulta("lock table eleicoes.voto; ");
$Consulta->executa();

$Eleicao->geraLogOperacao(DESCRICAO_INICIOVOTO);
$VetorCedula = $Controlador->devolveVetorCedula();
foreach($VetorCedula as $DadoVoto) {
    $Voto = new Voto($Concurso, $Eleicao);
    if(is_numeric($DadoVoto))
        $Voto->defineVotoChapa($Eleicao->devolveChapaPorNumero($DadoVoto));
    elseif($DadoVoto == "B")
        $Voto->defineVotoBranco();
    elseif($DadoVoto == "N")
        $Voto->defineVotoNulo();

    if(isset($Urna))
        $Voto->defineUrna($Urna);
    elseif(isset($Escopo))
        $Voto->defineEscopo($Escopo);

    $Voto->salva();
}
if(isset($Urna))
    $Eleitor->set("codurnavoto", $Urna);
$Eleitor->set("indefetuouvoto", "S");
$Eleitor->set("datahoravoto", null, "now()");
$Eleitor->set("ipvoto", $_SERVER['REMOTE_ADDR']);
$Eleitor->salva();

$Eleicao->geraLogOperacao(DESCRICAO_VOTOEFETUADO);

$db->encerraTransacao();

header("Location: ELC_Fim.php");
