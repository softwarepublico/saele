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
 * Esta classe representa um endereço IP que terá permissão de acessar a
 * área de votação da Eleicao correspondente, caso o ConcursoEleitoral seja
 * restrito por urnas.
 */
class UrnaVirtual extends Entidade {
    protected $NomeTabela = "eleicoes.urnavirtual";
    protected $VetorChaves = array(
      "codconcurso" => array(Tipo => "numero", Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "codeleicao"  => array(Tipo => "numero", Tamanho => 4, Foreign => true, Classe => "Eleicao"),
      "codurna"     => array(Tipo => "numero", Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "ip"          => array(Nome => "Endereço IP", Tipo => texto, Tamanho => 15, Obrigatorio => true),
      "descricao"   => array(Nome => "Descrição", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "indativa"    => array(Nome => "Ativa", Tipo => texto, Tamanho => 1, Obrigatorio => true, Valores => array("S", "N"))
    );
    private $EdicaoIP = false;
    private $Concurso;
    private $Eleicao;

    public function __construct($Arg1, $Arg2=NULL, $Arg3=NULL) {
        parent::__construct($Arg1, $Arg2, $Arg3);
        if(($Arg1 instanceof ConcursoEleitoral) && ($Arg2 instanceof Eleicao)) {
            $this->Concurso = $Arg1;
            $this->Eleicao = $Arg2;
        }
        else {
            $this->Concurso = $Arg2['Concurso'];
            $this->Eleicao = $Arg2['Eleicao'];
        }
    }

/**
 * Devolve os octetos do endereço IP da urna como um vetor de quatro posições.
 * @return array
 */
    public function devolvePartesIP() {
        preg_match('/^([\d]{1,3})\.([\d]{1,3})\.([\d]{1,3})\.([\d]{1,3})$/', $this->get("ip"), $IP);
        if(count($IP) == 5)
            return array($IP[1], $IP[2], $IP[3], $IP[4]);
        else
            return array();
    }

    public function set($Campo, $Valor, $Mascara=null) {
        if(!$this->EdicaoIP && ($Campo == "ip"))
            throw new Exception("Utilize o método definePartesIP()", 0);
        else return parent::set($Campo, $Valor, $Mascara);
    }

/**
 * Define o endereço IP da urna, recebendo os quatro octetos como um vetor de
 * quatro posições. O endereço DEVE ser definido dessa forma.
 * @param array $IP
 * @return boolean
 */
    public function definePartesIP($IP) {
        if( (isset($IP[0]) && is_numeric($IP[0]) && ($IP[0] >= 0) && ($IP[0] <= 255))
         && (isset($IP[1]) && is_numeric($IP[1]) && ($IP[1] >= 0) && ($IP[1] <= 255))
         && (isset($IP[2]) && is_numeric($IP[2]) && ($IP[2] >= 0) && ($IP[2] <= 255))
         && (isset($IP[3]) && is_numeric($IP[3]) && ($IP[3] >= 0) && ($IP[3] <= 255))) {
            $this->EdicaoIP = true;
            $this->set("ip", implode(".", $IP));
            $this->EdicaoIP = false;
            return true;
        }
        else return false;
    }
/**
 * Devolve um iterador com todos os eleitores que votaram nesta urna.
 * @return Iterador
 */
    public function devolveVotantes() {
        return new Iterador("Eleitor",
            " where codconcurso = :codconcurso[numero]
                and codeleicao = :codeleicao[numero]
                and codurnavoto = :codurna[numero]",
            array("codconcurso" => $this->get("codconcurso"),
                  "codeleicao" => $this->get("codeleicao"),
                  "codurna" => $this->get("codurna")));
    }

    public function exclui() {
        if($this->Concurso->estadoConcurso() != CONCURSO_NAOINICIADO)
            throw new UrnaVirtualException("O Concurso Eleitoral já iniciou", 0);
        parent::exclui();
    }
}

class UrnaVirtualException extends Exception {
}
?>