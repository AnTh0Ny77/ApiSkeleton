<?php
namespace Src\Repository;
require  '././vendor/autoload.php';

use DateTime;
use Src\Database;
use PDO;
use Src\Entities\User;
use Src\Repository\BaseRepository;
use Src\Services\ResponseHandler;

Class RefreshRepository extends BaseRepository {


    public function insertOne($user_id){

        $exist = $this->findOneBy(['user__id' => $user_id] , false);
        if (!empty($exist)) {
            $this->delete(['user__id' => $user_id]);
        }
        $key = md5(microtime().rand());
        $date = date('Y-m-d H:i:s' , strtotime("+30 days"));
        $array = [
            'user__id' => $user_id , 
            'refresh_token' => $key , 
            'exp__date' =>  $date
        ];
        $this->insert($array);
        return $this->findOneBy(['user__id' => $user_id] , false)['refresh_token'];
    }
}