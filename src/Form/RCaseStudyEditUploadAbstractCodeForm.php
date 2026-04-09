<?php

/**
 * @file
 * Contains \Drupal\r_case_study\Form\RCaseStudyEditUploadAbstractCodeForm.
 */

namespace Drupal\r_case_study\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\r_case_study\Form\stdClass;
use Drupal\Core\Database\Database;

class RCaseStudyEditUploadAbstractCodeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'r_case_study_edit_upload_abstract_code_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $form['#attributes'] = ['enctype' => "multipart/form-data"];
    /* get current proposal */
    // $proposal_id = (int) arg(3);
    $route_match = \Drupal::routeMatch();

    $proposal_id = (int) $route_match->getParameter('id');
    //var_dump($proposal_id);die;
    $uid = $user->uid;
    $query = \Drupal::database()->select('case_study_proposal');
    $query->fields('case_study_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    //var_dump($proposal_data);die;

    if (!$proposal_data) {
      // \Drupal::messenger()->addMessage(t('Invalid proposal selected. Please try again.'), 'error');
      // drupal_goto('case-study-project/manage-proposal/edit-upload-file');
      //return;
    } //$proposal_q
    else {
      $query = \Drupal::database()->select('case_study_submitted_abstracts');
      $query->fields('case_study_submitted_abstracts');
      $query->condition('proposal_id', $proposal_data->id);
      $abstracts_q = $query->execute()->fetchObject();
      $form['project_title'] = [
        '#type' => 'item',
        '#markup' => $proposal_data->project_title,
        '#title' => t('Title of the Case Study Project'),
      ];
      $form['contributor_name'] = [
        '#type' => 'item',
        '#markup' => $proposal_data->contributor_name,
        '#title' => t('Contributor Name'),
      ];
      $existing_uploaded_R_file = \Drupal::service("r_case_study_global")->default_value_for_uploaded_files("R", $proposal_data->id);
      if (!$existing_uploaded_R_file) {
        $existing_uploaded_R_file = new \stdClass();
        $existing_uploaded_R_file->filename = "No file uploaded";
      } //!$existing_uploaded_A_file
      $form['upload_case_study_abstract'] = [
        '#type' => 'file',
        '#title' => t('Upload the Case study abstract'),
        //'#required' => TRUE,
        '#description' => t('<span style="color:red;">Current File :</span> ' . $existing_uploaded_R_file->filename . '<br />Separate filenames with underscore. No spaces or any special characters allowed in filename.') . '<br />' . t('<span style="color:red;">Allowed file extensions : ') . \Drupal::config('r_case_study.settings')->get('resource_upload_extensions', '') . '</span>',
      ];
       
      $existing_uploaded_C_file = \Drupal::service("r_case_study_global")->default_value_for_uploaded_files("C", $proposal_data->id);
      if (!$existing_uploaded_C_file) {
        $existing_uploaded_C_file = new \stdClass();
        $existing_uploaded_C_file->filename = "No file uploaded";
      } //!$existing_uploaded_S_file
      $form['upload_case_study_developed_process'] = [
        '#type' => 'file',
        '#title' => t('Upload the Case Directory'),
        //'#required' => TRUE,
        '#description' => t('<span style="color:red;">Current File :</span> ' . $existing_uploaded_C_file->filename . '<br />Separate filenames with underscore. No spaces or any special characters allowed in filename.') . '<br />' . t('<span style="color:red;">Allowed file extensions : ') . \Drupal::config('r_case_study.settings')->get('case_study_project_files_extensions', '') . '</span>',
        
      ];
      $form['prop_id'] = [
        '#type' => 'hidden',
        '#value' => $proposal_data->id,
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => t('Submit'),
        // '#submit' => [
        //   // 'r_case_study_edit_upload_abstract_code_form_submit'
        //   'r_case_study.edit_upload_abstract_code_form'
        //   ],
      ];
      $form['cancel'] = [
        '#type' => 'item',
        // '#markup' => l(t('Cancel'), 'case-study-project/manage-proposal/edit-upload-file'),
        '#markup' => Link::fromTextAndUrl(
          $this->t('Cancel'),
          Url::fromUri('internal:/case-study-project/manage-proposal/edit-upload-file'))->toString(),
      ];
      return $form;
    }
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $v = $form_state->getValues();
    $root_path = \Drupal::service("r_case_study_global")->r_case_study_path();
    $query = \Drupal::database()->select('case_study_proposal');
    $query->fields('case_study_proposal');
    $query->condition('id', $v['prop_id']);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    $proposal_id = $proposal_data->id;
    if (!$proposal_data) {
      // drupal_goto('');
      return;
    } //!$proposal_data
    $proposal_id = $proposal_data->id;
    $proposal_directory = $proposal_data->directory_name;
    /* create proposal folder if not present */
    //$dest_path = $proposal_directory . '/';
    $dest_path_project_files = $proposal_directory . '/project_files/';
    $proposal_id = $proposal_data->id;
    foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {

      if ($file_name) {
        /* uploading file */
        /* checking file type */
        if (strstr($file_form_name, 'upload_case_study_abstract')) {
          $file_type = 'R';
          $abs_file_name = $_FILES['files']['name'][$file_form_name];
        }
        else {
          $abs_file_name = "Not updated";
        }
        if (strstr($file_form_name, 'upload_case_study_developed_process')) {
          $file_type = 'C';
          $proj_file_name = $_FILES['files']['name'][$file_form_name];
        }
        else {
          $proj_file_name = "Not updated";
        }
        if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name])) {
          $query_ab_f = "SELECT * FROM case_study_submitted_abstracts_file WHERE proposal_id = :proposal_id AND filetype =
				:filetype";
          $args_ab_f = [
            ":proposal_id" => $proposal_id,
            ":filetype" => $file_type,
          ];
          $query_ab_f_result = \Drupal::database()->query($query_ab_f, $args_ab_f)->fetchObject();
          unlink($root_path . $dest_path_project_files . $query_ab_f_result->filename);
          $query = "UPDATE {case_study_submitted_abstracts_file} SET filename = :filename, filepath=:filepath, filemime=:filemime, filesize=:filesize, timestamp=:timestamp WHERE proposal_id = :proposal_id AND filetype = :filetype";
          $args = [
            ":filename" => $_FILES['files']['name'][$file_form_name],
            ":filepath" => $file_path . $_FILES['files']['name'][$file_form_name],
            ":filemime" => mime_content_type($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]),
            ":filesize" => $_FILES['files']['size'][$file_form_name],
            ":timestamp" => time(),
            ":proposal_id" => $proposal_id,
            ":filetype" => $file_type,
          ];
          \Drupal::database()->query($query, $args, ['return' => Database::RETURN_INSERT_ID]);

          \Drupal::messenger()->addMessage($file_name . ' file updated successfully.', 'status');

        }
        else {
          \Drupal::messenger()->addMessage($file_name . ' file not updated successfully.', 'error');
        }
      }
    } //$_FILES['files']['name'] as $file_form_name => $file_name
    /* sending email */

$mailManager = \Drupal::service('plugin.manager.mail');
$langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();

$config = \Drupal::config('r_case_study.settings');

$email_to = $user->getEmail();
$from = $config->get('case_study_from_email') ?: \Drupal::config('system.site')->get('mail');
$cc   = $config->get('case_study_cc_emails');
$bcc  = $config->get('case_study_emails');

$params = [
  'proposal_id' => $proposal_id,
  'user_id' => $user->id(),
  'abs_file' => $abs_file_name,
  'proj_file' => $proj_file_name,
  'headers' => [
    'From' => $from,
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/plain; charset=UTF-8',
    'Content-Transfer-Encoding' => '8Bit',
    'X-Mailer' => 'Drupal',
  ],
];

// Add CC/BCC only if present
if (!empty($cc)) {
  $params['headers']['Cc'] = $cc;
}
if (!empty($bcc)) {
  $params['headers']['Bcc'] = $bcc;
}

$result = $mailManager->mail(
  'r_case_study', // ✅ must match module name
  'abstract_edit_file_uploaded',
  $email_to,
  $langcode,
  $params,
  $from,
  TRUE
);

if (empty($result['result'])) {
  \Drupal::messenger()->addError('Error sending email message.');
}
else {
  \Drupal::messenger()->addStatus('Email sent successfully.');
}
 // drupal_goto('case-study-project/abstract-code/edit-upload-files/' . $proposal_id);
    $form_state->setRedirect('r_case_study.proposal_edit_file_all', ['proposal_id' => $proposal_id]);
 
  }

}
?>
