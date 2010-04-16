<?php

class Processor {
    
    public function __construct()
    {
        
    }
    
    public function run($params)
    {
        fwrite(STDOUT, 'Processor running with params: ');
        print_r($params);
        print("\n");
        return array('0', 'Processor finished successfully [' . __FUNCTION__ . ']');
    }
    
    public function generatePdf($params)
    {
        fwrite(STDOUT, 'Processor running ' . __FUNCTION__ . 'with params: ');
        print_r($params);
        print("\n");
        return array('0', 'Processor finished successfully [' . __FUNCTION__ . ']');
    }
    
    public function generateThumbnail($params)
    {
        fwrite(STDOUT, 'Processor running ' . __FUNCTION__ . 'with params: ');
        print_r($params);
        print("\n");
        return array('0', 'Processor finished successfully [' . __FUNCTION__ . ']');
    }
    
    public function generateFlash($params)
    {
        fwrite(STDOUT, 'Processor running ' . __FUNCTION__ . 'with params: ');
        print_r($params);
        print("\n");
        return array('0', 'Processor finished successfully [' . __FUNCTION__ . ']');
    }
    
}

?>