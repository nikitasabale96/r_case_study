<?php

/**
 * @file
 * Contains \Drupal\r_case_study\Form\GeneratePdf.
 */

namespace Drupal\r_case_study\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class GeneratePdf extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'generate_pdf';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $mpath = drupal_get_path('module', 'r_case_study');
    //var_dump($mpath);die;
    require($mpath . '/pdf/fpdf/fpdf.php');
    require($mpath . '/pdf/phpqrcode/qrlib.php');
    $user = \Drupal::currentUser();
    $x = $user->uid;
    $proposal_id = arg(3);
    $query3 = \Drupal::database()->query("SELECT * FROM case_study_proposal WHERE approval_status=3 AND uid= :uid AND id=:proposal_id", [
      ':uid' => $user->uid,
      ':proposal_id' => $proposal_id,
    ]);
    $data3 = $query3->fetchObject();
    if ($data3) {
      if ($data3->uid != $x) {
        drupal_set_message('Certificate is not available', 'error');
        return;
      }
    }
    $gender = [
      'salutation' => 'Mr. /Ms.',
      'gender' => 'He/She',
    ];
    if ($data3->gender) {
      if ($data3->gender == 'M') {
        $gender = [
          'salutation' => 'Mr.',
          'gender' => 'He',
        ];
      } //$data3->gender == 'M'
      else {
        $gender = [
          'salutation' => 'Ms.',
          'gender' => 'She',
        ];
      }
    } //$data3->gender
    $pdf = new FPDF('L', 'mm', 'Letter');
    if (!$pdf) {
      echo "Error!";
    } //!$pdf
    $pdf->AddPage();
    $image_bg = $mpath . "/pdf/images/bg_cert.png";
    $pdf->Image($image_bg, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
    $pdf->SetMargins(18, 1, 18);
    $path = drupal_get_path('module', 'r_case_study');
    $pdf->Ln(15);
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'BI', 25);
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'BI', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(240, 20, 'This is to certify that', '0', '1', 'C');
    $pdf->Ln(-6);
    $pdf->SetFont('Arial', 'BI', 25);
    $pdf->SetTextColor(139, 69, 19);
    $pdf->Cell(240, 8, utf8_decode($data3->contributor_name), '0', '1', 'C');
    $pdf->Ln(0);
    $pdf->SetFont('Arial', 'I', 12);
    if (strtolower($data3->branch) != "others") {
      $pdf->SetTextColor(0, 0, 0);
      $pdf->MultiCell(240, 8, 'from ' . utf8_decode($data3->university) . ' has successfully', '0', 'C');
      $pdf->Ln(0);
      $pdf->Cell(240, 8, 'completed Internship under R Case-Study Project.', '0', '1', 'C');
      $pdf->Ln(0);
      $pdf->Cell(240, 8, 'He/she has created a Case-Study titled ', '0', '1', 'C');
      $pdf->Ln(0);
      $pdf->SetTextColor(139, 69, 19);
      $pdf->Cell(240, 8, utf8_decode($data3->project_title), '0', '1', 'C');
      $pdf->SetTextColor(0, 0, 0);
      $pdf->Ln(0);
      $pdf->Cell(240, 8, ' using R .The work done is available at', '0', '1', 'C');
      $pdf->Cell(240, 4, '', '0', '1', 'C');
      $pdf->SetX(120);
      $pdf->SetFont('', 'U');
      $pdf->SetTextColor(139, 69, 19);
      $pdf->write(0, 'https://r.fossee.in/', 'https://r.fossee.in/');
      $pdf->Ln(0);
    } //strtolower($data3->branch) != "others"
    else {
      $pdf->SetTextColor(0, 0, 0);
      $pdf->Cell(240, 8, 'from ' . $data3->university . ' has successfully', '0', '1', 'C');
      $pdf->Ln(0);
      $pdf->Cell(240, 8, 'completed Internship under R Case-Study Project', '0', '1', 'C');
      $pdf->Ln(0);
    }
    $proposal_get_id = 0;
    $UniqueString = "";
    $tempDir = $path . "/pdf/temp_prcode/";
    $query = \Drupal::database()->select('case_study_qr_code');
    $query->fields('case_study_qr_code');
    $query->condition('proposal_id', $proposal_id);
    $result = $query->execute();
    $data = $result->fetchObject();
    $DBString = $data->qr_code;
    $proposal_get_id = $data->proposal_id;
    if ($DBString == "" || $DBString == "null") {
      $UniqueString = generateRandomString();
      $query = "
				INSERT INTO case_study_qr_code
				(proposal_id,qr_code)
				VALUES
				(:proposal_id,:qr_code)
				";
      $args = [
        ":proposal_id" => $proposal_id,
        ":qr_code" => $UniqueString,
      ];
      $result = \Drupal::database()->query($query, $args, ['return' => Database::RETURN_INSERT_ID]);
    } //$DBString == "" || $DBString == "null"
    else {
      $UniqueString = $DBString;
    }
    $codeContents = "https://r.fossee.in/case-study-project/certificates/verify/" . $UniqueString;
    $fileName = 'generated_qrcode.png';
    $pngAbsoluteFilePath = $tempDir . $fileName;
    $urlRelativeFilePath = $path . "/pdf/temp_prcode/" . $fileName;
    QRcode::png($codeContents, $pngAbsoluteFilePath);
    /*$pdf->SetTextColor(0, 0, 0);
	$pdf->Ln(30);
	$pdf->SetX(198);
	$pdf->SetFont('', '');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY(-85);
	$pdf->SetX(200);
	$pdf->Ln(16);
	$pdf->Cell(240, 8, 'Prof. Kannan M. Moudgalya', 0, 1, 'R');
	$pdf->Ln(-2);
	$pdf->SetFont('Arial', '', 10);
	$pdf->Cell(240, 8, 'Principal Investigator - FOSSEE', 0, 1, 'R');
	$pdf->Ln(-2);
	$pdf->Cell(240, 8, ' Dept. of Chemical Engineering, IIT Bombay.', 0, 1, 'R');*/
    $pdf->Ln(30);
    $pdf->SetX(29);
    $pdf->SetY(-58);
    $sign1 = $path . "/pdf/images/sign1.png";
    $sign2 = $path . "/pdf/images/sign2.png";
    $pdf->Image($sign1, $pdf->GetX() + 10, $pdf->GetY() - 15, 80, 0);
    $pdf->Image($sign2, $pdf->GetX() + 170, $pdf->GetY() - 15, 80, 0);
    $pdf->SetX(29);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetY(-58);
    $pdf->Ln(8);
    $pdf->SetX(10);
    $pdf->Cell(0, 0, $UniqueString, 0, 0, 'C');
    $pdf->SetX(29);
    $pdf->SetY(-50);
    $image4 = $path . "/pdf/images/bottom_line.png";
    $pdf->SetY(-50);
    $pdf->SetX(80);
    $image3 = $path . "/pdf/images/moe.png";
    $image2 = $path . "/pdf/images/fossee.png";

    $pdf->Ln(8);
    $pdf->Image($image2, $pdf->GetX() + 15, $pdf->GetY() + 7, 40, 0);
    $pdf->Ln(6);
    $pdf->Image($pngAbsoluteFilePath, $pdf->GetX() + 106, $pdf->GetY() - 10, 25, 0);
    $pdf->Image($image3, $pdf->GetX() + 200, $pdf->GetY() + 6, 40, 0);
    $pdf->Image($image4, $pdf->GetX() + 60, $pdf->GetY() + 23, 120, 0);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(0, 0, 0);
    $filename = str_replace(' ', '-', $data3->contributor_name) . '-R-Case-Study-Certificate.pdf';
    $file = $path . '/pdf/temp_certificate/' . $proposal_id . '_' . $filename;
    $pdf->Output($file, 'F');
    ob_clean();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=" . $filename);
    header("Content-Length: " . filesize($file));
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");
    header("Pragma: no-cache");
    flush();
    $fp = fopen($file, "r");
    while (!feof($fp)) {
      echo fread($fp, filesize($file));
      flush();
    } //!feof($fp)
    ob_end_flush();
    ob_clean();
    fclose($fp);
    unlink($file);

    //drupal_goto('flowsheeting-project/certificate');
    return;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {

}
}
?>
