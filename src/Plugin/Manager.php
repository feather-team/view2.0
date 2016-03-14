<?php
namespace FeatherView\Plugin;
use FeatherView;

class Manager{
    protected $dirs = array();
    protected $engine;
    protected $systemItems = array();
    protected $instances = array();

    public function __construct(FeatherView\Engine $engine, $dir = null){
        $this->engine = $engine;
        $dir && $this->setPluginDir($dir);
    }

    public function registerSystemPlugin($name, $opt = null){
        $this->systemItems[$name] = $opt;
    }

    public function callSystemPlugins($content, $info = array()){
        foreach($this->systemItems as $name => $opt){
            $content = $this->instance($name, $opt)->exec($content, $info);
        }

        return $content;
    }

    //获取plugin实例
    public function instance($name, $opt = null){
        if(isset($this->instances[$name])){
            return $this->instances[$name];
        }

        //兼容1.x
        $previousClassName = 'Feather_View_Plugin_' . FeatherView\Helper::ul2camel($name, true);
        //2.0使用ns方式
        $nsClassName = '\\' . __NAMESPACE__ . '\\' . FeatherView\Helper::ul2camel($name);

        if(!class_exists($previousClassName) && !class_exists($nsClassName)){
            $previousClassFile = strtolower($previousClassName);

            if(FeatherView\Helper::loadFile($this->dirs, $previousClassFile)){
                $realClassName = $previousClassName;
            }else if(FeatherView\Helper::loadFile($this->dirs, $name)){
                $realClassName = $nsClassName;
            }else{
                throw new \Exception("plugin [{$name}] is not exists!");
            }
        }

        return $this->instances[$name] = new $realClassName($opt, $this->engine);
    }

    public function setPluginDir($dir = '/'){
        $this->dirs = (array)$dir;
    }

    public function addPluginDir($dir = '/'){
        $this->dirs = array_merge($this->dirs, (array)$dir);
    }
}