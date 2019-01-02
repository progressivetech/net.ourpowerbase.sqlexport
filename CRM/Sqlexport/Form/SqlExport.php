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
    $sql = $values['sql'];

    if (!preg_match('/^SELECT (.*) FROM/i', $sql, $matches)) {
      // fail
    }
    $this->fields = explode(',', $matches[1]);
   
    $dao = CRM_Core_DAO::executeQuery($sql);
    $header = $this->fields;
    while($dao->fetch()) {
      $row = array();
      foreach($this->fields as $field) {
        $field = trim($field);
        if (preg_match('/ AS (.*)/i', $field, $matches)) {
          $field = trim($matches[1]);
        }
        if (property_exists($dao, $field)) {
          $row[$field] = $dao->$field;
        } 
        else {
          $row[$field] = '';
        }
      }
      $rows[] = $row;
    }
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
