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


require_once("CONEXAO/DBPHP.php");
require_once("PUBLIC/Controlador.class.php");
require_once("PUBLIC/PessoaEleicao.class.php");
require_once("PUBLIC/ConcursoEleitoral.class.php");

session_start();
error_reporting(E_PARSE | E_ERROR);

require_once("Funcoes_Pessoa.php");

switch($_POST['Destino']) {
    case 'Votacao': $Origem = "LoginEleicoes.php?CodConcurso=".$_POST['CodConcurso']; break;
    case 'Administracao': $Origem = "LoginAdm.php"; break;
    case 'Solicitacao': $Origem = "LoginSol.php"; break;
    case 'SolicitacaoEnquete': $Origem = "LoginSol.php?Enquete"; break;
}

if (!AutenticaPessoa($_POST['Usuario'], $_POST['Senha'])) { ?>
	<html><body>
		<script> alert('Usuário ou senha inválidos.'); location.href = '<?=$Origem?>'; </script>
	</body></html>
    <?php
    exit;
}

$Pessoa = PessoaEleicao::devolvePessoaPorIdentificador($_POST['Usuario']);
if(is_null($Pessoa)) { ?>
	<html><body>
		<script> alert('Usuário ou senha inválidos.'); location.href = '<?=$Origem?>'; </script>
	</body></html>
    <?php
    exit;
}

if($Pessoa->get("pessoaautenticada") != "S") { ?>
	<html><body>
		<script> alert('Usuário não validado para o sistema.'); location.href = '<?=$Origem?>'; </script>
	</body></html>
    <?php
    exit;
}
$Controlador = Controlador::instancia($Pessoa);

switch($_POST['Destino']) {
  case 'Votacao':
      try {
          $Controlador->registraConcursoVotacao(new ConcursoEleitoral($_POST['CodConcurso']));
      }
      catch(ControladorException $e) { ?>
          <html><body>
            <script> alert('<?=$e->getMessage()?>'); location.href = '<?=$Origem?>'; </script>
          </body></html>
          <?php
          exit;
      }
      $Controlador->registraOrigem("LoginEleicoes.php?CodConcurso=".$_POST['CodConcurso']);
      header("Location: VOTACAO/ELC_Entrada.php");
      exit;
    case 'Administracao':
        if($Pessoa->eGerenteSistema() || $Pessoa->eMembroComissaoEleitoral()) {
            $Controlador->registraOrigem("LoginAdm.php");
            header("Location: ADMINISTRACAO/ELC_Cadastro_Concursos.php");
        }
        else {
            session_destroy(); ?>
            <html><body>
              <script> alert('Aplicação não disponível.'); location.href = 'LoginAdm.php'; </script>
            </body></html>
            <?php
        }
        exit;
    case 'Solicitacao':
        if($Pessoa->eSolicitante()) {
            $Controlador->registraOrigem("LoginSol.php");
            header("Location: SOLICITACAO/SOL_Solicitacao.php");
        }
        else {
            session_destroy(); ?>
            <html><body>
              <script> alert('Aplicação não disponível.'); location.href = 'LoginSol.php'; </script>
            </body></html>
            <?php
        }
        exit;
    case 'SolicitacaoEnquete':
        if($Pessoa->eSolicitante()) {
            $Controlador->registraOrigem("LoginSol.php?Enquete");
            header("Location: SOLICITACAO/SOL_Solicitacao_Enquete.php");
        }
        else {
            session_destroy(); ?>
            <html><body>
              <script> alert('Aplicação não disponível.'); location.href = 'LoginSol.php?Enquete'; </script>
            </body></html>
            <?php
        }
        exit;
}

