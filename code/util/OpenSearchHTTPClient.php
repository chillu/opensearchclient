<?php
// @codeCoverageIgnoreStart
/**
 * @package opensearchclient
 */
class OpenSearchHTTPClient {
	
	function __construct() {}
	
	/**
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	function request(SS_HTTPRequest $request) {
		$url = $request->getURL();
		if($request->getVars()) $url .= '?' . http_build_query($request->getVars());

		$ch  = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 seconds should be enough for any geoserver request

		if($request->isPost()) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getBody());
		}
		$headers = $request->getHeaders();
		if($headers) {
			$curlHeaders = array();
			foreach($headers as $header => $value) {
				$curlHeaders[] = "$header: $value";			
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders); 
		}
		$response = curl_exec($ch);
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);
		
		return new SS_HTTPResponse($response, $statusCode);
	}
}
// @codeCoverageIgnoreEnd