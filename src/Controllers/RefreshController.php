<?php
namespace Src\Controllers;
require  '././vendor/autoload.php';
use Src\Services\ResponseHandler;
use Src\Database;
use Src\Repository\RefreshRepository;
use Src\Services\Security;
use Src\Entities\User;

Class RefreshController {

    public static function path(){
        return '/refresh';
    }

    public static function renderDoc(){
        $doc = [
              [
                'name' => 'refreshToken',
                "tittle" => 'Rafraichissement de jeton', 
                'method' => 'POST',
                'path' => self::path(),
                'description' => 'permet d obtenir un nouveau JWT' ,
                'body' =>  [
                    'type' => 'application/json',
                    'fields' => [
                            'refresh_token'   
                    ]
                    ],
                'reponse' =>  'envoi un token valide pour le user',
                "Auth" => 'PUBLIC'
            ] 
        ];
        return $doc;
    }


    public static function index($method,$data){
        $notFound = new NotFoundController();
        switch ($method) {
            case 'POST':
                return self::refresh();
                break;

            case 'GET':
                return $notFound::index();
                break;

            default:
                return $notFound::index();
                break;
        }
    }

	public static function refresh(){
        $database = new Database();
        $security = new Security();
        $database->DbConnect();
        $responseHandler = new ResponseHandler();
        $security = new Security();
        $refreshRepository = new RefreshRepository("refresh_token" , $database , User::class);
        $body = json_decode(file_get_contents('php://input'), true);

        if (empty($body['refresh_token'])) {
            return $responseHandler->handleJsonResponse('le refresh token n a pas été trouvé dans la requete' , 400 , 'Bad Request');
        }

        $refresh_token = $refreshRepository->findOneBy(['refresh_token' => $body['refresh_token'] ] , false);
        if (empty($refresh_token)) {
            return $responseHandler->handleJsonResponse('refresh token est invalide', 400 , 'Bad Request');
        }
        $token  = $security->returnToken($refresh_token['user__id']);
        $data = [
            "token" => $token
        ];
        return $responseHandler->handleJsonResponse($data , 200 , 'ok');
    }
}