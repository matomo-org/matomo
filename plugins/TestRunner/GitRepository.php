<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TestRunner;

/**
 * git repository introspection.
 */
class GitRepository
{
    /**
     * The path to the git repository.
     *
     * @var string
     */
    private $repoPath;

    /**
     * Constructor.
     *
     * @param string $repoPath
     */
    public function __construct($repoPath)
    {
        $this->repoPath;
    }

    /**
     * Gets the commit hash from a revision description (eg, `"HEAD^^"`).
     *
     * @param string $revisionDesc
     * @return string
     */
    public function getRevisionHash($revisionDesc)
    {
        return trim(`cd "{$this->repoPath}" && git rev-parse HEAD`);
    }

    /**
     * Returns the commit hash for HEAD.
     *
     * @return string
     */
    public function getHeadHash()
    {
        return $this->getRevisionHash("HEAD");
    }
}
