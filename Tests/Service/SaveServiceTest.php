<?php
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use WebComposer\SynchronizationBundle\Entity\Package;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;
use WebComposer\SynchronizationBundle\Repository\ProjectPackageDependencyRepository;
use WebComposer\SynchronizationBundle\Repository\ProjectPackageRepository;
use WebComposer\SynchronizationBundle\Service\ServiceEntityFactory;
use WebComposer\SynchronizationBundle\Service\SaveService;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 19:34
 */
class SaveServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SaveService
     */
    private $service;
    /**
     * @var ServiceEntityFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEntityFactory;
    /**
     * @var EntityManager|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEntityManager;

    protected function setUp()
    {
        parent::setUp();
        $this->mockEntityFactory = $this->getMockBuilder(ServiceEntityFactory::class)->disableOriginalConstructor()->getMock();
        $this->mockEntityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();

        $this->service = new SaveService($this->mockEntityFactory, $this->mockEntityManager);
    }

    /**
     * @test
     */
    public function buildProject_ifExists()
    {
        $mockProjectRepository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $mockEntity = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(Project::class)->will($this->returnValue($mockProjectRepository));

        $mockProjectRepository->expects($this->at(0))->method('findOneBy')->with(['name' => 'test'])->will($this->returnValue($mockEntity));

        $mockEntity->expects($this->at(0))->method('setName')->with('test');
        $mockEntity->expects($this->at(1))->method('setDirectory')->with('/path/to/test');

        $this->mockEntityManager->expects($this->at(1))->method('persist')->with($mockEntity);
        $this->mockEntityManager->expects($this->at(2))->method('flush');

        $this->assertSame($mockEntity, $this->service->buildProject('test', '/path/to/test'));
    }

    /**
     * @test
     */
    public function buildProject_ifNotExists()
    {
        $mockProjectRepository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $mockEntity = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(Project::class)->will($this->returnValue($mockProjectRepository));

        $mockProjectRepository->expects($this->at(0))->method('findOneBy')->with(['name' => 'test'])->will($this->returnValue(null));

        $this->mockEntityFactory->expects($this->at(0))->method('createProject')->will($this->returnValue($mockEntity));

        $mockEntity->expects($this->at(0))->method('setName')->with('test');
        $mockEntity->expects($this->at(1))->method('setDirectory')->with('/path/to/test');

        $this->mockEntityManager->expects($this->at(1))->method('persist')->with($mockEntity);
        $this->mockEntityManager->expects($this->at(2))->method('flush');

        $this->assertSame($mockEntity, $this->service->buildProject('test', '/path/to/test'));
    }

    /**
     * @test
     */
    public function buildPackage_ifExists()
    {
        $mockPackageRepository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $mockEntity = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(Package::class)->will($this->returnValue($mockPackageRepository));

        $mockPackageRepository->expects($this->at(0))->method('findOneBy')->with(['name' => 'test'])->will($this->returnValue($mockEntity));

        $this->assertSame($mockEntity, $this->service->buildPackage('test', 'url'));
    }

    /**
     * @test
     */
    public function buildPackage_ifNotExists()
    {
        $mockPackageRepository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $mockEntity = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(Package::class)->will($this->returnValue($mockPackageRepository));

        $mockPackageRepository->expects($this->at(0))->method('findOneBy')->with(['name' => 'test'])->will($this->returnValue(null));

        $this->mockEntityFactory->expects($this->at(0))->method('createPackage')->will($this->returnValue($mockEntity));

        $mockEntity->expects($this->at(0))->method('setName')->with('test')->will($this->returnValue($mockEntity));
        $mockEntity->expects($this->at(1))->method('setRepository')->with('url')->will($this->returnValue($mockEntity));

        $this->mockEntityManager->expects($this->at(1))->method('persist')->with($mockEntity);
        $this->mockEntityManager->expects($this->at(2))->method('flush');

        $this->assertSame($mockEntity, $this->service->buildPackage('test', 'url'));
    }

    /**
     * @test
     */
    public function buildProjectPackage_ifExists()
    {
        $mockProjectPackageRepository = $this->getMockBuilder(ProjectPackageRepository::class)->disableOriginalConstructor()->getMock();
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $mockPackage = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();
        $mockEntity = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(ProjectPackage::class)->will($this->returnValue($mockProjectPackageRepository));

        $mockProjectPackageRepository->expects($this->at(0))->method('findOneBy')->with(['project' => $mockProject, 'package' => $mockPackage])->will($this->returnValue($mockEntity));

        $mockEntity->expects($this->at(0))->method('setVersion')->with('version')->will($this->returnValue($mockEntity));

        $this->mockEntityManager->expects($this->at(1))->method('persist')->with($mockEntity);
        $this->mockEntityManager->expects($this->at(2))->method('flush');

        $this->assertSame($mockEntity, $this->service->buildProjectPackage($mockProject, $mockPackage, 'version'));
    }

    /**
     * @test
     */
    public function buildProjectPackage_ifNotExists()
    {
        $mockProjectPackageRepository = $this->getMockBuilder(ProjectPackageRepository::class)->disableOriginalConstructor()->getMock();
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $mockPackage = $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock();
        $mockEntity = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(ProjectPackage::class)->will($this->returnValue($mockProjectPackageRepository));

        $mockProjectPackageRepository->expects($this->at(0))->method('findOneBy')->with(['project' => $mockProject, 'package' => $mockPackage])->will($this->returnValue(null));

        $this->mockEntityFactory->expects($this->at(0))->method('createProjectPackage')->will($this->returnValue($mockEntity));

        $mockEntity->expects($this->at(0))->method('setPackage')->with($mockPackage)->will($this->returnValue($mockEntity));
        $mockEntity->expects($this->at(1))->method('setProject')->with($mockProject)->will($this->returnValue($mockEntity));

        $mockProject->expects($this->at(0))->method('addProjectPackage')->with($mockEntity);

        $mockEntity->expects($this->at(2))->method('setVersion')->with('version')->will($this->returnValue($mockEntity));

        $this->mockEntityManager->expects($this->at(1))->method('persist')->with($mockEntity);
        $this->mockEntityManager->expects($this->at(2))->method('flush');

        $this->assertSame($mockEntity, $this->service->buildProjectPackage($mockProject, $mockPackage, 'version'));
    }

    /**
     * @test
     */
    public function buildProjectPackageDependency_ifExists()
    {
        $mockProjectPackageDependencyRepository = $this->getMockBuilder(ProjectPackageDependencyRepository::class)->disableOriginalConstructor()->getMock();
        $mockEntity = $this->getMockBuilder(ProjectPackageDependency::class)->disableOriginalConstructor()->getMock();

        $mockSource = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();
        $mockTarget = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(ProjectPackageDependency::class)->will($this->returnValue($mockProjectPackageDependencyRepository));

        $mockProjectPackageDependencyRepository->expects($this->at(0))->method('findOneBy')->with(['sourceProjectPackage' => $mockSource, 'targetProjectPackage' => $mockTarget])->will($this->returnValue($mockEntity));

        $mockEntity->expects($this->at(0))->method('setVersion')->with('version')->will($this->returnValue($mockEntity));
        $mockEntity->expects($this->at(1))->method('setDevelopment')->with(true)->will($this->returnValue($mockEntity));

        $this->mockEntityManager->expects($this->at(1))->method('persist')->with($mockEntity);
        $this->mockEntityManager->expects($this->at(2))->method('flush');

        $this->assertSame($mockEntity, $this->service->buildProjectPackageDependency($mockSource, $mockTarget, 'version', true));
    }

    /**
     * @test
     */
    public function buildProjectPackageDependency_ifNotExists()
    {
        $mockProjectPackageDependencyRepository = $this->getMockBuilder(ProjectPackageDependencyRepository::class)->disableOriginalConstructor()->getMock();
        $mockEntity = $this->getMockBuilder(ProjectPackageDependency::class)->disableOriginalConstructor()->getMock();

        $mockSource = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();
        $mockTarget = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();

        $this->mockEntityManager->expects($this->at(0))->method('getRepository')->with(ProjectPackageDependency::class)->will($this->returnValue($mockProjectPackageDependencyRepository));

        $mockProjectPackageDependencyRepository->expects($this->at(0))->method('findOneBy')->with(['sourceProjectPackage' => $mockSource, 'targetProjectPackage' => $mockTarget])->will($this->returnValue(null));

        $this->mockEntityFactory->expects($this->at(0))->method('createProjectPackageDependency')->will($this->returnValue($mockEntity));

        $mockEntity->expects($this->at(0))->method('setSourceProjectPackage')->with($mockSource)->will($this->returnValue($mockEntity));
        $mockEntity->expects($this->at(1))->method('setTargetProjectPackage')->with($mockTarget)->will($this->returnValue($mockEntity));
        $mockEntity->expects($this->at(2))->method('setVersion')->with('version')->will($this->returnValue($mockEntity));

        $this->mockEntityManager->expects($this->at(1))->method('persist')->with($mockEntity);
        $this->mockEntityManager->expects($this->at(2))->method('flush');

        $this->assertSame($mockEntity, $this->service->buildProjectPackageDependency($mockSource, $mockTarget, 'version'));
    }
}