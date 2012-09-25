<?php

/**
 * Prosta klasa do obslugi danych na dysku
 * 
 * @author Wojciech Brüggemann <wojtek77@o2.pl>
 */
class DB
{
    /**
     * Singleton
     * dziala rowniez jesli po klasie DB dziedziczy inna klasa DB
     * @return DB
     */
    static public function getInstance()
    {
        static $instances;
        
        $class = get_called_class();
        if (!isset($instances[$class]))
        {
            $instances[$class] = new $class();
        }
        
        return $instances[$class];
    }
    
    
    
    private $_dir;
    private $_expire;
    
    /**
     * @param string $dir   katalog gdzie przechowywane sa dane
     * @param int $expire  dlugosc zycia danych w bazie danych (w sekundach), null oznacza brak ograniczen
     */
    public function __construct($dir='./db', $expire=null)
    {
        if (substr($dir, -1) !== '/') $dir .= '/';
        $this->_dir = $dir;
        if (!is_dir($dir)) mkdir ($dir, 0777, true);
        
        $this->_expire = $expire;
    }
    
    public function __destruct()
    {
        /* usuwanie plikow jesli jest okreslona dlugosc zycia */
        if (isset($this->_expire))
        {
            $time = time();
            foreach (glob(__DIR__.'/'.$this->_dir.'*', GLOB_NOSORT) as $f)
            {
                if (filemtime($f)+$this->_expire < $time)
                    unlink($f);
            }
        }
    }
    
    
    /**
     * Pobieranie danych z bazy
     * @param string $id    identyfikator
     * @return string-null  w przypadku porazki zwracany jest null 
     */
    public function get($id)
    {
        $id = $this->filterId($id);
        
        return
            file_exists($path = $this->_dir.$id)
                ?   unserialize(file_get_contents($path))
                :   null;
    }
    
    /**
     * Dodawanie nowych danych do bazy
     * @param mixed $data   dane
     * @param string $id    identyfikator
     * @return string       identyfikator
     */
    public function put($data, $id=null)
    {
        if (isset($id))
            $id = $this->filterId($id);
        else
            $id = date('YmdHis').uniqid();
        
        file_put_contents($this->_dir.$id, serialize($data), LOCK_EX);
        
        return $id;
    }
    
    /**
     * Zmiana danych w bazie dla danego identyfikatora
     * @param string $id    identyfikator
     * @param mixed $data   dane
     * @return boolean      zwraca czy operacja "update" zakonczyla sie sukcesem
     */
    public function update($id, $data)
    {
        $id = $this->filterId($id);
        
        $path = $this->_dir.$id;
        if (file_exists($path))
        {
            file_put_contents($path, serialize($data), LOCK_EX);
            return true;
        }
        else
            return false;
    }
    
    /**
     * Usuwanie danych w bazie dla danego identyfikatora
     * @param string $id    identyfikator
     * @return boolean      zwraca czy operacja "delete" zakonczyla sie sukcesem
     */
    public function delete($id)
    {
        $id = $this->filterId($id);
        
        $path = $this->_dir.$id;
        if (file_exists($path))
        {
            unlink($path);
            return true;
        }
        else
            return false;
    }
    
    
    
    private function filterId($id)
    {
        return basename($id);
    }
}
