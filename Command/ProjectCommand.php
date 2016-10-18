<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 18.10.2016
 * Time: 07:48
 */

namespace WebComposer\SynchronizationBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WebComposer\SynchronizationBundle\Exception\SynchronizationException;
use WebComposer\SynchronizationBundle\Service\SaveService;

class ProjectCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    protected function configure(){
        $this->setName('web-composer:create-project')
            ->setDescription('create/edit projects')
            ->setHelp('php bin/console web-composer:create-project projectName /path/to/project')
            ->addArgument('projectName', InputArgument::REQUIRED, 'The name of the project')
            ->addArgument('projectDirectory', InputArgument::REQUIRED, 'The local path to the project');
    }

    public function execute(InputInterface $input, OutputInterface $output){
        $projectName = $input->getArgument('projectName');
        $projectDirectory = $input->getArgument('projectDirectory');

        if(!file_exists($projectDirectory)){
            throw new SynchronizationException('project directory does not exist!');
        }

        /**
         * @var $saveService SaveService
         */
        $saveService = $this->container->get('web_composer.save_service');
        $saveService->buildProject($projectName,$projectDirectory);
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}