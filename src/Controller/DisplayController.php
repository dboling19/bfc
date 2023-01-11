<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class DisplayController extends AbstractController
{

  private $dir;

  public function __construct(ContainerBagInterface $params)
  { 
    $this->dir = $params->get('app.content_dir');
  }

  #[Route('/', name: 'home')]
  public function home(): Response
  {

    $files = scandir($this->dir);
    // var_dump($files);die;

    return $this->render('display/home.html.twig', [
      'files' => $files,
    ]);
  }
}
