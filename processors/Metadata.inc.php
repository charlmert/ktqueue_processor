<?php

/*
 * This is a runnable class that gets invoked by the KT Queue System for 
 * a single simple event/job meant inserting metadata into a document. 
 * 
 */

class Metadata {
    
    /* 
     * Contains all the predefined parameter names. Any parameter specified must
     * exist in this list.
     * 
     */
    var $paramConst;

    /* 
     * REST url to the servlet base for the call.
     * 
     */
    var $url;
    
    /* 
     * Source file to perform operations on.
     * 
     */
    var $srcFile;
    
    /*
     * Dest file to save the resulting file to.
     * 
     */
    var $destFile;
    
    /*
     * Array containing metadata to insert.
     * 
     */
    var $metadata;
    
    
    public function __construct()
    {
        //Initializing Param Constants
        $this->paramConst[] = 'url';
        $this->paramConst[] = 'src_file';
        $this->paramConst[] = 'dest_file';
        $this->paramConst[] = 'metadata';
    }

    /*
     * Util function to check validity of parameters 
     * 
     */
    public function isParamsValid($params, &$reason) {
        $valid = false;
        
        foreach ($this->paramConst as $key=>$value) {
            foreach ($params as $k=>$v) {
                if ($key == $k) {
                    $valid = true;
                    return $valid;
                }            
            } 
        }
        
        $reason =  array('1', 'Metadata Insertion Failed: Couldn\'t Find any Required Parameters  [' . __FUNCTION__ . ']');
        return $valid;
    }
    
    /*
     * Runs the Metadata class VIA REST call to the Metadata Servlet.
     * @params : Array() $params
     * 
     *    Expected Array Structure (Key/Value Pair):
     *    
     *    $params = Array (
     *       'url' => 'http://yourhost:8080/convert/',
     *       'src_file' => '/path/to/src_file',
     *       'dest_file' => '/path/to/dest_file',
     *       'metadata' => Array('key' => 'value')      //Special case for metadata
     * 	  )
     * 
     */
    public function run($params)
    {
        $reason = '';
        //Checking for valid parameters
        if (!$this->isParamsValid($params, $reason)) {
            return $reason;
        }
        
        $this->url = $params['url']; 
        $this->srcFile = $params['src_file'];
        $this->destFile = $params['dest_file'];
        $this->metadata = $params['metadata'];

       /*
        //TODO: facilitate url building to include params: 
        if (!empty($params)) {
            foreach ($params as $key=>$value)
            $paramdata .= "literal.$key=$value&";
        }
    
        $paramdata = substr($paramdata, 0, strlen($paramdata) - 1);
        
        $url = $url . '&' . $paramdata;
       */
        
        $result = $this->insertDocumentMetadata($this->url,  $this->srcFile, $this->destFile, $this->metadata);
        
        //fwrite(STDOUT, 'Metadata running with params: ');
        //print_r($params);
        //print("\n");
        //fwrite(STDOUT, 'Metadata Response: ');
        //print_r($result);
        
        return $result;
    }

    
    /**
     * Insert Document Metadata. Takes the file (binary formatted document) and sends it to the server to 
     * for metadata insertion and places the modified file in the specified destination.  
     *
     * @param string $url url to the kt metadata servlet.
     * @param string $srcFile the local path to the file to operate on.
     * @return Array('ErrCode', 'Message')
     *
     */
    function insertDocumentMetadata($url, $srcFile, $destFile, $aMetadata) {

        if (!empty($aMetadata)) {
            foreach ($aMetadata as $key=>$value)
            $metadata .= "$key=".urlencode($value) . '&';
        }
    
        $metadata = substr($metadata, 0, strlen($metadata) - 1);
                
        if (preg_match('/\/.*\?.*/', $url)) {
            $url = $url . '&' . $metadata;
        } else {
            $url = $url . '?' . $metadata;
        }
        
        echo "curl \"$url\" -F \"inputDocument=@$srcFile\"\n";
                
        $file = array('inputDocument'=>'@'.$srcFile); //Formatting for post file upload
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close ($ch);
        
        $info['body'] = $result;
        
		if ($info['http_code'] != 200)
		{
		    $err_code = ($info['http_code'] != '')? $info['http_code'] : 'NULL';
		    return array($err_code, 'Metadata Insertion Failed: BAD HTTP CODE ['.$err_code.'] | [' . __FUNCTION__ . ']');
		} else {
		    //Success Code 200
		    //Need to retrieve binary file from stream
		    $fp = fopen($destFile, 'wb');
		    if ($fp) {
		        fwrite($fp, $result);
		    } else {
    		    return array('0', 'Metadata Insertion Failed: Couldn\'t write to ['. $destFile .'] | [' . __FUNCTION__ . ']');
		    }
		    fclose($fp);
		    
		    return array('0', 'Metadata Success: Document saved to ['. $destFile .'] | [' . __FUNCTION__ . ']');
		}
        
    }
    
}

?>
