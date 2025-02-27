<?php

/**
 * @file
 * Contains \Drupal\r_case_study\Form\RCaseStudyProposalEditForm.
 */

namespace Drupal\r_case_study\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Link;

class RCaseStudyProposalEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'r_case_study_proposal_edit_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
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
    $proposal_data = $proposal_q->fetchObject();
    /*if ($proposal_q) {
        if ($proposal_data = $proposal_q->fetchObject()) {
            /* everything ok 
        } //$proposal_data = $proposal_q->fetchObject()
        else {
            drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
            drupal_goto('case-study-project/manage-proposal');
            return;
        }
    } //$proposal_q
    else {
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('case-study-project/manage-proposal');
        return;
    }*/
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
    // $user_data = user_load($proposal_data->uid);
    $user_data = User::load($proposal_data->uid);

    $form['name_title'] = [
      '#type' => 'select',
      '#title' => t('Title'),
      '#options' => [
        'Dr' => 'Dr',
        'Prof' => 'Prof',
        'Mr' => 'Mr',
        'Ms' => 'Ms',
      ],
      '#required' => TRUE,
      '#default_value' => $proposal_data->name_title,
    ];
    $form['contributor_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the Proposer'),
      '#size' => 30,
      '#maxlength' => 50,
      '#required' => TRUE,
      '#default_value' => $proposal_data->contributor_name,
    ];
    $form['student_email_id'] = [
      '#type' => 'item',
      '#title' => t('Email'),
      // '#markup' => $user_data->getEmail(),
      '#markup' => $user_data ? $user_data->getEmail() : t('No email found'),
    ];
    $form['contact_no'] = [
      '#type' => 'textfield',
      '#title' => t('Contact Number'),
      '#size' => 30,
      '#maxlength' => 50,
      '#required' => TRUE,
      '#default_value' => $proposal_data->contact_no,
    ];
    $form['university'] = [
      '#type' => 'textfield',
      '#title' => t('University/Institute'),
      '#size' => 200,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#default_value' => $proposal_data->university,
    ];
    $form['department'] = [
      '#type' => 'textfield',
      '#title' => t('Department'),
      '#size' => 80,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#default_value' => $proposal_data->department,
    ];
    $form['how_did_you_know_about_project'] = [
      '#type' => 'textfield',
      '#title' => t('How did you come to know about the Case Study Project?'),
      '#default_value' => $proposal_data->how_did_you_know_about_project,
      '#required' => TRUE,
    ];
    $form['profession'] = [
      '#type' => 'textfield',
      '#title' => t('Current Professional Status'),
      '#default_value' => $proposal_data->profession,
    ];
    $form['faculty_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the Faculty'),
      '#size' => 50,
      '#maxlength' => 50,
      '#validated' => TRUE,
      '#default_value' => $faculty_name,
    ];
    $form['faculty_department'] = [
      '#type' => 'textfield',
      '#title' => t('Department of the Faculty'),
      '#size' => 50,
      '#maxlength' => 50,
      '#validated' => TRUE,
      '#default_value' => $faculty_department,
    ];
    $form['faculty_email'] = [
      '#type' => 'textfield',
      '#title' => t('Email id of the Faculty'),
      '#size' => 255,
      '#maxlength' => 255,
      '#validated' => TRUE,
      '#default_value' => $faculty_email,
    ];
    $form['country'] = [
      '#type' => 'select',
      '#title' => t('Country'),
      '#options' => [
        'India' => 'India',
        'Others' => 'Others',
      ],
      '#default_value' => $proposal_data->country,
      '#required' => TRUE,
      '#tree' => TRUE,
      '#validated' => TRUE,
    ];
    $form['other_country'] = [
      '#type' => 'textfield',
      '#title' => t('Other than India'),
      '#size' => 100,
      '#default_value' => $proposal_data->country,
      '#attributes' => [
        'placeholder' => t('Enter your country name')
        ],
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'Others'
            ]
          ]
        ],
    ];
    $form['other_state'] = [
      '#type' => 'textfield',
      '#title' => t('State other than India'),
      '#size' => 100,
      '#attributes' => [
        'placeholder' => t('Enter your state/region name')
        ],
      '#default_value' => $proposal_data->state,
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'Others'
            ]
          ]
        ],
    ];
    $form['other_city'] = [
      '#type' => 'textfield',
      '#title' => t('City other than India'),
      '#size' => 100,
      '#attributes' => [
        'placeholder' => t('Enter your city name')
        ],
      '#default_value' => $proposal_data->city,
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'Others'
            ]
          ]
        ],
    ];
    $form['all_state'] = [
      '#type' => 'select',
      '#title' => t('State'),
      '#options' => _r_case_study_list_of_states(),
      '#default_value' => $proposal_data->state,
      '#validated' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'India'
            ]
          ]
        ],
    ];
    $form['city'] = [
      '#type' => 'select',
      '#title' => t('City'),
      '#options' => _r_case_study_list_of_cities(),
      '#default_value' => $proposal_data->city,
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'India'
            ]
          ]
        ],
    ];
    $form['pincode'] = [
      '#type' => 'textfield',
      '#title' => t('Pincode'),
      '#size' => 30,
      '#maxlength' => 6,
      '#default_value' => $proposal_data->pincode,
      '#attributes' => [
        'placeholder' => 'Insert pincode of your city/ village....'
        ],
    ];
    $form['r_version'] = [
      '#type' => 'select',
      '#title' => t('Version used'),
      '#options' => _cs_list_of_versions(),
      '#default_value' => $proposal_data->r_version,
    ];

    $form['project_title'] = [
      '#type' => 'textfield',
      '#title' => t('Title of the Case Study Project'),
      '#size' => 300,
      '#maxlength' => 100,
      '#required' => TRUE,
      '#default_value' => $proposal_data->project_title,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Objective and Necessity of the Case Study'),
      '#size' => 300,
      '#maxlength' => 1200,
      '#required' => TRUE,
      '#default_value' => $proposal_data->description,
    ];
    $form['date_of_proposal'] = [
      '#type' => 'textfield',
      '#title' => t('Date of Proposal'),
      '#default_value' => date('d/m/Y', $proposal_data->creation_date),
      '#disabled' => TRUE,
    ];
    $form['expected_completion_date'] = [
      '#type' => 'textfield',
      '#title' => t('Date of Proposal'),
      '#default_value' => date('d/m/Y', $proposal_data->expected_date_of_completion),
      '#disabled' => TRUE,
    ];
    $form['delete_proposal'] = [
      '#type' => 'checkbox',
      '#title' => t('Delete Proposal'),
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
    if ($form_state->getValue(['simulation_type']) < 13) {
      if ($form_state->getValue(['solver_used']) == '0') {
        $form_state->setErrorByName('solver_used', t('Please select an option'));
      }
    }
    else {
      if ($form_state->getValue(['simulation_type']) == 13) {
        if ($form_state->getValue(['solver_used_text']) != '') {
          if (strlen($form_state->getValue(['solver_used_text'])) > 100) {
            $form_state->setErrorByName('solver_used_text', t('Maximum charater limit is 100 charaters only, please check the length of the solver used'));
          } //strlen($form_state['values']['project_title']) > 250
          else {
            if (strlen($form_state->getValue(['solver_used_text'])) < 7) {
              $form_state->setErrorByName('solver_used_text', t('Minimum charater limit is 7 charaters, please check the length of the solver used'));
            }
          } //strlen($form_state['values']['project_title']) < 10
        }
        else {
          $form_state->setErrorByName('solver_used_text', t('Solver used cannot be empty'));
        }
      }
    }
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
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('case-study-project/manage-proposal');
        return;
      }
    } //$proposal_q
    else {
      drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
      drupal_goto('case-study-project/manage-proposal');
      return;
    }
    /* delete proposal */
    if ($form_state->getValue(['delete_proposal']) == 1) {
      /* sending email */
      // $user_data = user_load($proposal_data->uid);
      // $email_to = $user_data->mail;
      // $from = variable_get('case_study_from_email', '');
      // $bcc = variable_get('case_study_emails', '');
      // $cc = variable_get('case_study_cc_emails', '');
      // $params['case_study_proposal_deleted']['proposal_id'] = $proposal_id;
      // $params['case_study_proposal_deleted']['user_id'] = $proposal_data->uid;
      // $params['case_study_proposal_deleted']['headers'] = [
      //   'From' => $from,
      //   'MIME-Version' => '1.0',
      //   'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
      //   'Content-Transfer-Encoding' => '8Bit',
      //   'X-Mailer' => 'Drupal',
      //   'Cc' => $cc,
      //   'Bcc' => $bcc,
      // ];
      // if (!drupal_mail('case_study', 'case_study_proposal_deleted', $email_to, user_preferred_language($user), $params, $from, TRUE)) {
      //   drupal_set_message('Error sending email message.', 'error');
      // }

      drupal_set_message(t('Case Study proposal has been deleted.'), 'status');
      if (rrmdir_project($proposal_id) == TRUE) {
        $query = \Drupal::database()->delete('case_study_proposals_file');
        $query->condition('proposal_id', $proposal_id);
        $proposals_file_deleted = $query->execute();
        $query = \Drupal::database()->delete('case_study_proposal');
        $query->condition('id', $proposal_id);
        $num_deleted = $query->execute();
        drupal_set_message(t('Proposal Deleted'), 'status');
        drupal_goto('case-study-project/manage-proposal');
        return;
      } //rrmdir_project($proposal_id) == TRUE
    } //$form_state['values']['delete_proposal'] == 1
    /* update proposal */
    $v = $form_state->getValues();
    $project_title = $v['project_title'];
    $proposar_name = $v['name_title'] . ' ' . $v['contributor_name'];
    $university = $v['university'];
    $directory_names = \Drupal::service("r_case_study_global")->_r_case_study_dir_name($project_title, $proposar_name);
    if (CaseStudy_RenameDir($proposal_id, $directory_names)) {
      $directory_name = $directory_names;
    } //LM_RenameDir($proposal_id, $directory_names)
    else {
      return;
    }
    $query = \Drupal::database()->select('case_study_proposals_file');
    $query->fields('case_study_proposals_file');
    $result = $query->execute();
    while ($case_study_proposal_files = $result->fetchObject()) {
      if ($case_study_proposal_files->filetype == 'A') {
        $file_type = 'A';
      }
      else {
        $file_type = 'R';
      }
      switch ($file_type) {
        case 'A':
          $str = substr($case_study_proposal_files->filepath, strrpos($case_study_proposal_files->filepath, '/'));
          $resource_file = ltrim($str, '/');
          $filepath_query = "UPDATE case_study_proposals_file SET
                                filepath = :filepath
                                WHERE proposal_id = :proposal_id and filetype = :filetype";
          $args = [
            ':filepath' => $directory_name . '/' . $resource_file,
            ':proposal_id' => $proposal_id,
            ':filetype' => $file_type,
          ];
          break;
        case 'R':
          $str = substr($case_study_proposal_files->filepath, strrpos($case_study_proposal_files->filepath, '/'));
          $resource_file = ltrim($str, '/');
          $filepath_query = "UPDATE case_study_proposals_file SET
                                filepath = :filepath
                                WHERE proposal_id = :proposal_id and filetype = :filetype";
          $args = [
            ':filepath' => $directory_name . '/' . $resource_file,
            ':proposal_id' => $proposal_id,
            ':filetype' => $file_type,
          ];
          break;
      }
      $propsoal_files_result = \Drupal::database()->query($filepath_query, $args);
    }

    $query = "UPDATE case_study_proposal SET
				name_title=:name_title,
				contributor_name=:contributor_name,
				university=:university,
				department=:department,
				how_did_you_know_about_project = :how_did_you_know_about_project,
                profession=:profession,
				faculty_name = :faculty_name,
				faculty_department = :faculty_department,
				faculty_email = :faculty_email,
				city=:city,
				pincode=:pincode,
				state=:state,
				project_title=:project_title,
                description=:description,
                r_version=:r_version,
				directory_name=:directory_name
				WHERE id=:proposal_id";
    $args = [
      ':name_title' => $v['name_title'],
      ':contributor_name' => $v['contributor_name'],
      ':university' => $v['university'],
      ":department" => $v['department'],
      ":how_did_you_know_about_project" => $v['how_did_you_know_about_project'],
      ":profession" => $v['profession'],
      ":faculty_name" => $v['faculty_name'],
      ":faculty_department" => $v['faculty_department'],
      ":faculty_email" => $v['faculty_email'],
      ':city' => $v['city'],
      ':pincode' => $v['pincode'],
      ':state' => $v['all_state'],
      ':project_title' => $project_title,
      ':description' => $v['description'],
      ':r_version' => $v['r_version'],
      ':directory_name' => $directory_name,
      ':proposal_id' => $proposal_id,
    ];
    $result = \Drupal::database()->query($query, $args);
    \Drupal::messenger()->addMessage(t('Proposal Updated'), 'status');
    $response = new RedirectResponse(Url::fromRoute('r_case_study.proposal_pending')->toString());
    // Send the redirect response
       $response->send();
       return;
  }

}
?>
