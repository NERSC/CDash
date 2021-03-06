<?php
//
// After including cdash_test_case.php, subsequent require_once calls are
// relative to the top of the CDash source tree
//
require_once dirname(__FILE__) . '/cdash_test_case.php';

require_once 'include/common.php';
require_once 'include/pdo.php';

use CDash\Database;
use CDash\Model\BuildConfigure;

class PutDynamicBuildsTestCase extends KWWebTestCase
{
    public function __construct()
    {
        parent::__construct();
        $this->PDO = Database::getInstance();
        $this->PDO->getPdo();
    }

    public function testPutDynamicBuildsDiff()
    {
        // Create a project for this test.
        $settings = [
            'Name' => 'PutDynamicBuildsProject',
            'Description' => 'PutDynamicBuildsProject'
        ];
        $this->ProjectId = $this->createProject($settings);

        // Get one of the default buildgroups for this project.
        $stmt = $this->PDO->prepare(
            'SELECT id FROM buildgroup WHERE projectid = :projectid LIMIT 2');
        $this->PDO->execute($stmt, [':projectid' => $this->ProjectId]);
        $this->ParentGroupId = $stmt->fetchColumn();
        $this->ChildGroupId = $stmt->fetchColumn();

        // Use the API PUT a list of builds.
        $client = $this->getGuzzleClient();
        $build_rules = [
            [ 'match' => 'foo', 'parentgroupid' => $this->ParentGroupId, 'site' => 'Any', ],
            [ 'match' => 'bar', 'parentgroupid' => $this->ParentGroupId, 'site' => 'Any', ],
            [ 'match' => 'baz', 'parentgroupid' => $this->ParentGroupId, 'site' => 'Any', ],
        ];
        $starttime_stmt = $this->PDO->prepare('
            SELECT starttime FROM build2grouprule
            WHERE buildname     = :buildname AND
                  parentgroupid = :parentgroupid AND
                  siteid        = 0');
        $this->verifyListGetsCreated($client, $starttime_stmt, $build_rules);

        // Submit another list to test update functionality.
        $build_rules = [
            [ 'match' => 'foo', 'parentgroupid' => $this->ParentGroupId, 'site' => 'Any', ],
            [ 'match' => 'bip', 'parentgroupid' => $this->ParentGroupId, 'site' => 'Any', ],
            [ 'match' => 'bop', 'parentgroupid' => $this->ParentGroupId, 'site' => 'Any', ],
        ];
        $this->verifyListGetsCreated($client, $starttime_stmt, $build_rules);

        // Make sure bar and baz got soft-deleted.
        $endtime_stmt = $this->PDO->prepare('
            SELECT endtime FROM build2grouprule
            WHERE buildname     = :buildname AND
                  parentgroupid = :parentgroupid AND
                  siteid        = 0');
        foreach (['%bar%', '%baz%'] as $match) {
            $query_params = [
                ':buildname'     => $match,
                ':parentgroupid' => $this->ParentGroupId
            ];
            $this->PDO->execute($starttime_stmt, $query_params);
            $endtime = $starttime_stmt->fetchColumn();
            $this->assertTrue(strtotime($endtime) > strtotime('-1 week'));
        }

        // Clean up.
        $this->deleteProject($this->ProjectId);
    }

    protected function verifyListGetsCreated($client, $stmt, $build_rules)
    {
        $payload = [
            'project' => 'PutDynamicBuildsProject',
            'buildgroupid' => $this->ChildGroupId,
            'dynamiclist' => $build_rules,
        ];
        $response = $client->request('PUT',
            $this->url . '/api/v1/buildgroup.php',
            ['body' => json_encode($payload)]);

        // Verify that these rules were created.
        foreach ($build_rules as $build_rule) {
            $query_params = [
                ':buildname'     => "%{$build_rule['match']}%",
                ':parentgroupid' => $build_rule['parentgroupid']
            ];
            $this->PDO->execute($stmt, $query_params);
            $starttime = $stmt->fetchColumn();
            if ($starttime === false) {
                $this->fail("{$build_rule['match']} did not get created");
            }
        }
    }
}
