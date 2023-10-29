<?php


namespace SwitcherCore\Config;


use Exception;
use SwitcherCore\Config\Objects\Model;

class ModelCollector extends Collector
{
    /**
     * @var Model[]
     */
    protected $modelsDB;
    protected function read()
    {
        $this->modelsDB = $this->reader->readModels();
    }

    /**
     * @param string $descr
     * @param string $hardware
     * @param string $oidId
     * @return Model
     * @throws Exception
     */
    function getModelByDetect($descr = "",  $oidId = "", $ifacesCount = 0) {
        foreach ($this->modelsDB as $model) {
            if(!$model->detectByDescription($descr)) {
                continue;
            }
            if(!$model->detectByObjId($oidId)) {
                continue;
            }
            if(!$model->detectByIfacesCount($ifacesCount)) {
                continue;
            }
            return $model;
        }
        throw new Exception("Device {$descr} not supported by system");
    }

    /**
     * @param $key
     * @return Model
     * @throws Exception
     */
    function getModelByKey($key) {
        foreach ($this->modelsDB as $model) {
            if($model->getKey() === $key) {
                return $model;
            }
            if($model->getRewrites() && isset($model->getRewrites()['mapping'])) {
                foreach ($model->getRewrites()['mapping'] as $rewrite) {
                    if(isset($rewrite['rewrite']['key']) && $rewrite['rewrite']['key'] == $key) {
                        foreach ($rewrite['rewrite'] as $key=>$value) {
                            $model->{$key} = $value;
                        }
                        return  $model;
                    }
                }
            }
        }
        throw new \Exception("Model not found by key $key");
    }

    /**
     * @return string[]
     * @throws Exception
     */
    function getAllModelKeys() {
       $modelKeys = [];
        foreach ($this->modelsDB as $model) {
            if($model->getRewrites() && isset($model->getRewrites()['mapping'])) {
                foreach ($model->getRewrites()['mapping'] as $rewrite) {
                    if(isset($rewrite['rewrite']['key']) && $rewrite['rewrite']['key'] != $model->getKey()) {
                        $modelKeys[] = $model->getKey();
                    }
                }
            }
            $modelKeys[] = $model->getKey();
        }
        return $modelKeys;
    }

}