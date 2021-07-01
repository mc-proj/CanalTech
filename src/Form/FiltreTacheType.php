<?php

namespace App\Form;

use App\Entity\Tache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use App\Entity\Projet;

class FiltreTacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date_debut', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'label' => 'Date début :',
                'input' => 'datetime',
                'empty_data' => '',

            ])
            ->add('date_fin', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'label' => 'Date fin :',
                'input' => 'datetime',
            ])
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'nom', //le champ nom de l'entite Projet sera affiché
                'label' => "Projet :",
                'placeholder' => 'Tous'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}
