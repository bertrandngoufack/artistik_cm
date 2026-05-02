<?php
 
class FrmHrsAppHelper{
    public static function plugin_path(){
        return dirname(dirname(__FILE__));
    }
    
    public static function get_default_options(){
        return array(
            'highrise' => 0, 
            'hrs_list' => array()
        );
    }
    
    public static function get_field_details($all_fields){
        $multi_fields = array('email_address', 'instant_messenger', 'web_address', 'address', 'phone_number');
		$locations = array( 'Work', 'Home', 'Other' );

        $list_fields = array();
        foreach($all_fields as $list_field){
            $f_array = array('tag' => $list_field, 'name' => ucwords(str_replace('_', ' ', $list_field)));
            $f_array['multi'] = (in_array($list_field, $multi_fields)) ? true : false;
            switch($list_field){
                case 'email_address':
                    $f_array['name'] = __('Email', 'frmhrs');
					$f_array['location'] = $locations;
				break;
                case 'address':
                    $f_array['location'] = $locations;
					$f_array['defaults'] = array( 'street' => '', 'city' => '', 'state' => '', 'zip' => '', 'country' => '' );
					$f_array['fields'] = array(
						'street' => array(
							'placeholder' => __( 'Street', 'formidable-highrise' ),
							'size' => '100%',
						),
						'city' => array(
							'placeholder' => __( 'City', 'formidable-highrise' ),
							'size' => '30%',
						),
						'state' => array(
							'placeholder' => __( 'State', 'formidable-highrise' ),
							'size' => '30%',
						),
						'zip' => array(
							'placeholder' => __( 'Zip', 'formidable-highrise' ),
							'size' => '30%',
						),
						'country' => array(
							'placeholder' => __( 'Country', 'formidable-highrise' ),
							'size' => '30%',
						),
					);
                break;
                case 'instant_messenger':
                    $f_array['protocol'] = array('AIM', 'MSN', 'ICQ', 'Jabber', 'Yahoo', 'Skype', 'QQ', 'Sametime', 'Gadu-Gadu', 'Google Talk', 'Other');
                case 'web_address':
                    $f_array['location'] = array('Work', 'Personal', 'Other');
                    $f_array['name'] = __('Website', 'frmhrs');
                break;
                case 'phone_number':
                    $f_array['name'] = __('Phone', 'frmhrs');
                    $f_array['location'] = array('Work', 'Mobile', 'Fax', 'Pager', 'Home', 'Skype', 'Other');
                break;
                case 'twitter_account':
                    $f_array['name'] = 'Twitter';
                break;
                case 'company_name':
                    $f_array['name'] = __('Company', 'frmhrs');
                break;
            }
            
            
            $list_fields[] = $f_array;
            unset($list_field);
        }
        
        unset($multi_fields);
        return $list_fields;
    }

}