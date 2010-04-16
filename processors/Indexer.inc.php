<?PHP
/*
 * This is a runnable class that gets invoked by the KT Queue System for 
 * a single simple event/job meant for the purpose of indexing a document via SolR. 
 * 
 */

class Indexer {
    
    /*
     * Contains all the predefined parameter names. Any parameter specified must
     * exist in this list.
     */
    var $paramConst;
    
    public function __construct()
    {
        //Initializing Param Constants
        $this->paramConst[] = 'url';
        $this->paramConst[] = 'doc_id';
        $this->paramConst[] = 'src_file';
        
    }

    /*
     * Util function to check validity of parameters 
     * 
     */
    private function isParamsValid($params, &$reason) {
        $valid = false;
        
        foreach ($this->paramConst as $key=>$value) {
            foreach ($params as $k=>$v) {
                if ($key == $k) {
                    $valid = true;
                    return $valid;
                }            
            } 
        }
        
        $reason =  array('1', 'Indexer Failed: Couldn\'t Find any Required Parameters  [' . __FUNCTION__ . ']');
        return $valid;
    }
    
    /*
     * Runs the Indexer class VIA REST call to the SolR Indexer Servlet.
     * @params : Array() $params
     * 
     *    Expected Array Structure (Key/Value Pair):
     *    
     *    $params = Array (
     *       'url' => 'http://yourhost:8080/convert/',
     *       'doc_id' => 'KT Document ID',
     *       'src_file' => '/path/to/src_file'
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

       /*
        //TODO: facilitate url building to include params: 
        if (!empty($params)) {
            foreach ($params as $key=>$value)
            $paramdata .= "literal.$key=$value&";
        }
    
        $paramdata = substr($paramdata, 0, strlen($paramdata) - 1);
        
        $url = $url . '&' . $paramdata;
       */
        
        $result = $this->indexDocumentToSolr($this->url,  $this->srcFile, $this->docId);
        
        //fwrite(STDOUT, 'Indexer running with params: ');
        //print_r($params);
        //print("\n");
        //return array('0', 'Indexer finished successfully [' . __FUNCTION__ . ']');
                
        return $result;
    }

    
    /**
     * Raw Extract Method. Takes the file (binary formatted document) and sends it to the SolR 
     * server to be extracted (tika) and indexed based on it's metadata.  
     *
     * @param string $url url to the base solr instance. e.g. http://localhost:8983/solr
     * @param string $file Full local path to the file to be sent.
     * @return Array('ErrCode', 'Message')
     *
     */
    function indexDocumentToSolr($url, $srcFile, $docId = null) {

        if ($docId != null) {
            if (preg_match('/\/.*\?.*/', $url)) {
                $url = $url . '&literal.id=' . $docId;
            } else {
                $url = $url . '?literal.id=' . $docId;
            }
        }
        
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
		    return array($err_code, 'Indexer Failed: BAD HTTP CODE ['.$err_code.'] | [' . __FUNCTION__ . ']');
		} else {
		    $success_code = ($info['http_code'] != '')? $info['http_code'] : 'NULL';
		    return array($success_code, 'Indexer Success: DocID '.$docId.' indexed to SolR ['. $url .'] | [' . __FUNCTION__ . ']');
		}
        
    }
    
}
?>