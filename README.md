# OpenSearch Client and Aggregator

## Introduction

Client library for talking to [http://www.opensearch.org/](OpenSearch) servers
and displaying search results in SilverStripe templates.
Does not generate search results itself, but rather present results from other opensearch providers.
Supports multiple OpenSearch sources.

The library follows the [http://www.opensearch.org/Specifications/OpenSearch/1.1](OpenSearch 1.1 specification).

## Maintainer

 * Ingo Schommer (ingo at silverstripe dot com)

## Installation


 * Register one or more OpenSearch description URLs in your `mysite/_config.php`:

		OpenSearchController::register_description('ssdoc', new OpenSearchDescription('http://doc.silverstripe.org/lib/exe/opensearch.php'));
		
 * Add a route to the `OpenSearchController` (the 'opensearch' URL prefix is customizeable):

		Director::addRules(50, array(
			'opensearch//$Action/$ID' => 'OpenSearchController'
		));