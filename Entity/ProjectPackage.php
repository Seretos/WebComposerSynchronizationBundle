<?php
namespace WebComposer\SynchronizationBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="WebComposer\SynchronizationBundle\Repository\ProjectPackageRepository")
 */
class ProjectPackage
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
     * @ORM\OneToMany(
     *     targetEntity="WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency",
     *     mappedBy="sourceProjectPackage",
     *     cascade={"persist","remove"}
     * )
     */
    private $outDependencies;

    /**
     * @ORM\OneToMany(
     *     targetEntity="WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency",
     *     mappedBy="targetProjectPackage",
     *     cascade={"persist","remove"}
     * )
     */
    private $inDependencies;

    /**
     * @ORM\ManyToOne(targetEntity="WebComposer\SynchronizationBundle\Entity\Project", inversedBy="projectPackages")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="WebComposer\SynchronizationBundle\Entity\Package", cascade={"persist"})
     * @ORM\JoinColumn(name="package_id", referencedColumnName="id", nullable=false)
     */
    private $package;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->outDependencies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inDependencies = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @return ProjectPackage
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
     * Add outDependency
     *
     * @param \WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency $outDependency
     *
     * @return ProjectPackage
     */
    public function addOutDependency(\WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency $outDependency)
    {
        $this->outDependencies[] = $outDependency;

        return $this;
    }

    /**
     * Remove outDependency
     *
     * @param \WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency $outDependency
     */
    public function removeOutDependency(\WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency $outDependency)
    {
        $this->outDependencies->removeElement($outDependency);
    }

    /**
     * Get outDependencies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOutDependencies()
    {
        return $this->outDependencies;
    }

    /**
     * Add inDependency
     *
     * @param \WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency $inDependency
     *
     * @return ProjectPackage
     */
    public function addInDependency(\WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency $inDependency)
    {
        $this->inDependencies[] = $inDependency;

        return $this;
    }

    /**
     * Remove inDependency
     *
     * @param \WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency $inDependency
     */
    public function removeInDependency(\WebComposer\SynchronizationBundle\Entity\ProjectPackageDependency $inDependency)
    {
        $this->inDependencies->removeElement($inDependency);
    }

    /**
     * Get inDependencies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInDependencies()
    {
        return $this->inDependencies;
    }

    /**
     * Set project
     *
     * @param \WebComposer\SynchronizationBundle\Entity\Project $project
     *
     * @return ProjectPackage
     */
    public function setProject(\WebComposer\SynchronizationBundle\Entity\Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \WebComposer\SynchronizationBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set package
     *
     * @param \WebComposer\SynchronizationBundle\Entity\Package $package
     *
     * @return ProjectPackage
     */
    public function setPackage(\WebComposer\SynchronizationBundle\Entity\Package $package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * Get package
     *
     * @return \WebComposer\SynchronizationBundle\Entity\Package
     */
    public function getPackage()
    {
        return $this->package;
    }
}
