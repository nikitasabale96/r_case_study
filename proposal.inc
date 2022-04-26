<?php
/*
Approval Status :
0 - Pending
1 - Approved
2 - Dis-Approved
3 - Completed
Solution Status :
0 - Pending
1 - Approved
2 - Dis-Approved
Solution Display :
0 - No
1 - Yes
is_completed
0 - in progress
1 - Completed
Tables :
case_study_solution : approval_status
0 - Pending
1 - Approved
2 - Disapproved (delete it)
*/
function r_case_study_proposal_form($form, &$form_state, $no_js_use = FALSE)
{
	global $user;
	/************************ start approve book details ************************/
	if ($user->uid == 0)
	{
		$msg = drupal_set_message(t('It is mandatory to login on this website to access the case study proposal form. If you are new user please create a new account first.'), 'error');
		drupal_goto('user/login', array('query' => drupal_get_destination()));
		return $msg;
	} //$user->uid == 0
	$query = db_select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('uid', $user->uid);
	$query->orderBy('id', 'DESC');
	$query->range(0, 1);
	$proposal_q = $query->execute();
	$proposal_data = $proposal_q->fetchObject();
	if ($proposal_data)
	{
		if ($proposal_data->approval_status == 0 || $proposal_data->approval_status == 1)
		{
			drupal_set_message(t('We have already received your proposal.'), 'status');
			drupal_goto('');
			return;
		} //$proposal_data->approval_status == 0 || $proposal_data->approval_status == 1
	} //$proposal_data
	$form['#attributes'] = array(
		'enctype' => "multipart/form-data"
	);

	$form['name_title'] = array(
		'#type' => 'select',
		'#title' => t('Name Title'),
		'#options' => array(
			'Dr' => 'Dr',
			'Prof' => 'Prof',
			'Mr' => 'Mr',
			'Ms' => 'Ms'
		),
		'#required' => TRUE
	);
	$form['contributor_name'] = array(
		'#type' => 'textfield',
		'#title' => t('Name of the contributor'),
		'#size' => 250,
		'#attributes' => array(
			'placeholder' => t('Enter your full name.....')
		),
		'#maxlength' => 250,
		'#required' => TRUE
	);
	$form['contributor_email_id'] = array(
		'#type' => 'textfield',
		'#title' => t('Email'),
		'#size' => 30,
		'#value' => $user->mail,
		'#disabled' => TRUE
	);
	$form['contributor_contact_no'] = array(
		'#type' => 'textfield',
		'#title' => t('Contact No.'),
		'#size' => 10,
		'#attributes' => array(
			'placeholder' => t('Enter your contact number')
		),
		'#maxlength' => 250,
		'#required' => TRUE
	);
	$form['university'] = array(
		'#type' => 'textfield',
		'#title' => t('University/Institute/Organisation'),
		'#size' => 80,
		'#maxlength' => 200,
		'#required' => TRUE,
		'#attributes' => array(
			'placeholder' => 'Insert full name of your university.... '
		)
	);
	$form['department'] = array(
		'#type' => 'select',
		'#title' => t('Department/Branch'),
		'#options' => _r_case_study_list_of_departments(),
		'#required' => TRUE,
	);
	$form['other_department'] = array(
		'#type' => 'textfield',
		'#title' => t('If ‘Other’, please specify'),
		'#maxlength' => 50,
		'#description' => t('<span style="color:red">Maximum character limit is 50</span>'),
		'#states' => array(
			'visible' => array(
				':input[name="department"]' => array(
					'value' => 'Others'
				)
			)
		)	

	);
	$form['how_did_you_know_about_project'] = array(
		'#type' => 'select',
		'#title' => t('How did you come to know about the Case Study Project?'),
		'#options' =>  array(
			'Poster' => 'Poster',
			'Website' => 'Website',
			'Email' => 'Email',
			'Others' => 'Others'
		),
		'#required' => TRUE
	);
	$form['others_how_did_you_know_about_project'] = array(
		'#type' => 'textfield',
		'#title' => t('If ‘Other’, please specify'),
		'#maxlength' => 50,
		'#description' => t('<span style="color:red">Maximum character limit is 50</span>'),
		'#states' => array(
			'visible' => array(
				':input[name="how_did_you_know_about_project"]' => array(
					'value' => 'Others'
				)
			)
		)		
	);
	$form['faculty_name'] = array(
		'#type' => 'textfield',
		'#title' => t('Name of the Faculty Member of your Institution, if any, who helped you with this Case Study Project'),
		'#size' => 50,
		'#maxlength' => 50,
		'#validated' => TRUE,
		'#description' => t('<span style="color:red">Maximum character limit is 50</span>')
	);
	$form['faculty_department'] = array(
		'#type' => 'textfield',
		'#title' => t('Department of the Faculty Member of your Institution, if any, who helped you with this Case Study Project'),
		'#size' => 50,
		'#maxlength' => 50,
		'#validated' => TRUE,
		'#description' => t('<span style="color:red">Maximum character limit is 50</span>')
	);
	$form['faculty_email'] = array(
		'#type' => 'textfield',
		'#title' => t('Email id of the Faculty Member of your Institution, if any, who helped you with this Case Study Project'),
		'#size' => 255,
		'#maxlength' => 255,
		'#validated' => TRUE,
		'#description' => t('<span style="color:red">Maximum character limit is 255</span>')
	);
	$form['country'] = array(
		'#type' => 'select',
		'#title' => t('Country'),
		'#options' => array(
			'India' => 'India',
			'Others' => 'Others'
		),
		'#required' => TRUE,
		'#tree' => TRUE
	);
	$form['other_country'] = array(
		'#type' => 'textfield',
		'#title' => t('Other Country'),
		'#size' => 100,
		'#attributes' => array(
			'placeholder' => t('Enter your country name')
		),
		'#states' => array(
			'visible' => array(
				':input[name="country"]' => array(
					'value' => 'Others'
				)
			)
		)
	);
	$form['other_state'] = array(
		'#type' => 'textfield',
		'#title' => t('State'),
		'#size' => 100,
		'#attributes' => array(
			'placeholder' => t('Enter your state/region name')
		),
		'#states' => array(
			'visible' => array(
				':input[name="country"]' => array(
					'value' => 'Others'
				)
			)
		)
	);
	$form['other_city'] = array(
		'#type' => 'textfield',
		'#title' => t('City'),
		'#size' => 100,
		'#attributes' => array(
			'placeholder' => t('Enter your city name')
		),
		'#states' => array(
			'visible' => array(
				':input[name="country"]' => array(
					'value' => 'Others'
				)
			)
		)
	);
	$form['all_state'] = array(
		'#type' => 'select',
		'#title' => t('State'),
		'#options' => _r_case_study_list_of_states(),
		'#validated' => TRUE,
		'#states' => array(
			'visible' => array(
				':input[name="country"]' => array(
					'value' => 'India'
				)
			)
		)
	);
	$form['city'] = array(
		'#type' => 'select',
		'#title' => t('City'),
		'#options' => _r_case_study_list_of_cities(),
		'#states' => array(
			'visible' => array(
				':input[name="country"]' => array(
					'value' => 'India'
				)
			)
		)
	);
	$form['pincode'] = array(
		'#type' => 'textfield',
		'#title' => t('Pincode'),
		'#size' => 6
	);
	/***************************************************************************/
	$form['hr'] = array(
		'#type' => 'item',
		'#markup' => '<hr>'
	);
	$form['r_version'] = array(
		'#type' => 'select',
		'#title' => t('Select the R version'),
		'#options' => _cs_list_of_versions(),
		'#required' => TRUE
	);
	$form['r_other_version'] = array(
		'#type' => 'textfield',
		'#title' => t('Enter the R version used'),
		'#description' => t('<span style="color:red">This is a mandatory field</span>'),
		'#size' => 100,
		'#maxlength' => 100,
		'#states' => array(
			'visible' => array(
				':input[name="r_version"]' => array(
					'value' => 'Others'
				)
			)
		)
	);
	$form['case_study_title'] = array(
		'#type' => 'textarea',
		'#title' => t('Case Study Title'),
		'#size' => 250,
		'#maxlength' => 500,
		'#description' => t('Maximum character limit is 500'),
		'#required' => TRUE,
	);
	$form['description'] = array(
		'#type' => 'textarea',
		'#title' => t('Objective'),
		'#size' => 250,
		'#maxlength' => 1200,
		'#description' => t('Maximum character limit is 1200'),
		'#required' => TRUE,
	);
	$form['references'] = array(
		'#type' => 'textfield',
		'#title' => t("Literature Survey"),
		'#size' => 255,
		'#maxlength' => 2500,
		'#description' => t('Maximum character limit is 2500'),
		'#required' => TRUE
	);
	$form['references_1'] = array(
		'#type' => 'select',
		'#title' => t('Do you want to add another Literature Survey'),
		'#options' => array(
			'Yes' => 'Yes',
			'No' => 'No'
		),
		'#required' => TRUE,
		'#tree' => TRUE
	);
	$form['other_references'] = array(
		'#type' => 'textfield',
		'#title' => t('Other Literature Survey'),
		'#size' => 255,
		'#maxlength' => 2500,
		'#description' => t('Maximum character limit is 2500'),
		'#states' => array(
			'visible' => array(
				':input[name="references_1"]' => array(
					'value' => 'Yes'
				)
			)
		)
	);
	$form['sample_abstract_file'] = array(
        '#type' => 'item',
        '#title' => t('<h5>Ideal Sample Abstract File Submission</h5>'),
		'#markup' => t('Kindly refer to the following link to know the ideal sample abstract file submission:') . t('<a href= "https://r.fossee.in/case-study-project/download/proposal-abstract-files/4" target="_blank"> Click Here</a>')
    );
	$form['abstract_file'] = array(
		'#type' => 'fieldset',
		'#title' => t('Submit an Abstract<span style="color:red">*</span>'),
		'#collapsible' => FALSE,
		'#collapsed' => FALSE
	);
	$form['abstract_file']['abstract_file_path'] = array(
		'#type' => 'file',
		'#size' => 48,
		'#description' => t('<span style="color:red;">Upload filenames with allowed extensions only. No spaces or any special characters allowed in filename.</span>') . '<br />' . t('<span style="color:red;">Allowed file extensions: ') . variable_get('proposal_abstract_upload_extensions', '') . '</span>'
	);
	$form['raw_data_file'] = array(
		'#type' => 'fieldset',
		'#title' => t('Submit raw data file/s<span style="color:red">*</span>'),
		'#collapsible' => FALSE,
		'#collapsed' => FALSE
	);
	$form['raw_data_file']['raw_data_file_path'] = array(
		'#type' => 'file',
		'#size' => 48,
		'#description' => t('<span style="color:red;">Upload filenames with allowed extensions only. No spaces or any special characters allowed in filename.</span>') . '<br />' . t('<span style="color:red;">Allowed file extensions: ') . variable_get('proposal_raw_data_upload_extensions', '') . '</span>'
	);
	$form['date_of_proposal'] = array(
		'#type' => 'date_popup',
		'#title' => t('Date of Proposal'),
		'#default_value' => date("Y-m-d H:i:s"),
		'#date_format' => 'd M Y',
		'#disabled' => TRUE,
		'#date_label_position' => '',
	);
	$form['expected_date_of_completion'] = array(
		'#type' => 'date_popup',
		'#title' => t('Expected Date of Completion'),
		'#date_label_position' => '',
		'#description' => '',
		'#default_value' => '',
		'#date_format' => 'd-M-Y',
		//'#date_increment' => 0,
		//'#minDate' => '+0',
		'#date_year_range' => '0 : +1',
		'#datepicker_options' => array('maxDate' => 31, 'minDate' => 0),
		'#required' => TRUE
	);
	$form['term_condition'] = array(
		'#type' => 'checkboxes',
		'#title' => t('Terms And Conditions'),
		'#options' => array(
			'status' => t('<a href="https://r.fossee.in/case-study-project/term-and-conditions" target="_blank">I agree to the Terms and Conditions</a>')
		),
		'#required' => TRUE
	);
	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Submit')
	);
	return $form;
}

function r_case_study_proposal_form_validate($form, &$form_state)
{
	/*if ($form_state['values']['term_condition'] == '1')
	{
		form_set_error('term_condition', t('Please check the terms and conditions'));
		// $form_state['values']['country'] = $form_state['values']['other_country'];
	}*/ //$form_state['values']['term_condition'] == '1'
	if ($form_state['values']['country'] == 'Others')
	{
		if ($form_state['values']['other_country'] == '')
		{
			form_set_error('other_country', t('Enter country name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['other_country'] == ''
		else
		{
			$form_state['values']['country'] = $form_state['values']['other_country'];
		}
		if ($form_state['values']['other_state'] == '')
		{
			form_set_error('other_state', t('Enter state name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['other_state'] == ''
		else
		{
			$form_state['values']['all_state'] = $form_state['values']['other_state'];
		}
		if ($form_state['values']['other_city'] == '')
		{
			form_set_error('other_city', t('Enter city name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['other_city'] == ''
		else
		{
			$form_state['values']['city'] = $form_state['values']['other_city'];
		}
	} //$form_state['values']['country'] == 'Others'
	else
	{
		if ($form_state['values']['country'] == '')
		{
			form_set_error('country', t('Select country name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['country'] == ''
		if ($form_state['values']['all_state'] == '')
		{
			form_set_error('all_state', t('Select state name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['all_state'] == ''
		if ($form_state['values']['city'] == '')
		{
			form_set_error('city', t('Select city name'));
			// $form_state['values']['country'] = $form_state['values']['other_country'];
		} //$form_state['values']['city'] == ''
	}
	//Validation for project title
	$form_state['values']['case_study_title'] = trim($form_state['values']['case_study_title']);
	if ($form_state['values']['case_study_title'] != '')
	{
		if (strlen($form_state['values']['case_study_title']) > 250)
		{
			form_set_error('case_study_title', t('Maximum charater limit is 250 charaters only, please check the length of the project title'));
		} //strlen($form_state['values']['project_title']) > 250
		else if (strlen($form_state['values']['case_study_title']) < 10)
		{
			form_set_error('case_study_title', t('Minimum charater limit is 10 charaters, please check the length of the project title'));
		}
		else if(preg_match('/[\^£$%&*()}{@#~?><>.:;`|=_+¬]/', $form_state['values']['case_study_title']))
			form_set_error('case_study_title', t('Special characters are not allowed for Case Study project title')); //strlen($form_state['values']['project_title']) < 10
	} //$form_state['values']['project_title'] != ''
	else
	{
		form_set_error('case_study_title', t('Project title shoud not be empty'));
	}

	if ($form_state['values']['how_did_you_know_about_project'] == 'Others')
	{
		if ($form_state['values']['others_how_did_you_know_about_project'] == '')
		{
		form_set_error('others_how_did_you_know_about_project', t('Please enter how did you know about the project'));
		}
		else
		{
			$form_state['values']['how_did_you_know_about_project'] = $form_state['values']['others_how_did_you_know_about_project'];
		}
	}
	if ($form_state['values']['department'] == 'Others')
	{
		if ($form_state['values']['other_department'] == '')
		{
		form_set_error('other_department', t('Please enter the department'));
		}
		else
		{
			$form_state['values']['department'] = $form_state['values']['other_department'];
		}
	}
	if ($form_state['values']['r_version'] == 'Others')
	{
		if ($form_state['values']['r_other_version'] == '')
		{
		form_set_error('r_other_version', t('Please enter the version'));
		}
		else
		{
			$form_state['values']['r_version'] = $form_state['values']['r_other_version'];
		}
	}
	
if (isset($_FILES['files']))
	{
		/* check if atleast one source or result file is uploaded */
		if (!($_FILES['files']['name']['abstract_file_path']))
			form_set_error('abstract_file_path', t('Please upload the abstract file'));
		if(!($_FILES['files']['name']['raw_data_file_path']))
			form_set_error('raw_data_file_path', t('Please upload the raw data file'));
		/* check for valid filename extensions */
		foreach ($_FILES['files']['name'] as $file_form_name => $file_name)
		{
			if ($file_name)
			{
				/* checking file type */
				if (strstr($file_form_name, 'abstract_file_path'))
						$file_type = 'A';
				else if (strstr($file_form_name, 'raw_data_file_path'))
						$file_type = 'R';
				$allowed_extensions_str = '';
				switch ($file_type)
				{
					case 'A':
						$allowed_extensions_str = variable_get('proposal_abstract_upload_extensions', '');
						break;
					case 'R':
						$allowed_extensions_str = variable_get('proposal_raw_data_upload_extensions', '');
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
	return $form_state;
}
function r_case_study_proposal_form_submit($form, &$form_state)
{
	global $user;
	$root_path = r_case_study_path();
	if (!$user->uid)
	{
		drupal_set_message('It is mandatory to login on this website to access the proposal form', 'error');
		return;
	}
	if ($form_state['values']['how_did_you_know_about_project'] == 'Others')
	{
			$how_did_you_know_about_project = $form_state['values']['others_how_did_you_know_about_project'];
	}
	else {
		$how_did_you_know_about_project = $form_state['values']['how_did_you_know_about_project'];
	}
	if($form_state['values']['other_references'] == 'Yes'){
		$other_references = $form_state['values']['other_references'];
    }
    else{
        $other_references = 'NA';
    }
	/* inserting the user proposal */
	$v = $form_state["values"];
	$project_title = trim($v['case_study_title']);
	$proposar_name = $v['name_title'] . ' ' . $v['contributor_name'];
	$university = $v['university'];
	$directory_name = _r_case_study_dir_name($project_title, $proposar_name);
	$result = "INSERT INTO {case_study_proposal} 
    (
    uid, 
    approver_uid,
    name_title, 
    contributor_name,
    contact_no,
    university,
    department,
    how_did_you_know_about_project,
    faculty_name,
    faculty_department,
    faculty_email,
    city, 
    pincode, 
    state, 
    country,
    r_version,
    project_title,
    description,
    reference,
    other_reference,
    directory_name,
    approval_status,
    is_completed, 
    dissapproval_reason,
    creation_date, 
    expected_date_of_completion,
    approval_date
    ) VALUES
    (
    :uid, 
    :approver_uid, 
    :name_title, 
    :contributor_name, 
    :contact_no,
    :university,
    :department,
    :how_did_you_know_about_project,
    :faculty_name,
    :faculty_department,
    :faculty_email,
    :city, 
    :pincode, 
    :state,  
    :country,
    :r_version,
    :project_title, 
    :description,
    :reference,
    :other_reference,
    :directory_name,
    :approval_status,
    :is_completed, 
    :dissapproval_reason,
    :creation_date, 
    :expected_date_of_completion,
    :approval_date
    )";
	$args = array(
		":uid" => $user->uid,
		":approver_uid" => 0,
		":name_title" => trim($v['name_title']),
		":contributor_name" => trim($v['contributor_name']),
		":contact_no" => $v['contributor_contact_no'],
		":university" => trim($v['university']),
		":department" => $v['department'],
		":how_did_you_know_about_project" => trim($how_did_you_know_about_project),
		":faculty_name" => trim($v['faculty_name']),
		":faculty_department" => trim($v['faculty_department']),
		":faculty_email" => trim($v['faculty_email']),
		":city" => $v['city'],
		":pincode" => $v['pincode'],
		":state" => $v['all_state'],
		":country" => $v['country'],
		":r_version" => $v['r_version'],
		":project_title" => $project_title,
		':description' => $v['description'],
		":reference" => $v['references'],
		":other_reference" => $other_references,
		":directory_name" => $directory_name,
		":approval_status" => 0,
		":is_completed" => 0,
		":dissapproval_reason" => "NULL",
		":creation_date" => time(),
		":expected_date_of_completion" => strtotime(date($v['expected_date_of_completion'])),
		":approval_date" => 0
		);
	$proposal_id = db_query($result, $args, array(
		'return' => Database::RETURN_INSERT_ID
	));
	$dest_path = $directory_name . '/';
	$dest_path1 = $root_path . $dest_path;
	//var_dump($dest_path1);die;	
	if (!is_dir($root_path . $dest_path))
		mkdir($root_path . $dest_path);
	/* uploading files */
	foreach ($_FILES['files']['name'] as $file_form_name => $file_name)
	{
		if ($file_name)
		{
			/* checking file type */
			if (strstr($file_form_name, 'abstract_file_path'))
						$file_type = 'A';
			else if (strstr($file_form_name, 'raw_data_file_path'))
						$file_type = 'R';
			switch ($file_type)
			{
				case 'A':
					if (file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]))
					{
						 drupal_set_message(t("Error uploading file. File !filename already exists.", array('!filename' => $_FILES['files']['name'][$file_form_name])), 'error');
						//unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
					} //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					/* uploading file */
					if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name]))
					{
						$query = "INSERT INTO {case_study_proposals_file} (proposal_id, filename, filepath, filemime, filesize, filetype, timestamp)
          VALUES (:proposal_id, :filename, :filepath, :filemime, :filesize, :filetype, :timestamp)";
							$args = array(
								":proposal_id" => $proposal_id,
								":filename" => $_FILES['files']['name'][$file_form_name],
								":filepath" => $dest_path . $_FILES['files']['name'][$file_form_name],
								":filemime" => mime_content_type($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]),
								":filesize" => $_FILES['files']['size'][$file_form_name],
								":filetype" => $file_type,
								":timestamp" => time()
							);
						$updateresult = db_query($query, $args);
						drupal_set_message($file_name . ' uploaded successfully.', 'status');
					} //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					else
					{
						drupal_set_message('Error uploading file : ' . $dest_path . $file_name, 'error');
					}
					break;
				case 'R':
					if (file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]))
					{
						 drupal_set_message(t("Error uploading file. File !filename already exists.", array('!filename' => $_FILES['files']['name'][$file_form_name])), 'error');
						//unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
					} //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					/* uploading file */
					if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name]))
					{
						$query = "INSERT INTO {case_study_proposals_file} (proposal_id, filename, filepath, filemime, filesize, filetype, timestamp)
          VALUES (:proposal_id, :filename, :filepath, :filemime, :filesize, :filetype, :timestamp)";
							$args = array(
								":proposal_id" => $proposal_id,
								":filename" => $_FILES['files']['name'][$file_form_name],
								":filepath" => $dest_path . $_FILES['files']['name'][$file_form_name],
								":filemime" => mime_content_type($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]),
								":filesize" => $_FILES['files']['size'][$file_form_name],
								":filetype" => $file_type,
								":timestamp" => time()
							);
						$updateresult = db_query($query, $args);
						drupal_set_message($file_name . ' uploaded successfully.', 'status');
					} //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					else
					{
						drupal_set_message('Error uploading file : ' . $dest_path . $file_name, 'error');
					}
					break;
				}

		} //$file_name
	}	
	if (!$proposal_id)
	{
		drupal_set_message(t('Error receiving your proposal. Please try again.'), 'error');
		return;
	} //!$proposal_id
	/* sending email */
	$email_to = $user->mail;
	$form = variable_get('case_study_from_email', '');
	$bcc = variable_get('case_study_emails', '');
	$cc = variable_get('case_study_cc_emails', '');
	$params['case_study_proposal_received']['proposal_id'] = $proposal_id;
	$params['case_study_proposal_received']['user_id'] = $user->uid;
	$params['case_study_proposal_received']['headers'] = array(
		'From' => $form,
		'MIME-Version' => '1.0',
		'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
		'Content-Transfer-Encoding' => '8Bit',
		'X-Mailer' => 'Drupal',
		'Cc' => $cc,
		'Bcc' => $bcc
	);
	if (!drupal_mail('case_study', 'case_study_proposal_received', $email_to, user_preferred_language($user), $params, $form, TRUE))
		drupal_set_message('Error sending email message.', 'error');
	drupal_set_message(t('We have received your case study proposal. We will get back to you soon.'), 'status');
	drupal_goto('');
}