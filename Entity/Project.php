<?php
namespace WebComposer\SynchronizationBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, length=255, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    private $directory;

    /**
     * @ORM\OneToMany(
     *     targetEntity="WebComposer\SynchronizationBundle\Entity\ProjectPackage",
     *     mappedBy="project",
     *     cascade={"persist"}
     * )
     */
    private $projectPackages;

    /**
     * @ORM\ManyToOne(targetEntity="WebComposer\SynchronizationBundle\Entity\ProjectPackage")
     * @ORM\JoinColumn(name="project_package_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $rootProjectPackage;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->projectPackages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set directory
     *
     * @param string $directory
     *
     * @return Project
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get directory
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Add projectPackage
     *
     * @param \WebComposer\SynchronizationBundle\Entity\ProjectPackage $projectPackage
     *
     * @return Project
     */
    public function addProjectPackage(\WebComposer\SynchronizationBundle\Entity\ProjectPackage $projectPackage)
    {
        $this->projectPackages[] = $projectPackage;

        return $this;
    }

    /**
     * Remove projectPackage
     *
     * @param \WebComposer\SynchronizationBundle\Entity\ProjectPackage $projectPackage
     */
    public function removeProjectPackage(\WebComposer\SynchronizationBundle\Entity\ProjectPackage $projectPackage)
    {
        $this->projectPackages->removeElement($projectPackage);
    }

    /**
     * Get projectPackages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjectPackages()
    {
        return $this->projectPackages;
    }

    /**
     * Set rootProjectPackage
     *
     * @param \WebComposer\SynchronizationBundle\Entity\ProjectPackage $rootProjectPackage
     *
     * @return Project
     */
    public function setRootProjectPackage(\WebComposer\SynchronizationBundle\Entity\ProjectPackage $rootProjectPackage = null)
    {
        $this->rootProjectPackage = $rootProjectPackage;

        return $this;
    }

    /**
     * Get rootProjectPackage
     *
     * @return \WebComposer\SynchronizationBundle\Entity\ProjectPackage
     */
    public function getRootProjectPackage()
    {
        return $this->rootProjectPackage;
    }
}
