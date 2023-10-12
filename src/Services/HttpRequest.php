<?php
namespace Src\Services;
require  '././vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


Class HttpRequest {

    public $config;

    public function __construct(){
        $this->config = json_decode(file_get_contents('config.json'));
    }

    public static function makeHeaders($token){
		$headers = ['Authorization' => 'Bearer ' .$token, 'Accept' => 'application/json'];
		return $headers;
	}

	public static  function handleResponse($response , $http_error){
		return [
			'code' => $response->getStatusCode(),
			'data' => json_decode($response->getBody()->read(16384087),true)['data'] , 
			'http_errors' => $http_error
		];
	}

    public static function Build( string $method, string $base_uri , string $env_uri ,  string $url, string $token, array $params  , bool $http_error ){

      
		$client = new \GuzzleHttp\Client(['base_uri' => $base_uri, 'curl' => array(CURLOPT_SSL_VERIFYPEER => false)]);

        switch ($method) {
            case 'POST':

                try { $response = $client->post( $env_uri . $url,  ['headers' => self::makeHeaders($token), 'json' => $params ]);} 
                catch (GuzzleHttp\Exception\ClientException $exeption) {$response = $exeption->getResponse();}
                return self::handleResponse($response , $http_error);
                break; 

            case 'GET':

                try { $response = $client->get( $env_uri . $url ,  ['headers' => self::makeHeaders($token), 'query' => $params ]);} 
                catch (GuzzleHttp\Exception\ClientException $exeption) {$response = $exeption->getResponse();}
                return self::handleResponse($response , $http_error);
                break;
        }
    }

}