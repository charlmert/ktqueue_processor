<?PHP
require_once('Indexer.inc.php');

/*
 * 
 * Indexing Example:
 * Will connect to a SolR server to index the document specified by src_file.
 * 
 */

$objRunner = new Indexer();

$params = Array (
	'url' => 'http://localhost:8983/solr/update/extract',
    'doc_id' => 'DOC_001',
    'src_file' => '/var/www/qa_test_data/2003_doc.doc',
);

$res = $objRunner->run($params);

var_dump($res);
?>
