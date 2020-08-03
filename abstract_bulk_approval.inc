<?php

function r_case_study_abstract_bulk_approval_form($form, &$form_state)
{
	$options_first = _bulk_list_of_case_study_project();
	$selected = isset($form_state['values']['case_study_project']) ? $form_state['values']['case_study_project'] : key($options_first);
	$form = array();
	$form['case_study_project'] = array(
		'#type' => 'select',
		'#title' => t('Title of the Case Study'),
		'#options' => _bulk_list_of_case_study_project(),
		'#default_value' => $selected,
		'#ajax' => array(
			
			'callback' => 'ajax_bulk_case_study_abstract_details_callback'
		),
		'#suffix' => '<div id="ajax_selected_case_study"></div><div id="ajax_selected_case_study_pdf"></div>'
	);
	$form['case_study_actions'] = array(
		'#type' => 'select',
		'#title' => t('Please select action for Case Study'),
		'#options' => _bulk_list_case_study_actions(),
		'#default_value' => 0,
		'#prefix' => '<div id="ajax_selected_case_study_action" style="color:red;">',
		'#suffix' => '</div>',
		'#states' => array(
			'invisible' => array(
				':input[name="case_study_project"]' => array(
					'value' => 0
				)
			)
		)
	);
	$form['message'] = array(
		'#type' => 'textarea',
		'#title' => t('Please specify the reason for marking resubmit/disapproval'),
		'#prefix' => '<div id= "message_submit">',
		'#states' => array(
			'visible' => array(
				array(
					':input[name="case_study_actions"]' => array(
						'value' => 3
					)
				),
				'or',
				array(
					':input[name="case_study_actions"]' => array(
						'value' => 2
					)
				)
			)
		)
	);
	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Submit'),
	);
	return $form;
}
function ajax_bulk_case_study_abstract_details_callback($form, $form_state)
{
	$commands = array();
	$case_study_project_default_value = $form_state['values']['case_study_project'];
	if ($case_study_project_default_value != 0)
	{
		$commands[] = ajax_command_html('#ajax_selected_case_study', _case_study_details($case_study_project_default_value));
		$form['case_study_actions']['#options'] = _bulk_list_case_study_actions();
		$commands[] = ajax_command_replace('#ajax_selected_case_study_action', drupal_render($form['case_study_actions']));
	} //$case_study_project_default_value != 0
	else
	{
		$commands[] = ajax_command_html('#ajax_selected_case_study', '');
		$commands[] = ajax_command_data('#ajax_selected_case_study', 'form_state_value_select', $form_state['values']['case_study_project']);
	}
	return array(
		'#type' => 'ajax',
		'#commands' => $commands
	);
}
/************************************************************/
function r_case_study_abstract_bulk_approval_form_submit($form, &$form_state)
{
	global $user;
	$msg = '';
	$root_path = r_case_study_path();
	//var_dump($form_state['values']);die;
	if ($form_state['clicked_button']['#value'] == 'Submit')
	{
		if ($form_state['values']['case_study_project'])
		// case_study_abstract_del_lab_pdf($form_state['values']['case_study_project']);
			if (user_access('Case Study bulk manage abstract'))
			{
				$query = db_select('case_study_proposal');
				$query->fields('case_study_proposal');
				$query->condition('id', $form_state['values']['case_study_project']);
				$user_query = $query->execute();
				$user_info = $user_query->fetchObject();
				$user_data = user_load($user_info->uid);
				if ($form_state['values']['case_study_actions'] == 1)
				{
					// approving entire project //
					$query = db_select('case_study_submitted_abstracts');
					$query->fields('case_study_submitted_abstracts');
					$query->condition('proposal_id', $form_state['values']['case_study_project']);
					$abstracts_q = $query->execute();
					$experiment_list = '';
					while ($abstract_data = $abstracts_q->fetchObject())
					{
						db_query("UPDATE {case_study_submitted_abstracts} SET abstract_approval_status = 1, is_submitted = 1, approver_uid = :approver_uid WHERE id = :id", array(
							':approver_uid' => $user->uid,
							':id' => $abstract_data->id
						));
						db_query("UPDATE {case_study_submitted_abstracts_file} SET file_approval_status = 1, approvar_uid = :approver_uid WHERE submitted_abstract_id = :submitted_abstract_id", array(
							':approver_uid' => $user->uid,
							':submitted_abstract_id' => $abstract_data->id
						));
					} //$abstract_data = $abstracts_q->fetchObject()
					drupal_goto('case-study-project/manage-proposal/all');
					drupal_set_message(t('Approved Case Study.'), 'status');
					// email 
					$email_subject = t('[!site_name][Case Study] Your uploaded Case Study have been approved', array(
						'!site_name' => variable_get('site_name', '')
					));
					$email_body = array(
						0 => t('

Dear !user_name,

Congratulations!
Your simulation and abstract for Power Systems Simulation Project at FOSSEE with the following details have been approved.

Full Name: ' . $user_info->name_title . ' ' . $user_info->contributor_name . '
Email : ' . $user_data->mail . '
University/Institute : ' . $user_info->university . '
City : ' . $user_info->city . '

Project Title  : ' . $user_info->project_title . '
Description of the simulation: ' . $user_info->description .'

Kindly send us the internship forms as early as possible for processing your honorarium on time. In case you have already sent these forms, please share the the consignment number or tracking id with us.

Note: It will take upto 30 days from the time we receive your forms, to process your honorarium.


Best Wishes,

!site_name Power Systems Team,
FOSSEE,IIT Bombay', array(
							'!site_name' => variable_get('site_name', ''),
							'!user_name' => $user_data->name
						))
					);
					/** sending email when everything done **/
					$email_to = $user_data->mail;
					$from = variable_get('case_study_from_email', '');
					$bcc = variable_get('case_study_emails', '');
					$cc = variable_get('case_study_cc_emails', '');
					$params['standard']['subject'] = $email_subject;
					$params['standard']['body'] = $email_body;
					$params['standard']['headers'] = array(
						'From' => $from,
						'MIME-Version' => '1.0',
						'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
						'Content-Transfer-Encoding' => '8Bit',
						'X-Mailer' => 'Drupal',
						'Cc' => $cc,
						'Bcc' => $bcc
					);
					if (!drupal_mail('case_study', 'standard', $email_to, language_default(), $params, $from, TRUE))
					{
						$msg = drupal_set_message('Error sending email message.', 'error');
					} //!drupal_mail('case_study', 'standard', $email_to, language_default(), $params, $from, TRUE)
				} //$form_state['values']['case_study_actions'] == 1
				elseif ($form_state['values']['case_study_actions'] == 2)
				{
					if (strlen(trim($form_state['values']['message'])) <= 30)
					{
						form_set_error('message', t(''));
						$msg = drupal_set_message("Please mention the reason for marking resubmit. Minimum 30 character required", 'error');
						return $msg;
					}
					//pending review entire project 
					$query = db_select('case_study_submitted_abstracts');
					$query->fields('case_study_submitted_abstracts');
					$query->condition('proposal_id', $form_state['values']['case_study_project']);
					$abstracts_q = $query->execute();
					$experiment_list = '';
					while ($abstract_data = $abstracts_q->fetchObject())
					{
						db_query("UPDATE {case_study_submitted_abstracts} SET abstract_approval_status = 0, is_submitted = 0, approver_uid = :approver_uid WHERE id = :id", array(
							':approver_uid' => $user->uid,
							':id' => $abstract_data->id
						));
						db_query("UPDATE {case_study_proposal} SET is_submitted = 0, approver_uid = :approver_uid WHERE id = :id", array(
							':approver_uid' => $user->uid,
							':id' => $abstract_data->proposal_id
						));
						db_query("UPDATE {case_study_submitted_abstracts_file} SET file_approval_status = 0, approvar_uid = :approver_uid WHERE submitted_abstract_id = :submitted_abstract_id", array(
							':approver_uid' => $user->uid,
							':submitted_abstract_id' => $abstract_data->id
						));
					} //$abstract_data = $abstracts_q->fetchObject()
					drupal_set_message(t('Resubmit the project files'), 'status');
					// email 
					$email_subject = t('[!site_name][Case Study] Your uploaded Case Study have been marked as pending', array(
						'!site_name' => variable_get('site_name', '')
					));
					$email_body = array(
						0 => t('

Dear !user_name,

Kindly resubmit the project files for the project: ' . $user_info->project_title . '.
Description of the simulation: ' . $user_info->description . '

Reason: ' . $form_state['values']['message'] . '

Looking forward for the re-submission from you with the above suggested changes.

Best Wishes,

!site_name Power Systems Team,
FOSSEE,IIT Bombay', array(
							'!site_name' => variable_get('site_name', ''),
							'!user_name' => $user_data->name
						))
					);
					/** sending email when everything done **/
					$email_to = $user_data->mail;
					$from = variable_get('case_study_from_email', '');
					$bcc = variable_get('case_study_emails', '');
					$cc = variable_get('case_study_cc_emails', '');
					$params['standard']['subject'] = $email_subject;
					$params['standard']['body'] = $email_body;
					$params['standard']['headers'] = array(
						'From' => $from,
						'MIME-Version' => '1.0',
						'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
						'Content-Transfer-Encoding' => '8Bit',
						'X-Mailer' => 'Drupal',
						'Cc' => $cc,
						'Bcc' => $bcc
					);
					if (!drupal_mail('case_study', 'standard', $email_to, language_default(), $params, $from, TRUE))
					{
						drupal_set_message('Error sending email message.', 'error');
					} //!drupal_mail('case_study', 'standard', $email_to, language_default(), $params, $from, TRUE)
				} //$form_state['values']['case_study_actions'] == 2
				elseif ($form_state['values']['case_study_actions'] == 3) //disapprove and delete entire Case Study
				{
					if (strlen(trim($form_state['values']['message'])) <= 30)
					{
						form_set_error('message', t(''));
						$msg = drupal_set_message("Please mention the reason for disapproval. Minimum 30 character required", 'error');
						return $msg;
					} //strlen(trim($form_state['values']['message'])) <= 30
					if (!user_access('Case Study bulk delete code'))
					{
						$msg = drupal_set_message(t('You do not have permission to Bulk Dis-Approved and Deleted Entire Project.'), 'error');
						return $msg;
					} //!user_access('case_study bulk delete code')
					if (case_study_abstract_delete_project($form_state['values']['case_study_project'])) //////
					{
						drupal_set_message(t('Dis-Approved and Deleted Entire Case Study.'), 'status');
						$email_subject = t('[!site_name][Case Study] Your uploaded Case Study have been marked as dis-approved', array(
						'!site_name' => variable_get('site_name', '')
					));
					$email_body = array(
						0 => t('
Dear !user_name,

We regret to inform you that your simulation and abstract for Power Systems Simulation Project at FOSSEE with the following details have been disapproved:

Full Name: ' . $user_info->name_title . ' ' . $user_info->contributor_name . '
Email : ' . $user_data->mail . '
University/Institute : ' . $user_info->university . '
City : ' . $user_info->city . '

Project Title  : ' . $user_info->project_title . '
Description of the simulation: ' . $user_info->description .'

Reason for dis-approval: ' . $form_state['values']['message'] . '

Kindly note that the incorrect files will be deleted from all our databases.

Thank you for participating in the Power Systems Simulation Project. You are welcome to submit a new proposal.

Best Wishes,

!site_name Power Systems Team,
FOSSEE,IIT Bombay', array(
						'!site_name' => variable_get('site_name', ''),
						'!user_name' => $user_data->name
											))
					);
					$email_to = $user_data->mail;
					$from = variable_get('case_study_from_email', '');
					$bcc = variable_get('case_study_emails', '');
					$cc = variable_get('case_study_cc_emails', '');
					$params['standard']['subject'] = $email_subject;
					$params['standard']['body'] = $email_body;
					$params['standard']['headers'] = array(
						'From' => $from,
						'MIME-Version' => '1.0',
						'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
						'Content-Transfer-Encoding' => '8Bit',
						'X-Mailer' => 'Drupal',
						'Cc' => $cc,
						'Bcc' => $bcc
					);
					if (!drupal_mail('case_study', 'standard', $email_to, language_default(), $params, $from, TRUE))
					{
						drupal_set_message('Error sending email message.', 'error');
					}
					} //case_study_abstract_delete_project($form_state['values']['case_study_project'])
					else
					{
						drupal_set_message(t('Error Dis-Approving and Deleting Entire Case Study.'), 'error');
					}
					// email 
					
				} //$form_state['values']['case_study_actions'] == 3
			} //user_access('case_study project bulk manage code')
		return $msg;
	} //$form_state['clicked_button']['#value'] == 'Submit'
}
/**********************************************************/
function _bulk_list_of_case_study_project()
{
	$project_titles = array(
		'0' => 'Please select...'
	);
	$query = db_select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('is_submitted', 1);
	$query->condition('approval_status', 1);
	$query->orderBy('creation_date', 'DESC');
	$project_titles_q = $query->execute();
	while ($project_titles_data = $project_titles_q->fetchObject())
	{
		$project_titles[$project_titles_data->id] = $project_titles_data->project_title . ' (Proposed by ' . $project_titles_data->contributor_name . ')';
	} //$project_titles_data = $project_titles_q->fetchObject()
	return $project_titles;
}
function _bulk_list_case_study_actions()
{
	$case_study_actions = array(
		0 => 'Please select...'
	);
	$case_study_actions[1] = 'Approve Entire Case Study';
	$case_study_actions[2] = 'Resubmit Project files';
	$case_study_actions[3] = 'Dis-Approve Entire Case Study (This will delete Case Study)';
	//$case_study_actions[4] = 'Delete Entire Case Study Including Proposal';
	return $case_study_actions;
}
function _case_study_details($case_study_proposal_id)
{
	$return_html = "";
	$query_pro = db_select('case_study_proposal');
	$query_pro->fields('case_study_proposal');
	$query_pro->condition('id', $case_study_proposal_id);
	$abstracts_pro = $query_pro->execute()->fetchObject();
	//var_dump($abstracts_pro);die;
	$query_pdf = db_select('case_study_submitted_abstracts_file');
	$query_pdf->fields('case_study_submitted_abstracts_file');
	$query_pdf->condition('proposal_id', $case_study_proposal_id);
	$query_pdf->condition('filetype', 'R');
	$abstracts_pdf = $query_pdf->execute()->fetchObject();
	if ($abstracts_pdf == TRUE)
	{
		if ($abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != "")
		{
			$abstract_filename = $abstracts_pdf->filename;
		} //$abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != ""
		else
		{
			$abstract_filename = "File not uploaded";
		}
	} //$abstracts_pdf == TRUE
	else
	{
		$abstract_filename = "File not uploaded";
	}
	$query_process = db_select('case_study_submitted_abstracts_file');
	$query_process->fields('case_study_submitted_abstracts_file');
	$query_process->condition('proposal_id', $case_study_proposal_id);
	$query_process->condition('filetype', 'C');
	$abstracts_query_process = $query_process->execute()->fetchObject();
	$query = db_select('case_study_submitted_abstracts');
	$query->fields('case_study_submitted_abstracts');
	$query->condition('proposal_id', $case_study_proposal_id);
	$abstracts_q = $query->execute()->fetchObject();
	if($abstracts_q->other_libraries_used == 1)
	{
		$other_libraries_used = "Yes";
	}
	else
	{
		$other_libraries_used = "No";
	}
	if ($abstracts_q)
	{
		if ($abstracts_q->is_submitted == 0)
		{
			//drupal_set_message(t('Abstract is not submmited yet.'), 'error', $repeat = FALSE);
			//return;
		} //$abstracts_q->is_submitted == 0
	} //$abstracts_q
	//var_dump($abstracts_query_process);die;
	if ($abstracts_query_process == TRUE)
	{
		if ($abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != "")
		{
			$abstracts_query_process_filename = $abstracts_query_process->filename;
		} //$abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != ""
		else
		{
			$abstracts_query_process_filename = "File not uploaded";
		}
	} //$abstracts_query_process == TRUE
	else
	{
		$url = l('Upload abstract', 'case-study-project/abstract-code/upload');
		$abstracts_query_process_filename = "File not uploaded";
	}
	$download_case_study = l('Download Case Study','case-study-project/full-download/project/'.$case_study_proposal_id);
	$return_html .= '<strong>Contributor Name:</strong><br />' . $abstracts_pro->name_title . ' ' . $abstracts_pro->contributor_name . '<br /><br />';
	$return_html .= '<strong>Title of the Case Study:</strong><br />' . $abstracts_pro->project_title . '<br /><br />';
	$return_html .= '<strong>Uploaded Report of the project:</strong><br />' . $abstract_filename . '<br /><br />';
	$return_html .= '<strong>Uploaded Code file:</strong><br />' . $abstracts_query_process_filename . '<br /><br />';
	$return_html .= $download_case_study;
	return $return_html;
}