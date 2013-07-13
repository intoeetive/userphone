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
 File: mod.userphone.php
-----------------------------------------------------
 Purpose: Add and validate user phones via SMS
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}


class Userphone {

    var $return_data	= ''; 	

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function __construct()
    {        
    	$this->EE =& get_instance(); 
    	
    	$this->EE->lang->loadfile('userphone');  
    }
    /* END */
    

    function verify()
    {
    	if (in_array($this->EE->session->userdata('group_id'), array(0,2,3)))
    	{
    		return $this->EE->TMPL->no_results();
    	}
    	
    	$this->EE->db->select('phone_pending');
    	$this->EE->db->from('userphone');
		$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
    	$q = $this->EE->db->get();
    	if ($q->num_rows()==0 || $q->row('phone_pending')=='')
    	{
    		return $this->EE->TMPL->no_results();
    	}
    	
		if ($this->EE->TMPL->fetch_param('return')=='')
        {
            $return = $this->EE->functions->fetch_site_index();
        }
        else if ($this->EE->TMPL->fetch_param('return')=='SAME_PAGE')
        {
            $return = $this->EE->functions->fetch_current_uri();
        }
        else if (strpos($this->EE->TMPL->fetch_param('return'), "http://")!==FALSE || strpos($this->EE->TMPL->fetch_param('return'), "https://")!==FALSE)
        {
            $return = $this->EE->TMPL->fetch_param('return');
        }
        else
        {
            $return = $this->EE->functions->create_url($this->EE->TMPL->fetch_param('return'));
        }
        
        $data['hidden_fields']['ACT'] = $this->EE->functions->fetch_action_id('Userphone', 'verify_code');
		$data['hidden_fields']['RET'] = $return;
        $data['hidden_fields']['PRV'] = $this->EE->functions->fetch_current_uri();
        
        if ($this->EE->TMPL->fetch_param('ajax')=='yes') $data['hidden_fields']['ajax'] = 'yes';
        if ($this->EE->TMPL->fetch_param('skip_success_message')=='yes')
        {
            $data['hidden_fields']['skip_success_message'] = 'y';
        }
									      
        $data['id']		= ($this->EE->TMPL->fetch_param('id')!='') ? $this->EE->TMPL->fetch_param('id') : 'userphone_form';
        $data['name']		= ($this->EE->TMPL->fetch_param('name')!='') ? $this->EE->TMPL->fetch_param('name') : 'userphone_form';
        $data['class']		= ($this->EE->TMPL->fetch_param('class')!='') ? $this->EE->TMPL->fetch_param('class') : 'userphone_form';
		
		$tagdata = $this->EE->TMPL->tagdata;
		$tagdata = $this->EE->TMPL->swap_var_single('phone', $q->row('phone_pending'), $tagdata);
		
		$act = $this->EE->db->select('action_id')->from('actions')->where('class', 'Userphone')->where('method', 'get_new_code')->get();
        $request_new_code = trim($this->EE->config->item('site_url'), '/').'/?ACT='.$act->row('action_id').'&RET='.$this->EE->uri->uri_string;
        if ($this->EE->TMPL->fetch_param('ajax')=='yes') $request_new_code .= '&ajax=yes';
		$tagdata = $this->EE->TMPL->swap_var_single('request_new_code', $request_new_code, $tagdata);
		
        $out = $this->EE->functions->form_declaration($data).$tagdata."\n"."</form>";
        
        return $out;
    }
    
    
    
    function verify_code()
    {
		//is pending, or banned?
    	if (in_array($this->EE->session->userdata('group_id'), array(0,2,3)))
    	{
    		if ($this->EE->input->get_post('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('unauthorized_access');
                exit();
            }
			$this->EE->output->show_user_error('general', lang('unauthorized_access'));
    	}
    	
    	if ($this->EE->input->post('code')=='')
    	{
    		if ($this->EE->input->get_post('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('no_code_submitted');
                exit();
            }
			$this->EE->output->show_user_error('general', lang('no_code_submitted'));
    	}
    	
    	$this->EE->db->select('phone_pending');
    	$this->EE->db->from('userphone');
		$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
    	$this->EE->db->where('code', $this->EE->input->post('code'));
    	$q = $this->EE->db->get();
    	
    	if ($q->num_rows()==0)
    	{
    		if ($this->EE->input->get_post('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('code_verification_failed');
                exit();
            }
			$this->EE->output->show_user_error('general', lang('code_verification_failed'));
    	}
    	
    	$data = array(
			'phone'				=> $q->row('phone_pending'),
			'phone_pending'		=> '',
			'code'	=> ''
		);
    	
    	$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
    	$this->EE->db->update('userphone', $data);
    	
    	//move user to group now
    	$query = $this->EE->db->select('settings')->from('modules')->where('module_name', 'Userphone')->limit(1)->get();
        $settings = unserialize($query->row('settings')); 
        
        if ($settings['move_to_group']!=0 && $this->EE->session->userdata('group_id')!=$settings['move_to_group'])
        {
        	$m_data = array('group_id' => $settings['move_to_group']);
        	$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
        	$this->EE->db->update('members', $m_data);
			$this->EE->stats->update_member_stats();
        }
    	
    	//return
    	
		if ($this->EE->input->get_post('ajax')=='yes')
        {
            echo $this->EE->lang->line('phone_verified');
            exit();
        }
        
        $return = ($this->EE->input->get_post('RET')!==false)?$this->EE->input->get_post('RET'):$this->EE->config->item('site_url');
        $site_name = ($this->EE->config->item('site_name') == '') ? $this->EE->lang->line('back') : stripslashes($this->EE->config->item('site_name'));
        
        if ($this->EE->input->get_post('skip_success_message')=='y')
        {
        	$this->EE->functions->redirect($return);
        }
            
        $data = array(	'title' 	=> $this->EE->lang->line('success'),
        				'heading'	=> $this->EE->lang->line('success'),
        				'content'	=> $this->EE->lang->line('phone_verified'),
        				'redirect'	=> $return,
        				'link'		=> array($return, $site_name),
                        'rate'		=> 3
        			 );
			
		$this->EE->output->show_message($data);
		
    }
    
    
    
    function add()
    {
    	if (in_array($this->EE->session->userdata('group_id'), array(0,2,3)))
    	{
    		return $this->EE->TMPL->no_results();
    	}
    	
    	$tagdata = $this->EE->TMPL->tagdata;
    	
    	$this->EE->db->select('phone');
    	$this->EE->db->from('userphone');
		$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
    	$q = $this->EE->db->get();
    	if ($q->num_rows()>0)
    	{
    		$tagdata = $this->EE->TMPL->swap_var_single('phone', $q->row('phone'), $tagdata);
    	}
    	else
    	{
    		$tagdata = $this->EE->TMPL->swap_var_single('phone', '', $tagdata);
    	}
    	
		if ($this->EE->TMPL->fetch_param('return')=='')
        {
            $return = $this->EE->functions->fetch_site_index();
        }
        else if ($this->EE->TMPL->fetch_param('return')=='SAME_PAGE')
        {
            $return = $this->EE->functions->fetch_current_uri();
        }
        else if (strpos($this->EE->TMPL->fetch_param('return'), "http://")!==FALSE || strpos($this->EE->TMPL->fetch_param('return'), "https://")!==FALSE)
        {
            $return = $this->EE->TMPL->fetch_param('return');
        }
        else
        {
            $return = $this->EE->functions->create_url($this->EE->TMPL->fetch_param('return'));
        }
        
        $data['hidden_fields']['ACT'] = $this->EE->functions->fetch_action_id('Userphone', 'add_phone');
		$data['hidden_fields']['RET'] = $return;
        $data['hidden_fields']['PRV'] = $this->EE->functions->fetch_current_uri();
        
        if ($this->EE->TMPL->fetch_param('ajax')=='yes') $data['hidden_fields']['ajax'] = 'yes';
        if ($this->EE->TMPL->fetch_param('skip_success_message')=='yes')
        {
            $data['hidden_fields']['skip_success_message'] = 'y';
        }
									      
        $data['id']		= ($this->EE->TMPL->fetch_param('id')!='') ? $this->EE->TMPL->fetch_param('id') : 'userphone_form';
        $data['name']		= ($this->EE->TMPL->fetch_param('name')!='') ? $this->EE->TMPL->fetch_param('name') : 'userphone_form';
        $data['class']		= ($this->EE->TMPL->fetch_param('class')!='') ? $this->EE->TMPL->fetch_param('class') : 'userphone_form';
		
        $out = $this->EE->functions->form_declaration($data).$tagdata."\n"."</form>";
        
        return $out;
    }
    
    
    
    function add_phone()
    {
		//is pending, or banned?
    	if (in_array($this->EE->session->userdata('group_id'), array(0,2,3)))
    	{
    		if ($this->EE->input->get_post('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('unauthorized_access');
                exit();
            }
			$this->EE->output->show_user_error('general', lang('unauthorized_access'));
    	}
    	
    	if ($this->EE->input->post('phone')=='')
    	{
    		if ($this->EE->input->get_post('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('no_phone_submitted');
                exit();
            }
			$this->EE->output->show_user_error('general', lang('no_phone_submitted'));
    	}
    	
    	$this->EE->db->select('phone');
    	$this->EE->db->from('userphone');
		$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
    	$q = $this->EE->db->get();
    	
    	$update = false;
    	
    	if ($q->num_rows()>0)
    	{
    		if ($q->row('phone') == $this->EE->input->post('phone'))
    		{
    			if ($this->EE->input->get_post('ajax')=='yes')
	            {
	                echo lang('error').": ".$this->EE->lang->line('phone_is_yours');
	                exit();
	            }
				$this->EE->output->show_user_error('general', lang('phone_is_yours'));
	    		}
			$update = true;
    	}
    	
    	$data = array(
			'phone_pending'		=> $this->EE->input->post('phone'),
			'code'				=> $this->_send_code($this->EE->input->post('phone'))
		);
		
		if ($data['code']==false)
		{
			if ($this->EE->input->get_post('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('cannot_send_sms');
                exit();
            }
			$this->EE->output->show_user_error('general', lang('cannot_send_sms'));
		}
    	
    	if ($update==true)
    	{
	    	$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
	    	$this->EE->db->update('userphone', $data);
    	}
    	else
    	{
    		$data['member_id'] = $this->EE->session->userdata('member_id');
	    	$this->EE->db->insert('userphone', $data);
    	}
    	
    	//return
    	
		if ($this->EE->input->get_post('ajax')=='yes')
        {
            echo $this->EE->lang->line('phone_added_please_verify');
            exit();
        }
        
        $return = ($this->EE->input->get_post('RET')!==false)?$this->EE->input->get_post('RET'):$this->EE->config->item('site_url');
        $site_name = ($this->EE->config->item('site_name') == '') ? $this->EE->lang->line('back') : stripslashes($this->EE->config->item('site_name'));
        
        if ($this->EE->input->get_post('skip_success_message')=='y')
        {
        	$this->EE->functions->redirect($return);
        }
            
        $data = array(	'title' 	=> $this->EE->lang->line('success'),
        				'heading'	=> $this->EE->lang->line('success'),
        				'content'	=> $this->EE->lang->line('phone_added_please_verify'),
        				'redirect'	=> $return,
        				'link'		=> array($return, $site_name),
                        'rate'		=> 3
        			 );
			
		$this->EE->output->show_message($data);
		
    }
    
    
    function get_new_code()
    {
    	//is pending, or banned?
    	if (in_array($this->EE->session->userdata('group_id'), array(0,2,3)))
    	{
    		if ($this->EE->input->get('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('unauthorized_access');
                exit();
            }
			$this->EE->output->show_user_error('general', lang('unauthorized_access'));
    	}
    	
    	$this->EE->db->select('phone_pending');
    	$this->EE->db->from('userphone');
		$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
    	$q = $this->EE->db->get();
    	  	
    	if ($q->num_rows()==0 || $q->row('phone_pending')=='')
    	{
    		if ($this->EE->input->get_post('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('no_phone_check_pending');
                exit();
            }
			$this->EE->output->show_user_error('general', lang('no_phone_check_pending'));
    	}

    	$data = array(
			'code'				=> $this->_send_code($q->row('phone_pending'))
		);
		
		if ($data['code']==false)
		{
			if ($this->EE->input->get_post('ajax')=='yes')
            {
                echo lang('error').": ".$this->EE->lang->line('cannot_send_sms');
                exit();
            }
			$this->EE->output->show_user_error('general', lang('cannot_send_sms'));
		}
    	
    	$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
    	$this->EE->db->update('userphone', $data);
    	
    	//return
    	
		if ($this->EE->input->get('ajax')=='yes')
        {
            echo $this->EE->lang->line('phone_added_please_verify');
            exit();
        }
        
        $return = ($this->EE->input->get('RET')!==false)?$this->EE->input->get_post('RET'):$this->EE->config->item('site_url');
        $site_name = ($this->EE->config->item('site_name') == '') ? $this->EE->lang->line('back') : stripslashes($this->EE->config->item('site_name'));
        
        if ($this->EE->input->get('skip_success_message')=='y')
        {
        	$this->EE->functions->redirect($return);
        }
            
        $data = array(	'title' 	=> $this->EE->lang->line('success'),
        				'heading'	=> $this->EE->lang->line('success'),
        				'content'	=> $this->EE->lang->line('phone_added_please_verify'),
        				'redirect'	=> $return,
        				'link'		=> array($return, $site_name),
                        'rate'		=> 3
        			 );
			
		$this->EE->output->show_message($data);
    }
    
    
    function _send_code($phone)
    {
    	if ($phone=='') return false;
		
		$query = $this->EE->db->select('settings')->from('modules')->where('module_name', 'Userphone')->limit(1)->get();
        $settings = unserialize($query->row('settings')); 
        
        $gateway = $settings['sms_gateway'];
        
        $this->EE->load->library($gateway, $settings);
        
        $code = $this->_generate_code();
        
        $this->EE->load->library('template');
        
        $message = $this->EE->template->swap_var_single('code', $code, $settings['sms_tmpl']);
        
        $sent = $this->EE->$gateway->send($phone, $message);
        
        if ($sent!=false)
        {
        	return $code;
        }
        
        return false;
        
    }
    
    
    function _generate_code($length = 10, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
	{
        // Length of character list
        $chars_length = (strlen($chars) - 1);
    
        // Start our string
        $string = $chars[rand(0, $chars_length)];
        
        // Generate random string
        for ($i = 1; $i < $length; $i++)
        {
            // Grab a random character from our list
            $r = $chars[rand(0, $chars_length)];
            
            // Make sure the same two characters don't appear next to each other
            //if ($r != $string{$i - 1}) $string .=  $r;
            $string .=  $r;
        }
        
        // Return the string
        return $string;
    }



    function phone()
    {
    	if ($this->EE->TMPL->fetch_param('member_id')=='CURRENT_USER' || ($this->EE->TMPL->fetch_param('member_id')=='' && $this->EE->TMPL->fetch_param('username')==''))
    	{
    		$member_id = $this->EE->session->userdata('member_id');
    		if ($member_id==0) return $this->EE->TMPL->no_results();
    	}
    	else if ($this->EE->TMPL->fetch_param('username')=='')
    	{
    		$member_id = $this->EE->TMPL->fetch_param('member_id');
    	}
    	else
    	{
    		$username = $this->EE->TMPL->fetch_param('username');
    	}
		
		$this->EE->db->select('phone')
    				->from('userphone');
		if (isset($member_id))
		{
    		$this->EE->db->where('member_id', $member_id);
		}
		else
		{
			$this->EE->db->join('members', 'members.member_id=userphone.member_id', 'left');
			$this->EE->db->where('username', $username);
		}
		$q = $this->EE->db->get();
		if ($q->num_rows()==0)
		{
			return $this->EE->TMPL->no_results();
		}
		
		$this->return_data = $q->row('phone');
		
		return $this->return_data;
    }
    

    

}
/* END */
?>