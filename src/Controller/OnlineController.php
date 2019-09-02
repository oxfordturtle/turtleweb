<?php

/** ONLINE CONTROLLER
 *  pages for the online (green) section
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/online", name="online_")
 */
class OnlineController extends AbstractController
{

    /** --- ONLINE SYSTEM HOME PAGE
     * @Route("/", name="home")
     */
    public function home(Request $request): Response
    {
        return $this->render('online/home.html.twig');
    }

    /** --- ONLINE SYSTEM HELP PAGE
     * @Route("/help", name="help")
     */
    public function help(Request $request): Response
    {
        return $this->render('online/help.html.twig');
    }

    /** --- ONLINE SYSTEM ABOUT PAGE
     * @Route("/about", name="about")
     */
    public function about(Request $request): Response
    {
        return $this->render('online/about.html.twig');
    }
}
