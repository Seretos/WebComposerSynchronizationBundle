<?php
use JMS\Composer\Graph\DependencyGraph;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WebComposer\SynchronizationBundle\Controller\SynchronizeController;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Service\SynchronizationService;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 18.10.2016
 * Time: 00:59
 */
class SynchronizeControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SynchronizeController
     */
    private $controller;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContainer;

    protected function setUp()
    {
        parent::setUp();
        $this->mockContainer = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
        $this->controller = new SynchronizeController();
        $this->controller->setContainer($this->mockContainer);
    }

    /**
     * @test
     */
    public function synchronizeAction(){
        $mockSynchronizer = $this->getMockBuilder(SynchronizationService::class)->disableOriginalConstructor()->getMock();
        $this->mockContainer->expects($this->at(0))->method('get')->with('web_composer.synchronizer')->will($this->returnValue($mockSynchronizer));

        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $mockSynchronizer->expects($this->at(0))->method('findProject')->with('test')->will($this->returnValue($mockProject));
        $mockGraph = $this->getMockBuilder(DependencyGraph::class)->disableOriginalConstructor()->getMock();
        $mockSynchronizer->expects($this->at(1))->method('analyze')->with($mockProject)->will($this->returnValue($mockGraph));
        $mockSynchronizer->expects($this->at(2))->method('synchronizePackages')->with($mockGraph)->will($this->returnValue('packages'));
        $mockSynchronizer->expects($this->at(3))->method('synchronizeProjectPackages')->with($mockProject,$mockGraph)->will($this->returnValue('projectPackages'));
        $mockSynchronizer->expects($this->at(4))->method('synchronizeProjectPackageDependencies')->with($mockProject,$mockGraph)->will($this->returnValue('dependencies'));
        $mockSynchronizer->expects($this->at(5))->method('synchronizePackageVersions')->with($mockProject)->will($this->returnValue('outdated'));

        $this->mockContainer->expects($this->at(1))->method('has')->with('templating')->will($this->returnValue(true));
        $twig = $this->getMockBuilder(TwigEngine::class)->disableOriginalConstructor()->getMock();
        $this->mockContainer->expects($this->at(2))->method('get')->with('templating')->will($this->returnValue($twig));
        $twig->expects($this->at(0))->method('renderResponse')->with('WebComposerSynchronizationBundle:Default:synchronize.html.twig',['packages' => 'packages','projectPackages' => 'projectPackages', 'dependencies' => 'dependencies','outdated' => 'outdated'])->will($this->returnValue('success'));

        $this->assertSame('success',$this->controller->synchronizeAction('test'));
    }
}