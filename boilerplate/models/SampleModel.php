<?php

/**
 * Same Model
 */
class SampleModel extends Model
{
	protected $datasource = 'mysql'; // Name of the datasource in our config
	protected $table      = 'test';  // Table to interact with from this model
	protected $order_by   = 'posted_at DESC'; // Columns to order by
}

?>
