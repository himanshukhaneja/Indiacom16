<?php
/**
 * Created by PhpStorm.
 * User: Kisholoy
 * Date: 8/3/14
 * Time: 11:13 PM
 */

class ErrorController extends CI_Controller
{
    private $errorMsgs = array(
        1 => "Insufficient Privileges",
        2 => "Could not connect to database"
    );
    public function __construct()
    {
        parent::__construct();
    }

    public function index($errorMsg)
    {
        $this->load->view('Pages/errorPage', array('page_error' => $errorMsg));
    }

    public function errorPage($errorId)
    {
        if(!isset($this->errorMsgs[$errorId]))
        {
            $errorMsg = "Unknown Error";
        }
        else
        {
            $errorMsg = $this->errorMsgs[$errorId];
        }
        $this->load->view('Pages/errorPage', array('page_error' => $errorMsg));
    }
}