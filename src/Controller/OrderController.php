<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/orders', name: 'app_orders')]
    public function orders(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $orders = $user->getOrders();


        return $this->render('order/orders.html.twig', [
            'orders' => $orders,
        ]);

    }

    #[Route('/order/{id}', name: 'app_order', priority: 2)]
    public function order(ManagerRegistry $doctrine, EntityManagerInterface $em, Request $request,int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $orders = $user->getOrders();
        $enter = false;
        if ($id !== 0)
        {
            foreach ($orders as $order)
            {
                if ($order->getId() === $id)
                {
                    $enter = true;
                }
            }
        } else {
            $enter = true;
        }
        if ($enter === true)
        {
            $productIds = $request->getSession()->get('products', []);
            if (empty($productIds))
            {
                $qb = $em->createQueryBuilder();
                $q  = $qb->select(array('order_item'))
                    ->from(OrderItem::class, 'order_item')
                    ->where(
                        $qb->expr()->eq('order_item.orderId', $id)
                    )
                    ->orderBy('order_item.orderId', 'DESC')
                    ->getQuery();
                $productsFromQ = $q->getResult();

                foreach ($productsFromQ as $productFromQ) {
                    $idProduct = $productFromQ->getId();
                    $result[] = $idProduct;
                }
                $productIds = $result;
            }
            if ($id !== 0) {
                $request->getSession()->set('orderId', $id);
            }
            $orderId = $request->getSession()->get('orderId',);
            $products = [];
            $cartAmounts = [];
            $entityManager = $doctrine->getManager();
            if (!empty($productIds))
            {
                foreach ($productIds as $productId) {
                    if(!isset($cartAmounts[$productId])){
                        $cartAmounts[$productId] = 1;
                    }else{
                        $cartAmounts[$productId]++;
                    }

                    if(!isset($products[$productId])&&!isset($orderId)) {
                        $products[$productId] = $doctrine->getRepository(Product::class)->find($productId);
                    } if (isset($orderId)) {
                        $order = $doctrine->getRepository(Order::class)->find($orderId);
                        if (!isset($order)){
                            return $this->redirect($this->generateUrl(route: 'app_products'));
                        }
                        $products = $order->getOrderItems();
                    }
                }
            }

            if (isset($orderId)&&isset($productIds)) {

                $totalIncl = $this->calculatePriceBtw($products);
                $totalExcl = $this->calculatePrice($products);

                return $this->render('order/index.html.twig', [
                    'order' => $order,
                    'products' => $products,
                    'cartAmounts' => $cartAmounts,
                    'priceIncl' => $totalIncl,
                    'priceExcl' => $totalExcl,
                    'id' => $id,
                ]);

            }

            if (!isset($orderId)&&isset($productIds)) {
                $order = new Order();
                $order->setUser($this->getUser());
                $order->setStatus('in progress');
                $entityManager->persist($order);
                $entityManager->flush();
                $request->getSession()->set('orderId', $order->getId());
                $entityManager->persist($order);
                $entityManager->flush();


                foreach ($products as $product)
                {
                    $orderItem = new OrderItem();
                    $orderItem->setTotal($cartAmounts[$product->getId()] * $product->getPriceBtw());
                    $orderItem->setPrice($cartAmounts[$product->getId()] * $product->getprice());
                    $orderItem->setOrderId($order);
                    $orderItem->setProduct($product);
                    $orderItem->setQuantity($cartAmounts[$product->getId()]);
                    $entityManager->persist($orderItem);
                    $entityManager->flush();

                }

                return $this->redirect($this->generateUrl(route: 'app_order',parameters: ['id'=>0]));
            }
        }

        $request->getSession()->clear();
        return $this->redirect($this->generateUrl(route: 'app_products'));
    }

    //paypal linken in deze route
    #[Route('/order/pay/{id}', name: 'app_orderPay')]
    function orderPay(EntityManagerInterface $doctrine, Request $request,int $id){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $order = $doctrine->getRepository(Order::class)->find($id);
        $totalIncl = $this->calculatePriceBtw($order->getOrderItems());
//        $request->getSession()->set('orderId', '');
//        $request->getSession()->set('Products', '');
//
//        if (isset($order)){
//            $order->setStatus(Status: 'payed');
//            $doctrine->flush();
//        }

        return $this->render('order/pay.html.twig', [
            'euros' => $totalIncl
        ]);
    }

    #[Route('/order/cancel/{id}', name: 'app_orderCancel')]
    function orderCancel(EntityManagerInterface $doctrine, Request $request, int $id){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $order = $doctrine->getRepository(Order::class)->find($id);
        $request->getSession()->set('orderId', '');
        $request->getSession()->set('Products', '');

        if (isset($order)){
            $order->setStatus(Status: 'cancel');
            $doctrine->flush();
        }

        return $this->redirect($this->generateUrl(route: 'app_products'));
    }

    #[Route('/order/refund/{id}', name: 'app_orderRefund')]
    function orderRefund(EntityManagerInterface $doctrine, Request $request, int $id){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $order = $doctrine->getRepository(Order::class)->find($id);
        $request->getSession()->set('orderId', '');
        $request->getSession()->set('Products', '');

        if (isset($order)){
            $order->setStatus(Status: 'refund');
            $doctrine->flush();
        }

        return $this->redirect($this->generateUrl(route: 'app_products'));
    }

    function calculatePrice($products): float{
        $totalPrice = 0;
        foreach ( $products as $product ) {
            $price = $product->getprice();
            $totalPrice = $totalPrice + $price;
        }
        return $totalPrice;
    }

    function calculatePriceBtw($products): float{
        $totalPriceBtw = 0;
        foreach ( $products as $product ) {
            $priceBtw = $product->getTotal();
            $totalPriceBtw = $totalPriceBtw + $priceBtw;
        }
        return $totalPriceBtw;
    }
}
