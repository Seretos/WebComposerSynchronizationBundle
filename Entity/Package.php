<?php
namespace WebComposer\SynchronizationBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 */
class Package
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $repository;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     */
    private $extern;

    /**
     * 
     */
    private $QRCodeImage;

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
     * @return Package
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
     * Set repository
     *
     * @param string $repository
     *
     * @return Package
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Get repository
     *
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set extern
     *
     * @param boolean $extern
     *
     * @return Package
     */
    public function setExtern($extern)
    {
        $this->extern = $extern;

        return $this;
    }

    /**
     * Get extern
     *
     * @return boolean
     */
    public function getExtern()
    {
        return $this->extern;
    }

    /**
     * Set qRCodeImage
     *
     * @param string $qRCodeImage
     *
     * @return Package
     */
    public function setQRCodeImage($qRCodeImage)
    {
        $this->QRCodeImage = $qRCodeImage;

        return $this;
    }

    /**
     * Get qRCodeImage
     *
     * @return string
     */
    public function getQRCodeImage()
    {
        return $this->QRCodeImage;
    }
}
