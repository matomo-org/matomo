<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\CustomBranding;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\CustomLogoTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ManyUsersTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleSuperusersTrigger;
use Piwik\Plugins\UsersManager\Model;

/**
 * The promotions pitched on the shape of the instance rather than on a website's reports.
 *
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class InstanceStateTriggersTest extends TestCase
{
    /**
     * @dataProvider getUserCountCases
     */
    public function testTheLoginPromotionAppliesItsUserFloor(int $numUsers, bool $expectedToFire): void
    {
        $trigger = new ManyUsersTrigger($this->makeEnvironment($numUsers));

        $this->assertSame($expectedToFire, $trigger->evaluate(1)->isTriggered());
    }

    /**
     * @return array<string, array{int, bool}>
     */
    public function getUserCountCases(): array
    {
        return [
            'just below the floor' => [19, false],
            'at the floor' => [20, true],
            'well above the floor' => [250, true],
            'a single user' => [1, false],
        ];
    }

    public function testTheLoginPromotionReportsTheUserCountForTheCopy(): void
    {
        $result = (new ManyUsersTrigger($this->makeEnvironment(42)))->evaluate(1);

        $this->assertSame(['count' => 42], $result->getContext());
    }

    /**
     * @dataProvider getBrandingCases
     */
    public function testTheBrandingPromotionFollowsTheCustomLogo(bool $hasLogo, bool $expectedToFire): void
    {
        $branding = $this->createMock(CustomBranding::class);
        $branding->method('hasCustomLogo')->willReturn($hasLogo);

        $this->assertSame($expectedToFire, (new CustomLogoTrigger($branding))->evaluate(1)->isTriggered());
    }

    /**
     * @return array<string, array{bool, bool}>
     */
    public function getBrandingCases(): array
    {
        return [
            'a logo has been uploaded' => [true, true],
            'still on Matomo branding' => [false, false],
        ];
    }

    /**
     * The copy for the branding promotion names no figure, so it reports none.
     */
    public function testTheBrandingPromotionReportsNoFigure(): void
    {
        $branding = $this->createMock(CustomBranding::class);
        $branding->method('hasCustomLogo')->willReturn(true);

        $this->assertSame([], (new CustomLogoTrigger($branding))->evaluate(1)->getContext());
    }

    /**
     * @dataProvider getAuditCases
     */
    public function testTheAuditPromotionNeedsBothUsersAndAdministrators(
        int $numUsers,
        int $numSuperusers,
        bool $expectedToFire
    ): void {
        $trigger = new MultipleSuperusersTrigger(
            $this->makeEnvironment($numUsers),
            $this->makeUserModel($numSuperusers)
        );

        $this->assertSame($expectedToFire, $trigger->evaluate(1)->isTriggered());
    }

    /**
     * @return array<string, array{int, int, bool}>
     */
    public function getAuditCases(): array
    {
        return [
            'at both floors' => [10, 3, true],
            'just below the user floor' => [9, 3, false],
            'just below the administrator floor' => [10, 2, false],
            // Many users with one administrator is a login problem, not an audit one.
            'many users, one administrator' => [80, 1, false],
            'few users, many administrators' => [4, 6, false],
            'well above both floors' => [60, 8, true],
        ];
    }

    public function testTheAuditPromotionReportsBothCountsForTheCopy(): void
    {
        $trigger = new MultipleSuperusersTrigger($this->makeEnvironment(31), $this->makeUserModel(4));

        $this->assertSame(['count' => 31, 'numSuperusers' => 4], $trigger->evaluate(1)->getContext());
    }

    private function makeEnvironment(int $numUsers): Environment
    {
        $environment = $this->createMock(Environment::class);
        $environment->method('getNumUsers')->willReturn($numUsers);

        return $environment;
    }

    private function makeUserModel(int $numSuperusers): Model
    {
        $model = $this->createMock(Model::class);
        $model->method('getUsersHavingSuperUserAccess')->willReturn(array_fill(0, $numSuperusers, ['login' => 'admin']));

        return $model;
    }
}
