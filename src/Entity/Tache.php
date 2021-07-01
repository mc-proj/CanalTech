<?php

namespace App\Entity;

use App\Repository\TacheRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TacheRepository::class)
 */
class Tache
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez entrer un intitulé")
     */
    private $intitule;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull(message="Veuillez entrer une date et heure de début")
     * @Assert\LessThan(propertyPath="date_fin", message="La date de début doit etre avant la date de fin")
     */
    private $date_debut;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\GreaterThan(propertyPath="date_debut", message="La date de fin doit etre après la date de début")
     */
    private $date_fin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $est_facture;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="taches")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(message="Veuillez choisir un projet")
     * @Assert\NotNull(message="Veuillez choisir un projet")
     */
    private $projet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): self
    {
        $this->intitule = $intitule;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(?\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getEstFacture(): ?bool
    {
        return $this->est_facture;
    }

    public function setEstFacture(bool $est_facture): self
    {
        $this->est_facture = $est_facture;

        return $this;
    }

    public function getProjet(): ?projet
    {
        return $this->projet;
    }

    public function setProjet(?projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }
}
