<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Finder\Finder;

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

    $finder = new Finder();
    $finder->in($this->dir);

    if ($finder->hasResults()) {
      $files = iterator_to_array($finder->getIterator());
    }
    // dd(iterator_to_array($finder->getIterator()));


    return $this->render('display/home.html.twig', [
      'files' => $files,
    ]);
  }
}
