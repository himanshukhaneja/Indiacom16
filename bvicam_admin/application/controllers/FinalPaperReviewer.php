<?php

require_once(dirname(__FILE__) . "/../../../CommonResources/Base/BaseController.php");

class FinalPaperReviewer extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        //$this -> load -> model('convener_model');
        $this -> load -> model('paper_model');//paper
        $this -> load -> model('subject_model');//subject
        $this -> load -> model('track_model');//track
        $this -> load -> model('event_model');//event
        $this -> load -> model('paper_model');//paper_version
        $this -> load -> model('paper_version_model');
        $this -> load -> model('submission_model');
        $this -> load -> model('paper_version_review_model');
        $this -> load -> model('reviewer_model');
        $this -> load -> model('review_result_model');
        $this->load->helper(array('form', 'url'));
        $this->controllerName = "FinalPaperReviewer";
        require(dirname(__FILE__).'/../config/privileges.php');
        $this->privileges = $privilege;
    }

    private function index($page)
    {
        require(dirname(__FILE__).'/../utils/ViewUtils.php');
        $sidebarData['controllerName'] = $this->controllerName;
        $sidebarData['links'] = $this->setSidebarLinks();
        if ( ! file_exists(APPPATH.'views/pages/'.$page.'.php'))
        {
            show_404();
        }

        $sidebarData['loadableComponents'] = $this->access_model->getLoadableDashboardComponents($this->privileges['Page']);
        $this->data['navbarItem'] = pageNavbarItem($page);
        $this->load->view('templates/header', $this->data);
        $this->load->view('templates/navbar', $sidebarData);
        $this->load->view('pages/'.$page, $this->data);
        $this->load->view('templates/footer');
    }

    private function setSidebarLinks()
    {

    }

    public function load()
    {
        if($this->checkAccess("loadAllPapers", false))
        {
            $this->loadAllPapers();
        }
        else if($this->checkAccess("loadTrackPapers", false))
        {
            $this->loadTrackPapers();
        }
        else
            $this->loadUnauthorisedAccessPage();
    }

    private function loadAllPapers()
    {
        $page = "ConvenerDashboardHome";
        $this->load->model('event_model');
        $this->load->model('track_model');
        $this->load->model('review_result_model');

        $this->data['events'] = $this->event_model->getAllActiveEvents();
        $this->data['reviewResultTypes'] = $this->review_result_model->getAllReviewResultTypeNames();
        foreach($this->data['events'] as $event)
        {
            $this->data['tracks'][$event->event_id] = $this->track_model->getAllTracks($event->event_id);
            foreach($this->data['tracks'][$event->event_id] as $track)
            {
                $this->data['no_reviewer_papers'][$track->track_id] = $this->paper_version_model->getNoReviewerPapers(null, $track->track_id);
                $this->data['reviewed_papers'][$track->track_id] = $this->paper_version_model->getReviewerReviewedPapers(null, $track->track_id);
                $this->data['not_reviewed_papers'][$track->track_id] = $this->paper_version_model->getNotReviewedPapers(null, $track->track_id);
                $this->data['convener_reviewed_papers'][$track->track_id] = $this->paper_version_model->getConvenerReviewedPapers(null, $track->track_id);
            }
        }

        $this->index($page);
    }

    private function loadTrackPapers()
    {
        $page = "ConvenerDashboardHome";
        $this->load->model('event_model');

        $this->data['events'] = $this->event_model->getAllActiveEvents();
        $tracks = $this->track_model->getTracksByCoConvener($_SESSION[APPID]['user_id']);
        if(count($tracks) == 0)
            die("No track assigned");

        foreach($tracks as $track)
        {
            $this->data['tracks'][$track->track_event_id][] = $track;
            $this->data['no_reviewer_papers'][$track->track_id] = $this->paper_version_model->getNoReviewerPapers(null, $track->track_id);
            $this->data['reviewed_papers'][$track->track_id] = $this->paper_version_model->getReviewerReviewedPapers(null, $track->track_id);
            $this->data['not_reviewed_papers'][$track->track_id] = $this->paper_version_model->getNotReviewedPapers(null, $track->track_id);
            $this->data['convener_reviewed_papers'][$track->track_id] = $this->paper_version_model->getConvenerReviewedPapers(null, $track->track_id);
        }
        $this->index($page);
    }

    private function sendMail($email_id, $message, $attachments = array())
    {
        $this->load->library('email');

        $this->email->from('conference@bvicam.ac.in', 'Indiacom');
        $this->email->to($email_id);
        $this->email->reply_to("conference@bvicam.ac.in");
        $this->email->subject('Indiacom Paper Review');
        $this->email->message($message);
        foreach($attachments as $attachment)
        {
            $this->email->attach($attachment);
        }

        if($this->email->send())
            return true;

        return false;
    }

    private function uploadComments($fileElem,$eventId,$paper_version_id)
    {
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/FileNameUtil.php");
        $config['upload_path'] = SERVER_ROOT . UPLOAD_PATH . $eventId . "/" . CONVENER_REVIEW_FOLDER;
        $config['allowed_types'] = 'pdf|doc|docx';
        $config['file_name'] = FileNameUtil::makeConvenerReviewCommentsFilename($paper_version_id);
        $config['overwrite'] = true;

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload($fileElem))
        {
            return false;
        }
        $uploadData = $this->upload->data();

        return $uploadData['file_ext'];
    }

    public function setReviewerAssigned($paper_version_id, $value)
    {
        if(!$this->checkAccess("setReviewerAssigned"))
            return;
        $update_data = array('paper_version_is_reviewer_assigned'   =>  $value);

        if($this -> paper_version_model -> setReviewerAssigned($update_data, $paper_version_id))
            $this -> data['message'] = "success";
        else
            $this -> data['error1'] = "Sorry, there is some problem. Try again later";
    }

    public function paperInfo($paper_version_id)
    {
        if(!$this->checkAccess("paperInfo"))
            return;
        $this->load->model('review_stage_model');
        $page = 'paperInfo';
        $this->data['paperVersionDetails'] = $this->paper_version_model->getPaperVersionDetails($paper_version_id);
        //TODO: confirm usage of below condition
        if($this->data['paperVersionDetails'] == null)
        {
            $this->load->view('pages/unauthorizedAccess');
            return;
        }
        $this->data['paperDetails'] = $this->paper_model->getPaperDetails($this->data['paperVersionDetails']->paper_id);
        $this->data['subjectDetails'] = $this->subject_model->getSubjectDetails($this->data['paperDetails']->paper_subject_id);
        $this->data['trackDetails'] = $this->track_model->getTrackDetails($this->data['subjectDetails']->subject_track_id);
        $this->data['eventDetails'] = $this->event_model->getEventDetails($this->data['trackDetails']->track_event_id);
        $this->data['submissions'] = $this->submission_model->getSubmissionsByAttribute('submission_paper_id', $this->data['paperVersionDetails']->paper_id);
        $this->load->library('form_validation');
        $this->form_validation->set_rules('event', 'Event','');

        if($this->input->post('FormReviewSubmission'))
        {
            $this->form_validation->set_rules('comments', 'Comments', 'required');
            $this->form_validation->set_rules('review_result', 'Review Result', 'required');
            if($this->form_validation->run())
            {
                if(($doc_path = $comments_url = $this->uploadComments('comments',$this->data['eventDetails']->event_id,$paper_version_id)) == false)
                {
                    $this->data['uploadError'] = $this->upload->display_errors();
                }
                /*else
                {
                    $versionDetails = array(
                        "paper_version_comments_path" => $doc_path
                    );
                    $this->paper_version_model->sendConvenerReview($versionDetails, $paper_version_id);
                }*/
                else
                {
                    date_default_timezone_set('Asia/Kolkata');
                    $update_data = array(
                        'paper_version_review_result_id' => $this->input->post('review_result'),
                        'paper_version_review' => $this->input->post('comments'),
                        'paper_version_review_date' => date("Y/m/d H:i:s"),
                        'paper_version_comments_path' => $doc_path,
                        'paper_version_is_reviewed_convener' => 1
                    );

                    if($this->paper_version_model->sendConvenerReview($update_data, $paper_version_id))
                    {
                        $this->load->model('submission_model');
                        $this->load->model('member_model');
                        $this->load->model('paper_model');

                        $member_info = $this->member_model->getMemberInfo($this->data['paperDetails']->paper_contact_author_id);
                        $email_id = $member_info['member_email'];
                        $message =  $this->getReviewMailMessage(
                            $update_data['paper_version_review_result_id'],
                            array(
                                "member_name" => $member_info['member_salutation'] . " " . $member_info['member_name'],
                                "paper_title" => $this->data['paperDetails']->paper_title,
                                "paper_code" => $this->data['paperDetails']->paper_code,
                                "comments" => $this->input->post('comments')
                            )
                        );
                        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/FileNameUtil.php");
                        $this->data['paperVersionDetails'] = $this->paper_version_model->getPaperVersionDetails($paper_version_id);
                        if($message != null && $this->sendMail($email_id, $message, array(SERVER_ROOT.UPLOAD_PATH."{$this->data['eventDetails']->event_id}/".PAPER_FOLDER.FileNameUtil::makePaperVersionFileName($this->data['paperVersionDetails']->paper_id, $this->data['paperVersionDetails']->paper_version_number,$doc_path))))
                            $this->data['message'] = "success";
                        else
                            $this->data['error2'] = "Sorry, there is some problem. Try again later";
                    }
                    else
                        $this->data['error2'] = "Sorry, there is some problem. Try again later";
                }
            }
        }
        else if(($this->input->post('Form1')))
        {
            if($this->form_validation->run() && is_array($this->input->post('reviewers')) && is_array($this->input->post('reviewStages')))
            {
                $reviewStages = $this->input->post('reviewStages');
                foreach($this->input->post('reviewers') as $index=>$reviewer_id)
                {
                    $paper_version_review_record = array(
                        'paper_version_id' => $paper_version_id,
                        'paper_version_reviewer_id' => $reviewer_id,
                        'paper_version_review_stage' => $reviewStages[$index]
                    );

                    if($this->paper_version_review_model->addPaperVersionReviewRecord($paper_version_review_record))
                        $this->data['message'] = "success";
                    else
                        $this->data['error1'] = "Sorry, there is some problem. Try again later";
                }

                $this->setReviewerAssigned($paper_version_id, 1);
            }
        }
        else if(($this->input->post('Form3')))
        {
            if($this->form_validation->run())
            {
                if($this->paper_version_review_model->removePaperVersionReviewer($this->input->post('Form3')))
                    $this->data['message'] = "Success";
                else
                    $this->data['error3'] = "Sorry, there is some problem. Try again later";
            }
        }

        $this->data['review_results'] = $this->review_result_model->getAllReviewResults();
        $this->data['allReviewers'] = $this->reviewer_model->getAllReviewers();
        $this->data['reviewStages'] = $this->review_stage_model->getAllReviewStages();
        $totalReviewStages = 0;

        $reviewerNames = array();
        foreach($this->data['allReviewers'] as $reviewer)
        {
            $reviewerNames[$reviewer->user_id] = $reviewer->user_name;
        }
        $reviewStageDetails = array();
        foreach($this->data['reviewStages'] as $reviewStage)
        {
            $reviewStageDetails[$reviewStage->review_stage_id] = $reviewStage;
            $totalReviewStages++;
        }

        $this->data['totalReviewStages'] = $totalReviewStages;
        $this->data['reviewerNames'] = $reviewerNames;
        $this->data['reviewStageDetails'] = $reviewStageDetails;
        $this->data['reviews'] = $this->paper_version_review_model->getPaperVersionAllReviews($paper_version_id);
        if(empty($this->data['reviews']))
            $this->setReviewerAssigned($paper_version_id, 0);
        $this->index($page);
    }

    private function getReviewMailMessage($reviewResultId, $messageData = array())
    {
        $this->load->model('review_result_model');
        $reviewResultDetails = $this->review_result_model->getReviewResultDetails($reviewResultId);
        switch($reviewResultDetails->review_result_acronym)
        {
            case "REJ_IR":
                return $this->load->view('pages/Email/Review_REJ_IR', $messageData, true);
            case "REV_IR":
                return $this->load->view('pages/Email/Review_REV_IR', $messageData, true);
            case "SENT_DR":
                return $this->load->view('pages/Email/Review_SENT_DR', $messageData, true);
            case "REJ_DR":
                return $this->load->view('pages/Email/Review_REJ_DR', $messageData, true);
            case "MIN_DR":
                return $this->load->view('pages/Email/Review_MIN_DR', $messageData, true);
            case "MAJ_DR":
                return $this->load->view('pages/Email/Review_MAJ_DR', $messageData, true);
            case "ACC_DR":
                return $this->load->view('pages/Email/Review_ACC_DR', $messageData, true);
            default:
                break;
        }
        return null;
    }

    /*private function downloadPaperVersionDocuments($paperVersionId, $eventId, $documentPathFieldName)
    {
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/DownloadUtil.php");
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/FileNameUtil.php");
        $this->load->model('paper_version_model');
        $versionInfo = $this->paper_version_model->getPaperVersionDetails($paperVersionId);
        $fileName = FileNameUtil::makePaperVersionFileName(
            $versionInfo->paper_id,
            $versionInfo->paper_version_number,
            $versionInfo->$documentPathFieldName
        );
        DownloadUtil::downloadFile(
            SERVER_ROOT.UPLOAD_PATH.$eventId."/".PAPER_FOLDER.$fileName,
            $fileName);
    }*/

    public function downloadPaperVersion($paperVersionId, $eventId)
    {
        if(!$this->checkAccess("downloadPaperVersion"))
            return;
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/DownloadUtil.php");
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/FileNameUtil.php");
        $this->load->model('paper_version_model');
        $versionInfo = $this->paper_version_model->getPaperVersionDetails($paperVersionId);
        $fileName = FileNameUtil::makePaperVersionFilename(
            $versionInfo->paper_id,
            $versionInfo->paper_version_number,
            $versionInfo->paper_version_document_path
        );
        DownloadUtil::downloadFile(
            SERVER_ROOT.UPLOAD_PATH.$eventId."/".PAPER_FOLDER.$fileName,
            $fileName);
        //$this->downloadPaperVersionDocuments($paperVersionId, $eventId, "paper_version_document_path");
    }

    public function downloadComplianceReport($paperVersionId, $eventId)
    {
        if(!$this->checkAccess("downloadComplianceReport"))
            return;
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/DownloadUtil.php");
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/FileNameUtil.php");
        $this->load->model('paper_version_model');
        $versionInfo = $this->paper_version_model->getPaperVersionDetails($paperVersionId);
        $fileName = FileNameUtil::makeComplianceReportFilename(
            $versionInfo->paper_id,
            $versionInfo->paper_version_number,
            $versionInfo->paper_version_compliance_report_path
        );
        DownloadUtil::downloadFile(
            SERVER_ROOT.UPLOAD_PATH.$eventId."/".COMPLIANCE_REPORT_FOLDER.$fileName,
            $fileName);
        //$this->downloadPaperVersionDocuments($paperVersionId, $eventId, "paper_version_compliance_report_path");
    }

    public function downloadConvenerReviewComments($paperVersionId, $eventId)
    {
        if(!$this->checkAccess("downloadConvenerReviewComments"))
            return;
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/DownloadUtil.php");
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/FileNameUtil.php");
        $this->load->model('paper_version_model');
        $versionInfo = $this->paper_version_model->getPaperVersionDetails($paperVersionId);
        $fileName = FileNameUtil::makeConvenerReviewCommentsFilename(
            $paperVersionId,
            $versionInfo->paper_version_comments_path
        );
        DownloadUtil::downloadFile(
            SERVER_ROOT.UPLOAD_PATH.$eventId."/".CONVENER_REVIEW_FOLDER.$fileName,
            $fileName);
        //$this->downloadPaperVersionDocuments($paperVersionId, $eventId, "paper_version_comments_path");
    }

    public function downloadReviewerComments($paperVersionReviewId, $eventId)
    {
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/DownloadUtil.php");
        require_once(dirname(__FILE__) . "/../../../CommonResources/Utils/FileNameUtil.php");
        $this->load->model('paper_version_review_model');
        $versionReviewInfo = $this->paper_version_review_model->getPaperVersionReviewerReview($paperVersionReviewId);
        $fileName = FileNameUtil::makeReviewerReviewCommentsFilename(
            $paperVersionReviewId,
            $versionReviewInfo->paper_version_review_comments_file_path
        );
        DownloadUtil::downloadFile(
            SERVER_ROOT.UPLOAD_PATH.$eventId."/".REVIEWER_REVIEW_FOLDER.$fileName,
            $fileName
        );
    }
}
?>