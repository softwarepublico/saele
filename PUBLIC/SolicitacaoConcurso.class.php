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

require_once("ConcursoEleitoral.class.php");
final class SolicitacaoConcurso extends Entidade {
    static private $Strings = array(
        'E' => array(
            STR_CONCURSOELEITORAL   => "Concurso Eleitoral",
            STR_ELEICAO             => "Eleição",
            STR_CHAPA               => "Chapa",
            STR_GERENTE             => "Gerente",
            STR_ELEITOR             => "Eleitor",
            STR_CONCURSOSELEITORAIS => "Concursos Eleitorais",
            STR_ELEICOES            => "Eleições",
            STR_CHAPAS              => "Chapas",
            STR_GERENTES            => "Gerentes",
            STR_ELEITORES           => "Eleitores",
        ),
        'Q' => array(
            STR_CONCURSOELEITORAL   => "Enquete",
            STR_ELEICAO             => "Questão",
            STR_CHAPA               => "Resposta",
            STR_GERENTE             => "Responsável",
            STR_ELEITOR             => "Participante",
            STR_CONCURSOSELEITORAIS => "Enquetes",
            STR_ELEICOES            => "Questões",
            STR_CHAPAS              => "Respostas",
            STR_GERENTES            => "Responsáveis",
            STR_ELEITORES           => "Participantes",
        )
    );
    
    protected $NomeTabela = "eleicoes.solicitacaoconcurso";
    protected $VetorChaves = array(
        "nrseqsolicitacaoconcurso"  => array(Tipo => numero, Tamanho => 6, Foreign => false)
    );
    protected $VetorCampos = array(
        "nomeconcurso"          => array(Nome => "Nome do Concurso", Tipo => texto, Tamanho => 120, Obrigatorio => true),
        "datainicioconcurso"    => array(Nome => "Data de Início", Tipo => datahora, Obrigatorio => true),
        "datafimconcurso"       => array(Nome => "Data de Fim", Tipo => datahora, Obrigatorio => true),
        "nomepessoacontato"     => array(Nome => "Pessoa para Contato", Tipo => texto, Tamanho => 72, Obrigatorio => false),
        "ramalcontato"          => array(Nome => "Ramal para Contato", Tipo => texto, Tamanho => 5, Obrigatorio => false),
        "emailcontato"          => array(Nome => "E-Mail para Contato", Tipo => texto, Tamanho => 50, Obrigatorio => false),
        "comissaoeleitoral"     => array(Nome => "Comissão Eleitoral", Tipo => texto, Tamanho => 255, Obrigatorio => false),
        "gerentesconcurso"      => array(Nome => "Gerentes do Concurso", Tipo => texto, Tamanho => 255, Obrigatorio => false),
        "indbarradoporip"       => array(Nome => "Barrado por IP", Tipo => texto, Tamanho => 1, Obrigatorio => true, Valores => array("S", "E", "N")),
        "perfileleitores"       => array(Nome => "Perfil dos Eleitores", Tipo => texto, Tamanho => 1, Obrigatorio => true),
        "datasolicitacao"       => array(Nome => "Data de Solicitação", Tipo => datahora, Obrigatorio => false),
        "usuariosolicitacao"    => array(Nome => "Usuário de Solicitação", Tipo => numero, Tamanho => 6, Obrigatorio => true, Classe => "PessoaEleicao"),
        "dataatendimento"       => array(Nome => "Data de Atendimento", Tipo => datahora, Obrigatorio => false),
        "codconcurso"           => array(Nome => "Código do Concurso", Tipo => numero, Tamanho => 4, Obrigatorio => false, Classe => "ConcursoEleitoral"),
        "codorgaoescopo"        => array(Nome => "Órgão de Escopo", Tipo => numero, Tamanho => 5, Obrigatorio => false),
        "observacao"            => array(Nome => "Observação", Tipo => texto, Obrigatorio => false),
        "modalidadeconcurso"    => array(Nome => "Modalidade do Concurso", Tipo => texto, Tamanho => 1, Obrigatorio => true, Valores => array("E", "Q"))
    );

    public function retornaString($String) {
        if(isset(self::$Strings['E'][$String]))
            return self::$Strings[$this->get("modalidadeconcurso")][$String];
        else
            return null;
    }

    public function geraEleicaoSolicitacao() {
        return new EleicaoSolicitacao($this);
    }

    public function devolveEleicoesSolicitacao() {
        return new Iterador("EleicaoSolicitacao", " where nrseqsolicitacaoconcurso = :NrSeqSolicitacaoConcurso[numero]", array("NrSeqSolicitacaoConcurso" => $this->getChave()));
    }
}