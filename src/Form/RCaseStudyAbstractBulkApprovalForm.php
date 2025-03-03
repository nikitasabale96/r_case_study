<?php

namespace Drupal\r_case_study\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Render\RendererInterface;

class RCaseStudyAbstractBulkApprovalForm extends FormBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new RCaseStudyAbstractBulkApprovalForm.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'r_case_study_abstract_bulk_approval_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $options_first = \Drupal::service("r_case_study_global")->_bulk_list_of_case_study_project();
    $selected = $form_state->getValue('case_study_project', key($options_first));

    $form['case_study_project'] = [
      '#type' => 'select',
      '#title' => $this->t('Title of the Case Study'),
      '#options' => $this->_list_of_case_study(),
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => ':: ajax_bulk_case_study_abstract_details_callback',
        'wrapper' => 'ajax_selected_abstract_details',
        'event' => 'change',

      ],
    ];

    $form['download_abstract_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax_selected_abstract_details'],
    ];
    $form['download_abstract_wrapper']['selected_abstract'] = [
      '#type' => 'markup',
      '#markup' => $this->_case_study_details($selected), // Display details dynamically
    ];
    
// //===============
$case_study_default_value = $url_case_study_id;
    $form['case_study_details'] = [
      '#type' => 'item',
      '#markup' => '<div id="ajax_case_study_details">' . $this->_case_study_details($case_study_default_value) . '</div>',
    ];
    

   
    
    

  
    $form['download_abstract_wrapper']['selected_abstract'] = [
      '#type' => 'markup',
    '#markup' => Link::fromTextAndUrl($this->t('Download Case Study'), Url::fromUri('internal:/case-study-project/full-download/project/', ['project' => $case_study_proposal_id]))->toString(),

       ];

    $form['case_study_actions'] = [
      '#type' => 'select',
      '#title' => t('Please select action for Case Study'),
      '#options' => \Drupal::service("r_case_study_global")->_bulk_list_case_study_actions(),
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
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }


public function _list_of_case_study()
{
	$case_study_titles = array(
		'0' => 'Please select...'
	);
	//$lab_titles_q = db_query("SELECT * FROM {case_study_proposal} WHERE solution_display = 1 ORDER BY lab_title ASC");
	$query = \Drupal::database()->select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('approval_status', 3);
	$query->orderBy('project_title', 'ASC');
	$case_study_titles_q = $query->execute();
	while ($case_study_titles_data = $case_study_titles_q->fetchObject()) {
		$case_study_titles[$case_study_titles_data->id] = $case_study_titles_data->project_title . ' (Proposed by ' . $case_study_titles_data->name_title . ' ' . $case_study_titles_data->contributor_name . ')';
	} //$case_study_titles_data = $case_study_titles_q->fetchObject()
	return $case_study_titles;
}
  public function _case_study_details($case_study_proposal_id)
  {
    $return_html = "";
    $query_pro = \Drupal::database()->select('case_study_proposal', 'csp')
  ->fields('csp', ['id', 'name_title', 'contributor_name', 'project_title'])
  ->condition('id', (int) $case_study_proposal_id)
  ->execute()
  ->fetchObject();

    //var_dump($abstracts_pro);die;
    $query_pdf = \Drupal::database()->select('case_study_submitted_abstracts_file');
    $query_pdf->fields('case_study_submitted_abstracts_file');
    $query_pdf->condition('proposal_id', $case_study_proposal_id);
    $query_pdf->condition('filetype', 'R');
    $abstracts_pdf = $query_pdf->execute()->fetchObject();
    if ($abstracts_pdf == TRUE)
    {
      if ($abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != "")
      {
        $abstract_filename = $abstracts_pdf->filename;
      } //$abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != ""
      else
      {
        // $abstract_filename = "File not uploaded";
      }
    } //$abstracts_pdf == TRUE
    else
    {
      // $abstract_filename = "File not uploaded";
    }
    // $query_process = \Drupal::database()->select('case_study_submitted_abstracts_file');
    // $query_process->fields('case_study_submitted_abstracts_file');
    // $query_process->condition('proposal_id', $case_study_proposal_id);
    // $query_process->condition('filetype', 'C');
    // $abstracts_query_process = $query_process->execute()->fetchObject();
    // $query = \Drupal::database()->select('case_study_submitted_abstracts');
    // $query->fields('case_study_submitted_abstracts');
    // $query->condition('proposal_id', $case_study_proposal_id);
    // $abstracts_q = $query->execute()->fetchObject();
    $database = Database::getConnection();

    $abstracts_q = $database->select('case_study_submitted_abstracts', 'csa')
        ->fields('csa')
        ->condition('proposal_id', $proposal_data->id)
        ->execute()
        ->fetchObject();
        $abstracts_pro = $database->select('case_study_proposal', 'csp')
        ->fields('csp')
        ->condition('id', $proposal_data->id)
        ->execute()
        ->fetchObject();

    if ($abstracts_q)
    {
      if ($abstracts_q->is_submitted == 0)
      {
        //drupal_set_message(t('Abstract is not submmited yet.'), 'error', $repeat = FALSE);
        //return;
        $this->messenger->addError($this->t('Abstract is not submitted yet.'));

      } //$abstracts_q->is_submitted == 0
    } //$abstracts_q
    //var_dump($abstracts_query_process);die;
    if ($abstracts_query_process == TRUE)
    {
      if ($abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != "")
      {
        $abstracts_query_process_filename = $abstracts_query_process->filename;
      } //$abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != ""
      else
      {
        // $abstracts_query_process_filename = "File not uploaded";
      }
    } //$abstracts_query_process == TRUE
    else
    {
      // $url = l('Upload abstract', 'case-study-project/abstract-code/upload');
      // $abstracts_query_process_filename = "File not uploaded";
    }
    // $download_case_study = l('Download Case Study','case-study-project/full-download/project/'.$case_study_proposal_id);
    $return_html .= '<strong>Contributor Name:</strong><br />' . $abstracts_pro->name_title . ' ' . $abstracts_pro->contributor_name . '<br /><br />';
    $return_html .= '<strong>Title of the Case Study:</strong><br />' . $abstracts_pro->project_title . '<br /><br />';
    $return_html .= '<strong>Uploaded Report of the project:</strong><br />' . $abstract_filename . '<br /><br />';
    $return_html .= '<strong>Uploaded data and code files of the project:</strong><br />' . $abstracts_query_process_filename . '<br /><br />';
    $return_html .= $download_case_study;
    return $return_html;
  }

  function _case_study_information($proposal_id)
{
	$query = \Drupal::database()->select('case_study_proposal');
	$query->fields('case_study_proposal');
	$query->condition('id', $proposal_id);
	$query->condition('approval_status', 3);
	$case_study_q = $query->execute();
	$case_study_data = $case_study_q->fetchObject();
	if ($case_study_data) {
		return $case_study_data;
	} //$case_study_data
	else {
		return 'Not found';
	}
}
//  public function _case_study_details($case_study_default_value)
//   {
//     $case_study_details = $this->_case_study_information($case_study_default_value);
//     if ($case_study_default_value != 0) {
//       $form['case_study_details']['#markup'] = '<span style="color: rgb(128, 0, 0);"><strong>About the case study</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Proposer Name:</strong> ' . $case_study_details->name_title . ' ' . $case_study_details->contributor_name . '</li>' . '<li><strong>Title of the Case Study:</strong> ' . $case_study_details->project_title . '</li>' . '<li><strong>University:</strong> ' . $case_study_details->university . '</li>' . '<li><strong>R Version:</strong> ' . $case_study_details->r_version . '</li>' . '</ul>';
//       $details = $form['case_study_details']['#markup'];
//       return $details;
//     } //$case_study_default_value != 0
  
//   }
  
  //   public function  ajax_bulk_case_study_abstract_details_callback($form, $form_state) {
  //   return $form['download_abstract_wrapper'];
  // }

  public function ajax_bulk_case_study_abstract_details_callback(array &$form, FormStateInterface $form_state) {
    $selected_case_study = $form_state->getValue('case_study_project');
    $form['download_abstract_wrapper']['selected_abstract']['#markup'] = $this->_case_study_details($selected_case_study);
    return $form['download_abstract_wrapper'];
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger->addMessage($this->t('Form submitted successfully.'));
  }
}
