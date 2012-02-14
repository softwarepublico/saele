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

class Iterador implements Iterator, Countable {
  private $NotEOF = false;
  private $TemRegistro;
  private $Consulta = NULL;
  private $NomeClasse = NULL;
  private $Objeto = NULL;
  private $VarsExtra = NULL;

  public function __construct($NomeClasse, $SQLAdicional=NULL, $ParametrosAdicionais=array(), $VarsExtra=NULL) {
    $this->NomeClasse = $NomeClasse;
    $dummy = new $NomeClasse("dummy");
    $Consulta = $dummy->devolveConsultaIterador();
    $Consulta->addSQL(" ".$SQLAdicional);
    $Consulta->setParametros(todos, $ParametrosAdicionais);

    $this->Consulta = $Consulta;
    $this->VarsExtra = $VarsExtra;
    $this->NotEOF = ($this->Consulta->executa(true));
    if($this->NotEOF) {
      $this->TemRegistro = true;
      $this->Objeto = new $this->NomeClasse($this->Consulta, $this->VarsExtra);
    }
    else
      $this->TemRegistro = false;
  }
  
  function proximo() {
    $Objeto = $this->Objeto;
    $this->next();
    return $Objeto;
  }
  
  function devolveVetor() {
    $VetorRetorno = array();
    if($this->NotEOF) {
      do {
        $VetorRetorno[$this->Objeto->getChave()] = $this->Objeto->getAll();
        $this->next();
      } while($this->NotEOF);
      $this->rewind();
    }
    return $VetorRetorno;
  }
  
  function temRegistro() {
    return $this->TemRegistro;
  }
  
  function rewind() {
    if($this->TemRegistro) {
      $this->NotEOF = $this->Consulta->primeiro();
      $this->Objeto = new $this->NomeClasse($this->Consulta, $this->VarsExtra);
    }
  }
  
  function current() {
    return $this->Objeto;
  }
  
  function key() {
    if(is_null($this->Objeto))
      return 0;
    
    return $this->Objeto->getChave();
  }
  
  function next() {
    $this->NotEOF = ($this->Consulta->proximo());
    if($this->NotEOF)
      $this->Objeto = new $this->NomeClasse($this->Consulta, $this->VarsExtra);
  }
  
  function valid() {
    return $this->NotEOF;
  }
  
  function count() {
    return $this->Consulta->nrlinhas();
  }
}