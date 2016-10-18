<?php

namespace WebComposer\SynchronizationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WebComposer\SynchronizationBundle\Service\SynchronizationService;

class SynchronizeController extends Controller
{
    public function synchronizeAction($projectName)
    {
        /**
         * @var $synchronizer SynchronizationService
         */
        $synchronizer = $this->get('web_composer.synchronizer');

        $project = $synchronizer->findProject($projectName);
        $graph = $synchronizer->analyze($project);

        $packages = $synchronizer->synchronizePackages($graph);
        $projectPackages = $synchronizer->synchronizeProjectPackages($project,$graph);
        $dependencies = $synchronizer->synchronizeProjectPackageDependencies($project,$graph);
        $outdatedPackages = $synchronizer->synchronizePackageVersions($project);

        return $this->render('WebComposerSynchronizationBundle:Default:synchronize.html.twig',['packages' => $packages,'projectPackages' => $projectPackages, 'dependencies' => $dependencies,'outdated' => $outdatedPackages]);
    }
}
