<?PHP

require_once('PDFConvert.inc.php');

/*
 * 
 * PDFConvert Example:
 * S3 issn't natively supported by the servlet so for now simply
 * change the src_file and dest_file params.
 * 
 */

$objRunner = new PDFConvert();

$params = Array (
	'url' => 'http://localhost:8080/converter/converted/document.pdf?outputFormat=pdf',
    'doc_id' => 'KT Document ID',
    'src_file' => '/var/www/qa_test_data/2003_doc.doc',
    'dest_file' => '/var/www/qa_test_data/2003_doc_PDFConvert.pdf',
	's3_enabled' => false,
    's3_fetch_url' => 'http://amazon/s3/bucket/file/url/fetch',
    's3_put_url' => 'http://amazon/s3/bucket/file/url/put'
);

$res = $objRunner->run($params);

var_dump($res);

?>
