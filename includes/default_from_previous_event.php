<?php
/**
 * @file
 * Provides Default from Previous Event feature.
 */

require_once 'helper.php';

/**
 * Handles @DEFAULT-FROM-PREVIOUS-EVENT action tag.
 */
function auto_populate_fields_default_from_previous_event() {
    global $Proj;

    $action_tag = '@DEFAULT-FROM-PREVIOUS-EVENT';
    $targets = array();

    foreach (auto_populate_fields_get_fields_names() as $field_name) {
        $misc = $Proj->metadata[$field_name]['misc'];
        if (auto_populate_fields_action_tag_semaphore($misc, $action_tag, false)) {
            if (!$source_field = Form::getValueInQuotesActionTag($misc, $action_tag)) {
                // If no value is provided on the action tag, set the same
                // field as source by default.
                $source_field = $field_name;
            }

            $targets[$field_name] = $source_field;
        }
    }

    if (empty($targets)) {
        return;
    }

    $data = REDCap::getData($Proj->project['project_id'], 'array', $_GET['id']);
    if (empty($data)) {
        return;
    }

    $data = $data[$_GET['id']];
    $arm = $Proj->eventInfo[$_GET['event_id']]['arm_num'];
    $events = array_keys($Proj->events[$arm]['events']);

    foreach ($targets as $field_target => $field_source) {
        $default_value = null;

        foreach ($events as $event) {
            if (!empty($data[$event]) && isset($data[$event][$field_source])) {
                $default_value = $data[$event][$field_source];
            }
        }

        if (!is_null($default_value)) {
            $Proj->metadata[$field_target]['misc'] .= ' @DEFAULT="' . $default_value . '"';
        }
    }
}
