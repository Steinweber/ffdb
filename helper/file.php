<?php


namespace FFDB\Helper;


class File
{
    public static function read($file){
        if (!is_dir($file) && is_readable($file) && filesize($file)) {
            $handle = fopen($file, 'r');

            flock($handle, LOCK_SH);

            $data = fread($handle, filesize($file));

            flock($handle, LOCK_UN);

            fclose($handle);

            return $data;
        }
        return null;
    }

    public static function write($file,$content){
        if(!is_dir($file)){
            $handle = fopen($file, 'w');

            flock($handle, LOCK_EX);

            fwrite($handle, $content);

            fflush($handle);

            flock($handle, LOCK_UN);

            fclose($handle);
            return true;
        }
        return  false;
    }

    public static function dbFilePath($path,$db_name,$extension){
        return File::path($path,$db_name).$db_name.'.'.$extension;
    }

    public static function dbPath($path,$db_name){
        return  File::path($path,$db_name);
    }

    public static function indexFile($path){
        return File::path($path).'_index.json';
    }

    public static function path(){
        $args = func_get_args();
        $path = $args[0];
        if(substr($path,-1) !== '/'){
            $path .= '/';
        }
        if(count($args) > 1){
            unset($args[0]);
            $path .= implode('/',$args);
        }
        return $path.'/';
    }

    public static function createFileIfNotExists($path){
        if(!is_file($path)){
            File::write($path,'');
        }
    }

    public static function createDirIfNotExists($path){
        $path = File::path($path);
        if(!is_dir($path)){
            mkdir($path,0777);
        }
    }

    public static function clearFolder($path){
        $path = File::path($path);
        $di = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ( $ri as $file ) {
            $file->isDir() ?  rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        rmdir($path);
        return true;
    }

}