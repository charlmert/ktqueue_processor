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
                         'DESCRIPTION' => 'Once there was a far blue boy.',
						 'COMEDEY' => 'He didn\'t have a yonder but he was kak hungry',
						 'FOOD' => 'Please sir can I have some more'
                  )
);

$res = $objRunner->run($params);

var_dump($res);

?>
