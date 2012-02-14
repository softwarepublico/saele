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

require_once("Entidade.class.php");

class PessoaEleicao extends Entidade {
    protected $NomeTabela = "eleicoes.pessoaeleicao";
    protected $VetorChaves = array(
      "codpessoaeleicao"    => array(Tipo => numero, Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "cpf"                 => array(Nome => "CPF", Tipo => cpf, Tamanho => 11, Obrigatorio => true),
      "nomepessoa"          => array(Nome => "Nome", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "nrregistrogeral"     => array(Nome => "Registro Geral", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "email"               => array(Nome => "E-Mail", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "localtrabalho"       => array(Nome => "Local de Trabalho", Tipo => texto, Tamanho => 120, Obrigatorio => false),
      "pessoaautenticada"   => array(Nome => "Autenticada", Tipo => texto, Tamanho => 1, Obrigatorio => false),
      "gerentesistema"      => array(Nome => "Gerente do Sistema", Tipo => texto, Tamanho => 1, Obrigatorio => false),
      "solicitante"         => array(Nome => "Solicitante", Tipo => texto, Tamanho => 1, Obrigatorio => false),
      "identificacaousuario"=> array(Nome => "Identificação do Usuário", Tipo => texto, Tamanho => 30, Obrigatorio => true),
    );

/**
 * Informa se a pessoa já foi homologada.
 * @return boolean
 */
    public function homologada() {
        return ($this->get("pessoaautenticada") == "S");
    }

/**
 * Informa se a pessoa é gerente do sistema.
 * @return boolean
 */
    public function eGerenteSistema() {
        return $this->get("gerentesistema") == "S";
    }

/**
 * Informa se a pessoa pode solicitar concursos e enquetes.
 * @return boolean
 */
    public function eSolicitante() {
        return $this->get("solicitante") == "S";
    }

/**
 * Informa se a pessoa é membro de comissão eleitoral de alguma eleição.
 * @return boolean
 */
    public function eMembroComissaoEleitoral() {
        $SQL = " select * from eleicoes.comissaoeleitoral
                 where codpessoaeleicao = :CodPessoaEleicao[numero] ";
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("CodPessoaEleicao", $this->getChave());
        return $Consulta->executa(true);
    }

    public static function devolvePessoaPorIdentificador($Identificador) {
        $SQL = " select * from eleicoes.pessoaeleicao where identificacaousuario = :Identificador[texto] ";
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("Identificador", $Identificador);
        if($Consulta->executa(true))
            return new PessoaEleicao($Consulta);
        else
            return null;
    }
}
?>
