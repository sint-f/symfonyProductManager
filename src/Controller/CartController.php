<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/cart', name: 'app_cart')]
    public function Cart(Request $request, EntityManagerInterface $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $session= $request->getSession();
        $productIds = $session->get('products', []);
        $session->set('orderId', null);

        foreach ($productIds as $productId) {
            $value = $doctrine->getRepository(Product::class)->find($productId);
            $products[] = $value;
        }

        if(!isset($products)){
            $products = null;
        }

        return $this->render('cart/index.html.twig', [
            'products' => $products,
        ]);

    }

    #[Route('/cart/{id}', name: 'app_cartproduct')]
    public function CartAdd(Request $request,int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $session= $request->getSession();
        $products = $session->get('products', []);
        $arrayproducts = array_merge($products, [$id]);

        $session->set('products', $arrayproducts);

        return $this->redirect($this->generateUrl(route: 'app_products'));
    }

    #[Route('/cart/delete/{id}', name: 'app_cartdeleteproduct')]
    public function CartDeleteProduct(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $session= $request->getSession();
        $products = $session->get('products', []);

        unset($products[$id]);
        $session->set('products', $products);

        return $this->redirect($this->generateUrl(route: 'app_cart'));
    }

    #[Route('/cart/clear', name: 'app_cartclear', priority: 2)]
    public function CartRemove(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $session= $request->getSession();

        $products = [];
        $session->set('products', $products);

        return $this->redirect($this->generateUrl(route: 'app_cart'));
    }
}
