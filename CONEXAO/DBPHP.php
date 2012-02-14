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

define('BANCO_DEFAULT', 'eleicoes');

class DB {
  private $debug;
  private $conexao;
  private $transacao = false;

  private static $instancia = NULL;

  private function __construct($NomeBanco = null) {
    if(trim($NomeBanco) == "")
      $this->conecta(BANCO_DEFAULT);
    else
      $this->conecta($NomeBanco);
    
    $this->transacao = false;
  }

/**
 *
 * @param string $NomeBanco
 * @return DB
 */
  public static function instancia($NomeBanco = NULL) {
    if(is_null(self::$instancia)) {
          self::$instancia = new DB($NomeBanco);
    }
    else {
        if(trim($NomeBanco) != "")
            self::$instancia->bancoPadrao($NomeBanco);
    }
    return self::$instancia;
  }

  private function conecta($nomebanco) {
    $host = ''; // ENDEREÇO DO BANCO DE DADOS
    $user = ''; // USUÁRIOl
    $pass = ''; // SENHA

    $ConnString = "host=".$host." port=5432 dbname=".$nomebanco." user=".$user." password=".$pass;
    $this->conexao = pg_connect($ConnString);
    if(!$this->conexao)
      throw new Exception('Não foi possível conectar ao banco de dados', 1);

    return true;
  }

  private function desconecta() {
    pg_close($this->conexao);
  }


  function bancopadrao($NomeBanco) {
    if (!is_null($nomebanco)) {
      return pg_query($this->conexao, "\connect ".pg_escape_string($nomebanco));
    } else {
      return false;
    }
  }

  function debug($status=true) {
      $this->debug = $status;
  }

  function executaQuery($SQL) {
      return @pg_query($this->conexao, $SQL);
  }

    public function transacaoIniciada() {
        return $this->transacao;
    }

    public function iniciaTransacao() {
        if($this->transacao)
            return false;
        
        @pg_query(" start transaction ");
        $this->transacao = true;
        return true;
    }
    
    public function encerraTransacao() {
        if($this->transacao) {
            @pg_query(" commit transaction ");
            $this->transacao = false;
            return true;
        }
        else return false;
    }

    public function desfazTransacao() {
        if($this->transacao) {
            @pg_query(" rollback transaction ");
            $this->transacao = false;
            return true;
        }
        else return false;
    }

      function __destruct() {
        if($this->transacao)
            $this->desfazTransacao();
        $this->desconecta();
        self::$instancia = NULL;
      }
}

define('membrosql','membrosql');
define('texto','texto');
define('html','html');
define('data','data');
define('hora','hora');
define('datahora','datahora');
define('moeda','moeda');
define('numero','numero');
define('email','email');
define('cep','cep');
define('cpf','cpf');
define('expressaoregular','expressaoregular');
define('escopo','escopo');
define('todas','todas');
define('todos','todos');

define('bancoteste', ''); // OPCIONAL

define("ASPA", "'");

class Consulta {
  private $db;
  private $sqloriginal;
  private $sql;
  private $recordset;
  private $campo;
  private $linhaatual;
  private $ultimalinha;
  private $eof;
  private $bof;

  function consulta($SQL = null) {
    $this->sql = NULL;
    $this->sqloriginal = NULL;
    $this->recordset = NULL;
    $this->linhaatual = NULL;

    $this->eof = $this->bof = NULL;
    $this->db = DB::instancia();

    if (trim($SQL) != "") {
      $this->setSQL($SQL);
    }
  }

  function __sleep() {
      unset($this->db);
      unset($this->recordset);
      return array('sqloriginal', 'sql', 'linhaatual', 'ultimalinha', 'eof', 'bof');
  }

  function __wakeup() {
      $this->db = DB::instancia();
      if(!is_null($this->linhaatual))
        $this->executa();
  }

  function setSQL($SQL) {
      $this->sql = $this->sqloriginal = $SQL;
  }

  function getSQL($original = false) {
    if ($original) {
      return $this->sqloriginal;
    } else {
      return $this->sql;
    }
  }


  function addSQL($sqladicional) {
      $this->sqloriginal .= $sqladicional;
      $this->sql .= $sqladicional;
  }


  function setParametros($NomeParametros, $ValorParametros) {
    $VetorErros = array();

    if($NomeParametros == todos)
        $VetorParametrosUsr = true;
    else
        $VetorParametrosUsr = array_map("trim", explode(",", $NomeParametros));

    preg_match_all('/:([a-zA-Z0-9_]+)\[([a-zA-Z]+)\]/', $this->sql, $VetorParametros);
    foreach($VetorParametros[0] as $Indice => $Parametro) { // PERCORRE TODOS OS PARÂMETROS DEFINIDOS NA CONSULTA
        $NomeParametro = $VetorParametros[1][$Indice];
        $TipoParametro = $VetorParametros[2][$Indice];

        if( ($VetorParametrosUsr === true) ||
            (in_array($NomeParametro, $VetorParametrosUsr)) ) {

            if(is_object($ValorParametros) && ($ValorParametros instanceof consulta)) {
                if(vazio($ValorParametros->campo($NomeParametro)))
                    $ValorParametro = NULL;
                else
                    switch($TipoParametro) {
                        case membrosql:
                        case texto:
                            $ValorParametro = $ValorParametros->campo($NomeParametro); break;
                        case data:
                            $ValorParametro = $ValorParametros->campo($NomeParametro, data); break;
                        case hora:
                            $ValorParametro = $ValorParametros->campo($NomeParametro, hora); break;
                        case datahora:
                            $ValorParametro = $ValorParametros->campo($NomeParametro, datahora); break;
                        case numero:
                            $ValorParametro = $ValorParametros->campo($NomeParametro, numero); break;
                        case moeda:
                            $ValorParametro = $ValorParametros->campo($NomeParametro, moeda); break;
                        case cpf:
                            $ValorParametro = $ValorParametros->campo($NomeParametro, cpf); break;
                        case cep:
                            $ValorParametro = $ValorParametros->campo($NomeParametro, cep); break;
                    }
            }
            elseif(is_array($ValorParametros)) {
                if(isset($ValorParametros[$NomeParametro]))
                    $ValorParametro = $ValorParametros[$NomeParametro];
                else
                    $ValorParametro = NULL;
            }
            else
                $ValorParametro = $ValorParametros;

            if($TipoParametro == membrosql) {
                $Aspa = NULL;
                $NovoValorParametro = $ValorParametro;
            }
            elseif((is_array($ValorParametro) && !empty($ValorParametro)) || (trim($ValorParametro) != "")) {
                switch($TipoParametro) {
                    case texto:
                        if(is_array($ValorParametro))
                            $NovoValorParametro = implode(",", array_map(array("Consulta", "formataTexto"), $ValorParametro));
                        else
                            $NovoValorParametro = self::formataTexto($ValorParametro);
                    break;
                    case data:
                        if(is_array($ValorParametro))
                            $NovoValorParametro = implode(",", array_map(array("Consulta", "formataValidaData"), $ValorParametro));
                        else
                            $NovoValorParametro = self::formataValidaData($ValorParametro);
                    break;
                    case hora:
                        if(is_array($ValorParametro))
                            $NovoValorParametro = implode(",", array_map(array("Consulta", "validaHora"), $ValorParametro));
                        else
                            $NovoValorParametro = self::validaHora($ValorParametro);
                    break;
                    case datahora:
                        if(is_array($ValorParametro))
                            $NovoValorParametro = implode(",", array_map(array("Consulta", "formataValidaDataHora"), $ValorParametro));
                        else
                            $NovoValorParametro = self::formataValidaDataHora($ValorParametro);
                    break;
                    case moeda:
                        if(is_array($ValorParametro))
                            $NovoValorParametro = implode(",", array_map(array("Consulta", "formataMoeda"), $ValorParametro));
                        else
                            $NovoValorParametro = self::formataMoeda($ValorParametro);
                    break;
                    case numero:
                        if(is_array($ValorParametro))
                            $NovoValorParametro = implode(",", array_map(array("Consulta", "formataNumero"), $ValorParametro));
                        else
                            $NovoValorParametro = self::formataNumero($ValorParametro);
                    break;
                    case cpf:
                        $NovoValorParametro = self::formataValidaCPF($ValorParametro);
                    break;
                    case email:
                        $NovoValorParametro = self::validaEMail($ValorParametro);
                    break;
                    case cep:
                        $NovoValorParametro = self::formataValidaCEP($ValorParametro);
                    break;
                    default:
                        throw new DBPHPException("Tipo de parâmetro inválido: ".$TipoParametro, TIPO_PARAMETRO_INVALIDO);
                }
                if($NovoValorParametro === false) {
                  $NovoValorParametro = 'null';
                  $VetorErros[$NomeParametro] = $ValorParametro;
                }
              }
              else {
                $NovoValorParametro = 'null';
              }

              $this->sql = preg_replace('/:'.$NomeParametro.'\['.$TipoParametro.'\]/', str_replace('$', '\$', $NovoValorParametro), $this->sql);
        }
    }
    return $VetorErros;
  }

  function executa($irprimeiralinha = false) {
      $retorno = $this->db->executaQuery($this->sql);
      if($retorno === false)
          throw new SQLException("Erro na consulta", $this->sql, pg_last_error(), 0);

      $this->recordset = $retorno;

      $this->ultimalinha = null;
      $this->linhaatual = null;
      if ($irprimeiralinha) {
        return $this->proximo();
      } else {
        $this->bof = true;
        $this->eof = false;
        return true;
      }
  }

  function proximo() {
    $this->campo = @pg_fetch_array($this->recordset, is_null($this->linhaatual) ? 0 : $this->linhaatual + 1);
    if($this->campo === false) {
        $this->bof = false;
        $this->eof = true;
        return false;
    }
    else {
        $this->bof = $this->eof = false;
        if(is_null($this->linhaatual))
          $this->linhaatual = 0;
        else
          $this->linhaatual++;
        return true;
    }
  }

  function anterior() {
    if (!is_null($this->linhaatual) && ($this->linhaatual > 0)) {
      $this->linhaatual--;
      $this->campo = pg_fetch_array($this->recordset, $this->linhaatual);
      $this->bof = $this->eof = false;
    }
    else {
        $this->bof = true;
        return false;
    }
  }

  function primeiro() {
    $this->campo = pg_fetch_array($this->recordset, 0);
    if($this->campo !== false) {
        $this->linhaatual=0;
        $this->bof = true;
        $this->eof = false;
        return true;
    }
    else {
        $this->bof = $this->eof = true;
        return false;
    }
  }

  function ultimo() {
    if (is_null($this->ultimalinha))
      $this->ultimalinha = (pg_num_rows($this->recordset) - 1);
    
    if($this->ultimalinha >= 0) {
        $this->campo = pg_fetch_array($this->recordset, $this->ultimalinha);
        $this->linhaatual = $this->ultimalinha;
        $this->bof = false;
        $this->eof = true;
        return true;
    }
    else {
        $this->bof = $this->eof = true;
        return false;
    }
  }

  function campo($nomecampo,$formatacao=null,$expressaoregular=null,$substituicao=null) {
    if(is_array($this->campo) && isset($this->campo[strtolower($nomecampo)]))
      $resultado = $this->campo[strtolower($nomecampo)];
    else {
      $resultado = NULL;
    }

    if(is_null($resultado))
        return null;

    switch ($formatacao) {
      case html:
        return formatatextohtml($resultado);
      case data:
        return self::formataTempo($resultado,data);
      case hora:
        return self::formataTempo($resultado,hora);
      case datahora:
        return self::formataTempo($resultado,datahora);
      case moeda:
        return number_format($resultado,2,',','.');
      case numero:
        return str_replace('.',',',$resultado);
      case cep:
        return preg_replace('/^([0-9]{5})([0-9]{3})$/','\1-\2',$resultado);
      case cpf:
        return preg_replace('/^([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})$/','\1.\2.\3-\4', str_pad($resultado, 11, "0", STR_PAD_LEFT));
      case expressaoregular:
        if ((trim($expressaoregular) == "") || (trim($substituicao) == "")) {
          throw new DBPHPException("Parâmetros insuficientes para o método campo", 0);
        }
        return preg_replace($expressaoregular,$substituicao,$resultado);
      default:
        return $resultado;
    }
  }

  function nrLinhas() {
    if(is_null($this->ultimalinha))
        $this->ultimalinha = (pg_num_rows($this->recordset) - 1);
    return $this->ultimalinha + 1;
  }
        
    function resultado() {
        return $this->campo;
    }

    public static function formataTexto($Texto, $Aspa=ASPA) {
      return $Aspa.str_replace("'", "\\'", $Texto).$Aspa;
  }

    public static function formataValidaCPF($CPF) {
      $Invalidos =  array("12345678909",
                          "11111111111",
                          "22222222222",
                          "33333333333",
                          "44444444444",
                          "55555555555",
                          "66666666666",
                          "77777777777",
                          "88888888888",
                          "99999999999",
                          "00000000000");
      if(preg_match('/^(\d\d\d)\.(\d\d\d)\.(\d\d\d)-(\d\d)$/', $CPF) == 0)
        return false;
      $CPFNum = preg_replace('/^(\d\d\d)\.(\d\d\d)\.(\d\d\d)-(\d\d)$/', '$1$2$3$4', $CPF);
      if(in_array($CPFNum, $Invalidos))
        return false;

      $acum=0;
      for($i = 0; $i < 9; $i++)
        $acum += $CPFNum[$i] * (10 - $i);

      $x = $acum % 11;
      $acum = ($x > 1) ? (11 - $x) : 0;
      if($acum != $CPFNum[9])
        return false;


      $acum=0;
      for ($i = 0; $i < 10; $i++)
        $acum += $CPFNum[$i] * (11 - $i);
      $x=$acum % 11;
      $acum = ($x > 1) ? (11 - $x) : 0;
      if($acum != $CPFNum[10])
        return false;

      return $CPFNum;
    }

    public static function formataValidaData($data, $Aspa=ASPA) {
        $regexpdata = '/^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/';
        preg_match($regexpdata,$data,$resultado);
        if (!empty($resultado)) {
            return $Aspa.preg_replace('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2,4})$/','\2/\1/\3',$data).$Aspa;
        } else {
            return false;
        }
    }

    public static function validaHora($hora, $Aspa=ASPA) {
        $regexphora='/^(([0]{0,1}[0-9]{1})|(1[0-9]{1})|(2[0-3]{1})):(([0]{0,1}[0-9]{1})|([0-5]{1}[0-9]{1}))$/';
        preg_match($regexphora,$hora,$resultado);
        if (!empty($resultado)) {
            return $Aspa.$hora.$Aspa;
        } else {
            return false;
        }
    }

    public static function formataValidaDataHora($datahora) {
        $datahora = explode(' ', $datahora);

        $data = self::formatavalidadata($datahora[0], NULL);
        if(isset($datahora[1]))
            $hora = self::validahora($datahora[1], NULL);
        else
            $hora = NULL;

        if(($data !== false) && ($hora !== false)) {
            if(is_null($hora))
                return ASPA.$data.ASPA;
            else
                return ASPA.$data." ".$hora.ASPA;
        }
        return false;
    }

  public static function formataTempo($data, $tipo) {
    $resultado = explode(' ',$data);
    $tempo = explode(':', $resultado[1]);
    $data = explode('-', $resultado[0]);

    $dia = $data[2];
    $mes = $data[1];
    $ano = $data[0];

    /*
    $dia=(strlen($resultado[1])==1?'0'.$resultado[1]:$resultado[1]);
    $mes=$resultado[0];
    $ano=$resultado[2];
    if ($resultado[5]=='PM') {
      $hora=($resultado[3]=='12'?'00':$resultado[3]+12);
    } else {
      $hora=(strlen($resultado[3])==1?'0'.$resultado[3]:$resultado[3]);
    }
    $minuto=(strlen($resultado[4])==1?'0'.$resultado[4]:$resultado[4]); */
    switch ($tipo) {
      case data:
        $resultado=$dia.'/'.$mes.'/'.$ano;
      break;
      case hora:
        $resultado=$tempo[0].":".$tempo[1];
      break;
      case datahora:
        $resultado=$dia.'/'.$mes.'/'.$ano.' '.$tempo[0].":".$tempo[1];
      break;
    }
    return $resultado;
  }

  public static function formataMoeda($moeda) {
    return $this->formataNumero(str_replace(',','.',(str_replace('.','',$moeda))));
  }

  public static function formataNumero($numero) {
      $regexpnumero = '/^([+-]{0,1}[0-9]{0,})[,]{0,1}([0-9]{0,})/';
      preg_match($regexpnumero,$numero,$resultado);

      if (isset($resultado[1])) {
        if (isset($resultado[2])) {
          return $resultado[1].'.'.$resultado[2];
        } else {
          return $resultado[1];
        }
      }
      elseif(isset($resultado[2])) {
        return '0.'.$resultado[2];
      }
      else return false;
  }

  public static function validaEMail($email) {
    $regexpemail = '/^[^.][a-zA-Z0-9_-]{1,}(\.[a-zA-Z0-9_-]{1,}){0,}[^.]@[^.][a-zA-Z0-9_-]{1,}(\.[a-zA-Z0-9_-]{1,}){1,}[^.]$/';
    preg_match($regexpemail,$email,$resultado);

    if (isset($resultado[0])) {
      return $email;
    }
    else {
      return false;
    }
  }

    public static function validaDado($Dado, $Tipo) {
        switch($Tipo) {
            case texto:
                return true;
            case numero:
            case moeda:
                return (self::formataNumero($Dado) !== false);
            case data:
                return (self::formataValidaData($Dado) !== false);
            case hora:
                return (self::validaHora($Dado) !== false);
            case datahora:
                return (self::formataValidaDataHora($Dado) !== false);
            case email:
                return (self::validaEMail($Dado) !== false);
            case cpf:
                return (self::formataValidaCPF($Dado) !== false);
            case cep:
                return (self::formataValidaCEP($Dado) !== false);
        }
        return false;
    }
}

class SQLException extends Exception {
  private $SQL, $MensagemErro;

  public function __construct($Message, $SQL, $MensagemErro, $Code) {
      $this->SQL = $SQL;
      $this->MensagemErro = $MensagemErro;
      parent::__construct($Message, $Code);
  }

  public function __toString() {
      return "Erro de SQL; Mensagem retornada: ".$this->MensagemErro.", consulta executada: ".$this->SQL." ".$this->MensagemErro.", traço: ".$this->getTraceAsString();
  }
}

class DBPHPException extends Exception {
  public function __toString() {
      return "Erro na classe DBPHP: ".$this->getMessage();
  }
}
?>