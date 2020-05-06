<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType as CoreFileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            // tricky way to get thumbnail path in the form view, this value will not be saved
            ->add('thumbsPublicPath', null, [
                'disabled' => true,
            ])
            ->add('name', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'readonly' => true,
                    'placeholder' => 'Nazwa pliku',
                    'class' => 'form-control',
                 ],
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Opis (niewymagany)',
                    'class' => 'form-control',
                ],
             ])
            ->add('isMain', RadioType::class, [
                'label' => 'To zdjęcie jest również okładką',
                'required' => false,
            ])
            ->add('position', HiddenType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'image-position',
                ],
            ])
            ->add('file', CoreFileType::class, [
                'label' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'maxSizeMessage' => 'Maksymalny rozmiar dla pliku to 5M',
                        'mimeTypes' => [
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Możesz dodać wyłącznie pliki w formacie JPEG',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Image',
            'creation_type' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'image';
    }
}
