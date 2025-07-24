<?php

/**
 * @file
 * Contains \Drupal\r_case_study\Form\RCaseStudyRunForm.
 */

namespace Drupal\r_case_study\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RendererInterface;

class RCaseStudyRunForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'r_case_study_run_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $options_first = $this->_list_of_case_study();
    // $url_case_study_id = (int) arg(2);
    $route_match = \Drupal::routeMatch();

    $url_case_study_id = (int) $route_match->getParameter('url_case_study_id');
    $case_study_data = $this->_case_study_information($url_case_study_id);
    if ($case_study_data == 'Not found') {
      $url_case_study_id = '';
    } //$case_study_data == 'Not found'
    if (!$url_case_study_id) {
      $selected = !$form_state->getValue(['case_study']) ? $form_state->getValue(['case_study']) : key($options_first);
    } //!$url_case_study_id
    elseif ($url_case_study_id == '') {
      $selected = 0;
    } //$url_case_study_id == ''
    else {
      $selected = $url_case_study_id;
    }
    $form = [];
    // $form['case_study'] = [
    //   '#type' => 'select',
    //   '#title' => t('Title of the case study'),
    //   '#options' => $this->_list_of_case_study(),
    //   '#default_value' => $selected,
    //   '#ajax' => [
    //     'callback' => '::case_study_project_details_callback'
    //     ],
    // ];

    $form['case_study'] = [
      '#type' => 'select',
      '#title' => t('Title of the case study'),
      '#options' => $this->_list_of_case_study(),
      '#default_value' => $selected,
      '#ajax' => [
          'callback' => '::case_study_project_details_callback',
          'wrapper' => 'ajax_case_study_details', // Ensure this wrapper ID matches the div below
          'event' => 'change',
      ],
  ];
  
    if (!$url_case_study_id) {
      // $form['case_study_details'] = [
      //   '#type' => 'item',
      //   '#markup' => '<div id="ajax_case_study_details"></div>',
      // ];
      $form['case_study_details'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'ajax_case_study_details'],
        '#markup' => $this->_case_study_details($selected),
    ];
    
      $form['selected_case_study'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_case_study"></div>',
      ];
    } //!$url_case_study_id
    else {
      $case_study_default_value = $url_case_study_id;
      $form['case_study_details'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_case_study_details">' . $this->_case_study_details($case_study_default_value) . '</div>',
      ];
      $report_url = Url::fromUri('internal:/case-study-project/download/final-report/' . $case_study_default_value);
$case_study_url = Url::fromUri('internal:/case-study-project/full-download/project/' . $case_study_default_value);

$report_link = Link::fromTextAndUrl('Download Report', $report_url)->toString();
$case_study_link = Link::fromTextAndUrl('Download Case Study', $case_study_url)->toString();

$markup = '<div id="ajax_selected_case_study">' . $report_link . '<br>' . $case_study_link . '</div>';
      $form['selected_case_study'] = [
        '#type' => 'item',
        '#markup' => $markup,
        // '#markup' => '<div id="ajax_selected_case_study">' . l('Download Report', "case-study-project/download/final-report/" . $case_study_default_value) . '<br>' . l('Download Case Study', 'case-study-project/full-download/project/' . $case_study_default_value) . '</div>',
          // '#markup' => '<div id="ajax_selected_case_study">' . Url::fromUri('internal:/case-study-project/download/final-report/' . $case_study_default_value)->toString(),
      ];
    }
    return $form;
  }
  function _list_of_case_study()
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

public function case_study_project_details_callback(array &$form, FormStateInterface $form_state) {
  $response = new AjaxResponse();

  $case_study_default_value = $form_state->getValue('case_study');

  if ($case_study_default_value != 0) {
      // Get case study details
      $case_study_details = $this->_case_study_details($case_study_default_value);
      
      // Get links
      $report_url = Url::fromUri('internal:/case-study-project/download/final-report/' . $case_study_default_value);
      $case_study_url = Url::fromUri('internal:/case-study-project/full-download/project/' . $case_study_default_value);

      $report_link = Link::fromTextAndUrl('Download Report', $report_url)->toString();
      $case_study_link = Link::fromTextAndUrl('Download Case Study', $case_study_url)->toString();

      $markup = '<div id="ajax_selected_case_study">' . $report_link . '<br>' . $case_study_link . '</div>';

      // Update AJAX elements
      $response->addCommand(new HtmlCommand('#ajax_case_study_details', $case_study_details));
      $response->addCommand(new HtmlCommand('#ajax_selected_case_study', $markup));
  } else {
      // If no case study is selected, clear the details
      $response->addCommand(new HtmlCommand('#ajax_case_study_details', ''));
      $response->addCommand(new HtmlCommand('#ajax_selected_case_study', ''));
  }

  return $response;
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
function _case_study_details($case_study_default_value)
{
	$case_study_details =$this-> _case_study_information($case_study_default_value);
	if ($case_study_default_value != 0) {
		$form['case_study_details']['#markup'] = '<span style="color: rgb(128, 0, 0);"><strong>About the case study</strong></span></td><td style="width: 35%;"><br />' . '<ul>' . '<li><strong>Proposer Name:</strong> ' . $case_study_details->name_title . ' ' . $case_study_details->contributor_name . '</li>' . '<li><strong>Title of the Case Study:</strong> ' . $case_study_details->project_title . '</li>' . '<li><strong>University:</strong> ' . $case_study_details->university . '</li>' . '<li><strong>R Version:</strong> ' . $case_study_details->r_version . '</li>' . '</ul>';
		$details = $form['case_study_details']['#markup'];
		return $details;
	} //$case_study_default_value != 0
}

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {

}
}
?>
