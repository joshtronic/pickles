<?php

/**
 * Common viewer class
 *
 * This is the class that each viewer class should be extending from.
 *
 * @package    PICKLES
 * @subpackage Viewer
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2007-2008 Joshua Sherman
 */
abstract class Viewer_Common extends Object {

	/**
	 * Protected model object
	 */
	protected $model = null;

	/**
	 * Constructor
	 *
	 * Runs the parent's constructor and adds the model to the object.
	 *
	 * @param object $model Object for the model we're loading
	 * @todo  Need better validation of the passed model.
	 */
	public function __construct(Model $model) {
		parent::__construct();
		$this->model = $model;
	}

	/**
	 * Abstract display function that is overloaded within the loaded viewer
	 */
	abstract public function display();
}

?>
