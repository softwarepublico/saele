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

define("Nome", "Nome");
define("Tipo", "Tipo");
define("Tamanho", "Tamanho");
define("Obrigatorio", "Obrigatorio");
define("Classe", "Classe");
define("Foreign", "Foreign");
define("Autoincremento", "Autoincremento");
define("Valores", "Valores");

define("EXCECAO_CAMPONAOPREENCHIDO", 1);
define("EXCECAO_VALORINVALIDO", 2);
define("EXCECAO_ERROINSERCAO", 3);
define("EXCECAO_ERROEXCLUSAO", 4);
define("EXCECAO_VALORNEGATIVO", 5);
define("EXCECAO_QTDMINIMACARACTERES", 6);
define("EXCECAO_NENHUMCAMPOSELECIONADO", 7);
define("EXCECAO_DATAINVALIDA", 8);
define("EXCECAO_DATAINVALIDA_APENASNUMEROS", 9);
define("EXCECAO_CHAVENAOPREENCHIDA", 10);
define("EXCECAO_CHAVEINVALIDA", 11);
define("EXCECAO_OBJETOINVALIDO", 12);

/**
 *      - Classe abstrata para uma instância de uma entidade do banco de dados com *um* único
 *  registro como chave primária.
 */
abstract class Entidade {
    private $NomeChave = null;
    private $Chave = null;
    private $Autoincremento = null;
    private $VetorDadosChaves;
    private $VetorDados;
    private $Consulta;
    private $SQL;
    private $ObjetoGeneralizacao = null;
    private $Valido;
    private $Novo;
    private $Dummy;

    /**
     *      - Construtor da classe.
     * @param $NomeChave
     *      - Nome da coluna principal da chave primária.
     *      - Detectada automaticamente no momento da instanciação do método. Utilizado mais para enxugar o código.
     * @param $Chave
     *      - Chave primária do registro carregado.
     *      - Quando $Chave===NULL subentende-se que estamos trabalhando com um novo registro.
     * @param $Autoincremento
     *      - Coluna IDENTITY da tabela, caso exista.
     *      - A coluna é declarada na classe final. O nome da coluna é guardado neste atributo para referência futura.
     * @param $VetorDadosChaves
     *      - Vetor com o conteúdo das colunas da chave primária.
     * @param $VetorDados
     *      - Vetor com o conteúdo das colunas que não fazem parte da chave primária.
     * @param $Valido
     *      - Valor booleano que sinaliza se o objeto é válido.
     *      - Detectado no momento da instanciação.
     */
    function __construct() {
        if( func_num_args() > 0 && func_get_arg(0) == "dummy" ) {
            $this->Dummy = true;
            $this->Valido = false;
        }
        // Quando a classe recebe uma CONSULTA como único argumento, todos os dados do objeto serão carregados daquela consulta. Isto é utilizado principalmente com a classe Iterador
        elseif( func_num_args() > 0 && func_get_arg(0) instanceof Consulta ) {
            $Consulta = func_get_arg(0);
            foreach($this->VetorChaves as $Nome => $Array) {
              if(!isset($Array['Autoincremento']) || !$Array['Autoincremento']) {
                $this->VetorDadosChaves[$Nome] = $Consulta->campo($Nome);
                if(!isset($Array['Foreign']) || !$Array['Foreign']) {
                  $this->NomeChave = $Nome;
                  $this->Chave = $Consulta->campo($Nome, $Array['Tipo']);
                }
              }
              else {
                $this->NomeChave = $this->Autoincremento = $Nome;
                $this->VetorDadosChaves[$Nome] = $Consulta->campo($Nome);
                $this->Chave = $Consulta->campo($Nome, $Array['Tipo']);
              }
            }
            foreach($this->VetorCampos as $Nome => $Array) {
              if($Array['Tipo'] == "datahora") {
                $this->VetorDados[$Nome]['Valor'] = $Consulta->campo($Nome, datahora);
                $this->VetorDados[$Nome."_data"]['Valor'] = $Consulta->campo($Nome, data);
                $this->VetorDados[$Nome."_hora"]['Valor'] = $Consulta->campo($Nome, hora);
                $this->VetorDados[$Nome]['NovoValor'] = $Consulta->campo($Nome, datahora);
                $this->VetorDados[$Nome."_data"]['NovoValor'] = $Consulta->campo($Nome, data);
                $this->VetorDados[$Nome."_hora"]['NovoValor'] = $Consulta->campo($Nome, hora);
              }
              else {
                $this->VetorDados[$Nome]['Valor'] = $Consulta->campo($Nome, $Array['Tipo']);
                $this->VetorDados[$Nome]['NovoValor'] = $Consulta->campo($Nome, $Array['Tipo']);
              }
            }
            if(isset($this->ClasseGeneralizacao)) {
              $this->ObjetoGeneralizacao = new $this->ClasseGeneralizacao($this->GetChave());
            }

            $this->Consulta = $Consulta;
            $this->Novo = false;
            $this->Valido = true;
        }
        else {
            // Quando o argumento é um vetor
            if( (func_num_args() == 1) && (is_array(func_get_arg(0))) ) {
              $Chaves = func_get_arg(0);
            }
            // Quando são múltiplos argumentos
            else {
              $Chaves = array();
              $Args = func_get_args();
              $i = 0;
              foreach($this->VetorChaves as $Nome => $Array) {
                if(isset($Args[$i])) {
                  if($Args[$i] instanceof Consulta) {
                    echo "<pre>";
                    var_dump(debug_backtrace());
                    exit;
                  }
                  if(isset($Array['Classe']) && !is_null($Array['Classe']) && is_object($Args[$i]) && ($Args[$i]->eInstanciaGeneralizada($Array['Classe']))) {
                    $Chaves[$Nome] = $Args[$i]->GetChave();
                    $i++;
                  }
                  else
                    $Chaves[$Nome] = $Args[$i++];
                }
              }
            }
        
            foreach($this->VetorChaves as $Nome => $Array) {
                if(isset($Array['Foreign']) && ($Array['Foreign'] == true) && !isset($this->ClasseGeneralizacao)) {
                    if(!isset($Chaves[$Nome]) || is_null($Chaves[$Nome]))
                        throw new EntidadeChaveNaoPreenchidaException($Nome, EXCECAO_CHAVENAOPREENCHIDA); // Todas as colunas da chave primária que são chaves estrangeiras DEVEM ser preenchidas!
                    if(is_object($Chaves[$Nome]) && !( $Chaves[$Nome]->EInstanciaGeneralizada($this->VetorChaves[$Nome]['Classe'])))
                        throw new EntidadeChaveInvalidaException($Nome, EXCECAO_CHAVEINVALIDA); // A chave, se for um objeto, deve ser um objeto da classe definida
                    $this->VetorDadosChaves[$Nome] = $Chaves[$Nome];
                }
                else {
                    if(isset($Array['Autoincremento']) && $Array['Autoincremento']) {
                        $this->NomeChave = $this->Autoincremento = $Nome;
                    }

                    // Poderá haver uma coluna "identificadora" na chave primária, isto é, que não é uma chave estrangeira. Se esse valor for nulo, o objeto representa um novo registro
                    if(!isset($Chaves[$Nome]))
                        $this->Chave = $this->VetorDadosChaves[$Nome] = null;
                    else
                        $this->Chave = $this->VetorDadosChaves[$Nome] = $Chaves[$Nome];
                    $this->NomeChave = $Nome;
                }
            }

            
            if(!is_null($this->NomeChave) && is_null($this->Chave)) {
                // A tabela possui uma coluna identificadora (auto-incrementada),
                // e trata-se de um OBJETO NOVO e VÁLIDO.
                foreach($this->VetorCampos as $Nome => $Array) {
                    if(isset($Array['Default'])) {
                        $this->VetorDados[$Nome]['Valor'] = $Array['Default'];
                        $this->VetorDados[$Nome]['NovoValor'] = $Array['Default'];
                    }
                    else{
                        $this->VetorDados[$Nome]['Valor'] = NULL;
                        $this->VetorDados[$Nome]['NovoValor'] = NULL;
                    }
                }
                if(isset($this->ClasseGeneralizacao)) {
                    $this->ObjetoGeneralizacao = new $this->ClasseGeneralizacao();
                }
                $this->Novo = true;
                $this->Valido = true;
            }
            else {
                // Devemos montar o SELECT para descarregar os dados
                $Campos = array();

                $SQL = $this->devolveSQLConsulta()." WHERE ".$this->devolveClausulaWhere($Campos);

                $Consulta = new Consulta($SQL);
                $Consulta->setparametros(todos, $Campos);
                if($Consulta->executa(true)) {
                    // Existe registro -- descarrega os dados e marca objeto como NÃO NOVO e VÁLIDO
                    foreach($this->VetorCampos as $Nome => $Array) {
                        if($Array['Tipo'] == "data" && trim($Consulta->campo($Nome)) == ""){
                            $this->VetorDados[$Nome]['Valor'] = "";
                            $this->VetorDados[$Nome]['NovoValor']="";
                        }
                        elseif($Array['Tipo'] == "datahora") {
                            $this->VetorDados[$Nome]['Valor'] = $Consulta->campo($Nome, datahora);
                            $this->VetorDados[$Nome."_data"]['Valor'] = $Consulta->campo($Nome, data);
                            $this->VetorDados[$Nome."_hora"]['Valor'] = $Consulta->campo($Nome, hora);
                            $this->VetorDados[$Nome]['NovoValor'] = $Consulta->campo($Nome, datahora);
                            $this->VetorDados[$Nome."_data"]['NovoValor'] = $Consulta->campo($Nome, data);
                            $this->VetorDados[$Nome."_hora"]['NovoValor'] = $Consulta->campo($Nome, hora);
                        }
                        else {
                            $this->VetorDados[$Nome]['Valor'] = $Consulta->campo($Nome, $Array['Tipo']);
                            $this->VetorDados[$Nome]['NovoValor'] = $Consulta->campo($Nome, $Array['Tipo']);
                        }
                    }
                    if(isset($this->ClasseGeneralizacao)) {
                      $this->ObjetoGeneralizacao = new $this->ClasseGeneralizacao($this->Chave);
                    }
                    $this->Consulta = $Consulta;
                    $this->Novo = false;
                    $this->Valido = true;
                }
                elseif(is_null($this->NomeChave)) {
                    // NÃO existe registro, porém não há coluna identificadora;
                    // Marca objeto como NOVO e VÁLIDO
                    foreach($this->VetorCampos as $Nome => $Array) {
                        if(isset($Array['Default'])) {
                            $this->VetorDados[$Nome]['Valor'] = $Array['Default'];
                            $this->VetorDados[$Nome]['NovoValor'] = $Array['Default'];
                        }
                        else{
                            $this->VetorDados[$Nome]['Valor'] = NULL;
                            $this->VetorDados[$Nome]['NovoValor'] = NULL;
                        }
                    }
                    if(isset($this->ClasseGeneralizacao)) {
                        $this->ObjetoGeneralizacao = new $this->ClasseGeneralizacao();
                    }
                    $this->Novo = true;
                    $this->Valido = true;
                }
                // NÃO existe registro e existe coluna identificadora;
                // O objeto é INVÁLIDO, pois referencia um registro que não existe
                else $this->Valido = false;
            }
        }
    }

    // Métodos auxiliares para criação de iteradores //
    function devolveNomeTabela() {
        return $this->NomeTabela;
    }

    function devolveVetorChaves() {
        return $this->VetorChaves;
    }

    function devolveClassesAnexadas() {
        if(isset($this->ClassesAnexadas))
            return $this->ClassesAnexadas;
        else
            return null;
    }

    private function devolveSQLConsulta() {
        $ClausulaSelect = " SELECT TAB.* ";
        $ClausulaFrom = " FROM ".$this->NomeTabela." as TAB ";
        if(isset($this->ClassesAnexadas))
            foreach($this->ClassesAnexadas as $NomeClasse => $ClasseAnexada) {
                if(isset($ClasseAnexada['Alias']))
                    $Prefixo = $ClasseAnexada['Alias'];
                else
                    $Prefixo = $ClasseAnexada['Tabela'];

                $ClausulaSelect .= ", ".$Prefixo.".* ";

                $CondicoesJoin = array();
                foreach($ClasseAnexada['Chaves'] as $NomeChaveLocal => $NomeChaveExterna)
                    $CondicoesJoin[] = "TAB.".$NomeChaveLocal.' = '.$Prefixo.'.'.$NomeChaveExterna;

                $ClausulaFrom .= (!isset($ClasseAnexada['Inner']) || $ClasseAnexada['Inner'] ? ' inner ' : ' left outer ').' join '.$ClasseAnexada['Tabela']." ".(isset($ClasseAnexada['Alias']) ? $ClasseAnexada['Alias'] : null)
                                .' on '.implode(" and ", $CondicoesJoin);
            }
        return $ClausulaSelect.$ClausulaFrom;
    }

    private function devolveClausulaWhere(&$Campos) {
        $Criterios = array();
        foreach($this->VetorChaves as $NomeChave => $DadosChave) {
          $Criterios[] = "TAB.".$NomeChave." = :".$NomeChave."[".$DadosChave['Tipo']."]";
          if(is_object($this->VetorDadosChaves[$NomeChave]))
            $Campos[$NomeChave] = $this->VetorDadosChaves[$NomeChave]->GetChave();
          else
            $Campos[$NomeChave] = $this->VetorDadosChaves[$NomeChave]; // Deverá ser alterado quando permitirmos que objetos sejam passados como chave
        }
        return implode(" AND ", $Criterios);
    }

    public function devolveConsultaIterador() {
        $Consulta = new Consulta($this->devolveSQLConsulta());
        return $Consulta;
    }

/**
 * Verifica se o objeto instanciado é válido, ou seja,
 * se ele não representa um registro inexistente.
 * @return boolean
 */
    function valido() {
        return $this->Valido;
    }

/**
 * Verifica se o objeto instanciado é novo, ou seja,
 * representa um novo registro.
 * @return boolean
 */
    function novo() {
        return $this->Novo;
    }

/**
 * Devolve o valor da coluna identificadora do objeto.
 * @return mixed
 */
    function getChave() {
        if(!$this->Valido)
            throw new Exception("Objeto inválido", EXCECAO_OBJETOINVALIDO);
        if(is_null($this->NomeChave))
            return false;
        else return $this->Chave;
    }

/**
 * Altera o valor de um campo do objeto.
 * @param string $NomeCampo O Nome da coluna a ser alterada
 * @param mixed $Valor O valor desejado
 * @param string $Mascara Máscara para o valor
 * @return boolean
 */
    function set($NomeCampo, $Valor, $Mascara=NULL) {
        $NomeCampo = strtolower($NomeCampo);
        if(!$this->Valido)
            throw new Exception("Objeto inválido", EXCECAO_OBJETOINVALIDO);

        if(isset($this->VetorCampos[$NomeCampo])) {
            $Campo = $this->VetorCampos[$NomeCampo];
            if(is_null($Valor) && !is_null($Mascara) && (strpos($Mascara, '~') === FALSE)) {
                $this->VetorDados[$NomeCampo]['Mascara'] = $Mascara;
                return true;
            }
            if($Campo['Obrigatorio'] && (is_null($Valor) || (!is_object($Valor) && trim($Valor) == "")) && (is_null($Mascara) || (trim($Mascara) == "")))
                throw new Exception($Campo['Nome'], EXCECAO_CAMPONAOPREENCHIDO); // CAMPO OBRIGATÓRIO NÃO PREENCHIDO
          
            if(isset($Campo['Classe']) && !is_null($Campo['Classe']) && is_object($Valor) && ($Valor->EInstanciaGeneralizada($Campo['Classe'])))
                $this->VetorDados[$NomeCampo]['NovoValor'] = $Valor->GetChave();
            else {
                if(isset($Campo['Classe']) && !is_null($Campo['Classe']) && !Consulta::validaDado($Valor, $Campo['Tipo']))
                    throw new EntidadeValorInvalidoException($Campo['Nome']);

                if(trim($Valor) != "")
                    $this->VetorDados[$NomeCampo]['NovoValor'] = $Valor;
                else
                    $this->VetorDados[$NomeCampo]['NovoValor'] = "";
            }
            if(!is_null($Mascara))
                $this->VetorDados[$NomeCampo]['Mascara'] = $Mascara;
            else
                unset($this->VetorDados[$NomeCampo]['Mascara']);
            return true;
        }
        elseif(!is_null($this->ObjetoGeneralizacao)) {
            $this->ObjetoGeneralizacao->Set($NomeCampo, $Valor, $Mascara);
        }
        else {
            trigger_error("Campo inválido:".$NomeCampo, E_USER_WARNING);
            return false;
        }
    }

/**
 * Devolve o conteúdo de um campo do objeto.
 * @param string $NomeCampo O nome da coluna
 * @param string $Tipo O tipo de dado ao qual se deseja formatar
 * @return mixed
 */
    function get($NomeCampo, $Tipo=NULL) {
        $NomeCampo = strtolower($NomeCampo);
        if(!$this->Valido)
            throw new Exception("Objeto inválido", EXCECAO_OBJETOINVALIDO);

        if(isset($this->VetorChaves[$NomeCampo])) {
          return $this->VetorDadosChaves[$NomeCampo];
        }
        elseif(isset($this->VetorCampos[$NomeCampo])) {
          if(in_array($this->VetorCampos[$NomeCampo]['Tipo'], array(datahora, data)) && $Tipo == data) {
            return $this->VetorDados[$NomeCampo."_data"]['NovoValor'];
          }
          elseif(in_array($this->VetorCampos[$NomeCampo]['Tipo'], array(datahora, hora)) && $Tipo == hora)
            return $this->VetorDados[$NomeCampo."_hora"]['NovoValor'];
          else
            return $this->VetorDados[$NomeCampo]['NovoValor'];
        }
        elseif(!is_null($this->ObjetoGeneralizacao)) {
          return $this->ObjetoGeneralizacao->Get($NomeCampo, $Tipo);
        }
        else {
            throw new Exception("Campo inválido:".$NomeCampo, 0);
        }
    }

    function getObj($NomeClasse) {
        if(isset($this->ClassesAnexadas[$NomeClasse]))
            return new $NomeClasse($this->Consulta);
        else throw new Exception("Classe inválida", 0);
    }
    
    function getAll() {
      if(!$this->Valido)
          throw new Exception("Objeto inválido", EXCECAO_OBJETOINVALIDO);

      $VetorRetorno = array();
      foreach($this->VetorDadosChaves as $Nome => $Array)
          $VetorRetorno[$Nome] = $Array['NovoValor'];
      foreach($this->VetorDados as $Nome => $Array)
          $VetorRetorno[$Nome] = $Array['NovoValor'];
      if(!is_null($this->ObjetoGeneralizacao)) {
        return array_merge($VetorRetorno, $this->ObjetoGeneralizacao->GetAll());
      }
      return $VetorRetorno;
    }

    function getValorAntigo($NomeCampo) {
        $NomeCampo = strtolower($NomeCampo);
        if(!$this->Valido)
            throw new Exception("Objeto inválido", EXCECAO_OBJETOINVALIDO);

        if(isset($this->VetorChaves[$NomeCampo])) {
          return $this->VetorDadosChaves[$NomeCampo]['Valor'];
        }
        elseif(isset($this->VetorCampos[$NomeCampo])) {
          return $this->VetorDados[$NomeCampo]['Valor'];
        }
        elseif(!is_null($this->ObjetoGeneralizacao)) {
          return $this->ObjetoGeneralizacao->GetValorAntigo($NomeCampo);
        }
        else {
          trigger_error("Campo inválido.", E_USER_WARNING);
          return false;
        }
    }
    
    function existeCampo($NomeCampo) {
      $NomeCampo = strtolower($NomeCampo);
      return isset($this->VetorCampos[$NomeCampo]) || isset($this->VetorChaves[$NomeCampo]);
    }

    function salva() {
        if(!$this->Valido)
            throw new Exception("Objeto inválido", EXCECAO_OBJETOINVALIDO);

        $db = DB::instancia();
        $temTransacao = $db->transacaoIniciada();
        if(!$temTransacao)
            $db->iniciaTransacao();

        $StrCampos = $StrValores = NULL;

        if(!is_null($this->ObjetoGeneralizacao))
          $this->ObjetoGeneralizacao->Salva();

        if($this->Novo) {
            $Lock = new consulta("lock table ".$this->NomeTabela);
            $Lock->executa();

            if(!is_null($this->NomeChave) && is_null($this->Autoincremento)) {
                if(!is_null($this->ObjetoGeneralizacao)) {
                    $this->VetorDadosChaves[$this->NomeChave] = $this->Chave = $this->ObjetoGeneralizacao->GetChave();
                }
                else {
                    $SQL = " SELECT coalesce(max(:NomeChave[membrosql]), 0) + 1 as Chave
                             FROM ".$this->NomeTabela." WHERE (1=1) ";
                    $Consulta = new Consulta($SQL);
                    $Consulta->setparametros("NomeChave", $this->NomeChave);
                    foreach($this->VetorChaves as $NomeChave => $DadosChave) {
                        if(isset($DadosChave['Foreign']) && ($DadosChave['Foreign'] == true)) {
                            $Consulta->addsql(" AND ".$NomeChave." = :".$NomeChave."[".$DadosChave['Tipo']."]");
                            if(is_object($this->VetorDadosChaves[$NomeChave]))
                                $Consulta->setparametros($NomeChave, $this->VetorDadosChaves[$NomeChave]->GetChave()); // Deverá ser alterado quando permitirmos que objetos sejam passados como chave
                            else
                                $Consulta->setparametros($NomeChave, $this->VetorDadosChaves[$NomeChave]); // Deverá ser alterado quando permitirmos que objetos sejam passados como chave
                        }
                    }
                    $Consulta->executa(true);
                    $this->VetorDadosChaves[$this->NomeChave] = $this->Chave = $Consulta->campo("Chave");
                }
            }

          $ListaColunas = $ListaParametros = $Dados = array();
          foreach($this->VetorChaves as $NomeChave => $VetorChave) {
              $ListaColunas[] = $NomeChave;
              $ListaParametros[] = ":".$NomeChave."[".$VetorChave['Tipo']."]";
              if(is_object($this->VetorDadosChaves[$NomeChave]))
                  $Dados[$NomeChave] = $this->VetorDadosChaves[$NomeChave]->GetChave();
              else
                  $Dados[$NomeChave] = $this->VetorDadosChaves[$NomeChave]; // Deverá ser alterado quando permitirmos que objetos sejam passados como chave
          }
          foreach($this->VetorCampos as $NomeCampo => $VetorCampo) {
              $ListaColunas[] = $NomeCampo;
              if(isset($this->VetorDados[$NomeCampo]['Mascara'])) {
                  $a = preg_replace('/~/', ":".$NomeCampo."[".$VetorCampo['Tipo']."]", $this->VetorDados[$NomeCampo]['Mascara']);
                  $ListaParametros[] = preg_replace('/~/', ":".$NomeCampo."[".$VetorCampo['Tipo']."]", $this->VetorDados[$NomeCampo]['Mascara']);
              }
              else
                  $ListaParametros[] = ":".$NomeCampo."[".$VetorCampo['Tipo']."]";

              $Dados[$NomeCampo] = $this->VetorDados[$NomeCampo]['NovoValor'];
          }


          $SQL = " INSERT INTO ".$this->NomeTabela." (".implode(",", $ListaColunas).") VALUES (".implode(",", $ListaParametros)."); ";
          $Consulta = new Consulta($SQL);
          $Consulta->setparametros(todos, $Dados);
          $Consulta->executa();
          $this->Novo = false;
        }
        else {

          $Criterios = $ListaParametros = $Dados = array();
          foreach($this->VetorCampos as $NomeCampo => $VetorCampo) {
              if(isset($this->VetorDados[$NomeCampo]['Mascara']))
                  $ListaParametros[] = $NomeCampo." = ".preg_replace('/~/', ":".$NomeCampo."[".$VetorCampo['Tipo']."]", $this->VetorDados[$NomeCampo]['Mascara']);
              elseif(isset($this->VetorDados[$NomeCampo]['NovoValor'])) {
                  $ListaParametros[] = $NomeCampo." = :".$NomeCampo."[".$VetorCampo['Tipo']."]";
                  $Dados[$NomeCampo] = $this->VetorDados[$NomeCampo]['NovoValor'];
              }
          }

          if(!empty($Dados)) {
            /*
            foreach($this->VetorChaves as $NomeChave => $DadosChave) {
              $Criterios[] = $NomeChave." = :".$NomeChave."[".$DadosChave['Tipo']."]";
              if(is_object($this->VetorDadosChaves[$NomeChave]))
                $Dados[$NomeChave] = $this->VetorDadosChaves[$NomeChave]->GetChave();
              else
                $Dados[$NomeChave] = $this->VetorDadosChaves[$NomeChave]; // Deverá ser alterado quando permitirmos que objetos sejam passados como chave
            } */
            $SQL = " UPDATE ".$this->NomeTabela." TAB SET ".implode(",", $ListaParametros)." WHERE ".$this->devolveClausulaWhere($Dados);
            $Atualiza = new Consulta($SQL);
            $Atualiza->setparametros(todos, $Dados);
            $Atualiza->executa();
          }

        }

        if(!is_null($this->NomeChave))
          $this->VetorDadosChaves[$this->NomeChave] = $this->Chave;
        foreach($this->VetorDados as $Indice => $Array) {
            $this->VetorDados[$Indice]['Valor'] = $Array['NovoValor'];
        }
        if(!$temTransacao)
            $db->encerraTransacao();

    }

    function desfaz() {
        if(!$this->Valido)
            throw new Exception("Objeto inválido", EXCECAO_OBJETOINVALIDO);

        foreach($this->VetorDados as $Indice => $Array)  {
            $this->VetorDados[$Indice]['NovoValor'] = $this->VetorDados[$Indice]['Valor'];
        }
        if(!is_null($this->ObjetoGeneralizacao))
          $this->ObjetoGeneralizacao->Desfaz();
    }

    /*
     *      - Verifica todos os dados de uma entidade para ter certeza que ela não está modificada
     */
    function verificarMudanca(){
        if(!$this->Valido)
            throw new Exception("Objeto inválido", EXCECAO_OBJETOINVALIDO);

        $retorno = true;
        foreach($this->VetorDados as $Indice=>$Array){
            if(($Indice!="DataUltimaAtu") && ($Indice!="UsuarioUltimaAtua")){
                if($Array['Valor']!=$Array['NovoValor']){
                    $retorno = false;
                }
            }
        }
        if(!is_null($this->ObjetoGeneralizacao))
          return $retorno || $this->ObjetoGeneralizacao->VerificarMudanca();
        return $retorno;
    }

    function exclui() {
        if(!$this->Valido)
            throw new Exception("Objeto inválido", EXCECAO_OBJETOINVALIDO);

        $Campos = array();
        $SQL = " DELETE FROM ".$this->NomeTabela." TAB WHERE ".$this->devolveClausulaWhere($Campos);
        $Exclui = new Consulta($SQL);
        $Exclui->setparametros(todos, $Campos);
        if($Exclui->executa()) {
            if(!is_null($this->ObjetoGeneralizacao))
              $this->ObjetoGeneralizacao->Exclui();
            $this->Valido = FALSE;
            return TRUE;
        }
        else throw new Exception("Erro de exclusão", EXCECAO_ERROEXCLUSAO);
    }

    private function eInstanciaGeneralizada($Classe) {
        return ($this instanceof $Classe)
            || (!is_null($this->ObjetoGeneralizacao) && $this->ObjetoGeneralizacao->EInstanciaGeneralizada($Classe));
    }
}

class EntidadeChaveNaoPreenchidaException extends Exception {
}
class EntidadeChaveInvalidaException extends Exception {
}
class EntidadeValorInvalidoException extends Exception {
}
  
function ValidaData($data) {
  $regexpdata='/^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/';
  preg_match($regexpdata,$data,$resultado);
  return !empty($resultado);
}