<?php

/**
 * Viewer class
 *
 * Uses the factory design pattern to create a new Viewer object based on what
 * viewer the model says it wants to use.
 *
 * @package    PICKLES
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2007-2008 Joshua Sherman
 */
class Viewer {

	/**
	 * Private constructor
	 */
	private function __construct() { }

	/**
	 * Viewer factory
	 *
	 * Creates an instance of the Viewer type that the model requests.
	 *
	 * @param  object $model The model to be displayed
	 * @return object An instance of the viewer, loaded with the passed model
	 * @todo   Add some checking to determine if the passed object is really a
	 *         valid instance of Model.
	 * @todo   Create constants to correspond with each viewer type so it's
	 *         potentially easier to reference from the model (since the
	 *         constants would each be uppercase instead of mixedcase.
	 */
	public static function factory(Model $model) {
		$class = 'Viewer_' . $model->getView();
		return new $class($model);
	}
}

?>
