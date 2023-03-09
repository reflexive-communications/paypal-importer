<?php

require_once 'paypal_importer.civix.php';

// phpcs:disable
use CRM_PaypalImporter_ExtensionUtil as E;

// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function paypal_importer_civicrm_config(&$config)
{
    _paypal_importer_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function paypal_importer_civicrm_navigationMenu(&$menu)
{
    _paypal_importer_civix_insert_navigation_menu($menu, 'Contributions', [
        'label' => E::ts('Paypal Importer'),
        'name' => 'paypal_importer',
        'url' => 'civicrm/contribute/paypal-import',
        'permission' => 'administer CiviCRM,access CiviContribute,edit contributions',
        'operator' => 'AND',
        'separator' => 0,
    ]);
    _paypal_importer_civix_navigationMenu($menu);
}
