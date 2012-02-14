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

/**
 * A classe Voto representa cada voto dado individualmente por um eleitor em
 * uma eleição. É importante notar que esta classe NÃO extende a classe Entidade,
 * e serve apenas para registrar um voto dado, não para recuperar um voto individual
 * e buscar seu valor.
 */
class Voto {
    private $Concurso;
    private $Eleicao;

    private $Voto = null;
    private $Urna = null;
    private $Escopo = null;

/**
 * Cria o voto para um dado Concurso e uma dada Eleicao.
 * @param ConcursoEleitoral $Concurso
 * @param Eleicao $Eleicao
 */
    public function __construct(ConcursoEleitoral $Concurso, Eleicao $Eleicao) {
        if($Eleicao->get("codconcurso") != $Concurso->getChave())
            throw new VotoException("Eleição inválida", 0);
        $this->Concurso = $Concurso;
        $this->Eleicao = $Eleicao;
    }

/**
 * Define a chapa para o qual o voto foi dado.
 * @param Chapa $Chapa
 */
    public function defineVotoChapa(Chapa $Chapa) {
        if(($Chapa->get("codconcurso") == $this->Concurso->getChave())
        && ($Chapa->get("codeleicao") == $this->Eleicao->getChave())) {
            $this->Voto = $Chapa;
        }
        else throw new VotoException("Chapa inválida para eleição atual", 0);
    }
/**
 * Define o voto como branco.
 */
    public function defineVotoBranco() {
        $this->Voto = "B";
    }
/**
 * Define o voto como nulo.
 */
    public function defineVotoNulo() {
        $this->Voto = "N";
    }
/**
 * Define a Urna no qual o voto foi dado.
 * @param Urna $Urna
 */
    public function defineUrna(UrnaVirtual $Urna) {
        $this->Urna = $Urna;
        $this->Escopo = NULL;
    }
/**
 * Define o EscopoIP no qual o voto foi dado.
 * @param EscopoIP $Escopo
 */
    public function defineEscopo(EscopoIP $Escopo) {
        $this->Escopo = $Escopo;
        $this->Urna = NULL;
    }
/**
 * Registra o Voto no banco de dados.
 * @return boolean
 */
    public function salva() {
        do {
            $Rand = rand(1, 999999);
            $Consulta = new consulta("
select * from eleicoes.voto
where codconcurso = :CodConcurso[numero]
and codeleicao = :CodEleicao[numero]
and numerorandomico = :Rand[numero]");
            $Consulta->setParametros("CodConcurso", $this->Concurso->getChave());
            $Consulta->setParametros("CodEleicao", $this->Eleicao->getChave());
            $Consulta->setParametros("Rand", $Rand);
        } while($Consulta->executa(true));
        $Insere = new consulta("
insert into eleicoes.voto
 (codconcurso, codeleicao, numerorandomico,
  indvotobranco, indvotonulo, codchapa,
  codurna, dominio)
values
 (:CodConcurso[numero], :CodEleicao[numero], :Rand[numero],
  :VotoBranco[texto], :VotoNulo[texto], :CodChapa[numero],
  :CodUrna[numero], :Dominio[numero]) ");
        $Insere->setParametros("CodConcurso", $this->Concurso->getChave());
        $Insere->setParametros("CodEleicao", $this->Eleicao->getChave());
        $Insere->setParametros("Rand", $Rand);
        
        if($this->Voto == "B") {
            $Insere->setParametros("VotoBranco", "S");
            $Insere->setParametros("VotoNulo", null);
            $Insere->setParametros("CodChapa", null);
        }
        elseif($this->Voto == "N") {
            $Insere->setParametros("VotoBranco", null);
            $Insere->setParametros("VotoNulo", "S");
            $Insere->setParametros("CodChapa", null);
        }
        elseif($this->Voto instanceof Chapa) {
            $Insere->setParametros("VotoBranco", null);
            $Insere->setParametros("VotoNulo", null);
            $Insere->setParametros("CodChapa", $this->Voto->getChave());
        }
        else throw new VotoException("Voto inválido: ".$this->Voto, 0);

        if($this->Urna instanceof UrnaVirtual) {
            $Insere->setParametros("CodUrna", $this->Urna->getChave());
            $Insere->setParametros("Dominio", null);
        }
        elseif($this->Escopo instanceof Escopo) {
            $Insere->setParametros("CodUrna", null);
            $Insere->setParametros("Dominio", $this->Escopo->getChave);
        }
        else
            $Insere->setParametros("CodUrna, Dominio", null);

        $Insere->executa();

        return true;
    }
}

class VotoException extends Exception {
}
?>
