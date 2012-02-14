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
MostraCabecalho("Sistema de Eleições Eletrônicas");

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();
$Concurso = $Controlador->recuperaConcursoVotacao();

$Controlador->removeEleicaoVotacao();

if($Concurso->get("indbarradoporip") == "S")
    echo '<embed src="../Som/conffim.wav" hidden="true" autostart="true">';

$Eleicoes = $Concurso->devolveEleicoesDisponiveisEleitor($Pessoa);
if($Eleicoes->temRegistro()) {
    $Controlador->registraEleicaoVotacao($Eleicoes->proximo());
    ?>
    <h1>Seu voto foi efetuado com sucesso.</h1>

    <h2>Clique no botão abaixo para prosseguir para a <?=$Concurso->retornaString(STR_ELEICAO)?> seguinte.</h2>

    <p style="text-align: center;"><input type="button" value="Prosseguir" onclick="javascript: location.href = 'ELC_Urna.php';" /></p>

    </body>
    </html>
    <?php
}
else {
    ?>
    <script>window.setTimeout("location.href = '../ELC_Logout.php';", 3000);</script>
    <div class="Fim">FIM</div>

    </body>
    </html>
    <?php
}