<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Download;
use AppBundle\Entity\OrderItem;
use AppBundle\Form\OrderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Framework;
use AppBundle\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * For simplicity, everything is in one controller but ideally these would be
 * in separate controllers.
 */
class HomeController extends Controller
{
    const STATE_BEGIN    = 'begin';
    const STATE_COMPLETE = 'complete';
    const STATE_READY    = 'ready';

    /**
     * @Framework\Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle::homepage.html.twig', [
            'downloads' => $this->getDoctrine()->getRepository(Download::class)->findAll(),
        ]);
    }

    /**
     * @Framework\Route("/begin-order-for-download/{id}", name="begin_order_for_download")
     */
    public function beginOrderForDownloadAction(Request $request, $id)
    {
        $download = $this->getDoctrine()->getRepository(Download::class)->findOneBy(['id' => $id]);

        $order = new Order();
        $form = $this->createForm(new OrderType(), $order);

        if ('POST' === $request->getMethod()) {
            $order->setState(self::STATE_BEGIN);

            $orderItem = new OrderItem();
            $orderItem->setDownload($download);
            $orderItem->setOrder($order);
            $orderItem->setUnitPrice(59);
            // $orderItem->setImmutable(true); // Need to verify how this affects behavior.

            $this->get('event_dispatcher')->dispatch('app.download_ordered', new GenericEvent($order));

            $form->handleRequest($request);

            if ($form->isValid()) {
                $order->setState(self::STATE_COMPLETE);
                $this->addFlash('order.state', self::STATE_COMPLETE);

                $this->getDoctrine()->getManager()->persist($order);
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('complete_order', [
                    'id' => $order->getId(),
                ]);
            }

            $this->getDoctrine()->getManager()->persist($order);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('AppBundle::begin_order_for_download.html.twig', [
            'form'     => $form->createView(),
            'download' => $download,
        ]);
    }

    /**
     * @Framework\Route("/complete-order/{id}", name="complete_order")
     */
    public function completeOrderAction(Request $request, $id)
    {
        $this->verifySessionState($request->getSession(), self::STATE_COMPLETE);

        $order = $this->getDoctrine()->getRepository(Order::class)->findOneBy(['id' => $id]);

        $order->setState(self::STATE_READY);
        $this->addFlash('order.state', self::STATE_READY);

        return $this->render('AppBundle::complete_order.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Framework\Route("/download/{id}", name="download")
     */
    public function downloadAction(Request $request, $id)
    {
        $this->verifySessionState($request->getSession(), self::STATE_READY);

        $download = $this->getDoctrine()->getRepository(Download::class)->findOneBy(['id' => $id]);

        return new BinaryFileResponse(
            $download->getFilePath(),
            BinaryFileResponse::HTTP_OK,
            [],
            false,
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }

    /**
     * Prevent direct access, and quietly redirect to homepage.
     *
     * @param Session $session
     * @param string  $state
     */
    private function verifySessionState(Session $session, $state)
    {
        $flashBag = $session->getFlashBag();

        if (
            !$flashBag->has('order.state')
            || !in_array($state, $flashBag->peek('order.state', []))
        ) {
            $this->redirectToRoute('homepage');
        } else {
            $flashBag->get('order.state');
        }
    }
}
