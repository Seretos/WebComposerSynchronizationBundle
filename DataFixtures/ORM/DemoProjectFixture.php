<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 01:52
 */

namespace WebComposer\SynchronizationBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WebComposer\SynchronizationBundle\Entity\Project;

class DemoProjectFixture implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $projectPath = realpath($this->container->get('kernel')->getRootDir().'/../');
        $project = $manager->getRepository(Project::class)->findOneBy(['name' => basename($projectPath)]);
        if($project == null){
            $project = $this->container->get('web_composer.entity_factory')->createProject();
        }
        $project->setName(basename($projectPath));
        $project->setDirectory($projectPath);

        $manager->persist($project);
        $manager->flush();
    }
}