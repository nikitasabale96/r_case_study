<?php
 
 namespace Drupal\r_case_study\Services;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Database\Database;
use Drupal\Core\DrupalKernel;
use Drupal\user\Entity\User;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;


class RCaseStudyGlobalfunction{

    public function _r_case_study_dir_name($project, $proposar_name)
{
    $project_title = $this->ucname($project);
    $proposar_name = $this->ucname($proposar_name);
    $dir_name = $project_title . ' By ' . $proposar_name;
    $directory_name = str_replace("__", "_", str_replace(" ", "_", str_replace("/", "_", trim($dir_name))));
    return $directory_name;
}
public function ucname($string)
{
$string = ucwords(strtolower($string));
foreach (array(
'-',
'\''
) as $delimiter)
{
if (strpos($string, $delimiter) !== false)
{
$string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
} //strpos($string, $delimiter) !== false
} //array( '-', '\'') as $delimiter
return $string;
}

 public function default_value_for_uploaded_files($filetype, $proposal_id)
{
	$query = \Drupal::database()->select('case_study_submitted_abstracts_file');
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

public function _bulk_list_of_case_study_project()
{
	$project_titles = array(
		'0' => 'Please select...'
	);
	$query = \Drupal::database()->select('case_study_proposal');
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

 public function _bulk_list_case_study_actions()
{
	$case_study_actions = array(
		0 => 'Please select...'
	);
	$case_study_actions[1] = 'Approve Entire Case Study';
	$case_study_actions[2] = 'Resubmit Project files(This will enable resubmission for the contributor)';
	$case_study_actions[3] = 'Dis-Approve Entire Case Study (This will delete the Case Study files and the proposal from the db)';
	//$case_study_actions[4] = 'Delete Entire Case Study Including Proposal';
	return $case_study_actions;
}

 public function _r_case_study_list_of_states()
{
    $states = array(
        0 => '-Select-',
    );
    $query = \Drupal::database()->select('list_states_of_india');
    $query->fields('list_states_of_india');
    //$query->orderBy('', '');
    $states_list = $query->execute();
    while ($states_list_data = $states_list->fetchObject()) {
        $states[$states_list_data->state] = $states_list_data->state;
    } //$states_list_data = $states_list->fetchObject()
    return $states;
}

public function _r_case_study_list_of_cities()
{
    $city = array(
        0 => '-Select-',
    );
    $query = \Drupal::database()->select('list_cities_of_india');
    $query->fields('list_cities_of_india');
    $query->orderBy('city', 'ASC');
    $city_list = $query->execute();
    while ($city_list_data = $city_list->fetchObject()) {
        $city[$city_list_data->city] = $city_list_data->city;
    } //$city_list_data = $city_list->fetchObject()
    return $city;
}
public function _r_case_study_list_of_pincodes()
{
    $pincode = array(
        0 => '-Select-',
    );
    $query = \Drupal::database()->select('list_of_all_india_pincode');
    $query->fields('list_of_all_india_pincode');
    $query->orderBy('pincode', 'ASC');
    $pincode_list = $query->execute();
    while ($pincode_list_data = $pincode_list->fetchObject()) {
        $pincode[$pincode_list_data->pincode] = $pincode_list_data->pincode;
    } //$pincode_list_data = $pincode_list->fetchObject()
    return $pincode;
}
public function _r_case_study_list_of_departments()
{
    $department = array();
    $query = \Drupal::database()->select('list_of_departments');
    $query->fields('list_of_departments');
    $query->orderBy('id', 'DESC');
    $department_list = $query->execute();
    while ($department_list_data = $department_list->fetchObject()) {
        $department[$department_list_data->department] = $department_list_data->department;
    } //$department_list_data = $department_list->fetchObject()
    return $department;
}

public function _cs_list_of_versions(){
    $versions = array();
    $query = \Drupal::database()->select('case_study_software_version');
    $query->fields('case_study_software_version');
    $version_list = $query->execute();
    while($version_data = $version_list->fetchObject()){
        $versions[$version_data->case_study_version] = $version_data->case_study_version;
    }
    return $versions;
}

public function r_case_study_path()
{
    return $_SERVER['DOCUMENT_ROOT'] . base_path() . 'r_uploads/case_study_uploads/';
}

public function r_case_study_get_proposal() {
    $user = \Drupal::currentUser();
    $query = \Drupal::database()->select('case_study_proposal', 'csp');
    $query->fields('csp');
    $query->condition('uid', $user->id());
    $query->orderBy('id', 'DESC');
    $query->range(0, 1);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();

    if (!$proposal_data) {
        \Drupal::messenger()->addError("No approved Case Study proposal found.");
        return false;
    }
    if ($proposal_data->approval_status != 1) {
        \Drupal::messenger()->addError("Case Study proposal is not approved.");
        return false;
    }
    return $proposal_data;
}



// public function createReadmeFileCaseStudyProject($proposal_id) {
//     // Fetch proposal data from the database
//     $query = $this->database->select('case_study_proposal', 'csp')
//         ->fields('csp')
//         ->condition('id', $proposal_id)
//         ->execute();
//     $proposal_data = $query->fetchObject();

//     if (!$proposal_data) {
//         $this->messenger->addError(t('Invalid proposal ID.'));
//         return FALSE;
//     }

//     // Define the directory path
//     $root_path = $this->r_case_study_path();
//     $directory = $root_path . '/' . $proposal_data->directory_name;

//     // Ensure the directory exists
//     if (!$this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
//         $this->messenger->addError(t('Failed to create directory: @dir', ['@dir' => $directory]));
//         return FALSE;
//     }

//     // Define README file path
//     $file_path = $directory . "/README.txt";

//     // Create README content
//     $txt = "About the Case Study\n\n";
//     $txt .= "Title Of The Case Study Project: " . $proposal_data->project_title . "\n";
//     $txt .= "Proposer Name: " . $proposal_data->name_title . " " . $proposal_data->contributor_name . "\n";
//     $txt .= "University: " . $proposal_data->university . "\n\n";
//     $txt .= "Case Study Project By FOSSEE, IIT Bombay\n";

//     // Write content to file
//     if (file_put_contents($file_path, $txt) === FALSE) {
//         $this->messenger->addError(t('Failed to write to file: @file', ['@file' => $file_path]));
//         return FALSE;
//     }

//     // Return the text content
//     return $txt;
// }


// public function createReadmeFileCaseStudyProject($proposal_id) {

//   // Fetch proposal data
//   $proposal_data = $this->database->select('case_study_proposal', 'csp')
//     ->fields('csp')
//     ->condition('id', $proposal_id)
//     ->range(0, 1)
//     ->execute()
//     ->fetchObject();

//   if (!$proposal_data) {
//     $this->messenger->addError(t('Invalid proposal ID.'));
//     return FALSE;
//   }

//   // Directory path
//   $root_path = $this->r_case_study_path();
//   $directory = $root_path . '/' . $proposal_data->directory_name;

//   // Ensure directory exists
//   if (!$this->fileSystem->prepareDirectory(
//     $directory,
//     FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
//   )) {
//     $this->messenger->addError(t('Failed to create directory: @dir', ['@dir' => $directory]));
//     return FALSE;
//   }

//   // File path
//   $file_path = $directory . '/README.txt';

//   // File content
//   $txt = "About the Case Study\n\n";
//   $txt .= "Title Of The Case Study Project: {$proposal_data->project_title}\n";
//   $txt .= "Proposer Name: {$proposal_data->name_title} {$proposal_data->contributor_name}\n";
//   $txt .= "University: {$proposal_data->university}\n\n";
//   $txt .= "Case Study Project By FOSSEE, IIT Bombay\n";

//   // Write file using Drupal API
//   try {
//     $this->fileSystem->saveData($txt, $file_path, FileSystemInterface::EXISTS_REPLACE);
//   }
//   catch (\Exception $e) {
//     $this->messenger->addError(t('Failed to write file: @msg', ['@msg' => $e->getMessage()]));
//     return FALSE;
//   }

//   return $txt;
// }
function CreateReadmeFileCaseStudyProject($proposal_id)
{
    $result = \Drupal::database()->query("
                        SELECT * from case_study_proposal WHERE id = :proposal_id", array(
        ":proposal_id" => $proposal_id,
    ));
    $proposal_data = $result->fetchObject();
    $root_path = $this->r_case_study_path();
    $readme_file = fopen($root_path . $proposal_data->directory_name . "/README.txt", "w") or die("Unable to open file!");
    $txt = "";
    $txt .= "About the Case Study";
    $txt .= "\n" . "\n";
    $txt .= "Title Of The Case Study Project: " . $proposal_data->project_title . "\n";
    $txt .= "Proposar Name: " . $proposal_data->name_title . " " . $proposal_data->contributor_name . "\n";
    $txt .= "University: " . $proposal_data->university . "\n";
    $txt .= "\n" . "\n";
    $txt .= " Case Study Project By FOSSEE, IIT Bombay" . "\n";
    fwrite($readme_file, $txt);
    fclose($readme_file);
    return $txt;
}


function rrmdir_project($prop_id) {

  $proposal_id = $prop_id;

  // Fetch proposal data
  $proposal_data = \Drupal::database()
    ->select('case_study_proposal', 'csp')
    ->fields('csp')
    ->condition('id', $proposal_id)
    ->range(0, 1)
    ->execute()
    ->fetchObject();

  if (!$proposal_data) {
    \Drupal::messenger()->addError('Data not found');
    return;
  }

  $root_path = $this->r_case_study_path();
  $dir = $root_path . $proposal_data->directory_name;

  if ($proposal_data->id == $prop_id) {

    if (is_dir($dir)) {

      $objects = scandir($dir);

      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {

          $path = $dir . "/" . $object;

          if (is_dir($path)) {
            rrmdir_project_recursive($path);
          }
          else {
            unlink($path);
          }
        }
      }

      rmdir($dir);

      \Drupal::messenger()->addStatus("Directory deleted successfully");
      return;
    }

    \Drupal::messenger()->addError("Directory not present");
    return;
  }

  \Drupal::messenger()->addError("Data not found");
}

function CaseStudy_RenameDir($proposal_id, $dir_name) {

  // Fetch data
  $result = \Drupal::database()
    ->select('case_study_proposal', 'csp')
    ->fields('csp', ['directory_name', 'id'])
    ->condition('id', $proposal_id)
    ->range(0, 1)
    ->execute()
    ->fetchObject();

  if ($result) {

    $root_path = $this->r_case_study_path();

    $files_id_dir = $root_path . $result->id;
    $file_dir = $root_path . $result->directory_name;

    if (is_dir($file_dir)) {
      $new_directory_name = rename($file_dir, $root_path . $dir_name);
      return $new_directory_name;
    }
    elseif (is_dir($files_id_dir)) {
      $new_directory_name = rename($files_id_dir, $root_path . $dir_name);
      return $new_directory_name;
    }
    else {
      \Drupal::messenger()->addError('Directory not available for rename.');
      return;
    }
  }
  else {
    \Drupal::messenger()->addError('Project directory name not present in database');
    return;
  }
}

}
