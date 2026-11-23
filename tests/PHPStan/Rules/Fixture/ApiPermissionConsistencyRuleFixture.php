<?php

namespace Piwik\Tests\PHPStan\Rules\Fixture;

use Piwik\Attributes\Permission;
use Piwik\Piwik;

class FixtureApi extends \Piwik\Plugin\API
{
    #[Permission('someView')]
    public function validNoParameter()
    {
        Piwik::checkUserHasSomeViewAccess();
    }

    #[Permission('admin', 'idSite')]
    public function validParameterized($idSite)
    {
        Piwik::checkUserHasAdminAccess($idSite);
    }

    #[Permission('superuser')]
    public function mismatchedBodyCheck()
    {
        Piwik::checkUserHasSomeViewAccess();
    }

    #[Permission('someAdmin')]
    public function missingBodyCheck()
    {
    }

    #[Permission('admin', 'idSite')]
    public function wrongBodyMethod($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);
    }

    #[Permission('superUserOrUser', 'userLogin')]
    public function wrongBodyParameter($userLogin, $otherLogin)
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($otherLogin);
    }

    #[Permission('someView')]
    #[Permission('superuser')]
    public function multipleAttributes()
    {
        Piwik::checkUserHasSomeViewAccess();
    }
}
