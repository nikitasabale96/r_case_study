<?php
/******************************************************************************/
/****************************** DELETION FUNCTIONS ****************************/
/******************************************************************************/
function case_study_delete_abstract_file($abstract_id)
{
	$status = TRUE;
	$root_path = r_case_study_path();
	$abstract_q = \Drupal::database()->query("SELECT * from case_study_proposal dfp
LEFT JOIN case_study_submitted_abstracts dfsa ON dfp.id = dfsa.proposal_id
WHERE dfsa.id = :abstract_id", array(
		":abstract_id" => $abstract_id
	));
	$abstract_data = $abstract_q->fetchObject();
	if (!$abstract_data)
	{
		\Drupal::messenger()->addError('Invalid case study project abstract.');
		return FALSE;
	} //!$abstract_data
	/* deleting solutions */
	$query = \Drupal::database()->select('case_study_submitted_abstracts_file');
	$query->fields('case_study_submitted_abstracts_file');
	$query->condition('submitted_abstract_id', $abstract_id);
	$abstract_f_q = $query->execute();
	
	while ($abstract_f_data = $abstract_f_q->fetchObject())
	{
		unlink($root_path . $abstract_data->directory_name . '/project_files/' . $abstract_f_data->filepath);

	} 
	$res = rmdir($root_path . $abstract_data->directory_name . '/project_files');
	return True;
}
function case_study_abstract_delete_project($proposal_id)
{
	$status = TRUE;
	$root_path = r_case_study_path();
	//$proposal_q = \Drupal::database()->query("SELECT * FROM {case_study_proposal} WHERE id = %d", $lab_id);
	$query = \Drupal::database()->select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('id', $proposal_id);
	$proposal_q = $query->execute();
	$proposal_data = $proposal_q->fetchObject();
	if (!$proposal_data)
	{
		\Drupal::messenger()->addError('Invalid Case Study Project.');
		return FALSE;
	} //!$proposal_data
	$query = \Drupal::database()->select('case_study_submitted_abstracts');
	$query->fields('case_study_submitted_abstracts');
	$query->condition('proposal_id', $proposal_data->id);
	$abstract_q = $query->execute();
	while ($abstract_data = $abstract_q->fetchObject())
	{
		if (!case_study_delete_abstract_file($abstract_data->id))
		{
			$status = FALSE;
		} //!case_study_delete_abstract_file($abstract_data->id)
	} //$abstract_data = $abstract_q->fetchObject()
	$dir_path_udc = $root_path . $proposal_data->directory_name;
	$query = \Drupal::database()->select('case_study_proposals_file');
	$query->fields('case_study_proposals_file');
	$query->condition('proposal_id', $proposal_data->id);
	$proposal_files = $query->execute();
	while($proposal_files_data = $proposal_files->fetchObject()){
	if (is_dir($dir_path_udc))
	{
		unlink($root_path . $proposal_files_data->filepath);
		$res = rmdir($dir_path_udc);
	} //is_dir($dir_path_udc)
}
	$dir_main_path = $root_path . $proposal_data->directory_name;
	//var_dump($dir_main_path);die;
	if (is_dir($dir_main_path))
	{
		$res_main = rmdir($dir_main_path);
	} //is_dir($dir_main_path)
	\Drupal::database()->delete('case_study_proposal')->condition('id', $proposal_data->id)->execute();
	\Drupal::database()->delete('case_study_proposals_file')->condition('proposal_id', $proposal_data->id)->execute();
	\Drupal::database()->delete('case_study_submitted_abstracts')->condition('proposal_id', $proposal_data->id)->execute();
	\Drupal::database()->delete('case_study_submitted_abstracts_file')->condition('proposal_id', $proposal_data->id)->execute();
	return $status;
}