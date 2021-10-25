<?php

namespace Easoblue\LaraModelService\Traits;

use App\Exceptions\ValidationException;

trait ModelService{

    protected $model;
    protected $options;
    protected $data;
    protected $initConfig;

    public function __construct($model = null,$config = [])
    {

        $this->initConfig = $config;
        $this->initConfig['msg_404'] = $config['msg_404'] ?? 'Model not found.';

        if($model instanceof $this->modelName ){
            $this->model = $model;
        }elseif(is_numeric($model) || is_string($model)){
            $this->model = new $this->modelName;
            $this->model = $this::prepare(['filter_id' => $model])->first();
            if(!$this->model){
                throw new ValidationException((object)[],400,$this->initConfig['msg_404']);
            }
        }else{
            $this->model = new $this->modelName;
        }

        $this->afterConstruct();
    }
    
    public static function instance(){
        return (new self);
    }
    
    public function setModel($model){
        $this->model = $model;
        return this;
    }

    private function defaultOptions($data = array()){
        
        $this->options['first']          = $data['first'] ?? false;
        $this->options['paginate']       = $data['paginate'] ?? true;
        $this->options['limit']          = $data['limit'] ?? "";
        $this->options['sort_by']        = $data['sort_by'] ?? "";
        $this->options['sort_value']     = $data['sort_value'] ?? "";
        $this->options['id']             = $data['id'] ?? false;

        $this->options['filter_id']      = $data['filter_id'] ?? null;
        $this->options['columns']        = $data['columns'] ?? null;

        if($this->options['id'] || $this->options['filter_id']){

           $this->model = $this->model->where((isset($this->IdField) ? $this->IdField : 'id'),$this->options['filter_id'] ?? $this->options['id']);
           // $this->options['first'] = true;
        }

        if($this->options['sort_by'] && $this->options['sort_value']){
          $this->model = $this->model->orderBy(
            $this->options['sort_by'],
            $this->options['sort_value']
           );
        }

        if($this->options['limit']){
           $this->model = $this->model->limit($this->options['limit']);
        }
    }

    public function userOptions(){}
    
    public function afterConstruct(){}

    public function prepare($data = array())
    {   
   
        if($this->model){
            $this->defaultOptions($data);
            $this->userOptions($data);

            // Because it has highest priorty
            if($this->options['columns']){
               if(is_string($this->options['columns'])){
                $this->options['columns'] = explode(',', $this->options['columns']);
                $this->model = $this->model->select($this->options['columns']);
               }
            }
        }
        
        return $this;
    }

    public function getModel(){
        return $this->model;
    }

    public function getNewModel(){
       return new $this->modelName;
    }

    public function setNewModel(){
        $this->model = new $this->modelName;
        return $this->model;
    }
    public function newModel(){
        $this->model = new $this->modelName;
        return $this;
    }

    public function get(){
        if($this->options['first'] ?? false){
            return $this->first();
        }
        return $this->model->get();
    }

    public function first($id = null){
        if($id){
            $this->prepare(['filter_id' => $id]);
        }
        return $this->model->first();
    }

    public function paginate($paginate = 20){

        if($this->options['first']??false){
            return $this->first();
        }
        if($this->options['paginate']??false){
            return $this->model->paginate($paginate);
        }
        return $this->get();
    }

    public function store($data = array()){
        $this->data = $data;
        $this->beforeModelSave('store');
        $this->createOrUpdate('store');
        $this->afterModelSave('store');
        return $this->model;
    }

    public function update($data = array()){

        $this->data = $data;
        $this->beforeModelSave('update');
        $this->createOrUpdate('update');
        $this->afterModelSave('update');
        return $this->model;
    }

    public function beforeModelSave(){}
    public function createOrUpdate(){}
    public function afterModelSave(){}

}
