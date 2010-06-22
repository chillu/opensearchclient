<?php
/**
 * @todo More parameters from http://www.opensearch.org/Specifications/OpenSearch/1.1#OpenSearch_URL_template_syntax
 *  searchTerms, startPage, language, inputEncoding, outputEncoding
 * @todo Handle other character encodings than UTF8
 * 
 * @package opensearchclient
 */
class OpenSearchQuery {
	
	/**
	 * @var String
	 */
	protected $urlTemplate = null;
	
	/**
	 * @var Int
	 */
	protected $startIndex = 0;
	
	/**
	 * @var Int
	 */
	protected $count = 20;
	
	/**
	 * @var String
	 */
	protected $keywords = null;
	
	/**
	 * @var Array List of all valid URL parameters in the OpenSearch 1.1 spec.
	 * @see http://www.opensearch.org/Specifications/OpenSearch/1.1#OpenSearch_1.1_parameters
	 */
	static $allowed_placeholders = array('searchTerms','count','startIndex','startPage', 'language', 'outputEncoding', 'inputEncoding');
	
	/**
	 * @param String $urlTemplate
	 * @param String $keywords (without URL encoding)
	 */
	function __construct($urlTemplate, $keywords) {
		$this->urlTemplate = $urlTemplate;
		$this->keywords = $keywords;
	}
	
	/**
	 * @return DataObjectSet A set of {@link OpenSearchQuery_Result} objects.
	 */
	function getResults() {
		require_once BASE_PATH . '/' . SAPPHIRE_DIR . '/thirdparty/simplepie/simplepie.inc';
		
		$url = $this->getUrl();
		
		// get data
		$c = Object::create('OpenSearchHTTPClient');
		$response = $c->request(new SS_HTTPRequest('GET', $this->getUrl()));
		if($response->isError()) {
			throw new Exception(sprintf('Invalid search response: %', $response->getBody()));
		}
		
		// parse data
		$feed = Object::create('SimplePie');
		$feed->set_raw_data($response->getBody()); // prevents caching, but we can't easily mock SimplePie for now
		$feed->init();
		$totalCountData = $feed->get_feed_tags('http://a9.com/-/spec/opensearch/1.1/', 'totalResults');
		$totalCount = ($totalCountData) ? (int)$totalCountData[0]['data'] : null;
		
		// compile results
		$results = new DataObjectSet();
		foreach($feed->get_items() as $item) {
			$result = new OpenSearchQuery_Result();
			$result = $result->customise(array(
				'Title' => $item->get_title(),
				'Description' => $item->get_description(),
				'Link' => $item->get_link(),
			));
			$results->push($result);
		}
		
		// set pagination state
		$results->setPageLimits($this->startIndex, $this->count, $totalCount);
		
		return $results;
	}
	
	/**
	 * Returns the URL with all placeholders replaced with their actual values.
	 * Unmatched placeholders will be replaced by an empty string.
	 * 
	 * @return String
	 */
	function getUrl() {
		$data = array(
			'searchTerms' => $this->keywords,
			'startIndex' => $this->startIndex,
			'count' => $this->count
		);
		
		// Replace all parameters
		$parts = parse_url($this->urlTemplate);
		$params = array();
		if(isset($parts['query'])) parse_str($parts['query'], $params);
		foreach($params as $key => $value) {
			// Transform "{placeholder?}" or "{placeholder}" into "placeholder"
			$placeholder = str_replace('?', '', $value);
			$placeholder = preg_replace('/\{(.*)\}/', '\\1', $placeholder);
			if(!in_array($placeholder, self::$allowed_placeholders)) continue;
			
			// Replace with data, or empty string if not applicable
			$params[$key] = (isset($data[$placeholder])) ? $data[$placeholder] : '';
		}
				
		// stitch URL back together - this is very clumsy, thank you PHP!
		$scheme = (isset($parts['scheme'])) ? $parts['scheme'] : 'http';
		$user = (isset($parts['user']) && $parts['user'])  ? $parts['user'] : '';
		$userpass = ($user) ? (isset($parts['pass']) && $parts['pass']) ? ':' . $parts['pass'] . '@' : '@' : null;
		$host = (isset($parts['host'])) ? $parts['host'] : '';
		$port = (isset($parts['port']) && $parts['port']) ? ':'.$parts['port'] : '';
		$path = (isset($parts['path']) && $parts['path']) ? $parts['path'] : '';
		$params = ($params) ?  '?' . http_build_query($params) : '';
		$fragment = (isset($parts['fragment']) && $parts['fragment']) ?  '#'.$parts['fragment'] : '';
		$url =  $scheme . '://' . $userpass . $host . $port . $path . $params . $fragment;
		
		return $url;
	}
	
	function setStartIndex($i) {
		$this->startIndex = $i;
	}
	
	function getStartIndex() {
		return $this->startIndex;
	}
	
	function setCount($i) {
		$this->count = $i;
	}
	
	function getCount() {
		return $this->count;
	}
	
}

/**
 * Result objects have the following properties: "Title", "Description", "Link".
 * 
 * @package opensearchclient
 */
class OpenSearchQuery_Result extends ViewableData {
	
}