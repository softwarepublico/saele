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

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();
$Concurso = $Controlador->recuperaConcursoVotacao();

$Eleicoes = $Concurso->devolveEleicoesDisponiveisEleitor($Pessoa);
if($Eleicoes->temRegistro()) {
    $Eleicao = $Eleicoes->proximo();
    $Controlador->registraEleicaoVotacao($Eleicao);
    header("Location: ELC_Urna.php");
    exit;
}
else {
    MostraCabecalho("Sistema de Eleições Eletrônicas"); ?>
    <script>
        alert('Não há eleições disponíveis neste concurso para votação.');
        location.href = '../ELC_Logout.php';
    </script>
    </body>
    </html>
    <?php
}