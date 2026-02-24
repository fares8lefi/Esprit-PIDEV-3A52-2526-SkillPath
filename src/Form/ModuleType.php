<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Module;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('course', EntityType::class, [
                'label' => 'Formation (Cours Parent)',
                'class' => Course::class,
                'choice_label' => 'title',
                'placeholder' => 'Sélectionner une formation',
                'attr' => [
                    'class' => 'form-input'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le cours parent est obligatoire'])
                ]
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre du chapitre',
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Ex: Les variables'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le titre est obligatoire'])
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de contenu',
                'choices' => [
                    'Sélectionner un type' => '',
                    '📹 Vidéo' => 'video',
                    '📝 Texte' => 'texte',
                    '🎓 Quiz' => 'quiz',
                    '💻 Exercice' => 'exercice',
                    '📄 Document' => 'document',
                ],
                'attr' => [
                    'class' => 'form-input'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le type est obligatoire'])
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'form-input resize-none',
                    'rows' => 8,
                    'placeholder' => 'Saisissez le contenu...'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le contenu est obligatoire'])
                ],
                'help' => 'Pour les vidéos, insérer l\'URL. Pour les documents, insérer le chemin du fichier.'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description courte (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-input resize-none',
                    'rows' => 3,
                    'placeholder' => 'Bref résumé...'
                ],
            ])
            ->add('documentFile', \Symfony\Component\Form\Extension\Core\Type\FileType::class, [
                'label' => 'Fichier de cours (PDF, DOC)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-input'
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un document valide (PDF ou Word)',
                    ])
                ],
            ])
            ->add('imageFile', \Symfony\Component\Form\Extension\Core\Type\FileType::class, [
                'label' => 'Logo/Image du module',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-input'
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Formats acceptés: JPG, PNG, WEBP (max 2Mo)',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Module::class,
        ]);
    }
}

