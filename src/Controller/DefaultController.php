<?php /**
 * @file
 * Contains \Drupal\r_case_study\Controller\DefaultController.
 */

namespace Drupal\r_case_study\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Service;
use Drupal\user\Entity\User;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Default controller for the r_case_study module.
 */
class DefaultController extends ControllerBase {

  public function r_case_study_proposal_pending() {
    // Get pending proposals to be approved.
    $pending_rows = [];
      $query = \Drupal::database()->select('case_study_proposal');
      $query->fields('case_study_proposal');
      $query->condition('approval_status', 0);
      $query->orderBy('id', 'DESC');
      $pending_q = $query->execute();
        while ($pending_data = $pending_q->fetchObject()) {
      // Create links using modern Link and Url APIs.
     
      $approval_url = Link::fromTextAndUrl('Approve', Url::fromRoute('r_case_study.proposal_approval_form',['id'=>$pending_data->id]))->toString();
      $edit_url =  Link::fromTextAndUrl('Edit', Url::fromRoute('r_case_study.proposal_edit_form',['id'=>$pending_data->id]))->toString();
      $mainLink = t('@linkApprove | @linkReject', array('@linkApprove' => $approval_url, '@linkReject' => $edit_url));
  
      $pending_rows[] = [
        date('d-m-Y', $pending_data->creation_date),
        Link::fromTextAndUrl($pending_data->contributor_name, Url::fromRoute('entity.user.canonical', ['user' => $pending_data->uid])),
        $pending_data->project_title,
        ['data' => $mainLink],
      ];
    }
  
    // Check if there are any pending proposals.
    if (empty($pending_rows)) {
      \Drupal::messenger()->addStatus(t('There are no pending proposals.'));
      return '';
    }
  
    // Define table header.
    $pending_header = [
      t('Date of Submission'),
      t('Student Name'),
      t('Title of the Case Study Project'),
      t('Action'),
    ];
  
    // Render the table using renderable arrays.
    $output = [
      '#type' => 'table',
      '#header' => $pending_header,
      '#rows' => $pending_rows,
      '#attributes' => [
        'class' => ['case-study-proposal-pending-table'],
      ],
    ];
  
    return $output;
  }
  
  // public function r_case_study_proposal_pending() {
  //   /* get pending proposals to be approved */
  //   $pending_rows = [];
  //   $query = \Drupal::database()->select('case_study_proposal');
  //   $query->fields('case_study_proposal');
  //   $query->condition('approval_status', 0);
  //   $query->orderBy('id', 'DESC');
  //   $pending_q = $query->execute();
  //   while ($pending_data = $pending_q->fetchObject()) {

  //     $approval_url = Link::fromTextAndUrl('Approve', Url::fromRoute('r_case_study.proposal_approval_form',['id'=>$pending_data->id]))->toString();
  //       $edit_url = Link::fromTextAndUrl('Edit', Url::fromRoute('r_case_study.proposal_edit_form',['id'=>$pending_data->id]))->toString();
  //       $mainLink = t('@linkApprove | @linkReject', array('@linkApprove' => $approval_url, '@linkReject' => $edit_url));
      
  //       // Link::fromTextAndUrl($pending_data->name, Url::fromRoute('entity.user.canonical', ['user' => $pending_data->uid]));
  //     $pending_rows[$pending_data->id] = [
  //       date('d-m-Y', $pending_data->creation_date),
  //       // l($pending_data->name_title . ' ' . $pending_data->contributor_name, 'user/' . $pending_data->uid),
        
  //       $pending_data->project_title,
  //       // l('Approve', 'case-study-project/manage-proposal/approve/' . $pending_data->id),
        
  //       // $pending_data->lab_title,
  //       // $pending_data->department,
  //       $mainLink
  //     ];
  //   } //$pending_data = $pending_q->fetchObject()
  //   /* check if there are any pending proposals */
  //   if (!$pending_rows) {
  //     \Drupal::messenger()->addMessage(t('There are no pending proposals.'), 'status');
  //     return '';
  //   } //!$pending_rows
    

  //   $pending_header = [
  //     'Date of Submission',
  //     'Student Name',
  //     'Title of the Case Study Project',
  //     'Action',
  //   ];
  //   //$output = theme_table($pending_header, $pending_rows);
  //   $output = [
  //     '#type' => 'table',
  //     '#header' => $pending_header,
  //     '#rows' => $pending_rows,
  //   ];
  //   return $output;
  // }

  // public function r_case_study_proposal_all() {
  //   /* get pending proposals to be approved */
  //   $proposal_rows = [];
  //   $query = \Drupal::database()->select('case_study_proposal');
  //   $query->fields('case_study_proposal');
  //   $query->orderBy('id', 'DESC');
  //   $proposal_q = $query->execute();
  //   while ($proposal_data = $proposal_q->fetchObject()) {
  //     $approval_status = '';
  //     switch ($proposal_data->approval_status) {
  //       case 0:
  //         $approval_status = 'Pending';
  //         break;
  //       case 1:
  //         $approval_status = "<span style='color:red;'>Approved</span>";
  //         break;
  //       case 2:
  //         $approval_status = "<span style='color:black;'>Dis-approved</span>";
  //         break;
  //       case 3:
  //         $approval_status = "<span style='color:green;'>Completed</span>";
  //         break;
  //       case 5:
  //         $approval_status = 'On Hold';
  //         break;
  //       default:
  //         $approval_status = 'Unknown';
  //         break;
  //     } //$proposal_data->approval_status
  //     if ($proposal_data->actual_completion_date == 0) {
  //       $actual_completion_date = "Not Completed";
  //     } //$proposal_data->actual_completion_date == 0
  //     else {
  //       $actual_completion_date = date('d-m-Y', $proposal_data->actual_completion_date);
  //     }
  //     if ($proposal_data->approval_date == 0) {
  //       $approval_date = "Not Approved";
  //     } //$proposal_data->actual_completion_date == 0
  //     else {
  //       $approval_date = date('d-m-Y', $proposal_data->approval_date);
  //     }
  //     $approval_url =  Link::fromTextAndUrl('Status', Url::fromRoute('r_case_study.proposal_status_form',['id'=>$proposal_data->id]))->toString();
  //     // var_dump($approval_url);die;
  //     $edit_url =  Link::fromTextAndUrl('Edit', Url::fromRoute('r_case_study.proposal_edit_form',['id'=>$proposal_data->id]))->toString();
  //     $mainLink = t('@linkApprove | @linkReject', array('@linkApprove' => $approval_url, '@linkReject' => $edit_url));
      
  //     $proposal_rows[] = [
  //       date('d-m-Y', $proposal_data->creation_date),
  //       // l($proposal_data->contributor_name, 'user/' . $proposal_data->uid),
  //       Link::fromTextAndUrl($pending_data->name, Url::fromRoute('entity.user.canonical', ['user' => $pending_data->uid])),

  //       $proposal_data->project_title,
  //       $approval_date,
  //       $actual_completion_date,
  //       $approval_status,
  //       // l('Status', 'case-study-project/manage-proposal/status/' . $proposal_data->id) . ' | ' . l('Edit', 'case-study-project/manage-proposal/edit/' . $proposal_data->id),
  //     ];
  //   } //$proposal_data = $proposal_q->fetchObject()
  //   /* check if there are any pending proposals */
  //   // if (!$proposal_rows) {
  //   //   \Drupal::messenger()->addMessage(t('There are no proposals.'), 'status');
  //   //   return '';
  //   // } //!$proposal_rows
  //   $proposal_header = [
  //     'Date of Submission',
  //     'Name of the contributor',
  //     'Title of the case-study project',
  //     'Date of Approval',
  //     'Date of Project Completion',
  //     'Status',
  //     'Action',
  //   ];
  //   $output = [
  //     '#type' => 'table',
  //     '#header' => $pending_header,
  //     '#rows' => $pending_rows,
  //   ];
  //   return $output;
  // }
  
  public function r_case_study_proposal_all() {
    /* Get pending proposals to be approved */
    $proposal_rows = [];
    
    $query = \Drupal::database()->select('case_study_proposal', 'csp');
    $query->fields('csp');
    $query->orderBy('id', 'DESC');
    $proposal_q = $query->execute();
    
    while ($proposal_data = $proposal_q->fetchObject())
      {
        $approval_status = '';
        switch ($proposal_data->approval_status)
        {
            case 0:
                $approval_status = 'Pending';
                break;
            case 1:
                $approval_status = "Approved";
                break;
            case 2:
                $approval_status = "Dis-approved";
                break;
            case 3:
                $approval_status = "Completed";
                break;
            case 4:
                  $approval_status = "On Hold";
            default:
                $approval_status = 'Unknown';
                break;
        }
  
      // Define completion date
      $actual_completion_date = $proposal_data->actual_completion_date == 0
        ? "Not Completed"
        : date('d-m-Y', $proposal_data->actual_completion_date);
      
      // Define approval date
      $approval_date = $proposal_data->approval_date == 0
        ? "Not Approved"
        : date('d-m-Y', $proposal_data->approval_date);
  
      // Generate approval and edit URLs
      $approval_url = Link::fromTextAndUrl(
        'Status', 
        Url::fromRoute('r_case_study.proposal_status_form', ['id' => $proposal_data->id])
      )->toRenderable()['#markup'];
  
      $edit_url = Link::fromTextAndUrl(
        'Edit', 
        Url::fromRoute('r_case_study.proposal_edit_form', ['id' => $proposal_data->id])
      )->toRenderable()['#markup'];
  
      // Contributor name link
      // $contributor_link = Link::fromTextAndUrl(
      //   $proposal_data->contributor_name,
      //   Url::fromRoute('entity.user.canonical', ['user' => $proposal_data->uid])
      // )->toRenderable()['#markup'];
      $contributor_link = Link::fromTextAndUrl($proposal_data->contributor_name, Url::fromRoute('entity.user.canonical', ['user' => $proposal_data->uid]))->toString();

  
      $status_url = Link::fromTextAndUrl('Status', Url::fromRoute('r_case_study.proposal_status_form',['id'=>$proposal_data->id]))->toString();
      $edit_url =  Link::fromTextAndUrl('Edit', Url::fromRoute('r_case_study.proposal_edit_form',['id'=>$proposal_data->id]))->toString();
      // $mainLink = t('@linkApprove | @linkReject', array('@linkApprove' => $approval_url, '@linkReject' => $edit_url));
  
      // Construct table row
      $proposal_rows[] = [
        date('d-m-Y', $proposal_data->creation_date),
        ['data' => ['#markup' => $contributor_link]], // Ensures link rendering
        $proposal_data->project_title,
        $approval_date,
        $actual_completion_date,
        ['data' => ['#markup' => $approval_status]], // Allows HTML span styling
        ['data' => ['#markup' => $status_url . ' | ' . $edit_url]], // Ensures both links render correctly
      ];
    }
  
    /* Check if there are any pending proposals */
    // if (empty($proposal_rows)) {
    //   \Drupal::messenger()->addMessage(t('There are no proposals.'), 'status');
    //   return [];
    // }
  
    // Define table header
    $proposal_header = [
      'Date of Submission',
      'Name of the Contributor',
      'Title of the Case-Study Project',
      'Date of Approval',
      'Date of Project Completion',
      'Status',
      'Action',
    ];
  
    // Render the table
    $output = [
      '#type' => 'table',
      '#header' => $proposal_header,
      '#rows' => $proposal_rows,
      '#empty' => t('No proposals found.'),
    ];
    return $output;
  }
  
 

  public function r_case_study_proposal_edit_file_all() {
    /* get pending proposals to be approved */
    $proposal_rows = [];
    $query = \Drupal::database()->select('case_study_proposal');
    $query->fields('case_study_proposal');
    $query->orderBy('id', 'DESC');
    $query->condition('approval_status', '0', '<>');
    $query->condition('approval_status', '1', '<>');
    $query->condition('approval_status', '2', '<>');
    $query->orderBy('approval_status', 'DESC');
    $proposal_q = $query->execute();
    while ($proposal_data = $proposal_q->fetchObject()) {
      $approval_status = '';
      switch ($proposal_data->approval_status) {
        case 0:
          $approval_status = 'Pending';
          break;
        case 1:
          $approval_status = 'Approved';
          break;
        case 2:
          $approval_status = 'Dis-approved';
          break;
        case 3:
          $approval_status = 'Completed';
          break;
        case 5:
          $approval_status = 'On Hold';
          break;
        default:
          $approval_status = 'Unknown';
          break;
      } //$proposal_data->approval_status
      if ($proposal_data->actual_completion_date == 0) {
        $actual_completion_date = "Not Completed";
      } //$proposal_data->actual_completion_date == 0
      else {
        $actual_completion_date = date('d-m-Y', $proposal_data->actual_completion_date);
      }
      if ($proposal_data->approval_date == 0) {
        $approval_date = "Not Approved";
      } //$proposal_data->actual_completion_date == 0
      else {
        $approval_date = date('d-m-Y', $proposal_data->approval_date);
      }
      $proposal_rows[] = [
        date('d-m-Y', $proposal_data->creation_date),
        // l($proposal_data->contributor_name, 'user/' . $proposal_data->uid),
      Link::fromTextAndUrl($proposal_data->contributor_name, Url::fromRoute('entity.user.canonical', ['user' => $proposal_data->uid]))->toString(),

        $proposal_data->project_title,
        $approval_date,
        $actual_completion_date,
        $approval_status,
        // l('Edit', 'case-study-project/abstract-code/edit-upload-files/' . $proposal_data->id),
         Link::fromTextAndUrl('Edit', Url::fromRoute('r_case_study.edit_upload_abstract_code_form',['id'=>$proposal_data->id]))->toString(),
      ];
    } //$proposal_data = $proposal_q->fetchObject()
    /* check if there are any pending proposals */
    if (!$proposal_rows) {
      \Drupal::messenger()->addMessage(t('There are no proposals.'), 'status');
      return '';
    } //!$proposal_rows
    $proposal_header = [
      'Date of Submission',
      'Student Name',
      'Title of the case-study project',
      'Date of Approval',
      'Date of Project Completion',
      'Status',
      'Action',
    ];
    $output = [
      '#type' => 'table',
      '#header' => $proposal_header,
      '#rows' => $proposal_rows,
      '#empty' => t('No proposals found.'),
    ];
    return $output;
  }

  
public function r_case_study_abstract() {
    $user = \Drupal::currentUser();
    $return_html = "";

    // Fetch proposal data using the custom service.
    $proposal_data = \Drupal::service("r_case_study_global")->r_case_study_get_proposal();
    // if (!$proposal_data) {
    //     return;
    // }
    // if (!$proposal_data) {
    //   return new RedirectResponse(Url::fromRoute('<front>')->toString());
    // }

    // Get existing submitted abstracts.
    $database = Database::getConnection();
    
    if (!$proposal_data) {
      return [
          '#type' => 'markup',
          '#markup' => '<p style="color:red;">No proposal data found.</p>',
      ];
  }
  
  
    $abstracts_q = $database->select('case_study_submitted_abstracts', 'csa')
        ->fields('csa')
        ->condition('proposal_id', $proposal_data->id)
        ->execute()
        ->fetchObject();

    // Fetch proposal details.
    $abstracts_pro = $database->select('case_study_proposal', 'csp')
        ->fields('csp')
        ->condition('id', $proposal_data->id)
        ->execute()
        ->fetchObject();

    // Fetch uploaded report (filetype: R)
//     $abstracts_pdf = $database->select('case_study_submitted_abstracts_file', 'csaf')
//         ->fields('csaf')
//         ->condition('proposal_id', $proposal_data->id)
//         ->condition('filetype', 'R')
//         ->execute()
//         ->fetchObject();

//     // Determine uploaded report filename
//     $abstract_filename = (!empty($abstracts_pdf) && !empty($abstracts_pdf->filename)) ? $abstracts_pdf->filename : "File not uploaded";
//     // Determine uploaded report filename
   
    

// // var_dump($abstract_filename);die;
//     // Fetch uploaded code/data files (filetype: C)
//     $abstracts_query_process = $database->select('case_study_submitted_abstracts_file', 'csaf')
//         ->fields('csaf')
//         ->condition('proposal_id', $proposal_data->id)
//         ->condition('filetype', 'C')
//         ->execute()
//         ->fetchObject();
//         // Determine uploaded code/data filename

// var_dump($abstracts_query_process_filename);die;

    // Determine uploaded code/data filename
    // $abstracts_query_process_filename = (!empty($abstracts_query_process) && !empty($abstracts_query_process->filename))
        // ? $abstracts_query_process->filename
        // : "File not uploaded";

        $abstracts_pdf = $database->select('case_study_submitted_abstracts_file', 'csaf')
    ->fields('csaf')
    ->condition('proposal_id', $proposal_data->id)
    ->condition('filetype', 'R')
    ->execute()
    ->fetchObject();

\Drupal::logger('r_case_study')->notice('Fetched abstracts_pdf: ' . print_r($abstracts_pdf, TRUE));

// Fetch uploaded code/data files (filetype: C)
$abstracts_query_process = $database->select('case_study_submitted_abstracts_file', 'csaf')
    ->fields('csaf')
    ->condition('proposal_id', $proposal_data->id)
    ->condition('filetype', 'C')
    ->execute()
    ->fetchObject();

\Drupal::logger('r_case_study')->notice('Fetched abstracts_query_process: ' . print_r($abstracts_query_process, TRUE));
$abstract_filename = !empty($abstracts_pdf) && isset($abstracts_pdf->filename) 
    ? $abstracts_pdf->filename 
    : "File not uploaded";

$abstracts_query_process_filename = !empty($abstracts_query_process) && isset($abstracts_query_process->filename) 
    ? $abstracts_query_process->filename 
    : "File not uploaded";

\Drupal::logger('r_case_study')->notice("Abstract filename: $abstract_filename");
\Drupal::logger('r_case_study')->notice("Abstract query process filename: $abstracts_query_process_filename");

    // Generate upload/edit links based on submission status
    if (!empty($abstracts_q) && isset($abstracts_q->is_submitted)) {
        if ($abstracts_q->is_submitted == 0) {
            $url = Link::fromTextAndUrl(
                'Edit',
                Url::fromRoute('case-study-project.upload_abstract_code_form')
            )->toString();
        } elseif ($abstracts_q->is_submitted == 1) {
            $url = ""; // No edit link if already submitted
        } else {
            $url = Link::fromTextAndUrl(
                'Upload project files',
                Url::fromRoute('r_case_study.upload_abstract_code_form')
            )->toString();
        }
    } else {
        $url = Link::fromTextAndUrl(
            'Upload project files',
            Url::fromRoute('r_case_study.upload_abstract_code_form')
        )->toString();
    }

    // Generate return HTML
    $return_html .= '<strong>Contributor Name:</strong><br />' . htmlspecialchars($proposal_data->name_title) . ' ' . htmlspecialchars($proposal_data->contributor_name) . '<br /><br />';
    $return_html .= '<strong>Title of the Case Study:</strong><br />' . htmlspecialchars($proposal_data->project_title) . '<br /><br />';
    $return_html .= '<strong>Uploaded Report of the project:</strong><br />' . htmlspecialchars($abstract_filename) . '<br /><br />';
    $return_html .= '<strong>Uploaded data and code files of the project:</strong><br />' . htmlspecialchars($abstracts_query_process_filename) . '<br /><br />';
    $return_html .= $url . '<br />';

    return [
        '#type' => 'markup',
        '#markup' => $return_html,
        '#allowed_tags' => ['br', 'strong', 'a'], // Security: Whitelist allowed HTML tags
    ];
}

  // public function r_case_study_abstract() {
  //   $user = \Drupal::currentUser();
  //   $return_html = "";
  //   $proposal_data =  \Drupal::service("r_case_study_global")->r_case_study_get_proposal();
  //   // var_dump($proposal_data);die;
  //   if (!$proposal_data) {
  //     // drupal_goto('');
  //     return;
  //   } //!$proposal_data
  //   //$return_html .= l('Upload abstract', 'powersystems/pssp/abstract-code/upload') . '<br />';
	// /* get experiment list */
  //   $query = \Drupal::database()->select('case_study_submitted_abstracts');
  //   $query->fields('case_study_submitted_abstracts');
  //   $query->condition('proposal_id', $proposal_data->id);
  //   $abstracts_q = $query->execute()->fetchObject();
  //   $query_pro = \Drupal::database()->select('case_study_proposal');
  //   $query_pro->fields('case_study_proposal');
  //   $query_pro->condition('id', $proposal_data->id);
  //   $abstracts_pro = $query_pro->execute()->fetchObject();
  //   $query_pdf = \Drupal::database()->select('case_study_submitted_abstracts_file');
  //   $query_pdf->fields('case_study_submitted_abstracts_file');
  //   $query_pdf->condition('proposal_id', $proposal_data->id);
  //   $query_pdf->condition('filetype', 'R');
  //   $abstracts_pdf = $query_pdf->execute()->fetchObject();
  //   // var_dump($query);die;
  //   if ($abstracts_pdf == TRUE) {
  //     if ($abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != "") {
  //       $abstract_filename = $abstracts_pdf->filename;
  //       //$abstract_filename = l($abstracts_pdf->filename, 'powersystems/pssp/download/project-file/' . $proposal_data->id);
  //     } //$abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != ""
  //     else {
  //       $abstract_filename = "File not uploaded";
  //     }
  //   } //$abstracts_pdf == TRUE
  //   else {
  //     $abstract_filename = "File not uploaded";
  //   }
  //   $query_process = \Drupal::database()->select('case_study_submitted_abstracts_file');
  //   $query_process->fields('case_study_submitted_abstracts_file');
  //   $query_process->condition('proposal_id', $proposal_data->id);
  //   $query_process->condition('filetype', 'C');
  //   $abstracts_query_process = $query_process->execute()->fetchObject();
  //   // var_dump($abstracts_query_process);die;
  //   if ($abstracts_query_process == TRUE) {
  //     if ($abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != "") {
  //       $abstracts_query_process_filename = $abstracts_query_process->filename;
  //       //$abstracts_query_process_filename = l($abstracts_query_process->filename, 'powersystems/pssp/download/project-file/' . $proposal_data->id); 
  //     } //$abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != ""
  //     else {
  //       $abstracts_query_process_filename = "File not uploaded";
  //     }
  //     if ($abstracts_q->is_submitted == '') {
  //       // $url = l('Upload project files', 'case-study-project/abstract-code/upload');
  //       $url = Link::fromTextAndUrl(
  //         t('Upload project files'),
  //         Url::fromRoute('r_case_study.upload_abstract_code_form')
  //     )->toString();
      
  //     } //$abstracts_q->is_submitted == ''
  //     else {
  //       if ($abstracts_q->is_submitted == 1) {
  //         $url = "";
  //       } //$abstracts_q->is_submitted == 1
  //       else {
  //         if ($abstracts_q->is_submitted == 0) {
  //           // $url = l('Edit', 'case-study-project/abstract-code/upload');
  //           $url = Link::fromTextAndUrl(
  //             t('Edit'),
  //             Url::fromRoute('case-study-project.upload_abstract_code_form')
  //         )->toString();
  //         }
  //       }
  //     } //$abstracts_q->is_submitted == 0
  //   } //$abstracts_query_process == TRUE
  //   else {
  //     // $url = l('Upload project files', 'case-study-project/abstract-code/upload');
  //     $url = Link::fromTextAndUrl(
  //       t('Upload project files'),
  //       Url::fromRoute('r_case_study.upload_abstract_code_form')
  //   )->toString();
  //     $abstracts_query_process_filename = "File not uploaded";
  //   }
  //   $return_html .= '<strong>Contributor Name:</strong><br />' . $proposal_data->name_title . ' ' . $proposal_data->contributor_name . '<br /><br />';
  //   $return_html .= '<strong>Title of the Case Study:</strong><br />' . $proposal_data->project_title . '<br /><br />';
  //   $return_html .= '<strong>Uploaded Report of the project:</strong><br />' . $abstract_filename . '<br /><br />';
  //   $return_html .= '<strong>Uploaded data and code files of the project:</strong><br />' . $abstracts_query_process_filename . '<br /><br />';
  //   $return_html .= $url . '<br />';
  //   return $return_html;
  // }

  public function r_case_study_download_full_project() {
    // $user = \Drupal::currentUser();
    // $id = arg(3);
    
    $route_match = \Drupal::routeMatch();

    $id = (int) $route_match->getParameter('id');
    // var_dump($id);die;
    // var_dump($root_path);die;
    $root_path = \Drupal::service("r_case_study_global")->r_case_study_path();
    
    // var_dump($_SERVER['D OCUMENT_ROOT'] . base_path());die;
    // var_dump($root_path);die;
    $query = \Drupal::database()->select('case_study_proposal');
    $query->fields('case_study_proposal');
    $query->condition('id', $proposal_id);
    $case_study_q = $query->execute();
    $case_study_data = $case_study_q->fetchObject();
    $CS_PATH = $case_study_data->directory_name . '/';
    // var_dump($case_study_data);die;
    /* zip filename */
    $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
    // var_dump($zip_filename);die;
    /* creating zip archive on the server */
    $zip = new \ZipArchive();
    $zip->open($zip_filename, \ZipArchive::CREATE);
    $query = \Drupal::database()->select('case_study_proposal');
    $query->fields('case_study_proposal');
    $query->condition('id', $id);
    $case_study_udc_q = $query->execute();
    $query_proposal_files = \Drupal::database()->select('case_study_proposals_file');
    $query_proposal_files->fields('case_study_proposals_file');
    $query_proposal_files->condition('proposal_id', $id);
    $proposal_files = $query_proposal_files->execute();
    while ($proposal_files_data = $proposal_files->fetchObject()) {
      $zip->addFile($root_path . $proposal_files_data->filepath, $CS_PATH . str_replace(' ', '_', basename($proposal_files_data->filename)));
    }
    // var_dump($root_path . $proposal_files_data->filepath, $CS_PATH . str_replace(' ', '_', basename($proposal_files_data->filename)));die;
    $query = \Drupal::database()->select('case_study_submitted_abstracts_file');
    $query->fields('case_study_submitted_abstracts_file');
    $query->condition('proposal_id', $id);
    $project_files = $query->execute();
    //var_dump($project_files->rowCount());die;
    while ($cs_project_files = $project_files->fetchObject()) {
      // var_dump($root_path . $CS_PATH . 'project_files/' . $cs_project_files->filepath);die;
      $zip->addFile($root_path . $CS_PATH . 'project_files/' . $cs_project_files->filepath, $CS_PATH . 'project_files/' . str_replace(' ', '_', basename($cs_project_files->filename)));
      // $zip->addFile($root_path . $LAB_PATH . $solution_files_row->filepath, $LAB_PATH . $EXP_PATH . $CODE_PATH . str_replace(' ', '_', ($solution_files_row->filename)));
    }
    $zip_file_count = $zip->numFiles;
    //var_dump($zip_file_count);die;
    $zip->close();
    if ($zip_file_count > 0) {
      if ($user->uid) {
        /* download zip file */
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $case_study_data->project_title) . '.zip"');
        header('Content-Length: ' . filesize($zip_filename));
        ob_end_flush();
        ob_clean();
        flush();
        readfile($zip_filename);
        unlink($zip_filename);
      } //$user->uid
      else {
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $case_study_data->project_title) . '.zip"');
        header('Content-Length: ' . filesize($zip_filename));
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0');
        header('Pragma: no-cache');
        ob_end_flush();
        ob_clean();
        flush();
        readfile($zip_filename);
        unlink($zip_filename);
      }
    } //$zip_file_count > 0
    else {
      \Drupal::messenger()->addMessage("There is no Case Study in this proposal to download", 'error');
      // drupal_goto('case-study-project/completed-case-studies');
      // return new RedirectResponse(Url::fromRoute('case-study-project.completed_proposals_all')->toString());
      // $response = new RedirectResponse(Url::fromUri('internal:/case-study-project/completed-case-studies')->toString());
      // $response->send();
    }
  }
//   public function r_case_study_download_full_project() {
//     $route_match = \Drupal::routeMatch();
//     $id = (int) $route_match->getParameter('id');
//     $root_path = \Drupal::service("r_case_study_global")->r_case_study_path();
//     $user = \Drupal::currentUser();

//     // Fetch case study data
//     $query = \Drupal::database()->select('case_study_proposal', 'csp');
//     $query->fields('csp');
//     $query->condition('id', $id);
//     $case_study_q = $query->execute();
//     $case_study_data = $case_study_q->fetchObject();

//     if (!$case_study_data) {
//         throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Case study not found.");
//     }

//     $CS_PATH = $case_study_data->directory_name . '/';

//     // Generate ZIP filename
//     $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';

//     // Create ZIP archive
//     $zip = new \ZipArchive();
//     if ($zip->open($zip_filename, \ZipArchive::CREATE) !== true) {
//         throw new \Exception("Could not create ZIP file.");
//     }

//     // Fetch proposal files
//     $query_proposal_files = \Drupal::database()->select('case_study_proposals_file', 'csf');
//     $query_proposal_files->fields('csf');
//     $query_proposal_files->condition('proposal_id', $id);
//     $proposal_files = $query_proposal_files->execute();

//     while ($proposal_files_data = $proposal_files->fetchObject()) {
//         $full_file_path = $root_path . $proposal_files_data->filepath;
//         if (file_exists($full_file_path)) {
//             $zip->addFile($full_file_path, $CS_PATH . str_replace(' ', '_', basename($proposal_files_data->filename)));
//         } else {
//             \Drupal::logger('r_case_study')->warning("File not found: " . $full_file_path);
//         }
//     }

//     // Fetch project files
//     $query = \Drupal::database()->select('case_study_submitted_abstracts_file', 'csaf');
//     $query->fields('csaf');
//     $query->condition('proposal_id', $id);
//     $project_files = $query->execute();

//     while ($cs_project_files = $project_files->fetchObject()) {
//         $full_project_path = $root_path . $CS_PATH . 'project_files/' . $cs_project_files->filepath;
//         if (file_exists($full_project_path)) {
//             $zip->addFile($full_project_path, $CS_PATH . 'project_files/' . str_replace(' ', '_', basename($cs_project_files->filename)));
//         } else {
//             \Drupal::logger('r_case_study')->warning("Project file not found: " . $full_project_path);
//         }
//     }

//     $zip_file_count = $zip->numFiles;
//     $zip->close();

//     if ($zip_file_count > 0) {
//         if (ob_get_length()) {
//             ob_end_clean();
//         }

//         // Prepare file download response
//         header('Content-Type: application/zip');
//         header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $case_study_data->project_title) . '.zip"');
//         header('Content-Length: ' . filesize($zip_filename));
//         header("Content-Transfer-Encoding: binary");
//         header('Expires: 0');
//         header('Pragma: no-cache');

//         readfile($zip_filename);
//         unlink($zip_filename);
//         exit;
//     } else {
//         \Drupal::messenger()->addMessage("There are no files in this case study to download.", 'error');
//     }
// }

  
  // public function r_case_study_completed_proposals_all() {
  //   $output = "";
  //   $query = \Drupal::database()->select('case_study_proposal');
  //   $query->fields('case_study_proposal');
  //   $query->condition('approval_status', 3);
  //   $query->orderBy('actual_completion_date', 'DESC');
  //   //$query->condition('is_completed', 1);
  //   $result = $query->execute();

  //   if ($result->rowCount() == 0) {
  //     $output .= "We welcome your contributions." . "<hr>";

  //   } //$result->rowCount() == 0
  //   else {
  //     $output .= "Work has been completed for the following Case Studies. We welcome your contributions." . "<hr>";
  //     $preference_rows = [];
  //     $i = $result->rowCount();
  //     while ($row = $result->fetchObject()) {
  //       $proposal_id = $row->id;
  //       $query1 = \Drupal::database()->select('case_study_submitted_abstracts_file');
  //       $query1->fields('case_study_submitted_abstracts_file');
  //       $query1->condition('file_approval_status', 1);
  //       $query1->condition('proposal_id', $proposal_id);
  //       $project_files = $query1->execute();
  //       $project_abstract = $project_files->fetchObject();
  //       $completion_date = date("Y", $row->actual_completion_date);
  //       $preference_rows[] = [
  //         $i,
  //         l($row->project_title, "case-study-project/case-study-run/" . $row->id),
  //         $row->contributor_name,
  //         $row->university,
  //         $completion_date,
  //       ];
  //       $i--;
  //     } //$row = $result->fetchObject()
  //     $preference_header = [
  //       'No',
  //       'Title of the Case Study',
  //       'Contributor Name',
  //       'University / Institute',
  //       'Year of Completion',
  //     ];
  //     $output .= theme('table', [
  //       'header' => $preference_header,
  //       'rows' => $preference_rows,
  //     ]);
  //   }
  //   return $output;
  // }

  
  
  public function r_case_study_completed_proposals_all() {
      // Initialize render array
      $output = [];
  
      // Query database
      $query = \Drupal::database()->select('case_study_proposal', 'csp');
      $query->fields('csp', ['id', 'project_title', 'contributor_name', 'university', 'actual_completion_date']);
      $query->condition('approval_status', 3);
      $query->orderBy('actual_completion_date', 'DESC');
  
      $results = $query->execute()->fetchAll(); // Fetch all results
  
      // Check if there are no records
      if (empty($results)) {
          $output['message'] = [
              '#markup' => "<p>We welcome your contributions.</p><hr>",
          ];
      } else {
          $output['message'] = [
              '#markup' => "<p>Work has been completed for the following Case Studies. We welcome your contributions.</p><hr>",
          ];
  
          $preference_rows = [];
          $i = count($results); // Count total rows
  
          foreach ($results as $row) {
              $proposal_id = $row->id;
  
              // Fetch related project files
              $query1 = \Drupal::database()->select('case_study_submitted_abstracts_file', 'csaf');
              $query1->fields('csaf', ['id']);
              $query1->condition('file_approval_status', 1);
              $query1->condition('proposal_id', $proposal_id);
              $project_files = $query1->execute()->fetchAll();
  
              // Convert date to year
              $completion_date = date("Y");
  
              // Generate link for project title
              $url = Url::fromUri('internal:/case-study-project/case-study-run/' . $row->id);
              $project_link = Link::fromTextAndUrl($row->project_title, $url)->toRenderable();
  
              $preference_rows[] = [
                  $i,
                  ['data' => $project_link], // Renderable array for the link
                  $row->contributor_name,
                  $row->university,
                  $completion_date,
              ];
              $i--;
          }
  
          // Define table headers
          $preference_header = [
              'No',
              'Title of the Case Study',
              'Contributor Name',
              'University / Institute',
              'Year of Completion',
          ];
  
          // Create render array for the table
          $output['table'] = [
              '#type' => 'table',
              '#header' => $preference_header,
              '#rows' => $preference_rows,
          ];
      }
  
      return $output;
  }

  
  
public function r_case_study_progress_all() {
  // Ensure $page_content is an array
  $page_content = [];

  // Fetch data from the database
  $query = \Drupal::database()->select('case_study_proposal', 'csp');
  $query->fields('csp', ['project_title', 'contributor_name', 'university', 'approval_date']);
  $query->condition('approval_status', 1);
  $query->condition('is_completed', 0);
  $query->orderBy('approval_date', 'DESC');

  $result = $query->execute();
  $rows = $result->fetchAll(); // Convert to an array

  // Check if there are no records
  if (empty($rows)) {
      $page_content = [
          '#markup' => "<p>We welcome your contributions.</p><hr>",
      ];
  } else {
      $preference_rows = [];
      $i = 1; // Start numbering from 1

      foreach ($rows as $row) {
          // Always show the current year instead of the stored year
          $approval_date = date("Y");

          $preference_rows[] = [
              $i,
              $row->project_title,
              $row->contributor_name,
              $row->university,
              $approval_date,
          ];
          $i++;
      }

      // Define table headers
      $preference_header = [
          'No',
          'Case Study Project',
          'Contributor Name',
          'University / Institute',
          'Year',
      ];

      // Create table render array
      $table = [
          '#type' => 'table',
          '#header' => $preference_header,
          '#rows' => $preference_rows,
      ];

      // Wrap message and table in a render array
       // Message without list formatting
       $page_content['message'] = [
        '#markup' => "<p>Work is in progress for the following Case Studies under Case Studies Project</p><hr>",
    ];

    $page_content['table'] = $table;


  }

  return $page_content;
}

  public function r_case_study_proposal_literature_survey_file() {
    // $proposal_id = arg(3);
    $route_match = \Drupal::routeMatch();

    $proposal_id = (int) $route_match->getParameter('id');
    $root_path = \Drupal::service("r_case_study_global")->r_case_study_path();
    // var_Dump($root_path);die;
    $query = \Drupal::database()->select('case_study_proposals_file');
    $query->fields('case_study_proposals_file');
    $query->condition('proposal_id', $proposal_id);
    $query->condition('filetype', 'S');
    $result = $query->execute();
    $case_study_literature_survey_files = $result->fetchObject();
    // var_dump($case_study_literature_survey_files);die;
    $query1 = \Drupal::database()->select('case_study_proposal');
    $query1->fields('case_study_proposal');
    $query1->condition('id', $proposal_id);
    $result1 = $query1->execute();
    $case_study = $result1->fetchObject();
    $directory_name = $case_study->directory_name . '/';
    $literature_survey_file = $case_study_literature_survey_files->filename;
    // var_dump($literature_survey_file);die;
    ob_clean();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type:" . $case_study_abstract_file->filemime);
    header('Content-disposition: attachment; filename="' . $literature_survey_file . '"');
    header("Content-Length: " . filesize($root_path . $directory_name . $literature_survey_file));
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");
    header("Pragma: no-cache");
    readfile($root_path . $directory_name . $literature_survey_file);
    ob_end_flush();
    ob_clean();
  }

  // public function r_case_study_download_final_report() {
  //   // $proposal_id = arg(3);
  //   // $root_path = r_case_study_path();

  //   $route_match = \Drupal::routeMatch();
  //   $proposal_id = (int) $route_match->getParameter('id');
  //   $root_path = \Drupal::service("r_case_study_global")->r_case_study_path();
  //   $query = \Drupal::database()->select('case_study_proposal');
  //   $query->fields('case_study_proposal');
  //   $query->condition('id', $proposal_id);
  //   $result = $query->execute();
  //   $r_case_study_project_files = $result->fetchObject();
  //   $query = \Drupal::database()->select('case_study_submitted_abstracts_file');
  //   $query->fields('case_study_submitted_abstracts_file');
  //   $query->condition('proposal_id', $proposal_id);
  //   $query->condition('filetype', 'R');
  //   $project_files = $query->execute();
  //   $final_report_data = $project_files->fetchObject();
  //   $directory_name = $r_case_study_project_files->directory_name . '/project_files/';
  //   /*$str = substr($r_case_study_project_files->samplefilepath, strrpos($r_case_study_project_files->samplefilepath, '/'));
  //   $abstract_file = ltrim($str, '/');*/
  //   //var_dump($final_report_data);die;
  //   ob_clean();
  //   header("Pragma: public");
  //   header("Expires: 0");
  //   header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  //   header("Cache-Control: public");
  //   header("Content-Description: File Transfer");
  //   header("Content-Type: application/pdf");
  //   header('Content-disposition: attachment; filename="' . $final_report_data->filename . '"');
  //   header("Content-Length: " . filesize($root_path . $directory_name . $final_report_data->filename));
  //   header("Content-Transfer-Encoding: binary");
  //   header("Expires: 0");
  //   header("Pragma: no-cache");
  //   readfile($root_path . $directory_name . $final_report_data->filename);
  //   ob_end_flush();
  //   ob_clean();
  // }

  

  public function r_case_study_download_final_report() {
    $route_match = \Drupal::routeMatch();
    $proposal_id = (int) $route_match->getParameter('id');
    $root_path = \Drupal::service("r_case_study_global")->r_case_study_path();

    // Fetch case study details
    $query = \Drupal::database()->select('case_study_proposal', 'csp');
    $query->fields('csp');
    $query->condition('id', $proposal_id);
    $result = $query->execute();
    $case_study_project_files = $result->fetchObject();
// var_dump($case_study_project_files);die;
    // var_dump($proposal_id);die;
    // if (!$case_study_project_files) {
    //     throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Case study not found.");
    // }

    // Fetch final report details
    // $query = \Drupal::database()->select('case_study_submitted_abstracts_file', 'csaf');
    // $query->fields('csaf');
    // $query->condition('proposal_id', $proposal_id);
    // $query->condition('filetype', 'R');
    // $project_files = $query->execute();
// var_dump($project_files);die;
    $final_report_data = \Drupal::database()->select('case_study_submitted_abstracts_file', 'csaf')
    ->fields('csaf')
    ->condition('id', $proposal_id)
    ->condition('filetype', 'R')
    ->execute()
    ->fetchObject();

    // $final_report_data = $project_files->fetchObject();

    // if (!$final_report_data) {
    //     throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Final report not found.");
    // }

    // Get the file path
    $directory_name = $case_study_project_files->directory_name . '/project_files/';
    $file_path = $root_path . $directory_name . $final_report_data->filename;
// var_dump($root_path . $directory_name . $final_report_data->filename);die;
    // var_dump($final_report_data);die;
    ob_clean();
    // Set headers for file download
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: application/pdf");
    header('Content-Disposition: attachment; filename="' . basename($final_report_data->filename) . '"');
    header("Content-Length: " . filesize($file_path));
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");
    header("Pragma: no-cache");
    ob_end_flush();
    ob_clean();
    // Read file
    readfile($file_path);
    exit;
}



  public function r_case_study_proposal_abstract_file() {
    // $proposal_id = arg(3);
    $route_match = \Drupal::routeMatch();

    $proposal_id = (int) $route_match->getParameter('id');
    $root_path = \Drupal::service("r_case_study_global")->r_case_study_path();
    // var_dump($root_path);die;
    $query = \Drupal::database()->select('case_study_proposals_file');
    $query->fields('case_study_proposals_file');
    $query->condition('proposal_id', $proposal_id);
    $query->condition('filetype', 'A');
    $result = $query->execute();
    $case_study_abstract_files = $result->fetchObject();
    $query1 = \Drupal::database()->select('case_study_proposal');
    $query1->fields('case_study_proposal');
    $query1->condition('id', $proposal_id);
    $result1 = $query1->execute();
    $case_study = $result1->fetchObject();
    $directory_name = $case_study->directory_name . '/';
    $abstract_file = $case_study_abstract_files->filename;
    ob_clean();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type:" . $case_study_abstract_file->filemime);
    header('Content-disposition: attachment; filename="' . $abstract_file . '"');
    header("Content-Length: " . filesize($root_path . $directory_name . $abstract_file));
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");
    header("Pragma: no-cache");
    readfile($root_path . $directory_name . $abstract_file);
    ob_end_flush();
    ob_clean();
  //  var_dump($root_path . $directory_name . $abstract_file);die;
    return;
  }

  public function r_case_study_proposal_rawdata_file() {
    // $proposal_id = arg(3);
$route_match = \Drupal::routeMatch();
$proposal_id = (int) $route_match->getParameter('id');

    $root_path = \Drupal::service("r_case_study_global")->r_case_study_path();
    $query_file = \Drupal::database()->select('case_study_proposals_file');

    $query_file->fields('case_study_proposals_file');
    $query_file->condition('proposal_id', $proposal_id);
    $query_file->condition('filetype', 'R');
    $result = $query_file->execute();
    $case_study_rawdata_files = $result->fetchObject();
    $query_pro = \Drupal::database()->select('case_study_proposal');
    $query_pro->fields('case_study_proposal');
    $query_pro->condition('id', $proposal_id);
    $result_pro = $query_pro->execute();
    $case_study = $result_pro->fetchObject();
    $directory_name = $case_study->directory_name . '/';
    $rawdata_file = $case_study_rawdata_files->filename;
    //var_dump($directory_name);die;
    ob_clean();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: " . $case_study_rawdata_files->filemime);
    header('Content-disposition: attachment; filename="' . $rawdata_file . '"');
    header("Content-Length: " . filesize($root_path . $directory_name . $rawdata_file));
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");
    header("Pragma: no-cache");
    readfile($root_path . $directory_name . $rawdata_file);
    /*ob_end_flush();
		ob_clean();*/
    return;
  }

  public function _list_case_study_certificates() {
    $user = \Drupal::currentUser();
    $query_id = \Drupal::database()->query("SELECT id FROM case_study_proposal WHERE approval_status=3 AND uid= :uid", [
      ':uid' => $user->uid
      ]);
    $exist_id = $query_id->fetchObject();
    if ($exist_id) {
      if ($exist_id->id) {
        if ($exist_id->id < 3) {
          \Drupal::messenger()->addMessage('<strong>You need to propose a <a href="https://r.fossee.in/case-study-project/proposal">Case Study Project</a></strong>. If you have already proposed then your Case-Study is under reviewing process', 'status');
          return '';
        } //$exist_id->id < 3
        else {
          $search_rows = [];
          global $output;
          $output = '';
          $query3 = \Drupal::database()->query("SELECT id,project_title,contributor_name FROM case_study_proposal WHERE approval_status=3 AND uid= :uid", [
            ':uid' => $user->uid
            ]);
          while ($search_data3 = $query3->fetchObject()) {
            if ($search_data3->id) {
              $search_rows[] = [
                $search_data3->project_title,
                $search_data3->contributor_name,
                l('Download Certificate', 'case-study-project/certificates/generate-pdf/' . $search_data3->id),
              ];
            } //$search_data3->id
          } //$search_data3 = $query3->fetchObject()
          if ($search_rows) {
            $search_header = [
              'Project Title',
              'Contributor Name',
              'Download Certificates',
            ];
            $output = theme('table', [
              'header' => $search_header,
              'rows' => $search_rows,
            ]);
            return $output;
          } //$search_rows
          else {
            echo ("Error");
            return '';
          }
        }
      }
    } //$exist_id->id
    else {
      \Drupal::messenger()->addMessage('<strong>You need to propose a <a href="https://r.fossee.in/case-study-project/proposal">Case Study Project</a></strong>. If you have already proposed then your Case-Study is under reviewing process', 'status');
      $page_content = "<span style='color:red;'> No certificate available </span>";
      return $page_content;
    }
  }

  public function verify_certificates($qr_code = 0) {
    $qr_code = arg(3);
    $page_content = "";
    if ($qr_code) {
      $page_content = verify_qrcode_fromdb($qr_code);
    } //$qr_code
    else {
      $verify_certificates_form = drupal_get_form("verify_certificates_form");
      $page_content = drupal_render($verify_certificates_form);
    }
    return $page_content;
  }

}
