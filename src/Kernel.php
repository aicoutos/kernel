<?php
namespace Basic;
use Basic\Routing;
use Medoo\Medoo;
class Kernel{
    function autoRouting(
        $bool=true
    ){
        if($bool){
            new Routing(true);
        }else{
            new Routing(false);
        }
    }
    function controller(
        $str
    ){
        $arr=explode('@',$str);
        $className=@$arr[0];
        $methodName=@$arr[1];
        $filename=$this->root().'app/controller/'.$className.'.php';
        $filenameIndex=$this->root().'app/controller/IndexController.php';
        if(file_exists($filename)){
            require_once $filename;
        }elseif(file_exists($filenameIndex)){
            require_once $filenameIndex;
        }else{
            die('o arquivo '.$className.'.php não existe');
        }
        $classNameWithNamespace='App\Controller\\'.$className;
        if(class_exists($classNameWithNamespace)){
            if(method_exists($classNameWithNamespace,$methodName)){
                $Controller=new $classNameWithNamespace();
                $Controller->$methodName();
            }else{
                $className=$this->e($className,false);
                $methodName=$this->e($methodName,false);
                die('o método '.$methodName.' não existe em '.$className);
            }
        }else{
            $className=$this->e($className,false);
            die('a classe '.$className.' não existe');
        }
    }
    function db(){
        return new Medoo([
            // required
            'database_type' => $_ENV['DB_TYPE'],
            'database_name' => $_ENV['DB_NAME'],
            'server' => $_ENV['DB_SERVER'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],

            // [optional]
            'charset' => $_ENV['DB_CHARSET'],
            'collation' => $_ENV['DB_COLLATION'],
            'port' => $_ENV['DB_PORT']
        ]);
    }
    function e(
        $str=null,
        $print=true
    ){
        if($print){
            print htmlentities($str);
        }else{
            return htmlentities($str);
        }
    }
    function error(
        $showErrors=true
    ){
        if($showErrors){
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }else{
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }
    }
    function getInputVars(){
        $pairs = explode("&", file_get_contents("php://input"));
        $vars = array();
        foreach ($pairs as $pair) {
            $nv = explode("=", $pair);
            $name = urldecode($nv[0]);
            $value = urldecode($nv[1]);
            $vars[$name] = $value;
        }
        return $vars;
    }
    function getMethod(){
        return @$_SERVER['REQUEST_METHOD'];
    }
    function root(){
        $str=getcwd();
        $arr=explode('/',$str);
        end($arr);
        $key=key($arr);
        unset($arr[$key]);
        $str=implode('/',$arr).'/';
        return $str;
    }
    function segment(
        $segmentId=null
    ){
        $str=$_SERVER["REQUEST_URI"];
        $str=@explode('?',$str)[0];
        $arr=explode('/',$str);
        $arr=array_filter($arr);
        $arr=array_values($arr);
        if(count($arr)<1){
            $segment[1]='/';
        }else{
            $i=1;
            foreach ($arr as $key => $value) {
                $segment[$i++]=$value;
            }
        }
        if(is_null($segmentId)){
            return $segment;
        }else{
            if(isset($segment[$segmentId])){
                return $segment[$segmentId];
            }else{
                return false;
            }
        }
    }
    function view(
        $name,
        $data=false
    ){
        $m = new \Mustache_Engine;
        $filename=$this->root().'app/view/'.$name.'.mustache';
        if(file_exists($filename)){
            $template=file_get_contents($filename);
            return $m->render($template, $data);
        }else{
            die('a view '.$this->e($name,false).'.mustache não existe');
        }

    }
}
?>
