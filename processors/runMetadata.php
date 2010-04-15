<?PHP

require_once('Metadata.inc.php');

/*
 * 
 * Metadata Inserter Example:
 * 
 */

$objRunner = new Metadata();

$params = Array (
	'url' => 'http://localhost:8080/ktservlets/metadata/update',
    'doc_id' => 'DOC_METADATA_001',
    'src_file' => '/var/www/qa_test_data/2003_doc.doc',
    'dest_file' => '/var/www/qa_test_data/2003_doc_I_New_Dem.doc',
	'metadata' => Array ('DOC_ID' => 'DOC_ID',
                         'META1' => 'Text1',
						 'META2' => 'Text2',
						 'META3' => 'Text3'
                  )
);

$res = $objRunner->run($params);

var_dump($res);

?>
