<?php
namespace Src\Controllers;
require  '././vendor/autoload.php';
use HaydenPierce\ClassFinder\ClassFinder as ClassFinderClassFinder;

Class BasePathController {

    protected static $twig;

    public static function path(){
        return '';
    }

	public static function index(){
        $loader = new \Twig\Loader\FilesystemLoader('./public/templates/');
       	self::$twig = new \Twig\Environment($loader, [
           'debug' => true,
           'cache' => false,
       	]);
       	self::$twig->addExtension(new \Twig\Extension\DebugExtension());
           
        $classes = ClassFinderClassFinder::getClassesInNamespace('Src\Controllers');
        $doc = [];
        foreach ($classes as $key => $value){
           $classe =  new $value();
           if (get_class($classe) != "Src\Controllers\BasePathController" and get_class($classe) != "Src\Controllers\NotFoundController" and get_class($classe) != "Src\Controllers\BaseController"  ) {
                $temp = $classe->renderDoc();
             
               array_push($doc , $classe->renderDoc());
           }
        }
        
        return self::$twig->render(
            'documentation.html.twig',[
            'doc' => $doc
        ]
        );	
       
    }
}