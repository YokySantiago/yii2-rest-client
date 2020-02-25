<?php

namespace yokysantiago\ms\rest\client;

class RESTServices 
{

    /**
     * @var $strURL 
     * Indica la URL a la cual se realizará el consumo del servicio web
     */
    private $strURL;

    /**
     * @var $boolRetornaInformacion
     * Indica si el servicio que se consumirá retorna información
     */
    private $boolRetornaInformacion = 0;

    /**
     * @var $boolPost 
     * Indica si se enviará información en el servicio que se consumirá
     */
    private $boolPost = 0;

    /**
     * @var $boolVerificarHostSSL
     * Indica si se verificará que el host cumpla con certificado SSL
     */
    private $boolVerificarHostSSL = 0;

    /**
     * @var $boolVerificarPeerSSL
     * Indica si se verificará que el peer cumpla con certificado SSL
     */
    private $boolVerificarPeerSSL = 0;

    /**
     * @var $arrCabeceras 
     * Variable en donde se encuentran las cabeceras de la petición
     */
    private $arrCabeceras = array();

    /**
     * @var $logFunction
     * Indica si se tiene acceso a la funcion genérica de log_mensaje de adminfo
     */
    private $logFunction  = false;

    /**
     * @var $curl
     * Variable en donde se encuentra la configuración realizada al CURL
     */
    private $curl;

    /**
     * @var $multiCurl
     * Variable en donde se encuentra la configuración realizada al CURL
     */
    private $multiCurl;

    /**
     * @var $multiCurl
     * Variable que indica si la petición que se hará al WS tiene varios hilos para enviar
     */
    private $esMultiCurl = null;

    /**
     * @var $httpCode
     * Variable que obtiene el código resultante del llamado WS
     */
    private $httpCode = null;

    /**
     * Inicialización de cla clase
     * 
     * @param $strURL Url a la cual se consultará el servicio web
     * @param $boolVerificarHostSSL verificación SSL en el Host
     * @param $boolVerificarPeerSSL verificación SSL en el Host
     * @param $esMultiCurl bool identificación de envio múltiple
     * 
     * @author Santiago Botero Ruiz <sbotero@solati.com.co> 
     */
    public function inicializar($strURL, $boolVerificarHostSSL = 0, $boolVerificarPeerSSL = 0, $esMultiCurl = 0) {
        $this->strURL = $strURL;
        $this->boolVerificarHostSSL = $boolVerificarHostSSL;
        $this->boolVerificarPeerSSL = $boolVerificarPeerSSL;
        $this->esMultiCurl = $esMultiCurl;
        $this->logFunction = function_exists('log_mensaje');
        
        if( $this->esMultiCurl ) {
            $this->multiCurl = curl_multi_init();
            $this->curl = array();
        } else {
            $this->curl = curl_init();
            $this->multiCurl = null;
        }

    }

    /**
     * Método que configura una variable en el CURL inicializado
     * 
     * @param $variable Variable de CURL que debe ser parametrizada
     * @param $valor El valor de la variable de curl dada
     * 
     * @author Santiago Botero Ruiz <sbotero@solati.com.co>
     */
    public function setearVariable( $variable, $valor ) 
    {
        curl_setopt($this->curl, $variable, $valor );
    }

    /**
     * Método que configura las cabeceras del consumo del webservice
     * 
     * @param $arrCabeceras Cabeceras que se configuraran en la petición del web service
     * 
     * @author Santiago Botero Ruiz <sbotero@solati.com.co>
     */
    public function setearCabeceras ( $arrCabeceras = array() ) 
    {
        if( is_array($arrCabeceras) && !empty($arrCabeceras) ) {
            $this->arrCabeceras = $arrCabeceras;
        } else if( $this->logFunction ) {
            log_mensaje('info', 'RESTServices:: La cabecera de la petición al WS se encuentra vacia');
        }
    }

    /**
     * Método que realiza el llamado de un servicio web mediante POST
     * 
     * @param $datos Datos enviados en la petición POST
     * 
     * @author Santiago Botero Ruiz <sbotero@solati.com.co>
     */
    public function obtenerInformacionPOST( $datos = array() )
    {
        $this->boolPost = 1;
        $this->boolRetornaInformacion = 1;

        if( $this->esMultiCurl ) {
            $curl = array();
            foreach ($datos as $key => $value) {

                $this->curl = curl_init();

                $this->setearVariable(CURLOPT_URL, $strURL);
                $this->setearVariable(CURLOPT_SSL_VERIFYHOST, $this->boolVerificarHostSSL);
                $this->setearVariable(CURLOPT_SSL_VERIFYPEER, $this->boolVerificarPeerSSL);
                $this->setearVariable(CURLOPT_POST, $this->boolPost);
                $this->setearVariable(CURLOPT_RETURNTRANSFER, $this->boolRetornaInformacion);
                
                if ( !empty($datos) ) {
                    $this->setearVariable(CURLOPT_POSTFIELDS, $datos);
                }

                if( isset($this->arrCabeceras) ) {
                    $this->setearVariable(CURLOPT_HTTPHEADER, $this->arrCabeceras);
                }

                curl_multi_add_handle($this->multiCurl, $this->curl);
                array_push($curl, $this->curl);
            }

            $respuesta = $this->ejecutarLlamado($curl);
        } else {
            $this->setearVariable(CURLOPT_POST, $this->boolPost);
            $this->setearVariable(CURLOPT_RETURNTRANSFER, $this->boolRetornaInformacion);

            if ( !empty($datos) ) {
                $this->setearVariable(CURLOPT_POSTFIELDS, json_encode($datos));
            }
    
            if ( $this->logFunction ) {
                log_mensaje('debug', 'RESTServices:: Llamado de webservice POST');
                log_mensaje('debug', 'WS CALL    : ' . $this->strURL);
                log_mensaje('debug', 'WS Data    : ' . json_encode($datos));
            }
            
            $respuesta = $this->ejecutarLlamado();
        }

        return $respuesta;
    }

    /**
     * Método que realiza el llamado de un servicio web mediante GET
     * 
     * @param $datos Datos enviados en la petición GET
     * 
     * @author Santiago Botero Ruiz <sbotero@solati.com.co>
     */
    public function obtenerInformacionGET( $datos = array() )
    {
        $this->boolPost = 0;
        $this->boolRetornaInformacion = 1;

        if( $this->esMultiCurl ) {
            $curl = array();
            foreach ($datos as $key => $value) {

                $this->curl = curl_init();
                $strURL = $this->strURL . '?' . http_build_query($value);
                $this->setearVariable(CURLOPT_URL, $strURL);
                $this->setearVariable(CURLOPT_SSL_VERIFYHOST, $this->boolVerificarHostSSL);
                $this->setearVariable(CURLOPT_SSL_VERIFYPEER, $this->boolVerificarPeerSSL);
                $this->setearVariable(CURLOPT_POST, $this->boolPost);
                $this->setearVariable(CURLOPT_RETURNTRANSFER, $this->boolRetornaInformacion);

                if( isset($this->arrCabeceras) ) {
                    $this->setearVariable(CURLOPT_HTTPHEADER, $this->arrCabeceras);
                }

                curl_multi_add_handle($this->multiCurl, $this->curl);
                array_push($curl, $this->curl);
            }

            $respuesta = $this->ejecutarLlamado($curl);
        } else {
            $this->setearVariable(CURLOPT_POST, $this->boolPost);
            $this->setearVariable(CURLOPT_RETURNTRANSFER, $this->boolRetornaInformacion);

            if ( !empty($datos) ) {
                $this->strURL =  $this->strURL . '?' . http_build_query($datos);
            }
    
            if ( $this->logFunction ) {
                log_mensaje('debug', 'RESTServices:: Llamado de webservice GET');
                log_mensaje('debug', 'WS CALL    : ' . $this->strURL);
                log_mensaje('debug', 'WS Data    : ' . $datos);
            }
            
            $respuesta = $this->ejecutarLlamado();
        }

        return $respuesta;
    }

    /**
     * Método que ejecuta el llamado del WEB SERVICE
     * 
     * @param $curl es la configuración de la ejecución de los servicios web cuando se da por múltiples envíos
     * 
     * @author Santiago Botero Ruiz <sbotero@solati.com.co>
     */
    private function ejecutarLlamado($curl = null)
    {

        $respuesta = null;
        if ( $this->esMultiCurl && !empty($curl) ) {
            $iEjecutando    = null;
            $resultadoRaw   = array();
            $resultadoFinal = array();

            /* La variable $iEjecutando nos indica el número de llamados a web
             * services que aún estan pendientes por ejecutar (los envios se
             * envian en paralelo).*/
            do {
                curl_multi_exec($this->multiCurl, $iEjecutando);
                sleep(1);
            } while ($iEjecutando > 0);

            foreach ($curl as $key => $value) {
                $resultadoFinal[$key] = curl_multi_getcontent($value);

                if ( !empty($resultadoFinal[$key]) ) {
                    log_mensaje('error', "ERROR DE COMUNICACIÓN WS: " . curl_error($value));
                }

                curl_multi_remove_handle($this->multiCurl, $value);
            }

            // Cerramos el objeto global que proceso todas las peticiones
            curl_multi_close($this->multiCurl);
            $respuesta =  $resultadoFinal;
        } else {
            $this->setearVariable(CURLOPT_URL, $this->strURL);
            $this->setearVariable(CURLOPT_SSL_VERIFYHOST, $this->boolVerificarHostSSL);
            $this->setearVariable(CURLOPT_SSL_VERIFYPEER, $this->boolVerificarPeerSSL);

            if( isset($this->arrCabeceras) && !empty($this->arrCabeceras) ) {
                $this->setearVariable(CURLOPT_HTTPHEADER, $this->arrCabeceras);
            }
            $arrResult = curl_exec($this->curl);

            if( $this->boolRetornaInformacion ) {

                if ($arrResult === false) {
                    if ( $this->logFunction ) {
                        log_mensaje('error', "ERROR DE COMUNICACIÓN WS: " . curl_error($this->curl));
                    }
                    $respuesta =  array(
                        false, 
                        'Imposible consultar el WS solicitado.'
                    );
                }
        
                $respuesta = $arrResult;

                if ( $this->logFunction ) {
                    log_mensaje('debug', "WS Response    : " . $respuesta);
                }
            }

            $this->httpCode  = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            curl_close($this->curl);
        }
        return $respuesta;
    }

    public function obtenerHttpCode() 
    {
        return $this->httpCode;
    }
}

?>