<?php

/*
=====================================================
 UserPhone
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2013 Yuri Salimovskiy
=====================================================
 This software is intended for usage with
 ExpressionEngine CMS, version 2.0 or higher
=====================================================
 File: ext.userphone.php
-----------------------------------------------------
 Purpose: Add and validate user phones via SMS
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'userphone/config.php';

class Userphone_upd {

    var $version = USERPHONE_ADDON_VERSION;
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
    } 
    
    function install() { 
		
		$this->EE->load->dbforge(); 
        
        //----------------------------------------
		// EXP_MODULES
		// The settings column, Ellislab should have put this one in long ago.
		// No need for a seperate preferences table for each module.
		//----------------------------------------
		if ($this->EE->db->field_exists('settings', 'modules') == FALSE)
		{
			$this->EE->dbforge->add_column('modules', array('settings' => array('type' => 'TEXT') ) );
		}
        
        $settings = array(
			'move_to_group'		=> 0,
			'sms_gateway' 		=> 'twilio',
			'sms_tmpl'			=> '{code}',
			'sms_username'		=> '',
			'sms_password'		=> '',
			'sms_api_id'		=> '',
			'sms_from_number'	=> '',
		);

        $data = array( 'module_name' => 'Userphone' , 'module_version' => $this->version, 'has_cp_backend' => 'y', 'has_publish_fields' => 'n', 'settings'=> serialize($settings) ); 
        $this->EE->db->insert('modules', $data); 
        
        $data = array( 'class' => 'Userphone' , 'method' => 'add_phone' ); 
        $this->EE->db->insert('actions', $data); 
        
		$data = array( 'class' => 'Userphone' , 'method' => 'get_new_code' ); 
        $this->EE->db->insert('actions', $data); 
        
        $data = array( 'class' => 'Userphone' , 'method' => 'verify_code' ); 
        $this->EE->db->insert('actions', $data); 
        
        //exp_userphone
        $fields = array(
			'member_id'			=> array('type' => 'INT',		'unsigned' => TRUE),
			'phone'				=> array('type' => 'VARCHAR',	'constraint'=> 25,	'default' => ''),
			'phone_pending'		=> array('type' => 'VARCHAR',	'constraint'=> 25,	'default' => ''),
			'code'				=> array('type' => 'VARCHAR',	'constraint'=> 25,	'default' => '')
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('member_id');
		$this->EE->dbforge->create_table('userphone', TRUE);
        
        return TRUE; 
        
    } 
    
    function uninstall() { 

        $this->EE->load->dbforge(); 
		
		$this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Userphone')); 
        
        $this->EE->db->where('module_id', $query->row('module_id')); 
        $this->EE->db->delete('module_member_groups'); 
        
        $this->EE->db->where('module_name', 'Userphone'); 
        $this->EE->db->delete('modules'); 
        
        $this->EE->db->where('class', 'Userphone'); 
        $this->EE->db->delete('actions'); 
        
        $this->EE->dbforge->drop_table('userphone');
        
        return TRUE; 
    } 
    
    function update($current='') 
	{ 
        return TRUE; 
    } 
	

}
/* END */
?>