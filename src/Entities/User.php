<?php
namespace Src\Entities;
require  '././vendor/autoload.php';

Class User {

	public $user__id;

    public $user__mail;

    public $user__password;

    public $token;

    public $refresh_token;

   
	public function getUser__id(){
		return $this->user__id;
	}

	public function setUser__id($user__id){
		$this->user__id = $user__id;
		return $this;
	}


    public function getUser__mail(){
        return $this->user__mail;
    }

    public function setUser__mail($user__mail){
        if (filter_var($user__mail, FILTER_VALIDATE_EMAIL)) {
            $this->user__mail = $user__mail;
            return $this;
        }else return 'Le mail saisi n est pas un email valide'; 
    }

    public function getUser__password(){
        return $this->user__password;
    }

    public function setUser__password($user__password){
        if (preg_match("/^(?=.*[0-9])(?=.*[A-Z]).{8,20}$/" ,  $user__password)) {
            $this->user__password = $user__password;
            return $this;
        }else return 'Le mot de pass doit contenir 8 charactères minimum ,un nombre, une majuscule et une minuscule ';
    }

    public function getToken(){
        return $this->token;
    }

    public function setToken($token){
        $this->token = $token;
        return $this;
    }


    public function getRefresh_token(){
        return $this->refresh_token;
    }
    
    public function setRefresh_token($refresh_token){
        $this->refresh_token = $refresh_token;

        return $this;
    }

}