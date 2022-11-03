<?php
/****Datos Globales */
//$LoRa_token = "";

function loraLogin(){
    $LoRa_token ="";

    $Output = TRUE;

    $url = "http://107.6.54.113:8080/api/internal/login";
    
    $conexion = curl_init(); 
    $envio = "{\"password\":\"hoX85ZNSs@.k06\",\"username\":\"admin\"}"; // --- Puede ser un xml, un json, etc.
    
    curl_setopt($conexion, CURLOPT_URL,$url);
    // --- Datos que se van a enviar por POST.
    curl_setopt($conexion, CURLOPT_POSTFIELDS,$envio);
    // --- Cabecera incluyendo la longitud de los datos de envio.
    curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Content-Type: application/json', 'Content-Length: '.strlen($envio)));
    // --- Petición POST.
    curl_setopt($conexion, CURLOPT_POST, 1);
    // --- HTTPGET a false porque no se trata de una petición GET.
    curl_setopt($conexion, CURLOPT_HTTPGET, FALSE);
    // -- HEADER a false.
    curl_setopt($conexion, CURLOPT_HEADER, FALSE);
    curl_setopt( $conexion, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($conexion, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($conexion, CURLOPT_SSL_VERIFYHOST, FALSE);
    // --- Respuesta.
    $respuesta=curl_exec($conexion);

    
    $jsonvar = json_decode($respuesta) ;
    $LoRa_token = $jsonvar -> jwt;
    if ($Output) {
        echo "Response: " . $respuesta .  "<BR>";
        echo "LoRa token: " . $LoRa_token . "<BR>" . "<BR>";
    }
    curl_close($conexion);
    return $LoRa_token;
}

function SwAna_Login($urlSW){
    $Output = TRUE;

    $url_format = "http://%s:9500/api/login";
    $url = sprintf($url_format , $urlSW);
    
    $conexion = curl_init();
    $envio = "{\"email\":\"btp@admin.com\",\"password\":\"123456\"}"; // --- Puede ser un xml, un json, etc.
    curl_setopt($conexion, CURLOPT_URL,$url);
    // --- Datos que se van a enviar por POST.
    curl_setopt($conexion, CURLOPT_POSTFIELDS,$envio);
    // --- Cabecera incluyendo la longitud de los datos de envio.
    curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Content-Type: application/json', 'Content-Length: '.strlen($envio)));
    // --- Petición POST.
    curl_setopt($conexion, CURLOPT_POST, 1);
    // --- HTTPGET a false porque no se trata de una petición GET.
    curl_setopt($conexion, CURLOPT_HTTPGET, FALSE);
    // -- HEADER a false.
    curl_setopt($conexion, CURLOPT_HEADER, FALSE);
    curl_setopt( $conexion, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($conexion, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($conexion, CURLOPT_SSL_VERIFYHOST, FALSE);
    // --- Respuesta.
    $respuesta=curl_exec($conexion);
    
    $jsonvar = json_decode($respuesta) ;
    $SW_token = $jsonvar -> token;
    if ($Output) {
        echo "Response: " . $respuesta .  "<BR>";
        echo "Sw token: " . $SW_token . "<BR>" . "<BR>";
    }
    curl_close($conexion);
    return $SW_token;
    
    
}

function createDevice($name, $devEUI, $deviceProfileID, $LoRa_token, $applicationID){
    $Output = TRUE;

    //$applicationID = "24";
    $description = $name;
    $referenceAltitude = "0";
    $skipFCntCheck = "true";

    $url = "http://107.6.54.113:8080/api/devices";

        $conexion = curl_init();

    $envio_format = "{\"device\":{
                            \"applicationID\":\"%s\",
                            \"description\":\"%s\",
                            \"devEUI\":\"%s\",
                            \"deviceProfileID\":\"%s\",
                            \"name\":\"%s\",
                            \"referenceAltitude\":%s,
                            \"skipFCntCheck\":%s,
                            \"tags\":{},
                            \"variables\":{}}}";
    $envio = sprintf($envio_format, 
                            $applicationID,
                            $description,
                            $devEUI,
                            $deviceProfileID,
                            $name,
                            $referenceAltitude,
                            $skipFCntCheck);
     
    //echo $envio . "<BR>";

    curl_setopt($conexion, CURLOPT_URL,$url);

    curl_setopt($conexion, CURLOPT_POSTFIELDS,$envio);

    curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Content-Type: application/json', 'Accept: application/json', 'Grpc-Metadata-Authorization: Bearer '.$LoRa_token));

    curl_setopt($conexion, CURLOPT_POST, 1);
 
    // --- HTTPGET a false porque no se trata de una petición GET.
 
    curl_setopt($conexion, CURLOPT_HTTPGET, FALSE);
 
    // -- HEADER a false.
 
    curl_setopt($conexion, CURLOPT_HEADER, FALSE);

    curl_setopt( $conexion, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($conexion, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($conexion, CURLOPT_SSL_VERIFYHOST, FALSE);
 
 
    // --- Respuesta.
 
    $respuesta=curl_exec($conexion);
    if ($Output) {
        echo "Response: " . $respuesta .  "<BR>";
        echo "CreateLoRaServer : " . $devEUI . "<BR>" . "<BR>";
    }
 
    curl_close($conexion);
    
}

function Activation_ABP($devEUI, $devAddr, $LoRa_token) {
    $Output = TRUE;
    /*-----------ABP Keys-----------------------*/
    $fCntUp = 0;
    $aFCntDown = 0;
    $nFCntDown = 0;

    $appSKey = "f6a66e8fc525c6729d47c180e169af0e";
    $nwkSEncKey = "16f19a54be94b130b0e5a7bc5c84ea6b";
    $fNwkSIntKey = "16f19a54be94b130b0e5a7bc5c84ea6b";
    $sNwkSIntKey = "16f19a54be94b130b0e5a7bc5c84ea6b";

    $url_format = "http://107.6.54.113:8080/api/devices/%s/activate";
    $url = sprintf($url_format , $devEUI);

    $conexion = curl_init();
    $envio_format = "{\"deviceActivation\":{
                        \"aFCntDown\":%d,
                        \"appSKey\":\"%s\",
                        \"devAddr\":\"%s\",
                        \"devEUI\":\"%s\",
                        \"fCntUp\":%d,
                        \"fNwkSIntKey\":\"%s\",
                        \"nFCntDown\":%d,
                        \"nwkSEncKey\":\"%s\",
                        \"sNwkSIntKey\":\"%s\"}}"; 
    $envio = sprintf($envio_format, 
                                    $aFCntDown,
                                    $appSKey,
                                    $devAddr,
                                    $devEUI,
                                    $fCntUp, 
                                    $fNwkSIntKey, 
                                    $nFCntDown, 
                                    $nwkSEncKey, 
                                    $sNwkSIntKey);
    
    //echo $envio;
    
    curl_setopt($conexion, CURLOPT_URL,$url);
    // --- Datos que se van a enviar por POST.
    curl_setopt($conexion, CURLOPT_POSTFIELDS,$envio);
    // --- Cabecera incluyendo la longitud de los datos de envio.
    curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Content-Type: application/json', 'Content-Length: '.strlen($envio), 'Accept: application/json', 'Grpc-Metadata-Authorization: Bearer ' . $LoRa_token));
    // --- Petición POST.
    curl_setopt($conexion, CURLOPT_POST, 1);
    // --- HTTPGET a false porque no se trata de una petición GET.
    curl_setopt($conexion, CURLOPT_HTTPGET, FALSE);
    // -- HEADER a false.
    curl_setopt($conexion, CURLOPT_HEADER, FALSE);
    curl_setopt($conexion, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($conexion, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($conexion, CURLOPT_SSL_VERIFYHOST, FALSE);
    // --- Respuesta.
    $respuesta=curl_exec($conexion);
    if ($Output) {
        echo "Response: " . $respuesta .  "<BR>";
        echo "Activated by ABP : " . $devEUI . "<BR>" . "<BR>";
    }
    curl_close($conexion);
}

function Consultar_Activation($devEUI, $LoRa_token) {
    $Output = TRUE;
    
    $url_format = "http://107.6.54.113:8080/api/devices/%s/activation";
    $url = sprintf($url_format , $devEUI);

    //echo $url .  "<BR>";
    $conexion = curl_init();
    
    $envio_format = "{}"; 
    
    $envio = "";
    curl_setopt($conexion, CURLOPT_URL,$url);
    // --- Datos que se van a enviar por POST.
    curl_setopt($conexion, CURLOPT_POSTFIELDS,$envio);
    // --- Cabecera incluyendo la longitud de los datos de envio.
    curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Content-Type: application/json', 'Content-Length: '.strlen($envio), 'Accept: application/json', 'Grpc-Metadata-Authorization: Bearer ' . $LoRa_token));
    // --- Petición POST.
    curl_setopt($conexion, CURLOPT_POST, 0);
    // --- HTTPGET a false porque no se trata de una petición GET.
    curl_setopt($conexion, CURLOPT_HTTPGET, TRUE);
    // -- HEADER a false.
    curl_setopt($conexion, CURLOPT_HEADER, FALSE);
    curl_setopt($conexion, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($conexion, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($conexion, CURLOPT_SSL_VERIFYHOST, FALSE);
    // --- Respuesta.
    $respuesta=curl_exec($conexion);
    if ($Output) {
        //echo "Response: " . $devEUI .";". $respuesta .  "<BR>";
        //echo "Activated by ABP : " . $devEUI . "<BR>" . "<BR>";
    }
    $http_code = curl_getinfo($conexion, CURLINFO_HTTP_CODE);
    curl_close($conexion);

    $jsonvar = json_decode($respuesta) ;
    //print_r($jsonvar);
    if ($http_code == 200) {
        return $respuesta;
        $address = $jsonvar -> deviceActivation -> devAddr;
    } else {
        return $jsonvar -> error;
    }
}

function Activation_ABP_Extended($envio, $devEUI, $LoRa_token){
    $Output = TRUE;
    
    $url_format = "http://107.6.54.113:8080/api/devices/%s/activate";
    $url = sprintf($url_format , $devEUI);

    $conexion = curl_init();
        
    curl_setopt($conexion, CURLOPT_URL,$url);
    // --- Datos que se van a enviar por POST.
    curl_setopt($conexion, CURLOPT_POSTFIELDS,$envio);
    // --- Cabecera incluyendo la longitud de los datos de envio.
    curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Content-Type: application/json', 'Content-Length: '.strlen($envio), 'Accept: application/json', 'Grpc-Metadata-Authorization: Bearer ' . $LoRa_token));
    // --- Petición POST.
    curl_setopt($conexion, CURLOPT_POST, 1);
    // --- HTTPGET a false porque no se trata de una petición GET.
    curl_setopt($conexion, CURLOPT_HTTPGET, FALSE);
    // -- HEADER a false.
    curl_setopt($conexion, CURLOPT_HEADER, FALSE);
    curl_setopt($conexion, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($conexion, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($conexion, CURLOPT_SSL_VERIFYHOST, FALSE);
    // --- Respuesta.
    $respuesta=curl_exec($conexion);
    if ($Output) {
        echo "Response: " . $respuesta .  "<BR>";
        echo "Activated by ABP : " . $devEUI . "<BR>" . "<BR>";
    }
    curl_close($conexion);
}

function Consultar_devProfile($eui, $LoRa_token){
    $url_format = "http://107.6.54.113:8080/api/devices/%s";
    $url = sprintf($url_format , $eui);
    
    $conexion = curl_init();
    $envio = "";
    curl_setopt($conexion, CURLOPT_URL,$url);
            // --- Datos que se van a enviar por POST.
    curl_setopt($conexion, CURLOPT_POSTFIELDS,$envio);
            // --- Cabecera incluyendo la longitud de los datos de envio.
    curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Accept: application/json', 'Content-Length: '.strlen($envio), 'Grpc-Metadata-Authorization: Bearer ' . $LoRa_token));
            // --- Petición POST.
    // curl_setopt($conexion, CURLOPT_CUSTOMREQUEST, "DELETE");
    // --- HTTPGET a false porque no se trata de una petición GET.
    curl_setopt($conexion, CURLOPT_HTTPGET, TRUE);
    // -- HEADER a false.
    curl_setopt($conexion, CURLOPT_HEADER, FALSE);
    curl_setopt( $conexion, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($conexion, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($conexion, CURLOPT_SSL_VERIFYHOST, FALSE);
    // --- Respuesta.
    $respuesta=curl_exec($conexion);
    $jsonvar = json_decode($respuesta);
    try {
        $name_format = "lora_node_%s";
        $SWname = sprintf($name_format , $eui);
        $jsonvar -> device -> name = $SWname;
    } catch (\Throwable $th) {
        $jsonvar = null;
    }
    
    $respuesta = json_encode($jsonvar);

    curl_close($conexion);
    return $respuesta;
}

function Update_devProfile($envio2, $eui, $LoRa_token){
    $Output = TRUE;

    $url_format = "http://107.6.54.113:8080/api/devices/%s";
    $url = sprintf($url_format , $eui);
    
    $conexion = curl_init();
    curl_setopt($conexion, CURLOPT_URL,$url);
            // --- Datos que se van a enviar por POST.
    curl_setopt($conexion, CURLOPT_POSTFIELDS,$envio2);
            // --- Cabecera incluyendo la longitud de los datos de envio.
    curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Accept: application/json', 'Content-Length: '.strlen($envio2), 'Grpc-Metadata-Authorization: Bearer ' . $LoRa_token));
            // --- Petición POST.
    curl_setopt($conexion, CURLOPT_CUSTOMREQUEST, "PUT");
            // --- HTTPGET a false porque no se trata de una petición GET.
    curl_setopt($conexion, CURLOPT_HTTPGET, FALSE);
            // -- HEADER a false.
    curl_setopt($conexion, CURLOPT_HEADER, FALSE);
    curl_setopt( $conexion, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($conexion, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($conexion, CURLOPT_SSL_VERIFYHOST, FALSE);
        
        // --- Respuesta.
    $respuesta=curl_exec($conexion);

    if ($Output) {
        echo "Response: " . $respuesta .  "<BR>";
        echo "Device profile Updated : " . $eui . "<BR>" . "<BR>";
    }
    
    curl_close($conexion);
    
}

function CreateSwAna($urlSW, $envio, $eui, $SwAna_token){
    $Output = TRUE;

    $url_format = "http://%s:9500/api/devices";
    $url = sprintf($url_format , $urlSW);

    $conexion = curl_init();
    
    curl_setopt($conexion, CURLOPT_URL,$url);
    curl_setopt($conexion, CURLOPT_POSTFIELDS,$envio);
    //curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Accept: application/json', 'Content-Length: '.strlen($envio), 'Grpc-Metadata-Authorization: Bearer ' . $LoRa_token));
    curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Content-Type: application/json;charset=UTF-8','authorization: Token '.$SwAna_token));
    curl_setopt($conexion, CURLOPT_POST, 1);
    // --- HTTPGET a false porque no se trata de una petición GET.
    curl_setopt($conexion, CURLOPT_HTTPGET, FALSE);
    // -- HEADER a false.
    curl_setopt($conexion, CURLOPT_HEADER, FALSE);
    curl_setopt( $conexion, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($conexion, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($conexion, CURLOPT_SSL_VERIFYHOST, FALSE);
    // --- Respuesta.
    $respuesta=curl_exec($conexion);
    if ($Output) {
        echo "Response: " . $respuesta .  "<BR>";
        echo "Device created SwAna : " . $eui . "<BR>" . "<BR>";
    }
    curl_close($conexion);
}

function DeleteLora($eui, $LoRa_token){
    $Output = TRUE;
    $url_format = "http://107.6.54.113:8080/api/devices/%s";
    $url = sprintf($url_format , $eui);
    
    $conexion = curl_init();
    $envio = "";
    
    curl_setopt($conexion, CURLOPT_URL,$url);
        // --- Datos que se van a enviar por POST.
    curl_setopt($conexion, CURLOPT_POSTFIELDS,$envio);
        // --- Cabecera incluyendo la longitud de los datos de envio.
    curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Accept: application/json', 'Content-Length: '.strlen($envio), 'Grpc-Metadata-Authorization: Bearer ' . $LoRa_token));
        // --- Petición POST.
    curl_setopt($conexion, CURLOPT_CUSTOMREQUEST, "DELETE");
        // --- HTTPGET a false porque no se trata de una petición GET.
    curl_setopt($conexion, CURLOPT_HTTPGET, FALSE);
        // -- HEADER a false.
    curl_setopt($conexion, CURLOPT_HEADER, FALSE);
    curl_setopt( $conexion, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($conexion, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($conexion, CURLOPT_SSL_VERIFYHOST, FALSE);
    
        // --- Respuesta.
    $respuesta=curl_exec($conexion);

    if ($Output) {
        echo "Response: " . $respuesta .  "<BR>";
        echo "Device Deleted : " . $eui . "<BR>" . "<BR>";
    }   

    curl_close($conexion);
}
?>