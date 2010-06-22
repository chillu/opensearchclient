<?php
/**
 * Caution: You need to call {@link load()} before using the object.
 * 
 * @package opensearchaggregator
 */
class OpenSearchDescription {
	
	/**
	 * @var String
	 */
	protected $url = null;
	
	/**
	 * @var array
	 */
	protected $urls = array();
	
	/**
	 * @var SimpleXMLElement
	 */
	protected $xml = null;
	
	/**
	 * @param String $url Absolute URL to a valid OpenSearch description document.
	 */
	function __construct($url) {
		$this->url = $url;
	}
	
	/**
	 * @param String $url
	 */
	function load() {
		// TODO Caching
		$c = Object::create('OpenSearchHTTPClient');
		$response = $c->request(new SS_HTTPRequest('GET', $this->url));
		if($response->getStatusCode() >= 400) throw new Exception(sprintf('Invalid description (Code: %d, Response: %s)', $response->getStatusCode(), $response->getBody()));
		
		$this->xml = simplexml_load_string($response->getBody());
		
		if(!$this->xml->Url || !count($this->xml->Url)) {
			throw new Exception('No valid URLs in description document');
		}
		
		foreach($this->xml->Url as $url) {
			$this->urls[] = array(
				'template' => (string)$url["template"],
				'type' => (string)$url["type"],
			);
		}
	}
	
	/**
	 * @return Array
	 */
	function getUrls() {
		if(!$this->xml) $this->load();
		
		return $this->urls;
	}
	
	/**
	 * @return String|array If passed as an array, the first matching type is returned
	 */
	function getUrlByType($type) {
		if(!$this->xml) $this->load();
		
		foreach($this->urls as $url) {
			if(
				(is_array($type) && in_array($url['type'], $type))
				|| $url['type'] == $type
			) {
				return $url;
			}
		}
		return false;
	}
	
	function getShortName() {
		if(!$this->xml) $this->load();
		
		return (string)$this->xml->ShortName;
	}
	
	function getDescription() {
		if(!$this->xml) $this->load();
		
		return (string)$this->xml->Description;
	}
	
}