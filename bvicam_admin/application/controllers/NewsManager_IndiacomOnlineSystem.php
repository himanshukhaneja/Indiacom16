<?php
/**
 * Created by PhpStorm.
 * User: Saurav
 * Date: 10/14/14
 * Time: 9:27 PM
 */

require("NewsManager.php");

class NewsManager_IndiacomOnlineSystem extends NewsManager
{
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = "NewsManager_IndiacomOnlineSystem";
        require(dirname(__FILE__) . '/../config/privileges.php');
        $this->privileges = $privilege;
    }

    public function addNews()
    {
        if(!$this->checkAccess("addNews"))
            return;
        $folder = "NewsManager/";
        if($this->addNewsSubmitHandle())
        {
            $this->load->helper('url');
            redirect('/NewsManager/load');
        }
        $this->load->model('event_model');
        $this->data['events'] = $this->event_model->getAllActiveEvents();
        $this->load->view('templates/header', $this->data);
        $this->load->view("pages/{$folder}addNews");
        $this->load->view("pages/{$folder}addNewsIndiacomOnlineSystem", $this->data);
        $this->load->view('templates/footer');
    }

    public function disableNews($newsId)
    {
        if(!$this->checkAccess("disableNews"))
            return;
        $this->load->model('indiacom_news_model');
        $this->load->helper('url');
        parent::disableNews($newsId);
        $this->indiacom_news_model->disableNews($newsId);
        redirect("NewsManager/load");
    }

    public function enableNews($newsId)
    {
        if(!$this->checkAccess("enableNews"))
            return;
        $this->load->model('indiacom_news_model');
        $this->load->helper('url');
        parent::enableNews($newsId);
        $this->indiacom_news_model->enableNews($newsId);
        redirect("NewsManager/load");
    }

    public function deleteNews($newsId)
    {
        if(!$this->checkAccess("deleteNews"))
            return;
        $this->load->model('indiacom_news_model');
        $this->load->helper('url');
        try{
            $this->indiacom_news_model->deleteNews($newsId);
        }
        catch(DeleteException $ex)
        {
            echo $ex->sqlError();
        }
        parent::deleteNews($newsId);
        redirect("NewsManager/load");
    }

    protected function addNewsSubmitHandle()
    {
        $this->load->model('application_model');
        $this->load->model('indiacom_news_model');
        $this->load->database();
        $this->db->trans_begin();
        $appId = $this->application_model->getApplicationId("Indiacom Online System");
        $newsId = parent::addNewsSubmitHandle($appId);

        if(!isset($this->form_validation))
            $this->load->library('form_validation');
        $this->form_validation->set_rules('event', "Event", 'required');

        if($this->form_validation->run() && $newsId)
        {
            $newsDetails = array(
                "news_id" => $newsId,
                "news_event_id" => $this->input->post('event')
            );
            if($this->input->post('stickyDate') != '')
                $newsDetails['news_sticky_date'] = $this->input->post('stickyDate');

            $this->indiacom_news_model->addNews($newsDetails);
            $attachments =  $_FILES['attachments'];
            $attachmentNames = $this->input->post('attachmentNames');
            if(!empty($attachmentNames) && ($paths = $this->uploadAttachments($newsId, $attachments)) == false)
            {
                $this->db->trans_rollback();
                return false;
            }
            foreach($paths as $key=>$path)
            {
                $attachmentDetails = array(
                    "attachment_name" => $attachmentNames[$key],
                    "attachment_url" => $path
                );
                $this->indiacom_news_model->addAttachment($newsId, $attachmentDetails);
            }
            $this->db->trans_commit();
            return true;
        }
        $this->db->trans_rollback();
        return false;
    }

    private function uploadAttachments($newsId, $attachments)
    {
        $config['upload_path'] = SERVER_ROOT . UPLOAD_PATH . NEWS_FOLDER . NEWS_ATTACHMENT_FOLDER;
        $config['allowed_types'] = '*';
        $config['overwrite'] = true;
        $this->load->library('upload');
        $paths = array();
        foreach($attachments['name'] as $key=>$name)
        {
            $_FILES['attachments[]']['name'] = $attachments['name'][$key];
            $_FILES['attachments[]']['type'] = $attachments['type'][$key];
            $_FILES['attachments[]']['tmp_name'] = $attachments['tmp_name'][$key];
            $_FILES['attachments[]']['error'] = $attachments['error'][$key];
            $_FILES['attachments[]']['size'] = $attachments['size'][$key];
            $config['file_name'] = "attachment_{$newsId}_{$key}";
            $this->upload->initialize($config);

            if ( ! $this->upload->do_upload('attachments[]'))
            {
                return false;
            }
            $uploadData = $this->upload->data();
            $paths[] = UPLOAD_PATH . NEWS_FOLDER . NEWS_ATTACHMENT_FOLDER . $config['file_name'] . $uploadData['file_ext'];
        }
        return $paths;
    }
}