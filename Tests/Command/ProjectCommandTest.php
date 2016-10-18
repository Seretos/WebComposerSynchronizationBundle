<?php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WebComposer\SynchronizationBundle\Command\ProjectCommand;
use WebComposer\SynchronizationBundle\Exception\SynchronizationException;
use WebComposer\SynchronizationBundle\Service\SaveService;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 18.10.2016
 * Time: 08:18
 */
class ProjectCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ProjectCommand
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
        $this->command = new ProjectCommand();
        $this->command->setContainer($this->mockContainer);
        $this->assertSame('web-composer:create-project',$this->command->getName());
        $this->assertSame('create/edit projects',$this->command->getDescription());
        $this->assertSame('php bin/console web-composer:create-project projectName /path/to/project',$this->command->getHelp());
    }

    /**
     * @test
     */
    public function execute_withError(){
        $mockInput = $this->getMockBuilder(InputInterface::class)->disableOriginalConstructor()->getMock();
        $mockOutput = $this->getMockBuilder(OutputInterface::class)->disableOriginalConstructor()->getMock();

        $mockInput->expects($this->at(0))->method('getArgument')->with('projectName')->will($this->returnValue('test'));
        $mockInput->expects($this->at(1))->method('getArgument')->with('projectDirectory')->will($this->returnValue('test2'));

        $this->expectException(SynchronizationException::class);

        $this->command->execute($mockInput,$mockOutput);
    }

    /**
     * @test
     */
    public function execute_method(){
        $mockInput = $this->getMockBuilder(InputInterface::class)->disableOriginalConstructor()->getMock();
        $mockOutput = $this->getMockBuilder(OutputInterface::class)->disableOriginalConstructor()->getMock();
        $mockService = $this->getMockBuilder(SaveService::class)->disableOriginalConstructor()->getMock();

        $mockInput->expects($this->at(0))->method('getArgument')->with('projectName')->will($this->returnValue('test'));
        $mockInput->expects($this->at(1))->method('getArgument')->with('projectDirectory')->will($this->returnValue(__DIR__));

        $this->mockContainer->expects($this->at(0))->method('get')->with('web_composer.save_service')->will($this->returnValue($mockService));
        $mockService->expects($this->at(0))->method('buildProject')->with('test',__DIR__);

        $this->command->execute($mockInput,$mockOutput);
    }
}