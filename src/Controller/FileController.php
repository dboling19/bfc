<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\DisplayController;

class FileController extends AbstractController
{

    /**
    * 
    #[Route('/save', name: 'save_file')]
    public function save_file(): Response
    {
      

        return $this->render('file/index.html.twig', [
            
        ]);
    }
}
