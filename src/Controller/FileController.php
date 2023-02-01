<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\DisplayController;

class FileController extends AbstractController
{

    /**
     * Controls file saving to the directory and database
     * 
     * @author Daniel Boling
     */
    #[Route('/save', name: 'save_file')]
    public function save_file(Request $request): Response
    {

      if ($params = $request->request->all())
      {
        dd($params);
      } else {
        echo 'No params';
      }
      

      return $this->render('file/index.html.twig', [
          
      ]);
    }
}
