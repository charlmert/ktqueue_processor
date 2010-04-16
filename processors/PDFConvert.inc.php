<?php

/*
 * This is a runnable class that gets invoked by the KT Queue System for 
 * a single simple event/job meant for converting a document to PDF 
 * 
 */

class PDFConvert {
    
    /*
     * Contains all the predefined parameter names. Any parameter specified must
     * exist in this list.
     */
    var $paramConst;
    
    public function __construct()
    {
        //Initializing Param Constants
        $this->paramConst[] = 'url';
        $this->paramConst[] = 'src_file';
        $this->paramConst[] = 'dest_file';
        $this->paramConst[] = 'doc_id';
        $this->paramConst[] = 's3_enabled';
        $this->paramConst[] = 's3_fetch_url';
        $this->paramConst[] = 's3_put_url';
        
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
        
        $reason =  array('1', 'PDFConvert Failed: Couldn\'t Find any Required Parameters  [' . __FUNCTION__ . ']');
        return $valid;
    }
    
    /*
     * Runs the PDF Convert class VIA REST call to the PDFConvert Servlet.
     * @params : Array() $params
     * 
     *    Expected Array Structure (Key/Value Pair):
     *    
     *    $params = Array (
     *       'url' => 'http://yourhost:8080/convert/',
     *       'doc_id' => 'KT Document ID',
     *       'src_file' => '/path/to/src_file',
     *       'dest_file' => '/path/to/dest_file',
     *       's3_enabled' => false,
     *       's3_fetch_url' => 'http://amazon/s3/bucket/file/url/fetch',
     *       's3_put_url' => 'http://amazon/s3/bucket/file/url/put',
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
        $this->docId = $params['doc_id'];
        $this->srcFile = $params['src_file'];
        $this->destFile = $params['dest_file'];
        $this->s3FetchUrl = $params['s3_fetch_url'];
        $this->s3PutUrl = $params['s3_put_url'];

       /*
        //TODO: facilitate url building to include params: 
        if (!empty($params)) {
            foreach ($params as $key=>$value)
            $paramdata .= "literal.$key=$value&";
        }
    
        $paramdata = substr($paramdata, 0, strlen($paramdata) - 1);
        
        $url = $url . '&' . $paramdata;
       */
        
        $result = $this->convertDocumentToPDF($this->url,  $this->srcFile, $this->destFile);
        
        //fwrite(STDOUT, 'PDFConvert running with params: ');
        //print_r($params);
        //print("\n");
        //fwrite(STDOUT, 'PDFConvert Response: ');
        //print_r($result);
        
        return $result;
    }

    
    /**
     * Raw Extract Method. Takes the file (binary formatted document) and sends it to the server to 
     * be extracted and indexed based on it's metadata.  
     *
     * @param string $file Can be a file or url to the file.
     * @param string $file Optional parameter to specify extra metadata to be indexed.
     * @return Curl response //Apache_Solr_Response
     *
     * @throws Exception If an error occurs during the service call
     */
    function convertDocumentToPDF($url, $srcFile, $destFile) {
    
        $url = $url . '&outputFormat=pdf';

        echo "curl \"$url\" -F \"inputDocument=@$srcFile\"\n";
        
        $file = array('inputDocument'=>'@'.$srcFile); //Formatting for post file upload
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close ($ch);
        
        $info['body'] = $result;
        
		if ($info['http_code'] != 200)
		{
		    $err_code = ($info['http_code'] != '')? $info['http_code'] : 'NULL';
		    return array($err_code, 'PDF Convertion Failed: BAD HTTP CODE ['.$err_code.'] | [' . __FUNCTION__ . ']');
		} else {
		    //Success Code 200
		    //Need to retrieve binary file from stream
		    $fp = fopen($destFile, 'wb');
		    if ($fp) {
		        fwrite($fp, $result);
		    } else {
    		    return array('0', 'PDF Convertion Failed: Couldn\'t write PDF to ['. $destFile .'] | [' . __FUNCTION__ . ']');
		    }
		    fclose($fp);
		    
		    return array('0', 'PDF Convertion Success: PDF saved to ['. $destFile .'] | [' . __FUNCTION__ . ']');
		}
        
    }
    
}

?>
