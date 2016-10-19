<?php

namespace WebComposer\SynchronizationBundle\Tests;

use JMS\Composer\DependencyAnalyzer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SynchronizeControllerIntegrationTest extends WebTestCase
{
    /**
     * @test
     */
    public function synchronizeAction()
    {
        $client = static::createClient();

        $client->request('GET', '/synchronize/webComposer');
        $projectPath = realpath(__DIR__ . '/../../../../');

        $analyzer = new DependencyAnalyzer();
        $graph = $analyzer->analyze($projectPath);

        foreach ($graph->getPackages() as $package) {
            $this->assertContains('package: ' . $package->getName(), $client->getResponse()->getContent());
            $this->assertContains('projectPackage: ' . $package->getName() . ' - ' . $package->getVersion(), $client->getResponse()->getContent());
            foreach ($package->getOutEdges() as $edge) {
                $this->assertContains('dependency: ' . $edge->getSourcePackage()->getName() . ' - ' . $edge->getDestPackage()->getName() . ' - ' . htmlspecialchars($edge->getVersionConstraint()), $client->getResponse()->getContent());
            }
        }
    }
}
