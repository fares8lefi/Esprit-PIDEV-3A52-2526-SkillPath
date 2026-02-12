<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
                    'placeholder' => 'Ex: Conférence Symfony'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le titre est obligatoire'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all resize-none',
                    'rows' => 5,
                    'placeholder' => 'Description de l\'événement...'
                ],
            ])
            ->add('eventDate', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La date est obligatoire'])
                ]
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'heure de début est obligatoire'])
                ]
            ])
            ->add('endTime', TimeType::class, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'heure de fin est obligatoire'])
                ]
            ])
            ->add('location', EntityType::class, [
                'label' => 'Lieu',
                'class' => Location::class,
                'placeholder' => 'Sélectionner un lieu',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image (optionnel)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '3M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Formats acceptés: JPG, PNG, WEBP',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
