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
	
	protected $template = 'Page';
	
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
	
	/**
	 * @return Form
	 */
	function Form() {
		if(!self::$descriptions) throw new InvalidArgumentException('No $descriptions provided');

		$descMap = array();
		foreach(self::$descriptions as $uid => $description) {
			$description->load();
			$descMap[$uid] = $description->getShortName();
		}
		
		$form = new Form(
			$this,
			'Form',
			new FieldSet(
				new TextField('q', false),
				new CheckboxSetField('descriptions', false, $descMap)
			),
			new FieldSet(
				new FormAction('doSearch', _t('OpenSearchController.Search', 'Search'))
			),
			new RequiredFields(array('q'))
		);
		$form->setFormMethod('GET');
		
		return $form;
	}
	
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
			$q = Object::create('OpenSearchQuery', $url['template'], $data['q']);
			
			$resultsBySource->push(new ArrayData(array(
				'Uid' => $uid,
				'Title' => $description->getShortName(),
				'Results' => $q->getResults()
			)));
		}
		
		return $this->customise(array(
			'ResultsBySource' => $resultsBySource
		))->renderWith(array('OpenSearchResults', 'Page'));
	}
		
	/**
	 * Generates descriptions from the description elements.
	 * 
	 * @return array List of {@link OpenSearchDescription} instances.
	 */
	function getDescriptions() {
		if(!self::$descriptions) return false;
	}
}