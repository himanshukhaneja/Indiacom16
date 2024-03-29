<?php
/**
 * Created by PhpStorm.
 * User: Pavithra
 * Date: 2/18/15
 * Time: 2:36 PM
 */

require_once(dirname(__FILE__) . "/../../../CommonResources/Base/BaseController.php");

class DeliverablesManager extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = "DeliverablesManager";
        require(dirname(__FILE__) . '/../config/privileges.php');
        $this->privileges = $privilege;
    }

    private function index($page)
    {
        require(dirname(__FILE__) . '/../utils/ViewUtils.php');
        $sidebarData['controllerName'] = $this->controllerName;
        $sidebarData['links'] = $this->setSidebarLinks();
        if (!file_exists(APPPATH . 'views/pages/DeliverablesManager/' . $page . '.php')) {
            show_404();
        }
        $sidebarData['loadableComponents'] = $this->access_model->getLoadableDashboardComponents($this->privileges['Page']);
        $this->data['navbarItem'] = pageNavbarItem($page);
        $this->load->view('templates/header');
        $this->load->view('templates/navbar', $sidebarData);
        $this->load->view('pages/DeliverablesManager/' . $page, $this->data);
        $this->load->view('templates/footer');
    }

    private function setSidebarLinks()
    {

    }

    public function assignDeliverables_AJAX()
    {
        if(!$this->checkAccess("assignDeliverables_AJAX"))
        {
            echo json_encode(false);
            return;
        }
        $this->load->model("deliverables_model");
        $this->load->model("submission_model");

        $member_id = $this->input->post('memberId');
        $submission_id = $this->input->post('submissionId');
        $deliverables_id = $this->input->post('deliverables_id');

        if($deliverables_id == "null")
            $deliverables_id = null;

        $is_deliverables_assigned = $this->input->post('isDeliverablesAssigned');

        if($submission_id != "")
            $member_id = null;
        else if($member_id != "")
            $submission_id = null;
        else
            echo "false";

        $deliverablesStatusRecord = array(
                'event_id' => EVENT_ID,
                'member_id' => $member_id,
                'deliverables_id' => $deliverables_id,
                'submission_id' => $submission_id,
                'status' => $is_deliverables_assigned
            );

        echo json_encode($this->deliverables_model->assignDeliverables($deliverablesStatusRecord));
    }

    //Get deliverable payments (br or pr) for memberId.
    private function getMemberDeliverablesPayments($memberId)
    {
        $this->load->model('payment_model');
        $this->load->model('payment_head_model');

        $brPayheadId = $this->payment_head_model->getPaymentHeadId("BR");
        $prPayheadId = $this->payment_head_model->getPaymentHeadId("PR");
        $comboPayheadId = $this->payment_head_model->getPaymentHeadId("COMBO");

        if($brPayheadId == null && $prPayheadId == null && $comboPayheadId == null)
        {
            return false;
        }

        $orig_payments[$brPayheadId] = $this->payment_model->getPayments($memberId, null, $brPayheadId);
        $orig_payments[$prPayheadId] = $this->payment_model->getPayments($memberId, null, $prPayheadId);
        $orig_payments[$comboPayheadId] = $this->payment_model->getPayments($memberId, null, $comboPayheadId);

        $payments_record = array();

        foreach($orig_payments as $payheadId => $payments_array)
        {
            foreach($payments_array as $index => $payments)
            {
                $payable_class_amount = $payments -> payable_class_amount;
                $waiveoff_amount = $payments -> waiveoff_amount;
                $discount_amount = $payments -> discount_type_amount ? ($payments -> discount_type_amount * $payable_class_amount) : 0;
                $pay_amount = $payable_class_amount - ($waiveoff_amount + $discount_amount );
                $paid_amount = $payments -> paid_amount;

                $pending_amount = $pay_amount - $paid_amount;

                if($pending_amount <= 0)
                   $payments_record[] = $payments;
            }
        }

        return $payments_record;
    }

    //show assign deliverables page for a member
    public function assignMemberDeliverables($member_id)
    {
        if(!$this->checkAccess("assignMemberDeliverables"))
            return;
        $page = "assignMemberDeliverables";

        $this -> load -> model('deliverables_model');

        $this -> data['deliverablesPayments'] = $this -> getMemberDeliverablesPayments($member_id);

        foreach($this -> data['deliverablesPayments'] as $payheadId => $payment)
        {
            //foreach($payments_array as $index => $payments)
            $this->data['deliverablesStatus'][] = $this -> deliverables_model -> getDeliverablesStatusRecord($payment -> payment_member_id, $payment -> payment_submission_id);
            //$this -> data['deliverablesStatus'][$payment -> submission_member_id][$payment -> payment_submission_id] = $this -> deliverables_model -> getDeliverablesStatusRecord($payment -> submission_member_id, $payment -> payment_submission_id);
        }
        $this -> index($page);
    }

    //show assign deliverables page containing all authors of a paper
    public function assignPaperDeliverables($paper_id)
    {
        if(!$this->checkAccess("assignPaperDeliverables"))
            return;
        $page = "assignPaperDeliverables";

        $this -> load -> model('submission_model');
        $this -> load -> model('deliverables_model');

        $paper_authors_array = $this->submission_model->getAllAuthorsOfPaper($paper_id);

        foreach ($paper_authors_array as $index => $author)
        {
            $member_id = $author->submission_member_id;

            $this -> data['deliverablesPayments'][$member_id] = $this -> getMemberDeliverablesPayments($member_id);

            foreach($this -> data['deliverablesPayments'][$member_id] as $payheadId => $payment)
            {
                $this->data['deliverablesStatus'][$member_id][] = $this -> deliverables_model -> getDeliverablesStatusRecord($payment -> payment_member_id, $payment -> payment_submission_id);
                //foreach($payments_array as $index => $payments)
                    //$this -> data['deliverablesStatus'][$payments -> submission_member_id][$payments -> payment_submission_id] = $this -> deliverables_model -> getDeliverablesStatusRecord($payments -> submission_member_id, $payments -> payment_submission_id);
            }
        }
        $this -> index($page);
    }
} 