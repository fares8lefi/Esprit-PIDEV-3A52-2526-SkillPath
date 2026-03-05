<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

/**
 * @extends AbstractType<Location>
 */
class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du lieu',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
                    'placeholder' => 'Ex: Salle de conférence A'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire'])
                ]
            ])
            ->add('building', TextType::class, [
                'label' => 'Bâtiment',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
                    'placeholder' => 'Ex: Bâtiment Principal'
                ],
            ])
            ->add('roomNumber', TextType::class, [
                'label' => 'Numéro de salle',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
                    'placeholder' => 'Ex: A-101'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de salle est obligatoire'])
                ]
            ])
            ->add('maxCapacity', IntegerType::class, [
                'label' => 'Capacité maximale',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
                    'placeholder' => 'Ex: 50',
                    'min' => 1
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La capacité est obligatoire']),
                    new Positive(['message' => 'La capacité doit être un nombre positif'])
                ]
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
            ])
            ->add('latitude', HiddenType::class, [
                'required' => false,
                'attr'     => ['id' => 'location_latitude'],
            ])
            ->add('longitude', HiddenType::class, [
                'required' => false,
                'attr'     => ['id' => 'location_longitude'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
