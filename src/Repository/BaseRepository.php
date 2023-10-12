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


        public static function renderParam(){
            return  [
                'start' => 'tk__titre',
                'end' => 'cli__ville',
                'self' => [
                    'name' => 'ticket' , 
                    'alias' => 't',
                    'field' => [
                        'tk__id' => 'in' ,
                        'tk__lu' => 'in',
                        'tk__motif' => 'in',
                        'tk__titre' => 'like' , 
                        'tk__groupe' => 'in', 
                    ] 
                ],
                'materiel' => [
                    'alias' => 'm',
                    'type' => 'LEFT',
                    'on' => [
                        'mat__id' => 't.tk__motif_id'
                    ],
                    'field' => [
                        'mat__id' => 'in' ,
                        'mat__cli__id' => 'in' ,
                        'mat__type' => 'like' , 
                        'mat__marque' => 'like', 
                        'mat__model' => 'like', 
                        'mat__pn' => 'like',
                        'mat__sn' => 'like', 
                        'mat__idnec' => 'like'
                    ]
                ], 
                'lien_user_client' => [
                    'alias' => 'l',
                    'type' => 'LEFT',
                    'on' => [
                        'luc__cli__id' => 'm.mat__cli__id'
                    ],
                    'field' => [
                    
                    ]
                ], 
                'client' => [
                    'alias' => 'c',
                    'type' => 'LEFT',
                    'on' => [
                        'cli__id' => 'l.luc__cli__id'
                    ],
                    'field' => [
                        'cli__id' => 'like' ,
                        'cli__nom' => 'like' , 
                        'cli__ville' => 'like'
                    ]
                ], 'ticket_ligne' => [
                    'alias' => 'y',
                    'type' => 'LEFT',
                    'on' => [
                        'tkl__tk_id' => 't.tk__id'
                    ],
                    'field' => [
                        'tkl__user_id_dest' => 'in',
                        'tkl__user_id' => 'in'
                    ]
                ], 
            ];
    
    }


    public function search(array $in ,  $clause,  int $limit , array $order  , array $params ){

        //////////////////////////////////////////////////////////////////////// CONFIG ///////////////////////////////////////////////////////////////////
       

        ////////////////////////////////////////////////////////////////////////////////////// LIMIT //////////////////////////////////////////////////////
        $limit_clause = '';
        if (!empty($limit)) {
            $limit_clause .= ' LIMIT ' . intval($limit);
        }
       
        ///////////////////////////////////////////////////////////////////////////// LEFT ///////////////////////////////////////////////////////////////////
        $left_clause = '';
        foreach ($params as $key => $value) {
            if ($key != 'self' ) {
                $left_clause .=   ' ' . $value['type'] . ' JOIN '.$key.' as '.  $value['alias'] .'  ON  ( ' . $value['alias'].'.';
                foreach ($value['on'] as $keys => $entry) {
                    $left_clause .=  $keys.' = '.$entry;
                }
                $left_clause .= ' ) ';
            }
        }

        ////////////////////////////////////////////////////////////////////////////// IN ///////////////////////////////////////////////////////////
            $in_clause = '';
            foreach ($params as $key => $value) {
                foreach ($value['field'] as $ref => $entry) {
                    if ( $entry == 'in' or $entry == 'double') {
                        foreach ($in as $search => $option) {
                            
                            if (!empty($option) ) {
                                if ($search == $ref) {
                                    $in_clause .= ' AND ( '.$value['alias'].'.'.$ref. ' IN ( ';
                                    foreach ($in[$search] as $index =>  $input) {
                                        if ($index === array_key_last($in[$search])){
                                            $in_clause .=   '"'. $input . '" ) ';
                                        }else{
                                            $in_clause .= '"' . $input . '" , ';
                                        }
                                    }  
                                    $in_clause .= ' )  ';
                                }
                            }   
                        }
                    }
                }
            }

       ////////////////////////////////////////////////////////////////////////////// WHERE ///////////////////////////////////////////////////////////
            $where_clause = '';
            if (!empty($clause)) {
                $filtre = str_replace("-", ' ', $clause);
                $filtre = str_replace("'", ' ',$clause);
                $nb_mots_filtre = str_word_count($filtre, 0, "0123456789");
                $mots_filtre = str_word_count($filtre, 1, '0123456789');
                $first = reset($params);
                for ($i = 0; $i < $nb_mots_filtre; $i++){
                    foreach ($params as $key => $value) {
                        if (!empty($value['field'])) {
                            foreach ($value['field'] as $field => $input) {
                                if($input == 'like' or $input == 'double'){
                                    if ($i == 0 ){
                                        if ($field == $params['self']['start']) {
                                            $where_clause .=  ' AND ( ( ' .  $value['alias'].'.'.$field  . ' LIKE "%' .$mots_filtre[$i] .'%" )';
                                        }else {
                                            $where_clause .=  ' OR  ( ' .  $value['alias'].'.'.$field  . ' LIKE "%' .$mots_filtre[$i] .'%" ) ';
                                        }
                                        if ($field == $params['self']['end']) {
                                            $where_clause .= ' ) ';
                                        }
                                        
                                    }else {
                                        if ($field == $params['self']['start']) {
                                            $where_clause .=   ' AND ( ( ' .  $value['alias'].'.'.$field  .'  LIKE "%' .$mots_filtre[$i] .'%" ) ';
                                        }else {
                                            $where_clause .=   ' OR ( ' .  $value['alias'].'.'.$field  .'  LIKE "%' .$mots_filtre[$i] .'%" ) ';
                                        }
                                        if ($field == $params['self']['end']) {
                                            $where_clause .= ' ) ';
                                        }
                                       
                                    }
                                }
                            }
                        }
                    }
                }
            
        
                $orderclause = '';
                if (!empty($order)) {
                    $orderclause .= 'ORDER BY';
                }  
            }

     ////////////////////////////////////////////////////////////////////////////// ORDER ///////////////////////////////////////////////////////////
     $orderclause = " ";
        foreach ($order as $key => $value) {
            if ($key === array_key_last($order)){
                $orderclause .= ' '.$key . ' ' . $value . ' ' ;
            }else {
                $orderclause .= ' '.$key . ' ' . $value . ', ' ;
            }
        }

    ///////////////////////////////////////////////////////////////////////////////// FINAL ////////////////////////////////////////////////////////////////////////
        $clause = 'SELECT DISTINCT  *  FROM ' . $params['self']['name'] . ' as ' . $params['self']['alias'].' '. $left_clause . ' WHERE 1 = 1 ' . $in_clause . ' ' . $where_clause . ' ' .  $orderclause  .'  ' . $limit_clause . '';
       
        $request = $this->Db->Pdo->query($clause);
        return  $request->fetchAll(PDO::FETCH_ASSOC);
    }

   

}