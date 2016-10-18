<?php
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use JMS\Composer\DependencyAnalyzer;
use JMS\Composer\Graph\DependencyEdge;
use JMS\Composer\Graph\DependencyGraph;
use JMS\Composer\Graph\PackageNode;
use Symfony\Component\Process\Process;
use WebComposer\SynchronizationBundle\Entity\Package;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;
use WebComposer\SynchronizationBundle\Exception\SynchronizationException;
use WebComposer\SynchronizationBundle\Repository\ProjectPackageDependencyRepository;
use WebComposer\SynchronizationBundle\Repository\ProjectPackageRepository;
use WebComposer\SynchronizationBundle\Service\SaveService;
use WebComposer\SynchronizationBundle\Service\ServiceEntityFactory;
use WebComposer\SynchronizationBundle\Service\SynchronizationService;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 20:10
 */
class SynchronizationServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SynchronizationService
     */
    private $service;
    /**
     * @var DependencyAnalyzer|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAnalyzer;
    /**
     * @var EntityManager|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEntityManager;

    /**
     * @var SaveService|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSaveService;

    /**
     * @var ServiceEntityFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->mockAnalyzer = $this->getMockBuilder(DependencyAnalyzer::class)->disableOriginalConstructor()->getMock();
        $this->mockEntityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->mockSaveService = $this->getMockBuilder(SaveService::class)->disableOriginalConstructor()->getMock();
        $this->mockFactory = $this->getMockBuilder(ServiceEntityFactory::class)->disableOriginalConstructor()->getMock();

        $this->service = new SynchronizationService($this->mockAnalyzer,$this->mockEntityManager,$this->mockSaveService, $this->mockFactory);
    }

    /**
     * @test
     */
    public function findProject_ifExists(){
        $mockProjectRepository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $mockEntity = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(Project::class)->will($this->returnValue($mockProjectRepository));

        $mockProjectRepository->expects($this->at(0))->method('findOneBy')->with(['name' => 'test'])->will($this->returnValue($mockEntity));

        $this->assertSame($mockEntity,$this->service->findProject('test'));
    }

    /**
     * @test
     */
    public function findProject_ifNotExists(){
        $mockProjectRepository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(Project::class)->will($this->returnValue($mockProjectRepository));

        $mockProjectRepository->expects($this->at(0))->method('findOneBy')->with(['name' => 'test'])->will($this->returnValue(null));

        $this->expectException(SynchronizationException::class);
        $this->service->findProject('test');
    }

    /**
     * @test
     */
    public function analyze(){
        $mockResult = $this->getMockBuilder(DependencyGraph::class)->disableOriginalConstructor()->getMock();
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $mockProject->expects($this->at(0))->method('getDirectory')->will($this->returnValue('test'));

        $this->mockAnalyzer->expects($this->at(0))->method('analyze')->with('test')->will($this->returnValue($mockResult));

        $this->assertSame($mockResult,$this->service->analyze($mockProject));
    }

    /**
     * @test
     */
    public function synchronizePackages(){
        $mockGraph = $this->getMockBuilder(DependencyGraph::class)->disableOriginalConstructor()->getMock();
        $mockPackages = [];

        $mockPackage = $this->getMockBuilder(PackageNode::class)->disableOriginalConstructor()->getMock();
        $mockPackage->expects($this->at(0))->method('getData')->will($this->returnValue([]));
        $mockPackage->expects($this->at(1))->method('getName')->will($this->returnValue('test'));
        $mockPackage->expects($this->at(2))->method('getVersion')->will($this->returnValue('version'));
        $mockPackages[] = $mockPackage;

        $mockPackage = $this->getMockBuilder(PackageNode::class)->disableOriginalConstructor()->getMock();
        $mockPackage->expects($this->at(0))->method('getData')->will($this->returnValue(['source' => ['type' => 'other']]));
        $mockPackage->expects($this->at(1))->method('getName')->will($this->returnValue('test2'));
        $mockPackage->expects($this->at(2))->method('getVersion')->will($this->returnValue('version2'));
        $mockPackages[] = $mockPackage;

        $mockPackage = $this->getMockBuilder(PackageNode::class)->disableOriginalConstructor()->getMock();
        $mockPackage->expects($this->at(0))->method('getData')->will($this->returnValue(['source' => ['type' => 'git','url' => 'repositoryUrl']]));
        $mockPackage->expects($this->at(1))->method('getName')->will($this->returnValue('test3'));
        $mockPackage->expects($this->at(2))->method('getVersion')->will($this->returnValue('version3'));
        $mockPackages[] = $mockPackage;

        $mockGraph->expects($this->at(0))->method('getPackages')->will($this->returnValue($mockPackages));

        $this->mockSaveService->expects($this->at(0))->method('buildPackage')->with('test','version',null)->will($this->returnValue('test1'));
        $this->mockSaveService->expects($this->at(1))->method('buildPackage')->with('test2','version2',null)->will($this->returnValue('test2'));
        $this->mockSaveService->expects($this->at(2))->method('buildPackage')->with('test3','version3','repositoryUrl')->will($this->returnValue('test3'));
        $this->assertSame(['test1','test2','test3'],$this->service->synchronizePackages($mockGraph));
    }

    /**
     * @test
     */
    public function synchronizeProjectPackages(){
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $mockGraph = $this->getMockBuilder(DependencyGraph::class)->disableOriginalConstructor()->getMock();
        $mockProjectPackageRepository = $this->getMockBuilder(ProjectPackageRepository::class)->disableOriginalConstructor()->getMock();
        $mockPackageRepository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(ProjectPackage::class)->will($this->returnValue($mockProjectPackageRepository));
        $this->mockEntityManager->expects($this->at(1))->method('getRepository')->with(Package::class)->will($this->returnValue($mockPackageRepository));

        $mockPackages = [];

        $mockPackage = $this->getMockBuilder(PackageNode::class)->disableOriginalConstructor()->getMock();
        $mockPackage->expects($this->at(0))->method('getName')->will($this->returnValue('test'));
        $mockPackage->expects($this->at(1))->method('getVersion')->will($this->returnValue('version'));
        $mockPackages[] = $mockPackage;

        $mockPackage = $this->getMockBuilder(PackageNode::class)->disableOriginalConstructor()->getMock();
        $mockPackage->expects($this->at(0))->method('getName')->will($this->returnValue('test2'));
        $mockPackage->expects($this->at(1))->method('getVersion')->will($this->returnValue('version2'));
        $mockPackages[] = $mockPackage;

        $mockGraph->expects($this->at(0))->method('getPackages')->will($this->returnValue($mockPackages));
        $mockGraph->expects($this->at(1))->method('getRootPackage')->will($this->returnValue($mockPackage));
        $mockGraph->expects($this->at(2))->method('getRootPackage')->will($this->returnValue($mockPackage));

        $projectPackages = [];

        $mockResultPackage = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();
        $mockPackageRepository->expects($this->at(0))->method('findOneBy')->with(['name' => 'test'])->will($this->returnValue($mockResultPackage));

        $mockProjectPackage = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();
        $projectPackages[] = $mockProjectPackage;
        $this->mockSaveService->expects($this->at(0))->method('buildProjectPackage')->with($mockProject,$mockResultPackage,'version')->will($this->returnValue($mockProjectPackage));

        $mockResultPackage = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();
        $mockPackageRepository->expects($this->at(1))->method('findOneBy')->with(['name' => 'test2'])->will($this->returnValue($mockResultPackage));

        $mockProjectPackage = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();
        $projectPackages[] = $mockProjectPackage;
        $this->mockSaveService->expects($this->at(1))->method('buildProjectPackage')->with($mockProject,$mockResultPackage,'version2')->will($this->returnValue($mockProjectPackage));

        $mockProject->expects($this->at(0))->method('setRootProjectPackage')->with($mockProjectPackage);
        $this->mockEntityManager->expects($this->at(2))->method('persist')->with($mockProject);
        $this->mockEntityManager->expects($this->at(3))->method('flush');

        $mockProjectPackageRepository->expects($this->at(0))->method('removeUnusedPackages')->with($mockProject,$projectPackages);

        $this->assertSame($projectPackages,$this->service->synchronizeProjectPackages($mockProject,$mockGraph));
    }

    /**
     * @test
     */
    public function synchronizeProjectPackageDependencies(){
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $mockGraph = $this->getMockBuilder(DependencyGraph::class)->disableOriginalConstructor()->getMock();
        $mockProjectPackageRepository = $this->getMockBuilder(ProjectPackageRepository::class)->disableOriginalConstructor()->getMock();
        $mockProjectPackageDependencyRepository = $this->getMockBuilder(ProjectPackageDependencyRepository::class)->disableOriginalConstructor()->getMock();

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(ProjectPackage::class)->will($this->returnValue($mockProjectPackageRepository));
        $this->mockEntityManager->expects($this->at(1))->method('getRepository')->with(ProjectPackageDependency::class)->will($this->returnValue($mockProjectPackageDependencyRepository));

        $mockPackages = [];

        $mockPackageEdge1 = $this->getMockBuilder(DependencyEdge::class)->disableOriginalConstructor()->getMock();
        $mockPackageEdge1DestNode = $this->getMockBuilder(PackageNode::class)->disableOriginalConstructor()->getMock();
        $mockPackageEdge1DestNode->expects($this->at(0))->method('getName')->will($this->returnValue('test2'));
        $mockPackageEdge1->expects($this->at(0))->method('getDestPackage')->will($this->returnValue($mockPackageEdge1DestNode));
        $mockPackageEdge1->expects($this->at(1))->method('getVersionConstraint')->will($this->returnValue('testVersion'));
        $mockPackageEdge1->expects($this->at(2))->method('isDevDependency')->will($this->returnValue(true));

        $mockPackage = $this->getMockBuilder(PackageNode::class)->disableOriginalConstructor()->getMock();
        $mockPackage->expects($this->at(0))->method('getName')->will($this->returnValue('test'));
        $mockPackage->expects($this->at(1))->method('getOutEdges')->will($this->returnValue([$mockPackageEdge1]));
        $mockPackages[] = $mockPackage;

        $mockGraph->expects($this->at(0))->method('getPackages')->will($this->returnValue($mockPackages));

        $mockDependencyResult = $this->getMockBuilder(ProjectPackageDependency::class)->disableOriginalConstructor()->getMock();
        $mockSource = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();
        $mockTarget = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();
        $mockProjectPackageRepository->expects($this->at(0))->method('findOneByProjectAndPackageName')->with($mockProject,'test')->will($this->returnValue($mockSource));
        $mockProjectPackageRepository->expects($this->at(1))->method('findOneByProjectAndPackageName')->with($mockProject,'test2')->will($this->returnValue($mockTarget));
        $this->mockSaveService->expects($this->at(0))->method('buildProjectPackageDependency')->with($mockSource,$mockTarget,'testVersion',true)->will($this->returnValue($mockDependencyResult));

        $mockProjectPackageDependencyRepository->expects($this->at(0))->method('removeUnusedDependencies')->with($mockProject,[$mockDependencyResult]);

        $this->assertSame([$mockDependencyResult],$this->service->synchronizeProjectPackageDependencies($mockProject,$mockGraph));
    }

    /**
     * @test
     */
    public function synchronizePackageVersions(){
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $mockProject->expects($this->at(0))->method('getDirectory')->will($this->returnValue('/dir/to/project'));

        $mockProcess = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
        $mockProcess->expects($this->at(0))->method('setWorkingDirectory')->with('/dir/to/project');
        $mockProcess->expects($this->at(1))->method('run');
        $mockProcess->expects($this->at(2))->method('getOutput')->will($this->returnValue('LimetecBiotechnologies/database/DriverBundle v0.2.3  v0.2.4  This bundle normalize the differences between mysqli a...
phpunit/phpunit                              5.6.0   5.6.1   The PHP Unit Testing framework.
    '));

        $mockDriverPackage = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();
        $mockDriverPackage->expects($this->at(0))->method('setMaxVersion')->with('v0.2.4');
        $mockPhpUnitPackage = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();
        $mockPhpUnitPackage->expects($this->at(0))->method('setMaxVersion')->with('5.6.1');

        $mockPackageRepository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $mockPackageRepository->expects($this->at(0))->method('findOneBy')->with(['name' => 'LimetecBiotechnologies/database/DriverBundle'])->will($this->returnValue($mockDriverPackage));
        $mockPackageRepository->expects($this->at(1))->method('findOneBy')->with(['name' => 'phpunit/phpunit'])->will($this->returnValue($mockPhpUnitPackage));

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->will($this->returnValue($mockPackageRepository));
        $this->mockEntityManager->expects($this->at(1))->method('persist')->with($mockDriverPackage);
        $this->mockEntityManager->expects($this->at(2))->method('getRepository')->will($this->returnValue($mockPackageRepository));
        $this->mockEntityManager->expects($this->at(3))->method('persist')->with($mockPhpUnitPackage);
        $this->mockEntityManager->expects($this->at(4))->method('flush');

        $this->mockFactory->expects($this->at(0))->method('createProcess')->will($this->returnValue($mockProcess));

        $this->assertSame([$mockDriverPackage,$mockPhpUnitPackage],$this->service->synchronizePackageVersions($mockProject));
    }
}