<?php

/**
 * Função debug  váriaveis
 *
 * @param mixed $var
 * @return void
 */
function printR($var){
    echo '<pre>';
        print_r($var);
    echo '</pre>';
}
/**
 * Formata para cpf ou cnpj 
 *
 * @param string $value
 * @param boolean $formatado
 * @return string
 */
function formatarCPF_CNPJ($value, $formatado = true){
    $codigoLimpo = preg_replace("[' '-./ t]",'',$value);
    $codigoLimpo = trim($codigoLimpo);
    $tamanho = (strlen($codigoLimpo) -2);
    if ($tamanho != 9 && $tamanho != 12){
        return false;
    }
    if ($formatado){
        $mascara = ($tamanho == 9) ? '###.###.###-##' : '##.###.###/####-##';
        $indice = -1;
        for ($i=0; $i < strlen($mascara); $i++) {
            if ($mascara[$i]=='#') $mascara[$i] = $codigoLimpo[++$indice];
        }
        $retorno = $mascara;
    }else{
        $retorno = $codigoLimpo;
    }
    return $retorno;
}
/**
 * Remove formatacao CPF / CNPJ 
 *
 * @param string $campo
 * @return string
 */
function limpaCPF_CNPJ($value){
    $codigoLimpo = preg_replace('#[^0-9]#','',$value);
    $codigoLimpo = trim($codigoLimpo);
    
    return $codigoLimpo;
}
/**
 * Converte data para formato pt-br
 *
 * @param string $data
 * @return string
 */
function dataConvBR($data){
    $datah = explode(" ",$data);
    $data = implode("/",array_reverse(explode("-",$datah[0])));
    if( count($datah) == 2 ) {
        $data = $data.' '.$datah[1];
    }
    return $data;
}
/**
 * Converte data string para formato EN
 *
 * @param string $data
 * @return string
 */
function dataConvEN($data){
    $datah = explode(" ",$data);
    $data = implode("-",array_reverse(explode("/",$datah[0])));
    if( count($datah) == 2 ) {
        $data = $data.' '.$datah[1];
    }
    return $data;
}
/**
 * Limpar string de caracteres especiais.
 *
 * @param string $string
 * @return string
 */
function limpaStr($string) {
    $string = preg_replace('/[áàãâä]/ui', 'a', $string);
    $string = preg_replace('/[éèêë]/ui', 'e', $string);
    $string = preg_replace('/[íìîï]/ui', 'i', $string);
    $string = preg_replace('/[óòõôö]/ui', 'o', $string);
    $string = preg_replace('/[úùûü]/ui', 'u', $string);
    $string = preg_replace('/[ç]/ui', 'c', $string);
    //echo $string;
    $string = preg_replace("/[][><}{)(:;,ºª!?*%~^`&#@]/", "", $string);
    $string = preg_replace("/ /", "_", $string);
    $string = strtolower($string);
    return $string;
}


function highlight($text, $terms) {
    return str_replace($terms, '<kbd>'.$terms.'</kbd>', $text);
}

/**
 * Tira acentos de letras na string.
 *
 * @param string $str
 * @return string
 */
function tirarAcento($str) {
    $str = preg_replace('/[áàãâä]/ui', 'a', $str);
    $str = preg_replace('/[éèêë]/ui', 'e', $str);
    $str = preg_replace('/[íìîï]/ui', 'i', $str);
    $str = preg_replace('/[óòõôö]/ui', 'o', $str);
    $str = preg_replace('/[úùûü]/ui', 'u', $str);
    $str = preg_replace('/[ç]/ui', 'c', $str);
    // $str = preg_replace('/[,(),;:|!"#$%&/=?~^><ªº-]/', '_', $str);
    //$str = preg_replace('/[^a-z0-9]/i', '_', $str);
    $str = preg_replace('/_+/', '_', $str); // ideia do Bacco :)
    return $str;
}
/**
 * Grava SysLogger no banco de dados
 *
 * @param string $msg
 * @param string $tela
 * @param integer $statusCode
 * @return void
 */
/*function SysLogger($msg,$tela=null,$statusCode=200){
    $data = date("m-Y");
    $hora = date("Y-m-d H:i:s");
    if( $_SESSION['app']['remoteIp'] == null ){
        if(isset ($_SERVER ['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
        }elseif(isset ($_SERVER ['HTTP_X_REAL_IP'])){
            $ip = $_SERVER ['HTTP_X_REAL_IP'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    }else {
        $ip = $_SESSION['app']['remoteIp'];
    }
    
    if( $tela != null ){
        $tela = 'origen: '.$tela.'; ';
    }else{
        $tela = $_SERVER['REQUEST_URI'];
    }
    
    if( $_SESSION['app']['user'] != null ){
        $user = $_SESSION['app']['user'];
        $sessao = $_SESSION['app']['sessao'];
        $userName = ' - '.$_SESSION['app']['login'];
    }
    
    //Nome do arquivo:
    if($statusCode == 200){
        $arquivo = __DIR__."/tmp/log_$data.txt";
    }else{
        $arquivo = __DIR__."/tmp/log_error_$data.txt";
    }

    if( is_array($msg) ){
        $msg = json_encode($msg);
        $msg = str_replace('","','", "',$msg);
    }
    
    //Texto a ser impresso no log:
    $texto = "Date: [$hora] | Ip: [$ip] | User: [$user $userName] | Source: [ $tela ] | Log: [ $msg ] \n";

    if( file_exists(__DIR__.'/tmp/') ){
        // Gravando log no arquivo
        $manipular = fopen($arquivo, "a+b");
        fwrite($manipular, $texto);
        fclose($manipular);
    }
    
    // Gravar no Banco de Dados
    if($statusCode == 200){
        $save = new \Modulos\System\Models\Log();
        $save->data = $hora;
        $save->sessao = $sessao;
        $save->ip = $ip;
        $save->tela = $tela;
        $save->user = $user;
        $save->detalhe = $texto;
        $save->save(); 
    }
}*/

/**
 * ##UTILIZAR SEMPRE PARA GRAVAR NO BANCO
 * Retorna saída no formado AAAA-MM-DD HH:mm:SS
 *
 * @param string $data
 * @return string
 */
function FDate ($data){ //
    
    if (!empty($data)) {
        $ano = 0;
        $mes = 0;
        $dia = 0;
        $hora = 0;
        $minuto = 0;
        $segundo = 0;
        $Time = "00:00:00";
        
        $DateParts= explode(" ",$data);
        $Date = $DateParts[0];
        if (isset($DateParts[1]))
            $Time = $DateParts[1];
            
            //formato brasileiro com hora!!!
            if ( @ereg ("([0-9]{1,2})[/|-]([0-9]{1,2})[/|-]([0-9]{4}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $Date." ".$Time, $sep)) {
                
                $dia = $sep[1];
                $mes = $sep[2];
                $ano = $sep[3];
                $hora = $sep[4];
                $minuto = $sep[5];
                $segundo = $sep[6];
            } else
                //formato americano com hora
                if ( @ereg ("([0-9]{4})[/|-]([0-9]{1,2})[/|-]([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $Date." ".$Time, $sep)) {
                    $dia = $sep[3];
                    $mes = $sep[2];
                    $ano = $sep[1];
                    $hora = $sep[4];
                    $minuto = $sep[5];
                    $segundo = $sep[6];
                } else
                    print "Invalid date format!!";
                    //$data = strtotime($ano."-".$mes."-".$dia." ".$hora.":".$minuto.":".$segundo);
                    $data = $ano."-".$mes."-".$dia." ".$hora.":".$minuto.":".$segundo;
                    return $data;
    } else
        return "0000-00-00 00:00:00";
        //...
}

/**
 * Faz a diferença entre datas retornando segundos 
 *
 * @param string $data1
 * @param string $data2
 * @return int
 */
function diff_em_segundos ($data1, $data2) {
    $data1 = FDate($data1);
    $data2 = FDate($data2);
    
    $s = strtotime($data2)-strtotime($data1);
    $secs = $s;
    
    $d = intval($s/86400);
    $s -= $d*86400;
    $h = intval($s/3600);
    $s -= $h*3600;
    $m = intval($s/60);
    $s -= $m*60;
    
    if(strlen($h) == 1){$h = "0".$h;}; //Coloca um zero antes
    if(strlen($m) == 1){$m = "0".$m;}; //Coloca um zero antes
    if(strlen($s) == 1){$s = "0".$s;}; //Coloca um zero antes
    
    $v = $d." dias ".$h.":".$m.":".$s;
    $min = $m;
    
    $dias = $d;
    
    $horas = $h;
    $minutos = $m;
    $segundos = $s;
    
    $dias *=86400; //Dia de 24 horas
    $horas *=3600;
    $minutos *=60;
    $segundos +=$dias+$horas+$minutos;
    
    $h = intval($segundos/3600);
    $m = intval($segundos/60);
    
    return $secs;
}

/**
 * Tratamento de Datas
 * @param string $data1
 * @param string $data2
 * @return string
 */
function date_difference($data1, $data2){
    if ($data1>$data2) {
        $temp = $data1;
        $data1 = $data2;
        $data2 = $temp;
    }
    $s = strtotime($data2)-strtotime($data1);
    $d = intval($s/86400);
    $s -= $d*86400;
    $h = intval($s/3600);
    $s -= $h*3600;
    $m = intval($s/60);
    $s -= $m*60;
    
    $v = $d." dias, ".$h.":".$m.":".$s;
    echo $v."<br>";
    return $v;
}

/**
 * Retorna diferença entre datas
 *
 * @param string $data1
 * @param string $data2
 * @return int
 */
function date_diff2($data1, $data2){
    $s = @strtotime($data2)-@strtotime($data1);
    $d = intval($s/86400);
    $s -= $d*86400;
    $h = intval($s/3600);
    $s -= $h*3600;
    $m = intval($s/60);
    $s -= $m*60;
    
    $v = $d;
    return $v;
}
/**
 *  Faz diferença entre datas retornando em dias
 *
 * @param string $data1
 * @param string $data2
 * @return int
 */
function dateDiffDias($data1, $data2){
    if (empty($data1) || empty($data2)){
        $v = "";
    } else {
        $s = @strtotime($data2)-@strtotime($data1);
        $d = intval($s/86400);
        $s -= $d*86400;
        $h = intval($s/3600);
        $s -= $h*3600;
        $m = intval($s/60);
        $s -= $m*60;
        
        $v = $d;
    }
    return (int)$v;
}

/**
 * Converte segundos para horas
 *
 * @param int $segundos
 * @return string
 */
function segundosEmHoras($segundos){
    $h=0;$m=0;
    while($segundos >=60){
        if ($segundos >= 3600) {
            while ($segundos >= 3600) { //ORDEM DE HORAS
                $segundos-=3600;
                $h+=1;
            }
        } else
            if ($segundos >= 60) {
                while ($segundos >= 60) {//ORDEM DE MINUTOS
                    $segundos-=60;
                    $m+=1;
                }
            }
    }
    if(strlen($h) == 1){$h = "0".$h;}; //Coloca um zero antes
    if(strlen($m) == 1){$m = "0".$m;}; //Coloca um zero antes
    if(strlen($segundos) == 1){$segundos = "0".$segundos;}; //Coloca um zero antes
    $horas = $h.":".$m.":".$segundos;
    return $horas;
}
/**
 * Converte segundo para horas completas
 *
 * @param [type] $input
 * @return void
 */
function getFullHour($input) {
    $seconds = intval($input); //Converte para inteiro
    
    $negative = $seconds < 0; //Verifica se é um valor negativo
    
    if ($negative) {
        $seconds = -$seconds; //Converte o negativo para positivo para poder fazer os calculos
    }
    
    $days = floor($seconds / 86400);
    $hours = floor(($seconds - ($days * 86400)) / 3600);
    $mins = floor(($seconds - (($days * 86400)+($hours * 3600))) / 60);
    $secs = floor($seconds % 60);
    
    $sign = $negative ? '-' : ''; //Adiciona o sinal de negativo se necessário
    
    return $sign . sprintf('%d %02d:%02d', $days, $hours, $mins);
}
/**
 * Valida código sql para consulta no banco.
 *
 * @param string $sql
 * @return boolean
 */
function validaCodigoSql($sql){
    $sql = strtoupper($sql);
    
    $regex = "/(alter table|insert|delete|update|drop table|show tables)/i";
    preg_match( $regex,$sql,$result);

    if($result){
        return false;
    }else{
        return true;
    }
}

/**
 * Retorna as iniciais das palavras.
 *
 * @param string $value
 * @return string
 */
function iniciaisNome($value){
    $inic = '';
    $var = explode(" ",$value);
    if(count($var) == 1){
        $var = explode(".",$value);
    }
    for ($i = 0; $i < count($var); $i++) {
        $inic .= substr($var[$i],0,1);
    }
    return $inic;
}

/**
 * Retorna data por extenso em portugues
 *
 * @param string|boolean $data - false pega data atual. 
 * @return void
 */
function dataExtenso($data=false){
    if ($data)	{
        $mes = date('m', strtotime($data));
    }else{
        $mes = date('m');
        $data = date('Y-m-d');
    }
    $meses = array(
        '01' => 'janeiro',
        '02' => 'fevereiro',
        '03' => 'março',
        '04' => 'abril',
        '05' => 'maio',
        '06' => 'junho',
        '07' => 'julho',
        '08' => 'agosto',
        '09' => 'setembro',
        '10' => 'outubro',
        '11' => 'novembro',
        '12' => 'dezembro'
    );
    return date('d', strtotime($data)) . ' de ' . $meses[$mes] . ' de ' . date('Y', strtotime($data));
}
/**
 * Retorna valor por extenso em portugues
 *
 * @param integer $valor
 * @param boolean $complemento
 * @return void
 */
function valorPorExtenso($valor=0, $complemento=true) {
	$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
	$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
 
	$c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
	$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
	$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezesete", "dezoito", "dezenove");
	$u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
 
	$z=0;
    $rt = null;
	$valor = number_format($valor, 2, ".", ".");
	$inteiro = explode(".", $valor);
	for($i=0;$i<count($inteiro);$i++)
		for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
			$inteiro[$i] = "0".$inteiro[$i];
 
	// $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;) 
	$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
	for ($i=0;$i<count($inteiro);$i++) {
		$valor = $inteiro[$i];
		$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
		$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
		$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
	
		$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
		$t = count($inteiro)-1-$i;
		if ($complemento == true) {
			$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
			if ($valor == "000")$z++; elseif ($z > 0) $z--;
			if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t]; 
		}
		if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
	} 
	return($rt ? $rt : "zero");
}

function geraDebugTxt($msg){
    if(isset ($_SERVER ['HTTP_X_FORWARDED_FOR'])){
        $ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
    }elseif(isset ($_SERVER ['HTTP_X_REAL_IP'])){
        $ip = $_SERVER ['HTTP_X_REAL_IP'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    //Nome do arquivo:
    $arquivo = "debug_".date('Y_m_d').".txt";
    if( is_array($msg) ){
        $msg = json_encode($msg);
        $msg = str_replace('","','", "',$msg);
    }
    
    //Texto a ser impresso no log:
    $texto = $ip." | ".date('d/m/Y H:i:s')." | $msg \n";
    
    if( file_exists('../../../base/tmp/') ){
        // Gravando log no arquivo
        $manipular = fopen('../../../base/tmp/'.$arquivo, "a+b");
        fwrite($manipular, $texto);
        fclose($manipular);
    }
}

/**
 * Verifica se string é formato JSON
 *
 * @param string $string
 * @param boolean $return_data
 * @return boolean
 */
function isJson($string,$return_data = false) {
    $data = @json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
}
/**
 * Retorna dia semana 
 *
 * @param int $day - numero do dia
 * @return void
 */
function diaSemana($day){
    $newDate = dataConvEN($day);
    list($y,$m,$t) = explode('-',$newDate);
    $day2  = getdate(mktime(0,0,0,$m,$t,$y));
    
    switch ($day2['wday']){
        case 0: 
            $dia_semana = "DOM";
            break;
        case 1: 
            $dia_semana = "SEG";
            break;
        case 2: 
            $dia_semana = "TER";
            break;
        case 3: 
            $dia_semana = "QUA";
            break;
        case 4: 
            $dia_semana = "QUI";
            break;
        case 5: 
            $dia_semana = "SEX";
            break;
        case 6:
            $dia_semana = "SAB";
            break;
    }
    return  $dia_semana;
}

/**
* Função para calcular o próximo dia útil de uma data
* Formato de entrada da $data: YYYY-MM-DD
*/
function proximoDiaUtil($data, $saida = 'd/m/Y') {
    $timestamp = strtotime($data);
    $dia = date('N', $timestamp);
    if ($dia >= 6) {
        $timestamp_final = $timestamp + ((8 - $dia) * 3600 * 24);
    } else {
        $timestamp_final = $timestamp;
    }
    return date($saida, $timestamp_final);
}

/**
 * Converte atributos para array
 *
 * @param array $attributes
 * @return string
 */
function attributesToArray($attributes = array()){
    $a = '';
    foreach ($attributes as $attribute => $value) {
        $a .= " $attribute='$value'";
    }
    return $a;
}
/**
 * Identifica Ad usuário
 *
 * @param string $cn
 * @return void
 */
function userAdIdentifica( $cn ){
    $tipo = array();
    $cn = explode(',', $cn);
    foreach ($cn as $val ){
        $val = explode('=', $val);
        $tipo[$val[0]][] = $val[1];
    }
    printR($tipo['OU']);
}


/**
 * Função para tratamento da Data para ser exibida no grid de mailer
 * @param string $data
 */
function dataMail($data = false){
    $data = explode(' ', $data);
    $hora = $data[1];
    $data = $data[0];
    if( $data == date('d/m/Y')){
        $hora = explode(':', $hora);
        return $hora[0].':'.$hora[1];
    }else{
        $data = dataConvEN($data);
        if ($data)	{
            $mes = date('m', strtotime($data));
        }else{
            $mes = date('m');
            $data = date('Y-m-d');
        }
        $meses = array(
            '01' => 'jan',
            '02' => 'fev',
            '03' => 'mar',
            '04' => 'abr',
            '05' => 'mai',
            '06' => 'jun',
            '07' => 'jul',
            '08' => 'ago',
            '09' => 'set',
            '10' => 'out',
            '11' => 'nov',
            '12' => 'dez'
        );
        if ( date('Y', strtotime($data)) != date('Y') ){
            return dataConvBR($data);
        }else{
            return date('d', strtotime($data)) . ' de ' . $meses[$mes];
        }
    }
}

/**
 * Converte Str para array
 *
 * @param [type] $str
 * @param [type] $sep1
 * @param [type] $sep2
 * @return array
 */
function str2array($str,$sep1,$sep2){
    $str = str_replace(' & ',' #ampersand# ',$str);
    $arr = explode($sep1, $str);
    
    $arrN = array();
    foreach($arr as $item){
        $valor = explode($sep2, $item);
        
        $arrN[$valor[0]] = str_replace(' #ampersand# ',' & ',$valor[1]);
    }
    return $arrN;
}
/**
 * Converte bytes em B, KB, MB, GB,TB,....
 *
 * @param Float $Bytes
 * @return void
 */
function bytesHumanSize(Float $Bytes){
    $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
    $base = 1024;
    $class = min((int)log($Bytes , $base) , count($si_prefix) - 1);
    return sprintf('%1.2f' , $Bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
}

/**
 * file_exists - Valida se arquivo existe no servidor
 * 
 * @param [string] $file
 */
/*function file_exists($file){
    return file_exists($file);
}*/

/**
 * zdebug - Visualizar dados em highlight em box fixado topo.
 *
 * @param [mixed] $data
 * @return void
 */
function zdebug($data){
    $high = '<pre id="zdebug" class="debug" style="width:500px;min-height:200px;position: fixed;top: 0; right: 0;z-index: 9999; overflow: scroll;max-height: 600px;">';
    $high .= '<button onclick="document.getElementById(\'zdebug\').style.display = \'none\'">Fechar</button>';
    $high .= highlight_string("<?php\n\n" . print_r( $data ,true) . "\n?>",true);
    $high .= '</pre>';
    echo $high;
}


function zdebugJson($data){
    $result = print_r($data,true);

    header( 'debug', true, 500 );
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'message'   => $result,
        'statusCode'=> 500
    ],JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Converter valores em class grid bootstrap
 *
 * @param mixed $value - valor com numero coluna  ou string com numero de coluna separado por espaco
 * Ex: 1  ou "2 3 4 5"
 * @return void
 */
function convertColGrid($value){
    // Gera coluna padrao tamnho sm
    if(is_numeric($value) or strlen($value) == 1){
        return 'col-xs-'.$value;
    }
    //Gerar colunas
    $grid = explode(' ',$value);
    $cols = '';
    if(isset($grid[0])) $cols .= 'col-xs-'.$grid[0];
    if(isset($grid[1])) $cols .= ' col-sm-'.$grid[1];
    if(isset($grid[2])) $cols .= ' col-md-'.$grid[2];
    if(isset($grid[3])) $cols .= ' col-lg-'.$grid[3];

    return $cols;
}

/**
 * Checa se esta o numero esta no formato Real R$ 
 *
 * @param string $valor
 * @return boolean
 */
function isFormatoReal($valor) {
    $valor = (string)$valor;
    $regra = "/^[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}$/";
    if(preg_match($regra,$valor)) {
        return true;
    } else {
        return false;
    }
}
/**
 * Converte valor Real para float 
 *
 * @param string $value
 * @return float
 */
function toDecimal($value){
    if(isFormatoReal($value)){
        $result = floatval(str_replace(',', '.', str_replace('.', '', $value)));
        return (float) $result;
    }else{
        return (float) $value;
    }
}

/**
 * Converte string data para formato do mysql  'Y-m-d' 
 *  ou retorna data se for  data normal
 *
 * @param string | date $value 
 * @return string
 */
function toDateStr($value,$format = 'Y-m-d'){
    if(is_string($value)){
        $value = str_replace('/', '-', $value);
        return date($format, strtotime($value));
    }else{
        return date_format($value, $format); 
    }
}

/**
 * Converte string data time para formato do mysql  'Y-m-d H:i:s' 
 *  ou retorna data se for  data normal
 *
 * @param string | date $value 
 * @return string
 */
function toDateTimeStr($value,$format = 'Y-m-d H:i:s'){
    if(is_string($value)){
        $value = str_replace('/', '-', $value);
        return date('Y-m-d H:i:s', strtotime($value));
    }else{
        return date_format($value, $format); 
    }
}

/**
 *  Converte string formato lista antiga '1=Tipo1,2=Tipo2' 
 *  para array lista 
 *
 * @param string
 * @return array - Array id|nome
 */
function strToListSelect($str){
    $opts = explode(',',$str);
    if(count($opts) == 0) return [];

    $values = array_map(function($opt){
        $value = explode('=',$opt);
        return [
            'id'=> $value[0],
            'nome'=> @$value[1]
        ];
    },$opts);

    return $values;
}

/**
 * Undocumented function
 *
 * @param [type] $str
 * @return void
 */
function strToHash($str){
    $opts = explode(',',$str);
    if(count($opts) == 0) return [];

    $values = [];
    foreach($opts as $opt){
		$value = explode('=',$opt);
        $values[$value[0]] = $value[1];
    }

    return $values;
}

/**
 * Retorna valor de array sem quebra de index.
 *
 * @param mixed $value
 * @return mixed $default=null
 */
function getParam(&$value,$default=null){
    return ( isset($value) ) ? $value : $default;
}

/**
 * Converte dashes para camelCase 
 * teste-site = testeSite
 * 
 * @param [string] $string
 * @param boolean $capitalizeFirstCharacter - seta primeiro upperCase
 * @return string
 */
function dashesToCamelCase($string, $capitalizeFirstCharacter = true) 
{
    $str = str_replace('-', '', ucwords($string, '-'));

    if (!$capitalizeFirstCharacter) {
        $str = lcfirst($str);
    }
    return $str;
}

/**
 * Debuga Query aloquent , juntando os bidings .
 *
 * @param [type] $query
 * @return void
 */
function debugBuilder($query){
    $result = str_replace(array('?'), array('\'%s\''),$query->toSql());
    $result = vsprintf( $result,$query->getBindings());
    var_dump($result);
    exit;
}

//--------------------------------------------------------------------------------
function moveUploadedFile($directory, \Slim\Http\UploadedFile $uploadedFile, $name=null){
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(12)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);
    if( $name ){
        $file = substr(limpaStr(pathinfo($uploadedFile->getClientFilename(), PATHINFO_FILENAME)),1,60);
        $filename = $name.$file.'.'.$extension;
    }        
    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
    
    return $filename;
}

/**
 *  Retorna caminha diretorio da class
 *
 * @param string $class - Nome class com namespace
 * @return string
 */
function getPathClass($class){
    $a = new \ReflectionClass($class);
    return dirname($a->getFileName());
}

/**
 *  Converte dados para tipo passado por paramentros.
 *
 * @param string $value
 * @param string $type
 *   INT  = 1, 
 *   STRING = 'teste'
 *   LIST = '1=ATIVO,2=INATIVO' para [ [ 'id'=>'1','nome'=>'ATIVO' ] ]
 *   ARRAY = 'ATIVO,INATIVO' para ['ATIVO','INATIVO']
 *   HASH =  '1=ATIVO,2=INATIVO' PARA [['1'=>'ATIVO','2'=>'INATIVO' ]]
 *   JSON = '{"OPCAO":[1,2]}' para object(stdClass)#1 (1) { ["OPCAO"]=> array(2) { [0]=> int(1) [1]=> int(2) } }
 * @return mixed
 */
function convertToType($value,$type){
    if($type){
        switch ($type) {
            case 'INT':
                return (int) $value;
                break;
            case 'STRING':
                return (string) $value;
                break;
            case 'LIST':
                return strToListSelect($value);
                break;
            case 'ARRAY':
                return explode(',',$value);
                break;
            case 'HASH':
                return strToHash($value);
                break;
            case 'JSON':
                return json_decode($value);
                break;
            default:
                return $type;
                break;
        }
    }
}

function win2unix($wtime) {
    return ($wtime * 0.0000001) - 11644473600;
}

function formmat_oz($oztime) {
    return substr($oztime, 0, 4) . "-" . substr($oztime, 4, 2) . "-" . substr($oztime, 6, 2) . " " . substr($oztime, 8, 2) . ":" . substr($oztime, 10, 2) . ":" . substr($oztime, 12, 2);
}

function win2unixFmt($wtime) {
    return date('Y-m-d H:i:s', bcsub(bcdiv($wtime, '10000000'), '11644473600'));
}

function generateSeriesTime($timeStart,$timeEnd,$intervalMinute = 30,$formatTime = 'H:i'){
    $dTimeStart = gettype($timeStart) == 'string' ? new \DateTime($timeStart) : clone $timeStart;
    $dTimeEnd   = gettype($timeEnd) == 'string' ? new \DateTime($timeEnd) : clone $timeEnd;

    $seriesTime = []; 
    do {
        array_push($seriesTime,$dTimeStart->format($formatTime));
        $dTimeStart->add(new \DateInterval('PT' . $intervalMinute . 'M'));
        
    } while ($dTimeStart <= $dTimeEnd);
    
    return $seriesTime;
}