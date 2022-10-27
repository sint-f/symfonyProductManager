<?php

namespace App\Controller;

use App\Entity\Btw;
use App\Entity\Category;
use App\Form\BtwType;
use App\Form\CategoryType;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public  function productsAll(EntityManagerInterface $em): Response
    {
        $repository = $em->getRepository(Product::class);
        $products = $repository->findAll();

        return $this->render('products/index.html.twig', [
            'products' => $products,
        ]);

    }

    #[Route('/create/admin', name: 'app_create')]
    public function create(ManagerRegistry $doctrine, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $entityManager->persist($product);
            $entityManager->flush();
            // ... perform some action, such as saving the task to the database

            return $this->redirectToRoute('app_products');
        }

        return $this->renderForm('post/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}/admin', name: 'app_delete')]
    public function deleteProduct(EntityManagerInterface $doctrine, int $id)
    {
        $product = $doctrine->getRepository(Product::class)->find($id);
        $doctrine->remove($product);
        $doctrine->flush();

        return $this->redirect($this->generateUrl(route: 'app_products'));
    }

    #[Route('/products/{id}', name: 'app_products_id')]
    public function showProduct(EntityManagerInterface $doctrine, int $id): Response
    {
        $product = $doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );

        }

        return $this->render('products/product.html.twig', [
            'product' => $product,
        ]);
    }
    #[Route('/products/category/{id}', name: 'app_products_categoryid')]
    public function showCategoryProducts(EntityManagerInterface $doctrine, int $id): Response
    {
        $qb = $doctrine->createQueryBuilder();
        $q  = $qb->select(array('product'))
            ->from(Product::class, 'product')
            ->where(
                $qb->expr()->eq('product.category', $id)
            )
            ->orderBy('product.category', 'DESC')
            ->getQuery();
        $products = $q->getResult();

        if (!$products) {
            throw $this->createNotFoundException(
                'No category found for id '.$id
            );

        }

        return $this->render('products/productcategory.html.twig', [
            'products' => $products,
        ]);
    }


    #[Route('/btw/admin', name: 'app_btw')]
    public function btw(EntityManagerInterface $entityManager, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $btw = new Btw();
        $form = $this->createForm(BtwType::class, $btw);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($btw);
            $entityManager->flush();

            return $this->redirectToRoute('app_products');
        }

        return $this->renderForm('post/btw.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/bewerk/{id}/admin', name: 'app_bewerk')]
    public function bewerkProduct(ManagerRegistry $doctrine, \Symfony\Component\HttpFoundation\Request $request, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $doctrine->getRepository(Product::class)->find($id);
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_products');
        }

        return $this->renderForm('post/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/category/admin', name: 'app_category')]
    public  function categoryAll(EntityManagerInterface $doctrine): Response
    {
        $repository = $doctrine->getRepository(Category::class);
        $category = $repository->findAll();

        return $this->render('post/category.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/category/create/admin', name: 'app_categorycreate')]
    public function categoryCreate(ManagerRegistry $doctrine, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category');
        }

        return $this->renderForm('post/categorycreate.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/category/delete/{id}/admin', name: 'app_categorydelete')]
    public function deleteCategory(EntityManagerInterface $doctrine, int $id)
    {
        $category = $doctrine->getRepository(Category::class)->find($id);

        $doctrine->remove($category);
        $doctrine->flush();

        return $this->redirect($this->generateUrl(route: 'app_category'));
    }
}