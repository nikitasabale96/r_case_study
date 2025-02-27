<?php

/**
 * @file
 * Contains \Drupal\r_case_study\Form\RCaseStudyProposalForm.
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
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;

class RCaseStudyProposalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'r_case_study_proposal_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state, $no_js_use = NULL) {
    $user = \Drupal::currentUser();
    /************************ start approve book details ************************/
    if ($user->isAnonymous()) {
      // $msg = \Drupal::messenger()->addError(t('This is an error message, red in color'));
      $url = Link::fromTextAndUrl(t('login'), Url::fromRoute('user.page'))->toString();
      
      $msg = \Drupal::messenger()->addmessage(t('It is mandatory to ' . Link::fromTextAndUrl(t('login'), Url::fromRoute('user.page'))->toString() . ' on this website to access the lab proposal form. If you are new user please create a new account first.'));
      
      // RedirectResponse('lab-migration-project');
      // \Drupal::RedirectResponse('user');
  //     $redirect = new RedirectResponse($url);
  //     $redirect->send();
  // return $msg;
  // Redirect to the login page
  $response = new RedirectResponse(Url::fromRoute('user.page')->toString());

  $response->send();
  return $msg;

  
    }//$user->uid == 0
    $query = \Drupal::database()->select('case_study_proposal');
    $query->fields('case_study_proposal');
    $query->condition('uid', $user->id());
    $query->orderBy('id', 'DESC');
    $query->range(0, 1);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    if ($proposal_data) {
      if ($proposal_data->approval_status == 0 || $proposal_data->approval_status == 1) {
        \Drupal::messenger()->addMessage(t('We have already received your proposal.'), 'status');
        $response = new RedirectResponse(Url::fromRoute('<front>')->toString());

        
        // drupal_goto('');
        return $response;
      } //$proposal_data->approval_status == 0 || $proposal_data->approval_status == 1
    } //$proposal_data
    $form['#attributes'] = [
      'enctype' => "multipart/form-data"
      ];

    $form['name_title'] = [
      '#type' => 'select',
      '#title' => t('Name Title'),
      '#options' => [
        'Dr' => 'Dr',
        'Prof' => 'Prof',
        'Mr' => 'Mr',
        'Ms' => 'Ms',
      ],
      '#required' => TRUE,
    ];
    $form['contributor_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the contributor'),
      '#size' => 250,
      '#attributes' => [
        'placeholder' => t('Enter your full name.....')
        ],
      '#maxlength' => 250,
      '#required' => TRUE,
    ];
    $form['contributor_email_id'] = [
      '#type' => 'textfield',
      '#title' => t('Email'),
      '#size' => 30,
      '#value' => $user ? $user->getEmail() : '',
      '#disabled' => TRUE,
    ];
    $form['contributor_contact_no'] = [
      '#type' => 'textfield',
      '#title' => t('Contact No.'),
      '#size' => 10,
      '#attributes' => [
        'placeholder' => t('Enter your contact number')
        ],
      '#maxlength' => 250,
      '#required' => TRUE,
    ];
    $form['university'] = [
      '#type' => 'textfield',
      '#title' => t('University/Institute/Organisation'),
      '#size' => 80,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Insert full name of your university.... '
        ],
    ];
    $form['department'] = [
      '#type' => 'select',
      '#title' => t('Department/Branch'),
      '#options' => _r_case_study_list_of_departments(),
      '#required' => TRUE,
    ];
    $form['other_department'] = [
      '#type' => 'textfield',
      '#title' => t('If ‘Other’, please specify'),
      '#maxlength' => 50,
      '#description' => t('<span style="color:red">Maximum character limit is 50</span>'),
      '#states' => [
        'visible' => [
          ':input[name="department"]' => [
            'value' => 'Others'
            ]
          ]
        ],
    ];
    $form['how_did_you_know_about_project'] = [
      '#type' => 'select',
      '#title' => t('How did you come to know about the Case Study Project?'),
      '#options' => [
        'Poster' => 'Poster',
        'Website' => 'Website',
        'Email' => 'Email',
        'Others' => 'Others',
      ],
      '#required' => TRUE,
    ];
    $form['others_how_did_you_know_about_project'] = [
      '#type' => 'textfield',
      '#title' => t('If ‘Other’, please specify'),
      '#maxlength' => 50,
      '#description' => t('<span style="color:red">Maximum character limit is 50</span>'),
      '#states' => [
        'visible' => [
          ':input[name="how_did_you_know_about_project"]' => [
            'value' => 'Others'
            ]
          ]
        ],
    ];
    $form['profession'] = [
      '#type' => 'select',
      '#title' => t('Kindly select the option which describes your current status'),
      '#options' => [
        'Student' => 'Student',
        'Faculty (School/College/University)' => 'Faculty (School/College/University)',
        'Working Professional (other than faculty)' => 'Working Professional (other than faculty)',
      ],
      '#description' => t('<span style="color:red">It is mandatory for a student contributor to work under the guidance of a faculty for the case study project</span>'),
      '#required' => TRUE,
    ];
    $form['faculty_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the Faculty Member of your Institution, who helped you with this Case Study Project'),
      '#size' => 50,
      '#maxlength' => 50,
      '#description' => t('<span style="color:red">Maximum character limit is 50</span>'),
      '#attributes' => [
        'placeholder' => 'Insert Name of the Faculty Member '
        ],
    ];
    $form['faculty_department'] = [
      '#type' => 'textfield',
      '#title' => t('Department of the Faculty Member of your Institution, who helped you with this Case Study Project'),
      '#size' => 50,
      '#maxlength' => 50,
      '#description' => t('<span style="color:red">Maximum character limit is 50</span>'),
      '#attributes' => [
        'placeholder' => 'Insert Department of the Faculty Member '
        ],
    ];
    $form['faculty_email'] = [
      '#type' => 'textfield',
      '#title' => t('Email id of the Faculty Member of your Institution, who helped you with this Case Study Project'),
      '#size' => 255,
      '#maxlength' => 255,
      '#description' => t('<span style="color:red">Maximum character limit is 255</span>'),
      '#attributes' => [
        'placeholder' => 'Insert Email id of the Faculty Member '
        ],
    ];
    $form['country'] = [
      '#type' => 'select',
      '#title' => t('Country'),
      '#options' => [
        'India' => 'India',
        'Others' => 'Others',
      ],
      '#required' => TRUE,
      '#tree' => TRUE,
    ];
    $form['other_country'] = [
      '#type' => 'textfield',
      '#title' => t('Other Country'),
      '#size' => 100,
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
      '#title' => t('State'),
      '#size' => 100,
      '#attributes' => [
        'placeholder' => t('Enter your state/region name')
        ],
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
      '#title' => t('City'),
      '#size' => 100,
      '#attributes' => [
        'placeholder' => t('Enter your city name')
        ],
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
      '#options' =>  \Drupal::service("r_case_study_global")->_r_case_study_list_of_states(),
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
      '#options' =>  \Drupal::service("r_case_study_global")->_r_case_study_list_of_cities(),
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
      '#size' => 6,
    ];
    /***************************************************************************/
    $form['hr'] = [
      '#type' => 'item',
      '#markup' => '<hr>',
    ];
    $form['r_version'] = [
      '#type' => 'select',
      '#title' => t('Select the R version'),
      '#options' => \Drupal::service("r_case_study_global")->_cs_list_of_versions(),
      '#required' => TRUE,
    ];
    $form['r_other_version'] = [
      '#type' => 'textfield',
      '#title' => t('Enter the R version used'),
      '#description' => t('<span style="color:red">This is a mandatory field</span>'),
      '#size' => 100,
      '#maxlength' => 100,
      '#states' => [
        'visible' => [
          ':input[name="r_version"]' => [
            'value' => 'Others'
            ]
          ]
        ],
    ];
    $form['case_study_title'] = [
      '#type' => 'textarea',
      '#title' => t('Case Study Title'),
      '#size' => 250,
      '#maxlength' => 100,
      '#description' => t('Maximum character limit is 100'),
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Objective and Necessity of the Case Study'),
      '#size' => 250,
      '#maxlength' => 1200,
      '#description' => t('Maximum character limit is 1200'),
      '#required' => TRUE,
    ];
    $form['raw_data_title'] = [
      '#type' => 'item',
      '#title' => t('<h5>Data Submission</h5>'),
      '#markup' => t('Upload a zip file containing raw data (in .csv)/Data Description File (in .pdf)/ Data Source Link (in .txt)'),
    ];
    $form['raw_data_file'] = [
      '#type' => 'fieldset',
      '#title' => t('Upload data directory submission <span style="color:red">*</span>'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['raw_data_file']['raw_data_file_path'] = [
      '#type' => 'file',
      '#size' => 48,
      '#description' => t('<span style="color:red;">Upload filenames with allowed extensions only. No spaces or any special characters allowed in filename.</span>') . '<br />' . t('<span style="color:red;">Allowed file extensions: ') . \Drupal::config('r_case_study.settings')->get('proposal_raw_data_upload_extensions', '') . '</span>',
    ];
    $form['sample_references_file'] = [
      '#type' => 'item',
      '#title' => t('<h5>Literature Survey Submission</h5>'),
      '#markup' => t('Please download the template of the Literature Survey using the following link: ') . t('<a href= "https://static.fossee.in/r/Sample_R_Codes/Literature%20Survey.docx" target="_blank">Click Here</a>') . '<br>' . t('Kindly fill it and submit it in the section below.'),
    ];

    $form['literature_survey_file'] = [
      '#type' => 'fieldset',
      '#title' => t('Submit Literature Survey<span style="color:red">*</span>'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['literature_survey_file']['literature_survey_file_path'] = [
      '#type' => 'file',
      '#size' => 48,
      '#description' => t('<span style="color:red;">Upload filenames with allowed extensions only. No spaces or any special characters allowed in filename.</span>') . '<br />' . t('<span style="color:red;">Allowed file extensions: ') . \Drupal::config('r_case_study.settings')->get('proposal_literature_survey_upload_extensions', '') . '</span>',
    ];
    $form['sample_abstract_file'] = [
      '#type' => 'item',
      '#title' => t('<h5>Methodology Details</h5>'),
      '#markup' => t('Kindly refer to Point no. 4 of the Proposal Documents in the <a href= "https://r.fossee.in/case-study-project/submission-guidelines" target="_blank">Submission Guidlines</a> to know the requirements of the document.'),
    ];
    $form['abstract_file'] = [
      '#type' => 'fieldset',
      '#title' => t('Methodology Details<span style="color:red">*</span>'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['abstract_file']['abstract_file_path'] = [
      '#type' => 'file',
      '#size' => 48,
      '#description' => t('<span style="color:red;">Upload filenames with allowed extensions only. No spaces or any special characters allowed in filename.</span>') . '<br />' . t('<span style="color:red;">Allowed file extensions: ') . \Drupal::config('r_case_study.settings')->get('proposal_abstract_upload_extensions', '') . '</span>',
    ];

    $form['date_of_proposal'] = [
      '#type' => 'date_popup',
      '#title' => t('Date of Proposal'),
      '#default_value' => date("Y-m-d H:i:s"),
      '#date_format' => 'd M Y',
      '#disabled' => TRUE,
      '#date_label_position' => '',
    ];
    $form['expected_date_of_completion'] = [
      '#type' => 'date_popup',
      '#title' => t('Expected Date of Completion'),
      '#date_label_position' => '',
      '#description' => '',
      '#default_value' => '',
      '#date_format' => 'd-M-Y',
      //'#date_increment' => 0,
      //'#minDate' => '+0',
		'#date_year_range' => '0 : +1',
      '#datepicker_options' => [
        'maxDate' => 31,
        'minDate' => 0,
      ],
      '#required' => TRUE,
    ];
    $form['term_condition'] = [
      '#type' => 'checkboxes',
      '#title' => t('Terms And Conditions'),
      '#options' => [
        'status' => t('<a href="https://r.fossee.in/case-study-project/term-and-conditions" target="_blank">I agree to the Terms and Conditions</a>')
        ],
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    return $form;
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    /*if ($form_state['values']['term_condition'] == '1')
	{
		form_set_error('term_condition', t('Please check the terms and conditions'));
		// $form_state['values']['country'] = $form_state['values']['other_country'];
	}*/ //$form_state['values']['term_condition'] == '1'
    if ($form_state->getValue([
      'country'
      ]) == 'Others') {
      if ($form_state->getValue(['other_country']) == '') {
        $form_state->setErrorByName('other_country', t('Enter country name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['other_country'] == ''
      else {
        $form_state->setValue(['country'], $form_state->getValue([
          'other_country'
          ]));
      }
      if ($form_state->getValue(['other_state']) == '') {
        $form_state->setErrorByName('other_state', t('Enter state name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['other_state'] == ''
      else {
        $form_state->setValue(['all_state'], $form_state->getValue([
          'other_state'
          ]));
      }
      if ($form_state->getValue(['other_city']) == '') {
        $form_state->setErrorByName('other_city', t('Enter city name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['other_city'] == ''
      else {
        $form_state->setValue(['city'], $form_state->getValue(['other_city']));
      }
    } //$form_state['values']['country'] == 'Others'
    else {
      if ($form_state->getValue(['country']) == '') {
        $form_state->setErrorByName('country', t('Select country name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['country'] == ''
      if ($form_state->getValue([
        'all_state'
        ]) == '') {
        $form_state->setErrorByName('all_state', t('Select state name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['all_state'] == ''
      if ($form_state->getValue([
        'city'
        ]) == '') {
        $form_state->setErrorByName('city', t('Select city name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['city'] == ''
    }
    //Validation for project title
    $form_state->setValue([
      'case_study_title'
      ], trim($form_state->getValue(['case_study_title'])));
    if ($form_state->getValue(['case_study_title']) != '') {
      if (strlen($form_state->getValue(['case_study_title'])) < 10) {
        $form_state->setErrorByName('case_study_title', t('Minimum charater limit is 10 charaters, please check the length of the project title'));
      }
      else {
        if (preg_match('/[\^£$%&*()}{@#~?><>.:;`|=_+¬]/', $form_state->getValue([
          'case_study_title'
          ]))) {
          $form_state->setErrorByName('case_study_title', t('Special characters are not allowed for Case Study project title'));
        }
      } //strlen($form_state['values']['project_title']) < 10
    } //$form_state['values']['project_title'] != ''
    else {
      $form_state->setErrorByName('case_study_title', t('Project title shoud not be empty'));
    }

    if ($form_state->getValue(['how_did_you_know_about_project']) == 'Others') {
      if ($form_state->getValue(['others_how_did_you_know_about_project']) == '') {
        $form_state->setErrorByName('others_how_did_you_know_about_project', t('Please enter how did you know about the project'));
      }
      else {
        $form_state->setValue(['how_did_you_know_about_project'], $form_state->getValue([
          'others_how_did_you_know_about_project'
          ]));
      }
    }
    if ($form_state->getValue(['department']) == 'Others') {
      if ($form_state->getValue(['other_department']) == '') {
        $form_state->setErrorByName('other_department', t('Please enter the department'));
      }
      else {
        $form_state->setValue(['department'], $form_state->getValue([
          'other_department'
          ]));
      }
    }
    if ($form_state->getValue(['r_version']) == 'Others') {
      if ($form_state->getValue(['r_other_version']) == '') {
        $form_state->setErrorByName('r_other_version', t('Please enter the version'));
      }
      else {
        $form_state->setValue(['r_version'], $form_state->getValue([
          'r_other_version'
          ]));
      }
    }

    if (isset($_FILES['files'])) {
      /* check if atleast one source or result file is uploaded */
      if (!($_FILES['files']['name']['literature_survey_file_path'])) {
        $form_state->setErrorByName('literature_survey_file_path', t('Please upload the literature survey file'));
      }
      if (!($_FILES['files']['name']['abstract_file_path'])) {
        $form_state->setErrorByName('abstract_file_path', t('Please upload the abstract file'));
      }
      if (!($_FILES['files']['name']['raw_data_file_path'])) {
        $form_state->setErrorByName('raw_data_file_path', t('Please upload the raw data file'));
      }
      /* check for valid filename extensions */
      foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
        if ($file_name) {
          /* checking file type */
          if (strstr($file_form_name, 'literature_survey_file_path')) {
            $file_type = 'S';
          }
          if (strstr($file_form_name, 'abstract_file_path')) {
            $file_type = 'A';
          }
          else {
            if (strstr($file_form_name, 'raw_data_file_path')) {
              $file_type = 'R';
            }
          }
          $allowed_extensions_str = '';
          switch ($file_type) {
            case 'S':
              $allowed_extensions_str = \Drupal::config('case_study.settings')->get('proposal_literature_survey_upload_extensions', '');
              break;
            case 'A':
              $allowed_extensions_str = \Drupal::config('case_study.settings')->get('proposal_abstract_upload_extensions', '');
              break;
            case 'R':
              $allowed_extensions_str = \Drupal::config('case_study.settings')->get('proposal_raw_data_upload_extensions', '');
              break;
          }
          $allowed_extensions = explode(',', $allowed_extensions_str);
          $fnames = explode('.', strtolower($_FILES['files']['name'][$file_form_name]));
          $temp_extension = end($fnames);
          // if (!in_array($temp_extension, $allowed_extensions)) {
          //   $form_state->setErrorByName($file_form_name, t('Only file with ' . $allowed_extensions_str . ' extensions can be uploaded.'));
          // }
          // if ($_FILES['files']['size'][$file_form_name] <= 0) {
          //   $form_state->setErrorByName($file_form_name, t('File size cannot be zero.'));
          // }
          // /* check if valid file name */
          // if (!textbook_companion_check_valid_filename($_FILES['files']['name'][$file_form_name])) {
          //   $form_state->setErrorByName($file_form_name, t('Invalid file name specified. Only alphabets and numbers are allowed as a valid filename.'));
          // }
        } //$file_name
      } //$_FILES['files']['name'] as $file_form_name => $file_name
    }
    return $form_state;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $root_path = r_case_study_path();
    if (!$user->id()) {
      \Drupal::messenger()->addmessage('It is mandatory to login on this website to access the proposal form', 'error');
      return;
    }
    if ($form_state->getValue(['how_did_you_know_about_project']) == 'Others') {
      $how_did_you_know_about_project = $form_state->getValue(['others_how_did_you_know_about_project']);
    }
    else {
      $how_did_you_know_about_project = $form_state->getValue(['how_did_you_know_about_project']);
    }
    /* inserting the user proposal */
    $v = $form_state->getValues();
    $project_title = trim($v['case_study_title']);
    $proposar_name = $v['name_title'] . ' ' . $v['contributor_name'];
    $university = $v['university'];
    $directory_name = \Drupal::service("r_case_study_global")->_r_case_study_dir_name($project_title, $proposar_name);
    $result = "INSERT INTO {case_study_proposal} 
    (
    uid, 
    approver_uid,
    name_title, 
    contributor_name,
    contact_no,
    university,
    department,
    how_did_you_know_about_project,
    profession,
    faculty_name,
    faculty_department,
    faculty_email,
    city, 
    pincode, 
    state, 
    country,
    r_version,
    project_title,
    description,
    -- reference,
    directory_name,
    approval_status,
    is_completed, 
    dissapproval_reason,
    creation_date, 
    expected_date_of_completion,
    approval_date
    ) VALUES
    (
    :uid, 
    :approver_uid, 
    :name_title, 
    :contributor_name, 
    :contact_no,
    :university,
    :department,
    :how_did_you_know_about_project,
    :profession,
    :faculty_name,
    :faculty_department,
    :faculty_email,
    :city, 
    :pincode, 
    :state,  
    :country,
    :r_version,
    :project_title, 
    :description,
    -- :reference,
    :directory_name,
    :approval_status,
    :is_completed, 
    :dissapproval_reason,
    :creation_date, 
    :expected_date_of_completion,
    :approval_date
    )";
    $args = [
      ":uid" => $this->currentUser()->id(),
      ":approver_uid" => 0,
      ":name_title" => trim($v['name_title']),
      ":contributor_name" => trim($v['contributor_name']),
      ":contact_no" => $v['contributor_contact_no'],
      ":university" => trim($v['university']),
      ":department" => $v['department'],
      ":how_did_you_know_about_project" => trim($how_did_you_know_about_project),
      ":profession" => $v['profession'],
      ":faculty_name" => $v['faculty_name'],
      ":faculty_department" => $v['faculty_department'],
      ":faculty_email" => $v['faculty_email'],
      ":city" => $v['city'],
      ":pincode" => $v['pincode'],
      ":state" => $v['all_state'],
      ":country" => $v['country'],
      ":r_version" => $v['r_version'],
      ":project_title" => $project_title,
      ':description' => $v['description'],
      // ":reference" => $v['references'],
		":directory_name" => $directory_name,
      ":approval_status" => 0,
      ":is_completed" => 0,
      ":dissapproval_reason" => "NULL",
      ":creation_date" => time(),
      ":expected_date_of_completion" => $expected_date_of_completion,
      ":approval_date" => 0,
    ];
    $proposal_id = \Drupal::database()->query($result, $args, [
      'return' => Database::RETURN_INSERT_ID
      ]);
    //var_dump($args);die;
    $dest_path = $directory_name . '/';
    $dest_path1 = $root_path . $dest_path;
    //var_dump($dest_path1);die;	
    if (!is_dir($root_path . $dest_path)) {
      mkdir($root_path . $dest_path);
    }
    /* uploading files */
    foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
      if ($file_name) {
        /* checking file type */
        if (strstr($file_form_name, 'literature_survey_file_path')) {
          $file_type = 'S';
        }
        else {
          if (strstr($file_form_name, 'abstract_file_path')) {
            $file_type = 'A';
          }
          else {
            if (strstr($file_form_name, 'raw_data_file_path')) {
              $file_type = 'R';
            }
          }
        }
        switch ($file_type) {
          case 'S':
            if (file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
              \Drupal::messenger()->addMessage(t("Error uploading file. File !filename already exists.", [
                '!filename' => $_FILES['files']['name'][$file_form_name]
                ]), 'error');
              //unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
            } //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					/* uploading file */
            if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
              $query = "INSERT INTO {case_study_proposals_file} (proposal_id, filename, filepath, filemime, filesize, filetype, timestamp)
          VALUES (:proposal_id, :filename, :filepath, :filemime, :filesize, :filetype, :timestamp)";
              $args = [
                ":proposal_id" => $proposal_id,
                ":filename" => $_FILES['files']['name'][$file_form_name],
                ":filepath" => $dest_path . $_FILES['files']['name'][$file_form_name],
                ":filemime" => mime_content_type($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]),
                ":filesize" => $_FILES['files']['size'][$file_form_name],
                ":filetype" => $file_type,
                ":timestamp" => time(),
              ];
              $updateresult = \Drupal::database()->query($query, $args);
              \Drupal::messenger()->addMessage($file_name . ' uploaded successfully.', 'status');
            } //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
            else {
              \Drupal::messenger()->addMessage('Error uploading file : ' . $dest_path . $file_name, 'error');
            }
            break;
          case 'A':
            if (file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
              \Drupal::messenger()->addMessage(t("Error uploading file. File !filename already exists.", [
                '!filename' => $_FILES['files']['name'][$file_form_name]
                ]), 'error');
              //unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
            } //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					/* uploading file */
            if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
              $query = "INSERT INTO {case_study_proposals_file} (proposal_id, filename, filepath, filemime, filesize, filetype, timestamp)
          VALUES (:proposal_id, :filename, :filepath, :filemime, :filesize, :filetype, :timestamp)";
              $args = [
                ":proposal_id" => $proposal_id,
                ":filename" => $_FILES['files']['name'][$file_form_name],
                ":filepath" => $dest_path . $_FILES['files']['name'][$file_form_name],
                ":filemime" => mime_content_type($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]),
                ":filesize" => $_FILES['files']['size'][$file_form_name],
                ":filetype" => $file_type,
                ":timestamp" => time(),
              ];
              $updateresult = \Drupal::database()->query($query, $args);
              \Drupal::messenger()->addMessage($file_name . ' uploaded successfully.', 'status');
            } //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
            else {
              \Drupal::messenger()->addMessage('Error uploading file : ' . $dest_path . $file_name, 'error');
            }
            break;
          case 'R':
            if (file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
              \Drupal::messenger()->addMessage(t("Error uploading file. File !filename already exists.", [
                '!filename' => $_FILES['files']['name'][$file_form_name]
                ]), 'error');
              //unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
            } //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
					/* uploading file */
            if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
              $query = "INSERT INTO {case_study_proposals_file} (proposal_id, filename, filepath, filemime, filesize, filetype, timestamp)
          VALUES (:proposal_id, :filename, :filepath, :filemime, :filesize, :filetype, :timestamp)";
              $args = [
                ":proposal_id" => $proposal_id,
                ":filename" => $_FILES['files']['name'][$file_form_name],
                ":filepath" => $dest_path . $_FILES['files']['name'][$file_form_name],
                ":filemime" => mime_content_type($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]),
                ":filesize" => $_FILES['files']['size'][$file_form_name],
                ":filetype" => $file_type,
                ":timestamp" => time(),
              ];
              $updateresult = \Drupal::database()->query($query, $args);
              \Drupal::messenger()->addMessage($file_name . ' uploaded successfully.', 'status');
            } //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
            else {
              \Drupal::messenger()->addError('Error uploading file : ' . $dest_path . $file_name, 'error');
            }
            break;
        }

      } //$file_name
    }
    if (!$proposal_id) {
      \Drupal::messenger()->addMessage(t('Error receiving your proposal. Please try again.'), 'error');
      return;
    } //!$proposal_id
	/* sending email */
    // $email_to = $user->mail;
    // $form = variable_get('case_study_from_email', '');
    // $bcc = variable_get('case_study_emails', '');
    // $cc = variable_get('case_study_cc_emails', '');
    // $params['case_study_proposal_received']['proposal_id'] = $proposal_id;
    // $params['case_study_proposal_received']['user_id'] = $user->uid;
    // $params['case_study_proposal_received']['headers'] = [
    //   'From' => $form,
    //   'MIME-Version' => '1.0',
    //   'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
    //   'Content-Transfer-Encoding' => '8Bit',
    //   'X-Mailer' => 'Drupal',
    //   'Cc' => $cc,
    //   'Bcc' => $bcc,
    // ];
    // if (!drupal_mail('case_study', 'case_study_proposal_received', $email_to, user_preferred_language($user), $params, $form, TRUE)) {
    //   \Drupal::messenger()->addMessage('Error sending email message.', 'error');
    // }
    \Drupal::messenger()->addMessage(t('We have received your case study proposal. We will get back to you soon.'), 'status');
    // drupal_goto('');
    $response = new RedirectResponse(Url::fromRoute('<front>')->toString());
  
// Send the redirect response
  $response->send();
  }

}
?>
