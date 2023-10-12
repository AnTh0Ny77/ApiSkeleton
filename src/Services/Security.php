<?php
namespace Src\Services;
require  '././vendor/autoload.php';
use ReallySimpleJWT\Token;
use Src\Entities\User;

Class Security {

    public $config;

    public function __construct(){
        $this->config = json_decode(file_get_contents('config.json'));
    }

	public function returnToken( int $user__id){
        $tokens = new Token();
        $payload = [
            'iat' => time(),
            'uid' => $user__id,
            'exp' => time() + 3600
        ];
        $secret = $this->config->security->app_secret;
        return Token::customPayload($payload, $secret);
    }

    public function verifyToken($token){
        return Token::validate($token, $this->config->security->app_secret);
    }

    public function verifyExp($token){
        return Token::validateExpiration($token, $this->config->security->app_secret);
    }

    public function readToken($token){
        return Token::getPayload($token , $this->config->security->app_secret);
    }

    public function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}