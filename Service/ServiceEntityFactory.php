<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 02:34
 */

namespace WebComposer\SynchronizationBundle\Service;


use Symfony\Component\Process\Process;
use WebComposer\SynchronizationBundle\Entity\Package;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;

class ServiceEntityFactory
{
    public function createProject(){
        return new Project();
    }

    public function createPackage(){
        return new Package();
    }

    public function createProjectPackage(){
        return new ProjectPackage();
    }

    public function createProjectPackageDependency(){
        return new ProjectPackageDependency();
    }

    public function createProcess($process){
        return new Process($process);
    }
}