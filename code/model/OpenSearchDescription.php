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
	 * @var SS_Cache
	 */
	protected $cache;
	
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
		$cache = $this->getCache();
		$cacheKey = sha1($this->url);
		
		if($xml = $cache->load($cacheKey)) {
			$this->xml = simplexml_load_string($xml);
		} else {
			$reqClass = (class_exists('SS_HTTPRequest')) ? 'SS_HTTPRequest' : 'OpenSearchHTTPRequest';
			$c = Object::create('OpenSearchHTTPClient');
			$response = $c->request(new $reqClass('GET', $this->url));
			if($response->getStatusCode() >= 400) throw new Exception(sprintf('Invalid description (Code: %d, Response: %s)', $response->getStatusCode(), $response->getBody()));
			$xmlStr = $response->getBody();
			$this->xml = simplexml_load_string($xmlStr);
			var_dump($this->url);
			var_dump($this->xml->Url);
			var_dump(count($this->xml->Url));
			if(!$this->xml->Url || !count($this->xml->Url)) {
				throw new Exception(sprintf(
					'No valid URLs in description document at %s',
					$this->url
				));
			}

			foreach($this->xml->Url as $url) {
				$this->urls[] = array(
					'template' => (string)$url["template"],
					'type' => (string)$url["type"],
				);
			}
			
			$cache->save($xmlStr);
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
	
	function setCache($cache) {
		$this->cache = $cache;
	}
	
	function getCache() {
		if(!$this->cache) {
			$this->cache = SS_Cache::factory('opensearch_descriptions');
			SS_Cache::set_cache_lifetime('opensearch_descriptions', 60*60 /* one hour */, 100);
		}
		return $this->cache;
	}
	
}