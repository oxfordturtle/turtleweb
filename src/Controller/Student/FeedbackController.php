<?php

/** FEEDBACK CONTROLLER
 *  the forum (red) section when logged in as a student
 */
namespace App\Controller\Student;

use App\Entity\Feedback\Feedback;
use App\Entity\Feedback\FeedbackManager;
use App\Entity\Feedback\CreateHelpFeedbackType;
use App\Entity\Feedback\CreateSuggestionFeedbackType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student/feedback", name="student_")
 */
class FeedbackController extends AbstractController
{
    /** --- STUDENT AREA FEEDBACK PAGE
     * @Route("/{tab}", requirements={"tab": "suggestion|help"}, name="feedback")
     */
    public function feedback(Request $request, FeedbackManager $feedbackManager, $tab = 'help'): Response
    {
        // help request form
        $helpFeedback = new Feedback($this->getUser(), 'help');
        $helpFeedbackForm = $this->createForm(CreateHelpFeedbackType::class, $helpFeedback);
        $helpFeedbackForm->handleRequest($request);
        if ($helpFeedbackForm->isSubmitted()) {
            $tab = 'help';
            if ($helpFeedbackForm->isValid()) {
                try {
                    $feedbackManager->saveFeedback($helpFeedback);
                    $this->addFlash('notice', 'Your help request has been submitted. We will email you as soon as possible.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        // suggestions form
        $suggestionFeedback = new Feedback($this->getUser(), 'suggestion');
        $suggestionFeedbackForm = $this->createForm(CreateSuggestionFeedbackType::class, $suggestionFeedback);
        $suggestionFeedbackForm->handleRequest($request);
        if ($suggestionFeedbackForm->isSubmitted()) {
            $tab = 'suggestions';
            if ($suggestionFeedbackForm->isValid()) {
                try {
                    $feedbackManager->saveFeedback($suggestionFeedback);
                    $this->addFlash('notice', 'Thank you, your suggestion has been sent.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        // render and return the response
        $twigs = [
          'tab' => $tab,
          'helpFeedbackForm' => $helpFeedbackForm->createView(),
          'suggestionFeedbackForm' => $suggestionFeedbackForm->createView()
        ];
        return $this->render('student/feedback.html.twig', $twigs);
    }
}
