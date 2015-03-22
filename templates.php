<?php


/**
 * Manages element templates
 */
class Eddditor_Templates {


    private static function validate_structure($structure) {
        if (!isset($structure['template_id']) OR !is_int($structure['template_id'])) {
            $structure['template_id'] = -1;
        }

        if (!isset($structure['type']) OR !is_string($structure['type'])) {
            $structure['type'] = '';
        }

        if (!isset($structure['values']) OR !is_array($structure['values'])) {
            $structure['values'] = array();
        }

        if (!isset($structure['options']) OR !is_array($structure['options'])) {
            $structure['options'] = array();
        }

        if (!isset($structure['deleted']) OR $structure['deleted'] !== true) {
            $structure['deleted'] = false;
        }

        return $structure;
    }


    /**
     * Get template data for a given template ID
     *
     * @param string $id Template ID
     * @return mixed Template data on success, false on failure
     */
    public static function get($id) {
        $templates = get_option('eddditor_element_templates');

        if (isset($templates[$id])) {
            $template = self::validate_structure($templates[$id]);
            return $template;
        }

        return false;
    }


    /**
     * Get blank element instances for all saved templates
     *
     * @return array Element instances for all templates
     */
    public static function get_all() {
        $templates = array();
        $saved_templates = get_option('eddditor_element_templates');
        if (!is_array($saved_templates)) {
            $saved_templates = array();
        }

        $post_id = get_the_ID();

        foreach ($saved_templates as $template) {
            $template_object = self::create_element($template);
            if ($template_object AND $template_object->is_enabled_for($post_id) AND (!isset($template['deleted']) OR !$template['deleted'])) {
                $templates[] = $template_object;
            }
        }

        return $templates;
    }


    /**
     * Save a new template
     *
     * @param object $element Eddditor_Element object to be saved as a new template
     * @return object Eddditor_Element object with template ID
     */
    public static function save($element) {
        $templates = get_option('eddditor_element_templates');
        $id = count($templates);
        $element->set_template_id($id);
        $templates[$id] = $element->get_template_data();
        update_option('eddditor_element_templates', $templates);
        return $element;
    }


    /**
     * Update an existing template's data
     *
     * @param string $id Template ID
     * @param array $structure Element data (keys: type, values)
     * @return bool True if template has been updated, false on failure
     */
    public static function update($id, $structure) {
        $templates = get_option('eddditor_element_templates');

        if (isset($templates[$id])) {
            $structure = self::validate_structure($structure);
            $templates[$id] = $structure;
            update_option('eddditor_element_templates', $templates);
            return true;
        }

        return false;
    }


    /**
     * Delete an existing template
     *
     * @param string $id Template ID
     * @return bool True if template has been deleted, false on failure
     */
    public static function delete($id) {
        $templates = get_option('eddditor_element_templates');

        if (!is_int($id)) {
            return false;
        }

        if (isset($templates[$id])) {
            $templates[$id]['deleted'] = true;
            update_option('eddditor_element_templates', $templates);
            return true;
        }

        return false;
    }


    /**
     * Create a new element instance from a saved template
     *
     * @param int|array $id_or_structure Template ID or element structure with template_id
     * @return mixed New element instance, or false on failure
     */
    public static function create_element($id_or_structure, $options = array()) {
        $element = false;

        if (is_array($id_or_structure)) {
            $structure = self::validate_structure($id_or_structure);
            return self::create_element($structure['template_id'], $structure['options']);
        } else if (is_int($id_or_structure)) {
            $id = $id_or_structure;

            $templates = get_option('eddditor_element_templates');
            if (is_array($templates) AND isset($templates[$id]) AND is_array($templates[$id])) {
                $template = self::validate_structure($templates[$id]);
                $element = Eddditor::create_element($template['type'], $template['values'], $options);
            }
        }

        if (!$element) {
            return false;
        }

        if (!$template['deleted']) {
            $element->set_template_id($id);
        }
        return $element;
    }


}