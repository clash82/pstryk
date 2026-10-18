<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    final public const string TYPE_ADMIN = 'ROLE_ADMIN';
    final public const string TYPE_USER = 'ROLE_USER';

    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $isCreation = 'create' === $options['creation_type'];

        $passwordConstraints = [new Length(min: 8, minMessage: 'Hasło musi mieć co najmniej {{ limit }} znaków')];

        if ($isCreation) {
            $passwordConstraints[] = new NotBlank(message: 'Podaj hasło');
        }

        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'constraints' => [
                    new NotBlank(message: 'Podaj adres e-mail'),
                    new Email(message: 'Podaj poprawny adres e-mail'),
                    new Length(max: 180),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Hasło',
                'mapped' => false,
                'required' => $isCreation,
                'constraints' => $passwordConstraints,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'help' => $isCreation
                        ? 'co najmniej 8 znaków'
                        : 'pozostaw pole puste, aby nie zmieniać hasła',
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Typ konta',
                'choices' => [
                    'Administrator (zaplecze + ukryte zdjęcia)' => self::TYPE_ADMIN,
                    'Użytkownik (tylko ukryte zdjęcia)' => self::TYPE_USER,
                ],
                'attr' => [
                    'help' => 'tylko administratorzy mają dostęp do zaplecza; użytkownicy po zalogowaniu widzą wszystkie ukryte zdjęcia na stronach albumów',
                ],
            ])
            ->add('save', SubmitType::class)
            ->add('back', ButtonType::class);

        // the entity keeps a list of roles, the form lets choose a single account type
        $builder->get('roles')->addModelTransformer(new CallbackTransformer(
            static fn (?array $roles): string => \in_array(self::TYPE_ADMIN, $roles ?? [], true)
                ? self::TYPE_ADMIN
                : self::TYPE_USER,
            static fn (?string $type): array => self::TYPE_ADMIN === $type
                ? [User::ROLE_ADMIN]
                : [User::ROLE_USER]
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'creation_type' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'user';
    }
}
