<?php

/**
 * Settings used by sqlexport.
 */

return array(
  'sqlexport_lastquery' => array(
    'name' => 'sqlexport_lastquery',
    'type' => 'String',
    'default' => '',
    'add' => '5.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'The last sql query that was run.',
    'help_text' => 'Enter a select statement to export the results',
	),
);
