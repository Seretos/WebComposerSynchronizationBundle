<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 01:40
 */

namespace WebComposer\SynchronizationBundle\Service;

use Doctrine\ORM\EntityManager;
use JMS\Composer\DependencyAnalyzer;
use JMS\Composer\Graph\DependencyGraph;
use WebComposer\SynchronizationBundle\Entity\Package;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;
use WebComposer\SynchronizationBundle\Exception\SynchronizationException;
use WebComposer\SynchronizationBundle\Repository\ProjectPackageDependencyRepository;
use WebComposer\SynchronizationBundle\Repository\ProjectPackageRepository;

class SynchronizationService
{
    private $analyzer;
    private $manager;
    private $projectService;
    private $factory;

    public function __construct(DependencyAnalyzer $analyzer, EntityManager $manager, SaveService $projectService, ServiceEntityFactory $factory)
    {
        $this->analyzer = $analyzer;
        $this->manager = $manager;
        $this->projectService = $projectService;
        $this->factory = $factory;
    }

    /**
     * @param $projectName
     * @return Project
     * @throws SynchronizationException
     */
    public function findProject($projectName){
        $project = $this->manager->getRepository(Project::class)->findOneBy(['name' => $projectName]);

        if($project == null){
            throw new SynchronizationException('project with name '.$projectName.' not found in projects table!');
        }
        return $project;
    }

    public function analyze(Project $project){
        return $this->analyzer->analyze($project->getDirectory());
    }

    /**
     * @param DependencyGraph $graph
     * @return Package[]
     */
    public function synchronizePackages(DependencyGraph $graph){
        $packages = [];

        foreach($graph->getPackages() as $package){
            $repository = null;
            $data = $package->getData();
            if(isset($data['source']) && $data['source']['type'] == 'git'){
                $repository = $data['source']['url'];
            }
            $packages[] = $this->projectService->buildPackage($package->getName(),$package->getVersion(),$repository);
        }
        return $packages;
    }

    /**
     * @param Project $project
     * @param DependencyGraph $graph
     * @return ProjectPackage[]
     */
    public function synchronizeProjectPackages(Project $project, DependencyGraph $graph){
        /**
         * @var $projectPackageRepository ProjectPackageRepository
         */
        $projectPackageRepository = $this->manager->getRepository(ProjectPackage::class);
        $packageRepository = $this->manager->getRepository(Package::class);

        $used = [];
        foreach($graph->getPackages() as $graphPackage){
            $package = $packageRepository->findOneBy(['name' => $graphPackage->getName()]);
            $projectPackage = $this->projectService->buildProjectPackage($project, $package, $graphPackage->getVersion());
            if($graph->getRootPackage() == $graphPackage){
                $project->setRootProjectPackage($projectPackage);
                $this->manager->persist($project);
                $this->manager->flush();
            }
            $used[] = $projectPackage;
        }

        $projectPackageRepository->removeUnusedPackages($project,$used);
        return $used;
    }

    /**
     * @param Project $project
     * @param DependencyGraph $graph
     * @return ProjectPackageDependency[]
     */
    public function synchronizeProjectPackageDependencies(Project $project, DependencyGraph $graph){
        /**
         * @var $projectPackageRepository ProjectPackageRepository
         * @var $projectPackageDependencyRepository ProjectPackageDependencyRepository
         */
        $projectPackageRepository = $this->manager->getRepository(ProjectPackage::class);
        $projectPackageDependencyRepository = $this->manager->getRepository(ProjectPackageDependency::class);

        $used = [];
        foreach($graph->getPackages() as $graphPackage){
            $source = $projectPackageRepository->findOneByProjectAndPackageName($project,$graphPackage->getName());
            foreach($graphPackage->getOutEdges() as $edge){
                $target = $projectPackageRepository->findOneByProjectAndPackageName($project,$edge->getDestPackage()->getName());
                $used[] = $this->projectService->buildProjectPackageDependency($source,$target,$edge->getVersionConstraint(),$edge->isDevDependency());
            }
        }

        $projectPackageDependencyRepository->removeUnusedDependencies($project,$used);
        return $used;
    }

    /**
     * @param Project $project
     * @return Package[]
     */
    public function synchronizePackageVersions(Project $project){
        $process = $this->factory->createProcess('php composer.phar outdated');

        $process->setWorkingDirectory($project->getDirectory());
        $process->run();

        $result = explode("\n",$process->getOutput());

        $packages = [];
        foreach($result as $res){
            $output_array = preg_split("/\s+/",$res);
            if(count($output_array)>1 && strlen($output_array[0])>0) {
                /**
                 * @var $package Package
                 */
                $package = $this->manager->getRepository(Package::class)->findOneBy(['name' => $output_array[0]]);
                $package->setMaxVersion($output_array[2]);
                $this->manager->persist($package);
                $packages[] = $package;
            }
        }
        $this->manager->flush();

        return $packages;
    }
}