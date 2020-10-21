<?php

use CRM_Sqlexport_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Sqlexport_Form_SqlExport extends CRM_Core_Form {
  var $fields = array();

  public function buildQuickForm() {
    CRM_Core_Resources::singleton()->addStyleFile('net.ourpowerbase.sqlexport', 'sqlexport.css');
    $queries = Civi::settings()->get('sqlexport_queries');
    if (empty($queries)) {
      $queries = [];
    }
    $query_options = [];
    foreach($queries as $name => $sql) {
      $query_options[$name] = $name;
    }

    $defaults = [];
    $action = CRM_Utils_Request::retrieve('sqlexport_action', 'String');
    if ($action) {
      $defaults['action'] = $action;
    }
    $saved_query = CRM_Utils_Request::retrieve('saved_query', 'String');
    if ($saved_query) {
      $defaults['saved_query'] = $saved_query;
    }
    
    $this->setDefaults($defaults);

    // add form elements
    $this->add(
      'select',
      'saved_query',
      'Choose from a saved Query',
      $query_options
    );
    $this->add(
      'text', // field type
      'name', // field name
      'Query Name', // field label
      NULL,
      FALSE // only required when saving or deleting
    );
    $this->add(
      'textarea', // field type
      'sql', // field name
      'SQL Statement', // field label
      NULL,
      TRUE // is required
    );
    $this->add(
      'select',
      'action',
      'Action',
      [ 'export' => 'Export', 'save' => 'Save', 'delete' => 'Delete' ],
    );
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    $this->assign('sqlexport_queries', json_encode($queries));

    parent::buildQuickForm();
  }

  public function addRules() {
    $this->addFormRule(array('CRM_Sqlexport_Form_SqlExport', 'sqlExportRules'));
  }

  public function sqlExportRules($values) {
    $sql = $values['sql'];
    if (!preg_match('/^SELECT/i', $sql)) {
      $errors['sql'] = 'Your sql statement must start with SELECT.';
    }
    if (($values['action'] == 'save' || $values['action'] == 'delete') && empty($values['name'])) {
      $errors['name'] = 'Please include a name when saving or deleting a SQL statement.';
    }
    return empty($errors) ? TRUE : $errors;
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    $values = $this->exportValues();
    $sql = $values['sql'];
    $name = $values['name'];
    $action = $values['action'];
    if ($action == 'save') {
      $queries = Civi::settings()->get('sqlexport_queries');
      if (empty($queries)) {
        $queries = [];
      }
      $queries[$name] = $sql;
      Civi::settings()->set('sqlexport_queries', $queries);
      $session->setStatus(ts("The query was saved."));  
      CRM_Utils_System::redirect("/civicrm/sqlexport?sqlexport_action=save&saved_query=$name");
    }
    if ($action == 'delete') {
      $queries = Civi::settings()->get('sqlexport_queries');
      if (empty($queries)) {
        $queries = [];
      }
      unset($queries[$name]);
      Civi::settings()->set('sqlexport_queries', $queries);
      $session->setStatus(ts("The query was deleted."));  
      CRM_Utils_System::redirect("/civicrm/sqlexport");
    }

    // Otherwise we export.
    $sql = str_replace("\n", " ", $values['sql']);

    if (!preg_match('/^SELECT (.*) FROM/i', $sql, $matches)) {
      $session->setStatus(ts("Please ensure your SQL statement starts with SELECT."));  
      return FALSE;
    }
    $init_fields = explode(',', $matches[1]);
    foreach($init_fields as $field) {
      $field = trim($field);
      if (preg_match('/(.*) AS (.*)/i', $field, $matches)) {
        $column_name = trim($matches[1]);
        $display_name = trim($matches[2]);
      }
      else {
        $column_name = $display_name = $field;
      }
      $this->fields[$column_name] = $display_name;
    }
    $header = array_values($this->fields);
    $dao = CRM_Core_DAO::executeQuery($sql);

    $rows = $dao->fetchAll(); 
    // CRM_Core_Error::debug_var('header', $header);  
    // CRM_Core_Error::debug_var('rows', $rows);  
    CRM_Core_Report_Excel::writeCSVFile('SqlExport.csv', $header, $rows);
    CRM_Utils_System::civiExit();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
