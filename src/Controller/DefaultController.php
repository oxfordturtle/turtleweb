<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the main pages of the site.
 *
 * @Route("/")
 */
class DefaultController extends AbstractController
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
    return $this->render('pages/index.html.twig');
  }

  /**
   * Route for the online system page.
   *
   * @Route("/online", name="online")
   * @return Response
   */
  public function online(): Response
  {
    // render and return the page
    return $this->render('pages/online.html.twig');
  }

  /**
   * Route for the documentation page.
   *
   * @Route("/documentation", name="documentation")
   * @return Response
   */
  public function documentation(): Response
  {
    // render and return the page
    return $this->render('pages/documentation.html.twig');
  }

  /**
   * Route for the about page.
   *
   * @Route("/about", name="about")
   * @return Response
   */
  public function about(): Response
  {
    // render and return the page
    return $this->render('pages/about.html.twig');
  }

  /**
   * Route for the contact page.
   *
   * @Route("/contact", name="contact")
   * @return Response
   */
  public function contact(): Response
  {
    // render and return the page
    return $this->render('pages/contact.html.twig');
  }

  /**
   * Route for downloading a file from the downloads directory.
   *
   * @Route("/downloads/{path}", name="downloads", requirements={"path": ".+"})
   * @param string $path
   * @return BinaryFileResponse
   */
  public function downloads(string $path): BinaryFileResponse
  {
    // get the file path
    $path = $this->getParameter('downloads_directory').$path;

    // look for the file
    if (!file_exists($path)) {
      throw new NotFoundHttpException('File not found.');
    }

    // return the file
    return new BinaryFileResponse($path);
  }
}
