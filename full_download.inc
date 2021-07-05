<?php
// $Id$
function r_case_study_download_full_project()
{
	global $user;
	$id = arg(3);
	$root_path = r_case_study_path();
	//var_dump($_SERVER['DOCUMENT_ROOT'] . base_path());die;
	$query = db_select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('id', $id);
	$case_study_q = $query->execute();
	$case_study_data = $case_study_q->fetchObject();
	$CS_PATH = $case_study_data->directory_name . '/';
	/* zip filename */
	$zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
	/* creating zip archive on the server */
	$zip = new ZipArchive();
	$zip->open($zip_filename, ZipArchive::CREATE);
	$query = db_select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('id', $id);
	$case_study_udc_q = $query->execute();
	$query_proposal_files = db_select('case_study_proposals_file');
	$query_proposal_files->fields('case_study_proposals_file');
	$query_proposal_files->condition('proposal_id', $id);
	$proposal_files = $query_proposal_files->execute();
	while ($proposal_files_data = $proposal_files->fetchObject()) {
		$zip->addFile($root_path . $proposal_files_data->filepath, $CS_PATH . str_replace(' ', '_', basename($proposal_files_data->filename)));
	}
	$query = db_select('case_study_submitted_abstracts_file');
	$query->fields('case_study_submitted_abstracts_file');
	$query->condition('proposal_id', $id);
	$project_files = $query->execute();
        //var_dump($project_files->rowCount());die;
	while ($cs_project_files = $project_files->fetchObject()) {
                //var_dump($root_path . $CS_PATH . 'project_files/' . $cs_project_files->filepath);die;
		$zip->addFile($root_path . $CS_PATH . 'project_files/' . $cs_project_files->filepath, $CS_PATH . 'project_files/' . str_replace(' ', '_', basename($cs_project_files->filename)));
	}
	$zip_file_count = $zip->numFiles;
	//var_dump($zip_file_count);die;
	$zip->close();
	if ($zip_file_count > 0) {
		if ($user->uid) {
			/* download zip file */
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $case_study_data->project_title) . '.zip"');
			header('Content-Length: ' . filesize($zip_filename));
			ob_end_flush();
			ob_clean();
			flush();
			readfile($zip_filename);
			unlink($zip_filename);
		} //$user->uid
		else {
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $case_study_data->project_title) . '.zip"');
			header('Content-Length: ' . filesize($zip_filename));
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			ob_end_flush();
			ob_clean();
			flush();
			readfile($zip_filename);
			unlink($zip_filename);
		}
	} //$zip_file_count > 0
	else {
		drupal_set_message("There is no Case Study in this proposal to download", 'error');
		drupal_goto('case-study-project/completed-case-studies');
	}
}
function case_study_download_completed_project()
{
		global $user;
	$id = arg(3);
	$root_path = r_case_study_path();
	$query = db_select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('id', $id);
	$case_study_q = $query->execute();
	$case_study_data = $case_study_q->fetchObject();
	$CS_PATH = $case_study_data->directory_name . '/';
	/* zip filename */
	$zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
	/* creating zip archive on the server */
	$zip = new ZipArchive();
	$zip->open($zip_filename, ZipArchive::CREATE);
	$query = db_select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('id', $id);
	$case_study_udc_q = $query->execute();
	$query = db_select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('id', $id);
	$query = db_select('case_study_submitted_abstracts_file');
	$query->fields('case_study_submitted_abstracts_file');
	$query->condition('proposal_id', $id);
	$project_files = $query->execute();
	//var_dump($root_path . $CS_PATH . 'project_files/');die;
	while ($cs_project_files = $project_files->fetchObject()) {
		$zip->addFile($root_path . $CS_PATH . 'project_files/' . $cs_project_files->filepath, $CS_PATH . str_replace(' ', '_', basename($cs_project_files->filename)));
	}
	$zip_file_count = $zip->numFiles;
	$zip->close();
	if ($zip_file_count > 0) {
		if ($user->uid) {
			/* download zip file */
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $case_study_data->project_title) . '.zip"');
			header('Content-Length: ' . filesize($zip_filename));
			ob_end_flush();
			ob_clean();
			//flush();
			readfile($zip_filename);
			unlink($zip_filename);
		} //$user->uid
		else {
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $case_study_data->project_title) . '.zip"');
			header('Content-Length: ' . filesize($zip_filename));
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			ob_end_flush();
			ob_clean();
			//flush();
			readfile($zip_filename);
			unlink($zip_filename);
		}
	} //$zip_file_count > 0
	else {
		drupal_set_message("There is no Case Case Study in this proposal to download", 'error');
		drupal_goto('case-study-project/completed-case-studies');
	}
}