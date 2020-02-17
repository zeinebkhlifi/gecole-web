<?php

namespace TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('backend/index.html.twig');
    }
    public function index1Action()
    {
        return $this->render('frontend/index.html.twig');
    }
}
