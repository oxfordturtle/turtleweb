<?php

namespace App\Entity\Feedback;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class FeedbackManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveFeedback(Feedback $feedback)
    {
        // save the feedback
        $this->entityManager->persist($feedback);
        $this->entityManager->flush();

        // send email to turtle@cs.ox.ac.uk
        $user = $feedback->getUser();
        $to = 'turtle@cs.ox.ac.uk';
        $subject = 'Turtle Feedback ['.$feedback->getType().']: '.$feedback->getSubject();
        $message = '<html><head><title>Turtle Feedback</title></head><body>';
        $message .= '<p>'.$user->getFirstname().' '.$user->getSurname().' posted some feedback on www.turtle.ox.ac.uk:</p>';
        $message .= '<pre>'.$feedback->getComments().'</pre>';
        $message .= '</body></html>';
        $headers = 'Content-Type: text/html; charset=UTF-8
From: '.$user->getFirstname().' '.$user->getSurname().' <'.$user->getEmail().'>';
        mail($to, $subject, $message, $headers);
    }
}
