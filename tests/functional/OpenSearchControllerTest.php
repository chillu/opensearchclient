<?php
/**
 * @package opensearchclient
 */
class OpenSearchControllerTest extends FunctionalTest {
	
	function setUp() {
		parent::setUp();
		
		OpenSearchController::clear_descriptions();
		OpenSearchController::register_description('test1', new OpenSearchDescription('http://test.com/OpenSearchDescriptionTest/opensearch/valid'));
		OpenSearchController::register_description('test2', new OpenSearchDescription('http://test.com/OpenSearchDescriptionTest/opensearch/otherdescription'));
				
		Object::useCustomClass('OpenSearchHTTPClient', 'OpenSearchTestHTTPClient');
	}
	
	function tearDown() {
		parent::tearDown();
		
		Object::useCustomClass('OpenSearchHTTPClient', 'OpenSearchHTTPClient');
	}
	
	function testResults() {
		$this->get('OpenSearchControllerTest_Controller');
		
		$response = $this->submitForm('Form_Form', null, array('q' => 'test', 'sources' => array()));
		$xml = $this->assertExactMatchBySelector(
			'ul.opensearch-resultsBySource h3', 
			array('Test Search 1', 'Test Search 2')
		);
	}
	
}

class OpenSearchControllerTest_Controller extends OpenSearchController implements TestOnly {

	function Link($action = null) {
		return Controller::join_links('OpenSearchControllerTest_Controller', $action);
	}
	
}