/*!
 * Matomo - free/libre analytics platform
 *
 * ClickHouse POC (DEV-20678): asserts the Matomo app can reach the ClickHouse service
 * from the UI test environment and complete a CREATE/INSERT/SELECT round trip, pinned
 * with a screenshot. The ClickHouse host comes from the [ClickHouse] config section;
 * CI maps the service container to 127.0.0.1 via the CLICKHOUSE_HOST env variable.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ClickHouseStatus", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

    const url = "?module=ClickHouseStatus&action=index&idSite=1&period=day&date=today";

    before(function () {
        testEnvironment.pluginsToLoad = ['ClickHouseStatus'];

        if (process.env.CLICKHOUSE_HOST) {
            testEnvironment.overrideConfig('ClickHouse', 'host', process.env.CLICKHOUSE_HOST);
        }
        if (process.env.CLICKHOUSE_PORT) {
            testEnvironment.overrideConfig('ClickHouse', 'port', process.env.CLICKHOUSE_PORT);
        }

        testEnvironment.save();
    });

    it("should connect to ClickHouse and complete the round trip", async function () {
        await page.goto(url);
        expect(await page.screenshotSelector('#clickHouseStatus')).to.matchImage('connected');
    });
});
