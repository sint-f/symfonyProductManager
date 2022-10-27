<?php

namespace App\Command;

use App\Entity\Order;
use Couchbase\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCommand(
    name: 'order-progress-delete',
    description: 'Delete all Orders with Progress as status',
    hidden: false,
)]
class OrderProgressDeleteCommand extends Command
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        $order = $this->order->delete($input->getArgument('username'));

//        $io = new SymfonyStyle($input, $output);
//        $this->container->get('doctrine')->getManager();
//
//        $orders = $doctrine->getRepository(Order::class);
//        foreach ($orders as $order){
//            $status = $order->getStatus();
//            if ($status === 'in progress')
//            {
//                $doctrine->remove($order);
////                    $io->title($order.' order verwijdert');
//            }
//        }

        return Command::SUCCESS;
    }
}
