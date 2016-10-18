<?php
namespace WebComposer\SynchronizationBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="WebComposer\SynchronizationBundle\Repository\ProjectPackageDependencyRepository")
 */
class ProjectPackageDependency
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $version;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     */
    private $development;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="WebComposer\SynchronizationBundle\Entity\ProjectPackage",
     *     inversedBy="outDependencies"
     * )
     * @ORM\JoinColumn(name="source_project_package_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $sourceProjectPackage;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="WebComposer\SynchronizationBundle\Entity\ProjectPackage",
     *     inversedBy="inDependencies"
     * )
     * @ORM\JoinColumn(name="target_project_package_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $targetProjectPackage;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set version
     *
     * @param string $version
     *
     * @return ProjectPackageDependency
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set sourceProjectPackage
     *
     * @param \WebComposer\SynchronizationBundle\Entity\ProjectPackage $sourceProjectPackage
     *
     * @return ProjectPackageDependency
     */
    public function setSourceProjectPackage(\WebComposer\SynchronizationBundle\Entity\ProjectPackage $sourceProjectPackage)
    {
        $this->sourceProjectPackage = $sourceProjectPackage;

        return $this;
    }

    /**
     * Get sourceProjectPackage
     *
     * @return \WebComposer\SynchronizationBundle\Entity\ProjectPackage
     */
    public function getSourceProjectPackage()
    {
        return $this->sourceProjectPackage;
    }

    /**
     * Set targetProjectPackage
     *
     * @param \WebComposer\SynchronizationBundle\Entity\ProjectPackage $targetProjectPackage
     *
     * @return ProjectPackageDependency
     */
    public function setTargetProjectPackage(\WebComposer\SynchronizationBundle\Entity\ProjectPackage $targetProjectPackage)
    {
        $this->targetProjectPackage = $targetProjectPackage;

        return $this;
    }

    /**
     * Get targetProjectPackage
     *
     * @return \WebComposer\SynchronizationBundle\Entity\ProjectPackage
     */
    public function getTargetProjectPackage()
    {
        return $this->targetProjectPackage;
    }

    /**
     * Set development
     *
     * @param boolean $development
     *
     * @return ProjectPackageDependency
     */
    public function setDevelopment($development)
    {
        $this->development = $development;

        return $this;
    }

    /**
     * Get development
     *
     * @return boolean
     */
    public function getDevelopment()
    {
        return $this->development;
    }
}
