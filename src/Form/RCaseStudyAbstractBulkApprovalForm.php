<?php

/**
 * @file
 * Contains \Drupal\r_case_study\Form\RCaseStudyAbstractBulkApprovalForm.
 */

namespace Drupal\r_case_study\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class RCaseStudyAbstractBulkApprovalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'r_case_study_abstract_bulk_approval_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $options_first = _bulk_list_of_case_study_project();
    $selected = !$form_state->getValue(['case_study_project']) ? $form_state->getValue([
      'case_study_project'
      ]) : key($options_first);
    $form = [];
    $form['case_study_project'] = [
      '#type' => 'select',
      '#title' => t('Title of the Case Study'),
      '#options' => _bulk_list_of_case_study_project(),
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => 'ajax_bulk_case_study_abstract_details_callback'
        ],
      '#suffix' => '<div id="ajax_selected_case_study"></div><div id="ajax_selected_case_study_pdf"></div>',
    ];
    $form['case_study_actions'] = [
      '#type' => 'select',
      '#title' => t('Please select action for Case Study'),
      '#options' => _bulk_list_case_study_actions(),
      '#default_value' => 0,
      '#prefix' => '<div id="ajax_selected_case_study_action" style="color:red;">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="case_study_project"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => t('Please specify the reason for marking resubmit/disapproval'),
      '#prefix' => '<div id= "message_submit">',
      '#states' => [
        'visible' => [
          [
            ':input[name="case_study_actions"]' => [
              'value' => 3
              ]
            ],
          'or',
          [':input[name="case_study_actions"]' => ['value' => 2]],
        ]
        ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $msg = '';
    $root_path = r_case_study_path();
    //var_dump($form_state['values']);die;
    if ($form_state->get(['clicked_button', '#value']) == 'Submit') {
      if ($form_state->getValue(['case_study_project']))
        // case_study_abstract_del_lab_pdf($form_state['values']['case_study_project']);
 {
        if (user_access('Case Study bulk manage abstract')) {
          $query = db_select('case_study_proposal');
          $query->fields('case_study_proposal');
          $query->condition('id', $form_state->getValue(['case_study_project']));
          $user_query = $query->execute();
          $user_info = $user_query->fetchObject();
          $user_data = user_load($user_info->uid);
          if ($form_state->getValue(['case_study_actions']) == 1) {
            // approving entire project //
            $query = db_select('case_study_submitted_abstracts');
            $query->fields('case_study_submitted_abstracts');
            $query->condition('proposal_id', $form_state->getValue(['case_study_project']));
            $abstracts_q = $query->execute();
            $experiment_list = '';
            while ($abstract_data = $abstracts_q->fetchObject()) {
              db_query("UPDATE {case_study_submitted_abstracts} SET abstract_approval_status = 1, is_submitted = 1, approver_uid = :approver_uid WHERE id = :id", [
                ':approver_uid' => $user->uid,
                ':id' => $abstract_data->id,
              ]);
              db_query("UPDATE {case_study_submitted_abstracts_file} SET file_approval_status = 1, approvar_uid = :approver_uid WHERE submitted_abstract_id = :submitted_abstract_id", [
                ':approver_uid' => $user->uid,
                ':submitted_abstract_id' => $abstract_data->id,
              ]);
            } //$abstract_data = $abstracts_q->fetchObject()
            drupal_goto('case-study-project/manage-proposal/all');
            drupal_set_message(t('Approved Case Study.'), 'status');
            // email 
            $email_subject = t('[!site_name][Case Study] Your uploaded Case Study have been approved', [
              '!site_name' => variable_get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear ' . $user_info->contributor_name . ',

Congratulations!
Your report and code files for Case Study Project at FOSSEE with the following details have been approved.

Full Name: ' . $user_info->name_title . ' ' . $user_info->contributor_name . '
Email : ' . $user_data->mail . '
University/Institute : ' . $user_info->university . '
City : ' . $user_info->city . '

Project Title  : ' . $user_info->project_title . '
Description of the Case Study: ' . $user_info->description . '

Kindly send us the internship forms as early as possible for processing your honorarium on time. In case you have already sent these forms, please share the the consignment number or tracking id with us.

Note: It will take upto 30 days from the time we receive your forms, to process your honorarium.


Best Wishes,

!site_name Team,
FOSSEE, IIT Bombay', [
                '!site_name' => variable_get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /** sending email when everything done **/
            $email_to = $user_data->mail;
            $from = variable_get('case_study_from_email', '');
            $bcc = variable_get('case_study_emails', '');
            $cc = variable_get('case_study_cc_emails', '');
            $params['standard']['subject'] = $email_subject;
            $params['standard']['body'] = $email_body;
            $params['standard']['headers'] = [
              'From' => $from,
              'MIME-Version' => '1.0',
              'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
              'Content-Transfer-Encoding' => '8Bit',
              'X-Mailer' => 'Drupal',
              'Cc' => $cc,
              'Bcc' => $bcc,
            ];
            if (!drupal_mail('case_study', 'standard', $email_to, language_default(), $params, $from, TRUE)) {
              $msg = drupal_set_message('Error sending email message.', 'error');
            } //!drupal_mail('case_study', 'standard', $email_to, language_default(), $params, $from, TRUE)
          } //$form_state['values']['case_study_actions'] == 1
          elseif ($form_state->getValue(['case_study_actions']) == 2) {
            if (strlen(trim($form_state->getValue(['message']))) <= 30) {
              $form_state->setErrorByName('message', t(''));
              $msg = drupal_set_message("Please mention the reason for marking resubmit. Minimum 30 character required", 'error');
              return $msg;
            }
            //pending review entire project 
            $query = db_select('case_study_submitted_abstracts');
            $query->fields('case_study_submitted_abstracts');
            $query->condition('proposal_id', $form_state->getValue(['case_study_project']));
            $abstracts_q = $query->execute();
            $experiment_list = '';
            while ($abstract_data = $abstracts_q->fetchObject()) {
              db_query("UPDATE {case_study_submitted_abstracts} SET abstract_approval_status = 0, is_submitted = 0, approver_uid = :approver_uid WHERE id = :id", [
                ':approver_uid' => $user->uid,
                ':id' => $abstract_data->id,
              ]);
              db_query("UPDATE {case_study_proposal} SET is_submitted = 0, approver_uid = :approver_uid WHERE id = :id", [
                ':approver_uid' => $user->uid,
                ':id' => $abstract_data->proposal_id,
              ]);
              db_query("UPDATE {case_study_submitted_abstracts_file} SET file_approval_status = 0, approvar_uid = :approver_uid WHERE submitted_abstract_id = :submitted_abstract_id", [
                ':approver_uid' => $user->uid,
                ':submitted_abstract_id' => $abstract_data->id,
              ]);
            } //$abstract_data = $abstracts_q->fetchObject()
            drupal_set_message(t('Resubmit the project files'), 'status');
            // email 
            $email_subject = t('[!site_name][Case Study] Your uploaded Case Study have been marked as pending', [
              '!site_name' => variable_get('site_name', '')
              ]);
            $email_body = [
              0 => t('

Dear ' . $user_info->contributor_name . ',

Kindly resubmit the project files for the project: ' . $user_info->project_title . '.
Description of the simulation: ' . $user_info->description . '

Reason: ' . $form_state->getValue(['message']) . '

Looking forward for the re-submission from you with the above suggested changes.

Best Wishes,

!site_name Team,
FOSSEE, IIT Bombay', [
                '!site_name' => variable_get('site_name', ''),
                '!user_name' => $user_data->name,
              ])
              ];
            /** sending email when everything done **/
            $email_to = $user_data->mail;
            $from = variable_get('case_study_from_email', '');
            $bcc = variable_get('case_study_emails', '');
            $cc = variable_get('case_study_cc_emails', '');
            $params['standard']['subject'] = $email_subject;
            $params['standard']['body'] = $email_body;
            $params['standard']['headers'] = [
              'From' => $from,
              'MIME-Version' => '1.0',
              'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
              'Content-Transfer-Encoding' => '8Bit',
              'X-Mailer' => 'Drupal',
              'Cc' => $cc,
              'Bcc' => $bcc,
            ];
            if (!drupal_mail('case_study', 'standard', $email_to, language_default(), $params, $from, TRUE)) {
              drupal_set_message('Error sending email message.', 'error');
            } //!drupal_mail('case_study', 'standard', $email_to, language_default(), $params, $from, TRUE)
          } //$form_state['values']['case_study_actions'] == 2
          elseif ($form_state->getValue(['case_study_actions']) == 3) //disapprove and delete entire Case Study
 {
            if (strlen(trim($form_state->getValue(['message']))) <= 30) {
              $form_state->setErrorByName('message', t(''));
              $msg = drupal_set_message("Please mention the reason for disapproval. Minimum 30 character required", 'error');
              return $msg;
            } //strlen(trim($form_state['values']['message'])) <= 30
            if (!user_access('Case Study bulk delete code')) {
              $msg = drupal_set_message(t('You do not have permission to Bulk Dis-Approved and Deleted Entire Project.'), 'error');
              return $msg;
            } //!user_access('case_study bulk delete code')
            if (case_study_abstract_delete_project($form_state->getValue(['case_study_project']))) //////
 {
              drupal_set_message(t('Dis-Approved and Deleted Entire Case Study.'), 'status');
              $email_subject = t('[!site_name][Case Study] Your uploaded Case Study have been marked as dis-approved', [
                '!site_name' => variable_get('site_name', '')
                ]);
              $email_body = [
                0 => t('
Dear ' . $user_info->contributor_name . ',

We regret to inform you that your report and code files for Case Study Project at FOSSEE with the following details have been disapproved:

Full Name: ' . $user_info->name_title . ' ' . $user_info->contributor_name . '
Email : ' . $user_data->mail . '
University/Institute : ' . $user_info->university . '
City : ' . $user_info->city . '

Project Title  : ' . $user_info->project_title . '
Description of the Case Study: ' . $user_info->description . '

Reason for dis-approval: ' . $form_state->getValue(['message']) . '

Kindly note that the incorrect files will be deleted from all our databases.

Thank you for participating in the Case Study Project. You are welcome to submit a new proposal.

Best Wishes,

!site_name Team,
FOSSEE, IIT Bombay', [
                  '!site_name' => variable_get('site_name', ''),
                  '!user_name' => $user_data->name,
                ])
                ];
              $email_to = $user_data->mail;
              $from = variable_get('case_study_from_email', '');
              $bcc = variable_get('case_study_emails', '');
              $cc = variable_get('case_study_cc_emails', '');
              $params['standard']['subject'] = $email_subject;
              $params['standard']['body'] = $email_body;
              $params['standard']['headers'] = [
                'From' => $from,
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
                'Content-Transfer-Encoding' => '8Bit',
                'X-Mailer' => 'Drupal',
                'Cc' => $cc,
                'Bcc' => $bcc,
              ];
              if (!drupal_mail('case_study', 'standard', $email_to, language_default(), $params, $from, TRUE)) {
                drupal_set_message('Error sending email message.', 'error');
              }
            } //case_study_abstract_delete_project($form_state['values']['case_study_project'])
            else {
              drupal_set_message(t('Error Dis-Approving and Deleting Entire Case Study.'), 'error');
            }
            // email 

          } //$form_state['values']['case_study_actions'] == 3
        }
      } //user_access('case_study project bulk manage code')
      return $msg;
    } //$form_state['clicked_button']['#value'] == 'Submit'
  }

}
?>
