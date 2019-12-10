<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for Turtle System downloads.
 *
 * @Route("/turtle", name="turtle_")
 */
class TurtleController extends AbstractController
{
  /**
   * Route for downloading Turtle System version information.
   *
   * @Route("/versions", name="versions")
   * @return BinaryFileResponse
   */
  public function versions(): BinaryFileResponse
  {
    // get the file path
    $path = $this->getParameter('turtle_directory').'versions.json';

    // check the file exists
    if (!file_exists($path)) {
      throw new NotFoundHttpException('File '.$path.' not found.');
    }

    // return the file
    return new BinaryFileResponse($path);
  }

  /**
   * Route for downloading Turtle System download totals.
   *
   * @Route("/downloads", name="count")
   * @return BinaryFileResponse
   */
  public function count(Request $request): BinaryFileResponse
  {
    // get the file path
    $path = $this->getParameter('turtle_directory').'downloads.json';

    // check the file exists
    if (!file_exists($path)) {
      throw new NotFoundHttpException('File '.$path.' not found.');
    }

    // return the file
    return new BinaryFileResponse($path);
  }

  /**
   * Route for downloading the Turtle System D
   *
   * @Route("/d/{version}", name="tsd")
   * @param string $version
   * @return BinaryFileResponse
   */
  public function tsd(string $version): BinaryFileResponse
  {
    // get the file directory and name
    $turtleDir = $this->getParameter('turtle_directory');
    $versions = json_decode(file_get_contents($turtleDir.'versions.json'), true);
    $filename = 'TurtleSystemD_'.$versions['D'][$version].'.exe';

    // check the file exists
    if (!file_exists($turtleDir.$filename)) {
      throw new NotFoundHttpException('File '.$filename.' not found.');
    }

    // increment the download totals
    $count = json_decode(file_get_contents($turtleDir.'downloads.json'), true);
    $month = date('Y').'.'.date('m');
    if (!array_key_exists($month, $count)) {
      $count[$month] = [];
    }
    if (!array_key_exists('D'.$version, $count[$month])) {
      $count[$month]['D'.$version] = 1;
    } else {
      $count[$month]['D'.$version] += 1;
    }
    $data = json_encode($count, JSON_PRETTY_PRINT);
    file_put_contents($turtleDir.'downloads.json', $data);

    // create and return the response
    $response = new BinaryFileResponse($turtleDir.$filename);
    $response->setContentDisposition('attachment', 'TurtleSystemD'.$version.'.exe');
    return $response;
  }

  /**
   * Route for downloading the Turtle System E.
   *
   * @Route("/e/{platform}", name="tse")
   */
  public function tse(string $platform = 'browser'): BinaryFileResponse
  {
    // get the file directory and name
    $turtleDir = $this->getParameter('turtle_directory');
    $versions = json_decode(file_get_contents($turtleDir.'versions.json'), true);
    $exts = ['browser' => 'js', 'macos' => 'app.zip', 'win' => 'exe'];
    $ext = $exts[$platform];
    $filename = 'TurtleSystemE_'.$versions['E'].'.'.$ext;

    // check the file exists
    if (!file_exists($turtleDir.$filename)) {
      throw new NotFoundHttpException('File '.$filename.' not found.');
    }

    // increment the download totals
    if ($platform !== 'browser') {
      $count = json_decode(file_get_contents($turtleDir.'downloads.json'), true);
      $month = date('Y').'.'.date('m');
      if (!array_key_exists($month, $count)) {
        $count[$month] = [];
      }
      if (!array_key_exists('E'.$platform, $count[$month])) {
        $count[$month]['E'.$platform] = 1;
      } else {
        $count[$month]['E'.$platform] += 1;
      }
      $data = json_encode($count, JSON_PRETTY_PRINT);
      file_put_contents($turtleDir.'downloads.json', $data);
    }

    // create and return the response
    $response = new BinaryFileResponse($turtleDir.$filename);
    if ($platform === 'browser') {
      $response->setContentDisposition('inline');
    } else {
      $response->setContentDisposition('attachment', 'TurtleSystemE.'.$ext);
    }
    return $response;
  }
}
