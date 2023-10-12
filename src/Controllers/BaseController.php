<?php
namespace Src\Controllers;
use Src\Services\Security;
use Src\Services\ResponseHandler;
require  '././vendor/autoload.php';


Class BaseController { 

    public static function Auth( ResponseHandler $responseHandler , Security $security){

        $token = $security->getBearerToken();
      
        if (empty($token)) {
            return $responseHandler->handleJsonResponse([
                'msg' => 'JWT not found '
            ] , 401 , 'Unauthorized');
        }
        $isAuth = $security->verifyToken($token);
        if ($isAuth == false) {
            return $responseHandler->handleJsonResponse([
                'msg' => 'invalid JWT'
            ] , 498 , 'Token expired/invalid');
        }
        $isExp = $security->verifyExp($token);
        if($isExp == false){
            return $responseHandler->handleJsonResponse([
                'msg' => 'expired JWT'
            ] , 498 , 'Token expired/invalid');
        }
        return null;
    }

}