<?php
/**
 * @package opensearch
 */

// Fix up 2.3 bug (which uses HTTPRequest instead of SS_HTTPRequest).
// Basically a backport of http://open.silverstripe.org/changeset/103099 for 2.3
if(class_exists('HTTPRequest')) {
	class OpenSearchHTTPRequest extends HTTPRequest {
		function __construct($httpMethod, $url, $getVars = array(), $postVars = array(), $body = null) {
			$this->httpMethod = strtoupper(self::detect_method($httpMethod, $postVars));
			$this->url = $url;
		
			if(Director::is_relative_url($url)) {
				$this->url = preg_replace(array('/\/+/','/^\//', '/\/$/'),array('/','',''), $this->url);
			}
			if(preg_match('/^(.*)\.([A-Za-z][A-Za-z0-9]*)$/', $this->url, $matches)) {
				$this->url = $matches[1];
				$this->extension = $matches[2];
			}
			if($this->url) $this->dirParts = preg_split('|/+|', $this->url);
			else $this->dirParts = array();
		
			$this->getVars = (array)$getVars;
			$this->postVars = (array)$postVars;
			$this->body = $body;
		}
	}
}