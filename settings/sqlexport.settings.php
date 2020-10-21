<?php

/**
 * Settings used by sqlexport.
 */

return array(
  // deprecated
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
  'sqlexport_queries' => array(
    'name' => 'sqlexport_queries',
    'type' => 'String',
    'serialize' => CRM_Core_DAO::SERIALIZE_JSON,
    'add' => '5.27',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'A dictionary of all saved queries.',
	),

);
