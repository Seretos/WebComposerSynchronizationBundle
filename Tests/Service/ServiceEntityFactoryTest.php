<?php
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Process\Process;
use WebComposer\SynchronizationBundle\Entity\Package;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;
use WebComposer\SynchronizationBundle\Service\ServiceEntityFactory;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 19:15
 */
class ServiceEntityFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceEntityFactory
     */
    private $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->factory = new ServiceEntityFactory();
    }

    /**
     * @test
     */
    public function createProject()
    {
        $entity = $this->factory->createProject();
        $this->assertInstanceOf(Project::class, $entity);
        $this->assertSame(null, $entity->getId());
        $this->assertSame(null, $entity->getDirectory());
        $this->assertSame(null, $entity->getName());
        $this->assertSame(null, $entity->getRootProjectPackage());
        $this->assertInstanceOf(ArrayCollection::class, $entity->getProjectPackages());
        $this->assertSame(0, $entity->getProjectPackages()->count());
    }

    /**
     * @test
     */
    public function createPackage()
    {
        $entity = $this->factory->createPackage();
        $this->assertInstanceOf(Package::class, $entity);
        $this->assertSame(null, $entity->getId());
        $this->assertSame(null, $entity->getRepository());
        $this->assertSame(null, $entity->getExtern());
        $this->assertSame(null, $entity->getName());
        $this->assertSame(null, $entity->getQRCodeImage());
    }

    /**
     * @test
     */
    public function createProjectPackage()
    {
        $entity = $this->factory->createProjectPackage();
        $this->assertInstanceOf(ProjectPackage::class, $entity);
        $this->assertInstanceOf(ArrayCollection::class, $entity->getInDependencies());
        $this->assertInstanceOf(ArrayCollection::class, $entity->getOutDependencies());
        $this->assertSame(null, $entity->getId());
        $this->assertSame(0, $entity->getInDependencies()->count());
        $this->assertSame(0, $entity->getOutDependencies()->count());
        $this->assertSame(null, $entity->getPackage());
        $this->assertSame(null, $entity->getProject());
        $this->assertSame(null, $entity->getVersion());
        $this->assertSame(null, $entity->getMaxVersion());
    }

    /**
     * @test
     */
    public function createProjectPackageDependency()
    {
        $entity = $this->factory->createProjectPackageDependency();
        $this->assertInstanceOf(ProjectPackageDependency::class, $entity);
        $this->assertSame(null, $entity->getId());
        $this->assertSame(null, $entity->getSourceProjectPackage());
        $this->assertSame(null, $entity->getTargetProjectPackage());
        $this->assertSame(null, $entity->getVersion());
    }

    /**
     * @test
     */
    public function createProcess()
    {
        $entity = $this->factory->createProcess('test');
        $this->assertInstanceOf(Process::class, $entity);
        $this->assertSame('test', $entity->getCommandLine());
    }
}