<?php

class FrmHrsSettingsController{
    function __construct(){
        add_action('frm_add_settings_section', array(__CLASS__, 'add_settings_section'));
        
        add_action('wp_ajax_frm_hrs_add_tag_row', array(__CLASS__, 'add_tag_row'));
        add_action('wp_ajax_frm_hrs_get_field_values', array(__CLASS__, 'get_field_values'));
        
        // < 2.0 fallback
        add_action('init', array(__CLASS__, 'load_form_settings_hooks') );
        
        // 2.0 hooks
        add_action('frm_before_list_actions', array(__CLASS__, 'migrate_to_2'));
    }
    
    public static function add_settings_section($sections){
        $sections['highrise'] = array('class' => __CLASS__, 'function' => 'route');
        return $sections;
    }
    
    public static function add_tag_row(){
		$form_id = absint( $_POST['form_id'] );
		$tag = sanitize_title( $_POST['tag'] );
		$meta_name = sanitize_text_field( $_POST['meta_name'] );

		$list_fields = FrmHrsAppHelper::get_field_details( array( $tag ) );
		$list_field = reset( $list_fields );
		$show_add = array( $tag );
		$adding_row = true;
        
        if ( isset($_POST['action_key']) ) {
            // v2.0+
            $options = array();
            $action_control = FrmFormActionsController::get_form_actions( 'highrise' );
			$action_control->_set( sanitize_title( $_POST['action_key'] ) );
            include(FrmHrsAppHelper::plugin_path() .'/views/action-settings/_tag_row.php');
        } else {
            // < v2.0
            $hide_highrise = '';
            
            $frm_field = new FrmField();
            $form_fields = $frm_field->getAll("fi.form_id='". $form_id ."' and fi.type not in ('break', 'end_divider', 'divider', 'html', 'captcha', 'form')", 'field_order');
            unset($frm_field);
            
			$values = array( 'hrs_list' => array( $tag ) );
            include(FrmHrsAppHelper::plugin_path() .'/views/settings/_tag_row.php');
        }
        
        die();
    }
    
    public static function get_field_values(){
        global $wpdb;
        
        $form_id = (int)$_POST['form_id'];
        $meta_name = $_POST['meta_name'];
        
        $frm_field = new FrmField();
        $new_field = $frm_field->getOne($_POST['field_id']);
        unset($frm_field);
        
        $values = $wpdb->get_var($wpdb->prepare("SELECT options FROM {$wpdb->prefix}frm_forms WHERE id=%d", $form_id));
        $values = maybe_unserialize($values);
        if(!isset($values['hrs_list']))
            $values['hrs_list'] = array('hide_field' => array(), 'hide_field_cond' => array(), 'hide_opt' => array());
            
        require(FrmHrsAppHelper::plugin_path() .'/views/settings/_field_values.php');
        die();
    }
    
    public static function display_form($errors=array(), $message=''){
        $frm_hrs_settings = new FrmHrsSettings();
        
        include(FrmHrsAppHelper::plugin_path() . '/views/settings/form.php');
    }

    public static function process_form(){
        $frm_hrs_settings = new FrmHrsSettings();
		
        //$errors = $frm_hrs_settings->validate($_POST,array());
        $errors = array();
        
        $frm_hrs_settings->update($_POST);

        if( empty($errors) ){
            $frm_hrs_settings->store();
            $message = __('Settings Saved', 'formidable');
        }
            
        self::display_form($errors, $message);
    }

    public static function route(){
        $action = FrmAppHelper::get_param('action');
        if ( $action == 'process-form' ) {
            return self::process_form();
        } else {
            return self::display_form();
        }
    }
    
    public static function add_scripts() {
        include(FrmHrsAppHelper::plugin_path() .'/views/action-settings/script.php');
    }
    
    public static function migrate_to_2($form) {
        if ( ! isset($form->options['highrise']) || ! $form->options['highrise'] || ! isset($form->options['hrs_list']) || empty($form->options['hrs_list']) ) {
            return;
        }
        
        $frm_version = is_callable('FrmAppHelper::plugin_version') ? FrmAppHelper::plugin_version() : 0;
        
        if ( version_compare($frm_version, '1.07.20', '<=') ) {
            return;
        }
        
        $action_control = FrmFormActionsController::get_form_actions( 'highrise' );
        $orginal_options = $form->options;
        
        $form->options = $form->options['hrs_list'];
        $post_id = $action_control->migrate_to_2($form, 'skip');
        $form->options = $orginal_options;
        
        if ( $post_id ) {
            global $wpdb;
            
            // update form options
            unset($form->options['highrise']);
            unset($form->options['hrs_list']);
            
            $wpdb->update($wpdb->prefix .'frm_forms', array('options' => $form->options), array('id' => $form->id));
            wp_cache_delete( $form->id, 'frm_form');
        }
        
        return $post_id;
    }
    
    /* Start v2.0 Fallback */
    public static function load_form_settings_hooks() {
        $frm_version = is_callable('FrmAppHelper::plugin_version') ? FrmAppHelper::plugin_version() : 0;
        
        if ( version_compare($frm_version, '1.07.20', '>') ) {
            add_action('frm_add_form_option_section', array(__CLASS__, 'add_scripts'));
            return;
        }
        
        // load hooks for < v2.0
        add_action('frm_add_form_settings_section', array(__CLASS__, 'add_options'), 10);
        add_action('wp_ajax_frm_hrs_add_logic_row', array(__CLASS__, 'add_logic_row'));
        add_filter('frm_setup_new_form_vars', array(__CLASS__, 'setup_new_vars'));
        add_filter('frm_setup_edit_form_vars', array(__CLASS__, 'setup_edit_vars'));
        add_filter('frm_form_options_before_update', array(__CLASS__, 'update_options'), 15, 2);
        
    }
    
    public static function add_options($sections){
        $sections['highrise'] = array('class' => 'FrmHrsSettingsController', 'function' => 'options');
        return $sections;
    }
    
    public static function options($values){
		$api = FrmHrsSettings::api();

        $all_fields = array(
            'first_name', 'last_name', 'title', 'company_name', 'email_address', 
            'instant_messenger', 'twitter_account', 'web_address', 'address', 'phone_number'
        );
        
        $list_fields = FrmHrsAppHelper::get_field_details($all_fields);
        
        $custom_fields = $api->getXMLObjectForUrl('/subject_fields.xml');
        if(isset($custom_fields->{'subject-field'})){
            foreach($custom_fields->{'subject-field'} as $c){
                $list_fields[] = array('tag' => (int)$c->id, 'name' => $c->label, 'multi' => false);
                unset($custom_field);
            }
        }
        
        $frm_field = new FrmField();
        $form_fields = $frm_field->getAll("fi.form_id='". $values['id'] ."' and fi.type not in ('break', 'divider', 'html', 'captcha', 'form')", 'field_order');
        unset($frm_field);
        
        $hide_highrise = ($values['highrise']) ? '' : 'style="display:none;"';
        $show_add = $tag_count = array();
        
        include(FrmHrsAppHelper::plugin_path() .'/views/settings/options.php');
    }
    
    public static function add_logic_row(){
        global $wpdb;
            
        $form_id = (int)$_POST['form_id'];
        $meta_name = $_POST['meta_name'];
        $hide_field = '';
        
        $frm_field = new FrmField();
        $form_fields = $frm_field->getAll("fi.form_id = ". $form_id ." and (type in ('select','radio','checkbox','10radio','scale','data') or (type = 'data' and (field_options LIKE '\"data_type\";s:6:\"select\"%' OR field_options LIKE '%\"data_type\";s:5:\"radio\"%' OR field_options LIKE '%\"data_type\";s:8:\"checkbox\"%') ))", " ORDER BY field_order");
        unset($frm_field);

        $values = $wpdb->get_var($wpdb->prepare("SELECT options FROM {$wpdb->prefix}frm_forms WHERE id=%d", $form_id));
        $values = maybe_unserialize($values);
        if(!isset($values['hrs_list']))
            $values['hrs_list'] = array('hide_field' => array(), 'hide_field_cond' => array(), 'hide_opt' => array());
        
        if(!isset($values['hrs_list']['hide_field_cond'][$meta_name]))
            $values['hrs_list']['hide_field_cond'][$meta_name] = '==';
            
        include(FrmHrsAppHelper::plugin_path() .'/views/settings/_logic_row.php');
        
        die();
    }
    
    public static function setup_new_vars($values){
        $defaults = FrmHrsAppHelper::get_default_options();
        foreach ($defaults as $opt => $default){
            $values[$opt] = FrmAppHelper::get_param($opt, $default);
            unset($default);
            unset($opt);
        }
        return $values;
    }
    
    public static function setup_edit_vars($values){
        $defaults = FrmHrsAppHelper::get_default_options();
        foreach ($defaults as $opt => $default){
            if (!isset($values[$opt]))
                $values[$opt] = ($_POST and isset($_POST['options'][$opt])) ? $_POST['options'][$opt] : $default;
            unset($default);
            unset($opt);
        }
        
        if(isset($_POST) and isset($_POST['options']['hrs_list']))
            $values['hrs_list'] = $_POST['options']['hrs_list'];

        return $values;
    }
    
    public static function update_options($options, $values){
        $defaults = FrmHrsAppHelper::get_default_options();
        
        foreach($defaults as $opt => $default){
            $options[$opt] = (isset($values['options'][$opt])) ? $values['options'][$opt] : $default;
            unset($default);
            unset($opt);
        }

        unset($defaults);
        
        return $options;
    }
    /* End < 2.0 fallback */
}