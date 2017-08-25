<?php

namespace tests\AppBundle\Entity\Manager;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Manager\ContactManager;
use AppBundle\Service\MailerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

class ContactManagerTest extends TestCase
{
    /**
     * @var \Twig_Environment
     */
    protected $templating;

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @var array
     */
    protected $template;

    /**
     * @var string $from
     */
    protected $from;

    /**
     * @var string $to
     */
    protected $to;

    /**
     * @var MailerService
     */
    protected $mailerService;

    /**
     * @var ContactManager
     */
    protected $contactManager;

    public function setUp()
    {
//        $transport = $this->createMock(\Swift_Transport::class);
//
//        $this->mailer = $this->getMockBuilder(\Swift_Mailer::class)
//                             ->setConstructorArgs([$transport])
//                             ->getMock();

        $this->templating = $this->getMockBuilder(\Twig_Environment::class)
                                 ->setMethods(['render'])
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->translator = $this->getMockBuilder(Translator::class)
                                 ->setMethods(['trans'])
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->template = 'Bundle:Controller:Method';
        $this->from     = 'from@test.fr';
        $this->to       = 'to@test.fr';

        $this->mailerService = $this->getMockBuilder(MailerService::class)
                                    ->setMethods(['sendMail'])
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->contactManager = new ContactManager(
            $this->mailerService,
            $this->templating,
            $this->translator,
            $this->template,
            $this->from,
            $this->to
        );
    }

    public function testSendMail()
    {
        $contact = new Contact();
        $contact->setAdditionalInformation('some more information');
        $contact->setCellphone('0123456789');
        $contact->setEmail('contact.email@test.fr');
        $contact->setFirstName('firstName');
        $contact->setLastName('lastName');
        $contact->setKnowledge('pub_papier');

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('message_subject', ['%name%' => $contact->getFirstName().' '.$contact->getLastName()], 'contact')
            ->willReturn('some translation');

        $this->templating
            ->expects($this->once())
            ->method('render')
            ->with($this->template, ['contact' => $contact])
            ->willReturn('the rendered template');

        $this->mailerService
            ->expects($this->once())
            ->method('sendMail')
            ->with(
                $this->from,
                $this->to,
                'some translation',
                'the rendered template'
            );

        $this->contactManager->sendMail($contact);
    }
}