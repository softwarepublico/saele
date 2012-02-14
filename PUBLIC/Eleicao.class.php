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

define("COMISSAO_MEMBRO", 1);
define("COMISSAO_GERENTE", 2);

define("ELEITOR_JAVOTOU", "JAVOTOU");
define("ELEITOR_NAOVOTOU", "NAOVOTOU");
define("ELEITOR_HOMOLOGADO", "HOMOLOGADO");
define("ELEITOR_NAOHOMOLOGADO", "NAOHOMOLOGADO");

define("CHAPAS_PORNUMERO", 0);
define("CHAPAS_PORVOTOSDESC", 1);

/**
 * Esta classe representa uma eleição dentro de um ConcursoEleitoral, no qual
 * o eleitor poderá terá uma ou mais possibilidades de voto em um número determinado
 * de chapas.
 */
final class Eleicao extends Entidade {
    protected $NomeTabela = "eleicoes.eleicao";
    protected $VetorChaves = array(
      "codconcurso" => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "codeleicao"  => array(Tipo => numero, Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "descricao"           => array(Nome => "Descrição", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "nrpossibilidades"    => array(Nome => "Número de possibilidades de votos", Tipo => numero, Obrigatorio => true),
      "nrdigitoschapa"      => array(Nome => "Número de dígitos para chava", Tipo => numero, Obrigatorio => true, Valores => array(1, 2)),
      "votosbrancos"        => array(Nome => "Votos brancos", Tipo => numero, Obrigatorio => false),
      "votosnulos"          => array(Nome => "Votos nulos", Tipo => numero, Obrigatorio => false)
    );
    private $Concurso;

    public function __construct($Arg1, $Arg2=NULL) {
        parent::__construct($Arg1, $Arg2);
        if($Arg1 instanceof ConcursoEleitoral) {
            $this->Concurso = $Arg1;
        }
        elseif($Arg2 instanceof ConcursoEleitoral) {
            $this->Concurso = $Arg2;
        }
    }

/**
 * Cria uma nova chapa para a atual eleição.
 * @return Chapa
 */
    public function geraChapa() {
        return new Chapa($this->Concurso, $this);
    }

/**
 * Devolve uma chapa da atual eleição. Caso ela não exista, devolve NULL.
 * @param int $CodChapa O código da chapa desejada.
 * @return Chapa
 */
    public function devolveChapa($CodChapa) {
        $Chapa = new Chapa($this->Concurso, $this, $CodChapa);
        if(!$Chapa->novo())
            return $Chapa;
        else
            return null;
    }

/**
 * Devolve um iterador com todas as chapas da eleição.
 * @return Iterador
 */
    public function devolveChapas($Ordem=null) {
        $StrOrdem = null;
        if($Ordem == CHAPAS_PORNUMERO)
            $StrOrdem = ' order by nrchapa ';
        elseif($Ordem == CHAPAS_PORVOTOSDESC)
            $StrOrdem = ' order by nrvotosrecebidos desc ';
        return new Iterador("Chapa",
                            "where codconcurso = :CodConcurso[numero]
                               and codeleicao = :CodEleicao[numero]".$StrOrdem,
                            array("CodConcurso" => $this->get("codconcurso"),
                                  "CodEleicao" => $this->getChave()),
                            array("Concurso" => $this->Concurso, "Eleicao" => $this));
    }
/**
 * Devolve o número total de votos depositados na eleição atual. Este procedimento
 * serve apenas para fins de auditoria.
 * @return int
 */
    public function devolveNrVotos() {
        $SQL = " select count(*) as Nr from eleicoes.voto
                 where codconcurso = :codconcurso[numero]
                   and codeleicao = :codeleicao[numero] ";
        $Consulta = new Consulta($SQL);
        $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
        $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
        $Consulta->executa(true);
        return $Consulta->campo("Nr");
    }

/**
 * Devolve o número de votos em branco depositados na eleição atual. Como este
 * procedimento informa parte do resultado da eleição, ele só pode ser executado
 * após o término do período de votação.
 * @return int
 */
    public function devolveNrVotosBrancos() {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new EleicaoException("Os votos só podem ser contados após o término do concurso", 1);

        $SQL = " select count(*) as Nr from eleicoes.voto
                 where codconcurso = :codconcurso[numero]
                   and codeleicao = :codeleicao[numero]
                   and indvotobranco = 'S' ";
        $Consulta = new Consulta($SQL);
        $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
        $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
        $Consulta->executa(true);
        return $Consulta->campo("Nr");
    }

/**
 * Devolve o número de votos nulos depositados na eleição atual. Como este
 * procedimento informa parte do resultado da eleição, ele só pode ser executado
 * após o término do período de votação.
 * @return int
 */
    public function devolveNrVotosNulos() {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new EleicaoException("Os votos só podem ser contados após o término do concurso", 1);
        $SQL = " select count(*) as Nr from eleicoes.voto
                 where codconcurso = :codconcurso[numero]
                   and codeleicao = :codeleicao[numero]
                   and indvotonulo = 'S' ";
        $Consulta = new Consulta($SQL);
        $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
        $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
        $Consulta->executa(true);
        return $Consulta->campo("Nr");
    }

/**
 * Devolve o número de votos em branco depositados em uma determinada urna
 * na eleição atual. Como este procedimento informa parte do resultado da
 * eleição, ele só pode ser executado após o término do período de votação.
 * @return int
 */
    public function devolveNrVotosBrancosPorUrna(UrnaVirtual $Urna) {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new EleicaoException("Os votos só podem ser contados após o término do concurso", 1);
        if( ($Urna->get("codconcurso") == $this->get("codconcurso"))
         && ($Urna->get("codeleicao") == $this->get("codeleicao"))) {
            $SQL = " select count(*) as Nr from eleicoes.voto
                     where codconcurso = :codconcurso[numero]
                       and codeleicao = :codeleicao[numero]
                       and indvotobranco = 'S'
                       and codurna = :codurna[numero] ";
            $Consulta = new Consulta($SQL);
            $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
            $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
            $Consulta->setParametros("codurna", $Urna->get("codurna"));
            $Consulta->executa(true);
            return $Consulta->campo("Nr");
        }
        else throw new EleicaoException("Urna inválida", 0);
    }

/**
 * Devolve o número de votos nulos depositados em uma determinada urna
 * na eleição atual. Como este procedimento informa parte do resultado da
 * eleição, ele só pode ser executado após o término do período de votação.
 * @return int
 */
    public function devolveNrVotosNulosPorUrna(UrnaVirtual $Urna) {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new EleicaoException("Os votos só podem ser contados após o término do concurso", 1);
        if( ($Urna->get("codconcurso") == $this->get("codconcurso"))
         && ($Urna->get("codeleicao") == $this->get("codeleicao"))) {
            $SQL = " select count(*) as Nr from eleicoes.voto
                     where codconcurso = :codconcurso[numero]
                       and codeleicao = :codeleicao[numero]
                       and indvotonulo = 'S'
                       and codurna = :codurna[numero] ";
            $Consulta = new Consulta($SQL);
            $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
            $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
            $Consulta->setParametros("codurna", $Urna->get("codurna"));
            $Consulta->executa(true);
            return $Consulta->campo("Nr");
        }
        else throw new EleicaoException("Urna inválida", 0);
    }

/**
 * Efetua o procedimento de contagem de votos para a eleição. Esse procedimento
 * só pode ser realizado após o término do período de votação do concurso.
 * @return boolean
 */
    public function realizaContagemVotos() {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new EleicaoException("Os votos só podem ser contados após o término do concurso", 1);
        $Chapas = $this->devolveChapas();
        foreach($Chapas as $Chapa) {
            $Chapa->set("nrvotosrecebidos", $Chapa->devolveNrVotos());
            $Chapa->salva();
        }
        $this->set("votosbrancos", $this->devolveNrVotosBrancos());
        $this->set("votosnulos", $this->devolveNrVotosNulos());
        $this->salva();
    }

/**
 * Gera uma nova urna para a eleição atual.
 * @return UrnaVirtual
 */
    public function geraUrna() {
        return new UrnaVirtual($this->Concurso, $this);
    }

/**
 * Devolve uma urna da atual eleição a partir do código. Caso ela não exista,
 * devolve NULL.
 * @param int $CodUrna O código da urna desejado.
 * @return UrnaVirtual
 */
    public function devolveUrna($CodUrna) {
        $Urna = new UrnaVirtual($this->Concurso, $this, $CodUrna);
        if(!$Urna->novo())
            return $Urna;
        else
            return null;
    }

/**
 * Devolve um iterador com todas as urnas cadastradas para a eleição.
 * @return Iterador
 */
    public function devolveUrnas() {
        return new Iterador("UrnaVirtual",
                            "where codconcurso = :CodConcurso[numero]
                               and codeleicao = :CodEleicao[numero]",
                            array("CodConcurso" => $this->get("codconcurso"),
                                  "CodEleicao" => $this->getChave()),
                            array("Concurso" => $this->Concurso, "Eleicao" => $this));
    }

/**
 * Devolve uma urna da atual eleição a partir do endereço IP. Caso ela não exista,
 * devolve NULL.
 * @param string $IP O endereço IP da urna.
 * @return UrnaVirtual
 */
    public function devolveUrnaPorIP($IP) {
        $Consulta = new Consulta('
select * from eleicoes.urnavirtual
where codconcurso = :CodConcurso[numero]
  and codeleicao = :CodEleicao[numero]
  and ip = :IP[texto] ');
        $Consulta->setparametros("CodConcurso", $this->Concurso->Get("codconcurso"));
        $Consulta->setparametros("CodEleicao", $this->Get("codeleicao"));
        $Consulta->setParametros("IP", $IP);
        if($Consulta->executa(true))
            return new UrnaVirtual($Consulta, array("Concurso" => $this->Concurso, "Eleicao" => $this));
        else
            return null;
    }

/**
 * Devolve a chapa a partir do número de votação. Caso não exista, devolve NULL.
 * @param int $NrChapa O número da chapa desejada.
 * @return Chapa
 */
    public function devolveChapaPorNumero($NrChapa) {
        $SQL = " select * from eleicoes.chapa
                 where codconcurso = :CodConcurso[numero]
                   and codeleicao = :CodEleicao[numero]
                   and nrchapa = :NrChapa[numero] ";
        $Consulta = new consulta($SQL);
        $Consulta->setparametros("CodConcurso", $this->Concurso->Get("codconcurso"));
        $Consulta->setparametros("CodEleicao", $this->Get("codeleicao"));
        $Consulta->setparametros("NrChapa", $NrChapa);
        if($Consulta->executa(true))
          return new Chapa($Consulta, array("Concurso" => $this->Concurso, "Eleicao" => $this));
        else
          return NULL;
    }
/**
 * Gera um novo escopo IP para a eleição.
 * @return EscopoIP
 */
    public function geraEscopoIP() {
        return new EscopoIP($this->Concurso, $this);
    }

/**
 * Devolve um escopo IP a partir de seu código. Caso não exista, devolve NULL.
 * @param int $NrSeqEscopo
 * @return EscopoIP
 */
    public function devolveEscopoIP($NrSeqEscopo) {
        $Escopo = new EscopoIP($this->Concurso, $this, $NrSeqEscopo);
        if(!$Escopo->novo())
            return $Escopo;
        else
            return null;
    }

/**
 * Devolve um iterador com todos os escopos IP da eleição.
 * @return Iterador
 */
    public function devolveEscoposIP() {
        return new Iterador("EscopoIP",
                            "where codconcurso = :CodConcurso[numero]
                               and codeleicao = :CodEleicao[numero]",
                            array("CodConcurso" => $this->get("codconcurso"),
                                  "CodEleicao" => $this->getChave()),
                            array("Concurso" => $this->Concurso, "Eleicao" => $this));
    }

/**
 * Devolve um escopo IP da eleição que englobe o endereço IP dado. Caso haja mais
 * de um escopo que satisfaça esse requisito, será devolvido o escopo mais
 * específico (ex. 192.168.15.* é mais específico do que 192.168.*.*). Caso
 * nenhum escopo IP incorpore o endereço dado, retorna NULL.
 * @param string $IP
 * @return EscopoIP
 */
    public function devolveEscopoPorPrefixoIP($IP) {
        $Consulta = new Consulta("
select * from eleicoes.dominioip
where codconcurso = :CodConcurso[numero]
  and codeleicao = :CodEleicao[numero]
  and :IP[texto] like prefixoip || '%' ");
        $Consulta->setParametros("CodConcurso", $this->get("codconcurso"));
        $Consulta->setParametros("CodEleicao", $this->get("codeleicao"));
        $Consulta->setParametros("IP", $IP);
        $Consulta->executa();
        $Escopo = null;
        $Octetos = 0;
        while($Consulta->proximo()) {
            if(substr_count($Consulta->campo("prefixoip"), ".") > $Octetos) {
                $Escopo = new EscopoIP($Consulta, array("Concurso" => $this->Concurso, "Eleicao" => $this));
                $Octetos = substr_count(".", $Consulta->campo("prefixoip"));
            }
        }
        return $Escopo;
    }

/**
 * Devolve um escopo IP da eleição a partir do prefixo exato. Caso não exista,
 * devolve NULL.
 * @param string $IP
 * @return EscopoIP
 */
    public function devolveEscopoPorIPExato($IP) {
        $Consulta = new Consulta("
select * from eleicoes.dominioip
where codconcurso = :CodConcurso[numero]
  and codeleicao = :CodEleicao[numero]
  and prefixoip = :IP[texto] ");
        $Consulta->setParametros("CodConcurso", $this->Concurso->getChave());
        $Consulta->setParametros("CodEleicao", $this->getChave());
        $Consulta->setParametros("IP", $IP);
        if($Consulta->executa(true))
            return new EscopoIP($Consulta, array("Concurso" => $this->Concurso, "Eleicao" => $this));
        else
            return null;
    }

/**
 * Devolve um iterador com os eleitores da eleição. É possível aplicar filtros
 * na lista de eleitores: ELEITOR_JAVOTOU, ELEITOR_NAOVOTOU, ELEITOR_HOMOLOGADO
 * e ELEITOR_NAOHOMOLOGADO. Esses filtros podem ser combinados, passando cada um
 * como um parâmetro diferente.
 * @return Iterador
 */
    public function devolveEleitores() {
        $SQL = " where TAB.codconcurso = :CodConcurso[numero]
                   and TAB.codeleicao = :CodEleicao[numero] ";
        foreach(func_get_args() as $Arg)
            switch($Arg) {
                case ELEITOR_JAVOTOU:
                    $SQL .= " and TAB.datahoravoto is not null "; break;
                case ELEITOR_NAOVOTOU:
                    $SQL .= " and TAB.datahoravoto is null "; break;
                case ELEITOR_HOMOLOGADO:
                    $SQL .= " and P.pessoaautenticada = 'S' "; break;
                case ELEITOR_NAOHOMOLOGADO:
                    $SQL .= " and coalesce(P.pessoaautenticada, 'N') = 'N' "; break;
            }
        $SQL .= " order by P.nomepessoa ";
        $Campos = array("CodConcurso" => $this->get("codconcurso"), "CodEleicao" => $this->get("codeleicao"));
        return new Iterador("Eleitor", $SQL, $Campos, array("Concurso" => $this->Concurso, "Eleicao" => $this));
    }

/**
 * Devolve um objeto Eleitor para a PessoaEleicao informada, caso ela seja
 * eleitora da Eleicao atual. Caso contrário, devolve NULL.
 * @param PessoaEleicao $Pessoa
 * @return Eleitor
 */
    public function devolveEleitor(PessoaEleicao $Pessoa) {
        $Eleitor = new Eleitor($this->Concurso, $this, $Pessoa);
        if(!$Eleitor->novo())
            return $Eleitor;
        else
            return null;
    }

/**
 * Exclui eleitores da eleição. É possível aplicar filtros para a exclusão:
 * ELEITOR_JAVOTOU, ELEITOR_NAOVOTOU, ELEITOR_HOMOLOGADO e ELEITOR_NAOHOMOLOGADO.
 * Esses filtros podem ser combinados, passando cada um como um parâmetro diferente.
 * @return boolean
 */
    public function excluiEleitores() {
        $SQL = " delete from eleicoes.eleitor
                 where codconcurso = :CodConcurso[numero]
                   and codeleicao = :CodEleicao[numero] ";
        foreach(func_get_args() as $Arg)
            switch($Arg) {
                case ELEITOR_JAVOTOU:
                    $SQL .= " and datahoravoto is not null "; break;
                case ELEITOR_NAOVOTOU:
                    $SQL .= " and datahoravoto is null "; break;
                case ELEITOR_HOMOLOGADO:
                    $SQL .= " and codpessoaeleicao in (select codpessoaeleicao from eleicoes.pessoaeleicao where pessoaautenticada = 'S') "; break;
                case ELEITOR_NAOHOMOLOGADO:
                    $SQL .= " and codpessoaeleicao in (select codpessoaeleicao from eleicoes.pessoaeleicao where and coalesce(P.pessoaautenticada, 'N') = 'N') "; break;
            }
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("CodConcurso", $this->get("CodConcurso"));
        $Consulta->setParametros("CodEleicao", $this->get("CodEleicao"));
        return $Consulta->executa();
    }

/**
 * Informa se a eleição já teve a zerésima realizada.
 * @return boolean
 */
    public function eleicaoZerada() {
        return (!is_null($this->get("votosbrancos"))) && (!is_null($this->get("votosnulos")));
    }

/**
 * Zera os votos de uma eleição. Procedimento necessário para
 * o início de uma eleição.
 * @return boolean
 */
    public function realizaZeresima() {
        $Controlador = Controlador::instancia();
        $Pessoa = $Controlador->recuperaPessoaLogada();
        if(!$Pessoa->eGerenteSistema() && ($this->verificaComissao($Pessoa) != COMISSAO_GERENTE))
            throw new EleicaoException("Pessoa sem permissão", 0);

        if($this->eleicaoZerada())
            throw new EleicaoException("A eleição já foi zerada", 0);

        try {
            $db = DB::instancia();
            $db->iniciaTransacao();
            $Chapas = $this->devolveChapas();
            foreach($Chapas as $Chapa) {
                $Chapa->set("nrvotosrecebidos", 0);
                $Chapa->salva();
            }
            $this->set("votosbrancos", 0);
            $this->set("votosnulos", 0);
            $this->salva();

            $Log = new LogOperacao($this->Concurso);
            $Log->set("codeleicao", $this);
            $Log->set("codpessoaeleicao", $Pessoa);
            $Log->set("dataoperacao", null, "now()");
            $Log->set("ip", $_SERVER['REMOTE_ADDR']);
            $Log->set("descricao", DESCRICAO_ZERESIMA);
            $Log->salva();
            $db->encerraTransacao();
            return true;
        }
        catch(Exception $e) {
            Consulta::desfazTransacao();
            throw $e;
        }
    }

/**
 * Verifica se a pessoa informada é membro da comissão ou gerente da eleição.
 * Retorna false, se a pessoa não for nenhum dos dois, ou
 * retorna ELEICAO_GERENTE ou ELEICAO_MEMBROCOMISSAO.
 * @param PessoaEleicao $Pessoa
 * @return int|boolean
 */
    public function verificaComissao(PessoaEleicao $Pessoa) {
        $MembroComissao = new MembroComissao($this->Concurso, $this, $Pessoa);
        if(!$MembroComissao->novo()) {
            if($MembroComissao->get("gerente") == "S")
                return COMISSAO_GERENTE;
            else
                return COMISSAO_MEMBRO;
        }
        else return false;
    }

/**
 * Devolve o objeto Candidato para a PessoaEleicao informada, caso ela seja
 * candidato de alguma chapa da eleição corrente. Caso contrário, devolve NULL.
 * @param PessoaEleicao $Pessoa
 * @return Candidato
 */
    public function devolveCandidato(PessoaEleicao $Pessoa) {
        $SQL = " select * from eleicoes.candidato
                 where codconcurso = :CodConcurso[numero]
                   and codeleicao = :CodEleicao[numero]
                   and codpessoaeleicao = :CodPessoaEleicao[numero] ";
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("CodConcurso", $this->get("CodConcurso"));
        $Consulta->setParametros("CodEleicao", $this->get("CodEleicao"));
        $Consulta->setParametros("CodPessoaEleicao", $Pessoa->get("CodPessoaEleicao"));
        if($Consulta->executa(true))
            return new Candidato($Consulta);
        else
            return null;
    }

/**
 * Retorna todos os gerentes da eleição.
 * @return Iterador
 */
    public function devolveGerentes() {
        $SQL = " where TAB.codconcurso = :CodConcurso[numero]
                   and TAB.codeleicao = :CodEleicao[numero]
                   and TAB.gerente = 'S' ";
        $Campos = array("CodConcurso" => $this->get("codconcurso"), "CodEleicao" => $this->get("codeleicao"));
        return new Iterador("MembroComissao", $SQL, $Campos);
    }

/**
 * Devolve o objeto MembroComissao para a PessoaEleicao informada, caso ela seja
 * gerente eleição corrente. Caso contrário, devolve NULL.
 * @param PessoaEleicao $Pessoa
 * @return Candidato
 */
    public function devolveGerente(PessoaEleicao $Pessoa) {
        $Gerente = new MembroComissao($this->Concurso, $this, $Pessoa);
        if(!$Gerente->novo() && ($Gerente->get("gerente") == "S"))
            return $Gerente;
        else
            return null;
    }

/**
 * Retorna todos os membros da comissão eleitoral da eleição.
 * @return Iterador
 */
    public function devolveMembrosComissao() {
        $SQL = " where TAB.codconcurso = :CodConcurso[numero]
                   and TAB.codeleicao = :CodEleicao[numero]
                   and TAB.gerente = 'N' ";
        $Campos = array("CodConcurso" => $this->get("codconcurso"), "CodEleicao" => $this->get("codeleicao"));
        return new Iterador("MembroComissao", $SQL, $Campos);
    }

/**
 * Devolve o objeto MembroComissao para a PessoaEleicao informada, caso ela seja
 * membro da comissão eleitoral da eleição corrente. Caso contrário, devolve NULL.
 * @param PessoaEleicao $Pessoa
 * @return Candidato
 */
    public function devolveMembroComissao(PessoaEleicao $Pessoa) {
        $Membro = new MembroComissao($this->Concurso, $this, $Pessoa);
        if($Gerente->novo() || ($Membro->get("gerente") == "S"))
            return null;
        else
            return $Membro;
    }

/**
 * Cadastra a PessoaEleicao informada como gerente da eleição.
 * @param PessoaEleicao $Pessoa
 * @return boolean
 */
    public function cadastraGerente(PessoaEleicao $Pessoa) {
        $MembroComissao = new MembroComissao($this->Concurso, $this, $Pessoa);
        if(!$MembroComissao->novo())
            throw new MembroComissaoException("Esta pessoa já faz parte da comissão eleitoral", 1);
        $MembroComissao->set("gerente", "S");
        $MembroComissao->salva();
        return true;
    }

/**
 * Cadastra a PessoaEleicao informada na comissão eleitoral da eleição.
 * @param PessoaEleicao $Pessoa
 * @return boolean
 */
    public function cadastraMembroComissao(PessoaEleicao $Pessoa) {
        $MembroComissao = new MembroComissao($this->Concurso, $this, $Pessoa);
        if(!$MembroComissao->novo())
            throw new MembroComissaoException("Esta pessoa já faz parte da comissão eleitoral", 1);
        $MembroComissao->set("gerente", "N");
        $MembroComissao->salva();
        return true;
    }

/**
 * Cadastra a PessoaEleicao informada como Eleitor da eleição.
 * @param PessoaEleicao $Pessoa
 * @return boolean
 */
    public function cadastraEleitor(PessoaEleicao $Pessoa) {
        $Eleitor = new Eleitor($this->Concurso, $this, $Pessoa);
        $Eleitor->salva();
        return true;
    }

/**
 * Devolve um iterador com as pessoas não homologadas relacionadas à eleição:
 * membros da comissão, gerentes, candidatos e eleitores.
 * @return Iterador
 */
    public function devolvePessoasNaoHomologadas() {
        $SQL = " WHERE TAB.pessoaautenticada = 'N'
                 AND EXISTS (SELECT * from eleicoes.eleitor
                             where codconcurso = :codconcurso[numero]
                               and codeleicao = :codeleicao[numero]
                               and codpessoaeleicao = TAB.codpessoaeleicao)
                  OR EXISTS (SELECT * from eleicoes.candidato
                             where codconcurso = :codconcurso[numero]
                               and codeleicao = :codeleicao[numero]
                               and codpessoaeleicao = TAB.codpessoaeleicao)
                  OR EXISTS (SELECT * from eleicoes.comissaoeleitoral
                             where codconcurso = :codconcurso[numero]
                               and codeleicao = :codeleicao[numero]
                               and codpessoaeleicao = TAB.codpessoaeleicao)
               ORDER BY TAB.nomepessoa ";
        return new Iterador("PessoaEleicao", $SQL, array("codconcurso" => $this->get("codconcurso"), "codeleicao" => $this->get("codeleicao")));
    }

/**
 * Gera um LogOperacao para a Eleicao atual, com a descrição informada.
 * @param string $Descricao
 * @return LogOperacao
 */
    public function geraLogOperacao($Descricao) {
        $Log = new LogOperacao($this->Concurso);
        $Log->set("codeleicao", $this);
        $Log->set("codpessoaeleicao", Controlador::instancia()->recuperaPessoaLogada());
        $Log->set("descricao", $Descricao);
        $Log->set("dataoperacao", null, "now()");
        $Log->set("ip", $_SERVER['REMOTE_ADDR']);
        $Log->salva();
        return $Log;
    }
}

class EleicaoException extends Exception {
}
?>
