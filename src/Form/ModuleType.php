<?php

namespace App\Form;

use App\Entity\Cours;
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
            ->add('cours', EntityType::class, [
                'label' => 'Formation (Cours Parent)',
                'class' => Cours::class,
                'choice_label' => 'titre',
                'placeholder' => 'Sélectionner une formation',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le cours parent est obligatoire'])
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Titre du chapitre',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
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
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le type est obligatoire'])
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all resize-none',
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
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all resize-none',
                    'rows' => 3,
                    'placeholder' => 'Bref résumé...'
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
