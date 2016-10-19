<?php
use JMS\Composer\Graph\DependencyGraph;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WebComposer\SynchronizationBundle\Command\ProjectSynchronizeCommand;
use WebComposer\SynchronizationBundle\Entity\Package;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;
use WebComposer\SynchronizationBundle\Service\SynchronizationService;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 18.10.2016
 * Time: 19:16
 */
class ProjectSynchronizeCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ProjectSynchronizeCommand
     */
    private $command;
    /**
     * @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContainer;

    protected function setUp()
    {
        parent::setUp();
        $this->mockContainer = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
        $this->command = new ProjectSynchronizeCommand();
        $this->command->setContainer($this->mockContainer);

        $this->assertSame('web-composer:synchronize-project', $this->command->getName());
        $this->assertSame('synchronize an project', $this->command->getDescription());
        $this->assertSame('php bin/console web-composer:synchronize-project projectName', $this->command->getHelp());
    }

    /**
     * @test
     */
    public function execute_method()
    {
        $mockInput = $this->getMockBuilder(InputInterface::class)->disableOriginalConstructor()->getMock();
        $mockOutput = $this->getMockBuilder(OutputInterface::class)->disableOriginalConstructor()->getMock();
        $mockService = $this->getMockBuilder(SynchronizationService::class)->disableOriginalConstructor()->getMock();
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $mockGraph = $this->getMockBuilder(DependencyGraph::class)->disableOriginalConstructor()->getMock();
        $mockPackage = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();
        $mockProjectPackage = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();
        $mockProjectPackageDependency = $this->getMockBuilder(ProjectPackageDependency::class)->disableOriginalConstructor()->getMock();
        $mockDependencyPackage = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();
        $mockDependencyProjectPackage = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();

        $mockInput->expects($this->at(0))->method('getArgument')->with('projectName')->will($this->returnValue('testProject'));

        $this->mockContainer->expects($this->at(0))->method('get')->with('web_composer.synchronizer')->will($this->returnValue($mockService));

        $mockService->expects($this->at(0))->method('findProject')->with('testProject')->will($this->returnValue($mockProject));
        $mockService->expects($this->at(1))->method('analyze')->with($mockProject)->will($this->returnValue($mockGraph));
        $mockService->expects($this->at(2))->method('synchronizePackages')->with($mockGraph)->will($this->returnValue([$mockPackage]));
        $mockService->expects($this->at(3))->method('synchronizeProjectPackages')->with($mockProject, $mockGraph)->will($this->returnValue([$mockProjectPackage]));
        $mockService->expects($this->at(4))->method('synchronizeProjectPackageDependencies')->with($mockProject, $mockGraph)->will($this->returnValue([$mockProjectPackageDependency]));
        $mockService->expects($this->at(5))->method('synchronizePackageVersions')->with($mockProject)->will($this->returnValue([$mockDependencyProjectPackage]));

        $mockPackage->expects($this->any())->method('getName')->will($this->returnValue('testPackageName'));

        $mockProjectPackage->expects($this->any())->method('getPackage')->will($this->returnValue($mockPackage));
        $mockProjectPackage->expects($this->any())->method('getVersion')->will($this->returnValue('testProjectPackageVersion'));

        $mockDependencyPackage->expects($this->any())->method('getName')->will($this->returnValue('testDependencyPackage'));

        $mockDependencyProjectPackage->expects($this->any())->method('getPackage')->will($this->returnValue($mockDependencyPackage));
        $mockDependencyProjectPackage->expects($this->any())->method('getMaxVersion')->will($this->returnValue('testDependencyPackageVersion'));

        $mockProjectPackageDependency->expects($this->at(0))->method('getSourceProjectPackage')->will($this->returnValue($mockProjectPackage));
        $mockProjectPackageDependency->expects($this->at(1))->method('getTargetProjectPackage')->will($this->returnValue($mockDependencyProjectPackage));
        $mockProjectPackageDependency->expects($this->at(2))->method('getVersion')->will($this->returnValue('dependencyVersion'));

        $mockOutput->expects($this->at(0))->method('writeln')->with('update package testPackageName');
        $mockOutput->expects($this->at(1))->method('writeln')->with('update project package testPackageName - testProjectPackageVersion');
        $mockOutput->expects($this->at(2))->method('writeln')->with('update project dependency testPackageName - testDependencyPackage - dependencyVersion');
        $mockOutput->expects($this->at(3))->method('writeln')->with('updated outdated package testDependencyPackage - testDependencyPackageVersion');

        $this->command->execute($mockInput, $mockOutput);
    }
}