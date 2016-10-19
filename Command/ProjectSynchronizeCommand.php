<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 18.10.2016
 * Time: 19:04
 */

namespace WebComposer\SynchronizationBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WebComposer\SynchronizationBundle\Service\SynchronizationService;

class ProjectSynchronizeCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    protected function configure()
    {
        $this->setName('web-composer:synchronize-project')
            ->setDescription('synchronize an project')
            ->setHelp('php bin/console web-composer:synchronize-project projectName')
            ->addArgument('projectName', InputArgument::REQUIRED, 'The name of the project');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $projectName = $input->getArgument('projectName');

        /**
         * @var $synchronizer SynchronizationService
         */
        $synchronizer = $this->container->get('web_composer.synchronizer');

        $project = $synchronizer->findProject($projectName);
        $graph = $synchronizer->analyze($project);

        $packages = $synchronizer->synchronizePackages($graph);
        foreach ($packages as $package) {
            $output->writeln('update package ' . $package->getName());
        }

        $projectPackages = $synchronizer->synchronizeProjectPackages($project, $graph);
        foreach ($projectPackages as $package) {
            $output->writeln('update project package ' . $package->getPackage()->getName() . ' - ' . $package->getVersion());
        }

        $dependencies = $synchronizer->synchronizeProjectPackageDependencies($project, $graph);
        foreach ($dependencies as $dependency) {
            $output->writeln('update project dependency ' . $dependency->getSourceProjectPackage()->getPackage()->getName() . ' - ' . $dependency->getTargetProjectPackage()->getPackage()->getName() . ' - ' . $dependency->getVersion());
        }

        $outdatedPackages = $synchronizer->synchronizePackageVersions($project);
        foreach ($outdatedPackages as $package) {
            $output->writeln('updated outdated package ' . $package->getPackage()->getName() . ' - ' . $package->getMaxVersion());
        }
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}