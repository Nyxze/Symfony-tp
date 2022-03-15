<?php

namespace App\Controller;

use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController{


    /**
     * @Route("/home/{userName}", name="home",
     * defaults={"userName":"Paul"})
     *
     * @param Request $request
     * @param ProductService $productService
     * @param string $userName
     * @return Response
     */
    public function homeAction(
        Request $request,
        ProductService $productService,
        string $userName = "test"): Response
    {
        $age = $request->query->get('age', 8);
        $session = $request->getSession();
        $session->set("user", "Jane");



        return $this->render("home/login.html.twig", [
            "age" => $age,
            "userName" => $userName,
            "appNameFromController" => $this->getParameter("app_name")
        ]);
    }

    /**
     * @Route("/user/{id<\d+>?5}", name="user")
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function userAction(Request $request,int $id = 5): Response
    {
        return $this->render("home/user.html.twig", ["id" => $id]);
    }

    /**
     * @Route("/nav/{current}", name="nav")
     * @param string $current
     * @return Response
     */
    public function navbar(string $current= "test"): Response{
        return $this->render("/home/navbar.html.twig", [
            "current" => $current
        ]);
    }

}