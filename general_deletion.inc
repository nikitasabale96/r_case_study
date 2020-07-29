<?php
/******************************************************************************/
/****************************** DELETION FUNCTIONS ****************************/
/******************************************************************************/
function case_study_abstract_delete_project($proposal_id)
{
	$status = TRUE;
	$root_path = r_case_study_path();
	$query = db_select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('id', $proposal_id);
	$proposal_q = $query->execute();
	$proposal_data = $proposal_q->fetchObject();
	if (!$proposal_data)
	{
		drupal_set_message('Invalid Case Study Project.', 'error');
		return FALSE;
	} //!$proposal_data
	$query = db_select('case_study_submitted_abstracts_file');
	$query->fields('case_study_submitted_abstracts_file');
	$query->condition('proposal_id', $proposal_id);
	$abstract_q = $query->execute();
	$dir_project_files = $root_path . $proposal_data->directory_name;
	while ($abstract_data = $abstract_q->fetchObject())
	{
		if (is_dir($dir_project_files)){

		unlink($root_path . $proposal_data->directory_name . '/' . $abstract_data->filepath);
		}
		else
		{
			drupal_set_message('Invalid case study project abstract.', 'error');
		}
		db_delete('case_study_submitted_abstracts_file')->condition('proposal_id', $proposal_id)->execute();
		//!dwsim_flowsheet_delete_abstract_file($abstract_data->id)
	}
	$res = rmdir($root_path . $proposal_data->directory_name);
	db_delete('case_study_proposal')->condition('id', $proposal_data->id)->execute();
	db_delete('case_study_submitted_abstracts')->condition('proposal_id', $proposal_id)->execute();
	return $status;
}