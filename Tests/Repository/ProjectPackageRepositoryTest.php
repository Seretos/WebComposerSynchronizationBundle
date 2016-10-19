<?php
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use WebComposer\SynchronizationBundle\Entity\Package;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;
use WebComposer\SynchronizationBundle\Repository\ProjectPackageRepository;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 23:17
 */
class ProjectPackageRepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ProjectPackageRepository
     */
    private $repository;
    /**
     * @var EntityManager|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockManager;

    protected function setUp()
    {
        parent::setUp();
        $this->mockManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $mockMetaData = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $this->repository = new ProjectPackageRepository($this->mockManager, $mockMetaData);
    }

    /**
     * @test
     */
    public function removeUnusedPackages()
    {
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $usedPackages = [];
        $usedPackages[] = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();

        $mockQueryBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $this->mockManager->expects($this->at(0))->method('createQueryBuilder')->will($this->returnValue($mockQueryBuilder));

        $mockQueryBuilder->expects($this->at(0))->method('delete')->with(ProjectPackage::class, 'pp')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(1))->method('where')->with('pp.project = :project')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(2))->method('andWhere')->with('pp.id NOT IN(:packages)')->will($this->returnValue($mockQueryBuilder));

        $mockQuery = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();
        $mockQueryBuilder->expects($this->at(3))->method('getQuery')->will($this->returnValue($mockQuery));

        $mockQuery->expects($this->at(0))->method('setParameter')->with('project', $mockProject)->will($this->returnValue($mockQuery));
        $mockQuery->expects($this->at(1))->method('setParameter')->with('packages', $usedPackages)->will($this->returnValue($mockQuery));

        $mockQuery->expects($this->at(2))->method('execute');

        $this->repository->removeUnusedPackages($mockProject, $usedPackages);
    }

    /**
     * @test
     */
    public function findOneByProjectAndPackageName()
    {
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();

        $mockQueryBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $this->mockManager->expects($this->at(0))->method('createQueryBuilder')->will($this->returnValue($mockQueryBuilder));

        $mockQueryBuilder->expects($this->at(0))->method('select')->with('pp')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(1))->method('from')->with(ProjectPackage::class, 'pp')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(2))->method('innerJoin')->with(Package::class, 'p', 'WITH', 'p.id = pp.package')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(3))->method('where')->with('pp.project = :project')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(4))->method('andWhere')->with('p.name = :name')->will($this->returnValue($mockQueryBuilder));

        $mockQuery = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();

        $mockQueryBuilder->expects($this->at(5))->method('getQuery')->will($this->returnValue($mockQuery));

        $mockQuery->expects($this->at(0))->method('setParameter')->with('project', $mockProject)->will($this->returnValue($mockQuery));
        $mockQuery->expects($this->at(1))->method('setParameter')->with('name', 'test')->will($this->returnValue($mockQuery));

        $mockQuery->expects($this->at(2))->method('getOneOrNullResult')->will($this->returnValue('success'));

        $this->assertSame('success', $this->repository->findOneByProjectAndPackageName($mockProject, 'test'));
    }

    /**
     * @test
     */
    public function isDevelopmentProjectPackage_withRootPackage()
    {
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $mockProjectPackage = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();

        $mockProject->expects($this->at(0))->method('getRootProjectPackage')->will($this->returnValue($mockProjectPackage));
        $mockProjectPackage->expects($this->at(0))->method('getProject')->will($this->returnValue($mockProject));

        $this->assertSame(false, $this->repository->isDevelopmentProjectPackage($mockProjectPackage));
    }

    /**
     * @test
     */
    public function isDevelopmentProjectPackage_true()
    {
        $mockProjectPackage = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();
        $this->createIsDevelopmentProjectPackageMock($mockProjectPackage);
        $this->assertSame(true, $this->repository->isDevelopmentProjectPackage($mockProjectPackage));
    }

    /**
     * @test
     */
    public function isDevelopmentProjectPackage_false()
    {
        $mockProjectPackage = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();
        $this->createIsDevelopmentProjectPackageMock($mockProjectPackage, [1]);
        $this->assertSame(false, $this->repository->isDevelopmentProjectPackage($mockProjectPackage));
    }

    private function createIsDevelopmentProjectPackageMock(PHPUnit_Framework_MockObject_MockObject $mockProjectPackage, $result = [])
    {
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $mockRootProjectPackage = $this->getMockBuilder(ProjectPackage::class)->disableOriginalConstructor()->getMock();
        $mockQueryBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $mockQuery = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();

        $mockProject->expects($this->at(0))->method('getRootProjectPackage')->will($this->returnValue($mockRootProjectPackage));
        $mockProjectPackage->expects($this->at(0))->method('getProject')->will($this->returnValue($mockProject));

        $mockQueryBuilder->expects($this->at(0))->method('select')->with('pp')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(1))->method('from')->with(ProjectPackage::class, 'pp')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(2))->method('innerJoin')->with(ProjectPackageDependency::class, 'ppd', 'WITH', 'ppd.targetProjectPackage = pp.id')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(3))->method('where')->with('pp = :package')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(4))->method('andWhere')->with('ppd.development = :development')->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(5))->method('getQuery')->will($this->returnValue($mockQuery));

        $mockQuery->expects($this->at(0))->method('setParameter')->with('package', $mockProjectPackage)->will($this->returnValue($mockQuery));
        $mockQuery->expects($this->at(1))->method('setParameter')->with('development', false)->will($this->returnValue($mockQuery));
        $mockQuery->expects($this->at(2))->method('getResult')->will($this->returnValue($result));

        $this->mockManager->expects($this->at(0))->method('createQueryBuilder')->will($this->returnValue($mockQueryBuilder));
    }
}