<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 17.10.2016
 * Time: 02:38
 */

namespace WebComposer\SynchronizationBundle\Repository;


use Doctrine\ORM\EntityRepository;
use WebComposer\SynchronizationBundle\Entity\Package;
use WebComposer\SynchronizationBundle\Entity\Project;
use WebComposer\SynchronizationBundle\Entity\ProjectPackage;
use WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency;

class ProjectPackageRepository extends EntityRepository
{
    /**
     * @param Project $project
     * @param ProjectPackage[] $usedPackages
     */
    public function removeUnusedPackages(Project $project, array $usedPackages)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->delete(ProjectPackage::class, 'pp')
            ->where('pp.project = :project')
            ->andWhere('pp.id NOT IN(:packages)')
            ->getQuery();

        $query->setParameter('project', $project);
        $query->setParameter('packages', $usedPackages);
        $query->execute();
    }

    /**
     * @param Project $project
     * @param $packageName
     * @return ProjectPackage
     */
    public function findOneByProjectAndPackageName(Project $project, $packageName)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('pp')
            ->from(ProjectPackage::class, 'pp')
            ->innerJoin(Package::class, 'p', 'WITH', 'p.id = pp.package')
            ->where('pp.project = :project')
            ->andWhere('p.name = :name')
            ->getQuery();

        $query->setParameter('project', $project);
        $query->setParameter('name', $packageName);

        return $query->getOneOrNullResult();
    }

    public function isDevelopmentProjectPackage(ProjectPackage $package)
    {
        if ($package->getProject()->getRootProjectPackage() == $package) {
            return false;
        }
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('pp')
            ->from(ProjectPackage::class, 'pp')
            ->innerJoin(ProjectPackageDependency::class, 'ppd', 'WITH', 'ppd.targetProjectPackage = pp.id')
            ->where('pp = :package')
            ->andWhere('ppd.development = :development')
            ->getQuery();

        $query->setParameter('package', $package);
        $query->setParameter('development', false);

        if (count($query->getResult()) > 0) {
            return false;
        }
        return true;
    }
}