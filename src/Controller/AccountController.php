<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\File\CreateFileType;
use App\Service\FileManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the account pages of the site.
 *
 * @Route("/account", name="account_")
 * @IsGranted("ROLE_USER")
 */
class AccountController extends AbstractController
{
  /**
   * Route for the account overview page.
   *
   * @Route("/", name="index")
   * @return Response
   */
  public function index(): Response
  {
    // redirect if the user is not verified
    if (!$this->getUser()->isVerified()) {
      return $this->redirectToRoute('account_verify');
    }

    // render and return the page
    return $this->render('account/index.html.twig');
  }

  /**
   * Route for user's who have not yet verified their account.
   *
   * @Route("/verify", name="verify")
   * @return Response
   */
  public function verify(): Response
  {
    // render and return the page
    return $this->render('account/verify.html.twig');
  }

  /**
   * Route for the account files page.
   *
   * @Route("/files", name="files")
   * @param Request $request
   * @param FileManager $fileManager
   * @return Response
   */
  public function files(Request $request, FileManager $fileManager): Response
  {
    // redirect if the user is not verified
    if (!$this->getUser()->isVerified()) {
      return $this->redirectToRoute('account_verify');
    }

    // render and return the page
    return $this->render('account/files.html.twig');
  }

  /**
   * Route for fetching a user's file data as JSON.
   *
   * @Route("/files/json", name="files_json")
   * @param Request $request
   * @param FileManager $fileManager
   * @return Response
   */
  public function filesJson(Request $request, FileManager $fileManager): Response
  {
    // redirect if the user is not verified
    if (!$this->getUser()->isVerified()) {
      return $this->redirectToRoute('account_verify');
    }

    // render and return the JSON data
    return $this->json($fileManager->getUserFileData($this->getUser()));
  }

  /**
   * Route for uploading a new file.
   *
   * @Route("/files/upload", name="upload_file")
   * @param Request $request
   * @param FileManager $fileManager
   * @return Response
   */
  public function newFile(Request $request, FileManager $fileManager, $path = null): Response
  {
    // redirect if the user is not verified
    if (!$this->getUser()->isVerified()) {
      return $this->redirectToRoute('account_verify');
    }

    // create the new file form
    $file = new File($this->getUser(), 'txt');
    $fileForm = $this->createForm(CreateFileType::class, $file);

    // handle the new file form
    $fileForm->handleRequest($request);
    if ($fileForm->isSubmitted() && $fileForm->isValid()) {
      $fileManager->create($file, $this->getUser());
      $this->addFlash('success', 'File has been uploaded.');
    }

    // set the twig variables
    $twigs = [
      'fileForm' => $fileForm->createView()
    ];

    // render and return the page
    return $this->render('account/upload-file.html.twig', $twigs);
  }
}
