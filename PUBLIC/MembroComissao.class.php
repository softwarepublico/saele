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
 * Esta classe representa um membro da comissão eleitoral de uma eleição.
 * Além de um membro comum, uma pessoa pode ser gerente da eleição, e essa distinção
 * é indicada pela coluna "gerente".
 */
final class MembroComissao extends Entidade {
    protected $NomeTabela = "eleicoes.comissaoeleitoral";
    protected $VetorChaves = array(
      "codconcurso"     => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "codeleicao"      => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "Eleicao"),
      "codpessoaeleicao"=> array(Tipo => numero, Tamanho => 8, Foreign => true, Classe => "PessoaEleicao")
    );
    protected $VetorCampos = array(
      "gerente"     => array(Nome => "Gerente", Tipo => texto, Tamanho => 1, Obrigatorio => true, Valores => array("S", "N"))
    );

    protected $ClassesAnexadas = array(
        "PessoaEleicao" => array(
            "Tabela" => "eleicoes.pessoaeleicao",
            "Chaves" => array("codpessoaeleicao" => "codpessoaeleicao"),
            "Inner" => true)
    );

    private $Concurso, $Eleicao, $Pessoa;

    public function __construct($Arg1, $Arg2=null, $Arg3=null) {
        parent::__construct($Arg1, $Arg2, $Arg3);
        if($Arg1 instanceof ConcursoEleitoral) {
            $this->Concurso = $Arg1;
            $this->Eleicao = $Arg2;
            $this->Pessoa = $Arg3;
        }
    }

    public function salva() {
        if($this->novo()) {
            if($this->Eleicao->devolveCandidato($this->Pessoa) !== null)
                throw new MembroComissaoException("Esta pessoa é candidato desta Eleição", 2);
        }
        parent::salva();
    }
}

class MembroComissaoException extends Exception {
    
}
?>