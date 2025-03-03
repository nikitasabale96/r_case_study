<?php

/**
 * @file
 * Contains \Drupal\r_case_study\Form\RCaseStudyProposalApprovalForm.
 */

namespace Drupal\r_case_study\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;

class RCaseStudyProposalApprovalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'r_case_study_proposal_approval_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    /* get current proposal */
    // $proposal_id = (int) arg(3);
    /* get current proposal */
    $route_match = \Drupal::routeMatch();

    $proposal_id = (int) $route_match->getParameter('id');
    $query = \Drupal::database()->select('case_study_proposal');
    $query->fields('case_study_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      } //$proposal_data = $proposal_q->fetchObject()
      else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'), 'error');
        // drupal_goto('case-study-project/manage-proposal');
        return;
      }
    } //$proposal_q
    else {
      \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'), 'error');
      // drupal_goto('case-study-project/manage-proposal');
      return;
    }
    if ($proposal_data->faculty_name == '') {
      $faculty_name = 'NA';
    }
    else {
      $faculty_name = $proposal_data->faculty_name;
    }
    if ($proposal_data->faculty_department == '') {
      $faculty_department = 'NA';
    }
    else {
      $faculty_department = $proposal_data->faculty_department;
    }
    if ($proposal_data->faculty_email == '') {
      $faculty_email = 'NA';
    }
    else {
      $faculty_email = $proposal_data->faculty_email;
    }
    $form['contributor_name'] = [
      '#type' => 'item',
      // '#markup' => l($proposal_data->name_title . ' ' . $proposal_data->contributor_name, 'user/' . $proposal_data->uid),
      '#markup' => Link::fromTextAndUrl(
        $proposal_data->name_title . ' ' . $proposal_data->contributor_name,
        Url::fromUserInput('/user/' . $proposal_data->uid)
      )->toString(),
      '#title' => t('Student name'),
    ];
    $user_data = \Drupal\user\Entity\User::load($uid); // Ensure $uid is valid
    $form['student_email_id'] = [
      '#title' => t('Student Email'),
      '#type' => 'item',
      
      // '#markup' => $user_data->getEmail(),
      // 
      '#markup' => $user_data ? $user_data->getEmail():'',
      // '#markup' => $user_data ? $user_data->getEmail() : t('No email found'),

            // '#markup' => \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid)->mail,
      '#title' => t('Email'),
    ];

    $form['contributor_contact_no'] = [
      '#title' => t('Contact No.'),
      '#type' => 'item',
      '#markup' => $proposal_data->contact_no,
    ];
    $form['university'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->university,
      '#title' => t('University/Institute'),
    ];
    $form['department'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->department,
      '#title' => t('Department'),
    ];
    $form['how_did_you_know_about_project'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->how_did_you_know_about_project,
      '#title' => t('How did you know about the project'),
    ];
    $form['profession'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->profession,
      '#title' => t('Current Professional Status'),
    ];
    $form['faculty_name'] = [
      '#type' => 'item',
      '#markup' => $faculty_name,
      '#title' => t('Name of the faculty'),
    ];
    $form['faculty_department'] = [
      '#type' => 'item',
      '#markup' => $faculty_department,
      '#title' => t('Department of the faculty'),
    ];
    $form['faculty_email'] = [
      '#type' => 'item',
      '#markup' => $faculty_email,
      '#title' => t('Email of the faculty'),
    ];
    $form['country'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->country,
      '#title' => t('Country'),
    ];
    $form['all_state'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->state,
      '#title' => t('State'),
    ];
    $form['city'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->city,
      '#title' => t('City'),
    ];
    $form['pincode'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->pincode,
      '#title' => t('Pincode/Postal code'),
    ];
    $form['r_version'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->r_version,
      '#title' => t('R Version used'),
    ];
    $form['project_title'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->project_title,
      '#title' => t('Title of the Case Study Project'),
    ];
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->description,
      '#title' => t('Objective and Necessity of the Case Study'),
    ];
    $form['literature_survey_file'] = [
      '#type' => 'item',
      // '#markup' => l('View Literature Survey', 'case-study-project/download/proposal-literature-survey-files/' . $proposal_data->id),
      '#markup' => Link::fromTextAndUrl(
    'View Literature Survey',
    Url::fromUri('internal:/case-study-project/download/proposal-literature-survey-files/' . $proposal_data->id)
  )->toString(), // Extracts the markup string
  // '#allowed_tags' => ['a'], // Ens
      '#title' => t('Literature Survey'),
    ];
    $form['abstract_file'] = [
      '#type' => 'item',
      // '#markup' => l('View Methodology details', 'case-study-project/download/proposal-abstract-files/' . $proposal_data->id),
      '#markup' => Link::fromTextAndUrl(
        'View Methodology details',
        Url::fromUri('internal:/case-study-project/download/proposal-abstarct-files/' . $proposal_data->id)
      )->toString(),
      '#title' => t('Abstract File'),
    ];
    $form['rawdata_file'] = [
      '#type' => 'item',
      // '#markup' => l('View raw data', 'case-study-project/download/proposal-rawdata-files/' . $proposal_data->id),
      '#markup' => Link::fromTextAndUrl(
        'View raw data',
        Url::fromUri('internal:/case-study-project/download/proposal-rawdata-files/' . $proposal_data->id)
      )->toString(),
      '#title' => t('Raw Data File'),
    ];
    $form['date_of_proposal'] = [
      '#type' => 'textfield',
      '#title' => t('Date of Proposal'),
      '#default_value' => date('d/m/Y', $proposal_data->creation_date),
      '#disabled' => TRUE,
    ];
    // $form['expected_completion_date'] = [
    //   '#type' => 'textfield',
    //   '#title' => t('Expected Date of Completion'),
    //   '#default_value' => date('d/m/Y', $proposal_data->expected_date_of_completion),
    //   '#disabled' => TRUE,
    // ];

    $form['expected_completion_date'] = [
      '#type' => 'textfield',
      '#title' => t('Expected Date of Completion'),
      '#default_value' => date('d/m/Y', strtotime('+30 days')), // Set future date (30 days from today)
      '#disabled' => TRUE,
    ];
    
    $form['approval'] = [
      '#type' => 'radios',
      '#title' => t('Select an action on the R Case Study proposal'),
      '#options' => [
        '1' => 'Approve',
        '2' => 'Disapprove',
      ],
      '#required' => TRUE,
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => t('Reason for disapproval'),
      '#attributes' => [
        'placeholder' => t('Enter reason for disapproval in minimum 30 characters '),
        'cols' => 50,
        'rows' => 4,
      ],
      '#states' => [
        'visible' => [
          ':input[name="approval"]' => [
            'value' => '2'
            ]
          ]
        ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    $form['cancel'] = [
      '#type' => 'item',
      // '#markup' => l(t('Cancel'), 'case-study-project/manage-proposal'),
      '#markup' => Link::fromTextAndUrl(
  $this->t('Cancel'),
  Url::fromUri('internal:/case-study-project/manage-proposal/pending'))->toString(),
    ];
    return $form;
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    if ($form_state->getValue(['approval']) == 2) {
      if ($form_state->getValue(['message']) == '') {
        $form_state->setErrorByName('message', t('Reason for disapproval could not be empty'));
      } //$form_state['values']['message'] == ''
    } //$form_state['values']['approval'] == 2
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    /* get current proposal */
    // $proposal_id = (int) arg(3);
    $route_match = \Drupal::routeMatch();

    $proposal_id = (int) $route_match->getParameter('id');
    $query = \Drupal::database()->select('case_study_proposal');
    $query->fields('case_study_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      } //$proposal_data = $proposal_q->fetchObject()
      else {
        \Drupal::messenger()->addmessage(t('Invalid proposal selected. Please try again.'), 'error');
        // drupal_goto('case-study-project/manage-proposal');
        return;
      }
    } //$proposal_q
    else {
      \Drupal::messenger()->addmessage(t('Invalid proposal selected. Please try again.'), 'error');
      // drupal_goto('case-study-project/manage-proposal');
      return;
    }
    if ($form_state->getValue(['approval']) == 1) {
      $query = "UPDATE {case_study_proposal} SET approver_uid = :uid, approval_date = :date, approval_status = 1 WHERE id = :proposal_id";
      $args = [
        ":uid" =>  \Drupal::currentUser()->id(),
        ":date" => time(),
        ":proposal_id" => $proposal_id,
      ];
      \Drupal::database()->query($query, $args);
      /* sending email */
      // $user_data = user_load($proposal_data->uid);
      // $email_to = $user_data->mail;
      // $from = variable_get('case_study_from_email', '');
      // $bcc = variable_get('case_study_emails', '');
      // $cc = variable_get('case_study_cc_emails', '');
      // $params['case_study_proposal_approved']['proposal_id'] = $proposal_id;
      // $params['case_study_proposal_approved']['user_id'] = $proposal_data->uid;
      // $params['case_study_proposal_approved']['headers'] = [
      //   'From' => $from,
      //   'MIME-Version' => '1.0',
      //   'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
      //   'Content-Transfer-Encoding' => '8Bit',
      //   'X-Mailer' => 'Drupal',
      //   'Cc' => $cc,
      //   'Bcc' => $bcc,
      // ];
      // if (!drupal_mail('case_study', 'case_study_proposal_approved', $email_to, language_default(), $params, $from, TRUE)) {
      //   \Drupal::messenger()->addmessage('Error sending email message.', 'error');
      // }

      // \Drupal::messenger()->addmessage('R Case Study proposal No. ' . $proposal_id . ' approved. User has been notified of the approval.', 'status');
      // drupal_goto('case-study-project/manage-proposal');
      return;
    } //$form_state['values']['approval'] == 1
    else {
      if ($form_state->getValue(['approval']) == 2) {
        $query = "UPDATE {case_study_proposal} SET approver_uid = :uid, approval_date = :date, approval_status = 2, dissapproval_reason = :dissapproval_reason WHERE id = :proposal_id";
        $args = [
          ":uid" => $user->id(),
          ":date" => time(),
          ":dissapproval_reason" => $form_state->getValue(['message']),
          ":proposal_id" => $proposal_id,
        ];
        $result = \Drupal::database()->query($query, $args);
        /* sending email */
        // $user_data = user_load($proposal_data->uid);
        // $email_to = $user_data->mail;
        // $from = variable_get('case_study_from_email', '');
        // $bcc = variable_get('case_study_emails', '');
        // $cc = variable_get('case_study_cc_emails', '');
        // $params['case_study_proposal_disapproved']['proposal_id'] = $proposal_id;
        // $params['case_study_proposal_disapproved']['user_id'] = $proposal_data->uid;
        // $params['case_study_proposal_disapproved']['headers'] = [
        //   'From' => $from,
        //   'MIME-Version' => '1.0',
        //   'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        //   'Content-Transfer-Encoding' => '8Bit',
        //   'X-Mailer' => 'Drupal',
        //   'Cc' => $cc,
        //   'Bcc' => $bcc,
        // ];
        // if (!drupal_mail('case_study', 'case_study_proposal_disapproved', $email_to, language_default(), $params, $from, TRUE)) {
        //   \Drupal::messenger()->addmessage('Error sending email message.', 'error');
        // }

        \Drupal::messenger()->addMessage('R Case Study proposal No. ' . $proposal_id . ' dis-approved. User has been notified of the dis-approval.', 'error');
        // drupal_goto('case-study-project/manage-proposal');
//         $response = new RedirectResponse(Url::fromUri('internal:/case-study-project/manage-proposal/pending')->toString());
// $response->send();
$form_state->setRedirect('r_case_study.proposal_pending');
        return;
      }
    } //$form_state['values']['approval'] == 2
  }

}
?>
