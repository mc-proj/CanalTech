<?php

namespace App\Form;

use App\Entity\Tache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use App\Entity\Projet;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
//use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


//possibilite d'utiliser $options pr champ sur condition => 1 seul type pr taches
//https://ourcodeworld.com/articles/read/510/how-to-programatically-remove-a-field-from-a-symfony-3-form-form-types
//  !! symfo 3

//https://openclassrooms.com/forum/sujet/champs-imbriques

class FiltreTacheType extends AbstractType
{
    /*public function __construct() {
        dd("construct");
    }*/
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date_debut', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'label' => 'Date début :',
                'input' => 'datetime',
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

            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                [$this, 'onPreSubmit']
            )




            /*->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                $data = $event->getData();

                if($data['date_debut']['time'] === "") {
                    $data['date_debut']['time'] = "00:00";
                }


            });*/



        ;

        //dd("test");
        //dd($builder->get('date_debut')->getData());  //null
    }

    public function onPreSubmit(FormEvent $event): void {
        $data = $event->getData();
        //$form = $event->getForm();

        //dd($data);

        if($data['date_debut']['time'] === "" && $data['date_debut']['date'] !== "") {
            $data['date_debut']['time'] = "00:00";
            $event->setData($data);
        }

        if($data['date_fin']['time'] === "" && $data['date_fin']['date'] !== "") {
            $data['date_fin']['time'] = "00:00";
            $event->setData($data);
        }

        //ajout champ
        //$form->add('email', EmailType::class);

        //suppr champ
        /*unset($data['email']);
        $event->setData($data);*/

        //dd($data);
    }

    //bonne question, aucune reponse :/
    //https://stackoverflow.com/questions/67871979/symfony-validation-comparison-between-two-dates-in-a-form

    //form events ?
    //https://symfony.com/doc/current/form/dynamic_form_modification.html

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
            'validation_groups' => ['filtre']

            /*'validation_groups' => function(FormInterface $form) {
                $data = $form->getData();
                dd($data);
            }*/


        ]);
    }

}
