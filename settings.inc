<?php
// $Id$
function r_case_study_settings_form($form, $form_state)
{
	$form['emails'] = array(
		'#type' => 'textfield',
		'#title' => t('(Bcc) Notification emails'),
		'#description' => t('Specify emails id for Bcc option of mail system with comma separated'),
		'#size' => 50,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#default_value' => variable_get('case_study_emails', '')
	);
	$form['cc_emails'] = array(
		'#type' => 'textfield',
		'#title' => t('(Cc) Notification emails'),
		'#description' => t('Specify emails id for Cc option of mail system with comma separated'),
		'#size' => 50,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#default_value' => variable_get('case_study_cc_emails', '')
	);
	$form['from_email'] = array(
		'#type' => 'textfield',
		'#title' => t('Outgoing from email address'),
		'#description' => t('Email address to be display in the from field of all outgoing messages'),
		'#size' => 50,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#default_value' => variable_get('case_study_from_email', '')
	);
	$form['extensions']['proposal_abstract_file'] = array(
		'#type' => 'textfield',
		'#title' => t('Allowed file extensions for uploading abstract files in the proposal form'),
		'#description' => t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
		'#size' => 50,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#default_value' => variable_get('proposal_abstract_upload_extensions', '')
	);
	$form['extensions']['proposal_raw_data_file'] = array(
		'#type' => 'textfield',
		'#title' => t('Allowed file extensions for uploading the raw data file in proposal form'),
		'#description' => t('A comma separated list WITHOUT SPACE of file extensions that are permitted to be uploaded on the server'),
		'#size' => 50,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#default_value' => variable_get('proposal_raw_data_upload_extensions', '')
	);
	$form['extensions']['abstract_upload'] = array(
		'#type' => 'textfield',
		'#title' => t('Allowed file extensions for abstract'),
		'#description' => t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
		'#size' => 50,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#default_value' => variable_get('case_study_abstract_upload_extensions', '')
	);
	$form['extensions']['case_study_upload'] = array(
		'#type' => 'textfield',
		'#title' => t('Allowed extensions for project files'),
		'#description' => t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
		'#size' => 50,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#default_value' => variable_get('case_study_project_files_extensions', '')
	);
	
	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Submit')
	);
	return $form;
}
function r_case_study_settings_form_validate($form, &$form_state)
{
	return;
}
function r_case_study_settings_form_submit($form, &$form_state)
{
	variable_set('case_study_emails', $form_state['values']['emails']);
	variable_set('case_study_cc_emails', $form_state['values']['cc_emails']);
	variable_set('case_study_from_email', $form_state['values']['from_email']);
	variable_set('proposal_abstract_upload_extensions', $form_state['values']['proposal_abstract_file']);
	variable_set('proposal_raw_data_upload_extensions', $form_state['values']['proposal_raw_data_file']);
	variable_set('case_study_abstract_upload_extensions', $form_state['values']['abstract_upload']);
	variable_set('case_study_project_files_extensions', $form_state['values']['case_study_upload']);
	drupal_set_message(t('Settings updated'), 'status');
}
