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

final class Controlador {
    private $PessoaLogada = NULL;
    private $ConcursoEdicao = NULL;
    private $EleicaoEdicao = NULL;
    private $ConcursoVotacao = NULL;
    private $EleicaoVotacao = NULL;
    private $VetorCedula = array();
    private $NrVotoAtual = NULL;
    private $Origem = NULL;

    private function __construct(PessoaEleicao $Pessoa) {
        $this->PessoaLogada = $Pessoa;
    }

/**
 *
 * @param int $CodPessoa
 * @return Controlador
 */
    public static function instancia($Pessoa = null) {
        if(isset($_SESSION['Controlador']))
            return $_SESSION['Controlador'];
        elseif(!is_null($Pessoa)) {
            if($Pessoa->valido()) {
                $_SESSION['Controlador'] = new Controlador($Pessoa);
                return $_SESSION['Controlador'];
            }
            else throw new ControladorException("Pessoa inválida", 0);
        }
        else {
            ob_clean();
            echo "Sessão expirada";
            exit;
        }
    }

/**
 *
 * @return PessoaEleicao
 */
    public function recuperaPessoaLogada() {
        return $this->PessoaLogada;
    }

    public function registraEleicaoEdicao(ConcursoEleitoral $Concurso, Eleicao $Eleicao) {
        if($this->PessoaLogada->eGerenteSistema() || ($Eleicao->verificaComissao($this->PessoaLogada) == COMISSAO_GERENTE)) {
            $this->ConcursoEdicao = $Concurso;
            $this->EleicaoEdicao = $Eleicao;
            return true;
        }
        else throw new ControladorException("Permissão negada", 0);
    }

    public function removeEleicaoEdicao() {
        unset($this->ConcursoEdicao);
        $this->ConcursoEdicao = null;
        unset($this->EleicaoEdicao);
        $this->EleicaoEdicao = null;
        return true;
    }

    public function registraConcursoVotacao(ConcursoEleitoral $Concurso) {
        if(!$Concurso->abertoParaVotacao()) {
            if($Concurso->estadoConcurso() < CONCURSO_INICIADO)
                throw new ControladorException("Concurso não iniciado", 0);
            if($Concurso->estadoConcurso() > CONCURSO_INICIADO)
                throw new ControladorException("Concurso já encerrado", 0);
        }
        $this->ConcursoVotacao = $Concurso;
        return true;
    }

    public function registraEleicaoVotacao(Eleicao $Eleicao) {
        if(!($this->ConcursoVotacao instanceof ConcursoEleitoral))
            throw new ControladorException("Não há concurso registrado", 0);
        if($Eleicao->get("codconcurso") != $this->ConcursoVotacao->get("codconcurso"))
            throw new ControladorException("A Eleição não faz parte do Concurso", 0);
        $Eleitor = $Eleicao->devolveEleitor($this->PessoaLogada);
        if(is_null($Eleitor))
            throw new ControladorException("Você não é eleitor desta eleição", 0);
        if(!is_null($Eleitor->get("datahoravoto")))
            throw new ControladorException("Você já votou nesta eleição", 0);
        if($this->ConcursoVotacao->get("indbarradoporip") == "S") {
            $Urna = $Eleicao->devolveUrnaPorIP($_SERVER['REMOTE_ADDR']);
            if(is_null($Urna))
                throw new ControladorException("Máquina não autorizada nesta eleição", 0);
            if($Urna->get("indativa") != "S")
                throw new ControladorException("Máquina não autorizada nesta eleição", 0);
        }
        elseif($this->ConcursoVotacao->get("indbarradoporip") == "E") {
            $Escopo = $Eleicao->devolveEscopoPorPrefixoIP($_SERVER['REMOTE_ADDR']);
            if(is_null($Escopo))
                throw new ControladorException("Máquina não autorizada nesta eleição", 0);
            if($Escopo->get("indativa") != "S")
                throw new ControladorException("Máquina não autorizada nesta eleição", 0);
        }
        if(!$Eleicao->eleicaoZerada())
            throw new ControladorException("Eleição não iniciada", 0);
        $this->EleicaoVotacao = $Eleicao;
        return true;
    }

    public function removeEleicaoVotacao() {
        unset($this->EleicaoVotacao);
        $this->EleicaoVotacao = null;
    }
/**
 *
 * @return ConcursoEleitoral
 */
    public function recuperaConcursoEdicao() {
        if($this->ConcursoEdicao instanceof ConcursoEleitoral) {
            return $this->ConcursoEdicao;
        }
        else throw new ControladorException("Não há concurso em edição", 1);
    }

/**
 *
 * @return ConcursoEleitoral
 */
    public function recuperaConcursoVotacao() {
        if($this->ConcursoVotacao instanceof ConcursoEleitoral) {
            $this->registraConcursoVotacao($this->ConcursoVotacao); // Refaz o registro para testar novamente as restrições
            return $this->ConcursoVotacao;
        }
        else throw new ControladorException("Não há concurso para votação", 1);
    }
/**
 *
 * @return Eleicao
 */
    public function recuperaEleicaoEdicao() {
        if($this->EleicaoEdicao instanceof Eleicao) {
            return $this->EleicaoEdicao;
        }
        else throw new ControladorException("Não há eleição em edição", 2);
    }
/**
 *
 * @return Eleicao
 */
    public function recuperaEleicaoVotacao() {
        if($this->EleicaoVotacao instanceof Eleicao) {
            $this->registraEleicaoVotacao($this->EleicaoVotacao);
            return $this->EleicaoVotacao;
        }
        else throw new ControladorException("Não há eleição para votação", 2);
    }

    public function inicializaVetorCedula() {
        $this->VetorCedula = array();
        array_fill(1, $this->EleicaoVotacao->get("nrpossibilidades"), "B");
    }

    public function registraNrVotoAtual($Voto) {
        if($Voto <= $this->EleicaoVotacao->get("nrpossibilidades"))
            $this->NrVotoAtual = $Voto;
        else throw new ControladorException("Voto além do número de possibilidades", 3);
    }

    public function registraVoto($Voto) {
        if(($Voto == "B") || ($Voto == "N") || (!is_null($this->EleicaoVotacao->devolveChapaPorNumero($Voto)))) {
            if(is_numeric($Voto)) {
                $PosVoto = array_search($Voto, $this->VetorCedula);
                if(($PosVoto !== false) && ($PosVoto !== $this->NrVotoAtual))
                    throw new ControladorException("Voto repetido", 4);
            }
            $this->VetorCedula[$this->NrVotoAtual] = $Voto;
        }
        else throw new ControladorException("Voto inválido: ".$Voto, 5);
    }

    public function devolveVetorCedula() {
        return $this->VetorCedula;
    }

    public function devolveNrVotoAtual() {
        return $this->NrVotoAtual;
    }

    public function devolveVoto($Voto) {
        if(isset($this->VetorCedula[$Voto]))
            return $this->VetorCedula[$Voto];
    }

    public function registraOrigem($Origem) {
        $this->Origem = $Origem;
    }

    public function recuperaOrigem() {
        return $this->Origem;
    }


    public function __destruct() {
        $_SESSION['Controlador'] = $this;
    }
}

class ControladorException extends Exception {
}
?>
