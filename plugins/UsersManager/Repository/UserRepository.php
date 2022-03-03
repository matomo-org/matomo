<?php

namespace Piwik\Plugins\UsersManager\Repository;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Emails\UserCreatedEmail;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Emails\UserInviteEmail;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Plugins\UsersManager\Validators\Email;
use Piwik\Plugins\UsersManager\Validators\Login;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\IdSite;

class UserRepository
{

    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }


    public function index($id)
    {

    }

    public function create($userLogin, $email, $initialIdSite)
    {
        $this->validateAccess();
        if (!Piwik::hasUserSuperUserAccess()) {
            if (empty($initialIdSite)) {
                throw new \Exception(Piwik::translate("UsersManager_AddUserNoInitialAccessError"));
            }
            // check if the site exist
            IdSite::validate($initialIdSite);
            Piwik::checkUserHasAdminAccess($initialIdSite);
        }

        //validate info
        BaseValidator::check('userLogin', $userLogin, [new Login(true)]);
        BaseValidator::check('email', $email, [new Email(true)]);

        //insert user into database.
        $this->model->addUser($userLogin, '', $email, Date::now()->getDatetime(), true);

        $mail = StaticContainer::getContainer()->make(UserCreatedEmail::class, array(
          'login'        => Piwik::getCurrentUserLogin(),
          'emailAddress' => Piwik::getCurrentUserEmail(),
          'userLogin'    => $userLogin,
        ));
        $mail->safeSend();

        /**
         * Triggered after a new user is invited.
         *
         * @param string $userLogin The new user's details handle.
         */
        Piwik::postEvent('UsersManager.inviteUser.end', array($userLogin, $email));

        if ($initialIdSite) {
            API::getInstance()->setUserAccess($userLogin, 'view', $initialIdSite);
        }
    }


    public function update()
    {

    }

    public function delete()
    {

    }

    public function sendInvite($userLogin, $expired = 7)
    {

        //retrieve user details
        $user = API::getInstance()->getUser($userLogin, true);

        //remove all previous token
        $this->model->deleteAllTokensForUser($userLogin);

        //generate Token
        $generatedToken = $this->model->generateRandomTokenAuth();

        //attach token to user
        $this->model->addTokenAuth($userLogin, $generatedToken, "Invite Token", Date::now()->getDatetime(),
          Date::now()->addDay($expired)->getDatetime());


        // send email
        $email =  StaticContainer::getContainer()->make(UserInviteEmail::class, array(
          'currentUser' => Piwik::getCurrentUserLogin(),
          'user'  => $user,
          'token'       => $generatedToken
        ));
        $email->safeSend();
    }

    private function validateAccess()
    {
        Piwik::checkUserHasSomeAdminAccess();
        UsersManager::dieIfUsersAdminIsDisabled();
    }


}