<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 18:08
 */

namespace WebComposer\SynchronizationBundle\Repository;


use Doctrine\ORM\EntityRepository;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;

class ProjectPackageDependencyRepository extends EntityRepository
{
    /**
     * @param Project $project
     * @param ProjectPackageDependency[] $usedDependencies
     */
    public function removeUnusedDependencies(Project $project, array $usedDependencies)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ppd')
            ->from(ProjectPackageDependency::class, 'ppd')
            ->innerJoin(ProjectPackage::class, 'pp', 'WITH', 'pp.id = ppd.sourceProjectPackage')
            ->where('pp.project = :project')
            ->andWhere('ppd.id NOT IN(:dependencies)')
            ->getQuery();

        $query->setParameter('project', $project);
        $query->setParameter('dependencies', $usedDependencies);

        $deleteQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->delete(ProjectPackageDependency::class, 'ppd')
            ->where('ppd.id IN(:unusedDependencies)')
            ->getQuery();
        $deleteQuery->setParameter('unusedDependencies', $query->getResult());
        $deleteQuery->execute();
    }

    /**
     * @param Project $project
     * @return ProjectPackageDependency[]
     */
    public function findByProject(Project $project)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ppd')
            ->from(ProjectPackageDependency::class, 'ppd')
            ->innerJoin(ProjectPackage::class, 'pp', 'WITH', 'pp.id = ppd.sourceProjectPackage')
            ->where('pp.project = :project')
            ->getQuery();

        $query->setParameter('project', $project);

        return $query->getResult();
    }
}