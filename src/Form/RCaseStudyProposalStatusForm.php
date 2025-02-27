<?php

/**
 * @file
 * Contains \Drupal\r_case_study\Form\RCaseStudyProposalStatusForm.
 */

namespace Drupal\r_case_study\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\user\Entity\User;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Link;

class RCaseStudyProposalStatusForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'r_case_study_proposal_status_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
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
        \Drupal::messenger()->addMessage(t('Invalid proposal selected. Please try again.'), 'error');
        // drupal_goto('case-study-project/manage-proposal');
        return;
      }
    } //$proposal_q
    else {
      \Drupal::messenger()->addMessage(t('Invalid proposal selected. Please try again.'), 'error');
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
    $user_url = Url::fromRoute('entity.user.canonical', ['user' => $proposal_data->uid]);
$user_link = Link::fromTextAndUrl($proposal_data->name_title . ' ' . $proposal_data->contributor_name, $user_url)->toString();
    $form['contributor_name'] = [
      '#type' => 'item',
      '#markup' => $user_link,
      // '#markup' => l($proposal_data->name_title . ' ' . $proposal_data->contributor_name, 'user/' . $proposal_data->uid),
     
      '#title' => t('Student name'),
    ];
    $form['student_email_id'] = [
      '#title' => t('Student Email'),
      '#type' => 'item',
      // '#markup' => user_load($proposal_data->uid)->mail,
      '#markup' => $user_data ? $user_data->getEmail():'',

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
    /*$url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
    $reference = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $re);*/
    $form['literature_survey_file'] = [
      '#type' => 'item',
      // '#markup' => l('View Literature Survey', 'case-study-project/download/proposal-literature-survey-files/' . $proposal_data->id),
      '#markup' => Link::fromTextAndUrl(
        'View Literature Survey',
        Url::fromUri('internal:/case-study-project/download/proposal-literature-survey-files/' . $proposal_data->id)
      )->toString(), // Extracts the markup string
      '#title' => t('Literature Survey'),
    ];
    $form['abstract_file'] = [
      '#type' => 'item',
      // '#markup' => l('View abstract', 'case-study-project/download/proposal-abstract-files/' . $proposal_data->id),
      '#markup' => Link::fromTextAndUrl(
        'View abstract',
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
    

    $proposal_status = '';
    switch ($proposal_data->approval_status) {
      case 0:
        $proposal_status = t('Pending');
        break;
      case 1:
        $proposal_status = t('Approved');
        break;
      case 2:
        $proposal_status = t('Dis-approved');
        break;
      case 3:
        $proposal_status = t('Completed');
        break;
      case 5:
        $approval_status = t('On Hold');
        break;
      default:
        $proposal_status = t('Unkown');
        break;
    }
    $form['proposal_status'] = [
      '#type' => 'item',
      '#markup' => $proposal_status,
      '#title' => t('Proposal Status'),
    ];
    if ($proposal_data->approval_status == 0) {
      $form['approve'] = [
        '#type' => 'item',
        // '#markup' => l('Click here', 'case-study-project/manage-proposal/approve/' . $proposal_id),
        '#title' => t('Approve'),
      ];
    } //$proposal_data->approval_status == 0
    if ($proposal_data->approval_status == 1) {
      $form['completed'] = [
        '#type' => 'checkbox',
        '#title' => t('Completed'),
        '#description' => t('Check if user has provided all the required files and pdfs.'),
      ];
    } //$proposal_data->approval_status == 1
    if ($proposal_data->approval_status == 2) {
      $form['message'] = [
        '#type' => 'item',
        '#markup' => $proposal_data->message,
        '#title' => t('Reason for disapproval'),
      ];
    } //$proposal_data->approval_status == 2
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    $form['cancel'] = [
      '#type' => 'markup',
      // '#markup' => l(t('Cancel'), 'case-study-project/manage-proposal/all'),
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    /* get current proposal */
    // $proposal_id = (int) arg(3);
    $route_match = \Drupal::routeMatch();

    $proposal_id = (int) $route_match->getParameter('id');
    //$proposal_q = \Drupal::database()->query("SELECT * FROM {case_study_proposal} WHERE id = %d", $proposal_id);
    $query = \Drupal::database()->select('case_study_proposal');
    $query->fields('case_study_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      } //$proposal_data = $proposal_q->fetchObject()
      else {
        \Drupal::messenger()->addMessage(t('Invalid proposal selected. Please try again.'), 'error');
        // drupal_goto('case-study-project/manage-proposal');
        return;
      }
    } //$proposal_q
    else {
      \Drupal::messenger()->addMessage(t('Invalid proposal selected. Please try again.'), 'error');
      // drupal_goto('case-study-project/manage-proposal');
      return;
    }
    /* set the book status to completed */
    if ($form_state->getValue(['completed']) == 1) {
      $up_query = "UPDATE case_study_proposal SET approval_status = :approval_status , actual_completion_date = :expected_completion_date WHERE id = :proposal_id";
      $args = [
        ":approval_status" => '3',
        ":proposal_id" => $proposal_id,
        ":expected_completion_date" => time(),
      ];
      $result = \Drupal::database()->query($up_query, $args);
      CreateReadmeFileCaseStudyProject($proposal_id);
      if (!$result) {
        \Drupal::messenger()->addMessage('Error in update status', 'error');
        return;
      } //!$result
        /* sending email */
      $user_data = user_load($proposal_data->uid);
      $email_to = $user_data->mail;
      $from = variable_get('case_study_from_email', '');
      $bcc = variable_get('case_study_emails', '');
      $cc = variable_get('case_study_cc_emails', '');
      $params['case_study_proposal_completed']['proposal_id'] = $proposal_id;
      $params['case_study_proposal_completed']['user_id'] = $proposal_data->uid;
      $params['case_study_proposal_completed']['headers'] = [
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc,
      ];
      if (!drupal_mail('case_study', 'case_study_proposal_completed', $email_to, language_default(), $params, $from, TRUE)) {
        \Drupal::messenger()->addMessage('Error sending email message.', 'error');
      }

      \Drupal::messenger()->addMessage('Congratulations! R Case Study proposal has been marked as completed. User has been notified of the completion.', 'status');
    }
    // drupal_goto('case-study-project/manage-proposal');
    $response = new RedirectResponse(Url::fromRoute('r_case_study.proposal_all')->toString());
$response->send();
    return $response;

  }

}
?>
