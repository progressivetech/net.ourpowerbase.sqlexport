<?php
use CRM_Sqlexport_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Sqlexport_Upgrader extends CRM_Sqlexport_Upgrader_Base {
  /**
   * Switch from old settings style to new one 
   *
   * @return TRUE on success
   * @throws Exception
   **/
  public function upgrade_1000() {
    $lastquery = Civi::settings()->get('sqlexport_lastquery');
    if ($lastquery) {
      // If we had a last query, ensure it is saved and available with the name 'Default'
      // There really should not be a sqlexport_queries variable here, but just in case...
      $sqlexport_queries = Civi::settings()->get('sqlexport_queries');
      if (empty($sqlexport_queries)) {
        $sqlexport_queries = [];
      }
      $sqlexport_queries['default'] = $lastquery;
      Civi::settings()->set('sqlexport_queries', $sqlexport_queries);
    }
    return TRUE;
  } 



}
