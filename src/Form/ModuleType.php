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
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * @extends AbstractType<Module>
 */
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
            ->add('level', ChoiceType::class, [
                'label' => 'Niveau de difficulté',
                'choices' => [
                    'Débutant' => 'Débutant',
                    'Intermédiaire' => 'Intermédiaire',
                    'Avancé' => 'Avancé',
                ],
                'placeholder' => '-- Sélectionner un niveau --',
                'attr' => [
                    'class' => 'form-input'
                ],
                'required' => false
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
            ->add('imageFile', VichImageType::class, [
                'label' => 'Logo / Illustration',
                'required' => false,
                'allow_delete' => true,
                'download_uri' => true,
                'image_uri' => true,
                'asset_helper' => true,
                'attr' => [
                    'class' => 'form-input'
                ],
            ])
            ->add('documentFile', VichFileType::class, [
                'label' => 'Document de Cours (PDF/Word)',
                'required' => false,
                'allow_delete' => true,
                'download_uri' => true,
                'asset_helper' => true,
                'attr' => [
                    'class' => 'form-input'
                ],
            ])
            ->add('scheduledAt', \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class, [
                'label' => 'Planification (Schedule at)',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-input'
                ],
                'help' => 'Date et heure à laquelle le module sera visible/proposé dans le calendrier.'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Module::class,
        ]);
    }
}

