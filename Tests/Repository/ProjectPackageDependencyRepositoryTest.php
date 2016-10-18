<?php
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;
use WebComposer\SynchronizationBundle\Repository\ProjectPackageDependencyRepository;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 23:36
 */
class ProjectPackageDependencyRepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ProjectPackageDependencyRepository
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
        $this->repository = new ProjectPackageDependencyRepository($this->mockManager,$mockMetaData);
    }

    /**
     * @test
     */
    public function removeUnusedDependencies(){
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();
        $dependencies = [];
        $dependencies[] = $this->getMockBuilder(ProjectPackageDependency::class)->disableOriginalConstructor()->getMock();

        $mockSelectQueryBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $this->mockManager->expects($this->at(0))->method('createQueryBuilder')->will($this->returnValue($mockSelectQueryBuilder));

        $mockSelectQueryBuilder->expects($this->at(0))->method('select')->with('ppd')->will($this->returnValue($mockSelectQueryBuilder));
        $mockSelectQueryBuilder->expects($this->at(1))->method('from')->with(ProjectPackageDependency::class,'ppd')->will($this->returnValue($mockSelectQueryBuilder));
        $mockSelectQueryBuilder->expects($this->at(2))->method('innerJoin')->with(ProjectPackage::class,'pp','WITH','pp.id = ppd.sourceProjectPackage')->will($this->returnValue($mockSelectQueryBuilder));
        $mockSelectQueryBuilder->expects($this->at(3))->method('where')->with('pp.project = :project')->will($this->returnValue($mockSelectQueryBuilder));
        $mockSelectQueryBuilder->expects($this->at(4))->method('andWhere')->with('ppd.id NOT IN(:dependencies)')->will($this->returnValue($mockSelectQueryBuilder));

        $mockSelectQuery = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();
        $mockSelectQueryBuilder->expects($this->at(5))->method('getQuery')->will($this->returnValue($mockSelectQuery));

        $mockSelectQuery->expects($this->at(0))->method('setParameter')->with('project',$mockProject)->will($this->returnValue($mockSelectQuery));
        $mockSelectQuery->expects($this->at(1))->method('setParameter')->with('dependencies',$dependencies)->will($this->returnValue($mockSelectQuery));
        $mockSelectQuery->expects($this->at(2))->method('getResult')->will($this->returnValue('unused'));

        $mockDeleteQueryBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $this->mockManager->expects($this->at(1))->method('createQueryBuilder')->will($this->returnValue($mockDeleteQueryBuilder));

        $mockDeleteQueryBuilder->expects($this->at(0))->method('delete')->with(ProjectPackageDependency::class,'ppd')->will($this->returnValue($mockDeleteQueryBuilder));
        $mockDeleteQueryBuilder->expects($this->at(1))->method('where')->with('ppd.id IN(:unusedDependencies)')->will($this->returnValue($mockDeleteQueryBuilder));

        $mockDeleteQuery = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();
        $mockDeleteQueryBuilder->expects($this->at(2))->method('getQuery')->will($this->returnValue($mockDeleteQuery));

        $mockDeleteQuery->expects($this->at(0))->method('setParameter')->with('unusedDependencies','unused')->will($this->returnValue($mockDeleteQuery));
        $mockDeleteQuery->expects($this->at(1))->method('execute');

        $this->repository->removeUnusedDependencies($mockProject,$dependencies);
    }
}