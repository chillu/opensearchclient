<?php
/**
 * @package opensearchclient
 * @subpackage tests
 */
class OpenSearchTestHTTPClient extends OpenSearchHTTPClient {

	function __construct() {}
	
	function request($request) {
		switch($request->getURL()) {
			case 'http://test.com/OpenSearchDescriptionTest/opensearch/nourls':
				$response = $this->getDescriptionNoUrls();
				break;
			case 'http://test.com/OpenSearchDescriptionTest/opensearch/valid':
				$response = $this->getDescription();
				break;
			case 'http://test.com/OpenSearchDescriptionTest/opensearch/otherdescription':
				$response = $this->getOtherDescription();
				break;
			case 'http://test.com/OpenSearchQueryTest/?q=keywords&index=0&count=20&format=rss':
				$response = $this->getResults();
				break;
			case 'http://test.com/?q=test&pw=0&format=atom':
			case 'http://test2.com/?q=test&pw=0&format=atom':
				$response = $this->getResults();
				break;
			default:
				throw new InvalidArgumentException(sprintf('Unknown URL: %s', $request->getURL()));
		}
		
		return new SS_HTTPResponse($response);
	}
	
	protected function getDescription() {
		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName>Test Search 1</ShortName>
<Description>Use Example.com to search the Web.</Description>
<Tags>example web</Tags>
<Contact>admin@example.com</Contact>
<Url type="application/rss+xml" template="http://test.com/?q={searchTerms}&amp;pw={startIndex?}&amp;format=rss"/>
<Url type="application/atom+xml" template="http://test.com/?q={searchTerms}&amp;pw={startIndex?}&amp;format=atom"/>
</OpenSearchDescription>
XML;
	}
	
	protected function getOtherDescription() {
		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName>Test Search 2</ShortName>
<Description>Use Example.com to search the Web.</Description>
<Tags>example web</Tags>
<Contact>admin@example.com</Contact>
<Url type="application/rss+xml" template="http://test2.com/?q={searchTerms}&amp;pw={startIndex?}&amp;format=rss"/>
<Url type="application/atom+xml" template="http://test2.com/?q={searchTerms}&amp;pw={startIndex?}&amp;format=atom"/>
</OpenSearchDescription>
XML;
	}
	
	protected function getDescriptionNoUrls() {
		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName>Web Search</ShortName>
<Description>Use Example.com to search the Web.</Description>
<Tags>example web</Tags>
<Contact>admin@example.com</Contact>
</OpenSearchDescription>
XML;
	}
	
	protected function getResults($data = null) {
		$data = array_merge(array(
			'totalResults' => 100,
			'startIndex' => 0,
			'itemsPerPage' => 20,
			'searchTerms' => 'New York History'
		), (array)$data);
		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/">
	<title>Example.com Search: New York history</title> 
	<link href="http://example.com/New+York+history"/>
	<updated>2003-12-13T18:30:02Z</updated>
	<author> 
		<name>Example.com, Inc.</name>
	</author> 
	<id>urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6</id>
	<opensearch:totalResults>{$data['totalResults']}</opensearch:totalResults>
	<opensearch:startIndex>{$data['startIndex']}</opensearch:startIndex>
	<opensearch:itemsPerPage>{$data['itemsPerPage']}</opensearch:itemsPerPage>
	<opensearch:Query role="request" searchTerms="{$data['searchTerms']}" startPage="1" />
	<entry>
	  <title>Test result 1</title>
	  <link href="http://www.columbia.edu/cu/lweb/eguids/amerihist/nyc.html"/>
	  <id>urn:uuid:1225c695-cfb8-4ebb-aaaa-80da344efa6a</id>
	  <updated>2003-12-13T18:30:02Z</updated>
	  <content type="text">
	    Test Content 1
	  </content>
	</entry>
	<entry>
	  <title>Test result 2</title>
	  <link href="http://www.columbia.edu/cu/lweb/eguids/amerihist/nyc.html"/>
	  <id>urn:uuid:1225c695-cfb8-4ebb-aaaa-80da344efa6a</id>
	  <updated>2003-12-13T18:30:02Z</updated>
	  <content type="text">
			Test Content 2
	  </content>
	</entry>
</feed>
XML;
	}
}