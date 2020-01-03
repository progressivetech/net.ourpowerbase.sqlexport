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
    // add form elements
    $this->add(
      'textarea', // field type
      'sql', // field name
      'SQL Statement', // field label
      NULL,
      TRUE // is required
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

    $sql = Civi::settings()->get('sqlexport_lastquery', NULL);
    $this->setDefaults(array('sql' => $sql));
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
    return empty($errors) ? TRUE : $errors;
  }

  public function postProcess() {
    $values = $this->exportValues();
    $sql = str_replace("\n", " ", $values['sql']);

    $session = CRM_Core_Session::singleton();
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
    Civi::settings()->set('sqlexport_lastquery', $sql);
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
