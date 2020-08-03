<?php
// $Id$
//filetype: C - Code file
//filetype: R - Report file

function r_case_study_abstract()
{
	global $user;
	$return_html = "";
	$proposal_data = r_case_study_get_proposal();
	if (!$proposal_data)
	{
		drupal_goto('');
		return;
	} //!$proposal_data
	//$return_html .= l('Upload abstract', 'powersystems/pssp/abstract-code/upload') . '<br />';
	/* get experiment list */
	$query = db_select('case_study_submitted_abstracts');
	$query->fields('case_study_submitted_abstracts');
	$query->condition('proposal_id', $proposal_data->id);
	$abstracts_q = $query->execute()->fetchObject();
	$query_pro = db_select('case_study_proposal');
	$query_pro->fields('case_study_proposal');
	$query_pro->condition('id', $proposal_data->id);
	$abstracts_pro = $query_pro->execute()->fetchObject();
	$query_pdf = db_select('case_study_submitted_abstracts_file');
	$query_pdf->fields('case_study_submitted_abstracts_file');
	$query_pdf->condition('proposal_id', $proposal_data->id);
	$query_pdf->condition('filetype', 'R');
	$abstracts_pdf = $query_pdf->execute()->fetchObject();
	if ($abstracts_pdf == TRUE)
	{
		if ($abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != "")
		{
			$abstract_filename = $abstracts_pdf->filename;
			//$abstract_filename = l($abstracts_pdf->filename, 'powersystems/pssp/download/project-file/' . $proposal_data->id);
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
	$query_process->condition('proposal_id', $proposal_data->id);
	$query_process->condition('filetype', 'C');
	$abstracts_query_process = $query_process->execute()->fetchObject();
	if ($abstracts_query_process == TRUE)
	{
		if ($abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != "")
		{
			$abstracts_query_process_filename = $abstracts_query_process->filename;
			//$abstracts_query_process_filename = l($abstracts_query_process->filename, 'powersystems/pssp/download/project-file/' . $proposal_data->id); 
		} //$abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != ""
		else
		{
			$abstracts_query_process_filename = "File not uploaded";
		}
		if ($abstracts_q->is_submitted == '')
		{
			$url = l('Upload abstract', 'case-study-project/abstract-code/upload');
		} //$abstracts_q->is_submitted == ''
		else if ($abstracts_q->is_submitted == 1)
		{
			$url = "";
		} //$abstracts_q->is_submitted == 1
		else if ($abstracts_q->is_submitted == 0)
		{
			$url = l('Edit', 'case-study-project/abstract-code/upload');
		} //$abstracts_q->is_submitted == 0
	} //$abstracts_query_process == TRUE
	else
	{
		$url = l('Upload abstract', 'case-study-project/abstract-code/upload');
		$abstracts_query_process_filename = "File not uploaded";
	}
	$return_html .= '<strong>Contributor Name:</strong><br />' . $proposal_data->name_title . ' ' . $proposal_data->contributor_name . '<br /><br />';
	$return_html .= '<strong>Title of the Case Study:</strong><br />' . $proposal_data->project_title . '<br /><br />';
	$return_html .= '<strong>Uploaded Report of the project:</strong><br />' . $abstract_filename . '<br /><br />';
	$return_html .= '<strong>Uploaded Code file:</strong><br />' . $abstracts_query_process_filename . '<br /><br />';
	$return_html .= $url . '<br />';
	return $return_html;
}
function r_case_study_upload_abstract_code_form($form, &$form_state)
{
	global $user;
	$form['#attributes'] = array(
		'enctype' => "multipart/form-data"
	);
	/* get current proposal */
	//$proposal_id = (int) arg(3);
	$uid = $user->uid;
	$query = db_select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('uid', $uid);
	$query->condition('approval_status', '1');
	$proposal_q = $query->execute();
	if ($proposal_q)
	{
		if ($proposal_data = $proposal_q->fetchObject())
		{
			/* everything ok */
		} //$proposal_data = $proposal_q->fetchObject()
		else
		{
			drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
			drupal_goto('case-study-project/abstract-code');
			return;
		}
	} //$proposal_q
	else
	{
		drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
		drupal_goto('case-study-project/abstract-code');
		return;
	}
	$query = db_select('case_study_submitted_abstracts');
	$query->fields('case_study_submitted_abstracts');
	$query->condition('proposal_id', $proposal_data->id);
	$abstracts_q = $query->execute()->fetchObject();
	if ($abstracts_q)
	{
		if ($abstracts_q->is_submitted == 1)
		{
			drupal_set_message(t('You have already submited your project files, hence you can not upload more code, for any query please write to us.'), 'error', $repeat = FALSE);
			drupal_goto('case-study-project/abstract-code');
			//return;
		} //$abstracts_q->is_submitted == 1
	} //$abstracts_q->is_submitted == 1
	$form['project_title'] = array(
		'#type' => 'item',
		'#markup' => $proposal_data->project_title,
		'#title' => t('Title of the Case Study')
	);
	$form['contributor_name'] = array(
		'#type' => 'item',
		'#markup' => $proposal_data->contributor_name,
		'#title' => t('Contributor Name')
	);
	$existing_uploaded_report_file = default_value_for_uploaded_files("R", $proposal_data->id);
	if (!$existing_uploaded_report_file)
	{
		$existing_uploaded_report_file = new stdClass();
		$existing_uploaded_report_file->filename = "No file uploaded";
	} //!$existing_uploaded_report_file
	$form['upload_report'] = array(
		'#type' => 'file',
		'#title' => t('Upload an abstract of the project.') ,
		//'#required' => TRUE,
		'#description' => t(t('<span style="color:red;">Current File :</span> ' . $existing_uploaded_report_file->filename . '<br />' . t('<span style="color:red;">Allowed file extensions: ') . variable_get('project_report_upload_extensions', '') . '</span>'
	)));
	$existing_uploaded_code_file = default_value_for_uploaded_files("C", $proposal_data->id);
	if (!$existing_uploaded_code_file)
	{
		$existing_uploaded_code_file = new stdClass();
		$existing_uploaded_code_file->filename = "No file uploaded";
	} //!$existing_uploaded_code_file
	$form['upload_code_file'] = array(
		'#type' => 'file',
		'#title' => t('Upload the Project Files'),
		//'#required' => TRUE,
		'#description' => t('<span style="color:red;">Current File :</span> ' . $existing_uploaded_code_file->filename . '<br />Separate filenames with underscore. No spaces or any special characters allowed in filename.') . '<br />' . t('<span style="color:red;">Allowed file extensions: ') . variable_get('project_code_file_upload_extensions', '') . '</span>'
	);
	$form['prop_id'] = array(
		'#type' => 'hidden',
		'#value' => $proposal_data->id
	);
	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Submit'),
		'#submit' => array(
			'r_case_study_upload_abstract_code_form_submit'
		)
	);
	$form['cancel'] = array(
		'#type' => 'item',
		'#markup' => l(t('Cancel'), 'case-study-project/abstract-code')
	);
	return $form;
}

///////////////////////////////////////////////////////////////
function r_case_study_upload_abstract_code_form_validate($form, &$form_state)
{
	if (isset($_FILES['files']))
	{
		/* check if file is uploaded */
		$existing_uploaded_report_file = default_value_for_uploaded_files("R", $form_state['values']['prop_id']);
		$existing_uploaded_code_file = default_value_for_uploaded_files("C", $form_state['values']['prop_id']);
		if (!$existing_uploaded_code_file)
		{
			if (!($_FILES['files']['name']['upload_code_file']))
				form_set_error('upload_code_file', t('Please upload the file.'));
		} //!$existing_uploaded_code_file
		if (!$existing_uploaded_report_file)
		{
			if (!($_FILES['files']['name']['upload_report']))
				form_set_error('upload_report', t('Please upload the file.'));
		} //!$existing_uploaded_report_file
		/* check for valid filename extensions */
		
		/* check if atleast one source or result file is uploaded */
		if (!($_FILES['files']['name']['upload_report']))
			form_set_error('upload_report', t('Please upload the abstract file'));
		if(!($_FILES['files']['name']['upload_code_file']))
			form_set_error('upload_code_file', t('Please upload the raw data file'));
		/* check for valid filename extensions */
		if ($_FILES['files']['name']['upload_report'] || $_FILES['files']['name']['upload_code_file'])
	{
		foreach ($_FILES['files']['name'] as $file_form_name => $file_name)
		{
			if ($file_name)
			{
				/* checking file type */
				if (strstr($file_form_name, 'upload_report'))
						$file_type = 'R';
				else if (strstr($file_form_name, 'upload_code_file'))
						$file_type = 'C';
				$allowed_extensions_str = '';
				switch ($file_type)
				{
					case 'R':
						$allowed_extensions_str = variable_get('project_report_upload_extensions', '');
						break;
					case 'C':
						$allowed_extensions_str = variable_get('project_code_file_upload_extensions', '');
						break;
				}
				$allowed_extensions = explode(',', $allowed_extensions_str);
				$fnames = explode('.', strtolower($_FILES['files']['name'][$file_form_name]));
				$temp_extension = end($fnames);
				if (!in_array($temp_extension, $allowed_extensions))
					form_set_error($file_form_name, t('Only file with ' . $allowed_extensions_str . ' extensions can be uploaded.'));
				if ($_FILES['files']['size'][$file_form_name] <= 0)
					form_set_error($file_form_name, t('File size cannot be zero.'));
				/* check if valid file name */
				if (!textbook_companion_check_valid_filename($_FILES['files']['name'][$file_form_name]))
					form_set_error($file_form_name, t('Invalid file name specified. Only alphabets and numbers are allowed as a valid filename.'));
			} //$file_name
		} //$_FILES['files']['name'] as $file_form_name => $file_name
	}
}
}
function r_case_study_upload_abstract_code_form_submit($form, &$form_state)
{
	global $user;
	$v = $form_state['values'];
	$root_path = r_case_study_path();
	$proposal_data = r_case_study_get_proposal();
	$proposal_id = $proposal_data->id;
	if (!$proposal_data)
	{
		drupal_goto('');
		return;
	} //!$proposal_data
	$proposal_id = $proposal_data->id;
	$proposal_directory = $proposal_data->directory_name;
	/* create proposal folder if not present */
	//$dest_path = $proposal_directory . '/';
	$dest_path_project_files = $proposal_directory . '/project_files/';
	if (!is_dir($root_path . $dest_path_project_files))
		mkdir($root_path . $dest_path_project_files);
	$proposal_id = $proposal_data->id;
	$query_s = "SELECT * FROM {case_study_submitted_abstracts} WHERE proposal_id = :proposal_id";
	$args_s = array(
		":proposal_id" => $proposal_id
	);
	$query_s_result = db_query($query_s, $args_s)->fetchObject();
	if (!$query_s_result)
	{
		/* creating solution database entry */
		$query = "INSERT INTO {case_study_submitted_abstracts} (
	proposal_id,
	approver_uid,
	abstract_approval_status,
	abstract_upload_date,
	abstract_approval_date,
	is_submitted) VALUES (:proposal_id, :approver_uid, :abstract_approval_status, :abstract_upload_date, :abstract_approval_date, :is_submitted)";
		$args = array(
			":proposal_id" => $proposal_id,
			":approver_uid" => 0,
			":abstract_approval_status" => 0,
			":abstract_upload_date" => time(),
			":abstract_approval_date" => 0,
			":is_submitted" => 1
		);
		$submitted_abstract_id = db_query($query, $args, array(
			'return' => Database::RETURN_INSERT_ID
		));
		
		$query1 = "UPDATE {case_study_proposal} SET is_submitted = :is_submitted WHERE id = :id";
		$args1 = array(
			":is_submitted" => 1,
			":id" => $proposal_id
		);
		db_query($query1,$args1);
		drupal_set_message('Abstract uploaded successfully.', 'status');
	} //!$query_s_result
	else
	{
		$query = "UPDATE {case_study_submitted_abstracts} SET 

	abstract_upload_date =:abstract_upload_date,
	is_submitted= :is_submitted 
	WHERE proposal_id = :proposal_id
	";
		$args = array(
			":abstract_upload_date" => time(),
			":is_submitted" => 1,
			":proposal_id" => $proposal_id
		);
		$submitted_abstract_id = db_query($query, $args, array(
			'return' => Database::RETURN_INSERT_ID
		));
		$query1 = "UPDATE {case_study_proposal} SET is_submitted = :is_submitted WHERE id = :id";
		$args1 = array(
			":is_submitted" => 1,
			":id" => $proposal_id
		);
		db_query($query1,$args1);
		drupal_set_message('Abstract updated successfully.', 'status');
	}
	foreach ($_FILES['files']['name'] as $file_form_name => $file_name)
	{
		if ($file_name)
		{
			/* checking file type */
			if (strstr($file_form_name, 'upload_code_file'))
			{
				$file_type = 'C';
			} //strstr($file_form_name, 'upload_code_file')
			else if (strstr($file_form_name, 'upload_report'))
			{
				$file_type = 'R';
			}
			switch ($file_type)
			{
				case 'C':
					if (file_exists($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]))
					{
						//unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
						drupal_set_message(t("File !filename already exists hence overwirtten the exisitng file ", array(
							'!filename' => $_FILES['files']['name'][$file_form_name]
						)), 'error');
					} //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					/* uploading file */
					else if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]))
					{
						/* for uploaded files making an entry in the database */
						$query_ab_f = "SELECT * FROM case_study_submitted_abstracts_file WHERE proposal_id = :proposal_id AND filetype = 
				:filetype";
						$args_ab_f = array(
							":proposal_id" => $proposal_id,
							":filetype" => $file_type
						);
						$query_ab_f_result = db_query($query_ab_f, $args_ab_f)->fetchObject();
						if (!$query_ab_f_result)
						{
							$query = "INSERT INTO {case_study_submitted_abstracts_file} (submitted_abstract_id, proposal_id, uid, approvar_uid, filename, filepath, filemime, filesize, filetype, timestamp)
          VALUES (:submitted_abstract_id, :proposal_id, :uid, :approvar_uid, :filename, :filepath, :filemime, :filesize, :filetype, :timestamp)";
							$args = array(
								":submitted_abstract_id" => $submitted_abstract_id,
								":proposal_id" => $proposal_id,
								":uid" => $user->uid,
								":approvar_uid" => 0,
								":filename" => $_FILES['files']['name'][$file_form_name],
								":filepath" => $_FILES['files']['name'][$file_form_name],
								":filemime" => mime_content_type($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]),
								":filesize" => $_FILES['files']['size'][$file_form_name],
								":filetype" => $file_type,
								":timestamp" => time()
							);
							db_query($query, $args);
							drupal_set_message($file_name . ' uploaded successfully.', 'status');
						} //!$query_ab_f_result
						else
						{
							unlink($root_path . $dest_path_project_files . $query_ab_f_result->filename);
							$query = "UPDATE {case_study_submitted_abstracts_file} SET filename = :filename, filepath=:filepath, filemime=:filemime, filesize=:filesize, timestamp=:timestamp WHERE proposal_id = :proposal_id AND filetype = :filetype";
							$args = array(
								":filename" => $_FILES['files']['name'][$file_form_name],
								":filepath" => $file_path . $_FILES['files']['name'][$file_form_name],
								":filemime" => mime_content_type($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]),
								":filesize" => $_FILES['files']['size'][$file_form_name],
								":timestamp" => time(),
								":proposal_id" => $proposal_id,
								":filetype" => $file_type
							);
							db_query($query, $args);
							drupal_set_message($file_name . ' file updated successfully.', 'status');
						}
					} //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					else
					{
						drupal_set_message('Error uploading file : ' . $dest_path_project_files . $file_name, 'error');
					}
					break;
				case 'R':
					if (file_exists($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]))
					{
						//unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
						drupal_set_message(t("File !filename already exists hence overwirtten the exisitng file ", array(
							'!filename' => $_FILES['files']['name'][$file_form_name]
						)), 'error');
					} //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					/* uploading file */
					else if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]))
					{
						/* for uploaded files making an entry in the database */
						$query_ab_f = "SELECT * FROM case_study_submitted_abstracts_file WHERE proposal_id = :proposal_id AND filetype = 
				:filetype";
						$args_ab_f = array(
							":proposal_id" => $proposal_id,
							":filetype" => $file_type
						);
						$query_ab_f_result = db_query($query_ab_f, $args_ab_f)->fetchObject();
						if (!$query_ab_f_result)
						{
							$query = "INSERT INTO {case_study_submitted_abstracts_file} (submitted_abstract_id, proposal_id, uid, approvar_uid, filename, filepath, filemime, filesize, filetype, timestamp)
          VALUES (:submitted_abstract_id, :proposal_id, :uid, :approvar_uid, :filename, :filepath, :filemime, :filesize, :filetype, :timestamp)";
							$args = array(
								":submitted_abstract_id" => $submitted_abstract_id,
								":proposal_id" => $proposal_id,
								":uid" => $user->uid,
								":approvar_uid" => 0,
								":filename" => $_FILES['files']['name'][$file_form_name],
								":filepath" => $_FILES['files']['name'][$file_form_name],
								":filemime" => mime_content_type($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]),
								":filesize" => $_FILES['files']['size'][$file_form_name],
								":filetype" => $file_type,
								":timestamp" => time()
							);
							db_query($query, $args);
							drupal_set_message($file_name . ' uploaded successfully.', 'status');
						} //!$query_ab_f_result
						else
						{
							unlink($root_path . $dest_path_project_files . $query_ab_f_result->filename);
							$query = "UPDATE {case_study_submitted_abstracts_file} SET filename = :filename, filepath=:filepath, filemime=:filemime, filesize=:filesize, timestamp=:timestamp WHERE proposal_id = :proposal_id AND filetype = :filetype";
							$args = array(
								":filename" => $_FILES['files']['name'][$file_form_name],
								":filepath" => $file_path . $_FILES['files']['name'][$file_form_name],
								":filemime" => mime_content_type($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]),
								":filesize" => $_FILES['files']['size'][$file_form_name],
								":timestamp" => time(),
								":proposal_id" => $proposal_id,
								":filetype" => $file_type
							);
							db_query($query, $args);
							drupal_set_message($file_name . ' file updated successfully.', 'status');
						}
					} //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					else
					{
						drupal_set_message('Error uploading file : ' . $dest_path_project_files . $file_name, 'error');
					}
					break;
			} //$file_type
		} //$file_name
	} //$_FILES['files']['name'] as $file_form_name => $file_name
	/* sending email */
	$email_to = $user->mail;
	$from = variable_get('case_study_from_email', '');
	$bcc = variable_get('case_study_emails', '');
	$cc = variable_get('case_study_cc_emails', '');
	$params['abstract_uploaded']['proposal_id'] = $proposal_id;
	$params['abstract_uploaded']['submitted_abstract_id'] = $submitted_abstract_id;
	$params['abstract_uploaded']['user_id'] = $user->uid;
	$params['abstract_uploaded']['headers'] = array(
		'From' => $from,
		'MIME-Version' => '1.0',
		'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
		'Content-Transfer-Encoding' => '8Bit',
		'X-Mailer' => 'Drupal',
		'Cc' => $cc,
		'Bcc' => $bcc
	);
	if (!drupal_mail('case_study', 'abstract_uploaded', $email_to, language_default(), $params, $from, TRUE))
		drupal_set_message('Error sending email message.', 'error');
	drupal_goto('case-study-project/abstract-code');
}
function default_value_for_uploaded_files($filetype, $proposal_id)
{
	$query = db_select('case_study_submitted_abstracts_file');
	$query->fields('case_study_submitted_abstracts_file');
	$query->condition('proposal_id', $proposal_id);
	$selected_files_array = "";
	if ($filetype == "R")
	{
		$query->condition('filetype', $filetype);
		$filetype_q = $query->execute()->fetchObject();
		return $filetype_q;
	} //$filetype == "A"
	elseif ($filetype == "C")
	{
		$query->condition('filetype', $filetype);
		$filetype_q = $query->execute()->fetchObject();
		return $filetype_q;
	}
	else
	{
		return;
	}
	return;
}