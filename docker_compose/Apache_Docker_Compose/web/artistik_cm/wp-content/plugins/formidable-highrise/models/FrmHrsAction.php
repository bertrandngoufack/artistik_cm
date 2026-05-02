<?php

class FrmHrsAction extends FrmFormAction {

	function __construct() {
		$action_ops = array(
		    'classes'   => 'frm_highrise_icon frm_icon_font',
            'limit'     => 99,
            'active'    => true,
            'priority'  => 30,
            'event'     => array('create'),
		);
		
	    $this->FrmFormAction('highrise', __('Add to Highrise', 'formidable'), $action_ops);
	}

	function form( $form_action, $args = array() ) {
	    extract($args);
	    
	    global $wpdb;

        $api = FrmHrsSettings::api();

        $all_fields = self::get_defaults();
        unset($all_fields['tags'], $all_fields['background']);
        
        $list_fields = FrmHrsAppHelper::get_field_details(array_keys($all_fields));
        unset($all_fields);
        
        $contact_heading = '<h3 style="clear:both">'. __('Contact Information', 'frmhrs') .'</h3>';
        $social_heading = '<h3 style="clear:both">'. __('Social Networks', 'frmhrs') .'</h3>';
        
        $custom_fields = $api->getXMLObjectForUrl('/subject_fields.xml');
        if ( isset($custom_fields->{'subject-field'}) ) {
            $custom_heading = '<h3>'. __('Custom Fields', 'frmhrs') .'</h3>';
            foreach ( $custom_fields->{'subject-field'} as $c ) {
                $list_fields[] = array(
                    'tag'   => (int) $c->id,
                    'name'  => $c->label,
                    'multi' => false,
                    'custom' => true,
                );
                unset($custom_field);
            }
        }
        
        $show_add = $tag_count = array();
        
        $action_control = $this;
	    $options = $form_action->post_content;
	    
	    include(FrmHrsAppHelper::plugin_path() .'/views/action-settings/options.php');
	}
	
	function get_defaults() {
	    return array(
	        'first_name'    => array(),
            'last_name'     => array(),
            'title'         => array(),
            'company_name'  => array(),
            'phone_number'  => array(),
            'email_address' => array(),
            'instant_messenger' => array(),
            'web_address'   => array(),
            'address'       => array(),
            'twitter_account' => '',
            
            // + custom fields
            'tags'          => '',
	        'background'    => 'Inserted from '. get_option('blogname'),
	    );
	}
	
	function get_switch_fields() {
	    return array(
            'first_name'    => array('tag'),
            'last_name'     => array('tag'),
            'title'         => array('tag'),
            'company_name'  => array('tag'),
            'email_address' => array('tag'),
            'instant_messenger' => array('tag'),
            'twitter_account' => array('tag'),
            'web_address'   => array('tag'),
            'address'       => array('tag'),
            'phone_number'  => array('tag'),
        );
	}

	public function migrate_values($action, $form) {
	    if ( ! empty($form->options['hide_field']) ) {
    	    $action->post_content['conditions']['send_stop'] = 'send';
    	    foreach ( $form->options['hide_field'] as $k => $field_id ) {
                $action->post_content['conditions'][] = array(
                    'hide_field'        => $field_id,
                    'hide_field_cond'   => isset($form->options['hide_field_cond'][$k]) ? $form->options['hide_field_cond'][$k] : '==',
                    'hide_opt'          => isset($form->options['hide_opt'][$k]) ? $form->options['hide_opt'][$k] : '',
                );
    	    }
    	    unset($action->post_content['hide_field'], $action->post_content['hide_field_cond']);
    	    unset($action->post_content['hide_opt']);
        }
        $action->post_content['event'] = array('create');
        
        // fill in custom fields
        foreach ( $form->options as $k => $v ) {
            if ( !isset($action->post_content[$k]) ) {
                $action->post_content[$k] = $v;
            }
            
            if ( is_array($v) && isset($v['tag']) ) {
                if ( is_numeric($v['tag']) ){
                    $action->post_content[$k]['tag'] = '['. $v['tag'] .']';
                } else if ( is_array($v['tag']) ) {
                    foreach ( $v['tag'] as $tag_key => $tag ) {
                        $action->post_content[$k]['tag'][$tag_key] = '['. $tag .']';
                    }
                }
            }
        }
        
	    return $action;
	}
}
