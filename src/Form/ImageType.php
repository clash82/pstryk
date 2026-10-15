<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
    public const MAX_FILE_SIZE = '5M';

    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $fileConstraint = new File(
            maxSize: self::MAX_FILE_SIZE,
            mimeTypes: [
                'image/jpeg',
            ],
            maxSizeMessage: 'Maksymalny rozmiar dla pliku to '.self::MAX_FILE_SIZE,
            mimeTypesMessage: 'Możesz dodać wyłącznie pliki w formacie JPEG',
        );

        $builder
            // tricky way to get thumbnail path in the form view, this value will not be saved
            ->add('filePath', null, [
                'disabled' => true,
            ])
            ->add('name', TextType::class, [
                'label' => false,
                'required' => true,
                'empty_data' => '',
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
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Publiczny',
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
                'required' => false,
                // the same limit is used by the drop zone to reject too big files before they get added
                'attr' => [
                    'data-max-size' => $fileConstraint->maxSize,
                    'data-max-size-message' => $fileConstraint->maxSizeMessage,
                ],
                'constraints' => [$fileConstraint],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'creation_type' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'image';
    }
}
