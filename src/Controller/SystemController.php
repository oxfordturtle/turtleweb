<?php

/** SYSTEM CONTROLLER
 *  pages for the system (blue) section
 */
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="system_")
 */
class SystemController extends AbstractController
{

    /** --- SYSTEM HOME PAGE
     * @Route("/", name="home")
     */
    public function home(Request $request): Response
    {
        return $this->render('system/home.html.twig');
    }

    /** --- DOCUMENTATION PAGE
     * @Route("/documentation", name="documentation")
     */
    public function documentation(Request $request): Response
    {
        return $this->render('system/documentation.html.twig');
    }

    /** --- ARTICLES PAGE
     * @Route("/articles", name="articles")
     */
    public function articles(Request $request): Response
    {
        return $this->render('system/articles.html.twig');
    }

    /** --- CSAC BOOK PAGE
     * @Route("/csac", name="csac")
     */
    public function csac(Request $request): Response
    {
        return $this->render('system/csac.html.twig');
    }

    /** --- ABOUT PAGE
     * @Route("/about", name="about")
     */
    public function about(Request $request): Response
    {
        return $this->render('system/about.html.twig');
    }

    /** --- CONTACT PAGE
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request): Response
    {
        return $this->render('system/contact.html.twig');
    }
}
