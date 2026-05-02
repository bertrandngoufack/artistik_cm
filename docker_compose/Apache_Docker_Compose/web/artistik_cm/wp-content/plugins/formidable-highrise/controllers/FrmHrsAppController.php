<?php

class FrmHrsAppController{
    function __construct(){
        add_action('admin_init', 'FrmHrsAppController::include_updater', 1);
        add_action('frm_entry_form', 'FrmHrsAppController::hidden_form_fields');
        add_action('frm_after_create_entry', 'FrmHrsAppController::send_to_highrise', 25, 2);
        
        // 2.0 hooks
        add_action('frm_registered_form_actions', array(__CLASS__, 'register_actions') );
        add_action('frm_trigger_highrise_action', array(__CLASS__, 'trigger_highrise'), 10, 3);
    }
    
    public static function include_updater(){
		if ( class_exists( 'FrmAddon' ) ) {
			include_once( FrmHrsAppHelper::plugin_path() . '/models/FrmHrsUpdate.php' );
			FrmHrsUpdate::load_hooks();
		}
    }
    
    public static function register_actions($actions) {
        $actions['highrise'] = 'FrmHrsAction';
        
        include_once(FrmHrsAppHelper::plugin_path() . '/models/FrmHrsAction.php');
        
        return $actions;
    }
    
    public static function hidden_form_fields($form){
        if(isset($form->options['highrise']) and $form->options['highrise'] and isset($form->options['hrs_list']) and is_array($form->options['hrs_list']))
            echo '<input type="hidden" name="frm_highrise" value="1"/>'."\n";
    }
    
    public static function send_to_highrise($entry_id, $form_id) {
        if ( ! isset($_POST) || ! isset($_POST['frm_highrise']) ) {
            return;
        }
        
        global $wpdb;
        
        $form_options = $wpdb->get_var($wpdb->prepare("SELECT options FROM {$wpdb->prefix}frm_forms WHERE id=%d", $form_id));
        $form_options = maybe_unserialize($form_options);
        if ( ! isset($form_options['highrise']) || ! $form_options['highrise'] ) {
            return;
        }
        
        //check conditions
        $subscribe = true;
        if ( isset($form_options['hrs_list']['hide_field']) && is_array($form_options['hrs_list']['hide_field']) && class_exists('FrmProFieldsHelper') ) {
            //for now we are assuming that if all conditions are met, then the user will be subscribed
            foreach ( $form_options['hrs_list']['hide_field'] as $hide_key => $hide_field){
                if ( ! $subscribe ) {
                    break;
                }
                
                if ( empty($hide_field) ) {
                    continue;
                }
                
                $observed_value = (isset($_POST['item_meta'][$hide_field])) ? $_POST['item_meta'][$hide_field] : '';
                    
                $subscribe = FrmProFieldsHelper::value_meets_condition($observed_value, $form_options['hrs_list']['hide_field_cond'][$hide_key], $form_options['hrs_list']['hide_opt'][$hide_key]);
                    
            }
        }
            
        if ( ! $subscribe ) { //don't subscribe if conditional logic is not met
            return;
        }
        
        $form = array(
            'id'    => $form_id,
            'options' => $form_options,
        );
        
        $action = array(
            'post_content' => $form_options['hrs_list'],
        );
        
        return self::save_in_highrise( (object) $action, $entry_id, (object) $form );
    }
    
    public static function trigger_highrise($action, $entry, $form) {
        return self::save_in_highrise($action, $entry, $form);
    }
    
    public static function save_in_highrise($action, $entry, $form) {        
        global $wpdb;

        if ( is_numeric($entry) ) {
            $entry_id = $entry;
        } else {
            $entry_id = $entry->id;
        }
        
        $api = FrmHrsSettings::api();

		// enabling highrise debug
		//$api->debug = true;
		
        $person = new HighrisePerson($api);
        foreach ( $action->post_content as $field_tag => $field_info ) {
            if ( in_array($field_tag, array('hide_field', 'hide_field_cond', 'hide_opt', 'conditions', 'event')) ) {
                continue;
            }

			self::prepare_field_info( $field_info, compact( 'form', 'entry', 'entry_id', 'field_tag' ) );

            switch ( $field_tag ) {
                case 'first_name':
                    $person->setFirstName($field_info['tag']);
                break;
                case 'last_name':
                    $person->setLastName($field_info['tag']);
                break;
                case 'title':
                    $person->setTitle( preg_replace( "/\r|\n/", ' ', strip_tags($field_info['tag']) ) );
                break;
                case 'company_name':
                    $person->setCompanyName($field_info['tag']);
                break;
                case 'email_address':
                    foreach ( $field_info['tag'] as $tkey => $field_val ) {

						// Check for duplicate first
						try {
							$possible_duplicates = $api->findPeopleByEmail($field_val);
							foreach ( $possible_duplicates as $duplicate ) {
								if ( $duplicate && $duplicate->id ) {
									$person->addTag('duplicate_check');
									break;
								}
							}
							unset( $possible_duplicates );
						} catch ( Exception $e ) {
							//print_r($e);
						}

						$person->addEmailAddress( $field_val, $field_info['location'][ $tkey ] );
                    }
                break;
                case 'instant_messenger':
                    foreach ( $field_info['tag'] as $tkey => $field_val ) {
                        $person->addInstantMessenger($field_info['protocol'][$tkey], $field_val, $field_info['location'][$tkey]);
                    }
                break;
                case 'twitter_account':
                    if ( is_array($field_info['tag']) ) {
                        foreach ( $field_info['tag'] as $tkey => $field_val ) {
                            $person->addTwitterAccount($field_val, $field_info['location'][$tkey]);
                        }
                    } else if ( ! empty($field_info['tag']) ) {
                        $person->addTwitterAccount($field_info['tag']);
                    }

                break;
                case 'web_address':
                    foreach ( $field_info['tag'] as $tkey => $field_val ) {
                        $person->addWebAddress($field_val, $field_info['location'][$tkey]);
                    }
                break;
                case 'address':
                    foreach ( $field_info['tag'] as $tkey => $field_val ) {
						if ( ! is_array( $field_val ) ) {
							// for reverse compatability
							$field_val = array( 'street' => $field_val );
						}
						$address = new HighriseAddress();
						foreach ( $field_val as $key => $val ) {
							$function_name = 'set' . ucfirst( $key );
							$address->$function_name( $val );
						}

                        $address->setLocation($field_info['location'][$tkey]);
                        $person->addAddress($address);
                        unset($address);
                    }
                break;
                case 'phone_number':
                    foreach ( $field_info['tag'] as $tkey => $field_val ) {
                        $person->addPhoneNumber($field_val, $field_info['location'][$tkey]);
                    }
                break;
                case 'tags':
                    if ( ! empty($field_info) ) {
                        $tags = explode(',', $field_info);
                        foreach ( $tags as $t ) {
                            $person->addTag(trim($t));
                        }
                    }
                break;
                case 'background':
                    $person->setBackground($field_info);
                break;
                default:
                    //custom fields
					if ( ! empty( $field_info ) ) {
						if ( is_array( $field_info ) ) {
							$field_info = implode( ', ', $field_info );
						}
						$person->addCustomField( $field_tag, $field_info );
					}
                break;
            }

			unset( $field_info );
        }
        
        try {
            $person->save();
        } catch ( Exception $e ) {
            //print_r($e);
        }

    }

	private static function prepare_field_info( &$field_info, $args ) {
		if ( isset( $field_info['tag'] ) ) {
			if ( is_array( $field_info['tag'] ) ) {
				self::switch_shortcodes_to_values( $field_info['tag'], $args );
			} else {
				$field_info['tag'] = self::get_single_value_from_shortcode( $field_info['tag'], $args );
			}
		} else if ( ! is_array( $field_info ) && '' != $field_info ) {
			self::replace_shortcodes( $field_info, $args );
		} else if ( is_array( $field_info ) ) {
			foreach ( $field_info as $tkey => $field_val ) {
				self::replace_shortcodes( $field_val, $args );
				$field_info[ $tkey ] = $field_val;
			}
		}
	}

	private static function switch_shortcodes_to_values( &$field_tags, $args ) {
		$tags = $field_tags;
		foreach ( $tags as $tkey => $field_val ) {
			$field_val = self::prepare_shortcode_value( $field_val, $args );

			if ( '' == $field_val ) {
				// remove empty values
				unset( $field_tags[ $tkey ] );
			} else {
				$field_tags[ $tkey ] = $field_val;
			}

			unset( $tkey, $field_val );
		}
	}

	private static function prepare_shortcode_value( $field_val, $args ) {
		if ( is_array( $field_val ) ) {
			foreach ( $field_val as $key => $val ) {
				$field_val[ $key ] = self::get_single_value_from_shortcode( $val, $args );
			}
		} else {
			$field_val = self::get_single_value_from_shortcode( $field_val, $args );
		}

		return $field_val;
	}

	private static function get_single_value_from_shortcode( $field_val, $args ) {
		$field_val = trim( $field_val );
		if ( '' == $field_val ) {
			return $field_val;
		}

		if ( is_numeric( $field_val ) ) {
			$field_val = self::get_field_value( $field_val, $args['field_tag'], $args['entry_id'] );
		} else {
			if ( ! strpos( $field_val, ' show=') && in_array( $args['field_tag'], array( 'first_name', 'last_name', 'email_address' ) ) ) {
				$shortcode_value = ( $args['field_tag'] == 'email_address' ) ? 'user_email' : $args['field_tag'];
				$field_val = str_replace( ']', ' show='. $shortcode_value .']', $field_val );
			}

			self::replace_shortcodes( $field_val, $args );
		}

		return $field_val;
	}

	private static function replace_shortcodes( &$field_val, $args ) {
		$field_val = apply_filters( 'frm_content', $field_val, $args['form'], $args['entry'] );
		$field_val = do_shortcode( $field_val );
	}

    private static function get_field_value($field_id, $field_tag, $entry_id){
        $val = (isset($_POST['item_meta'][$field_id])) ? $_POST['item_meta'][$field_id] : '';
        if(!is_numeric($val))
            return $val;
        
        $frm_field = new FrmField();
        $field = $frm_field->getOne($field_id);
        unset($frm_field);
        
        if($field->type == 'user_id'){
            $user_data = get_userdata($val);
            if($field_tag == 'email_address')
                $val = $user_data->user_email;
            else if($field_tag == 'first_name')
                $val = $user_data->first_name;
            else if($field_tag == 'last_name')
                $val = $user_data->last_name;
		} else {
			$atts = array( 'type' => $field->type, 'truncate' => false, 'entry_id' => $entry_id );
			if ( is_callable('FrmEntriesHelper::display_value') ) {
				$val = FrmEntriesHelper::display_value( $val, $field, $atts );
			} else if ( is_callable('FrmProEntryMetaHelper::display_value') ) {
				$val = FrmProEntryMetaHelper::display_value( $val, $field, $atts );
			}
		}
        
        return $val;
    }

}