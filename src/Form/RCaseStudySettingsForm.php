<?php

/**
 * @file
 * Contains \Drupal\r_case_study\Form\RCaseStudySettingsForm.
 */

namespace Drupal\r_case_study\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\ConfigFormBase;

class RCaseStudySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'r_case_study_settings_form';
  }
  protected function getEditableConfigNames() {
    return [
      'r_case_study.settings',
    ];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $config = $this->config('case_study.settings');
    $form['emails'] = [
      '#type' => 'textfield',
      '#title' => t('(Bcc) Notification emails'),
      '#description' => t('Specify emails id for Bcc option of mail system with comma separated'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('case_study_emails', ''),
    ];
    $form['cc_emails'] = [
      '#type' => 'textfield',
      '#title' => t('(Cc) Notification emails'),
      '#description' => t('Specify emails id for Cc option of mail system with comma separated'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('case_study_cc_emails', ''),
    ];
    $form['from_email'] = [
      '#type' => 'textfield',
      '#title' => t('Outgoing from email address'),
      '#description' => t('Email address to be display in the from field of all outgoing messages'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('case_study_from_email', ''),
    ];
    $form['extensions']['proposal_literature_survey_file'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed file extensions for uploading literature survey file in the proposal form'),
      '#description' => t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('proposal_literature_survey_upload_extensions', ''),
    ];
    $form['extensions']['proposal_abstract_file'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed file extensions for uploading abstract files in the proposal form'),
      '#description' => t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('proposal_abstract_upload_extensions', ''),
    ];
    $form['extensions']['proposal_raw_data_file'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed file extensions for uploading the raw data file in proposal form'),
      '#description' => t('A comma separated list WITHOUT SPACE of file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('proposal_raw_data_upload_extensions', ''),
    ];
    $form['extensions']['project_submission_report'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed file extensions for uploading the report during Project Submission'),
      '#description' => t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('project_report_upload_extensions', ''),
    ];
    $form['extensions']['project_submission_code_file'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed extensions for uploading the code file during Project Submission'),
      '#description' => t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('project_code_file_upload_extensions', ''),
    ];
    $form['extensions']['resource_upload'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed file extensions for uploading resource files'),
      '#description' => t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('resource_upload_extensions', ''),
    ];
    $form['extensions']['case_study_upload'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed extensions for project files'),
      '#description' => t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('case_study_project_files_extensions', ''),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    // return $form;
    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    
      parent::validateForm($form, $form_state);
    
  
    return;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {

    parent::submitForm($form, $form_state);
   $this->config('r_case_study.settings')
  
    ->set ('case_study_emails', $form_state->getValue(['emails']))
    ->set ('case_study_cc_emails', $form_state->getValue(['cc_emails']))
    ->set ('case_study_from_email', $form_state->getValue(['from_email']))
    ->set ('proposal_literature_survey_upload_extensions', $form_state->getValue(['proposal_literature_survey_file']))
    ->set ('proposal_abstract_upload_extensions', $form_state->getValue(['proposal_abstract_file']))
    ->set ('proposal_raw_data_upload_extensions', $form_state->getValue(['proposal_raw_data_file']))
    ->set ('project_report_upload_extensions', $form_state->getValue(['project_submission_report']))
    ->set ('project_code_file_upload_extensions', $form_state->getValue(['project_submission_code_file']))
    ->set ('resource_upload_extensions', $form_state->getValue(['resource_upload']))
    ->set ('case_study_project_files_extensions', $form_state->getValue(['case_study_upload']))
    ->save();
    \Drupal::messenger()->addMessage($this->t('Settings updated'), 'status');  }

}
?>
