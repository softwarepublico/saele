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

require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

$Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
$Eleicao = $Concurso->devolveEleicao($_GET['CodEleicao']);
$Urna = (isset($_GET['CodUrna']) ? $Eleicao->devolveUrna($_GET['CodUrna']) : NULL);

require_once('../fpdf.php');

class EleicaoPDF extends FPDF {
    private $Concurso, $Eleicao, $Urna;

    public function defineParametros(ConcursoEleitoral $Concurso, Eleicao $Eleicao, UrnaVirtual $Urna=NULL) {
        $this->Concurso = $Concurso;
        $this->Eleicao = $Eleicao;
        $this->Urna = $Urna;
    }

    function Header() {
        $this->SetFont('Arial','B', 12);
        $this->setX(0);
        $this->sety(15);
        $this->Cell(0, 6, 'SISTEMA DE ELEIÇÕES', 0, 1, 'C');
        $this->Cell(0, 6, 'Relatório de Votantes', 0, 1, 'C');

        $this->line(10,30,200,30);

        $this->sety(31);
        $this->SetFont('Arial','B', 9);
        $this->Cell(35, 4, $this->Concurso->retornaString(STR_CONCURSOELEITORAL).':', 0, 0, 'R');
        $this->SetFont('Arial','', 9);
        $this->MultiCell(0, 4, $this->Concurso->get("descricao"), 0, 'L');

        $this->SetFont('Arial','B', 9);
        $this->Cell(35, 4, $this->Concurso->retornaString(STR_ELEICAO).':', 0, 0, 'R');
        $this->SetFont('Arial','', 9);
        $this->MultiCell(0, 4, $this->Eleicao->get("descricao"), 0, 'L');

        if(!is_null($this->Urna)) {
            $this->SetFont('Arial','B', 9);
            $this->Cell(35, 4, 'Urna:', 0, 0, 'R');
            $this->SetFont('Arial','', 9);
            $this->MultiCell(0, 4, $this->Urna->get("descricao"), 0, 'L');
        }

       $this->line(10,$this->gety(),200,$this->gety());

       $this->SetY($this->GetY() + 5);
       $this->SetFont('Arial', 'B', 9);
       $this->Cell(0, 4, 'Lista de Votantes:', 'TLRB', 1, 'C');
       $this->Cell(30, 4, '  Cartão UFRGS', 'LB', 0);
       $this->Cell(160, 4, 'Nome', 'RB', 1);
       $this->SetFont('Arial', '', 9);
    }
}
$PDF = new EleicaoPDF('P','mm','A4');
$PDF->defineParametros($Concurso, $Eleicao, $Urna);
$PDF->AddPage();

if(isset($Urna))
  $Votantes = $Urna->devolveVotantes();
else
  $Votantes = $Eleicao->devolveEleitores(ELEITOR_JAVOTOU);

$Nr = count($Votantes);
$i = 1;
foreach($Votantes as $Eleitor) {
    $Pessoa = $Eleitor->getObj("PessoaEleicao");
    if($i == $Nr) $Border = 'B'; else $Border = '';
    $PDF->Cell(30, 4, '  '.$Pessoa->get("identificacaousuario"), 'LB', 0);
    $PDF->Cell(160, 4, $Pessoa->get("nomepessoa"), 'RB', 1);
    if($PDF->GetY() > 260)
        $PDF->addPage();
    $i++;
}

$SQL = "SELECT now() as agora ";
$ConsultaDataAtual = new consulta($SQL);
$ConsultaDataAtual->executa(true);

$PDF->SetY($PDF->GetY() + 10);
$PDF->SetFont('Arial', '', 9);
$PDF->Cell(0, 0, 'Impresso dia '.$ConsultaDataAtual->campo("agora", data)
                .' às '.$ConsultaDataAtual->campo("agora", hora), NULL, 0, 'L');

$PDF->Output('Relatorio.pdf','D');
exit();
?>