<?php

/**
 * Created by PhpStorm.
 * User: Pavithra
 * Date: 2/12/15
 * Time: 8:24 PM
 */

require_once(dirname(__FILE__) . "/../../../CommonResources/Base/BaseController.php");

class TrackManager extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = "TrackManager";
        require(dirname(__FILE__) . '/../config/privileges.php');
        $this->privileges = $privilege;
    }

    private function index($page)
    {
        require(dirname(__FILE__) . '/../utils/ViewUtils.php');
        $sidebarData['controllerName'] = $this->controllerName;
        $sidebarData['links'] = $this->setSidebarLinks();
        if (!file_exists(APPPATH . 'views/pages/TrackManager/' . $page . '.php')) {
            show_404();
        }
        $sidebarData['loadableComponents'] = $this->access_model->getLoadableDashboardComponents($this->privileges['Page']);
        $this->data['navbarItem'] = pageNavbarItem($page);
        $this->load->view('templates/header');
        $this->load->view('templates/navbar', $sidebarData);
        //$this->load->view('templates/sidebar');
        $this->load->view('pages/TrackManager/' . $page, $this->data);
        $this->load->view('templates/footer');
    }

    private function setSidebarLinks()
    {

    }

    private function getPaperInfo($paper_id)
    {
        $this->load->model('paper_model'); //paper
        $this->load->model('subject_model'); //subject
        $this->load->model('track_model'); //track
        $this->load->model('event_model'); //event
        $this->load->model('submission_model');

        $this->data['paperDetails'] = $this->paper_model->getPaperDetails($paper_id);

        if(isset($this->data['paperDetails']))
            $this->data['subjectDetails'] = $this->subject_model->getSubjectDetails($this->data['paperDetails']->paper_subject_id);

        if(isset($this->data['subjectDetails']))
            $this->data['trackDetails'] = $this->track_model->getTrackDetails($this->data['subjectDetails']->subject_track_id);

        if(isset($this->data['trackDetails']))
            $this->data['eventDetails'] = $this->event_model->getEventDetails($this->data['trackDetails']->track_event_id);
        $this->data['submissions'] = $this->submission_model->getSubmissionsByAttribute('submission_paper_id', $paper_id);

    }

    public function home()
    {
        if(!$this->checkAccess("home"))
            return;
        $this->load->library('form_validation');

        $this->form_validation->set_rules('searchValue', 'Search value', 'required');

        if ($this->form_validation->run()) {
            $this->load->helper('url');

            $search_by = $this->input->post('searchBy');
            $search_value = $this->input->post('searchValue');

            switch ($search_by) {
                case 'MemberID':
                    if (isset($search_value))
                        redirect('/TrackManager/markAuthorAttendance/' . $search_value);
                    break;

                case 'PaperID':
                    if (isset($search_value))
                        redirect('/TrackManager/markPaperAttendance/' . $search_value);
                    break;

                case 'MemberName':
                    $this->getMatchingMembers_AJAX($search_value);
                    return;
            }
        }
    }

    private function getMatchingMembers_AJAX($member_name)
    {
        $this->load->model('member_model');

        $matchingRecords = $this->member_model->getMatchingMembers($member_name);

        echo json_encode($matchingRecords);
    }

    public function markAuthorAttendance($member_id = null)
    {
        if(!$this->checkAccess("markAuthorAttendance"))
            return;
        $this->home();
        $page = "markAuthorAttendance";

        if ($member_id) {
            $this->load->model('paper_status_model');
            $this->load->model('attendance_model');
            $this->load->model('certificate_model');
            $this->load->model('submission_model');
            $this->load->model('certificate_model');
            $this->load->model('member_model');
            $this->load->model('discount_model');
            $this->load->model('payment_model');

            $this->data['memberId'] = true;
            $this->data['memberDetails'] = $this->member_model->getMemberInfo($member_id);
            $this->data['papers'] = $this->paper_status_model->getMemberAcceptedPapers($member_id, EVENT_ID);

            $this->data['registrationCat'] = $this->member_model->getMemberCategory($member_id);
            //$papers = $this->paper_status_model->getMemberAcceptedPapers($member_id);

            if(!isset($this->data['registrationCat']))
                $this->data['memberId'] = false;

            $this->data['discounts'] = $this->discount_model->getMemberEligibleDiscounts($member_id, $this->data['papers']);

            if($this->discount_model->error != null)
                die($this->discount_model->error);

            if(isset($this->data['registrationCat']) && isset($this->data['papers']))
                $this->data['papersInfo'] = $this->payment_model->calculatePayables(
                    $member_id,
                    DEFAULT_CURRENCY,
                    $this->data['registrationCat'],
                    $this->data['papers'],
                    date("Y-m-d"),
                    EVENT_ID
                );

            foreach ($this->data['papers'] as $paper) {
                $this->data['attendance'][$paper->paper_id] = $this->attendance_model->getAttendanceRecord($paper->submission_id);
                $this->data['certificate'][$paper->paper_id] = $this->certificate_model->getCertificateRecord($paper->submission_id);
            }
        } else {
            if($member_id == null)
                $this->data['memberId'] = null;
            else
                $this->data['memberId'] = false;
        }

        $this->index($page);

    }

    public function markPaperAttendance($paper_id = null)
    {
        if(!$this->checkAccess("markPaperAttendance"))
            return;
        $page = "markPaperAttendance";

        $this->home();

        if (isset($paper_id) && $paper_id) {
            $this->load->model('paper_status_model');
            $this->load->model('payment_model');
            $this->getPaperInfo($paper_id);
            $this->data['PaperRegistered'] = $this->payment_model->isPaperRegistered($paper_id);
            $this->data['members'] = $this->paper_status_model->getTrackMemberInfo($paper_id);
        } else {
            $this->data['paperId'] = false;
        }

        $this->index($page);
    }
}