<?php
namespace Src\Repository;
require  '././vendor/autoload.php';
require  '././src/Entities/User.php';
use Src\Database;
use PDO;
use PDOException;
use Src\Entities\User;
use ReflectionClass;
use Src\Services\ResponseHandler;

Class BaseRepository {

    public string $Table;
    public  $Class;
    public  $Db;

    public function __construct(string $table , $db , $class){
       
        $this->Table = $table;
        $this->Db = $db;
        $this->Class = $class;
    }

    public function verifyColumn(array $array){
        $object = new $this->Class();     
        foreach ($array as $key => $value) { 
            if ($key != 'search') {
                if (!property_exists($object , $key )) {
                    return 'Le champ '.$key.' n existe pas  ';
                }
            }
        }
    }

    public function findBy(array $array , int $limit , array $order){
        $limitclause = '';
        switch ($limit) {
            case 0:
            case null:
                $limitclause = '';
                break;
            
            default:
                $limitclause = 'LIMIT ' .  $limit;
                break;
        }
        $orderclause = '';
        if (!empty($order)) {
            $orderclause .= ' ORDER BY ' ;
            foreach ($order as $key => $value) {
                if ($key === array_key_last($order)){
                    $orderclause .= ' '.$key . ' ' . $value . ' ' ;
                }else {
                    $orderclause .= ' '.$key . ' ' . $value . ', ' ;
                }
            }
        }
        $clause = '';
        foreach ($array as $key => $value) {
            $clause .=  'AND ' . $key . ' = ' .$value.'';
        }
        $request = 'SELECT * FROM '.$this->Table.' WHERE 1 = 1 '.$clause .' ' . $orderclause . $limitclause ;
        
        $request = $this->Db->Pdo->query($request);
        return  $request->fetchAll(PDO::FETCH_ASSOC);
    }


    public function findRandom(){
        $request = 'SELECT *
            FROM promo
            
            ORDER BY RAND( )
            LIMIT 3';

        $request = $this->Db->Pdo->query($request);
        return  $request->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllAdd(){
        $request = 'SELECT *
            FROM promo';

        $request = $this->Db->Pdo->query($request);
        return  $request->fetchAll(PDO::FETCH_ASSOC);
    }


    

    public function findOneBy(array $array , bool $auto ){
        $clause = '';
        $data = [];
        foreach ($array as $key => $value) {
            $clause .=  "AND " . $key. " = '" .$value. "' ";
            array_push($data ,  $value);
        }
        $request = "SELECT * FROM ".$this->Table." WHERE 1 = 1 ".$clause ."";
    
        $request = $this->Db->Pdo->query($request);
        $request = $request->fetch(PDO::FETCH_ASSOC);
       
        if($request != false){
            if ($auto == true ) 
                return $this->auto_mapping($request, $this->Class);
            if ($auto == false) 
                return $request;
        }
        return null;
    }

    public function auto_mapping($array , $class){
        $object = new $class();
        foreach($array as $key => $value){
            $setName = 'set' . ucfirst($key);
            $object->$setName($value);
        }
        return $object;
    }

    public function clean($string){
        return trim(preg_replace('/[^A-Za-z0-9\-\ÀÁÂÄÈÉèËÊÎéêëïúöôûâàÓÔÙÚÿ@.]/', '', $string)); 
    }

    public function cleanKeepSpace($string){
        return trim(preg_replace('/[^A-Za-z0-9\-\ÀÁÂÄÈÉèËÊÎéêëïúöôûâàÓÔÙÚÿ@. ]/', '', $string)); 
    }


    public function getOrder($get_array){
        $array_order = [];
        foreach ($get_array as $key => $value) {
            if ( strtoupper($value)  === 'DESC' or  strtoupper($value)  === 'ASC') {
                $array_order[$key]  =  $value;
            }
        }
        return $array_order;
    }
   
    public function insert(array $array){
        $error = null;
        $column = '( ';
        $value = '( ';
        foreach ($array as $key => $val) {
            if ($key === array_key_last($array)){
                $column .= $key.' ';
                $value .=  ':'.$key.'';
            }else {
                $column .= $key.', ';
                $value .=  ':'.$key.', ';
            }
        }
        $column .= ') ';
        $value .=  ') ';
        $request = "INSERT INTO " .$this->Table." ";
        $request .= $column . ' VALUES ' . $value ; 

        try {
            $request = $this->Db->Pdo->prepare($request);
            foreach ($array as $key => $val) {
                $value =  ':'.$key.'';
                $request->bindValue($value, $val);
            }
            $request->execute();
       
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
        if ($error != null) {
           
            return $error;
        }
        $id = $this->Db->Pdo->lastInsertId();
        return $id;
    }

   


    public function insertNoPrimary(array $array)
    {
        $error = null;
        $column = '( ';
        $value = '( ';
        foreach ($array as $key => $val) {
            if ($key === array_key_last($array)) {
                $column .= $key . ' ';
                $value .=  ':' . $key . '';
            } else {
                $column .= $key . ', ';
                $value .=  ':' . $key . ', ';
            }
        }
        $column .= ') ';
        $value .=  ') ';
        $request = "INSERT INTO " . $this->Table . " ";
        $request .= $column . ' VALUES ' . $value;
        
        try {
            $request = $this->Db->Pdo->prepare($request);
            foreach ($array as $key => $val) {
                $value =  ':' . $key . '';
                $request->bindValue($value, $val);
            }
            $request->execute();
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
        if ($error != null) {
            return $error;
        }
        
        return true;
    }

    public function insertRole($user , $role){
        try {
            $request = $this->Db->Pdo->prepare('INSERT INTO user_role ( ur__user_id , ur__role) VALUES (  :ur__user_id, :ur__role ) ;');
            $request->bindValue(':ur__user_id', $user);
            $request->bindValue(':ur__role', $role);
            $request->execute();
        } catch (PDOException $e){
            $error = $e->getMessage();
            return $error;
        }
        
        return true;
    }

    public function deleteRole($user){
        try {
            $request = $this->Db->Pdo->prepare('DELETE FROM  user_role WHERE ur__user_id  = '. $user .'');
            $request->execute();
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
        if ($error != null) {
            return $error;
        }
        return true;
    }

    public function delete(array $array){
        $clause = '';
        $data = [];
        foreach ($array as $key => $value) {
            $clause .=  "AND " . $key. " = '" .$value. "' ";
            array_push($data ,  $value);
        }
        $request = "DELETE FROM ".$this->Table." WHERE 1 = 1 ".$clause ."";
        $request = $this->Db->Pdo->prepare($request);
        $request = $request->execute();
        if($request != false)
            return $request;
        return null;
    }

    public function searchBy(array $array){
        $clause = '';
        $first = reset( $array );
        foreach ($array as $key => $value) {
            if ($value == $first) {
                $clause .=  'AND  ( ' . $key . ' LIKE "%' .$value.'%"';
            }else{
                $clause .=  'OR ' . $key . ' LIKE "%' .$value.'%"';
            }
        }
        $clause .= ' )';
        $request = 'SELECT * FROM '.$this->Table.' WHERE 1 = 1 '.$clause .' ' ;
        $request = $this->Db->Pdo->query($request);
        $results =   $request->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $key => $value) {
            $value = $this->auto_mapping($value, $this->Class);
        }
        return $results;
    }

    public function returnPrimaryKey(){
        $request = "SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = 'myrecode'
            AND TABLE_NAME =  '".$this->Table."'
            AND COLUMN_KEY = 'PRI';";
        $request = $this->Db->Pdo->query($request);
        return $request->fetch(PDO::FETCH_ASSOC);
    }

    public function update(array $field){

        $array_exclusion  = [ 'token' , 'refresh_token' , 'roles'  , 'clients' , 'password' , 'user__password'] ; 
        
        $identifier =  $this->returnPrimaryKey()['COLUMN_NAME'];
      
        if (!isset($field[$identifier]) or empty($field[$identifier])) {
            return 'le champ '.$identifier.' doit etre renseigné pour effectuer la mise à jour';
        }
        
        $column = $this->verifyColumn($field);
        
        if (!empty($column)) 
            return $column;
            
        $id = $field[$identifier];
        $setClause = 'SET ';
        $arraySetClause = [];
        $array_remplacement = [];
        foreach ($field as $key => $value){
            if ($key != $identifier and !in_array($key , $array_exclusion) ) {
                $array_remplacement[$key] = $value;
            }
        }

        foreach ($array_remplacement as $key => $value){
            if ($key != $identifier ) {
                    if ($key === array_key_last($array_remplacement)) {
                        $setClause.= ''.$key. '= ? ';
                        array_push($arraySetClause , $value);
                    }else{
                        $setClause.= ''.$key. '= ? , ';
                        array_push($arraySetClause , $value);
                    }
            }
        }
        
        $clause = 'WHERE  ( 1 = 1 AND  ' . $identifier . ' = ' . $id . ' )';
       
        $request = $this->Db->Pdo->prepare('UPDATE '.$this->Table.' '.$setClause.' '. $clause. ' ');
        $request->execute($arraySetClause);
    }

   

}