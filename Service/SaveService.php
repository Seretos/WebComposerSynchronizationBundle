<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 17:17
 */

namespace WebComposer\SynchronizationBundle\Service;


use Doctrine\ORM\EntityManager;
use WebComposer\SynchronizationBundle\Entity\Package;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;

class SaveService
{
    private $factory;
    private $manager;

    public function __construct(ServiceEntityFactory $factory, EntityManager $manager)
    {
        $this->factory = $factory;
        $this->manager = $manager;
    }

    public function buildProject($name, $directory)
    {
        $project = $this->manager->getRepository(Project::class)->findOneBy(['name' => $name]);
        if ($project == null) {
            $project = $this->factory->createProject();
        }
        $project->setName($name);
        $project->setDirectory($directory);

        $this->manager->persist($project);
        $this->manager->flush();

        return $project;
    }

    public function buildPackage($name, $repository = null)
    {
        $package = $this->manager->getRepository(Package::class)->findOneBy(['name' => $name]);
        if ($package == null) {
            $package = $this->factory->createPackage();
            $package->setName($name);
            $package->setRepository($repository);
            $this->manager->persist($package);
            $this->manager->flush();
        }

        return $package;
    }

    public function buildProjectPackage(Project $project, Package $package, $version = null)
    {
        $projectPackage = $this->manager->getRepository(ProjectPackage::class)->findOneBy(['project' => $project, 'package' => $package]);
        if ($projectPackage == null) {
            $projectPackage = $this->factory->createProjectPackage();
            $projectPackage->setPackage($package);
            $projectPackage->setProject($project);

            $project->addProjectPackage($projectPackage);
        }
        $projectPackage->setVersion($version);

        $this->manager->persist($projectPackage);
        $this->manager->flush();

        return $projectPackage;
    }

    public function buildProjectPackageDependency(ProjectPackage $source, ProjectPackage $target, $version = null, $development = false)
    {
        $projectPackageDependency = $this->manager->getRepository(ProjectPackageDependency::class)->findOneBy(['sourceProjectPackage' => $source, 'targetProjectPackage' => $target]);
        if ($projectPackageDependency == null) {
            $projectPackageDependency = $this->factory->createProjectPackageDependency();
            $projectPackageDependency->setSourceProjectPackage($source);
            $projectPackageDependency->setTargetProjectPackage($target);
        }
        $projectPackageDependency->setVersion($version);
        $projectPackageDependency->setDevelopment($development);
        $this->manager->persist($projectPackageDependency);
        $this->manager->flush();

        return $projectPackageDependency;
    }

}