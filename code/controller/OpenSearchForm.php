<?php
/**
 * @package opensearchaggregator
 */
class OpenSearchForm extends Form {
	
	function __construct($controller, $name, FieldSet $fields, FieldSet $actions, $validator = null) {
		if(!$fields) $fields = new FieldSet(
			new TextField('')
		);
		
		parent::__construct($controller, $name, $fields, $actions, $validator);
	}
	
}