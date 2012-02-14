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

final class Chapa extends Entidade {
    protected $NomeTabela = "eleicoes.chapa";
    protected $VetorChaves = array(
      "codconcurso" => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "codeleicao"  => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "Eleicao"),
      "codchapa"    => array(Tipo => numero, Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "descricao"           => array(Nome => "Descrição", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "nrchapa"             => array(Nome => "Número da chapa", Tipo => numero, Tamanho => 2, Obrigatorio => true),
      "nrvotosrecebidos"    => array(Nome => "Número de votos contados", Tipo => numero, Obrigatorio => false)
    );
    private $Concurso;
    private $Eleicao;

    public function __construct($Arg1, $Arg2=NULL, $Arg3=NULL) {
        parent::__construct($Arg1, $Arg2, $Arg3);
        if($Arg1 instanceof ConcursoEleitoral) {
            $this->Concurso = $Arg1;
            $this->Eleicao = $Arg2;
        }
        else {
            $this->Concurso = $Arg2['Concurso'];
            $this->Eleicao = $Arg2['Eleicao'];
        }
    }

/**
 * Devolve o número de votos recebidos pela Chapa atual. Como este procedimento 
 * informa parte do resultado da eleição, ele só pode ser executado após o
 * término do período de votação.
 * @return int
 */
    public function devolveNrVotos() {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new ChapaException("Os votos só podem ser contados após o término do concurso", 1);

        $SQL = " select count(*) as Nr from eleicoes.voto
                 where codconcurso = :codconcurso[numero]
                   and codeleicao = :codeleicao[numero]
                   and codchapa = :codchapa[numero] ";
        $Consulta = new Consulta($SQL);
        $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
        $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
        $Consulta->setParametros("codchapa", $this->get("codchapa"));
        $Consulta->executa(true);
        return $Consulta->campo("Nr");
    }

/**
 * Devolve o número de votos recebidos pela Chapa atual em uma determinada chapa.
 * Como este procedimento informa parte do resultado da eleição, ele só pode
 * ser executado após o término do período de votação.
 * @return int
 */
    public function devolveNrVotosPorUrna(UrnaVirtual $Urna) {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new ChapaException("Os votos só podem ser contados após o término do concurso", 1);

        if( ($Urna->get("codconcurso") == $this->get("codconcurso"))
         && ($Urna->get("codeleicao") == $this->get("codeleicao"))) {
            $SQL = " select count(*) as Nr from eleicoes.voto
                     where codconcurso = :codconcurso[numero]
                       and codeleicao = :codeleicao[numero]
                       and codchapa = :codchapa[numero]
                       and codurna = :codurna[numero] ";
            $Consulta = new Consulta($SQL);
            $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
            $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
            $Consulta->setParametros("codchapa", $this->get("codchapa"));
            $Consulta->setParametros("codurna", $Urna->get("codurna"));
            $Consulta->executa(true);
            return $Consulta->campo("Nr");
        }
        else throw new ChapaException("Urna inválida", 0);
    }

/**
 * Retorna um Iterador com todos os Candidatos da Chapa atual.
 * @return Iterador
 */
    public function devolveCandidatos() {
        $SQL = " where TAB.codconcurso = :CodConcurso[numero]
                   and TAB.codeleicao = :CodEleicao[numero]
                   and TAB.codchapa = :CodChapa[numero] ";
        $Campos = array("CodConcurso" => $this->get("codconcurso"),
                        "CodEleicao" => $this->get("codeleicao"),
                        "CodChapa" => $this->get("codchapa"));
        return new Iterador("Candidato", $SQL, $Campos);
    }

/**
 * Cadastra a PessoaEleicao informada como Candidato da Chapa atual, com a
 * Participacao informada.
 * @param PessoaEleicao $Pessoa
 * @param Participacao $Participacao
 * @return boolean
 */
    public function cadastraCandidato(PessoaEleicao $Pessoa, Participacao $Participacao) {
        $Candidato = new Candidato($this->Concurso, $this->Eleicao, $this, $Pessoa);
        $Candidato->set("codparticipacao", $Participacao);
        $Candidato->salva();
        return true;
    }

    public function exclui() {
        $Candidatos = $this->devolveCandidatos();
        foreach($Candidatos as $Candidato)
            $Candidato->exclui();
        return parent::exclui();
    }
}

class ChapaException extends Exception {
}
?>
