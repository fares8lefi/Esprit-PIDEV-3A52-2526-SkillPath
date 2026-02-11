<?php

namespace App\Form;

use App\Entity\Cours;  // ✅ Votre entité
use App\Entity\Module;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all',
                    'placeholder' => 'Ex: Introduction aux variables JavaScript'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le titre est obligatoire'])
                ]
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
            ->add('module', EntityType::class, [
                'label' => 'Module',
                'class' => Module::class,
                'choice_label' => 'name',  // ✅ Adaptez selon le nom de votre champ
                'placeholder' => 'Sélectionner un module',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le module est obligatoire'])
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-skillpath-blue focus:border-transparent transition-all resize-none',
                    'rows' => 8,
                    'placeholder' => 'Saisissez le contenu du cours...'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le contenu est obligatoire'])
                ],
                'help' => 'Pour les vidéos, insérer l\'URL. Pour les documents, insérer le chemin du fichier.'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,  // ✅ Votre entité
        ]);
    }
}