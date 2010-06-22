<?php
/**
 * @package opensearchclient
 * @subpackage tests
 */
class OpenSearchDescriptionTest extends SapphireTest {
	
	function setUp() {
		parent::setUp();
		
		Object::useCustomClass('OpenSearchHTTPClient', 'OpenSearchTestHTTPClient');
	}
	
	function tearDown() {
		parent::tearDown();
		
		Object::useCustomClass('OpenSearchHTTPClient', 'OpenSearchHTTPClient');
	}
	
	// function testNoUrls() {
	// 	$d = new OpenSearchDescription('http://test.com/OpenSearchDescriptionTest/opensearch/nourls');
	// }
	
	function testGetUrls() {
		$d = new OpenSearchDescription('http://test.com/OpenSearchDescriptionTest/opensearch/valid');
		$urls = $d->getUrls();
		$this->assertEquals(2, count($urls));
		$this->assertEquals(
			array(
				'template' => 'http://test.com/?q={searchTerms}&pw={startIndex?}&format=rss',
				'type' => 'application/rss+xml'
			),
			$urls[0]
		);
		$this->assertEquals(
			array(
				'template' => 'http://test.com/?q={searchTerms}&pw={startIndex?}&format=atom',
				'type' => 'application/atom+xml'
			),
			$urls[1]
		);
	}
	
	function testGetUrlByType() {
		$d = new OpenSearchDescription('http://test.com/OpenSearchDescriptionTest/opensearch/valid');
		
		$this->assertEquals(
			$d->getUrlByType('application/rss+xml'), 
			array(
				'template' => 'http://test.com/?q={searchTerms}&pw={startIndex?}&format=rss',
				'type' => 'application/rss+xml'
			)
		);
		$this->assertFalse($d->getUrlByType('application/unknown'));
	}
	
	function testGetUrlByTypeArray() {
		$d = new OpenSearchDescription('http://test.com/OpenSearchDescriptionTest/opensearch/valid');
		
		$this->assertEquals(
			$d->getUrlByType(array('invalid', 'application/rss+xml')), 
			array(
				'template' => 'http://test.com/?q={searchTerms}&pw={startIndex?}&format=rss',
				'type' => 'application/rss+xml'
			)
		);
		$this->assertFalse($d->getUrlByType('application/unknown'));
	}
	
}