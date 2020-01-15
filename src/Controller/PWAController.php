<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the progressive web app pages.
 *
 * @Route("/pwa", name="pwa_")
 */
class PWAController extends AbstractController
{
  /**
   * Route for the home page.
   *
   * @Route("/", name="home")
   * @return Response
   */
  public function index(): Response
  {
    // render and return the page
    return $this->render('pwa/index.html.twig');
  }
}
