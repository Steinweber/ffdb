<?php


namespace FFDB\Instance;


use FFDB\Helper\File;
use FFDB\Helper\Registry;

class InstanceIndex
{
    protected $registry;
    private $db_name;
    private $data;

    public function __construct(Registry $registry,$db_name,$data=[])
    {
        $this->registry = $registry;
        $this->db_name = $db_name;

        $path = $registry->get('path');


        if(empty($data)){
            $instance_reg = $registry->instances->{$db_name};
            $extension = $instance_reg->get('extension');

            File::createDirIfNotExists(File::dbPath($path,$db_name));

            $db_file = File::dbFilePath($path,$db_name,$extension);
            File::createFileIfNotExists($db_file);

            $data['files'] = [$db_file];
            $data['adaptor'] = $instance_reg->get('adaptor');
            $data['extension'] = $extension;
        }else{
            //If instance from existing DB, load information to registry
            $registry->instances->{$db_name}->set('extension',$data['extension']);
            $registry->instances->{$db_name}->set('adaptor',$data['adaptor']);
        }
        $this->data = $data;
    }

    public function setMaxEntries($limit){
        $limit = (int) $limit;
        if($limit > 0){
            $this->data['maxEntries'] = $limit;
        }
    }

    public function getDbFiles()
    {
        return $this->data['files'];
    }

    public function getExtension(){
        return $this->data['extension'];
    }

    public function getAdaptor(){
        return $this->data['adaptor'];
    }


    public function data(){
        return $this->data;
    }
}