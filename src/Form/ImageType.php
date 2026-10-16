<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Image;
use App\Manager\PendingUploadManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType as CoreFileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImageType extends AbstractType
{
    /** used only when the upload_max_filesize ini value cannot be read or is unlimited */
    private const string FALLBACK_MAX_FILE_SIZE = '5M';

    public function __construct(private readonly PendingUploadManager $pendingUploadManager)
    {
    }

    /**
     * Shared by the form and by the endpoint that receives files one by one, so both enforce the same rules.
     */
    public static function createFileConstraint(): File
    {
        $maxFileSize = self::getMaxFileSize();

        return new File(
            maxSize: $maxFileSize,
            mimeTypes: [
                'image/jpeg',
            ],
            maxSizeMessage: 'Maksymalny rozmiar dla pliku to '.$maxFileSize,
            mimeTypesMessage: 'Możesz dodać wyłącznie pliki w formacie JPEG',
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $fileConstraint = self::createFileConstraint();

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
            // set by the drop zone: identifies a file that has already been uploaded in a separate request
            ->add('uploadToken', HiddenType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
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

        // a file uploaded earlier by the drop zone replaces the (empty) file input of the entry
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();

            if (!\is_array($data) || ($data['file'] ?? null) instanceof UploadedFile) {
                return;
            }

            $file = $this->pendingUploadManager->find((string) ($data['uploadToken'] ?? ''));

            if ($file instanceof UploadedFile) {
                $data['file'] = $file;
                $event->setData($data);
            }
        });
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

    /**
     * The limit is taken from PHP's upload_max_filesize (e.g. "5M"), so the form validation,
     * the drop zone and the server never disagree about the allowed size.
     */
    private static function getMaxFileSize(): string
    {
        $limit = trim((string) \ini_get('upload_max_filesize'));

        // "0" / "-1" mean unlimited and anything else non-numeric cannot be handled by the constraint
        if (!preg_match('/^[1-9]\d*[KMG]?$/i', $limit)) {
            return self::FALLBACK_MAX_FILE_SIZE;
        }

        return $limit;
    }
}
