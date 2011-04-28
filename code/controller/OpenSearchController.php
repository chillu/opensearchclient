<?php
/**
 * Light wrapper around {@link OpenSearchForm} to make it routable.
 * 
 * @package opensearchaggregator
 */
class OpenSearchController extends Controller {
	
	static $allowed_actions = array(
		'Form',
		'search',
		'doFormSearch'
	);
	
	/**
	 * @var Array Used in {@link doSearch()}.
	 */
	static $search_results_template = array('OpenSearchResults', 'Page');
	
	/**
	 * @var string
	 */
	protected $searchLabel = false;
	protected $descriptionsLabel = false;
	protected $defaultSearchText = false;
	
	protected $template = array('Page', 'Page');
	
	/**
	 * @var Map of unique identifiers to {@link OpenSearchDescription} objects
	 */
	static $descriptions = array();
	
	/**
	 * @var Array
	 */
	static $valid_content_types = array(
		'application/rss+xml',
		'application/atom+xml',
	);
	
	/**
	 * @param String $uid
	 * @param OpenSearchDescription $desc
	 */
	static function register_description($uid, OpenSearchDescription $desc) {
		self::$descriptions[$uid] = $desc;
	}
	
	/**
	 * @param String $uid
	 */
	static function unregister_description($uid) {
		if(isset(self::$descriptions[$uid])) unset(self::$descriptions[$uid]);
	}
	
	/**
	 * Reset all descriptions
	 */
	static function clear_descriptions() {
		self::$descriptions = array();
	}
	
	/**
	 * @return array
	 */
	static function get_registered_descriptions() {
		return self::$descriptions;
	}
	
	function Link($action = null) {
		return Controller::join_links('OpenSearchController', $action);
	}
	
	function index($request) {
		if($request->getVar('q')) {
			return $this->doSearch($request->getVars(), $this->Form());
		} else {
			return $this;
		}
	}
		
	/**
	 * @return Form
	 */
	function Form() {
		$descMap = $this->getDescriptions();

		if(!$descMap) throw new InvalidArgumentException('No $descriptions provided');

		$form = new Form(
			$this,
			'Form',
			new FieldSet(
				$query = new TextField('q', $this->searchLabel, $this->defaultSearchText),
				$descField = new CheckboxSetField('descriptions', $this->descriptionsLabel, $descMap)
			),
			new FieldSet(
				new FormAction('doSearch', _t('OpenSearchController.Search', 'Search'))
			),
			new RequiredFields(array('q'))
		);
	
		// set a nicer error message.
		$query->setCustomValidationMessage($this->getValidationMessage());
		
		$form->setFormMethod('GET');
		$form->loadDataFrom($this->request->getVars());
		$form->disableSecurityToken();
		
		// Tick all descriptions by default
		$descs = $this->request->getVar('descriptions');
		if(!$descs) $descField->setValue(array_keys($descMap));
		
		return $form;
	}
	
	/**
	 * Perform the search.
	 */
	function doSearch($data, $form = null) {
		if(!isset($data['q'])) throw new InvalidArgumentException('Parameter "q" missing');
		
		if(@$data['descriptions']) {
			$descriptions = array();
			foreach($data['descriptions'] as $uid) {
				if(!isset(self::$descriptions[$uid])) throw new InvalidArgumentException(sprintf('Description "%s" not found', $uid));
				
				$descriptions[$uid] = self::$descriptions[$uid];
			}
			
		} else {
			$descriptions = self::$descriptions;
		}
		
		$resultsBySource = new DataObjectSet();
		foreach($descriptions as $uid => $description) {
			$url = $description->getUrlByType('application/atom+xml');
			if(!$url) throw new Exception(sprintf("No URL template with type 'application/atom+xml' detected for '%s'", $uid));
			
			$q = Object::create('OpenSearchQuery', $url['template'], $data['q']);

			$results = $q->getResults();
			
			$resultsBySource->push(new ArrayData(array(
				'Uid' => $uid,
				'Title' => $description->getShortName(),
				'Results' => $results
			)));
		}

		return $this->customise(array(
			'ResultsBySource' => $resultsBySource
		))->renderWith(self::$search_results_template);
	}
		
	
	/**
	 * The validation message for submitting the form
	 * 
	 * @return String
	 */
	function getValidationMessage() {
		return _t('OpenSearchController.VALIDATIONMESSAGE', 'Please enter a search query');
	}
	
	/**
	 * Return a map of 'nice' description titles to the ID
	 * 
	 * @return array
	 */
	function getDescriptions() {
		$output = array();
		
		if(!self::$descriptions) return false;
		
		foreach(self::$descriptions as $uid => $description) {
			try {
				$description->load();
				$output[$uid] = $description->getShortName();
			}
			catch(Exception $e) {
				user_error($e, E_USER_ERROR);
				continue;
			}
		}
		
		return $output;
	} 
}