<?php

/**
 * @file
 * Contains \Drupal\r_case_study\Form\RCaseStudyUploadAbstractCodeForm.
 */

namespace Drupal\r_case_study\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Database\Database;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\StringTranslation\TranslatableMarkup;
// use Drupal\Component\Render\Markup;
use Drupal\Core\Render\Markup; 


class RCaseStudyUploadAbstractCodeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'r_case_study_upload_abstract_code_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $form['#attributes'] = ['enctype' => "multipart/form-data"];
    /* get current proposal */
    //$proposal_id = (int) arg(3);
    // $uid = $user->id();
    // $query = \Drupal::database()->select('case_study_proposal');
    // $query->fields('case_study_proposal');
    // $query->condition('uid', $uid);
    // $query->condition('approval_status', '1');
    // $proposal_q = $query->execute();

    $uid = $user->id();
$query = \Drupal::database()->select('case_study_proposal', 'csp'); // Added alias 'csp'
$query->fields('csp', ['project_title', 'contributor_name']); // Specify only needed fields
$query->condition('csp.uid', $uid);
$query->condition('csp.approval_status', 1); // Remove quotes around integer values
$query->orderBy('csp.id', 'DESC');
$query->range(0, 1);
$proposal_data = $query->execute()->fetchObject();

    // if ($proposal_q) {
    //   if ($proposal_data = $proposal_q->fetchObject()) {
    //     /* everything ok */
    //   } //$proposal_data = $proposal_q->fetchObject()
    //   else {
    //     \Drupal::messenger()->addMessage(t('Invalid proposal selected. Please try again.'), 'error');
    //     // drupal_goto('case-study-project/abstract-code');
    //     return;
    //   }
    // } //$proposal_q
    // else {
    //   \Drupal::messenger()->addMessage(t('Invalid proposal selected. Please try again.'), 'error');
    //   // drupal_goto('case-study-project/abstract-code');
    //   return;
    // }
    $query = \Drupal::database()->select('case_study_submitted_abstracts');
    $query->fields('case_study_submitted_abstracts');
    $query->condition('proposal_id', $proposal_data->id);
    $abstracts_q = $query->execute()->fetchObject();
    if ($abstracts_q) {
      if ($abstracts_q->is_submitted == 1) {
        \Drupal::messenger()->addMessage(t('You have already submited your project files, hence you can not upload more code, for any query please write to us.'), 'error', $repeat = FALSE);
        // drupal_goto('case-study-project/abstract-code');
        //return;
      } //$abstracts_q->is_submitted == 1
    } //$abstracts_q->is_submitted == 1
    $form['project_title'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->project_title,
      '#title' => t('Title of the Case Study'),
    ];
    $form['contributor_name'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->contributor_name,
      '#title' => t('Contributor Name'),
    ];
    $existing_uploaded_report_file = \Drupal::service("r_case_study_global")->default_value_for_uploaded_files("R", $proposal_data->id);
    if (!$existing_uploaded_report_file) {
      $existing_uploaded_report_file = new \stdClass();
      $existing_uploaded_report_file->filename = "No file uploaded";
    } //!$existing_uploaded_report_file
    $form['report_file'] = [
      '#type' => 'fieldset',
      '#title' => t('Upload the report of the project.<span style="color:red">*</span>'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    // $form['report_file']['upload_report'] = [
    //   '#type' => 'file',
    //   '#description' => t(t('<span style="color:red;">Current File :</span> ' . $existing_uploaded_report_file->filename . '<br />' . t('<span style="color:red;">Allowed file extensions: ') . \Drupal::config('case_study.settings')->get('project_report_upload_extensions', '') . '</span>')),
    // ];
   
    $form['report_file']['upload_report'] = [
      '#type' => 'file',
      '#description' => $this->t('<span style="color:red;">Current File :</span> @filename <br />
                                 <span style="color:red;">Allowed file extensions: </span> @extensions', [
          '@filename' => !empty($existing_uploaded_report_file->filename) ? $existing_uploaded_report_file->filename : 'No file uploaded',
          '@extensions' =>  \Drupal::config('r_case_study.settings')->get('project_report_upload_extensions', ''),
      ]),
  ];
  

    $existing_uploaded_code_file = \Drupal::service("r_case_study_global")->default_value_for_uploaded_files("C", $proposal_data->id);
    if (!$existing_uploaded_code_file) {
      $existing_uploaded_code_file = new \stdClass();
      $existing_uploaded_code_file->filename = "No file uploaded";
    } //!$existing_uploaded_code_file
    $form['code_file'] = [
      '#type' => 'fieldset',
      '#title' => t('Upload the data and code files of the project.<span style="color:red">*</span>'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['code_file']['upload_code_file'] = [
      '#type' => 'file',
      '#description' => $this->t('<span style="color:red;">Current File :</span> ' . $existing_uploaded_code_file->filename . '<br />Separate filenames with underscore. No spaces or any special characters allowed in filename.') . '<br />' . t('<span style="color:red;">Allowed file extensions: ') .  \Drupal::config('r_case_study.settings')->get('project_code_file_upload_extensions', '') . '</span>',
    ];
    $form['prop_id'] = [
      '#type' => 'hidden',
      '#value' => $proposal_data->id,
    ];
    // $form['submit'] = [
    //   '#type' => 'submit',
    //   '#value' => t('Submit'),
    //   '#submit' => [
    //     'r_case_study.upload_abstract_code_form'
    //     ],
    // ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'), // Use $this->t() inside a form class
      '#submit' => ['::submitForm'], // Call the class method
  ];
  
  
    $form['cancel'] = [
      '#type' => 'item',
      // '#markup' => l(t('Cancel'), 'case-study-project/abstract-code'),
    ];
    return $form;
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    if (isset($_FILES['files'])) {
      /* check if file is uploaded */
      $existing_uploaded_report_file = \Drupal::service("r_case_study_global")->default_value_for_uploaded_files("R", $form_state->getValue([
        'prop_id'
        ]));
      $existing_uploaded_code_file = \Drupal::service("r_case_study_global")->default_value_for_uploaded_files("C", $form_state->getValue([
        'prop_id'
        ]));
      if (!$existing_uploaded_code_file) {
        if (!($_FILES['files']['name']['upload_code_file'])) {
          $form_state->setErrorByName('upload_code_file', t('Please upload the file.'));
        }
      } //!$existing_uploaded_code_file
      if (!$existing_uploaded_report_file) {
        if (!($_FILES['files']['name']['upload_report'])) {
          $form_state->setErrorByName('upload_report', t('Please upload the file.'));
        }
      } //!$existing_uploaded_report_file
		/* check for valid filename extensions */

      /* check if atleast one source or result file is uploaded */
      /*if (!($_FILES['files']['name']['upload_report']))
			form_set_error('upload_report', t('Please upload the abstract file'));
		if(!($_FILES['files']['name']['upload_code_file']))
			form_set_error('upload_code_file', t('Please upload the raw data file'));*/
      /* check for valid filename extensions */
      if ($_FILES['files']['name']['upload_report'] || $_FILES['files']['name']['upload_code_file']) {
        foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
          if ($file_name) {
            /* checking file type */
            if (strstr($file_form_name, 'upload_report')) {
              $file_type = 'R';
            }
            else {
              if (strstr($file_form_name, 'upload_code_file')) {
                $file_type = 'C';
              }
            }
            $allowed_extensions_str = '';
            switch ($file_type) {
              case 'R':
                $allowed_extensions_str = \Drupal::config('r_case_study.settings')->get('project_report_upload_extensions', '');
                break;
              case 'C':
                $allowed_extensions_str = \Drupal::config('r_case_study.settings')->get('project_code_file_upload_extensions', '');
                break;
            }
            $allowed_extensions = explode(',', $allowed_extensions_str);
            $fnames = explode('.', strtolower($_FILES['files']['name'][$file_form_name]));
            $temp_extension = end($fnames);
            if (!in_array($temp_extension, $allowed_extensions)) {
              $form_state->setErrorByName($file_form_name, t('Only file with ' . $allowed_extensions_str . ' extensions can be uploaded.'));
            }
            if ($_FILES['files']['size'][$file_form_name] <= 0) {
              $form_state->setErrorByName($file_form_name, t('File size cannot be zero.'));
            }
            /* check if valid file name */
            // if (!textbook_companion_check_valid_filename($_FILES['files']['name'][$file_form_name])) {
            //   $form_state->setErrorByName($file_form_name, t('Invalid file name specified. Only alphabets and numbers are allowed as a valid filename.'));
            // }
          } //$file_name
        } //$_FILES['files']['name'] as $file_form_name => $file_name
      }
    }
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $v = $form_state->getValues();
    $root_path = \Drupal::service("r_case_study_global")->r_case_study_path();
    $proposal_data = \Drupal::service("r_case_study_global")->r_case_study_get_proposal();
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
    if (!is_dir($root_path . $dest_path_project_files)) {
      mkdir($root_path . $dest_path_project_files);
    }
    $proposal_id = $proposal_data->id;
    $query_s = "SELECT * FROM {case_study_submitted_abstracts} WHERE proposal_id = :proposal_id";
    $args_s = [":proposal_id" => $proposal_id];
    $query_s_result = \Drupal::database()->query($query_s, $args_s)->fetchObject();
    if (!$query_s_result) {
      /* creating solution database entry */
      $query = "INSERT INTO {case_study_submitted_abstracts} (
	proposal_id,
	approver_uid,
	abstract_approval_status,
	abstract_upload_date,
	abstract_approval_date,
	is_submitted) VALUES (:proposal_id, :approver_uid, :abstract_approval_status, :abstract_upload_date, :abstract_approval_date, :is_submitted)";
      $args = [
        ":proposal_id" => $proposal_id,
        ":approver_uid" => 0,
        ":abstract_approval_status" => 0,
        ":abstract_upload_date" => time(),
        ":abstract_approval_date" => 0,
        ":is_submitted" => 1,
      ];
      $submitted_abstract_id = \Drupal::database()->query($query, $args, [
        'return' => Database::RETURN_INSERT_ID
        ]);

      $query1 = "UPDATE {case_study_proposal} SET is_submitted = :is_submitted WHERE id = :id";
      $args1 = [
        ":is_submitted" => 1,
        ":id" => $proposal_id,
      ];
      \Drupal::database()->query($query1, $args1);
      \Drupal::messenger()->addMessage('Abstract uploaded successfully.', 'status');
    } //!$query_s_result
    else {
      $query = "UPDATE {case_study_submitted_abstracts} SET 

	abstract_upload_date =:abstract_upload_date,
	is_submitted= :is_submitted 
	WHERE proposal_id = :proposal_id
	";
      $args = [
        ":abstract_upload_date" => time(),
        ":is_submitted" => 1,
        ":proposal_id" => $proposal_id,
      ];
      $submitted_abstract_id = \Drupal::database()->query($query, $args, [
        'return' => Database::RETURN_INSERT_ID
        ]);
      $query1 = "UPDATE {case_study_proposal} SET is_submitted = :is_submitted WHERE id = :id";
      $args1 = [
        ":is_submitted" => 1,
        ":id" => $proposal_id,
      ];
      \Drupal::database()->query($query1, $args1);
      \Drupal::messenger()->addMessage('Abstract updated successfully.', 'status');
    }
    foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
      if ($file_name) {
        /* checking file type */
        if (strstr($file_form_name, 'upload_code_file')) {
          $file_type = 'C';
        } //strstr($file_form_name, 'upload_code_file')
        else {
          if (strstr($file_form_name, 'upload_report')) {
            $file_type = 'R';
          }
        }
        switch ($file_type) {
          case 'C':
            if (file_exists($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name])) {
              //unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
              \Drupal::messenger()->addMessage(t("File !filename already exists hence overwirtten the exisitng file ", [
                '!filename' => $_FILES['files']['name'][$file_form_name]
                ]), 'error');
            } //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					/* uploading file */
            else {
              if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name])) {
                /* for uploaded files making an entry in the database */
                $query_ab_f = "SELECT * FROM case_study_submitted_abstracts_file WHERE proposal_id = :proposal_id AND filetype = 
				:filetype";
                $args_ab_f = [
                  ":proposal_id" => $proposal_id,
                  ":filetype" => $file_type,
                ];
                $query_ab_f_result = \Drupal::database()->query($query_ab_f, $args_ab_f)->fetchObject();
                if (!$query_ab_f_result) {
                  $query = "INSERT INTO {case_study_submitted_abstracts_file} (submitted_abstract_id, proposal_id, uid, approvar_uid, filename, filepath, filemime, filesize, filetype, timestamp)
          VALUES (:submitted_abstract_id, :proposal_id, :uid, :approvar_uid, :filename, :filepath, :filemime, :filesize, :filetype, :timestamp)";
                  $args = [
                    ":submitted_abstract_id" => $submitted_abstract_id,
                    ":proposal_id" => $proposal_id,
                    ":uid" => $user->uid,
                    ":approvar_uid" => 0,
                    ":filename" => $_FILES['files']['name'][$file_form_name],
                    ":filepath" => $_FILES['files']['name'][$file_form_name],
                    ":filemime" => mime_content_type($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]),
                    ":filesize" => $_FILES['files']['size'][$file_form_name],
                    ":filetype" => $file_type,
                    ":timestamp" => time(),
                  ];
                  \Drupal::database()->query($query, $args);
                  \Drupal::messenger()->addMessage($file_name . ' uploaded successfully.', 'status');
                } //!$query_ab_f_result
                else {
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
                  \Drupal::database()->query($query, $args);
                  \Drupal::messenger()->addMessage($file_name . ' file updated successfully.', 'status');
                }
              } //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
              else {
                // \Drupal::messenger()->addMessage('Error uploading file : ' . $dest_path_project_files . $file_name, 'error');
              }
            }
            break;
          case 'R':
            if (file_exists($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name])) {
              //unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
              \Drupal::messenger()->addMessage(t("File !filename already exists hence overwirtten the exisitng file ", [
                '!filename' => $_FILES['files']['name'][$file_form_name]
                ]), 'error');
            } //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					/* uploading file */
            else {
              if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name])) {
                /* for uploaded files making an entry in the database */
                $query_ab_f = "SELECT * FROM case_study_submitted_abstracts_file WHERE proposal_id = :proposal_id AND filetype = 
				:filetype";
                $args_ab_f = [
                  ":proposal_id" => $proposal_id,
                  ":filetype" => $file_type,
                ];
                $query_ab_f_result = \Drupal::database()->query($query_ab_f, $args_ab_f)->fetchObject();
                if (!$query_ab_f_result) {
                  $query = "INSERT INTO {case_study_submitted_abstracts_file} (submitted_abstract_id, proposal_id, uid, approvar_uid, filename, filepath, filemime, filesize, filetype, timestamp)
          VALUES (:submitted_abstract_id, :proposal_id, :uid, :approvar_uid, :filename, :filepath, :filemime, :filesize, :filetype, :timestamp)";
                  $args = [
                    ":submitted_abstract_id" => $submitted_abstract_id,
                    ":proposal_id" => $proposal_id,
                    ":uid" => $user->uid,
                    ":approvar_uid" => 0,
                    ":filename" => $_FILES['files']['name'][$file_form_name],
                    ":filepath" => $_FILES['files']['name'][$file_form_name],
                    ":filemime" => mime_content_type($root_path . $dest_path_project_files . $_FILES['files']['name'][$file_form_name]),
                    ":filesize" => $_FILES['files']['size'][$file_form_name],
                    ":filetype" => $file_type,
                    ":timestamp" => time(),
                  ];
                  \Drupal::database()->query($query, $args);
                  \Drupal::messenger()->addMessage($file_name . ' uploaded successfully.', 'status');
                } //!$query_ab_f_result
                else {
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
                  \Drupal::database()->query($query, $args);
                  \Drupal::messenger()->addMessage($file_name . ' file updated successfully.', 'status');
                }
              } //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
              else {
                // \Drupal::messenger()->addMessage('Error uploading file : ' . $dest_path_project_files . $file_name, 'error');
              }
            }
            break;
        } //$file_type
      } //$file_name
    } //$_FILES['files']['name'] as $file_form_name => $file_name
	/* sending email */
    // $email_to = $user->mail;
    // $from = variable_get('case_study_from_email', '');
    // $bcc = variable_get('case_study_emails', '');
    // $cc = variable_get('case_study_cc_emails', '');
    // $params['abstract_uploaded']['proposal_id'] = $proposal_id;
    // $params['abstract_uploaded']['submitted_abstract_id'] = $submitted_abstract_id;
    // $params['abstract_uploaded']['user_id'] = $user->uid;
    // $params['abstract_uploaded']['headers'] = [
    //   'From' => $from,
    //   'MIME-Version' => '1.0',
    //   'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
    //   'Content-Transfer-Encoding' => '8Bit',
    //   'X-Mailer' => 'Drupal',
    //   'Cc' => $cc,
    //   'Bcc' => $bcc,
    // ];
    // if (!drupal_mail('case_study', 'abstract_uploaded', $email_to, language_default(), $params, $from, TRUE)) {
    //   \Drupal::messenger()->addMessage('Error sending email message.', 'error');
    // }
    // drupal_goto('case-study-project/abstract-code');
    $response = new RedirectResponse(Url::fromRoute('r_case_study.abstract')->toString());
$response->send();
  }

}
?>
