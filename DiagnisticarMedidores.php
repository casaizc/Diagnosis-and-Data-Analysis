<?php
include 'functions.php';
require_once 'PHPExcel/Classes/PHPExcel.php';
include 'database.php';

$LoraLogin = TRUE;
$SwAna_Login = FALSE;
// FALSE TRUE
/****AKC********/
$url = "13.58.193.254";


$pdo = Database::connect();

//connection con la base de datos de MySQL
if( $pdo === false )  {  
    echo "Could not connect. ";  
    die( print_r( sqlsrv_errors(), true));  
}

$archivo = "PlantillaSwAna.xlsx";

$inputFileType = PHPExcel_IOFactory::identify($archivo);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);

$objPHPExcel = $objReader->load($archivo);
$sheet = $objPHPExcel->getSheet(0); 

$highestRow = $sheet->getHighestRow(); 
$highestColumn = $sheet->getHighestColumn();

/************Tokens********/
$LoRa_token = "";
$SwAna_token = "";
/************Tokens********/

date_default_timezone_set('America/Bogota');

if ($LoraLogin) {
  $LoRa_token = loraLogin();
}
if ($SwAna_Login) {
  $SwAna_token = SwAna_Login($url);
}
$devices = 1;
echo "EUI; cantidad de tx;Ultimo dato;Mayor devolucion de contador;Fecha de devolucion;Ultimo nivel de Bateria;Menor nivel de Bateria;Fecha de Low Batt;TiempoLastTx Dias;TiempoLastTx Horas;ValorMuyGrande;Device Address;Analisis Bateria;Analisis RollOver;Analisis TX; Analisis Disminucion;Analisis aumento". "<BR>";

for ($fila = 2; $fila <= 830; $fila++){
  $NumDatos = 0;
  /**********************Lectura de datos desde excel*****************************/
  $eui = $sheet->getCell("A".$fila)->getValue();
  // Consultar BD por datos
  try{
    //Se busca si el contador existe o no y se selecciona el dato del contador que tiene almacenado
    $sql = "SELECT * FROM `$eui`";
    $query = $pdo->prepare($sql);
    $query->execute() or die('Problem executing query');
    
    $data = array();
    $battery = array();
    $date = array();
    $residenciaExist = 0;           //boolean para indicar si el contador existe o hay que crear un nuevo reg
    while($row = $query->fetch(PDO::FETCH_ASSOC))
    {
    array_push($data,$row['DATA_CONTADOR']);
    array_push($battery,$row['BATERIA']);
    array_push($date,$row['FECHA']);
    //echo $row['DATA_CONTADOR'] ." ; ". $row['BATERIA'] ." ; ". $row['FECHA'] . "<BR>";
    }

    $NumDatos = count($data);
    $dif = 0;
    $mayordevo = 0;
    $fechaDev = "null";
    for($x = 1; $x < $NumDatos; $x++) {
      $dif = $data[$x-1] - $data[$x];
        if ($dif > $mayordevo) {
          $mayordevo = $dif;
          $fechaDev = $date[$x-1];
        }
    }
    $lowstBatt = $battery[0];
    $fechaLowBatt = $date[0];
    for($x = 0; $x < $NumDatos; $x++) {
      $battery[$x];
        if ($lowstBatt > $battery[$x]) {
          $lowstBatt = $battery[$x];
          $fechaLowBatt = $date[$x];
        }
    }
    $BigCounter = 0;
    $lastSeenBD = $date[$NumDatos - 1];
    $lastBatt = $battery[$NumDatos - 1];
    $lastData = $data[$NumDatos - 1];
        
  }catch(PDOException $e){    
    $NumDatos = "null";
    $mayordevo = "null";
    $fechaDev = "null";
    $lowstBatt = "null";
    $fechaLowBatt = "null";
    $BigCounter = 0;
    $lastSeenBD = date('Y-m-d H:i:s');
    $BigCounter = "null";
    $fechaBigCounter = "null";
    $lastBatt = "null";
    $lastData = "null";
  }
  $activation = json_decode(Consultar_Activation($eui, $LoRa_token));
  if ($activation == null) {
    $devAddress = "null";
  } else {
    $devAddress = $activation -> deviceActivation -> devAddr;
  }  
  $dev_profile = json_decode(Consultar_devProfile($eui, $LoRa_token));

  if ($dev_profile == null) {
    $lastSeenServidor = $lastSeenBD;
  } else {
    $lastSeenServidor = date('Y-m-d H:i:s',strtotime($dev_profile -> lastSeenAt));
  }

  $fechaActual = date('Y-m-d H:i:s'); 
  $datetimeBD = date_create($lastSeenBD);
  $datetimeACtual = date_create($fechaActual);
  $datetimeServidor = date_create($lastSeenServidor);
  $TimeLsatTxBD = date_diff($datetimeBD, $datetimeACtual);
  $diffHorasTxBD =$TimeLsatTxBD->format('%h');
  $diffDiasTxBD = $TimeLsatTxBD->format('%a');


  $TimeDiffServidorBD = date_diff($datetimeServidor, $datetimeBD);
  $diffHoras =$TimeDiffServidorBD->format('%h');
  $diffDias = $TimeDiffServidorBD->format('%a');
  if ($diffDias) {
    $BigCounter = "ValorMuyGrande";
  } else {
    if ($diffHoras) {
      $BigCounter = "ValorMuyGrande";
    }else {
      $BigCounter = "OK";
    }
  }

  /**Realiza diferencia de tiempo actual con tiempo chirpstak
   * realizar diferencia last chirp y last bd para saber si cont gigante
   * Organizar datos en la salida
   * Logica de Diagnostico de medidor
   * 
   * **/

    //print_r($dev_profile);
  /*
  $devProfile = Consultar_devProfile($eui, $LoRa_token);
  print_r($devProfile);
  echo "<BR>" . "<BR>";
  */

  //echo $contador->format('%h:%i:%s') . "<BR>";
  //echo           "EUI   ; cantidad de tx  ;Ultimo dato      ;Mayor devolucion de contador;Fecha de devolucion;Ultimo nivel de Bateria;Menor nivel de Bateria;Fecha de Low Batt    ;       TiempoLastTx Dias           ;          TiempoLastTx Horas            ;  ValorMuyGrande   ;  Credenciales" . "<BR>";
  $diagnostico = $eui .  ";" . $NumDatos . ";" . $lastData . ";"      . $mayordevo .      ";" . $fechaDev .   ";"    . $lastBatt .    ";"   . $lowstBatt .   ";" . $fechaLowBatt . ";" . $TimeLsatTxBD->format('%a') . ";" . $TimeLsatTxBD->format('%h:%i:%s'). ";" . $BigCounter . ";"  . $devAddress. ";";
  echo $diagnostico;

  	/**
  * Analisis de comportamiento de medidor
    **/
  if ($NumDatos == "null") {
    $analisisDiagnostico = "Bateria null;RollOver null;Tx null;Contador null;Aumento null";
  } else {
    $analisisDiagnostico = "";
    // Nivel de batt
    if ($lastBatt < 3400) {
      if ((70 < $lastBatt) && ($lastBatt <= 100) ) { // para medidores Bove
        $analisisDiagnostico = $analisisDiagnostico . "Bateria OK".":".$lastBatt.";";
      } else {
        $analisisDiagnostico = $analisisDiagnostico . "Bateria".":".$lastBatt.";";
      }
    }else {
      $analisisDiagnostico = $analisisDiagnostico . "Bateria OK".":".$lastBatt.";";
    }
    // Big counter
    $analisisDiagnostico = $analisisDiagnostico . $BigCounter.":".$TimeDiffServidorBD->format('%a') . "_" . $TimeDiffServidorBD->format('%h-%i-%s').";";

    // No Tx
    if ($diffDiasTxBD) {
      $analisisDiagnostico = $analisisDiagnostico . "No TX".":".$TimeLsatTxBD->format('%a') . "_" . $TimeLsatTxBD->format('%h-%i-%s'). ";";
    } else {
      if ($diffHorasTxBD > 7) {
        $analisisDiagnostico = $analisisDiagnostico . "TX Itermitente".":".$TimeLsatTxBD->format('%a') . "_" . $TimeLsatTxBD->format('%h-%i-%s'). ";";
      }else {
        $analisisDiagnostico = $analisisDiagnostico . "TX OK".":".$TimeLsatTxBD->format('%a') . "_" . $TimeLsatTxBD->format('%h-%i-%s'). ";";
      }
    }
      
    // Reinicio de contador
    if ($mayordevo > 10) {
      $analisisDiagnostico = $analisisDiagnostico . "Disminucion de contador" .":".$mayordevo.";";
    }else {
      $analisisDiagnostico = $analisisDiagnostico . "Contador OK" .";";
    }
    
    // No aumenta consumo
      //Datos a analizar 10% del total de datos
      $cantidadDatos = round(($NumDatos/10), $precision = 0, $mode = PHP_ROUND_HALF_UP);
      $desviacion = 5;
      $indice = $NumDatos - 1;
      $resultPulsos = "No Aumenta pulsos";
      for ($i=0; $i < $cantidadDatos; $i++) { 
        $diferencia = $lastData - $data[$indice];
        if ($diferencia > 5) {
          $resultPulsos = "Aumento de pulsos OK";
          $i = $cantidadDatos;
        }
        $indice--;
      }
      $analisisDiagnostico = $analisisDiagnostico . $resultPulsos .";";

    
  }
    
  echo $analisisDiagnostico . "<BR>";

}


//var_dump ($contador);


?>