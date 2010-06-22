<?php
class OpenSearchQueryTest extends SapphireTest {
	
	function setUp() {
		parent::setUp();
		
		Object::useCustomClass('OpenSearchHTTPClient', 'OpenSearchTestHTTPClient');
	}
	
	function tearDown() {
		parent::tearDown();
		
		Object::useCustomClass('OpenSearchHTTPClient', 'OpenSearchHTTPClient');
	}
	
	function testGetUrl() {
		$q = new OpenSearchQuery('http://test.com/OpenSearchQueryTest/?q={searchTerms}&index={startIndex?}&count={count?}&format=rss', 'keywords');
		$this->assertEquals(
			'http://test.com/OpenSearchQueryTest/?q=keywords&index=0&count=20&format=rss',
			$q->getUrl()
		);
	}
	
	function testGetUrlWithStartIndexAndCount() {
		$q = new OpenSearchQuery('http://test.com/OpenSearchQueryTest/?q={searchTerms}&index={startIndex?}&count={count?}&format=rss', 'keywords');
		$q->setCount(44);
		$q->setStartIndex(2);
		$this->assertEquals(
			'http://test.com/OpenSearchQueryTest/?q=keywords&index=2&count=44&format=rss',
			$q->getUrl()
		);
	}
	
	function testGetResults() {
		$q = new OpenSearchQuery('http://test.com/OpenSearchQueryTest/?q={searchTerms}&index={startIndex?}&count={count?}&format=rss', 'keywords');
		$results = $q->getResults();
		$this->assertType('DataObjectSet', $results);
		$this->assertEquals(2, $results->Count());
		
		$result1 = $results->First();
		$this->assertEquals('Test result 1', $result1->Title);
		
		$result2 = $results->Last();
		$this->assertEquals('Test result 2', $result2->Title);
		
		$this->assertEquals(100, $results->TotalItems());
	}
}