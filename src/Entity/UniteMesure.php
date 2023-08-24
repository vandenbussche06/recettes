<?php

namespace App\Entity;

use App\Repository\UniteMesureRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UniteMesureRepository::class)
 */
class UniteMesure
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nomAbrege;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomAbrege(): ?string
    {
        return $this->nomAbrege;
    }

    public function setNomAbrege(string $nomAbrege): self
    {
        $this->nomAbrege = $nomAbrege;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }
}
