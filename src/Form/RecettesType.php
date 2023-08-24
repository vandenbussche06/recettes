<?php

namespace App\Form;

use App\Entity\Recettes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RecettesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => true,
                'label' => "Titre de la recette",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le titre de la recette est obligatoire.'
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le nom doit contenir au minimum {{ limit }} caractères.'
                    ]),
                ]
            ])
            ->add('sous_titre')
            ->add('imageFile', FileType::class, [
                'label' => 'Télécharger la photo de la recette',
                'required' => false,
                'mapped' => false
            ])
            ->add('description')
            ->add('nb_personnes', IntegerType::class, [
                'required' => true,
                'label' => "Nb de personnes",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nombre de personnes est obligatoire.'
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Le nombre de personnes doit contenir au minimum {{ limit }} personne.',
                        'max' => 50,
                        'maxMessage' => 'Le nombre de personnes doit contenir au maximum {{ limit }} personnes.',
                    ])
                ],
            ])
            ->add('niveau', ChoiceType::class, [
                'choices'  => [
                    'FACILE' => 1,
                    'INTERMEDIAIRE' => 2,
                    'DIFFICILE' => 3,
                ],
            ])
            ->add('temps')
            ->add('prix_personne')
            ->add('vegetarien', CheckboxType::class, [
                'label'    => 'Recette végétarienne',
                'required' => false,
            ])
            ->add('brouillon', SubmitType::class, [
                'label' => "Enregistrer en brouillon"
            ])
            ->add('publier', SubmitType::class, [
                'label' => "Enrigistrer"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recettes::class,
        ]);
    }
}
