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

define("CONCURSO_NAOINICIADO", 0);
define("CONCURSO_INICIADO", 1);
define("CONCURSO_ENCERRADO", 2);

define("SITUACAOCONCURSO_CRIADO", 0);
define("SITUACAOCONCURSO_CARREGADO", 1);
define("SITUACAOCONCURSO_HOMOLOGADO", 2);
define("SITUACAOCONCURSO_APURADO", 3);
define("SITUACAOCONCURSO_ARQUIVADO", 4);

define("MODALIDADE_ELEICAO", 'E');
define("MODALIDADE_ENQUETE", 'Q');

define("STR_CONCURSOELEITORAL", 0);
define("STR_ELEICAO", 1);
define("STR_CHAPA", 2);
define("STR_GERENTE", 3);
define("STR_ELEITOR", 4);
define("STR_VOTO", 5);
define("STR_CONCURSOSELEITORAIS", 100);
define("STR_ELEICOES", 101);
define("STR_CHAPAS", 102);
define("STR_GERENTES", 103);
define("STR_ELEITORES", 104);
define("STR_VOTOS", 105);

require_once("Eleicao.class.php");
require_once("LogOperacao.class.php");

/**
 * A classe ConcursoEleitoral representa um grupo de eleições que ocorre em
 * um determinado período de tempo. Utilizando o exemplo das eleições federais,
 * a cada quatro anos, ocorre um CONCURSO ELEITORAL, dentro do qual se realizam
 * várias ELEIÇÕES: eleição para deputado estadual e federal, senador, governador
 * e presidente.
 */
final class ConcursoEleitoral extends Entidade {
    static private $ListaParticipacoes = array();
    static private $SituacaoConcursoTextual = array(
        0 => "Criado",
        1 => "Carregado",
        2 => "Homologado",
        3 => "Apurado",
        4 => "Arquivado"
    );

    static private $Strings = array(
        'E' => array(
            STR_CONCURSOELEITORAL   => "Concurso Eleitoral",
            STR_ELEICAO             => "Eleição",
            STR_CHAPA               => "Chapa",
            STR_GERENTE             => "Gerente",
            STR_ELEITOR             => "Eleitor",
            STR_VOTO                => "Voto",
            STR_CONCURSOSELEITORAIS => "Concursos Eleitorais",
            STR_ELEICOES            => "Eleições",
            STR_CHAPAS              => "Chapas",
            STR_GERENTES            => "Gerentes",
            STR_ELEITORES           => "Eleitores",
            STR_VOTOS               => "Votos",
        ),
        'Q' => array(
            STR_CONCURSOELEITORAL   => "Enquete",
            STR_ELEICAO             => "Questão",
            STR_CHAPA               => "Opção",
            STR_GERENTE             => "Responsável",
            STR_ELEITOR             => "Participante",
            STR_VOTO                => "Resposta",
            STR_CONCURSOSELEITORAIS => "Enquete",
            STR_ELEICOES            => "Questões",
            STR_CHAPAS              => "Opções",
            STR_GERENTES            => "Responsáveis",
            STR_ELEITORES           => "Participantes",
            STR_VOTOS               => "Respostas",
        )
    );
    protected $NomeTabela = "eleicoes.concursoeleitoral";
    protected $VetorChaves = array(
      "codconcurso" => array(Tipo => "numero", Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "descricao"           => array(Nome => "Descrição", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "datahorainicio"      => array(Nome => "Início", Tipo => datahora, Obrigatorio => true),
      "datahorafim"         => array(Nome => "Fim", Tipo => datahora, Obrigatorio => true),
      "indbarradoporip"     => array(Nome => "Barrado por IP", Tipo => texto, Obrigatorio => true, Valores => array('S', 'E', 'N')),
      "indhabilitacontagem" => array(Nome => "Contagem para gerentes", Tipo => texto, Obrigatorio => true, Valores => array('S', 'N')),
      "modalidadeconcurso"  => array(Nome => "Modalidade", Tipo => texto, Obrigatorio => true, Valores => array('E', 'Q')),
      "situacaoconcurso"    => array(Nome => "Situação", Tipo => numero, Obrigatorio => true, Valores => array(0, 1, 2, 3, 4))
    );
    private $EstadoConcurso = NULL;
/**
 * Cria uma nova Eleicao para o atual concurso.
 * @return Eleicao
 */
    public function geraEleicao() {
        return new Eleicao($this);
    }

/**
 * Devolve uma Eleicao do atual concurso. Caso ela não exista, devolve NULL.
 * @param int $CodEleicao O código da eleição desejada.
 * @return Eleicao
 */
    public function devolveEleicao($CodEleicao) {
        $Eleicao = new Eleicao($this, $CodEleicao);
        if($Eleicao->valido()) {
            return $Eleicao;
        }
        else {
            unset($Eleicao);
            return NULL;
        }
    }

/**
 * Devolve um iterador com todas as eleições do concurso.
 * @return Iterador
 */
    public function devolveEleicoes() {
        return new Iterador("Eleicao", " where codconcurso = :CodConcurso[numero] order by codeleicao ",
                            array("CodConcurso" => $this->Get("codconcurso")), $this);
    }

/**
 * Devolve um iterador com todas as eleições disponíveis para votação para uma
 * determinada pessoa.
 * @param PessoaEleicao $Pessoa
 * @return Iterador
 */
    public function devolveEleicoesDisponiveisEleitor(PessoaEleicao $Pessoa) {
        if($this->abertoParaVotacao()) {
            $SQL = "
where TAB.codconcurso = :CodConcurso[numero]
  and exists (select * from eleicoes.eleitor
              where codconcurso = TAB.codconcurso
                and codeleicao = TAB.codeleicao
                and codpessoaeleicao = :CodPessoaEleicao[numero]
                and datahoravoto is null)
  and exists (select * from eleicoes.logoperacao
              where codconcurso = TAB.codconcurso
                and codeleicao = TAB.codeleicao
                and descricao = '".DESCRICAO_ZERESIMA."') ";
            $Campos['CodPessoaEleicao'] = $Pessoa->getChave();
            switch($this->get("indbarradoporip")) {
                case 'S':
                    $SQL .= "
  and exists (select * from eleicoes.urnavirtual
              where codconcurso = TAB.codconcurso
                and codeleicao = TAB.codeleicao
                and ip = :IP[texto]
                and indativa = 'S') ";
                    $Campos['IP'] = $_SERVER['REMOTE_ADDR'];
                    break;
                case 'E':
                    $SQL .= "
  and exists (select * from eleicoes.dominioip
              where codconcurso = TAB.codconcurso
                and codeleicao = TAB.codeleicao
                and :IP[texto] like prefixoip || '%'
                and indativa = 'S') ";
                    $Campos['IP'] = $_SERVER['REMOTE_ADDR'];
                    break;
            }
            $Campos['CodConcurso'] = $this->getChave();
            return new Iterador("Eleicao", $SQL, $Campos, $this);
        }
        else throw new ConcursoEleitoralException("Concurso não iniciado", 0);
    }

/**
 * Informa se o ConcursoEleitoral está aberto para edição dos dados.
 * @return boolean
 */
    public function abertoParaAlteracoes() {
        return $this->estadoConcurso() == CONCURSO_NAOINICIADO;
    }
/**
 * Informa se o ConcursoEleitoral está aberto para votação.
 * @return boolean
 */
    public function abertoParaVotacao() {
        return ($this->estadoConcurso() == CONCURSO_INICIADO)
            && ($this->get("situacaoconcurso") == SITUACAOCONCURSO_HOMOLOGADO);
    }
/**
 * Informa se a modalidade do ConcursoEleitoral admite candidatos.
 * @return boolean
 */
    public function admiteCandidatos() {
        return ($this->get("modalidadeconcurso") == "E");
    }

/**
 * Informa o estado do ConcursoEleitoral em relação à data atual:
 * CONCURSO_NAOINICIADO, CONCURSO_INICIADO ou CONCURSO_ENCERRADO.
 * @return int
 */
    public function estadoConcurso() {
        if(is_null($this->EstadoConcurso)) {
            $SQL = " select
                       case when datahorainicio > now() then ".CONCURSO_NAOINICIADO."
                            when datahorafim > now() then ".CONCURSO_INICIADO."
                            else ".CONCURSO_ENCERRADO." end as situacao
                     from eleicoes.concursoeleitoral
                     where codconcurso = :CodConcurso[numero] ";
            $Consulta = new consulta($SQL);
            $Consulta->setparametros("CodConcurso", $this->GetChave());
            $Consulta->executa(true);
            $this->EstadoConcurso = (int)$Consulta->campo("situacao");
        }
        return $this->EstadoConcurso;
    }

/**
 * Informa a situação atual do ConcursoEleitoral em forma textual.
 * @return string Descrição da situação atual
 */
    public function situacaoConcursoTextual() {
        return self::$SituacaoConcursoTextual[$this->get("situacaoconcurso")];
    }
/**
 * Gera um vetor com o checklist do ConcursoEleitoral; ou seja, uma série de verificações
 * feitas sobre o concurso para verificar o andamento do cadastro.
 * @return array
 */
    public function geraChecklist() {
        $Checklist = array(
            0 => array(
                "SQL" => " select case when not exists
                                (select * from eleicoes.eleicao E
                                 where codconcurso = :CodConcurso[numero]
                                   and not exists
                                    (select * from eleicoes.chapa where codconcurso = E.codconcurso))
                            then 'S' else 'N' end as Check ",
                "OK" => "Já existem chapas cadastradas em todas as eleições deste concurso.",
                "Erro" => "Existem eleições neste concurso que não possuem chapas."
                ),
            1 => array(
                "SQL" => " select case when not exists
                                (select * from eleicoes.eleicao e
                                 where e.codconcurso = :CodConcurso[numero]
                                   and not exists
                                    (select * from eleicoes.eleitor
                                     where codconcurso = e.codconcurso
                                       and codeleicao = e.codeleicao))
                            then 'S' else 'N' end as Check ",
                "OK" => "Já existem eleitores cadastrados em todas as eleições deste concurso.",
                "Erro" => "Existem eleições neste concurso que não possuem eleitores."
                ),
            2 => array(
                "SQL" => " select case when
                                (select situacaoconcurso from eleicoes.concursoeleitoral
                                 where codconcurso = :CodConcurso[numero]) >= ".SITUACAOCONCURSO_HOMOLOGADO."
                            then 'S' else 'N' end as Check ",
                "OK" => "Este concurso eleitoral já foi homologado.",
                "Erro" => "A homologação final deste concurso ainda não foi realizada."
                ),
            3 => array(
                "SQL" => " select case when exists
                                (select 1 from eleicoes.logoperacao
                                  where codconcurso = :CodConcurso[numero]
                                    and descricao = '".DESCRICAO_ZERESIMA."')
                            then 'S' else 'N' end as Check ",
                "OK" => "A zerésima deste concurso já foi realizada.",
                "Erro" => "A zerésima deste concurso ainda não foi realizada."
                )
        );
        $ChecklistRetorno = array();
        foreach($Checklist as $i => $ItemChecklist) {
            $Consulta = new Consulta($ItemChecklist['SQL']);
            $Consulta->setParametros("CodConcurso", $this->getChave());
            $Consulta->executa(true);
            if($Consulta->campo("Check") == 'S')
                $ChecklistRetorno[$i]['Mensagem'] = $ItemChecklist['OK'];
            else
                $ChecklistRetorno[$i]['Mensagem'] = $ItemChecklist['Erro'];
            $ChecklistRetorno[$i]['OK'] = ($Consulta->campo("Check") == 'S');
        }
        return $ChecklistRetorno;
    }

    public function retornaString($String) {
        if(isset(self::$Strings['E'][$String]))
            return self::$Strings[$this->get("modalidadeconcurso")][$String];
        else
            return null;
    }

/**
 * Efetua o procedimento de contagem de votos para as eleições do ConcursoEleitoral.
 * Esse procedimento só pode ser realizado após o término do período de votação,
 * e habilita a apuração dos votos. Ele somente pode ser feito pelo gerente
 * do sistema.
 * @return boolean
 */
    public function realizaContagemVotos() {
        $Controlador = Controlador::instancia();
        $Pessoa = $Controlador->recuperaPessoaLogada();

        if(!$Pessoa->eGerenteSistema())
            throw new ConcursoEleitoralException("Operação exclusiva para gerentes", 1);
        if($this->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new ConcursoEleitoralException("Os votos só podem ser contados após o término do concurso", 2);
        $db = DB::instancia();
        $db->iniciaTransacao();
        $Eleicoes = $this->devolveEleicoes();
        foreach($Eleicoes as $Eleicao) {
            $Eleicao->realizaContagemVotos();
        }

        if(is_null(LogOperacao::getLogPorDescricao(DESCRICAO_CONTAGEM, $this)))
            $this->geraLogOperacao(DESCRICAO_CONTAGEM);
        else
            $this->geraLogOperacao(DESCRICAO_RECONTAGEM);

        $this->set("situacaoconcurso", SITUACAOCONCURSO_APURADO);
        $this->salva();

        $db->encerraTransacao();
        return true;
    }

/**
 * Encerra o concurso eleitoral, alterado sua sitação para Arquivado. Esse
 * procedimento pode ser feito somente após a contagem de votos, e apenas pelo
 * gerente do concurso.
 * @return boolean
 */
    public function finalizaConcurso() {
        $Controlador = Controlador::instancia();
        $Pessoa = $Controlador->recuperaPessoaLogada();
        if(!$Pessoa->eGerenteSistema())
            throw new ConcursoEleitoralException("Usuário sem permissão", 0);
        if($this->get("situacaoconcurso") < SITUACAOCONCURSO_APURADO)
            throw new ConcursoEleitoralException("O Concurso ainda não foi apurado", 0);
        $db = DB::instancia();
        $db->iniciaTransacao();
        $this->set("situacaoconcurso", SITUACAOCONCURSO_ARQUIVADO);
        $this->salva();

        $this->geraLogOperacao(DESCRICAO_FINALIZACAO);

        $db->encerraTransacao();
        return true;
    }

/**
 * Gera um LogOperacao para o ConcursoEleitoral atual, com a descrição informada.
 * @param string $Descricao
 * @return LogOperacao
 */
    public function geraLogOperacao($Descricao) {
        $Log = new LogOperacao($this);
        $Log->set("codpessoaeleicao", Controlador::instancia()->recuperaPessoaLogada());
        $Log->set("descricao", $Descricao);
        $Log->set("dataoperacao", null, "now()");
        $Log->set("ip", $_SERVER['REMOTE_ADDR']);
        $Log->salva();
        return $Log;
    }

    public static function devolveParticipacoes() {
        if(empty(self::$ListaParticipacoes)) {
            $SQL = " select * from eleicoes.participacao ";
            $Consulta = new Consulta($SQL);
            $Consulta->executa();
            while($Consulta->proximo())
                self::$ListaParticipacoes[$Consulta->campo("codparticipacao")] = $Consulta->campo("descricaoparticipacao");
        }
        return self::$ListaParticipacoes;
    }
}

class ConcursoEleitoralException extends Exception {
    
}
?>
