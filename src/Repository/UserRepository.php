<?php
namespace Src\Repository;
require  '././vendor/autoload.php';

use DateTime;
use Src\Database;
use PDO;
use Src\Repository\BaseRepository;
use Src\Entities\User;
use Src\Services\ResponseHandler;

Class UserRepository  extends BaseRepository{

    public function encrypt_password($pass){
        return  password_hash($pass, PASSWORD_DEFAULT);
    }

    public function postUser($user_data){

        $user = new User();
        $pass  = $user->setUser__password($user_data['user__password']);
        if (!$pass instanceof User) 
            return $pass;

        $user_data['user__password'] = $this->encrypt_password($user_data['user__password']);

        $mail =  $user->setUser__mail($user_data['user__mail']);
        if (!$mail instanceof User) 
            return $mail;
        
        $mail = $this->findOneBy(['user__mail' =>  $user_data['user__mail']] , true);

        if ($mail instanceof User) 
            return 'vous possédez deja un compte pour cet email.';
       
        $id_user = $this->insert($user_data);
        $user = $this->findOneBy(['user__id' =>  $id_user] , true );
        return $user;
    }

    public function updatePassword($user__id , $password){
        $request = $this->Db->Pdo->prepare('UPDATE user set user__password = ? , user__confirm = 1 where user__id = '. $user__id .' ');
        $request->execute([$password]);
        return true;
    }
 

    public function loginUser($user_data){
        if (empty($user_data['user__password'])) 
            return 'Le champ password ne peut pas etre vide.';

        if (empty($user_data['user__mail'])) 
            return 'Le champ mail ne peut pas etre vide.';
       
        $user = $this->findOneBy(['user__mail' =>  $user_data['user__mail']] , false);
        
        if (empty($user)) 
            return 'Identifiants invalides.';

        $password_authenticity = password_verify($user_data['user__password'],$user['user__password']);
        
        if ($password_authenticity == false )
             return 'Identifiants invalides.';

        $user = $this->findOneBy(['user__mail' =>  $user_data['user__mail']] , true);
        
        $user = $this->getRole($user);
        
        return $user;
    }
}