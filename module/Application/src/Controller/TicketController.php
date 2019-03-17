<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TicketController extends AbstractActionController
{
    protected $userTbl;
    protected $ticketTbl;
    protected $commentTbl;
    protected $enterpriseTbl;

    public function __construct($userTbl, $enterpriseTbl, $ticketTbl, $commentTbl)
    {
       $this->userTbl = $userTbl;
       $this->ticketTbl = $ticketTbl;
       $this->commentTbl = $commentTbl;
       $this->enterpriseTbl = $enterpriseTbl;
    }

    public function indexAction()
    {
        if (!isset($_SESSION['login'])) {
            $this->redirect()->toRoute('account', ['action' => 'login']);
        }

        $userId = $_SESSION['login'][0];
        $userDb = $this->userTbl->getById($userId);

        return new ViewModel([
            'user' => $userDb
        ]);
    }

    private function hasUserPermission($ticketId = null) {
        if (!isset($_SESSION['login'])) {
            $this->redirect()->toRoute('account', ['action' => 'login']);
            return false;
        }
        if ($ticketId != null) {
            $ticket = $this->ticketTbl->getById($ticketId); 
            if ($_SESSION['login'][1] == 1 && $_SESSION['login'][0] != $ticket->fk_user) {
                $this->redirect()->toRoute('ticket', ['action' => 'overview']);
                return false;
            }
        }
        return true;
    }

    public function closeTicketAction()
    {
        $ticketId = (int) $this->params()->fromRoute('id', 0);
        if ($this->hasUserPermission($ticketId)) {
            $data = ['status' => 3];
            $this->ticketTbl->update($ticketId, $data);

            $ticket = $this->ticketTbl->getById($ticketId);
            $user = $this->userTbl->getById($ticket->fk_user);
            $message = "Sehr geehrter Kunde<br /><br />Ihr Ticket '".$ticket->subject."' mit der ID: #T-".$ticketId." wurde von unserem Support abgeschlossen.<br /><br />Freundliche Grüsse<br />Web Weaves Team";
            sendMail($user->email, "#T-".$ticketId.": Ihr Ticket wurde geschlossen", $message);
            $this->redirect()->toRoute('ticket', ['action' => 'detail', 'id' => $ticketId]);
        }
    }

    public function editTicketAction()
    {
        $ticketId = (int) $this->params()->fromRoute('id', 0);

        if ($this->hasUserPermission($ticketId)) {
            $data = ['status' => 1];
            $this->ticketTbl->update($ticketId, $data);

            $ticket = $this->ticketTbl->getById($ticketId); 
            $user = $this->userTbl->getById($ticket->fk_user);
            $message = "Sehr geehrter Kunde<br /><br />Ihr Ticket '".$ticket->subject."' mit der ID: #T-".$ticketId." wird gerade von unserem Support bearbeitet.<br /><br />Freundliche Grüsse<br />Web Weaves Team";
                sendMail($user->email, "#T-".$ticketId.": Ihr Ticket wird gerade bearbeitet", $message);

            $this->redirect()->toRoute('ticket', ['action' => 'detail', 'id' => $ticketId]);
        }
    }

    public function overviewAction()
    {
        $this->hasUserPermission();
        $selectedCustomer = "";

        if ($_SESSION['login'][1] == 2) {
            $ticketList = $this->ticketTbl->fetchAll();

            $enterpriseID = (int) $this->params()->fromRoute('id', 0);
            if ($enterpriseID != null) {
                $selectedCustomer = $enterpriseID;
                $user = $this->userTbl->getByEnterpriseId($enterpriseID);
                $ticketList = $this->ticketTbl->getByUserId($user->pk_user);
            }
        } else {
            $ticketList = $this->ticketTbl->getByUserId($_SESSION['login'][0]);
        }

        return new ViewModel([
            'ticketList' => $ticketList,
            'userTbl' => $this->userTbl,
            'enterpriseTbl' => $this->enterpriseTbl,
            'selectedCustomer' => $selectedCustomer
        ]);
    }

    public function createAction()
    {
        $this->hasUserPermission();

        $success = false;
        $data = [
            'customer'              => "",
            'priority'              => "",
            'subject'               => "",
            'message'               => ""
        ];
        $alerts = array();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $this->params();

            $data = [
                'priority'          => $params->fromPost('priority'),
                'subject'           => $params->fromPost('subject'),
                'message'           => $params->fromPost('message')
            ];

            if ($data['priority'] == "" || empty($data['subject']) || empty($data['message'])) {
                array_push($alerts, "Bitte geben Sie alle Pflichtfelder mit '*' an.");
            }

            if ($_SESSION['login'][1] == 2) {
                $data = [
                    'customer'      => $params->fromPost('customer')
                ];
                if (empty($data['customer'])) {
                    array_push($alerts, "Bitte geben Sie alle Pflichtfelder mit '*' an.");
                }
            }

            if (!count($alerts)) {
                if ($_SESSION['login'][1] == 1) {
                    $userId = $_SESSION['login'][0];
                    $user = $this->userTbl->getById($userId);
                } else {
                    $user = $this->userTbl->getByEnterpriseId($data['customer']);
                    $userId = $user->pk_user;
                }

                $dataTicket = [
                    'fk_user'       => $userId,
                    'priority'      => $params->fromPost('priority'),
                    'subject'       => html2text($params->fromPost('subject'))
                ];
                $ticketId = $this->ticketTbl->insert($dataTicket);

                $message = html2text($params->fromPost('message'));
                $dataComment = [
                    'fk_ticket'     => $ticketId,
                    'fk_user'       => $userId,
                    'message'       => $message
                ];
                $this->commentTbl->insert($dataComment);
                
                $enterprise = $this->enterpriseTbl->getById($user->fk_enterprise);
                $message = "Hallo zusammen<br /><br />Es wurde ein Ticket mit der ID: #T-".$ticketId." erstellt.<br />Kunde: ".$enterprise->name.", ".$user->firstname." ".$user->lastname."<br /><br />Freundliche Grüsse<br />Customer Care System 'Weaver'";
                sendMail(SUPPORT_EMAIL, "#T-".$ticketId.": Neues Ticket wurde erstellt", $message);

                $this->redirect()->toRoute('ticket', ['action' => 'detail', 'id' => $ticketId]);
            }
        }

        return new ViewModel([
            'formData' => $data,
            'alerts' => $alerts,
            'success' => $success,
            'userTbl' => $this->userTbl,
            'enterpriseTbl' => $this->enterpriseTbl
        ]);
    }

    public function detailAction()
    {
        $ticketId = (int) $this->params()->fromRoute('id', 0);
        $this->hasUserPermission($ticketId);
        
        $ticket = $this->ticketTbl->getById($ticketId);
        $commentList = $this->commentTbl->getByTicketId($ticketId);

        $success = false;
        $data = [
            'message'               => ""
        ];
        $alerts = array();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $this->params();

            $data = [
                'message'           => $params->fromPost('message')
            ];

            if (empty($data['message'])) {
                array_push($alerts, "Bitte geben Sie eine Nachricht ein.");
            }

            if (!count($alerts)) {
                $dataTicket = [
                    'status'       => $ticket->fk_user == $_SESSION['login'][0] ? 0 : 2,
                ];

                $message = html2text($params->fromPost('message'));
                $dataComment = [
                    'fk_ticket'     => $ticketId,
                    'fk_user'       => $_SESSION['login'][0],
                    'message'       => $message
                ];

                $this->ticketTbl->update($ticketId, $dataTicket);
                $this->commentTbl->insert($dataComment);

                if ($ticket->fk_user == $_SESSION['login'][0]) {
                    $message = "Hallo zusammen<br /><br />Das Ticket mit der ID: #T-".$ticketId." wurde soeben bearbeitet und eine Nachfrage/Antwort wurde hinzugefügt.<br /><br />Freundliche Grüsse<br />Customer Care System 'Weaver'";
                    $mail = SUPPORT_EMAIL;
                } else {
                    $message = "Sehr geehrter Kunde<br /><br />Ihr Ticket mit der ID: #T-".$ticketId." wurde soeben von unserem Support beantwortet.<br /><br />Freundliche Grüsse<br />Web Weaves Team";
                    $user = $this->userTbl->getById($ticket->fk_user);
                    $mail = $user->email;
                }
                sendMail($mail, "Ticket #T-".$ticketId." wurde aktualisiert", $message);

                $this->redirect()->toRoute('ticket', ['action' => 'detail', 'id' => $ticketId]);
            }
        }

        return new ViewModel([
            'ticket' => $ticket,
            'formData' => $data,
            'alerts' => $alerts,
            'success' => $success,
            'userTbl' => $this->userTbl,
            'commentList' => $commentList
        ]);
    }
}
