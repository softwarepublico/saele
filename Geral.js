var ArrayCentral=new Array();
var IE=(navigator.userAgent.toUpperCase().indexOf('MSIE')>-1);

function CentralClass(NomeDeExibicao,TipoInformacao,Obrigatorio) {
  this.NomeDeExibicao=NomeDeExibicao;
  this.TipoInformacao=TipoInformacao;
  this.Obrigatorio=Obrigatorio;
}

for (i=0;i<document.forms.length;i++) {
  if (document.forms[i].onsubmit) {
    alert('ERRO INTERNO: Utilize a funcao \'setfuncaovalidacaoadicional(Formulario,NomeFuncao)\' para adicionar validações ao form.');
  }
  document.forms[i].onsubmit=function () {return Validacao(this);}
}//SETA TODOS FORMS PARA A FUNCAO DE VALIDACAO

//*****TIPOS DE INFORMACAO*****
var colecao=0;
var texto=0;
var numero=1;
var numeroneg=2;
var inteiro=3;
var inteironeg=4;
var data=5;
var hora=6;
var moeda=7;
var email=8;
var cep=9;
var ip=10;
var arquivo=11;
var url=12;

//*****TIPOS DE INFORMACAO*****
function atributos(NomeCampo,NomeDeExibicao,Tipo,Obrigatorio) {
  var TipoInformacao=Tipo;
  if (NomeCampo.trim()!='') {
    var Objeto=obj(NomeCampo)[0];
    if (Obrigatorio) {
      setclass(NomeCampo,'obrigatorio');
    }
    eval('ArrayCentral[\''+NomeCampo+'\']=new CentralClass(\''+NomeDeExibicao+'\','+TipoInformacao+','+Obrigatorio+');');
    //-SETA FUNCOES NOS COMPONENTES APARTIR DO TIPO-
    switch (TipoInformacao) {
      case 1: //NUMERO
        if (IE) {
          Objeto.onkeypress=function () {return NumeroKeyPress(event,this);}
        } else {
          Objeto.onkeypress=function (event) {return NumeroKeyPress(event,this);}
        }
        Objeto.onblur=function () {NumeroBlur(this);}
        return true;
      break;
      case 2: //NUMERO NEGATIVO OU POSITIVO
        if (IE) {
          Objeto.onkeypress=function () {return NumeroNegKeyPress(event,this);}
        } else {
          Objeto.onkeypress=function (event) {return NumeroNegKeyPress(event,this);}
        }
        Objeto.onblur=function () {NumeroNegBlur(this);}
        return true;
      break;
      case 3: //INTEIRO
        if (IE) {
          Objeto.onkeypress=function () {return InteiroKeyPress(event);}
        } else {
          Objeto.onkeypress=function (event) {return InteiroKeyPress(event);}
        }
        Objeto.onblur=function () {InteiroBlur(this);}
        return true;
      break;
      case 4: //INTEIRO NEGATIVO OU POSITIVO
        if (IE) {
          Objeto.onkeypress=function () {return InteiroNegKeyPress(event);}
        } else {
          Objeto.onkeypress=function (event) {return InteiroNegKeyPress(event);}
        }
        Objeto.onblur=function () {InteiroNegBlur(this);}
        return true;
      break;
      case 5: //DATA
        if (IE) {
          Objeto.onkeypress=function () {return DataKeyPress(event,this);}
        } else {
          Objeto.onkeypress=function (event) {return DataKeyPress(event,this);}
        }
        Objeto.onblur=function () {return DataBlur(this);}
        return true;
      break;
      case 6: //HORA
        if (IE) {
          Objeto.onkeypress=function () {return HoraKeyPress(event,this);}
        } else {
          Objeto.onkeypress=function (event) {return HoraKeyPress(event,this);}
        }
        Objeto.onblur=function () {return HoraBlur(this);}
        return true;
      break;
      case 7: //MOEDA
        if (IE) {
          Objeto.onkeypress=function () {return MoedaKeyPress(event,this);}
        } else {
          Objeto.onkeypress=function (event) {return MoedaKeyPress(event,this);}
        }
        Objeto.onblur=function () {return MoedaBlur(this);}
        return true;
      break;
      case 8: //EMAIL
        Objeto.onblur=function () {EMailBlur(this);}
        return true;
      break;
      case 9: //CEP
        if (IE) {
          Objeto.onkeypress=function () {return CEPKeyPress(event,this);}
        } else {
          Objeto.onkeypress=function (event) {return CEPKeyPress(event,this);}
        }
        Objeto.onblur=function () {return CEPBlur(this);}
        return true;
      break;
      case 10: //IP
        Objeto.onblur=function () {IPBlur(this);}
        return true;
      break;
      case 11: //ARQUIVO
        Objeto.onblur=function () {ArquivoBlur(this);}
        return true;
      break;
      case 12: //URL
        Objeto.onblur=function () {URLBlur(this);}
        return true;
      break;
    }
    //FIM -SETA FUNCOES NOS COMPONENTES APARTIR DO TIPO-
  }
}













function NumeroKeyPress(Evento,Objeto) {
  var TeclaPressionada=(IE?Evento.keyCode:Evento.which);
  if (Objeto.value.indexOf(',')!=-1&&TeclaPressionada==44) {
    return false;
  }
  return ((TeclaPressionada>47&&TeclaPressionada<58)||(TeclaPressionada==0)||(TeclaPressionada==8)||(TeclaPressionada==44));
}

function NumeroNegKeyPress(Evento,Objeto) {
  var TeclaPressionada=(IE?Evento.keyCode:Evento.which);
  if (Objeto.value.indexOf(',')!=-1&&TeclaPressionada==44) {
    return false;
  }
  return ((TeclaPressionada>47&&TeclaPressionada<58)||(TeclaPressionada==0)||(TeclaPressionada==8)||(TeclaPressionada>42&&TeclaPressionada<46));
}

function InteiroKeyPress(Evento) {
  var TeclaPressionada=(IE?Evento.keyCode:Evento.which);
  return ((TeclaPressionada>47&&TeclaPressionada<58)||(TeclaPressionada==0)||(TeclaPressionada==8));
}

function InteiroNegKeyPress(Evento) {
  var TeclaPressionada=(IE?Evento.keyCode:Evento.which);
  return ((TeclaPressionada>47&&TeclaPressionada<58)||(TeclaPressionada==0)||(TeclaPressionada==8)||(TeclaPressionada==43)||(TeclaPressionada==45));
}

function DataKeyPress(Evento,Campo) {
  var TeclaPressionada=(IE?Evento.keyCode:Evento.which);
  var TamanhoTexto=Campo.value.length;
  if (TeclaPressionada==0) {
    return true;
  }
  if (
       (
         (
             (TeclaPressionada>47)
           &&(TeclaPressionada<58)
         )
         ||(TeclaPressionada==8)        
       )
       &&((Campo.value.lastIndexOf('/')+5-TamanhoTexto)>0)
     ) {
    if (
             ((Campo.value.lastIndexOf('/')+3-TamanhoTexto)>0)
          || (Campo.value.split('/').length>2)
       ) {
      return true;
    } else {  
      Campo.value=Campo.value+'/';
    }  
  } else {
    if (
         (
           (
               (TeclaPressionada==47)
             &&(Campo.value[TamanhoTexto-1]!='/')
           )
         )
         &&((Campo.value.lastIndexOf('/')+5-TamanhoTexto)>0)           
       ) {
      return true
    } else {
      return false;
    }  
  }
}

function HoraKeyPress(Evento,Campo) {
  var TeclaPressionada=(IE?Evento.keyCode:Evento.which);
  var TamanhoTexto=Campo.value.length;
  if (TeclaPressionada==0) {
    return true;
  }
  if (
       (
         (
             (TeclaPressionada>47)
           &&(TeclaPressionada<58)
         )
         ||(TeclaPressionada==8)
       )
       &&(
             ((Campo.value.lastIndexOf(':')+3-TamanhoTexto)>0)
           ||(Campo.value.lastIndexOf(':')==-1)
         )                   
     ) {
    if (
             ((Campo.value.lastIndexOf(':')+3-TamanhoTexto)>0)
          || (Campo.value.split(':').length>1)
       ) {
      return true;   
    } else {
      Campo.value=Campo.value+':';   
    }      
  } else {
    if (
         (TeclaPressionada==58)
         &&(Campo.value[TamanhoTexto-1]!=':')  
       ) {
      return true; 
    } else {
      return false;
    }
  }
}

function MoedaKeyPress(Evento,Campo) {
  var TeclaPressionada=(IE?Evento.keyCode:Evento.which);
  var ValorTeclaPressionada=String.fromCharCode(TeclaPressionada);
  var ValorCampo=Campo.value.replaceall(',','').replaceall('.','');

  if (TeclaPressionada>47&&TeclaPressionada<58) {
    Campo.value=ValorCampo+ValorTeclaPressionada;
    FormataMoeda(Campo);
  } else {
    if ((TeclaPressionada==0)||(TeclaPressionada==8)) {
      return true;
    }
  }
  return false;
}

function CEPKeyPress(Evento,Campo) {
  var TeclaPressionada=(IE?Evento.keyCode:Evento.which);
  var ValorTeclaPressionada=String.fromCharCode(TeclaPressionada);

  if ((Campo.value.length==9)&&(TeclaPressionada!=0)&&(TeclaPressionada!=8)) {
    return false;
  }

  if (((Campo.value.indexOf('-')!=-1)||(Campo.value.length<5))&&TeclaPressionada==45) {
    return false;
  }

  if ((Campo.value.length==5)&&(TeclaPressionada!=45)) {
    Campo.value=Campo.value+'-';
    return true;
  }

  return ((TeclaPressionada>47&&TeclaPressionada<58)||(TeclaPressionada==0)||(TeclaPressionada==8)||(TeclaPressionada==45));
}














function NumeroBlur(Campo) {
  if (!ValidaNumero(Campo.value)) {
    alert('O número digitado é inválido')
  }  
}

function NumeroNegBlur(Campo) {
  if (!ValidaNumeroNeg(Campo.value)) {
    alert('O número digitado é inválido')
  }  
}

function InteiroBlur(Campo) {
  if (!ValidaInteiro(Campo.value)) {
    alert('O número digitado é inválido')
  }  
}

function InteiroNegBlur(Campo) {
  if (!ValidaInteiroNeg(Campo.value)) {
    alert('O número digitado é inválido')
  }  
}

function DataBlur(Campo) {
  if (!ValidaData(Campo.value)) {
    alert('A data digitada é inválida');
  } 
  return true; 
}

function HoraBlur(Campo) {
  if (!ValidaHora(Campo.value)) {
    alert('A hora digitada é inválida');
  }
  return true;
}

function MoedaBlur(Campo) {
  if (!ValidaMoeda(Campo.value)) {
    alert('O valor digitado é inválido')
  }  
  return true;
}

function EMailBlur(Campo) {
  if (!ValidaEMail(Campo.value)) {
    alert('O EMail digitado é inválido.');
  }
  return true;
}

function CEPBlur(Campo) {
  if (!ValidaCEP(Campo.value)) {
    alert('O CEP digitado é inválido.');
  }
  return true;
}

function IPBlur(Campo) {
  if (!ValidaIP(Campo.value)) {
    alert('O IP digitado é inválido');
  }
  return true;
}

function ArquivoBlur(Campo) {
  if (!ValidaArquivo(Campo.value)) {
    alert('O caminho do arquivo digitado é inválido');
  }
  return true;
}

function URLBlur(Campo) {
  if (!ValidaURL(Campo.value)) {
    alert('A URL digitada é inválida');
  }
  return true;
}












//*****VALIDACAO*****
function ValidaNumero(Numero) {
  if (Numero.trim()!='') {
    var NumeroExp=/^[0-9]{1,}(\,[0-9]{1,}){0,1}$/
    return NumeroExp.test(Numero);
  } else {
    return true;
  }
}

function ValidaNumeroNeg(NumeroNeg) {
  if (NumeroNeg.trim()!='') {
    var NumeroExpNeg=/^[+-]{0,1}[0-9]{1,}(\,[0-9]{1,}){0,1}$/
    return NumeroExpNeg.test(NumeroNeg);
  } else {
    return true;
  }
}

function ValidaInteiro(Inteiro) {
  if (Inteiro.trim()!='') {
    var InteiroExp=/^[0-9]{0,}$/
    return InteiroExp.test(Inteiro);
  } else {
    return true;
  }
}

function ValidaInteiroNeg(InteiroNeg) {
  if (InteiroNeg.trim()!='') {
    var InteiroExpNeg=/^[+-]{0,1}[0-9]{0,}$/
    return InteiroExpNeg.test(InteiroNeg);
  } else {
    return true;
  }
}

function ValidaData(Data) {
  if (Data.trim()!='') {
    var DataExp=/^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/;
    return DataExp.test(Data);
  } else {
    return true;
  }
}

function ValidaHora(Hora) {
  if (Hora.trim()!='') {
    var HoraExp=/^(([0]{0,1}[0-9]{1})|(1[0-9]{1})|(2[0-3]{1})):(([0]{0,1}[0-9]{1})|([0-5]{1}[0-9]{1}))$/
    return HoraExp.test(Hora);
  } else {
    return true;
  }
}

function ValidaMoeda(Moeda) {
  if (Moeda.trim()!='') {
    var MoedaExp=/^[0-9]{1,3}([.]{1}[0-9]{3}){0,}\,[0-9]{2}$/
    return MoedaExp.test(Moeda);
  } else {
    return true;
  }
}

function ValidaEMail(EMail) {
  if (EMail.trim()!='') {
    var EMailExp=/^[^.][a-zA-Z0-9_-]{1,}(\.[a-zA-Z0-9_-]{1,}){0,}[^.]@[^.][a-zA-Z0-9_-]{1,}(\.[a-zA-Z0-9_-]{1,}){1,}[^.]$/
    return EMailExp.test(EMail);
  } else {
    return true;
  }
}

function ValidaCEP(CEP) {
  if (CEP.trim()!='') {
    var CEPExp=/^[0-9]{5}\-[0-9]{3}$/
    return CEPExp.test(CEP);
  } else {
    return true;
  }
}

function ValidaIP(IP) {
  if (IP.trim()!='') {
    var IPExp=/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/
    return IPExp.test(IP);
  } else {
    return true;
  }
}

function ValidaArquivo(Arquivo) {
//  if (Arquivo.trim()!='') {
//    var ArquivoExp=/^([a-z]):\\([^/:*?"<>|\r\n]*)\\([^\\/:*?"<>|\r\n]*)$/
//    return ArquivoExp.test(Arquivo);
//  } else {
    return true;
//  }
}
function ValidaURL(URL) {
//  if (URL.trim()!='') {
//    var URLExp=/^((?#protocol)https?|ftp)://((?#domain)[-A-Z0-9.]+)((?#file)/[-A-Z0-9+&@#/%=~_|!:,.;]*)?((?#parameters)\?[-A-Z0-9+&@#/%=~_|!:,.;]*)?$/
//    return URLExp.test(URL);
//  } else {
    return true;
//  }
}
//*****FIM VALIDACAO*****




















//*****FUNCOES GERAIS*****
String.prototype.trim = function() {
  var x=this;
  x=x.replace(/^\s*(.*)/, "$1");
  x=x.replace(/(.*?)\s*$/, "$1");
  return x;
}//TIRA ESPACOS INICIAIS E FINAIS

String.prototype.ltrim = function() {
  var x=this;
  x=x.replace(/^\s*(.*)/, "$1");
  return x;
}//TIRA ESPACOS INICIAIS

String.prototype.rtrim = function() {
  var x=this;
  x=x.replace(/(.*?)\s*$/, "$1");
  return x;
}//TIRA ESPACOS FINAIS

String.prototype.replaceall = function(Velho,Novo) {
  var x=this;
  var TamanhoString=x.length;
  var Resultado='';
  var Caractere='';

  for (i=0;i<TamanhoString;i++) {
    Caractere=(String.fromCharCode(x.charCodeAt(i))).replace(Velho,Novo)
    Resultado=Resultado+Caractere;
  }
  return Resultado;
}//FUNCAO QUER SUBSTITUI TODAS OCORRENCIAS DE Velho POR Novo

String.prototype.retirazerosdaesquerda = function() {
  var x=this;
  x=x.replace(/^[0]*(.*)/, "$1");
  return (x.trim()!=''?x:'0');
}//FUNCAO PARA RETIRAR ZEROS DA ESQUERDA

function voltar() {
  history.back();
}

function fechar(janela) {
  janela.close();
}

function visivel(IdObjeto) {
  return objid(IdObjeto).style.display=='block';
}

function alteravisibilidade(IdObjeto,Visibilidade) {
  if (Visibilidade) {
    objid(IdObjeto).style.display='block';
  } else {
    objid(IdObjeto).style.display='none';
  }
}

function array_search(){
//fazer a funcao p ver se um valor esta contido no vetor = PHP  
    
    
}



function invertevisibilidade(IdObjeto) {
  alteravisibilidade(IdObjeto,(!(visivel(IdObjeto))));
}

function FormataMoeda(Objeto) {
  var Valor=Objeto.value.retirazerosdaesquerda();
  var TamanhoValor=Valor.length;
  var ParteInteiraFinal='';
  var Centavos='';

  switch (TamanhoValor) {
   case 1 :
     Centavos='0'+Valor;
     ParteInteiraFinal='0';
   break;
   case 2 :
     Centavos=Valor;
     ParteInteiraFinal='0';
   break;
   default:
     var Centavo1=String.fromCharCode(Valor.charCodeAt(TamanhoValor-2));
     var Centavo2=String.fromCharCode(Valor.charCodeAt(TamanhoValor-1));
     Centavos=Centavo1+Centavo2;
     var ParteInteira=Valor.substring(0,(TamanhoValor-2));
     var TamanhoParteInteira=ParteInteira.length;
     if (TamanhoParteInteira>3) {
       var LugarPonto=TamanhoParteInteira%3;
       if (LugarPonto!=0) {
         ParteInteiraFinal=ParteInteira.substr(0,LugarPonto)+'.';
       }
       var TamanhoParteInteiraFinal=ParteInteiraFinal.replaceall('.','').length;
       while (TamanhoParteInteiraFinal<TamanhoParteInteira) {
         ParteInteiraFinal=ParteInteiraFinal+ParteInteira.substr(LugarPonto,3)+'.';
         TamanhoParteInteiraFinal=ParteInteiraFinal.replaceall('.','').length;
         LugarPonto=TamanhoParteInteiraFinal;
       }
       ParteInteiraFinal=ParteInteiraFinal.replace(/(.*?)[.]*$/, "$1");
     } else {
       ParteInteiraFinal=ParteInteira;
     }
   break;
  }
  Objeto.value=ParteInteiraFinal+','+Centavos;
}//FORMATACAO DE MOEDA NO ESTILO BR

function obj(Nome) {
  return document.getElementsByName(Nome);
}//RETORNA UM OBJETO EM QUALQUER BROWSER A PARTIR DO NOME

function objid(Id) {
  return document.getElementById(Id);
}//RETORNA UM OBJETO EM QUALQUER BROWSER A PARTIR DO ID

function DataBRToDate(Data) {
  var ArrayTemp=Data.split('/');
  return new Date(new Number(ArrayTemp[2]),new Number(ArrayTemp[1])-1,new Number(ArrayTemp[0]));
}

function DateToDataBR(Data) {
  return Data.getDate()+'/'+(Data.getMonth()+1)+'/'+Data.getFullYear();
}

function setclass(NomeCampo,NomeClass) {
  for (i=0;i<obj(NomeCampo).length;i++) {
    if (!obj(NomeCampo)[i].className) {
      obj(NomeCampo)[i].className=NomeClass;
    }
  }
}

function setclassrestante(Formulario,NomeClass) {
  for (i=0;i<Formulario.elements.length;i++) {
    if (!Formulario.elements[i].className) {
      Formulario.elements[i].className=NomeClass;
    }
  }
}

function limpaclass(NomeCampo) {
  for (i=0;i<obj(NomeCampo).length;i++) {
    if (obj(NomeCampo)[i].className) {
      obj(NomeCampo)[i].className='';
    }
  }
}

function cancelafuncaovalidacao(Formulario) {
  Formulario.onsubmit=function () {return true;}
  return true;
}

function setfuncaovalidacao(Formulario,Funcao) {
  cancelafuncaovalidacao(Formulario);
  Formulario.onsubmit=function () {return eval(Funcao);}
}

function setfuncaovalidacaoadicional(Formulario,NomeFuncao) {
  Formulario.onsubmit=function () {return ((Validacao(this))&&(eval(NomeFuncao)));}
}

function setfuncao(NomeObjeto,NomeMetodo,NomeFuncao) {
  eval('obj(\''+NomeObjeto+'\')[0].'+NomeMetodo+'=function () {return (eval('+NomeFuncao+'));}');
}

function setfuncaoadicional(NomeObjeto,NomeMetodo,NomeFuncao) {
  eval('ArrayCentral[\''+NomeMetodo+'\']=new Array();');
  eval('ArrayCentral[\''+NomeMetodo+'\'][\''+NomeObjeto+'\']=obj(\''+NomeObjeto+'\')[0].'+NomeMetodo+';');
  eval('obj(\''+NomeObjeto+'\')[0].'+NomeMetodo+'=function () {return ((ArrayCentral[\''+NomeMetodo+'\'][\''+NomeObjeto+'\'])&&('+NomeFuncao+'));}');
}

function funcoesnoseventos(Formulario) {
  var NomeObj;
  var TipoInformacao;
  var Obrigatorio;
  var NomeDeExibicao;

  for (i=0;i<Formulario.elements.length;i++) {
    NomeObj=Formulario.elements[i].name;
    TipoInformacao=eval(obj(NomeObj)[0].getAttribute('tipo').toLowerCase());
    if (obj(NomeObj)[0].getAttribute('obrigatorio')&&obj(NomeObj)[0].getAttribute('obrigatorio')!='false') {
      Obrigatorio=true;
    } else {
      Obrigatorio=false;
    }
    NomeDeExibicao=obj(NomeObj)[0].getAttribute('nomedeexibicao');
    atributos(NomeObj,NomeDeExibicao,TipoInformacao,Obrigatorio);
  }
}

//*****FIM FUNCOES GERAIS*****

























//****************************
//****************************
//****************************
function Validacao(Formulario) {
//  try {
    var NomeObj;
    var ValorObj='';
    var FuncaoTemporaria;
    var TipoInformacao=0;
    var Obrigatorio;
    var NomeDeExibicao;

    for (i=0;i<Formulario.elements.length;i++) {
      NomeObj=Formulario.elements[i].name;
      Formulario.elements[i].name
      if (NomeObj.trim()!='') {
        if (ArrayCentral[NomeObj]) {
          TipoInformacao=ArrayCentral[NomeObj].TipoInformacao;
          Obrigatorio=ArrayCentral[NomeObj].Obrigatorio;
          NomeDeExibicao=ArrayCentral[NomeObj].NomeDeExibicao
        } else {
          TipoInformacao=obj(NomeObj)[0].getAttribute('tipo');
          if (TipoInformacao) {
            TipoInformacao=eval(obj(NomeObj)[0].getAttribute('tipo').toLowerCase());
          } else {
            TipoInformacao=0;
          }
          if (obj(NomeObj)[0].getAttribute('obrigatorio')&&obj(NomeObj)[0].getAttribute('obrigatorio')!='false') {
            Obrigatorio=obj(NomeObj)[0].getAttribute('obrigatorio');
          } else {
            Obrigatorio=false;
          }
          NomeDeExibicao=obj(NomeObj)[0].getAttribute('nomedeexibicao');
        }


  // TESTA SE O OBJETO É DE CHECAR E ADAPTA A FUNCAO DE VALIDACAO
        if ((obj(NomeObj)[0].type=='checkbox')||(obj(NomeObj)[0].type=='radio')) {
          for (j=0;j<obj(NomeObj).length;j++) {
            if (obj(NomeObj)[j].checked) {
              ValorObj='1';
            }
          }
        } else {
          ValorObj=Formulario.elements[i].value;
        }
  // FIM DA ADAPTACAO
        switch (TipoInformacao) {
          case 0: //TEXTO OU COLECAO
            FuncaoTemporaria=function (Parametro) {return (Parametro.trim()!='');}
          break;
          case 1: //NUMERO
            FuncaoTemporaria=function (Parametro) {return ValidaNumero(Parametro);}
          break;
          case 2: //NUMERO NEGATIVO OU POSITIVO
            FuncaoTemporaria=function (Parametro) {return ValidaNumeroNeg(Parametro);}
          break;
          case 3: //INTEIRO
            FuncaoTemporaria=function (Parametro) {return ValidaInteiro(Parametro);}
          break;
          case 4: //INTEIRO NEGATIVO OU POSITIVO
            FuncaoTemporaria=function (Parametro) {return ValidaInteiroNeg(Parametro);}
          break;
          case 5: //DATA
            FuncaoTemporaria=function (Parametro) {return ValidaData(Parametro);}
          break;
          case 6: //HORA
            FuncaoTemporaria=function (Parametro) {return ValidaHora(Parametro);}
          break;
          case 7: //MOEDA
            FuncaoTemporaria=function (Parametro) {return ValidaMoeda(Parametro);}
          break;
          case 8: //EMAIL
            FuncaoTemporaria=function (Parametro) {return ValidaEMail(Parametro);}
          break;
          case 9: //CEP
            FuncaoTemporaria=function (Parametro) {return ValidaCEP(Parametro);}
          break;
          case 10: //IP
            FuncaoTemporaria=function (Parametro) {return ValidaIP(Parametro);}
          break;
          case 11: //ARQUIVO
            FuncaoTemporaria=function (Parametro) {return ValidaArquivo(Parametro);}
          break;
          case 12: //URL
            FuncaoTemporaria=function (Parametro) {return ValidaURL(Parametro);}
          break;
        }
        if ( (ValorObj!='')&&(!FuncaoTemporaria(ValorObj)) ) {
          alert('O campo '+NomeDeExibicao+' está inválido.');
          return false;
        }
        if ((Obrigatorio)&&(ValorObj=='')) {
          alert('O campo '+NomeDeExibicao+' precisa ser preenchido.');
          return false;
        }
      }
    }
    return true;
//  } catch(E) {
//    alert('Erro interno, comunique o analista responsável: '+E.message);
//    return false;
//  }
}

//****************************
//****************************
//****************************

if (navigator.userAgent.toUpperCase().indexOf('MSIE')!=-1) {
  var Versao=navigator.userAgent.split(';')[1].split(' ')[2].trim().split('.')[0];
  if (Versao<6) { // IE 6.0.2900
    alert('A versão do seu internet explorer está defasada.\nTalvez a página não seja exibida corretamente.')
  }
} else {
  if (navigator.userAgent.toUpperCase().indexOf('NETSCAPE')!=-1) {
    var Versao=navigator.userAgent.split('/')[navigator.userAgent.split('/').length-1].split(' ')[0].trim().split('.')[0];
    if (Versao<8) { // NETSCAPE 8.0.1
      alert('A versão do seu netscape está defasada.\nTalvez a página não seja exibida corretamente.')
    }
  } else {
    if (navigator.userAgent.toUpperCase().indexOf('FIREFOX')!=-1) {
      var Versao=navigator.userAgent.split('/')[navigator.userAgent.split('/').length-1].split(' ')[0].trim().split('.')[0];
      if (Versao<1) { // FIREFOX 1.0.4
        alert('A versão do seu firefox está defasada.\nTalvez a página não seja exibida corretamente.')
      }
    } else {
      if (navigator.userAgent.toUpperCase().indexOf('OPERA')!=-1) {
        var Versao=navigator.userAgent.split('/')[navigator.userAgent.split('/').length-1].split(' ')[0].trim().split('.')[0];
        if (Versao<7) { // OPERA 7.54
          alert('A versão do seu opera está defasada.\nTalvez a página não seja exibida corretamente.')
        }
      }
    }
  }
}