<?php
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use WebComposer\SynchronizationBundle\DataFixtures\ORM\DemoProjectFixture;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Service\ServiceEntityFactory;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 22:26
 */
class DemoProjectFixtureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DemoProjectFixture
     */
    private $fixture;
    /**
     * @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContainer;

    protected function setUp()
    {
        parent::setUp();
        $this->mockContainer = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
        $this->fixture = new DemoProjectFixture();
        $this->fixture->setContainer($this->mockContainer);
    }

    /**
     * @test
     */
    public function load_ifExists(){
        $projectPath = realpath(__DIR__.'/../');
        $mockManager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $mockProjectRepository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();

        $mockManager->expects($this->at(0))->method('getRepository')->with(Project::class)->will($this->returnValue($mockProjectRepository));
        $mockProjectRepository->expects($this->at(0))->method('findOneBy')->with(['name' => basename($projectPath)])->will($this->returnValue($mockProject));

        $mockKernel = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $mockKernel->expects($this->at(0))->method('getRootDir')->will($this->returnValue(__DIR__));
        $this->mockContainer->expects($this->at(0))->method('get')->with('kernel')->will($this->returnValue($mockKernel));

        $mockProject->expects($this->at(0))->method('setName')->with(basename($projectPath));
        $mockProject->expects($this->at(1))->method('setDirectory')->with($projectPath);

        $mockManager->expects($this->at(1))->method('persist')->with($mockProject);
        $mockManager->expects($this->at(2))->method('flush');

        $this->fixture->load($mockManager);
    }

    /**
     * @test
     */
    public function load_ifNotExists(){
        $projectPath = realpath(__DIR__.'/../');
        $mockManager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $mockProjectRepository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $mockProject = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();

        $mockManager->expects($this->at(0))->method('getRepository')->with(Project::class)->will($this->returnValue($mockProjectRepository));
        $mockProjectRepository->expects($this->at(0))->method('findOneBy')->with(['name' => basename($projectPath)])->will($this->returnValue(null));

        $mockKernel = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $mockKernel->expects($this->at(0))->method('getRootDir')->will($this->returnValue(__DIR__));
        $this->mockContainer->expects($this->at(0))->method('get')->with('kernel')->will($this->returnValue($mockKernel));

        $mockEntityFactory = $this->getMockBuilder(ServiceEntityFactory::class)->disableOriginalConstructor()->getMock();
        $this->mockContainer->expects($this->at(1))->method('get')->with('web_composer.entity_factory')->will($this->returnValue($mockEntityFactory));
        $mockEntityFactory->expects($this->at(0))->method('createProject')->will($this->returnValue($mockProject));

        $mockProject->expects($this->at(0))->method('setName')->with(basename($projectPath));
        $mockProject->expects($this->at(1))->method('setDirectory')->with($projectPath);

        $mockManager->expects($this->at(1))->method('persist')->with($mockProject);
        $mockManager->expects($this->at(2))->method('flush');

        $this->fixture->load($mockManager);
    }
}