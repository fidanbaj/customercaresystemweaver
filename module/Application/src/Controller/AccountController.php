<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractActionController
{
    protected $userTbl;
    protected $enterpriseTbl;

    public function __construct($userTbl, $enterpriseTbl)
    {
       $this->userTbl = $userTbl;
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

    private function hasUserPermission() {
        if (!isset($_SESSION['login'])) {
            $this->redirect()->toRoute('account', ['action' => 'login']);
            return false;
        } 
        if (isset($_SESSION['login']) && $_SESSION['login'][1] != 2) {
            $this->redirect()->toRoute('account', ['action' => 'index']);
            return false;
        }
        return true;
    }

    private function encryptId($id) {
        return base64_encode(crypt($id, "resetlink".date("Ymd")));
    }

    public function userAdministrationAction()
    {
        $this->hasUserPermission();

        return new ViewModel([
            'userList' => $this->userTbl->fetchAll(),
            'enterpriseTbl' => $this->enterpriseTbl
        ]);
    }

    public function changeUserStatusAction()
    {
        if ($this->hasUserPermission()) {
            $userId = (int) $this->params()->fromRoute('id', 0);
            $user = $this->userTbl->getById($userId);
            $status = $user->status ? 0 : 1;
            $data = ['deleted' => 0, 'status' => $status];

            if ($status == 1) {
                $message = "Sehr geehrter Kunde<br /><br />Ihr Account wurde soeben auf dem Customer Care System 'Weaver' aktiviert.<br /><br />Freundliche Grüsse<br />Web Weaves Team";
                sendMail($user->email, "Account wurde aktiviert", $message);
            }

            $this->userTbl->update($userId, $data);
            $this->redirect()->toRoute('account', ['action' => 'useradministration']);
        }
    }

    public function deleteUserAction()
    {
        if ($this->hasUserPermission()) {
            $data = ['deleted' => 1, 'status' => 0];
            $userId = (int) $this->params()->fromRoute('id', 0);

            $this->userTbl->update($userId, $data);
            $this->redirect()->toRoute('account', ['action' => 'useradministration']);
        }
    }

    public function resetPasswordAction()
    {
        if (isset($_SESSION['login'])) {
            $this->redirect()->toRoute('account', ['action' => 'index']);
        }
        $routeResetId = $this->params()->fromRoute('id', 0);    

        $success = false;
        $data = [
            'email'             => "",
            'newpassword'       => "",
            'newpasswordrepeat' => ""
        ];
        $alerts = array();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $this->params();
            
            $data = [
                'email'             => html2text($params->fromPost('email')),
                'newpassword'       => html2text($params->fromPost('newpassword')),
                'newpasswordrepeat' => html2text($params->fromPost('newpasswordrepeat'))
            ];

            if (empty($data['email']) || empty($data['newpassword']) || empty($data['newpasswordrepeat'])) {
                array_push($alerts,"Bitte geben Sie alle Pflichtfelder mit '*' an.");
            }
            if (!count($alerts) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) == FALSE) {
                array_push($alerts,"E-Mail Adresse ist nicht valid.");
            }
            if (!count($alerts) && $data['newpassword'] != $data['newpasswordrepeat']) {
                array_push($alerts,"Die neuen Passwörter stimmen nicht überein.");
            }

            if (!count($alerts)) {
                $user = $this->userTbl->getByMail($data['email']);

                if ($user == null) {
                    array_push($alerts,"Ein Benutzer mit dieser E-Mail Adresse existiert nicht.");
                } else {
                    if ($user->status == 0 || $user->deleted) {
                        array_push($alerts,"Ihr Passwort kann nicht zurückgesetzt werden, bitte wenden Sie sich an den Support.");
                    }

                    $resetId = $this->encryptId($user->pk_user);                    
                    if ($routeResetId != $resetId) {
                        array_push($alerts,"Reset ist fehlgeschlagen.");
                    }
                }

                if (!count($alerts)) {
                    $updateData['password'] = crypt($data['newpassword'], LOGIN_PW_SALT);
                    $this->userTbl->update($user->pk_user, $updateData);
                    $success = true;                        
                    
                    $message = "Sehr geehrter Kunde<br /><br />Ihr Passwort wurde soeben zurückgesetzt. Falls Sie dies nicht veranlasst haben, melden Sie sich bitte bei unserem Support.<br /><br />Freundliche Grüsse<br />Web Weaves Team";
                    sendMail($user->email, "Passwort zurücksetzen", $message);

                    $data = [
                        'email'             => "",
                        'newpassword'       => "",
                        'newpasswordrepeat' => ""
                    ];
                }                
            }
        }

        return new ViewModel([
            'alerts' => $alerts,
            'formData' => $data,
            'success' => $success,
            'resetId' => $routeResetId
        ]);
    }

    public function forgotPasswordAction()
    {
        if (isset($_SESSION['login'])) {
            $this->redirect()->toRoute('account', ['action' => 'index']);
        }

        $success = false;
        $data = [
            'email'             => ""
        ];
        $alerts = array();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $this->params();

            $data = [
                'email'         => html2text($params->fromPost('email'))
            ];

            if (empty($data['email'])) {
                array_push($alerts,"Bitte geben Sie alle Pflichtfelder mit '*' an.");
            }
            if (!count($alerts) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) == FALSE) {
                array_push($alerts,"E-Mail Adresse ist nicht valid.");
            }

            if (!count($alerts)) {
                $user = $this->userTbl->getByMail($data['email']);

                if ($user == null) {
                    array_push($alerts,"Ein Benutzer mit dieser E-Mail Adresse existiert nicht.");
                } else {
                    if ($user->status == 0 || $user->deleted) {
                        array_push($alerts,"Ihr Passwort kann nicht zurückgesetzt werden, bitte wenden Sie sich an den Support.");
                    }
                }

                if (!count($alerts)) {
                    $success = true;                        
                    
                    $server_url = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost();
                    $resetLink = $server_url . "/account/resetpassword/" . $this->encryptId($user->pk_user);

                    $message = "Sehr geehrter Kunde<br /><br />Damit Sie Ihr Passwort zurücksetzen können, klicken Sie bitte auf folgenden <a href='".$resetLink."'>Link</a> und folgen Sie den Anweisungen. Der Link ist bis Ende dieses Tages gültig.<br /><br />Freundliche Grüsse<br />Web Weaves Team";
                    sendMail($user->email, "Passwort zurücksetzen", $message);
                }                
            }
        }

        return new ViewModel([
            'alerts' => $alerts,
            'formData' => $data,
            'success' => $success
        ]);
    }    

    public function loginAction()
    {
        if (isset($_SESSION['login'])) {
            $this->redirect()->toRoute('account', ['action' => 'index']);
        }

        $data = [
            'username' => "",
            'password' => ""
        ];
        $alerts = array();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $this->params();

            $data = [
                'username' => html2text($params->fromPost('username')),
                'password' => html2text($params->fromPost('password'))
            ];

            if (empty($data['username']) || empty($data['password'])) {
                array_push($alerts,"Bitte geben Sie alle Pflichtfelder mit '*' an.");
            }

            if (!count($alerts)) {
                $user = $this->userTbl->getByMail($data['username']);
                
                if ($user != null) {
                    if ($data['username'] != $user->email || crypt($data['password'], LOGIN_PW_SALT) != $user->password) {
                        array_push($alerts,"Login fehlgeschlagen.");
                    }
                    if ($user->deleted) {
                        array_push($alerts,"Ihr Account wurde gelöscht.<br /> Bitte wenden Sie sich an den Support per E-Mail oder Telefon.");
                    }
                    if (!count($alerts) && $user->status == 0) {
                        array_push($alerts,"Ihr Account wurde noch nicht freigeschaltet.");
                    }
                } else {
                    array_push($alerts,"Login fehlgeschlagen.");
                }

                if (!count($alerts)) {
                    $_SESSION['login'] = array($user->pk_user, $user->type);
                    $this->redirect()->toRoute('account', ['action' => 'index']);
                }
            }
        }

        return new ViewModel([
            'alerts' => $alerts,
            'formData' => $data
        ]);
    }

    public function manageAction()
    {
        $success = false;
        $alerts = array();
        $request = $this->getRequest();
        $data = [            
            'enterprisename' => "",
            'title' => "",
            'firstname' => "",
            'lastname' => "",
            'address' => "",
            'zip' => "",
            'place' => "",
            'email' => "",
            'password' => "",
            'passwordrepeat' => "",
            'phone' => ""
        ];

        if (isset($_SESSION['login'])) {
            $userId = $_SESSION['login'][0];
            $userDb = $this->userTbl->getById($userId);
            $enterpriseDb = $this->enterpriseTbl->getById($userDb->fk_enterprise);

            $data = [            
                'enterprisename'    => html2text($enterpriseDb->name),
                'title'             => html2text($userDb->title),
                'firstname'         => html2text($userDb->firstname),
                'lastname'          => html2text($userDb->lastname),
                'address'           => html2text($enterpriseDb->address),
                'zip'               => html2text($enterpriseDb->zip),
                'place'             => html2text($enterpriseDb->place),
                'email'             => html2text($userDb->email),
                'password'          => html2text($userDb->password),
                'passwordrepeat'    => html2text(""),
                'phone'             => html2text($enterpriseDb->phone)
            ];
        }

        if ($request->isPost()) {
            $params = $this->params();
            //fill array with input from form
            $data = [            
                'enterprisename'    => $params->fromPost('enterprisename'),
                'title'             => $params->fromPost('title'),
                'firstname'         => $params->fromPost('firstname'),
                'lastname'          => $params->fromPost('lastname'),
                'address'           => $params->fromPost('address'),
                'zip'               => $params->fromPost('zip'),
                'place'             => $params->fromPost('place'),
                'email'             => $params->fromPost('email'),
                'password'          => $params->fromPost('password'),
                'passwordrepeat'    => $params->fromPost('passwordrepeat'),
                'phone'             => $params->fromPost('phone')
            ];

            // check for empty fields
            if (empty($data['enterprisename']) || empty($data['title']) || empty($data['firstname']) || empty($data['lastname']) || 
                empty($data['address']) || empty($data['zip']) || empty($data['place']) || empty($data['email']) || 
                empty($data['password']) || empty($data['phone'])) {
                array_push($alerts,"Bitte geben Sie alle Pflichtfelder mit '*' an.");
            }

            //check password for create mode
            if (!isset($_SESSION['login']) && !count($alerts) && $data['password'] != $data['passwordrepeat']) {
                array_push($alerts,"Die erneute Passworteingabe stimmt nicht überein!");
            }
            //check password for edit mode
            if (!count($alerts) && isset($_SESSION['login']) && ($data['password'] != "" && $data['passwordrepeat'] != "") && 
                $data['password'] != $data['passwordrepeat']) {
                array_push($alerts,"Die erneute Passworteingabe stimmt nicht überein.");
            }

            if (!count($alerts) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) == FALSE) {
                array_push($alerts,"E-Mail Adresse ist nicht valid.");
            }

            if (!count($alerts) && !is_numeric($data['zip'])) {
                array_push($alerts,"PLZ muss eine Nummer sein.");
            }

            if (!count($alerts)) {
                $user = $this->userTbl->getByMail($data['email']);
                if (isset($_SESSION['login']) && $user && $user->email == $userDb->email) {
                    $user = null;
                }
                if ($user != null) {
                    array_push($alerts,"Es existiert bereits ein Account mit dieser E-Mail Adresse.");
                }

                $enterprise = $this->enterpriseTbl->getByName($data['enterprisename']);
                if (isset($_SESSION['login']) && $enterprise && $enterprise->name == $enterpriseDb->name) {
                    $enterprise = null;
                }
                if ($enterprise != null) {
                    array_push($alerts,"Es existiert bereits ein Account mit diesem Unternehmen.");
                }
            }

            if (!count($alerts)) {
                $dataEnterprise = [
                    'name'              => $data['enterprisename'],
                    'address'           => $data['address'],
                    'zip'               => $data['zip'],
                    'place'             => $data['place'],
                    'phone'             => $data['phone']
                ];
                if (isset($_SESSION['login'])) {
                    $enterpriseId = $userDb->fk_enterprise;
                    $this->enterpriseTbl->update($enterpriseId, $dataEnterprise);
                } else {
                    $enterpriseId = $this->enterpriseTbl->insert($dataEnterprise);
                }
                
                $dataUser = [
                    'fk_enterprise'     => $enterpriseId,
                    'title'             => $data['title'],
                    'firstname'         => $data['firstname'],
                    'lastname'          => $data['lastname'],
                    'email'             => $data['email']                    
                ];
                
                if ($data['password'] != "" && $data['passwordrepeat'] != "") {
                    $saltedPassword = crypt($data['password'], LOGIN_PW_SALT);
                    $dataUser['password'] = $saltedPassword;
                }

                if (isset($_SESSION['login'])) {
                    $this->userTbl->update($userDb->pk_user, $dataUser);
                } else {
                    $dataUser['type'] = 1;
                    $dataUser['status'] = 0;
                    $this->userTbl->insert($dataUser);
                    
                    //send mail to user
                    $message = "Sehr geehrter Kunde<br /><br />Ihr Account wurde erfolgreich erfasst. Wir werden Ihre Daten prüfen und Sie erhalten eine Benachrichtigung, sobald wir Ihren Account aktivieren.<br /><br />Freundliche Grüsse<br />Web Weaves Team";
                    sendMail($data['email'], "Neuer Account", $message);

                    //send mail to support
                    $message = "Hallo zusammen<br /><br />Soeben hat sich jemand auf dem Portal registriert. Bitte prüfen und gegebenfalls freischalten.<br /><br />Freundliche Grüsse<br />Customer Care System 'Weaver'";
                    sendMail(SUPPORT_EMAIL, "Neuer Account: ".$data['enterprisename'], $message);
                }
                $success = true;
            }
        }

        return new ViewModel([
            'alerts' => $alerts,
            'formData' => $data,
            'success' => $success,
            'mode' => isset($_SESSION['login']) ? "edit" : "create"
        ]);
    }

    public function logoutAction()
    {
        unset($_SESSION['login']);
        return new ViewModel();
    }
}
