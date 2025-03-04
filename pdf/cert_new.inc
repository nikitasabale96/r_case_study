<?php
function generate_pdf()
{
	$mpath = drupal_get_path('module', 'r_case_study');
	//var_dump($mpath);die;
	require($mpath . '/pdf/fpdf/fpdf.php');
	require($mpath . '/pdf/phpqrcode/qrlib.php');
	$user = \Drupal::currentUser();
	$x                 = $user->uid;
	$proposal_id       = arg(3);
	$query3 = \Drupal::database()->query("SELECT * FROM case_study_proposal WHERE approval_status=3 AND uid= :uid AND id=:proposal_id", array(
		':uid' => $user->uid,
		':proposal_id'=>$proposal_id
	));
	$data3 = $query3->fetchObject();
	if($data3){
	if($data3->uid != $x){
		\Drupal::messenger()->addError('Certificate is not available');
		return;
	}
	}
	$gender            = array(
		'salutation' => 'Mr. /Ms.',
		'gender' => 'He/She'
	);
	if ($data3->gender) {
		if ($data3->gender == 'M') {
			$gender = array(
				'salutation' => 'Mr.',
				'gender' => 'He'
			);
		} //$data3->gender == 'M'
		else {
			$gender = array(
				'salutation' => 'Ms.',
				'gender' => 'She'
			);
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
	$path   = drupal_get_path('module', 'r_case_study');
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
		$pdf->MultiCell(240, 8, 'from ' . utf8_decode($data3->university) . ' has successfully', '0','C');
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
	$UniqueString    = "";
	$tempDir         = $path . "/pdf/temp_prcode/";
	$query           = \Drupal::database()->select('case_study_qr_code');
	$query->fields('case_study_qr_code');
	$query->condition('proposal_id', $proposal_id);
	$result          = $query->execute();
	$data            = $result->fetchObject();
	$DBString        = $data->qr_code;
	$proposal_get_id = $data->proposal_id;
	if ($DBString == "" || $DBString == "null") {
		$UniqueString = generateRandomString();
		$query        = "
				INSERT INTO case_study_qr_code
				(proposal_id,qr_code)
				VALUES
				(:proposal_id,:qr_code)
				";
		$args         = array(
			":proposal_id" => $proposal_id,
			":qr_code" => $UniqueString
		);
		$result       = \Drupal::database()->query($query, $args, $query);
	} //$DBString == "" || $DBString == "null"
	else {
		$UniqueString = $DBString;
	}
	$codeContents        = "https://r.fossee.in/case-study-project/certificates/verify/" . $UniqueString;
	$fileName            = 'generated_qrcode.png';
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
    $pdf->Image($sign1, $pdf->GetX()+10, $pdf->GetY() - 15, 80, 0);
    $pdf->Image($sign2, $pdf->GetX()+170, $pdf->GetY()  - 15, 80, 0);
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
	$pdf->Image($image2, $pdf->GetX() +15, $pdf->GetY() + 7, 40, 0);
	$pdf->Ln(6);
	$pdf->Image($pngAbsoluteFilePath, $pdf->GetX() + 106, $pdf->GetY() - 10, 25, 0);
	$pdf->Image($image3, $pdf->GetX() + 200, $pdf->GetY() +6, 40, 0);
	$pdf->Image($image4, $pdf->GetX() +60, $pdf->GetY() + 23, 120, 0);
	$pdf->SetFont('Arial', 'I', 8);
	$pdf->SetTextColor(0, 0, 0);
	$filename = str_replace(' ', '-', $data3->contributor_name) . '-R-Case-Study-Certificate.pdf';
	$file     = $path . '/pdf/temp_certificate/' . $proposal_id . '_' . $filename;
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
function generateRandomString($length = 5)
{
	$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString     = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	} //$i = 0; $i < $length; $i++
	return $randomString;
}
function generate_copyright_form_pdf()
{
	$mpath = drupal_get_path('module', 'r_case_study');
	require($mpath . '/pdf/fpdf/fpdf.php');
	$user = \Drupal::currentUser();
	$x           = $user->uid;
	$proposal_id = arg(3);
	$query2 = \Drupal::database()->query("SELECT id FROM case_study_proposal WHERE approval_status=3 AND uid= :uid", array(
		':uid' => $user->uid
	));
	$data2             = $query2->fetchObject();
	$query3 = \Drupal::database()->query("SELECT * FROM case_study_proposal WHERE approval_status=3 AND uid= :uid", array(
		':uid' => $user->uid
	));
	$data3       = $query3->fetchObject();
	$gender      = array(
		'salutation' => 'Mr. /Ms.',
		'gender' => 'He/She'
	);
	if ($data3->gender) {
		if ($data3->gender == 'M') {
			$gender = array(
				'salutation' => 'Mr.',
				'gender' => 'He'
			);
		} //$data3->gender == 'M'
		else {
			$gender = array(
				'salutation' => 'Ms.',
				'gender' => 'She'
			);
		}
	} //$data3->gender
	$pdf = new FPDF('P', 'mm', 'Letter');
	if (!$pdf) {
		echo "Error!";
	} //!$pdf
	$pdf->AddPage();
	$path = drupal_get_path('module', 'r_case_study');
	$pdf->SetFont('Arial', 'B', 25);
	$pdf->Ln(30);
	$pdf->Cell(200, 8, 'Copyright Transfer Form', 0, 1, 'C');
	$pdf->Ln(20);
	$pdf->SetFont('Arial', '', 12);
	$pdf->MultiCell(200, 8, 'I hereby transfer the copyrights of the R Case-Study Project for ' . $data2->project_title . ' to FOSSEE Project, IIT Bombay.', 0);
	$pdf->Ln(10);
	$pdf->MultiCell(200, 8, 'I  understand  that  the  FOSSEE  project  will  release  the  Case-Study  under  the Creative Commons (CC) license.');
	$pdf->SetX(75);
	$pdf->SetFont('', 'U');
	$pdf->SetTextColor(0, 0, 255);
	$pdf->SetFont('', '');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('', '');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY(-10);
	$pdf->SetX(209);
	$cur_date = date('jS F, Y');
	$pdf->SetY(140);
	$pdf->SetFont('', '');
	$pdf->Ln(0);
	$pdf->Cell(200, 0, ' Date: ' . $cur_date . '', 0, 1, 'L');
	$pdf->Cell(200, 20, ' Place: _________________', 0, 1, 'L');
	$pdf->SetY(140);
	$pdf->SetX(120);
	$pdf->Cell(180, 0, 'Signature: _________________', 0, 1, 'L');
	$pdf->SetY(144);
	$pdf->SetX(120);
	$pdf->multicell(140, 14, 'Name: ' . $gender['salutation'] . ' ' . $data3->full_name . '', 0, '');
	$filename = str_replace(' ', '-', $data3->full_name) . '-R-Case-Study-copyright-form.pdf';
	$file     = $path . '/pdf/temp_certificate/' . $proposal_id . '_' . $filename;
	$pdf->Output($file, 'F');
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=" . $filename);
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header("Content-Description: File Transfer");
	header("Content-Length: " . filesize($file));
	flush();
	$fp = fopen($file, "r");
	while (!feof($fp)) {
		echo fread($fp, 65536);
		flush();
	} //!feof($fp)
	fclose($fp);
	unlink($file);
	drupal_goto('Summer_Internship_Forms/forms');
	return;
}
function generate_undertaking_form_pdf()
{
	$mpath = drupal_get_path('module', 'textbook_companion');
	require($mpath . '/pdf/fpdf/fpdf.php');
	$user = \Drupal::currentUser();
	$x           = $user->uid;
	$proposal_id = arg(3);
	$query2 = \Drupal::database()->query("SELECT id FROM case_study_proposal WHERE approval_status=3 AND uid= :uid", array(
		':uid' => $user->uid
	));
	$data2             = $query2->fetchObject();
	$query3 = \Drupal::database()->query("SELECT * FROM case_study_proposal WHERE approval_status=3 AND uid= :uid", array(
		':uid' => $user->uid
	));
	$data3       = $query3->fetchObject();
	$gender      = array(
		'salutation' => 'Mr. /Ms.',
		'gender' => 'He/She'
	);
	if ($data3->gender) {
		if ($data3->gender == 'M') {
			$gender = array(
				'salutation' => 'Mr.',
				'gender' => 'He'
			);
		} //$data3->gender == 'M'
		else {
			$gender = array(
				'salutation' => 'Ms.',
				'gender' => 'She'
			);
		}
	} //$data3->gender
	$pdf = new FPDF('P', 'mm', 'Letter');
	if (!$pdf) {
		echo "Error!";
	} //!$pdf
	$pdf->AddPage();
	$path = drupal_get_path('module', 'r_case_study');
	$pdf->SetFont('Arial', 'B', 25);
	$pdf->Ln(30);
	$pdf->Cell(200, 8, 'Undertaking Form', 0, 1, 'C');
	$pdf->Ln(0);
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->Cell(200, 8, '(To be signed by college teacher)', 0, 1, 'C');
	$pdf->Ln(20);
	$pdf->SetFont('Arial', '', 12);
	$pdf->MultiCell(200, 8, 'I hereby certify that all the codes written by ' . $gender['salutation'] . ' ' . $data3->full_name . ' under the R Textbook Companion Project for the book ' . $data2->book . ' ( Author: ' . $data2->author . ', Edition: ' . $data2->edition . ', Publisher: ' . $data2->publisher . ', Year: ' . $data2->year . ') are correctly reproducing the results given in the aforementioned book.', 0);
	$pdf->Ln(10);
	$pdf->MultiCell(200, 8, 'I understand that the R Textbook Companion created is a part of FOSSEE project, IIT Bombay, and is sponsored by the National Mission on Education through Information and Communication Technology (NMEICT), under MHRD, Govt. of India. The project requires that the textbook companion is made available for public access as an open source document. Hence I undertake that this R Textbook Companion can be made public along with the information that I have certified all the codes as giving the correct answer.');
	$pdf->SetX(75);
	$pdf->SetFont('', 'U');
	$pdf->SetTextColor(0, 0, 255);
	$pdf->SetFont('', '');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('', '');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetY(-10);
	$pdf->SetX(209);
	$cur_date = date('jS F, Y');
	$pdf->SetY(180);
	$pdf->SetFont('', '');
	$pdf->Ln(0);
	$pdf->Cell(200, 0, ' Date: ' . $cur_date . '', 0, 1, 'L');
	$pdf->Cell(200, 20, ' Place: _________________', 0, 1, 'L');
	$pdf->SetY(180);
	$pdf->SetX(120);
	$pdf->Cell(140, 0, 'Signature: _________________', 0, 1, 'L');
	$pdf->SetX(120);
	$pdf->multicell(140, 14, 'Name: ' . '____________________', 0, '');
	$pdf->SetX(120);
	$pdf->multicell(140, 14, 'Designation: ' . '______________', 0, '');
	$filename = str_replace(' ', '-', $data3->full_name) . '-R-Textbook-Companion-undertaking-form.pdf';
	$file     = $path . '/pdf/temp_certificate/' . $proposal_id . '_' . $filename;
	$pdf->Output($file, 'F');
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=" . $filename);
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header("Content-Description: File Transfer");
	header("Content-Length: " . filesize($file));
	flush();
	$fp = fopen($file, "r");
	while (!feof($fp)) {
		echo fread($fp, 65536);
		flush();
	} //!feof($fp)
	fclose($fp);
	unlink($file);
	drupal_goto('Summer_Internship_Forms/forms');
	return;
}
