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

define("DESCRICAO_ZERESIMA", "Zerésima");
define("DESCRICAO_CONTAGEM", "Contagem de votos");
define("DESCRICAO_RECONTAGEM", "Recontagem de votos");
define("DESCRICAO_FINALIZACAO", "Concurso Eleitoral finalizado");
define("DESCRICAO_ACESSOVOTACAO", "Acesso à área de votação");
define("DESCRICAO_INICIOVOTO", "Consistência OK, início da operação de voto");
define("DESCRICAO_VOTOEFETUADO", "Voto efetuado com sucesso");
define("DESCRICAO_EMAILS", "E-Mails enviados");

class LogOperacao extends Entidade {
    protected $NomeTabela = "eleicoes.logoperacao";
    protected $VetorChaves = array(
      "codconcurso"         => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "nrseqlogoperacao"    => array(Tipo => numero, Tamanho => 8, Foreign => false)
    );
    protected $VetorCampos = array(
      "codeleicao"          => array(Nome => "Eleição", Tipo => numero, Tamanho => 4, Obrigatorio => false, Classe => "Eleicao"),
      "codpessoaeleicao"    => array(Nome => "Pessoa", Tipo => numero, Tamanho => 8, Obrigatorio => false, Classe => "PessoaEleicao"),
      "dataoperacao"        => array(Nome => "Data de Operação", Tipo => datahora, Obrigatorio => true),
      "ip"                  => array(Nome => "IP", Tipo => texto, Tamanho => 15, Obrigatorio => true),
      "descricao"           => array(Nome => "Descrição", Tipo => texto, Tamanho => 120, Obrigatorio => true)
    );

    public static function getNumLogsPorDescricao($Descricao, ConcursoEleitoral $Concurso, Eleicao $Eleicao=null) {
        $SQL = " select count(*) as Num from eleicoes.logoperacao
                 where codconcurso = :CodConcurso[numero]
                   and descricao = :Descricao[texto] ";
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("CodConcurso", $Concurso->getChave());
        $Consulta->setParametros("Descricao", $Descricao);
        if(is_null($Eleicao))
            $Consulta->addSQL("and codeleicao is null");
        else {
            $Consulta->addSQL("and codeleicao = :CodEleicao[numero] ");
            $Consulta->setParametros("CodEleicao", $Eleicao->getChave());
        }
        $Consulta->executa(true);
        return (int)$Consulta->campo("Num");
    }

    public static function getLogPorDescricao($Descricao, ConcursoEleitoral $Concurso, Eleicao $Eleicao=null) {
        $SQL = " select * from eleicoes.logoperacao
                 where codconcurso = :CodConcurso[numero]
                   and descricao = :Descricao[texto] ";
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("CodConcurso", $Concurso->getChave());
        $Consulta->setParametros("Descricao", $Descricao);
        if(is_null($Eleicao))
            $Consulta->addSQL("and codeleicao is null");
        else {
            $Consulta->addSQL("and codeleicao = :CodEleicao[numero] ");
            $Consulta->setParametros("CodEleicao", $Eleicao->getChave());
        }
        if($Consulta->executa(true))
            return new LogOperacao($Consulta);
        else
            return null;
    }

    public static function getIteradorLogsPorDescricao($Descricao, ConcursoEleitoral $Concurso, Eleicao $Eleicao=null) {
        $SQL = " where codconcurso = :CodConcurso[numero]
                   and descricao = :Descricao[texto] ";
        if(is_null($Eleicao)) {
            $SQL .= " and codeleicao is null ";
            $CodEleicao = NULL;
        }
        else {
            $SQL .= "and codeleicao = :CodEleicao[numero] ";
            $CodEleicao = $Eleicao->getChave();
        }
        return new Iterador("LogOperacao", $SQL, array("CodConcurso" => $Concurso->getChave(), "CodEleicao" => $CodEleicao, "Descricao" => $Descricao));
    }
}
?>
