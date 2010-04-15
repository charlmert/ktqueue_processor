<?php

class Indexer {
    
    public function __construct()
    {
        
    }
    
    public function run($params)
    {
        fwrite(STDOUT, 'Indexer running with params: ');
        print_r($params);
        print("\n");
        return array('0', 'Indexer finished successfully [' . __FUNCTION__ . ']');
    }
    
}

?>