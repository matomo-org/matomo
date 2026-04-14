<?php

namespace Piwik\Tests\PHPStan\Rules\Fixture;

use Piwik\Attributes\Permission;
use Piwik\Piwik;

class FixtureApi extends \Piwik\Plugin\API
{
    /**
     * @matomo-permission someView
     */
    #[Permission('someView')]
    public function validNoParameter()
    {
        Piwik::checkUserHasSomeViewAccess();
    }

    /**
     * @matomo-permission admin($idSite)
     */
    #[Permission('admin', 'idSite')]
    public function validParameterized($idSite)
    {
        Piwik::checkUserHasAdminAccess($idSite);
    }

    #[Permission('someView')]
    public function missingDocblock()
    {
        Piwik::checkUserHasSomeViewAccess();
    }

    /**
     * @matomo-permission someView
     */
    public function missingAttribute()
    {
        Piwik::checkUserHasSomeViewAccess();
    }

    /**
     * @matomo-permission someView
     */
    #[Permission('superuser')]
    public function mismatchedMetadata()
    {
        Piwik::checkUserHasSomeViewAccess();
    }

    /**
     * @matomo-permission someAdmin
     */
    #[Permission('someAdmin')]
    public function missingBodyCheck()
    {
    }

    /**
     * @matomo-permission admin(idSite)
     */
    #[Permission('admin', 'idSite')]
    public function wrongBodyMethod($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);
    }

    /**
     * @matomo-permission superUserOrUser(userLogin)
     */
    #[Permission('superUserOrUser', 'userLogin')]
    public function wrongBodyParameter($userLogin, $otherLogin)
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($otherLogin);
    }

    /**
     * @matomo-permission someView
     */
    #[Permission('someView')]
    #[Permission('superuser')]
    public function multipleAttributes()
    {
        Piwik::checkUserHasSomeViewAccess();
    }
}
