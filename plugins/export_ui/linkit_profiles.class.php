<?php
/**
 * @file
 * Contains the Linkit profile export UI integration code.
 */

/**
 * Linktit profile export UI class handler for Views UI.
 */
class linkit_profiles extends ctools_export_ui {
  /**
   * @TODO: Can we call parent:list_build_row and then just add linkit specific
   * columns?
   */
  function list_build_row($item, &$form_state, $operations) {
    // Set up sorting
    $name = $item->{$this->plugin['export']['key']};
    $schema = ctools_export_get_schema($this->plugin['schema']);

    // Note: $item->{$schema['export']['export type string']} should have already been set up by export.inc so
    // we can use it safely.
    switch ($form_state['values']['order']) {
      case 'disabled':
        $this->sorts[$name] = empty($item->disabled) . $name;
        break;
      case 'title':
        $this->sorts[$name] = $item->{$this->plugin['export']['admin_title']};
        break;
      case 'name':
        $this->sorts[$name] = $name;
        break;
      case 'storage':
        $this->sorts[$name] = $item->{$schema['export']['export type string']} . $name;
        break;
    }

    $this->rows[$name]['data'] = array();
    $this->rows[$name]['class'] = !empty($item->disabled) ? array('ctools-export-ui-disabled') : array('ctools-export-ui-enabled');

    // If we have an admin title, make it the first row.
    if (!empty($this->plugin['export']['admin_title'])) {
      $this->rows[$name]['data'][] = array('data' => check_plain($item->{$this->plugin['export']['admin_title']}), 'class' => array('ctools-export-ui-title'));
    }

    // Load the insert plugin definition.
    $insert_plugin = linkit_insert_plugin_load($item->data['insert_plugin']['plugin']);
    $this->rows[$name]['data'][] = array('data' => check_plain($insert_plugin['name']), 'class' => array('linkit-export-ui-insert-plugin'));

    $this->rows[$name]['data'][] = array('data' => check_plain($item->{$schema['export']['export type string']}), 'class' => array('ctools-export-ui-storage'));

    $ops = theme('links__ctools_dropbutton', array('links' => $operations, 'attributes' => array('class' => array('links', 'inline'))));

    $this->rows[$name]['data'][] = array('data' => $ops, 'class' => array('ctools-export-ui-operations'));

    // Add an automatic mouseover of the description if one exists.
    $this->rows[$name]['title'] = $item->{$this->plugin['export']['admin_description']};
  }

  function list_table_header() {
    $header = array();
    $header[] = array('data' => t('Title'), 'class' => array('ctools-export-ui-title'));
    $header[] = array('data' => t('Insert plugin'), 'class' => array('linkit-export-ui-insert-plugin'));
    $header[] = array('data' => t('Storage'), 'class' => array('ctools-export-ui-storage'));
    $header[] = array('data' => t('Operations'), 'class' => array('ctools-export-ui-operations'));
    return $header;
  }
}